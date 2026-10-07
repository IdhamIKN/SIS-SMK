<div id="menu-main" class="menu menu-box-right menu-box-detached rounded-m"
    data-menu-width="270" data-menu-effect="menu-over">

    {{-- Header --}}
    <div class="menu-header">
        <a href="#" class="close-menu border-right-0">
            <i class="fa font-12 color-red-dark fa-times"></i>
        </a>
    </div>

    {{-- Avatar & Identitas --}}
    <div class="menu-logo text-center">
        @if (auth()->user()?->avatar)
            <a href="{{ route('profile.index') }}">
                <img class="rounded-circle shadow-l" width="80" src="{{ Storage::url(auth()->user()->avatar) }}" alt="Foto Profil">
            </a>
        @else
            <a href="{{ route('profile.index') }}">
                <div class="icon icon-xxl rounded-circle bg-highlight shadow-l mx-auto">
                    <i class="fas fa-user font-30 color-white"></i>
                </div>
            </a>
        @endif>
        <h1 class="pt-2 font-700 font-18">{{ sekolah_data()['system_name'] ?? config('app.name', 'SIS SMKN 5 Madiun') }}</h1>
        <h2 class="pt-1 font-600 font-16">{{ auth()->user()?->name ?? 'Pengguna' }}</h2>
        <p class="font-11 mt-n1 opacity-60">{{ auth()->user()?->getRoleNames()->first() ?? '' }}</p>
    </div>

    <div class="menu-items mb-4">

        {{-- BERANDA --}}
        <h5 class="text-uppercase opacity-20 font-12 pl-3">Beranda</h5>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active-nav' : '' }}">
            <i data-feather="home" data-feather-line="1" data-feather-size="17" data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
            <span>Dashboard</span>
            <i class="fa fa-angle-right"></i>
        </a>


        {{-- SISWA --}}
        @hasrole('siswa')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Absensi Saya</h5>

            <a href="{{ route('absen.index') }}" class="{{ request()->routeIs('absen.index') ? 'active-nav' : '' }}">
                <i data-feather="log-in" data-feather-line="1" data-feather-size="17" data-feather-color="green-dark" data-feather-bg="green-fade-light"></i>
                <span>Absen Masuk / Pulang</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('absen.rekap') }}" class="{{ request()->routeIs('absen.rekap') ? 'active-nav' : '' }}">
                <i data-feather="bar-chart-2" data-feather-line="1" data-feather-size="17" data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
                <span>Rekap Kehadiran Saya</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('admin.rekap-poin.index') }}" class="{{ request()->routeIs('admin.rekap-poin.*') ? 'active-nav' : '' }}">
                <i data-feather="award" data-feather-line="1" data-feather-size="17" data-feather-color="amber-dark" data-feather-bg="amber-fade-light"></i>
                <span>Rekap Poin Saya</span>
                <i class="fa fa-angle-right"></i>
            </a>

            {{-- <a href="{{ route('siswa.izin.index') }}" class="{{ request()->routeIs('siswa.izin.*') ? 'active-nav' : '' }}">
                <i data-feather="clipboard-list" data-feather-line="1" data-feather-size="17" data-feather-color="teal-dark" data-feather-bg="teal-fade-light"></i>
                <span>Pengajuan Izin</span>
                <i class="fa fa-angle-right"></i>
            </a> --}}

            <a href="{{ route('event.index') }}" class="{{ request()->routeIs('event.*') ? 'active-nav' : '' }}">
                <i data-feather="calendar" data-feather-line="1" data-feather-size="17" data-feather-color="red-dark" data-feather-bg="red-fade-light"></i>
                <span>Event & Absensi Event</span>
                <i class="fa fa-angle-right"></i>
            </a>

            @if (auth()->user()?->siswa?->isPetugasLaporanGuru())
                <a href="{{ route('siswa.lapor-guru') }}" class="{{ request()->routeIs('siswa.lapor-guru') ? 'active-nav' : '' }}">
                    <i data-feather="alert-triangle" data-feather-line="1" data-feather-size="17" data-feather-color="orange-dark" data-feather-bg="orange-fade-light"></i>
                    <span>Lapor Guru Tidak Hadir</span>
                    <i class="fa fa-angle-right"></i>
                </a>
            @endif

            {{-- PKL — hanya tampil jika siswa punya penugasan aktif atau selesai --}}
            @php
                $_pklAktif   = auth()->user()?->siswa?->penugasanPkl()->where('status','aktif')->exists() ?? false;
                $_pklSelesai = !$_pklAktif && (auth()->user()?->siswa?->penugasanPkl()->where('status','selesai')->exists() ?? false);
            @endphp
            @if ($_pklAktif || $_pklSelesai)
                <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">PKL</h5>

                @if ($_pklAktif)
                    <a href="{{ route('siswa.pkl.dashboard') }}" class="{{ request()->routeIs('siswa.pkl.dashboard') ? 'active-nav' : '' }}">
                        <i data-feather="hard-drive" data-feather-line="1" data-feather-size="17" data-feather-color="yellow-dark" data-feather-bg="yellow-fade-light"></i>
                        <span>Dashboard PKL</span>
                        <i class="fa fa-angle-right"></i>
                    </a>
                    <a href="{{ route('siswa.pkl.jurnal.create') }}" class="{{ request()->routeIs('siswa.pkl.jurnal.create') ? 'active-nav' : '' }}">
                        <i data-feather="edit-3" data-feather-line="1" data-feather-size="17" data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
                        <span>Isi Jurnal Hari Ini</span>
                        <i class="fa fa-angle-right"></i>
                    </a>
                    <a href="{{ route('siswa.pkl.jurnal.index') }}" class="{{ request()->routeIs('siswa.pkl.jurnal.index') ? 'active-nav' : '' }}">
                        <i data-feather="book-open" data-feather-line="1" data-feather-size="17" data-feather-color="indigo-dark" data-feather-bg="indigo-fade-light"></i>
                        <span>Riwayat Jurnal PKL</span>
                        <i class="fa fa-angle-right"></i>
                    </a>
                @endif

                @if ($_pklSelesai)
                    <a href="{{ route('siswa.pkl.jurnal.rekap') }}" class="{{ request()->routeIs('siswa.pkl.jurnal.rekap') ? 'active-nav' : '' }}">
                        <i data-feather="archive" data-feather-line="1" data-feather-size="17" data-feather-color="teal-dark" data-feather-bg="teal-fade-light"></i>
                        <span>Rekap Jurnal PKL Saya</span>
                        <i class="fa fa-angle-right"></i>
                    </a>
                @endif
            @endif
        @endhasrole


        {{-- GTK --}}
        @hasrole('gtk')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Tugas Mengajar</h5>

            <a href="{{ route('admin.jadwal-kbm.guru') }}" class="{{ request()->routeIs('admin.jadwal-kbm.guru') ? 'active-nav' : '' }}">
                <i data-feather="calendar" data-feather-line="1" data-feather-size="17" data-feather-color="orange-dark" data-feather-bg="orange-fade-light"></i>
                <span>Jadwal Mengajar Saya</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('guru.jurnal-mengajar.index') }}" class="{{ request()->routeIs('guru.jurnal-mengajar.*') ? 'active-nav' : '' }}">
                <i data-feather="book-open" data-feather-line="1" data-feather-size="17" data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
                <span>Jurnal Mengajar</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('kehadiran-guru.laporan') }}" class="{{ request()->routeIs('kehadiran-guru.*') ? 'active-nav' : '' }}">
                <i data-feather="clipboard-check" data-feather-line="1" data-feather-size="17" data-feather-color="emerald-dark" data-feather-bg="emerald-fade-light"></i>
                <span>Laporan Kehadiran KBM</span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endhasrole


        {{-- TATA TERTIB & LAINNYA --}}
        @hasanyrole('superadmin|admin_tatib|bk|gtk|wali_kelas')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Tata Tertib</h5>

            <a href="{{ route('admin.pelanggaran.index') }}" class="{{ request()->routeIs('admin.pelanggaran.*') ? 'active-nav' : '' }}">
                <i data-feather="alert-octagon" data-feather-line="1" data-feather-size="17" data-feather-color="red-dark" data-feather-bg="red-fade-light"></i>
                <span>Pelanggaran Siswa</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('admin.penghargaan.index') }}" class="{{ request()->routeIs('admin.penghargaan.*') ? 'active-nav' : '' }}">
                <i data-feather="award" data-feather-line="1" data-feather-size="17" data-feather-color="emerald-dark" data-feather-bg="emerald-fade-light"></i>
                <span>Penghargaan Siswa</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('admin.rekap-poin.index') }}" class="{{ request()->routeIs('admin.rekap-poin.*') ? 'active-nav' : '' }}">
                <i data-feather="bar-chart-2" data-feather-line="1" data-feather-size="17" data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
                <span>Rekap Poin Siswa</span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endhasanyrole


        {{-- MANAJEMEN DATA --}}
        @hasanyrole('superadmin|admin_tatib|bk|waka|kepala_sekolah|kepsek')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Manajemen Data</h5>

            @can('dashboard-laporan.view')
            <a href="{{ route('admin.dashboard-laporan.index') }}" class="{{ request()->routeIs('admin.dashboard-laporan.*') ? 'active-nav' : '' }}">
                <i data-feather="activity" data-feather-line="1" data-feather-size="17" data-feather-color="purple-dark" data-feather-bg="purple-fade-light"></i>
                <span>Dashboard Laporan</span>
                <i class="fa fa-angle-right"></i>
            </a>
            @endcan

            <a href="{{ route('siswa.index') }}" class="{{ request()->routeIs('siswa.*') ? 'active-nav' : '' }}">
                <i data-feather="users" data-feather-line="1" data-feather-size="17" data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
                <span>Data Siswa</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('gtk.index') }}" class="{{ request()->routeIs('gtk.*') && !request()->routeIs('kehadiran-guru.*') ? 'active-nav' : '' }}">
                <i data-feather="briefcase" data-feather-line="1" data-feather-size="17" data-feather-color="teal-dark" data-feather-bg="teal-fade-light"></i>
                <span>Data GTK</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('kelas.index') }}" class="{{ request()->routeIs('kelas.*') ? 'active-nav' : '' }}">
                <i data-feather="grid" data-feather-line="1" data-feather-size="17" data-feather-color="purple-dark" data-feather-bg="purple-fade-light"></i>
                <span>Data Kelas</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('admin.mata-pelajaran.index') }}" class="{{ request()->routeIs('admin.mata-pelajaran.*') ? 'active-nav' : '' }}">
                <i data-feather="book" data-feather-line="1" data-feather-size="17" data-feather-color="cyan-dark" data-feather-bg="cyan-fade-light"></i>
                <span>Mata Pelajaran</span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endhasanyrole


        {{-- PENJADWALAN --}}
        @hasanyrole('superadmin|admin_tatib|waka|kepala_sekolah')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Penjadwalan</h5>

            <a href="{{ route('admin.set-jam.index') }}" class="{{ request()->routeIs('admin.set-jam.*') ? 'active-nav' : '' }}">
                <i data-feather="clock" data-feather-line="1" data-feather-size="17" data-feather-color="blue-dark" data-feather-bg="blue-fade-light"></i>
                <span>Set Jam Pelajaran</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('admin.jadwal-kbm.index') }}" class="{{ request()->routeIs(['admin.jadwal-kbm.index','admin.jadwal-kbm.show','admin.jadwal-kbm.create','admin.jadwal-kbm.edit']) ? 'active-nav' : '' }}">
                <i data-feather="calendar" data-feather-line="1" data-feather-size="17" data-feather-color="orange-dark" data-feather-bg="orange-fade-light"></i>
                <span>Jadwal KBM</span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endhasanyrole


        {{-- PENGATURAN IZIN --}}
        {{-- PENGATURAN IZIN --}}
        {{-- @hasanyrole('superadmin|admin_tatib|bk|wali_kelas')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Pengajuan Izin</h5>
            @php $izinPendingCount = \App\Models\PengajuanIzin::diajukan()->count(); @endphp

            <a href="{{ route('admin.izin.index') }}" class="{{ request()->routeIs('admin.izin.*') ? 'active-nav' : '' }}">
                <i data-feather="file-text" data-feather-line="1" data-feather-size="17" data-feather-color="teal-dark" data-feather-bg="teal-fade-light"></i>
                <span>Verifikasi Izin Siswa</span>
                @if ($izinPendingCount > 0)
                    <span class="badge bg-danger text-white ms-auto" style="font-size:0.7rem; padding:3px 7px; border-radius:20px;">
                        {{ $izinPendingCount > 9 ? '9+' : $izinPendingCount }}
                    </span>
                @endif
                <i class="fa fa-angle-right"></i>
            </a>
        @endhasrole --}}


        {{-- PKL — Admin / Waka --}}
        @can('pkl.view')
            <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">PKL</h5>

            <a href="{{ route('admin.pkl.lokasi.index') }}" class="{{ request()->routeIs('admin.pkl.lokasi.*') ? 'active-nav' : '' }}">
                <i data-feather="map-pin" data-feather-line="1" data-feather-size="17" data-feather-color="yellow-dark" data-feather-bg="yellow-fade-light"></i>
                <span>Lokasi PKL</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('admin.pkl.penugasan.index') }}" class="{{ request()->routeIs('admin.pkl.penugasan.*') ? 'active-nav' : '' }}">
                <i data-feather="user-check" data-feather-line="1" data-feather-size="17" data-feather-color="teal-dark" data-feather-bg="teal-fade-light"></i>
                <span>Penugasan Siswa PKL</span>
                <i class="fa fa-angle-right"></i>
            </a>

            <a href="{{ route('admin.pkl.rekap.per-siswa') }}" class="{{ request()->routeIs('admin.pkl.rekap.*') ? 'active-nav' : '' }}">
                <i data-feather="bar-chart-2" data-feather-line="1" data-feather-size="17" data-feather-color="purple-dark" data-feather-bg="purple-fade-light"></i>
                <span>Rekap & Laporan PKL</span>
                <i class="fa fa-angle-right"></i>
            </a>
        @endcan


        {{-- AKUN --}}
        <h5 class="text-uppercase opacity-20 font-12 pl-3 mt-3">Akun</h5>

        <a href="{{ route('profile.index') }}" class="{{ request()->routeIs('profile.*') ? 'active-nav' : '' }}">
            <i data-feather="user" data-feather-line="1" data-feather-size="17" data-feather-color="slate-dark" data-feather-bg="slate-fade-light"></i>
            <span>Profil Saya</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i data-feather="log-out" data-feather-line="1" data-feather-size="17" data-feather-color="red-dark" data-feather-bg="red-fade-light"></i>
            <span>Keluar</span>
            <i class="fa fa-angle-right"></i>
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    </div>
</div>