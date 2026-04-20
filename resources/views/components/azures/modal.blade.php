{{--
    Komponen: Modal Azures
    Penggunaan: @include('components.azures.modal', ['id' => '...', 'title' => '...'])

    Props:
    - id: Modal ID (required)
    - title: Judul modal (required)
    - size: 'small', 'medium', 'large' (default: medium)
    - content: Slot untuk konten modal
    - footer: Slot untuk footer modal (optional)
--}}

<div id="{{ $id }}" class="menu menu-box-modal {{ $size === 'large' ? 'menu-box-modal-large' : ($size === 'small' ? 'menu-box-modal-small' : 'menu-box-modal') }} rounded-m">
    <div class="menu-header">
        <h5 class="font-700">{{ $title }}</h5>
        <a href="#" class="close-menu">
            <i class="fa fa-times"></i>
        </a>
    </div>

    <div class="menu-content {{ $size === 'large' ? 'menu-content-large' : '' }}">
        {{ $content ?? $slot }}
    </div>

    @if(isset($footer) || isset($footerSlot))
        <div class="menu-footer">
            {{ $footer ?? $footerSlot }}
        </div>
    @endif
</div>