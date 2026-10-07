# Debug Absen Pulang Gagal (Jam 13:56 no error)

## Status: 🔄 On Progress

### Steps:
- [ ] 1. Tambah comprehensive logging di AbsenController::store()
- [x] 2. Fix frontend JS error handling di index.blade.php  
- [ ] 3. Test absen pulang → cek storage/logs/siswa.log
- [ ] 4. Query DB: `SELECT * FROM absen_siswa WHERE siswa_id=YOUR_ID AND tanggal=CURDATE();`
- [ ] 5. Jalankan `tail -f storage/logs/siswa.log` & test absen

### Commands Debug:
```bash
cd sis-app
php artisan config:clear
tail -f storage/logs/siswa.log
# Di tab lain: buka /absen → coba absen pulang
```

### Cek DB:
```sql
SELECT * FROM absen_siswa 
WHERE tanggal = CURDATE() AND jenis='pulang' 
ORDER BY waktu_absen DESC LIMIT 5;
```

