@extends('layouts.app')
@section('title', 'Edit Absensi Manual')

@push('styles')
    @include('components.event-styles')
    <style>
        /* ── Layout ── */
        .form-wrap {
            padding-top: var(--header-h, 56px);
            padding-bottom: 148px;
            /* BUG FIX: was `148px;">; ` — stray "> and semicolon */
            max-width: 640px;
            margin: 0 auto;
        }

        .form-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 14px;
        }

        .form-card h3 {
            font-size: .9rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ── Form controls ── */
        .form-row {
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            font-size: .8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .form-label .req {
            color: #ef4444;
            margin-left: 2px;
        }

        .form-input {
            width: 100%;
            padding: 9px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: .875rem;
            font-family: inherit;
            background: #f8fafc;
            color: #0f172a;
            outline: none;
            box-sizing: border-box;
        }

        .form-input:focus {
            border-color: #0ea5e9;
            background: #fff;
        }

        .form-input.is-error {
            border-color: #ef4444;
        }

        .error-msg {
            color: #dc2626;
            font-size: .72rem;
            margin-top: 4px;
        }

        .hint {
            font-size: .7rem;
            color: #64748b;
            margin-top: 4px;
        }

        /* ── Info / notice boxes ── */
        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: .82rem;
        }

        /* BUG FIX: was `display:none` but toggled to flex by JS — keep initial display:none,
           JS uses `.style.display = 'flex'` to show it */
        .alfa-warning {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-radius: 10px;
            padding: 11px 14px;
            margin-bottom: 14px;
            font-size: .8rem;
            color: #be123c;
            display: none;
            /* hidden until JS confirms ada_poin === true */
            align-items: flex-start;
            gap: 8px;
        }

        .izin-notice {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 14px;
            font-size: .8rem;
            color: #166534;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        /* ── Izin form (conditional) ── */
        .izin-form-wrap {
            display: none;
        }

        .izin-form-wrap.show {
            display: block;
        }

        /* ── Jenis izin grid ── */
        .jenis-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 4px;
        }

        .jenis-option {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all .15s;
            font-size: .82rem;
        }

        .jenis-option:has(input:checked) {
            border-color: #0ea5e9;
            background: #eff6ff;
        }

        /* ── Fixed bottom action bar ── */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 0);
            left: 0;
            right: 0;
            padding: 10px 16px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 100;
            /* lower than modal overlay (z-index: 9998) */
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
            line-height: 1;
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .ab-btn-orange {
            background: #f59e0b;
            color: #fff;
        }

        /* ══════════════════════════════════════════════
           MODAL — Konfirmasi Hapus Poin Alfa
           BUG FIX: overlay must be z-index above action-bar (100) and page content.
           Use z-index: 9998 for overlay, 9999 for the modal card itself.
           ══════════════════════════════════════════════ */
        .poin-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .65);
            z-index: 9998;
            align-items: center;
            justify-content: center;
            padding: 20px;
            /* BUG FIX: when open, use display:flex (set via .open class below) */
        }

        .poin-overlay.open {
            display: flex;
        }

        .poin-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
            position: relative;
            /* ensure stacking above overlay backdrop */
            z-index: 9999;
        }

        .poin-modal-head {
            padding: 16px 18px;
            border-bottom: 1px solid #fee2e2;
            background: #fff1f2;
        }

        .poin-modal-head h4 {
            margin: 0;
            font-size: .95rem;
            font-weight: 800;
            color: #be123c;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .poin-modal-body {
            padding: 16px 18px;
            font-size: .85rem;
            color: #0f172a;
            line-height: 1.6;
        }

        .poin-modal-body p {
            margin: 0 0 8px;
        }

        .poin-modal-body p:last-child {
            margin-bottom: 0;
        }

        .poin-modal-body .detail {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            padding: 10px 13px;
            margin-top: 10px;
            font-size: .78rem;
        }

        .poin-modal-foot {
            padding: 12px 18px;
            border-top: 1px solid #eef2f7;
            display: flex;
            gap: 8px;
        }

        .poin-modal-foot button {
            flex: 1;
            padding: 11px;
            border-radius: 10px;
            font-size: .83rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            font-family: inherit;
            transition: opacity .15s;
        }

        .poin-modal-foot button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        .btn-hapus-poin {
            background: #dc2626;
            color: #fff;
        }

        .btn-simpan-saja {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap form-wrap">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>Admin</div>
            <h2><i class="fas fa-pen"></i> Edit Absensi Manual</h2>
            <p>Ubah data + recalculate seluruh rule absensi secara otomatis</p>
        </div>

        {{-- Info Siswa --}}
        <div class="info-box">
            <div style="display:flex;align-items:center;gap:10px;">
                <div
                    style="width:38px;height:38px;border-radius:50%;background:#0ea5e9;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;flex-shrink:0;">
                    {{ strtoupper(substr($absen->siswa->nama_lengkap ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:700;font-size:.9rem;">{{ $absen->siswa->nama_lengkap }}</div>
                    <div style="font-size:.75rem;color:#64748b;">
                        {{ $absen->siswa->nis }} &bull; {{ $absen->siswa->kelas->nama_kelas ?? '-' }} &bull;
                        {{ $absen->tanggal->translatedFormat('l, d F Y') }}
                    </div>
                </div>
            </div>
            <div style="margin-top:10px;padding-top:10px;border-top:1px solid #bae6fd;font-size:.78rem;">
                <strong>Status saat ini:</strong>
                Masuk <strong
                    style="color:#16a34a;">{{ $absen->jam_masuk ? substr($absen->jam_masuk, 0, 5) : '—' }}</strong>
                ({{ ucfirst($absen->status_masuk ?? ($absen->status ?? 'alfa')) }})
                &nbsp;|&nbsp;
                Pulang <strong
                    style="color:#0369a1;">{{ $absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : '—' }}</strong>
                @if ($absen->catatan)
                    &nbsp;|&nbsp;<em style="color:#64748b;">{{ $absen->catatan }}</em>
                @endif
            </div>
        </div>

        {{-- Info izin yang sudah ada --}}
        @if ($izinAktif)
            <div class="izin-notice">
                <i class="fas fa-file-medical" style="flex-shrink:0;margin-top:1px;"></i>
                <span>Sudah ada <strong>{{ $izinAktif->jenis_label }}</strong> aktif untuk siswa ini
                    ({{ $izinAktif->tanggal_mulai->format('d/m/Y') }} – {{ $izinAktif->tanggal_sampai->format('d/m/Y') }},
                    status: <strong>{{ $izinAktif->status_label }}</strong>).
                    Jika Anda memilih status Izin/Sakit, data izin ini akan diperbarui.</span>
            </div>
        @endif

        {{-- Peringatan poin alfa — tersembunyi; JS tampilkan jika ada_poin === true --}}
        <div class="alfa-warning" id="alfaWarning">
            <i class="fas fa-exclamation-triangle" style="margin-top:1px;flex-shrink:0;"></i>
            <span>Siswa ini memiliki <strong>poin pelanggaran Alfa otomatis</strong> pada tanggal ini.
                Saat Anda mengubah status dari Alfa, sistem akan menanyakan apakah poin pelanggaran tersebut juga ingin
                dihapus.</span>
        </div>

        <form method="POST" action="{{ route('admin.absen-manual.update', $absen) }}" id="editForm"
            enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="hapus_poin_alfa" id="hapusPoinAlfaFlag" value="0">

            {{-- Absen Masuk --}}
            <div class="form-card">
                <h3><i class="fas fa-sign-in-alt" style="color:#16a34a;"></i> Absen Masuk</h3>
                <div class="form-row">
                    <label class="form-label">Jam Masuk</label>
                    <input type="time" name="jam_masuk" id="jamMasukInput"
                        class="form-input @error('jam_masuk') is-error @enderror"
                        value="{{ old('jam_masuk', $absen->jam_masuk ? substr($absen->jam_masuk, 0, 5) : '') }}">
                    @error('jam_masuk')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Kosongkan untuk menghapus jam masuk (status menjadi Alfa)</div>
                </div>
                <div class="form-row">
                    <label class="form-label">Override Status Masuk</label>
                    <select name="status_masuk" id="statusMasukSelect"
                        class="form-input @error('status_masuk') is-error @enderror">
                        <option value="">— Hitung Otomatis —</option>
                        <option value="hadir"
                            {{ old('status_masuk', $absen->status_masuk) === 'hadir' ? 'selected' : '' }}>Hadir
                        </option>
                        <option value="terlambat"
                            {{ old('status_masuk', $absen->status_masuk) === 'terlambat' ? 'selected' : '' }}>Terlambat
                        </option>
                        <option value="izin"
                            {{ old('status_masuk', $absen->status_masuk) === 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="sakit"
                            {{ old('status_masuk', $absen->status_masuk) === 'sakit' ? 'selected' : '' }}>Sakit
                        </option>
                        <option value="alfa"
                            {{ old('status_masuk', $absen->status_masuk) === 'alfa' ? 'selected' : '' }}>Alfa</option>
                        <option value="pkl" {{ old('status_masuk', $absen->status_masuk) === 'pkl' ? 'selected' : '' }}>
                            PKL
                        </option>
                    </select>
                    @error('status_masuk')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Biarkan kosong agar sistem menghitung otomatis dari jam masuk</div>
                </div>
            </div>

            {{-- Form Izin/Sakit/PKL — muncul kondisional saat status = izin/sakit/pkl --}}
            <div class="form-card izin-form-wrap" id="izinFormWrap">
                <h3 id="izinFormTitle">
                    <i class="fas fa-file-medical" style="color:#dc2626;"></i> Data Izin / Sakit
                    <span style="font-size:.72rem;font-weight:500;color:#64748b;margin-left:4px;">(langsung
                        disetujui)</span>
                </h3>
                {{-- Notice PKL --}}
                <div id="pklNoticeEdit"
                    style="display:none;margin-bottom:12px;background:#fef3c7;border:1px solid #fde68a;border-radius:9px;padding:10px 13px;font-size:.78rem;color:#92400e;">
                    <i class="fas fa-briefcase"></i>
                    <strong>PKL (Praktik Kerja Lapangan):</strong> Siswa tidak hadir di sekolah pada periode ini.
                    Poin pelanggaran alfa otomatis pada rentang tanggal tersebut akan dihapus saat konfirmasi.
                </div>

                {{-- Jenis Izin --}}
                <div class="form-row">
                    <label class="form-label">Jenis Izin <span class="req">*</span></label>
                    <div class="jenis-grid" id="jenisGrid">
                        @php
                            $jenisOptions = [
                                'izin_sakit' => [
                                    'label' => 'Izin Sakit',
                                    'icon' => 'fa-heart-pulse',
                                    'color' => '#dc2626',
                                ],
                                'izin_terlambat' => [
                                    'label' => 'Izin Terlambat',
                                    'icon' => 'fa-clock',
                                    'color' => '#c2410c',
                                ],
                                'izin_pulang_cepat' => [
                                    'label' => 'Pulang Cepat',
                                    'icon' => 'fa-person-walking',
                                    'color' => '#0369a1',
                                ],
                                'izin_lainnya' => [
                                    'label' => 'Izin Lainnya',
                                    'icon' => 'fa-file-lines',
                                    'color' => '#6d28d9',
                                ],
                                'pkl' => [
                                    'label' => 'PKL',
                                    'icon' => 'fa-briefcase',
                                    'color' => '#b45309',
                                ],
                            ];
                            $jenisDefault = old('izin_jenis', $izinAktif?->jenis ?? '');
                            if (!$jenisDefault) {
                                $statusNow = $absen->status_masuk ?? ($absen->status ?? 'alfa');
                                $jenisDefault =
                                    $statusNow === 'sakit'
                                        ? 'izin_sakit'
                                        : ($statusNow === 'izin'
                                            ? 'izin_lainnya'
                                            : ($statusNow === 'pkl'
                                                ? 'pkl'
                                                : ''));
                            }
                        @endphp
                        @foreach ($jenisOptions as $val => $opt)
                            <label class="jenis-option">
                                <input type="radio" name="izin_jenis" value="{{ $val }}"
                                    {{ $jenisDefault === $val ? 'checked' : '' }}>
                                <i class="fas {{ $opt['icon'] }}"
                                    style="color:{{ $opt['color'] }};margin-right:5px;"></i>
                                <strong>{{ $opt['label'] }}</strong>
                            </label>
                        @endforeach
                    </div>
                    @error('izin_jenis')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tanggal Mulai --}}
                <div class="form-row">
                    <label class="form-label">Tanggal Mulai <span class="req">*</span></label>
                    <input type="date" name="izin_tanggal_mulai" id="izinTanggalMulai"
                        class="form-input @error('izin_tanggal_mulai') is-error @enderror"
                        value="{{ old('izin_tanggal_mulai', $izinAktif?->tanggal_mulai?->format('Y-m-d') ?? $absen->tanggal->format('Y-m-d')) }}">
                    @error('izin_tanggal_mulai')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Isi tanggal awal izin. Default: tanggal absensi ini.</div>
                </div>

                {{-- Tanggal Sampai --}}
                <div class="form-row">
                    <label class="form-label">Tanggal Sampai <span class="req">*</span></label>
                    <input type="date" name="izin_tanggal_sampai" id="izinTanggalSampai"
                        class="form-input @error('izin_tanggal_sampai') is-error @enderror"
                        value="{{ old('izin_tanggal_sampai', $izinAktif?->tanggal_sampai?->format('Y-m-d') ?? $absen->tanggal->format('Y-m-d')) }}">
                    @error('izin_tanggal_sampai')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Untuk izin 1 hari, biarkan sama dengan tanggal mulai. Isi lebih untuk izin
                        multi-hari.</div>
                </div>

                {{-- Alasan --}}
                <div class="form-row">
                    <label class="form-label">Alasan <span class="req">*</span></label>
                    <textarea name="izin_alasan" class="form-input @error('izin_alasan') is-error @enderror" rows="3"
                        placeholder="Tuliskan alasan izin/sakit...">{{ old('izin_alasan', $izinAktif?->alasan ?? '') }}</textarea>
                    @error('izin_alasan')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Upload Bukti --}}
                <div class="form-row">
                    <label class="form-label">
                        Upload Surat / Bukti
                        <span style="font-size:.68rem;font-weight:500;color:#64748b;margin-left:4px;">(Opsional)</span>
                    </label>
                    @if ($izinAktif?->bukti)
                        <div
                            style="background:#f0fdf4;border:1px solid #86efac;border-radius:9px;padding:8px 12px;font-size:.78rem;color:#166534;margin-bottom:8px;">
                            <i class="fas fa-file-check"></i>
                            Bukti saat ini:
                            <a href="{{ asset('storage/' . $izinAktif->bukti) }}" target="_blank"
                                style="color:#16a34a;font-weight:600;">Lihat File</a>
                            <span style="color:#64748b;margin-left:6px;">— Upload baru untuk mengganti</span>
                        </div>
                    @endif
                    <input type="file" name="izin_bukti" id="izinBuktiInput"
                        accept="image/jpeg,image/png,image/jpg,application/pdf"
                        class="form-input @error('izin_bukti') is-error @enderror" onchange="previewBuktiFile(this)">
                    @error('izin_bukti')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div id="buktiPreview"
                        style="display:none;margin-top:8px;background:#f0fdf4;border:1px solid #86efac;border-radius:9px;padding:9px 12px;font-size:.8rem;color:#166534;align-items:center;gap:8px;">
                        <i class="fas fa-file-check"></i>
                        <span id="buktiNama" style="font-weight:600;"></span>
                        <span id="buktiUkuran" style="color:#64748b;margin-left:4px;font-size:.72rem;"></span>
                    </div>
                    <div class="hint">JPG, PNG, atau PDF &bull; Maks 5 MB &bull; Surat dokter, izin orang tua, dll.</div>
                </div>
            </div>

            {{-- Absen Pulang --}}
            <div class="form-card">
                <h3><i class="fas fa-sign-out-alt" style="color:#0ea5e9;"></i> Absen Pulang</h3>
                <div class="form-row">
                    <label class="form-label">Jam Pulang</label>
                    <input type="time" name="jam_pulang" class="form-input @error('jam_pulang') is-error @enderror"
                        value="{{ old('jam_pulang', $absen->jam_pulang ? substr($absen->jam_pulang, 0, 5) : '') }}">
                    @error('jam_pulang')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Kosongkan untuk menghapus jam pulang</div>
                </div>
            </div>

            {{-- Catatan --}}
            <div class="form-card">
                <h3><i class="fas fa-sticky-note" style="color:#f59e0b;"></i> Catatan</h3>
                <div class="form-row">
                    <textarea name="catatan" class="form-input @error('catatan') is-error @enderror" rows="3"
                        placeholder="Catatan perubahan...">{{ old('catatan', $absen->catatan) }}</textarea>
                    @error('catatan')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if ($absen->diverifikasi_oleh)
                <div
                    style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;font-size:.75rem;color:#64748b;margin-bottom:14px;">
                    <i class="fas fa-history"></i>
                    Terakhir diubah oleh <strong>{{ $absen->diverifikasiOleh->name ?? 'Admin' }}</strong>
                    &bull; {{ $absen->updated_at?->translatedFormat('d M Y, H:i') }}
                </div>
            @endif
        </form>
    </div>

    {{-- ══ MODAL Konfirmasi Hapus Poin Alfa ════════════════════════════════════════ --}}
    {{--   Letakkan DI LUAR .form-wrap agar tidak terpotong overflow parent manapun --}}
    <div class="poin-overlay" id="overlayPoinAlfa" role="dialog" aria-modal="true" aria-labelledby="modalPoinTitle">
        <div class="poin-modal">
            <div class="poin-modal-head">
                <h4 id="modalPoinTitle">
                    <i class="fas fa-exclamation-triangle"></i>
                    Konfirmasi Simpan Perubahan
                </h4>
            </div>
            <div class="poin-modal-body">
                <p>Point pelanggaran pada rentang tanggal tersebut akan dihapus.</p>
                <p><strong>Apakah Anda yakin ingin melanjutkan?</strong></p>
                <div class="detail" id="poinAlfaDetail">
                    <i class="fas fa-spinner fa-spin"></i> Memuat detail poin...
                </div>
            </div>
            <div class="poin-modal-foot">
                <button type="button" class="btn-simpan-saja" id="btnSimpanSaja">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn-hapus-poin" id="btnHapusPoin">
                    <i class="fas fa-check"></i> Konfirmasi
                </button>
            </div>
        </div>
    </div>

    {{-- ══ Fixed bottom bar ════════════════════════════════════════════════════════ --}}
    <div class="action-bar">
        <a href="{{ route('admin.absen-manual.index', ['tanggal' => $absen->tanggal->format('Y-m-d')]) }}"
            class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Batal
        </a>
        <button type="button" id="btnSimpan" class="ab-btn ab-btn-orange">
            <i class="fas fa-sync-alt"></i> Simpan &amp; Recalculate
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const SISWA_ID = {{ $absen->siswa_id }};
            const TANGGAL = '{{ $absen->tanggal->format('Y-m-d') }}';
            const STATUS_AWAL = '{{ $absen->status_masuk ?? ($absen->status ?? 'alfa') }}';
            const CEK_POIN_URL = '{{ route('admin.absen-manual.api.cek-poin-alfa') }}';
            const HAPUS_POIN_URL = '{{ route('admin.absen-manual.api.hapus-poin-alfa') }}';
            const CEK_RANGE_URL = '{{ route('admin.absen-manual.api.cek-poin-alfa-range') }}';
            const HAPUS_RANGE_URL = '{{ route('admin.absen-manual.api.hapus-poin-alfa-range') }}';
            const CSRF_TOKEN = '{{ csrf_token() }}';

            const overlay = document.getElementById('overlayPoinAlfa');
            const alfaWarning = document.getElementById('alfaWarning');
            const izinFormWrap = document.getElementById('izinFormWrap');
            const izinFormTitle = document.getElementById('izinFormTitle');
            const pklNoticeEdit = document.getElementById('pklNoticeEdit');
            const hapusPoinFlag = document.getElementById('hapusPoinAlfaFlag');
            const form = document.getElementById('editForm');
            const btnSimpan = document.getElementById('btnSimpan');
            const btnBatal = document.getElementById('btnSimpanSaja'); // "Batal"
            const btnKonfirmasi = document.getElementById('btnHapusPoin'); // "Konfirmasi"
            const poinDetail = document.getElementById('poinAlfaDetail');
            const statusSelect = document.getElementById('statusMasukSelect');
            const tMulai = document.getElementById('izinTanggalMulai');
            const tSampai = document.getElementById('izinTanggalSampai');

            let adaPoinAlfa = false;
            let poinAlfaData = null;
            // Mode hapus: 'single' (satu tanggal) atau 'range' (dari form izin)
            let hapusMode = 'single';

            // ══════════════════════════════════════════════════════════════════
            // 1. Sinkron tanggal_mulai → tanggal_sampai
            // ══════════════════════════════════════════════════════════════════
            if (tMulai && tSampai) {
                tMulai.addEventListener('change', function() {
                    if (!tSampai.value || tSampai.value < this.value) {
                        tSampai.value = this.value;
                    }
                    tSampai.min = this.value;
                });
                // Set min awal
                if (tMulai.value) tSampai.min = tMulai.value;
            }

            // ══════════════════════════════════════════════════════════════════
            // 2. Tampil/sembunyikan form izin + notice PKL
            // ══════════════════════════════════════════════════════════════════
            function toggleIzinForm() {
                const val = statusSelect.value;
                const isPkl = val === 'pkl';
                const butuhIzin = val === 'izin' || val === 'sakit' || isPkl;

                if (butuhIzin) {
                    izinFormWrap.classList.add('show');

                    // Judul seksi
                    if (isPkl) {
                        izinFormTitle.innerHTML =
                            '<i class="fas fa-briefcase" style="color:#b45309;"></i> Data PKL <span style="font-size:.72rem;font-weight:500;color:#64748b;margin-left:4px;">(langsung disetujui)</span>';
                        pklNoticeEdit.style.display = 'block';
                        // Auto-pilih jenis PKL
                        const radioPkl = document.querySelector('input[name=izin_jenis][value=pkl]');
                        if (radioPkl) radioPkl.checked = true;
                    } else {
                        izinFormTitle.innerHTML =
                            '<i class="fas fa-file-medical" style="color:#dc2626;"></i> Data Izin / Sakit <span style="font-size:.72rem;font-weight:500;color:#64748b;margin-left:4px;">(langsung disetujui)</span>';
                        pklNoticeEdit.style.display = 'none';
                        // Auto-pilih sakit jika status = sakit dan belum ada pilihan
                        if (val === 'sakit') {
                            const radioSakit = document.querySelector('input[name=izin_jenis][value=izin_sakit]');
                            if (radioSakit && !document.querySelector('input[name=izin_jenis]:checked')) {
                                radioSakit.checked = true;
                            }
                        }
                    }
                } else {
                    izinFormWrap.classList.remove('show');
                    pklNoticeEdit.style.display = 'none';
                }
            }

            // Sinkron juga saat jenis radio berubah (misal user ubah dari PKL ke lainnya)
            document.querySelectorAll('input[name=izin_jenis]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (this.value === 'pkl') {
                        pklNoticeEdit.style.display = 'block';
                    } else {
                        pklNoticeEdit.style.display = 'none';
                    }
                });
            });

            statusSelect.addEventListener('change', toggleIzinForm);
            toggleIzinForm(); // jalankan saat halaman dimuat

            // ══════════════════════════════════════════════════════════════════
            // 3. Cek poin alfa saat status awal = alfa
            // ══════════════════════════════════════════════════════════════════
            if (STATUS_AWAL === 'alfa') {
                fetch(`${CEK_POIN_URL}?siswa_id=${SISWA_ID}&tanggal=${TANGGAL}`)
                    .then(r => r.json())
                    .then(data => {
                        adaPoinAlfa = !!data.ada_poin;
                        poinAlfaData = data;
                        if (adaPoinAlfa) alfaWarning.style.display = 'flex';
                    })
                    .catch(() => {
                        /* lanjut tanpa fitur hapus poin */ });
            }

            // ══════════════════════════════════════════════════════════════════
            // 4. Helper buka / tutup modal
            // ══════════════════════════════════════════════════════════════════
            function bukaModal(mode, extraData) {
                hapusMode = mode || 'single';
                let html = '';

                if (mode === 'range' && extraData) {
                    const jumlah = extraData.jumlah || 0;
                    const mulai = extraData.tanggal_mulai || TANGGAL;
                    const sampai = extraData.tanggal_sampai || TANGGAL;
                    if (jumlah > 0) {
                        html = `
                            <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
                                <i class="fas fa-exclamation-circle" style="color:#dc2626;flex-shrink:0;"></i>
                                <strong style="color:#dc2626;">Ditemukan ${jumlah} poin pelanggaran alfa</strong>
                            </div>
                            <div style="font-size:.72rem;color:#64748b;margin-bottom:4px;">
                                <i class="fas fa-calendar-alt"></i> Periode: ${mulai} s/d ${sampai}
                            </div>
                            <div style="font-size:.78rem;color:#92400e;background:#fef3c7;border-radius:6px;padding:6px 9px;margin-top:6px;">
                                <i class="fas fa-trash-alt"></i> Semua poin alfa otomatis pada rentang ini akan dihapus.
                            </div>`;
                    } else {
                        html = `
                            <div style="display:flex;gap:8px;align-items:center;">
                                <i class="fas fa-check-circle" style="color:#16a34a;"></i>
                                <span>Tidak ada poin pelanggaran alfa pada rentang tanggal ini.</span>
                            </div>`;
                    }
                } else if (poinAlfaData) {
                    html = `
                        <div style="display:flex;gap:12px;">
                            <div><span style="color:#64748b;">Pasal:</span> <strong>${poinAlfaData.pasal || '-'}</strong></div>
                            <div><span style="color:#64748b;">Poin:</span> <strong style="color:#dc2626;">${poinAlfaData.poin_nilai || 0}</strong></div>
                        </div>
                        <div style="margin-top:4px;font-size:.72rem;color:#94a3b8;">
                            <i class="fas fa-robot"></i> Dibuat otomatis pada ${TANGGAL}
                        </div>`;
                }

                poinDetail.innerHTML = html || '<span style="color:#94a3b8;">Tidak ada detail tersedia.</span>';
                overlay.classList.add('open');
                document.body.style.overflow = 'hidden';
                btnKonfirmasi.disabled = false;
                btnKonfirmasi.innerHTML = '<i class="fas fa-check"></i> Konfirmasi';
            }

            function tutupModal() {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }

            // ══════════════════════════════════════════════════════════════════
            // 5. Tombol Simpan utama
            // ══════════════════════════════════════════════════════════════════
            btnSimpan.addEventListener('click', function() {
                // Validasi form izin jika ditampilkan
                if (izinFormWrap.classList.contains('show')) {
                    const jenisChecked = document.querySelector('input[name=izin_jenis]:checked');
                    const alasan = document.querySelector('textarea[name=izin_alasan]').value.trim();
                    const mulai = tMulai?.value;
                    const sampai = tSampai?.value;

                    if (!jenisChecked) {
                        alert('Pilih jenis izin terlebih dahulu.');
                        return;
                    }
                    if (!alasan) {
                        alert('Alasan izin wajib diisi.');
                        return;
                    }
                    if (!mulai) {
                        alert('Tanggal mulai wajib diisi.');
                        return;
                    }
                    if (!sampai) {
                        alert('Tanggal sampai wajib diisi.');
                        return;
                    }
                    if (sampai < mulai) {
                        alert('Tanggal sampai tidak boleh sebelum tanggal mulai.');
                        return;
                    }
                }

                const statusBaru = statusSelect.value;
                const jamMasuk = document.getElementById('jamMasukInput').value;
                const statusEfektif = statusBaru !== '' ? statusBaru : (jamMasuk ? 'hadir_atau_terlambat' :
                    'alfa');
                const berubahDariAlfa = (STATUS_AWAL === 'alfa') && (statusEfektif !== 'alfa');

                // Jika status berubah ke PKL atau izin/sakit dengan range → cek poin range
                const isPklOrRange = (statusEfektif === 'pkl') ||
                    (izinFormWrap.classList.contains('show') && tMulai?.value && tSampai?.value &&
                        tSampai.value !== TANGGAL);

                if (berubahDariAlfa && adaPoinAlfa && !isPklOrRange) {
                    // Mode single tanggal
                    bukaModal('single', null);
                } else if (berubahDariAlfa || statusEfektif === 'pkl' || statusEfektif === 'izin' ||
                    statusEfektif === 'sakit') {
                    // Mode range — selalu tampilkan modal konfirmasi
                    const mulai = tMulai?.value || TANGGAL;
                    const sampai = tSampai?.value || TANGGAL;

                    poinDetail.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memeriksa poin alfa...';
                    overlay.classList.add('open');
                    document.body.style.overflow = 'hidden';
                    btnKonfirmasi.disabled = true;

                    fetch(
                            `${CEK_RANGE_URL}?siswa_id=${SISWA_ID}&tanggal_mulai=${mulai}&tanggal_sampai=${sampai}`)
                        .then(r => r.json())
                        .then(data => {
                            bukaModal('range', data);
                        })
                        .catch(() => {
                            bukaModal('range', {
                                jumlah: 0,
                                tanggal_mulai: mulai,
                                tanggal_sampai: sampai
                            });
                        });
                } else {
                    hapusPoinFlag.value = '0';
                    form.submit();
                }
            });

            // ══════════════════════════════════════════════════════════════════
            // 6. Tombol di dalam modal
            // ══════════════════════════════════════════════════════════════════

            // "Batal" — tutup modal, tidak submit
            btnBatal.addEventListener('click', function() {
                tutupModal();
            });

            // "Konfirmasi" — hapus poin (range atau single) lalu submit
            btnKonfirmasi.addEventListener('click', function() {
                btnKonfirmasi.disabled = true;
                btnKonfirmasi.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

                const mulai = tMulai?.value || TANGGAL;
                const sampai = tSampai?.value || TANGGAL;

                const hapusUrl = hapusMode === 'range' ? HAPUS_RANGE_URL : HAPUS_POIN_URL;
                const hapusBody = hapusMode === 'range' ?
                    {
                        siswa_id: SISWA_ID,
                        tanggal_mulai: mulai,
                        tanggal_sampai: sampai
                    } :
                    {
                        siswa_id: SISWA_ID,
                        tanggal: TANGGAL
                    };

                fetch(hapusUrl, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        body: JSON.stringify(hapusBody),
                    })
                    .then(r => r.json())
                    .then(() => {
                        tutupModal();
                        hapusPoinFlag.value = '1';
                        form.submit();
                    })
                    .catch(() => {
                        tutupModal();
                        hapusPoinFlag.value = '0';
                        form.submit();
                    });
            });

            // Klik backdrop → tutup
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) tutupModal();
            });

            // Escape → tutup
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlay.classList.contains('open')) tutupModal();
            });
        });

        // ── Preview file bukti ────────────────────────────────────────────────────────
        function previewBuktiFile(input) {
            const preview = document.getElementById('buktiPreview');
            const nama = document.getElementById('buktiNama');
            const ukuran = document.getElementById('buktiUkuran');
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    alert('Ukuran file terlalu besar (maks 5 MB).');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                nama.textContent = file.name;
                ukuran.textContent = file.size < 1024 * 1024 ?
                    Math.round(file.size / 1024) + ' KB' :
                    (file.size / 1024 / 1024).toFixed(1) + ' MB';
                preview.style.display = 'flex';
            } else {
                preview.style.display = 'none';
            }
        }

        // ── SweetAlert untuk error validasi server ──
        @if ($errors->any())
            if (typeof Swal !== 'undefined' && !window.__swalValidationShown) {
                window.__swalValidationShown = true;
                const errorList = @json($errors->all());
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: '<ul style="padding:0;margin:0;list-style:none;text-align:left;">' +
                        errorList.map(m => '<li style="margin-bottom:4px;">• ' + m + '</li>').join('') +
                        '</ul>',
                    confirmButtonText: 'Oke',
                    confirmButtonColor: '#ef4444',
                });
            }
        @endif
    </script>
@endpush
