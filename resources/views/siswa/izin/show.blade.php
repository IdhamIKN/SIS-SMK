@extends('layouts.app')

@section('title', 'Detail Pengajuan Izin #{{ $izin->id }}')

{{-- ═══════════════════════════════════════════════════════════
     SEMUA @push('styles') HARUS DI LUAR @section('content')
     Jika diletakkan di dalam @section, Blade tidak akan
     memprosesnya sebagai stack — CSS akan bocor ke halaman.
     ═══════════════════════════════════════════════════════════ --}}
@push('styles')
    @include('components.izin-styles')
@endpush

@section('content')
<div class="izin-wrap">

    {{-- Page Strip --}}
    <div class="page-strip {{ $izin->status == 'disetujui' ? 'page-strip-approved' : ($izin->status == 'ditolak' ? 'page-strip-rejected' : 'page-strip-izin') }}">
        <div class="live-badge">
            <span class="live-dot"></span>
            #{{ $izin->id }} &bull; {{ $izin->created_at->format('d/m/Y H:i') }}
        </div>
        <h2>
            <i class="fas fa-file-circle-check"></i>
            {{ $izin->jenis_label }}
        </h2>
        <p>
            {{ $izin->status_label }}
            @if ($izin->tanggal_izin)
                &bull; {{ $izin->tanggal_izin->format('d F Y') }}
            @endif
        </p>
    </div>

    {{-- Status Chips --}}
    <div class="status-bar">
        <div class="s-chip">
            <div class="ci ci-p"><i class="fas fa-calendar-day"></i></div>
            <div>
                <div class="c-lbl">Tanggal Izin</div>
                <div class="c-val">
                    @if ($izin->tanggal_mulai && $izin->tanggal_sampai)
                        {{ $izin->tanggal_mulai->format('d M Y') }} - {{ $izin->tanggal_sampai->format('d M Y') }}
                    @elseif ($izin->tanggal_izin)
                        {{ $izin->tanggal_izin->format('d M Y') }}
                    @else
                        Tanggal belum tersedia
                    @endif
                </div>
            </div>
        </div>
        <div class="s-chip">
            <div class="ci {{ $izin->status == 'disetujui' ? 'ci-g' : ($izin->status == 'ditolak' ? 'ci-r' : 'ci-p') }}">
                <i class="fas fa-{{ $izin->status == 'disetujui' ? 'check-circle' : ($izin->status == 'ditolak' ? 'times-circle' : 'clock') }}"></i>
            </div>
            <div>
                <div class="c-lbl">Status</div>
                <div class="c-val">{{ $izin->status_label }}</div>
            </div>
        </div>
    </div>

    {{-- Alasan & Bukti --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:var(--purple-fade,#ede9fe);">
                <i class="fas fa-align-left"></i>
            </div>
            <h3>Alasan Pengajuan</h3>
            <span class="hbadge">Detail lengkap</span>
        </div>
        <div class="c-body" style="padding:16px 18px;">
            <p style="font-size:.9rem;color:var(--text-main);line-height:1.65;white-space:pre-wrap;margin:0;">{{ $izin->alasan }}</p>
            @if ($izin->bukti)
                <div style="margin-top:14px;">
                    <div style="font-size:.78rem;font-weight:600;color:var(--text-muted);margin-bottom:8px;">
                        <i class="fas fa-paperclip"></i> Bukti Terlampir
                    </div>
                    <img src="{{ Storage::url($izin->bukti) }}"
                        style="max-width:100%;max-height:280px;border-radius:10px;border:1px solid var(--border,#e2e8f0);display:block;"
                        alt="Bukti Izin">
                </div>
            @endif
        </div>
    </div>

    {{-- Verifikasi --}}
    @if ($izin->verifier || $izin->waktu_verifikasi)
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="{{ $izin->status == 'disetujui' ? 'background:#dcfce7;' : ($izin->status == 'ditolak' ? 'background:#fee2e2;' : 'background:#fef9c3;') }}">
                    <i class="fas fa-user-check"></i>
                </div>
                <h3>Informasi Verifikasi</h3>
            </div>
            <div class="c-body" style="padding:14px 18px;">
                @if ($izin->verifier)
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="verifier-avatar"><i class="fas fa-user-tie"></i></div>
                        <div>
                            <div style="font-weight:700;font-size:.9rem;color:var(--text-main);">{{ $izin->verifier->name }}</div>
                            <div style="font-size:.75rem;color:var(--text-muted);">{{ $izin->waktu_verifikasi?->format('d M Y, H:i') }}</div>
                        </div>
                        <div style="margin-left:auto;">
                            <span style="font-size:.72rem;font-weight:700;padding:4px 10px;border-radius:20px;{{ $izin->status == 'disetujui' ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' }}">
                                {{ $izin->status_label }}
                            </span>
                        </div>
                    </div>
                @else
                    <p style="color:var(--text-muted);font-size:.85rem;margin:0;">
                        <i class="fas fa-hourglass-half"></i> Menunggu verifikasi admin
                    </p>
                @endif
            </div>
        </div>
    @endif

    {{-- Info Siswa --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#dbeafe;"><i class="fas fa-user-graduate"></i></div>
            <h3>Informasi Siswa</h3>
        </div>
        <div class="c-body" style="padding:14px 18px;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
                @if ($izin->siswa->foto)
                    <img src="{{ $izin->siswa->foto }}" width="46" height="46"
                        style="border-radius:50%;object-fit:cover;border:2px solid var(--border,#e2e8f0);" alt="Foto Siswa">
                @else
                    <div class="siswa-avatar">{{ substr($izin->siswa->nama_lengkap, 0, 1) }}</div>
                @endif
                <div>
                    <div style="font-weight:700;font-size:.95rem;color:var(--text-main);">{{ $izin->siswa->nama_lengkap }}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);">{{ $izin->siswa->nis }}</div>
                </div>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-chalkboard-user" style="width:14px;"></i> Kelas</span>
                <span class="dr-value">{{ $izin->siswa->kelas->nama_kelas ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-mobile-screen-button" style="width:14px;"></i> HP Siswa</span>
                <span class="dr-value">{{ $izin->siswa->no_hp_siswa }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-phone" style="width:14px;"></i> HP Orang Tua</span>
                <span class="dr-value">{{ $izin->siswa->no_hp_ortu1 }}</span>
            </div>
        </div>
    </div>

</div>

{{-- ACTION BAR —  fixed tepat di atas bottom navbar --}}
<div class="action-bar">
    @if ($izin->status == 'diajukan')
        <a href="{{ route('siswa.izin.edit', $izin) }}" class="ab-btn ab-btn-edit">
            <i class="fas fa-pen"></i> Edit
        </a>
        <form id="frmDelete" method="POST" action="{{ route('siswa.izin.destroy', $izin) }}">
            @csrf @method('DELETE')
            <button type="button" class="ab-btn ab-btn-delete" onclick="confirmDelete()">
                <i class="fas fa-trash-alt"></i> Hapus
            </button>
        </form>
    @else
        <a href="{{ route('siswa.izin.index') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
        </a>
    @endif
</div>

@endsection

{{-- ═══════════════════════════════════════════════════════
     @push('scripts') JUGA harus di luar @section('content')
     ═══════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
@if (session('success'))
Swal.fire({
    title: 'Berhasil',
    text: @json(session('success')),
    icon: 'success',
    confirmButtonColor: '#10b981'
});
@endif

function confirmDelete() {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: 'Yakin hapus pengajuan izin ini?<br><small style="color:#64748b;">Tindakan tidak bisa dibatalkan.</small>',
        icon: 'warning',
        showCancelButton: true,
        reverseButtons: true,

        // Pakai buttonsStyling:false lalu terapkan class kustom
        buttonsStyling: false,
        customClass: {
            confirmButton: 'ab-btn ab-btn-delete',
            cancelButton : 'ab-btn ab-btn-back',
        },

        confirmButtonText: '<i class="fas fa-trash-alt"></i>&nbsp; Hapus',
        cancelButtonText : '<i class="fas fa-times"></i>&nbsp; Batal',
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('frmDelete').submit();
        }
    });
}
</script>
@endpush
