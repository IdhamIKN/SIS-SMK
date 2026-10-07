@extends('layouts.app')

@section('title', 'Jadwal Mengajar Guru')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Strip ── */
        .jmg-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0ea5e9 100%);
            position: relative;
            overflow: hidden;
            margin-bottom: 0;
        }

        .jmg-strip::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .jmg-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .7rem;
            font-weight: 600;
            color: rgba(255, 255, 255, .9);
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .jmg-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7dd3fc;
            display: inline-block;
        }

        .jmg-strip h2 {
            font-size: 1.3rem;
            font-weight: 800;
            color: #fff;
            margin: 0 0 4px;
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .jmg-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .65);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* ── Filter card ── */
        .jmg-filter {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
            margin: 14px 0 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }

        .jmg-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;
            min-width: 200px;
        }

        .jmg-field label {
            font-size: .67rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .jmg-sel {
            width: 100%;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            padding: 9px 12px;
            font-size: .84rem;
            background: #f8fafc;
            font-family: inherit;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
        }

        .jmg-sel:focus {
            border-color: #0ea5e9;
            background: #fff;
        }

        .jmg-btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 14px;
            border-radius: 9px;
            font-size: .8rem;
            font-weight: 700;
            background: #f1f5f9;
            color: #475569;
            border: 1.5px solid #e2e8f0;
            text-decoration: none;
            white-space: nowrap;
        }

        /* ── Guru info card ── */
        .jmg-guru-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .jmg-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #e0f2fe;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .jmg-guru-card h4 {
            font-size: .98rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 3px;
        }

        .jmg-guru-card p {
            font-size: .76rem;
            color: #64748b;
            margin: 0;
            line-height: 1.55;
        }

        /* ── Stats chips ── */
        .jmg-chips {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
        }

        .jmg-chip {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .jmg-chip-ico {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .92rem;
            flex-shrink: 0;
        }

        .jmg-chip-val {
            font-size: 1.3rem;
            font-weight: 800;
            line-height: 1;
        }

        .jmg-chip-lbl {
            font-size: .62rem;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-top: 2px;
        }

        /* ── Grid table ── */
        .jmg-table-wrap {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow-x: auto;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            margin-bottom: 12px;
        }

        .jmg-table {
            border-collapse: collapse;
            width: 100%;
        }

        .jmg-table th {
            background: linear-gradient(135deg, #0369a1, #0ea5e9);
            color: #fff;
            padding: 10px 8px;
            text-align: center;
            font-weight: 700;
            font-size: .74rem;
            white-space: nowrap;
        }

        .jmg-table th:first-child {
            border-radius: 12px 0 0 0;
        }

        .jmg-table th:last-child {
            border-radius: 0 12px 0 0;
        }

        .jmg-table td {
            border: 1px solid #f1f5f9;
            vertical-align: middle;
            text-align: center;
        }

        /* ── Day column ── */
        .jmg-day-col {
            position: sticky;
            left: 0;
            z-index: 2;
            font-weight: 800;
            font-size: .74rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            padding: 10px 10px;
            white-space: nowrap;
            min-width: 64px;
        }

        .jmg-day-sen {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .jmg-day-sel {
            background: #ede9fe;
            color: #7c3aed;
        }

        .jmg-day-rab {
            background: #dcfce7;
            color: #15803d;
        }

        .jmg-day-kam {
            background: #fef3c7;
            color: #b45309;
        }

        .jmg-day-jum {
            background: #fee2e2;
            color: #b91c1c;
        }

        .jmg-day-sab {
            background: #f1f5f9;
            color: #475569;
        }

        /* ── Cell ── */
        .jmg-empty-cell {
            color: #cbd5e1;
            font-size: .78rem;
            padding: 16px 8px;
        }

        .jmg-cell {
            position: relative;
            padding: 10px 8px;
            min-width: 76px;
            overflow: hidden;
        }

        .jmg-cell::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-style: solid;
            border-width: 0 18px 18px 0;
            border-color: transparent var(--accent) transparent transparent;
            opacity: .8;
        }

        .jmg-cell-mapel {
            font-weight: 700;
            font-size: .74rem;
            color: #0f172a;
            line-height: 1.25;
            position: relative;
            z-index: 1;
        }

        .jmg-cell-meta {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            margin-top: 3px;
            position: relative;
            z-index: 1;
        }

        .jmg-cell-meta span {
            font-size: .64rem;
            color: #64748b;
            font-weight: 600;
        }

        .jmg-cell-time {
            color: #94a3b8 !important;
            font-weight: 500 !important;
        }

        /* ── Empty state ── */
        .jmg-empty {
            text-align: center;
            padding: 50px 20px;
            color: #94a3b8;
        }

        .jmg-empty i {
            font-size: 3rem;
            display: block;
            margin-bottom: 12px;
            opacity: .3;
        }

        .jmg-empty h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .jmg-empty p {
            font-size: .84rem;
            color: #64748b;
            margin: 0;
        }

        /* ── Hint ── */
        .jmg-hint {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .72rem;
            color: #94a3b8;
            margin-bottom: 16px;
        }

        @media (max-width:480px) {
            .jmg-cell {
                min-width: 58px;
                padding: 8px 4px;
            }

            .jmg-cell-mapel {
                font-size: .66rem;
            }

            .jmg-cell-meta span {
                font-size: .58rem;
            }

            .jmg-day-col {
                padding: 8px 6px;
                font-size: .66rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Strip --}}
        <div class="jmg-strip">
            <div class="jmg-live"><span class="jmg-dot"></span>Jadwal Per Guru</div>
            <h2><i class="fas fa-chalkboard-teacher"></i> Jadwal Mengajar Guru</h2>
            <p>Tampilan grid mingguan jadwal mengajar per guru</p>
        </div>

        {{-- Guru Picker + Tombol Kembali --}}
        <form method="GET" id="guruForm">
            <div class="jmg-filter">
                <div class="jmg-field">
                    <label><i class="fas fa-user-tie" style="margin-right:3px;"></i>Pilih Guru</label>
                    <select name="gtk_id" id="gtk_id" class="jmg-sel" onchange="this.form.submit()">
                        <option value="">— Pilih Guru —</option>
                        @foreach ($gtkList as $g)
                            <option value="{{ $g->id }}" {{ request('gtk_id') == $g->id ? 'selected' : '' }}>
                                {{ $g->nama_lengkap }}{{ $g->kd_guru ? ' (' . $g->kd_guru . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('admin.jadwal-kbm.index') }}" class="jmg-btn-back">
                    <i class="fas fa-table-list"></i> Kembali ke Daftar
                </a>
            </div>
        </form>

        @if ($gtk)
            @php
                $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                $dayClass = [
                    'Senin' => 'jmg-day-sen',
                    'Selasa' => 'jmg-day-sel',
                    'Rabu' => 'jmg-day-rab',
                    'Kamis' => 'jmg-day-kam',
                    'Jumat' => 'jmg-day-jum',
                    'Sabtu' => 'jmg-day-sab',
                ];
                $palette = ['#0ea5e9', '#10b981', '#a855f7', '#f59e0b', '#ef4444', '#14b8a6', '#6366f1', '#ec4899'];

                $semuaSlot = collect($jadwalGuru)->flatten(1);
                $maxJam = max((int) ($semuaSlot->max('jam_ke') ?: 1), 1);
                $totalJam = $semuaSlot->count();
                $jumlahKelas = $semuaSlot->map(fn($s) => $s->kelas_id ?? $s->kelas?->id)->filter()->unique()->count();

                // Build colspan rows
                $rows = [];
                foreach ($hariList as $hari) {
                    $slots = ($jadwalGuru[$hari] ?? collect())->keyBy('jam_ke');
                    $cells = [];
                    $jam = 1;
                    while ($jam <= $maxJam) {
                        $slot = $slots->get($jam);
                        if (!$slot) {
                            $cells[] = ['type' => 'empty'];
                            $jam++;
                            continue;
                        }
                        $kelasId = $slot->kelas_id ?? $slot->kelas?->id;
                        $sig = ($slot->mata_pelajaran_id ?? $slot->mata_pelajaran) . '|' . $kelasId;
                        $colspan = 1;
                        $next = $jam + 1;
                        while ($next <= $maxJam) {
                            $ns = $slots->get($next);
                            if (!$ns) {
                                break;
                            }
                            $nk = $ns->kelas_id ?? $ns->kelas?->id;
                            $ns2 = ($ns->mata_pelajaran_id ?? $ns->mata_pelajaran) . '|' . $nk;
                            if ($ns2 !== $sig) {
                                break;
                            }
                            $colspan++;
                            $next++;
                        }
                        $colorSeed = $slot->mata_pelajaran_id ?? ($slot->mata_pelajaran ?? 'x');
                        $color = $palette[crc32((string) $colorSeed) % count($palette)];
                        $cells[] = ['type' => 'filled', 'colspan' => $colspan, 'slot' => $slot, 'color' => $color];
                        $jam = $next;
                    }
                    $rows[$hari] = $cells;
                }
            @endphp

            {{-- Guru Info --}}
            <div class="jmg-guru-card">
                <div class="jmg-avatar">👨‍🏫</div>
                <div>
                    <h4>{{ $gtk->nama_lengkap }}</h4>
                    <p><strong>Kode Guru:</strong> {{ $gtk->kd_guru ?: '—' }}</p>
                    <p><strong>Kompetensi:</strong> {{ $gtk->mata_pelajaran ?: 'Belum ditentukan' }}</p>
                </div>
            </div>

            {{-- Stat Chips --}}
            <div class="jmg-chips">
                <div class="jmg-chip">
                    <div class="jmg-chip-ico" style="background:#dbeafe;color:#1d4ed8;"><i
                            class="fas fa-calendar-check"></i></div>
                    <div>
                        <div class="jmg-chip-val" style="color:#1d4ed8;">{{ $totalJam }}</div>
                        <div class="jmg-chip-lbl">Jam Mengajar</div>
                    </div>
                </div>
                <div class="jmg-chip">
                    <div class="jmg-chip-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-door-open"></i>
                    </div>
                    <div>
                        <div class="jmg-chip-val" style="color:#15803d;">{{ $jumlahKelas }}</div>
                        <div class="jmg-chip-lbl">Kelas Diajar</div>
                    </div>
                </div>
            </div>

            {{-- Grid Jadwal --}}
            <div class="jmg-table-wrap">
                <table class="jmg-table" style="min-width: {{ 80 + $maxJam * 70 }}px;">
                    <thead>
                        <tr>
                            <th>Hari / Jam</th>
                            @for ($i = 1; $i <= $maxJam; $i++)
                                <th>{{ $i }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hariList as $hari)
                            <tr>
                                <td class="jmg-day-col {{ $dayClass[$hari] ?? 'jmg-day-sab' }}">
                                    {{ Str::limit($hari, 3, '') }}
                                </td>
                                @foreach ($rows[$hari] as $cell)
                                    @if ($cell['type'] === 'empty')
                                        <td class="jmg-empty-cell">–</td>
                                    @else
                                        @php $slot = $cell['slot']; @endphp
                                        <td colspan="{{ $cell['colspan'] }}" class="jmg-cell"
                                            style="--accent:{{ $cell['color'] }};">
                                            <div class="jmg-cell-mapel">
                                                {{ $slot->mataPelajaran?->nama_mapel ?? $slot->mata_pelajaran }}
                                            </div>
                                            <div class="jmg-cell-meta">
                                                @if ($slot->kelas?->nama_kelas)
                                                    <span>{{ $slot->kelas->nama_kelas }}</span>
                                                @endif
                                                @if ($slot->jam_mulai && $slot->jam_selesai)
                                                    <span class="jmg-cell-time">
                                                        {{ $slot->jam_mulai->format('H:i') }}–{{ $slot->jam_selesai->format('H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="jmg-hint">
                <i class="fas fa-arrows-left-right"></i> Geser ke samping untuk melihat seluruh jam pelajaran
            </p>
        @else
            {{-- Empty State --}}
            <div class="jmg-empty">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3>Pilih guru terlebih dahulu</h3>
                <p>Jadwal mengajar akan ditampilkan dalam bentuk grid mingguan setelah memilih guru.</p>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success')),
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endpush
