# Flow Penjadwalan Otomatis Tanding (CI3 parity → CI4)

Doc fokus: **penjadwalan otomatis tanding** (schedule detail `detail_jadwal_tanding` dari data pertandingan/bracket).

Status implementasi saat audit repo `dps-ci4`:
- Flow jadwal tanding (CRUD + edit tools) sudah aktif di CI4.
- Penjadwalan otomatis tanding (bulk auto-fill detail jadwal) **belum terlihat sebagai modul khusus** bernama `penjadwalan otomatis` seperti CI3 side nav.
- Yang sudah ada dan relevan (CI4):
  - create jadwal slot: `jadwal_tanding` (tanggal, jam_mulai, jam_selesai, gelanggang)
  - lihat detail jadwal: `detail_jadwal_tanding`
  - edit otomasi ringan: **sort nomor partai**, **pola penjadwalan**, **tukar atlet**
  - audit/dashboard buat cek "belum dijadwalkan".

Kalau CI3 punya menu "Penjadwalan Otomatis → Legacy Tanding / Hybrid Jadwal", modul ini masih kandidat migrasi fase lanjut.

---

## 1) Route (CI4)

File: `app/Config/Routes.php`

Group: `admin/super` (filter: `adminrole:super_admin`)

Entry terkait jadwal tanding:
- `GET  admin/super/jadwal-tanding` → `Admin\\Super\\PembuatanJadwalController::jadwalTanding`
- `GET  admin/super/jadwal-tanding/(:num)` → `Admin\\Super\\PembuatanJadwalController::showJadwalTanding/$1`
- `POST admin/super/jadwal-tanding/create` → `Admin\\Super\\PembuatanJadwalController::createJadwalTanding`
- `POST admin/super/jadwal-tanding/(:num)/update-keterangan` → `Admin\\Super\\PembuatanJadwalController::updateKeteranganJadwalTanding/$1`
- `POST admin/super/jadwal-tanding/(:num)/delete` → `Admin\\Super\\PembuatanJadwalController::deleteJadwalTanding/$1`

Tools edit "otomatis" dalam konteks jadwal (reorder):
- `POST admin/super/jadwal-tanding/sortir-ulang/(:num)` → `::sortirUlangJadwalTanding/$1`
- `POST admin/super/jadwal-tanding/pola-penjadwalan/(:num)` → `::polaPenjadwalanJadwalTanding/$1`
- `POST admin/super/jadwal-tanding/tukar-atlet` → `::tukarAtletJadwalTanding`

PDF export:
- `POST admin/super/jadwal-tanding/create-pdf-ajax/(:num)/(:num)` → `::createPdfJadwalTandingAjax/$1/$2`

Catatan:
- Route super admin **reuse view sekretariat** via `routePrefix`.
- Route sekretariat analog ada juga, tapi sebagian stub.

---

## 2) Controller (CI4)

File: `app/Controllers/Admin/Super/PembuatanJadwalController.php`

### 2.1 List jadwal tanding
Method: `jadwalTanding()`
- Model: `JadwalTandingModel`
- View: `admin/sekretariat/jadwal_tanding/index`
- Data view:
  - `rows` = `$model->get_all()`
  - `gelanggang` = `(new GelanggangModel())->findAll()`
  - `routePrefix` = `'admin/super/jadwal-tanding'`

### 2.2 Detail jadwal tanding
Method: `showJadwalTanding(int $id)`
- Model: `JadwalTandingModel::findWithGelanggang($id)`
- Detail: `JadwalTandingModel::get_detail_jadwal($id)`
- View: `admin/sekretariat/jadwal_tanding/show`
- View include modal tools (khusus `super_admin`):
  - `shared_components/jadwal_tanding/modal_sortir_ulang_nomor_partai`
  - `shared_components/jadwal_tanding/modal_atur_pola_jadwal`
  - `shared_components/jadwal_tanding/modal_tukar_atlet`

### 2.3 Create jadwal slot
Method: `createJadwalTanding()`
- Validasi:
  - `id_gelanggang` required `is_natural_no_zero`
  - `tanggal` required `valid_date`
  - `jam_mulai` required
  - `jam_selesai` required
- Insert ke `jadwal_tanding`:
  - `id_gelanggang`, `tanggal`, `jam_mulai`, `jam_selesai`, `keterangan`

### 2.4 Sort ulang nomor partai (otomatis re-number)
Method: `sortirUlangJadwalTanding(int $id)`
- Input: `nomor_partai_awal`
- Guard: block jika ada pertandingan terkunci (sudah punya skor/pemenang): `jadwalTandingHasLockedMatches($id)`
- Action: `renumberJadwalTanding($id, $awal)` lalu sync range.

