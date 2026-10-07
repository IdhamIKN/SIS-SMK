@extends('layouts.app')

@section('title', 'Detail Jam Pelajaran')

@push('styles')
    @include('components.event-styles')
    <style>
        .detail-wrap {
            padding-bottom: calc(var(--footer-h, 60px) + 88px);
        }

        /* ── Hero Card (jam summary) ── */
        .jam-hero {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            border: 1px solid #f1f5f9;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .jam-hero-body {
            padding: 18px 16px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .jam-hero-icon {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .jam-hero-info {
            flex: 1;
            min-width: 0;
        }

        .jam-hero-name {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px;
        }

        .jam-hero-sub {
            font-size: .75rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .jam-hero-footer {
            padding: 10px 16px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* ── Shift badge variants ── */
        .shift-pagi {
            background: #fef9c3;
            color: #a16207;
        }

        .shift-siang {
            background: #ffedd5;
            color: #c2410c;
        }

        .shift-sore {
            background: #ede9fe;
            color: #6d28d9;
        }

        .shift-malam {
            background: #1e293b;
            color: #94a3b8;
        }

        /* ── Section Card ── */
        .detail-section {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
            border: 1px solid #f1f5f9;
            margin-bottom: 14px;
            overflow: hidden;
        }

        .detail-section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            border-bottom: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        .detail-section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .detail-section-title {
            font-size: .82rem;
            font-weight: 700;
            color: #334155;
            letter-spacing: .2px;
        }

        /* ── Detail Row ── */
        .detail-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 16px;
            border-bottom: 1px solid #f8fafc;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            flex-shrink: 0;
        }

        .detail-row-content {
            flex: 1;
        }

        .detail-row-label {
            font-size: .65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #94a3b8;
            margin-bottom: 2px;
        }

        .detail-row-value {
            font-size: .88rem;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.4;
        }

        .detail-row-value.mono {
            font-family: 'Courier New', monospace;
            font-size: .92rem;
        }

        .detail-row-value.muted {
            color: #94a3b8;
            font-weight: 400;
            font-style: italic;
        }

        /* ── Time comparison block ── */
        .time-compare {
            display: flex;
            gap: 0;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
            margin: 0 16px 14px;
        }

        .time-compare-col {
            flex: 1;
            padding: 12px 14px;
            text-align: center;
            position: relative;
        }

        .time-compare-col+.time-compare-col {
            border-left: 1px solid #f1f5f9;
        }

        .time-compare-label {
            font-size: .63rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .time-compare-value {
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
        }

        .time-compare-limit {
            font-size: .65rem;
            color: #94a3b8;
            margin-top: 3px;
        }

        .time-arrow-center {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            color: #cbd5e1;
            font-size: .7rem;
            border-left: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }

        /* ── Status pill ── */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 700;
        }

        .status-pill-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .pill-active {
            background: #dcfce7;
            color: #15803d;
        }

        .pill-active .status-pill-dot {
            background: #22c55e;
        }

        .pill-inactive {
            background: #f1f5f9;
            color: #64748b;
        }

        .pill-inactive .status-pill-dot {
            background: #94a3b8;
        }

        /* ── Meta chip ── */
        .meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: .72rem;
            font-weight: 600;
            background: #f1f5f9;
            color: #64748b;
        }

        /* ── Fixed Action Bar ── */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 60px);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: .84rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
        }

        .ab-btn:active {
            transform: scale(.97);
        }

        .ab-back {
            background: #f1f5f9;
            color: #475569;
            border: 1.5px solid #e2e8f0;
            flex: 0 0 auto;
            padding: 12px 18px;
        }

        .ab-back:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .ab-edit {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            box-shadow: 0 3px 12px rgba(37, 99, 235, .3);
        }

        .ab-edit:hover {
            filter: brightness(1.08);
        }

        .ab-delete {
            background: linear-gradient(135deg, #e11d48, #be123c);
            color: #fff;
            box-shadow: 0 3px 12px rgba(225, 29, 72, .3);
            flex: 0 0 auto;
            padding: 12px 16px;
        }

        .ab-delete:hover {
            filter: brightness(1.08);
        }
    </style>
@endpush

@section('content')

    @php
        $shif = strtolower($setJam->shif ?? 'pagi');
        $shiftClass = "shift-{$shif}";
        $icons = ['pagi' => 'fa-sun', 'siang' => 'fa-cloud-sun', 'sore' => 'fa-cloud-moon', 'malam' => 'fa-moon'];
        $icon = $icons[$shif] ?? 'fa-clock';
        $iconBg = ['pagi' => '#fef9c3', 'siang' => '#ffedd5', 'sore' => '#ede9fe', 'malam' => '#1e293b'];
        $iconColor = ['pagi' => '#a16207', 'siang' => '#c2410c', 'sore' => '#6d28d9', 'malam' => '#94a3b8'];
    @endphp

    <div class="event-wrap detail-wrap"
        style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- ── Page Strip ── --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2>
                <i class="fas fa-clock"></i>
                Detail Jam Pelajaran
            </h2>
            <p>Informasi lengkap jam &bull; <strong style="color:#fff;">{{ $setJam->nama_jam }}</strong></p>
        </div>

        {{-- ── Hero Summary ── --}}
        <div class="jam-hero">
            <div class="jam-hero-body">
                <div class="jam-hero-icon"
                    style="background:{{ $iconBg[$shif] ?? '#f1f5f9' }};color:{{ $iconColor[$shif] ?? '#64748b' }};">
                    <i class="fas {{ $icon }}"></i>
                </div>
                <div class="jam-hero-info">
                    <h3 class="jam-hero-name">{{ $setJam->nama_jam }}</h3>
                    <div class="jam-hero-sub">
                        <span><i class="fas fa-hashtag"></i> #{{ $setJam->id_jam }}</span>
                        <span>·</span>
                        <span class="hbadge {{ $shiftClass }}">{{ $setJam->shif }}</span>
                        <span>·</span>
                        <span class="status-pill {{ $setJam->statusjam ? 'pill-active' : 'pill-inactive' }}">
                            <span class="status-pill-dot"></span>
                            {{ $setJam->statusjam ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Time quick-view --}}
            <div class="time-compare">
                <div class="time-compare-col">
                    <div class="time-compare-label"><i class="fas fa-sign-in-alt"></i> Masuk</div>
                    <div class="time-compare-value">{{ $setJam->time_in?->format('H:i') ?? '--:--' }}</div>
                    {{-- @if ($setJam->limit_in)
                    <div class="time-compare-limit">batas {{ $setJam->limit_in->format('H:i') }}</div>
                @endif --}}
                </div>
                <div class="time-arrow-center"><i class="fas fa-arrow-right"></i></div>
                <div class="time-compare-col">
                    <div class="time-compare-label"><i class="fas fa-sign-out-alt"></i> Pulang</div>
                    <div class="time-compare-value">{{ $setJam->time_out?->format('H:i') ?? '--:--' }}</div>
                    {{-- @if ($setJam->limit_out)
                    <div class="time-compare-limit">batas {{ $setJam->limit_out->format('H:i') }}</div>
                @endif --}}
                </div>
            </div>
        </div>

        {{-- ── Informasi Utama ── --}}
        <div class="detail-section">
            <div class="detail-section-header">
                <div class="detail-section-icon" style="background:#eff6ff;color:#1d4ed8;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <span class="detail-section-title">Informasi Utama</span>
            </div>

            <div class="detail-row">
                <div class="detail-row-icon" style="background:#eff6ff;color:#1d4ed8;">
                    <i class="fas fa-hashtag"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">ID Jam</div>
                    <div class="detail-row-value mono">#{{ $setJam->id_jam }}</div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-row-icon" style="background:#f0fdf4;color:#15803d;">
                    <i class="fas fa-tag"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">Nama Jam</div>
                    <div class="detail-row-value">{{ $setJam->nama_jam }}</div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-row-icon"
                    style="background:{{ $iconBg[$shif] ?? '#f1f5f9' }};color:{{ $iconColor[$shif] ?? '#64748b' }};">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">Shift</div>
                    <div class="detail-row-value">
                        <span class="hbadge {{ $shiftClass }}">
                            <i class="fas {{ $icon }}"></i> {{ $setJam->shif }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Kelompok Jam --}}
            <div class="detail-row">
                <div class="detail-row-icon" style="background:#ede9fe;color:#7c3aed;">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">Kelompok Jadwal</div>
                    <div class="detail-row-value">
                        @php
                            $klpBadge = match($setJam->kelompok_jam ?? 'reguler') {
                                'reguler_1112' => ['Reguler – Kelas 11 & 12 (Senin–Kamis)', 'background:#dbeafe;color:#1d4ed8;'],
                                'jumat'        => ['Jumat – Semua Kelas', 'background:#fce7f3;color:#9d174d;'],
                                default        => ['Reguler – Kelas 10 (Senin–Kamis)', 'background:#dcfce7;color:#15803d;'],
                            };
                        @endphp
                        <span class="hbadge" style="{{ $klpBadge[1] }}">{{ $klpBadge[0] }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-row-icon" style="background:#f0fdf4;color:#15803d;">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">Status</div>
                    <div class="detail-row-value">
                        <span class="status-pill {{ $setJam->statusjam ? 'pill-active' : 'pill-inactive' }}">
                            <span class="status-pill-dot"></span>
                            {{ $setJam->statusjam ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Waktu Masuk ── --}}
        <div class="detail-section">
            <div class="detail-section-header">
                <div class="detail-section-icon" style="background:#f0fdf4;color:#15803d;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <span class="detail-section-title">Waktu Mulai</span>
            </div>

            <div class="detail-row">
                <div class="detail-row-icon" style="background:#f0fdf4;color:#15803d;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">Jam Mulai</div>
                    <div class="detail-row-value mono">
                        {{ $setJam->time_in?->format('H:i') ?? '-' }}
                    </div>
                </div>
            </div>

            {{-- <div class="detail-row">
            <div class="detail-row-icon" style="background:#fef9c3;color:#a16207;">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="detail-row-content">
                <div class="detail-row-label">Batas Keterlambatan</div>
                <div class="detail-row-value {{ $setJam->limit_in ? 'mono' : 'muted' }}">
                    {{ $setJam->limit_in?->format('H:i') ?? 'Tidak diatur' }}
                </div>
            </div>
        </div> --}}
        </div>

        {{-- ── Waktu Pulang ── --}}
        <div class="detail-section">
            <div class="detail-section-header">
                <div class="detail-section-icon" style="background:#eff6ff;color:#1d4ed8;">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <span class="detail-section-title">Waktu Selesai</span>
            </div>

            <div class="detail-row">
                <div class="detail-row-icon" style="background:#eff6ff;color:#1d4ed8;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">Jam Selesai</div>
                    <div class="detail-row-value mono">
                        {{ $setJam->time_out?->format('H:i') ?? '-' }}
                    </div>
                </div>
            </div>

            {{-- <div class="detail-row">
            <div class="detail-row-icon" style="background:#fef9c3;color:#a16207;">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="detail-row-content">
                <div class="detail-row-label">Batas Pulang Awal</div>
                <div class="detail-row-value {{ $setJam->limit_out ? 'mono' : 'muted' }}">
                    {{ $setJam->limit_out?->format('H:i') ?? 'Tidak diatur' }}
                </div>
            </div>
        </div> --}}
        </div>

        {{-- ── Metadata ── --}}
        <div class="detail-section">
            <div class="detail-section-header">
                <div class="detail-section-icon" style="background:#f1f5f9;color:#64748b;">
                    <i class="fas fa-database"></i>
                </div>
                <span class="detail-section-title">Metadata</span>
            </div>

            <div class="detail-row">
                <div class="detail-row-icon" style="background:#eff6ff;color:#1d4ed8;">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">Dibuat</div>
                    <div class="detail-row-value {{ $setJam->created_at ? '' : 'muted' }}">
                        {{ $setJam->created_at?->translatedFormat('d F Y, H:i') ?? '-' }}
                    </div>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-row-icon" style="background:#fffbeb;color:#b45309;">
                    <i class="fas fa-pen"></i>
                </div>
                <div class="detail-row-content">
                    <div class="detail-row-label">Terakhir Diubah</div>
                    <div class="detail-row-value {{ $setJam->updated_at ? '' : 'muted' }}">
                        {{ $setJam->updated_at?->translatedFormat('d F Y, H:i') ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Fixed Action Bar ── --}}
    <div class="action-bar">
        <a href="{{ route('admin.set-jam.index') }}" class="ab-btn ab-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <a href="{{ route('admin.set-jam.edit', $setJam) }}" class="ab-btn ab-edit">
            <i class="fas fa-pen"></i> Edit Jam
        </a>
        <button type="button" class="ab-btn ab-delete" id="btnDelete">
            <i class="fas fa-trash"></i>
        </button>

        {{-- Hidden delete form --}}
        <form id="deleteForm" method="POST" action="{{ route('admin.set-jam.destroy', $setJam) }}"
            style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }

            /* ── Delete Confirmation ── */
            document.getElementById('btnDelete').addEventListener('click', function() {
                Swal.fire({
                    title: 'Hapus Jam Pelajaran?',
                    html: `Yakin ingin menghapus <strong>{{ addslashes($setJam->nama_jam) }}</strong>?<br>
                   <small style="color:#94a3b8;">Data yang terhubung dengan jadwal KBM tidak dapat dihapus.</small>`,
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    showCancelButton: true,
                    confirmButtonColor: '#be123c',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('deleteForm').submit();
                    }
                });
            });

            /* ── Session Alerts ── */
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
            @endif
        });
    </script>
@endpush
