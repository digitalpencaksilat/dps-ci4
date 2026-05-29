# Flow Drawing Tanding (CI3 parity → CI4)

Doc fokus: menu **Admin → Super → Pembuatan Jadwal → Drawing Tanding**.
Tujuan: view/model/controller CI4 punya struktur + entrypoint mirip CI3, tapi pakai CodeIgniter 4.

## 1) Route (CI4)
File: `app/Config/Routes.php`

Routes terkait drawing tanding:
- `GET  admin/super/drawing-tanding` → `Admin\Super\PembuatanJadwalController::drawingTanding`
- `POST admin/super/drawing-tanding/distribusikan-peserta` → `::distribusikanPesertaTanding`
- `POST admin/super/drawing-tanding/acak-bagan` → `::acakBaganTandingBulk`
- `POST admin/super/drawing-tanding/distribusikan-tanpa-lawan/(:num)` → `::distribusikanPesertaTandingTanpaLawan/$1`
- `POST admin/super/drawing-tanding/pisahkan-kontingen-sendiri` → `::pisahkanKontingenTanding`
- `GET  admin/super/drawing-tanding/laporan-hasil-drawing-bagan` → `::laporanHasilDrawingBaganTanding`

## 2) Controller (CI4)
File: `app/Controllers/Admin/Super/PembuatanJadwalController.php`

### 2.1 Halaman Drawing Tanding
Method: `drawingTanding()`
- return view: `admin/super/drawing_tanding`
- data:
  - `kategoriRows` (pengganti `$data_kategori_lomba_tanding` CI3)
  - `summary`

CI3 referensi:
- Controller: `../dps/application/controllers/users/Super_admin.php`
- Method: `drawing_tanding()`
- Set:
  - `$data['data_kategori_lomba_tanding'] = Kategori_lomba_model->get(['nama_kategori_lomba'=>'tanding'])`
  - `$data['main_view'] = 'admin/super_admin/drawing_tanding'`

### 2.2 POST Distribusikan Peserta (CI3 parity)
CI3 flow:
- POST `users/super-admin/distribusikan-peserta-tanding`
- loop kategori → ambil kelas tanding per kategori → call:
  - `Kelas_tanding_model->distribusikan_peserta_tanding($id_kelas_tanding, $mode)`

CI4 flow (migrasi):
- POST `admin/super/drawing-tanding/distribusikan-peserta`
- controller normalize ids → loop kategori → ambil kelas tanding → call:
  - `KelasTandingModel->distribusikan_peserta_tanding($id_kelas_tanding, $mode)`

Catatan parity saat ini:
- Distribusi peserta sudah dipindah ke model CI4 dengan algoritma CI3.
- Mode didukung:
  - `prestasi`
  - `pemasalan`
  - `komposisi_seimbang`
  - `komposisi_lengkap`
- Post-process parity ikut jalan:
  - `pisahkan_atlet_bertemu_kontingen_sendiri()`
  - `distribukan_peserta_tanding_tanpa_lawan()`
  - `delete_kompetisi_tanding_kosong()`
- Distribusi peserta dan acak bagan tetap terpisah. Distribusi tidak auto-generate bagan.

### 2.3 POST Acak Bagan (CI3 parity)
CI3 flow:
- POST `users/super-admin/acak-bagan-tanding`
- untuk tiap kategori:
  - detect toggle `random_kategori_lomba_{id}`
  - loop kompetisi/pool → call:
    - `Kompetisi_tanding_model->acak_bagan_tanding($id_kompetisi_tanding, $random_seed)`

CI4 flow:
- POST `admin/super/drawing-tanding/acak-bagan`
- controller loop kategori → loop pool kompetisi tanding → call:
  - `KompetisiTandingModel->acak_bagan_tanding($id_kompetisi_tanding, $randomSeed)`

## 3) View (CI4)
File: `app/Views/admin/super/drawing_tanding.php`

View CI4 tetap 2 blok utama mirip CI3:
1) **Pendistribusian Peserta Tanding**
2) **Acak Bagan**

UI update:
- deskripsi metode distribusi sudah disesuaikan dengan status parity real CI4
- tabel kategori distribusi dan tabel kategori acak bagan dipaksa `overflow: auto` agar bisa discroll

