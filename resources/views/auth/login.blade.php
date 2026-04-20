@extends('layouts.guest')

@section('title', 'Login')

@section('content')

    {{-- Page Title --}}
    <div class="page-title page-title-small">
        <h2>Login</h2>
    </div>

    {{-- Header Card / Banner --}}
    <div class="card header-card shape-rounded" data-card-height="150">
        <div class="card-overlay bg-highlight opacity-95"></div>
        <div class="card-overlay dark-mode-tint"></div>
        <div class="card-bg preload-img" data-src="{{ asset('azures/images/pictures/20s.jpg') }}"></div>
    </div>

    {{-- Form Login --}}
    <div class="card card-style">
        <div class="content mt-4 mb-0">

            {{-- Nama Sekolah --}}
            <h4 class="text-center font-700 mb-1">{{ config('sekolah.nama', 'Sistem Informasi Sekolah') }}</h4>
            <p class="text-center font-12 color-highlight mb-4">Silakan login untuk melanjutkan</p>

            {{-- Error Messages --}}
            @if($errors->any())
                <div class="alert bg-red-dark color-white mb-3 rounded-s">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Session Status --}}
            @if(session('status'))
                <div class="alert bg-green-dark color-white mb-3 rounded-s">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
<div class="input-style has-borders has-icon validate-field mb-4">
                    <i class="fa fa-id-card"></i>
                    <input type="text"
                           name="username"
                           class="form-control @error('username') is-invalid @enderror"
                           id="username"
                           value="{{ old('username') }}"
                           placeholder="NIS / NIP"
                           autocomplete="username"
                           required>
                    <label for="username" class="color-highlight">Nomor Induk</label>
                    <i class="fa fa-times disabled invalid color-red-dark"></i>
                    <i class="fa fa-check disabled valid color-green-dark"></i>
                    <em>(NIS untuk siswa, NIP untuk guru/staf)</em>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="input-style has-borders has-icon validate-field mb-4">
                    <i class="fa fa-lock"></i>
                    <input type="password"
                           name="password"
                           class="form-control validate-password @error('password') is-invalid @enderror"
                           id="password"
                           placeholder="Password"
                           autocomplete="current-password"
                           required>
                    <label for="password" class="color-highlight">Password</label>
                    <i class="fa fa-times disabled invalid color-red-dark"></i>
                    <i class="fa fa-check disabled valid color-green-dark"></i>
                    <em>(wajib diisi)</em>
                </div>

                {{-- Remember Me --}}
                <div class="d-flex mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label font-12 opacity-70" for="remember">
                            Ingat saya
                        </label>
                    </div>
                </div>

                {{-- Tombol Login --}}
                <button type="submit"
                        class="btn btn-m btn-full bg-highlight rounded-s text-uppercase font-900 mt-2 mb-4">
                    <i class="fas fa-sign-in-alt me-2"></i> Masuk
                </button>

            </form>

            <div class="divider mb-0"></div>

            <div class="text-center py-3">
                <p class="font-11 opacity-50 mb-0">
                    &copy; {{ date('Y') }} {{ config('sekolah.nama', 'SIS SMKN 5 Madiun') }} — All rights reserved
                </p>
            </div>

        </div>
    </div>

@endsection