{{--
    Komponen: Home Menu Grid (ditampilkan di halaman dashboard).
    Menu disusun dari hak akses route agar dashboard tidak menawarkan
    tautan yang tidak bisa dibuka oleh role pengguna.
--}}

@php
    $user = auth()->user();
    $has = fn ($roles) => $user?->hasAnyRole((array) $roles) ?? false;
    $isPetugasLaporanGuru = $user?->hasRole('siswa') && ($user->siswa?->isPetugasLaporanGuru() ?? false);

    // Status PKL siswa:
    //   $isPklAktif   → siswa sedang PKL aktif sekarang  (bisa input jurnal, lihat dashboard)
    //   $isPklSelesai → siswa pernah PKL dan sudah selesai (hanya lihat rekap jurnal, read-only)
    //   $isPklSiswa   → gabungan: tampilkan menu PKL jika aktif ATAU selesai
    if ($has('siswa') && $user?->siswa) {
        $isPklAktif   = $user->siswa->penugasanPkl()
                            ->where('status', 'aktif')
                            ->exists();
        $isPklSelesai = !$isPklAktif && $user->siswa->penugasanPkl()
                            ->where('status', 'selesai')
                            ->exists();
        $isPklSiswa   = $isPklAktif || $isPklSelesai;
    } else {
        $isPklAktif   = false;
        $isPklSelesai = false;
        $isPklSiswa   = false;
    }

    // Hitung izin pending untuk badge (hanya load jika admin)
    $izinPendingDash = $has(['superadmin', 'admin_tatib', 'bk', 'wali_kelas'])
        ? \App\Models\PengajuanIzin::diajukan()->count()
        : 0;

    $menuGroups = [
        [
            'title' => 'Absensi Saya',
            'desc' => 'Kehadiran, izin, dan rekap pribadi',
            'icon' => 'fa-user-check',
            'color' => 'c-green',
            'visible' => $has('siswa'),
            'items' => [
                ['label' => 'Absen Masuk / Pulang', 'desc' => 'Presensi harian', 'route' => 'absen.index', 'icon' => 'fa-clipboard-check', 'color' => 'c-green'],
                ['label' => 'Rekap Kehadiran Saya', 'desc' => 'Riwayat absensi', 'route' => 'absen.rekap', 'icon' => 'fa-chart-bar', 'color' => 'c-blue'],
                ['label' => 'Rekap Poin Saya', 'desc' => 'Poin tata tertib', 'route' => 'admin.rekap-poin.index', 'icon' => 'fa-award', 'color' => 'c-yellow'],
                // ['label' => 'Pengajuan Izin', 'desc' => 'Izin sakit/terlambat', 'route' => 'siswa.izin.index', 'icon' => 'fa-file-medical', 'color' => 'c-teal'],
            ],
        ],
        [
            'title' => 'Kegiatan Siswa',
            'desc' => 'Event sekolah dan laporan kelas',
            'icon' => 'fa-school',
            'color' => 'c-orange',
            'visible' => $has('siswa'),
            'items' => array_values(array_filter([
                ['label' => 'Event & Absensi Event', 'desc' => 'Kegiatan sekolah', 'route' => 'event.index', 'icon' => 'fa-calendar', 'color' => 'c-red'],
                $isPetugasLaporanGuru
                    ? ['label' => 'Lapor Guru Tidak Hadir', 'desc' => 'Petugas kelas', 'route' => 'siswa.lapor-guru', 'icon' => 'fa-exclamation-triangle', 'color' => 'c-orange']
                    : null,
            ])),
        ],
        [
            'title' => 'Tugas Mengajar',
            'desc' => 'Jadwal, jurnal, dan laporan KBM',
            'icon' => 'fa-chalkboard-teacher',
            'color' => 'c-orange',
            'visible' => $has(['gtk', 'TU']),
            'items' => array_values(array_filter([
                $has(['gtk'])
                    ? [
                        'label' => 'Jadwal Mengajar Saya',
                        'desc' => 'Jadwal KBM guru',
                        'route' => 'admin.jadwal-kbm.guru',
                        'icon' => 'fa-calendar-day',
                        'color' => 'c-orange',
                    ]
                    : null,
        
                $has(['gtk'])
                    ? [
                        'label' => 'Jurnal Mengajar',
                        'desc' => 'Isi jurnal pembelajaran',
                        'route' => 'guru.jurnal-mengajar.index',
                        'icon' => 'fa-book-open',
                        'color' => 'c-blue',
                    ]
                    : null,
        
                $has(['gtk', 'TU'])
                    ? [
                        'label' => 'Laporan Kehadiran KBM',
                        'desc' => 'Status kehadiran guru',
                        'route' => 'kehadiran-guru.laporan',
                        'icon' => 'fa-clipboard-list',
                        'color' => 'c-green',
                    ]
                    : null,
        
                $has(['gtk', 'TU'])
                    ? [
                        'label' => 'Event & Absen Guru',
                        'desc' => 'Scan barcode kegiatan',
                        'route' => 'event-guru.index',
                        'icon' => 'fa-star',
                        'color' => 'c-indigo',
                    ]
                    : null,
            ])),
        ],
        [
            'title' => 'Manajemen Data',
            'desc' => 'Data utama sekolah',
            'icon' => 'fa-database',
            'color' => 'c-blue',
            'visible' => $has(['superadmin', 'admin_tatib', 'bk', 'wali_kelas']),
            'items' => array_values(array_filter([
                $has(['superadmin', 'admin_tatib', 'bk', 'wali_kelas'])
                    ? ['label' => 'Data Siswa', 'desc' => 'Profil dan kelas siswa', 'route' => 'siswa.index', 'icon' => 'fa-users', 'color' => 'c-blue']
                    : null,
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Data GTK', 'desc' => 'Guru dan tenaga kependidikan', 'route' => 'gtk.index', 'icon' => 'fa-briefcase', 'color' => 'c-teal']
                    : null,
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Data Kelas', 'desc' => 'Rombel dan wali kelas', 'route' => 'kelas.index', 'icon' => 'fa-th', 'color' => 'c-purple']
                    : null,
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Mata Pelajaran', 'desc' => 'Master mapel', 'route' => 'admin.mata-pelajaran.index', 'icon' => 'fa-book-open', 'color' => 'c-indigo']
                    : null,
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Tahun Ajaran', 'desc' => 'Periode akademik', 'route' => 'academic-years.index', 'icon' => 'fa-calendar-alt', 'color' => 'c-yellow']
                    : null,
            ])),
        ],
        [
            'title' => 'Penjadwalan',
            'desc' => 'Jam pelajaran dan jadwal mengajar',
            'icon' => 'fa-calendar-alt',
            'color' => 'c-orange',
            'visible' => $has(['superadmin', 'admin_tatib']),
            'items' => array_values(array_filter([
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Set Jam Pelajaran', 'desc' => 'Master jam KBM', 'route' => 'admin.set-jam.index', 'icon' => 'fa-clock', 'color' => 'c-blue']
                    : null,
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Jadwal KBM', 'desc' => 'Kelola jadwal kelas', 'route' => 'admin.jadwal-kbm.index', 'icon' => 'fa-calendar', 'color' => 'c-orange']
                    : null,
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Jadwal Guru', 'desc' => 'Jadwal per guru', 'route' => 'admin.jadwal-kbm.guru', 'icon' => 'fa-user-check', 'color' => 'c-teal']
                    : null,
            ])),
        ],
        // [
        //     'title' => 'Verifikasi Izin',
        //     'desc' => 'Setujui atau tolak pengajuan izin siswa',
        //     'icon' => 'fa-file-check',
        //     'color' => 'c-teal',
        //     'visible' => $has(['superadmin', 'admin_tatib', 'bk', 'wali_kelas']),
        //     'items' => [
        //         ['label' => 'Verifikasi Izin Siswa', 'desc' => 'Setujui / tolak pengajuan', 'route' => 'admin.izin.index', 'icon' => 'fa-file-check', 'color' => 'c-teal', 'badge' => $izinPendingDash ?: null],
        //     ],
        // ],
        [
            'title' => 'Kehadiran & Absensi',
            'desc' => 'Rekap siswa, laporan guru, jurnal, dan event',
            'icon' => 'fa-clipboard-check',
            'color' => 'c-green',
            'visible' => $has(['superadmin', 'admin_tatib', 'bk', 'wali_kelas', 'waka', 'kepala_sekolah', 'Event']),
            'items' => array_values(array_filter([
                $has(['siswa', 'gtk', 'superadmin', 'admin_tatib', 'bk', 'wali_kelas'])
                    ? ['label' => $has('siswa') && ! $has(['gtk', 'superadmin', 'admin_tatib', 'bk', 'wali_kelas']) ? 'Rekap Kehadiran Saya' : 'Rekap Absensi Siswa', 'desc' => 'Data kehadiran', 'route' => 'absen.rekap', 'icon' => 'fa-check-square', 'color' => 'c-green']
                    : null,
                $has(['superadmin', 'admin_tatib', 'bk', 'wali_kelas'])
                    ? ['label' => 'Absensi Manual & Izin', 'desc' => 'Tambah/edit absen & buat izin', 'route' => 'admin.absen-manual.index', 'icon' => 'fa-clipboard-list', 'color' => 'c-teal']
                    : null,
                $has(['gtk', 'superadmin', 'admin_tatib', 'bk'])
                    ? ['label' => 'Laporan Kehadiran Guru', 'desc' => 'Status KBM guru', 'route' => 'kehadiran-guru.laporan', 'icon' => 'fa-clipboard-check', 'color' => 'c-green']
                    : null,
                $has(['superadmin', 'waka', 'kepala_sekolah'])
                    ? ['label' => 'Jurnal Mengajar Guru', 'desc' => 'Jurnal pembelajaran', 'route' => 'guru.jurnal-mengajar.index', 'icon' => 'fa-book-open', 'color' => 'c-blue']
                    : null,
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Petugas Laporan Guru', 'desc' => 'Penanggung jawab kelas', 'route' => 'admin.petugas-laporan-guru.index', 'icon' => 'fa-user-shield', 'color' => 'c-blue']
                    : null,
                $has(['siswa', 'gtk', 'superadmin', 'admin_tatib', 'bk', 'wali_kelas', 'Event'])
                    ? ['label' => 'Event & Absensi Event', 'desc' => 'Kegiatan sekolah', 'route' => 'event.index', 'icon' => 'fa-star', 'color' => 'c-red']
                    : null,
                $has(['superadmin', 'admin_tatib', 'bk', 'wali_kelas', 'Event'])
                    ? ['label' => 'Event Guru', 'desc' => 'Kelola & rekap event guru', 'route' => 'event-guru.index', 'icon' => 'fa-chalkboard-teacher', 'color' => 'c-indigo']
                    : null,            ])),
        ],
        [
            'title' => 'Tata Tertib',
            'desc' => 'Pelanggaran, penghargaan, dan rekap poin',
            'icon' => 'fa-gavel',
            'color' => 'c-purple',
            'visible' => $has(['gtk', 'superadmin', 'admin_tatib', 'bk', 'wali_kelas', 'waka', 'kepala_sekolah', 'Event']),
            'items' => array_values(array_filter([
                $has(['superadmin', 'admin_tatib', 'bk', 'gtk', 'wali_kelas', 'Event'])
                    ? ['label' => 'Pelanggaran Siswa', 'desc' => 'Catat pelanggaran', 'route' => 'admin.pelanggaran.index', 'icon' => 'fa-exclamation-circle', 'color' => 'c-red']
                    : null,
                $has(['superadmin', 'admin_tatib', 'bk', 'gtk', 'wali_kelas', 'Event'])
                    ? ['label' => 'Penghargaan Siswa', 'desc' => 'Catat apresiasi', 'route' => 'admin.penghargaan.index', 'icon' => 'fa-award', 'color' => 'c-green']
                    : null,
                $has(['superadmin', 'admin_tatib', 'bk'])
                    ? ['label' => 'Master Pasal', 'desc' => 'Atur pasal dan poin', 'route' => 'admin.pasal.index', 'icon' => 'fa-book-open', 'color' => 'c-purple']
                    : null,
                $has(['superadmin', 'admin_tatib', 'bk', 'gtk', 'wali_kelas', 'waka', 'kepala_sekolah'])
                ? [
                    'label' => 'Rekap Poin Siswa',
                    'desc' => 'Akumulasi poin',
                    'route' => 'admin.rekap-poin.index',
                    'icon' => 'fa-bullseye',
                    'color' => 'c-blue',
                ]
                : null,
                $has(['superadmin', 'admin_tatib', 'bk'])
                    ? ['label' => 'Surat Panggilan Ortu', 'desc' => 'Buat & cetak surat', 'route' => 'admin.surat-panggilan.index', 'icon' => 'fa-envelope-open-text', 'color' => 'c-orange']
                    : null,
            ])),
        ],
        // ── PKL (Praktik Kerja Lapangan) ─────────────────────────────────────
        [
            'title'   => 'PKL',
            'desc'    => 'Praktik Kerja Lapangan',
            'icon'    => 'fa-hard-hat',
            'color'   => 'c-yellow',
            'visible' => $isPklSiswa || $user?->can('pkl.view'),
            'items'   => array_values(array_filter([

                // ── Menu Siswa PKL AKTIF ──────────────────────────────────
                $isPklAktif
                    ? ['label' => 'Dashboard PKL', 'desc' => 'Info & status PKL kamu', 'route' => 'siswa.pkl.dashboard', 'icon' => 'fa-tachometer-alt', 'color' => 'c-yellow']
                    : null,
                $isPklAktif
                    ? ['label' => 'Jurnal Harian PKL', 'desc' => 'Isi catatan kegiatan harian', 'route' => 'siswa.pkl.jurnal.create', 'icon' => 'fa-book-open', 'color' => 'c-blue']
                    : null,
                $isPklAktif
                    ? ['label' => 'Riwayat Jurnal Saya', 'desc' => 'Semua jurnal yang sudah diisi', 'route' => 'siswa.pkl.jurnal.index', 'icon' => 'fa-list-alt', 'color' => 'c-indigo']
                    : null,

                // ── Menu Siswa PKL SELESAI (read-only rekap) ─────────────
                $isPklSelesai
                    ? ['label' => 'Rekap Jurnal PKL Saya', 'desc' => 'Lihat riwayat kegiatan PKL', 'route' => 'siswa.pkl.jurnal.rekap', 'icon' => 'fa-history', 'color' => 'c-teal']
                    : null,

                // ── Menu Admin / Waka / Tatib ─────────────────────────────
                $user?->can('pkl.view') && !$isPklSiswa
                    ? ['label' => 'Penugasan Siswa PKL', 'desc' => 'Kelola penugasan ke lokasi', 'route' => 'admin.pkl.penugasan.index', 'icon' => 'fa-user-tie', 'color' => 'c-teal']
                    : null,
                $user?->can('pkl.view') && !$isPklSiswa
                    ? ['label' => 'Lokasi PKL', 'desc' => 'Master tempat magang', 'route' => 'admin.pkl.lokasi.index', 'icon' => 'fa-map-marker-alt', 'color' => 'c-orange']
                    : null,
                $user?->can('pkl.view') && !$isPklSiswa
                    ? ['label' => 'Rekap per Siswa', 'desc' => 'Laporan kehadiran siswa PKL', 'route' => 'admin.pkl.rekap.per-siswa', 'icon' => 'fa-chart-bar', 'color' => 'c-purple']
                    : null,
                $user?->can('pkl.view') && !$isPklSiswa
                    ? ['label' => 'Rekap per Lokasi', 'desc' => 'Statistik per tempat PKL', 'route' => 'admin.pkl.rekap.per-lokasi', 'icon' => 'fa-map', 'color' => 'c-blue']
                    : null,
            ])),
        ],
        [
            'title' => 'Monitoring',
            'desc' => 'Pantauan kehadiran sekolah',
            'icon' => 'fa-chart-line',
            'color' => 'c-blue',
            'visible' => $has(['superadmin', 'waka', 'kepala_sekolah', 'kepsek', 'admin_tatib', 'bk', 'kurikulum']),
            'items' => array_values(array_filter([
                $has(['superadmin', 'waka', 'kepala_sekolah', 'kepsek'])
                    ? ['label' => 'Panel Realtime Kehadiran', 'desc' => 'Status kelas langsung', 'route' => 'panel.realtime', 'icon' => 'fa-chart-line', 'color' => 'c-blue']
                    : null,
                $user?->can('dashboard-laporan.view')
                    ? ['label' => 'Dashboard Laporan', 'desc' => 'Grafik & analitik aktivitas', 'route' => 'admin.dashboard-laporan.index', 'icon' => 'fa-chart-bar', 'color' => 'c-purple']
                    : null,
            ])),
        ],
        [
            'title' => 'Sistem',
            'desc' => 'Konfigurasi dan pemeriksaan aplikasi',
            'icon' => 'fa-cogs',
            'color' => 'c-yellow',
            'visible' => $has(['superadmin', 'admin_tatib']),
            'items' => array_values(array_filter([
                $has(['superadmin', 'admin_tatib'])
                    ? ['label' => 'Konfigurasi Sekolah', 'desc' => 'Identitas dan aturan app', 'route' => 'admin.school-config.index', 'icon' => 'fa-cog', 'color' => 'c-blue']
                    : null,
                $has('superadmin')
                    ? ['label' => 'Role & Permission', 'desc' => 'Kelola hak akses sistem', 'route' => 'admin.roles.index', 'icon' => 'fa-user-shield', 'color' => 'c-orange']
                    : null,
                $has('superadmin')
                    ? ['label' => 'Log Viewer', 'desc' => 'Pantau log aplikasi', 'url' => url('/log-viewer'), 'target' => '_blank', 'icon' => 'fa-terminal', 'color' => 'c-yellow']
                    : null,
            ])),
        ],
        [
            'title' => 'Akun',
            'desc' => 'Profil dan sesi pengguna',
            'icon' => 'fa-user-circle',
            'color' => 'c-purple',
            'visible' => true,
            'items' => [
                ['label' => 'Profil Saya', 'desc' => 'Data akun pribadi', 'route' => 'profile.index', 'icon' => 'fa-user', 'color' => 'c-purple'],
                ['label' => 'Keluar', 'desc' => 'Akhiri sesi login', 'url' => '#', 'icon' => 'fa-sign-out-alt', 'color' => 'c-red', 'logout' => true],
            ],
        ],
    ];

    $visibleGroups = collect($menuGroups)
        ->filter(fn ($group) => $group['visible'] && ! empty($group['items']))
        ->values();
