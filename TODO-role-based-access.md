# TODO: Role-Based Access Control - COMPLETED

## Menu & Navigation
- [x] Footer bar sudah memisahkan menu siswa vs admin
- [x] Dashboard quick actions difilter berdasarkan role
- [x] Menu samping (menu-main) ditambah menu Event & Izin untuk siswa

## Data Filter by Role
- [x] Event show.blade.php - statistik & absen terbaru hanya data siswa sendiri (jika role siswa)
- [x] Event rekap.blade.php - hanya menampilkan data absen siswa sendiri (jika role siswa)
- [x] EventController::index() - event yang ditampilkan untuk siswa hanya yang berlaku untuk kelasnya

## Ringkasan Perubahan

### 1. Dashboard (`dashboard.blade.php`)
- **Siswa**: Absensi, Event, Izin
- **Admin/Staff**: Event, Siswa, Laporan, GTK, Kelas

### 2. Menu Samping (`menu-main.blade.php`)
- **Siswa**: Absen, Event, Izin (semua dengan link aktif)
- **Admin**: Data Siswa, Data GTK, Data Kelas, Absensi, Laporan

### 3. Event Show (`event/show.blade.php`)
- Statistik difilter per siswa (jika role siswa)
- Label "Siswa Unik" → "Status Saya" untuk siswa
- Tabel "Absen Terbaru" → "Absen Saya" untuk siswa
- Action bar: siswa tidak melihat tombol Edit, hanya Rekap Saya + Scan

### 4. Event Rekap (`event/rekap.blade.php`)
- Semua query absen difilter `where('siswa_id', $siswaId)` jika role siswa
- Judul tabel: "Absen Saya" untuk siswa
- Tombol Export Excel disembunyikan untuk siswa
- Pesan empty state disesuaikan per role

### 5. Event Index (`EventController::index()`)
- Siswa: hanya melihat event yang `berlaku_untuk_semua = true` ATAU event yang terkait dengan kelasnya
- Admin/Staff: melihat semua event tanpa filter
