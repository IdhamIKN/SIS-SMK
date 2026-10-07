{{--
    Custom pagination view — gaya Azures (tanpa dependensi Tailwind/translasi).

    CARA PAKAI:
    1. Simpan file ini ke: resources/views/vendor/pagination/azures.blade.php
    2. Pakai salah satu cara di bawah:

       A) Per halaman:
          {{ $items->links('pagination::azures') }}

       B) Jadi default di SELURUH aplikasi (disarankan, biar semua tabel
          ber-pagination ikut rapi), tambahkan di app/Providers/AppServiceProvider.php:

          use Illuminate\Pagination\Paginator;

          public function boot(): void
          {
              Paginator::defaultView('pagination::azures');
              Paginator::defaultSimpleView('pagination::azures');
          }

          Kalau sudah didaftarkan dengan cara B, panggilan biasa
          {{ $items->links() }} otomatis memakai tampilan ini.
--}}
@if ($paginator->hasPages())
    @once
        <style>
            .azures-pagination {
                display: flex;
                align-items: center;
                flex-wrap: wrap;
                gap: 10px;
                font-size: .78rem;
            }
            .azures-pagination .ap-info {
                color: #94a3b8;
                font-size: .74rem;
                order: 2;
                flex: 1 1 100%;
                text-align: center;
            }
            .azures-pagination .ap-controls {
                display: flex;
                align-items: center;
                gap: 4px;
                order: 1;
                flex: 1 1 100%;
                justify-content: center;
            }
            .azures-pagination .ap-pages {
                display: flex;
                gap: 4px;
                flex-wrap: wrap;
                justify-content: center;
            }
            .azures-pagination .ap-btn,
            .azures-pagination .ap-page {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 30px;
                height: 30px;
                padding: 0 8px;
                border-radius: 8px;
                border: 1px solid #e2e8f0;
                background: #fff;
                color: #475569;
                font-size: .76rem;
                font-weight: 600;
                text-decoration: none;
                line-height: 1;
                transition: background .15s, color .15s, border-color .15s;
            }
            .azures-pagination .ap-btn:hover,
            .azures-pagination .ap-page:hover {
                background: #f1f5f9;
            }
            .azures-pagination .ap-btn.ap-disabled {
                color: #cbd5e1;
                cursor: default;
                pointer-events: none;
            }
            .azures-pagination .ap-page.ap-active {
                background: #2563eb;
                border-color: #2563eb;
                color: #fff;
            }
            .azures-pagination .ap-dots {
                color: #cbd5e1;
                padding: 0 4px;
                font-weight: 700;
            }
            @media (min-width: 640px) {
                .azures-pagination { justify-content: flex-end; }
                .azures-pagination .ap-info {
                    order: 1;
                    flex: 0 0 auto;
                    text-align: left;
                    margin-right: auto;
                }
                .azures-pagination .ap-controls {
                    order: 2;
                    flex: 0 0 auto;
                }
            }
        </style>
    @endonce

    <nav role="navigation" aria-label="Pagination Navigation" class="azures-pagination">

        <div class="ap-info">
            Menampilkan {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </div>

        <div class="ap-controls">
            {{-- Tombol sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="ap-btn ap-disabled" aria-disabled="true"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="ap-btn" rel="prev"><i class="fas fa-chevron-left"></i></a>
            @endif

            {{-- Nomor halaman --}}
            <div class="ap-pages">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="ap-dots">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="ap-page ap-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="ap-page">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol selanjutnya --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="ap-btn" rel="next"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="ap-btn ap-disabled" aria-disabled="true"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>

    </nav>
@endif