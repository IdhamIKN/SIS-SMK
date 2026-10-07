# Setup & Deploy Checklist — SIS App
> Versi update: **Agustus 2026**
> Diuji di lokal: **2026-08-11** | Semua test: **PASS 59 / FAIL 0 / SKIP 2**

---

## 1. Prasyarat Server Hosting

```
PHP         >= 8.2
MySQL       >= 8.0
Composer    >= 2.x
Node.js     >= 18 (hanya jika build assets di server)
Cron/Task   aktif (untuk Laravel Scheduler)
```

---

## 2. Upload & Konfigurasi Awal

```bash
# 1. Upload semua file ke server (exclude .git, node_modules, vendor)
# 2. Masuk ke folder project
cd /path/to/sis-app

# 3. Install dependencies PHP
composer install --optimize-autoloader --no-dev

# 4. Salin .env dan sesuaikan
cp .env.example .env
# Edit .env: DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
# Edit .env: APP_URL, APP_ENV=production, APP_DEBUG=false

# 5. Generate app key
php artisan key:generate

# 6. Set permission folder (Linux/cPanel)
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs storage/app/public

# 7. Buat symlink storage
php artisan storage:link
```

---

## 3. Migrasi Database (WAJIB — semua Pending)

Jalankan migration sekaligus untuk semua perubahan sejak April s/d Agustus 2026:

```bash
php artisan migrate --force
```

### Daftar migrasi baru Agustus 2026 yang akan dijalankan:

| Tanggal | File | Keterangan |
|---------|------|------------|
| 01 Agu | `add_wa_notif_toggles_to_tblsekolah` | Toggle notif WA: pulang, event, tatib |
| 01 Agu | `add_wa_laporan_guru_config_to_tblsekolah` | Toggle + nomor WA laporan kehadiran guru |
| 02 Agu | `fix_event_permissions` | Fix permission event.* & event-guru.* untuk semua role |
| 02 Agu | `add_libur_mode_to_tblsekolah` | Mode libur panjang (nonaktifkan semua proses otomatis) |
| 03 Agu | `add_kelompok_jam_to_tblsetjam` | Kolom kelompok_jam: reguler / jumat |
| 03 Agu | `seed_jam_pelajaran_sesuai_gambar` | Seed data jam pelajaran reguler |
| 03 Agu | `seed_jam_pelajaran` | Seed tambahan jam pelajaran |
| 03 Agu | `nullable_limit_cols_tblsetjam` | limit_in & limit_out menjadi nullable |
| 03 Agu | `refactor_absen_siswa_unified_record` | Pola 1 record/siswa/hari (masuk+pulang tergabung) |
| 03 Agu | `refactor_absen_event_unified_record` | Pola 1 record/siswa/event (masuk+pulang tergabung) |
| 03 Agu | `create_absen_activity_log_table` | Audit log aksi admin terhadap absensi |
| 06 Agu | `add_auto_penghargaan_to_events_table` | Kolom auto_penghargaan & pasal_penghargaan_id di events |
| 06 Agu | `add_poin_fields_to_events_table` | Override poin pelanggaran & penghargaan per event |
| 06 Agu | `create_auto_pelanggaran_rules_table` | Tabel aturan pelanggaran otomatis |
| 06 Agu | `create_auto_penghargaan_rules_table` | Tabel aturan penghargaan otomatis |
| 08 Agu | `fix_tblpenghargaan_idpen_autoincrement_and_deviceid_length` | Fix AUTO_INCREMENT + perbesar deviceid |
| 08 Agu | `add_soft_deletes_to_tblpelanggaran_and_tbltransaksi` | Soft delete pelanggaran & transaksi |
| 10 Agu | `add_auto_poin_kehadiran_to_tblsekolah` | Konfigurasi poin otomatis hadir & terlambat |
| 11 Agu | `add_soft_deletes_to_tblpenghargaan` | Soft delete penghargaan |
| 11 Agu | `add_ekstrakurikuler_fields_to_events_table` | Flag ekstrakurikuler, pelatih 1–3, pembina |
| 11 Agu | `create_event_photos_table` | Tabel foto kegiatan event (maks 10/event) |

