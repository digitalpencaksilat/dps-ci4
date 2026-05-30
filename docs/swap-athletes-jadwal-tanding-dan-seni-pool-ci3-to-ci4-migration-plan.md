# Rencana Migrasi: Swap Athletes (Jadwal Tanding + Jadwal Seni Pool) (CI3 -> CI4)

Status: DRAFT

Dokumen ini menjadi acuan untuk implementasi/migrasi fitur **Swap Athletes** pada area **Super Admin -> Pembuatan Jadwal** di project `dps-ci4`, dengan parity perilaku dari CI3 (`/Applications/XAMPP/xamppfiles/htdocs/dps`).

Catatan istilah:

- "Swap Athletes" pada UI CI3/CI4 berarti "tukar peserta" pada data jadwal/pertandingan.
- Domain fitur ini ada 2, dengan logic yang berbeda:
  - **Tanding**: swap dua `peserta_tanding` yang sudah ter-distribusi ke pertandingan.
  - **Seni Pool (Artistic Pool)**: swap dua slot penampilan/kelompok pada jadwal pool.

---

## 1) Tujuan

- Menyediakan fitur swap yang aman (guard skor/pemenang/penilaian) untuk memperbaiki jadwal tanpa harus reset besar.
- Menjaga parity output jadwal (tabel detail, tampilan, dan PDF export) setelah swap.
- Memindahkan logic swap yang masih "menempel" pada controller menjadi pola CI4 yang lebih rapi (service + transaksi + logging) agar mudah diaudit dan dipelihara.

---

## 2) Ruang Lingkup

Yang termasuk:

- Swap pada **jadwal tanding**.
- Swap pada **jadwal seni sistem pool** (swap kelompok/penampilan di slot pool).
- Validasi dan guard (minimal parity CI3) untuk mencegah swap pada data yang sudah "locked".
- Regenerasi PDF jadwal (atau minimal memastikan tombol/flow existing tidak rusak).

Yang tidak termasuk (di luar scope dokumen ini):

- Redesign UI/UX besar (cukup parity UI yang sudah ada di CI4).
- Optimasi algoritma drawing/penjadwalan otomatis.
- Migrasi digital scoring end-to-end (hanya dampak yang diperlukan supaya swap aman).

---

## 3) Baseline CI3 (Sumber Kebenaran)

### 3.1 Swap Tanding (CI3)

Controller:

- `application/controllers/resources/Jadwal_tanding.php`
  - `tukar_atlet_tanding()`

View/modal:

- `application/views/shared_components/jadwal_tanding/modal_tukar_atlet.php`

Model/logic inti:

- `application/models/resources/Pertandingan_model.php`
  - `transfer_atlet()`

Ringkas perilaku CI3 (high level):

- Input: `id_atlet_1`, `id_atlet_2` (sebenarnya `id_peserta_tanding`).
- Mencari pertandingan terkait peserta.
- Menjalankan transfer/swap dua arah.
- Sinkronisasi bagan kompetisi terdampak.
- Identifikasi jadwal terdampak dan regenerate PDF.

### 3.2 Swap Seni Pool (CI3)

Controller:

- `application/controllers/resources/Jadwal_seni.php`
  - `tukar_kelompok_peserta_seni_pool()`

View/modal:

- `application/views/shared_components/detail_jadwal_seni/tombol_edit_jadwal_pool.php`

Ringkas perilaku CI3 (high level):

- Input: `id_penampilan_seni_1`, `id_penampilan_seni_2`.
- Validasi kedua penampilan ada dan memiliki `jenis_seni` yang sama.
- Swap `id_kompetisi_seni` antar `penampilan_seni`.
- Swap slot jadwal: set `detail_jadwal_seni.id_penampilan_seni` ke null lalu assign silang.
- Update penilaian seni agar `id_perangkat_pertandingan` sesuai gelanggang.
- Regenerate PDF jadwal seni.

---

## 4) Kondisi CI4 Saat Ini (Temuan di `dps-ci4`)

### 4.1 Jadwal Tanding

UI modal sudah ada dan sudah dipakai pada halaman jadwal tanding sekretariat:

- `app/Views/admin/sekretariat/jadwal_tanding/show.php` memunculkan menu `Swap Athletes`
- `app/Views/shared_components/jadwal_tanding/modal_tukar_atlet.php`

Route:

- `POST admin/super/jadwal-tanding/tukar-atlet` -> `Admin\Super\PembuatanJadwalController::tukarAtletJadwalTanding`
- `POST admin/sekretariat/jadwal-tanding/tukar-atlet` -> `Admin\Sekretariat\JadwalTandingController::tukarAtlet` (saat ini masih stub "belum tersedia")

Implementasi swap (super admin) saat ini:

- `app/Controllers/Admin/Super/PembuatanJadwalController.php::tukarAtletJadwalTanding()`
  - guard: block jika atlet sudah terlibat pertandingan yang memiliki skor atau pemenang
  - swap langsung pada table `pertandingan` untuk field `id_atlet_merah` dan `id_atlet_biru` via sentinel (-id)

Gap dibanding CI3:

- Belum ada sinkronisasi bagan kompetisi.
- Belum ada identifikasi jadwal terdampak + regen PDF otomatis.
- Belum ada endpoint sekretariat yang aktif (yang ada baru super admin).

### 4.2 Jadwal Seni Pool

Belum ditemukan route/controller/view di CI4 yang setara dengan CI3 untuk swap seni pool (perlu audit lanjutan).

Implikasi:

- Planning ini mencakup pekerjaan membuat endpoint swap seni pool di CI4.

---

## 5) Kontrak Data dan Guard

### 5.1 Payload swap tanding

