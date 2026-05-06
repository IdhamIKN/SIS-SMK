@extends('layouts.app')

@section('title', 'Detail - ' . $siswa->nama_lengkap)

@push('styles')
    @include('components.izin-styles')
    <style>
        /* ── Profile hero ── */
        .profile-hero {
            position: relative;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 55%, #0ea5e9 100%);
            padding: 24px 20px 64px;
            overflow: hidden;
        }
        .profile-hero::before {
            content: ''; position: absolute;
            top: -50px; right: -50px;
            width: 160px; height: 160px;
            background: rgba(255,255,255,.07); border-radius: 50%;
        }
        .profile-hero::after {
            content: ''; position: absolute;
            bottom: -30px; left: -20px;
            width: 120px; height: 120px;
            background: rgba(255,255,255,.05); border-radius: 50%;
        }
        .hero-nav {
            position: relative; z-index: 2;
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px;
        }
        .hero-back {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,255,255,.18);
            display: flex; align-items: center; justify-content: center;
            color: #fff; text-decoration: none; font-size: .9rem;
            border: 1px solid rgba(255,255,255,.25);
            backdrop-filter: blur(6px);
        }
        .hero-actions { display: flex; gap: 8px; }
        .hero-action-btn {
            padding: 7px 14px; border-radius: 10px;
            background: rgba(255,255,255,.18);
            color: #fff; font-size: .75rem; font-weight: 700;
            text-decoration: none; border: 1px solid rgba(255,255,255,.25);
            display: inline-flex; align-items: center; gap: 5px;
            backdrop-filter: blur(6px); transition: background .18s;
        }
        .hero-action-btn:hover { background: rgba(255,255,255,.28); }

        /* ── Profile card (overlap hero) ── */
        .profile-card {
            position: relative; z-index: 3;
            background: #fff; border-radius: 20px;
            box-shadow: 0 6px 28px rgba(0,0,0,.12);
            margin: -44px 16px 0;
            padding: 20px;
            display: flex; align-items: center; gap: 16px;
        }
        .profile-avatar {
            width: 72px; height: 72px; border-radius: 16px;
            border: 3px solid #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,.15);
            flex-shrink: 0; overflow: hidden;
            background: #ede9fe;
            display: flex; align-items: center; justify-content: center;
            color: #7c3aed; font-size: 1.6rem;
        }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-info { flex: 1; min-width: 0; }
        .profile-name {
            font-size: 1rem; font-weight: 800;
            color: #0f172a; margin: 0 0 3px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .profile-nisn { font-size: .72rem; color: #64748b; margin: 0 0 6px; }
        .profile-tags { display: flex; flex-wrap: wrap; gap: 5px; }
        .ptag {
            font-size: .65rem; font-weight: 700;
            padding: 2px 8px; border-radius: 20px;
            display: inline-flex; align-items: center; gap: 3px;
        }
        .ptag-purple { background: #ede9fe; color: #7c3aed; }
        .ptag-green  { background: #dcfce7; color: #15803d; }
        .ptag-red    { background: #fee2e2; color: #dc2626; }
        .ptag-blue   { background: #dbeafe; color: #1d4ed8; }

        /* ── Stat grid ── */
        .absen-stat-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;
            margin-bottom: 14px;
        }
        .absen-stat {
            background: #fff; border: 1px solid var(--border, #e2e8f0);
            border-radius: 12px; padding: 12px 8px; text-align: center;
        }
        .absen-stat .as-val { font-size: 1.3rem; font-weight: 800; line-height: 1; margin-bottom: 3px; }
        .absen-stat .as-lbl { font-size: .6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .03em; }

        /* ── Detail row ── */
        .detail-row {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding: 9px 0; font-size: .84rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .detail-row:last-child { border-bottom: none; }
        .dr-label {
            color: #64748b; display: flex; align-items: center; gap: 7px;
            flex-shrink: 0; min-width: 110px; font-size: .78rem;
        }
        .dr-label i { width: 14px; text-align: center; }
        .dr-value { font-weight: 600; color: #0f172a; text-align: right; font-size: .82rem; word-break: break-word; max-width: 180px; }

        /* ── Absensi table ── */
        .absen-table { width: 100%; border-collapse: collapse; font-size: .78rem; }
        .absen-table thead tr { background: #f8fafc; border-bottom: 2px solid var(--border, #e2e8f0); }
        .absen-table th { padding: 8px 10px; text-align: left; font-size: .68rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
        .absen-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .absen-table tbody tr:last-child td { border-bottom: none; }
        .absen-table tbody tr:hover td { background: #fafbfc; }
        .status-badge {
            display: inline-block; padding: 2px 8px;
            border-radius: 20px; font-size: .65rem; font-weight: 700;
        }
        .s-hadir  { background: #dcfce7; color: #15803d; }
        .s-sakit  { background: #dbeafe; color: #1d4ed8; }
        .s-izin   { background: #fef9c3; color: #a16207; }
        .s-alfa   { background: #fee2e2; color: #dc2626; }

        /* ── Wrap ── */
        .dash-wrap { padding: 16px 16px calc(var(--footer-h) + 80px); }

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
        .ab-btn-back    { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; flex: 0 0 auto; padding: 12px 14px; }
        .ab-btn-edit    { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .ab-btn-edit:hover { background: #fef3c7; }
        .ab-btn-pdf     { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .ab-btn-pdf:hover { background: #bbf7d0; }
        .ab-btn-delete  { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; flex: 0 0 auto; padding: 12px 14px; }
        .ab-btn-delete:hover { background: #fecaca; }

        .empty-table { text-align: center; padding: 24px 16px; color: #94a3b8; font-size: .82rem; }
        .empty-table i { display: block; font-size: 1.8rem; margin-bottom: 6px; opacity: .4; }
    </style>
@endpush

@section('content')

{{-- ── Profile Hero ── --}}
<div class="profile-hero">
    <div class="hero-nav">
        <a href="{{ route('siswa.index') }}" class="hero-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="hero-actions">
            <a href="{{ route('siswa.edit', $siswa) }}" class="hero-action-btn">
                <i class="fas fa-pen"></i> Edit
            </a>
            <a href="{{ route('siswa.export-cv', $siswa) }}" target="_blank" class="hero-action-btn">
                <i class="fas fa-file-pdf"></i> CV
            </a>
        </div>
    </div>
</div>

{{-- ── Profile Card (overlap) ── --}}
<div style="padding: 0 0 0 0;">
    <div class="profile-card">
        <div class="profile-avatar">
            @if ($siswa->foto)
                <img src="{{ Storage::url($siswa->foto) }}" alt="{{ $siswa->nama_lengkap }}">
            @else
                <i class="fas fa-user-graduate"></i>
            @endif
        </div>
        <div class="profile-info">
            <h3 class="profile-name">{{ $siswa->nama_lengkap }}</h3>
            <p class="profile-nisn">NISN: {{ $siswa->nisn }}{{ $siswa->nis ? ' · NIS: ' . $siswa->nis : '' }}</p>
            <div class="profile-tags">
                <span class="ptag ptag-purple">
                    <i class="fas fa-door-open"></i> {{ $siswa->kelas?->nama_kelas ?? '-' }}
                </span>
                <span class="ptag ptag-blue">
                    <i class="fas fa-{{ $siswa->jenis_kelamin === 'L' ? 'mars' : 'venus' }}"></i>
                    {{ $siswa->jenis_kelamin === 'L' ? 'L' : 'P' }}
                </span>
                <span class="ptag {{ $siswa->status_aktif ? 'ptag-green' : 'ptag-red' }}">
                    <i class="fas fa-circle" style="font-size:.5rem;"></i>
                    {{ $siswa->status_aktif ? 'Aktif' : 'Non Aktif' }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="dash-wrap">

    {{-- ── Statistik Absensi ── --}}
    @php
        $absenStats = [
            'hadir' => $siswa->absenSiswa->where('status', 'hadir')->count(),
            'sakit' => $siswa->absenSiswa->where('status', 'sakit')->count(),
            'izin'  => $siswa->absenSiswa->where('status', 'izin')->count(),
            'alfa'  => $siswa->absenSiswa->where('status', 'alfa')->count(),
        ];
    @endphp

    <div class="absen-stat-grid" style="margin-top:14px;">
        <div class="absen-stat">
            <div class="as-val" style="color:#15803d;">{{ $absenStats['hadir'] }}</div>
            <div class="as-lbl">Hadir</div>
        </div>
        <div class="absen-stat">
            <div class="as-val" style="color:#1d4ed8;">{{ $absenStats['sakit'] }}</div>
            <div class="as-lbl">Sakit</div>
        </div>
        <div class="absen-stat">
            <div class="as-val" style="color:#a16207;">{{ $absenStats['izin'] }}</div>
            <div class="as-lbl">Izin</div>
        </div>
        <div class="absen-stat">
            <div class="as-val" style="color:#dc2626;">{{ $absenStats['alfa'] }}</div>
            <div class="as-lbl">Alfa</div>
        </div>
    </div>

    {{-- ── Data Pribadi ── --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-id-card"></i></div>
            <h3>Data Pribadi</h3>
        </div>
        <div class="c-body" style="padding:12px 18px;">
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-hashtag"></i> NIS</span>
                <span class="dr-value">{{ $siswa->nis ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-id-badge"></i> NISN</span>
                <span class="dr-value">{{ $siswa->nisn }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-user"></i> Nama</span>
                <span class="dr-value">{{ $siswa->nama_lengkap }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-venus-mars"></i> Kelamin</span>
                <span class="dr-value">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-door-open"></i> Kelas</span>
                <span class="dr-value">{{ $siswa->kelas?->nama_kelas ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-graduation-cap"></i> Angkatan</span>
                <span class="dr-value">{{ $siswa->angkatan ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-map-pin"></i> Tempat Lahir</span>
                <span class="dr-value">{{ $siswa->tempat_lahir ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-birthday-cake"></i> Tgl Lahir</span>
                <span class="dr-value">{{ $siswa->tanggal_lahir?->translatedFormat('d F Y') ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-map-marker-alt"></i> Alamat</span>
                <span class="dr-value" style="max-width:200px; text-align:right;">{{ $siswa->alamat ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-home"></i> Desa</span>
                <span class="dr-value">{{ $siswa->desa ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-building"></i> Kelurahan</span>
                <span class="dr-value">{{ $siswa->kelurahan ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-city"></i> Kecamatan</span>
                <span class="dr-value">{{ $siswa->kecamatan ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-map"></i> Kabupaten</span>
                <span class="dr-value">{{ $siswa->kabupaten ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-envelope"></i> Kode Pos</span>
                <span class="dr-value">{{ $siswa->kode_pos ?: '-' }}</span>
            </div>
        </div>
    </div>

    {{-- ── Kontak & Orang Tua ── --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-phone-alt"></i></div>
            <h3>Kontak &amp; Orang Tua</h3>
        </div>
        <div class="c-body" style="padding:12px 18px;">
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-mobile-alt"></i> HP Siswa</span>
                <span class="dr-value">
                    @if ($siswa->no_hp_siswa)
                        <a href="tel:{{ $siswa->no_hp_siswa }}" style="color:#7c3aed;font-weight:700;">{{ $siswa->no_hp_siswa }}</a>
                    @else — @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-user-friends"></i> Ortu 1</span>
                <span class="dr-value">{{ $siswa->nama_ortu1 ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-phone"></i> HP Ortu 1</span>
                <span class="dr-value">
                    @if ($siswa->no_hp_ortu1)
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $siswa->no_hp_ortu1) }}" target="_blank"
                           style="color:#15803d;font-weight:700;">
                            <i class="fab fa-whatsapp"></i> {{ $siswa->no_hp_ortu1 }}
                        </a>
                    @else — @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-user-friends"></i> Ortu 2</span>
                <span class="dr-value">{{ $siswa->nama_ortu2 ?: '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-phone"></i> HP Ortu 2</span>
                <span class="dr-value">
                    @if ($siswa->no_hp_ortu2)
                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', $siswa->no_hp_ortu2) }}" target="_blank"
                           style="color:#15803d;font-weight:700;">
                            <i class="fab fa-whatsapp"></i> {{ $siswa->no_hp_ortu2 }}
                        </a>
                    @else — @endif
                </span>
            </div>
            <div class="detail-row">
                <span class="dr-label"><i class="fas fa-user-shield"></i> Wali</span>
                <span class="dr-value">{{ $siswa->nama_wali ?: '-' }}</span>
            </div>
        </div>
    </div>

    {{-- ── Riwayat Absensi ── --}}
    <div class="card">
        <div class="c-head">
            <div class="c-icon" style="background:#fef3c7; color:#b45309;"><i class="fas fa-clipboard-list"></i></div>
            <h3>Riwayat Absensi</h3>
            <span class="hbadge">10 terbaru</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="absen-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($siswa->absenSiswa->sortByDesc('tanggal')->take(10) as $absen)
                        <tr>
                            <td style="white-space:nowrap;">{{ $absen->tanggal->format('d/m/Y') }}</td>
                            <td>
                                <span style="font-size:.72rem; font-weight:600; color:{{ $absen->jenis === 'masuk' ? '#15803d' : '#1d4ed8' }};">
                                    {{ $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $cls = match($absen->status) {
                                        'hadir' => 's-hadir',
                                        'sakit' => 's-sakit',
                                        'izin'  => 's-izin',
                                        default => 's-alfa',
                                    };
                                @endphp
                                <span class="status-badge {{ $cls }}">{{ ucfirst($absen->status) }}</span>
                            </td>
                            <td style="color:#64748b;">{{ $absen->waktu_absen?->format('H:i') ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-table">
                                    <i class="fas fa-clipboard"></i>
                                    Belum ada data absensi
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ── Action Bar ── --}}
<div class="action-bar">
    <a href="{{ route('siswa.index') }}" class="ab-btn ab-btn-back">
        <i class="fas fa-arrow-left"></i>
    </a>
    <a href="{{ route('siswa.edit', $siswa) }}" class="ab-btn ab-btn-edit">
        <i class="fas fa-pen"></i> Edit
    </a>
    <a href="{{ route('siswa.export-cv', $siswa) }}" target="_blank" class="ab-btn ab-btn-pdf">
        <i class="fas fa-file-pdf"></i> Export CV
    </a>
    <button type="button" class="ab-btn ab-btn-delete" onclick="confirmDelete('{{ route('siswa.destroy', $siswa) }}')">
        <i class="fas fa-trash-alt"></i>
    </button>
</div>

<form id="deleteForm" method="POST" action="{{ route('siswa.destroy', $siswa) }}" style="display:none;">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function confirmDelete(url) {
    if (typeof Swal === 'undefined') {
        if (!confirm('Yakin menghapus siswa {{ $siswa->nama_lengkap }}? Data tidak dapat dikembalikan.')) return;
        document.getElementById('deleteForm').submit();
        return;
    }
    Swal.fire({
        title: 'Hapus Siswa?',
        html: `Yakin menghapus <strong>{{ $siswa->nama_lengkap }}</strong>?<br>
               <small style="color:#64748b;">Data absensi dan izin juga akan terhapus.</small>`,
        icon: 'warning',
        showCancelButton: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
            confirmButton: 'ab-btn ab-btn-delete',
            cancelButton: 'ab-btn ab-btn-back',
        },
        confirmButtonText: '<i class="fas fa-trash-alt"></i> Hapus',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
    }).then(result => {
        if (result.isConfirmed) document.getElementById('deleteForm').submit();
    });
}
</script>
@endpush