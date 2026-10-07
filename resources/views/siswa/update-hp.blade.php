@extends('layouts.app')

@section('title', 'Update Nomor HP Siswa')

@push('styles')
    @include('components.izin-styles')
    <style>
        .upd-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .upd-card h3 {
            margin: 0 0 16px;
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: #6d28d9;
            transform: translateY(-1px);
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 14px 16px;
            font-size: .82rem;
            color: #1e40af;
            margin-bottom: 16px;
        }

        .info-box i {
            margin-right: 6px;
        }

        .col-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
        }

        .col-table th,
        .col-table td {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }

        .col-table th {
            background: #f1f5f9;
            font-weight: 700;
        }

        .col-table code {
            background: #f1f5f9;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: .78rem;
        }
    </style>
@endpush

@section('content')
    <div class="izin-wrap" style="padding-bottom: calc(var(--footer-h) + 24px);">

        {{-- Page Strip --}}
        <div class="page-strip" style="background: linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%);">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2><i class="fas fa-mobile-alt"></i> Update Nomor HP Siswa</h2>
            <p>Upload file Excel untuk memperbarui nomor HP & kelas siswa berdasarkan nama</p>
        </div>

        @if (session('error'))
            <div class="alert a-err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        {{-- Form Upload --}}
        <div class="upd-card">
            <h3><i class="fas fa-upload" style="color:#7c3aed; margin-right:8px;"></i> Upload File Excel</h3>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Proses pencocokan menggunakan <strong>nama siswa</strong>. Pastikan nama pada file Excel
                mendekati nama pada database. Sistem akan mengabaikan perbedaan huruf besar/kecil
                dan spasi berlebih.
            </div>

            <form action="{{ route('siswa.update-hp.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom:16px;">
                    <label for="file" class="form-label">File Excel (.xlsx / .xls) <span
                            style="color:#ef4444;">*</span></label>
                    <input type="file" name="file" id="file" class="form-input" accept=".xlsx,.xls" required>
                    <p style="margin:6px 0 0; font-size:.75rem; color:#64748b;">
                        Maksimal ukuran file: 5 MB.
                    </p>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-search"></i> Analisis &amp; Preview
                </button>
            </form>
        </div>

        {{-- Format Kolom --}}
        <div class="upd-card">
            <h3><i class="fas fa-table" style="color:#0891b2; margin-right:8px;"></i> Format Kolom yang Dibutuhkan</h3>
            <p style="font-size:.85rem; color:#374151; margin:0 0 12px;">
                File Excel harus memiliki header pada baris pertama dengan kolom berikut:
            </p>
            <table class="col-table">
                <thead>
                    <tr>
                        <th>Nama Kolom</th>
                        <th>Keterangan</th>
                        <th>Wajib?</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>nama_lengkap</code></td>
                        <td>Nama lengkap siswa (digunakan untuk pencocokan)</td>
                        <td>✅ Ya</td>
                    </tr>
                    <tr>
                        <td><code>kelas</code></td>
                        <td>Nama kelas (contoh: <code>X TKJ 1</code>)</td>
                        <td>Opsional</td>
                    </tr>
                    <tr>
                        <td><code>no_hp_siswa</code></td>
                        <td>Nomor HP siswa</td>
                        <td>Opsional</td>
                    </tr>
                    <tr>
                        <td><code>no_hp_ortu1</code></td>
                        <td>Nomor HP orang tua / wali</td>
                        <td>Opsional</td>
                    </tr>
                </tbody>
            </table>
            <p style="font-size:.75rem; color:#64748b; margin:10px 0 0;">
                Kolom lain (seperti <code>NO.</code>) akan diabaikan secara otomatis.
            </p>
        </div>

    </div>

    <a href="{{ route('siswa.index') }}" class="ab-btn ab-btn-back" style="margin-top:20px;">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Siswa
    </a>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                if (typeof Swal !== 'undefined' && !window.__swalValidationShown) {
                    window.__swalValidationShown = true;
                    const errorList = @json($errors->all());
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: '<ul style="padding:0;margin:0;list-style:none;text-align:left;">' +
                            errorList.map(m => '<li style="margin-bottom:4px;">• ' + m + '</li>').join('') +
                            '</ul>',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#ef4444',
                    });
                }
            @endif
        });
    </script>
@endpush
