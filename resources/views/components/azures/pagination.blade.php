{{--
    Komponen: Pagination Azures
    Penggunaan: @include('components.azures.pagination')

    Menggunakan pagination links dari Laravel yang sudah di-style dengan Azures
--}}

@if(isset($paginator) && $paginator->hasPages())
<div class="content">
    <div class="d-flex justify-content-center">
        <div class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <a href="#" class="page-link disabled">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="page-link">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements = $paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <a href="#" class="page-link active">{{ $page }}</a>
                @else
                    <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="page-link">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <a href="#" class="page-link disabled">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @endif
        </div>
    </div>
</div>
@endif

<style>
/* Azures Pagination Styles */
.pagination {
    display: flex;
    align-items: center;
    gap: 5px;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 35px;
    height: 35px;
    padding: 8px 12px;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    color: #6c757d;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.page-link:hover:not(.disabled):not(.active) {
    background-color: #e9ecef;
    border-color: #adb5bd;
    color: #495057;
}

.page-link.active {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.page-link.disabled {
    background-color: #e9ecef;
    border-color: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
    opacity: 0.6;
}

.page-link i {
    font-size: 12px;
}
</style>