# Plan Perbaikan Sistem - Analisis Error Log
**Periode log:** 15 Juli 2026 – 13 Agustus 2026  
**Tanggal analisis:** 13 Agustus 2026

---

## Ringkasan Temuan

Setelah menganalisis seluruh log dari folder `error-logs/` (4 kategori: `sis`, `absen`, `gtk`, `wa`, `point-pelanggaran`), berikut adalah semua masalah yang ditemukan beserta prioritas perbaikannya.

---

## MASALAH 1 — Siswa ID 4526 Tidak Memiliki Nomor HP Ortu

**Tingkat keparahan:** ⚠️ WARNING (berulang setiap hari)  
**Kategori log:** `wa-*.log`  
**Contoh log:**
```
[2026-08-13 04:54:32] local.WARNING: [Event] Tidak ada nomor HP ortu tersedia. {"absen_event_id":11706,"siswa_id":4526}
[2026-08-13 12:14:17] local.WARNING: [Event] Tidak ada nomor HP ortu tersedia. {"absen_event_id":11804,"siswa_id":4526}
[2026-08-13 15:42:06] local.WARNING: [Event] Tidak ada nomor HP ortu tersedia. {"absen_event_id":13031,"siswa_id":4526}
[2026-08-13 18:04:34] local.WARNING: [Event] Tidak ada nomor HP ortu tersedia. {"absen_event_id":13238,"siswa_id":4526}
```

**Analisis:**  
Siswa dengan ID `4526` tidak memiliki nomor HP ortu1 **maupun** ortu2 yang terdaftar di sistem. Warning ini muncul setiap kali siswa tersebut melakukan absen event — bisa muncul puluhan kali per hari tergantung jumlah event aktif.

**Rencana perbaikan:**
- [ ] Buka data siswa ID 4526, lengkapi nomor HP orang tua.
- [ ] Tambahkan validasi di form pendaftaran siswa agar nomor HP ortu wajib diisi.
- [ ] Buat query untuk menemukan semua siswa lain yang tidak punya nomor HP ortu sama sekali: `SELECT * FROM siswas WHERE no_hp_ortu1 IS NULL AND no_hp_ortu2 IS NULL` (sesuaikan nama kolom).
- [ ] Opsional: buat notifikasi admin ketika ada siswa baru tanpa nomor HP ortu.

---

## MASALAH 2 — Pengiriman WA Gagal (HTTP 502 Bad Gateway)

**Tingkat keparahan:** 🔴 ERROR (terjadi massal, tercatat pada 10 Agustus 2026)  
**Kategori log:** `wa-2026-08-10.log`  
**Contoh log:**
```
[2026-08-10 15:30:54] local.ERROR: [WA] Gagal kirim {"to":"6289518610120","jenis":"event","http_status":502,"body":"error code: 502","durasi_ms":6356.0}
[2026-08-10 15:30:54] local.WARNING: [Event] Ortu1 gagal, fallback ke ortu2. {"absen_event_id":8522,"no_hp":"089518610120"}
[2026-08-10 15:30:54] local.ERROR: [WA] Gagal kirim {"to":"6289617461766","jenis":"event","http_status":502,...}
[2026-08-10 15:30:54] local.ERROR: [WA] Gagal kirim {"to":"6281235596184","jenis":"event","http_status":502,...}
```

**Analisis:**  
Pada tanggal 10 Agustus 2026 sekitar pukul 15:30, terjadi **kegagalan massal pengiriman WA** ke banyak nomor sekaligus dengan HTTP 502 (Bad Gateway). Ini mengindikasikan WA Gateway server sedang down atau overload. Mekanisme fallback ke ortu2 berjalan, namun jika ortu2 juga tidak ada maka notifikasi tidak terkirim sama sekali. Durasi timeout mencapai 6+ detik per request menunjukkan koneksi sangat lambat sebelum gagal.

**Rencana perbaikan:**
- [ ] Cek dengan provider WA Gateway apakah ada incident pada 10 Agustus 2026 pukul 15:30 WIB.
- [ ] Implementasikan **retry queue** — jika kirim WA gagal, masukkan ke queue untuk dicoba ulang setelah beberapa menit (contoh: retry 3x dengan delay 5 menit).
- [ ] Kurangi timeout WA request dari default ke ~10 detik, dan tambahkan circuit breaker agar tidak blocking terlalu lama.
- [ ] Catat ke database pengiriman WA yang gagal agar admin bisa melihat history failure.
- [ ] Pertimbangkan job queue (Laravel Queue + Redis/database driver) untuk pengiriman WA event agar tidak blocking proses absen.

---

## MASALAH 3 — Login Gagal Berulang (Brute Force / Salah Password)

