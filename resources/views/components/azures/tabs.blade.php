{{--
    Komponen: Tabs Azures
    Penggunaan: @include('components.azures.tabs', ['tabs' => [...], 'active' => '...'])

    Props:
    - tabs: Array of tab items [['id' => '...', 'title' => '...', 'icon' => '...', 'href' => '...']]
    - active: ID tab yang aktif (optional)
--}}

@if(isset($tabs) && is_array($tabs))
<div class="tab-controls tab-controls-rounded">
    @foreach($tabs as $tab)
        <a href="{{ $tab['href'] ?? '#' }}"
           class="{{ ($active ?? '') === $tab['id'] ? 'active' : '' }} {{ $tab['class'] ?? '' }}">
            @if(isset($tab['icon']))
                <i class="{{ $tab['icon'] }} me-2"></i>
            @endif
            {{ $tab['title'] ?? 'Tab' }}
        </a>
    @endforeach
</div>
@endif

<style>
.tab-controls {
    display: flex;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 4px;
    margin-bottom: 20px;
}

.tab-controls a {
    flex: 1;
    text-align: center;
    padding: 12px 16px;
    text-decoration: none;
    color: #6c757d;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-size: 14px;
}

.tab-controls a:hover {
    background: rgba(0,123,255,0.1);
    color: #007bff;
}

.tab-controls a.active {
    background: #007bff;
    color: white;
    box-shadow: 0 2px 8px rgba(0,123,255,0.3);
}

.tab-controls-rounded a {
    border-radius: 8px;
}
</style>