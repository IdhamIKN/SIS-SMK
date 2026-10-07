# Dokumentasi Rule Bisnis: Laporan Kehadiran Guru

> Dokumen ini dibuat berdasarkan kode aktual sistem — bukan asumsi.  
> Sumber utama: `LaporanKehadiranController`, `CheckKelasOrange`, `SiswaPetugasLaporanController`, `config/status_guru.php`, `Kernel.php`.

---

## 1. Sisi Siswa

### Apakah semua siswa bisa melaporkan kehadiran guru?

**Tidak.** Hanya siswa yang ditunjuk sebagai **Petugas Laporan** yang dapat melaporkan kehadiran guru.

- Setiap kelas memiliki tepat **3 siswa petugas** (konstanta `JUMLAH_PETUGAS_PER_KELAS = 3` di `SiswaPetugasLaporanController`).
- Petugas ditunjuk oleh admin/superadmin melalui halaman manajemen petugas laporan.
- Pengecekan dilakukan via `$siswa->siswaPetugasLaporan()->where('kelas_id', ...)->exists()`.
- Jika siswa bukan petugas → sistem menolak dengan error: *"Anda tidak memiliki akses untuk melaporkan kehadiran guru."*

### Berapa banyak laporan per KBM?

**Tepat 1 laporan per kombinasi jadwal + tanggal.**

- Dijamin oleh unique constraint database: `laporan_kehadiran_guru_jadwal_kbm_id_tanggal_unique`.
- Dari 3 petugas, hanya **1 siswa pertama** yang bisa membuat laporan baru. Dua siswa lainnya hanya bisa melakukan **edit** terhadap laporan yang sudah ada.
- Jika sudah ada laporan dan siswa mencoba membuat baru → error: *"Jadwal ini sudah dilaporkan. Gunakan fitur edit untuk memperbarui laporan."*

### Apakah siswa dapat mengedit laporan?

**Ya**, selama dalam window waktu yang berlaku:

- **Bisa edit**: dalam rentang `jam_mulai` s.d. `jam_selesai + 15 menit`, dan tanggal harus hari ini.
- **Tidak bisa edit**: jika sudah melewati batas waktu atau bukan hari yang sama.
- Edit siswa tidak memperbarui `dilaporkan_oleh_siswa_id` — identitas pelapor pertama tetap tercatat.

### Window waktu untuk siswa

| Kondisi | Respon Sistem |
|---|---|
| Sebelum `jam_mulai` | "Laporan baru dapat dikirim mulai pukul HH:MM." |
| Antara `jam_mulai` s.d. `jam_selesai + 15 menit` | ✅ Boleh kirim / edit |
| Setelah `jam_selesai + 15 menit` | "Waktu laporan telah habis. Batas laporan adalah pukul HH:MM (15 menit setelah jam pelajaran selesai)." |
| Jadwal bukan hari ini | "Jadwal ini tidak berlangsung hari ini." |
| Jam tidak dikonfigurasi | "Jam mulai dan selesai pelajaran belum dikonfigurasi." |

---

## 2. Sisi Guru (GTK)

### Bagaimana guru mengirim laporan?

Guru mengakses halaman `/kehadiran-guru/create`, memilih jadwal yang tersedia, memilih status, dan mengirim form.

### Apakah guru hanya bisa lapor dirinya sendiri?

**Tidak selalu.** Guru bisa melaporkan kehadiran guru lain **jika** dia adalah **wali kelas** dari kelas yang bersangkutan.

Logika akses di `store()` dan `edit()`/`update()`:
```
bolehLapor = (jadwal->gtk_id === user->gtk->id)
          OR (user->gtk->kelasWali()->where('kelas.id', jadwal->kelas_id)->exists())
```

Artinya ada dua kondisi guru boleh lapor:
1. Guru mengajar jadwal tersebut (lapor diri sendiri).
2. Guru adalah wali kelas dari kelas jadwal tersebut (lapor guru lain di kelasnya).

Admin dan superadmin dapat melaporkan siapapun tanpa batasan.

### Window waktu untuk guru

- **Batas bawah**: jam pelajaran harus sudah dimulai (`now() >= jam_mulai`).
- **Batas atas**: **tidak ada** — guru dapat mengirim laporan kapan saja setelah jam mulai, bahkan malam hari atau hari berikutnya.

> ⚠️ **Potensi konflik**: Guru tidak memiliki deadline pengiriman, sehingga guru secara teknis bisa mengirim laporan jauh setelah jam pelajaran berakhir — bahkan setelah laporan orange sudah dibuat sistem. Ini menimpa laporan orange dan mengubah status menjadi sesuai input guru.

