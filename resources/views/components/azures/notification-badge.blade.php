{{--
    Komponen: Notification Badge Azures
    Penggunaan: @include('components.azures.notification-badge', ['count' => 5])

    Props:
    - count: Jumlah notifikasi (required)
    - color: Warna badge (default: red)
    - size: Ukuran - 'small', 'medium', 'large' (default: 'medium')
    - position: Posisi - 'top-right', 'top-left', 'bottom-right', 'bottom-left' (default: 'top-right')
--}}

@if(($count ?? 0) > 0)
<span class="notification-badge badge-{{ $size ?? 'medium' }} bg-{{ $color ?? 'red' }}-dark color-white position-{{ $position ?? 'top-right' }}">
    {{ $count > 99 ? '99+' : $count }}
</span>
@endif

<style>
.notification-badge {
    position: absolute;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 10px;
    min-width: 18px;
    height: 18px;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    z-index: 10;
}

/* Sizes */
.badge-small {
    font-size: 8px;
    min-width: 14px;
    height: 14px;
}

.badge-medium {
    font-size: 10px;
    min-width: 18px;
    height: 18px;
}

.badge-large {
    font-size: 12px;
    min-width: 22px;
    height: 22px;
}

/* Positions */
.position-top-right {
    top: -5px;
    right: -5px;
}

.position-top-left {
    top: -5px;
    left: -5px;
}

.position-bottom-right {
    bottom: -5px;
    right: -5px;
}

.position-bottom-left {
    bottom: -5px;
    left: -5px;
}

/* Animation */
.notification-badge {
    animation: badge-pulse 2s infinite;
}

@keyframes badge-pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}
</style>