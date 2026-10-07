@extends('layouts.app')

@section('title', 'Laporan Kehadiran Guru - Siswa')

@push('styles')
    @include('components.event-styles')
    <style>
        .jadwal-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            transition: all .2s;
        }

        .jadwal-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
            border-color: var(--event-primary, #f59e0b);
        }

        /* Kartu yang sudah dilaporkan */
        .jadwal-card.sudah-lapor {
            border-color: #d1fae5;
            background: #f0fdf4;
        }

        .jadwal-card.sudah-lapor .jadwal-title {
            color: #166534;
        }

        /* Badge status sudah lapor */
        .badge-sudah-lapor {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            background: #d1fae5;
            color: #166534;
            white-space: nowrap;
        }

        /* Badge waktu sisa edit */
        .badge-waktu-edit {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
        }

        .badge-expired {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-jendela {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: .69rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-jendela.aktif {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-jendela.menunggu {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-jendela.habis,
        .badge-jendela.tidak-lengkap {
            background: #fee2e2;
            color: #991b1b;
        }

        .jadwal-card.terkunci {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .jadwal-card.terkunci .status-radio,
        .jadwal-card.terkunci .form-input {
            opacity: .68;
        }

        .window-note {
            margin: 8px 0 0;
            color: #64748b;
            font-size: .75rem;
            line-height: 1.5;
        }

        .jadwal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
            gap: 8px;
        }

        .jadwal-title {
            font-weight: 600;
            color: var(--text-main);
            margin: 0;
        }

        .jadwal-meta {
            font-size: .75rem;
            color: var(--text-muted);
            margin: 4px 0;
        }

        /* Status yang sedang terpilih (tampilan readonly di kartu sudah lapor) */
        .status-current {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 2px solid;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .status-current-dot {
            width: 13px;
            height: 13px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-current-label {
            font-weight: 700;
            font-size: .875rem;
        }

        /* Collapsible edit form */
        .edit-form-wrap {
            display: none;
            margin-top: 10px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 12px;
            animation: fadeIn .2s ease;
        }

        .edit-form-wrap.open {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tombol edit kecil */
        .btn-edit-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 700;
            border: 2px solid #f59e0b;
            background: transparent;
            color: #92400e;
            cursor: pointer;
            font-family: inherit;
            transition: all .18s;
        }

        .btn-edit-toggle:hover {
            background: #fef3c7;
        }

        .btn-edit-toggle.active {
            background: #fef3c7;
        }

        .status-radio-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-top: 10px;
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

        .status-radio .status-option {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            background: #fff;
            transition: all .2s ease;
            cursor: pointer;
        }

        .status-radio .status-option:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, .05);
        }

        .status-radio input[type="radio"]:checked+.status-option {
            border-color: var(--status-color);
            background: var(--status-bg);
            box-shadow: 0 4px 14px rgba(0, 0, 0, .07);
        }

        .status-radio .status-option::before {
            content: "";
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: var(--status-color);
            margin-top: 3px;
            flex-shrink: 0;
        }

        .status-label {
            font-weight: 700;
            color: #1e293b;
            flex: 1;
            font-size: .85rem;
        }

        .status-desc {
            font-size: .73rem;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.4;
        }

        .form-group {
            margin-bottom: 12px;
        }

        .form-label {
            display: block;
            font-size: .82rem;
            font-weight: 600;
            color: var(--text-main, #0f172a);
            margin-bottom: 6px;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: var(--text-main, #0f172a);
            background: #fff;
            resize: vertical;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--event-primary, #f59e0b);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .12);
        }

        .card-divider {
            border: none;
            border-top: 1px dashed #e2e8f0;
            margin: 14px 0;
        }

        .warning-card {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .warning-card .warning-icon {
            color: #856404;
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .warning-card .warning-title {
            color: #856404;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .warning-card .warning-text {
            color: #856404;
            font-size: .85rem;
            margin: 0;
        }

        /* ── Per-card Submit Button ── */
        .btn-kirim {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: all .18s;
            background: var(--event-primary, #f59e0b);
            color: #fff;
            box-shadow: 0 3px 12px rgba(245, 158, 11, .25);
            margin-top: 4px;
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

        /* Tombol simpan perubahan (edit) */
        .btn-simpan {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 11px 16px;
            border-radius: 10px;
            font-size: .875rem;
            font-weight: 700;
            border: 2px solid #059669;
            cursor: pointer;
            font-family: inherit;
            transition: all .18s;
            background: #059669;
            color: #fff;
            margin-top: 8px;
        }

        .btn-simpan:hover {
            background: #047857;
        }

        .btn-simpan:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        /* Section divider label */
        .section-label {
            font-size: .78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            margin: 18px 0 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        /* ── Fixed Action Bar ── */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .875rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .ab-btn-back:hover {
            background: #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 72px);">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                Laporan Kehadiran Guru
            </div>
            <h2>
                <i class="fas fa-user-graduate"></i>
                Laporkan Guru Tidak Hadir
            </h2>
            <p>Bantu laporkan jika guru tidak hadir di kelas</p>
        </div>

        {{-- Flash via SweetAlert –– ditangani di JS bawah --}}
        @if (session('error'))
            <span id="flash-error" data-msg="{{ session('error') }}" hidden></span>
        @endif
        @if (session('success'))
            <span id="flash-success" data-msg="{{ session('success') }}" hidden></span>
        @endif

        {{-- Warning --}}
        <div class="warning-card">
            <div class="warning-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="warning-title">PENTING!</div>
            <div class="warning-text">
                Fitur ini digunakan jika guru tidak hadir di kelas dan belum ada laporan dari guru tersebut.
                Pastikan melaporkan dengan jujur dan bertanggung jawab.
                <br><strong>Laporan dapat dikirim mulai jam pelajaran dimulai dan dapat diperbarui hingga 15 menit
                    setelah jam pelajaran selesai.</strong>
            </div>
        </div>

        @php
            $siswa = auth()->user()->siswa;
            $tanggal = now()->toDateString();

            $jadwalHariIni = \App\Models\JadwalKBM::with(['gtk', 'kelas'])
                ->where('hari', now()->locale('id')->dayName)
                ->where('kelas_id', $siswa->kelas_id)
                ->orderBy('jam_ke')
                ->get();

            // Ambil semua laporan hari ini untuk kelas siswa (termasuk yang dibuat siswa lain/guru)
            $laporanHariIni = \App\Models\LaporanKehadiranGuru::where('tanggal', $tanggal)
                ->where('kelas_id', $siswa->kelas_id)
                ->get()
                ->keyBy('jadwal_kbm_id');

            $sudahDilaporkanIds = $laporanHariIni->keys()->toArray();

            $statusOptions = [
                'hijau' => [
                    'color' => '#22c55e',
                    'bg' => '#dcfce7',
                    'label' => 'Hadir Tepat Waktu',
                    'desc' => 'Guru hadir sesuai jadwal.',
                ],
                'kuning' => [
                    'color' => '#eab308',
                    'bg' => '#fef9c3',
                    'label' => 'Hadir Terlambat',
                    'desc' => 'Guru hadir melewati jam mulai KBM.',
                ],
                'biru' => [
                    'color' => '#3b82f6',
                    'bg' => '#dbeafe',
                    'label' => 'Pergi + Ada Tugas',
                    'desc' => 'Guru hadir lalu meninggalkan kelas dengan tugas.',
                ],
                'merah' => [
                    'color' => '#ef4444',
                    'bg' => '#fee2e2',
                    'label' => 'Tidak Hadir',
                    'desc' => 'Guru tidak hadir dan tidak ada tugas.',
                ],
                'abu' => [
                    'color' => '#64748b',
                    'bg' => '#f1f5f9',
                    'label' => 'Tidak Hadir + Ada Tugas',
                    'desc' => 'Guru tidak hadir tetapi memberikan tugas.',
                ],
                'pink' => [
                    'color' => '#ec4899',
                    'bg' => '#fce7f3',
                    'label' => 'Pergi + No Tugas',
                    'desc' => 'Guru hadir lalu meninggalkan kelas tanpa tugas.',
                ],
            ];

            $jadwalBelumLapor = $jadwalHariIni->filter(fn($j) => !in_array($j->id, $sudahDilaporkanIds));
            $jadwalSudahLapor = $jadwalHariIni->filter(fn($j) => in_array($j->id, $sudahDilaporkanIds));
        @endphp

        @if ($jadwalHariIni->isEmpty())
            <div class="card">
                <div class="c-body" style="text-align:center; padding:40px 20px;">
                    <div style="font-size:3rem; color:#e2e8f0; margin-bottom:16px;">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3 style="color:var(--text-main); margin-bottom:8px;">Tidak Ada Jadwal Hari Ini</h3>
                    <p style="color:var(--text-muted); margin-bottom:20px;">
                        Tidak ada jadwal pelajaran untuk kelasmu hari ini.
                    </p>
                    <a href="{{ route('absen.index') }}" class="action-btn btn-view">
                        <i class="fas fa-arrow-left"></i> Kembali ke Absen
                    </a>
                </div>
            </div>
        @else
            {{-- ════════════════════════════════════════════
                 SECTION: JADWAL YANG SUDAH ADA LAPORAN
            ═════════════════════════════════════════════ --}}
            @if ($jadwalSudahLapor->isNotEmpty())
                <div class="section-label">
                    <i class="fas fa-check-circle" style="color:#059669;"></i>
                    Sudah Dilaporkan
                </div>

                @foreach ($jadwalSudahLapor as $jadwal)
                    @php
                        $laporan = $laporanHariIni[$jadwal->id];
                        $statusInfo = $statusOptions[$laporan->status] ?? [
                            'color' => '#94a3b8',
                            'bg' => '#f8fafc',
                            'label' => $laporan->status_label,
                            'desc' => '',
                        ];
                        $waktuTerkonfigurasi = $jadwal->jam_mulai && $jadwal->jam_selesai;
                        $mulaiLaporan = $waktuTerkonfigurasi
                            ? \Carbon\Carbon::parse($tanggal . ' ' . $jadwal->jam_mulai->format('H:i:s'))
                            : null;
                        $batasEdit = $waktuTerkonfigurasi
                            ? \Carbon\Carbon::parse($tanggal . ' ' . $jadwal->jam_selesai->format('H:i:s'))->addMinutes(
                                15,
                            )
                            : null;
                        $belumDibuka = $waktuTerkonfigurasi && now()->lt($mulaiLaporan);
                        $masaBisa = $waktuTerkonfigurasi && now()->gte($mulaiLaporan) && now()->lte($batasEdit);
                        $sisaDetik = $masaBisa ? max(0, now()->diffInSeconds($batasEdit, false)) : 0;
                        $sisaEditLabel = sprintf('%02d:%02d', floor($sisaDetik / 60), $sisaDetik % 60);

                    @endphp

                    <div class="jadwal-card sudah-lapor" id="card-{{ $jadwal->id }}">
                        <div class="jadwal-header">
                            <div style="flex:1;">
                                <h4 class="jadwal-title">
                                    Jam {{ $jadwal->jam_ke }} — {{ $jadwal->mata_pelajaran }}
                                </h4>
                                <div class="jadwal-meta">
                                    <i class="fas fa-user-tie"></i> {{ $jadwal->gtk->nama_lengkap }}&nbsp;•&nbsp;
                                    <i class="fas fa-clock"></i> {{ $jadwal->jam_mulai }} – {{ $jadwal->jam_selesai }}
                                </div>
                            </div>
                            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
                                <span class="badge-sudah-lapor">
                                    <i class="fas fa-check"></i> Sudah Lapor
                                </span>
                                @if ($masaBisa)
                                    <span class="badge-waktu-edit" id="timer-{{ $laporan->id }}"
                                        data-sisa="{{ $sisaDetik }}">
                                        <i class="fas fa-clock"></i>
                                        Sisa edit: <span class="countdown">{{ $sisaEditLabel }}</span>
                                    </span>
                                @elseif ($belumDibuka)
                                    <span class="badge-waktu-edit">
                                        <i class="fas fa-lock"></i> Dibuka {{ $mulaiLaporan->format('H:i') }}
                                    </span>
                                @else
                                    <span class="badge-waktu-edit badge-expired">
                                        <i class="fas fa-lock"></i> Waktu edit habis
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Status saat ini --}}
                        <div class="status-current"
                            style="border-color:{{ $statusInfo['color'] }}; background:{{ $statusInfo['bg'] }};">
                            <div class="status-current-dot" style="background:{{ $statusInfo['color'] }};"></div>
                            <div>
                                <div class="status-current-label">{{ $statusInfo['label'] }}</div>
                                @if ($laporan->catatan)
                                    <div style="font-size:.75rem;color:#64748b;margin-top:2px;">
                                        <i class="fas fa-comment-alt"></i> {{ $laporan->catatan }}
                                    </div>
                                @endif
                            </div>
                        </div>


                        @if ($masaBisa)
                            <button type="button" class="btn-edit-toggle" id="toggle-{{ $laporan->id }}"
                                onclick="toggleEdit({{ $laporan->id }})">
                                <i class="fas fa-pen"></i> Ubah Laporan
                            </button>

                            {{-- Form Edit Inline --}}
                            <div class="edit-form-wrap" id="edit-form-{{ $laporan->id }}">
                                <form method="POST" action="{{ route('kehadiran-guru.update-siswa', $laporan->id) }}"
                                    class="edit-form" data-mata-pelajaran="{{ $jadwal->mata_pelajaran }}"
                                    data-jam="{{ $jadwal->jam_ke }}" data-laporan-id="{{ $laporan->id }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="form-label" style="margin-top:4px;">Pilih Status Baru:</div>
                                    <div class="status-radio-group">
                                        @foreach ($statusOptions as $value => $opt)
                                            <label class="status-radio"
                                                style="--status-color:{{ $opt['color'] }};--status-bg:{{ $opt['bg'] }};">
                                                <input type="radio" name="status" value="{{ $value }}"
                                                    {{ $laporan->status === $value ? 'checked' : '' }} required>
                                                <div class="status-option">
                                                    <div>
                                                        <div class="status-label">{{ $opt['label'] }}</div>
                                                        <div class="status-desc">{{ $opt['desc'] }}</div>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="form-group" style="margin-top:12px;">
                                        <label class="form-label">Catatan
                                            <span style="color:var(--text-muted);font-weight:400;">(Opsional)</span>
                                        </label>
                                        <textarea name="catatan" class="form-input" rows="2" placeholder="Tambahkan catatan jika diperlukan..."
                                            maxlength="500">{{ $laporan->catatan }}</textarea>
                                    </div>

                                    <button type="submit" class="btn-simpan">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                </form>
                            </div>
                        @elseif ($belumDibuka)
                            <p style="font-size:.75rem;color:#94a3b8;margin-top:6px;margin-bottom:0;">
                                <i class="fas fa-lock"></i> Edit laporan baru dapat dilakukan mulai pukul
                                {{ $mulaiLaporan->format('H:i') }}.
                            </p>
                        @elseif (!$waktuTerkonfigurasi)
                            <p style="font-size:.75rem;color:#94a3b8;margin-top:6px;margin-bottom:0;">
                                <i class="fas fa-lock"></i> Jam pelajaran belum dikonfigurasi.
                            </p>
                        @else
                            <p style="font-size:.75rem;color:#94a3b8;margin-top:6px;margin-bottom:0;">
                                <i class="fas fa-lock"></i> Waktu edit telah habis (melewati 15 menit setelah jam pelajaran
                                selesai).
                            </p>
                        @endif
                    </div>
                @endforeach
            @endif

            {{-- ════════════════════════════════════════════
                 SECTION: JADWAL YANG BELUM DILAPORKAN
            ═════════════════════════════════════════════ --}}
            @if ($jadwalBelumLapor->isNotEmpty())
                <div class="section-label">
                    <i class="fas fa-exclamation-circle" style="color:#f59e0b;"></i>
                    Belum Dilaporkan
                </div>

                {{-- Info --}}
                <div class="card">
                    <div class="c-head">
                        <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-info-circle"></i></div>
                        <h3>Informasi Laporan</h3>
                    </div>
                    <div class="c-body" style="padding:16px;">
                        <p style="color:var(--text-muted);font-size:.84rem;line-height:1.55;margin:0;">
                            Pilih jadwal yang gurunya tidak hadir. Tekan
                            <strong>Kirim Laporan</strong> pada masing-masing kartu.
                        </p>
                    </div>
                </div>

                {{-- ═══ TIAP JADWAL = FORM SENDIRI ═══ --}}
                @foreach ($jadwalBelumLapor as $jadwal)
                    @php
                        $waktuTerkonfigurasi = $jadwal->jam_mulai && $jadwal->jam_selesai;
                        $mulaiLaporan = $waktuTerkonfigurasi
                            ? \Carbon\Carbon::parse($tanggal . ' ' . $jadwal->jam_mulai->format('H:i:s'))
                            : null;
                        $batasLaporan = $waktuTerkonfigurasi
                            ? \Carbon\Carbon::parse($tanggal . ' ' . $jadwal->jam_selesai->format('H:i:s'))->addMinutes(
                                15,
                            )
                            : null;
                        $belumDibuka = $waktuTerkonfigurasi && now()->lt($mulaiLaporan);
                        $bisaLapor = $waktuTerkonfigurasi && now()->gte($mulaiLaporan) && now()->lte($batasLaporan);
                    @endphp
                    <form method="POST" action="{{ route('kehadiran-guru.lapor-siswa') }}" class="laporan-form"
                        data-mata-pelajaran="{{ $jadwal->mata_pelajaran }}" data-jam="{{ $jadwal->jam_ke }}"
                        data-window-start="{{ $mulaiLaporan ? $mulaiLaporan->timestamp * 1000 : '' }}"
                        data-window-end="{{ $batasLaporan ? $batasLaporan->timestamp * 1000 : '' }}"
                        data-jam-mulai="{{ $mulaiLaporan?->format('H:i') }}"
                        data-batas-akhir="{{ $batasLaporan?->format('H:i') }}">
                        @csrf
                        <input type="hidden" name="jadwal_kbm_id" value="{{ $jadwal->id }}">

                        <div class="jadwal-card {{ $bisaLapor ? '' : 'terkunci' }}">
                            <div class="jadwal-header">
                                <div style="flex:1;">
                                    <h4 class="jadwal-title">
                                        Jam {{ $jadwal->jam_ke }} — {{ $jadwal->mata_pelajaran }}
                                    </h4>
                                    <div class="jadwal-meta">
                                        <i class="fas fa-user-tie"></i> {{ $jadwal->gtk->nama_lengkap }}&nbsp;•&nbsp;
                                        <i class="fas fa-clock"></i> {{ $jadwal->jam_mulai }} –
                                        {{ $jadwal->jam_selesai }}
                                    </div>
                                </div>
                                <span
                                    class="badge-jendela {{ !$waktuTerkonfigurasi ? 'tidak-lengkap' : ($bisaLapor ? 'aktif' : ($belumDibuka ? 'menunggu' : 'habis')) }}">
                                    @if (!$waktuTerkonfigurasi)
                                        <i class="fas fa-exclamation-circle"></i> Jam belum diatur
                                    @elseif ($bisaLapor)
                                        <i class="fas fa-unlock"></i> Aktif s.d. {{ $batasLaporan->format('H:i') }}
                                    @elseif ($belumDibuka)
                                        <i class="fas fa-lock"></i> Dibuka {{ $mulaiLaporan->format('H:i') }}
                                    @else
                                        <i class="fas fa-lock"></i> Waktu habis
                                    @endif
                                </span>
                            </div>

                            <p class="window-note">
                                @if (!$waktuTerkonfigurasi)
                                    Jam mulai dan selesai pelajaran belum dikonfigurasi.
                                @elseif ($bisaLapor)
                                    Laporan dapat dikirim atau diperbarui sampai pukul {{ $batasLaporan->format('H:i') }}.
                                @elseif ($belumDibuka)
                                    Laporan baru dapat dikirim mulai pukul {{ $mulaiLaporan->format('H:i') }}.
                                @else
                                    Batas pengiriman laporan pukul {{ $batasLaporan->format('H:i') }} telah terlewati.
                                @endif
                            </p>

                            <div class="status-radio-group">
                                @foreach ($statusOptions as $value => $opt)
                                    <label class="status-radio"
                                        style="--status-color:{{ $opt['color'] }};--status-bg:{{ $opt['bg'] }};">
                                        <input type="radio" name="status" value="{{ $value }}" required
                                            @disabled(!$bisaLapor)>
                                        <div class="status-option">
                                            <div>
                                                <div class="status-label">{{ $opt['label'] }}</div>
                                                <div class="status-desc">{{ $opt['desc'] }}</div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <hr class="card-divider">

                            <div class="form-group">
                                <label class="form-label">Catatan
                                    <span style="color:var(--text-muted); font-weight:400;">(Opsional)</span>
                                </label>
                                <textarea name="catatan" class="form-input" rows="2" placeholder="Tambahkan catatan jika diperlukan..."
                                    maxlength="500" @disabled(!$bisaLapor)></textarea>
                            </div>

                            <button type="submit" class="btn-kirim" @disabled(!$bisaLapor)>
                                @if (!$waktuTerkonfigurasi)
                                    <i class="fas fa-lock"></i> Jam Pelajaran Belum Diatur
                                @elseif ($bisaLapor)
                                    <i class="fas fa-paper-plane"></i> Kirim Laporan Jam {{ $jadwal->jam_ke }}
                                @elseif ($belumDibuka)
                                    <i class="fas fa-lock"></i> Dibuka Pukul {{ $mulaiLaporan->format('H:i') }}
                                @else
                                    <i class="fas fa-lock"></i> Waktu Laporan Habis
                                @endif
                            </button>
                        </div>
                    </form>
                @endforeach
            @else
                {{-- Semua sudah dilaporkan --}}
                <div class="card" style="margin-top:8px;">
                    <div class="c-body" style="text-align:center; padding:28px 20px;">
                        <div style="font-size:2.5rem; color:#86efac; margin-bottom:12px;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 style="color:var(--text-main); margin-bottom:6px;">Semua Jadwal Sudah Ada Laporan</h3>
                        <p style="color:var(--text-muted); margin:0;">
                            Semua jadwal pelajaran hari ini sudah memiliki laporan kehadiran.
                        </p>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Action Bar: hanya Kembali --}}
    <div class="action-bar">
        <a href="{{ route('absen.index') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Absen
        </a>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ── 1. Flash session → SweetAlert ── */
            const elErr = document.getElementById('flash-error');
            const elOk = document.getElementById('flash-success');

            if (elErr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: elErr.dataset.msg,
                    confirmButtonColor: '#f59e0b',
                });
            }

            if (elOk) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: elOk.dataset.msg,
                    confirmButtonColor: '#f59e0b',
                    timer: 3000,
                    timerProgressBar: true,
                });
            }

            /* ── 2. Konfirmasi SweetAlert sebelum submit (form KIRIM baru) ── */
            const laporanForms = document.querySelectorAll('.laporan-form');

            function refreshLaporanWindow(form) {
                if (form.dataset.submitting === 'true') return;

                const mulai = Number(form.dataset.windowStart);
                const batasAkhir = Number(form.dataset.windowEnd);
                const jamMulai = form.dataset.jamMulai ?? '';
                const jamBatasAkhir = form.dataset.batasAkhir ?? '';
                const jamKe = form.dataset.jam ?? '';
                const sekarang = Date.now();
                const terkonfigurasi = mulai > 0 && batasAkhir > 0;
                const aktif = terkonfigurasi && sekarang >= mulai && sekarang <= batasAkhir;
                const menunggu = terkonfigurasi && sekarang < mulai;
                const card = form.querySelector('.jadwal-card');
                const badge = form.querySelector('.badge-jendela');
                const note = form.querySelector('.window-note');
                const button = form.querySelector('.btn-kirim');

                form.querySelectorAll('input[name="status"], textarea[name="catatan"]').forEach(function(control) {
                    control.disabled = !aktif;
                });

                if (card) card.classList.toggle('terkunci', !aktif);
                if (button) button.disabled = !aktif;

                if (!terkonfigurasi) {
                    if (badge) {
                        badge.className = 'badge-jendela tidak-lengkap';
                        badge.innerHTML = '<i class="fas fa-exclamation-circle"></i> Jam belum diatur';
                    }
                    if (note) note.textContent = 'Jam mulai dan selesai pelajaran belum dikonfigurasi.';
                    if (button) button.innerHTML = '<i class="fas fa-lock"></i> Jam Pelajaran Belum Diatur';
                    return;
                }

                if (aktif) {
                    if (badge) {
                        badge.className = 'badge-jendela aktif';
                        badge.innerHTML = '<i class="fas fa-unlock"></i> Aktif s.d. ' + jamBatasAkhir;
                    }
                    if (note) note.textContent = 'Laporan dapat dikirim atau diperbarui sampai pukul ' +
                        jamBatasAkhir + '.';
                    if (button) button.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Laporan Jam ' + jamKe;
                    return;
                }

                if (menunggu) {
                    if (badge) {
                        badge.className = 'badge-jendela menunggu';
                        badge.innerHTML = '<i class="fas fa-lock"></i> Dibuka ' + jamMulai;
                    }
                    if (note) note.textContent = 'Laporan baru dapat dikirim mulai pukul ' + jamMulai + '.';
                    if (button) button.innerHTML = '<i class="fas fa-lock"></i> Dibuka Pukul ' + jamMulai;
                    return;
                }

                if (badge) {
                    badge.className = 'badge-jendela habis';
                    badge.innerHTML = '<i class="fas fa-lock"></i> Waktu habis';
                }
                if (note) note.textContent = 'Batas pengiriman laporan pukul ' + jamBatasAkhir +
                ' telah terlewati.';
                if (button) button.innerHTML = '<i class="fas fa-lock"></i> Waktu Laporan Habis';
            }

            laporanForms.forEach(refreshLaporanWindow);
            setInterval(function() {
                laporanForms.forEach(refreshLaporanWindow);
            }, 1000);

            laporanForms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const mapel = form.dataset.mataPelajaran ?? '';
                    const jam = form.dataset.jam ?? '';
                    const radio = form.querySelector('input[name="status"]:checked');

                    if (!radio) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Belum Memilih Status',
                            text: 'Silakan pilih status kehadiran guru terlebih dahulu.',
                            confirmButtonColor: '#f59e0b',
                        });
                        return;
                    }

                    const statusLabel = radio.closest('.status-radio')
                        .querySelector('.status-label')?.textContent ?? radio.value;

                    Swal.fire({
                        icon: 'question',
                        title: 'Konfirmasi Laporan',
                        html: `Laporkan kondisi <strong>Jam ${jam} – ${mapel}</strong>?<br>
                               <small style="color:#6b7280;">Status: <b>${statusLabel}</b></small>`,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Kirim',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#f59e0b',
                        cancelButtonColor: '#94a3b8',
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            form.dataset.submitting = 'true';
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

            /* ── 3. Konfirmasi SweetAlert sebelum submit (form EDIT) ── */
            document.querySelectorAll('.edit-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const mapel = form.dataset.mataPelajaran ?? '';
                    const jam = form.dataset.jam ?? '';
                    const radio = form.querySelector('input[name="status"]:checked');

                    if (!radio) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Belum Memilih Status',
                            text: 'Silakan pilih status kehadiran guru terlebih dahulu.',
                            confirmButtonColor: '#059669',
                        });
                        return;
                    }

                    const statusLabel = radio.closest('.status-radio')
                        .querySelector('.status-label')?.textContent ?? radio.value;

                    Swal.fire({
                        icon: 'question',
                        title: 'Ubah Laporan?',
                        html: `Perbarui laporan <strong>Jam ${jam} – ${mapel}</strong>?<br>
                               <small style="color:#6b7280;">Status baru: <b>${statusLabel}</b></small>`,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#059669',
                        cancelButtonColor: '#94a3b8',
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            const btn = form.querySelector('.btn-simpan');
                            if (btn) {
                                btn.disabled = true;
                                btn.innerHTML =
                                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                            }
                            form.submit();
                        }
                    });
                });
            });

            /* ── 4. Countdown timer untuk masa edit ── */
            document.querySelectorAll('[id^="timer-"]').forEach(function(el) {
                let sisa = parseInt(el.dataset.sisa, 10);
                const countdownEl = el.querySelector('.countdown');
                if (!countdownEl || sisa <= 0) return;

                const interval = setInterval(function() {
                    sisa--;
                    if (sisa <= 0) {
                        clearInterval(interval);
                        // Sembunyikan tombol edit dan tampilkan pesan kadaluarsa
                        const laporanId = el.id.replace('timer-', '');
                        const toggleBtn = document.getElementById('toggle-' + laporanId);
                        const editForm = document.getElementById('edit-form-' + laporanId);
                        el.className = 'badge-waktu-edit badge-expired';
                        el.innerHTML = '<i class="fas fa-lock"></i> Waktu edit habis';
                        if (toggleBtn) toggleBtn.style.display = 'none';
                        if (editForm) editForm.style.display = 'none';
                        return;
                    }
                    const m = String(Math.floor(sisa / 60)).padStart(2, '0');
                    const s = String(sisa % 60).padStart(2, '0');
                    countdownEl.textContent = m + ':' + s;
                }, 1000);
            });
        });

        /* ── Toggle buka/tutup form edit ── */
        function toggleEdit(laporanId) {
            const wrap = document.getElementById('edit-form-' + laporanId);
            const toggle = document.getElementById('toggle-' + laporanId);
            if (!wrap) return;
            const isOpen = wrap.classList.contains('open');
            wrap.classList.toggle('open', !isOpen);
            toggle.classList.toggle('active', !isOpen);
            toggle.innerHTML = isOpen ?
                '<i class="fas fa-pen"></i> Ubah Laporan' :
                '<i class="fas fa-times"></i> Batal Ubah';
        }

        // ── SweetAlert untuk error validasi server ──
        document.addEventListener('DOMContentLoaded', function() {
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
