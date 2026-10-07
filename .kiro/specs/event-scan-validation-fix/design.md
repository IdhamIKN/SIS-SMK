# Design Document — event-scan-validation-fix

## Overview

Tiga perbaikan terpisah yang saling melengkapi untuk menutup celah race condition dan UX pada fitur scan absen event:

1. **Grace period pada command** `AutoPointPelanggaranEvent` — query hanya memproses event yang selesai **≥10 menit** yang lalu, sehingga scan yang terjadi sangat dekat dengan `tanggal_selesai` sudah pasti sudah committed sebelum poin pelanggaran ditetapkan.

2. **Validasi waktu ketat di backend** (`processScan`) — tambahkan cek eksplisit `now() < tanggal_selesai` (dan `now() >= tanggal_mulai`) *setelah* `isActive()` sudah ada, untuk menolak submit barcode yang tertunda di kamera browser pasca-event berakhir dengan pesan yang jelas.

3. **Auto-expiry di frontend** (`scan.blade.php`) — `setInterval` setiap 30 detik membandingkan waktu klien dengan `tanggal_selesai` dari server; bila event sudah berakhir, scanner dimatikan dan overlay "Event sudah berakhir" ditampilkan tanpa perlu refresh manual.

## Architecture

Tidak ada perubahan arsitektur (tidak ada model/migration/tabel baru). Semua perubahan bersifat in-place di lapisan yang sudah ada:

```
┌─────────────────────────────────────────────────────┐
│  Frontend (scan.blade.php)                          │
│  + setInterval checkEventExpiry() setiap 30 detik   │
│  + overlay "Event Berakhir" + nonaktifkan scanner   │
└────────────────┬────────────────────────────────────┘
                 │ POST /event/{id}/scan
┌────────────────▼────────────────────────────────────┐
│  AbsenEventController::processScan()                │
│  + validasi isActive() sudah ada → TETAP            │
│  + pesan error lebih deskriptif saat event berakhir │
└────────────────┬────────────────────────────────────┘
                 │ (tidak ada perubahan di sini)
┌────────────────▼────────────────────────────────────┐
│  AutoPointPelanggaranEvent (command)                │
│  + WHERE tanggal_selesai < now() - 10 menit         │
│    (grace period)                                   │
└─────────────────────────────────────────────────────┘
```

## Component Design

### 1. Command — AutoPointPelanggaranEvent

**File:** `app/Console/Commands/AutoPointPelanggaranEvent.php`

**Perubahan:** Query events menggunakan grace period 10 menit.

```php
// SEBELUM
->where('tanggal_selesai', '<', now())

// SESUDAH
->where('tanggal_selesai', '<', now()->subMinutes(10))
```

Ini adalah satu-satunya perubahan di command. Semua logika pemrosesan per-siswa (duplikasi, absensi harian, dll.) tetap tidak berubah.

Konstanta grace period tidak perlu di-externalize ke config karena ini adalah parameter internal command yang spesifik untuk satu use-case dan tidak perlu dikonfigurasi per-environment.

### 2. Controller — AbsenEventController::processScan()

**File:** `app/Http/Controllers/AbsenEventController.php`

`isActive()` di model sudah melakukan cek `now() >= tanggal_mulai && now() <= tanggal_selesai`. Namun pesan error yang dikembalikan saat event tidak aktif hanya berisi "Event tidak aktif." yang generik.

**Perubahan:** Tambahkan cek tambahan yang menghasilkan pesan error yang lebih spesifik untuk kasus event sudah berakhir vs belum mulai, agar frontend dapat menampilkan feedback yang lebih tepat:

```php
if (! $event->isActive()) {
    $now = now();
    if ($now->gt($event->tanggal_selesai)) {
        $message = 'Waktu scan event sudah berakhir. Kehadiran tidak dapat dicatat.';
    } elseif ($now->lt($event->tanggal_mulai)) {
        $message = 'Event belum dimulai.';
    } else {
        $message = 'Event tidak aktif.';
    }
    return response()->json(['success' => false, 'message' => $message], 400);
}
```

### 3. Frontend — scan.blade.php

**File:** `resources/views/event/scan.blade.php`

**Perubahan:** Tambahkan blok JavaScript di dalam `@push('scripts')` untuk auto-expiry:

```javascript
// Inject tanggal_selesai dari server sebagai Unix timestamp (milidetik)
const EVENT_ENDS_AT = {{ $event->tanggal_selesai->timestamp * 1000 }};

function checkEventExpiry() {
    if (Date.now() >= EVENT_ENDS_AT) {
        // Hentikan scanning
        scanning = false;

        // Tampilkan overlay menggunakan SweetAlert yang sudah ada di halaman
        Swal.fire({
            icon: 'warning',
            title: 'Event Sudah Berakhir',
            text: 'Waktu scan event ini telah habis. Halaman ini tidak aktif lagi.',
            confirmButtonColor: '#d33',
            allowOutsideClick: false,
            allowEscapeKey: false,
        });

        // Hapus interval agar tidak terpicu berulang
        clearInterval(expiryInterval);
    }
}

const expiryInterval = setInterval(checkEventExpiry, 30_000);

// Jalankan sekali langsung saat halaman dimuat
// (menangani kasus halaman dimuat setelah event selesai)
checkEventExpiry();
```

Script ini hanya diinjeksikan ketika scanner aktif (blok `@else` / bukan `session('success')`). Variabel `scanning` sudah dideklarasikan di scope yang sama sehingga dapat diakses langsung.

## Data Flow

```
[Siswa buka halaman scan]
       │
       ├─ checkEventExpiry() dipanggil sekali (DOM ready)
       │   └─ Jika sudah lewat: overlay ditampilkan, scanner tidak aktif
       │
       ├─ setInterval setiap 30 detik → checkEventExpiry()
       │   └─ Jika melewati tanggal_selesai saat halaman terbuka: overlay + scanner off
       │
       └─ Siswa scan barcode → POST processScan
               │
               ├─ isActive() = false (lewat tanggal_selesai)?
               │   └─ Return 400 + pesan "Waktu scan event sudah berakhir."
               │
               └─ isActive() = true → proses normal → record tersimpan
                       │
                       └─ [5 menit kemudian] command berjalan
                               │
                               └─ tanggal_selesai < now() - 10 menit?
                                   ├─ YA: proses poin pelanggaran (scan sudah pasti committed)
                                   └─ TIDAK: lewati (masih dalam grace period)
```

## Error Handling

| Skenario | Sebelum | Sesudah |
|---|---|---|
| Scan setelah event selesai (halaman masih terbuka) | "Event tidak aktif." (generik) | "Waktu scan event sudah berakhir. Kehadiran tidak dapat dicatat." |
| Halaman terbuka saat event berlangsung, event berakhir | Scanner tetap aktif sampai refresh | Overlay SweetAlert + scanner dimatikan dalam ≤30 detik |
| Command jalan dalam 10 menit setelah event selesai | Proses pelanggaran langsung | Dilewati (grace period), diproses pada run berikutnya |

## Testing Strategy

- **Unit / Feature test** untuk `processScan`: kirim POST setelah mock `tanggal_selesai` diset ke masa lalu → expect 400 + pesan spesifik.
- **Unit test** untuk command: buat event dengan `tanggal_selesai = now() - 5 menit` → event **tidak** diproses; `tanggal_selesai = now() - 11 menit` → event **diproses**.
- **Manual**: buka halaman scan, tunggu >30 detik setelah event berakhir → overlay muncul.
