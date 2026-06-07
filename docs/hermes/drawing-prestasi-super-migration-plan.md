# Planning Migrasi Drawing Prestasi (Sekretariat → Super) — DPS Legacy → CI4

Status: SELESAI DIEKSEKUSI (2026-06-07)
Tanggal: 2026-06-07

> Catatan eksekusi: Fase 1-5 selesai. Fase 2b (parametrik tombol_bagan/acak_bagan_manual)
> dibatalkan dan diganti pendekatan lebih aman — tombol aksi di-embed langsung di view super
> (CI4-style, menunjuk route `admin/super/drawing-prestasi/...`), shared component CI3-leftover
> TIDAK disentuh sama sekali sehingga nol risiko regresi halaman sekretariat.
Referensi legacy: `/Applications/XAMPP/xamppfiles/htdocs/dps`
Target: `/Applications/XAMPP/xamppfiles/htdocs/dps-ci4`
Perubahan role: fitur dipindah dari role **sekretariat** ke role **super_admin**

---

## 1. Ringkasan & Tujuan

Di project lama, menu **"Drawing Prestasi"** berada di area **sekretariat** dan terdiri dari 3
sub-halaman: drawing **tanding**, drawing **seni battle**, dan drawing **seni pool**. Halaman ini
adalah alat drawing **per-kategori terkonsolidasi**: operator memilih satu kategori (kompetisi) dari
dropdown, melihat bagannya, lalu melakukan pengacakan (otomatis/manual), sinkronisasi, atau pengundian
nomor (untuk pool seni).

Tujuan plan ini: **memigrasikan halaman Drawing Prestasi ke arsitektur CI4** dengan **memindahkan
hak akses ke role `super_admin`** (bukan lagi sekretariat), sambil **memanfaatkan ulang** service,
model, dan komponen bagan yang sudah lebih dulu dimigrasikan ke CI4.

> Catatan penting: drawing **per-kategori** secara teknis sudah berfungsi di CI4 lewat halaman
> `pool-tanding/{id}`, `pool-seni/{id}`, dan `battle-seni/{id}` (role sekretariat). Yang **belum ada**
> adalah halaman **"Drawing Prestasi" terkonsolidasi** (selektor dropdown + navigasi prev/next) sebagai
> entry point khusus, dan belum tersedia di role super.

---

## 2. Hasil Investigasi Legacy

### 2.1 Menu
`application/views/admin/sekretariat/components/sidenav.php` → grup menu **"Drawing Prestasi"**
(ikon `fa-random`), gate role `sekretariat || super_admin`, dengan 3 item:

- Tanding → `kompetisi-tanding/drawing-tanding-prestasi`
- Seni Battle → `kompetisi-seni/drawing-seni-battle-prestasi`
- Seni Pool → `kompetisi-seni/drawing-seni-pool-prestasi`

### 2.2 Controller & flow legacy

**`controllers/resources/Kompetisi_tanding.php::drawing_tanding_prestasi()`**
- View: `admin/sekretariat/drawing_tanding_prestasi.php`
- Ambil semua `kompetisi_tanding` jenis **prestasi**, urut `kategori_usia.min_umur ASC, kategori_lomba.id_kategori_lomba ASC, kelas_tanding.label ASC`.
- Dropdown selektor (GET) + navigasi prev/next (`get_prev_next_kompetisi_tanding`).
- Saat `?id_kompetisi_tanding=` ada: render `shared_components/kompetisi_tanding/bagan_pertandingan` + `tombol_bagan`, lalu tab **Daftar Peserta** & **Daftar Pertandingan**.
- Aksi terkait (method lain di controller yang sama):
  - `acak_bagan($id)` — pengacakan formula / full random (`acak_bagan_tanding`).
  - `halaman_acak_bagan_manual($id)` + `buat_bagan_manual($id)` — pengacakan manual via slot.
  - `sinkronkan_bagan($id)` — sinkronisasi nama atlet/kontingen dengan DB.
  - `update_bagan_pertandingan($id)` — simpan bagan (AJAX).
  - `bagan($id, $print)` — cetak bagan.

