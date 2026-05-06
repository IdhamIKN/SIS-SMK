@extends('layouts.app')

@section('title', 'Import Data GTK')

@push('styles')
    @include('components.izin-styles')
    <style>
        .import-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,.04);
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: var(--text-main, #0f172a);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid var(--border, #e2e8f0);
            border-radius: 10px;
            font-size: .875rem;
            font-family: inherit;
            color: var(--text-main, #0f172a);
            background: #f8fafc;
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
            box-sizing: border-box;
        }

        .form-input:focus {
            border-color: #7c3aed;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, .1);
        }

        .form-input[type="file"] {
            padding: 8px;
        }

        .btn-submit {
            padding: 12px 24px;
            background: #7c3aed;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
        }

        .btn-submit:hover {
            background: #6d28d9;
            transform: translateY(-1px);
        }

        .template-link {
            color: #0369a1;
            text-decoration: none;
            font-weight: 500;
        }

        .template-link:hover {
            text-decoration: underline;
        }
    </style>
@endpush

@section('content')
<div class="izin-wrap" style="padding-bottom: calc(var(--footer-h) + 20px);">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-izin" style="background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2><i class="fas fa-upload"></i> Import Data GTK</h2>
        <p>Upload file CSV untuk mengimport data guru & tenaga kependidikan</p>
    </div>

    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert a-err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="import-card">
        <h3 style="margin:0 0 16px; font-size:1.1rem; font-weight:700;">Upload File Data</h3>

        <form action="{{ route('gtk.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom:16px;">
                <label for="file" class="form-label">File CSV <span style="color:#ef4444;">*</span></label>
                <input type="file" name="file" id="file" class="form-input" accept=".csv" required>
                <p style="margin:8px 0 0; font-size:.75rem; color:#64748b;">
                    Pilih file CSV yang berisi data GTK. Pastikan format sesuai dengan template.
                </p>
            </div>

            <div style="margin-bottom:20px;">
                <p style="margin:0; font-size:.85rem; color:#374151;">
                    <i class="fas fa-info-circle" style="color:#0ea5e9;"></i>
                    Belum punya template? <a href="{{ route('gtk.template') }}" class="template-link" target="_blank">Download template CSV</a>
                </p>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-upload"></i> Import Data GTK
            </button>
        </form>
    </div>

    <div class="import-card">
        <h3 style="margin:0 0 16px; font-size:1.1rem; font-weight:700;">Format Data CSV</h3>
        <p style="margin:0 0 12px; font-size:.85rem; color:#374151;">
            File CSV harus memiliki kolom berikut (wajib ada header). User login akan dibuat otomatis dengan email <code>kd_guru@school.local</code> dan password default <code>password123</code>.
        </p>
        <div style="background:#f8fafc; padding:12px; border-radius:8px; font-family:monospace; font-size:.75rem; color:#374151; margin-bottom:12px;">
            kd_guru,nip,nik,nuptk,nama_lengkap,jenis_kelamin,no_hp,mata_pelajaran,jabatan,status_aktif,acc_absen,acc_kurikulum,acc_jurnal,acc_bk,guru_piket,acc_profil,group_acc,view_siswa
        </div>
        <ul style="font-size:.8rem; color:#4b5563; margin:0; padding-left:20px;">
            <li><strong>kd_guru</strong>: Kode guru (wajib, unik) - akan digunakan sebagai email login: <code>kd_guru@school.local</code></li>
            <li><strong>nip</strong>: Nomor Induk Pegawai (opsional)</li>
            <li><strong>nik</strong>: Nomor Induk Kependudukan (opsional, untuk login)</li>
            <li><strong>nuptk</strong>: Nomor Unik Pendidik (opsional)</li>
            <li><strong>nama_lengkap</strong>: Nama lengkap (wajib)</li>
            <li><strong>jenis_kelamin</strong>: L atau P</li>
            <li><strong>jabatan</strong>: Guru, Kepala Sekolah, dll</li>
            <li><strong>status_aktif</strong>: 1 (aktif) atau 0 (non aktif)</li>
            <li>Kolom akses (acc_*) dan view_siswa: 1 atau 0</li>
            <li><em>Catatan: Password default adalah <code>password123</code>. Harap ubah setelah import.</em></li>
        </ul>
    </div>

</div>

<a href="{{ route('gtk.index') }}" class="ab-btn ab-btn-back" style="margin-top:20px;">
    <i class="fas fa-arrow-left"></i> Kembali ke Daftar GTK
</a>
@endsection