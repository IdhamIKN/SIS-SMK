@extends('layouts.app')

@section('title', 'Tahun Ajaran')

@push('styles')
@include('components.izin-styles')
<style>
/* ── Page strip ── */
.page-strip {
    padding: 20px 20px 28px; position: relative; overflow: hidden;
}
.page-strip-ta {
    background: linear-gradient(135deg, #10b981 0%, #059669 60%, #047857 100%);
}
.page-strip::before {
    content: ''; position: absolute; top: -40px; right: -40px;
    width: 140px; height: 140px; background: rgba(255,255,255,.07); border-radius: 50%;
}
.page-strip::after {
    content: ''; position: absolute; bottom: -24px; left: -20px;
    width: 100px; height: 100px; background: rgba(255,255,255,.05); border-radius: 50%;
}
.live-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,.15); backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.2);
    padding: 3px 10px; border-radius: 20px;
    font-size: .7rem; font-weight: 600; color: rgba(255,255,255,.9);
    margin-bottom: 10px; position: relative; z-index: 1;
}
.live-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #a7f3d0; display: inline-block; animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1}50%{opacity:.4} }
.page-strip h2 {
    font-size: 1.3rem; font-weight: 800; color: #fff; margin: 0 0 4px;
    position: relative; z-index: 1; display: flex; align-items: center; gap: 8px;
}
.page-strip p { font-size: .8rem; color: rgba(255,255,255,.7); margin: 0; position: relative; z-index: 1; }

