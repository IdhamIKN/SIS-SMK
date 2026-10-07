@extends('layouts.app')

@section('title', 'Detail - ' . $gtk->nama_lengkap)

@push('styles')
    @include('components.izin-styles')
    <style>
        /* ── Profile hero ── */
        .profile-hero {
            position: relative;
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 55%, #0ea5e9 100%);
            padding: 24px 20px 64px;
            overflow: hidden;
        }

        .profile-hero::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 160px;
            height: 160px;
            background: rgba(255, 255, 255, .07);
            border-radius: 50%;
        }

        .profile-hero::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -20px;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, .05);
            border-radius: 50%;
        }

        .hero-nav {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .hero-back {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .18);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            font-size: .9rem;
            border: 1px solid rgba(255, 255, 255, .25);
            backdrop-filter: blur(6px);
        }

        .hero-actions {
            display: flex;
            gap: 8px;
        }

        .hero-action-btn {
            padding: 7px 14px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, .25);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            backdrop-filter: blur(6px);
            transition: background .18s;
        }

        .hero-action-btn:hover {
            background: rgba(255, 255, 255, .28);
        }

        /* ── Profile card (overlap hero) ── */
        .profile-card {
            position: relative;
            z-index: 3;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 6px 28px rgba(0, 0, 0, .12);
            margin: -44px 16px 0;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 16px;
            border: 3px solid #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .15);
            flex-shrink: 0;
            overflow: hidden;
            background: #ede9fe;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7c3aed;
            font-size: 1.6rem;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            flex: 1;
            min-width: 0;
        }

        .profile-name {
            font-size: 1rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .profile-nisn {
            font-size: .72rem;
            color: #64748b;
            margin: 0 0 6px;
        }

        .profile-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .ptag {
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
        }

        .ptag-purple {
            background: #ede9fe;
            color: #7c3aed;
        }

        .ptag-green {
            background: #dcfce7;
            color: #15803d;
        }

        .ptag-red {
            background: #fee2e2;
            color: #dc2626;
        }

        .ptag-blue {
            background: #dbeafe;
            color: #1d4ed8;
        }

        /* ── Detail row ── */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 9px 0;
            font-size: .84rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .dr-label {
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 7px;
            flex-shrink: 0;
            min-width: 110px;
            font-size: .78rem;
        }

        .dr-label i {
            width: 14px;
            text-align: center;
        }

        .dr-value {
            font-weight: 600;
            color: #0f172a;
            text-align: right;
            font-size: .82rem;
            word-break: break-word;
            max-width: 180px;
        }

        /* ── Permissions grid ── */
        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 8px;
        }

        .perm-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: .75rem;
            font-weight: 600;
        }

        .perm-item i {
            width: 16px;
            color: #64748b;
        }

        .perm-granted {
            background: #dcfce7;
            border-color: #86efac;
        }

        .perm-granted i {
            color: #15803d;
        }

        /* ── Wrap ── */
        .dash-wrap {
            padding: 16px 16px calc(var(--footer-h) + 80px);
        }

        /* ── Action bar ── */
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
            gap: 8px;
            z-index: 999;
            box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 12px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: all .18s;
            line-height: 1;
            white-space: nowrap;
        }

        .ab-btn:active {
            transform: scale(.97);
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            flex: 0 0 auto;
            padding: 12px 14px;
        }

        .btn-edit {
            background: #0d6efd;
            color: #fff;
            border: 1px solid #0b5ed7;
        }

        .btn-edit:hover {
            background: #0b5ed7;
        }

        .ab-btn-delete {
            background: #dc3545;
            color: #fff;
            border: 1px solid #bb2d3b;
            flex: 0 0 auto;
            padding: 12px 14px;
        }

        .ab-btn-delete:hover {
            background: #bb2d3b;
        }
        .btn-reset-pw {
            background: #f59e0b;
            color: #fff;
            border: 1px solid #d97706;
            flex: 0 0 auto;
            padding: 12px 14px;
        }
        
        .btn-reset-pw:hover {
            background: #d97706;
            color: #fff;
        }
    </style>
@endpush

