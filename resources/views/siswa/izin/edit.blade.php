@extends('layouts.app')

@section('title', 'Edit Pengajuan Izin #{{ $izin->id }}')

@push('styles')
    @include('components.izin-styles')
@endpush

@section('content')
@php
    $jenisFormValue = match ($izin->jenis) {
        'izin_sakit' => 'izin_sakit',
        'izin_pulang_cepat' => 'izin_pulang_cepat',
        'izin_terlambat' => 'izin_terlambat',
        default => 'izin_lainnya',
    };
@endphp
<div class="izin-wrap">
    {{-- Page Strip --}}
    <div class="page-strip page-strip-izin">
        <div class="live-badge">
            <span class="live-dot"></span>
            Edit #{{ $izin->id }} • {{ now()->translatedFormat('l, d F Y') }}
        </div>
        <h2>
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Edit Pengajuan Izin
        </h2>
        <p>Perbarui data pengajuan izin Anda (hanya untuk status menunggu)</p>
    </div>

    {{-- Alerts --}}
    @if ($errors->any())
        <div class="alert a-warn">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <strong>Silakan perbaiki:</strong>
                <ul style="margin: 4px 0 0 16px; font-size: .85rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Steps --}}
    <div class="steps">
        <div class="step active">
            <div class="step-dot">1</div>
            <div class="step-lbl">Jenis Izin</div>
        </div>
        <div class="step">
            <div class="step-dot">2</div>
            <div class="step-lbl">Tanggal</div>
        </div>
        <div class="step">
            <div class="step-dot">3</div>
            <div class="step-lbl">Alasan</div>
        </div>
        <div class="step">
            <div class="step-dot">4</div>
            <div class="step-lbl">Simpan</div>
        </div>
    </div>

    {{-- Form Card --}}
<form id="izinForm" method="POST" action="{{ route('siswa.izin.update', $izin) }}" enctype="multipart/form-data" data-izin-id="{{ $izin->id }}">
        @csrf
        @method('PUT')
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background: var(--purple-fade);">
                    <i class="fas fa-edit"></i>
                </div>
                <h3>Edit Detail Pengajuan</h3>
                <span class="hbadge">Status: {{ $izin->status_label }}</span>
            </div>
            <div class="c-body" style="padding: 20px;">

                {{-- Jenis --}}
                <div class="mb-4">
                    <label style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">
                        Jenis Izin <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="jenis" id="jenisSelect" class="form-select" style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem; transition: border-color .2s; background: var(--card);" required>
                        <option value="">Pilih jenis izin</option>
                        <option value="izin_sakit" {{ old('jenis', $jenisFormValue) == 'izin_sakit' ? 'selected' : '' }}>Izin Sakit</option>
                        <option value="izin_pulang_cepat" {{ old('jenis', $jenisFormValue) == 'izin_pulang_cepat' ? 'selected' : '' }}>Izin Pulang Cepat</option>
                        <option value="izin_terlambat" {{ old('jenis', $jenisFormValue) == 'izin_terlambat' ? 'selected' : '' }}>Izin Terlambat</option>
                        <option value="izin_lainnya" {{ old('jenis', $jenisFormValue) == 'izin_lainnya' ? 'selected' : '' }}>Izin Lainnya</option>
                    </select>
                </div>

                {{-- Date Section --}}
                <div class="date-section mb-4" id="dateSection">
                    {{-- Single Date --}}
                    <div id="singleDate" class="date-group" style="{{ $izin->isRangeJenis() ? 'display: none;' : '' }}">
                        <label style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">
                            Tanggal Izin <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="tanggal_izin" id="tanggalInput" class="form-control" 
                               value="{{ old('tanggal_izin', $izin->tanggal_izin_form) }}" min="{{ now()->format('Y-m-d') }}" 
                               style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem; transition: border-color .2s; background: var(--card);">
                    </div>

                    {{-- Range Date --}}
                    <div id="rangeDate" class="date-group" style="{{ $izin->isRangeJenis() ? 'display: block;' : 'display: none;' }}">
                        <label style="font-weight: 600; color: var(--text-main); margin-bottom: 12px; display: block;">
                            Rentang Tanggal Izin <span style="color: #ef4444;">*</span>
                        </label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <label style="font-size: .85rem; color: var(--text-muted); display: block; margin-bottom: 4px;">Mulai</label>
                                <input type="date" name="tanggal_mulai" id="tanggalMulaiInput" class="form-control" 
                                       value="{{ old('tanggal_mulai', $izin->tanggal_mulai_form) }}" min="{{ now()->format('Y-m-d') }}" 
                                       style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem; transition: border-color .2s; background: var(--card);">
                            </div>
                            <div>
                                <label style="font-size: .85rem; color: var(--text-muted); display: block; margin-bottom: 4px;">Sampai</label>
                                <input type="date" name="tanggal_sampai" id="tanggalSampaiInput" class="form-control" 
                                       value="{{ old('tanggal_sampai', $izin->tanggal_sampai_form) }}" min="{{ now()->format('Y-m-d') }}" 
                                       style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem; transition: border-color .2s; background: var(--card);">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Alasan --}}
                <div style="margin-bottom: 24px;">
                    <label style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">
                        Alasan <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="alasan" id="alasanInput" rows="5" class="form-control" 
                              style="border: 2px solid var(--border); border-radius: 12px; padding: 16px; font-size: .9rem; line-height: 1.5; resize: vertical; transition: border-color .2s; background: var(--card);" required>{{ old('alasan', $izin->alasan) }}</textarea>
                    <div style="font-size: .75rem; color: var(--text-muted); margin-top: 6px;">
                        Maksimal 500 karakter
                    </div>
                </div>

                {{-- Bukti --}}
                <div class="mb-4">
                    <label style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">
                        Bukti Izin {{ $jenisFormValue == 'izin_pulang_cepat' ? '(Opsional)' : '' }}
                    </label>
                    @if($izin->bukti)
                    <div style="border: 2px solid var(--border); border-radius: 12px; padding: 16px; text-align: center; margin-bottom: 12px;">
                        <img src="{{ Storage::url($izin->bukti) }}" style="max-width: 100%; max-height: 200px; border-radius: 8px; border: 1px solid var(--border);" alt="Bukti Saat Ini">
                        <div style="font-size: .8rem; color: var(--text-muted); margin-top: 8px;">
                            Bukti saat ini akan diganti jika upload baru
                        </div>
                    </div>
                    @endif
                    <input type="file" name="bukti" accept="image/jpeg,image/jpg,image/png" class="form-control" style="border: 2px solid var(--border); border-radius: 12px; padding: 12px 16px; font-size: .95rem;">
                    <div style="font-size: .75rem; color: var(--text-muted); margin-top: 6px;">
                        JPG/PNG max 10MB - Akan dikompres otomatis
                    </div>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn-sub" style="flex: 1;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Perubahan
                    </button>
