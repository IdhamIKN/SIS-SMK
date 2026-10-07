# TODO: Fitur Pengelolaan Kelas

## Step 1: Create Requests (Validation)
- [ ] `app/Http/Requests/KelasStoreRequest.php`
- [ ] `app/Http/Requests/KelasUpdateRequest.php`

## Step 2: Create Controller
- [ ] `app/Http/Controllers/Admin/KelasController.php`

## Step 3: Create Views
- [ ] `resources/views/kelas/index.blade.php`
- [ ] `resources/views/kelas/create.blade.php`
- [ ] `resources/views/kelas/edit.blade.php`
- [ ] `resources/views/kelas/show.blade.php`

## Step 4: Update Routes
- [ ] Add kelas resource routes in `routes/web.php`

## Step 5: Update Dashboard & Menu
- [ ] Fix Kelas button href in `resources/views/dashboard.blade.php`
- [ ] Add Kelas menu item in `resources/views/components/azures/menu-main.blade.php`

## Step 6: Testing
- [ ] Test happy path CRUD
- [ ] Test edge cases (delete kelas with siswa)
- [ ] Verify logs in `storage/logs/sis/`

