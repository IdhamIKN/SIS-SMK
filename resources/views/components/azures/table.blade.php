{{--
    Komponen: Table Azures
    Penggunaan: @include('components.azures.table', ['headers' => [...], 'rows' => [...]])

    Props:
    - headers: Array header kolom (required)
    - rows: Array data rows (required)
    - striped: Stripes pada baris (default: false)
    - hover: Hover effect (default: true)
    - responsive: Responsive table (default: true)
    - emptyMessage: Pesan jika data kosong (optional)
--}}

@if($responsive ?? true)
<div class="table-responsive">
@endif

<table class="table {{ $striped ?? false ? 'table-striped' : '' }} {{ $hover ?? true ? 'table-hover' : '' }} rounded-sm shadow-l">
    <thead>
        <tr>
            @foreach($headers ?? [] as $header)
                <th class="bg-highlight color-white py-3 font-12">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows ?? [] as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($headers ?? []) }}" class="text-center py-4 opacity-50">
                    <i class="fas fa-table font-20 mb-2"></i>
                    <p class="font-12 mb-0">{{ $emptyMessage ?? 'Tidak ada data' }}</p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($responsive ?? true)
</div>
@endif

<style>
.table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
}

.table th {
    border: none;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table td {
    padding: 12px 15px;
    vertical-align: middle;
    border-top: 1px solid #e9ecef;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,0.05);
}

.table-striped tbody tr:nth-of-type(odd) {
    background-color: rgba(0,0,0,0.025);
}

.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}
</style>