/* ── Alert ── */
.alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 12px; font-size: .84rem; margin: 12px 16px 0; }
.a-ok  { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
.a-err { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

/* ── Academic year cards ── */
.ta-list { padding: 0 16px; }
.ta-item {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
    margin-bottom: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden;
}
.ta-item.active { border-color: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,.1); }
.ta-head {
    display: flex; align-items: center; gap: 12px; padding: 14px 16px 10px;
}
.ta-icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: #dcfce7; color: #15803d;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.ta-icon.active { background: #10b981; color: #fff; }
.ta-info { flex: 1; min-width: 0; }
.ta-name {
    font-size: .9rem; font-weight: 700; color: #0f172a;
    margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.ta-detail { font-size: .72rem; color: #64748b; margin: 0; }
.ta-status {
    font-size: .68rem; font-weight: 700; padding: 3px 9px;
    border-radius: 20px; flex-shrink: 0;
}
.ta-status.active  { background: #dcfce7; color: #15803d; }
.ta-status.inactive { background: #f1f5f9; color: #64748b; }
.ta-meta {
    display: flex; flex-wrap: wrap; gap: 8px;
    font-size: .75rem; color: #64748b; padding: 0 16px;
}
.ta-meta span { display: inline-flex; align-items: center; gap: 4px; }
.ta-actions { display: flex; gap: 8px; padding: 10px 16px 14px; }
.ta-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 12px; border-radius: 8px; font-size: .78rem; font-weight: 600;
    border: none; cursor: pointer; text-decoration: none; font-family: inherit;
    transition: all .18s; white-space: nowrap;
}
.ta-btn:active { transform: scale(.96); }
.ta-btn.view   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.ta-btn.edit   { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.ta-btn.primary { background: #10b981; color: #fff; box-shadow: 0 3px 12px rgba(16,185,129,.3); }
.ta-btn.primary:hover { filter: brightness(1.08); }

/* ── Pagination ── */
.ta-pager {
    display: flex; align-items: center; justify-content: center;
    gap: 8px; padding: 12px 16px 0;
}
.ta-page-chip {
    padding: 7px 14px; border-radius: 20px; font-size: .78rem; font-weight: 700;
    background: #fff; border: 1px solid #e2e8f0; color: #475569; text-decoration: none;
}
.ta-page-chip.active { background: #10b981; color: #fff; border-color: #10b981; }

/* ── Empty ── */
.ta-empty { padding: 40px 20px; text-align: center; }
.ta-empty-ico { font-size: 3rem; color: #a7f3d0; margin-bottom: 12px; }
.ta-empty h3  { font-size: 1rem; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
.ta-empty p   { font-size: .84rem; color: #64748b; margin-bottom: 16px; }
.ta-btn-start {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 11px 20px; background: #10b981; color: #fff;
    border-radius: 12px; font-size: .85rem; font-weight: 700;
    text-decoration: none; transition: filter .18s;
}
.ta-btn-start:hover { filter: brightness(1.08); }

/* ── FAB ── */
.ta-fab {
    position: fixed; bottom: calc(var(--footer-h, 60px) + 16px); right: 16px;
    background: #10b981; color: #fff;
    padding: 13px 20px; border-radius: 50px;
    font-size: .875rem; font-weight: 700;
    display: inline-flex; align-items: center; gap: 7px;
    text-decoration: none; box-shadow: 0 4px 20px rgba(16,185,129,.35);
    z-index: 100; transition: all .2s;
}
.ta-fab:hover { filter: brightness(1.08); transform: translateY(-1px); }
.ta-fab:active { transform: scale(.97); }

.ta-wrap { padding-bottom: calc(var(--footer-h) + 80px); }
</style>
@endpush

@section('content')
<div class="ta-wrap">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-ta">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ now()->translatedFormat('d F Y') }}
        </div>
        <h2><i class="fas fa-calendar-alt"></i> Tahun Ajaran</h2>
        <p>Kelola tahun ajaran dan promosi siswa</p>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert a-err"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- Academic Years List --}}
    @if ($academicYears->count() > 0)
        <div class="ta-list">
            @foreach ($academicYears as $academicYear)
                <div class="ta-item {{ $academicYear->is_active ? 'active' : '' }}">
                    <div class="ta-head">
                        <div class="ta-icon {{ $academicYear->is_active ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="ta-info">
                            <h3 class="ta-name">{{ $academicYear->name }}</h3>
                            <p class="ta-detail">
                                {{ $academicYear->year_start }} - {{ $academicYear->year_end }}
                                @if($academicYear->promotion_deadline)
                                    • Deadline: {{ $academicYear->promotion_deadline->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                        <span class="ta-status {{ $academicYear->is_active ? 'active' : 'inactive' }}">
                            {{ $academicYear->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                    <div class="ta-meta">
                        <span><i class="fas fa-school"></i> {{ $academicYear->classes()->count() }} Kelas</span>
                        <span><i class="fas fa-users"></i> {{ $academicYear->classes->sum('siswa_count') }} Siswa</span>
                        @if($academicYear->promotion_waves)
                            <span><i class="fas fa-arrow-up"></i> {{ count($academicYear->promotion_waves) }} Gelombang</span>
                        @endif
                    </div>
                    <div class="ta-actions">
                        <a href="{{ route('academic-years.show', $academicYear) }}" class="ta-btn view">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        <a href="{{ route('academic-years.edit', $academicYear) }}" class="ta-btn edit">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        @if(!$academicYear->is_active)
                            <form action="{{ route('academic-years.set-active', $academicYear) }}" method="POST" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="ta-btn primary">
                                    <i class="fas fa-check"></i> Aktifkan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if ($academicYears->hasPages())
            <div class="ta-pager">
                @if (!$academicYears->onFirstPage())
                    <a href="{{ $academicYears->previousPageUrl() }}" class="ta-page-chip">← Sebelumnya</a>
                @endif
                <span class="ta-page-chip active">{{ $academicYears->currentPage() }} / {{ $academicYears->lastPage() }}</span>
                @if ($academicYears->hasMorePages())
                    <a href="{{ $academicYears->nextPageUrl() }}" class="ta-page-chip">Berikutnya →</a>
                @endif
            </div>
        @endif

    @else
        <div class="ta-empty">
            <div class="ta-empty-ico"><i class="fas fa-calendar-alt"></i></div>
            <h3>Belum ada tahun ajaran</h3>
            <p>Mulai buat tahun ajaran untuk mengelola promosi siswa.</p>
            <a href="{{ route('academic-years.create') }}" class="ta-btn-start">
                <i class="fas fa-plus"></i> Buat Tahun Ajaran
            </a>
        </div>
    @endif

    {{-- FAB --}}
    <a href="{{ route('academic-years.create') }}" class="ta-fab">
        <i class="fas fa-plus"></i> Tahun Ajaran Baru
    </a>

</div>
@endsection

@push('scripts')
<script>
// Auto-submit activation form without confirmation
document.addEventListener('DOMContentLoaded', function() {
    // Forms will auto-submit when button clicked
});
</script>
@endpush