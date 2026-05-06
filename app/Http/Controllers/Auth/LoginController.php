<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\GTK;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        Log::channel('sis')->info('[Auth] Login attempt', [
            'username' => $request->username,
            'ip' => $request->ip(),
        ]);

        $username = $request->username;
        $user = null;
        // Detect berdasarkan panjang digit (sesuai validsidigit.md)
        if (preg_match('/^\\d{16}$/', $username)) {
            // NIK (16 digit) - untuk GTK
            $gtk = GTK::where('nik', $username)->with('user')->first();
            if ($gtk && $gtk->user) {
                $user = $gtk->user;
                Log::channel('sis')->info('[Auth] NIK detected', ['nik' => $username]);
            }
        } elseif (preg_match('/^\\d{18}$/', $username)) {
            // NIP (18 digit) - untuk GTK
            $gtk = GTK::where('nip', $username)->with('user')->first();
            if ($gtk && $gtk->user) {
                $user = $gtk->user;
                Log::channel('sis')->info('[Auth] NIP detected', ['nip' => $username]);
            }
        } elseif (preg_match('/^\d+$/', $username)) {
            // NIS atau NISN (siswa) - panjang bervariasi, bukan 16 atau 18 digit 0094163349
            $siswa = Siswa::where('nisn', $username)->orWhere('nisn', $username)->with('user')->first();
            if ($siswa && $siswa->user) {
                $user = $siswa->user;
                Log::channel('sis')->info('[Auth] NIS/NISN detected', ['identifier' => $username]);
            }
        }

        // Fallback ke email jika tidak ditemukan di digit atau input bukan digit
        if (! $user) {
            $user = User::where('email', $username)->first();
            if ($user) {
                Log::channel('sis')->info('[Auth] Email fallback', ['email' => $username]);
            }
        }

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            $role = $user->getRoleNames()->first();

            Log::channel('sis')->info('[Auth] Login success', [
                'user_id' => $user->id,
                'role' => $role,
            ]);

            // Role-based redirect
            return match ($role) {
                'superadmin', 'admin_tatib' => redirect()->intended('/dashboard'),
                'kepala_sekolah', 'waka' => redirect()->intended('/panel/realtime'),
                'gtk' => redirect()->intended('/kehadiran-guru/laporan'),
                'bk' => redirect()->intended('/absen/rekap'),
                'wali_kelas' => redirect()->intended('/siswa'),
                'siswa' => redirect()->intended('/absen/masuk'),
                default => redirect()->intended('/dashboard'),
            };
        }

        Log::channel('sis')->warning('[Auth] Login failed', [
            'username' => $request->username,
            'ip' => $request->ip(),
        ]);

        return back()->withErrors([
            'username' => 'Nomor Induk atau password tidak cocok.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Log::channel('sis')->info('[Auth] Logout', [
            'user_id' => Auth::id(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
