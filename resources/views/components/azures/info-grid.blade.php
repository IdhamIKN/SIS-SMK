{{--
    Komponen: Info Grid Azures
    Penggunaan: @include('components.azures.info-grid', ['items' => [...]])

    Props:
    - items: Array of info items [['label' => '...', 'value' => '...']]
    - columns: Jumlah kolom (default: 2)
--}}

<div class="info-grid">
    @if(isset($items) && is_array($items))
        @php $chunks = array_chunk($items, $columns ?? 2); @endphp

        @foreach($chunks as $chunk)
            <div class="info-row">
                @foreach($chunk as $item)
                    <div class="info-cell info-label">{{ $item['label'] ?? '' }}</div>
                    <div class="info-cell">{{ $item['value'] ?? '' }}</div>
                @endforeach
            </div>
        @endforeach
    @endif
</div>

<style>
.info-grid {
    width: 100%;
}

.info-row {
    display: table-row;
    margin-bottom: 8px;
}

.info-cell {
    display: table-cell;
    padding: 3px 0;
    vertical-align: top;
    font-size: 14px;
}

.info-label {
    font-weight: 600;
    width: 140px;
    color: #6c757d;
    font-size: 13px;
}

.info-cell:not(.info-label) {
    color: #495057;
}
</style>