**`controllers/resources/Kompetisi_seni.php::drawing_seni_battle_prestasi()`**
- View: `admin/sekretariat/drawing_seni_battle_prestasi.php`
- Dropdown prestasi seni + prev/next; validasi `sistem_penampilan == 'battle'` & `jumlah_kelompok_peserta_seni > 1` (jika tidak → tampilkan pesan "Ooopss").
- Render `shared_components/kompetisi_seni/bagan_battle_seni` + `tombol_bagan` + tabel battle + modal insert.
- Aksi: `acak_bagan($id)` (`acak_bagan_battle_seni`), `sinkronkan_bagan`, `buat_bagan_manual`, `update_bagan_battle_seni`.

**`controllers/resources/Kompetisi_seni.php::drawing_seni_pool_prestasi()`**
- View: `admin/sekretariat/drawing_seni_pool_prestasi.php`
- Dropdown prestasi pool; validasi `sistem_penampilan == 'pool'` & peserta > 1.
- UI **roulette (Winwheel)** untuk mengundi `nomor_undi`, submit ke `kompetisi-seni/beri-nomor-undi/{id}` (`beri_nomor_undi`).
- Tabel `kelompok_peserta_seni`.

---

## 3. Kondisi Project CI4 (gap analysis)

### 3.1 Sudah ada — bisa di-reuse penuh
- `app/Services/SistemGugurTunggalService.php`
  - `acakBaganTanding(int $id, string $mode)` (`formula` / `full_random_persilat`)
  - `acakBaganBattleSeni(int $id, string $mode)`
- `app/Services/SekretariatKategoriSeniService.php::beriNomorUndi(int $id)`
- `app/Models/KompetisiTandingModel.php::acak_bagan_tanding()` (parity wrapper)
- Komponen render bagan:
  - `app/Views/shared_components/kompetisi_tanding/bagan_pertandingan.php`
  - `app/Views/shared_components/kompetisi_tanding/acak_bagan_manual.php`
  - `app/Views/shared_components/kompetisi_tanding/tombol_bagan.php`
  - `app/Views/shared_components/kompetisi_seni/bagan_battle_seni.php`
- Drawing per-kategori sekretariat yang sudah jalan:
  - `Admin\Sekretariat\PoolTandingController` → `show`, `acakBagan`, `printBagan`
  - `Admin\Sekretariat\PoolSeniController` → `show`, `beriNomorUndi`, `acakBaganBattle`, `printBagan`
  - `Admin\Sekretariat\BattleSeniController` → `show`
- Infrastruktur akses: filter `adminrole:super_admin` + group route `admin/super` (`app/Config/Filters.php`, `app/Config/Routes.php`).

### 3.2 Belum ada — inti pekerjaan migrasi
1. Controller super untuk halaman drawing prestasi terkonsolidasi (belum ada).
2. Route drawing prestasi di group `admin/super`.
3. View halaman terkonsolidasi: selektor dropdown + prev/next + render bagan + roulette undian.
4. Menu **"Drawing Prestasi"** di sidebar super (`app/Views/layouts/admin.php`).
5. **Wiring aksi yang masih "dead link"**: `tombol_bagan.php` (tanding & seni) dan
   `acak_bagan_manual.php` masih menunjuk URL gaya CI3 (`kompetisi-tanding/sinkronkan-bagan`,
   `halaman-acak-bagan-manual`, `buat-bagan-manual`) yang **belum punya route/controller di CI4**,
   dan masih di-gate `level == 'sekretariat'`.
6. **Manual shuffle + sinkronisasi bagan tanding** belum tersedia sebagai action di CI4
   (`PoolTandingController` hanya punya `acakBagan` & `printBagan`).

---

## 4. Rencana Kerja (bertahap)

### Fase 1 — Routing & Controller (super)
- Buat `app/Controllers/Admin/Super/DrawingPrestasiController.php`:
  - `tanding()` — selektor prestasi tanding + detail by `?id=` (prev/next).
  - `seniBattle()` — selektor prestasi seni battle + detail + validasi sistem penampilan.
  - `seniPool()` — selektor prestasi seni pool + roulette undian.
  - Aksi: `acakBaganTanding($id)`, `acakBaganBattleSeni($id)`, `beriNomorUndi($id)` (delegasi ke service existing),
    plus yang belum ada: `sinkronkanBaganTanding($id)`, `halamanAcakBaganManualTanding($id)`, `buatBaganManualTanding($id)`.