@endphp

<div class="home-menu">
    <div class="home-menu-titlebar">
        <p class="section-title">Daftar Menu</p>
        <span>{{ $visibleGroups->sum(fn ($group) => count($group['items'])) }} akses</span>
    </div>

    @foreach ($visibleGroups as $group)
        <section class="home-menu-group">
            <div class="home-menu-group-head">
                <span class="home-menu-group-icon {{ $group['color'] }}"><i class="fas {{ $group['icon'] }}"></i></span>
                <div>
                    <h3>{{ $group['title'] }}</h3>
                    <p>{{ $group['desc'] }}</p>
                </div>
            </div>

            <div class="qa-grid">
                @foreach ($group['items'] as $item)
                    @php
                        $href = $item['route'] ?? null ? route($item['route']) : ($item['url'] ?? '#');
                        $target = $item['target'] ?? null;
                    @endphp

                    <a
                        href="{{ $href }}"
                        class="qa-item"
                        @if ($target) target="{{ $target }}" rel="noopener" @endif
                        @if (! empty($item['logout'])) onclick="event.preventDefault(); document.getElementById('dashboard-logout-form').submit();" @endif
                    >
                        <div class="qa-icon {{ $item['color'] }}"><i class="fas {{ $item['icon'] }}"></i></div>
                        <div class="qa-text">
                            <div class="qa-label">{{ $item['label'] }}</div>
                            <div class="qa-desc">{{ $item['desc'] }}</div>
                        </div>
                        @if (!empty($item['badge']))
                            <span style="flex-shrink:0;background:#ef4444;color:#fff;font-size:.6rem;font-weight:700;padding:2px 7px;border-radius:20px;min-width:20px;text-align:center;">
                                {{ $item['badge'] > 99 ? '99+' : $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach

    <form id="dashboard-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</div>