- `id_atlet_1` (int, required, != `id_atlet_2`)
- `id_atlet_2` (int, required)
- CSRF (CI4)

Guard minimal:

- Kedua peserta valid.
- Block jika salah satu peserta berada pada pertandingan yang:
  - sudah punya skor (kolom scoring tertentu), atau
  - sudah punya pemenang (kolom winner tertentu).

Catatan: di CI4 sudah ada helper/guard `athleteHasLockedMatches()` pada `PembuatanJadwalController`.

### 5.2 Payload swap seni pool

- `id_penampilan_seni_1` (int, required, != `id_penampilan_seni_2`)
- `id_penampilan_seni_2` (int, required)

Guard minimal parity CI3:

- Kedua `penampilan_seni` ada.
- `jenis_seni` kedua penampilan harus sama.
- Block jika slot sudah punya skor/penilaian final (definisikan lock condition di CI4).

---

## 6) Rencana Implementasi di CI4 (Bertahap)

### Phase A - Inventory + Parity Check

- Pastikan schema CI4 untuk:
  - `pertandingan`, `detail_jadwal_tanding`, `jadwal_tanding`
  - `penampilan_seni`, `detail_jadwal_seni`, `jadwal_seni`, `kelompok_peserta_seni`, `penilaian_seni`, `perangkat_pertandingan`
- Definisikan "locked match" untuk tanding & seni berdasarkan kolom CI4 yang ada.
- Konfirmasi: apakah swap hanya untuk super admin atau juga sekretariat (policy/role).

Output phase A:

- Catatan field/kolom yang menjadi lock.
- Daftar endpoint final yang disepakati.

### Phase B - Refactor Swap Tanding ke Service (Recommended)

Target:

- Memindahkan logic swap dari controller ke service agar bisa di-reuse (super admin + sekretariat) dan lebih mudah dites.

Rencana:

- Buat service baru:
  - `app/Services/JadwalTandingSwapService.php`
- API service:
  - `swapPeserta(int $idPeserta1, int $idPeserta2): void`

Di dalam transaksi DB:

- Jalankan swap `pertandingan.id_atlet_merah/id_atlet_biru` (tetap boleh sentinel pattern).
- (Jika dibutuhkan parity CI3) lakukan sinkronisasi/rekalkulasi data turunan:
  - update bagan kompetisi tanding
  - tentukan jadwal terdampak dan trigger regen PDF (atau tandai dirty)

Controller:

- `PembuatanJadwalController::tukarAtletJadwalTanding()` cukup:
  - validasi payload
  - call service
  - set flash message

### Phase C - Aktifkan Endpoint Swap untuk Sekretariat (Opsional)

- Tentukan apakah sekretariat boleh swap.
- Jika ya:
  - Implement `Admin\Sekretariat\JadwalTandingController::tukarAtlet()` dengan memanggil service yang sama.
  - Pastikan `routePrefix` pada modal mengarah ke endpoint yang tepat sesuai role.

### Phase D - Implement Swap Seni Pool di CI4

Target parity CI3:

- Endpoint POST yang menerima `id_penampilan_seni_1` dan `id_penampilan_seni_2`.
- Swap slot `detail_jadwal_seni` untuk pool.
- Update dampak `penampilan_seni` (kompetisi) + dampak penilaian/perangkat.
- Regen PDF jadwal seni.

Rencana file:

- Routes: tambah endpoint di `app/Config/Routes.php` (group super admin mode pembuatan jadwal):
  - `POST admin/super/jadwal-seni/tukar-kelompok-peserta-seni-pool` -> controller baru/method baru.
- Controller:
  - Tambah method di `app/Controllers/Admin/Super/PembuatanJadwalController.php` ATAU controller khusus `JadwalSeniController` untuk super admin.
- Service:
  - `app/Services/JadwalSeniPoolSwapService.php`

Catatan implementasi:

- Pastikan swap dilakukan dalam transaksi.
- Wajib ada guard lock condition (minimal: penilaian sudah ada / sudah final).

### Phase E - QA + Audit Trail

- Tambahkan logging `log_message('info', ...)` untuk:
  - siapa melakukan swap
  - pasangan yang ditukar
  - timestamp
  - jadwal terdampak
- Tambahkan test minimal (jika suite test untuk service tersedia):
  - swap tanding tidak menukar ketika locked
  - swap tanding berhasil menukar kedua field merah/biru
  - swap seni pool menolak jenis_seni berbeda

---

## 7) Checklist Verifikasi (Manual)

Tanding:

1. Buka detail jadwal tanding super admin.
2. Jalankan swap 2 peserta.
3. Pastikan di UI daftar partai berubah sesuai swap.
4. Pastikan tidak ada pertandingan duplikat / hilang.
5. Coba swap peserta yang sudah ada skor/pemenang -> harus ditolak.
6. Jika PDF auto-regenerate: pastikan file PDF berubah sesuai jadwal terbaru.

Seni pool:

1. Buka detail jadwal seni pool.
2. Swap dua penampilan.
3. Pastikan nomor partai/slot pool bertukar.
4. Pastikan penilaian/perangkat mengikuti gelanggang yang benar.
5. Coba swap dua penampilan beda `jenis_seni` -> harus ditolak.

---

## 8) Open Questions (Butuh Keputusan)

- Swap tanding di CI4 saat ini swap `id_atlet_merah/id_atlet_biru` langsung. Apakah kita perlu parity CI3 untuk sinkronisasi bagan dan regen PDF otomatis, atau cukup swap data pertandingan saja?
- Siapa role yang boleh melakukan swap (super admin saja vs sekretariat juga)?
- Lock condition untuk seni pool di CI4 akan menggunakan indikator apa (skor, status penilaian, pemenang, atau flag lain)?