**Tingkat keparahan:** ⚠️ WARNING  
**Kategori log:** `sis-*.log`  
**Contoh log:**
```
[2026-08-13 00:47:21] local.WARNING: [Auth] Login failed {"username":"0092299003","ip":"127.0.0.1"}
[2026-08-09 11:40:41] local.WARNING: [Auth] Login failed {"username":"010362622","ip":"127.0.0.1"}
[2026-08-09 11:40:55] local.WARNING: [Auth] Login failed {"username":"010362622","ip":"127.0.0.1"}
[2026-08-09 11:40:56] local.WARNING: [Auth] Login failed {"username":"010362622","ip":"127.0.0.1"}
```

**Analisis:**  
Ada pola login gagal berulang dengan username yang sama dalam rentang detik yang sangat singkat (misalnya username `010362622` gagal 3x dalam 15 detik). Ini bisa berarti:
1. Siswa lupa password dan mencoba berulang kali.
2. Potensi bot / brute force attack.

**Rencana perbaikan:**
- [ ] Tambahkan **rate limiting** pada endpoint login: maksimal 5 percobaan dalam 1 menit per IP atau per username.
- [ ] Setelah N kali gagal (misal 5x), tampilkan CAPTCHA atau lock account sementara (5-10 menit).
- [ ] Tambahkan fitur **reset password** yang mudah diakses oleh siswa (misalnya via nomor HP yang terdaftar).
- [ ] Log alert ke admin jika ada >10 percobaan gagal dari satu IP dalam 1 menit.

---

## MASALAH 4 — Laporan Kehadiran GTK Tanpa Respon (Request Tergantung)

**Tingkat keparahan:** ⚠️ WARNING  
**Kategori log:** `gtk-2026-08-11.log`, `gtk-2026-08-12.log`  
**Contoh log:**
```
[2026-08-12 08:43:40] local.INFO: [LaporanKehadiran] Siswa lapor kehadiran guru {"user_id":4075}
[2026-08-11 12:23:37] local.INFO: [LaporanKehadiran] Siswa lapor kehadiran guru {"user_id":4075}
```
*(Tidak ada log "berhasil lapor" setelahnya untuk user 4075 pada kedua tanggal ini)*

**Analisis:**  
User ID `4075` mencoba melaporkan kehadiran guru tapi tidak ada log sukses setelahnya. Kemungkinan:
1. Request timeout sebelum controller selesai diproses.
2. Ada exception yang tidak ter-log dengan benar.
3. Validasi gagal tapi tidak ada response/log error yang memadai.

**Rencana perbaikan:**
- [ ] Tambahkan log eksplisit di block `catch` pada `LaporanKehadiranController` agar exception tercatat.
- [ ] Pastikan ada log juga ketika request **gagal validasi** (misal: sudah melewati batas waktu lapor).
- [ ] Cek apakah user 4075 memiliki problem khusus (kelas, jadwal, dsb).
- [ ] Tambahkan try-catch menyeluruh dengan logging di method `store` dan `update` laporan.

---

## MASALAH 5 — Notifikasi WA Laporan GTK Dinonaktifkan Secara Permanen

**Tingkat keparahan:** ℹ️ INFO (bukan error, tapi perlu evaluasi)  
**Kategori log:** `gtk-*.log`  
**Contoh log:**
```
[2026-08-13 08:19:28] local.INFO: [LaporanKehadiran] Notifikasi WA laporan guru dinonaktifkan, skip. {"laporan_id":451}
[2026-08-13 08:23:44] local.INFO: [LaporanKehadiran] Notifikasi WA laporan guru dinonaktifkan, skip. {"laporan_id":452}
```

**Analisis:**  
Semua notifikasi WA untuk laporan kehadiran GTK dinonaktifkan. Ini muncul di **setiap** laporan tanpa kecuali. Kemungkinan fitur ini dimatikan via config/database tapi belum pernah dievaluasi kembali.

**Rencana perbaikan:**
- [ ] Evaluasi apakah notifikasi WA GTK perlu diaktifkan kembali.
- [ ] Jika memang permanen dimatikan, pertimbangkan menghapus kode notifikasi WA tersebut agar log tidak penuh dengan pesan "dinonaktifkan, skip".
- [ ] Jika ingin diaktifkan per-kondisi, buat toggle di `SchoolConfig` atau setting per-admin.

---

## MASALAH 6 — Cron Job `CheckOrange` Berjalan Terlalu Sering

**Tingkat keparahan:** ℹ️ INFO (masalah performa)  
**Kategori log:** `sis-*.log`  
**Contoh log:**
```
[2026-08-13 00:00:02] local.INFO: [CheckOrange] Mulai pengecekan ...
[2026-08-13 00:01:01] local.INFO: [CheckOrange] Mulai pengecekan ...
[2026-08-13 00:02:01] local.INFO: [CheckOrange] Mulai pengecekan ...
... (setiap menit, 24 jam penuh)
```

