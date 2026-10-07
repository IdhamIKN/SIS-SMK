# Bugfix Requirements Document

## Introduction

Bug ini terjadi pada fitur scan event siswa di aplikasi SIS (Student Information System) berbasis Laravel.
Siswa yang **sudah berhasil scan masuk** ke suatu event masih tetap menerima **poin pelanggaran alfa/tidak hadir**
karena command `event:auto-point-pelanggaran` berjalan setiap 5 menit dan berpotensi dieksekusi
**setelah event selesai tapi sebelum record scan siswa berhasil tersimpan** — atau lebih tepatnya,
karena frontend tidak memberikan umpan balik yang dapat diandalkan bahwa scan benar-benar tersimpan di database.

Di samping itu, terdapat dua masalah validasi waktu yang saling berkaitan:

1. **Halaman scan tidak otomatis kadaluarsa** di sisi frontend saat waktu event berakhir — siswa yang membuka
   halaman scan sebelum event selesai dapat terus mencoba scan tanpa sadar bahwa event sudah tidak aktif,
   kecuali mereka me-refresh halaman secara manual.

2. **Backend tidak memvalidasi** bahwa waktu scan (`now()`) berada dalam rentang `tanggal_mulai–tanggal_selesai`
   pada endpoint `processScan`. Method `isActive()` memang ada di model `Event`, dan dipanggil di controller,
   **namun** method tersebut hanya mengecek apakah *pada saat halaman scan dimuat* event masih aktif (via
   `scan()` method). Pada `processScan()`, `isActive()` dipanggil ulang — sehingga ini sebenarnya sudah ada —
   tetapi ada window waktu antara validasi di `scan()` dan validasi di `processScan()` karena barcode yang sudah
   di-capture di kamera bisa di-submit setelah `tanggal_selesai` berlalu.

**Rangkuman bug condition C(X):**

```
C(X) = siswa SUDAH scan masuk (record AbsenEvent.waktu_masuk terisi)
       DAN command auto-point-pelanggaran berjalan sesaat SETELAH event selesai
       DAN pada saat command berjalan, record scan siswa SUDAH ada di database
       → BUG: siswa TETAP mendapat poin pelanggaran karena ada skenario
         di mana scan dilakukan SANGAT dekat dengan waktu tanggal_selesai,
         lalu command langsung berjalan dalam window 5 menit berikutnya
         sebelum konfirmasi bahwa record sudah committed.

C(X) tambahan = siswa membuka halaman scan, event berakhir,
                halaman tidak refresh otomatis,
                siswa berhasil scan barcode → backend MENOLAK (isActive() = false)
                NAMUN siswa tidak tahu karena tidak ada notifikasi real-time di halaman
```

---

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN siswa berhasil melakukan scan masuk pada event (record `waktu_masuk` tersimpan di tabel `absen_event`) DAN command `event:auto-point-pelanggaran` berjalan dalam window 5 menit setelah `tanggal_selesai` event THEN the system memberikan poin pelanggaran kepada siswa tersebut karena query `AbsenEvent::whereNotNull('waktu_masuk')` mengambil data dengan potensi **tidak mencakup scan yang baru saja tersimpan** akibat ketidakpastian timing antara HTTP request dan job scheduler

1.2 WHEN siswa membuka halaman scan event dan event kemudian berakhir (melewati `tanggal_selesai`) TANPA siswa me-refresh halaman THEN the system tetap menampilkan antarmuka scanner QR yang aktif seolah-olah event masih berlangsung, sehingga siswa tidak mendapat informasi bahwa waktu scan sudah habis

1.3 WHEN siswa berhasil melakukan scan (QR terbaca, fetch POST ke `processScan` dikirim) dalam rentang waktu antara `tanggal_mulai` dan `tanggal_selesai` THEN the system tidak memberikan konfirmasi eksplisit di frontend bahwa record scan **sudah benar-benar tersimpan di database** sebelum redirect ke halaman rekap

1.4 WHEN command `event:auto-point-pelanggaran` berjalan dan event baru saja selesai (dalam hitungan detik hingga beberapa menit) THEN the system memproses daftar siswa yang tidak scan berdasarkan snapshot data pada saat itu, tanpa mempertimbangkan kemungkinan ada scan yang sedang dalam proses HTTP transaction pada saat bersamaan (race condition)

1.5 WHEN siswa melakukan scan setelah `tanggal_selesai` event dan barcode yang di-capture masih valid di kamera (karena halaman belum di-refresh) THEN the system **kadang** menerima scan tersebut jika waktu antara capture barcode dan submit sangat singkat, menghasilkan record `waktu_masuk` yang timestampnya melewati `tanggal_selesai`

### Expected Behavior (Correct)

2.1 WHEN siswa berhasil melakukan scan masuk pada event (record `waktu_masuk` tersimpan di database) DAN command `event:auto-point-pelanggaran` berjalan setelah event selesai THEN the system SHALL mengecualikan siswa tersebut dari daftar penerima poin pelanggaran karena record scan sudah ada di tabel `absen_event` dengan `waktu_masuk` tidak null

2.2 WHEN siswa membuka halaman scan event dan event kemudian berakhir (melewati `tanggal_selesai`) TANPA siswa me-refresh halaman THEN the system SHALL secara otomatis menampilkan notifikasi/overlay bahwa event sudah berakhir dan menonaktifkan scanner, tanpa perlu refresh manual

