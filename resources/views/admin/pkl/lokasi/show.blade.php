@extends('layouts.app')
@section('title', 'Detail Lokasi PKL — '.$lokasiPkl->nama_tempat)

@push('styles')
    @include('components.event-styles')
    <style>
        .pkl-wrap { padding:0 12px; max-width:1100px; margin:0 auto; }
        @media(min-width:768px){ .pkl-wrap { padding:0 24px; } }
        .info-grid { display:grid; grid-template-columns:1fr; gap:10px; margin-bottom:16px; }
        @media(min-width:640px){ .info-grid { grid-template-columns:1fr 1fr; } }
        @media(min-width:1024px){ .info-grid { grid-template-columns:repeat(3,1fr); } }
        .info-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 16px; }
        .info-card h4 { font-size:.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em; margin:0 0 8px; }
        .info-row { display:flex; align-items:flex-start; gap:8px; font-size:.82rem; color:#0f172a; margin-bottom:5px; }
        .info-row i { width:14px; text-align:center; color:#94a3b8; flex-shrink:0; margin-top:2px; }
        .info-row a { color:#0ea5e9; text-decoration:none; }
        .stat-bar { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
        .stat-box { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 18px; text-align:center; min-width:80px; }
        .stat-box-val { font-size:1.6rem; font-weight:800; }
        .stat-box-lbl { font-size:.65rem; color:#64748b; font-weight:600; text-transform:uppercase; }
        .map-area { height:260px; border-radius:12px; overflow:hidden; border:1px solid #e2e8f0; margin-bottom:16px; }
        .poin-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:.7rem; font-weight:700; }
        .poin-on { background:#dcfce7; color:#15803d; }
        .poin-off { background:#f1f5f9; color:#94a3b8; }
        .siswa-table { width:100%; border-collapse:collapse; font-size:.8rem; }
        .siswa-table th { background:#f8fafc; padding:9px 10px; text-align:left; font-size:.67rem; font-weight:700; color:#64748b; text-transform:uppercase; border-bottom:2px solid #e2e8f0; }
        .siswa-table td { padding:9px 10px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .siswa-table tr:last-child td { border-bottom:none; }
        .badge-status-aktif { background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:20px; font-size:.65rem; font-weight:700; }
        .badge-status-selesai { background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:20px; font-size:.65rem; font-weight:700; }
        .badge-status-batal { background:#fee2e2; color:#dc2626; padding:2px 8px; border-radius:20px; font-size:.65rem; font-weight:700; }
        .action-bar { position:fixed; bottom:var(--footer-h,0); left:0; right:0; padding:10px 12px 12px; background:rgba(255,255,255,.96); backdrop-filter:blur(10px); border-top:1px solid #e2e8f0; display:flex; gap:8px; z-index:999; }
        @media(min-width:768px){ .action-bar { padding:10px 24px 12px; justify-content:flex-end; } }
        .ab-btn { flex:1; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:12px 16px; border-radius:12px; font-size:.82rem; font-weight:700; border:none; cursor:pointer; text-decoration:none; font-family:inherit; }
        @media(min-width:768px){ .ab-btn { flex:unset; min-width:130px; } }
        .ab-btn-amber { background:#f59e0b; color:#fff; }
        .ab-btn-green { background:#16a34a; color:#fff; }
        .ab-btn-back { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
    </style>
@endpush

@section('content')
<div class="pkl-wrap" style="padding-top:var(--header-h,56px);padding-bottom:148px;">

    {{-- Page strip --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge"><span class="live-dot"></span>PKL</div>
        <h2><i class="fas fa-building"></i> {{ $lokasiPkl->nama_tempat }}</h2>
        <p>{{ $lokasiPkl->jenis_usaha ?? '' }} {{ $lokasiPkl->kabupaten ? '— '.$lokasiPkl->kabupaten : '' }}</p>
    </div>

    @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('warning'))
        <div style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
            <i class="fas fa-exclamation-triangle"></i> {{ session('warning') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="stat-bar">
        <div class="stat-box">
            <div class="stat-box-val" style="color:#16a34a;">{{ $stats['aktif'] }}</div>
            <div class="stat-box-lbl">Aktif</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-val" style="color:#0ea5e9;">{{ $stats['selesai'] }}</div>
            <div class="stat-box-lbl">Selesai</div>
        </div>
        <div class="stat-box">
            <div class="stat-box-val" style="color:#dc2626;">{{ $stats['batal'] }}</div>
            <div class="stat-box-lbl">Batal</div>
        </div>
        @if($lokasiPkl->kapasitas)
        <div class="stat-box">
            <div class="stat-box-val" style="color:#f59e0b;">{{ $lokasiPkl->kapasitas }}</div>
            <div class="stat-box-lbl">Kapasitas</div>
        </div>
        @endif
    </div>

    {{-- Info Grid --}}
    <div class="info-grid">
        <div class="info-card">
            <h4><i class="fas fa-map-marker-alt" style="margin-right:4px;"></i> Alamat</h4>
            <div class="info-row"><i class="fas fa-road"></i>{{ $lokasiPkl->alamat }}</div>
            @if($lokasiPkl->kelurahan)<div class="info-row"><i class="fas fa-map-pin"></i>{{ collect([$lokasiPkl->kelurahan,$lokasiPkl->kecamatan,$lokasiPkl->kabupaten,$lokasiPkl->provinsi])->filter()->implode(', ') }}</div>@endif
            @if($lokasiPkl->kode_pos)<div class="info-row"><i class="fas fa-mail-bulk"></i>{{ $lokasiPkl->kode_pos }}</div>@endif
            @if($lokasiPkl->latitude)<div class="info-row"><i class="fas fa-crosshairs"></i>{{ $lokasiPkl->latitude }}, {{ $lokasiPkl->longitude }} (radius {{ $lokasiPkl->radius_meter }}m)</div>@endif
        </div>

        <div class="info-card">
            <h4><i class="fas fa-user-tie" style="margin-right:4px;"></i> Penanggung Jawab</h4>
            @if($lokasiPkl->nama_pj)
                <div class="info-row"><i class="fas fa-user"></i><strong>{{ $lokasiPkl->nama_pj }}</strong>{{ $lokasiPkl->jabatan_pj ? ' — '.$lokasiPkl->jabatan_pj : '' }}</div>
                @if($lokasiPkl->no_hp_pj)<div class="info-row"><i class="fas fa-phone"></i><a href="https://wa.me/{{ preg_replace('/\D/','',$lokasiPkl->no_hp_pj) }}" target="_blank">{{ $lokasiPkl->no_hp_pj }}</a></div>@endif
                @if($lokasiPkl->email_pj)<div class="info-row"><i class="fas fa-envelope"></i>{{ $lokasiPkl->email_pj }}</div>@endif
                @if($lokasiPkl->no_telp_kantor)<div class="info-row"><i class="fas fa-building"></i>{{ $lokasiPkl->no_telp_kantor }}</div>@endif
                @if($lokasiPkl->website)<div class="info-row"><i class="fas fa-globe"></i><a href="{{ $lokasiPkl->website }}" target="_blank">{{ $lokasiPkl->website }}</a></div>@endif
            @else
                <div style="font-size:.8rem;color:#94a3b8;">Belum ada data PJ.</div>
            @endif
        </div>

        <div class="info-card">
            <h4><i class="fas fa-clock" style="margin-right:4px;"></i> Jam Absen</h4>
            <div class="info-row"><i class="fas fa-sign-in-alt"></i>Masuk: {{ $lokasiPkl->jam_masuk_pkl ? \Carbon\Carbon::parse($lokasiPkl->jam_masuk_pkl)->format('H:i') : '—' }}</div>
            <div class="info-row"><i class="fas fa-hourglass-half"></i>Batas tepat: {{ $lokasiPkl->batas_terlambat_pkl ? \Carbon\Carbon::parse($lokasiPkl->batas_terlambat_pkl)->format('H:i') : '—' }}</div>
            <div class="info-row"><i class="fas fa-ban"></i>Batas absen: {{ $lokasiPkl->batas_absen_masuk_pkl ? \Carbon\Carbon::parse($lokasiPkl->batas_absen_masuk_pkl)->format('H:i') : '—' }}</div>
            <div class="info-row"><i class="fas fa-sign-out-alt"></i>Pulang: {{ $lokasiPkl->jam_pulang_pkl ? \Carbon\Carbon::parse($lokasiPkl->jam_pulang_pkl)->format('H:i') : '—' }}</div>

            <h4 style="margin-top:12px;"><i class="fas fa-star" style="margin-right:4px;"></i> Auto Poin</h4>
            <div style="display:flex;flex-wrap:wrap;gap:5px;">
                <span class="poin-badge {{ $lokasiPkl->auto_poin_hadir_pkl ? 'poin-on' : 'poin-off' }}">
                    <i class="fas fa-check-circle"></i> Hadir
                </span>
                <span class="poin-badge {{ $lokasiPkl->auto_poin_terlambat_pkl ? 'poin-on' : 'poin-off' }}">
                    <i class="fas fa-clock"></i> Terlambat
                </span>
                <span class="poin-badge {{ $lokasiPkl->auto_poin_alfa_pkl ? 'poin-on' : 'poin-off' }}">
                    <i class="fas fa-times-circle"></i> Alfa
                </span>
            </div>
        </div>
    </div>

    {{-- Peta --}}
    @if($lokasiPkl->latitude && $lokasiPkl->longitude)
    <div class="map-area" id="mapArea"></div>
    @endif

    {{-- Daftar Siswa PKL --}}
    <div class="card" style="border-radius:12px;overflow:hidden;margin-bottom:16px;">
        <div class="c-head" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid #f1f5f9;">
            <div class="c-icon" style="background:#fef3c7;color:#b45309;flex-shrink:0;"><i class="fas fa-users"></i></div>
            <h3 style="font-size:.9rem;font-weight:800;color:#0f172a;margin:0;flex:1;">Daftar Siswa PKL</h3>
            @can('pkl.create')
            <a href="{{ route('admin.pkl.penugasan.create', ['lokasi_pkl_id' => $lokasiPkl->id]) }}" class="action-btn btn-view" style="font-size:.75rem;padding:6px 12px;text-decoration:none;">
                <i class="fas fa-plus"></i> Assign Siswa
            </a>
            @endcan
        </div>

        @if($lokasiPkl->penugasan->isEmpty())
            <div style="text-align:center;padding:32px;color:#94a3b8;font-size:.85rem;">
                <i class="fas fa-users" style="font-size:2rem;opacity:.2;display:block;margin-bottom:10px;"></i>
                Belum ada siswa yang ditugaskan ke lokasi ini.
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="siswa-table">
                    <thead>
                        <tr>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pembimbing</th>
                            <th>Periode</th>
                            <th>Status</th>
                            @can('pkl.update')<th>Aksi</th>@endcan
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lokasiPkl->penugasan as $p)
                        <tr>
                            <td>
                                <div style="font-weight:700;font-size:.85rem;">{{ $p->siswa?->nama_lengkap ?? '-' }}</div>
                                <div style="font-size:.7rem;color:#64748b;">{{ $p->siswa?->nis }}</div>
                            </td>
                            <td style="font-size:.78rem;">{{ $p->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                            <td style="font-size:.78rem;">{{ $p->gtk?->nama_lengkap ?? '-' }}</td>
                            <td style="font-size:.75rem;white-space:nowrap;">
                                {{ $p->tanggal_mulai->format('d/m/Y') }} –<br>{{ $p->tanggal_selesai->format('d/m/Y') }}
                            </td>
                            <td>
                                <span class="badge-status-{{ $p->status }}">{{ $p->status_label }}</span>
                            </td>
                            @can('pkl.update')
                            <td>
                                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                    <a href="{{ route('admin.pkl.rekap.detail-siswa', $p) }}" class="action-btn btn-view" style="font-size:.68rem;padding:4px 8px;text-decoration:none;">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    @if($p->status === 'aktif')
                                    <form method="POST" action="{{ route('admin.pkl.penugasan.selesai', $p) }}" style="display:inline;" onsubmit="return confirm('Tandai PKL ini sebagai selesai?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="action-btn btn-view" style="font-size:.68rem;padding:4px 8px;background:#dbeafe;color:#1d4ed8;border:none;">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pkl.penugasan.batal', $p) }}" style="display:inline;" onsubmit="return confirm('Batalkan penugasan PKL ini?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="action-btn" style="font-size:.68rem;padding:4px 8px;background:#fee2e2;color:#dc2626;border:none;border-radius:6px;cursor:pointer;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div class="action-bar">
    <a href="{{ route('admin.pkl.lokasi.index') }}" class="ab-btn ab-btn-back"><i class="fas fa-arrow-left"></i></a>
    <a href="{{ route('admin.pkl.rekap.per-siswa', ['lokasi_pkl_id' => $lokasiPkl->id]) }}" class="ab-btn" style="background:#6366f1;color:#fff;">
        <i class="fas fa-chart-bar"></i> Rekap
    </a>
    @can('pkl.create')
    <a href="{{ route('admin.pkl.penugasan.create', ['lokasi_pkl_id' => $lokasiPkl->id]) }}" class="ab-btn ab-btn-green">
        <i class="fas fa-user-plus"></i> Assign Siswa
    </a>
    @endcan
    @can('pkl.update')
    <a href="{{ route('admin.pkl.lokasi.edit', $lokasiPkl) }}" class="ab-btn ab-btn-amber">
        <i class="fas fa-pen"></i> Edit
    </a>
    @endcan
</div>
@endsection

@push('scripts')
@if($lokasiPkl->latitude && $lokasiPkl->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<script>
document.addEventListener('DOMContentLoaded', function() {
    var script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = function() {
        var lat = {{ $lokasiPkl->latitude }};
        var lng = {{ $lokasiPkl->longitude }};
        var map = L.map('mapArea').setView([lat, lng], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom:19, attribution:'© OpenStreetMap' }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup('<b>{{ addslashes($lokasiPkl->nama_tempat) }}</b>').openPopup();
        L.circle([lat, lng], { radius: {{ $lokasiPkl->radius_meter ?? 200 }}, color:'#f59e0b', weight:2, fillColor:'#f59e0b', fillOpacity:.15 }).addTo(map);
    };
    document.body.appendChild(script);
});
</script>
@endif
@endpush