Perbedaan UI boleh, tapi field name POST dijaga parity:
- Distribusi:
  - `id_kategori_lomba[]`
  - `mode` (prestasi|pemasalan|komposisi_seimbang|komposisi_lengkap)
- Acak bagan:
  - `id_kategori_lomba_bagan[]`
  - `random_kategori_lomba_{id}`

## 4) Model (CI4 parity wrappers)
Tujuan: controller CI4 bisa panggil method name gaya CI3 (snake_case), tapi eksekusi pakai service CI4.

### 4.1 `KompetisiTandingModel`
File: `app/Models/KompetisiTandingModel.php`
Add parity methods:
- `acak_bagan_tanding(int $id, bool $random_seed=false): bool`
  - map ke `SistemGugurTunggalService->acakBaganTanding($id, $mode)`
- `generate_bagan_dari_jadwal_excel(int $id): bool`
  - map ke `SistemGugurTunggalService->generateBaganTandingDariJadwal($id)`

### 4.2 `KelasTandingModel`
File: `app/Models/KelasTandingModel.php`
Add parity methods:
- `distribusikan_peserta_tanding(int $id_kelas_tanding, string $mode): bool`
  - loop pool `kompetisi_tanding` by `id_kelas_tanding`
  - call `SistemGugurTunggalService->acakBaganTanding()`
- `distribukan_peserta_tanding_tanpa_lawan(...)`
- `pisahkan_atlet_bertemu_kontingen_sendiri(...)`

2 method terakhir masih placeholder (belum parity algoritma CI3).

## 5) Service pembentuk bracket
File: `app/Services/SistemGugurTunggalService.php`

Core:
- `acakBaganTanding($idKompetisiTanding, $mode)`
  - safety: block jika sudah ada `detail_jadwal_tanding` untuk kompetisi
  - generate bracket + insert `pertandingan`
  - update `peserta_tanding.nomor_bagan`
  - update `kompetisi_tanding.bagan_pertandingan`

Mode:
- `formula`
- `full_random_persilat`

## 6) Gap parity vs CI3 (todo)
- Manual fix pool 1 peserta: CI3 `distribusikan_peserta_tanding_tanpa_lawan($toleransi)` → belum aktif CI4.
- Manual pisah kontingen sendiri: CI3 `pisahkan_semua_atlet_bertemu_kontingen_sendiri()` → belum aktif CI4.
- Laporan hasil drawing bagan tanding (CI3): `laporan_hasil_drawing_bagan_tanding()` → sudah dimigrasi CI4:
  - route: `GET admin/super/drawing-tanding/laporan-hasil-drawing-bagan`
  - controller: `PembuatanJadwalController::laporanHasilDrawingBaganTanding()`
  - view: `app/Views/admin/super/report/laporan_hasil_drawing_bagan_tanding.php`

## 7) Audit query hasil migrasi
- Query drawing tanding CI4 sudah diaudit agar tidak pakai `jumlah_peserta_tanding` sebagai kolom fisik table.
- Pattern lama `WHERE jumlah_peserta_tanding ...` sudah dibersihkan dari module drawing tanding.
- Pengganti pakai:
  - subquery `COUNT(*) FROM peserta_tanding ...`
  - `HAVING jumlah_peserta_tanding ...` hanya pada alias hasil select/subquery
  - `EXISTS (SELECT 1 FROM peserta_tanding ...)` untuk filter pool berisi peserta

## 8) Quick test checklist
1. Open: `/admin/super/drawing-tanding`
2. Jalankan Distribusikan untuk beberapa kategori dan cek pool berubah tanpa auto-acak bagan.
3. Jalankan Acak Bagan untuk kategori sama dan cek bracket/peserta nomor bagan berubah.
4. Open `/admin/super/drawing-tanding/laporan-hasil-drawing-bagan`.
5. Validasi 4 tab laporan:
   - peserta tanpa lawan
   - kontingen sendiri pool 2 peserta
   - kontingen sendiri pool >2 peserta
   - kelas kuota tersisa
6. Ensure protected: jika sudah ada `detail_jadwal_tanding` → request fail saat acak/generate bagan.
