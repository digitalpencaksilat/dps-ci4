# Rencana Migrasi Mode Pembuatan Jadwal (Super Admin) CI3 ke CI4

Status: Phase 1-4 mayoritas selesai (dashboard audit selesai, diagnosis/overview tanding aktif, wrapper jadwal tanding/seni aktif, modul lanjutan lain masih stub)  
Scope: Migrasi mode **pembuatan_jadwal** dari CI3 (`htdocs/dps`) ke CI4 (`dps-ci4`) dengan pendekatan bertahap dan parity perilaku.

Update implementasi terbaru (CI4):

- Mode `pembuatan_jadwal` sudah aktif via `ModeController::pembuatanJadwal()`.
- Route super admin untuk dashboard + modul dasar pembuatan jadwal sudah terdaftar.
- Dashboard `admin/super/dashboard-pembuatan-jadwal` sudah tersedia (MVP + audit detail preview).
- Audit query dipindahkan ke service `app/Services/Admin/Super/PembuatanJadwalAuditService.php` (controller lebih tipis).
- Crosscheck schema DB aktual sudah dilakukan: tidak ada tabel `kategori_tanding`, dan nomor partai jadwal tersimpan di tabel detail (`detail_jadwal_tanding` / `detail_jadwal_seni`).
- Sidebar super admin untuk mode `pembuatan_jadwal` sudah aktif.
- Modul Jadwal Tanding/Seni pada mode ini sudah aktif via wrapper super admin (reuse view sekretariat + routePrefix + route group super).
- Menu `jadwal-tanding/diagnosis`, `jadwal-tanding/overview`, `jadwal-seni/diagnosis`, dan `jadwal-seni/overview` sudah aktif (halaman super admin khusus).
- Baseline fitur lanjutan jadwal sudah aktif: PDF export sederhana via mPDF, sortir ulang nomor partai, pola penjadwalan baseline, dan tukar atlet.
- Hardening awal Phase 5 sudah ditambahkan: validasi pola, blokir perubahan pada pertandingan yang sudah memiliki skor/pemenang, serta error handling/logging saat generate PDF gagal.
- Modul drawing/generate/operasi sudah tidak stub utama: drawing tanding/seni memakai flow bulk per kategori seperti CI3, generate bagan dari jadwal sudah POST bulk, dan operasi basis data sudah punya reset jadwal dengan guard.

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

## 4) Kondisi CI4 Saat Ini (Temuan Audit)

### 4.1 Flow mode (Super Admin)

1. Mode super admin CI4 yang sudah ada baru mencakup:
   - `pengaturan_event`
   - `perngaturan_kategori_lomba` (catatan: penamaan mengikuti CI3, typo masih terbawa)
2. Belum ada method mode `pembuatan_jadwal` di:
   - `app/Controllers/Admin/Super/ModeController.php`
3. Belum ada route/dashboard `admin/super/dashboard-pembuatan-jadwal`.
4. View pemilihan mode super admin saat ini hanya menampilkan 2 kartu mode:
   - `app/Views/admin/super/menu_tipe_super_admin.php`
5. Layout sidebar CI4 sudah membaca session `tipe_super_admin` dan melakukan branching menu:
   - `app/Views/layouts/admin.php` menggunakan `session()->get('tipe_super_admin')` untuk menampilkan menu mode tertentu.
   - Saat ini hanya ada branch untuk `pengaturan_event` dan `perngaturan_kategori_lomba`.

### 4.2 Modul jadwal yang sudah ada (Sekretariat)

1. Jadwal Tanding sudah ada (CRUD dasar + detail view):
   - Controller: `app/Controllers/Admin/Sekretariat/JadwalTandingController.php`
   - Model: `app/Models/JadwalTandingModel.php`
   - Route group: `admin/sekretariat/jadwal-tanding*` di `app/Config/Routes.php`
   - Catatan: beberapa endpoint masih stub (`createPdfAjax`, `tukarAtlet`, `sortirUlang`, `polaPenjadwalan`).
2. Jadwal Seni sudah ada (CRUD dasar + detail view):
   - Controller: `app/Controllers/Admin/Sekretariat/JadwalSeniController.php`
   - Model: `app/Models/JadwalSeniModel.php`

### 4.3 Kesenjangan utama vs CI3

1. Belum ada dashboard agregasi seperti CI3 (`dashboard_pembuatan_jadwal`) yang menampilkan metrik:
   - pertandingan belum dijadwalkan
   - BYE yang terjadwal
   - seni pool belum dijadwalkan
   - seni battle belum dijadwalkan
   - mismatch sistem penampilan seni