2.3 WHEN siswa berhasil melakukan scan (record tersimpan di database) THEN the system SHALL menampilkan konfirmasi sukses yang menyatakan bahwa kehadiran telah tercatat sebelum melakukan redirect

2.4 WHEN command `event:auto-point-pelanggaran` berjalan untuk memproses event yang baru selesai THEN the system SHALL memberikan grace period (jeda waktu minimum) antara `tanggal_selesai` event dan waktu eksekusi pemrosesan poin, untuk mencegah race condition dengan scan yang sedang dalam proses

2.5 WHEN siswa mencoba submit scan (POST ke `processScan`) setelah `tanggal_selesai` event THEN the system SHALL menolak scan tersebut dengan pesan error yang jelas bahwa waktu event sudah berakhir, terlepas dari apakah halaman sudah di-refresh atau belum

### Unchanged Behavior (Regression Prevention)

3.1 WHEN siswa yang **tidak hadir** di sekolah pada hari event berlangsung (status absen harian bukan `hadir` atau `terlambat`) THEN the system SHALL CONTINUE TO tidak memberikan poin pelanggaran event kepada siswa tersebut

3.2 WHEN siswa yang sudah scan masuk event sebelumnya mencoba scan masuk lagi (duplikasi) THEN the system SHALL CONTINUE TO menolak scan dengan respons HTTP 409 dan pesan "Anda sudah absen masuk"

3.3 WHEN event memiliki `auto_point_pelanggaran = false` THEN the system SHALL CONTINUE TO tidak memproses poin pelanggaran otomatis untuk event tersebut

3.4 WHEN `event:auto-point-pelanggaran` sudah pernah berjalan untuk suatu event (`auto_point_processed_at` tidak null) THEN the system SHALL CONTINUE TO tidak memproses event tersebut lagi (perlindungan duplikasi yang ada tetap berjalan)

3.5 WHEN barcode event sudah dirotasi (nilai `barcode_value` berubah) dan siswa mencoba scan dengan barcode lama THEN the system SHALL CONTINUE TO menolak scan dengan pesan "Barcode tidak valid atau sudah kadaluarsa"

3.6 WHEN event berlaku untuk kelas tertentu dan siswa bukan anggota kelas tersebut mencoba scan THEN the system SHALL CONTINUE TO menolak scan dengan pesan "Event ini tidak berlaku untuk Anda"

3.7 WHEN `event:auto-penghargaan` berjalan untuk siswa yang berhasil scan masuk THEN the system SHALL CONTINUE TO memberikan poin penghargaan kepada siswa yang scan, tidak terpengaruh oleh perbaikan bug ini

3.8 WHEN mode libur panjang aktif pada `Sekolah` THEN the system SHALL CONTINUE TO melewati (skip) semua pemrosesan auto-point dan auto-penghargaan

---

## Bug Condition — Structured Pseudocode

### Bug Condition Function

```pascal
FUNCTION isBugCondition(X)
  INPUT: X = { siswa_id, event_id, waktu_scan, waktu_command_jalan }
  OUTPUT: boolean

  // Bug terjadi ketika semua kondisi berikut terpenuhi:
  record_scan_ada ← AbsenEvent.where(event_id=X.event_id, siswa_id=X.siswa_id)
                              .whereNotNull(waktu_masuk).exists()
  
  event_baru_selesai ← Event.tanggal_selesai < X.waktu_command_jalan
                       AND (X.waktu_command_jalan - Event.tanggal_selesai) < GRACE_PERIOD
  
  poin_sudah_diberikan ← Pelanggaran.where(deviceid='auto-event-' + X.event_id,
                                            siswa_id=X.siswa_id).exists()

  RETURN record_scan_ada AND event_baru_selesai AND poin_sudah_diberikan
END FUNCTION
```

### Property: Fix Checking

```pascal
// Property: Siswa yang scan tidak boleh mendapat poin pelanggaran
FOR ALL X WHERE isBugCondition(X) DO
  result ← AutoPointPelanggaranEvent.processEvent'(X.event_id)
  ASSERT NOT Pelanggaran.where(deviceid='auto-event-' + X.event_id,
                                siswa_id=X.siswa_id).exists()
END FOR
```

### Property: Preservation Checking

```pascal
// Property: Siswa yang tidak scan tetap mendapat poin pelanggaran
FOR ALL X WHERE NOT isBugCondition(X) AND siswa_tidak_scan(X) DO
  ASSERT F(X) = F'(X)  // Perilaku sama seperti sebelum perbaikan
END FOR
```

### Grace Period Definition

```pascal
// Command hanya memproses event yang selesai LEBIH DARI grace_period yang lalu
GRACE_PERIOD ← 10 menit  // minimum jeda antara tanggal_selesai dan waktu proses poin

QUERY event untuk diproses:
  WHERE tanggal_selesai < (now() - GRACE_PERIOD)
  AND auto_point_processed_at IS NULL
  AND auto_point_pelanggaran = true
```

### Frontend Auto-Expiry

```pascal
// Frontend harus polling status event secara berkala
FUNCTION checkEventExpiry()
  INPUT: event_tanggal_selesai (timestamp dari server)
  
  IF now() > event_tanggal_selesai THEN
    tampilkan_overlay_kadaluarsa()
    nonaktifkan_scanner()
    // Tidak perlu redirect, cukup tampilkan pesan
  END IF
END FUNCTION

// Dijalankan setiap 30 detik via setInterval
setInterval(checkEventExpiry, 30000)
```
