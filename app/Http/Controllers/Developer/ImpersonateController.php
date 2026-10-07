<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ImpersonateController extends Controller
{
    /**
     * Tampilkan daftar semua user yang bisa di-impersonate.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $role   = $request->query('role');

        // Kecualikan email developer dari daftar (tidak bisa di-impersonate)
        $developerEmails = array_filter(array_map(
            'trim',
            explode(',', env('DEVELOPER_EMAIL', ''))
        ));

        $users = User::query()
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role, fn ($q) => $q->where('role_utama', $role))
            ->when($developerEmails, fn ($q) => $q->whereNotIn('email', $developerEmails))
            ->orderBy('role_utama')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $roles = User::distinct()->orderBy('role_utama')->pluck('role_utama');

        return view('developer.impersonate.index', compact('users', 'roles'));
    }

    /**
     * Mulai impersonate user yang dipilih.
     */
    public function take(Request $request, User $user): RedirectResponse
    {
        $currentUser = auth()->user();

        if (! $currentUser->canImpersonate()) {
            abort(404);
        }

        if (! $user->canBeImpersonated()) {
            abort(404);
        }

        Log::warning('[Impersonate] Developer mulai impersonate', [
            'developer_id'    => $currentUser->id,
            'developer_email' => $currentUser->email,
            'target_id'       => $user->id,
            'target_email'    => $user->email,
            'target_role'     => $user->role_utama,
            'ip'              => $request->ip(),
        ]);

        $currentUser->impersonate($user);

        // Redirect sesuai role target — sama dengan LoginController
        return $this->redirectByRole($user);
    }

    /**
     * Kembali ke akun developer.
     */
    public function leave(Request $request): RedirectResponse
    {
        $impersonatedUser = auth()->user();

        Log::warning('[Impersonate] Developer stop impersonate', [
            'impersonated_id'    => $impersonatedUser->id,
            'impersonated_email' => $impersonatedUser->email,
            'ip'                 => $request->ip(),
        ]);

        auth()->user()->leaveImpersonation();

        return redirect()->route('developer.index')
            ->with('success', 'Kembali ke akun developer. Impersonate selesai.');
    }

    /**
     * Redirect berdasarkan role — konsisten dengan LoginController.
     */
    private function redirectByRole(User $user): RedirectResponse
    {
        $role = $user->getRoleNames()->first() ?? $user->role_utama;

        return match ($role) {
            'superadmin', 'admin_tatib'       => redirect()->intended('/dashboard'),
            'kepala_sekolah', 'waka'          => redirect()->intended('/panel/realtime'),
            'gtk'                             => redirect()->intended('/kehadiran-guru/laporan'),
            'bk'                             => redirect()->intended('/absen/rekap'),
            'wali_kelas'                      => redirect()->intended('/siswa'),
            'siswa'                           => redirect()->intended('/absen/masuk'),
            default                           => redirect()->intended('/dashboard'),
        };
    }
}