### Apakah guru bisa mengedit laporan?

**Ya, tanpa batas waktu.** Method `update()` tidak memiliki pengecekan waktu sama sekali — guru dapat mengedit laporan hari apapun selama punya akses.

---

## 3. Batas Pengiriman Laporan

### Kapan laporan mulai bisa dikirim?

| Aktor | Mulai Bisa Kirim |
|---|---|
| **Guru** | Sejak `jam_mulai` jadwal berlangsung |
| **Siswa (petugas)** | Sejak `jam_mulai` jadwal berlangsung |

### Kapan batas maksimal pengiriman?

| Aktor | Batas Maksimal |
|---|---|
| **Guru** | ❌ Tidak ada batas — bisa kirim kapan saja setelah jam mulai |
| **Siswa (petugas)** | `jam_selesai + 15 menit` |

### Apa yang terjadi jika melewati batas waktu (tanpa laporan)?

Jika melewati **`jam_selesai + 20 menit`** dan belum ada laporan sama sekali:

- Command `sis:check-orange` (berjalan setiap menit, jam 06:00–18:00) akan otomatis membuat laporan dengan:
  - `status = 'orange'` (Tanpa Laporan)
  - `catatan = 'Auto-generated: Tidak ada laporan setelah 20 menit jam pelajaran selesai'`
  - `dilaporkan_oleh_siswa_id = NULL`
  - Ditampilkan sebagai pelapor "**Sistem**" di semua view

### Timeline dalam satu sesi KBM

```
jam_mulai          jam_selesai     +15 mnt    +20 mnt
    │                   │              │           │
    │<── guru bisa kirim setiap saat ─────────────────────►
    │<── siswa bisa kirim/edit ────────►│
    │                                  │           │
    │                             siswa kunci   orange
    │                                           dibuat
```

---

## 4. Batas Edit Laporan

### Tabel batas edit

| Aktor | Batas Edit | Dikunci Oleh |
|---|---|---|
| **Siswa petugas** | Sampai `jam_selesai + 15 menit`, hari ini saja | Waktu (server-side) |
| **Guru (GTK)** | ❌ Tidak ada batas | — |
| **Wali kelas** | ❌ Tidak ada batas | — |
| **Admin** | ❌ Tidak ada batas | — |

### Apakah laporan benar-benar dikunci?

- Untuk **siswa**: Ya, dikunci di sisi server (`pesanJendelaLaporanSiswa()` pada `updateOlehSiswa()`).
- Untuk **guru/admin**: **Tidak ada penguncian** — dapat diedit kapan saja.

### Siapa yang bisa mengubah laporan yang "terkunci"?

- GTK pemilik jadwal
- GTK wali kelas dari kelas yang bersangkutan
- Admin / superadmin (hapus pun bisa)

---

## 5. Validasi dan Hak Akses

### Validasi saat kirim (guru)

Diproses oleh `LaporanKehadiranStoreRequest`:

| Field | Rule |
|---|---|
| `jadwal_kbm_id` | `required\|exists:jadwal_kbm,id` |
| `status` | `required\|in:hijau,kuning,merah,abu,biru,pink,orange` |
| `catatan` | `nullable\|string\|max:500` |

### Validasi saat edit (guru)

Diproses oleh `LaporanKehadiranUpdateRequest`:

| Field | Rule |
|---|---|
| `status` | `required\|in:hijau,kuning,merah,abu,biru,pink,orange` |
| `catatan` | `nullable\|string\|max:500` |

### Validasi saat kirim/edit (siswa)

Diproses inline di controller (`$request->validate(...)`):

| Field | Rule |
|---|---|
| `jadwal_kbm_id` | `required\|exists:jadwal_kbm,id` |
| `status` | `required\|in:hijau,kuning,merah,abu,biru,pink,orange` |
| `catatan` | `nullable\|string\|max:500` |

> ⚠️ Secara teknis, siswa bisa memilih status `orange` melalui API langsung karena ada di rule validasi — namun view siswa tidak menampilkannya sebagai pilihan.

### Koreksi laporan yang salah

- Guru / admin dapat mengedit status dan catatan kapan saja via `/kehadiran-guru/{id}/edit`.
- Admin dapat menghapus laporan via `DELETE /kehadiran-guru/{id}`.
- GTK dengan role `gtk` **tidak bisa menghapus** laporan (diblokir di `destroy()`).

---

## 6. Status Laporan yang Tersedia