**Analisis:**  
`CheckKelasOrange` berjalan **setiap menit** non-stop, bahkan di luar jam sekolah (tengah malam, akhir pekan). Ini menghasilkan ratusan log entry per hari yang tidak berguna, menghabiskan disk space, dan membebani database dengan query yang tidak perlu.

**Rencana perbaikan:**
- [ ] Batasi jadwal `CheckOrange` hanya di jam sekolah, misalnya: `06:00 - 18:00` pada hari Senin–Sabtu.
- [ ] Di `Kernel.php`, ubah dari `->everyMinute()` menjadi `->everyMinute()->between('06:00', '18:00')->weekdays()` (atau sesuai kebutuhan sekolah).
- [ ] Pertimbangkan apakah cek setiap menit memang perlu — mungkin setiap 5 menit sudah cukup.

---

## MASALAH 7 — Cron Job `AutoPenghargaan` & `AutoPointPelanggaran` Berjalan Setiap 5 Menit Sepanjang Hari

**Tingkat keparahan:** ℹ️ INFO (masalah performa)  
**Kategori log:** `sis-*.log`, `point-pelanggaran-*.log`  
**Contoh log:**
```
[2026-08-13 00:00:01] local.INFO: [AutoPenghargaan] ========== AUTO PENGHARGAAN DIMULAI ==========
[2026-08-13 00:00:01] local.INFO: [AutoPenghargaan] Tidak ada event yang memenuhi syarat untuk diproses.
[2026-08-13 00:05:02] local.INFO: [AutoPenghargaan] ========== AUTO PENGHARGAAN DIMULAI ==========
[2026-08-13 00:05:02] local.INFO: [AutoPenghargaan] Tidak ada event yang memenuhi syarat untuk diproses.
... (berulang sepanjang malam)
```

**Analisis:**  
`AutoPenghargaan` dan `AutoPointPelanggaran` berjalan setiap 5 menit sepanjang 24 jam, termasuk di malam hari saat tidak ada event sama sekali. Setiap run mencatat 3 baris log "tidak ada event". Ini menghasilkan ribuan baris log tidak berguna per hari.

**Rencana perbaikan:**
- [ ] Batasi jam operasional cron ini, misalnya hanya antara `05:00 - 22:00`.
- [ ] Atau tambahkan check awal: jika tidak ada event aktif hari ini, skip tanpa menulis log "DIMULAI/SELESAI".
- [ ] Pertimbangkan frekuensi yang lebih rendah untuk AutoPenghargaan — misalnya setiap 15-30 menit sudah cukup.
- [ ] Jangan log ke level INFO saat "tidak ada yang perlu diproses" — gunakan level DEBUG saja agar tidak memenuhi log production.

---

## MASALAH 8 — Siswa Memuat Halaman Absen Berulang Kali (Possible UX Issue)

**Tingkat keparahan:** ℹ️ INFO (observasi UX)  
**Kategori log:** `sis-2026-08-12.log`  
**Contoh log:**
```
[2026-08-12 00:54:01] local.INFO: [Absen Masuk] Mulai {"user_id":4528,...}
[2026-08-12 00:54:10] local.INFO: [Absen] Halaman absen unified {"user_id":4528}
[2026-08-12 00:54:18] local.INFO: [Absen Masuk] Mulai {"user_id":4528,...}
```

**Analisis:**  
User 4528 memulai absen masuk pada 00:54:01, lalu membuka halaman absen lagi 9 detik kemudian, dan mencoba absen masuk lagi. Ini menunjukkan kemungkinan:
1. Response absen pertama lambat atau tidak ada feedback yang jelas ke user.
2. User mengklik tombol absen berkali-kali karena tidak yakin sudah berhasil.

**Rencana perbaikan:**
- [ ] Tambahkan **loading state** / spinner pada tombol absen setelah diklik agar user tahu proses sedang berjalan.
- [ ] Nonaktifkan tombol setelah diklik pertama kali (disabled) sampai response diterima.
- [ ] Tampilkan pesan sukses/gagal yang jelas setelah absen.
- [ ] Pastikan ada proteksi duplikasi di backend (unique constraint atau check sudah absen hari ini).

---

## MASALAH 9 — Log File `laravel.log` Terlalu Besar (>50MB)

**Tingkat keparahan:** ⚠️ Operasional  
**Catatan:** File `error-logs/laravel.log` tidak dapat dibuka karena ukurannya melebihi 50MB.

**Analisis:**  
Log Laravel utama sudah sangat besar dan tidak bisa dianalisis. Ini mengindikasikan tidak ada log rotation yang dikonfigurasi dengan baik, atau terlalu banyak yang ditulis ke log.

