{{--
    Komponen: Timeline/Activity Feed Azures
    Penggunaan: @include('components.azures.timeline', ['items' => [...]])

    Props:
    - items: Array activity items [['icon' => '...', 'title' => '...', 'subtitle' => '...', 'time' => '...', 'color' => '...']]
    - title: Judul timeline (optional)
    - emptyMessage: Pesan jika tidak ada item (optional)
--}}

<div class="card card-style">
    <div class="content mb-0">
        @if(isset($title) && $title)
            <h4 class="font-700 mb-3">{{ $title }}</h4>
        @endif

        <div class="timeline">
            @forelse($items ?? [] as $item)
                <div class="timeline-item">
                    <div class="timeline-marker bg-{{ $item['color'] ?? 'highlight' }}-dark">
                        <i class="{{ $item['icon'] ?? 'fas fa-circle' }} color-white font-12"></i>
                    </div>
                    <div class="timeline-content">
                        <h5 class="font-600 mb-1">{{ $item['title'] ?? 'Aktivitas' }}</h5>
                        @if(isset($item['subtitle']) && $item['subtitle'])
                            <p class="font-12 opacity-70 mb-1">{{ $item['subtitle'] }}</p>
                        @endif
                        @if(isset($item['time']) && $item['time'])
                            <small class="color-highlight font-11">{{ $item['time'] }}</small>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-4 opacity-50">
                    <i class="fas fa-clock font-30 mb-3 color-highlight"></i>
                    <p class="font-14 opacity-70">{{ $emptyMessage ?? 'Belum ada aktivitas' }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.timeline-content {
    flex: 1;
    padding-left: 15px;
}

.timeline-content h5 {
    margin-bottom: 5px;
    font-size: 14px;
}

.timeline-content p {
    margin-bottom: 3px;
    font-size: 12px;
}

.timeline-content small {
    font-size: 11px;
}
</style>