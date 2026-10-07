<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    // ──────────────────────────────────────────
    // ROLES
    // ──────────────────────────────────────────

    public function index(): View
    {
        $roles = Role::withCount('permissions', 'users')
            ->with('permissions')          // eager-load agar pluck di view tidak N+1
            ->orderBy('name')
            ->get();

        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique'   => 'Role dengan nama tersebut sudah ada.',
            'name.max'      => 'Nama role maksimal 100 karakter.',
        ]);

        Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $this->clearCache();

        return back()->with('success', "Role \"{$request->name}\" berhasil dibuat.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        // Lindungi role superadmin dari perubahan apapun
        if ($role->name === 'superadmin') {
            return back()->with('error', 'Role superadmin tidak dapat diubah.');
        }

        $request->validate([
            'name'          => 'required|string|max:100|unique:roles,name,' . $role->id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ], [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique'   => 'Nama role sudah digunakan role lain.',
        ]);

        // Update nama role
        $role->update(['name' => $request->name]);

        // Sync permissions HANYA jika form mode edit mengirim flag '_has_permissions'.
        // Flag ini di-set oleh JS saat modal edit dibuka (value=1).
        // Dengan cara ini, jika user hanya mengganti nama role tanpa menyentuh
        // checkbox, permissions yang sudah ada tetap dipertahankan —
        // karena checkbox kosong pun tetap menghasilkan array kosong di PHP,
        // sehingga kita perlu sinyal eksplisit "form ini memang membawa data permissions".
        if ($request->input('_has_permissions') === '1') {
            $role->syncPermissions($request->input('permissions', []));
        }

        $this->clearCache();

        return back()->with('success', "Role \"{$role->name}\" berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'superadmin') {
            return back()->with('error', 'Role superadmin tidak dapat dihapus.');
        }

        $roleName = $role->name;
        $role->delete();
        $this->clearCache();

        return back()->with('success', "Role \"{$roleName}\" berhasil dihapus.");
    }

    // ──────────────────────────────────────────
    // PERMISSIONS
    // ──────────────────────────────────────────

    public function storePermission(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:permissions,name',
        ], [
            'name.required' => 'Nama permission wajib diisi.',
            'name.unique'   => 'Permission dengan nama tersebut sudah ada.',
            'name.max'      => 'Nama permission maksimal 150 karakter.',
        ]);

        Permission::create(['name' => $request->name, 'guard_name' => 'web']);
        $this->clearCache();

        return back()->with('success', "Permission \"{$request->name}\" berhasil dibuat.");
    }

    public function destroyPermission(Permission $permission): RedirectResponse
    {
        $permName = $permission->name;
        $permission->delete();
        $this->clearCache();

        return back()->with('success', "Permission \"{$permName}\" berhasil dihapus.");
    }

    // ──────────────────────────────────────────
    // HELPER
    // ──────────────────────────────────────────

    private function clearCache(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
