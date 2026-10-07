@extends('layouts.app')

@section('title', 'Petugas Laporan Guru')

@push('styles')
    @include('components.event-styles')
    <style>
        .plg-wrap {
            padding-bottom: calc(var(--footer-h, 64px) + 36px);
        }

        .plg-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin: 14px 16px;
        }

        .plg-stat {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 11px;
            text-align: center;
        }

        .plg-stat strong {
            display: block;
            color: #0f172a;
            font-size: 1.25rem;
            line-height: 1;
        }

        .plg-stat span {
            display: block;
            margin-top: 5px;
            color: #64748b;
            font-size: .67rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .plg-panel {
            margin: 0 16px 14px;
            padding: 15px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .plg-label {
            display: block;
            margin-bottom: 6px;
            color: #475569;
            font-size: .76rem;
            font-weight: 700;
        }

        .plg-select,
        .plg-search {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            background: #fff;
            color: #0f172a;
            font-size: .84rem;
        }

        .plg-note {
            margin: 10px 0 0;
            color: #64748b;
            font-size: .76rem;
            line-height: 1.5;
        }

        .plg-title-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: center;
            margin-bottom: 12px;
        }

        .plg-title {
            margin: 0;
            color: #0f172a;
            font-size: .98rem;
            font-weight: 800;
        }

        .plg-counter {
            padding: 4px 9px;
            border-radius: 20px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: .72rem;
            font-weight: 800;
        }

        .plg-list {
            display: grid;
            gap: 8px;
            max-height: 430px;
            overflow: auto;
            margin-top: 10px;
        }

        .plg-option {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
        }

        .plg-option:has(input:checked) {
            border-color: #60a5fa;
            background: #eff6ff;
        }

        .plg-option input {
            width: 17px;
            height: 17px;
            accent-color: #2563eb;
            flex-shrink: 0;
        }

        .plg-option strong {
            display: block;
            color: #1e293b;
            font-size: .82rem;
        }

        .plg-option span {
            display: block;
            color: #64748b;
            font-size: .71rem;
        }

        .plg-save {
            width: 100%;
            margin-top: 12px;
            padding: 11px;
            border: 0;
            border-radius: 9px;
            background: #2563eb;
            color: #fff;
            font-size: .84rem;
            font-weight: 800;
            cursor: pointer;
        }

        .plg-save:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .plg-class-list {
            display: grid;
            gap: 9px;
            margin: 0 16px;
        }

        .plg-class {
            display: block;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            color: inherit;
            text-decoration: none;
        }

        .plg-class.active {
            border-color: #60a5fa;
            box-shadow: 0 0 0 2px #dbeafe;
        }

        .plg-class-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .plg-class-head strong {
            color: #0f172a;
            font-size: .86rem;
        }

        .plg-badge {
            padding: 3px 8px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 800;
        }

        .plg-ok {
            background: #dcfce7;
            color: #15803d;
        }

        .plg-warn {
            background: #fef3c7;
            color: #b45309;
        }

        .plg-names {
            margin: 0;
            color: #64748b;
            font-size: .74rem;
            line-height: 1.55;
        }

        .plg-empty {
            color: #94a3b8;
            font-style: italic;
        }
    </style>
@endpush

@section('content')
    <div class="plg-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span> Akses Laporan Siswa</div>
            <h2><i class="fas fa-user-shield"></i> Petugas Laporan Guru</h2>
            <p>Pilih tepat 3 siswa per kelas yang dapat mengirim dan mengedit laporan kehadiran guru.</p>
        </div>

        @if (session('success'))
            <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert a-err">
                <i class="fas fa-times-circle"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="plg-stats">
            <div class="plg-stat"><strong>{{ $kelas->count() }}</strong><span>Total Kelas</span></div>
            <div class="plg-stat"><strong>{{ $jumlahKelasLengkap }}</strong><span>Sudah Lengkap</span></div>
            <div class="plg-stat"><strong>{{ $kelas->count() - $jumlahKelasLengkap }}</strong><span>Perlu Diatur</span>
            </div>
        </div>

        <div class="plg-panel">
            <form method="GET" action="{{ route('admin.petugas-laporan-guru.index') }}">
                <label class="plg-label" for="kelas_id">Pilih kelas yang akan diatur</label>
                <select class="plg-select" id="kelas_id" name="kelas_id" onchange="this.form.submit()">
                    @foreach ($kelas as $item)
                        <option value="{{ $item->id }}" {{ $kelasDipilih?->id === $item->id ? 'selected' : '' }}>
                            {{ $item->nama_kelas }} ({{ $item->siswa_count }} siswa)
                        </option>
                    @endforeach
                </select>
            </form>
            <p class="plg-note">Ketiga siswa memiliki hak yang sama. Setelah salah satu mengirim laporan untuk satu jadwal,
                petugas lain hanya dapat memperbarui laporan tersebut selama batas edit masih aktif.</p>
        </div>

        @if ($kelasDipilih)
            <div class="plg-panel">
                <div class="plg-title-row">
                    <h3 class="plg-title">Atur {{ $kelasDipilih->nama_kelas }}</h3>
                    <span class="plg-counter"><span
                            id="selected-count">{{ count($petugasTerpilihIds) }}</span>/{{ $jumlahPetugasPerKelas }}
                        dipilih</span>
                </div>

                @if ($siswaKelas->count() < $jumlahPetugasPerKelas)
                    <p class="plg-note" style="color:#b45309;">Kelas ini belum memiliki cukup siswa untuk memilih
                        {{ $jumlahPetugasPerKelas }} petugas.</p>
                @else
                    <input type="search" id="student-search" class="plg-search" placeholder="Cari nama atau NIS siswa...">
                    <form method="POST" action="{{ route('admin.petugas-laporan-guru.update', $kelasDipilih) }}">
                        @csrf
                        @method('PUT')
                        <div class="plg-list" id="student-list">
                            @foreach ($siswaKelas as $siswa)
                                <label class="plg-option"
                                    data-search="{{ Str::lower($siswa->nama_lengkap . ' ' . $siswa->nis . ' ' . $siswa->nisn) }}">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}"
                                        {{ in_array($siswa->id, $petugasTerpilihIds, true) ? 'checked' : '' }}>
                                    <span>
                                        <strong>{{ $siswa->nama_lengkap }}</strong>
                                        <span>NIS: {{ $siswa->nis ?: '-' }} @if ($siswa->nisn)
                                                | NISN: {{ $siswa->nisn }}
                                            @endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="plg-save" id="save-reporters">
                            <i class="fas fa-save"></i> Simpan 3 Petugas Kelas
                        </button>
                    </form>
                @endif
            </div>
        @endif

        <div class="section-label" style="margin:18px 16px 10px;">Daftar Petugas Per Kelas</div>
        <div class="plg-class-list">
            @foreach ($kelas as $item)
                @php
                    $petugasValid = $item->siswaPetugasLaporan
                        ->filter(fn($petugas) => $petugas->siswa && (int) $petugas->siswa->kelas_id === (int) $item->id)
                        ->values();
                @endphp
                <a href="{{ route('admin.petugas-laporan-guru.index', ['kelas_id' => $item->id]) }}"
                    class="plg-class {{ $kelasDipilih?->id === $item->id ? 'active' : '' }}">
                    <div class="plg-class-head">
                        <strong>{{ $item->nama_kelas }}</strong>
                        <span
                            class="plg-badge {{ $petugasValid->count() === $jumlahPetugasPerKelas ? 'plg-ok' : 'plg-warn' }}">
                            {{ $petugasValid->count() }}/{{ $jumlahPetugasPerKelas }} petugas
                        </span>
                    </div>
                    @if ($petugasValid->isEmpty())
                        <p class="plg-names plg-empty">Belum ada siswa yang dipilih.</p>
                    @else
                        <p class="plg-names">{{ $petugasValid->pluck('siswa.nama_lengkap')->implode(', ') }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const max = {{ $jumlahPetugasPerKelas }};
            const checkboxes = Array.from(document.querySelectorAll('#student-list input[type="checkbox"]'));
            const counter = document.getElementById('selected-count');
            const saveButton = document.getElementById('save-reporters');
            const search = document.getElementById('student-search');

            function refreshSelection() {
                const selected = checkboxes.filter(function(checkbox) {
                    return checkbox.checked;
                });
                if (counter) counter.textContent = selected.length;
                if (saveButton) saveButton.disabled = selected.length !== max;
                checkboxes.forEach(function(checkbox) {
                    checkbox.disabled = !checkbox.checked && selected.length >= max;
                });
            }

            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', refreshSelection);
            });

            if (search) {
                search.addEventListener('input', function() {
                    const query = search.value.toLowerCase().trim();
                    document.querySelectorAll('#student-list .plg-option').forEach(function(option) {
                        option.style.display = option.dataset.search.includes(query) ? '' : 'none';
                    });
                });
            }

            refreshSelection();

            const header = document.querySelector('.header-auto-show');
            if (header) {
                header.classList.add('header-active');
            }
        });
    </script>
@endpush