**Rencana perbaikan:**
- [ ] Konfigurasi log rotation di `config/logging.php` — gunakan driver `daily` agar log dipecah per hari.
- [ ] Set `LOG_CHANNEL=daily` di `.env` jika belum.
- [ ] Tambahkan batas retensi log, misalnya hapus log yang lebih dari 30 hari: `'days' => 30`.
- [ ] Review apa yang menyebabkan log menjadi sebesar itu — kemungkinan ada exception yang terus berulang.

---

---

## BAGIAN 2 — Analisis `laravel.log` (Server Hosting)

> Log ini berasal langsung dari server hosting (`ep.edutec.my.id`), berisi error level aplikasi Laravel.
> Rentang log: **15 Juli 2026 – 9 Agustus 2026** (file >50MB, ~73.000+ baris).

---

## MASALAH L1 — `storage/` Permission Denied — Sistem Tidak Bisa Nulis Log & View Cache

**Tingkat keparahan:** 🔴 KRITIS — menyebabkan HTTP 500  
**Tanggal kejadian:** 31 Juli, 3 Agustus, 7–8 Agustus 2026  
**Contoh log:**

```
[2026-08-08 06:26:19] local.ERROR: [HTTP 500] ErrorException: file_put_contents(
  /www/wwwroot/ep.edutec.my.id/sis/storage/framework/views/8dac2e30...php
): Failed to open stream: Permission denied
{"url":"https://ep.edutec.my.id/absen/rekap","user_id":1}

[2026-08-03 08:11:27] local.ERROR: The stream or file
  "/www/wwwroot/ep.edutec.my.id/sis/storage/logs/sis-2026-08-03.log"
could not be opened in append mode: Failed to open stream: Permission denied

[2026-08-08 07:15:15] local.ERROR: The stream or file
  "/www/wwwroot/.../storage/logs/point-pelanggaran-2026-08-08.log"
could not be opened in append mode: Failed to open stream: Permission denied
```

**Analisis:**  
Direktori `storage/` dan subdirektorinya (`storage/logs/`, `storage/framework/views/`) kehilangan permission write di server hosting. Ini terjadi berulang di beberapa tanggal berbeda, kemungkinan akibat:

1. Deploy/upload file yang menimpa permission folder.
2. Shared hosting yang secara otomatis reset permission setelah update.
3. File baru yang dibuat dengan user berbeda (misal: www-data vs user FTP).

Dampaknya serius: halaman `/absen/rekap` dan `/event/create` menghasilkan **HTTP 500** karena Laravel tidak bisa compile blade template ke cache.

**Rencana perbaikan:**

- [ ] Jalankan di server: `chmod -R 775 storage/ bootstrap/cache/` dan `chown -R www-data:www-data storage/ bootstrap/cache/`
- [ ] Buat script post-deploy yang otomatis fix permission setiap kali ada deployment baru.
- [ ] Tambahkan monitoring permission — alert jika direktori `storage/` tidak writable.
- [ ] Gunakan `php artisan storage:link` setelah deploy untuk memastikan symlink masih benar.
- [ ] Jika menggunakan shared hosting, tanyakan ke provider apakah ada mekanisme persistent permission.

---

## MASALAH L2 — `PelanggaranObserver` Tidak Ditemukan (HTTP 500 Massal)

**Tingkat keparahan:** 🔴 KRITIS — menyebabkan seluruh aplikasi crash  
**Tanggal kejadian:** 8 Agustus 2026, mulai pukul 09:17 hingga sore hari  
**Contoh log:**

```
[2026-08-08 09:17:44] local.ERROR: Unable to find observer: App\Observers\PelanggaranObserver
[2026-08-08 09:17:57] local.ERROR: [HTTP 500] InvalidArgumentException: Unable to find observer:
  App\Observers\PelanggaranObserver
  {"url":"https://ep.edutec.my.id","user_agent":"edutech"}

[2026-08-08 09:18:10] local.ERROR: [HTTP 500] InvalidArgumentException: Unable to find observer:
  App\Observers\PelanggaranObserver
  {"url":"https://ep.edutec.my.id/history.back()"}

[2026-08-08 09:20:48] local.ERROR: [HTTP 500] InvalidArgumentException: Unable to find observer:
  App\Observers\PelanggaranObserver
  {"url":"https://ep.edutec.my.id/profile"}
```

**Analisis:**  
`App\Observers\PelanggaranObserver` di-register di `AppServiceProvider` atau `EventServiceProvider`, tetapi file class-nya tidak ada di server (kemungkinan tidak ikut ter-upload saat deploy, atau terhapus). Akibatnya **setiap request yang menyentuh model `Pelanggaran`** — termasuk halaman home, profile, dan dashboard — menghasilkan HTTP 500. Error ini terjadi ratusan kali dalam satu hari dan membuat sistem tidak bisa digunakan.

**Rencana perbaikan:**