@section('content')

    {{-- ── Profile Hero ── --}}
    <div class="profile-hero">
        <div class="hero-nav">
            <a href="{{ route('gtk.index') }}" class="hero-back">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="hero-actions">
                <a href="{{ route('gtk.edit', $gtk) }}" class="hero-action-btn">
                    <i class="fas fa-pen"></i> Edit
                </a>
            </div>
        </div>
    </div>

    {{-- ── Profile Card (overlap) ── --}}
    <div style="padding: 0 0 0 0;">
        <div class="profile-card">
            <div class="profile-avatar">
                @if ($gtk->foto)
                    <img src="{{ Storage::url($gtk->foto) }}" alt="{{ $gtk->nama_lengkap }}">
                @else
                    <i class="fas fa-user-tie"></i>
                @endif
            </div>
            <div class="profile-info">
                <h3 class="profile-name">{{ $gtk->nama_lengkap }}</h3>
                <p class="profile-nisn">Kode: {{ $gtk->kd_guru }}</p>
                <div class="profile-tags">
                    <span class="ptag ptag-purple">
                        <i class="fas fa-chalkboard-teacher"></i> {{ $gtk->jabatan }}
                    </span>
                    <span class="ptag ptag-blue">
                        <i class="fas fa-{{ $gtk->jenis_kelamin === 'L' ? 'mars' : 'venus' }}"></i>
                        {{ $gtk->jenis_kelamin === 'L' ? 'L' : 'P' }}
                    </span>
                    <span class="ptag {{ $gtk->status_aktif ? 'ptag-green' : 'ptag-red' }}">
                        <i class="fas fa-circle" style="font-size:.5rem;"></i>
                        {{ $gtk->status_aktif ? 'Aktif' : 'Non Aktif' }}
                    </span>
                    @if ($gtk->mataPelajaran->count() > 0)
                        <span class="ptag ptag-purple">
                            <i class="fas fa-book"></i> {{ $gtk->mataPelajaran->pluck('nama_mapel')->join(', ') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="dash-wrap">

        {{-- ── Data Identitas ── --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#ede9fe; color:#7c3aed;"><i class="fas fa-id-card"></i></div>
                <h3>Data Identitas</h3>
            </div>
            <div class="c-body" style="padding:12px 18px;">
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-hashtag"></i> Kode Guru</span>
                    <span class="dr-value">{{ $gtk->kd_guru }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-id-badge"></i> NIP</span>
                    <span class="dr-value">{{ $gtk->nip ?: '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-address-card"></i> NIK</span>
                    <span class="dr-value">{{ $gtk->nik ?: '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-graduation-cap"></i> NUPTK</span>
                    <span class="dr-value">{{ $gtk->nuptk ?: '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-user"></i> Nama</span>
                    <span class="dr-value">{{ $gtk->nama_lengkap }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-venus-mars"></i> Kelamin</span>
                    <span class="dr-value">{{ $gtk->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-circle"></i> Status</span>
                    <span class="dr-value">{{ $gtk->status_aktif ? 'Aktif' : 'Non Aktif' }}</span>
                </div>
            </div>
        </div>

        {{-- ── Kontak ── --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#dcfce7; color:#15803d;"><i class="fas fa-phone-alt"></i></div>
                <h3>Kontak</h3>
            </div>
            <div class="c-body" style="padding:12px 18px;">
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-mobile-alt"></i> No. HP</span>
                    <span class="dr-value">
                        @if ($gtk->no_hp)
                            <a href="tel:{{ $gtk->no_hp }}"
                                style="color:#7c3aed;font-weight:700;">{{ $gtk->no_hp }}</a>
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Mata Pelajaran & Jabatan ── --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#fef3c7; color:#b45309;"><i class="fas fa-book"></i></div>
                <h3>Mata Pelajaran &amp; Jabatan</h3>
            </div>
            <div class="c-body" style="padding:12px 18px;">
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-chalkboard-teacher"></i> Jabatan</span>
                    <span class="dr-value">{{ $gtk->jabatan }}</span>
                </div>
                <div class="detail-row">
                    <span class="dr-label"><i class="fas fa-book-open"></i> Mata Pelajaran</span>
                    <span class="dr-value">
                        @if ($gtk->mataPelajaran->count() > 0)
                            {{ $gtk->mataPelajaran->pluck('nama_mapel')->join(', ') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- ── Role & Hak Akses ── --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#dbeafe; color:#1d4ed8;"><i class="fas fa-shield-alt"></i></div>
                <h3>Role &amp; Hak Akses</h3>
            </div>
            <div class="c-body" style="padding:12px 18px;">
                <div class="c-body" style="padding:12px 18px;">
                    @php
                        $roles = $gtk->user?->getRoleNames() ?? collect();
                
                        $roleConfig = [
                            'superadmin' => ['icon' => 'fa-crown',           'bg' => '#fef3c7', 'color' => '#b45309'],
                            'kepsek'     => ['icon' => 'fa-user-tie',        'bg' => '#ede9fe', 'color' => '#7c3aed'],
                            'waka'       => ['icon' => 'fa-user-shield',     'bg' => '#dbeafe', 'color' => '#1d4ed8'],
                            'kurikulum'  => ['icon' => 'fa-book',            'bg' => '#fef3c7', 'color' => '#b45309'],
                            'admin_tatib'=> ['icon' => 'fa-gavel',           'bg' => '#fee2e2', 'color' => '#dc2626'],
                            'gtk'        => ['icon' => 'fa-chalkboard-teacher','bg'=> '#dcfce7', 'color' => '#15803d'],
                            'bk'         => ['icon' => 'fa-user-friends',    'bg' => '#dbeafe', 'color' => '#0369a1'],
                            'wali_kelas' => ['icon' => 'fa-users',           'bg' => '#dcfce7', 'color' => '#15803d'],
                            'siswa'      => ['icon' => 'fa-user-graduate',   'bg' => '#f1f5f9', 'color' => '#475569'],
                        ];
                    @endphp
                
                    @if ($roles->isEmpty())
                        <div style="text-align:center; padding:20px 0;">
                            <i class="fas fa-ban" style="font-size:1.5rem; color:#cbd5e1;"></i>
                            <p style="margin:8px 0 0; font-size:.8rem; color:#94a3b8;">Belum ada role yang ditetapkan.</p>
                        </div>
                    @else
                        <div style="display:flex; flex-wrap:wrap; gap:8px;">
                            @foreach ($roles as $role)
                                @php
                                    $cfg = $roleConfig[$role] ?? ['icon' => 'fa-tag', 'bg' => '#f1f5f9', 'color' => '#475569'];
                                    $isUtama = $gtk->user?->role_utama === $role;
                                @endphp
                                <div style="
                                    display:inline-flex; align-items:center; gap:7px;
                                    padding:8px 14px; border-radius:12px;
                                    background:{{ $cfg['bg'] }}; color:{{ $cfg['color'] }};
                                    font-size:.78rem; font-weight:700;
                                    border:1px solid {{ $cfg['color'] }}33;
                                    position:relative;
                                ">
                                    <i class="fas {{ $cfg['icon'] }}" style="font-size:.75rem;"></i>
                                    {{ $role }}
                                    @if ($isUtama)
                                        <!--<span style="-->
                                        <!--    position:absolute; top:-6px; right:-6px;-->
                                        <!--    background:#f59e0b; color:#fff;-->
                                        <!--    font-size:.55rem; font-weight:800;-->
                                        <!--    padding:1px 5px; border-radius:20px;-->
                                        <!--    border:1.5px solid #fff;-->
                                        <!--">UTAMA</span>-->
                                    @endif
                                </div>
                            @endforeach
                        </div>
                
                        <div style="margin-top:14px; padding-top:12px; border-top:1px solid #f1f5f9;">
                            <div class="detail-row">
                                <span class="dr-label"><i class="fas fa-envelope"></i> Akun Login</span>
                                <span class="dr-value" style="font-size:.75rem; color:#64748b;">
                                    {{ $gtk->user?->email ?? '-' }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
                <div style="margin-top:16px; padding-top:12px; border-top:1px solid #e2e8f0;">
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-eye"></i> Akses Siswa</span>
                        <span class="dr-value">{{ $gtk->view_siswa === 'full' ? 'Lengkap' : 'Terbatas' }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Action Bar ── --}}
    {{-- ── Action Bar ── --}}
    <div class="action-bar">
        <a href="{{ route('gtk.index') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <a href="{{ route('gtk.edit', $gtk) }}" class="ab-btn btn-edit">
            <i class="fas fa-pen"></i> Edit
        </a>
        <button type="button" class="ab-btn btn-reset-pw" onclick="confirmResetPassword()">
            <i class="fas fa-key"></i>
        </button>
        <button type="button" class="ab-btn ab-btn-delete" onclick="confirmDelete('{{ route('gtk.destroy', $gtk) }}')">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>
    
    <form id="deleteForm" method="POST" action="{{ route('gtk.destroy', $gtk) }}" style="display:none;">
        @csrf @method('DELETE')
    </form>
    <form id="resetPwForm" method="POST" action="{{ route('gtk.reset-password', $gtk) }}" style="display:none;">
        @csrf
    </form>
@endsection

@push('scripts')
    <script>
        function confirmDelete(url) {
            if (typeof Swal === 'undefined') {
                if (!confirm('Yakin menghapus GTK {{ $gtk->nama_lengkap }}? Data tidak dapat dikembalikan.')) return;
                document.getElementById('deleteForm').submit();
                return;
            }
            Swal.fire({
                title: 'Hapus GTK?',
                html: `Yakin menghapus <strong>{{ $gtk->nama_lengkap }}</strong>?<br>
               <small style="color:#64748b;">Data GTK akan terhapus permanen.</small>`,
                icon: 'warning',
                showCancelButton: true,
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'ab-btn ab-btn-delete',
                    cancelButton: 'ab-btn ab-btn-back',
                },
                confirmButtonText: '<i class="fas fa-trash-alt"></i> Hapus',
                cancelButtonText: '<i class="fas fa-times"></i> Batal',
            }).then(result => {
                if (result.isConfirmed) document.getElementById('deleteForm').submit();
            });
        }
        function confirmResetPassword() {
        if (typeof Swal === 'undefined') {
            if (!confirm('Reset password {{ $gtk->nama_lengkap }} ke 12345678?')) return;
            document.getElementById('resetPwForm').submit();
            return;
        }
        Swal.fire({
            title: 'Reset Password?',
            html: `Password <strong>{{ $gtk->nama_lengkap }}</strong> akan direset ke <code>12345678</code>.<br>
                   <small style="color:#64748b;">Guru harus ganti password setelah login.</small>`,
            icon: 'warning',
            showCancelButton: true,
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'ab-btn btn-reset-pw',
                cancelButton: 'ab-btn ab-btn-back',
            },
            confirmButtonText: '<i class="fas fa-key"></i> Reset',
            cancelButtonText: '<i class="fas fa-times"></i> Batal',
        }).then(result => {
            if (result.isConfirmed) document.getElementById('resetPwForm').submit();
        });
    }
    </script>
@endpush
