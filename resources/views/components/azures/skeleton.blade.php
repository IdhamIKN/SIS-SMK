{{--
    Komponen: Skeleton Loading Azures
    Penggunaan: @include('components.azures.skeleton', ['type' => 'card', 'rows' => 3])

    Props:
    - type: Tipe skeleton - 'card', 'list', 'table', 'text' (default: 'card')
    - rows: Jumlah baris untuk list/table (default: 3)
    - height: Tinggi custom (optional)
    - width: Lebar custom (optional)
--}}

@if($type ?? 'card' === 'card')
    {{-- Card Skeleton --}}
    <div class="card card-style skeleton-card">
        <div class="content">
            <div class="skeleton skeleton-avatar"></div>
            <div class="skeleton skeleton-title"></div>
            <div class="skeleton skeleton-text"></div>
            <div class="skeleton skeleton-text skeleton-text-short"></div>
        </div>
    </div>

@elseif($type ?? 'card' === 'list')
    {{-- List Skeleton --}}
    <div class="card card-style">
        <div class="content mb-0">
            <div class="list-group list-custom-large">
                @for($i = 0; $i < ($rows ?? 3); $i++)
                    <div class="skeleton-list-item">
                        <div class="skeleton skeleton-avatar"></div>
                        <div class="skeleton-content">
                            <div class="skeleton skeleton-title"></div>
                            <div class="skeleton skeleton-text"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>

@elseif($type ?? 'card' === 'table')
    {{-- Table Skeleton --}}
    <div class="table-responsive">
        <table class="table table-borderless rounded-sm">
            <thead>
                <tr>
                    <th class="bg-highlight color-white py-3">
                        <div class="skeleton skeleton-title" style="width: 100px;"></div>
                    </th>
                    <th class="bg-highlight color-white py-3">
                        <div class="skeleton skeleton-title" style="width: 120px;"></div>
                    </th>
                    <th class="bg-highlight color-white py-3">
                        <div class="skeleton skeleton-title" style="width: 80px;"></div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < ($rows ?? 3); $i++)
                    <tr>
                        <td><div class="skeleton skeleton-text"></div></td>
                        <td><div class="skeleton skeleton-text"></div></td>
                        <td><div class="skeleton skeleton-text skeleton-text-short"></div></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

@else
    {{-- Text Skeleton --}}
    <div class="skeleton {{ $width ? '' : 'skeleton-text' }}" {{ $width ? 'style="width: ' . $width . '"' : '' }} {{ $height ? 'style="height: ' . $height . '"' : '' }}></div>
@endif

<style>
/* Skeleton Base Styles */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 4px;
}

/* Skeleton Shapes */
.skeleton-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
}

.skeleton-title {
    height: 16px;
    margin-bottom: 8px;
}

.skeleton-text {
    height: 12px;
    margin-bottom: 6px;
}

.skeleton-text-short {
    width: 60%;
}

.skeleton-text:last-child {
    margin-bottom: 0;
    width: 80%;
}

/* List Item Skeleton */
.skeleton-list-item {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
}

.skeleton-list-item:last-child {
    border-bottom: none;
}

.skeleton-content {
    flex: 1;
    margin-left: 15px;
}

.skeleton-content .skeleton-title {
    width: 150px;
    margin-bottom: 4px;
}

.skeleton-content .skeleton-text {
    width: 200px;
}

/* Card Skeleton */
.skeleton-card .content {
    padding: 20px;
}

/* Animation */
@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Dark mode support */
.theme-dark .skeleton {
    background: linear-gradient(90deg, #2d3748 25%, #4a5568 50%, #2d3748 75%);
    background-size: 200% 100%;
}
</style>