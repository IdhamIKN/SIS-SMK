{{--
    Komponen: Advanced Empty State Azures
    Penggunaan: @include('components.azures.empty-state-advanced', ['type' => 'search'])

    Props:
    - type: Tipe empty state - 'search', 'error', 'network', 'permission', 'data' (default: 'data')
    - title: Judul custom (optional)
    - message: Pesan custom (optional)
    - icon: Icon custom (optional)
    - action: Array action button (optional)
--}}

@php
    $configs = [
        'search' => [
            'icon' => 'fas fa-search',
            'title' => 'Tidak ditemukan',
            'message' => 'Coba kata kunci lain atau periksa ejaan'
        ],
        'error' => [
            'icon' => 'fas fa-exclamation-triangle',
            'title' => 'Terjadi Kesalahan',
            'message' => 'Ada masalah saat memuat data. Silakan coba lagi.'
        ],
        'network' => [
            'icon' => 'fas fa-wifi-slash',
            'title' => 'Koneksi Terputus',
            'message' => 'Periksa koneksi internet Anda dan coba lagi.'
        ],
        'permission' => [
            'icon' => 'fas fa-lock',
            'title' => 'Akses Ditolak',
            'message' => 'Anda tidak memiliki izin untuk mengakses halaman ini.'
        ],
        'data' => [
            'icon' => 'fas fa-database',
            'title' => 'Belum ada data',
            'message' => 'Data belum tersedia. Tambah data baru untuk memulai.'
        ]
    ];

    $config = $configs[$type ?? 'data'] ?? $configs['data'];
@endphp

<div class="empty-state-advanced text-center py-5">
    <div class="empty-state-icon">
        <i class="{{ $icon ?? $config['icon'] }} fa-4x color-highlight opacity-50"></i>
    </div>

    <div class="empty-state-content mt-4">
        <h3 class="font-700 mb-2">{{ $title ?? $config['title'] }}</h3>
        <p class="font-14 opacity-70 mb-4">{{ $message ?? $config['message'] }}</p>

        @if(isset($action) && is_array($action))
            <a href="{{ $action['href'] ?? '#' }}"
               class="btn btn-m bg-highlight rounded-s text-uppercase font-900">
                @if(isset($action['icon']))
                    <i class="{{ $action['icon'] }} me-2"></i>
                @endif
                {{ $action['text'] ?? 'Aksi' }}
            </a>
        @endif
    </div>

    {{-- Additional elements based on type --}}
    @if(($type ?? 'data') === 'network')
        <div class="mt-3">
            <button onclick="window.location.reload()" class="btn btn-s btn-border border-highlight color-highlight rounded-s">
                <i class="fas fa-redo me-1"></i> Muat Ulang
            </button>
        </div>
    @elseif(($type ?? 'data') === 'search')
        <div class="mt-3">
            <small class="color-highlight">Tip: Gunakan kata kunci yang lebih spesifik</small>
        </div>
    @endif
</div>

<style>
.empty-state-advanced {
    min-height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.empty-state-icon {
    animation: empty-state-bounce 2s ease-in-out infinite;
}

.empty-state-content h3 {
    color: #495057;
}

.empty-state-content p {
    max-width: 400px;
    margin: 0 auto;
}

@keyframes empty-state-bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .empty-state-advanced {
        min-height: 250px;
        padding: 2rem 1rem;
    }

    .empty-state-icon i {
        font-size: 3rem;
    }

    .empty-state-content h3 {
        font-size: 1.25rem;
    }

    .empty-state-content p {
        font-size: 0.9rem;
        max-width: 300px;
    }
}
</style>