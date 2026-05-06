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
            grid-template-columns: repeat(1, 1fr);
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

        .warning-card {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
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
            Laporan Kehadiran Guru
        </div>
        <h2>
            <i class="fas fa-user-graduate"></i>
            Laporkan Guru Tidak Hadir
        </h2>
        <p>Bantu laporkan jika guru tidak hadir di kelas</p>
    </div>

    @if (session('error'))
        <div class="alert a-err"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    {{-- Warning --}}
    <div class="warning-card">
        <div class="warning-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="warning-title">PENTING!</div>
        <div class="warning-text">
            Fitur ini digunakan jika guru tidak hadir di kelas dan belum ada laporan dari guru tersebut.
            Pastikan melaporkan dengan jujur dan bertanggung jawab.
        </div>
    </div>

    @php
        $siswa = auth()->user()->siswa;
        $tanggal = now()->toDateString();

        // Ambil jadwal hari ini untuk kelas siswa
        $jadwalHariIni = \App\Models\JadwalKBM::with(['gtk', 'kelas'])
            ->where('hari', now()->locale('id')->dayName)
            ->where('kelas_id', $siswa->kelas_id)
            ->orderBy('jam_ke')
            ->get();

        // Cek jadwal yang sudah dilaporkan
        $sudahDilaporkan = \App\Models\LaporanKehadiranGuru::where('tanggal', $tanggal)
            ->where('kelas_id', $siswa->kelas_id)
            ->pluck('jadwal_kbm_id')
            ->toArray();

        $jadwalBelumLapor = $jadwalHariIni->filter(function($jadwal) use ($sudahDilaporkan) {
            return !in_array($jadwal->id, $sudahDilaporkan);
        });
    @endphp

    @if ($jadwalBelumLapor->isEmpty())
        <div class="card">
            <div class="c-body" style="text-align:center; padding:40px 20px;">
                <div style="font-size:3rem; color:#e2e8f0; margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 style="color:var(--text-main); margin-bottom:8px;">Semua Jadwal Sudah Ada Laporan</h3>
                <p style="color:var(--text-muted); margin-bottom:20px;">
                    Semua jadwal pelajaran hari ini sudah memiliki laporan kehadiran.
                </p>
                <a href="{{ route('absen.index') }}" class="action-btn btn-view">
                    <i class="fas fa-arrow-left"></i> Kembali ke Absen
                </a>
            </div>
        </div>
    @else
        <form id="laporanForm" method="POST" action="{{ route('kehadiran-guru.lapor-siswa') }}">
            @csrf

            {{-- Info --}}
            <div class="card">
                <div class="c-head">
                    <div class="c-icon" style="background:#fef3c7;"><i class="fas fa-info-circle"></i></div>
                    <h3>Informasi Laporan</h3>
                </div>
                <div class="c-body" style="padding:16px;">
                    <p style="color:var(--text-muted);font-size:.84rem;line-height:1.55;margin:0;">
                        Pilih jadwal pelajaran yang gurunya tidak hadir. Pastikan memilih status yang sesuai dengan kondisi sebenarnya.
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
                                <i class="fas fa-clock"></i> {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                            </div>
                        </div>
                    </div>

                    <div class="status-radio-group">
                        <label class="status-radio">
                            <input type="radio" name="jadwal_kbm_id" value="{{ $jadwal->id }}" required>
                            <div class="status-option">
                                <div class="status-dot" style="background:#F44336;"></div>
                                <div>
                                    <div class="status-label">Tidak Hadir - Tidak Ada Tugas</div>
                                    <div class="status-desc">Guru tidak hadir dan tidak ada tugas</div>
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
                                    <div class="status-desc">Guru tidak hadir tapi ada tugas</div>
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
                                    <div class="status-desc">Guru datang lalu pergi dengan meninggalkan tugas</div>
                                </div>
                                <input type="hidden" name="status[{{ $jadwal->id }}]" value="biru">
                            </div>
                        </label>

                        <label class="status-radio">
                            <input type="radio" name="jadwal_kbm_id" value="{{ $jadwal->id }}">
                            <div class="status-option">
                                <div class="status-dot" style="background:#E91E63;"></div>
                                <div>
                                    <div class="status-label">Hadir Lalu Pergi - Tidak Ada Tugas</div>
                                    <div class="status-desc">Guru datang lalu pergi tanpa meninggalkan tugas</div>
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
    <a href="{{ route('absen.index') }}" class="ab-btn ab-btn-back">
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