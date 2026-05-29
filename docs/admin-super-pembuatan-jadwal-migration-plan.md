# Rencana Migrasi Mode Pembuatan Jadwal (Super Admin) CI3 ke CI4

Status: Draft Planning  
Scope: Migrasi mode **pembuatan_jadwal** dari CI3 (`htdocs/dps`) ke CI4 (`dps-ci4`) dengan pendekatan bertahap dan parity perilaku.

---

## 1) Latar Belakang

Pada CI3, mode ini diaktifkan oleh method:

- `application/controllers/users/Super_admin.php` -> `mode_pembuatan_jadwal()`
- Session yang diset: `tipe_super_admin = pembuatan_jadwal`
- Redirect ke: `users/super-admin/dashboard-pembuatan-jadwal`

Dashboard mode ini adalah pusat operasi jadwal tanding/seni, validasi data jadwal, dan pintu masuk ke modul drawing, generate bagan, penjadwalan otomatis/manual, serta utilitas terkait.

Di CI4 saat ini, modul jadwal sudah ada sebagian di area sekretariat, tetapi **mode super admin pembuatan jadwal belum dimigrasikan utuh**.

---

## 2) Tujuan Migrasi

Membawa flow CI3 ini ke CI4:

1. Super admin dapat memilih mode `pembuatan_jadwal`.
2. Session `tipe_super_admin` terset dan mempengaruhi menu/sidebar.
3. Dashboard pembuatan jadwal tampil dengan data validasi utama.
4. Semua menu/modul di side nav mode pembuatan jadwal tersedia (minimal route + halaman dasar), lalu diimplementasi bertahap hingga parity.
5. Integrasi aman dengan modul jadwal yang sudah ada di CI4 (khususnya `admin/sekretariat/jadwal-*`).

---

## 3) Inventaris Sumber CI3 (Baseline)

### 3.1 Controller utama

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/users/Super_admin.php`
  - `mode_pembuatan_jadwal()`
  - `dashboard_pembuatan_jadwal()`
  - `operasi_basis_data()`
  - `drawing_tanding()`
  - `drawing_seni()`
  - `generate_bagan_tanding_dari_jadwal()`
  - `generate_bagan_seni_battle_dari_jadwal()`

### 3.2 View utama

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/admin/super_admin/menu_tipe_super_admin.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/admin/super_admin/dashboard_pembuatan_jadwal.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/admin/super_admin/components/sideNavPembuatanJadwal.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/admin/super_admin/template.php`

### 3.3 Model yang dipakai dashboard pembuatan jadwal

- `Gelanggang_model`
- `Jadwal_tanding_model`
- `Jadwal_seni_model`
- `Peserta_tanding_model`
- `Pertandingan_model`
- `Kelompok_peserta_seni_model`
- `Battle_seni_model`
- `Penampilan_seni_model`
- `Detail_jadwal_tanding_model`

---

## 4) Kondisi CI4 Saat Ini (Temuan)

1. Mode super admin CI4 saat ini baru mencakup:
   - `pengaturan_event`
   - `perngaturan_kategori_lomba`
2. Belum ada method mode `pembuatan_jadwal` di:
   - `app/Controllers/Admin/Super/ModeController.php`
3. Belum ada dashboard `dashboard-pembuatan-jadwal` di area `admin/super`.
4. Sebagian fitur jadwal sudah ada di sekretariat:
   - `app/Controllers/Admin/Sekretariat/JadwalTandingController.php`
   - `app/Controllers/Admin/Sekretariat/JadwalSeniController.php`
5. Layout CI4 sudah membaca session `tipe_super_admin`, jadi mekanisme mode bisa diperluas tanpa ubah konsep utama.

---

## 5) Daftar Menu/Modul Mode Pembuatan Jadwal (Paritas CI3)

Berdasarkan `sideNavPembuatanJadwal.php`, modul yang harus dipetakan:

1. Menu Utama
2. Dasbor Pembuatan Jadwal
3. Operasi Basis Data
4. Data Excel (Import/Export)
5. Gelanggang
6. Drawing dan Bagan
   - Drawing Tanding
   - Drawing Seni
7. Generate Bagan dari Jadwal
   - Tanding
   - Seni Battle
8. Penjadwalan Otomatis
   - Hybrid Jadwal
   - Legacy Tanding
   - Legacy Seni Pool
   - Legacy Seni Battle
9. Penjadwalan Manual
   - Tanding
   - Seni Pool
10. Jadwal Pertandingan
    - Diagnosis
    - Overview Tanding
    - Jadwal Tanding
    - Jadwal Seni
11. Utilitas
    - Log Sistem

---

## 6) Strategi Migrasi

### Prinsip

1. **Parity dahulu**, refactor belakangan.
2. **Bertahap per fase** agar aman dan mudah QA.
3. **Reuse modul CI4 existing** (jadwal sekretariat) jika sudah stabil.
4. **Controller tipis, service tebal** untuk query/agregasi.
5. **Tidak memecah role security**: semua route mode ini harus di balik `adminrole:super_admin`.

