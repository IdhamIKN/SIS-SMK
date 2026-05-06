@extends('layouts.app')

@section('title', 'Detail - ' . $academicYear->name)

@push('styles')
@include('components.izin-styles')
<style>
/* ── Hero section ── */
.ta-hero {
    position: relative;
    background: linear-gradient(135deg, #10b981 0%, #059669 60%, #047857 100%);
    padding: 24px 20px 80px; overflow: hidden;
}
.ta-hero::before {
    content: ''; position: absolute; top: -50px; right: -50px;
    width: 160px; height: 160px; background: rgba(255,255,255,.07); border-radius: 50%;
}
.ta-hero::after {
    content: ''; position: absolute; bottom: -30px; left: -20px;
    width: 120px; height: 120px; background: rgba(255,255,255,.05); border-radius: 50%;
}
.hero-nav {
    position: relative; z-index: 2;
    display: flex; align-items: center; justify-content: space-between;
}
.hero-back {
    width: 36px; height: 36px; border-radius: 10px;
    background: rgba(255,255,255,.18); backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    color: #fff; text-decoration: none; font-size: .9rem;
    border: 1px solid rgba(255,255,255,.25);
}
.hero-actions { display: flex; gap: 8px; }
.hero-action-btn {
    padding: 7px 14px; border-radius: 10px;
    background: rgba(255,255,255,.18); backdrop-filter: blur(6px);
    color: #fff; font-size: .75rem; font-weight: 700;
    text-decoration: none; border: 1px solid rgba(255,255,255,.25);
    display: inline-flex; align-items: center; gap: 5px; transition: background .18s;
}
.hero-action-btn:hover { background: rgba(255,255,255,.28); }

/* ── Hero card ── */
.ta-card {
    position: relative; z-index: 3;
    background: #fff; border-radius: 20px;
    box-shadow: 0 6px 28px rgba(0,0,0,.12);
    margin: -44px 16px 0; padding: 20px;
    display: flex; align-items: center; gap: 16px;
}
.ta-avatar {
    width: 72px; height: 72px; border-radius: 16px;
    border: 3px solid #fff; box-shadow: 0 2px 12px rgba(0,0,0,.15);
    flex-shrink: 0; overflow: hidden;
    background: #dcfce7; display: flex; align-items: center;
    justify-content: center; color: #10b981; font-size: 1.6rem;
}
.ta-info { flex: 1; min-width: 0; }
.ta-name { font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ta-detail { font-size: .72rem; color: #64748b; margin: 0 0 6px; }
.ta-tags { display: flex; flex-wrap: wrap; gap: 5px; }
.tag { font-size: .65rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; display: inline-flex; align-items: center; gap: 3px; }
.tag.active { background: #dcfce7; color: #15803d; }
.tag.inactive { background: #f1f5f9; color: #64748b; }

/* ── Stats grid ── */
.stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin: 14px 16px; }
.stat-box {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 8px; text-align: center;
}
.stat-box .s-val { font-size: 1.3rem; font-weight: 800; line-height: 1; margin-bottom: 3px; }
.stat-box .s-lbl { font-size: .6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }

/* ── Card ── */
.card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; margin: 0 16px 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden; }
.c-head { display: flex; align-items: center; gap: 10px; padding: 14px 16px 10px; border-bottom: 1px solid #f8fafc; }
.c-head h3 { margin: 0; font-size: .9rem; font-weight: 700; flex: 1; }
.c-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
.hbadge { font-size: .68rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; background: #f1f5f9; color: #64748b; }
.c-body { padding: 16px; }

/* ── Class list ── */
.class-list { padding: 0 16px; }
.class-item {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
    margin-bottom: 10px; box-shadow: 0 1px 4px rgba(0,0,0,.04); overflow: hidden;
}
.class-head {
    display: flex; align-items: center; gap: 12px; padding: 14px 16px 10px;
}
.class-icon {
    width: 48px; height: 48px; border-radius: 12px;
    background: #ede9fe; color: #7c3aed;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}
.class-info { flex: 1; min-width: 0; }
.class-name {
    font-size: .9rem; font-weight: 700; color: #0f172a;
    margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.class-detail { font-size: .72rem; color: #64748b; margin: 0; }
.class-status {
    font-size: .68rem; font-weight: 700; padding: 3px 9px;
    border-radius: 20px; flex-shrink: 0;
}
.class-status.ready { background: #dcfce7; color: #15803d; }
.class-status.pending { background: #fef9c3; color: #a16207; }
.class-status.promoted { background: #dbeafe; color: #1d4ed8; }
.class-status.graduated { background: #f1f5f9; color: #64748b; }
.class-meta {
    display: flex; flex-wrap: wrap; gap: 8px;
    font-size: .75rem; color: #64748b; padding: 0 16px;
}
.class-meta span { display: inline-flex; align-items: center; gap: 4px; }
.class-actions { display: flex; gap: 8px; padding: 10px 16px 14px; }
.class-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 12px; border-radius: 8px; font-size: .78rem; font-weight: 600;
    border: none; cursor: pointer; text-decoration: none; font-family: inherit;
    transition: all .18s; white-space: nowrap;
}
.class-btn:active { transform: scale(.96); }
.class-btn.view { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.class-btn.promote { background: #10b981; color: #fff; box-shadow: 0 3px 12px rgba(16,185,129,.3); }
.class-btn.promote:hover { filter: brightness(1.08); }

/* ── Alert ── */
.alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 12px; font-size: .84rem; margin: 12px 16px 0; }
.a-ok  { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
.a-err { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

/* ── Action bar ── */
.action-bar {
    position: fixed; bottom: var(--footer-h); left: 0; right: 0;
    padding: 10px 16px 12px;
    background: rgba(255,255,255,.96); backdrop-filter: blur(10px);
    border-top: 1px solid #e2e8f0; display: flex; gap: 8px;
    z-index: 999; box-shadow: 0 -4px 20px rgba(0,0,0,.06);
}
.ab-btn { flex: 1; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 12px 12px; border-radius: 12px; font-size: .82rem; font-weight: 700; border: none; cursor: pointer; text-decoration: none; font-family: inherit; transition: all .18s; line-height: 1; white-space: nowrap; }
.ab-btn:active { transform: scale(.97); }
.ab-btn-back   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; flex: 0 0 auto; padding: 12px 14px; }
.ab-btn-edit   { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.ab-btn-edit:hover { background: #fef3c7; }
.ab-btn-primary { background: #10b981; color: #fff; box-shadow: 0 3px 12px rgba(16,185,129,.3); }
.ab-btn-primary:hover { filter: brightness(1.08); }

.ta-wrap { padding: 16px 0 calc(var(--footer-h) + 80px); }
.empty-state { text-align: center; padding: 24px 16px; color: #94a3b8; font-size: .82rem; }
.empty-state i { display: block; font-size: 1.8rem; margin-bottom: 6px; opacity: .4; }
</style>
@endpush

@section('content')

{{-- Hero Section --}}
<div class="ta-hero">
    <div class="hero-nav">
        <a href="{{ route('academic-years.index') }}" class="hero-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="hero-actions">
            @if(!$academicYear->promotion_waves)
                <form action="{{ route('academic-years.initialize-waves', $academicYear) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="hero-action-btn">
                        <i class="fas fa-wave-square"></i> Init Waves
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

{{-- Hero Card --}}
<div class="ta-card">
    <div class="ta-avatar">
        <i class="fas fa-calendar-alt"></i>
    </div>
    <div class="ta-info">
        <h3 class="ta-name">{{ $academicYear->name }}</h3>
        <p class="ta-detail">{{ $academicYear->year_start }} - {{ $academicYear->year_end }}</p>
        <div class="ta-tags">
            <span class="tag {{ $academicYear->is_active ? 'active' : 'inactive' }}">
                <i class="fas fa-circle" style="font-size:.5rem;"></i>
                {{ $academicYear->is_active ? 'Aktif' : 'Tidak Aktif' }}
            </span>
            @if($academicYear->promotion_deadline)
                <span class="tag {{ $academicYear->canPromote() ? 'active' : 'inactive' }}">
                    <i class="fas fa-clock"></i>
                    Deadline: {{ $academicYear->promotion_deadline->format('d/m/Y') }}
                </span>
            @endif
        </div>
    </div>
</div>

<div class="ta-wrap">

    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="stats-grid">
        <div class="stat-box">
            <div class="s-val" style="color:#10b981;">{{ $promotionStats['total_classes'] }}</div>
            <div class="s-lbl">Total Kelas</div>
        </div>
        <div class="stat-box">
            <div class="s-val" style="color:#15803d;">{{ $promotionStats['ready_classes'] }}</div>
            <div class="s-lbl">Siap Promosi</div>
        </div>
        <div class="stat-box">
            <div class="s-val" style="color:#1d4ed8;">{{ $promotionStats['promoted_classes'] }}</div>
            <div class="s-lbl">Sudah Promosi</div>
        </div>
        <div class="stat-box">
            <div class="s-val" style="color:#64748b;">{{ $promotionStats['total_students'] }}</div>
            <div class="s-lbl">Total Siswa</div>
        </div>
    </div>

    {{-- Promotion Waves --}}
    @if($academicYear->promotion_waves)
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#fef9c3; color:#a16207;"><i class="fas fa-wave-square"></i></div>
                <h3>Gelombang Promosi</h3>
            </div>
            <div class="c-body">
                @foreach($academicYear->promotion_waves as $waveKey => $wave)
                    <div style="margin-bottom: 12px; padding: 8px; background: #f8fafc; border-radius: 8px;">
                        <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                            Gelombang {{ str_replace('wave_', '', $waveKey) }}
                        </div>
                        <div style="font-size: .8rem; color: #64748b;">
                            {{ ucfirst($wave['from']) }} → {{ $wave['to'] === 'graduated' ? 'Lulus' : ucfirst($wave['to']) }}
                            • Deadline: {{ \Carbon\Carbon::parse($wave['deadline'])->format('d/m/Y') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Classes List --}}
    @if ($classes->count() > 0)
        <div class="class-list">
            @foreach ($classes as $class)
                <div class="class-item">
                    <div class="class-head">
                        <div class="class-icon">
                            <i class="fas fa-school"></i>
                        </div>
                        <div class="class-info">
                            <h3 class="class-name">{{ $class->nama_kelas }}</h3>
                            <p class="class-detail">Tingkat {{ $class->tingkat }}</p>
                        </div>
                        <span class="class-status {{ $class->promotion_status }}">
                            {{ ucfirst($class->promotion_status) }}
                        </span>
                    </div>
                    <div class="class-meta">
                        <span><i class="fas fa-graduation-cap"></i> {{ $class->jurusan?->nama_jurusan ?? '-' }}</span>
                        <span><i class="fas fa-users"></i> {{ $class->siswa_count }} Siswa</span>
                        @if($class->promotion_wave)
                            <span><i class="fas fa-wave-square"></i> Wave {{ $class->promotion_wave }}</span>
                        @endif
                    </div>
                    <div class="class-actions">
                        <a href="{{ route('kelas.show', $class) }}" class="class-btn view">
                            <i class="fas fa-eye"></i> Detail
                        </a>
                        @if($academicYear->canPromote() && $class->canPromoteStudents() && $class->promotion_status === 'pending')
                            <a href="{{ route('kelas.promote', $class) }}" class="class-btn promote">
                                <i class="fas fa-arrow-up"></i> Promosi
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-school"></i>
            <p>Belum ada kelas di tahun ajaran ini.</p>
        </div>
    @endif

</div>

{{-- Action Bar --}}
<div class="action-bar">
    <a href="{{ route('academic-years.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
    <a href="{{ route('academic-years.edit', $academicYear) }}" class="ab-btn ab-btn-edit"><i class="fas fa-pen"></i> Edit</a>
</div>
@endsection