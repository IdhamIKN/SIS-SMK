{{--
    Komponen: List Item Large Azures
    Penggunaan: @include('components.azures.list-item-large', ['href' => '...', 'avatar' => '...', 'title' => '...', 'subtitle' => '...', 'meta' => '...'])

    Props:
    - href: URL tujuan (required)
    - avatar: URL avatar atau icon HTML (optional)
    - title: Judul utama (required)
    - subtitle: Sub judul (optional)
    - meta: Info tambahan (optional)
    - badge: Badge text (optional)
    - badgeColor: Warna badge (default: green-dark)
    - showActions: Tampilkan tombol edit/delete (default: false)
    - editUrl: URL edit (optional)
    - deleteUrl: URL delete (optional)
--}}

<a href="{{ $href ?? '#' }}" class="border-b">
    <div class="d-flex">
        {{-- Avatar/Icon --}}
        <div class="align-self-center">
            @if (isset($avatar) && $avatar)
                @if (str_contains($avatar, '<'))
                    {!! $avatar !!}
                @else
                    <img src="{{ $avatar }}" class="rounded-s shadow-l" width="50" height="50"
                        style="object-fit: cover;">
                @endif
            @else
                <div class="bg-highlight rounded-s shadow-l d-flex align-items-center justify-content-center"
                    style="width:50px;height:50px;">
                    <i class="fas fa-user font-18 color-white"></i>
                </div>
            @endif
        </div>

        {{-- Content --}}
        <div class="align-self-center ps-3 flex-fill">
            <h4 class="font-16 font-600 mb-0">{{ $title ?? 'Judul' }}</h4>
            @if (isset($subtitle) && $subtitle)
                <p class="font-12 color-highlight mb-0">{{ $subtitle }}</p>
            @endif
            @if (isset($meta) && $meta)
                <p class="font-11 opacity-70 mb-0">{{ $meta }}</p>
            @endif
        </div>

        {{-- Badge & Actions --}}
        <div class="align-self-center text-end">
            @if (isset($badge) && $badge)
                <span class="badge bg-{{ $badgeColor ?? 'green-dark' }} color-white font-10 mb-2">
                    {{ $badge }}
                </span>
            @endif

            @if (isset($showActions) && $showActions)
                <div class="d-flex">
                    @if (isset($editUrl) && $editUrl)
                        <a href="{{ $editUrl }}"
                            class="btn btn-s btn-border border-green-dark color-green-dark rounded-s me-1">
                            <i class="fas fa-edit"></i>
                        </a>
                    @endif
                    @if (isset($deleteUrl) && $deleteUrl)
                        <form method="POST" action="{{ $deleteUrl }}" class="d-inline"
                            onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="btn btn-s btn-border border-red-dark color-red-dark rounded-s">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
</a>
