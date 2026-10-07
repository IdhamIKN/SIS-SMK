@extends('layouts.app')

@section('title', 'Jurnal Mengajar')

@push('styles')
    @include('components.event-styles')
    @include('guru.jurnal.styles')
    <style>
        .ab-btn-export {
            background: #0d9488;
            color: #fff;
            box-shadow: 0 3px 12px rgba(13,148,136,.3);
        }

        /* ── Select2 override untuk modal export jurnal ── */
        #jurnalExportModal .select2-container {
            width: 100% !important;
        }
        #jurnalExportModal .select2-container--default .select2-selection--single {
            height: 40px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: .83rem;
            display: flex;
            align-items: center;
        }
        #jurnalExportModal .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            color: #0f172a;
            font-size: .83rem;
            padding-left: 4px;
        }
        #jurnalExportModal .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }
        #jurnalExportModal .select2-container--default.select2-container--focus .select2-selection--single,
        #jurnalExportModal .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #1D4ED8;
            outline: none;
        }
        #jurnalExportModal .select2-dropdown {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
            font-size: .83rem;
        }
        #jurnalExportModal .select2-search--dropdown { padding: 8px; }
        #jurnalExportModal .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            padding: 7px 10px;
            font-size: .83rem;
        }
        #jurnalExportModal .select2-search--dropdown .select2-search__field:focus {
            border-color: #1D4ED8;
            outline: none;
        }
        #jurnalExportModal .select2-results__option {
            padding: 9px 12px;
            font-size: .82rem;
        }
        #jurnalExportModal .select2-results__option--highlighted {
            background: #dbeafe !important;
            color: #1e40af !important;
        }
    </style>
@endpush

