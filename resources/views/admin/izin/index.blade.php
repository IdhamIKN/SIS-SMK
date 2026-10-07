@extends('layouts.app')

@section('title', 'Verifikasi Pengajuan Izin')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Page Strip ── */
        .izin-admin-wrap {
            padding-top: var(--header-h, 56px);
            padding-bottom: calc(var(--footer-h, 60px) + 24px);
        }

        /* ── Stat Cards ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--border, #e2e8f0);
            border-radius: 12px;
            padding: 14px 10px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: .68rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        /* ── Filter ── */
        .filter-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            align-items: end;
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-main, #0f172a);
            margin-bottom: 5px;
        }

        .form-input {
            width: 100%;
            padding: 8px 11px;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #0ea5e9;
        }

        /* ── Table ── */
        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .78rem;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .04);
        }

        .rekap-table thead tr {
            background: #f8fafc;
            border-bottom: 2px solid var(--border, #e2e8f0);
        }

        .rekap-table th {
            padding: 12px 8px;
            text-align: left;
            font-size: .7rem;
            font-weight: 700;
            color: var(--text-muted, #64748b);
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .rekap-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .rekap-table tbody tr:last-child td {
            border-bottom: none;
        }

        .rekap-table tbody tr:hover td {
            background: #fafbfc;
        }

        /* ── Custom Modal ── */
        .izin-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .6);
            z-index: 9998;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .izin-overlay.open {
            display: flex;
        }

        .izin-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
            overflow: hidden;
        }

        .izin-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid #eef2f7;
            flex-shrink: 0;
        }

        .izin-modal-head h4 {
            font-size: .95rem;
            font-weight: 800;
            margin: 0;
            color: #0f172a;
        }

        .izin-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 1.1rem;
            padding: 4px 8px;
            border-radius: 6px;
            line-height: 1;
        }

        .izin-modal-close:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .izin-modal-body {
            padding: 16px 18px;
            overflow-y: auto;
            flex: 1;
        }

        .izin-modal-foot {
            padding: 12px 18px;
            border-top: 1px solid #eef2f7;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            flex-shrink: 0;
        }

        .siswa-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--event-primary, #0ea5e9);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h, 60px) + 88px);">

        {{-- Page Strip --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge">
                <span class="live-dot"></span>
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <h2><i class="fas fa-file-check"></i> Verifikasi Izin Siswa</h2>
            <p>Setujui atau tolak pengajuan izin &bull; <strong>{{ $totalPending }}</strong> menunggu</p>
        </div>

        {{-- Alert --}}
        @if (session('success'))
            <div class="alert alert-success"
                style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif


        {{-- Stat Cards --}}
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-value" style="color:#f59e0b;">{{ $totalPending }}</div>
                <div class="stat-label">Menunggu</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#16a34a;">{{ \App\Models\PengajuanIzin::disetujui()->count() }}</div>
                <div class="stat-label">Disetujui</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#dc2626;">
                    {{ \App\Models\PengajuanIzin::where('status', 'ditolak')->count() }}</div>
                <div class="stat-label">Ditolak</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:#7c3aed;">{{ $izin->total() }}</div>
                <div class="stat-label">Filter ini</div>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="filter-section">
            <div class="filter-grid">
                <div>
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-input">
                        <option value="">Semua Kelas</option>
                        @foreach ($kelasList as $k)
                            <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Jenis Izin</label>
                    <select name="jenis" class="form-input">
                        <option value="">Semua Jenis</option>
                        <option value="izin_sakit" {{ request('jenis') == 'izin_sakit' ? 'selected' : '' }}>Izin Sakit
                        </option>
                        <option value="izin_terlambat" {{ request('jenis') == 'izin_terlambat' ? 'selected' : '' }}>Izin
                            Terlambat</option>
                        <option value="izin_pulang_cepat"{{ request('jenis') == 'izin_pulang_cepat' ? 'selected' : '' }}>Izin
                            Pulang Cepat</option>
                        <option value="izin_lainnya" {{ request('jenis') == 'izin_lainnya' ? 'selected' : '' }}>Izin
                            Lainnya</option>
                        <option value="pkl" {{ request('jenis') == 'pkl' ? 'selected' : '' }}>PKL</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="diajukan" {{ request('status', 'diajukan') == 'diajukan' ? 'selected' : '' }}>Menunggu
                        </option>
                        <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="" {{ request('status') === '' && request()->has('status') ? 'selected' : '' }}>
                            Semua</option>
                    </select>
                </div>
                <div style="display:flex;gap:8px;align-items:flex-end;">
                    <button type="submit" class="action-btn btn-view" style="white-space:nowrap;">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    @if (request()->hasAny(['kelas_id', 'jenis', 'status']))
                        <a href="{{ route('admin.izin.index') }}" class="action-btn btn-view"
                            style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;text-decoration:none;white-space:nowrap;">
                            <i class="fas fa-times"></i> Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#ccfbf1;color:#0f766e;"><i class="fas fa-file-check"></i></div>
                <h3>Daftar Pengajuan Izin</h3>
                <span class="hbadge">{{ $izin->total() }} data</span>
            </div>
            <div style="overflow-x:auto;">
                <table class="rekap-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($izin as $i => $item)
                            <tr>
                                <td style="color:#94a3b8;font-size:.72rem;">{{ $izin->firstItem() + $i }}</td>
                                <td>
                                    <div style="font-weight:700;font-size:.82rem;">{{ $item->siswa?->nama_lengkap ?? '-' }}
                                    </div>
                                    <div style="font-size:.68rem;color:#64748b;">{{ $item->siswa?->nis ?? '' }}</div>
                                </td>
                                <td style="font-size:.78rem;white-space:nowrap;">
                                    {{ $item->siswa?->kelas?->nama_kelas ?? '-' }}</td>
                                <td>
                                    <span class="badge-status" style="background:#e0f2fe;color:#0369a1;font-size:.65rem;">
                                        {{ $item->jenis_label }}
                                    </span>
                                </td>
                                <td style="font-size:.75rem;white-space:nowrap;">
                                    @if ($item->tanggal_mulai)
                                        {{ $item->tanggal_mulai->format('d/m/Y') }}
                                        @if ($item->isRangeJenis() && $item->tanggal_sampai && $item->tanggal_sampai != $item->tanggal_mulai)
                                            <br><span style="color:#64748b;">s/d
                                                {{ $item->tanggal_sampai->format('d/m/Y') }}</span>
                                        @endif
                                    @else
                                        <span style="color:#94a3b8;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColor = match ($item->status) {
                                            'disetujui' => 'background:#dcfce7;color:#15803d;',
                                            'ditolak' => 'background:#fee2e2;color:#dc2626;',
                                            default => 'background:#fef3c7;color:#b45309;',
                                        };
                                    @endphp
                                    <span class="badge-status" style="{{ $statusColor }}font-size:.65rem;">
                                        {{ $item->status_label }}
                                    </span>
                                </td>
                                <td style="text-align:center;">
                                    @if ($item->bukti)
                                        <button type="button" class="action-btn btn-edit"
                                            style="font-size:.7rem;padding:5px 10px;"
                                            onclick="lihatBukti('{{ Storage::url($item->bukti) }}', '{{ addslashes($item->siswa?->nama_lengkap ?? '') }}', '{{ addslashes($item->jenis_label) }}')">
                                            <i class="fas fa-image"></i> Lihat
                                        </button>
                                    @else
                                        <span style="color:#cbd5e1;font-size:.7rem;">—</span>
                                    @endif
                                </td>
                                <td style="white-space:nowrap;">
                                    @if ($item->status === 'diajukan')
                                        <button type="button" class="action-btn btn-view"
                                            style="font-size:.7rem;padding:5px 10px;"
                                            onclick="bukaModalDetail({{ $item->id }})">
                                            <i class="fas fa-check-times"></i> Verifikasi
                                        </button>
                                    @else
                                        <button type="button" class="action-btn btn-view"
                                            style="font-size:.7rem;padding:5px 10px;background:#f1f5f9;color:#475569;"
                                            onclick="bukaModalDetail({{ $item->id }})">
                                            <i class="fas fa-eye"></i> Detail
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center;padding:40px 20px;color:#94a3b8;">
                                    <div style="font-size:2.5rem;margin-bottom:8px;opacity:.3;"><i class="fas fa-inbox"></i>
                                    </div>
                                    <strong style="color:#475569;display:block;margin-bottom:4px;">Tidak ada pengajuan
                                        izin</strong>
                                    Semua izin sudah diproses atau belum ada pengajuan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($izin->hasPages())
                <div style="padding:14px 16px;display:flex;justify-content:center;">
                    {{ $izin->links() }}
                </div>
            @endif
        </div>

        {{-- Action Bar --}}
        <div class="action-bar">
            <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>

    </div>

    {{-- ═══════════════════════════════════════════
         MODAL: Detail & Verifikasi Izin
    ═══════════════════════════════════════════ --}}
    <div class="izin-overlay" id="overlayIzin">
        <form id="izinForm" method="POST" action="" class="izin-modal">
            <div class="izin-modal-head">
                <h4 id="izinModalTitle"><i class="fas fa-file-check" style="color:#0f766e;margin-right:6px;"></i> Detail
                    Izin</h4>
                <button type="button" class="izin-modal-close" onclick="tutupModalIzin()" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @csrf
            @method('PATCH')
            <div class="izin-modal-body">
                <div id="izinLoading" style="text-align:center;padding:30px;color:#94a3b8;">
                    <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                    Memuat data...
                </div>
                <div id="izinDetail" style="display:none;">
                    <div
                        style="display:flex;align-items:center;gap:12px;margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
                        <div class="siswa-avatar" id="modalSiswaAvatar">-</div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:.95rem;color:#0f172a;" id="modalSiswaNama">-</div>
                            <div style="font-size:.78rem;color:#64748b;" id="modalSiswaKelas">-</div>
                        </div>
                        <span class="badge-status" id="modalStatusBadge" style="font-size:.65rem;">-</span>
                    </div>

                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-tag" style="width:14px;"></i> Jenis Izin</span>
                        <span class="dr-value" id="modalJenis">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-calendar" style="width:14px;"></i> Tanggal</span>
                        <span class="dr-value" id="modalTanggal">-</span>
                    </div>
                    <div class="detail-row">
                        <span class="dr-label"><i class="fas fa-align-left" style="width:14px;"></i> Alasan</span>
                        <span class="dr-value" id="modalAlasan"
                            style="text-align:right;max-width:60%;white-space:pre-wrap;">-</span>
                    </div>
                    <div id="modalBuktiWrap" style="display:none;margin-top:10px;">
                        <div style="font-size:.78rem;font-weight:600;color:#64748b;margin-bottom:6px;">
                            <i class="fas fa-image"></i> Bukti
                        </div>
                        <img id="modalBuktiImg" src=""
                            style="max-width:100%;max-height:260px;border-radius:10px;border:1px solid #e2e8f0;display:block;"
                            alt="Bukti Izin">
                    </div>
                    <div id="modalVerifierWrap"
                        style="display:none;margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9;">
                        <div style="font-size:.78rem;font-weight:600;color:#64748b;margin-bottom:4px;">
                            <i class="fas fa-user-check"></i> Diverifikasi oleh
                        </div>
                        <div style="font-size:.85rem;font-weight:600;color:#0f172a;" id="modalVerifier">-</div>
                        <div style="font-size:.75rem;color:#64748b;" id="modalWaktuVerifikasi">-</div>
                    </div>
                </div>

                <div id="izinActions" style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;">
                    <div class="form-label" style="margin-bottom:8px;">Tindakan Verifikasi</div>
                    <div style="display:flex;gap:8px;margin-bottom:12px;">
                        <label
                            style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:8px;border:1.5px solid #16a34a;background:#f0fdf4;color:#15803d;cursor:pointer;font-weight:700;font-size:.85rem;">
                            <input type="radio" name="status" value="disetujui" required
                                onchange="updateCatatanHint()"> Setuju
                        </label>
                        <label
                            style="flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px;border-radius:8px;border:1.5px solid #dc2626;background:#fef2f2;color:#dc2626;cursor:pointer;font-weight:700;font-size:.85rem;">
                            <input type="radio" name="status" value="ditolak" required
                                onchange="updateCatatanHint()"> Tolak
                        </label>
                    </div>
                    <div id="catatanHint" style="font-size:.7rem;color:#94a3b8;margin-bottom:6px;display:none;"></div>
                    <div class="form-group">
                        <label class="form-label">Catatan (opsional)</label>
                        <textarea name="catatan" class="form-input" rows="2" placeholder="Berikan catatan verifikasi..."></textarea>
                    </div>
                </div>
            </div>
            <div class="izin-modal-foot">
                <button type="button" class="ab-btn ab-btn-back" onclick="tutupModalIzin()">Batal</button>
                <button type="submit" class="ab-btn ab-btn-primary" id="izinSubmitBtn" style="display:none;">
                    <i class="fas fa-save"></i> Simpan Verifikasi
                </button>
            </div>
        </form>
    </div>

    {{-- Modal Lihat Bukti --}}
    <div class="izin-overlay" id="overlayBukti">
        <div class="izin-modal" style="max-width:720px;">
            <div class="izin-modal-head">
                <h4><i class="fas fa-image" style="color:#0f766e;margin-right:6px;"></i> Bukti Izin</h4>
                <button class="izin-modal-close" onclick="tutupModalBukti()" aria-label="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="izin-modal-body" style="text-align:center;">
                <img id="buktiModalImg" src=""
                    style="max-width:100%;max-height:70vh;border-radius:10px;border:1px solid #e2e8f0;display:block;margin:0 auto;"
                    alt="Bukti Izin">
                <div id="buktiModalCaption" style="margin-top:10px;font-size:.85rem;color:#64748b;"></div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <?php
    $izinData = $izin
        ->getCollection()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->siswa?->nama_lengkap ?? '-',
                'nis' => $item->siswa?->nis ?? '',
                'kelas' => $item->siswa?->kelas?->nama_kelas ?? '-',
                'jenis' => $item->jenis_label,
                'tanggal' => $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') . ($item->isRangeJenis() && $item->tanggal_sampai && $item->tanggal_sampai != $item->tanggal_mulai ? ' – ' . $item->tanggal_sampai->format('d/m/Y') : '') : '-',
                'alasan' => $item->alasan,
                'bukti' => $item->bukti ? Storage::url($item->bukti) : null,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'verifier' => $item->verifier?->name ?? null,
                'waktu_verifikasi' => $item->waktu_verifikasi ? $item->waktu_verifikasi->format('d M Y, H:i') : null,
                'avatar_letter' => $item->siswa?->nama_lengkap ? substr($item->siswa->nama_lengkap, 0, 1) : '-',
            ];
        })
        ->keyBy('id');
    ?>
    <script>
        const IZIN_DATA = {!! json_encode($izinData) !!};

        document.addEventListener('DOMContentLoaded', function() {
            var hdr = document.querySelector('.header-auto-show');
            if (hdr) hdr.classList.add('header-active');

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#16a34a',
                    timer: 3000,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            @endif

            window.tutupModalIzin = function() {
                document.getElementById('overlayIzin').classList.remove('open');
                document.body.style.overflow = '';
            };

            window.tutupModalBukti = function() {
                document.getElementById('overlayBukti').classList.remove('open');
                document.body.style.overflow = '';
            };

            function bukaOverlay(id) {
                document.getElementById(id).classList.add('open');
                document.body.style.overflow = 'hidden';
            }

            document.getElementById('overlayIzin').addEventListener('click', function(e) {
                if (e.target === this) window.tutupModalIzin();
            });
            document.getElementById('overlayBukti').addEventListener('click', function(e) {
                if (e.target === this) window.tutupModalBukti();
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (document.getElementById('overlayIzin').classList.contains('open')) window
                        .tutupModalIzin();
                    if (document.getElementById('overlayBukti').classList.contains('open')) window
                        .tutupModalBukti();
                }
            });

            window.bukaModalDetail = function(id) {
                var data = IZIN_DATA[id];
                if (!data) return;

                var isDiajukan = data.status === 'diajukan';
                document.getElementById('izinModalTitle').innerHTML = isDiajukan ?
                    '<i class="fas fa-check-times" style="color:#0f766e;margin-right:6px;"></i> Verifikasi Izin' :
                    '<i class="fas fa-eye" style="color:#64748b;margin-right:6px;"></i> Detail Izin';

                document.getElementById('modalSiswaAvatar').textContent = data.avatar_letter;
                document.getElementById('modalSiswaNama').textContent = data.nama;
                document.getElementById('modalSiswaKelas').textContent = data.kelas + (data.nis ? ' • ' + data
                    .nis : '');

                var statusColor = data.status === 'disetujui' ? 'background:#dcfce7;color:#15803d;' : (data
                    .status === 'ditolak' ? 'background:#fee2e2;color:#dc2626;' :
                    'background:#fef3c7;color:#b45309;');
                document.getElementById('modalStatusBadge').style.cssText = statusColor + 'font-size:.65rem;';
                document.getElementById('modalStatusBadge').textContent = data.status_label;

                document.getElementById('modalJenis').textContent = data.jenis;
                document.getElementById('modalTanggal').textContent = data.tanggal;
                document.getElementById('modalAlasan').textContent = data.alasan;

                var buktiWrap = document.getElementById('modalBuktiWrap');
                var buktiImg = document.getElementById('modalBuktiImg');
                if (data.bukti) {
                    buktiWrap.style.display = 'block';
                    buktiImg.src = data.bukti;
                } else {
                    buktiWrap.style.display = 'none';
                    buktiImg.src = '';
                }

                var verifierWrap = document.getElementById('modalVerifierWrap');
                if (data.verifier) {
                    verifierWrap.style.display = 'block';
                    document.getElementById('modalVerifier').textContent = data.verifier;
                    document.getElementById('modalWaktuVerifikasi').textContent = data.waktu_verifikasi || '';
                } else {
                    verifierWrap.style.display = 'none';
                }

                var actions = document.getElementById('izinActions');
                var submitBtn = document.getElementById('izinSubmitBtn');
                if (isDiajukan) {
                    actions.style.display = 'block';
                    submitBtn.style.display = 'inline-flex';
                    document.getElementById('izinForm').action =
                        '{{ route('admin.izin.update', ['izin' => '__ID__']) }}'.replace('__ID__', data.id);
                } else {
                    actions.style.display = 'none';
                    submitBtn.style.display = 'none';
                    document.getElementById('izinForm').action = '';
                }

                document.getElementById('izinForm').reset();
                document.getElementById('izinDetail').style.display = 'block';
                document.getElementById('izinLoading').style.display = 'none';
                bukaOverlay('overlayIzin');
            };

            window.lihatBukti = function(url, nama, jenis) {
                document.getElementById('buktiModalImg').src = url;
                document.getElementById('buktiModalCaption').textContent = nama + ' — ' + jenis;
                bukaOverlay('overlayBukti');
            };

            window.updateCatatanHint = function() {
                var hint = document.getElementById('catatanHint');
                var selected = document.querySelector('input[name="status"]:checked');
                if (selected && selected.value === 'ditolak') {
                    hint.style.display = 'block';
                    hint.textContent = 'Catatan akan dikirim ke siswa & orang tua via WhatsApp.';
                    hint.style.color = '#dc2626';
                } else {
                    hint.style.display = 'none';
                    hint.textContent = '';
                }
            };

            document.getElementById('izinForm').addEventListener('submit', function(e) {
                var btn = document.getElementById('izinSubmitBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            });
        });
    </script>
@endpush
