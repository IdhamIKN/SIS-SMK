@extends('layouts.app')

@section('title', 'Import Data Siswa')

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
    <div class="page-strip page-strip-izin" style="background: linear-gradient(135deg, #10b981 0%, #34d399 100%);">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2><i class="fas fa-upload"></i> Import Data Siswa</h2>
        <p>Upload file CSV untuk mengimport data siswa</p>
    </div>

    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert a-err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    <div class="import-card">
        <h3 style="margin:0 0 16px; font-size:1.1rem; font-weight:700;">Upload File Data</h3>

        <form action="{{ route('siswa.import.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom:16px;">
                <label for="file" class="form-label">File CSV <span style="color:#ef4444;">*</span></label>
                <input type="file" name="file" id="file" class="form-input" accept=".csv" required>
                <p style="margin:8px 0 0; font-size:.75rem; color:#64748b;">
                    Pilih file CSV yang berisi data siswa. Pastikan format sesuai dengan template.
                </p>
            </div>

            <div style="margin-bottom:20px;">
                <p style="margin:0; font-size:.85rem; color:#374151;">
                    <i class="fas fa-info-circle" style="color:#10b981;"></i>
                    Belum punya template? <a href="{{ route('siswa.template') }}" class="template-link" target="_blank">Download template CSV</a>
                </p>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-upload"></i> Import Data Siswa
            </button>
        </form>
    </div>

    <div class="import-card">
        <h3 style="margin:0 0 16px; font-size:1.1rem; font-weight:700;">Format Data CSV</h3>
        <p style="margin:0 0 12px; font-size:.85rem; color:#374151;">
            File CSV harus memiliki kolom berikut (wajib ada header). User login akan dibuat otomatis dengan email <code>nisn@smkn5madiun.sch.id</code> dan password default <code>password123</code>.
        </p>
        <div style="background:#f8fafc; padding:12px; border-radius:8px; font-family:monospace; font-size:.75rem; color:#374151; margin-bottom:12px;">
            nisn,nama_lengkap,jenis_kelamin,nama_kelas,nis,angkatan,tempat_lahir,tanggal_lahir,alamat,no_hp_siswa,no_hp_ortu1,no_hp_ortu2,nama_ortu1,nama_ortu2,nama_wali,noreg,status_aktif
        </div>
        <ul style="font-size:.8rem; color:#4b5563; margin:0; padding-left:20px;">
            <li><strong>nisn</strong>: Nomor Induk Siswa Nasional (wajib, unik)</li>
            <li><strong>nama_lengkap</strong>: Nama lengkap siswa (wajib)</li>
            <li><strong>jenis_kelamin</strong>: L (Laki-laki) atau P (Perempuan) - wajib</li>
            <li><strong>nama_kelas</strong>: Nama kelas (opsional, harus sesuai dengan data kelas yang ada)</li>
            <li><strong>nis</strong>: Nomor Induk Siswa (opsional)</li>
            <li><strong>angkatan</strong>: Tahun angkatan (opsional)</li>
            <li><strong>tempat_lahir</strong>: Tempat lahir (opsional)</li>
            <li><strong>tanggal_lahir</strong>: Tanggal lahir dalam format YYYY-MM-DD (opsional)</li>
            <li><strong>alamat</strong>: Alamat lengkap (opsional)</li>
            <li><strong>no_hp_siswa</strong>: Nomor HP siswa (opsional)</li>
            <li><strong>no_hp_ortu1</strong>: Nomor HP orang tua 1 (opsional)</li>
            <li><strong>no_hp_ortu2</strong>: Nomor HP orang tua 2 (opsional)</li>
            <li><strong>nama_ortu1</strong>: Nama orang tua 1 (opsional)</li>
            <li><strong>nama_ortu2</strong>: Nama orang tua 2 (opsional)</li>
            <li><strong>nama_wali</strong>: Nama wali (opsional)</li>
            <li><strong>noreg</strong>: Nomor registrasi legacy (opsional)</li>
            <li><strong>status_aktif</strong>: Status aktif (1 untuk aktif, 0 untuk tidak aktif) - default 1</li>
            <li><em>Catatan: Password default adalah <code>password123</code>. Harap ubah setelah import.</em></li>
        </ul>
    </div>

</div>

<a href="{{ route('siswa.index') }}" class="ab-btn ab-btn-back" style="margin-top:20px;">
    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Siswa
</a>
@endsection