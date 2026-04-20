@extends('layouts.app')

@section('title', 'Tambah Siswa')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Tambah Siswa Baru</h1>

        <form method="POST" action="{{ route('siswa.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- NIS --}}
                @include('components.azures.form-input', [
                    'name' => 'nis',
                    'label' => 'NIS',
                    'type' => 'text',
                    'placeholder' => 'NIS',
                    'help' => '(opsional)',
                    'error' => $errors->first('nis')
                ])

                {{-- NISN --}}
                @include('components.azures.form-input', [
                    'name' => 'nisn',
                    'label' => 'NISN *',
                    'type' => 'text',
                    'placeholder' => 'NISN',
                    'icon' => 'fa fa-id-card',
                    'required' => true,
                    'error' => $errors->first('nisn')
                ])

                {{-- Nama Lengkap --}}
                <div class="col-12">
                    @include('components.azures.form-input', [
                        'name' => 'nama_lengkap',
                        'label' => 'Nama Lengkap *',
                        'type' => 'text',
                        'placeholder' => 'Nama Lengkap',
                        'icon' => 'fa fa-user',
                        'required' => true,
                        'error' => $errors->first('nama_lengkap')
                    ])
                </div>

                {{-- Jenis Kelamin --}}
                @include('components.azures.form-input', [
                    'name' => 'jenis_kelamin',
                    'label' => 'Jenis Kelamin *',
                    'type' => 'radio',
                    'options' => ['L' => 'Laki-Laki', 'P' => 'Perempuan'],
                    'required' => true,
                    'color' => 'blue',
                    'error' => $errors->first('jenis_kelamin')
                ])

                {{-- Kelas --}}
                @include('components.azures.form-input', [
                    'name' => 'kelas_id',
                    'label' => 'Kelas *',
                    'type' => 'select',
                    'options' => $kelas->pluck('nama_kelas', 'id')->toArray(),
                    'placeholder' => 'Pilih Kelas',
                    'required' => true,
                    'error' => $errors->first('kelas_id')
                ])

                {{-- Angkatan --}}
                @include('components.azures.form-input', [
                    'name' => 'angkatan',
                    'label' => 'Angkatan',
                    'type' => 'number',
                    'placeholder' => 'Angkatan',
                    'value' => old('angkatan', date('Y')),
                    'help' => '(tahun masuk)',
                    'error' => $errors->first('angkatan')
                ])

                {{-- Tempat Lahir --}}
                @include('components.azures.form-input', [
                    'name' => 'tempat_lahir',
                    'label' => 'Tempat Lahir',
                    'type' => 'text',
                    'placeholder' => 'Tempat Lahir',
                    'help' => '(opsional)',
                    'error' => $errors->first('tempat_lahir')
                ])

                {{-- Tanggal Lahir --}}
                @include('components.azures.form-input', [
                    'name' => 'tanggal_lahir',
                    'label' => 'Tanggal Lahir',
                    'type' => 'date',
                    'placeholder' => 'Tanggal Lahir',
                    'icon' => 'fa fa-calendar',
                    'help' => '(opsional)',
                    'error' => $errors->first('tanggal_lahir')
                ])

                {{-- No HP Siswa --}}
                @include('components.azures.form-input', [
                    'name' => 'no_hp_siswa',
                    'label' => 'No HP Siswa',
                    'type' => 'tel',
                    'placeholder' => 'No HP Siswa',
                    'icon' => 'fa fa-phone',
                    'help' => '(opsional)',
                    'error' => $errors->first('no_hp_siswa')
                ])

                <!-- No HP Ortu 1 -->
                <div>
                    <label for="no_hp_ortu1" class="block text-sm font-medium text-gray-700 mb-1">No HP Ortu 1</label>
                    <input type="text" name="no_hp_ortu1" id="no_hp_ortu1" value="{{ old('no_hp_ortu1') }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('no_hp_ortu1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Ortu 1 -->
                <div>
                    <label for="nama_ortu1" class="block text-sm font-medium text-gray-700 mb-1">Nama Ortu 1</label>
                    <input type="text" name="nama_ortu1" id="nama_ortu1" value="{{ old('nama_ortu1') }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('nama_ortu1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div>
                    <label class="flex items-center">
                        <input type="hidden" name="status_aktif" value="0">
                        <input type="checkbox" name="status_aktif" value="1" {{ old('status_aktif', true) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <span class="ml-2 text-sm text-gray-700">Status Aktif</span>
                    </label>
                </div>
            </div>

            {{-- Alamat --}}
            <div class="col-12 mt-4">
                @include('components.azures.form-input', [
                    'name' => 'alamat',
                    'label' => 'Alamat Lengkap',
                    'type' => 'textarea',
                    'placeholder' => 'Alamat lengkap siswa',
                    'rows' => 3,
                    'error' => $errors->first('alamat')
                ])
            </div>

            {{-- Foto --}}
            <div class="col-12 mt-4">
                @include('components.azures.file-upload', [
                    'name' => 'foto',
                    'label' => 'Foto Siswa',
                    'accept' => 'image/*',
                    'maxSize' => 2,
                    'help' => 'Format: JPG, PNG, max 2MB',
                    'error' => $errors->first('foto')
                ])
            </div>

            {{-- Status Aktif --}}
            <div class="col-12 mt-4 mb-4">
                @include('components.azures.form-input', [
                    'name' => 'status_aktif',
                    'label' => 'Siswa Aktif',
                    'type' => 'checkbox',
                    'value' => old('status_aktif', true),
                    'color' => 'green'
                ])
            </div>

            {{-- Buttons --}}
            @include('components.azures.button-actions', [
                'primary' => [
                    ['text' => 'Simpan Siswa', 'icon' => 'fas fa-save', 'type' => 'submit']
                ],
                'secondary' => [
                    ['text' => 'Batal', 'href' => route('siswa.index'), 'icon' => 'fas fa-arrow-left']
                ],
                'layout' => 'column'
            ])
        </form>
    </div>
</div>
@endsection