Sumber: `config/status_guru.php` (single source of truth)

| Kode | Label | Warna | Keterangan |
|---|---|---|---|
| `hijau` | Hadir Tepat Waktu | 🟢 | Guru hadir ≤10 menit setelah bel |
| `kuning` | Hadir Terlambat | 🟡 | Guru hadir >10 menit |
| `merah` | Tidak Hadir | 🔴 | Guru tidak hadir, tanpa tugas |
| `abu` | Tidak Hadir + Ada Tugas | ⚫ | Tidak hadir, ada tugas |
| `biru` | Pergi + Ada Tugas | 🔵 | Hadir lalu keluar, ada tugas |
| `pink` | Pergi + No Tugas | 🩷 | Hadir lalu keluar, tanpa tugas |
| `orange` | Tanpa Laporan | 🟠 | Auto-generated oleh sistem |
| `putih` | Belum Lapor | ⚪ | Status default sebelum laporan masuk |

> **Catatan**: Status `putih` (Belum Lapor) adalah status default di konfigurasi, namun **tidak ada record di database** dengan status ini — jika belum ada laporan, record memang belum ada sama sekali. Status ini hanya digunakan sebagai label di UI untuk slot jadwal yang belum memiliki laporan.

---

## 7. Alur Lengkap Satu Hari KBM

### Skenario: Guru hadir, siswa ikut melaporkan

```
07:00 — Jam pelajaran mulai (jam_mulai = 07:00)
         └─ Guru bisa mulai kirim laporan
         └─ Siswa petugas bisa mulai kirim laporan

07:15 — Guru mengakses /kehadiran-guru/create
         └─ Memilih jadwal → memilih status "hijau" → submit
         └─ Sistem menyimpan laporan, broadcast ke panel realtime
         └─ Notifikasi WA dikirim (delay 2 detik via queue)

07:15 — Panel realtime (/panel/realtime) update otomatis via WebSocket
         └─ Kartu kelas berubah dari abu/putih → hijau

08:30 — Jam pelajaran selesai (jam_selesai = 08:30)

08:45 — Batas waktu edit siswa habis (jam_selesai + 15 menit)
         └─ Siswa petugas tidak bisa lagi mengirim atau mengedit laporan
         └─ Guru masih bisa edit kapan saja

08:50 — sis:check-orange berjalan (setiap menit)
         └─ Jadwal ini sudah ada laporan → tidak dibuat orange
```

### Skenario: Tidak ada yang lapor, sistem generate orange

```
07:00 — Jam pelajaran mulai
         └─ Tidak ada guru/siswa yang lapor

08:30 — Jam pelajaran selesai

08:45 — Batas akhir laporan siswa (tidak ada yang kirim)

08:50 — sis:check-orange berjalan: jam_selesai + 20 menit terlewati
         └─ Cek: belum ada laporan untuk jadwal ini hari ini
         └─ Buat laporan otomatis:
            • status = orange
            • catatan = "Auto-generated: Tidak ada laporan setelah 20 menit jam pelajaran selesai"
            • dilaporkan_oleh_siswa_id = NULL (tampil sebagai "Sistem")
         └─ Panel realtime update via broadcast

09:00 — Guru melihat ada laporan orange atas namanya
         └─ Guru bisa edit via /kehadiran-guru/{id}/edit
         └─ Mengubah status ke "hijau" (ternyata hadir tapi lupa lapor)
         └─ Catatan bisa diisi untuk klarifikasi
```

---

## 8. Konflik dan Rekomendasi Perbaikan

### ⚠️ Konflik 1: Guru tidak memiliki deadline pengiriman

**Kondisi saat ini**: Guru bisa mengirim laporan kapan saja setelah jam mulai — termasuk keesokan hari atau seminggu kemudian.

**Risiko**: Laporan orange yang sudah dibuat sistem bisa ditimpa oleh guru, data historis menjadi tidak akurat.

**Rekomendasi**: Tambahkan batas waktu pengiriman untuk guru, misalnya `jam_selesai + 30 menit` atau sampai akhir hari (`23:59`). Implementasi di `store()`:

```php
// Tambahkan di method store(), setelah cek jam_mulai
if ($jadwal->jam_selesai && now()->format('H:i') > $jadwal->jam_selesai->copy()->addMinutes(30)->format('H:i')) {
    return back()->withErrors(['error' => 'Batas waktu pengiriman laporan telah habis.']);
}
```

---

### ⚠️ Konflik 2: Guru tidak memiliki batas waktu edit

