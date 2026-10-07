@extends('layouts.app')

@section('title', 'Master Pasal')

@push('styles')
    @include('components.event-styles')
    @include('admin.tatib._styles')
@endpush

@section('content')
    <div class="event-wrap" style="padding-top: var(--header-h, 56px); padding-bottom: calc(var(--footer-h) + 88px);">
        <div class="page-strip page-strip-event">
            <div class="live-badge"><span class="live-dot"></span>{{ now()->translatedFormat('l, d F Y') }}</div>
            <h2><i class="fas fa-book-open"></i> Master Pasal Tata Tertib</h2>
            <p>{{ $jenis === 'penghargaan' ? 'Penghargaan' : 'Pelanggaran' }} - tahun ajaran {{ $tahunAjaran }}</p>
        </div>

        @if (session('success'))
            <div class="alert a-ok"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert a-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif

        @if (session('import_errors'))
            <div class="alert a-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Catatan import</strong>
                    <ul style="margin:6px 0 0 18px;">
                        @foreach (array_slice(session('import_errors'), 0, 8) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @unless ($supportsStatus)
            <div class="alert a-info">
                <i class="fas fa-database"></i>
                Kolom status aktif belum tersedia. Jalankan <code>php artisan migrate</code> agar fitur aktif/nonaktif pasal
                bisa digunakan.
            </div>
        @endunless

        <div class="tatib-stats">
            <div class="tatib-stat">
                <div class="lbl">Total Pasal</div>
                <div class="val">{{ $stats['total'] }}</div>
            </div>
            <div class="tatib-stat">
                <div class="lbl">Aktif</div>
                <div class="val">{{ $stats['aktif'] }}</div>
            </div>
            <div class="tatib-stat">
                <div class="lbl">Nonaktif</div>
                <div class="val">{{ $stats['nonaktif'] }}</div>
            </div>
        </div>

        {{--
        PERBAIKAN PAGINATOR:
        Semua parameter filter dimasukkan sebagai hidden input agar ikut
        terbawa di URL ketika form di-submit. Laravel paginator mengambil
        query string dari URL aktif (withQueryString()), sehingga jika ada
        parameter yang hilang dari URL maka link paginator akan kehilangan
        filter tersebut.

        Kita TIDAK menggunakan hidden input untuk field yang sudah punya
        elemen input/select sendiri di dalam form (search, jenis, kategori,
        status, tahun_ajaran) agar tidak duplikat.
    --}}
        <form method="GET" action="{{ route('admin.pasal.index') }}" class="tatib-filter"
            style="grid-template-columns: 1.4fr .85fr .85fr .7fr .75fr auto;">
            <div class="tatib-field">
                <label>Cari</label>
                <input type="text" name="search" class="tatib-input" value="{{ request('search') }}"
                    placeholder="Kode, kategori, uraian">
            </div>
            <div class="tatib-field">
                <label>Jenis</label>
                <select name="jenis" class="tatib-select">
                    <option value="pelanggaran" @selected($jenis === 'pelanggaran')>Pelanggaran</option>
                    <option value="penghargaan" @selected($jenis === 'penghargaan')>Penghargaan</option>
                </select>
            </div>
            <div class="tatib-field">
                <label>Kategori</label>
                <select name="kategori" class="tatib-select">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $item)
                        <option value="{{ $item->idkategori }}" @selected(request('kategori') === $item->idkategori)>
                            {{ $item->idkategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="tatib-field">
                <label>Status</label>
                <select name="status" class="tatib-select">
                    <option value="">Semua</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="tatib-field">
                <label>Tahun</label>
                <input type="text" name="tahun_ajaran" class="tatib-input" value="{{ $tahunAjaran }}" maxlength="9">
            </div>
            <div class="tatib-actions">
                <button class="tatib-btn tatib-btn-primary" type="submit" title="Cari"><i
                        class="fas fa-search"></i></button>
                <a href="{{ route('admin.pasal.index') }}" class="tatib-btn tatib-btn-soft" title="Reset"><i
                        class="fas fa-times"></i></a>
                <a href="{{ route('admin.pasal.create', ['jenis' => $jenis, 'tahun_ajaran' => $tahunAjaran]) }}"
                    class="tatib-btn tatib-btn-warn" title="Tambah"><i class="fas fa-plus"></i></a>
                <a href="{{ route('admin.pasal.import', ['jenis' => $jenis, 'tahun_ajaran' => $tahunAjaran]) }}"
                    class="tatib-btn tatib-btn-green" title="Import"><i class="fas fa-file-import"></i></a>
            </div>
        </form>

        @if ($pasal->count())
            {{-- Info jumlah hasil & halaman --}}
            <div style="font-size:.74rem;color:#64748b;margin-bottom:6px;padding:0 2px;">
                Menampilkan {{ $pasal->firstItem() }}–{{ $pasal->lastItem() }} dari {{ $pasal->total() }} pasal
                &nbsp;•&nbsp; Halaman {{ $pasal->currentPage() }} / {{ $pasal->lastPage() }}
            </div>

            <div class="tatib-list">
                @foreach ($pasal as $item)
                    @php
                        $detail = $item->relationLoaded('detailTahun') ? $item->getRelation('detailTahun') : null;
                        $aktif = $supportsStatus ? (bool) ($item->status_aktif ?? true) : true;
                    @endphp
                    <div class="tatib-card" style="border-left: 4px solid {{ $aktif ? '#16a34a' : '#dc2626' }};">
                        <div>
                            <h3 class="tatib-title">[{{ $item->idpasal }}]
                                {{ $detail?->pasal ?? 'Belum ada uraian pasal' }}</h3>
                            <div class="tatib-meta">
                                <span><i
                                        class="fas fa-folder"></i>{{ $item->kategori?->kategori ?? $item->idkategori }}</span>
                                <span><i class="fas fa-sort-numeric-up"></i>Urut {{ $item->urut }}</span>
                                <span><i class="fas fa-calendar"></i>{{ $detail?->thnajaran ?? $tahunAjaran }}</span>
                                <span class="{{ $aktif ? 'tatib-pill-green' : 'tatib-pill-red' }}">
                                    <i
                                        class="fas {{ $aktif ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>{{ $aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="tatib-meta" style="margin-top:8px;">
                                @if ($detail)
                                    <span class="{{ $jenis === 'penghargaan' ? 'tatib-pill-green' : 'tatib-pill-red' }}">
                                        <i
                                            class="fas fa-bolt"></i>{{ (int) $detail->skormin }}-{{ (int) $detail->skormax }}
                                        poin
                                    </span>
                                @else
                                    <span class="tatib-pill-amber"><i class="fas fa-exclamation-circle"></i>Detail belum
                                        tersedia</span>
                                @endif
                            </div>
                        </div>
                        <div class="tatib-actions">
                            <a href="{{ route('admin.pasal.edit', ['pasal' => $item->idpasal, 'tahun_ajaran' => $tahunAjaran]) }}"
                                class="tatib-btn tatib-btn-soft" title="Edit"><i class="fas fa-edit"></i></a>

                            <form method="POST" action="{{ route('admin.pasal.toggle', $item) }}"
                                class="form-toggle-status" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <button class="tatib-btn {{ $aktif ? 'tatib-btn-warn' : 'tatib-btn-green' }}"
                                    type="button" title="{{ $aktif ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas {{ $aktif ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.pasal.destroy', $item) }}"
                                class="form-delete-pasal" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="tatib-btn tatib-btn-danger" type="button" title="Hapus"><i
                                        class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="tatib-pagination" style="padding: 4px 2px 8px;">
                {{ $pasal->withQueryString()->links('pagination::azures') }}
            </div>
        @else
            <div class="tatib-card">
                <div>
                    <h3 class="tatib-title">Belum ada data</h3>
                    <div class="tatib-meta"><span>Filter saat ini tidak menemukan pasal.</span></div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.header-auto-show');
            if (header) header.classList.add('header-active');

            document.querySelectorAll('.form-toggle-status button').forEach(function(button) {
                button.addEventListener('click', function() {
                    const form = button.closest('form');
                    Swal.fire({
                        title: 'Ubah status pasal?',
                        text: 'Pasal nonaktif tidak muncul pada pilihan input baru.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, lanjutkan',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then(function(result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });

            document.querySelectorAll('.form-delete-pasal button').forEach(function(button) {
                button.addEventListener('click', function() {
                    const form = button.closest('form');
                    Swal.fire({
                        title: 'Hapus pasal?',
                        text: 'Pasal yang sudah dipakai transaksi akan ditolak oleh sistem.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then(function(result) {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>
@endpush
