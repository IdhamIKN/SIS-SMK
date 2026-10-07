@extends('layouts.app')
@section('title', 'Rekap Guru - ' . $eventGuru->nama_event)
@push('styles')
@include('components.event-styles')
<style>
.ev-wrap{padding:0 12px;max-width:1280px;margin:0 auto;box-sizing:border-box}
@media(min-width:768px){.ev-wrap{padding:0 20px}}
.stat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px}
@media(min-width:768px){.stat-grid{gap:12px;margin-bottom:18px}}
.stat-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:10px 8px;text-align:center}
@media(min-width:768px){.stat-card{border-radius:12px;padding:16px}}
.stat-value{font-size:1.4rem;font-weight:800;line-height:1;margin-bottom:3px}
@media(min-width:768px){.stat-value{font-size:1.8rem}}
.stat-label{font-size:.6rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.ev-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
@media(max-width:767px){.ev-table-wrap{display:none}}
.ev-table{width:100%;border-collapse:collapse;font-size:.78rem;background:#fff}
.ev-table thead tr{background:#f8fafc;border-bottom:2px solid #e2e8f0}
.ev-table th{padding:11px 10px;text-align:left;font-size:.68rem;font-weight:700;color:#64748b;white-space:nowrap;text-transform:uppercase;letter-spacing:.04em}
.ev-table td{padding:10px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.ev-table tbody tr:last-child td{border-bottom:none}
.ev-table tbody tr:hover td{background:#fafbfc}
.ev-card-list{display:none}
@media(max-width:767px){.ev-card-list{display:flex;flex-direction:column;gap:0}}
.evi{padding:12px 14px;border-bottom:1px solid #f1f5f9;background:#fff}
.evi:last-child{border-bottom:none}
.evi-top{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:4px}
.evi-name{font-weight:700;font-size:.85rem;color:#0f172a}
.evi-mid{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:4px}
.evi-chip{font-size:.7rem;color:#64748b;background:#f1f5f9;border-radius:5px;padding:2px 7px;font-weight:600}
.evi-bot{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:4px}
.badge-masuk-g{background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:.65rem;font-weight:700}
.badge-pulang-g{background:#ffedd5;color:#c2410c;padding:2px 8px;border-radius:20px;font-size:.65rem;font-weight:700}
.badge-hadir-g{background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:20px;font-size:.65rem;font-weight:700}
.badge-belum-g{background:#f1f5f9;color:#64748b;padding:2px 8px;border-radius:20px;font-size:.65rem;font-weight:700}
/* empty */
.ev-empty{text-align:center;padding:36px 20px;color:#64748b}
.ev-empty i{font-size:2.5rem;opacity:.3;display:block;margin-bottom:10px}
.ev-empty strong{display:block;color:#0f172a;margin-bottom:4px;font-size:.9rem}
/* action bar */
.action-bar{position:fixed;bottom:var(--footer-h,0);left:0;right:0;padding:10px 12px 12px;background:rgba(255,255,255,.96);backdrop-filter:blur(10px);border-top:1px solid #e2e8f0;display:flex;gap:6px;z-index:999;box-shadow:0 -4px 20px rgba(0,0,0,.06);flex-wrap:wrap}
@media(min-width:768px){.action-bar{padding:10px 24px 12px;gap:10px;justify-content:flex-end;flex-wrap:nowrap}}
.ab-btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:11px 12px;border-radius:12px;font-size:.78rem;font-weight:700;border:none;cursor:pointer;text-decoration:none;font-family:inherit;transition:all .18s;line-height:1;white-space:nowrap;min-width:60px}
@media(min-width:768px){.ab-btn{flex:unset;min-width:100px;font-size:.84rem;padding:12px 16px}}
.ab-btn:active{transform:scale(.97)}
.ab-btn-back{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;flex:0 0 auto;padding:11px 14px}
.ab-btn-primary{background:#4338ca;color:#fff;box-shadow:0 3px 10px rgba(67,56,202,.3)}
.ab-btn-green{background:#15803d;color:#fff;box-shadow:0 3px 10px rgba(21,128,61,.3)}
.ab-btn-scan{background:#16a34a;color:#fff;box-shadow:0 3px 10px rgba(22,163,74,.3)}
</style>
@endpush

@section('content')
@php
    $isAdmin = auth()->user()->hasAnyRole(['superadmin','admin_tatib','bk']);
    $myGtk   = auth()->user()->gtk ?? null;
    $myGtkId = $myGtk?->id;
    $isGuru  = $myGtkId !== null;
    $totalHadirMasuk  = $eventGuru->absenEventGuru->where('jenis','masuk')->unique('gtk_id')->count();
    $totalHadirPulang = $eventGuru->absenEventGuru->where('jenis','pulang')->unique('gtk_id')->count();
    $totalScan        = $eventGuru->absenEventGuru->count();
@endphp

<div class="ev-wrap" style="padding-top:var(--header-h,56px);padding-bottom:calc(var(--footer-h,0px) + 88px)">

    {{-- Page Strip --}}
    <div class="page-strip" style="background:linear-gradient(135deg,#1e40af 0%,#4338ca 100%)">
        <div class="live-badge"><span class="live-dot"></span>Rekap Absensi Guru</div>
        <h2><i class="fas fa-chalkboard-teacher"></i> {{ Str::limit($eventGuru->nama_event,32) }}</h2>
        <p>{{ $eventGuru->tanggal_mulai->format('d M Y') }} &bull; {{ $eventGuru->tanggal_mulai->format('H:i') }}–{{ $eventGuru->tanggal_selesai->format('H:i') }}</p>
    </div>

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card"><div class="stat-value" style="color:#4338ca">{{ $totalHadirMasuk }}</div><div class="stat-label">Scan Masuk</div></div>
        <div class="stat-card"><div class="stat-value" style="color:#f59e0b">{{ $totalHadirPulang }}</div><div class="stat-label">Scan Pulang</div></div>
        <div class="stat-card"><div class="stat-value" style="color:#16a34a">{{ $totalScan }}</div><div class="stat-label">Total Scan</div></div>
    </div>

    {{-- Status Saya --}}
    @if($isGuru && $myGtkId)
    @php $myAbsen=$absenByGuru->get($myGtkId,collect()); $myMasuk=$myAbsen->firstWhere('jenis','masuk'); $myPulang=$myAbsen->firstWhere('jenis','pulang'); @endphp
    <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:12px">
        <div class="c-head">
            <div class="c-icon" style="background:#e0e7ff;flex-shrink:0"><i class="fas fa-user-check" style="color:#4338ca"></i></div>
            <h3>Status Absen Saya</h3>
        </div>
        <div class="c-body" style="padding:12px 16px">
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                @if($eventGuru->ada_absen_masuk)
                <div class="s-chip"><div class="ci {{ $myMasuk?'ci-g':'' }}"><i class="fas fa-{{ $myMasuk?'check':'times' }}"></i></div>
                <div><div class="c-lbl">Masuk</div><div class="c-val">{{ $myMasuk?$myMasuk->waktu_scan->format('H:i'):'Belum scan' }}</div></div></div>
                @endif
                @if($eventGuru->ada_absen_pulang)
                <div class="s-chip"><div class="ci {{ $myPulang?'ci-g':'' }}"><i class="fas fa-{{ $myPulang?'check':'times' }}"></i></div>
                <div><div class="c-lbl">Pulang</div><div class="c-val">{{ $myPulang?$myPulang->waktu_scan->format('H:i'):'Belum scan' }}</div></div></div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Data Card --}}
    <div class="card" style="border-radius:12px;overflow:hidden">
        <div class="c-head">
            <div class="c-icon" style="background:#e0e7ff;color:#4338ca;flex-shrink:0"><i class="fas fa-list-alt"></i></div>
            <h3>Daftar Kehadiran Guru</h3>
            <span class="hbadge">{{ $absenByGuru->count() }} guru</span>
        </div>

        @if($absenByGuru->count() > 0)
        {{-- TABLE desktop --}}
        <div class="ev-table-wrap">
            <table class="ev-table">
                <thead><tr><th>Kode</th><th>Nama Guru</th><th>Jabatan</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr></thead>
                <tbody>
                @foreach($absenByGuru as $gtkId => $absenGuru)
                @php $guru=$absenGuru->first()->gtk; $masuk=$absenGuru->firstWhere('jenis','masuk'); $pulang=$absenGuru->firstWhere('jenis','pulang'); @endphp
                <tr>
                    <td style="font-size:.72rem;color:#94a3b8">{{ $guru->kd_guru??'-' }}</td>
                    <td style="font-weight:600">{{ Str::limit($guru->nama_lengkap??'-',22) }}</td>
                    <td style="font-size:.75rem">{{ $guru->jabatan??'-' }}</td>
                    <td>@if($masuk)<span class="badge-masuk-g">{{ $masuk->waktu_scan->format('H:i') }}</span>@else<span style="color:#94a3b8;font-size:.75rem">—</span>@endif</td>
                    <td>@if($pulang)<span class="badge-pulang-g">{{ $pulang->waktu_scan->format('H:i') }}</span>@else<span style="color:#94a3b8;font-size:.75rem">—</span>@endif</td>
                    <td><span class="badge-hadir-g">Hadir</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{-- CARD LIST mobile --}}
        <div class="ev-card-list">
        @foreach($absenByGuru as $gtkId => $absenGuru)
        @php $guru=$absenGuru->first()->gtk; $masuk=$absenGuru->firstWhere('jenis','masuk'); $pulang=$absenGuru->firstWhere('jenis','pulang'); @endphp
        <div class="evi">
            <div class="evi-top">
                <span class="evi-name">{{ Str::limit($guru->nama_lengkap??'-',26) }}</span>
                <span class="badge-hadir-g">Hadir</span>
            </div>
            <div class="evi-mid">
                @if($guru->kd_guru)<span class="evi-chip">{{ $guru->kd_guru }}</span>@endif
                @if($guru->jabatan)<span class="evi-chip">{{ $guru->jabatan }}</span>@endif
            </div>
            <div class="evi-bot">
                <div style="display:flex;gap:5px;flex-wrap:wrap">
                    @if($masuk)<span class="badge-masuk-g"><i class="fas fa-sign-in-alt" style="font-size:.6rem"></i> {{ $masuk->waktu_scan->format('H:i') }}</span>@endif
                    @if($pulang)<span class="badge-pulang-g"><i class="fas fa-sign-out-alt" style="font-size:.6rem"></i> {{ $pulang->waktu_scan->format('H:i') }}</span>@endif
                </div>
            </div>
        </div>
        @endforeach
        </div>

        @else
        <div class="ev-empty"><i class="fas fa-inbox"></i><strong>Belum ada guru yang scan</strong>Data absensi akan muncul setelah guru melakukan scan barcode.</div>
        @endif
    </div>

</div>

{{-- Action Bar --}}
<div class="action-bar">
    <a href="{{ route('event-guru.show', $eventGuru) }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i> Kembali</a>
    @if($isAdmin)
        <a href="{{ route('event-guru.export', $eventGuru) }}" class="ab-btn ab-btn-green"><i class="fas fa-file-excel"></i> Export</a>
    @endif
    @if($isGuru && $eventGuru->isActive())
    @php
        $myAllR=$absenByGuru->get($myGtkId,collect());
        $sdMsk=$myAllR->where('jenis','masuk')->isNotEmpty();
        $sdPlg=$myAllR->where('jenis','pulang')->isNotEmpty();
        $nextJR=$sdMsk&&$eventGuru->ada_absen_pulang?'pulang':'masuk';
        $selesaiR=($sdMsk&&!$eventGuru->ada_absen_pulang)||$sdPlg;
    @endphp
    @if(!$selesaiR)<a href="{{ route('event-guru.scan', ['eventGuru'=>$eventGuru,'jenis'=>$nextJR]) }}" class="ab-btn ab-btn-scan"><i class="fas fa-qrcode"></i> {{ $nextJR==='masuk'?'Scan Masuk':'Scan Pulang' }}</a>@endif
    @endif
</div>
@endsection
@push('scripts')
<script>document.addEventListener('DOMContentLoaded',function(){var h=document.querySelector('.header-auto-show');if(h)h.classList.add('header-active');});</script>
@endpush
