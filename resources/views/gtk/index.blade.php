@extends('layouts.app')

@section('title', 'Daftar GTK')

@section('content')

    {{-- Breadcrumbs --}}
    @include('components.azures.breadcrumbs', [
        'items' => [
            ['title' => 'Daftar GTK', 'icon' => 'fas fa-user-tie']
        ]
    ])

    {{-- Page Title --}}
    <div class="page-title page-title-small">
        <h2><a href="#" data-back-button><i class="fa fa-arrow-left"></i></a>Daftar GTK</h2>
        <a href="{{ route('gtk.create') }}" class="btn btn-s bg-highlight rounded-s text-uppercase font-900 float-end">
            <i class="fas fa-plus me-2"></i>Tambah GTK
        </a>
    </div>

    {{-- Header Card --}}
    @include('components.azures.card-header', [
        'image' => asset('azures/images/pictures/20s.jpg'),
        'height' => 120,
        'title' => 'Data GTK',
        'subtitle' => 'Kelola data guru & tenaga kependidikan'
    ])

    {{-- Search Card --}}
    <div class="card card-style">
        <div class="content mb-0">

            {{-- Search Form --}}
            <form method="GET" class="mb-3">
                <div class="input-style has-borders has-icon validate-field mb-3">
                    <i class="fa fa-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="form-control" placeholder="Cari nama GTK...">
                    <label class="color-highlight">Pencarian</label>
                </div>

                <div class="input-style has-borders no-icon mb-3">
                    <label class="color-highlight">Jabatan</label>
                    <select name="jabatan" class="form-control">
                        <option value="">Semua Jabatan</option>
                        <option value="Guru" {{ request('jabatan') == 'Guru' ? 'selected' : '' }}>Guru</option>
                        <option value="Kepala Sekolah" {{ request('jabatan') == 'Kepala Sekolah' ? 'selected' : '' }}>Kepala Sekolah</option>
                        <option value="Wakil Kepala Sekolah" {{ request('jabatan') == 'Wakil Kepala Sekolah' ? 'selected' : '' }}>Wakil Kepala Sekolah</option>
                        <option value="BK" {{ request('jabatan') == 'BK' ? 'selected' : '' }}>BK</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-m btn-full bg-highlight rounded-s text-uppercase font-900">
                    <i class="fas fa-search me-2"></i>Cari GTK
                </button>
            </form>

        </div>
    </div>

    {{-- GTK List --}}
    <div class="card card-style">
        <div class="content mb-0">
            <div class="list-group list-custom-large">

                @forelse($gtks as $gtk)
                    @include('components.azures.list-item-large', [
                        'href' => route('gtk.show', $gtk),
                        'avatar' => $gtk->foto ? Storage::url($gtk->foto) : '<div class="bg-teal-dark rounded-s shadow-l d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="fas fa-user-tie font-18 color-white"></i></div>',
                        'title' => $gtk->nama_lengkap,
                        'subtitle' => $gtk->kd_guru,
                        'meta' => $gtk->jabatan . ($gtk->mata_pelajaran ? ' • ' . $gtk->mata_pelajaran : ''),
                        'badge' => $gtk->status_aktif ? 'Aktif' : 'Non Aktif',
                        'badgeColor' => $gtk->status_aktif ? 'green-dark' : 'red-dark',
                        'showActions' => true,
                        'editUrl' => route('gtk.edit', $gtk)
                    ])
                @empty
                    @include('components.azures.empty-state', [
                        'icon' => 'fas fa-user-tie',
                        'title' => 'Belum ada data GTK',
                        'message' => 'Tambah GTK baru atau import dari legacy data',
                        'action' => [
                            'text' => 'Tambah GTK',
                            'href' => route('gtk.create'),
                            'icon' => 'fas fa-plus'
                        ]
                    ])
                @endforelse

            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($gtks->hasPages())
    <div class="content">
        {{ $gtks->links() }}
    </div>
    @endif

@endsection

@push('scripts')
<script>
@if(session('success'))
{!! azures_toast_success(session('success')) !!}
@endif

@if($errors->any())
{!! azures_toast_error($errors->first()) !!}
@endif
</script>
@endpush