<a href="{{ route('siswa.izin.show', $izin) }}" class="btn-izin btn-izin-secondary" style="padding: 14px 24px; flex: 1; text-align: center;">
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Back --}}
    {{-- <a href="{{ route('siswa.izin.index') }}" class="btn-izin btn-izin-secondary" style="position: fixed; top: 24px; left: 24px; z-index: 1000;">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Riwayat
    </a> --}}
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('izinForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    const jenisSelect = document.getElementById('jenisSelect');
    const singleDate = document.getElementById('singleDate');
    const rangeDate = document.getElementById('rangeDate');
    const tanggalInput = document.getElementById('tanggalInput');
    const tanggalMulaiInput = document.getElementById('tanggalMulaiInput');
    const tanggalSampaiInput = document.getElementById('tanggalSampaiInput');

    function handleJenisChange(resetValues = false) {
        const jenis = jenisSelect.value;
        const isRange = jenis === 'izin_sakit' || jenis === 'izin_lainnya';

        singleDate.style.display = isRange ? 'none' : 'block';
        rangeDate.style.display = isRange ? 'block' : 'none';

        if (resetValues) {
            if (isRange) {
                tanggalInput.value = '';
            } else {
                tanggalMulaiInput.value = '';
                tanggalSampaiInput.value = '';
            }
        }
    }

    jenisSelect.addEventListener('change', function() {
        handleJenisChange(true);
    });
    handleJenisChange();

    let confirmedSubmit = false;

    form.addEventListener('submit', function(e) {
        if (confirmedSubmit) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            return;
        }

        e.preventDefault();

        if (typeof Swal === 'undefined') {
            if (confirm('Simpan perubahan pengajuan izin ini?')) {
                confirmedSubmit = true;
                form.requestSubmit();
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
            return;
        }

        Swal.fire({
            title: 'Simpan perubahan?',
            html: 'Pastikan data izin yang Anda ubah sudah benar sebelum disimpan.',
            icon: 'question',
            showCancelButton: true,
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn-sub',
                cancelButton: 'btn-izin btn-izin-secondary',
            },
            confirmButtonText: '<i class="fas fa-check"></i>&nbsp; Ya, simpan',
            cancelButtonText: '<i class="fas fa-times"></i>&nbsp; Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                confirmedSubmit = true;
                form.requestSubmit();
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    });
});
</script>
@endpush

@endsection
