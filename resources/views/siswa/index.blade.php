@extends('layouts.app')

@section('title', 'Daftar Siswa')

@section('content')

    {{-- Breadcrumbs --}}
    @include('components.azures.breadcrumbs', [
        'items' => [
            ['title' => 'Daftar Siswa', 'icon' => 'fas fa-users']
        ]
    ])

    {{-- Page Title --}}
    <div class="page-title page-title-small">
        <h2><a href="#" data-back-button><i class="fa fa-arrow-left"></i></a>Daftar Siswa</h2>
        <a href="{{ route('siswa.create') }}" class="btn btn-s bg-highlight rounded-s text-uppercase font-900 float-end">
            <i class="fas fa-plus me-2"></i>Tambah
        </a>
    </div>

    {{-- Header Card --}}
    @include('components.azures.card-header', [
        'image' => asset('azures/images/pictures/20s.jpg'),
        'height' => 120,
        'title' => 'Data Siswa',
        'subtitle' => 'Kelola data siswa SMKN 5 Madiun'
    ])

    {{-- Search Card --}}
    <div class="card card-style">
        <div class="content mb-0">

            {{-- Search Form --}}
            @include('components.azures.search-form', [
                'action' => route('siswa.index'),
                'placeholder' => 'Cari nama/NIS/NISN...',
                'fields' => [
                    [
                        'name' => 'kelas_id',
                        'type' => 'select',
                        'label' => 'Kelas',
                        'placeholder' => 'Semua Kelas',
                        'options' => $kelas->pluck('nama_kelas', 'id')->toArray(),
                        'col' => 6
                    ],
                    [
                        'name' => 'status_aktif',
                        'type' => 'select',
                        'label' => 'Status',
                        'placeholder' => 'Semua Status',
                        'options' => ['1' => 'Aktif', '0' => 'Non Aktif'],
                        'col' => 6
                    ]
                ],
                'buttonText' => 'Cari Siswa'
            ])

            {{-- Action Buttons --}}
            <div class="row mb-0">
                <div class="col-6">
                    <a href="{{ route('siswa.export') }}" class="btn btn-s btn-border border-green-dark color-green-dark rounded-s">
                        <i class="fas fa-download me-2"></i>Export Excel
                    </a>
                </div>
                <div class="col-6">
                    <button type="button" onclick="toggleImportForm()" class="btn btn-s btn-border border-blue-dark color-blue-dark rounded-s">
                        <i class="fas fa-upload me-2"></i>Import Excel
                    </button>
                </div>
            </div>

            {{-- Import Form (Hidden by default) --}}
            <div id="importForm" class="mt-3" style="display: none;">
                <form method="POST" action="{{ route('siswa.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="input-style has-borders has-icon validate-field mb-3">
                        <i class="fa fa-file-excel"></i>
                        <input type="file" name="file" accept=".xlsx,.xls" required class="form-control">
                        <label class="color-highlight">File Excel</label>
                        <i class="fa fa-times disabled invalid color-red-dark"></i>
                        <i class="fa fa-check disabled valid color-green-dark"></i>
                        <em>(format .xlsx atau .xls)</em>
                    </div>
                    <button type="submit" class="btn btn-m btn-full bg-purple-dark rounded-s text-uppercase font-900">
                        <i class="fas fa-upload me-2"></i>Import Data
                    </button>
                </form>
                <p class="font-11 opacity-60 mt-2 mb-0">
                    Format: NIS, NISN, Nama Lengkap, Jenis Kelamin (L/P), Nama Kelas, dll.
                </p>
            </div>

        </div>
    </div>

    {{-- Siswa List --}}
    <div class="card card-style">
        <div class="content mb-0">
            <div class="list-group list-custom-large">

                @forelse($siswas as $siswa)
                    @include('components.azures.list-item-large', [
                        'href' => route('siswa.show', $siswa),
                        'avatar' => $siswa->foto ? Storage::url($siswa->foto) : '<div class="bg-blue-dark rounded-s shadow-l d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="fas fa-user-graduate font-18 color-white"></i></div>',
                        'title' => $siswa->nama_lengkap,
                        'subtitle' => $siswa->nisn,
                        'meta' => ($siswa->kelas?->nama_kelas ?? '-') . ' • ' . ($siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'),
                        'badge' => $siswa->status_aktif ? 'Aktif' : 'Non Aktif',
                        'badgeColor' => $siswa->status_aktif ? 'green-dark' : 'red-dark',
                        'showActions' => true,
                        'editUrl' => route('siswa.edit', $siswa),
                        'deleteUrl' => route('siswa.destroy', $siswa)
                    ])
                @empty
                    @include('components.azures.empty-state', [
                        'icon' => 'fas fa-users',
                        'title' => 'Belum ada data siswa',
                        'message' => 'Tambah siswa baru atau import dari Excel',
                        'action' => [
                            'text' => 'Tambah Siswa',
                            'href' => route('siswa.create'),
                            'icon' => 'fas fa-plus'
                        ]
                    ])
                @endforelse

            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @include('components.azures.pagination')

@endsection

{{-- Confirm Modal --}}
@include('components.azures.confirm-modal', [
    'id' => 'delete-siswa-modal',
    'title' => 'Hapus Siswa',
    'message' => 'Apakah Anda yakin ingin menghapus siswa ini?',
    'warning' => 'Data yang dihapus tidak dapat dikembalikan.',
    'confirmText' => 'Ya, Hapus',
    'confirmColor' => 'red'
])

@push('scripts')
<script>
@if(session('success'))
{!! azures_toast_success(session('success')) !!}
@endif

@if($errors->any())
{!! azures_toast_error($errors->first()) !!}
@endif

// Override confirm action for siswa delete
function executeConfirmAction(modalId) {
    if (modalId === 'delete-siswa-modal') {
        // Custom logic for siswa delete can be added here
        console.log('Executing siswa delete action');
    }
    // Call parent function or handle specifically
}

// Helper function for delete links
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for delete buttons if needed
    const deleteButtons = document.querySelectorAll('[data-delete-url]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const url = this.getAttribute('data-delete-url');
            showConfirmModal('delete-siswa-modal', url, 'DELETE');
        });
    });
});
</script>
@endpush