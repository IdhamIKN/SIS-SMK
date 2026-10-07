@extends('layouts.app')
@section('title', 'Assign Siswa PKL')

@push('styles')
    @include('components.event-styles')
    <style>
        .form-wrap {
            padding-top: var(--header-h, 56px);
            padding-bottom: calc(var(--footer-h, 60px) + 88px);
            max-width: 820px;
            margin: 0 auto;
            padding-left: 12px;
            padding-right: 12px;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media(min-width:640px) {
            .form-grid-2 {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-group label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }

        .form-group label span {
            color: #ef4444;
        }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: .875rem;
            font-family: inherit;
            color: #0f172a;
            background: #fff;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: 2px solid #16a34a;
            outline-offset: -1px;
        }

        .is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            font-size: .72rem;
            color: #ef4444;
            margin-top: 3px;
        }

        /* ── Shared searchable dropdown ── */
        .siswa-search-wrap {
            position: relative;
        }

        .siswa-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .1);
            z-index: 100;
            max-height: 280px;
            overflow-y: auto;
            display: none;
        }

        .siswa-dropdown-item {
            padding: 9px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f8fafc;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .siswa-dropdown-item:hover {
            background: #f8fafc;
        }

        .siswa-dropdown-item.disabled {
            opacity: .5;
            cursor: not-allowed;
            background: #f8fafc;
        }

        .siswa-avatar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #dcfce7;
            color: #15803d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* ── Siswa chips ── */
        .siswa-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #dcfce7;
            color: #15803d;
            border-radius: 20px;
            padding: 4px 10px;
            font-size: .75rem;
            font-weight: 700;
            margin: 3px;
        }

        .siswa-chip-remove {
            background: none;
            border: none;
            cursor: pointer;
            color: #15803d;
            font-size: .75rem;
            padding: 0;
            line-height: 1;
        }

        .siswa-selected-wrap {
            min-height: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px;
            background: #fafdf7;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 2px;
        }

        /* ── Action bar ── */
        .action-bar {
            position: fixed;
            bottom: var(--footer-h, 0);
            left: 0;
            right: 0;
            padding: 10px 12px 12px;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(10px);
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 8px;
            z-index: 999;
        }

        @media(min-width:768px) {
            .action-bar {
                padding: 10px 24px 12px;
                justify-content: flex-end;
            }
        }

        .ab-btn {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: .82rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
        }

        @media(min-width:768px) {
            .ab-btn {
                flex: unset;
                min-width: 130px;
            }
        }

        .ab-btn-green {
            background: #16a34a;
            color: #fff;
        }

        .ab-btn-back {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .hint {
            font-size: .72rem;
            color: #64748b;
            margin-top: 3px;
        }

        .counter-badge {
            background: #16a34a;
            color: #fff;
            border-radius: 20px;
            padding: 1px 8px;
            font-size: .7rem;
            margin-left: 6px;
        }

        /* ── Selected badge (lokasi & gtk) ── */
        .select-badge {
            display: none;
            margin-top: 7px;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: .82rem;
            align-items: center;
            gap: 8px;
        }

        .select-badge-lokasi {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
        }

        .select-badge-gtk {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            color: #1d4ed8;
        }

        .select-badge-clear {
            background: none;
            border: none;
            cursor: pointer;
            font-size: .9rem;
            padding: 0;
            line-height: 1;
        }

        .select-badge-lokasi .select-badge-clear {
            color: #b45309;
        }

        .select-badge-gtk .select-badge-clear {
            color: #1d4ed8;
        }
    </style>
@endpush

@section('content')
    <div class="form-wrap">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>PKL</div>
            <h2><i class="fas fa-user-plus"></i> Assign Siswa ke Lokasi PKL</h2>
            <p>Pilih lokasi, siswa, guru pembimbing, dan periode magang.</p>
        </div>

        @if ($errors->any())
            <div
                style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:11px 14px;border-radius:8px;margin-bottom:14px;font-size:.83rem;">
                <i class="fas fa-exclamation-triangle"></i>
                @foreach ($errors->all() as $e)
                    <div>{{ $e }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.pkl.penugasan.store') }}" id="assignForm">
            @csrf

            {{-- ════════════════════════════════════════════════
                 LOKASI & PEMBIMBING
            ════════════════════════════════════════════════ --}}
            <div class="form-card">
                <h3><i class="fas fa-map-marked-alt" style="color:#f59e0b;"></i> Lokasi & Pembimbing</h3>
                <div class="form-grid form-grid-2" style="gap:12px;">

                    {{-- Searchable Lokasi PKL --}}
                    <div class="form-group" style="grid-column:1/-1;">
                        <label>Lokasi PKL <span>*</span></label>
                        <input type="hidden" name="lokasi_pkl_id" id="hidLokasiId"
                            value="{{ old('lokasi_pkl_id', $lokasi?->id) }}">

                        <div class="siswa-search-wrap" id="lokasiSearchWrap">
                            <input type="text" id="lokasiSearch"
                                class="form-control @error('lokasi_pkl_id') is-invalid @enderror"
                                placeholder="Ketik nama lokasi PKL…" autocomplete="off">
                            <div class="siswa-dropdown" id="lokasiDropdown"></div>
                        </div>

                        <div id="lokasiBadge" class="select-badge select-badge-lokasi">
                            <i class="fas fa-map-marker-alt"></i>
                            <span id="lokasiBadgeText" style="flex:1;font-weight:600;"></span>
                            <button type="button" class="select-badge-clear" onclick="clearLokasi()">✕</button>
                        </div>

                        @error('lokasi_pkl_id')
                            <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Searchable Guru Pembimbing --}}
                    <div class="form-group">
                        <label>Guru Pembimbing Sekolah</label>
                        <input type="hidden" name="gtk_id" id="hidGtkId" value="{{ old('gtk_id') }}">

                        <div class="siswa-search-wrap" id="gtkSearchWrap">
                            <input type="text" id="gtkSearch" class="form-control"
                                placeholder="Ketik nama atau NIP guru…" autocomplete="off">
                            <div class="siswa-dropdown" id="gtkDropdown"></div>
                        </div>

                        <div id="gtkBadge" class="select-badge select-badge-gtk">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span id="gtkBadgeText" style="flex:1;font-weight:600;"></span>
                            <button type="button" class="select-badge-clear" onclick="clearGtk()">✕</button>
                        </div>
                    </div>

                    {{-- Tahun Ajaran --}}
                    <div class="form-group">
                        <label>Tahun Ajaran</label>
                        <select name="academic_year_id" class="form-control">
                            <option value="">— Pilih —</option>
                            @if ($academicYear)
                                <option value="{{ $academicYear->id }}" selected>{{ $academicYear->name }}</option>
                            @endif
                        </select>
                    </div>

                </div>
            </div>

            {{-- ════════════════════════════════════════════════
                 PERIODE PKL
            ════════════════════════════════════════════════ --}}
            <div class="form-card">
                <h3><i class="fas fa-calendar-alt" style="color:#0ea5e9;"></i> Periode PKL</h3>
                <div class="form-grid form-grid-2" style="gap:12px;">
                    <div class="form-group">
                        <label>Tanggal Mulai <span>*</span></label>
                        <input type="date" name="tanggal_mulai" id="inpMulai"
                            class="form-control @error('tanggal_mulai') is-invalid @enderror"
                            value="{{ old('tanggal_mulai', now()->toDateString()) }}" required>
                        @error('tanggal_mulai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Tanggal Selesai <span>*</span></label>
                        <input type="date" name="tanggal_selesai" id="inpSelesai"
                            class="form-control @error('tanggal_selesai') is-invalid @enderror"
                            value="{{ old('tanggal_selesai') }}" required>
                        @error('tanggal_selesai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ════════════════════════════════════════════════
                 PILIH SISWA
            ════════════════════════════════════════════════ --}}
            <div class="form-card">
                <h3>
                    <i class="fas fa-users" style="color:#16a34a;"></i> Pilih Siswa
                    <span class="counter-badge" id="siswaCounter">0</span>
                </h3>

                <div style="display:flex;gap:10px;margin-bottom:10px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:160px;">
                        <label style="font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:4px;">
                            Filter Kelas
                        </label>
                        <select id="selFilterKelas" class="form-control" style="font-size:.82rem;">
                            <option value="">— Semua Kelas —</option>
                            @foreach ($kelasList as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex:2;min-width:200px;">
                        <label style="font-size:.75rem;font-weight:600;color:#475569;display:block;margin-bottom:4px;">
                            Cari Siswa
                        </label>
                        <div class="siswa-search-wrap">
                            <input type="text" id="siswaSearch" class="form-control"
                                placeholder="Ketik nama atau NIS siswa..." autocomplete="off" style="font-size:.85rem;">
                            <div class="siswa-dropdown" id="siswaDropdown"></div>
                        </div>
                    </div>
                </div>

                <div class="hint" style="margin-bottom:8px;">
                    Klik nama siswa untuk menambahkan. Bisa pilih lebih dari satu sekaligus.
                </div>

                <div class="siswa-selected-wrap" id="siswaSelectedWrap">
                    <span id="siswaEmptyHint" style="font-size:.75rem;color:#94a3b8;padding:4px 2px;">
                        Belum ada siswa dipilih.
                    </span>
                </div>
                <div id="siswaInputs"></div>

                @error('siswa_ids')
                    <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                @enderror
            </div>

            {{-- ════════════════════════════════════════════════
                 CATATAN
            ════════════════════════════════════════════════ --}}
            <div class="form-card">
                <h3><i class="fas fa-sticky-note" style="color:#64748b;"></i> Catatan</h3>
                <div class="form-group">
                    <textarea name="catatan" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)...">{{ old('catatan') }}</textarea>
                </div>
            </div>

        </form>
    </div>

    <div class="action-bar">
        <a href="{{ $lokasi ? route('admin.pkl.lokasi.show', $lokasi) : route('admin.pkl.lokasi.index') }}"
            class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <button type="submit" form="assignForm" id="btnSubmit" class="ab-btn ab-btn-green" disabled>
            <i class="fas fa-user-check"></i> Tugaskan Siswa (<span id="btnCounter">0</span>)
        </button>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /* ══════════════════════════════════════════════════════════════════
               UTILITY
            ══════════════════════════════════════════════════════════════════ */
            function escHtml(s) {
                return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            /* ══════════════════════════════════════════════════════════════════
               LOKASI PKL — searchable select (client-side, data dari Blade)
            ══════════════════════════════════════════════════════════════════ */
            var lokasiData = @json($lokasiOptions); // [{id, nama_tempat, kapasitas}]
            var lokasiTimer = null;

            var lokasiInput = document.getElementById('lokasiSearch');
            var lokasiDd = document.getElementById('lokasiDropdown');
            var lokasiHid = document.getElementById('hidLokasiId');
            var lokasiBadge = document.getElementById('lokasiBadge');
            var lokasiBadgeTx = document.getElementById('lokasiBadgeText');

            /* Pre-fill jika ada old() atau $lokasi dari controller */
            (function initLokasi() {
                var existingId = lokasiHid.value;
                if (!existingId) return;
                var found = lokasiData.find(function(l) {
                    return l.id == existingId;
                });
                if (found) selectLokasi(found.id, found.nama_tempat, found.kapasitas);
            })();

            lokasiInput.addEventListener('focus', doLokasiSearch);
            lokasiInput.addEventListener('input', function() {
                clearTimeout(lokasiTimer);
                lokasiTimer = setTimeout(doLokasiSearch, 200);
            });

            function doLokasiSearch() {
                var q = lokasiInput.value.trim().toLowerCase();
                var results = lokasiData.filter(function(l) {
                    return !q || l.nama_tempat.toLowerCase().indexOf(q) !== -1;
                });
                renderLokasiDropdown(results);
            }

            function renderLokasiDropdown(list) {
                if (!list.length) {
                    lokasiDd.innerHTML =
                        '<div style="padding:12px;font-size:.8rem;color:#94a3b8;text-align:center;">' +
                        '<i class="fas fa-search"></i> Lokasi tidak ditemukan.</div>';
                } else {
                    lokasiDd.innerHTML = list.map(function(l) {
                        var kap = l.kapasitas ?
                            ' &bull; Kapasitas: ' + l.kapasitas + ' siswa' :
                            '';
                        return '<div class="siswa-dropdown-item"' +
                            ' data-id="' + l.id + '"' +
                            ' data-nama="' + escHtml(l.nama_tempat) + '"' +
                            ' data-kap="' + escHtml(l.kapasitas || '') + '">' +
                            '<div class="siswa-avatar" style="background:#fef3c7;color:#92400e;">' +
                            '<i class="fas fa-building" style="font-size:.65rem;"></i>' +
                            '</div>' +
                            '<div style="flex:1;min-width:0;">' +
                            '<div style="font-weight:700;font-size:.84rem;white-space:nowrap;' +
                            'overflow:hidden;text-overflow:ellipsis;">' +
                            escHtml(l.nama_tempat) +
                            '</div>' +
                            '<div style="font-size:.7rem;color:#64748b;">Lokasi PKL' + kap + '</div>' +
                            '</div>' +
                            '</div>';
                    }).join('');
                }
                lokasiDd.style.display = 'block';

                lokasiDd.querySelectorAll('.siswa-dropdown-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        selectLokasi(this.dataset.id, this.dataset.nama, this.dataset.kap);
                    });
                });
            }

            function selectLokasi(id, nama, kapasitas) {
                lokasiHid.value = id;
                lokasiInput.style.display = 'none';
                lokasiDd.style.display = 'none';

                var kapInfo = kapasitas ? ' — Kapasitas: ' + kapasitas + ' siswa' : '';
                lokasiBadgeTx.textContent = nama + kapInfo;
                lokasiBadge.style.display = 'flex';
            }

            window.clearLokasi = function() {
                lokasiHid.value = '';
                lokasiInput.value = '';
                lokasiInput.style.display = '';
                lokasiBadge.style.display = 'none';
                lokasiDd.style.display = 'none';
                lokasiInput.focus();
            };

            /* Tutup dropdown lokasi saat klik di luar */
            document.addEventListener('click', function(e) {
                var wrap = document.getElementById('lokasiSearchWrap');
                if (wrap && !wrap.contains(e.target)) lokasiDd.style.display = 'none';
            });

            /* ══════════════════════════════════════════════════════════════════
               GURU PEMBIMBING — searchable select (client-side)
            ══════════════════════════════════════════════════════════════════ */
            var gtkData = @json($gtkList); // [{id, nama_lengkap, nip}]
            var gtkTimer = null;

            var gtkInput = document.getElementById('gtkSearch');
            var gtkDd = document.getElementById('gtkDropdown');
            var gtkHid = document.getElementById('hidGtkId');
            var gtkBadge = document.getElementById('gtkBadge');
            var gtkBadgeTx = document.getElementById('gtkBadgeText');

            /* Pre-fill jika ada old('gtk_id') */
            (function initGtk() {
                var existingId = gtkHid.value;
                if (!existingId) return;
                var found = gtkData.find(function(g) {
                    return g.id == existingId;
                });
                if (found) selectGtk(found.id, found.nama_lengkap, found.nip);
            })();

            gtkInput.addEventListener('focus', doGtkSearch);
            gtkInput.addEventListener('input', function() {
                clearTimeout(gtkTimer);
                gtkTimer = setTimeout(doGtkSearch, 200);
            });

            function doGtkSearch() {
                var q = gtkInput.value.trim().toLowerCase();
                var results = gtkData.filter(function(g) {
                    return !q ||
                        g.nama_lengkap.toLowerCase().indexOf(q) !== -1 ||
                        (g.nip && g.nip.toLowerCase().indexOf(q) !== -1);
                });
                renderGtkDropdown(results);
            }

            function renderGtkDropdown(list) {
                if (!list.length) {
                    gtkDd.innerHTML =
                        '<div style="padding:12px;font-size:.8rem;color:#94a3b8;text-align:center;">' +
                        '<i class="fas fa-search"></i> Guru tidak ditemukan.</div>';
                } else {
                    gtkDd.innerHTML = list.map(function(g) {
                        var initial = g.nama_lengkap ? g.nama_lengkap.charAt(0).toUpperCase() : '?';
                        return '<div class="siswa-dropdown-item"' +
                            ' data-id="' + g.id + '"' +
                            ' data-nama="' + escHtml(g.nama_lengkap) + '"' +
                            ' data-nip="' + escHtml(g.nip || '') + '">' +
                            '<div class="siswa-avatar" style="background:#eff6ff;color:#1d4ed8;">' +
                            escHtml(initial) +
                            '</div>' +
                            '<div style="flex:1;min-width:0;">' +
                            '<div style="font-weight:700;font-size:.84rem;white-space:nowrap;' +
                            'overflow:hidden;text-overflow:ellipsis;">' +
                            escHtml(g.nama_lengkap) +
                            '</div>' +
                            '<div style="font-size:.7rem;color:#64748b;">' +
                            'NIP: ' + escHtml(g.nip || '-') +
                            '</div>' +
                            '</div>' +
                            '</div>';
                    }).join('');
                }
                gtkDd.style.display = 'block';

                gtkDd.querySelectorAll('.siswa-dropdown-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        selectGtk(this.dataset.id, this.dataset.nama, this.dataset.nip);
                    });
                });
            }

            function selectGtk(id, nama, nip) {
                gtkHid.value = id;
                gtkInput.style.display = 'none';
                gtkDd.style.display = 'none';

                gtkBadgeTx.textContent = nama + (nip ? ' — NIP: ' + nip : '');
                gtkBadge.style.display = 'flex';
            }

            window.clearGtk = function() {
                gtkHid.value = '';
                gtkInput.value = '';
                gtkInput.style.display = '';
                gtkBadge.style.display = 'none';
                gtkDd.style.display = 'none';
                gtkInput.focus();
            };

            /* Tutup dropdown gtk saat klik di luar */
            document.addEventListener('click', function(e) {
                var wrap = document.getElementById('gtkSearchWrap');
                if (wrap && !wrap.contains(e.target)) gtkDd.style.display = 'none';
            });

            /* ══════════════════════════════════════════════════════════════════
               CARI SISWA — searchable (AJAX)
            ══════════════════════════════════════════════════════════════════ */
            var selectedSiswa = {}; // id → {nama, nis, kelas}
            var activeKelasId = '';
            var searchTimer = null;
            var SEARCH_URL = '{{ route('admin.pkl.penugasan.api.search-siswa') }}';

            /* Filter kelas */
            var selKelas = document.getElementById('selFilterKelas');
            if (selKelas) {
                selKelas.addEventListener('change', function() {
                    activeKelasId = this.value;
                    doSiswaSearch();
                });
            }

            /* Input pencarian siswa */
            var siswaInput = document.getElementById('siswaSearch');

            siswaInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(doSiswaSearch, 300);
            });

            siswaInput.addEventListener('focus', function() {
                if (this.value.length >= 1 || activeKelasId) doSiswaSearch();
            });

            function doSiswaSearch() {
                var q = siswaInput.value.trim();
                if (q.length < 1 && !activeKelasId) {
                    hideSiswaDropdown();
                    return;
                }

                var params = new URLSearchParams({
                    q: q,
                    kelas_id: activeKelasId,
                    tanggal_mulai: document.getElementById('inpMulai').value,
                    tanggal_selesai: document.getElementById('inpSelesai').value,
                });

                var dd = document.getElementById('siswaDropdown');
                dd.innerHTML =
                    '<div style="padding:12px;text-align:center;font-size:.8rem;color:#94a3b8;">' +
                    '<i class="fas fa-spinner fa-spin"></i> Mencari...</div>';
                dd.style.display = 'block';

                fetch(SEARCH_URL + '?' + params.toString())
                    .then(function(r) {
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(renderSiswaDropdown)
                    .catch(function(err) {
                        dd.innerHTML =
                            '<div style="padding:10px 12px;font-size:.8rem;color:#dc2626;">' +
                            '<i class="fas fa-exclamation-triangle"></i> Gagal memuat: ' +
                            err.message + '</div>';
                        dd.style.display = 'block';
                    });
            }

            function renderSiswaDropdown(data) {
                var dd = document.getElementById('siswaDropdown');
                if (!data || !data.length) {
                    dd.innerHTML =
                        '<div style="padding:12px;font-size:.8rem;color:#94a3b8;text-align:center;">' +
                        '<i class="fas fa-search"></i> Tidak ada siswa ditemukan.</div>';
                } else {
                    var html = data.map(function(s) {
                        var isSelected = !!selectedSiswa[s.id];
                        var isSudahPkl = s.sudah_pkl && !isSelected;
                        var disabled = isSelected || isSudahPkl;
                        var cls = 'siswa-dropdown-item' + (disabled ? ' disabled' : '');

                        var badge = '';
                        if (isSelected) {
                            badge = '<span style="flex-shrink:0;font-size:.65rem;background:#dcfce7;' +
                                'color:#15803d;padding:2px 8px;border-radius:10px;">Terpilih</span>';
                        } else if (isSudahPkl) {
                            badge = '<span style="flex-shrink:0;font-size:.65rem;background:#fef3c7;' +
                                'color:#92400e;padding:2px 8px;border-radius:10px;">Sudah PKL</span>';
                        }

                        return '<div class="' + cls + '"' +
                            ' data-id="' + s.id + '"' +
                            ' data-nama="' + escHtml(s.nama) + '"' +
                            ' data-nis="' + escHtml(s.nis || '') + '"' +
                            ' data-kelas="' + escHtml(s.kelas || '-') + '">' +
                            '<div class="siswa-avatar">' +
                            (s.nama ? s.nama.charAt(0).toUpperCase() : '?') +
                            '</div>' +
                            '<div style="flex:1;min-width:0;">' +
                            '<div style="font-weight:700;font-size:.84rem;white-space:nowrap;' +
                            'overflow:hidden;text-overflow:ellipsis;">' +
                            escHtml(s.nama) +
                            '</div>' +
                            '<div style="font-size:.7rem;color:#64748b;">' +
                            escHtml(s.nis || '-') + ' &bull; ' + escHtml(s.kelas || '-') +
                            '</div>' +
                            '</div>' + badge +
                            '</div>';
                    }).join('');
                    dd.innerHTML = html;
                }
                dd.style.display = 'block';

                dd.querySelectorAll('.siswa-dropdown-item:not(.disabled)').forEach(function(item) {
                    item.addEventListener('click', function() {
                        addSiswa(this.dataset.id, this.dataset.nama, this.dataset.nis, this.dataset
                            .kelas);
                    });
                });
            }

            function hideSiswaDropdown() {
                var dd = document.getElementById('siswaDropdown');
                if (dd) dd.style.display = 'none';
            }

            /* Tutup dropdown siswa saat klik di luar */
            document.addEventListener('click', function(e) {
                var wrap = document.querySelector('.siswa-search-wrap:last-of-type');
                /* Ambil wrap yang benar (wrapper siswa, bukan lokasi/gtk) */
                var siswaWrap = siswaInput ? siswaInput.closest('.siswa-search-wrap') : null;
                if (siswaWrap && !siswaWrap.contains(e.target)) hideSiswaDropdown();
            });

            function addSiswa(id, nama, nis, kelas) {
                if (selectedSiswa[id]) return;
                selectedSiswa[id] = {
                    nama: nama,
                    nis: nis,
                    kelas: kelas
                };
                renderSelected();
                updateButtons();
                hideSiswaDropdown();
                siswaInput.value = '';
            }

            function removeSiswa(id) {
                delete selectedSiswa[id];
                renderSelected();
                updateButtons();
            }

            function renderSelected() {
                var wrap = document.getElementById('siswaSelectedWrap');
                var inputs = document.getElementById('siswaInputs');
                var hint = document.getElementById('siswaEmptyHint');
                var ids = Object.keys(selectedSiswa);

                wrap.querySelectorAll('.siswa-chip').forEach(function(c) {
                    c.remove();
                });

                if (ids.length === 0) {
                    hint.style.display = 'inline';
                    inputs.innerHTML = '';
                    return;
                }

                hint.style.display = 'none';

                ids.forEach(function(id) {
                    var s = selectedSiswa[id];
                    var chip = document.createElement('span');
                    chip.className = 'siswa-chip';
                    chip.dataset.id = id;
                    chip.innerHTML =
                        '<i class="fas fa-user" style="font-size:.65rem;"></i> ' +
                        escHtml(s.nama) +
                        ' <button type="button" class="siswa-chip-remove"' +
                        ' onclick="removeSiswaGlobal(\'' + id + '\')">' +
                        '<i class="fas fa-times"></i></button>';
                    wrap.appendChild(chip);
                });

                inputs.innerHTML = ids.map(function(id) {
                    return '<input type="hidden" name="siswa_ids[]" value="' + id + '">';
                }).join('');
            }

            function updateButtons() {
                var count = Object.keys(selectedSiswa).length;
                document.getElementById('siswaCounter').textContent = count;
                document.getElementById('btnCounter').textContent = count;
                document.getElementById('btnSubmit').disabled = (count === 0);
            }

            window.removeSiswaGlobal = removeSiswa;

        });
    </script>
@endpush