### Pendekatan Integrasi

- Untuk fitur yang sudah ada di sekretariat, buat salah satu dari dua opsi:
  - Opsi A: route super admin sebagai alias aman ke controller/service yang sama.
  - Opsi B: controller super admin sendiri yang memanggil service bersama.
- Rekomendasi awal: **Opsi B** untuk menjaga pemisahan menu, breadcrumb, dan audit perubahan role.

---

## 7) Rencana Implementasi Per Fase

## Phase 0 - Audit Detail dan Mapping

Tujuan:

- Memetakan setiap menu CI3 ke endpoint CI4 target.
- Menentukan apakah modul diambil dari area sekretariat atau dibuat khusus super admin.
- Menyusun daftar gap data/query untuk dashboard pembuatan jadwal.

Deliverables:

- Tabel mapping CI3 -> CI4 final.
- Daftar dependency controller/model/service per menu.
- Daftar blocker (jika ada).

Acceptance:

- Semua menu pada section 5 punya target implementasi yang jelas.

---

## Phase 1 - Aktivasi Mode Pembuatan Jadwal di Super Admin

Implementasi:

1. Tambah method mode di `app/Controllers/Admin/Super/ModeController.php`:
   - set session `tipe_super_admin = pembuatan_jadwal`
   - redirect ke `admin/super/dashboard-pembuatan-jadwal`
2. Tambah route mode baru di `app/Config/Routes.php` (group `admin/super`).
3. Tambah card/menu mode di `app/Views/admin/super/menu_tipe_super_admin.php`.
4. Tambah branch menu/sidebar di `app/Views/layouts/admin.php` untuk mode `pembuatan_jadwal`.

Acceptance:

- Super admin bisa masuk mode pembuatan jadwal dari menu mode.
- Session mode terset benar dan menu berubah sesuai mode.

---

## Phase 2 - Dashboard Pembuatan Jadwal (MVP Parity)

Implementasi:

1. Buat controller baru, rekomendasi:
   - `app/Controllers/Admin/Super/PembuatanJadwalController.php`
2. Buat method `dashboard()` dengan agregasi data setara CI3:
   - pertandingan belum dijadwalkan
   - BYE yang terjadwal
   - seni pool belum dijadwalkan
   - seni battle belum dijadwalkan
   - mismatch sistem penampilan
3. Buat view:
   - `app/Views/admin/super/dashboard_pembuatan_jadwal.php`
4. Jika timeline `skedTape` belum siap, gunakan fallback tabel/ringkasan dulu, lalu lanjut parity UI di fase berikutnya.

Acceptance:

- Dashboard tampil tanpa error.
- Angka ringkasan utama konsisten dengan data DB.
- Role non-super-admin tertolak oleh filter.

---

## Phase 3 - Migrasi Side Nav Pembuatan Jadwal

Implementasi:

1. Buat komponen nav khusus mode ini:
   - `app/Views/admin/super/components/side_nav_pembuatan_jadwal.php`
2. Daftarkan nav tersebut di layout ketika `tipe_super_admin == pembuatan_jadwal`.
3. Semua item menu pada section 5 minimal punya route aktif.

Acceptance:

- Semua item menu muncul dan dapat diklik.
- Tidak ada dead link.

---

## Phase 4 - Integrasi Modul Existing (Reuse) dan Stub Halaman Belum Ada

Prioritas integrasi:

1. Jadwal Tanding/Seni (pakai service/controller existing jika memungkinkan).
2. Diagnosis dan Overview Tanding.
3. Gelanggang.
4. Drawing Tanding/Seni.
5. Generate Bagan dari Jadwal.
6. Penjadwalan Otomatis/Manual.
7. Utilitas Log.
8. Operasi Basis Data.

Catatan:

- Untuk modul yang belum siap, buat halaman stub bertanda "in progress" agar flow navigasi tidak putus.
- Stub tetap berada di route aman super admin.

Acceptance:

- Minimal seluruh menu bisa diakses.
- Modul prioritas (jadwal tanding/seni) berfungsi end-to-end.

---

## Phase 5 - Parity Fitur Lanjutan dan Hardening

Target parity lanjutan:

- Fitur tukar atlet, sortir ulang nomor partai, pola penjadwalan.
- Generate PDF/ekspor terkait jadwal.
- Kesesuaian perilaku penjadwalan otomatis legacy dan hybrid.

Hardening:

- Validasi input ketat.
- CSRF semua form POST.
- Escape output di view.
- Logging aman tanpa data sensitif.

Acceptance:

- Fitur lanjutan yang di-scope-kan berjalan setara CI3.
- Tidak ada regresi pada modul sekretariat.

---

## 8) Rencana Route CI4 (Draft)

Semua berada di group:

