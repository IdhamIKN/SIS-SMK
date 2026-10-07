# TODO: Fix absen/masuk Method Not Allowed & ParseError

- [x] 1. `routes/web.php` — Add GET routes `/absen/masuk` and `/absen/pulang` redirecting to `absen.index`
- [x] 2. `footer-bar.blade.php` — Fix non-siswa Absen nav link to `route('dashboard')`
- [x] 3. `masuk.blade.php` — Update form action to `route('absen.store', 'masuk')`
- [x] 4. `pulang.blade.php` — Update form action to `route('absen.store', 'pulang')`
- [x] 5. `LaporanKehadiranController.php` — Remove duplicated code block (fix ParseError)
- [x] 6. Clear route cache & verify routes

**Verification:**
- `php -l` on LaporanKehadiranController.php: ✅ No syntax errors
- `php artisan route:list --name=absen`: ✅ 7 routes listed, including `GET absen/masuk` and `GET absen/pulang`

