@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Edit Siswa: {{ $siswa->nama_lengkap }}</h1>

        <form method="POST" action="{{ route('siswa.update', $siswa) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- NIS -->
                <div>
                    <label for="nis" class="block text-sm font-medium text-gray-700 mb-1">NIS</label>
                    <input type="text" name="nis" id="nis" value="{{ old('nis', $siswa->nis) }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('nis')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NISN -->
                <div>
                    <label for="nisn" class="block text-sm font-medium text-gray-700 mb-1">NISN *</label>
                    <input type="text" name="nisn" id="nisn" value="{{ old('nisn', $siswa->nisn) }}" required
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('nisn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Lengkap -->
                <div class="md:col-span-2">
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap" value="{{ old('nama_lengkap', $siswa->nama_lengkap) }}" required
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('nama_lengkap')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label for="jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin *</label>
                    <select name="jenis_kelamin" id="jenis_kelamin" required
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-Laki</option>
                        <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas -->
                <div>
                    <label for="kelas_id" class="block text-sm font-medium text-gray-700 mb-1">Kelas *</label>
                    <select name="kelas_id" id="kelas_id" required
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Kelas</option>
                        @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ old('kelas_id', $siswa->kelas_id) == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                        @endforeach
                    </select>
                    @error('kelas_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Angkatan -->
                <div>
                    <label for="angkatan" class="block text-sm font-medium text-gray-700 mb-1">Angkatan</label>
                    <input type="number" name="angkatan" id="angkatan" value="{{ old('angkatan', $siswa->angkatan) }}" min="2000" max="2030"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('angkatan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label for="tempat_lahir" class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="tempat_lahir" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('tempat_lahir')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir', $siswa->tanggal_lahir?->format('Y-m-d')) }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('tanggal_lahir')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No HP Siswa -->
                <div>
                    <label for="no_hp_siswa" class="block text-sm font-medium text-gray-700 mb-1">No HP Siswa</label>
                    <input type="text" name="no_hp_siswa" id="no_hp_siswa" value="{{ old('no_hp_siswa', $siswa->no_hp_siswa) }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('no_hp_siswa')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No HP Ortu 1 -->
                <div>
                    <label for="no_hp_ortu1" class="block text-sm font-medium text-gray-700 mb-1">No HP Ortu 1</label>
                    <input type="text" name="no_hp_ortu1" id="no_hp_ortu1" value="{{ old('no_hp_ortu1', $siswa->no_hp_ortu1) }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('no_hp_ortu1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Ortu 1 -->
                <div>
                    <label for="nama_ortu1" class="block text-sm font-medium text-gray-700 mb-1">Nama Ortu 1</label>
                    <input type="text" name="nama_ortu1" id="nama_ortu1" value="{{ old('nama_ortu1', $siswa->nama_ortu1) }}"
                           class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('nama_ortu1')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div>
                    <label class="flex items-center">
                        <input type="hidden" name="status_aktif" value="0">
                        <input type="checkbox" name="status_aktif" value="1" {{ old('status_aktif', $siswa->status_aktif) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <span class="ml-2 text-sm text-gray-700">Status Aktif</span>
                    </label>
                </div>
            </div>

            <!-- Alamat -->
            <div class="mt-4">
                <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                <textarea name="alamat" id="alamat" rows="3"
                          class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat', $siswa->alamat) }}</textarea>
                @error('alamat')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Foto -->
            <div class="mt-4">
                <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">Foto Baru (opsional)</label>
                <input type="file" name="foto" id="foto" accept="image/*"
                       class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if($siswa->foto)
                    <p class="text-sm text-gray-600 mt-1">Foto saat ini akan diganti jika upload foto baru</p>
                @endif
                @error('foto')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="mt-6 flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Update
                </button>
                <a href="{{ route('siswa.show', $siswa) }}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection