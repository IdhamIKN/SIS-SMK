@extends('layouts.app')
@section('title', 'Buat Izin Siswa (Admin)')

@push('styles')
    @include('components.event-styles')
    <style>
        .form-wrap {
            padding-top: var(--header-h, 56px);
            padding-bottom: calc(var(--footer-h, 60px) + 88px);
            max-width: 680px;
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

        .info-box {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 14px;
            font-size: .82rem;
            color: #166534;
        }

        /* ── Jenis izin grid ── */
        .jenis-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .jenis-option {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all .15s;
            font-size: .82rem;
        }

        .jenis-option input[type=radio] {
            margin-right: 6px;
        }

        .jenis-option:has(input:checked) {
            border-color: #0ea5e9;
            background: #eff6ff;
        }

        /* ── Bulk toggle switch ── */
        .bulk-toggle-wrap {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all .15s;
        }

        .bulk-toggle-wrap:hover {
            border-color: #0d9488;
            background: #f0fdfa;
        }

        .bulk-toggle-wrap.active {
            border-color: #0d9488;
            background: #f0fdfa;
        }

        .toggle-switch {
            position: relative;
            width: 44px;
            height: 24px;
            flex-shrink: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            inset: 0;
            background: #cbd5e1;
            border-radius: 24px;
            cursor: pointer;
            transition: .2s;
        }

        .toggle-slider:before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: .2s;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .2);
        }

        input:checked+.toggle-slider {
            background: #0d9488;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(20px);
        }

        /* ── Siswa chips (bulk) ── */
        .siswa-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 20px;
            padding: 3px 10px 3px 8px;
            font-size: .72rem;
            font-weight: 600;
            margin: 2px;
        }

        .siswa-chip button {
            background: none;
            border: none;
            cursor: pointer;
            color: #0369a1;
            font-size: .65rem;
            padding: 0 1px;
            line-height: 1;
            opacity: .7;
        }

        .siswa-chip button:hover {
            opacity: 1;
        }

        /* ── Siswa list (bulk checkbox) ── */
        .siswa-list-wrap {
            max-height: 240px;
            overflow-y: auto;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            background: #f8fafc;
        }

        .siswa-list-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background .1s;
            font-size: .82rem;
        }

        .siswa-list-item:last-child {
            border-bottom: none;
        }

        .siswa-list-item:hover {
            background: #f0f9ff;
        }

        .siswa-list-item.selected {
            background: #eff6ff;
        }

        .siswa-list-item input[type=checkbox] {
            width: 16px;
            height: 16px;
            accent-color: #0d9488;
            flex-shrink: 0;
            cursor: pointer;
        }

        /* ── Action bar ── */
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
            z-index: 999;
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

        .ab-btn-teal {
            background: #0d9488;
            color: #fff;
        }

        /* ── Modal overlay ── */
        .ci-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .65);
            z-index: 9998;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .ci-overlay.open {
            display: flex;
        }

        .ci-modal {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .3);
        }

        .ci-modal-head {
            padding: 16px 18px;
            border-bottom: 1px solid #fde68a;
            background: #fffbeb;
        }

        .ci-modal-head h4 {
            margin: 0;
            font-size: .95rem;
            font-weight: 800;
            color: #92400e;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ci-modal-body {
            padding: 16px 18px;
            font-size: .85rem;
            color: #0f172a;
            line-height: 1.65;
        }

        .ci-modal-body p {
            margin: 0 0 10px;
        }

        .ci-modal-body p:last-child {
            margin-bottom: 0;
        }

        .ci-modal-foot {
            padding: 12px 18px;
            border-top: 1px solid #eef2f7;
            display: flex;
            gap: 8px;
        }

        .ci-modal-foot button {
            flex: 1;
            padding: 11px;
            border-radius: 10px;
            font-size: .83rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
            transition: opacity .15s;
        }

        .ci-modal-foot button:disabled {
            opacity: .6;
            cursor: not-allowed;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap form-wrap">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>Admin</div>
            <h2><i class="fas fa-file-medical"></i> Buat Izin Siswa</h2>
            <p>Izin dibuat oleh admin, langsung disetujui &amp; sync ke data absensi</p>
        </div>

        {{-- Info alur ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── --}}
        <div class="info-box">
            <i class="fas fa-check-double"></i>
            <strong>Alur Otomatis:</strong> Izin yang dibuat admin <strong>langsung berstatus Disetujui</strong>
            dan sistem otomatis memperbarui status absensi siswa pada rentang tanggal yang dipilih.
            <br><small style="margin-top:4px;display:block;color:#166534;opacity:.8;">
                Data kehadiran yang sudah ada akan di-<em>update</em> ke status izin.
                Jika siswa sudah hadir/terlambat pada hari tersebut, data hadir tidak akan ditimpa.
            </small>
        </div>

        {{-- Toggle Massal ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── ── --}}
        <label class="bulk-toggle-wrap" id="bulkToggleWrap">
            <label class="toggle-switch" onclick="event.stopPropagation()">
                <input type="checkbox" id="bulkToggle">
                <span class="toggle-slider"></span>
            </label>
            <div>
                <div style="font-size:.88rem;font-weight:700;color:#0f172a;">Buat Izin Secara Massal</div>
                <div style="font-size:.72rem;color:#64748b;margin-top:1px;">
                    Aktifkan untuk memilih beberapa siswa sekaligus dalam satu rentang tanggal
                </div>
            </div>
        </label>

        <form method="POST" action="{{ route('admin.absen-manual.izin.store') }}" id="izinForm"
            enctype="multipart/form-data">
            @csrf
            {{-- Flag massal (diisi JS) --}}
            <input type="hidden" name="is_massal" id="isMassal" value="0">
            {{-- siswa_ids[] diisi JS saat massal aktif --}}

            {{-- ══════════════════════════════════════════
                 PANEL SISWA TUNGGAL
            ══════════════════════════════════════════ --}}
            <div class="form-card" id="panelSiswaTunggal">
                <h3><i class="fas fa-user" style="color:#0ea5e9;"></i> Data Siswa</h3>

                <div class="form-row">
                    <label class="form-label">Kelas</label>
                    <select id="kelasSelect" class="form-input">
                        <option value="">— Pilih Kelas dulu —</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <label class="form-label">Siswa <span class="req">*</span></label>
                    <select name="siswa_id" id="siswaSelect" class="form-input @error('siswa_id') is-error @enderror">
                        <option value="">— Pilih Siswa —</option>
                    </select>
                    @error('siswa_id')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="siswa-preview" id="siswaPreview"
                        style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:10px 14px;margin-top:8px;font-size:.82rem;display:none;">
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
                 PANEL SISWA MASSAL
            ══════════════════════════════════════════ --}}
            <div class="form-card" id="panelSiswaMassal" style="display:none;">
                <h3><i class="fas fa-users" style="color:#0d9488;"></i> Pilih Siswa (Massal)</h3>

                {{-- Filter kelas --}}
                <div class="form-row">
                    <label class="form-label">Filter Kelas</label>
                    <select id="kelasMassalSelect" class="form-input">
                        <option value="">— Semua Kelas —</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Search siswa --}}
                <div class="form-row" style="position:relative;">
                    <label class="form-label">Cari Siswa</label>
                    <div style="position:relative;">
                        <input type="text" id="searchMassal" class="form-input" placeholder="Ketik nama atau NIS..."
                            autocomplete="off" style="padding-left:34px;">
                        <i class="fas fa-search"
                            style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.82rem;pointer-events:none;"></i>
                    </div>
                </div>

                {{-- Aksi cepat --}}
                <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
                    <button type="button" id="btnPilihSemua"
                        style="padding:6px 14px;border-radius:8px;font-size:.75rem;font-weight:700;border:1.5px solid #0d9488;background:#f0fdfa;color:#0d9488;cursor:pointer;font-family:inherit;">
                        <i class="fas fa-check-double"></i> Pilih Semua
                    </button>
                    <button type="button" id="btnBatalSemua"
                        style="padding:6px 14px;border-radius:8px;font-size:.75rem;font-weight:700;border:1.5px solid #e2e8f0;background:#f1f5f9;color:#475569;cursor:pointer;font-family:inherit;">
                        <i class="fas fa-times"></i> Batal Semua
                    </button>
                    <span id="massalCountBadge"
                        style="margin-left:auto;background:#e0f2fe;color:#0369a1;border-radius:20px;padding:4px 12px;font-size:.72rem;font-weight:700;align-self:center;">
                        0 dipilih
                    </span>
                </div>

                {{-- Daftar siswa --}}
                <div class="siswa-list-wrap" id="siswaMassalList">
                    <div style="padding:20px;text-align:center;color:#94a3b8;font-size:.82rem;">
                        <i class="fas fa-arrow-up" style="display:block;font-size:1.2rem;margin-bottom:6px;opacity:.4;"></i>
                        Pilih kelas atau ketik nama untuk menampilkan daftar siswa
                    </div>
                </div>

                {{-- Chips terpilih --}}
                <div id="selectedChipsWrap" style="margin-top:10px;display:none;">
                    <div style="font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">
                        <i class="fas fa-users"></i> Siswa terpilih:
                    </div>
                    <div id="selectedChips"></div>
                </div>

                {{-- Hidden inputs untuk siswa_ids —diisi JS saat submit --}}
                <div id="siswaIdsHidden"></div>
            </div>

            {{-- ══════════════════════════════════════════
                 JENIS IZIN
            ══════════════════════════════════════════ --}}
            <div class="form-card">
                <h3><i class="fas fa-tag" style="color:#7c3aed;"></i> Jenis Izin</h3>

                <div class="jenis-grid">
                    @php
                        $jenisOptions = [
                            'izin_sakit' => ['label' => 'Izin Sakit', 'icon' => 'fa-heart-pulse', 'color' => '#dc2626'],
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
                            'pkl' => ['label' => 'PKL', 'icon' => 'fa-briefcase', 'color' => '#b45309'],
                        ];
                    @endphp
                    @foreach ($jenisOptions as $val => $opt)
                        <label class="jenis-option">
                            <input type="radio" name="jenis" value="{{ $val }}"
                                {{ old('jenis') === $val ? 'checked' : '' }} required>
                            <i class="fas {{ $opt['icon'] }}" style="color:{{ $opt['color'] }};margin-right:5px;"></i>
                            <strong>{{ $opt['label'] }}</strong>
                        </label>
                    @endforeach
                </div>

                <div id="pklNotice"
                    style="display:none;margin-top:10px;background:#fef3c7;border:1px solid #fde68a;border-radius:9px;padding:10px 13px;font-size:.78rem;color:#92400e;">
                    <i class="fas fa-briefcase"></i>
                    <strong>PKL (Praktik Kerja Lapangan):</strong> Siswa tidak hadir di sekolah selama periode ini.
                    Poin pelanggaran alfa otomatis pada rentang tanggal tersebut akan dihapus saat menyimpan.
                </div>

                @error('jenis')
                    <div class="error-msg" style="margin-top:6px;">{{ $message }}</div>
                @enderror
            </div>

            {{-- ══════════════════════════════════════════
                 TANGGAL IZIN
            ══════════════════════════════════════════ --}}
            <div class="form-card">
                <h3><i class="fas fa-calendar" style="color:#16a34a;"></i> Tanggal Izin</h3>

                <div class="form-row">
                    <label class="form-label">Tanggal Mulai <span class="req">*</span></label>
                    <input type="date" name="tanggal_mulai" id="inputTanggalMulai"
                        class="form-input @error('tanggal_mulai') is-error @enderror"
                        value="{{ old('tanggal_mulai', $tanggal) }}" required>
                    @error('tanggal_mulai')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row">
                    <label class="form-label">Tanggal Sampai <span class="req">*</span></label>
                    <input type="date" name="tanggal_sampai" id="inputTanggalSampai"
                        class="form-input @error('tanggal_sampai') is-error @enderror"
                        value="{{ old('tanggal_sampai', $tanggal) }}" required>
                    @error('tanggal_sampai')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div class="hint">Untuk izin 1 hari, isi tanggal yang sama di Mulai dan Sampai</div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════
                 ALASAN & SURAT
            ══════════════════════════════════════════ --}}
            <div class="form-card">
                <h3><i class="fas fa-comment-dots" style="color:#f59e0b;"></i> Alasan &amp; Surat</h3>

                <div class="form-row">
                    <label class="form-label">Alasan <span class="req">*</span></label>
                    <textarea name="alasan" class="form-input @error('alasan') is-error @enderror" rows="3"
                        placeholder="Tuliskan alasan izin..." required>{{ old('alasan') }}</textarea>
                    @error('alasan')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-row" id="buktiWrap">
                    <label class="form-label">
                        Upload Surat / Bukti
                        <span style="font-size:.68rem;font-weight:500;color:#64748b;margin-left:4px;">(Opsional, hanya
                            untuk izin tunggal)</span>
                    </label>
                    <input type="file" name="bukti" id="buktiInput"
                        accept="image/jpeg,image/png,image/jpg,application/pdf"
                        class="form-input @error('bukti') is-error @enderror" onchange="previewBuktiFile(this)">
                    @error('bukti')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                    <div id="buktiPreview"
                        style="display:none;margin-top:8px;background:#f0fdf4;border:1px solid #86efac;border-radius:9px;padding:9px 12px;font-size:.8rem;color:#166534;align-items:center;gap:8px;">
                        <i class="fas fa-file-check"></i>
                        <span id="buktiNama" style="font-weight:600;"></span>
                        <span id="buktiUkuran" style="color:#64748b;margin-left:4px;font-size:.72rem;"></span>
                    </div>
                    <div class="hint">JPG, PNG, atau PDF · Maks 5 MB</div>
                </div>
            </div>
        </form>
    </div>

    {{-- ══ Action Bar ══════════════════════════════════════════════════════════════ --}}
    <div class="action-bar">
        <a href="{{ route('admin.absen-manual.index', ['tanggal' => $tanggal]) }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> Batal
        </a>
        <button type="button" id="btnBuatIzin" class="ab-btn ab-btn-teal">
            <i class="fas fa-check-double"></i> <span id="btnLabel">Buat &amp; Setujui Izin</span>
        </button>
    </div>

    {{-- ══ Modal Konfirmasi ════════════════════════════════════════════════════════ --}}
    <div id="overlayKonfirmasi" class="ci-overlay">
        <div class="ci-modal">
            <div class="ci-modal-head">
                <h4><i class="fas fa-exclamation-triangle"></i> Konfirmasi Simpan Izin</h4>
            </div>
            <div class="ci-modal-body">
                <p>Point pelanggaran pada rentang tanggal tersebut akan dihapus.</p>
                <p><strong>Apakah Anda yakin ingin melanjutkan?</strong></p>
                <div id="konfirmasiDetail"
                    style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:10px 13px;font-size:.78rem;color:#64748b;margin-top:4px;">
                    <i class="fas fa-spinner fa-spin"></i> Memeriksa poin alfa...
                </div>
            </div>
            <div class="ci-modal-foot">
                <button type="button" id="btnKonfBatal"
                    style="border:1px solid #e2e8f0;background:#f1f5f9;color:#475569;">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" id="btnKonfOk" style="border:none;background:#0d9488;color:#fff;">
                    <i class="fas fa-check-double"></i> Konfirmasi
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            'use strict';

            // ── URLs & Config ─────────────────────────────────────────────────────────
            const SEARCH_URL = '{{ route('admin.absen-manual.api.search-siswa') }}';
            const CEK_URL = '{{ route('admin.absen-manual.api.cek-poin-alfa-range') }}';
            const HAPUS_URL = '{{ route('admin.absen-manual.api.hapus-poin-alfa-range') }}';
            const BULK_URL = '{{ route('admin.absen-manual.izin.bulk') }}';
            const CSRF = '{{ csrf_token() }}';

            // ── DOM refs ──────────────────────────────────────────────────────────────
            const bulkToggle = document.getElementById('bulkToggle');
            const bulkToggleWrap = document.getElementById('bulkToggleWrap');
            const panelTunggal = document.getElementById('panelSiswaTunggal');
            const panelMassal = document.getElementById('panelSiswaMassal');
            const kelasSelect = document.getElementById('kelasSelect');
            const siswaSelect = document.getElementById('siswaSelect');
            const siswaPreview = document.getElementById('siswaPreview');
            const kelasMassal = document.getElementById('kelasMassalSelect');
            const searchMassal = document.getElementById('searchMassal');
            const siswaMassalList = document.getElementById('siswaMassalList');
            const selectedChipsWrap = document.getElementById('selectedChipsWrap');
            const selectedChips = document.getElementById('selectedChips');
            const siswaIdsHidden = document.getElementById('siswaIdsHidden');
            const massalCountBadge = document.getElementById('massalCountBadge');
            const pklNotice = document.getElementById('pklNotice');
            const isMassal = document.getElementById('isMassal');
            const tMulai = document.getElementById('inputTanggalMulai');
            const tSampai = document.getElementById('inputTanggalSampai');
            const izinForm = document.getElementById('izinForm');
            const btnBuatIzin = document.getElementById('btnBuatIzin');
            const btnLabel = document.getElementById('btnLabel');
            const buktiWrap = document.getElementById('buktiWrap');
            const overlayKonf = document.getElementById('overlayKonfirmasi');
            const konfDetail = document.getElementById('konfirmasiDetail');
            const btnKonfBatal = document.getElementById('btnKonfBatal');
            const btnKonfOk = document.getElementById('btnKonfOk');
            const btnPilihSemua = document.getElementById('btnPilihSemua');
            const btnBatalSemua = document.getElementById('btnBatalSemua');

            // ── State ─────────────────────────────────────────────────────────────────
            const selectedSiswa = new Map(); // id → {id, nama, nis, kelas}
            let allSiswaData = []; // cache hasil fetch
            let searchTimer = null;

            // ═════════════════════════════════════════════════════════════════════════
            // 1. Toggle Massal / Tunggal
            // ═════════════════════════════════════════════════════════════════════════
            bulkToggleWrap.addEventListener('click', function(e) {
                if (e.target === bulkToggle) return; // handled by input change
                bulkToggle.checked = !bulkToggle.checked;
                bulkToggle.dispatchEvent(new Event('change'));
            });

            bulkToggle.addEventListener('change', function() {
                const isBulk = this.checked;
                bulkToggleWrap.classList.toggle('active', isBulk);
                panelTunggal.style.display = isBulk ? 'none' : 'block';
                panelMassal.style.display = isBulk ? 'block' : 'none';
                isMassal.value = isBulk ? '1' : '0';
                btnLabel.textContent = isBulk ? 'Buat Izin Massal' : 'Buat & Setujui Izin';

                // Surat hanya untuk tunggal
                if (buktiWrap) buktiWrap.style.display = isBulk ? 'none' : 'block';

                // Reset siswa single selection required
                const siswaInput = document.querySelector('select[name=siswa_id]');
                if (siswaInput) siswaInput.required = !isBulk;
            });

            // ═════════════════════════════════════════════════════════════════════════
            // 2. Siswa Tunggal — dropdown per kelas
            // ═════════════════════════════════════════════════════════════════════════
            kelasSelect.addEventListener('change', function() {
                const kelasId = this.value;
                siswaSelect.innerHTML = '<option value="">Memuat...</option>';
                siswaPreview.style.display = 'none';
                if (!kelasId) {
                    siswaSelect.innerHTML = '<option value="">— Pilih Siswa —</option>';
                    return;
                }

                fetch(`${SEARCH_URL}?kelas_id=${kelasId}&q=`)
                    .then(r => r.json())
                    .then(data => {
                        siswaSelect.innerHTML = '<option value="">— Pilih Siswa —</option>';
                        data.forEach(s => {
                            const o = document.createElement('option');
                            o.value = s.id;
                            o.textContent = s.text;
                            siswaSelect.appendChild(o);
                        });
                    });
            });

            siswaSelect.addEventListener('change', function() {
                const o = this.options[this.selectedIndex];
                if (o && o.value) {
                    siswaPreview.style.display = 'block';
                    siswaPreview.innerHTML =
                        `<i class="fas fa-user" style="color:#16a34a;margin-right:6px;"></i><strong>${o.text}</strong>`;
                } else {
                    siswaPreview.style.display = 'none';
                }
            });

            // ═════════════════════════════════════════════════════════════════════════
            // 3. Siswa Massal — search + checkbox list
            // ═════════════════════════════════════════════════════════════════════════
            function fetchSiswaMassal() {
                const kelasId = kelasMassal.value;
                const q = searchMassal.value.trim();
                if (!kelasId && q.length < 2) {
                    siswaMassalList.innerHTML =
                        '<div style="padding:16px;text-align:center;color:#94a3b8;font-size:.8rem;"><i class="fas fa-arrow-up" style="display:block;margin-bottom:4px;opacity:.4;"></i>Pilih kelas atau ketik minimal 2 karakter</div>';
                    return;
                }

                siswaMassalList.innerHTML =
                    '<div style="padding:16px;text-align:center;color:#94a3b8;font-size:.8rem;"><i class="fas fa-spinner fa-spin"></i> Memuat...</div>';

                const url = `${SEARCH_URL}?kelas_id=${kelasId}&q=${encodeURIComponent(q)}`;
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        allSiswaData = data;
                        renderSiswaList(data);
                    })
                    .catch(() => {
                        siswaMassalList.innerHTML =
                            '<div style="padding:16px;text-align:center;color:#dc2626;font-size:.8rem;">Gagal memuat data siswa.</div>';
                    });
            }

            function renderSiswaList(data) {
                if (!data.length) {
                    siswaMassalList.innerHTML =
                        '<div style="padding:16px;text-align:center;color:#94a3b8;font-size:.8rem;">Tidak ada siswa ditemukan.</div>';
                    return;
                }

                siswaMassalList.innerHTML = data.map(s => `
            <div class="siswa-list-item${selectedSiswa.has(String(s.id)) ? ' selected' : ''}"
                 data-id="${s.id}" data-nama="${s.text}" data-nis="${s.nis}" data-kelas="${s.kelas}">
                <input type="checkbox" ${selectedSiswa.has(String(s.id)) ? 'checked' : ''} value="${s.id}">
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:700;font-size:.82rem;">${s.text.split('(')[0].trim()}</div>
                    <div style="font-size:.7rem;color:#64748b;">${s.nis} &nbsp;·&nbsp; ${s.kelas}</div>
                </div>
            </div>
        `).join('');

                // Attach listeners
                siswaMassalList.querySelectorAll('.siswa-list-item').forEach(item => {
                    item.addEventListener('click', function(e) {
                        const chk = this.querySelector('input[type=checkbox]');
                        if (e.target !== chk) chk.checked = !chk.checked;
                        toggleSiswa(this, chk.checked);
                    });
                });
            }

            function toggleSiswa(item, checked) {
                const id = String(item.dataset.id);
                const nama = item.dataset.nama;
                if (checked) {
                    selectedSiswa.set(id, {
                        id,
                        nama
                    });
                    item.classList.add('selected');
                } else {
                    selectedSiswa.delete(id);
                    item.classList.remove('selected');
                }
                item.querySelector('input[type=checkbox]').checked = checked;
                updateChips();
            }

            function updateChips() {
                const n = selectedSiswa.size;
                massalCountBadge.textContent = `${n} dipilih`;

                if (n === 0) {
                    selectedChipsWrap.style.display = 'none';
                    return;
                }
                selectedChipsWrap.style.display = 'block';
                selectedChips.innerHTML = [...selectedSiswa.values()].map(s => `
            <span class="siswa-chip">
                <i class="fas fa-user" style="font-size:.6rem;"></i>
                ${s.nama.split('(')[0].trim()}
                <button type="button" onclick="removeSiswa('${s.id}')" title="Hapus">×</button>
            </span>
        `).join('');
            }

            window.removeSiswa = function(id) {
                selectedSiswa.delete(String(id));
                // Uncheck in list if visible
                const item = siswaMassalList.querySelector(`.siswa-list-item[data-id="${id}"]`);
                if (item) {
                    item.classList.remove('selected');
                    item.querySelector('input[type=checkbox]').checked = false;
                }
                updateChips();
            };

            // Pilih semua yang terlihat
            btnPilihSemua.addEventListener('click', function() {
                siswaMassalList.querySelectorAll('.siswa-list-item').forEach(item => {
                    item.querySelector('input[type=checkbox]').checked = true;
                    toggleSiswa(item, true);
                });
            });

            // Batal semua
            btnBatalSemua.addEventListener('click', function() {
                selectedSiswa.clear();
                siswaMassalList.querySelectorAll('.siswa-list-item').forEach(item => {
                    item.classList.remove('selected');
                    item.querySelector('input[type=checkbox]').checked = false;
                });
                updateChips();
            });

            kelasMassal.addEventListener('change', fetchSiswaMassal);
            searchMassal.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(fetchSiswaMassal, 350);
            });

            // ═════════════════════════════════════════════════════════════════════════
            // 4. Jenis izin → PKL notice
            // ═════════════════════════════════════════════════════════════════════════
            document.querySelectorAll('input[name=jenis]').forEach(r => {
                r.addEventListener('change', function() {
                    pklNotice.style.display = this.value === 'pkl' ? 'block' : 'none';
                });
            });

            // ═════════════════════════════════════════════════════════════════════════
            // 5. Sinkronisasi tanggal
            // ═════════════════════════════════════════════════════════════════════════
            if (tMulai && tSampai) {
                tMulai.addEventListener('change', function() {
                    if (!tSampai.value || tSampai.value < this.value) tSampai.value = this.value;
                    tSampai.min = this.value;
                });
                if (tMulai.value) tSampai.min = tMulai.value;
            }

            // ═════════════════════════════════════════════════════════════════════════
            // 6. Tombol Buat Izin — validasi → cek poin → modal konfirmasi
            // ═════════════════════════════════════════════════════════════════════════
            btnBuatIzin.addEventListener('click', function() {
                const bulk = bulkToggle.checked;
                const jenis = document.querySelector('input[name=jenis]:checked')?.value;
                const mulai = tMulai.value;
                const sampai = tSampai.value;
                const alasan = document.querySelector('textarea[name=alasan]').value.trim();

                // Validasi umum
                if (!jenis) {
                    alert('Pilih jenis izin terlebih dahulu.');
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
                if (!alasan) {
                    alert('Alasan izin wajib diisi.');
                    return;
                }

                if (bulk) {
                    // Validasi massal
                    if (selectedSiswa.size === 0) {
                        alert('Pilih minimal satu siswa.');
                        return;
                    }
                } else {
                    // Validasi tunggal
                    if (!siswaSelect.value) {
                        alert('Pilih siswa terlebih dahulu.');
                        return;
                    }
                }

                // Tampilkan modal, cek poin di background
                overlayKonf.classList.add('open');
                document.body.style.overflow = 'hidden';
                konfDetail.innerHTML =
                    '<i class="fas fa-spinner fa-spin"></i> Memeriksa poin alfa pada rentang tanggal...';
                btnKonfOk.disabled = true;

                // Untuk cek, gunakan siswa pertama (representatif)
                const cekSiswaId = bulk ? [...selectedSiswa.keys()][0] : siswaSelect.value;

                fetch(`${CEK_URL}?siswa_id=${cekSiswaId}&tanggal_mulai=${mulai}&tanggal_sampai=${sampai}`)
                    .then(r => r.json())
                    .then(data => {
                        btnKonfOk.disabled = false;
                        const jumlah = data.jumlah || 0;
                        const siswaCount = bulk ? selectedSiswa.size : 1;

                        if (jumlah > 0) {
                            konfDetail.innerHTML = `
                        <div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">
                            <i class="fas fa-exclamation-circle" style="color:#dc2626;flex-shrink:0;"></i>
                            <strong style="color:#dc2626;">Ditemukan poin pelanggaran alfa pada periode ini</strong>
                        </div>
                        <div style="font-size:.72rem;color:#64748b;margin-bottom:6px;">
                            <i class="fas fa-calendar-alt"></i> ${mulai} s/d ${sampai}
                            ${bulk ? `&nbsp;·&nbsp; <i class="fas fa-users"></i> ${siswaCount} siswa` : ''}
                        </div>
                        <div style="background:#fef3c7;border-radius:6px;padding:7px 10px;font-size:.78rem;color:#92400e;">
                            <i class="fas fa-trash-alt"></i>
                            Poin alfa otomatis${bulk ? ' untuk semua siswa terpilih' : ''} akan dihapus setelah Konfirmasi.
                        </div>`;
                        } else {
                            konfDetail.innerHTML = `
                        <div style="display:flex;gap:8px;align-items:center;">
                            <i class="fas fa-check-circle" style="color:#16a34a;flex-shrink:0;"></i>
                            <div>
                                Tidak ada poin pelanggaran alfa pada rentang tanggal ini.
                                ${bulk ? `<div style="font-size:.72rem;color:#64748b;margin-top:2px;"><i class="fas fa-users"></i> ${siswaCount} siswa · ${mulai} s/d ${sampai}</div>` : ''}
                            </div>
                        </div>`;
                        }
                    })
                    .catch(() => {
                        btnKonfOk.disabled = false;
                        konfDetail.innerHTML =
                            '<span style="color:#94a3b8;"><i class="fas fa-info-circle"></i> Gagal memeriksa poin. Lanjut simpan.</span>';
                    });
            });

            // ═════════════════════════════════════════════════════════════════════════
            // 7. Modal konfirmasi — Batal / Konfirmasi
            // ═════════════════════════════════════════════════════════════════════════
            function tutupKonf() {
                overlayKonf.classList.remove('open');
                document.body.style.overflow = '';
            }

            btnKonfBatal.addEventListener('click', tutupKonf);
            overlayKonf.addEventListener('click', function(e) {
                if (e.target === overlayKonf) tutupKonf();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlayKonf.classList.contains('open')) tutupKonf();
            });

            btnKonfOk.addEventListener('click', function() {
                btnKonfOk.disabled = true;
                btnKonfOk.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

                const bulk = bulkToggle.checked;
                const mulai = tMulai.value;
                const sampai = tSampai.value;

                if (bulk) {
                    // ── Mode massal: kirim via fetch ke bulk endpoint ──────────────
                    const jenis = document.querySelector('input[name=jenis]:checked')?.value;
                    const alasan = document.querySelector('textarea[name=alasan]').value.trim();

                    fetch(BULK_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                siswa_ids: [...selectedSiswa.keys()],
                                jenis,
                                tanggal_mulai: mulai,
                                tanggal_sampai: sampai,
                                alasan,
                                hapus_poin: true,
                            }),
                        })
                        .then(r => r.json())
                        .then(data => {
                            tutupKonf();
                            let msg = `Berhasil: ${data.berhasil} izin dibuat.`;
                            if (data.skipped > 0) msg += ` ${data.skipped} dilewati (sudah ada izin).`;
                            if (data.poin_dihapus > 0) msg +=
                            ` ${data.poin_dihapus} poin alfa dihapus.`;
                            if (data.gagal > 0) msg += ` ${data.gagal} gagal.`;

                            if (data.berhasil > 0) {
                                showToast(msg, '#0d9488');
                                setTimeout(() => {
                                    window.location.href =
                                        '{{ route('admin.absen-manual.index', ['tanggal' => '__TGL__']) }}'
                                        .replace('__TGL__', mulai);
                                }, 1800);
                            } else {
                                btnKonfOk.disabled = false;
                                btnKonfOk.innerHTML = '<i class="fas fa-check-double"></i> Konfirmasi';
                                alert(msg + '\n' + (data.errors?.join('\n') || ''));
                            }
                        })
                        .catch(() => {
                            btnKonfOk.disabled = false;
                            btnKonfOk.innerHTML = '<i class="fas fa-check-double"></i> Konfirmasi';
                            alert('Terjadi kesalahan. Silakan coba lagi.');
                        });

                } else {
                    // ── Mode tunggal: hapus poin alfa lalu submit form biasa ───────
                    const siswaId = siswaSelect.value;
                    fetch(HAPUS_URL, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': CSRF
                            },
                            body: JSON.stringify({
                                siswa_id: siswaId,
                                tanggal_mulai: mulai,
                                tanggal_sampai: sampai
                            }),
                        })
                        .then(() => {
                            izinForm.submit();
                        })
                        .catch(() => {
                            izinForm.submit();
                        });
                }
            });

            // ═════════════════════════════════════════════════════════════════════════
            // 8. Helper: toast notification
            // ═════════════════════════════════════════════════════════════════════════
            function showToast(msg, color) {
                const t = document.createElement('div');
                t.style.cssText =
                    `position:fixed;top:80px;right:16px;background:${color};color:#fff;padding:12px 18px;border-radius:12px;font-size:.85rem;font-weight:700;z-index:99999;box-shadow:0 4px 20px rgba(0,0,0,.2);max-width:320px;line-height:1.5;`;
                t.innerHTML = `<i class="fas fa-check-circle" style="margin-right:6px;"></i>${msg}`;
                document.body.appendChild(t);
                setTimeout(() => t.remove(), 4000);
            }
        });

        // Preview file bukti
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
            document.addEventListener('DOMContentLoaded', function() {
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
            });
        @endif
    </script>
@endpush