> **Catatan:** Migrasi `2026_08_11_120001` dan `2026_08_11_120002` sudah `Ran` di lokal. Di server hosting pastikan semua dijalankan dari awal.

---

## 4. Cache & Optimasi

```bash
# Clear semua cache lama
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimasi untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## 5. Setup Scheduler (Cron)

### 5.1 Variabel path (sesuaikan dengan server)

```bash
# Path PHP 8.2 di BT Panel / aaPanel
PHP=/www/server/php/82/bin/php

# Path folder project (sesuaikan)
APP=/www/wwwroot/sis-app
```

### 5.2 Cron utama — cukup 1 baris (Laravel Scheduler)

Tambahkan di **BT Panel → Cron Jobs → Shell Script**, jalankan tiap menit:

```bash
* * * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan schedule:run >> /dev/null 2>&1
```

> Satu baris ini sudah cukup — Laravel Scheduler akan menjalankan semua command di bawah sesuai jadwalnya masing-masing.

---

### 5.3 Command lengkap untuk dijalankan manual di terminal server

Gunakan format ini setiap kali ingin jalankan manual lewat SSH atau BT Panel → Terminal:

```bash
# ── MIGRASI ──────────────────────────────────────────────────────────────
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan migrate --force

# ── CACHE & OPTIMASI ─────────────────────────────────────────────────────
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan config:clear
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan cache:clear
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan route:clear
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan view:clear
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan optimize

# ── ABSENSI HARIAN ───────────────────────────────────────────────────────
# Generate record absensi kosong semua siswa (biasanya 05:30)
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan absen:generate-harian

# Auto alfa siswa yang belum scan setelah jam eksekusi
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan absen:auto-alfa

# Auto-fill absensi masuk (biasanya 13:00)
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan attendance:auto-fill

# Sinkron izin yang disetujui → update status absen
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan attendance:update-from-approved-izin

# Beri poin otomatis hadir/terlambat/alfa berdasarkan status absen harian
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan absen:auto-poin-harian

# ── EVENT ─────────────────────────────────────────────────────────────────
# Beri poin PELANGGARAN siswa yang tidak scan event (setelah event selesai)
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan event:auto-point-pelanggaran

# Beri poin PENGHARGAAN siswa yang berhasil scan event (setelah event selesai)
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan event:auto-penghargaan

# Jalankan untuk event spesifik (ganti 123 dengan ID event)
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan event:auto-point-pelanggaran --event=123
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan event:auto-penghargaan --event=123

# Rotasi barcode event aktif
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan event:rotate-barcodes

# ── TATIB & POIN ─────────────────────────────────────────────────────────
# Sinkronisasi pelanggaran/penghargaan → tbltransaksi
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan tatib:sync-transaksi

# Backfill poin alfa untuk data lama
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan absen:backfill-alfa-poin

# Bersihkan poin alfa orphan (siswa sudah tidak alfa tapi poin masih ada)
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan tatib:cleanup-orphan-alfa

# ── MONITORING ───────────────────────────────────────────────────────────
# Cek kelas yang belum laporan (tandai orange)
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan sis:check-orange

# ── TEST FITUR (verifikasi setelah deploy) ────────────────────────────────
/www/server/php/82/bin/php /www/wwwroot/sis-app/artisan sis:test-feature --no-interaction
```

---

### 5.4 Jika ingin set Cron terpisah per command (alternatif tanpa Scheduler)

Gunakan ini di BT Panel → Cron Jobs jika tidak mau pakai `schedule:run`:

```bash
# Generate absen harian — tiap hari jam 05:30
30 5 * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan absen:generate-harian >> /dev/null 2>&1

# Auto alfa — tiap menit antara jam 06:00–12:00
* 6-12 * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan absen:auto-alfa >> /dev/null 2>&1

