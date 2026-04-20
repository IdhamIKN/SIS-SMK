{{--
    Komponen: Chart Container Azures
    Penggunaan: @include('components.azures.chart', ['id' => '...', 'type' => '...', 'data' => [...]])

    Props:
    - id: ID unik untuk chart (required)
    - type: Tipe chart - 'bar', 'line', 'pie', 'doughnut' (default: 'bar')
    - title: Judul chart (optional)
    - data: Data chart dalam format Chart.js (required)
    - height: Tinggi chart dalam px (default: 200)
    - options: Opsi Chart.js tambahan (optional)
--}}

<div class="card card-style mb-3">
    <div class="content">
        @if(isset($title) && $title)
            <h4 class="font-700 mb-3">{{ $title }}</h4>
        @endif

        <div style="height: {{ $height ?? 200 }}px;">
            <canvas id="{{ $id }}" width="400" height="{{ $height ?? 200 }}"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('{{ $id }}');
    if (ctx) {
        new Chart(ctx, {
            type: '{{ $type ?? 'bar' }}',
            data: @json($data ?? []),
            options: @json($options ?? [
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            ])
        });
    }
});
</script>