- [ ] **Segera**: Pastikan file `app/Observers/PelanggaranObserver.php` ada di repository dan ter-push ke server.
- [ ] Jalankan `php artisan optimize:clear` setelah deploy untuk flush cache class map.
- [ ] Tambahkan pengecekan di CI/CD: verifikasi semua file yang di-register di ServiceProvider benar-benar exist.
- [ ] Jangan register observer dengan string class langsung jika file bisa tidak ada — gunakan `if (class_exists(PelanggaranObserver::class))` sebagai safeguard.
- [ ] Buat checklist deployment yang mencakup: upload semua file `app/Observers/`, `app/Listeners/`, `app/Events/`.

---

## MASALAH L3 — `mb_split()` Undefined Function (PHP Extension Hilang)

**Tingkat keparahan:** 🔴 KRITIS — menyebabkan HTTP 500 di semua halaman  
**Tanggal kejadian:** 16 Juli 2026, pukul 12:51 – 13:58 (berlangsung ~1 jam)  
**Contoh log:**

```
[2026-07-16 12:51:33] local.ERROR: Call to undefined function Illuminate\Support\mb_split()
  at /www/wwwroot/.../vendor/laravel/framework/src/Illuminate/Support/Str.php:1641

[2026-07-16 12:53:32] local.ERROR: Call to undefined function Illuminate\Support\mb_split()
[2026-07-16 13:38:05] local.ERROR: Call to undefined function Illuminate\Support\mb_split()
[2026-07-16 13:58:00] local.ERROR: Call to undefined function Illuminate\Support\mb_split()
```

**Analisis:**  
`mb_split()` adalah bagian dari PHP extension `mbstring`. Error ini berarti extension `mbstring` tidak aktif atau tidak ter-load di PHP server saat kejadian. Kemungkinan penyebab:

1. Hosting melakukan update PHP atau restart yang menonaktifkan extension.
2. File `php.ini` berubah dan `extension=mbstring` tidak aktif.
3. Versi PHP yang digunakan tidak compatible dengan versi Laravel yang terinstall.

Dampaknya: **semua halaman yang menggunakan `Str::` helper Laravel** menjadi error 500. Error berlangsung ~1 jam sebelum kemungkinan diperbaiki manual.

**Rencana perbaikan:**

- [ ] Pastikan `mbstring` aktif secara permanen di konfigurasi PHP hosting.
- [ ] Tambahkan ke `composer.json` requirement: `"ext-mbstring": "*"` agar ada warning saat deploy jika extension tidak tersedia.
- [ ] Buat health check endpoint `GET /health` yang memverifikasi PHP extensions yang dibutuhkan (`mbstring`, `pdo_mysql`, `gd`, `zip`, dll).
- [ ] Koordinasi dengan provider hosting untuk tidak mengubah konfigurasi PHP tanpa pemberitahuan.

---

## MASALAH L4 — Database MySQL Tidak Bisa Dikoneksi (SQLSTATE HY000 [2002])

**Tingkat keparahan:** 🔴 KRITIS — seluruh aplikasi down  
**Tanggal kejadian:** 16 Juli 2026, pukul 11:33 – 11:40 (berlangsung ~7 menit)  
**Contoh log:**

```
[2026-07-16 11:33:42] local.ERROR: SQLSTATE[HY000] [2002] No connection could be made because
  the target machine actively refused it
  (SQL: select * from `cache` where `key` in (sekolah_data))
  (View: .../resources/views/auth/login.blade.php)

[2026-07-16 11:34:18] local.ERROR: SQLSTATE[HY000] [2002] No connection could be made ...
[2026-07-16 11:34:45] local.ERROR: SQLSTATE[HY000] [2002] No connection could be made ...
[2026-07-16 11:38:56] local.ERROR: SQLSTATE[HY000] [2002] No connection could be made ...
[2026-07-16 11:40:06] local.ERROR: SQLSTATE[HY000] [2002] No connection could be made ...
```

**Analisis:**  
MySQL server di hosting menolak koneksi selama ~7 menit. Bahkan halaman login tidak bisa tampil karena view blade memanggil query `cache` yang butuh koneksi database. Penyebab paling umum: MySQL service restart/crash di server, atau koneksi pool habis.

**Rencana perbaikan:**

- [ ] Konfigurasi **database retry** di Laravel: set `DB_RETRY=3` atau gunakan `reconnect()` untuk koneksi yang hilang.
- [ ] Gunakan **Redis untuk cache** (`CACHE_DRIVER=redis`) agar halaman login tidak tergantung database untuk menampilkan logo sekolah.
- [ ] Pisahkan query cache sekolah (`sekolah_data`) dari critical path halaman login — jadikan fallback ke nilai default jika cache tidak tersedia.
- [ ] Setup monitoring uptime database dan alert ke admin jika MySQL down > 1 menit.
- [ ] Pertimbangkan persistent connection atau connection pooling (misal: ProxySQL) jika hosting mendukung.