# Auto-fill absensi — tiap hari jam 13:00
0 13 * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan attendance:auto-fill >> /dev/null 2>&1

# Sinkron izin → absen — tiap 5 menit jam 08:00–23:59
*/5 8-23 * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan attendance:update-from-approved-izin >> /dev/null 2>&1

# Auto poin harian (hadir/terlambat/alfa) — tiap hari jam 14:00
0 14 * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan absen:auto-poin-harian >> /dev/null 2>&1

# Auto poin pelanggaran event — tiap 5 menit
*/5 * * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan event:auto-point-pelanggaran >> /dev/null 2>&1

# Auto penghargaan event — tiap 5 menit
*/5 * * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan event:auto-penghargaan >> /dev/null 2>&1

# Rotasi barcode — tiap menit
* * * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan event:rotate-barcodes >> /dev/null 2>&1

# Cek orange — tiap menit
* * * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan sis:check-orange >> /dev/null 2>&1

# Sync transaksi — tiap 30 menit
*/30 * * * * /www/server/php/82/bin/php /www/wwwroot/sis-app/artisan tatib:sync-transaksi >> /dev/null 2>&1
```

---

### 5.5 Command yang dijalankan scheduler (ringkasan dari `Kernel.php`)

| Command | Jadwal | Keterangan |
|---------|--------|------------|
| `absen:generate-harian` | Tiap hari 05:30 | Generate record absensi kosong semua siswa |
| `absen:auto-alfa` | Tiap menit 06:00-12:00 | Set status alfa jika belum scan setelah jam eksekusi |
| `attendance:auto-fill` | Tiap hari 13:00 | Auto-fill absensi setelah jam belajar |
| `attendance:update-from-approved-izin` | Tiap 5 menit 08:00-23:59 | Sinkron izin disetujui ke status absen |
| `event:auto-point-pelanggaran` | Tiap 5 menit | Beri poin pelanggaran siswa yang tidak scan event |
| `event:auto-penghargaan` | Tiap 5 menit | Beri poin penghargaan siswa yang scan event |
| `event:rotate-barcodes` | Tiap menit | Rotasi barcode event aktif |
| `sis:check-orange` | Tiap menit | Tandai kelas belum laporan setelah 20 menit |
| `tatib:sync-transaksi` | Tiap 30 menit | Sinkronisasi poin ke tbltransaksi |

---

## 6. Hasil Test Lokal (2026-08-11)

Semua command diuji coba dan hasilnya:

```
php artisan sis:test-feature --no-interaction
→ PASS 59 | FAIL 0 | SKIP 2 | TOTAL 61
→ Cleanup otomatis: semua data test terhapus bersih

php artisan absen:generate-harian
→ Dibuat: 2 | Dilewati (sudah ada): 1136 | Gagal: 0

php artisan absen:auto-alfa
→ Alfa: 176 diproses | Sudah absen: 962 | Poin: 2 | WA: 0

php artisan attendance:auto-fill
→ siswa=1138 | created=0 | skipped=949 | alfa=175

php artisan attendance:update-from-approved-izin
→ izin=14 | updated=0 | unchanged=14

php artisan event:auto-point-pelanggaran
→ Tidak ada event yang perlu diproses.

php artisan event:auto-penghargaan
→ Tidak ada event yang perlu diproses.

php artisan event:rotate-barcodes
→ 0 event di-rotate.

php artisan sis:check-orange
→ 35 laporan orange dibuat.

php artisan tatib:sync-transaksi
→ Semua sudah tersinkronisasi (0 pending).

php artisan absen:auto-poin-harian
→ Hadir: 0 | Terlambat: 0 | Alfa: 0 | Dilewati: 1124
  (normal — auto_poin_hadir/terlambat belum dikonfigurasi)
