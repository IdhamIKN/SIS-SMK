@extends('layouts.app')
@section('title', 'Edit Jurnal PKL')

@push('styles')
    @include('components.event-styles')
    <style>
        .form-wrap { padding-top:var(--header-h,56px); padding-bottom:calc(var(--footer-h,60px)+88px); max-width:640px; margin:0 auto; padding-left:12px; padding-right:12px; }
        .form-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:20px; margin-bottom:14px; }
        .form-card h3 { font-size:.9rem; font-weight:700; color:#0f172a; margin:0 0 16px; display:flex; align-items:center; gap:8px; }
        .form-group { margin-bottom:12px; }
        .form-group label { display:block; font-size:.8rem; font-weight:600; color:#374151; margin-bottom:5px; }
        .form-group label span { color:#ef4444; }
        .form-control { width:100%; padding:9px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:.875rem; font-family:inherit; color:#0f172a; background:#fff; box-sizing:border-box; }
        .form-control:focus { outline:2px solid #f59e0b; outline-offset:-1px; }
        .is-invalid { border-color:#ef4444; }
        .invalid-feedback { font-size:.72rem; color:#ef4444; margin-top:3px; }
        .hint { font-size:.72rem; color:#64748b; margin-top:3px; }
        .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .action-bar { position:fixed; bottom:var(--footer-h,0); left:0; right:0; padding:10px 12px 12px; background:rgba(255,255,255,.96); backdrop-filter:blur(10px); border-top:1px solid #e2e8f0; display:flex; gap:8px; z-index:999; }
        @media(min-width:768px){ .action-bar { padding:10px 24px 12px; justify-content:flex-end; } }
        .ab-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:12px 16px; border-radius:12px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; font-family:inherit; }
        @media(min-width:768px){ .ab-btn { flex:unset; min-width:120px; } }
        .ab-btn-amber { background:#f59e0b; color:#fff; }
        .ab-btn-back { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
    </style>
@endpush

@section('content')
<div class="form-wrap">
    <div class="page-strip page-strip-event">
        <div class="live-badge"><span class="live-dot"></span>PKL</div>
        <h2><i class="fas fa-pen"></i> Edit Jurnal PKL</h2>
        <p>{{ $jurnal->tanggal->translatedFormat('l, d F Y') }} — {{ $penugasan->lokasiPkl?->nama_tempat }}</p>
    </div>

    @if($jurnal->status_verifikasi === 'revisi' && $jurnal->catatan_pembimbing)
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-comment-alt"></i> <strong>Catatan pembimbing:</strong> {{ $jurnal->catatan_pembimbing }}
        </div>
    @endif

    <form method="POST" action="{{ route('siswa.pkl.jurnal.update', $jurnal) }}" enctype="multipart/form-data" id="jurnalForm">
        @csrf @method('PUT')

        <div class="form-card">
            <h3><i class="fas fa-calendar-day" style="color:#f59e0b;"></i> Info Kehadiran</h3>
            <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;margin-bottom:12px;font-size:.82rem;color:#475569;">
                <i class="fas fa-lock" style="margin-right:5px;color:#94a3b8;"></i>
                Tanggal tidak bisa diubah: <strong>{{ $jurnal->tanggal->translatedFormat('d F Y') }}</strong>
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Jam Datang</label>
                    <input type="time" name="jam_datang" class="form-control" value="{{ old('jam_datang', $jurnal->jam_datang ? \Carbon\Carbon::parse($jurnal->jam_datang)->format('H:i') : '') }}">
                </div>
                <div class="form-group">
                    <label>Jam Pulang</label>
                    <input type="time" name="jam_pulang" class="form-control" value="{{ old('jam_pulang', $jurnal->jam_pulang ? \Carbon\Carbon::parse($jurnal->jam_pulang)->format('H:i') : '') }}">
                </div>
            </div>
        </div>

        <div class="form-card">
            <h3><i class="fas fa-tasks" style="color:#6366f1;"></i> Kegiatan & Hasil</h3>
            <div class="form-group">
                <label>Uraian Kegiatan <span>*</span></label>
                <textarea name="kegiatan" class="form-control @error('kegiatan') is-invalid @enderror" rows="4" required>{{ old('kegiatan', $jurnal->kegiatan) }}</textarea>
                @error('kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Hasil / Output</label>
                <textarea name="hasil" class="form-control" rows="2">{{ old('hasil', $jurnal->hasil) }}</textarea>
            </div>
            <div class="form-group">
                <label>Kendala</label>
                <textarea name="kendala" class="form-control" rows="2">{{ old('kendala', $jurnal->kendala) }}</textarea>
            </div>
        </div>

        <div class="form-card">
            <h3><i class="fas fa-camera" style="color:#0ea5e9;"></i> Foto Dokumentasi</h3>
            @if($jurnal->foto)
                <img src="{{ Storage::url($jurnal->foto) }}" style="max-width:200px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:8px;display:block;" alt="Foto">
            @endif
            <div class="form-group" style="margin-bottom:0;">
                <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                <div class="hint">Kosongkan jika tidak ingin mengubah foto. Maks 3 MB.</div>
                @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </form>
</div>

<div class="action-bar">
    <a href="{{ route('siswa.pkl.jurnal.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
    <button type="submit" form="jurnalForm" class="ab-btn ab-btn-amber"><i class="fas fa-save"></i> Simpan Perubahan</button>
</div>
@endsection