2. Belum ada side nav khusus mode pembuatan jadwal (CI3: `sideNavPembuatanJadwal.php`).
3. Belum ada endpoint mode-level untuk drawing/generate bagan/operasi basis data di area `admin/super/*`.

### 4.4 Baseline CI3 yang perlu diparity-kan (verifikasi dari source)

Controller CI3: `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/users/Super_admin.php`

- `mode_pembuatan_jadwal()` set session `tipe_super_admin=pembuatan_jadwal` lalu redirect ke `users/super-admin/dashboard-pembuatan-jadwal`
- `dashboard_pembuatan_jadwal()` mengisi data via model:
  - `Gelanggang_model`, `Jadwal_tanding_model`, `Jadwal_seni_model`
  - `Peserta_tanding_model`
  - `Pertandingan_model` (filter untuk belum dijadwalkan + BYE terjadwal)
  - `Kelompok_peserta_seni_model` (pool belum dijadwalkan)
  - `Battle_seni_model` (battle belum dijadwalkan)
  - `Penampilan_seni_model` (cek mismatch sistem penampilan)
  - `Detail_jadwal_tanding_model` (grouping per gelanggang)
- View CI3 yang dipakai:
  - `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/admin/super_admin/dashboard_pembuatan_jadwal.php`
  - `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/admin/super_admin/components/sideNavPembuatanJadwal.php`
  - Dashboard CI3 memakai komponen skedTape + tautan ke `jadwal-tanding/{id}` dan `jadwal-seni/{id}` (pola ini perlu disesuaikan ke route CI4).

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
    - Diagnosis Tanding
    - Overview Tanding
    - Jadwal Tanding
    - Diagnosis Seni
    - Overview Seni
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
6. **Schema-first implementation**: query wajib mengikuti schema DB aktual, bukan asumsi dari nama field lama CI3.

### Baseline Schema yang Harus Diikuti

1. Tidak ada tabel `kategori_tanding` di schema aktual; relasi tanding mengikuti:
   - `pertandingan -> kompetisi_tanding -> kelas_tanding -> kategori_lomba -> kategori_usia`.
2. Nomor partai jadwal disimpan di tabel detail:
   - `detail_jadwal_tanding.nomor_partai`
   - `detail_jadwal_seni.nomor_partai`
3. Untuk status "sudah/belum dijadwalkan", pengecekan utama menggunakan keberadaan baris pada tabel detail (`EXISTS/NOT EXISTS`), bukan asumsi kolom `nomor_partai` di tabel induk.

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
- `POST admin/super/operasi-basis-data/reset-seluruh-jadwal`
- `GET admin/super/drawing-tanding`
- `POST admin/super/drawing-tanding/{id}/acak-bagan`
- `GET admin/super/drawing-seni`
- `POST admin/super/drawing-seni/{id}/acak-bagan-battle`
- `POST admin/super/drawing-seni/{id}/beri-nomor-undi`
- `GET admin/super/generate-bagan-tanding-dari-jadwal`
- `POST admin/super/generate-bagan-tanding-dari-jadwal`
- `GET admin/super/generate-bagan-seni-battle-dari-jadwal`
- `POST admin/super/generate-bagan-seni-battle-dari-jadwal`
- Route alias terkontrol ke modul jadwal/diagnosis/overview bila dibutuhkan.

Catatan:

- Hindari menyalin mentah path CI3 `users/super-admin/*`; gunakan pola CI4 konsisten `admin/super/*`.
- Bila perlu backward compatibility, alias route lama boleh ditambah sementara tetapi tetap dijaga filter-nya.

---

## 9) Tabel Mapping Awal CI3 -> CI4