- `admin/super` dengan filter `adminrole:super_admin`

Route minimum fase awal:

- `GET admin/super/mode-pembuatan-jadwal`
- `GET admin/super/dashboard-pembuatan-jadwal`

Route lanjutan (bertahap):

- `GET admin/super/operasi-basis-data`
- `GET admin/super/drawing-tanding`
- `GET admin/super/drawing-seni`
- `GET admin/super/generate-bagan-tanding-dari-jadwal`
- `GET admin/super/generate-bagan-seni-battle-dari-jadwal`
- Route alias terkontrol ke modul jadwal/diagnosis/overview bila dibutuhkan.

Catatan:

- Hindari menyalin mentah path CI3 `users/super-admin/*`; gunakan pola CI4 konsisten `admin/super/*`.
- Bila perlu backward compatibility, alias route lama boleh ditambah sementara tetapi tetap dijaga filter-nya.

---

## 9) Tabel Mapping Awal CI3 -> CI4

| CI3 | CI4 Target | Status |
|---|---|---|
| `mode_pembuatan_jadwal()` | `ModeController::pembuatanJadwal()` | Belum ada |
| `dashboard_pembuatan_jadwal()` | `PembuatanJadwalController::dashboard()` | Belum ada |
| `sideNavPembuatanJadwal.php` | `admin/super/components/side_nav_pembuatan_jadwal.php` | Belum ada |
| `jadwal-tanding/*` | Reuse modul sekretariat / super wrapper | Partial |
| `jadwal-seni/*` | Reuse modul sekretariat / super wrapper | Partial |
| `drawing_tanding()` | `PembuatanJadwalController::drawingTanding()` | Belum ada |
| `drawing_seni()` | `PembuatanJadwalController::drawingSeni()` | Belum ada |
| `generate_bagan_tanding_dari_jadwal()` | `PembuatanJadwalController::generateBaganTandingDariJadwal()` | Belum ada |
| `generate_bagan_seni_battle_dari_jadwal()` | `PembuatanJadwalController::generateBaganSeniBattleDariJadwal()` | Belum ada |
| `operasi_basis_data()` | `PembuatanJadwalController::operasiBasisData()` | Belum ada |

---

## 10) Risiko dan Mitigasi

Risiko:

1. Perbedaan query agregasi CI3 vs CI4 menyebabkan angka dashboard tidak konsisten.
2. Tumpang tindih route super admin vs sekretariat.
3. Fitur jadwal lanjutan belum lengkap di CI4.
4. Dependensi UI timeline (skedTape) belum siap.

Mitigasi:

1. Buat uji pembanding data per metrik dashboard.
2. Pakai namespace controller terpisah + filter role ketat.
3. Terapkan stub terkontrol sambil implementasi bertahap.
4. Sediakan fallback tampilan tabel jika komponen timeline belum dipakai.

---

## 11) QA Checklist

1. Super admin dapat memilih mode pembuatan jadwal.
2. Session `tipe_super_admin` berubah sesuai mode.
3. Dashboard pembuatan jadwal memuat metrik utama tanpa error.
4. Semua link side nav mode pembuatan jadwal tidak dead.
5. Modul jadwal tanding/seni tetap berjalan normal.
6. Akses route ditolak untuk non-super-admin.
7. `php -l` lolos untuk file yang ditambah/diubah.
8. Tambahkan test/unit atau test feature untuk route inti mode.

---

## 12) Definition of Done

Modul mode pembuatan jadwal dianggap selesai jika:

1. Mode + dashboard super admin aktif dan stabil.
2. Seluruh menu utama mode tersedia dengan route valid.
3. Modul prioritas (jadwal tanding/seni + diagnosis/overview) berfungsi.
4. Fitur lanjutan yang discope-kan sudah parity atau terdokumentasi jelas sebagai backlog.
5. Dokumen status migrasi diperbarui di `docs/migration-ci4-status.md`.

---

## 13) Urutan Eksekusi yang Direkomendasikan

1. Phase 0 - Audit mapping final.
2. Phase 1 - Aktivasi mode.
3. Phase 2 - Dashboard MVP.
4. Phase 3 - Side nav mode.
5. Phase 4 - Integrasi modul prioritas.
6. Phase 5 - Parity lanjutan + hardening + QA.

---

## 14) Catatan Implementasi Penting

- Pertahankan key session historis: `tipe_super_admin`.
- Pertahankan nilai mode: `pembuatan_jadwal` agar konsisten dengan data/logic lama.
- Gunakan pattern CI4: `session()->set()`, `redirect()->to()`, controller namespace `App\Controllers\Admin\Super`.
- Jangan bawa helper/library CI3 secara mentah; lakukan adaptasi native CI4.

---

Dokumen ini menjadi panduan kerja utama untuk migrasi mode pembuatan jadwal. Perubahan scope, blocker, dan progres per fase wajib diupdate berkala agar tim tetap sinkron.