---

## MASALAH L5 — HTTP 503 Service Unavailable (Maintenance Mode Tidak Sengaja Aktif)

**Tingkat keparahan:** 🔴 KRITIS — seluruh aplikasi tidak bisa diakses user  
**Tanggal kejadian:** 7–8 Agustus 2026, pukul 23:37 hingga pagi hari  
**Contoh log:**

```
[2026-08-07 23:37:04] local.ERROR: [HTTP 503] HttpException: Service Unavailable
  {"url":"https://ep.edutec.my.id","user_agent":"edutech"}

[2026-08-07 23:39:25] local.ERROR: [HTTP 503] HttpException: Service Unavailable
  {"url":"https://ep.edutec.my.id/absen","user_agent":"iPhone iOS 18_5"}

[2026-08-07 23:43:52] local.ERROR: [HTTP 503] HttpException: Service Unavailable
  {"url":"https://ep.edutec.my.id/absen/status-hari-ini","user_agent":"Android"}

[2026-08-08 06:28:20] local.ERROR: [HTTP 503] HttpException: Service Unavailable
  {"url":"https://ep.edutec.my.id","user_agent":"edutech"}
```

**Analisis:**  
Laravel **maintenance mode** (`php artisan down`) aktif selama lebih dari 7 jam (dari ~23:37 malam hingga pagi hari berikutnya). Selama itu, siswa yang mencoba absen dari HP (iPhone, Android) mendapat halaman 503 dan tidak bisa absen. User agent `"edutech"` yang berulang menunjukkan ada monitoring/healthcheck internal yang juga gagal.

**Rencana perbaikan:**

- [ ] **SOP Maintenance:** Selalu jalankan `php artisan up` segera setelah deployment selesai. Jangan tinggalkan server dalam maintenance mode semalaman.
- [ ] Gunakan `php artisan down --secret="token-rahasia"` agar admin masih bisa akses saat maintenance, lalu `php artisan up` setelah selesai.
- [ ] Tambahkan **monitoring otomatis**: alert ke admin via WA/Telegram jika situs tidak bisa diakses > 5 menit.
- [ ] Buat jadwal maintenance yang dikomunikasikan ke user (siswa/guru) sebelumnya, terutama kalau maintenance di jam sekolah.
- [ ] Pertimbangkan deployment strategy yang tidak memerlukan downtime (zero-downtime deploy).

---

## MASALAH L6 — HTTP 404 `_service-worker.js` Berulang Ratusan Kali

**Tingkat keparahan:** ⚠️ WARNING (noise log, bukan error fungsional)  
**Tanggal kejadian:** Setiap hari mulai 7 Agustus 2026  
**Contoh log:**

```
[2026-08-07 23:26:02] local.ERROR: [HTTP 404] NotFoundHttpException:
  The route _service-worker.js could not be found.
  {"url":"https://ep.edutec.my.id/_service-worker.js","user_id":null}

[2026-08-08 07:10:47] local.ERROR: [HTTP 404] NotFoundHttpException:
  The route _service-worker.js could not be found.

[2026-08-09 00:00:11] local.ERROR: [HTTP 404] NotFoundHttpException:
  The route _service-worker.js could not be found.
```

**Analisis:**  
Browser (terutama Chrome di Android dan iOS) secara otomatis mencari `/_service-worker.js` untuk mendaftarkan PWA Service Worker. Karena file ini tidak ada di aplikasi, setiap kunjungan pengguna dari mobile browser menghasilkan satu error 404 di log. Dengan ratusan pengguna aktif, ini menghasilkan ratusan entri error per hari yang **mengotori log dan menyembunyikan error nyata**.

**Rencana perbaikan:**

- [ ] **Opsi A (direkomendasikan):** Daftarkan route yang mengembalikan response kosong/204:
  ```php
  Route::get('/_service-worker.js', fn() => response('', 204));
  ```
- [ ] **Opsi B:** Buat file `public/_service-worker.js` kosong agar dilayani langsung oleh web server tanpa menyentuh Laravel.
- [ ] **Opsi C:** Filter error 404 untuk URL ini agar tidak ditulis ke log — tambahkan ke `$dontReport` di `Handler.php`.
- [ ] Hal serupa juga berlaku untuk `apple-touch-icon*.png` dan `apple-touch-icon-precomposed.png` yang juga sering 404.

---

## MASALAH L7 — HTTP 404 `history.back()` — Bug JavaScript di View

**Tingkat keparahan:** ⚠️ WARNING — bug kode di sisi frontend  
**Tanggal kejadian:** 7–9 Agustus 2026  
**Contoh log:**