| CI3 | CI4 Target | Dependency utama | Status audit |
|---|---|---|---|
| `mode_pembuatan_jadwal()` | `ModeController::pembuatanJadwal()` | Session `tipe_super_admin` | Selesai |
| `dashboard_pembuatan_jadwal()` | `PembuatanJadwalController::dashboard()` | `GelanggangModel`, `JadwalTandingModel`, `JadwalSeniModel`, `PembuatanJadwalAuditService` | Partial selesai: metrik + detail preview, timeline belum |
| `dashboard_pembuatan_jadwal.php` | `app/Views/admin/super/dashboard_pembuatan_jadwal.php` | shared table components + optional timeline/skedTape | Partial selesai: fallback tabel |
| `sideNavPembuatanJadwal.php` | branch di `layouts/admin.php` | route `admin/super/*` | Selesai baseline |
| `jadwal-tanding/*` | Super wrapper / shared service dari `Admin\Sekretariat\JadwalTandingController` | `JadwalTandingModel`, `GelanggangModel` | Selesai wrapper super: list/detail/create/update keterangan/delete + endpoint ajax dasar |
| `jadwal-seni/*` | Super wrapper / shared service dari `Admin\Sekretariat\JadwalSeniController` | `JadwalSeniModel`, `GelanggangModel` | Selesai wrapper super: list/detail/create/update keterangan/delete + endpoint ajax dasar |
| `drawing_tanding()` | `PembuatanJadwalController::drawingTanding()` + bulk POST distribusi/acak bagan/manual tools | `SistemGugurTunggalService`, query kategori/pool tanding | Partial parity kuat: struktur bulk per kategori mengikuti CI3 + UI CI4; algoritma distribusi manual/komposisi masih baseline/fallback |
| `drawing_seni()` | `PembuatanJadwalController::drawingSeni()` + bulk POST distribusi/nomor undi/acak battle | `SekretariatKategoriSeniService`, `SistemGugurTunggalService` | Selesai parity utama: bulk per kategori, nomor undi, acak battle, dan mode pisah kontingen round-robin per kontingen aktif |

| `generate_bagan_tanding_dari_jadwal()` | `PembuatanJadwalController::generateBaganTandingDariJadwal()` + `prosesGenerateBaganTandingDariJadwal()` | kompetisi tanding + ringkasan jadwal + regenerate bagan dari partai existing | Selesai parity utama: bulk checkbox + POST generate bagan tanding dari data existing |
| `generate_bagan_seni_battle_dari_jadwal()` | `PembuatanJadwalController::generateBaganSeniBattleDariJadwal()` + `prosesGenerateBaganSeniBattleDariJadwal()` | kompetisi seni battle + ringkasan jadwal + regenerate bagan dari battle existing | Selesai parity utama: bulk checkbox + POST generate bagan battle dari data existing |
| `operasi_basis_data()` | `PembuatanJadwalController::operasiBasisData()` + `resetSeluruhJadwal()` | operasi admin data berisiko tinggi | Partial parity: statistik + check integritas + reset seluruh jadwal dengan double confirmation |
| `jadwal-tanding/diagnosis` | `PembuatanJadwalController::diagnosisTanding()` | `PembuatanJadwalAuditService` | Selesai baseline: validasi belum terjadwal + BYE terjadwal |
| `jadwal-tanding/overview` | `PembuatanJadwalController::overviewTanding()` | `JadwalTandingModel::get_all()` | Selesai baseline: ringkasan jadwal + link detail |
| `jadwal-seni/diagnosis` | `PembuatanJadwalController::diagnosisSeni()` | `PembuatanJadwalAuditService` | Selesai baseline: pool, battle, mismatch sistem penampilan |
| `jadwal-seni/overview` | `PembuatanJadwalController::overviewSeni()` | `JadwalSeniModel::get_all()` | Selesai baseline: ringkasan jadwal + link detail |
| `create-pdf-ajax` jadwal | endpoint super admin di `PembuatanJadwalController` | `MpdfService`, view PDF sederhana | Selesai baseline: menghasilkan file PDF dan update `pdf_path` |
| `tukar-atlet` / `sortir-ulang` / `pola-penjadwalan` | endpoint super admin di `PembuatanJadwalController` | query builder `pertandingan` / `detail_jadwal_tanding` | Selesai baseline: validasi dasar + renumber sederhana |
| `utilities/log` | route development/super utility terproteksi | log viewer aman | Belum ada di mode super |

---

## 10) Modul, Controller, View, Model yang Perlu Disiapkan

### 10.1 Controller CI4 target

1. `app/Controllers/Admin/Super/ModeController.php`
   - Tambah method `pembuatanJadwal()`.
   - Set session `tipe_super_admin = pembuatan_jadwal`.
   - Redirect ke `admin/super/dashboard-pembuatan-jadwal`.
2. `app/Controllers/Admin/Super/PembuatanJadwalController.php`
   - `dashboard()` untuk agregasi validasi jadwal.
   - `drawingTanding()`, `drawingSeni()` untuk halaman drawing.
   - `generateBaganTandingDariJadwal()`, `generateBaganSeniBattleDariJadwal()` untuk halaman generate bagan.
   - `operasiBasisData()` untuk halaman operasi basis data (perlu guard ekstra karena destructive).
   - `diagnosisTanding()` dan `overviewTanding()` jika tidak memakai wrapper existing.

