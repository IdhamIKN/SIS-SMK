# Update sis-app ke GitHub Repo

Target: https://github.com/IdhamIKN/SIS-SMK

## Current State
- Branch: `blackboxai/initial-upload`
- Remote: `origin https://github.com/IdhamIKN/SIS-SMK.git`
- Status: **34 file modified**, banyak file **untracked**, 1 file **deleted**
- Untracked file penting: migration baru, controller baru, view baru (event, kelas, rekap), export, dll
- File deleted: `storage/framework/sessions/.gitignore`

## Plan
1. **Cek GitHub CLI** (`gh --version`)
2. **Tentukan branch target**
3. **Add semua perubahan** termasuk file baru
4. **Commit dengan pesan deskriptif**
5. **Push ke origin**
6. **Verifikasi di GitHub**

## Perubahan yang akan di-push
### Modified (34 file)
- Config & composer: `composer.json`, `composer.lock`, `config/sekolah.php`, `TODO.md`
- Controller: `LoginController`, `LaporanKehadiranController`, `AbsenController`  
- Models: `GTK`, `JadwalKBM`, `LaporanKehadiranGuru`
- Migration: `2026_04_15_151329_create_laporan_kehadiran_guru_table.php`
- Seeder: `GTKSeeder`
- Routes: `console.php`, `web.php`
- Public assets: vendor log-viewer css/js/manifest
- Views: banyak component Azure, dashboard, GTK laporan, siswa absen CRUD

### New Files (untracked)
- TODO baru: absen, event, siswa, kelas, role-based-access, dll
- Console commands, Export classes, Jobs, Requests
- Models: Event, AbsenEvent, SiswaPetugasLaporan
- Migrations: events, kelas-event bridge, absen_event, geolocation, gtks, jadwal_kbm
- Views: event/, kelas/,AJAX components
- Session files (bisa di-exclude jika perlu)