@section('content')
    <div class="event-wrap jurnal-wrap"
        style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h, 0px) + 88px);">

        {{-- ── Page Strip ──────────────────────────────────────── --}}
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>Jurnal Mengajar</div>
            <h2><i class="fas fa-book-open"></i>
                {{ $lihatSemuaJurnal ? 'Jurnal Mengajar Guru' : 'Jurnal Mengajar Saya' }}
            </h2>
            <p>{{ $lihatSemuaJurnal ? 'Rekap jurnal pembelajaran semua guru.' : 'Rekap jurnal pembelajaran berdasarkan jadwal KBM guru.' }}</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- ── Filter ──────────────────────────────────────────── --}}
        <form method="GET" class="filter-section" id="filterForm">
            <button type="button"
                class="filter-toggle {{ request()->hasAny(['tanggal', 'kelas', 'pelajaran', 'gtk_id']) ? 'open' : '' }}"
                id="filterToggle"
                aria-expanded="{{ request()->hasAny(['tanggal', 'kelas', 'pelajaran', 'gtk_id']) ? 'true' : 'false' }}"
                aria-controls="filterBody">
                <span class="ft-left">
                    <i class="fas fa-filter"></i>
                    Filter
                    @if (request()->hasAny(['tanggal', 'kelas', 'pelajaran', 'gtk_id']))
                        <span
                            style="background:#dbeafe;color:#1d4ed8;font-size:.65rem;padding:2px 8px;border-radius:20px;font-weight:700;">Aktif</span>
                    @endif
                </span>
                <i class="fas fa-chevron-down ft-chevron"></i>
            </button>

            <div class="filter-body {{ request()->hasAny(['tanggal', 'kelas', 'pelajaran', 'gtk_id']) ? 'open' : '' }}"
                id="filterBody">
                <div class="filter-grid">
                    <div>
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-input" value="{{ $tanggal }}">
                    </div>

                    @if ($lihatSemuaJurnal)
                        <div>
                            <label class="form-label" for="gtk_id">Guru</label>
                            <select id="gtk_id" name="gtk_id" class="form-input">
                                <option value="">Semua Guru</option>
                                @foreach ($gtkList as $guru)
                                    <option value="{{ $guru->id }}"
                                        {{ (string) $gtkFilter === (string) $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama_lengkap }} ({{ $guru->kd_guru }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="form-label" for="kelas">Kelas</label>
                        <select id="kelas" name="kelas" class="form-input">
                            <option value="">Semua Kelas</option>
                            @foreach ($kelasOptions as $kelas)
                                <option value="{{ $kelas }}" {{ $kelasFilter === $kelas ? 'selected' : '' }}>
                                    {{ $kelas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label" for="pelajaran">Mata Pelajaran</label>
                        <select id="pelajaran" name="pelajaran" class="form-input">
                            <option value="">Semua Mapel</option>
                            @foreach ($pelajaranOptions as $pelajaran)
                                <option value="{{ $pelajaran }}" {{ $pelajaranFilter === $pelajaran ? 'selected' : '' }}>
                                    {{ $pelajaran }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display:flex; gap:8px; align-items:flex-end; padding-top:4px;">
                        <button type="submit"
                            class="action-btn btn-view" style="flex:1; justify-content:center; padding:10px;">
                            <i class="fas fa-filter"></i> Terapkan
                        </button>
                        @if (request()->hasAny(['tanggal', 'kelas', 'pelajaran', 'gtk_id']))
                            <a href="{{ route('guru.jurnal-mengajar.index') }}"
                                class="action-btn"
                                style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; padding:10px 14px;">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </form>

        {{-- ── Tabel Card ───────────────────────────────────────── --}}
        <div class="card">
            <div class="c-head">
                <div class="c-icon" style="background:#e0f2fe;"><i class="fas fa-list"></i></div>
                <h3>Daftar Jurnal</h3>
                <span class="hbadge">{{ $jurnals->total() }} jurnal</span>
            </div>

            {{-- Tabel — desktop (≥768px) --}}
            <div class="jurnal-table-wrap">
                <table class="jurnal-table">
                    <thead>
                        <tr>
                            <th>Jadwal</th>
                            @if ($lihatSemuaJurnal)
                                <th>Guru</th>
                            @endif
                            <th>Kehadiran</th>
                            <th>Bukti</th>
                            @if ($canCreateJurnal)
                                <th style="width:70px;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jurnals as $jurnal)
                            @php
                                $canManageRow =
                                    $canManageAllJurnal || ($currentKdGuru && $jurnal->kdguru === $currentKdGuru);
                            @endphp
                            <tr>
                                <td>
                                    <div class="jadwal-cell">
                                        <strong>{{ $jurnal->kelas ?: '-' }}</strong>
                                        <small>{{ $jurnal->pelajaran ?: '-' }}</small>
                                        <small>
                                            @if ($jurnal->jam_mulai && $jurnal->jam_selesai)
                                                {{ $jurnal->jam_mulai->format('H:i') }} –
                                                {{ $jurnal->jam_selesai->format('H:i') }}
                                            @else
                                                Jam {{ $jurnal->jamke ?: '-' }}
                                            @endif
                                            · {{ optional($jurnal->tanggal)->format('d/m/Y') }}
                                        </small>
                                    </div>
                                </td>

                                @if ($lihatSemuaJurnal)
                                    <td>
                                        <div class="guru-cell">
                                            <strong>{{ $jurnal->guru ?: '-' }}</strong>
                                            <small>{{ $jurnal->kdguru ?: '-' }}</small>
                                        </div>
                                    </td>
                                @endif

                                <td>
                                    <span class="presence-chip ok">{{ (int) $jurnal->siswahadir }} H</span>
                                    <span class="presence-chip danger">{{ (int) $jurnal->siswatdkhadir }} TH</span>
                                </td>

                                <td>
                                    @if ($jurnal->bukti)
                                        <button type="button" class="bukti-preview-btn"
                                            data-src="{{ Storage::url($jurnal->bukti) }}" aria-label="Lihat bukti">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                    @else
                                        <span style="color:#94a3b8;">–</span>
                                    @endif
                                </td>

                                @if ($canCreateJurnal)
                                    <td>
                                        @if ($canManageRow)
                                            <div class="row-actions">
                                                <a href="{{ route('guru.jurnal-mengajar.edit', $jurnal) }}"
                                                    class="icon-action" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST"
                                                    action="{{ route('guru.jurnal-mengajar.destroy', $jurnal) }}"
                                                    class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="icon-action danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span style="color:#94a3b8;">–</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + ($lihatSemuaJurnal ? 1 : 0) + ($canCreateJurnal ? 1 : 0) }}">
                                    <div class="jurnal-empty">
                                        <i class="fas fa-inbox"></i>
                                        <strong>Belum ada jurnal</strong>
                                        {{ $canCreateJurnal ? 'Buat jurnal dari jadwal mengajar yang tersedia.' : 'Belum ada jurnal untuk filter yang dipilih.' }}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Card list — mobile (≤767px) --}}
            <div class="jurnal-card-list">
                @forelse ($jurnals as $jurnal)
                    @php
                        $canManageRow =
                            $canManageAllJurnal || ($currentKdGuru && $jurnal->kdguru === $currentKdGuru);
                    @endphp
                    <div class="jci">
                        <div class="jci-top">
                            <div class="jci-title">{{ $jurnal->kelas ?: '-' }}</div>
                            <div class="jci-tgl">{{ optional($jurnal->tanggal)->format('d/m/Y') }}</div>
                        </div>
                        <div class="jci-sub">{{ $jurnal->pelajaran ?: '-' }}</div>
                        <div class="jci-mid">
                            @if ($jurnal->jam_mulai && $jurnal->jam_selesai)
                                <span class="jci-chip">{{ $jurnal->jam_mulai->format('H:i') }} –
                                    {{ $jurnal->jam_selesai->format('H:i') }}</span>
                            @else
                                <span class="jci-chip">Jam {{ $jurnal->jamke ?: '-' }}</span>
                            @endif
                            @if ($lihatSemuaJurnal)
                                <span class="jci-chip">{{ $jurnal->guru ?: '-' }}</span>
                            @endif
                        </div>
                        <div class="jci-bot">
                            <div class="jci-presence">
                                <span class="presence-chip ok">{{ (int) $jurnal->siswahadir }} H</span>
                                <span class="presence-chip danger">{{ (int) $jurnal->siswatdkhadir }} TH</span>
                            </div>
                            <div class="jci-actions">
                                @if ($jurnal->bukti)
                                    <button type="button" class="bukti-preview-btn icon-action"
                                        data-src="{{ Storage::url($jurnal->bukti) }}" aria-label="Lihat bukti">
                                        <i class="fas fa-paperclip"></i>
                                    </button>
                                @endif
                                @if ($canCreateJurnal && $canManageRow)
                                    <a href="{{ route('guru.jurnal-mengajar.edit', $jurnal) }}"
                                        class="icon-action" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST"
                                        action="{{ route('guru.jurnal-mengajar.destroy', $jurnal) }}"
                                        class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-action danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="jurnal-empty">
                        <i class="fas fa-inbox"></i>
                        <strong>Belum ada jurnal</strong>
                        {{ $canCreateJurnal ? 'Buat jurnal dari jadwal mengajar yang tersedia.' : 'Belum ada jurnal untuk filter yang dipilih.' }}
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($jurnals->hasPages())
                <div class="jurnal-pagination">
                    @if ($jurnals->onFirstPage())
                        <span class="pg-btn disabled"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $jurnals->previousPageUrl() }}" class="pg-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    @foreach ($jurnals->getUrlRange(max(1, $jurnals->currentPage() - 2), min($jurnals->lastPage(), $jurnals->currentPage() + 2)) as $page => $url)
                        @if ($page == $jurnals->currentPage())
                            <span class="pg-btn active pg-num">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pg-btn pg-num">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($jurnals->hasMorePages())
                        <a href="{{ $jurnals->nextPageUrl() }}" class="pg-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="pg-btn disabled"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Action bar --}}
    <div class="action-bar">
        <a href="{{ route('dashboard') }}" class="ab-btn ab-btn-back">
            <i class="fas fa-arrow-left"></i> 
        </a>
        <button type="button" class="ab-btn ab-btn-export" onclick="openJurnalExportModal()">
            <i class="fas fa-file-export"></i> Cetak / Export
        </button>
        @if ($canCreateJurnal)
            <a href="{{ route('guru.jurnal-mengajar.create') }}" class="ab-btn ab-btn-primary">
                <i class="fas fa-plus"></i> Tambah Jurnal
            </a>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════
         MODAL EXPORT / CETAK JURNAL MENGAJAR
    ═══════════════════════════════════════════ --}}
    <div id="jurnalExportModal" style="display:none;position:fixed;inset:0;z-index:9998;
         background:rgba(15,23,42,.55);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);
         align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;
                    box-shadow:0 20px 60px rgba(0,0,0,.25);overflow:hidden;">

            {{-- Header --}}
            <div style="background:linear-gradient(135deg,#1D4ED8,#2563EB);padding:16px 20px;
                        display:flex;align-items:center;justify-content:space-between;">
                <div style="color:#fff;">
                    <div style="font-weight:800;font-size:.95rem;"><i class="fas fa-file-export"></i> Cetak / Export Jurnal Mengajar</div>
                    <div style="font-size:.73rem;opacity:.85;margin-top:2px;">Pilih rentang tanggal dan filter yang diinginkan</div>
                </div>
                <button onclick="closeJurnalExportModal()" style="background:rgba(255,255,255,.2);border:none;
                        color:#fff;width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:.9rem;
                        display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Body --}}
            <div style="padding:18px 20px 20px;">
                {{-- Range Tanggal --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                            <i class="fas fa-calendar-alt" style="color:#1D4ED8;"></i> Dari Tanggal
                        </label>
                        <input type="date" id="jurnalExpMulai" class="form-input"
                            value="{{ now()->startOfMonth()->toDateString() }}"
                            style="font-size:.83rem;">
                    </div>
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                            <i class="fas fa-calendar-alt" style="color:#1D4ED8;"></i> Sampai Tanggal
                        </label>
                        <input type="date" id="jurnalExpSelesai" class="form-input"
                            value="{{ now()->toDateString() }}"
                            style="font-size:.83rem;">
                    </div>
                </div>

                @if ($lihatSemuaJurnal)
                {{-- Filter Guru (hanya untuk lihat semua) --}}
                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                        <i class="fas fa-chalkboard-teacher" style="color:#1D4ED8;"></i> Guru
                    </label>
                    <select id="jurnalExpGtk" class="form-input jurnal-exp-select" data-placeholder="Semua Guru" style="font-size:.83rem;">
                        <option value=""></option>
                        @foreach ($gtkList as $guru)
                            <option value="{{ $guru->id }}">{{ $guru->nama_lengkap }} ({{ $guru->kd_guru }})</option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Filter Kelas --}}
                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                        <i class="fas fa-door-open" style="color:#1D4ED8;"></i> Kelas
                    </label>
                    <select id="jurnalExpKelas" class="form-input jurnal-exp-select" data-placeholder="Semua Kelas" style="font-size:.83rem;">
                        <option value=""></option>
                        @foreach ($kelasOptions as $kls)
                            <option value="{{ $kls }}">{{ $kls }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Mapel --}}
                <div style="margin-bottom:18px;">
                    <label style="display:block;font-size:.78rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
                        <i class="fas fa-book" style="color:#1D4ED8;"></i> Mata Pelajaran
                    </label>
                    <select id="jurnalExpMapel" class="form-input jurnal-exp-select" data-placeholder="Semua Mapel" style="font-size:.83rem;">
                        <option value=""></option>
                        @foreach ($pelajaranOptions as $mapel)
                            <option value="{{ $mapel }}">{{ $mapel }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Export --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <button onclick="doJurnalExport('pdf')"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;
                               padding:12px;border-radius:10px;font-size:.83rem;font-weight:700;
                               background:#dc2626;color:#fff;border:none;cursor:pointer;">
                        <i class="fas fa-file-pdf"></i> Cetak PDF
                    </button>
                    <button onclick="doJurnalExport('excel')"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;
                               padding:12px;border-radius:10px;font-size:.83rem;font-weight:700;
                               background:#15803d;color:#fff;border:none;cursor:pointer;">
                        <i class="fas fa-file-excel"></i> Download Excel
                    </button>
                </div>
                <p style="font-size:.7rem;color:#94a3b8;text-align:center;margin-top:10px;margin-bottom:0;">
                    PDF akan dibuka di tab baru untuk dicetak atau disimpan.
                </p>
            </div>
        </div>
    </div>

    {{-- Bukti Preview Modal --}}
    <div class="bukti-modal-overlay" id="buktiModalOverlay" aria-hidden="true">
        <div class="bukti-modal" role="dialog" aria-modal="true" aria-labelledby="buktiModalTitle">
            <div class="bukti-modal-header">
                <div class="bukti-modal-title" id="buktiModalTitle">
                    <i class="fas fa-paperclip"></i> Bukti Pembelajaran
                </div>
                <button type="button" class="bukti-modal-close" id="buktiModalClose" aria-label="Tutup">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="bukti-modal-body">
                <img id="buktiModalImg" class="bukti-modal-media" alt="Bukti" loading="lazy" src=""
                    data-loaded="0">
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ── Header active
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');

            // ── Filter toggle
            const filterToggle = document.getElementById('filterToggle');
            const filterBody   = document.getElementById('filterBody');
            filterToggle?.addEventListener('click', function() {
                const isOpen = filterBody.classList.toggle('open');
                filterToggle.classList.toggle('open', isOpen);
                filterToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            // ── Delete confirmation
            document.querySelectorAll('.delete-form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Hapus jurnal?',
                        text: 'Data jurnal dan bukti yang tersimpan akan dihapus.',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#94a3b8',
                    }).then(function(result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

            // ── Bukti preview modal (lazy-load)
            const overlay  = document.getElementById('buktiModalOverlay');
            const closeBtn = document.getElementById('buktiModalClose');
            const img      = document.getElementById('buktiModalImg');

            function openModal(src) {
                if (!overlay || !img) return;
                img.src = src;
                img.dataset.loaded = '1';
                overlay.classList.add('open');
                overlay.setAttribute('aria-hidden', 'false');
            }

            function closeModal() {
                if (!overlay || !img) return;
                overlay.classList.remove('open');
                overlay.setAttribute('aria-hidden', 'true');
                img.src = '';
                img.dataset.loaded = '0';
            }

            document.querySelectorAll('.bukti-preview-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const src = this.getAttribute('data-src');
                    if (src) openModal(src);
                });
            });

            closeBtn?.addEventListener('click', closeModal);
            overlay?.addEventListener('click', function(e) {
                if (e.target === overlay) closeModal();
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlay?.classList.contains('open')) closeModal();
            });
        });

        // ── Export Modal ─────────────────────────────────────────────────
        let jurnalExpSelect2Inited = false;

        function openJurnalExportModal() {
            const modal = document.getElementById('jurnalExportModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Init Select2 sekali saja
            if (!jurnalExpSelect2Inited && typeof $ !== 'undefined' && $.fn.select2) {
                $('.jurnal-exp-select').select2({
                    dropdownParent: $('#jurnalExportModal'),
                    allowClear: true,
                    language: { noResults: () => 'Tidak ditemukan' },
                    placeholder: function() { return $(this).data('placeholder') || 'Pilih...'; },
                });
                jurnalExpSelect2Inited = true;
            }
        }

        function closeJurnalExportModal() {
            const modal = document.getElementById('jurnalExportModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Tutup saat klik backdrop
        document.getElementById('jurnalExportModal')?.addEventListener('click', function(e) {
            if (e.target === this) closeJurnalExportModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeJurnalExportModal();
        });

        function doJurnalExport(type) {
            const mulai   = document.getElementById('jurnalExpMulai')?.value;
            const selesai = document.getElementById('jurnalExpSelesai')?.value;
            // Ambil nilai via jQuery/Select2 jika tersedia, fallback ke DOM biasa
            const gtkVal   = (typeof $ !== 'undefined') ? $('#jurnalExpGtk').val()   : document.getElementById('jurnalExpGtk')?.value;
            const kelasVal = (typeof $ !== 'undefined') ? $('#jurnalExpKelas').val()  : document.getElementById('jurnalExpKelas')?.value;
            const mapelVal = (typeof $ !== 'undefined') ? $('#jurnalExpMapel').val()  : document.getElementById('jurnalExpMapel')?.value;

            if (!mulai || !selesai) {
                Swal.fire({ icon:'warning', title:'Tanggal wajib diisi', text:'Pilih rentang tanggal terlebih dahulu.', confirmButtonColor:'#1D4ED8' });
                return;
            }
            if (mulai > selesai) {
                Swal.fire({ icon:'warning', title:'Rentang tidak valid', text:'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.', confirmButtonColor:'#1D4ED8' });
                return;
            }

            const params = new URLSearchParams({ tanggal_mulai: mulai, tanggal_selesai: selesai });
            if (gtkVal)   params.append('gtk_id',    gtkVal);
            if (kelasVal) params.append('kelas',     kelasVal);
            if (mapelVal) params.append('pelajaran', mapelVal);

            const baseUrl = type === 'pdf'
                ? '{{ route("guru.jurnal-mengajar.export.pdf") }}'
                : '{{ route("guru.jurnal-mengajar.export.excel") }}';

            const url = baseUrl + '?' + params.toString();

            if (type === 'pdf') {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }

            closeJurnalExportModal();
        }
    </script>
@endpush