### 10.2 View CI4 target

1. `app/Views/admin/super/menu_tipe_super_admin.php`
   - Tambah kartu mode Pembuatan Jadwal.
2. `app/Views/admin/super/dashboard_pembuatan_jadwal.php`
   - Port metrik CI3 dan tabel validasi.
   - Timeline skedTape boleh dibuat fase lanjutan jika asset/plugin belum siap.
3. `app/Views/admin/super/components/side_nav_pembuatan_jadwal.php`
   - Direkomendasikan dipisah agar `layouts/admin.php` tidak makin besar.
4. View halaman modul lanjutan:
   - `drawing_tanding.php`
   - `drawing_seni.php`
   - `generate_bagan_tanding_dari_jadwal.php`
   - `generate_bagan_seni_battle_dari_jadwal.php`
   - `operasi_basis_data.php`

### 10.3 Model/service CI4 target

1. Model yang sudah ada dan bisa dipakai langsung:
   - `app/Models/JadwalTandingModel.php`
   - `app/Models/JadwalSeniModel.php`
   - `app/Models/GelanggangModel.php`
2. Model/query yang perlu dicek atau dibuat untuk parity dashboard:
   - pertandingan belum dijadwalkan (`PertandinganModel` atau query builder dedicated)
   - BYE yang sudah punya nomor partai
   - kelompok seni pool belum dijadwalkan
   - battle seni non-BYE belum dijadwalkan
   - mismatch sistem penampilan (`PenampilanSeniModel`/service validasi)
   - detail jadwal tanding per gelanggang
3. Service audit dashboard yang sudah dibuat:
   - `app/Services/Admin/Super/PembuatanJadwalAuditService.php`
   - Menampung query pertandingan belum dijadwalkan, BYE terjadwal, seni pool/battle belum dijadwalkan, dan mismatch sistem penampilan.
   - Berikutnya perlu ditambah pagination/filter dan pembanding angka dengan baseline CI3 bila diperlukan.

### 10.4 Catatan integrasi route

- Semua route mode ini harus berada di group `admin/super` dengan filter `adminrole:super_admin`.
- Jika reuse controller sekretariat secara langsung, filter `adminrole:sekretariat` saat ini akan menghalangi super admin pada route `admin/sekretariat/*`; lebih aman membuat wrapper super atau shared service.
- Untuk POST destructive/generate, tambahkan CSRF + flash message + audit/log minimal.

---

## 11) Risiko dan Mitigasi

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

## 12) QA Checklist

1. Super admin dapat memilih mode pembuatan jadwal.
2. Session `tipe_super_admin` berubah sesuai mode.
3. Dashboard pembuatan jadwal memuat metrik utama tanpa error.
4. Semua link side nav mode pembuatan jadwal tidak dead.
5. Modul jadwal tanding/seni tetap berjalan normal.
6. Akses route ditolak untuk non-super-admin.
7. `php -l` lolos untuk file yang ditambah/diubah.
8. Tambahkan test/unit atau test feature untuk route inti mode.

---

## 13) Definition of Done

Modul mode pembuatan jadwal dianggap selesai jika:

1. Mode + dashboard super admin aktif dan stabil.
2. Seluruh menu utama mode tersedia dengan route valid.
3. Modul prioritas (jadwal tanding/seni + diagnosis/overview) berfungsi.
4. Fitur lanjutan yang discope-kan sudah parity atau terdokumentasi jelas sebagai backlog.
5. Dokumen status migrasi diperbarui di `docs/migration-ci4-status.md`.

---

## 14) Urutan Eksekusi yang Direkomendasikan

1. Phase 0 - Audit mapping final.
2. Phase 1 - Aktivasi mode.
3. Phase 2 - Dashboard MVP.
4. Phase 3 - Side nav mode.
5. Phase 4 - Integrasi modul prioritas.
6. Phase 5 - Parity lanjutan + hardening + QA.

---

## 15) Catatan Implementasi Penting

- Pertahankan key session historis: `tipe_super_admin`.
- Pertahankan nilai mode: `pembuatan_jadwal` agar konsisten dengan data/logic lama.
- Gunakan pattern CI4: `session()->set()`, `redirect()->to()`, controller namespace `App\Controllers\Admin\Super`.
- Jangan bawa helper/library CI3 secara mentah; lakukan adaptasi native CI4.

---

Dokumen ini menjadi panduan kerja utama untuk migrasi mode pembuatan jadwal. Perubahan scope, blocker, dan progres per fase wajib diupdate berkala agar tim tetap sinkron.