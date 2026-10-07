@extends('layouts.app')

@section('title', '🛠 Developer Panel')

@push('styles')
@include('components.event-styles')
<style>
    /* ══════════════════════════════════════════════════════════════
       DEVELOPER PANEL — mengikuti pola halaman pelanggaran
       Breakpoints: xs <480 | sm 480-767 | md 768+ | lg 1024+
       ══════════════════════════════════════════════════════════════ */

    /* ── Wrapper ── */
    .dev-wrap {
        padding: 0 12px;
        max-width: 1280px;
        margin: 0 auto;
        box-sizing: border-box;
    }
    @media (min-width: 768px)  { .dev-wrap { padding: 0 20px; } }
    @media (min-width: 1024px) { .dev-wrap { padding: 0 28px; } }

    /* ── Stat grid ── */
    .dev-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 14px;
    }
    @media (min-width: 480px) { .dev-stat-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (min-width: 768px) { .dev-stat-grid { grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; } }

    .dev-stat-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 8px;
        text-align: center;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    @media (min-width: 768px) { .dev-stat-card { border-radius: 12px; padding: 16px; } }

    .dev-stat-val {
        font-size: 1.4rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
    @media (min-width: 768px) { .dev-stat-val { font-size: 1.8rem; } }

    .dev-stat-lbl {
        font-size: .6rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    @media (min-width: 768px) { .dev-stat-lbl { font-size: .7rem; } }

    /* ── Filter ── */
    .filter-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 16px;
        overflow: hidden;
    }
    .filter-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        cursor: pointer;
        user-select: none;
        background: #f1f5f9;
        border: none;
        width: 100%;
        font-family: inherit;
        font-size: .875rem;
        font-weight: 700;
        color: #0f172a;
        gap: 8px;
    }
    .filter-toggle .ft-left { display: flex; align-items: center; gap: 8px; }
    .filter-toggle .ft-chevron { transition: transform .2s; color: #64748b; font-size: .8rem; }
    .filter-toggle.open .ft-chevron { transform: rotate(180deg); }
    @media (min-width: 768px) { .filter-toggle { display: none; } }

    .filter-body { padding: 12px 16px 16px; display: none; }
    .filter-body.open { display: block; }
    @media (min-width: 768px) { .filter-body { display: block !important; padding: 16px; } }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        align-items: end;
    }
    @media (max-width: 479px) { .filter-grid { grid-template-columns: 1fr; } }
    @media (min-width: 768px) { .filter-grid { grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); gap: 12px; } }

    .form-label-dev { display: block; font-size: .8rem; font-weight: 600; color: #0f172a; margin-bottom: 5px; }
    .form-input-dev {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: .875rem;
        font-family: inherit;
        background: #fff;
        color: #0f172a;
        outline: none;
        box-sizing: border-box;
        -webkit-appearance: none;
    }
    .form-input-dev:focus { border-color: #7c3aed; box-shadow: 0 0 0 3px rgba(124,58,237,.1); }

    /* ── Table desktop ── */
    .dev-table-wrap { display: none; overflow-x: auto; }
    @media (min-width: 768px) { .dev-table-wrap { display: block; } }

    .dev-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
    .dev-table th {
        background: #f8fafc;
        padding: 10px 12px;
        text-align: left;
        font-size: .72rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .dev-table td {
        padding: 11px 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        color: #0f172a;
    }
    .dev-table tr:last-child td { border-bottom: none; }
    .dev-table tr:hover td { background: #fafafa; }

    /* ── Card list mobile ── */
    .dev-card-list { display: flex; flex-direction: column; gap: 10px; padding: 12px; }
    @media (min-width: 768px) { .dev-card-list { display: none; } }

    .dev-card-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #7c3aed;
        border-radius: 10px;
        padding: 12px 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
    }
    .dev-ci-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 4px;
    }
    .dev-ci-nama { font-size: .88rem; font-weight: 800; color: #0f172a; }
    .dev-ci-meta {
        font-size: .73rem;
        color: #64748b;
        margin-bottom: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 4px 10px;
    }

    /* ── Role badge ── */
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: .71rem;
        font-weight: 700;
    }
    .role-superadmin     { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
    .role-admin_tatib    { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .role-kepala_sekolah { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .role-waka           { background: #cffafe; color: #0e7490; border: 1px solid #a5f3fc; }
    .role-gtk            { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .role-bk             { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    .role-kurikulum      { background: #f3e8ff; color: #7c3aed; border: 1px solid #e9d5ff; }
    .role-wali_kelas     { background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; }
    .role-siswa          { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .role-default        { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

    /* ── Action btn ── */
    .dev-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 7px;
        font-size: .75rem;
        font-weight: 600;
        font-family: inherit;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: filter .15s;
    }
    .dev-btn:hover { filter: brightness(.92); }
    .dev-btn-take { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
    .dev-btn-stop { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

    /* ── Pagination ── */
    .dev-pagination {
        padding: 12px 14px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 5px;
    }
    .pg-btn {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 34px; height: 34px; padding: 0 8px;
        border-radius: 8px; font-size: .8rem; font-weight: 600;
        border: 1.5px solid #e2e8f0; color: #64748b; background: #fff;
        text-decoration: none; transition: background .15s, color .15s;
        cursor: pointer; box-sizing: border-box;
    }
    .pg-btn.active { background: #7c3aed; border-color: #7c3aed; color: #fff; font-weight: 700; }
    .pg-btn:hover:not(.active):not(.disabled) { background: #ede9fe; border-color: #ddd6fe; color: #7c3aed; }
    .pg-btn.disabled { color: #cbd5e1; background: #f8fafc; cursor: not-allowed; }
    @media (max-width: 479px) { .pg-btn.pg-num:not(.active) { display: none; } }

    /* ── Empty ── */
    .dev-empty { padding: 40px 20px; text-align: center; color: #94a3b8; }
    .dev-empty i { font-size: 2rem; display: block; margin-bottom: 10px; }
    .dev-empty strong { display: block; font-size: .9rem; color: #64748b; }

    /* ── Warning stripe header ── */
    .dev-warning-stripe {
        background: repeating-linear-gradient(
            45deg, #1e1b4b, #1e1b4b 10px, #7c3aed 10px, #7c3aed 12px
        );
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        text-align: center;
        padding: 5px 12px;
        letter-spacing: .5px;
    }
</style>
@endpush

@section('content')
<div class="dev-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h, 0px) + 24px);">

    {{-- ── Page Strip ── --}}
    <div class="page-strip page-strip-event" style="--event-primary:#7c3aed;">
        <div class="live-badge"><span class="live-dot" style="background:#7c3aed;"></span>Developer</div>
        <h2><i class="fas fa-user-secret"></i> Developer Panel</h2>
        <p>Impersonate — Login sebagai user lain untuk debugging</p>
    </div>

    {{-- ── Warning stripe ── --}}
    <div class="dev-warning-stripe" style="margin-bottom:14px; border-radius:8px;">
        ⚠ RESTRICTED ACCESS &nbsp;·&nbsp; Halaman ini tidak ada di navigasi &nbsp;·&nbsp; Semua aksi tercatat di log
    </div>

    {{-- ── Stats ── --}}
    @php
        $totalUsers   = $users->total();
        $totalPages   = $users->lastPage();
        $currentPage  = $users->currentPage();
        $roles        = $roles ?? collect();
    @endphp
    <div class="dev-stat-grid" style="margin-top: 14px;">
        <div class="dev-stat-card">
            <div class="dev-stat-val" style="color:#7c3aed;">{{ $totalUsers }}</div>
            <div class="dev-stat-lbl">Total User</div>
        </div>
        <div class="dev-stat-card">
            <div class="dev-stat-val" style="color:#0f766e;">{{ $roles->count() }}</div>
            <div class="dev-stat-lbl">Jumlah Role</div>
        </div>
        <div class="dev-stat-card">
            <div class="dev-stat-val" style="color:#64748b;">{{ $currentPage }}</div>
            <div class="dev-stat-lbl">Halaman</div>
        </div>
        <div class="dev-stat-card">
            <div class="dev-stat-val" style="color:#64748b;">{{ $totalPages }}</div>
            <div class="dev-stat-lbl">Total Hal.</div>
        </div>
    </div>

    {{-- ── Flash ── --}}
    @if(session('success'))
    <div style="background:#dcfce7; border:1px solid #86efac; color:#15803d; padding:11px 14px; border-radius:8px; margin-bottom:14px; font-size:.83rem;">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    {{-- ── Banner: sedang impersonate ── --}}
    @if(auth()->user()->isImpersonated())
    <div style="background:#fee2e2; border:1px solid #fecaca; border-radius:10px; padding:12px 16px; margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
        <div style="font-size:.83rem; font-weight:700; color:#dc2626;">
            <i class="fas fa-user-check"></i>
            Sedang impersonate sebagai <strong>{{ auth()->user()->name }}</strong>
            <span class="role-badge role-{{ auth()->user()->role_utama ?? 'default' }}" style="margin-left:6px;">{{ auth()->user()->role_utama }}</span>
        </div>
        <form method="POST" action="{{ route('developer.impersonate.leave') }}" style="margin:0;">
            @csrf
            <button type="submit" class="dev-btn dev-btn-stop">
                <i class="fas fa-sign-out-alt"></i> Stop Impersonate
            </button>
        </form>
    </div>
    @endif

    {{-- ── Filter ── --}}
    @php $hasFilter = request()->hasAny(['search', 'role']); @endphp
    <form method="GET" action="{{ route('developer.index') }}" class="filter-section" id="devFilter">
        <button type="button" class="filter-toggle {{ $hasFilter ? 'open' : '' }}" id="devToggle" onclick="toggleDevFilter()">
            <span class="ft-left">
                <i class="fas fa-filter"></i> Filter
                @if($hasFilter)
                    <span style="background:#fef3c7; color:#b45309; font-size:.65rem; padding:2px 8px; border-radius:20px; font-weight:700;">Aktif</span>
                @endif
            </span>
            <i class="fas fa-chevron-down ft-chevron"></i>
        </button>
        <div class="filter-body {{ $hasFilter ? 'open' : '' }}" id="devFilterBody">
            <div class="filter-grid">
                <div>
                    <label class="form-label-dev">Cari</label>
                    <div style="position:relative;">
                        <input type="text" name="search" class="form-input-dev"
                               placeholder="Nama atau email…"
                               value="{{ request('search') }}"
                               style="padding-left:32px;">
                        <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.8rem;pointer-events:none;"></i>
                    </div>
                </div>
                <div>
                    <label class="form-label-dev">Role</label>
                    <select name="role" class="form-input-dev">
                        <option value="">Semua Role</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}" @selected(request('role') === $r)>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:8px; align-items:flex-end; padding-top:4px;">
                    <button type="submit" class="dev-btn dev-btn-take" style="flex:1; justify-content:center; padding:10px;">
                        <i class="fas fa-filter"></i> Terapkan
                    </button>
                    @if($hasFilter)
                        <a href="{{ route('developer.index') }}" class="dev-btn"
                           style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; padding:10px 14px;">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- ── Card: Tabel + List ── --}}
    <div class="card" style="border-radius:12px; overflow:hidden; margin-bottom:0;">

        {{-- Card header --}}
        <div class="c-head">
            <div class="c-icon" style="background:#ede9fe; color:#7c3aed; flex-shrink:0;">
                <i class="fas fa-user-secret"></i>
            </div>
            <h3>Daftar User</h3>
            <span class="hbadge">{{ $users->total() }} user</span>
        </div>

        @if($users->isEmpty())
            <div class="dev-empty">
                <i class="fas fa-inbox"></i>
                <strong>Tidak ada user ditemukan</strong>
                <span style="font-size:.8rem; margin-top:4px; display:block;">Coba ubah filter pencarian.</span>
            </div>
        @else

            {{-- ══ TABEL — Desktop (≥768px) ══ --}}
            <div class="dev-table-wrap">
                <table class="dev-table">
                    <thead>
                        <tr>
                            <th style="width:28px;">#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Siswa ID</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $i => $user)
                        @if($user->canBeImpersonated())
                        <tr>
                            <td style="color:#94a3b8; font-size:.72rem;">{{ $users->firstItem() + $loop->index }}</td>
                            <td>
                                <div style="font-weight:700; font-size:.85rem;">{{ $user->name }}</div>
                            </td>
                            <td style="font-size:.8rem; color:#64748b;">
                                {{ $user->email ?: '—' }}
                            </td>
                            <td>
                                @php $rc = 'role-' . ($user->role_utama ?? 'default'); @endphp
                                <span class="role-badge {{ $rc }}">{{ $user->role_utama ?? '?' }}</span>
                            </td>
                            <td style="font-size:.78rem; color:#94a3b8;">
                                {{ $user->siswa_id ?? '—' }}
                            </td>
                            <td style="text-align:center;">
                                <form method="POST"
                                      action="{{ route('developer.impersonate.take', $user) }}"
                                      id="take-{{ $user->id }}"
                                      style="margin:0; display:inline;">
                                    @csrf
                                    <button type="button" class="dev-btn dev-btn-take"
                                            onclick="confirmImpersonate('take-{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role_utama }}')">
                                        <i class="fas fa-user-ninja"></i> Login as
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- ══ CARD LIST — Mobile (<768px) ══ --}}
            <div class="dev-card-list">
                @foreach($users as $user)
                @if($user->canBeImpersonated())
                <div class="dev-card-item">
                    <div class="dev-ci-top">
                        <span class="dev-ci-nama">{{ $user->name }}</span>
                        @php $rc = 'role-' . ($user->role_utama ?? 'default'); @endphp
                        <span class="role-badge {{ $rc }}">{{ $user->role_utama ?? '?' }}</span>
                    </div>
                    <div class="dev-ci-meta">
                        @if($user->email)
                            <span><i class="fas fa-envelope"></i> {{ $user->email }}</span>
                        @endif
                        @if($user->siswa_id)
                            <span><i class="fas fa-id-card"></i> Siswa ID: {{ $user->siswa_id }}</span>
                        @endif
                    </div>
                    <form method="POST"
                          action="{{ route('developer.impersonate.take', $user) }}"
                          id="take-m-{{ $user->id }}"
                          style="margin:0;">
                        @csrf
                        <button type="button" class="dev-btn dev-btn-take"
                                onclick="confirmImpersonate('take-m-{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role_utama }}')">
                            <i class="fas fa-user-ninja"></i> Login as {{ $user->name }}
                        </button>
                    </form>
                </div>
                @endif
                @endforeach
            </div>

        @endif

        {{-- ── Pagination ── --}}
        @if($users->hasPages())
        <div class="dev-pagination">
            {{-- Prev --}}
            @if($users->onFirstPage())
                <span class="pg-btn disabled"><i class="fas fa-angle-left"></i></span>
            @else
                <a href="{{ $users->previousPageUrl() }}" class="pg-btn"><i class="fas fa-angle-left"></i></a>
            @endif

            @php
                $pgStart = max(1, $users->currentPage() - 2);
                $pgEnd   = min($users->lastPage(), $users->currentPage() + 2);
            @endphp

            @if($pgStart > 1)
                <a href="{{ $users->url(1) }}" class="pg-btn pg-num">1</a>
                @if($pgStart > 2)<span class="pg-btn pg-num" style="pointer-events:none;">…</span>@endif
            @endif

            @for($pg = $pgStart; $pg <= $pgEnd; $pg++)
                @if($users->currentPage() === $pg)
                    <span class="pg-btn pg-num active">{{ $pg }}</span>
                @else
                    <a href="{{ $users->url($pg) }}" class="pg-btn pg-num">{{ $pg }}</a>
                @endif
            @endfor

            @if($pgEnd < $users->lastPage())
                @if($pgEnd < $users->lastPage() - 1)<span class="pg-btn pg-num" style="pointer-events:none;">…</span>@endif
                <a href="{{ $users->url($users->lastPage()) }}" class="pg-btn pg-num">{{ $users->lastPage() }}</a>
            @endif

            {{-- Next --}}
            @if($users->hasMorePages())
                <a href="{{ $users->nextPageUrl() }}" class="pg-btn"><i class="fas fa-angle-right"></i></a>
            @else
                <span class="pg-btn disabled"><i class="fas fa-angle-right"></i></span>
            @endif
        </div>
        <div style="text-align:center; font-size:.72rem; color:#94a3b8; padding:4px 0 10px;">
            Halaman {{ $users->currentPage() }} / {{ $users->lastPage() }}
            &nbsp;·&nbsp; {{ $users->total() }} user
        </div>
        @endif

    </div>{{-- end card --}}

</div>{{-- end dev-wrap --}}
@endsection

@push('scripts')
<script>
function toggleDevFilter() {
    var btn  = document.getElementById('devToggle');
    var body = document.getElementById('devFilterBody');
    btn.classList.toggle('open');
    body.classList.toggle('open');
}

function confirmImpersonate(formId, nama, role) {
    Swal.fire({
        icon: 'warning',
        title: 'Login sebagai user ini?',
        html: 'Anda akan masuk sebagai <strong>' + nama + '</strong><br>'
            + '<span style="font-size:.8rem;color:#64748b;">Role: ' + role + '</span><br><br>'
            + '<small style="color:#94a3b8;">Semua aksi ini tercatat di log sistem.</small>',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-user-ninja"></i> Ya, Login as',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#7c3aed',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
        focusCancel: true,
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>
@endpush
