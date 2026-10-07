@extends('layouts.app')

@section('title', 'Buat Surat Panggilan Orang Tua')

@push('styles')
    @include('components.event-styles')
    @include('admin.tatib._styles')
    <style>
        .sp-section-title {
            font-size: .7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: .05em;
            margin: 18px 0 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #eef2f7;
        }

        .sp-form-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        @media (max-width:640px) {
            .sp-form-grid-3 {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width:641px) and (max-width:900px) {
            .sp-form-grid-3 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        /* Panggilan badge */
        .panggilan-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: .88rem;
            color: #0f172a;
            min-width: 120px;
        }

        .panggilan-badge.loading {
            color: #94a3b8;
        }

        .panggilan-badge .badge-number {
            font-size: 1.1rem;
            font-weight: 800;
            color: #c2410c;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 6px;
            padding: 2px 10px;
            min-width: 36px;
            text-align: center;
        }

        .panggilan-info {
            font-size: .72rem;
            color: #64748b;
            margin-top: 5px;
        }

        .panggilan-info.warn {
            color: #b45309;
        }

        /* Siswa search */
        .siswa-search-wrap {
            position: relative;
        }

        .siswa-search-wrap .clear-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 14px;
            padding: 2px 4px;
            display: none;
        }

        .siswa-search-wrap .clear-btn:hover {
            color: #475569;
        }

        /* Preview poin tombol */
        .btn-preview-poin {
            display: none;
            align-items: center;
            gap: 7px;
            margin-top: 10px;
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: .8rem;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-preview-poin:hover {
            background: #dbeafe;
        }

        .btn-preview-poin.visible {
            display: inline-flex;
        }

        /* Modal overlay */
        .sp-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            z-index: 9998;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .sp-modal-overlay.open {
            display: flex;
        }

        .sp-modal {
            background: #fff;
            border-radius: 14px;
            width: 100%;
            max-width: 760px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
        }

        .sp-modal-head {
            padding: 16px 20px;
            border-bottom: 1px solid #eef2f7;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sp-modal-head h3 {
            font-size: .98rem;
            font-weight: 800;
            margin: 0;
        }

        .sp-modal-close {
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            font-size: 1.1rem;
            padding: 4px 8px;
            border-radius: 6px;
        }

        .sp-modal-close:hover {
            background: #f1f5f9;
            color: #475569;
        }

        .sp-modal-body {
            padding: 16px 20px;
            overflow-y: auto;
            flex: 1;
        }

        .sp-modal-loading {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
            font-size: .88rem;
        }

        .sp-modal-loading i {
            display: block;
            font-size: 1.8rem;
            margin-bottom: 10px;
            opacity: .4;
        }

        /* Poin section dalam modal */
        .poin-section-title {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: .05em;
            margin: 14px 0 8px;
        }

        .poin-section-title:first-child {
            margin-top: 0;
        }

        .poin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .8rem;
        }

        .poin-table th {
            padding: 7px 10px;
            text-align: left;
            font-size: .68rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .poin-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .poin-table tbody tr:hover td {
            background: #f8fafc;
        }

        .poin-table tbody tr:last-child td {
            border-bottom: none;
        }

        .poin-badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 99px;
            font-weight: 700;
            font-size: .78rem;
        }

        .poin-badge.red {
            background: #fee2e2;
            color: #b91c1c;
        }

        .poin-badge.green {
            background: #dcfce7;
            color: #15803d;
        }

        .poin-total {
            text-align: right;
            font-weight: 800;
            font-size: .84rem;
            padding: 8px 10px;
            border-top: 2px solid #e2e8f0;
            color: #0f172a;
        }

        .poin-empty {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-size: .82rem;
        }

        .poin-empty i {
            display: block;
            font-size: 1.3rem;
            margin-bottom: 6px;
            opacity: .35;
        }

        .modal-siswa-info {
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 12px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            font-size: .82rem;
        }

        .modal-siswa-info span {
            color: #64748b;
        }

        .modal-siswa-info strong {
            color: #0f172a;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">

        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-envelope-open-text"></i> Buat Surat Panggilan Orang Tua</h2>
            <p>Tahun ajaran {{ $tahunAjaran }}</p>
        </div>

        @if ($errors->any())
            <div
                style="background:#fee2e2;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;margin-bottom:12px;">
                <ul style="margin:0;padding-left:16px;color:#b91c1c;font-size:.82rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.surat-panggilan.store') }}" id="sp-form">
            @csrf

            {{-- BAGIAN 1: DATA SISWA --}}
            <div class="tatib-form-card">
                <div class="sp-section-title"><i class="fas fa-user-graduate"></i> Data Siswa</div>
                <input type="hidden" name="tahun_ajaran" id="tahun_ajaran_input"
                    value="{{ old('tahun_ajaran', $tahunAjaran) }}">

                <div class="tatib-form-grid">
                    {{-- Siswa Search --}}
                    <div class="tatib-field full">
                        <label>Siswa <span style="color:#be123c;">*</span></label>
                        <input type="hidden" name="siswa_id" id="siswa_id_input"
                            value="{{ old('siswa_id', $siswa?->id ?? '') }}">

                        <div class="siswa-search-wrap">
                            <input type="text" id="siswa_search_input"
                                class="tatib-input @error('siswa_id') is-invalid @enderror"
                                placeholder="🔍  Ketik nama atau NIS siswa..." autocomplete="off"
                                value="{{ old('_siswa_text', $siswa ? $siswa->nama_lengkap . ' — ' . ($siswa->kelas?->nama_kelas ?? '-') : '') }}"
                                style="padding-right:34px;">
                            <button type="button" id="siswa_clear_btn" class="clear-btn" title="Hapus pilihan">
                                <i class="fas fa-times-circle"></i>
                            </button>
                            <div id="siswa_dropdown"
                                style="
                            display:none;position:absolute;top:100%;left:0;right:0;
                            background:#fff;border:1px solid #d1d5db;border-top:none;
                            border-radius:0 0 8px 8px;max-height:260px;overflow-y:auto;
                            z-index:999;box-shadow:0 4px 12px rgba(0,0,0,.12);
                        ">
                            </div>
                        </div>
                        @error('siswa_id')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror

                        {{-- Tombol preview poin — muncul setelah siswa dipilih --}}
                        <button type="button" id="btn_preview_poin" class="btn-preview-poin">
                            <i class="fas fa-chart-bar"></i> Lihat Rekap Poin Siswa
                        </button>
                    </div>

                    {{-- Panggilan Ke (otomatis) --}}
                    <div class="tatib-field">
                        <label>Panggilan ke-</label>
                        <input type="hidden" name="panggilan_ke" id="panggilan_ke_input"
                            value="{{ old('panggilan_ke', 1) }}">
                        <div class="panggilan-badge loading" id="panggilan_badge">
                            <span>Ke-</span>
                            <span class="badge-number" id="panggilan_number">{{ old('panggilan_ke', '…') }}</span>
                        </div>
                        <div class="panggilan-info" id="panggilan_info">Pilih siswa terlebih dahulu</div>
                        @error('panggilan_ke')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Nomor Surat --}}
                    <div class="tatib-field">
                        <label>Nomor Surat <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                        <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" class="tatib-input"
                            placeholder="cth. 421.2/SP/123/2025" maxlength="100">
                        @error('nomor_surat')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: WAKTU & TEMPAT --}}
            <div class="tatib-form-card">
                <div class="sp-section-title"><i class="fas fa-calendar-alt"></i> Waktu & Tempat Pemanggilan</div>
                <div class="sp-form-grid-3">
                    <div class="tatib-field">
                        <label>Hari <span style="color:#be123c;">*</span></label>
                        <select name="hari" class="tatib-select" id="hari_select" required>
                            @php
                                $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                $selectedHari = old('hari', now()->translatedFormat('l'));
                            @endphp
                            @foreach ($hariList as $h)
                                <option value="{{ $h }}" @selected($selectedHari === $h)>{{ $h }}
                                </option>
                            @endforeach
                        </select>
                        @error('hari')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="tatib-field">
                        <label>Tanggal Acara <span style="color:#be123c;">*</span></label>
                        <input type="date" name="tanggal_acara" id="tanggal_acara_input"
                            value="{{ old('tanggal_acara', now()->format('Y-m-d')) }}" class="tatib-input" required>
                        @error('tanggal_acara')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="tatib-field">
                        <label>Waktu <span style="color:#be123c;">*</span></label>
                        <input type="text" name="waktu" value="{{ old('waktu', '08:00 s.d Selesai') }}"
                            class="tatib-input" placeholder="cth. 08:00 s.d Selesai" maxlength="40" required>
                        @error('waktu')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="tatib-field">
                        <label>Lokasi / Tempat <span style="color:#be123c;">*</span></label>
                        <input type="text" name="lokasi" value="{{ old('lokasi', 'Ruang BK/BP') }}" class="tatib-input"
                            placeholder="cth. Ruang BK/BP" maxlength="150" required>
                        @error('lokasi')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="tatib-field">
                        <label>Menemui <span style="color:#94a3b8;font-weight:400;">(opsional)</span></label>
                        <input type="text" name="menemui" value="{{ old('menemui', 'BK') }}" class="tatib-input"
                            placeholder="cth. BK / Wali Kelas" maxlength="100">
                        @error('menemui')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="tatib-field">
                        <label>Tanggal Surat <span style="color:#be123c;">*</span></label>
                        <input type="date" name="tanggal_surat"
                            value="{{ old('tanggal_surat', now()->format('Y-m-d')) }}" class="tatib-input" required>
                        @error('tanggal_surat')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="tatib-field" style="margin-top:12px;">
                    <label>Keperluan / Agenda</label>
                    <textarea name="keperluan" class="tatib-textarea" placeholder="Isi keperluan pemanggilan orang tua..."
                        maxlength="1000">{{ old('keperluan', 'Membahas ketertiban menaati aturan anak bapak/ibu.') }}</textarea>
                    @error('keperluan')
                        <span class="tatib-error">{{ $message }}</span>
                    @enderror
                </div>
                <div style="margin-top:12px;display:flex;align-items:center;gap:10px;">
                    <input type="hidden" name="dengan_materai" value="0">
                    <input type="checkbox" name="dengan_materai" id="dengan_materai" value="1"
                        {{ old('dengan_materai', '1') == '1' ? 'checked' : '' }}
                        style="width:16px;height:16px;cursor:pointer;">
                    <label for="dengan_materai" style="cursor:pointer;font-size:.84rem;color:#334155;">
                        Sertakan keterangan membawa materai dalam surat
                    </label>
                </div>
            </div>

            {{-- BAGIAN 3: PENANDATANGAN --}}
            <div class="tatib-form-card">
                <div class="sp-section-title"><i class="fas fa-signature"></i> Pihak Penandatangan</div>
                <div class="tatib-field">
                    <label>Penandatangan <span style="color:#be123c;">*</span></label>
                    <select name="gtk_id" class="tatib-select" required>
                        <option value="">— Pilih penandatangan —</option>
                        @foreach ($gtk as $g)
                            <option value="{{ $g->id }}" @selected(old('gtk_id') == $g->id)>
                                {{ $g->nama_lengkap }}
                                {{ $g->jabatan ? ' — ' . $g->jabatan : '' }}
                                {{ $g->nip ? ' (NIP. ' . $g->nip . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <span style="font-size:.72rem;color:#64748b;margin-top:3px;">
                        Data diambil dari database GTK. Penandatangan bisa berupa Kepala Sekolah, Waka Kesiswaan, Waka
                        Sekolah, dll.
                    </span>
                    @error('gtk_id')
                        <span class="tatib-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="tatib-actions">
                <a href="{{ route('admin.surat-panggilan.index') }}" class="tatib-btn tatib-btn-soft">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" id="btn-submit" class="tatib-btn tatib-btn-primary">
                    <i class="fas fa-print"></i> Buat &amp; Pratinjau Surat
                </button>
            </div>
        </form>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL PREVIEW POIN                                               --}}
    {{-- ================================================================ --}}
    <div class="sp-modal-overlay" id="poin_modal_overlay">
        <div class="sp-modal">
            <div class="sp-modal-head">
                <div>
                    <h3><i class="fas fa-chart-bar" style="color:#1d4ed8;margin-right:7px;"></i>Rekap Poin Siswa</h3>
                    <div style="font-size:.75rem;color:#64748b;margin-top:2px;" id="modal_siswa_subtitle">—</div>
                </div>
                <button type="button" class="sp-modal-close" id="poin_modal_close" title="Tutup">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="sp-modal-body" id="poin_modal_body">
                <div class="sp-modal-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    Memuat data poin…
                </div>
            </div>
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
            /* ====================================================================
             * CONFIG
             * ==================================================================*/
            const API_SISWA = '{{ route('admin.tatib.siswa-search') }}';
            const API_PANGGILAN = '{{ route('admin.surat-panggilan.api.panggilan-ke') }}';
            const API_POIN = '{{ route('admin.surat-panggilan.api.preview-poin') }}';
            const TAHUN_AJARAN = document.getElementById('tahun_ajaran_input').value;

            /* ====================================================================
             * ELEMEN
             * ==================================================================*/
            const searchInput = document.getElementById('siswa_search_input');
            const hiddenSiswa = document.getElementById('siswa_id_input');
            const dropdown = document.getElementById('siswa_dropdown');
            const clearBtn = document.getElementById('siswa_clear_btn');

            const panggilanInput = document.getElementById('panggilan_ke_input');
            const panggilanBadge = document.getElementById('panggilan_badge');
            const panggilanNumber = document.getElementById('panggilan_number');
            const panggilanInfo = document.getElementById('panggilan_info');

            const tglAcara = document.getElementById('tanggal_acara_input');
            const hariSel = document.getElementById('hari_select');

            const btnPreview = document.getElementById('btn_preview_poin');
            const modalOverlay = document.getElementById('poin_modal_overlay');
            const modalClose = document.getElementById('poin_modal_close');
            const modalBody = document.getElementById('poin_modal_body');
            const modalSubtitle = document.getElementById('modal_siswa_subtitle');

            /* ====================================================================
             * AUTOCOMPLETE SISWA
             * ==================================================================*/
            let debounce = null;
            let lastQ = '';

            function renderDropdown(items) {
                dropdown.innerHTML = '';
                if (!items || items.length === 0) {
                    dropdown.innerHTML =
                        '<div style="padding:12px 14px;color:#888;font-size:13px;">' +
                        '<i class="fas fa-search" style="margin-right:6px;opacity:.5;"></i>' +
                        'Siswa tidak ditemukan</div>';
                    dropdown.style.display = 'block';
                    return;
                }
                items.forEach(function(item) {
                    const row = document.createElement('div');
                    row.style.cssText =
                        'padding:10px 14px;cursor:pointer;font-size:13px;' +
                        'border-bottom:1px solid #f3f4f6;display:flex;align-items:center;gap:10px;';
                    const ic = document.createElement('span');
                    ic.innerHTML =
                        '<i class="fas fa-user-graduate" style="color:#94a3b8;font-size:12px;"></i>';
                    const tx = document.createElement('span');
                    tx.textContent = item.text;
                    row.appendChild(ic);
                    row.appendChild(tx);
                    row.addEventListener('mouseenter', function() {
                        row.style.background = '#f0f9ff';
                    });
                    row.addEventListener('mouseleave', function() {
                        row.style.background = '';
                    });
                    row.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        selectSiswa(item.id, item.text);
                    });
                    dropdown.appendChild(row);
                });
                dropdown.style.display = 'block';
            }

            function selectSiswa(id, label) {
                hiddenSiswa.value = id;
                searchInput.value = label;
                dropdown.style.display = 'none';
                clearBtn.style.display = 'block';
                btnPreview.classList.add('visible');
                fetchPanggilanKe(id);
            }

            function clearSiswa() {
                hiddenSiswa.value = '';
                searchInput.value = '';
                dropdown.style.display = 'none';
                clearBtn.style.display = 'none';
                btnPreview.classList.remove('visible');
                lastQ = '';
                resetPanggilan();
            }

            function fetchSiswa(q) {
                if (q === lastQ) return;
                lastQ = q;
                if (q.length < 1) {
                    dropdown.style.display = 'none';
                    return;
                }
                fetch(API_SISWA + '?q=' + encodeURIComponent(q) + '&jenis=pelanggaran')
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(d) {
                        renderDropdown(d.results || []);
                    })
                    .catch(function() {
                        dropdown.innerHTML = '<div style="padding:10px 14px;color:#e00;font-size:13px;">' +
                            '<i class="fas fa-exclamation-circle"></i> Gagal memuat data</div>';
                        dropdown.style.display = 'block';
                    });
            }

            searchInput.addEventListener('input', function() {
                hiddenSiswa.value = '';
                btnPreview.classList.remove('visible');
                clearBtn.style.display = this.value ? 'block' : 'none';
                clearTimeout(debounce);
                debounce = setTimeout(function() {
                    fetchSiswa(searchInput.value.trim());
                }, 280);
            });
            searchInput.addEventListener('blur', function() {
                setTimeout(function() {
                    dropdown.style.display = 'none';
                }, 200);
            });
            searchInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 1 && dropdown.innerHTML !== '') dropdown.style.display =
                    'block';
            });
            clearBtn.addEventListener('click', clearSiswa);
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) dropdown.style
                    .display = 'none';
            });

            // Jika siswa sudah terisi saat page load (dari query param)
            if (hiddenSiswa.value) {
                clearBtn.style.display = 'block';
                btnPreview.classList.add('visible');
                fetchPanggilanKe(hiddenSiswa.value);
            }

            /* ====================================================================
             * PANGGILAN KE — OTOMATIS
             * ==================================================================*/
            function resetPanggilan() {
                panggilanNumber.textContent = '…';
                panggilanInput.value = 1;
                panggilanBadge.classList.add('loading');
                panggilanInfo.textContent = 'Pilih siswa terlebih dahulu';
                panggilanInfo.classList.remove('warn');
            }

            function setPanggilan(ke) {
                panggilanNumber.textContent = ke;
                panggilanInput.value = ke;
                panggilanBadge.classList.remove('loading');
                if (ke === 1) {
                    panggilanInfo.textContent = 'Panggilan pertama di tahun ajaran ini';
                    panggilanInfo.classList.remove('warn');
                } else {
                    panggilanInfo.textContent = 'Sudah ada ' + (ke - 1) + ' surat sebelumnya di TA ' + TAHUN_AJARAN;
                    panggilanInfo.classList.add('warn');
                }
            }

            function fetchPanggilanKe(siswaId) {
                panggilanNumber.textContent = '…';
                panggilanBadge.classList.add('loading');
                panggilanInfo.textContent = 'Menghitung…';
                fetch(API_PANGGILAN + '?siswa_id=' + encodeURIComponent(siswaId) + '&tahun_ajaran=' +
                        encodeURIComponent(TAHUN_AJARAN))
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(d) {
                        if (d.panggilan_ke !== undefined) setPanggilan(d.panggilan_ke);
                        else resetPanggilan();
                    })
                    .catch(function() {
                        panggilanInfo.textContent = 'Gagal memuat';
                        panggilanInfo.classList.remove('warn');
                    });
            }

            /* ====================================================================
             * AUTO-HARI DARI TANGGAL
             * ==================================================================*/
            if (tglAcara && hariSel) {
                tglAcara.addEventListener('change', function() {
                    const d = new Date(this.value + 'T00:00:00');
                    if (isNaN(d)) return;
                    const nama = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][d
                        .getDay()
                    ];
                    for (let i = 0; i < hariSel.options.length; i++) {
                        if (hariSel.options[i].value === nama) {
                            hariSel.selectedIndex = i;
                            break;
                        }
                    }
                });
            }

            /* ====================================================================
             * GUARD SUBMIT
             * ==================================================================*/
            document.getElementById('sp-form').addEventListener('submit', function(e) {
                if (!hiddenSiswa.value) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.style.borderColor = '#be123c';
                    setTimeout(function() {
                        searchInput.style.borderColor = '';
                    }, 2000);
                }
            });

            /* ====================================================================
             * MODAL PREVIEW POIN
             * ==================================================================*/
            function buildModalContent(data) {
                let html = '';

                // Info siswa
                html += '<div class="modal-siswa-info">' +
                    '<div><span>Nama&nbsp;</span><br><strong>' + escHtml(data.siswa.nama) + '</strong></div>' +
                    '<div><span>NIS&nbsp;</span><br><strong>' + escHtml(data.siswa.nis) + '</strong></div>' +
                    '<div><span>Kelas&nbsp;</span><br><strong>' + escHtml(data.siswa.kelas) + '</strong></div>' +
                    '<div><span>Tahun Ajaran&nbsp;</span><br><strong>' + escHtml(data.tahun_ajaran) +
                    '</strong></div>' +
                    '</div>';

                // ── Pelanggaran ──
                html += '<div class="poin-section-title">' +
                    '<i class="fas fa-exclamation-circle" style="color:#b91c1c;margin-right:5px;"></i>' +
                    'Pelanggaran</div>';

                if (data.pelanggaran.length === 0) {
                    html +=
                        '<div class="poin-empty"><i class="fas fa-inbox"></i>Tidak ada data pelanggaran tahun ajaran ini.</div>';
                } else {
                    html += '<div style="overflow-x:auto;"><table class="poin-table">' +
                        '<thead><tr><th style="width:5%">#</th><th style="width:13%">Tanggal</th>' +
                        '<th>Uraian</th><th>Keterangan</th>' +
                        '<th style="width:8%;text-align:right;">Poin</th></tr></thead><tbody>';
                    data.pelanggaran.forEach(function(row, i) {
                        html += '<tr>' +
                            '<td style="text-align:center;color:#94a3b8;">' + (i + 1) + '</td>' +
                            '<td style="white-space:nowrap;color:#64748b;font-size:.75rem;">' + escHtml(row
                                .tanggal) + '</td>' +
                            '<td>' + escHtml(row.uraian) + '</td>' +
                            '<td style="color:#64748b;font-size:.78rem;">' + escHtml(row.ket) + '</td>' +
                            '<td style="text-align:right;"><span class="poin-badge red">' + row.poin +
                            '</span></td>' +
                            '</tr>';
                    });
                    html += '</tbody></table></div>' +
                        '<div class="poin-total">Total Pelanggaran : ' +
                        '<span class="poin-badge red">' + data.total_pelanggaran + ' poin</span></div>';
                }

                // ── Penghargaan ──
                html += '<div class="poin-section-title" style="margin-top:16px;">' +
                    '<i class="fas fa-award" style="color:#15803d;margin-right:5px;"></i>' +
                    'Penghargaan</div>';

                if (data.penghargaan.length === 0) {
                    html +=
                        '<div class="poin-empty"><i class="fas fa-inbox"></i>Tidak ada data penghargaan tahun ajaran ini.</div>';
                } else {
                    html += '<div style="overflow-x:auto;"><table class="poin-table">' +
                        '<thead><tr><th style="width:5%">#</th><th style="width:13%">Tanggal</th>' +
                        '<th>Uraian</th><th>Keterangan</th>' +
                        '<th style="width:8%;text-align:right;">Poin</th></tr></thead><tbody>';
                    data.penghargaan.forEach(function(row, i) {
                        html += '<tr>' +
                            '<td style="text-align:center;color:#94a3b8;">' + (i + 1) + '</td>' +
                            '<td style="white-space:nowrap;color:#64748b;font-size:.75rem;">' + escHtml(row
                                .tanggal) + '</td>' +
                            '<td>' + escHtml(row.uraian) + '</td>' +
                            '<td style="color:#64748b;font-size:.78rem;">' + escHtml(row.ket) + '</td>' +
                            '<td style="text-align:right;"><span class="poin-badge green">+' + row.poin +
                            '</span></td>' +
                            '</tr>';
                    });
                    html += '</tbody></table></div>' +
                        '<div class="poin-total">Total Penghargaan : ' +
                        '<span class="poin-badge green">+' + data.total_penghargaan + ' poin</span></div>';
                }

                return html;
            }

            function escHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            function openModal() {
                const siswaId = hiddenSiswa.value;
                if (!siswaId) return;

                modalOverlay.classList.add('open');
                document.body.style.overflow = 'hidden';

                // Reset ke loading state
                modalBody.innerHTML =
                    '<div class="sp-modal-loading">' +
                    '<i class="fas fa-spinner fa-spin"></i>Memuat data poin…</div>';
                modalSubtitle.textContent = searchInput.value || '—';

                fetch(API_POIN + '?siswa_id=' + encodeURIComponent(siswaId) +
                        '&tahun_ajaran=' + encodeURIComponent(TAHUN_AJARAN))
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        modalSubtitle.textContent =
                            (data.siswa?.nama || '—') + ' · ' +
                            (data.siswa?.kelas || '—') + ' · TA ' + TAHUN_AJARAN;
                        modalBody.innerHTML = buildModalContent(data);
                    })
                    .catch(function() {
                        modalBody.innerHTML =
                            '<div class="sp-modal-loading" style="color:#b91c1c;">' +
                            '<i class="fas fa-exclamation-circle"></i>Gagal memuat data poin.</div>';
                    });
            }

            function closeModal() {
                modalOverlay.classList.remove('open');
                document.body.style.overflow = '';
            }

            btnPreview.addEventListener('click', openModal);
            modalClose.addEventListener('click', closeModal);
            modalOverlay.addEventListener('click', function(e) {
                if (e.target === modalOverlay) closeModal();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modalOverlay.classList.contains('open')) closeModal();
            });

        });
    </script>
@endpush
