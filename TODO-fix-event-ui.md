# TODO: Fix Event UI & Role-Based Buttons

## 1. event/edit.blade.php
- [x] Fix missing closing </div> tags on Info Card, Geolocation Card, Waktu Card, Absen Card, Peserta Card
- [x] Fix action bar: wrap with @if(!$isSiswa) check
- [x] Fix JavaScript clearLocation()
- [x] Add Swal2 script for delete confirmation

## 2. event/show.blade.php
- [x] Fix stat-grid nesting: properly close each stat-card
- [x] Fix Detail Card, Barcode Card, Recent Absen Card closing tags
- [x] Ensure action bar shows correct buttons per role

## 3. event/index.blade.php
- [x] Add $isSiswa role check at top of content
- [x] Hide Edit button for siswa
- [x] Hide Scan buttons for non-siswa (admin/guru)
- [x] Hide FAB "Tambah Event" for siswa

## 4. event/rekap.blade.php
- [x] Fix stat-grid closing tags
- [x] Fix Export Card and Table Card closing tags
- [x] Verify action bar role filter

## 5. event/scan.blade.php
- [x] Fix scanner-outer closing tags
- [x] Fix status card structure
- [x] Ensure scanner overlay closed properly

## 6. event/create.blade.php
- [x] Fix missing closing </div> tags for cards
- [x] Ensure consistency with edit form structure

## 7. Clear view cache & test
- [ ] Clear Laravel view cache
- [ ] Test each page as siswa
- [ ] Test each page as guru/admin

