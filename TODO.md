# Add Date Range to Izin/Create Form

## Current State:
- Single `tanggal_izin` field
- Controller validates `tanggal_izin` 
- Migration has only `tanggal_izin` column
- No `tanggal_mulai`/`tanggal_sampai` fields

## Plan:
1. Add `tanggal_mulai`, `tanggal_sampai` columns to migration
2. Update PengajuanIzin model fillable
3. Update controller validation & store logic (conditional based on jenis)
4. Update create.blade.php form (show date range for sakit/lainnya only)
5. Update index/show views if needed
6. Create migration & run
7. Test

**Step 1**: Create new migration ✅

