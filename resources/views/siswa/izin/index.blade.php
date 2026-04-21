@extends('layouts.app')

@section('title', 'Pengajuan Izin')

@push('styles')
    @include('components.izin-styles')
@endpush

@section('content')
    <div class="izin-wrap" style="padding-bottom: calc(var(--footer-h) + 80px);">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-izin">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2>
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Pengajuan Izin
            </h2>
            <p>Kelola riwayat izin &amp; sakit Anda</p>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="alert a-ok">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Steps --}}
        <div class="steps">
            <div class="step done">
                <div class="step-dot">✓</div>
                <div class="step-lbl">Riwayat</div>
            </div>
            <div class="step active">
                <div class="step-dot">2</div>
                <div class="step-lbl">Ajukan Baru</div>
            </div>
        </div>

        {{-- Status Chips --}}
        <div class="status-bar">
            <div class="s-chip">
                <div class="ci ci-p"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="c-lbl">Menunggu</div>
                    <div class="c-val">{{ $pendingCount ?? 0 }}</div>
                </div>
            </div>
            <div class="s-chip">
                <div class="ci ci-g"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="c-lbl">Disetujui</div>
                    <div class="c-val">{{ $approvedCount ?? 0 }}</div>
                </div>
            </div>
            <div class="s-chip">
                <div class="ci ci-r"><i class="fas fa-times-circle"></i></div>
                <div>
                    <div class="c-lbl">Ditolak</div>
                    <div class="c-val">{{ $rejectedCount ?? 0 }}</div>
                </div>
            </div>
            <div class="s-chip">
                <div class="ci"><i class="fas fa-list"></i></div>
                <div>
                    <div class="c-lbl">Total</div>
                    <div class="c-val">{{ $allCount ?? 0 }}</div>
                </div>
            </div>
        </div>

        {{-- Izin List --}}
        @if ($izin->count() > 0)
            @foreach ($izin as $item)
                <div class="card izin-item">
                    <div class="c-head">
                        <div class="c-icon" style="background:var(--purple-fade, #ede9fe);">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <h3>{{ $item->jenis_label }}</h3>
                        </div>
                        <span class="hbadge badge-status {{ $item->status == 'disetujui' ? 'status-approved' : ($item->status == 'ditolak' ? 'status-rejected' : 'status-pending') }}">
                            {{ $item->status_label }}
                        </span>
                    </div>

                    <div class="c-body" style="padding:12px 16px 14px;">

                        {{-- Alasan --}}
                        <p style="color:var(--text-main);font-size:.85rem;line-height:1.55;margin:0 0 10px;">
                            {{ Str::limit($item->alasan, 120) }}
                        </p>

                        {{-- Meta row --}}
                        <div class="izin-meta">
                            <span class="meta-date">
                                <i class="fas fa-calendar-day"></i>
                                @if ($item->tanggal_mulai && $item->tanggal_sampai)
                                    {{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_sampai->format('d M Y') }}
                                @elseif ($item->tanggal_mulai)
                                    {{ $item->tanggal_mulai->format('d M Y') }}
                                @else
                                    Tanggal belum tersedia
                                @endif
                            </span>
                            @if ($item->verifier)
                                <span class="meta-verifier">
                                    <i class="fas fa-user-check" style="font-size:.65rem;"></i>
                                    {{ $item->verifier->name }}
                                </span>
                            @endif
                            @if ($item->bukti)
                                <span class="meta-bukti">
                                    <i class="fas fa-paperclip" style="font-size:.65rem;"></i> Bukti terlampir
                                </span>
                            @endif
                        </div>

                        {{-- ACTION BUTTONS ──────────────────────────── --}}
                        <div class="action-group">

                            {{-- Lihat detail (selalu tampil) --}}
                            <a href="{{ route('siswa.izin.show', $item) }}" class="action-btn btn-view">
                                <i class="fas fa-eye"></i> Lihat
                            </a>

                            {{-- Edit & Hapus hanya jika status masih diajukan --}}
                            @if ($item->status == 'diajukan')
                                <a href="{{ route('siswa.izin.edit', $item) }}" class="action-btn btn-edit">
                                    <i class="fas fa-pen"></i> Edit
                                </a>

                                <form id="delete-form-{{ $item->id }}" method="POST" action="{{ route('siswa.izin.destroy', $item) }}"
                                    style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="button" class="action-btn btn-delete" onclick="confirmDelete({{ $item->id }})">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Pagination --}}
            @if ($izin->hasPages())
                <div class="pagination-chips">
                    @if (!$izin->onFirstPage())
                        <a href="{{ $izin->previousPageUrl() }}" class="page-chip">← Sebelumnya</a>
                    @endif
                    <span class="page-chip active">{{ $izin->currentPage() }} / {{ $izin->lastPage() }}</span>
                    @if ($izin->hasMorePages())
                        <a href="{{ $izin->nextPageUrl() }}" class="page-chip">Berikutnya →</a>
                    @endif
                </div>
            @endif

        @else
            {{-- Empty state --}}
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-file-circle-xmark"></i>
                </div>
                <h3 class="empty-title">Belum ada pengajuan izin</h3>
                <p class="empty-text">Ajukan izin pertama Anda sekarang untuk sakit, pulang cepat, atau alasan lainnya.</p>
                <a href="{{ route('siswa.izin.create') }}" class="btn-sub">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Izin Pertama
                </a>
            </div>
        @endif

    </div>

    {{-- FAB: Tambah Izin — dengan jarak dari footer --}}
    <a href="{{ route('siswa.izin.create') }}" class="fab-add">
        <i class="fas fa-plus"></i>
        Ajukan Izin
    </a>
@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (typeof Swal === 'undefined') {
        if (confirm('Yakin hapus pengajuan izin ini? Tindakan tidak bisa dibatalkan.')) {
            document.getElementById('delete-form-' + id).submit();
        }
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: 'Yakin hapus pengajuan izin ini?<br><small style="color:#64748b;">Tindakan tidak bisa dibatalkan.</small>',
        icon: 'warning',
        showCancelButton: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            confirmButton: 'action-btn btn-delete',
            cancelButton: 'action-btn btn-view',
        },
        confirmButtonText: '<i class="fas fa-trash-alt"></i>&nbsp; Hapus',
        cancelButtonText: '<i class="fas fa-times"></i>&nbsp; Batal',
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}
</script>
@endpush
