@extends('layouts.app')

@section('title', 'Detail Mata Pelajaran')

@push('styles')
    @include('components.event-styles')
    <style>
        .detail-wrap {
            padding-bottom: calc(var(--footer-h, 64px) + 80px);
        }

        /* ── Lebar konten menyesuaikan layar ── */
        @media (min-width: 768px) {
            .detail-wrap {
                max-width: 900px;
                margin-inline: auto;
                padding-left: 16px;
                padding-right: 16px;
            }
        }

        /* Info card */
        .info-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .07);
            margin-bottom: 14px;
            overflow: hidden;
        }

        .info-card-head {
            padding: 14px 16px 12px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card-head .ic-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .info-card-head h3 {
            font-size: .92rem;
            font-weight: 700;
            color: var(--text-main, #1e293b);
            margin: 0;
        }

        /* Detail rows — stack di mobile, 2 kolom di tablet/desktop */
        .detail-body {
            padding: 14px 16px;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        @media (min-width: 640px) {
            .detail-body {
                display: grid;
                grid-template-columns: 1fr 1fr;
                column-gap: 28px;
            }

            .detail-row.full {
                grid-column: 1 / -1;
            }
        }

        .detail-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 11px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row .dr-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            margin-top: 1px;
        }

        .detail-row .dr-content {
            flex: 1;
            min-width: 0;
        }

        .detail-row .dr-label {
            font-size: .72rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 3px;
        }

        .detail-row .dr-value {
            font-size: .88rem;
            font-weight: 600;
            color: var(--text-main, #1e293b);
            word-break: break-word;
        }

        .detail-row .dr-value.muted {
            font-weight: 400;
            color: #64748b;
            font-style: italic;
        }

        /* Kode pill */
        .kode-pill {
            font-family: 'Courier New', monospace;
            background: #f1f5f9;
            color: #475569;
            border-radius: 6px;
            padding: 3px 9px;
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .05em;
            display: inline-block;
        }

        /* Tags */
        .tag {
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: .75rem;
            display: inline-block;
        }

        .tag-umum {
            background: #ede9fe;
            color: #6d28d9;
        }

        .tag-jurusan {
            background: #dcfce7;
            color: #15803d;
        }

        .tag-mulok {
            background: #fef3c7;
            color: #b45309;
        }

        .tag-aktif {
            background: #dcfce7;
            color: #15803d;
        }

        .tag-nonaktif {
            background: #f1f5f9;
            color: #64748b;
        }

        /* ── Jadwal table (like siswa/6 show) ── */
        .absen-table-wrap {
            overflow-x: auto;
        }

        .absen-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
        }

        .absen-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border, #e2e8f0);
        }

        .absen-table th {
            padding: 8px 10px;
            text-align: left;
            font-size: .68rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .absen-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .absen-table tbody tr:last-child td {
            border-bottom: none;
        }

        .absen-table tbody tr:hover td {
            background: #fafbfc;
        }

        /* Di layar sempit, tabel berubah jadi kartu agar tidak perlu scroll horizontal */
        @media (max-width: 575.98px) {
            .absen-table thead {
                display: none;
            }

            .absen-table,
            .absen-table tbody,
            .absen-table tr,
            .absen-table td {
                display: block;
                width: 100%;
            }

            .absen-table tbody tr {
                background: #fff;
                border: 1px solid #f1f5f9;
                border-radius: 10px;
                padding: 4px 12px;
                margin-bottom: 10px;
            }

            .absen-table tbody tr:last-child {
                margin-bottom: 0;
            }

            .absen-table td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 7px 0;
                border-bottom: 1px dashed #f1f5f9;
                text-align: right;
            }

            .absen-table td:last-child {
                border-bottom: none;
            }

            .absen-table td::before {
                content: attr(data-label);
                font-size: .68rem;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: .04em;
                flex-shrink: 0;
                text-align: left;
            }

            .absen-table td.empty-cell {
                display: block;
                text-align: center;
            }

            .absen-table td.empty-cell::before {
                content: none;
            }
        }

        .empty-table {
            text-align: center;
            padding: 24px 16px;
            color: #94a3b8;
            font-size: .82rem;
        }

        .empty-table i {
            display: block;
            font-size: 1.8rem;
            margin-bottom: 6px;
            opacity: .4;
        }

        /* Pagination — tampilannya sendiri diatur di pagination::azures */
        .pagination-wrap {
            margin-top: 10px;
        }

        /* Action buttons */
        .action-group {
            display: flex;
            gap: 8px;
            padding: 14px 16px 4px;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border-radius: 9px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            white-space: nowrap;
            flex: 1;
            justify-content: center;
        }

        .action-btn:active {
            transform: scale(.96);
        }

        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-back:hover {
            background: #e2e8f0;
        }

        .btn-edit {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .btn-edit:hover {
            background: #fef3c7;
        }

        @media (min-width: 640px) {
            .action-group {
                justify-content: flex-end;
            }

            .action-btn {
                flex: 0 0 auto;
            }
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap detail-wrap"
        style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Page Strip --}}
        @php
            $kategoriClass = match ($mataPelajaran->kategori) {
                'jurusan' => ['bg' => '#dcfce7', 'color' => '#15803d', 'icon' => 'fa-cogs'],
                'mulok' => ['bg' => '#fef3c7', 'color' => '#b45309', 'icon' => 'fa-map-marker-alt'],
                default => ['bg' => '#ede9fe', 'color' => '#6d28d9', 'icon' => 'fa-globe'],
            };
        @endphp

        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2><i class="fas fa-book-open"></i> Detail Mata Pelajaran</h2>
            <p>{{ $mataPelajaran->nama_mapel }}</p>
        </div>

        {{-- Identitas Card --}}
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-icon" style="background:{{ $kategoriClass['bg'] }};">
                    <i class="fas {{ $kategoriClass['icon'] }}" style="color:{{ $kategoriClass['color'] }};"></i>
                </div>
                <h3>Identitas Mata Pelajaran</h3>
            </div>
            <div class="detail-body">

                <div class="detail-row">
                    <div class="dr-icon" style="background:#f1f5f9;">
                        <i class="fas fa-fingerprint" style="color:#475569;"></i>
                    </div>
                    <div class="dr-content">
                        <div class="dr-label">Kode</div>
                        <div class="dr-value"><span class="kode-pill">{{ $mataPelajaran->kode_mapel }}</span></div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="dr-icon" style="background:#eff6ff;">
                        <i class="fas fa-book" style="color:#2563eb;"></i>
                    </div>
                    <div class="dr-content">
                        <div class="dr-label">Nama Mata Pelajaran</div>
                        <div class="dr-value">{{ $mataPelajaran->nama_mapel }}</div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="dr-icon" style="background:{{ $kategoriClass['bg'] }};">
                        <i class="fas fa-tag" style="color:{{ $kategoriClass['color'] }};"></i>
                    </div>
                    <div class="dr-content">
                        <div class="dr-label">Kategori</div>
                        <div class="dr-value">
                            <span class="tag tag-{{ $mataPelajaran->kategori }}">
                                {{ $mataPelajaran->kategori_label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="detail-row">
                    <div class="dr-icon" style="background:#f0fdf4;">
                        <i class="fas fa-toggle-on" style="color:#15803d;"></i>
                    </div>
                    <div class="dr-content">
                        <div class="dr-label">Status</div>
                        <div class="dr-value">
                            <span class="tag {{ $mataPelajaran->status_aktif ? 'tag-aktif' : 'tag-nonaktif' }}">
                                <i class="fas fa-{{ $mataPelajaran->status_aktif ? 'check' : 'ban' }}"></i>
                                {{ $mataPelajaran->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="detail-row full">
                    <div class="dr-icon" style="background:#fff7ed;">
                        <i class="fas fa-align-left" style="color:#b45309;"></i>
                    </div>
                    <div class="dr-content">
                        <div class="dr-label">Deskripsi</div>
                        <div class="dr-value {{ $mataPelajaran->deskripsi ? '' : 'muted' }}">
                            {{ $mataPelajaran->deskripsi ?: 'Tidak ada deskripsi' }}
                        </div>
                    </div>
                </div>

            </div>

            {{-- Action Buttons --}}
            <div class="action-group">
                <a href="{{ route('admin.mata-pelajaran.index') }}" class="action-btn btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="{{ route('admin.mata-pelajaran.edit', $mataPelajaran) }}" class="action-btn btn-edit">
                    <i class="fas fa-pen"></i> Edit Mata Pelajaran
                </a>
            </div>
        </div>

        {{-- Jadwal KBM Card --}}
        <div class="info-card">
            <div class="info-card-head">
                <div class="ic-icon" style="background:#eff6ff;">
                    <i class="fas fa-calendar-week" style="color:#2563eb;"></i>
                </div>
                <h3>Jadwal KBM
                    <span
                        style="margin-left:6px; background:#eff6ff; color:#2563eb; font-size:.7rem; padding:2px 8px; border-radius:20px; font-weight:700;">
                        {{ $jadwalKBM->count() }}
                    </span>
                </h3>
            </div>

            <div style="padding: 4px 16px 8px;">
                <div class="absen-table-wrap">
                    <table class="absen-table">
                        <thead>
                            <tr>
                                <th>Hari &amp; Jam</th>
                                <th>Kelas</th>
                                <th>Guru</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jadwalKBM as $jadwal)
                                <tr>
                                    <td data-label="Hari & Jam" style="white-space:nowrap;">
                                        {{ $jadwal->hari }} &mdash; Jam ke-{{ $jadwal->jam_ke }}
                                    </td>
                                    <td data-label="Kelas">{{ $jadwal->kelas->nama_kelas }}</td>
                                    <td data-label="Guru">{{ $jadwal->gtk->nama_lengkap }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="empty-cell">
                                        <div class="empty-table">
                                            <i class="fas fa-calendar-times"></i>
                                            Belum ada jadwal KBM
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrap">
                    {{ $jadwalKBM->links('pagination::azures') }}
                </div>
            </div>
        </div>

    </div>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }

        });
    </script>
@endpush
