@extends('layouts.app')

@section('title', 'Detail Jadwal KBM')

@push('styles')
    <style>
        /* ─────────────────────────────────────────────────────
           SEMUA CSS di-scope ke .jks — tidak ada selector
           tanpa prefix agar tidak menutup sidebar / header
        ──────────────────────────────────────────────────── */
        .jks {
            font-family: inherit;
        }

        /* Strip */
        .jks .jks-strip {
            padding: 20px 20px 28px;
            background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #10b981 100%);
            position: relative;
            overflow: hidden;
        }

        .jks .jks-strip::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 140px;
            height: 140px;
            background: rgba(255, 255, 255, .06);
            border-radius: 50%;
        }

        .jks .jks-strip::after {
            content: '';
            position: absolute;
            bottom: -24px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, .04);
            border-radius: 50%;
        }

        .jks .jks-strip h2 {
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

        .jks .jks-strip p {
            font-size: .8rem;
            color: rgba(255, 255, 255, .65);
            margin: 0;
            position: relative;
            z-index: 1;
        }

        /* Cards */
        .jks .jks-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            margin: 12px 16px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
            overflow: hidden;
        }

        .jks .jks-chead {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px 11px;
            border-bottom: 1px solid #f8fafc;
        }

        .jks .jks-cico {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }

        .jks .jks-chead h3 {
            margin: 0;
            font-size: .9rem;
            font-weight: 700;
        }

        .jks .jks-cbody {
            padding: 16px;
        }

        /* Detail rows */
        .jks .jks-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .jks .jks-row:last-child {
            border-bottom: none;
        }

        .jks .jks-ico {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .jks .jks-content {
            flex: 1;
        }

        .jks .jks-lbl {
            font-size: .75rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .025em;
            margin-bottom: 2px;
        }

        .jks .jks-val {
            font-size: .9rem;
            color: #0f172a;
            font-weight: 600;
            line-height: 1.4;
        }

        /* Action bar — z-index 100 agar tidak menutup sidebar */
        .jks .jks-bar {
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

        .jks .jks-ab {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 14px;
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

        .jks .jks-ab:active {
            transform: scale(.97);
        }

        .jks .jks-ab.back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .jks .jks-ab.back:hover {
            background: #e2e8f0;
        }

        .jks .jks-ab.edit {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff;
            box-shadow: 0 3px 12px rgba(59, 130, 246, .3);
        }

        .jks .jks-ab.edit:hover {
            filter: brightness(1.08);
        }
    </style>
@endpush

@section('content')
    <div class="jks" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        {{-- Strip --}}
        <div class="jks-strip">
            <h2><i class="fas fa-calendar-check"></i> Detail Jadwal KBM</h2>
            <p>Lihat informasi lengkap jadwal kegiatan belajar mengajar</p>
        </div>

        {{-- ① Info Utama --}}
        <div class="jks-card">
            <div class="jks-chead">
                <div class="jks-cico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-info-circle"></i></div>
                <h3>Informasi Utama</h3>
            </div>
            <div class="jks-cbody">
                <div class="jks-row">
                    <div class="jks-ico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-hashtag"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">ID Jadwal</div>
                        <div class="jks-val">#{{ $jadwalKBM->id }}</div>
                    </div>
                </div>

                <div class="jks-row">
                    <div class="jks-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="jks-content">
                        <div class="jks-lbl">Kelas</div>
                        <div class="jks-val">
                            {{ $jadwalKBM->kelas->nama_kelas }}{{ $jadwalKBM->kelas->jurusan ? ' — ' . $jadwalKBM->kelas->jurusan->nama_jurusan : '' }}
                        </div>
                    </div>
                </div>

                <div class="jks-row">
                    <div class="jks-ico" style="background:#fef3c7;color:#b45309;"><i class="fas fa-user-tie"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Guru Pengajar</div>
                        <div class="jks-val">
                            {{ $jadwalKBM->gtk->nama_lengkap }}{{ $jadwalKBM->gtk->kd_guru ? ' (' . $jadwalKBM->gtk->kd_guru . ')' : '' }}
                        </div>
                    </div>
                </div>

                <div class="jks-row">
                    <div class="jks-ico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-book"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Mata Pelajaran</div>
                        <div class="jks-val">
                            {{ $jadwalKBM->mataPelajaran ? $jadwalKBM->mataPelajaran->nama_mapel . ' (' . $jadwalKBM->mataPelajaran->kode_mapel . ')' : $jadwalKBM->mata_pelajaran }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ② Waktu & Jadwal --}}
        <div class="jks-card">
            <div class="jks-chead">
                <div class="jks-cico" style="background:#fef3c7;color:#b45309;"><i class="fas fa-clock"></i></div>
                <h3>Waktu &amp; Jadwal</h3>
            </div>
            <div class="jks-cbody">
                <div class="jks-row">
                    <div class="jks-ico" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-calendar-day"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Hari</div>
                        <div class="jks-val">{{ $jadwalKBM->hari }}</div>
                    </div>
                </div>

                <div class="jks-row">
                    <div class="jks-ico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-hashtag"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Jam Ke</div>
                        <div class="jks-val">{{ $jadwalKBM->jam_ke }}</div>
                    </div>
                </div>

                <div class="jks-row">
                    <div class="jks-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-play"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Jam Mulai</div>
                        <div class="jks-val">{{ $jadwalKBM->jam_mulai ? $jadwalKBM->jam_mulai->format('H:i') : '-' }}</div>
                    </div>
                </div>

                <div class="jks-row">
                    <div class="jks-ico" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-stop"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Jam Selesai</div>
                        <div class="jks-val">{{ $jadwalKBM->jam_selesai ? $jadwalKBM->jam_selesai->format('H:i') : '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ③ Periode Akademik --}}
        <div class="jks-card">
            <div class="jks-chead">
                <div class="jks-cico" style="background:#ede9fe;color:#7c3aed;"><i class="fas fa-calendar-alt"></i></div>
                <h3>Periode Akademik</h3>
            </div>
            <div class="jks-cbody">
                <div class="jks-row">
                    <div class="jks-ico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-school"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Tahun Ajaran</div>
                        <div class="jks-val">{{ $jadwalKBM->tahun_ajaran ?: 'Tidak ditentukan' }}</div>
                    </div>
                </div>

                <div class="jks-row">
                    <div class="jks-ico" style="background:#dcfce7;color:#15803d;"><i class="fas fa-list-ol"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Semester</div>
                        <div class="jks-val">Semester {{ $jadwalKBM->semester }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ④ Metadata --}}
        <div class="jks-card">
            <div class="jks-chead">
                <div class="jks-cico" style="background:#f3f4f6;color:#374151;"><i class="fas fa-database"></i></div>
                <h3>Metadata</h3>
            </div>
            <div class="jks-cbody">
                <div class="jks-row">
                    <div class="jks-ico" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="jks-content">
                        <div class="jks-lbl">Dibuat</div>
                        <div class="jks-val">
                            {{ $jadwalKBM->created_at ? $jadwalKBM->created_at->format('d M Y, H:i') : '-' }}</div>
                    </div>
                </div>

                <div class="jks-row">
                    <div class="jks-ico" style="background:#fef3c7;color:#b45309;"><i class="fas fa-edit"></i></div>
                    <div class="jks-content">
                        <div class="jks-lbl">Terakhir Diubah</div>
                        <div class="jks-val">
                            {{ $jadwalKBM->updated_at ? $jadwalKBM->updated_at->format('d M Y, H:i') : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Bar --}}
        <div class="jks-bar">
            <a href="{{ route('admin.jadwal-kbm.index') }}" class="jks-ab back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('admin.jadwal-kbm.edit', $jadwalKBM) }}" class="jks-ab edit">
                <i class="fas fa-edit"></i> Edit Jadwal
            </a>
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
