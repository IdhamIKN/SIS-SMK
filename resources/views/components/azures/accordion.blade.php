{{--
    Komponen: Accordion Azures
    Penggunaan: @include('components.azures.accordion', ['items' => [...]])

    Props:
    - items: Array accordion items [['title' => '...', 'content' => '...', 'icon' => '...']]
    - multiple: Bisa buka multiple items (default: false)
    - firstOpen: Item pertama terbuka (default: false)
--}}

<div class="accordion" id="accordion-{{ uniqid() }}">
    @foreach($items ?? [] as $index => $item)
        <div class="accordion-item">
            <button class="accordion-header {{ $index === 0 && ($firstOpen ?? false) ? '' : 'collapsed' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse-{{ $index }}"
                    aria-expanded="{{ $index === 0 && ($firstOpen ?? false) ? 'true' : 'false' }}"
                    aria-controls="collapse-{{ $index }}">
                @if(isset($item['icon']) && $item['icon'])
                    <i class="{{ $item['icon'] }} me-2"></i>
                @endif
                <span class="accordion-title">{{ $item['title'] ?? 'Judul' }}</span>
                <i class="fas fa-chevron-down accordion-icon"></i>
            </button>

            <div id="collapse-{{ $index }}"
                 class="accordion-collapse collapse {{ $index === 0 && ($firstOpen ?? false) ? 'show' : '' }}"
                 data-bs-parent="#accordion-{{ uniqid() }}">
                <div class="accordion-body">
                    {!! $item['content'] ?? 'Konten belum tersedia' !!}
                </div>
            </div>
        </div>
    @endforeach
</div>

<style>
.accordion {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.accordion-item {
    border-bottom: 1px solid #e9ecef;
}

.accordion-item:last-child {
    border-bottom: none;
}

.accordion-header {
    width: 100%;
    padding: 15px 20px;
    background: white;
    border: none;
    text-align: left;
    font-size: 16px;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.3s ease;
}

.accordion-header:hover {
    background: #f8f9fa;
}

.accordion-header:focus {
    outline: none;
    box-shadow: none;
}

.accordion-title {
    flex: 1;
}

.accordion-icon {
    transition: transform 0.3s ease;
    font-size: 12px;
}

.accordion-header:not(.collapsed) .accordion-icon {
    transform: rotate(180deg);
}

.accordion-collapse {
    border-top: 1px solid #e9ecef;
}

.accordion-body {
    padding: 15px 20px;
    background: #f8f9fa;
    color: #6c757d;
    line-height: 1.5;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accordionButtons = document.querySelectorAll('.accordion-header');

    accordionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';

            // If not multiple, close others
            if (!{{ $multiple ?? false ? 'true' : 'false' }}) {
                const allButtons = this.closest('.accordion').querySelectorAll('.accordion-header');
                allButtons.forEach(btn => {
                    if (btn !== this) {
                        btn.classList.add('collapsed');
                        btn.setAttribute('aria-expanded', 'false');
                        const target = document.querySelector(btn.getAttribute('data-bs-target'));
                        if (target) {
                            target.classList.remove('show');
                        }
                    }
                });
            }

            // Toggle current
            this.classList.toggle('collapsed');
            this.setAttribute('aria-expanded', !isExpanded);
        });
    });
});
</script>