```

---

## 7. Fitur Baru yang Perlu Dikonfigurasi di Admin Panel

Setelah deploy & migrate, buka `/admin/school-config` dan lengkapi:

### 7.1 Notifikasi WhatsApp
- [ ] Toggle **WA Notif Pulang** (`wa_notif_pulang_enabled`)
- [ ] Toggle **WA Notif Event** (`wa_notif_event_enabled`)
- [ ] Toggle **WA Notif Tatib** (`wa_notif_tatib_enabled`)
- [ ] Toggle **WA Notif Laporan Guru** + isi nomor penerima JSON array

### 7.2 Mode Libur Panjang
- [ ] Isi **Tanggal Libur Dari** & **Libur Sampai** jika ada libur panjang
- [ ] Aktifkan toggle **Libur Mode** saat libur berlangsung

### 7.3 Auto Poin Kehadiran Harian
- [ ] Toggle **Auto Poin Hadir** + pilih pasal penghargaan
- [ ] Toggle **Auto Poin Terlambat** + pilih pasal pelanggaran

### 7.4 Auto Pelanggaran Rules
Buat rule di `/admin/auto-pelanggaran-rules`:
```
Contoh rule alfa harian:
  nama_rule    : Alfa Harian
  trigger_type : alfa_harian
  threshold    : 1
  periode_bulan: 0
  pasal_id     : (pilih pasal alfa)
```

### 7.5 Auto Penghargaan Rules
Buat rule di `/admin/auto-penghargaan-rules`:
```
Contoh rule hadir penuh bulanan:
  nama_rule    : Hadir Penuh Bulanan
  trigger_type : full_hadir_bulanan
  periode_bulan: 1
  izin=hadir   : true
  sakit=hadir  : true
  pasal_id     : (pilih pasal penghargaan)
```

---

## 8. Fitur Baru yang Langsung Aktif Setelah Migrate

Tidak perlu konfigurasi tambahan, langsung berfungsi:

| Fitur | Keterangan |
|-------|------------|
| Absen unified (masuk+pulang 1 record) | `absen_siswa` & `absen_event` pakai pola 1 record/hari |
| Soft delete pelanggaran & penghargaan | Data tidak hilang permanen, bisa dipulihkan |
| Audit log absensi | Setiap aksi admin tercatat di `absen_activity_log` |
| Event ekstrakurikuler | Kolom is_ekstrakurikuler, pelatih 1–3, pembina |
| Foto kegiatan event | Upload maks 10 foto per event di form event |
| Auto penghargaan event | Toggle per event, scan masuk → poin otomatis |
| Override poin event | Poin pelanggaran/penghargaan bisa di-override per event |
| Kelompok jam (reguler/jumat) | Jadwal Jumat bisa beda dari jadwal reguler |

---

## 9. Verifikasi Setelah Deploy

```bash
# Cek migrasi sudah semua Ran
php artisan migrate:status | grep Pending
# → Harus kosong (tidak ada Pending)

# Jalankan test fitur
php artisan sis:test-feature --no-interaction
# → PASS semua, FAIL 0

# Cek scheduler berjalan
php artisan schedule:list
```

---

## 10. Troubleshooting Umum

| Problem | Solusi |
|---------|--------|
| `SQLSTATE[HY000] No connection` | MySQL belum jalan / cek DB_HOST, DB_PORT di .env |
| `Class not found` setelah deploy | Jalankan `composer dump-autoload` |
| Permission denied storage | `chmod -R 777 storage bootstrap/cache` |
| Barcode tidak rotate | Pastikan cron aktif dan `event:rotate-barcodes` bisa diakses |
| WA notif tidak terkirim | Cek konfigurasi WhatsApp di school-config, pastikan nomor format internasional |
| Foto event tidak muncul | Pastikan `php artisan storage:link` sudah dijalankan |
| Auto-alfa tidak jalan | Cek `auto_alfa_enabled=true` & `jam_eksekusi_auto_alfa` di school-config |
| Poin tidak masuk tbltransaksi | Jalankan `php artisan tatib:sync-transaksi` untuk backfill |