```
[2026-08-07 23:43:04] local.ERROR: [HTTP 404] NotFoundHttpException:
  The route history.back() could not be found.
  {"url":"https://ep.edutec.my.id/history.back()","user_id":null}

[2026-08-08 09:18:10] local.ERROR: [HTTP 500] InvalidArgumentException:
  Unable to find observer: App\Observers\PelanggaranObserver
  {"url":"https://ep.edutec.my.id/history.back()"}
```

**Analisis:**  
Ada **bug di salah satu view Blade** di mana terdapat kode seperti:

```html
<a href="history.back()">Kembali</a>
```

Seharusnya:

```html
<a href="javascript:history.back()">Kembali</a>
```

atau menggunakan tombol dengan `onclick`. Akibatnya browser melakukan request ke URL literal `https://ep.edutec.my.id/history.back()` yang tentu saja tidak ada sebagai route. Error ini pasti terlihat oleh user sebagai halaman 404 saat mengklik tombol "Kembali".

**Rencana perbaikan:**

- [ ] Cari semua view yang menggunakan `href="history.back()"` tanpa prefix `javascript:`:
  ```
  grep -r 'href="history.back()' resources/views/
  ```
- [ ] Ganti semua kemunculan dengan `href="javascript:history.back()"` atau gunakan `onclick="history.back()"` pada elemen button.
- [ ] Lebih baik lagi, ganti dengan link back yang explicit ke route sebelumnya menggunakan `url()->previous()` di Laravel.

---

## MASALAH L8 — `ValidationException` Dilempar sebagai HTTP 500

**Tingkat keparahan:** ⚠️ WARNING — error handling tidak tepat  
**Tanggal kejadian:** 7 Agustus 2026  
**Contoh log:**

```
[2026-08-07 23:42:47] local.ERROR: [HTTP 500] Illuminate\Validation\ValidationException:
  Permission dengan nama tersebut sudah ada.
  {"url":"https://ep.edutec.my.id/admin/permissions","method":"POST","user_id":1}
```

**Analisis:**  
`ValidationException` seharusnya menghasilkan HTTP 422 (Unprocessable Entity) dan redirect kembali dengan error message, bukan HTTP 500. Ini terjadi karena validasi duplicate dilakukan dengan cara yang salah — kemungkinan `throw new ValidationException(...)` dipanggil langsung di luar konteks yang tepat, atau ada error di form request yang tidak ditangani dengan benar.

**Rencana perbaikan:**

- [ ] Cek `app/Http/Controllers/Admin/RoleController.php` atau controller permission terkait — pastikan validasi unique menggunakan `Rule::unique()` di FormRequest, bukan throw manual.
- [ ] Pastikan handler exception di `app/Exceptions/Handler.php` meng-handle `ValidationException` dengan benar (return 422, bukan 500).
- [ ] Tambahkan validasi `unique` yang proper:
  ```php
  'name' => ['required', Rule::unique('permissions')->ignore($id)]
  ```

---

## MASALAH L9 — HTTP 401 Unauthenticated pada Halaman Admin dari Bot/Scanner

**Tingkat keparahan:** ℹ️ INFO (security observation)  
**Tanggal kejadian:** 9 Agustus 2026  
**Contoh log:**

```
[2026-08-09 15:34:06] local.ERROR: [HTTP 401] AuthenticationException: Unauthenticated.
  {"url":"https://ep.edutec.my.id/event/create","user_agent":
  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/600.1.25 Safari/600.1.25"}

[2026-08-09 15:44:10] local.ERROR: [HTTP 401] AuthenticationException: Unauthenticated.
  {"url":"https://ep.edutec.my.id/absen/rekap?kelas_id=&status=&tanggal_mulai=..."}

[2026-08-09 16:46:29] local.ERROR: [HTTP 404] NotFoundHttpException:
  The route build/plus could not be found.
  {"user_agent":"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)"}
```

**Analisis:**  
Ada permintaan dari user agent mencurigakan (`Mac OS X 10_10_1` dengan Safari lama yang sudah obsolete, `MSIE 6.0` yang tidak digunakan siapapun sejak 2014) yang mencoba akses URL admin tanpa login. Ini adalah **security scanner / bot** yang melakukan reconnaissance terhadap aplikasi. Request ke `build/plus` adalah pola umum scanner vulnerabilitas.

**Rencana perbaikan:**

- [ ] Implementasikan **rate limiting** lebih agresif untuk request yang menghasilkan 401/403.
- [ ] Pertimbangkan memblokir user agent yang jelas-jelas bot/scanner di level web server (nginx/Apache) atau menggunakan WAF (Web Application Firewall).
- [ ] Filter error 401 untuk bot dari log agar tidak memenuhi storage — tambahkan ke `$dontReport` jika URL-nya di luar area yang seharusnya diakses.
- [ ] Pastikan semua route admin sudah dilindungi middleware `auth` dan `role`.

---

## MASALAH L10 — BcryptHasher Timeout (300 Detik) saat Hash Password

