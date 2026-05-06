@extends('layouts.app')

@section('title', 'Buat Laporan Kehadiran')

@push('styles')
    @include('components.event-styles')
    <style>
        .jadwal-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            transition: all .2s;
        }

        .jadwal-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
            border-color: var(--event-primary, #f59e0b);
        }

        .jadwal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
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

        .status-radio-group {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-top: 12px;
        }

        .status-radio {
            position: relative;
            cursor: pointer;
        }

        .status-radio input {
            position: absolute;
            opacity: 0;
        }

        .status-radio .status-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border: 2px solid var(--border, #e2e8f0);
            border-radius: 8px;
            background: #f8fafc;
            transition: all .2s;
            font-size: .8rem;
        }

        .status-radio input:checked + .status-option {
            border-color: var(--event-primary, #f59e0b);
            background: #fef3c7;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-label {
            font-weight: 600;
            flex: 1;
        }

        .status-desc {
            font-size: .7rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .form-group {
            margin-bottom: 16px;
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
        }

        .form-input:focus {
            outline: none;
            border-color: var(--event-primary, #f59e0b);
            box-shadow: 0 0 0 3px rgba(245, 158, 11, .12);
        }

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

        .ab-btn:active {
            transform: scale(.97);
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .ab-btn-back:hover {
            background: #e2e8f0;
        }

        .ab-btn-primary {
            background: var(--event-primary, #f59e0b);
            color: #fff;
            box-shadow: 0 3px 12px rgba(245, 158, 11, .3);
        }

        .ab-btn-primary:hover {
            filter: brightness(1.07);
        }
    </style>
@endpush

@section('content')
<div class="event-wrap" style="padding-bottom: calc(var(--footer-h) + 88px);">

    {{-- Page Strip --}}
    <div class="page-strip page-strip-event">
        <div class="live-badge">
            <span class="live-dot"></span>
            {{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}
        </div>
        <h2>
            <i class="fas fa-plus-circle"></i>
            Buat Laporan Kehadiran
        </h2>
        <p>Laporkan status kehadiran guru per jam pelajaran</p>
    </div>

    @if (session('error'))
        <div class="alert a-err"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    @if ($jadwalBelumLapor->isEmpty())
        <div class="card">
            <div class="c-body" style="text-align:center; padding:40px 20px;">
                <div style="font-size:3rem; color:#e2e8f0; margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="color:var(--text-main); margin-bottom:8px;">Semua Jadwal Sudah Dilaporkan</h3>
                <p style="color:var(--text-muted); margin-bottom:20px;">
                    Tidak ada jadwal yang belum dilaporkan untuk hari ini.
                </p>
                <a href="{{ route('kehadiran-guru.laporan') }}" class="action-btn btn-view">
                    <i class="fas fa-list"></i> Lihat Laporan
                </a>
            </div>
        </div>
    @else
        <form id="laporanForm" method="POST" action="{{ route('kehadiran-guru.store') }}">
            @csrf

            {{-- Info --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-info-circle"></i></div>
                    <h3>Informasi Laporan</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <p style="color:var(--text-muted);font-size:.84rem;line-height:1.55;margin:0;">
                        Pilih jadwal pelajaran yang ingin dilaporkan status kehadirannya.
                        Pastikan memilih status yang sesuai dengan kondisi sebenarnya.
                    </p>
                </div>
            </div>

            {{-- Jadwal List --}}
            @foreach ($jadwalBelumLapor as $jadwal)
                <div class="jadwal-card">
                    <div class="jadwal-header">
                        <div>
                            <h4 class="jadwal-title">
                                Jam {{ $jadwal->jam_ke }} - {{ $jadwal->mata_pelajaran }}
                            </h4>
                            <div class="jadwal-meta">
                                <i class="fas fa-user-tie"></i> {{ $jadwal->gtk->nama_lengkap }} •
                                <i class="fas fa-chalkboard"></i> {{ $jadwal->kelas->nama_kelas }} •
                                <i class="fas fa-clock"></i> {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                            </div>
                        </div>
                    </div>

                    <div class="status-radio-group">
                        <label class="status-radio">
                            <input type="radio" name="jadwal_kbm_id" value="{{ $jadwal->id }}" required>
                            <div class="status-option">
                                <div class="status-dot" style="background:#4CAF50;"></div>
                                <div>
                                    <div class="status-label">Hadir Tepat Waktu</div>
                                    <div class="status-desc">≤ 10 menit setelah bel</div>
                                </div>
                                <input type="hidden" name="status[{{ $jadwal->id }}]" value="hijau">
                            </div>
                        </label>

                        <label class="status-radio">
                            <input type="radio" name="jadwal_kbm_id" value="{{ $jadwal->id }}">
                            <div class="status-option">
                                <div class="status-dot" style="background:#FFC107;"></div>
                                <div>
                                    <div class="status-label">Terlambat</div>
                                    <div class="status-desc">> 10 menit setelah bel</div>
                                </div>
                                <input type="hidden" name="status[{{ $jadwal->id }}]" value="kuning">
                            </div>
                        </label>

                        <label class="status-radio">
                            <input type="radio" name="jadwal_kbm_id" value="{{ $jadwal->id }}">
                            <div class="status-option">
                                <div class="status-dot" style="background:#F44336;"></div>
                                <div>
                                    <div class="status-label">Tidak Hadir - No Tugas</div>
                                    <div class="status-desc">Tidak hadir tanpa tugas</div>
                                </div>
                                <input type="hidden" name="status[{{ $jadwal->id }}]" value="merah">
                            </div>
                        </label>

                        <label class="status-radio">
                            <input type="radio" name="jadwal_kbm_id" value="{{ $jadwal->id }}">
                            <div class="status-option">
                                <div class="status-dot" style="background:#9E9E9E;"></div>
                                <div>
                                    <div class="status-label">Tidak Hadir - Ada Tugas</div>
                                    <div class="status-desc">Tidak hadir tapi ada tugas</div>
                                </div>
                                <input type="hidden" name="status[{{ $jadwal->id }}]" value="abu">
                            </div>
                        </label>

                        <label class="status-radio">
                            <input type="radio" name="jadwal_kbm_id" value="{{ $jadwal->id }}">
                            <div class="status-option">
                                <div class="status-dot" style="background:#2196F3;"></div>
                                <div>
                                    <div class="status-label">Hadir Lalu Pergi - Ada Tugas</div>
                                    <div class="status-desc">Datang lalu pergi dengan tugas</div>
                                </div>
                                <input type="hidden" name="status[{{ $jadwal->id }}]" value="biru">
                            </div>
                        </label>

                        <label class="status-radio">
                            <input type="radio" name="jadwal_kbm_id" value="{{ $jadwal->id }}">
                            <div class="status-option">
                                <div class="status-dot" style="background:#E91E63;"></div>
                                <div>
                                    <div class="status-label">Hadir Lalu Pergi - No Tugas</div>
                                    <div class="status-desc">Datang lalu pergi tanpa tugas</div>
                                </div>
                                <input type="hidden" name="status[{{ $jadwal->id }}]" value="pink">
                            </div>
                        </label>
                    </div>

                    <div class="form-group" style="margin-top:12px;">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="catatan[{{ $jadwal->id }}]" class="form-input" rows="2"
                            placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>
            @endforeach

        </form>
    @endif

</div>

{{-- Action Bar --}}
@if (!$jadwalBelumLapor->isEmpty())
<div class="action-bar">
    <a href="{{ route('kehadiran-guru.laporan') }}" class="ab-btn ab-btn-back">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
    <button type="submit" form="laporanForm" class="ab-btn ab-btn-primary">
        <i class="fas fa-paper-plane"></i> Kirim Laporan
    </button>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-select status when jadwal is selected
    const radioButtons = document.querySelectorAll('input[type="radio"][name="jadwal_kbm_id"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            // Remove selected class from all options
            document.querySelectorAll('.status-option').forEach(option => {
                option.classList.remove('selected');
            });

            // Add selected class to the chosen option's parent
            if (this.checked) {
                this.nextElementSibling.classList.add('selected');
            }
        });
    });
});
</script>
@endpush