### 2.5 Pola penjadwalan (otomatis reorder)
Method: `polaPenjadwalanJadwalTanding(int $id)`
- Input: `jenis_pola_penjadwalan`
- Allowed:
  - `prestasi`
  - `pemasalan_seling_1`
  - `pemasalan_seling_2`
  - `pemasalan_seling_3`
  - `pemasalan_seling_4`
- Guard: block jika locked matches.
- Reorder:
  - fetch detail: `(new JadwalTandingModel())->get_detail_jadwal($id)`
  - `usort` by `jenis_perlombaan` then by `nomor_partai`
  - update `detail_jadwal_tanding.nomor_partai`
  - `syncJadwalTandingRange($id)`

### 2.6 Tukar atlet
Method: `tukarAtletJadwalTanding()`
- Input: `id_atlet_1`, `id_atlet_2`
- Guard: block jika atlet punya match terkunci
- Action: swap di tabel `pertandingan` untuk field:
  - `id_atlet_merah`
  - `id_atlet_biru`

---

## 3) Model (CI4)

### 3.1 JadwalTandingModel
File: `app/Models/JadwalTandingModel.php`

Fungsi penting:
- `get_all()`
  - join `gelanggang`
  - hitung `partai_awal`, `partai_akhir`, `jumlah_partai` via subquery `detail_jadwal_tanding`
- `findWithGelanggang($id)`
- `get_detail_jadwal($id_jadwal_tanding)`
  - join chain:
    - `detail_jadwal_tanding` → `pertandingan` → `kompetisi_tanding` → `kelas_tanding` → `kategori_lomba` → `kategori_usia`
    - join peserta untuk tampilkan atlet/kontingen merah/biru

Catatan parity CI3:
- Method naming snake_case (`get_all`, `get_detail_jadwal`) sengaja mirip CI3.
- Query pakai builder CI4 (`$this->db->table(...)`).

---

## 4) View (CI4)

### 4.1 List
File: `app/Views/admin/sekretariat/jadwal_tanding/index.php`
- Insert modal: `shared_components/jadwal_tanding/modal_insert`
- Tabel list: `shared_components/jadwal_tanding/tabel`
- Untuk super admin ada UI bulk update PDF.

### 4.2 Detail
File: `app/Views/admin/sekretariat/jadwal_tanding/show.php`
- Dropdown `Edit Schedule` muncul jika `session()->get('level') === 'super_admin'`
- Modal edit:
  - `modalSortirNomorPartai` → POST `.../sortir-ulang/{id}`
  - `modalAturPolaJadwal` → POST `.../pola-penjadwalan/{id}`
  - `modalTukarAtlet` → POST `.../tukar-atlet`

---

## 5) Dibanding CI3

Parity ada:
- Konsep data sama: `jadwal_tanding` header + `detail_jadwal_tanding` untuk nomor partai.
- Super admin punya mode `pembuatan_jadwal` pakai controller super.
- Naming banyak dipertahankan (snake_case method di model).

Belum parity:
- Modul **Penjadwalan Otomatis (Legacy/Hybrid)** yang auto-assign `pertandingan` masuk ke slot jadwal (insert bulk ke `detail_jadwal_tanding`) belum ketemu entrypoint-nya.
- Saat ini auto hanya reorder (sort/pola) terhadap detail yang sudah ada.

---

## 6) Candidate migrasi berikut (biar sama CI3)

Kalau mau parity penuh CI3 "penjadwalan otomatis":
1. Tentukan source CI3: controller + model method yang generate `detail_jadwal_tanding` dari list pertandingan yang belum terjadwal.
2. Buat CI4 service baru (rekomendasi):
   - `app/Services/Admin/Super/PenjadwalanOtomatisTandingService.php`
3. Buat controller action:
   - `Admin\\Super\\PembuatanJadwalController::penjadwalanOtomatisTanding()` (GET form)
   - `Admin\\Super\\PembuatanJadwalController::prosesPenjadwalanOtomatisTanding()` (POST execute)
4. Tambah route group `admin/super`.
5. Tambah doc QA: input parameter, constraints, rollback (transaksi), dan guard locked matches.

---

## 7) Quick QA checklist (CI4)

1. Buka `/admin/super/jadwal-tanding`
2. Add jadwal baru (modal insert) → pastikan row muncul.
3. Masuk detail `/admin/super/jadwal-tanding/{id}`
4. Jika detail kosong, pastikan message `Belum ada partai dijadwalkan.` tampil.
5. Jika detail ada:
   - coba `Sort Match Numbers` (nomor partai awal)
   - coba `Set Schedule Pattern`
   - coba `Swap Athletes`
6. Pastikan guard aktif:
   - jika ada skor/pemenang, sort/pola/tukar ditolak.
7. (super admin) coba update PDF per jadwal dan bulk.
