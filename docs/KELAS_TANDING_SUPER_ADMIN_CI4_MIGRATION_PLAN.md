# Rencana Implementasi Migrasi Super Admin Kelas Tanding (CI3 ➜ CI4)

Status: Draft Implementasi  
Scope: Modul **Super Admin Kelas Tanding** pada project `dps-ci4` dengan parity fitur dari CI3 `htdocs/dps`.

---

## 1) Tujuan Migrasi

Membawa flow CI3 berikut ke CI4 secara bertahap dan aman:

- Daftar kelas tanding (list)
- Detail kelas tanding (pool + peserta)
- Create kelas tanding (single)
- Create kelas tanding (multiple/generator)
- Update kelas tanding
- Delete kelas tanding
- Otomatis menambahkan pool
- Ubah jumlah peserta per pool per kategori + opsi redistribusi peserta

Referensi flow CI3 utama:

- `application/controllers/resources/Kelas_tanding.php`
- `application/models/resources/Kelas_tanding_model.php`
- `application/views/shared_pages/kelas_tanding/*`
- `application/views/shared_components/kelas_tanding/*`
- `application/controllers/resources/Kategori_lomba.php` (fitur update jumlah peserta per pool)

---

## 2) Kondisi Saat Ini di CI4

Sudah ada implementasi **sekretariat read-only**:

- `app/Config/Routes.php` (`admin/sekretariat/kelas-tanding`)
- `app/Controllers/Admin/Sekretariat/KelasTandingController.php`
- `app/Services/SekretariatKategoriTandingService.php`
- `app/Views/admin/sekretariat/kelas_tanding/index.php`
- `app/Views/admin/sekretariat/kelas_tanding/show.php`

Gap utama:

- Belum ada route/controller/service/view **super admin** untuk CRUD master kelas tanding.
- Belum ada generator multiple.
- Belum ada action auto tambah pool versi super admin.
- Belum ada action mass update jumlah peserta per pool.

---

## 3) Strategi Implementasi

### Prinsip

1. **Parity dulu, refactor belakangan**: pastikan perilaku setara CI3.
2. **Small incremental changes**: satu fitur selesai dan diverifikasi sebelum lanjut.
3. **Write operation via service layer**: controller tipis, logika di service.
4. **Transaction untuk operasi multi-tabel**.
5. **Tidak mengubah flow sekretariat existing** kecuali reuse query yang aman.

---

## 4) Rencana Per-Phase

## Phase A — Route & Skeleton Super Admin

### A1. Tambah routes super admin
File: `app/Config/Routes.php`

Tambahkan endpoint:

- `GET  admin/super/kelas-tanding` → index
- `GET  admin/super/kelas-tanding/(:num)` → show
- `POST admin/super/kelas-tanding` → store single
- `POST admin/super/kelas-tanding/create-multiple` → store multiple
- `POST admin/super/kelas-tanding/(:num)/update` → update
- `POST admin/super/kelas-tanding/(:num)/delete` → delete
- `POST admin/super/kelas-tanding/(:num)/otomatis-tambah-pool` → autoTambahPool
- `POST admin/super/kelas-tanding/update-jumlah-peserta-per-pool` → updateJumlahPesertaPerPool

Filter: `adminrole:super_admin`.

### A2. Buat controller baru
File baru: `app/Controllers/Admin/Super/KelasTandingController.php`

Method:

- `index()`
- `show(int $id)`
- `store()`
- `storeMultiple()`
- `update(int $id)`
- `delete(int $id)`
- `autoTambahPool(int $id)`
- `updateJumlahPesertaPerPool()`

---

## Phase B — Service Layer Super Admin

File baru: `app/Services/Admin/Super/KelasTandingService.php`

### B1. Read methods

- `listKelas(): array`
- `getKelas(int $id): ?object`
- `listPoolByKelas(int $id): array`
- `listPesertaByKelas(int $id): array`

Reuse query pattern dari `SekretariatKategoriTandingService` untuk konsistensi output.

### B2. Write methods

- `createSingle(array $payload): int|array`
  - Loop `id_kategori_lomba[]`.
  - Insert kelas.
  - Auto-create pool awal.

