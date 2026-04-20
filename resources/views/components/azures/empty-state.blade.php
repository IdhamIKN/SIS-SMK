{{--
    Komponen: Empty State Azures
    Penggunaan: @include('components.azures.empty-state', ['icon' => '...', 'title' => '...', 'message' => '...'])

    Props:
    - icon: FontAwesome icon class (default: 'fas fa-inbox')
    - title: Judul empty state (default: 'Belum ada data')
    - message: Pesan empty state (optional)
    - action: Array button action [['text' => '...', 'href' => '...', 'icon' => '...']] (optional)
--}}

<div class="text-center py-5 opacity-70">
    <div class="icon icon-xxl rounded-m bg-fade-highlight-light shadow-m mx-auto mb-3">
        <i class="{{ $icon ?? 'fas fa-inbox' }} font-30 color-highlight"></i>
    </div>
    <h4 class="font-600 mb-2">{{ $title ?? 'Belum ada data' }}</h4>
    @if(isset($message) && $message)
        <p class="font-12 opacity-70 mb-3">{{ $message }}</p>
    @endif

    @if(isset($action) && is_array($action))
        <a href="{{ $action['href'] ?? '#' }}" class="btn btn-m bg-highlight rounded-s text-uppercase font-900">
            @if(isset($action['icon']))
                <i class="{{ $action['icon'] }} me-2"></i>
            @endif
            {{ $action['text'] ?? 'Tambah Data' }}
        </a>
    @endif
</div>