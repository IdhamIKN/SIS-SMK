# TODO: Fix Fitur Pengelolaan Siswa

## Perbaikan yang Dilakukan

- [x] 1. Fix `siswa/show.blade.php` - Hapus konten duplikat setelah `@endsection`
- [x] 2. Fix `siswa/create.blade.php` - Rewrite menggunakan Azure mobile theme
- [x] 3. Fix `siswa/edit.blade.php` - Rewrite menggunakan Azure mobile theme
- [x] 4. Fix `form-input.blade.php` - Fix select option selected logic

## Detail Perubahan

### 1. siswa/show.blade.php
- Menghapus konten Tailwind CSS duplikat setelah `@endsection`
- Membersihkan directive `@endif` yang tidak memiliki pasangan `@if`

### 2. siswa/create.blade.php
- Mengganti Tailwind CSS dengan Azure mobile theme (`card card-style`, `content mb-0`, dll)
- Menggunakan komponen `form-input` secara konsisten untuk semua field
- Menambahkan field yang hilang: `no_hp_ortu2`, `nama_ortu2`, `nama_wali`
- Menghapus field `status_aktif` yang duplikat
- Memperbaiki layout grid menggunakan Bootstrap `col-*` classes

### 3. siswa/edit.blade.php
- Mengganti Tailwind CSS dengan Azure mobile theme
- Menggunakan komponen `form-input` secara konsisten
- Menambahkan field yang hilang: `no_hp_ortu2`, `nama_ortu2`, `nama_wali`
- Menambahkan preview foto yang sudah ada
- Memperbaiki layout grid menggunakan Bootstrap `col-*` classes

### 4. form-input.blade.php
- Fix undefined variable $value by using isset($value) checks in all form inputs