- `createMultiple(array $payload): array`
  - Generate label kelas, rentang berat, jumlah kelas.
  - Opsi kelas bebas + mini.
  - Insert kelas + auto-create pool untuk tiap kelas.

- `updateKelas(int $id, array $payload): bool`

- `deleteKelas(int $id): bool`
  - Tambahkan guard integritas referensi.

- `autoTambahPool(int $idKelas, ?int $maxPeserta = null): bool`

- `updateJumlahPesertaPerPool(array $payload): bool`
  - Mass update `kompetisi_tanding.max_peserta`.
  - Opsi redistribusi peserta jika diminta.

### B3. Transaction policy

Gunakan transaction untuk:

- create single (per kelas + pool)
- create multiple (batch)
- update jumlah peserta per pool massal

---

## Phase C — Model Alignment

### C1. `KelasTandingModel`
File: `app/Models/KelasTandingModel.php`

Update `allowedFields` agar sesuai field yang dipakai form dan update:

- `id_kategori_lomba`
- `label`
- `berat_minimal`
- `berat_maksimal`
- `juara_tiga_bersama`
- `jumlah_ronde`
- `waktu_per_ronde`
- `waktu_istirahat`
- `format_penilaian`
- `biaya_pendaftaran_dn`

(Sesuaikan akhir dengan schema aktual DB)

### C2. `KompetisiTandingModel`

Pastikan field untuk pool update/create sudah tersedia dan aman dipakai service.

---

## Phase D — Views Super Admin

Folder baru: `app/Views/admin/super/kelas_tanding/`

### D1. Halaman list (`index.php`)

Isi:

- tabel kelas tanding
- tombol/modal create single
- tombol/modal create multiple
- modal ubah jumlah peserta per pool
- action: lihat detail, delete

### D2. Halaman detail (`show.php`)

Isi:

- ringkasan kelas tanding
- tab daftar pool
- tab daftar peserta
- tab edit (khusus super admin)
- tombol “otomatis tambah pool”

### D3. Partials komponen

Bila perlu pisah partial agar mengikuti style existing.

---

## Phase E — Validation & Error Handling

Tambah rules validasi CI4 pada controller/service:

- single create rules
- multiple generator rules
- update rules
- mass update peserta/pool rules

Semua error kembali ke halaman asal dengan flash message.

---

## Phase F — Testing & Verification

### F1. Syntax

- `php -l` untuk semua file yang diubah/ditambah.

### F2. Automated tests

Target minimal:

- unit test service:
  - create single
  - create multiple
  - auto tambah pool
  - update jumlah peserta per pool
- negative case:
  - delete saat data terhubung
  - payload invalid

### F3. Manual QA checklist

1. List kelas tanding tampil sesuai data.
2. Detail kelas menampilkan pool + peserta.
3. Create single berhasil, pool awal otomatis terbentuk.
4. Create multiple berhasil untuk range label/berat.
5. Update berhasil.
6. Delete sesuai kebijakan integritas.
7. Otomatis tambah pool berhasil.
8. Ubah jumlah peserta per pool + redistribusi berjalan.

---

## 5) Dampak & Risiko

### Risiko

- Query agregat berat (subquery besar) berpotensi lambat.
- Delete bisa bentrok referential integrity jika policy tidak jelas.
- Generator multiple rawan off-by-one label/berat.

### Mitigasi

- Mulai dari parity SQL existing.
- Tambah guard delete eksplisit.
- Tambah test kasus batas untuk generator.

---

## 6) Definisi Selesai (Definition of Done)

Modul dinyatakan complete jika:

1. Semua endpoint super admin kelas tanding aktif.
2. Semua fitur CI3 yang discope-kan berjalan di CI4.
3. `php -l` pass untuk file terkait.
4. Test terkait pass (dengan catatan env test jika ada blocker).
5. Manual QA checklist terpenuhi.
6. `docs/migration-ci4-status.md` diperbarui.

---

## 7) Urutan Eksekusi Implementasi (Recommended)

1. Phase A (route + controller skeleton)
2. Phase B (service write + read)
3. Phase C (model alignment)
4. Phase D (view super admin)
5. Phase E (validation hardening)
6. Phase F (tests + QA + docs status)
