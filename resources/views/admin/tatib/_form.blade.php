@php
    $record = $record ?? null;
    $isPelanggaran = $jenis === 'pelanggaran';
    $tanggalValue = old('tanggal', $record?->tgl?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $selectedSiswa = old('siswa_id', $record?->siswa_id);
    $selectedPasal = old('idpasal', $record?->idpasal);

    // Teks siswa terpilih untuk ditampilkan di input
    $selectedSiswaText = $record?->siswa?->nama ?? ($record?->nama ?? '');

    // Teks pasal terpilih untuk ditampilkan di input
    $selectedPasalItem = $subPasal->firstWhere('idpasal', $selectedPasal);
    $selectedPasalText = $selectedPasalItem ? '[' . $selectedPasalItem->idpasal . '] ' . $selectedPasalItem->pasal : '';

    // Mapping pasal ke JSON — pakai function() biasa agar Blade tidak salah parse fn()
    $pasalJson = $subPasal->map(function ($p) {
        return [
            'id' => $p->idpasal,
            'label' => '[' . $p->idpasal . '] ' . $p->pasal,
            'isi' => $p->pasal,
            'skormin' => (int) $p->skormin,
            'skormax' => (int) $p->skormax,
        ];
    });

    // Tombol notifikasi WA hanya relevan saat CREATE (belum ada record tersimpan)
    $showWaNotif = empty($record);
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    {{-- Data semua pasal dikirim ke JS lewat JSON (filter lokal, tidak perlu AJAX) --}}
    <script id="subpasal-data" type="application/json">
        {!! json_encode($pasalJson) !!}
    </script>

    <div class="tatib-form-card">
        <div class="tatib-form-grid">

            {{-- ─── FIELD: SISWA ─────────────────────────────────────────── --}}
            <div class="tatib-field full" style="position: relative;">
                <label>Siswa</label>

                {{-- Tahun ajaran otomatis --}}
                <input type="hidden" name="tahun_ajaran" value="{{ old('tahun_ajaran', $tahunAjaran ?? now()->year) }}">

                {{-- Hidden input dikirim ke server --}}
                <input type="hidden" name="siswa_id" id="siswa_id_input"
                    value="{{ old('siswa_id', $selectedSiswa ?? '') }}">

                {{-- Input pencarian tampilan --}}
                <input type="text" id="siswa_search_input"
                    class="tatib-input @error('siswa_id') is-invalid @enderror"
                    placeholder="Ketik nama atau NIS siswa..." autocomplete="off"
                    value="{{ old('_siswa_text', $selectedSiswaText) }}">

                {{-- Dropdown hasil pencarian --}}
                <div id="siswa_dropdown"
                    style="
                    display: none;
                    position: absolute;
                    top: 100%; left: 0; right: 0;
                    background: #fff;
                    border: 1px solid #d1d5db;
                    border-top: none;
                    border-radius: 0 0 8px 8px;
                    max-height: 240px;
                    overflow-y: auto;
                    z-index: 999;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                ">
                </div>

                @error('siswa_id')
                    <span class="tatib-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- ─── FIELD: TANGGAL ────────────────────────────────────────── --}}
            <div class="tatib-field">
                <label>Tanggal</label>
                <input type="datetime-local" name="tanggal" value="{{ $tanggalValue }}" class="tatib-input" required>
                @error('tanggal')
                    <span class="tatib-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- ─── FIELD: JENIS PELANGGARAN/PENGHARGAAN (Searchable) ──────── --}}
            <div class="tatib-field full" style="position: relative;">
                <label>Jenis {{ $isPelanggaran ? 'Pelanggaran' : 'Penghargaan' }}</label>

                {{-- Hidden input dikirim ke server --}}
                <input type="hidden" name="idpasal" id="pasal_id_input"
                    value="{{ old('idpasal', $selectedPasal ?? '') }}">

                {{-- Input pencarian tampilan --}}
                <input type="text" id="pasal_search_input" class="tatib-input @error('idpasal') is-invalid @enderror"
                    placeholder="Ketik kode atau nama jenis {{ $isPelanggaran ? 'pelanggaran' : 'penghargaan' }}..."
                    autocomplete="off" value="{{ $selectedPasalText }}">

                {{-- Badge pilihan terpilih --}}
                <div id="pasal_selected_badge"
                    style="
                    display: {{ $selectedPasalText ? 'flex' : 'none' }};
                    align-items: center;
                    gap: 8px;
                    margin-top: 6px;
                    font-size: 12px;
                    color: #2563eb;
                ">
                    <i class="fas fa-tag"></i>
                    <span id="pasal_selected_text">
                        {{ $selectedPasalText
                            ? $selectedPasalText .
                                ' (' .
                                (int) $selectedPasalItem?->skormin .
                                '–' .
                                (int) $selectedPasalItem?->skormax .
                                ' poin)'
                            : '' }}
                    </span>
                    <button type="button" id="pasal_clear_btn"
                        style="background:none; border:none; cursor:pointer; color:#6b7280; font-size:12px; padding:0; margin-left:4px;"
                        title="Hapus pilihan">
                        ✕ hapus
                    </button>
                </div>

                {{-- Info rentang poin --}}
                <div id="poin_rentang_info"
                    style="margin-top: 8px; font-size: 12px; color: #6b7280; display: {{ $selectedPasal ? 'block' : 'none' }};">
                    Rentang poin: <span id="poin_rentang_text">
                        {{ $selectedPasalItem ? (int) $selectedPasalItem->skormin . '–' . (int) $selectedPasalItem->skormax : '' }}
                    </span>
                </div>

                {{-- Dropdown hasil pencarian --}}
                <div id="pasal_dropdown"
                    style="
                    display: none;
                    position: absolute;
                    top: 100%; left: 0; right: 0;
                    background: #fff;
                    border: 1px solid #d1d5db;
                    border-top: none;
                    border-radius: 0 0 8px 8px;
                    max-height: 280px;
                    overflow-y: auto;
                    z-index: 998;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                ">
                </div>

                @error('idpasal')
                    <span class="tatib-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- ─── FIELD: URAIAN ─────────────────────────────────────────── --}}
            <div class="tatib-field full">
                <label>Uraian</label>
                <textarea name="isi" class="tatib-textarea" data-isi-input required>{{ old('isi', $record?->isi) }}</textarea>
                @error('isi')
                    <span class="tatib-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- ─── FIELD: POIN ────────────────────────────────────────────── --}}
            <div class="tatib-field">
                <label>Poin</label>
                <input type="number" name="poin" value="{{ old('poin', $record?->poin) }}" class="tatib-input"
                    data-poin-input placeholder="Masukkan poin" required>
                @error('poin')
                    <span class="tatib-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- ─── FIELD: CATATAN / KETERANGAN ────────────────────────────── --}}
            <div class="tatib-field">
                <label>{{ $isPelanggaran ? 'Catatan' : 'Keterangan' }}</label>
                @if ($isPelanggaran)
                    <input type="text" name="catatan" value="{{ old('catatan') }}" class="tatib-input"
                        maxlength="200">
                    @error('catatan')
                        <span class="tatib-error">{{ $message }}</span>
                    @enderror
                @else
                    <input type="text" name="ket" value="{{ old('ket', $record?->ket) }}" class="tatib-input"
                        maxlength="80">
                    @error('ket')
                        <span class="tatib-error">{{ $message }}</span>
                    @enderror
                @endif
            </div>

            {{-- ─── FIELD: NOTIFIKASI WHATSAPP OTOMATIS (khusus create) ────── --}}
            @if ($showWaNotif)
                <div class="tatib-field full"
                    style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:600;">
                        <input type="checkbox" id="kirim_wa_toggle" name="kirim_wa" value="1"
                            style="width:16px;height:16px;" {{ old('kirim_wa') ? 'checked' : '' }}>
                        <i class="fab fa-whatsapp" style="color:#15803d;"></i>
                        Kirim notifikasi WhatsApp ke Orang Tua setelah data disimpan
                    </label>

                    <div id="wa-notif-detail"
                        style="display:{{ old('kirim_wa') ? 'block' : 'none' }};margin-top:10px;position:relative;">

                        {{-- Pilihan nomor dari database (muncul setelah siswa dipilih) --}}
                        <div id="wa-pilihan-nomor-form" style="display:none;margin-bottom:10px;">
                            <div style="font-size:.78rem;font-weight:600;color:#475569;margin-bottom:6px;">
                                <i class="fas fa-address-book"></i> Nomor tersimpan — klik untuk mengisi:
                            </div>
                            <div id="wa-list-nomor-form" style="display:flex;flex-direction:column;gap:6px;"></div>
                            <div style="border-top:1px dashed #e2e8f0;margin:10px 0;"></div>
                        </div>

                        <label style="font-size:.82rem;font-weight:600;">
                            Nomor HP Tujuan
                            <span id="wa-loading-form"
                                style="display:none;font-size:.72rem;color:#94a3b8;font-weight:400;">
                                <i class="fas fa-spinner fa-spin"></i> Memuat nomor...
                            </span>
                        </label>
                        <input type="text" name="nomor_wa" id="nomor_wa_input"
                            class="tatib-input @error('nomor_wa') is-invalid @enderror" value="{{ old('nomor_wa') }}"
                            placeholder="08xxx atau 628xxx (otomatis terisi jika siswa dipilih)" inputmode="numeric"
                            style="font-weight:600;letter-spacing:.5px;">
                        @error('nomor_wa')
                            <span class="tatib-error">{{ $message }}</span>
                        @enderror
                        <div style="font-size:.72rem;color:#94a3b8;margin-top:5px;">
                            Bisa pilih nomor tersimpan di atas, atau ketik nomor lain secara manual.
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <div class="tatib-actions">
        <a href="{{ $backRoute }}" class="tatib-btn tatib-btn-soft">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button type="submit" class="tatib-btn tatib-btn-primary">
            <i class="fas fa-save"></i> Simpan
        </button>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ═══════════════════════════════════════════════════════════════
            // DATA PASAL (dari JSON yang di-embed Blade, filter lokal)
            // ═══════════════════════════════════════════════════════════════
            var rawDataEl = document.getElementById('subpasal-data');
            var PASAL_LIST = rawDataEl ? JSON.parse(rawDataEl.textContent) : [];

            // ═══════════════════════════════════════════════════════════════
            // ELEMEN DOM — PASAL
            // ═══════════════════════════════════════════════════════════════
            var pasalSearchInput = document.getElementById('pasal_search_input');
            var pasalHiddenInput = document.getElementById('pasal_id_input');
            var pasalDropdown = document.getElementById('pasal_dropdown');
            var pasalBadge = document.getElementById('pasal_selected_badge');
            var pasalBadgeText = document.getElementById('pasal_selected_text');
            var pasalClearBtn = document.getElementById('pasal_clear_btn');
            var isiInput = document.querySelector('[data-isi-input]');
            var poinInput = document.querySelector('[data-poin-input]');
            var infoEl = document.getElementById('poin_rentang_info');
            var infoTxt = document.getElementById('poin_rentang_text');

            // ─── Terapkan data pasal ke field poin & uraian ─────────────
            function applyPasalData(item) {
                if (!poinInput) return;

                if (item) {
                    if (isiInput && item.isi) isiInput.value = item.isi;

                    poinInput.min = item.skormin;
                    poinInput.max = item.skormax;
                    poinInput.placeholder = item.skormin + '\u2013' + item.skormax + ' poin';

                    if (infoEl && infoTxt) {
                        infoEl.style.display = 'block';
                        infoTxt.textContent = item.skormin + '\u2013' + item.skormax;
                    }

                    // Isi nilai default poin jika masih kosong
                    if (poinInput.value === '') {
                        poinInput.value = item.skormin;
                    }
                } else {
                    // Mode input manual — lepas semua batasan
                    poinInput.removeAttribute('min');
                    poinInput.removeAttribute('max');
                    poinInput.placeholder = 'Masukkan poin';
                    if (infoEl) infoEl.style.display = 'none';
                }
            }

            // ─── Set pilihan pasal terpilih ─────────────────────────────
            function selectPasal(item) {
                pasalHiddenInput.value = item.id;
                pasalSearchInput.value = item.label;
                pasalDropdown.style.display = 'none';

                if (pasalBadge && pasalBadgeText) {
                    pasalBadgeText.textContent = item.label + ' (' + item.skormin + '\u2013' + item.skormax +
                        ' poin)';
                    pasalBadge.style.display = 'flex';
                }

                applyPasalData(item);
            }

            // ─── Hapus pilihan → kembali ke input manual ─────────────────
            function clearPasal() {
                pasalHiddenInput.value = '';
                pasalSearchInput.value = '';
                if (pasalBadge) pasalBadge.style.display = 'none';
                applyPasalData(null);
                pasalSearchInput.focus();
            }

            if (pasalClearBtn) {
                pasalClearBtn.addEventListener('click', clearPasal);
            }

            // ─── Render dropdown hasil filter ───────────────────────────
            function renderPasalDropdown(items) {
                pasalDropdown.innerHTML = '';

                if (items.length === 0) {
                    var emptyDiv = document.createElement('div');
                    emptyDiv.textContent = 'Jenis tidak ditemukan';
                    emptyDiv.style.cssText = 'padding:10px 14px;color:#888;font-size:13px;';
                    pasalDropdown.appendChild(emptyDiv);
                } else {
                    items.forEach(function(item) {
                        var div = document.createElement('div');
                        div.style.cssText =
                            'padding:10px 14px;cursor:pointer;border-bottom:1px solid #f3f4f6;line-height:1.5;overflow:hidden;';

                        var labelSpan = document.createElement('span');
                        labelSpan.textContent = item.label;
                        labelSpan.style.cssText = 'font-size:13px;font-weight:500;display:block;';

                        var poinSpan = document.createElement('span');
                        poinSpan.textContent = item.skormin + '\u2013' + item.skormax + ' poin';
                        poinSpan.style.cssText = 'font-size:11px;color:#6b7280;';

                        div.appendChild(labelSpan);
                        div.appendChild(poinSpan);

                        div.addEventListener('mouseenter', function() {
                            div.style.background = '#f0f9ff';
                        });
                        div.addEventListener('mouseleave', function() {
                            div.style.background = '';
                        });
                        div.addEventListener('mousedown', function(e) {
                            e.preventDefault();
                            selectPasal(item);
                        });
                        pasalDropdown.appendChild(div);
                    });
                }

                pasalDropdown.style.display = 'block';
            }

            // ─── Filter lokal (instan, tanpa AJAX) ──────────────────────
            function filterPasal(q) {
                if (!q || q.trim() === '') return PASAL_LIST;
                var lower = q.toLowerCase();
                return PASAL_LIST.filter(function(p) {
                    return p.label.toLowerCase().indexOf(lower) !== -1 ||
                        p.isi.toLowerCase().indexOf(lower) !== -1;
                });
            }

            // ─── Event listeners: input pencarian pasal ─────────────────
            if (pasalSearchInput) {
                pasalSearchInput.addEventListener('input', function() {
                    // Reset hidden jika user mengetik ulang
                    if (pasalHiddenInput.value !== '') {
                        pasalHiddenInput.value = '';
                        if (pasalBadge) pasalBadge.style.display = 'none';
                        applyPasalData(null);
                    }
                    renderPasalDropdown(filterPasal(pasalSearchInput.value));
                });

                pasalSearchInput.addEventListener('focus', function() {
                    renderPasalDropdown(filterPasal(pasalSearchInput.value));
                });

                pasalSearchInput.addEventListener('blur', function() {
                    setTimeout(function() {
                        pasalDropdown.style.display = 'none';
                    }, 200);
                });

                pasalSearchInput.addEventListener('keydown', function(e) {
                    // Navigasi keyboard: panah atas/bawah + Enter
                    var items = pasalDropdown.querySelectorAll('[data-pasal-item]');
                    var active = pasalDropdown.querySelector('[data-pasal-active]');
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!active && items.length) {
                            items[0].setAttribute('data-pasal-active', '1');
                            items[0].style.background = '#f0f9ff';
                        } else if (active) {
                            var next = active.nextElementSibling;
                            if (next && next.hasAttribute('data-pasal-item')) {
                                active.removeAttribute('data-pasal-active');
                                active.style.background = '';
                                next.setAttribute('data-pasal-active', '1');
                                next.style.background = '#f0f9ff';
                                next.scrollIntoView({
                                    block: 'nearest'
                                });
                            }
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (active) {
                            var prev = active.previousElementSibling;
                            if (prev && prev.hasAttribute('data-pasal-item')) {
                                active.removeAttribute('data-pasal-active');
                                active.style.background = '';
                                prev.setAttribute('data-pasal-active', '1');
                                prev.style.background = '#f0f9ff';
                                prev.scrollIntoView({
                                    block: 'nearest'
                                });
                            }
                        }
                    } else if (e.key === 'Enter') {
                        if (active) {
                            e.preventDefault();
                            active.dispatchEvent(new MouseEvent('mousedown'));
                        }
                    } else if (e.key === 'Escape') {
                        pasalDropdown.style.display = 'none';
                    }
                });
            }

            // Tutup dropdown pasal jika klik di luar
            document.addEventListener('click', function(e) {
                if (!pasalSearchInput) return;
                if (!pasalSearchInput.contains(e.target) && !pasalDropdown.contains(e.target)) {
                    pasalDropdown.style.display = 'none';
                }
            });

            // ─── Inisialisasi: restore pilihan dari old()/record ────────
            var preselectedId = pasalHiddenInput ? pasalHiddenInput.value : '';
            if (preselectedId) {
                var found = PASAL_LIST.find(function(p) {
                    return String(p.id) === String(preselectedId);
                });
                if (found) {
                    // Badge dan rentang sudah dirender Blade, tinggal sync poin
                    applyPasalData(found);
                }
            } else {
                applyPasalData(null);
            }


            // ═══════════════════════════════════════════════════════════════
            // NOTIFIKASI WHATSAPP OTOMATIS (khusus form create)
            // ═══════════════════════════════════════════════════════════════
            var kirimWaToggle = document.getElementById('kirim_wa_toggle');
            var waNotifDetail = document.getElementById('wa-notif-detail');
            var waPilihanBlock = document.getElementById('wa-pilihan-nomor-form');
            var waListNomor = document.getElementById('wa-list-nomor-form');
            var waLoadingForm = document.getElementById('wa-loading-form');
            var nomorWaInput = document.getElementById('nomor_wa_input');
            var routeNomorOrtuForm = "{{ route('admin.surat-panggilan.api.nomor-ortu') }}";

            if (kirimWaToggle && waNotifDetail) {
                kirimWaToggle.addEventListener('change', function() {
                    waNotifDetail.style.display = this.checked ? 'block' : 'none';
                    if (nomorWaInput) {
                        // required hanya secara UX; validasi wajib tetap ditegakkan di server
                        if (this.checked) {
                            nomorWaInput.setAttribute('required', 'required');
                        } else {
                            nomorWaInput.removeAttribute('required');
                        }
                    }
                });
            }

            function renderNomorOrtuForm(data) {
                if (!waListNomor) return;
                waListNomor.innerHTML = '';

                var opsi = [];
                if (data.no_hp_ortu1) opsi.push({
                    label: (data.nama_ortu1 || 'Orang Tua 1'),
                    nomor: data.no_hp_ortu1,
                    ikon: 'fas fa-user',
                    warna: '#eff6ff',
                    border: '#bfdbfe',
                    teks: '#1d4ed8',
                });
                if (data.no_hp_ortu2) opsi.push({
                    label: (data.nama_ortu2 || 'Orang Tua 2'),
                    nomor: data.no_hp_ortu2,
                    ikon: 'fas fa-user',
                    warna: '#fdf4ff',
                    border: '#e9d5ff',
                    teks: '#7c3aed',
                });
                if (data.nama_wali && data.no_hp_ortu2) opsi.push({
                    label: data.nama_wali,
                    nomor: data.no_hp_ortu2,
                    ikon: 'fas fa-user-shield',
                    warna: '#fff7ed',
                    border: '#fed7aa',
                    teks: '#c2410c',
                });

                if (opsi.length === 0) {
                    // Tidak ada nomor tersimpan sama sekali → tampilkan pesan eksplisit,
                    // supaya admin tahu memang harus isi manual (bukan sedang loading/gagal diam-diam)
                    if (waPilihanBlock) {
                        waListNomor.innerHTML =
                            '<div style="display:flex;align-items:center;gap:8px;padding:10px 12px;' +
                            'background:#fef2f2;border:1px solid #fecaca;border-radius:8px;' +
                            'color:#b91c1c;font-size:.78rem;">' +
                            '<i class="fas fa-triangle-exclamation"></i>' +
                            'Tidak ada nomor HP orang tua/wali tersimpan untuk siswa ini. Silakan isi nomor secara manual di bawah.' +
                            '</div>';
                        waPilihanBlock.style.display = 'block';
                    }
                    return;
                }

                opsi.forEach(function(o) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.style.cssText =
                        'background:' + o.warna + ';color:' + o.teks + ';border:1px solid ' + o.border +
                        ';border-radius:8px;padding:8px 12px;cursor:pointer;text-align:left;' +
                        'display:flex;align-items:center;gap:10px;font-size:.82rem;width:100%;';
                    btn.innerHTML =
                        '<i class="' + o.ikon + '" style="font-size:.9rem;flex-shrink:0;"></i>' +
                        '<div><div style="font-weight:600;">' + o.label + '</div>' +
                        '<div style="font-size:.75rem;opacity:.8;letter-spacing:.5px;">' + o.nomor +
                        '</div></div>' +
                        '<i class="fas fa-arrow-right" style="margin-left:auto;opacity:.5;"></i>';
                    btn.onclick = function() {
                        if (nomorWaInput) {
                            nomorWaInput.value = o.nomor;
                            nomorWaInput.style.borderColor = '#22c55e';
                            setTimeout(function() {
                                nomorWaInput.style.borderColor = '';
                            }, 1000);
                        }
                    };
                    waListNomor.appendChild(btn);
                });

                // Tambahkan catatan kecil: nomor tersimpan bisa saja sudah tidak aktif,
                // jadi tetap arahkan admin bisa mengganti manual jika perlu
                var catatan = document.createElement('div');
                catatan.style.cssText = 'font-size:.72rem;color:#94a3b8;margin-top:2px;';
                catatan.innerHTML =
                    '<i class="fas fa-circle-info"></i> Jika nomor di atas sudah tidak aktif, ketik nomor baru langsung di kolom "Nomor HP Tujuan" di bawah.';
                waListNomor.appendChild(catatan);

                if (waPilihanBlock) waPilihanBlock.style.display = 'block';
            }

            // Dipanggil setiap kali siswa berhasil dipilih dari dropdown pencarian
            function fetchNomorOrtuForWa(siswaId) {
                if (!siswaId || !waListNomor) return;
                if (waLoadingForm) waLoadingForm.style.display = 'inline';

                fetch(routeNomorOrtuForm + '?siswa_id=' + siswaId)
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        renderNomorOrtuForm(data);
                    })
                    .catch(function() {
                        // Gagal fetch (bukan berarti nomor kosong) — tetap beri tahu admin
                        // supaya tidak mengira sistem sudah selesai mencari tanpa hasil
                        if (waPilihanBlock && waListNomor) {
                            waListNomor.innerHTML =
                                '<div style="padding:10px 12px;background:#fffbeb;border:1px solid #fde68a;' +
                                'border-radius:8px;color:#92400e;font-size:.78rem;">' +
                                '<i class="fas fa-triangle-exclamation"></i> Gagal memuat nomor tersimpan. Silakan isi manual.' +
                                '</div>';
                            waPilihanBlock.style.display = 'block';
                        }
                    })
                    .finally(function() {
                        if (waLoadingForm) waLoadingForm.style.display = 'none';
                    });
            }


            // ═══════════════════════════════════════════════════════════════
            // ELEMEN DOM — SISWA (AJAX autocomplete, tidak berubah)
            // ═══════════════════════════════════════════════════════════════
            var searchInput = document.getElementById('siswa_search_input');
            var hiddenInput = document.getElementById('siswa_id_input');
            var dropdown = document.getElementById('siswa_dropdown');
            var jenis = '{{ $isPelanggaran ? 'pelanggaran' : 'penghargaan' }}';

            if (!searchInput || !hiddenInput || !dropdown) return;

            var debounceTimer = null;
            var lastQuery = '';

            function showSiswaDropdown(items) {
                dropdown.innerHTML = '';

                if (items.length === 0) {
                    dropdown.innerHTML =
                        '<div style="padding:10px 14px;color:#888;font-size:13px;">Siswa tidak ditemukan</div>';
                    dropdown.style.display = 'block';
                    return;
                }

                items.forEach(function(item) {
                    var div = document.createElement('div');
                    div.textContent = item.text;
                    div.style.cssText =
                        'padding:10px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid #f3f4f6;';
                    div.addEventListener('mouseenter', function() {
                        div.style.background = '#f0f9ff';
                    });
                    div.addEventListener('mouseleave', function() {
                        div.style.background = '';
                    });
                    div.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        hiddenInput.value = item.id;
                        searchInput.value = item.text;
                        dropdown.style.display = 'none';

                        // Begitu siswa dipilih, langsung ambil nomor ortu untuk panel WA
                        fetchNomorOrtuForWa(item.id);
                    });
                    dropdown.appendChild(div);
                });

                dropdown.style.display = 'block';
            }

            function fetchSiswa(q) {
                if (q === lastQuery) return;
                lastQuery = q;

                if (q.length < 1) {
                    dropdown.style.display = 'none';
                    return;
                }

                fetch('{{ route('admin.tatib.siswa-search') }}?q=' + encodeURIComponent(q) + '&jenis=' +
                        encodeURIComponent(jenis))
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        showSiswaDropdown(data.results || []);
                    })
                    .catch(function() {
                        dropdown.innerHTML =
                            '<div style="padding:10px 14px;color:#e00;font-size:13px;">Gagal memuat data</div>';
                        dropdown.style.display = 'block';
                    });
            }

            searchInput.addEventListener('input', function() {
                hiddenInput.value = '';
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    fetchSiswa(searchInput.value.trim());
                }, 300);
            });

            searchInput.addEventListener('blur', function() {
                setTimeout(function() {
                    dropdown.style.display = 'none';
                }, 200);
            });

            searchInput.addEventListener('focus', function() {
                if (searchInput.value.trim().length >= 1 && dropdown.innerHTML !== '') {
                    dropdown.style.display = 'block';
                }
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            // Kalau ada nomor_wa dari old() (form gagal validasi sebelumnya) dan siswa sudah terisi,
            // pancing ambil daftar nomor lagi
            if (hiddenInput.value && kirimWaToggle && kirimWaToggle.checked) {
                fetchNomorOrtuForWa(hiddenInput.value);
            }

            // ── SweetAlert untuk error validasi server ──
            @if ($errors->any())
                if (typeof Swal !== 'undefined' && !window.__swalValidationShown) {
                    window.__swalValidationShown = true;
                    var errorList = @json($errors->all());
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: '<ul style="padding:0;margin:0;list-style:none;text-align:left;">' +
                            errorList.map(function(m) {
                                return '<li style="margin-bottom:4px;">• ' + m + '</li>';
                            }).join('') +
                            '</ul>',
                        confirmButtonText: 'Oke',
                        confirmButtonColor: '#ef4444',
                    });
                }
            @endif

        });
    </script>
@endpush
