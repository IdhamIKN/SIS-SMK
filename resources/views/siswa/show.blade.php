@extends('layouts.app')

@section('title', 'Detail Siswa')

@section('content')

    {{-- Page Title --}}
    <div class="page-title page-title-small">
        <h2><a href="#" data-back-button><i class="fa fa-arrow-left"></i></a>Detail Siswa</h2>
        <div class="float-end">
            <a href="{{ route('siswa.edit', $siswa) }}" class="btn btn-s bg-blue-dark rounded-s text-uppercase font-900 me-2">
                <i class="fas fa-edit me-1"></i>Edit
            </a>
            <a href="{{ route('siswa.export-cv', $siswa) }}" target="_blank" class="btn btn-s bg-green-dark rounded-s text-uppercase font-900">
                <i class="fas fa-file-pdf me-1"></i>CV
            </a>
        </div>
    </div>

    {{-- Header Card --}}
    <div class="card header-card shape-rounded" data-card-height="120">
        <div class="card-overlay bg-highlight opacity-95"></div>
        <div class="card-overlay dark-mode-tint"></div>
        <div class="card-bg preload-img" data-src="{{ asset('azures/images/pictures/20s.jpg') }}"></div>
    </div>

    {{-- Student Info --}}
    <div class="card card-style">
        <div class="content">
            <div class="d-flex mb-4">
                {{-- Avatar --}}
                <div class="align-self-center">
                    @if($siswa->foto)
                        <img src="{{ Storage::url($siswa->foto) }}" class="rounded-s shadow-l" width="80" height="80" style="object-fit: cover;">
                    @else
                        <div class="bg-blue-dark rounded-s shadow-l d-flex align-items-center justify-content-center" style="width:80px;height:80px;">
                            <i class="fas fa-user-graduate font-24 color-white"></i>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="align-self-center ms-3 flex-fill">
                    <h3 class="font-700 mb-1">{{ $siswa->nama_lengkap }}</h3>
                    <p class="font-14 color-highlight mb-1">NISN: {{ $siswa->nisn }}</p>
                    <p class="font-13 opacity-70 mb-0">
                        {{ $siswa->kelas?->nama_kelas ?? '-' }} •
                        {{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} •
                        <span class="badge {{ $siswa->status_aktif ? 'bg-green-dark' : 'bg-red-dark' }} color-white font-10">
                            {{ $siswa->status_aktif ? 'Aktif' : 'Non Aktif' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Pribadi --}}
    <div class="card card-style">
        <div class="content mb-2">
            <h4 class="font-700 mb-3">Data Pribadi</h4>

            <div class="row mb-0">
                <div class="col-6">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">NIS</div>
                            <div class="info-cell">: {{ $siswa->nis ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">NISN</div>
                            <div class="info-cell">: {{ $siswa->nisn }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Nama Lengkap</div>
                            <div class="info-cell">: {{ $siswa->nama_lengkap }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Jenis Kelamin</div>
                            <div class="info-cell">: {{ $siswa->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Tempat Lahir</div>
                            <div class="info-cell">: {{ $siswa->tempat_lahir ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Tanggal Lahir</div>
                            <div class="info-cell">: {{ $siswa->tanggal_lahir?->format('d/m/Y') ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Angkatan</div>
                            <div class="info-cell">: {{ $siswa->angkatan ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Status</div>
                            <div class="info-cell">:
                                <span class="badge {{ $siswa->status_aktif ? 'bg-green-dark' : 'bg-red-dark' }} color-white">
                                    {{ $siswa->status_aktif ? 'Aktif' : 'Non Aktif' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <strong>Alamat:</strong> {{ $siswa->alamat ?: '-' }}
            </div>
        </div>
    </div>

    {{-- Kontak --}}
    <div class="card card-style">
        <div class="content mb-2">
            <h4 class="font-700 mb-3">Kontak</h4>

            <div class="row mb-0">
                <div class="col-6">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">No HP Siswa</div>
                            <div class="info-cell">: {{ $siswa->no_hp_siswa ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">No HP Ortu 1</div>
                            <div class="info-cell">: {{ $siswa->no_hp_ortu1 ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">No HP Ortu 2</div>
                            <div class="info-cell">: {{ $siswa->no_hp_ortu2 ?: '-' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-cell info-label">Nama Ortu 1</div>
                            <div class="info-cell">: {{ $siswa->nama_ortu1 ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Nama Ortu 2</div>
                            <div class="info-cell">: {{ $siswa->nama_ortu2 ?: '-' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-cell info-label">Nama Wali</div>
                            <div class="info-cell">: {{ $siswa->nama_wali ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Absensi --}}
    <div class="card card-style">
        <div class="content mb-2">
            <h4 class="font-700 mb-3">Riwayat Absensi {{ date('Y') }}</h4>

            @php
                $absenStats = [
                    'hadir' => $siswa->absenSiswa->where('status', 'hadir')->count(),
                    'sakit' => $siswa->absenSiswa->where('status', 'sakit')->count(),
                    'izin' => $siswa->absenSiswa->where('status', 'izin')->count(),
                    'alfa' => $siswa->absenSiswa->where('status', 'alfa')->count(),
                ];
            @endphp

            {{-- Statistik --}}
            <div class="row mb-3">
                <div class="col-3 text-center">
                    <div class="bg-green-fade-light rounded-s p-2">
                        <h5 class="font-700 color-green-dark mb-0">{{ $absenStats['hadir'] }}</h5>
                        <small class="color-green-dark">Hadir</small>
                    </div>
                </div>
                <div class="col-3 text-center">
                    <div class="bg-blue-fade-light rounded-s p-2">
                        <h5 class="font-700 color-blue-dark mb-0">{{ $absenStats['sakit'] }}</h5>
                        <small class="color-blue-dark">Sakit</small>
                    </div>
                </div>
                <div class="col-3 text-center">
                    <div class="bg-orange-fade-light rounded-s p-2">
                        <h5 class="font-700 color-orange-dark mb-0">{{ $absenStats['izin'] }}</h5>
                        <small class="color-orange-dark">Izin</small>
                    </div>
                </div>
                <div class="col-3 text-center">
                    <div class="bg-red-fade-light rounded-s p-2">
                        <h5 class="font-700 color-red-dark mb-0">{{ $absenStats['alfa'] }}</h5>
                        <small class="color-red-dark">Alfa</small>
                    </div>
                </div>
            </div>

            {{-- Tabel Absensi --}}
            @include('components.azures.table', [
                'headers' => ['Tanggal', 'Jenis', 'Status', 'Waktu'],
                'rows' => $siswa->absenSiswa->sortByDesc('tanggal')->take(10)->map(function($absen) {
                    return [
                        $absen->tanggal->format('d/m/Y'),
                        $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang',
                        '<span class="badge ' . ($absen->status === 'hadir' ? 'bg-green-dark' : 'bg-red-dark') . ' color-white">' . ucfirst($absen->status) . '</span>',
                        $absen->waktu_absen?->format('H:i') ?: '-'
                    ];
                })->toArray(),
                'emptyMessage' => 'Belum ada data absensi'
            ])
        </div>
    </div>
                    @endif
                </div>

                <!-- Absen Terbaru -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">Absen Terbaru</h2>
                    @forelse($siswa->absenSiswa->take(5) as $absen)
                        <div class="mb-3 pb-3 border-b border-gray-200 last:border-b-0">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium">{{ $absen->jenis === 'masuk' ? 'Masuk' : 'Pulang' }}</p>
                                    <p class="text-sm text-gray-600">{{ $absen->tanggal->format('d/m/Y') }}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-xs {{ $absen->status === 'hadir' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($absen->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500">{{ $absen->waktu_absen?->format('H:i') ?: '-' }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500">Belum ada data absen</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection