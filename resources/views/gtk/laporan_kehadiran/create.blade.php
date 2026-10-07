@extends('layouts.app')

@section('title', 'Buat Laporan Kehadiran')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Wrapper ── */
        .lkg-wrap {
            padding: 0 12px;
            max-width: 800px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        @media(min-width:768px) {
            .lkg-wrap {
                padding: 0 20px;
            }
        }

        @media(min-width:1024px) {
            .lkg-wrap {
                padding: 0 24px;
            }
        }

        /* ── Jadwal card ── */
        .jadwal-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            transition: box-shadow .2s, border-color .2s;
        }

        .jadwal-card:hover {
            box-shadow: 0 4px 14px rgba(0, 0, 0, .08);
            border-color: #f59e0b;
        }

        /* ── Jadwal head ── */
        .jadwal-head {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            background: #fffbeb;
        }

        @media(min-width:768px) {
            .jadwal-head {
                padding: 14px 18px;
            }
        }

        .jadwal-title {
            font-size: .9rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px;
        }

        @media(min-width:768px) {
            .jadwal-title {
                font-size: 1rem;
            }
        }

        .jadwal-meta {
            font-size: .72rem;
            color: #64748b;
            line-height: 1.7;
            display: flex;
            flex-wrap: wrap;
            gap: 2px 10px;
        }

        /* ── Status radio ── */
        .status-radio-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 14px 16px;
        }

        @media(min-width:480px) {
            .status-radio-group {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(min-width:768px) {
            .status-radio-group {
                grid-template-columns: repeat(3, 1fr);
                padding: 16px 18px;
                gap: 10px;
            }
        }

        @media(min-width:960px) {
            .status-radio-group {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        .status-radio {
            position: relative;
            cursor: pointer;
        }

        .status-radio input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .status-option {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 10px;
            border-radius: 9px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            transition: all .15s;
            cursor: pointer;
            height: 100%;
            box-sizing: border-box;
        }

        .status-option:hover {
            box-shadow: 0 3px 8px rgba(0, 0, 0, .06);
            transform: translateY(-1px);
        }

        .status-radio input[type="radio"]:checked+.status-option {
            border-color: var(--sc);
            background: var(--sb);
            box-shadow: 0 3px 10px rgba(0, 0, 0, .08);
        }

        .so-header {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .so-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--sc);
            flex-shrink: 0;
        }

        .so-label {
            font-weight: 700;
            color: #1e293b;
            font-size: .78rem;
            line-height: 1.3;
        }

        .so-desc {
            font-size: .67rem;
            color: #64748b;
            line-height: 1.4;
        }

        /* ── Catatan ── */
        .catatan-wrap {
            padding: 0 16px 14px;
        }

        @media(min-width:768px) {
            .catatan-wrap {
                padding: 0 18px 16px;
            }
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #f8fafc;
            resize: vertical;
            box-sizing: border-box;
            outline: none;
        }

        .form-input:focus {
            border-color: #f59e0b;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .1);
        }

        /* ── Kirim button (per-card) ── */
        .btn-kirim {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: calc(100% - 32px);
            margin: 0 16px 14px;
            padding: 12px 16px;
            border-radius: 11px;
            font-size: .875rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: all .18s;
            background: #f59e0b;
            color: #fff;
            box-shadow: 0 3px 12px rgba(245, 158, 11, .25);
        }

        @media(min-width:768px) {
            .btn-kirim {
                width: calc(100% - 36px);
                margin: 0 18px 16px;
            }
        }

        .btn-kirim:hover {
            filter: brightness(1.07);
        }

        .btn-kirim:active {
            transform: scale(.97);
        }

        .btn-kirim:disabled {
            opacity: .55;
            cursor: not-allowed;
            transform: none;
        }

        /* ── Empty state ── */
        .rekap-empty {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .rekap-empty i {
            font-size: 3rem;
            color: #e2e8f0;
            display: block;
            margin-bottom: 14px;
        }

        .rekap-empty strong {
            display: block;
            color: #0f172a;
            margin-bottom: 6px;
            font-size: 1rem;
        }

        /* ── Filter kelas (untuk admin/guru) ── */
        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
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

        .filter-toggle .ft-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-toggle .ft-chevron {
            transition: transform .2s;
            color: #64748b;
            font-size: .8rem;
        }

        .filter-toggle .ft-chevron.open {
            transform: rotate(180deg);
        }

        @media(min-width:768px) {
            .filter-toggle {
                display: none;
            }
        }

        .filter-body {
            padding: 12px 16px 16px;
            display: none;
        }

        .filter-body.open {
            display: block;
        }

        @media(min-width:768px) {
            .filter-body {
                display: block !important;
                padding: 16px;
            }
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            align-items: end;
        }

        @media(max-width:479px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }

        @media(min-width:768px) {
            .filter-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 12px;
            }
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
        }

        .form-input:focus {
            outline: 2px solid #f59e0b;
            outline-offset: -1px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: .72rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-view {
            background: #eff6ff;
            color: #1d4ed8;
        }

        /* ── Info box ── */
        .info-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 11px 14px;
            margin-bottom: 14px;
            font-size: .82rem;
            color: #78350f;
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }

        /* ── Action bar ── */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 0);
            left: 0;
            right: 0;
            padding: 10px 12px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                gap: 12px;
                justify-content: flex-end;
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
            white-space: nowrap;
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 130px;
                font-size: .875rem;
                padding: 12px 20px;
            }
        }

        .ab-btn:active {
            transform: scale(.97);
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap lkg-wrap" style="padding-top:var(--header-h,56px);padding-bottom: 148px;">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span
                    class="live-dot"></span>{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}</div>
            <h2><i class="fas fa-plus-circle"></i> Buat Laporan Kehadiran</h2>
            <p>Laporkan status kehadiran guru per jam pelajaran</p>
        </div>

        @if (session('success'))
            <div
                style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div
                style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if ($jadwalBelumLapor->isEmpty())
            {{-- Empty ── semua sudah dilaporkan ── --}}
            <div class="card" style="border-radius:12px;overflow:hidden;">
                <div class="rekap-empty">
                    <i class="fas fa-check-circle"></i>
                    <strong>Semua Jadwal Sudah Dilaporkan</strong>
                    Tidak ada jadwal yang belum dilaporkan untuk hari ini.
                    <br>
                    <a href="{{ route('kehadiran-guru.laporan') }}"
                        style="display:inline-flex;align-items:center;gap:5px;margin-top:14px;background:#eff6ff;color:#1d4ed8;padding:8px 16px;border-radius:8px;font-size:.82rem;font-weight:700;text-decoration:none;">
                        <i class="fas fa-list"></i> Lihat Laporan
                    </a>
                </div>
            </div>
        @else
            {{-- Filter kelas + pencarian (tidak tampil untuk siswa) ── --}}
            @if ($kelas->isNotEmpty())
                <form method="GET" action="{{ route('kehadiran-guru.create') }}" class="filter-section"
                    id="createFilterForm" style="margin-bottom:14px;">
                    <button type="button" class="filter-toggle" id="createFilterToggle" onclick="toggleCreateFilter()">
                        <span class="ft-left">
                            <i class="fas fa-filter"></i> Filter Kelas
                            @if ($filterKelasId)
                                <span
                                    style="background:#fef3c7;color:#b45309;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                            @endif
                        </span>
                        <i class="fas fa-chevron-down ft-chevron {{ $filterKelasId ? 'open' : '' }}"></i>
                    </button>
                    <div class="filter-body {{ $filterKelasId ? 'open' : '' }}" id="createFilterBody">
                        <div class="filter-grid">
                            <div>
                                <label class="form-label">Kelas</label>
                                <select name="kelas_id" class="form-input">
                                    <option value="">Semua Kelas</option>
                                    @foreach ($kelas as $k)
                                        <option value="{{ $k->id }}"
                                            {{ $filterKelasId == $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                            <div style="display:flex;gap:8px;align-items:flex-end;padding-top:4px;">
                                <button type="submit" class="action-btn btn-view"
                                    style="flex:1;justify-content:center;padding:10px;">
                                    <i class="fas fa-filter"></i> Terapkan
                                </button>
                                @if ($filterKelasId)
                                    <a href="{{ route('kehadiran-guru.create', ['tanggal' => $tanggal]) }}"
                                        class="action-btn"
                                        style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:10px 14px;text-decoration:none;">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            @endif

            {{-- Info box ── --}}
            <div class="info-box">
                <i class="fas fa-info-circle" style="flex-shrink:0;margin-top:1px;"></i>
                <span>Pilih status kehadiran untuk setiap jam pelajaran, lalu tekan <strong>Kirim Laporan</strong> pada
                    masing-masing kartu. Setiap kartu dikirim secara terpisah.</span>
            </div>

            {{-- ═══ TIAP JADWAL = FORM SENDIRI ═══ --}}
            @php
                $statusOptions = [
                    'hijau' => [
                        'color' => '#22c55e',
                        'bg' => '#dcfce7',
                        'label' => 'Hadir Tepat Waktu',
                        'desc' => '≤ 10 menit setelah bel',
                    ],
                    'kuning' => [
                        'color' => '#eab308',
                        'bg' => '#fef9c3',
                        'label' => 'Hadir Terlambat',
                        'desc' => '> 10 menit setelah bel',
                    ],
                    'merah' => [
                        'color' => '#ef4444',
                        'bg' => '#fee2e2',
                        'label' => 'Tidak Hadir',
                        'desc' => 'Tidak hadir tanpa tugas',
                    ],
                    'abu' => [
                        'color' => '#64748b',
                        'bg' => '#f1f5f9',
                        'label' => 'Tidak Hadir + Ada Tugas',
                        'desc' => 'Tidak hadir tapi ada tugas',
                    ],
                    'biru' => [
                        'color' => '#3b82f6',
                        'bg' => '#dbeafe',
                        'label' => 'Pergi + Ada Tugas',
                        'desc' => 'Datang lalu pergi dengan tugas',
                    ],
                    'pink' => [
                        'color' => '#ec4899',
                        'bg' => '#fce7f3',
                        'label' => 'Pergi + No Tugas',
                        'desc' => 'Datang lalu pergi tanpa tugas',
                    ],
                ];
            @endphp

            @foreach ($jadwalBelumLapor as $jadwal)
                @php $namaJam = $jamMap[$jadwal->jam_ke] ?? ('Jam Ke-' . $jadwal->jam_ke); @endphp
                <form method="POST" action="{{ route('kehadiran-guru.store') }}" class="laporan-form"
                    data-mata-pelajaran="{{ $jadwal->mata_pelajaran }}" data-jam="{{ $namaJam }}">
                    @csrf
                    <input type="hidden" name="jadwal_kbm_id" value="{{ $jadwal->id }}">

                    <div class="jadwal-card">
                        {{-- Header ── --}}
                        <div class="jadwal-head">
                            <h4 class="jadwal-title">
                                {{ $namaJam }} — {{ $jadwal->mata_pelajaran }}
                            </h4>
                            <div class="jadwal-meta">
                                <span><i class="fas fa-user-tie"></i> {{ $jadwal->gtk?->nama_lengkap ?? '-' }}</span>
                                <span><i class="fas fa-chalkboard"></i> {{ $jadwal->kelas?->nama_kelas ?? '-' }}</span>
                                <span><i class="fas fa-clock"></i>
                                    @if ($jadwal->jam_mulai && $jadwal->jam_selesai)
                                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} –
                                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Status radio ── --}}
                        <div class="status-radio-group">
                            @foreach ($statusOptions as $val => $opt)
                                <label class="status-radio" style="--sc:{{ $opt['color'] }};--sb:{{ $opt['bg'] }};">
                                    <input type="radio" name="status" value="{{ $val }}" required>
                                    <div class="status-option">
                                        <div class="so-header">
                                            <div class="so-dot"></div>
                                            <div class="so-label">{{ $opt['label'] }}</div>
                                        </div>
                                        <div class="so-desc">{{ $opt['desc'] }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        {{-- Catatan ── --}}
                        <div class="catatan-wrap">
                            <label class="form-label">
                                Catatan
                                <span style="font-weight:400;color:#94a3b8;margin-left:3px;">(Opsional)</span>
                            </label>
                            <textarea name="catatan" class="form-input" rows="2" placeholder="Tambahkan catatan jika diperlukan..."
                                maxlength="500"></textarea>
                        </div>

                        {{-- Kirim ── --}}
                        <button type="submit" class="btn-kirim">
                            <i class="fas fa-paper-plane"></i>
                            Kirim Laporan — {{ $namaJam }}
                        </button>
                    </div>
                </form>
            @endforeach

            {{-- Jadwal yang sudah dilaporkan ── info saja ── --}}
            @if ($jadwalHariIni->count() > $jadwalBelumLapor->count())
                @php $sudahLapor = $jadwalHariIni->diff($jadwalBelumLapor); @endphp
                @if ($sudahLapor->count() > 0)
                    <div
                        style="background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:12px 16px;margin-bottom:14px;">
                        <div style="font-size:.8rem;font-weight:700;color:#15803d;margin-bottom:8px;">
                            <i class="fas fa-check-circle"></i> {{ $sudahLapor->count() }} jadwal sudah dilaporkan hari ini
                        </div>
                        @foreach ($sudahLapor as $jd)
                            <div
                                style="font-size:.75rem;color:#166534;padding:3px 0;border-bottom:1px solid #bbf7d0;display:flex;align-items:center;gap:6px;">
                                <i class="fas fa-check" style="font-size:.65rem;"></i>
                                {{ $jamMap[$jd->jam_ke] ?? 'Jam Ke-' . $jd->jam_ke }} — {{ $jd->mata_pelajaran }}
                                <span style="color:#16a34a;margin-left:auto;font-weight:600;">✓ Sudah Lapor</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        @endif

    </div>

    {{-- Action Bar ── hanya Kembali ── --}}
    <div class="action-bar">
        <a href="{{ route('kehadiran-guru.laporan') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Laporan
        </a>
    </div>
@endsection

@push('scripts')
    <script>
        function toggleCreateFilter() {
            const body = document.getElementById('createFilterBody');
            const chevron = document.querySelector('#createFilterToggle .ft-chevron');
            body.classList.toggle('open');
            if (chevron) chevron.classList.toggle('open');
        }

        document.addEventListener('DOMContentLoaded', function() {

            /* ── Konfirmasi sebelum submit tiap form ── */
            document.querySelectorAll('.laporan-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const mapel = form.dataset.mataPelajaran ?? '';
                    const jam = form.dataset.jam ?? '';
                    const radio = form.querySelector('input[name="status"]:checked');

                    if (!radio) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Belum Memilih Status',
                            text: 'Silakan pilih status kehadiran terlebih dahulu.',
                            confirmButtonColor: '#f59e0b',
                        });
                        return;
                    }

                    const statusLabel = radio.closest('.status-radio')
                        ?.querySelector('.so-label')?.textContent ?? radio.value;

                    Swal.fire({
                        icon: 'question',
                        title: 'Konfirmasi Laporan',
                        html: `Kirim laporan untuk <strong>Jam ${jam}${mapel ? ' – ' + mapel : ''}</strong>?<br>
                       <small style="color:#6b7280;">Status: <strong>${statusLabel}</strong></small>`,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Kirim',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#94a3b8',
                    }).then(function(r) {
                        if (r.isConfirmed) {
                            const btn = form.querySelector('.btn-kirim');
                            if (btn) {
                                btn.disabled = true;
                                btn.innerHTML =
                                    '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
                            }
                            form.submit();
                        }
                    });
                });
            });

            // ── SweetAlert untuk error validasi server ──
            @if ($errors->any())
                if (typeof Swal !== 'undefined' && !window.__swalValidationShown) {
                    window.__swalValidationShown = true;
                    const errorList = @json($errors->all());
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: '<ul style="padding:0;margin:0;list-style:none;text-align:left;">' +
                            errorList.map(m => '<li style="margin-bottom:4px;">• ' + m + '</li>').join('') +
                            '</ul>',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#ef4444',
                    });
                }
            @endif
        });
    </script>
@endpush
