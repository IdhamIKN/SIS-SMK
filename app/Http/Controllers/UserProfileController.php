<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserProfileUpdateRequest;
use App\Http\Requests\UserProfileChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            return view('profile.index', compact('user'));
        } catch (\Exception $e) {
            \Log::error('Profile access error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengakses profil.');
        }
    }

    public function update(UserProfileUpdateRequest $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validated();

            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ];

            // Log the update attempt
            \Log::info('Profile update attempt', [
                'user_id' => $user->id,
                'current_email' => $user->email,
                'new_email' => $request->email,
                'name' => $request->name,
                'has_avatar' => $request->hasFile('avatar')
            ]);

            // Handle avatar upload with error handling
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                try {
                    // Delete old avatar if exists
                    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }

                    // Store new avatar
                    $avatarPath = $request->file('avatar')->store('avatars', 'public');
                    $data['avatar'] = $avatarPath;
                } catch (\Exception $e) {
                    \Log::error('Avatar upload failed', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                    // Continue without avatar if upload fails
                    unset($data['avatar']);
                }
            }

            // Update user data
            $updated = $user->update($data);

            if (!$updated) {
                throw new \Exception('Failed to update user data');
            }

            \Log::info('Profile updated successfully', ['user_id' => $user->id]);

            return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Profile update error', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage());
        }
    }

    public function changePassword(UserProfileChangePasswordRequest $request)
    {
        $validated = $request->validated();

        $user = Auth::user();

        // Check current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }
}