- Tambah route di group `admin/super` (filter `adminrole:super_admin`), contoh prefix:
  - `admin/super/drawing-prestasi/tanding`
  - `admin/super/drawing-prestasi/seni-battle`
  - `admin/super/drawing-prestasi/seni-pool`
  - endpoint POST aksi: acak, acak-manual, sinkron, beri-nomor-undi.
- Reuse pola flashdata `status`/`message` dan `csrf_field()` sesuai standar CI4 project.

### Fase 2 — Views
- Buat folder `app/Views/admin/super/drawing_prestasi/`:
  - `tanding.php`, `seni_battle.php`, `seni_pool.php`.
- Extend `layouts/admin` (bukan copy mentah CI3); modernisasi: selektor kategori, badge info,
  navigasi prev/next, empty/error state ("Ooopss") yang lebih rapi, tab peserta/pertandingan.
- Sediakan komponen tombol bagan **versi super** (atau buat parametrik: base URL + role)
  sehingga menunjuk ke route `admin/super/...` dan gate `super_admin`, **tanpa merusak** halaman
  sekretariat existing yang masih memakai komponen yang sama.

### Fase 3 — Model & Service (hanya bila ada gap)
- Tambahkan logika yang belum tersedia di CI4: `buat_bagan_manual` (tanding) & `sinkronkan_bagan`
  (tanding/seni) — idealnya sebagai method di service (`SistemGugurTunggalService`) atau service baru,
  bukan di controller.
- Query selektor: select kolom seperlunya + join, urut sesuai legacy, hindari N+1
  (prev/next dihitung dari koleksi yang sama, bukan query berulang).

### Fase 4 — Menu & Navigasi
- Tambah grup menu **"Drawing Prestasi"** (Tanding / Seni Battle / Seni Pool) di sidebar super
  pada `app/Views/layouts/admin.php`, dengan penanda `active` berbasis path.

### Fase 5 — Validasi & QA
- `php -l` untuk semua file PHP yang berubah; cek `php spark routes`.
- Uji manual (login super):
  - Tanding: pilih kategori → acak (formula & full random) → manual shuffle → sinkron → print.
  - Seni battle: acak bagan battle → sinkron → tabel battle.
  - Seni pool: roulette Winwheel → "Tetapkan Undian" → verifikasi `nomor_undi` tersimpan.
- Pastikan CSRF, redirect, flashdata konsisten; pastikan halaman sekretariat existing tidak regresi.

---

## 5. Risiko & Catatan

- **Komponen di-share**: `tombol_bagan.php` / `acak_bagan_manual.php` dipakai modul lain. Ubah secara
  **aditif/parametrik** (jangan hard-replace gate `sekretariat`) agar halaman sekretariat tetap jalan.
- **Library Winwheel**: roulette undian pool perlu dipastikan asetnya tersedia di `public/assets` CI4.
- **Parity vs duplikasi**: karena drawing per-kategori sudah ada di CI4 (pool-tanding/pool-seni/battle-seni),
  perlu keputusan apakah halaman Drawing Prestasi super memakai ulang action controller tersebut
  (lewat service bersama) agar tidak ada duplikasi logika.
- **Skema DB tidak berubah** (gunakan kolom `bagan_pertandingan`, `bagan_battle_seni`, `nomor_undi`,
  `jenis_perlombaan = 'prestasi'` seperti legacy).

---

## 6. Daftar File (perkiraan) yang Akan Dibuat/Diubah

Dibuat:
- `app/Controllers/Admin/Super/DrawingPrestasiController.php`
- `app/Views/admin/super/drawing_prestasi/tanding.php`
- `app/Views/admin/super/drawing_prestasi/seni_battle.php`
- `app/Views/admin/super/drawing_prestasi/seni_pool.php`

Diubah:
- `app/Config/Routes.php` (route di group `admin/super`)
- `app/Views/layouts/admin.php` (menu sidebar super)
- `app/Views/shared_components/kompetisi_tanding/tombol_bagan.php` (parametrik URL/role — aditif)
- `app/Views/shared_components/kompetisi_tanding/acak_bagan_manual.php` (parametrik URL — aditif)
- (opsional) `app/Services/SistemGugurTunggalService.php` (tambah `buatBaganManual` / `sinkronkanBagan` jika belum ada)
