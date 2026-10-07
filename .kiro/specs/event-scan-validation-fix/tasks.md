# Tasks — event-scan-validation-fix

## Task List

- [x] 1. Add grace period to AutoPointPelanggaranEvent command
  - Edit `app/Console/Commands/AutoPointPelanggaranEvent.php`
  - Change the query condition from `->where('tanggal_selesai', '<', now())` to `->where('tanggal_selesai', '<', now()->subMinutes(10))`
  - Add a log entry noting the grace period value when events are found/skipped
  - **Acceptance:** Running `php artisan event:auto-point-pelanggaran` for an event that ended 5 minutes ago does NOT process it; an event that ended 11 minutes ago IS processed
  - **Files:** `app/Console/Commands/AutoPointPelanggaranEvent.php`

- [x] 2. Improve processScan error message when event is expired
  - Edit `app/Http/Controllers/AbsenEventController.php`
  - In `processScan()`, replace the existing generic `isActive()` check block with a more descriptive one:
    - If `now() > tanggal_selesai` → return 400 with message `"Waktu scan event sudah berakhir. Kehadiran tidak dapat dicatat."`
    - If `now() < tanggal_mulai` → return 400 with message `"Event belum dimulai."`
    - Otherwise → return 400 with `"Event tidak aktif."`
  - All three cases still return HTTP 400 with `success: false`
  - **Acceptance:** POST to `processScan` after `tanggal_selesai` returns 400 + specific expired message; regression: POST during active event still processes normally
  - **Files:** `app/Http/Controllers/AbsenEventController.php`

- [x] 3. Add frontend auto-expiry check to scan.blade.php
  - Edit `resources/views/event/scan.blade.php`
  - Inside the `@push('scripts')` block, after the `html5QrCode.start(...)` call, inject:
    ```javascript
    const EVENT_ENDS_AT = {{ $event->tanggal_selesai->timestamp * 1000 }};

    function checkEventExpiry() {
        if (Date.now() >= EVENT_ENDS_AT) {
            scanning = false;
            clearInterval(expiryInterval);
            Swal.fire({
                icon: 'warning',
                title: 'Event Sudah Berakhir',
                text: 'Waktu scan event ini telah habis. Halaman ini tidak aktif lagi.',
                confirmButtonColor: '#d33',
                allowOutsideClick: false,
                allowEscapeKey: false,
            });
        }
    }

    const expiryInterval = setInterval(checkEventExpiry, 30000);
    checkEventExpiry();
    ```
  - Ensure this block is only rendered inside the scanner-active section (inside the `@else` of `session('success')`)
  - **Acceptance:** With `tanggal_selesai` set in the past, opening the scan page shows the SweetAlert overlay immediately; with a future `tanggal_selesai`, scanner works normally
  - **Files:** `resources/views/event/scan.blade.php`
