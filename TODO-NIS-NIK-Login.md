# TODO: Implementasi Login NIS/NIK

**Status:** Approved by user  
**Urutan:** Sequential

## ✅ 1. Update LoginController.php
- Edit `login()` method dengan logic NIS/NIP detection ✅
- Keep role redirect sama ✅

## ✅ 2. Update auth/login.blade.php  
- Ganti `email` field → `username` dengan placeholder "NIS / NIP" ✅
- Update label & validation message ✅

## [ ] 3. Test Implementation
```
php artisan route:clear
php artisan config:clear  
php artisan serve
```
- Test login siswa pake NIS
- Test login GTK pake NIP  
- Test fallback email

## [ ] 4. Verify & Complete
- Update TODO ini
- attempt_completion()

