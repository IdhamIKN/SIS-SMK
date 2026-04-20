{{--
    Komponen: Breadcrumbs Azures
    Penggunaan: @include('components.azures.breadcrumbs', ['items' => [...]])

    Props:
    - items: Array breadcrumb items [['title' => '...', 'url' => '...', 'icon' => '...']]
    - separator: Pemisah (default: '>')
    - homeText: Text untuk home (default: 'Beranda')
    - homeUrl: URL untuk home (default: route('dashboard'))
--}}

<nav aria-label="breadcrumb" class="breadcrumbs">
    <ol class="breadcrumb-list">
        {{-- Home --}}
        <li class="breadcrumb-item">
            <a href="{{ $homeUrl ?? route('dashboard') }}">
                @if(isset($homeIcon) && $homeIcon)
                    <i class="{{ $homeIcon }} me-1"></i>
                @endif
                {{ $homeText ?? 'Beranda' }}
            </a>
        </li>

        {{-- Separator --}}
        <li class="breadcrumb-separator">
            <span>{{ $separator ?? '>' }}</span>
        </li>

        {{-- Items --}}
        @foreach($items ?? [] as $index => $item)
            @if($index < count($items ?? []) - 1)
                {{-- Not last item --}}
                <li class="breadcrumb-item">
                    <a href="{{ $item['url'] ?? '#' }}">
                        @if(isset($item['icon']) && $item['icon'])
                            <i class="{{ $item['icon'] }} me-1"></i>
                        @endif
                        {{ $item['title'] ?? 'Page' }}
                    </a>
                </li>
                <li class="breadcrumb-separator">
                    <span>{{ $separator ?? '>' }}</span>
                </li>
            @else
                {{-- Last item --}}
                <li class="breadcrumb-item active">
                    @if(isset($item['icon']) && $item['icon'])
                        <i class="{{ $item['icon'] }} me-1"></i>
                    @endif
                    {{ $item['title'] ?? 'Page' }}
                </li>
            @endif
        @endforeach
    </ol>
</nav>

<style>
.breadcrumbs {
    padding: 10px 0;
    margin-bottom: 20px;
}

.breadcrumb-list {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 14px;
}

.breadcrumb-item {
    margin: 0 5px;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.2s ease;
}

.breadcrumb-item a:hover {
    color: #007bff;
}

.breadcrumb-item.active {
    color: #495057;
    font-weight: 600;
}

.breadcrumb-separator {
    color: #6c757d;
    margin: 0 5px;
    font-weight: 300;
}

.breadcrumb-separator span {
    font-size: 12px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .breadcrumb-list {
        font-size: 12px;
    }

    .breadcrumb-item {
        margin: 0 3px;
    }

    .breadcrumb-separator {
        margin: 0 3px;
    }
}
</style>