**Tingkat keparahan:** 🔴 ERROR (crash fatal, meski hanya sekali)  
**Tanggal kejadian:** 15 Juli 2026, pukul 14:44 — terjadi **di local development**  
**Contoh log:**

```
[2026-07-15 14:44:53] local.ERROR: Maximum execution time of 300 seconds exceeded
  at .../vendor/laravel/framework/src/Illuminate/Hashing/BcryptHasher.php:49
  {"userId":1}
```

**Analisis:**  
Proses hashing Bcrypt melebihi 300 detik. Ini terjadi di environment lokal (bukan server), kemungkinan karena **bcrypt cost factor terlalu tinggi** untuk CPU yang digunakan, atau ada infinite loop terkait password hashing. Cost factor default Laravel adalah 12 — di XAMPP dengan CPU lemah ini bisa lambat, tapi tidak sampai 300 detik kecuali ada yang tidak beres.

**Rencana perbaikan:**

- [ ] Cek nilai `BCRYPT_ROUNDS` di `.env` — pastikan tidak di-set terlalu tinggi (default 12 sudah cukup, jangan lebih dari 14 untuk production).
- [ ] Di `.env` local development, set `BCRYPT_ROUNDS=4` untuk mempercepat proses hashing saat testing.
- [ ] Ini adalah kejadian lokal dan tidak berulang di server — prioritas rendah.

---

## Prioritas Pengerjaan (Updated — Termasuk laravel.log)

| No | Masalah | Sumber | Prioritas | Estimasi |
|----|---------|--------|-----------|----------|
| L2 | PelanggaranObserver tidak ada — HTTP 500 massal | laravel.log | 🔴 Kritis | 1 jam |
| L5 | Maintenance mode tertinggal aktif — 503 semalaman | laravel.log | 🔴 Kritis | SOP deploy |
| L1 | storage/ Permission Denied — HTTP 500 | laravel.log | 🔴 Kritis | 2 jam |
| L3 | mb_split() undefined — PHP extension hilang | laravel.log | 🔴 Kritis | 30 menit |
| L4 | MySQL tidak bisa dikoneksi — SQLSTATE HY000 | laravel.log | 🔴 Kritis | 1-2 hari |
| 2  | WA Gagal Kirim HTTP 502 + Retry Queue | wa-*.log | 🔴 Tinggi | 1-2 hari |
| L7 | `history.back()` bug di view — 404 massal | laravel.log | 🟡 Sedang | 30 menit |
| L8 | ValidationException → HTTP 500 (harusnya 422) | laravel.log | 🟡 Sedang | 1 jam |
| 1  | Siswa 4526 No HP Ortu Kosong | wa-*.log | 🟡 Sedang | 30 menit + 1 hari |
| 3  | Login Gagal / Rate Limiting | sis-*.log | 🟡 Sedang | 1 hari |
| 9  | Log Rotation `laravel.log` | operasional | 🟡 Sedang | 1 jam |
| L6 | 404 `_service-worker.js` — noise log | laravel.log | 🟢 Rendah | 30 menit |
| L9 | Bot/scanner 401 — security observation | laravel.log | 🟢 Rendah | 2-4 jam |
| 6  | CheckOrange Terlalu Sering | sis-*.log | 🟢 Rendah | 1 jam |
| 7  | AutoPenghargaan/Pelanggaran Cron Noise | sis-*.log | 🟢 Rendah | 1 jam |
| 4  | Laporan GTK Tanpa Response Log | gtk-*.log | 🟢 Rendah | 2-4 jam |
| 5  | Notifikasi WA GTK Dinonaktifkan | gtk-*.log | 🟢 Rendah | 30 menit |
| 8  | UX Absen Double Tap | sis-*.log | 🟢 Rendah | 2-4 jam |
| L10 | BcryptHasher timeout (lokal saja) | laravel.log | 🟢 Rendah | 15 menit |

---

## Catatan Tambahan

- **Sistem berjalan stabil** secara umum di hari normal — tidak ada error database/query fatal yang berulang di server setelah masalah permisi diperbaiki.
- **WA Notifikasi absen masuk** (`absen_masuk`) berjalan normal di hari aktif (7 Agustus 2026).
- **Laporan Kehadiran GTK** secara umum berfungsi baik — sebagian besar laporan berhasil disimpan.
- **AutoAlfa** sudah benar melewati hari Minggu/hari libur.
- **Masalah L2 (PelanggaranObserver)** dan **L5 (Maintenance Mode)** adalah yang paling berdampak ke pengguna dan harus diselesaikan pertama.
- `laravel.log` menjadi besar (>50MB) sebagian besar karena error L2 (PelanggaranObserver) yang terjadi ratusan kali dan L6 (404 service-worker) yang terjadi ribuan kali. Setelah kedua masalah itu diperbaiki, ukuran log harian akan jauh berkurang.