**Kondisi saat ini**: GTK bisa mengedit laporan dari hari kapan saja tanpa batasan.

**Risiko**: Manipulasi data historis, rekap laporan tidak konsisten jika diedit setelah periode berlangsung.

**Rekomendasi**: Tambahkan batas waktu edit untuk GTK, misalnya hanya boleh edit laporan hari ini atau 1 hari sebelumnya. Implementasi di `update()`:

```php
// Tambahkan di method update(), sebelum proses update
if ($user->hasRole('gtk') && !$laporanKehadiran->tanggal->isToday()) {
    return back()->withErrors(['error' => 'Laporan dari hari sebelumnya tidak dapat diedit. Hubungi admin jika perlu koreksi.']);
}
```

---

### ⚠️ Konflik 3: Gap 5 menit antara batas siswa dan trigger orange

**Kondisi saat ini**:
- Siswa batas kirim/edit: `jam_selesai + 15 menit`
- Orange dibuat: `jam_selesai + 20 menit`

Gap 5 menit ini **disengaja** sebagai buffer — siswa sudah tidak bisa lapor, tapi orange belum dibuat. Selama 5 menit ini, jika ada guru yang lapor, orange tidak akan dibuat. Desain ini sudah tepat.

**Namun**: Perlu dipastikan bahwa selama gap 5 menit ini, **guru masih bisa lapor**. Dengan tidak adanya batas atas untuk guru, ini terpenuhi.

---

### ⚠️ Konflik 4: Siswa bisa input status `orange` via API

**Kondisi saat ini**: Validasi `in:hijau,kuning,merah,abu,biru,pink,orange` di `laporOlehSiswa()` dan `updateOlehSiswa()` mengizinkan status `orange` dari siswa. Namun view tidak menampilkannya sebagai pilihan.

**Risiko**: Jika siswa akses endpoint langsung (misal via cURL/Postman), bisa input status orange dan mengaburkan data (seolah laporan dari sistem padahal dari siswa).

**Rekomendasi**: Hapus `orange` dari validasi siswa:

```php
// Di laporOlehSiswa() dan updateOlehSiswa()
'status' => 'required|in:hijau,kuning,merah,abu,biru,pink',
```

---

### ⚠️ Konflik 5: Edit siswa tidak mencatat siapa yang terakhir edit

**Kondisi saat ini**: Saat siswa B mengedit laporan yang dibuat siswa A, field `dilaporkan_oleh_siswa_id` tetap menunjuk siswa A.

**Risiko**: Tidak ada jejak audit siapa yang terakhir mengubah laporan dari sisi siswa.

**Rekomendasi**: Tambahkan field `terakhir_diedit_siswa_id` di tabel, atau update `dilaporkan_oleh_siswa_id` saat edit:

```php
// Di updateOlehSiswa(), tambahkan:
$laporanKehadiran->update([
    'status'  => $request->status,
    'catatan' => $request->catatan,
    'dilaporkan_oleh_siswa_id' => $siswa->id, // update ke editor terakhir
]);
```

---

## 9. Ringkasan Rule Bisnis

```
LAPORAN KEHADIRAN GURU — RULE SUMMARY
═══════════════════════════════════════════════════════════

YANG BISA LAPOR:
  ✓ Guru pengajar jadwal tersebut
  ✓ Wali kelas dari kelas jadwal tersebut
  ✓ 3 siswa petugas laporan per kelas
  ✓ Admin/superadmin (tanpa batasan)

WINDOW WAKTU KIRIM:
  Guru   : jam_mulai → tidak terbatas ⚠️
  Siswa  : jam_mulai → jam_selesai + 15 menit
  Sistem : jam_selesai + 20 menit (auto-orange jika belum ada laporan)

WINDOW WAKTU EDIT:
  Guru   : kapan saja ⚠️
  Siswa  : jam_mulai → jam_selesai + 15 menit (hari ini saja)
  Admin  : kapan saja

LAPORAN PER KBM:
  Maksimal 1 laporan per jadwal per tanggal

HAPUS LAPORAN:
  Hanya admin/superadmin (GTK diblokir)

STATUS TERSEDIA (pilihan manual):
  hijau, kuning, merah, abu, biru, pink, orange
  (orange idealnya hanya dari sistem)

STATUS AUTO:
  orange → dibuat sistem 20 menit setelah jam_selesai jika tidak ada laporan
```

---

*Dokumen ini mencerminkan rule yang diterapkan per September 2026.*  
*Perbarui dokumen ini setiap ada perubahan pada controller atau command terkait.*
