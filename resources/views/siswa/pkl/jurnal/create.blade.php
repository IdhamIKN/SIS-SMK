@extends('layouts.app')
@section('title', 'Isi Jurnal PKL')

@push('styles')
    @include('components.event-styles')
    <style>
        .form-wrap {
            padding-top: var(--header-h, 56px);
            padding-bottom: calc(var(--footer-h, 60px)+88px);
            max-width: 640px;
            margin: 0 auto;
            padding-left: 12px;
            padding-right: 12px;
        }

        .form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 14px;
        }

        .form-card h3 {
            font-size: .9rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-group label span {
            color: #ef4444;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: 2px solid #f59e0b;
            outline-offset: -1px;
        }

        .is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            font-size: .72rem;
            color: #ef4444;
            margin-top: 3px;
        }

        .hint {
            font-size: .72rem;
            color: #64748b;
            margin-top: 3px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 0);
            left: 0;
            right: 0;
            padding: 10px 12px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                justify-content: flex-end;
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 120px;
            }
        }

        .ab-btn-amber {
            background: #f59e0b;
            color: #fff;
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="form-wrap">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>PKL</div>
            <h2><i class="fas fa-book"></i> Jurnal Harian PKL</h2>
            <p>Catat kegiatan yang kamu lakukan hari ini di {{ $penugasan->lokasiPkl?->nama_tempat }}.</p>
        </div>

        @php
            $absenHariIni = \App\Models\AbsenSiswa::where('siswa_id', auth()->user()->siswa?->id)
                ->whereDate('tanggal', today())
                ->whereNotNull('jam_masuk')
                ->first();
        @endphp

        @if (!$absenHariIni)
            <div
                style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-exclamation-triangle"></i> <strong>Kamu belum absen masuk PKL hari ini.</strong>
                Jurnal hanya bisa diisi setelah absen masuk.
                <a href="{{ route('siswa.pkl.absen.index') }}"
                    style="display:inline-flex;align-items:center;gap:4px;margin-top:8px;padding:7px 14px;background:#dc2626;color:#fff;border-radius:8px;font-size:.78rem;font-weight:700;text-decoration:none;">
                    <i class="fas fa-sign-in-alt"></i> Absen Masuk Dulu
                </a>
            </div>
        @endif

        @if ($jurnalHariIni && $jurnalHariIni->status_verifikasi !== 'revisi')
            <div
                style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-info-circle"></i> Kamu sudah mengisi jurnal hari ini. Mengisi form ini akan memperbarui
                jurnal yang ada.
            </div>
        @endif

        @if ($jurnalHariIni?->status_verifikasi === 'revisi')
            <div
                style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-comment-alt"></i> <strong>Catatan pembimbing:</strong>
                {{ $jurnalHariIni->catatan_pembimbing ?? '-' }}
            </div>
        @endif

        <form method="POST" action="{{ route('siswa.pkl.jurnal.store') }}" enctype="multipart/form-data" id="jurnalForm">
            @csrf

            <div class="form-card">
                <h3><i class="fas fa-calendar-day" style="color:#f59e0b;"></i> Info Kehadiran</h3>
                <div class="form-group">
                    <label>Tanggal <span>*</span></label>
                    <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror"
                        value="{{ old('tanggal', now()->toDateString()) }}"
                        min="{{ $penugasan->tanggal_mulai->toDateString() }}"
                        max="{{ $penugasan->tanggal_selesai->toDateString() }}" required>
                    @error('tanggal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Jam Datang</label>
                        <input type="time" name="jam_datang" class="form-control"
                            value="{{ old('jam_datang', $jurnalHariIni?->jam_datang ? \Carbon\Carbon::parse($jurnalHariIni->jam_datang)->format('H:i') : '') }}">
                    </div>
                    <div class="form-group">
                        <label>Jam Pulang</label>
                        <input type="time" name="jam_pulang" class="form-control"
                            value="{{ old('jam_pulang', $jurnalHariIni?->jam_pulang ? \Carbon\Carbon::parse($jurnalHariIni->jam_pulang)->format('H:i') : '') }}">
                    </div>
                </div>
            </div>

            <div class="form-card">
                <h3><i class="fas fa-tasks" style="color:#6366f1;"></i> Kegiatan & Hasil</h3>
                <div class="form-group">
                    <label>Uraian Kegiatan <span>*</span></label>
                    <textarea name="kegiatan" class="form-control @error('kegiatan') is-invalid @enderror" rows="4"
                        placeholder="Tuliskan kegiatan yang kamu lakukan hari ini..." required>{{ old('kegiatan', $jurnalHariIni?->kegiatan) }}</textarea>
                    @error('kegiatan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label>Hasil / Output</label>
                    <textarea name="hasil" class="form-control" rows="2"
                        placeholder="Apa yang berhasil kamu pelajari atau selesaikan?">{{ old('hasil', $jurnalHariIni?->hasil) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Kendala</label>
                    <textarea name="kendala" class="form-control" rows="2" placeholder="Kendala yang dihadapi hari ini (jika ada)...">{{ old('kendala', $jurnalHariIni?->kendala) }}</textarea>
                </div>
            </div>

            <div class="form-card">
                <h3><i class="fas fa-camera" style="color:#0ea5e9;"></i> Foto Dokumentasi</h3>
                @if ($jurnalHariIni?->foto)
                    <img src="{{ Storage::url($jurnalHariIni->foto) }}"
                        style="max-width:200px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:8px;display:block;"
                        alt="Foto">
                @endif
                <div class="form-group" style="margin-bottom:0;">
                    <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror"
                        accept="image/*">
                    <div class="hint">JPG/PNG maks 3 MB. Kosongkan jika tidak ingin mengubah foto.</div>
                    @error('foto')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </form>
    </div>

    <div class="action-bar">
        <a href="{{ route('siswa.pkl.dashboard') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
        <button type="submit" form="jurnalForm" class="ab-btn ab-btn-amber"><i class="fas fa-save"></i> Simpan
            Jurnal</button>
    </div>
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
