# SIS SMKN 5 Madiun

[![Laravel](https://img.shields.io/badge/Laravel-11-green?logo=laravel)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2+-8892BF?logo=php)](https://php.net)

Sistem Informasi Sekolah (SIS) untuk SMKN 5 Madiun - Management siswa, absensi GPS-based, laporan GTK.

## 🚀 Fitur Utama

- **Autentikasi Role-Based** (Siswa, GTK, BK, Wali Kelas, Admin Tatib, Superadmin) via Spatie Laravel Permission
- **Manajemen Siswa** 
  - CRUD lengkap
  - Import/Export Excel (Maatwebsite)
  - Generate CV PDF (DomPDF)
  - Integrasi data legacy (Siswa, Kelas, Jurusan, Absensi lama)
- **Absensi Siswa (Unified)**
  - Masuk/Pulang dengan GPS distance check (GeolocationService)
  - Notifikasi WhatsApp otomatis (WhatsappService)
  - Status hari ini, manual override (BK/Admin)
  - Jadwal KBM integration
- **GTK & Laporan Kehadiran**
  - CRUD GTK
  - Buat/Lihat laporan harian kehadiran guru
- **Dashboard** Overview data
- **Lainnya:** QR/Barcode, Log Viewer, Reverb WebSockets

## 📦 Dependencies Utama
```
laravel/framework ^11.31
spatie/laravel-permission *
maatwebsite/excel ^3.1
barryvdh/laravel-dompdf ^3.1
laravel/reverb *
opcodesio/log-viewer ^3.14
```

## 🛠 Setup Lokal

1. **Clone/Setup**
   ```
   cd sis-app
   composer install --optimize-autoloader --no-dev
   ```

2. **Environment**
   ```
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database** (MySQL/PostgreSQL)
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sis_smk5
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   ```
   php artisan migrate
   php artisan db:seed  # GTKSeeder, JurusanSeeder, KelasSeeder, SiswaSeeder, RolesSeeder
   ```

4. **Assets**
   ```
   npm install
   npm run build
   ```

5. **Run**
   ```
   php artisan serve
   php artisan queue:work  # WA notif, jobs
   php artisan reverb:start  # jika butuh websocket
   ```

**Login Superadmin:** `admin@smk5.test` / `password`

**Absensi Siswa:** Login NIS/NIK → Absen page → GPS harus dalam radius sekolah.

## 📱 Demo Absensi
- Siswa scan lokasi → Validasi jarak → Absen + WA notif ke wali
- Manual absen by BK

## 🗄 Database
```
users (auth + siswa_id)
permissions/roles
siswas (NIS, NISN, NIK, legacy data)
absen_siswas (masuk/pulang, lat/lng, distance)
gtks
jadwal_kbms
laporan_kehadiran_gurus
kelas/jurusans/sekolahs/set_jams
```

## 📚 Legacy Migration
Data lama dari `sql_id_edutec_my_id.sql` → Seeders import.

## 🚀 Deploy
```
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:work
```

## Todos (from project files)
- Absen unified fixes (TODO-Absen-Unified.md)
- NIS/NIK login (TODO-NIS-NIK-Login.md)

## Kontribusi
Fork → blackboxai/ branch → PR.

## Lisensi
MIT
