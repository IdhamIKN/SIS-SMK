{{--
    Komponen: Tooltip Azures
    Penggunaan: @include('components.azures.tooltip', ['content' => '...'])

    Props:
    - content: Konten tooltip (required)
    - position: Posisi - 'top', 'bottom', 'left', 'right' (default: 'top')
    - trigger: Cara trigger - 'hover', 'click', 'focus' (default: 'hover')
    - size: Ukuran - 'small', 'medium', 'large' (default: 'medium')
--}}

<span class="tooltip-container" data-tooltip="{{ $content ?? '' }}"
      data-position="{{ $position ?? 'top' }}"
      data-trigger="{{ $trigger ?? 'hover' }}"
      data-size="{{ $size ?? 'medium' }}">
    {!! $slot !!}
    <span class="tooltip-text tooltip-{{ $position ?? 'top' }} tooltip-{{ $size ?? 'medium' }}">
        {{ $content ?? '' }}
    </span>
</span>

<style>
.tooltip-container {
    position: relative;
    display: inline-block;
    cursor: help;
}

.tooltip-text {
    position: absolute;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    pointer-events: none;
}

/* Positions */
.tooltip-top {
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
}

.tooltip-top::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.8);
}

.tooltip-bottom {
    top: 125%;
    left: 50%;
    transform: translateX(-50%);
}

.tooltip-bottom::after {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-bottom-color: rgba(0, 0, 0, 0.8);
}

.tooltip-left {
    right: 125%;
    top: 50%;
    transform: translateY(-50%);
}

.tooltip-left::after {
    content: '';
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    border: 5px solid transparent;
    border-left-color: rgba(0, 0, 0, 0.8);
}

.tooltip-right {
    left: 125%;
    top: 50%;
    transform: translateY(-50%);
}

.tooltip-right::after {
    content: '';
    position: absolute;
    right: 100%;
    top: 50%;
    transform: translateY(-50%);
    border: 5px solid transparent;
    border-right-color: rgba(0, 0, 0, 0.8);
}

/* Sizes */
.tooltip-small {
    padding: 6px 10px;
    font-size: 11px;
}

.tooltip-large {
    padding: 12px 16px;
    font-size: 14px;
    white-space: normal;
    max-width: 200px;
}

/* Show tooltip */
.tooltip-container:hover .tooltip-text,
.tooltip-container:focus .tooltip-text {
    opacity: 1;
    visibility: visible;
}

.tooltip-container[data-trigger="click"]:active .tooltip-text {
    opacity: 1;
    visibility: visible;
}
</style>