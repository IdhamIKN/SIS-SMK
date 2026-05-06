@extends('layouts.guest')

@section('title', 'Login')

@push('styles')
    <style>
        * {
            box-sizing: border-box;
        }

        body,
        html {
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            font-family: inherit;
            min-height: 100vh;
        }

        /* ── Hero banner ── */
        .login-hero {
            position: relative;
            width: 100%;
            height: 220px;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 60%, #0ea5e9 100%);
            overflow: hidden;
            border-radius: 0 0 32px 32px;
        }

        .login-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, .08);
            border-radius: 50%;
        }

        .login-hero::after {
            content: '';
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 24px 20px 36px;
            text-align: center;
        }

        .hero-logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, .18);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: #fff;
            margin-bottom: 14px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, .25);
            box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
        }

        .hero-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            letter-spacing: -.01em;
        }

        .hero-sub {
            font-size: .78rem;
            color: rgba(255, 255, 255, .75);
            margin: 0;
        }

        /* ── Form card ── */
        .login-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, .10);
            margin: -28px 16px 0;
            padding: 28px 20px 24px;
            position: relative;
            z-index: 3;
        }

        .login-card-title {
            font-size: .82rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin: 0 0 20px;
            text-align: center;
        }

        /* ── Alert ── */
        .login-alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .login-alert.err {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .login-alert.ok {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        /* ── Field ── */
        .field-group {
            margin-bottom: 16px;
        }

        .field-label {
            display: block;
            font-size: .78rem;
            font-weight: 700;
            color: var(--text-main, #0f172a);
            margin-bottom: 6px;
        }

        .field-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .85rem;
            pointer-events: none;
            transition: color .2s;
        }

        .field-input {
            width: 100%;
            padding: 12px 42px 12px 38px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: .9rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
        }

        .field-input:focus {
            border-color: #7c3aed;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .12);
        }

        .field-input.is-invalid {
            border-color: #ef4444;
        }

        /* Toggle password */
        .toggle-pw {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .85rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 2px;
            line-height: 1;
            transition: color .2s;
        }

        .toggle-pw:hover {
            color: #7c3aed;
        }

        .field-hint {
            font-size: .68rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        .field-error {
            font-size: .72rem;
            color: #dc2626;
            margin-top: 4px;
        }

        /* ── Remember ── */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            cursor: pointer;
        }

        .remember-row input[type="checkbox"] {
            width: 17px;
            height: 17px;
            accent-color: #7c3aed;
            cursor: pointer;
            flex-shrink: 0;
        }

        .remember-row span {
            font-size: .82rem;
            color: #64748b;
        }

        /* ── Submit button ── */
        .btn-login {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #fff;
            font-size: .95rem;
            font-weight: 800;
            font-family: inherit;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            letter-spacing: .02em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 16px rgba(124, 58, 237, .35);
            transition: all .2s;
        }

        .btn-login:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: scale(.98);
        }

        /* ── Footer ── */
        .login-footer {
            text-align: center;
            font-size: .7rem;
            color: #94a3b8;
            margin: 20px 16px 0;
            padding-bottom: 24px;
        }

        /* ── Responsivitas untuk HP kecil ── */
        @media (max-width: 768px) {
            body,
            html {
                min-height: auto;
                height: auto;
            }

            .login-hero {
                height: auto;
                min-height: 150px;
                padding: 20px;
            }

            .hero-content {
                padding: 0;
            }

            .login-card {
                margin: 20px 16px 0;
            }
        }
    </style>
@endpush

@section('content')

    {{-- ── Hero Banner ── --}}
    <div class="login-hero">
        <div class="hero-content">
            <div class="hero-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="hero-title">{{ config('sekolah.nama', 'SMKN 5 Madiun') }}</h1>
            <p class="hero-sub">Sistem Informasi Sekolah</p>
        </div>
    </div>

    {{-- ── Login Card ── --}}
    <div class="login-card">
        <p class="login-card-title">Masuk ke Akun Anda</p>

        {{-- Error --}}
        @if ($errors->any())
            <div class="login-alert err">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        {{-- Status (misal setelah reset password) --}}
        @if (session('status'))
            <div class="login-alert ok">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" autocomplete="off">
            @csrf

            {{-- Nomor Induk --}}
            <div class="field-group">
                <label class="field-label" for="username">Nomor Induk</label>
                <div class="field-wrap">
                    <i class="fas fa-id-card field-icon"></i>
                    <input type="text" id="username" name="username"
                        class="field-input @error('username') is-invalid @enderror" value="{{ old('username') }}"
                        placeholder="NIS / NIP / NIK" autocomplete="username" required>
                </div>
                <div class="field-hint">NISN untuk siswa &nbsp;·&nbsp; NIP / NIK untuk guru &amp; staf</div>
                @error('username')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="field-group">
                <label class="field-label" for="password">Password</label>
                <div class="field-wrap">
                    <i class="fas fa-lock field-icon"></i>
                    <input type="password" id="password" name="password"
                        class="field-input @error('password') is-invalid @enderror" placeholder="Masukkan password"
                        autocomplete="current-password" required>
                    <button type="button" class="toggle-pw" onclick="togglePassword()" id="toggleBtn">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror
            </div>

            {{-- Remember Me --}}
            <label class="remember-row">
                <input type="checkbox" name="remember" id="remember">
                <span>Ingat saya di perangkat ini</span>
            </label>

            {{-- Submit --}}
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Masuk
            </button>

        </form>
    </div>

    {{-- Footer --}}
    <div class="login-footer">
        &copy; {{ date('Y') }} {{ config('sekolah.nama', 'SMKN 5 Madiun') }} &mdash; All rights reserved
    </div>

@endsection

@push('scripts')
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
        }
    </script>
@endpush
