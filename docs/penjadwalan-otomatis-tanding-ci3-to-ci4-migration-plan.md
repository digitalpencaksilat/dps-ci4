# Rencana Migrasi: Penjadwalan Otomatis Tanding (CI3 -> CI4)

Status: IN PROGRESS (Phase A-C selesai, Phase D hardening/QA berjalan)

Dokumen ini adalah planning implementasi flow **penjadwalan tanding otomatis** yang di CI3 berada di modul `Jadwal_tanding` (resources) dan akan diparity-kan ke CodeIgniter 4 pada project `dps-ci4`.

Target utama: parity perilaku (view + controller + model/service + validasi) terhadap CI3, dengan adaptasi idiom CI4 (Routes, Controller, Validation, Model/Service, transaksi DB).

---

## 1) Ruang Lingkup

Yang termasuk:

- Halaman GET untuk form penjadwalan tanding otomatis (parity CI3 `penjadwalan_tanding_otomatis()`).
- Handler POST untuk generate jadwal otomatis (parity CI3 `buat_jadwal_tanding_otomatis()`).
- Logic generate detail jadwal (`detail_jadwal_tanding`) berdasarkan daftar pertandingan yang tersedia dan pengaturan yang dipilih.
- Opsi "langsung buat PDF" setelah generate (jika dipakai pada CI3).
- Guard/validasi agar tidak membuat duplikasi (parity pengecekan CI3 `cek_pertandingan_terinput`).

Yang tidak termasuk (di luar scope planning ini):

- Refactor algoritma scheduling (hanya parity dulu).
- Pembuatan drawing/bagan tanding (sudah ada dokumen/flow terpisah).
- Perombakan UI/UX besar (gunakan UI minimal parity dulu).

---

## 2) Referensi Baseline CI3 (Sumber Kebenaran)

View (form):

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/shared_pages/jadwal_tanding/penjadwalan_tanding_otomatis.php`

Controller:

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/resources/Jadwal_tanding.php`
  - `penjadwalan_tanding_otomatis()` (GET)
  - `buat_jadwal_tanding_otomatis()` (POST)

Model:

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Jadwal_tanding_model.php`
  - `jadwal_tanding_otomatis_prestasi($pengaturan)`
  - `jadwal_tanding_otomatis_pemasalan($pengaturan)`

Validation rule CI3:

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/config/form_validation.php`
  - rule `create_jadwal_tanding_otomatis`

---

## 3) Entry Point dan Kontrak Data (Parity CI3)

### 3.1 GET: Form penjadwalan tanding otomatis

Tujuan:

- Menampilkan form pengaturan scheduling (tanggal/jam, gelanggang, jumlah partai, babak, urutan kelas, dll).
- Menyediakan data yang dibutuhkan dropdown/checkbox/urutan kelas.

Parity minimal:

- Field name dan shape payload mengikuti CI3 agar JS/validator parity:
  - `tanggal`
  - `jam_mulai`
  - `jam_selesai`
  - `keterangan` (opsional)
  - `id_gelanggang[]`
  - `jumlah_partai[]`
  - `babak_pertandingan[]`
  - `jenis_penjadwalan` (misal: prestasi/pemasalan/selang-seling sesuai CI3)
  - `urutan_id_kelas_tanding[]`
  - `langsung_buat_pdf` (checkbox)
  - `pdf_library` (opsional, bila CI3 menyediakan pilihan)

### 3.2 POST: Generate jadwal tanding otomatis

Tujuan:

- Memvalidasi payload.
- Membangun struktur `pengaturan` (setara CI3).
- Menjalankan scheduling dan menulis:
  - `jadwal_tanding` (per gelanggang)
  - `detail_jadwal_tanding` (bulk, berisi `id_jadwal_tanding`, `nomor_partai`, `id_pertandingan`)
- (Opsional) langsung generate PDF sesuai parameter.

Output:

- Redirect ke halaman jadwal tanding yang relevan (misal daftar jadwal atau detail gelanggang) dan flash message sukses/gagal.

---

## 4) Kondisi CI4 Saat Ini (Temuan)

- CI4 sudah memiliki modul jadwal tanding (CRUD jadwal, detail view, beberapa utilitas seperti sortir ulang/pola/swap/pdf) via:
  - `app/Controllers/Admin/Super/PembuatanJadwalController.php` (wrapper super admin)
  - `app/Controllers/Admin/Sekretariat/JadwalTandingController.php`
  - `app/Models/JadwalTandingModel.php`
  - view sekretariat: `app/Views/admin/sekretariat/jadwal_tanding/*`
- Namun, belum ada entrypoint parity CI3 untuk "buat jadwal tanding otomatis" yang menyusun `detail_jadwal_tanding` dari daftar pertandingan.

Implikasi:

- Implementasi akan menambah route + controller + view baru di CI4, serta service/model baru untuk algoritma generate.

---

## 5) Desain Target di CI4

### 5.1 Route (CI4)

Rekomendasi: letakkan di area super admin mode pembuatan jadwal.

Contoh rencana route (final menyesuaikan pola route CI4 yang sudah ada):

- GET `admin/super/jadwal-tanding/penjadwalan-otomatis`
- POST `admin/super/jadwal-tanding/buat-jadwal-tanding-otomatis`

Catatan:

- Kalau ingin parity URL CI3 secara literal, bisa buat alias route tanpa merusak struktur CI4.

### 5.2 Controller (CI4)

Buat controller khusus (atau tambahkan ke controller super admin yang relevan) untuk menampung GET+POST.

Rekomendasi file:

- `app/Controllers/Admin/Super/PenjadwalanTandingOtomatisController.php`

Method:

- `index()` (GET) menyiapkan data view.
- `store()` (POST) validasi input, panggil service generate, handle redirect/PDF.

### 5.3 View (CI4)

Rekomendasi file:

- `app/Views/admin/super/jadwal_tanding/penjadwalan_tanding_otomatis.php`

Prinsip:

- Samakan `name="..."` field dengan CI3.
- Pertahankan UX minimal (table repeater untuk gelanggang + jumlah partai + babak) dulu.

### 5.4 Service/Model (CI4)

Pisahkan algoritma scheduling dari controller.

Rekomendasi:

- `app/Services/JadwalTandingOtomatisService.php`

Tanggung jawab:

- Normalisasi dan validasi business-rule (di luar validation input form).
- Query pertandingan yang eligible.
- Generate mapping pertandingan -> slot partai per gelanggang.
- Insert `jadwal_tanding` dan `detail_jadwal_tanding` dalam transaksi DB.
- Guard duplikasi.
- (Opsional parity) memanggil penugasan wasit/juri bila modul CI4-nya ada.

---

## 6) Validasi (Parity Rule CI3)

Rule CI3 `create_jadwal_tanding_otomatis` mensyaratkan:

- `tanggal` (required)
- `jam_mulai` (required)
- `jam_selesai` (required)
- `id_gelanggang[]` (required)
- `jumlah_partai[]` (required)
- `babak_pertandingan[]` (required)
- `jenis_penjadwalan` (required)
- `urutan_id_kelas_tanding[]` (required)

Rencana CI4:

- Tambahkan rules pada controller `store()` menggunakan Validation CI4.
- Pastikan array fields di-handle dengan benar.

---

## 7) Algoritma Scheduling (Parity)

CI3 memiliki 2 jalur utama:

- `jadwal_tanding_otomatis_prestasi($pengaturan)`
- `jadwal_tanding_otomatis_pemasalan($pengaturan)`

Rencana CI4:

- Port 1:1 logic inti ke service CI4 terlebih dulu (parity), termasuk:
  - cara menentukan daftar pertandingan dan urutan berdasarkan `urutan_id_kelas_tanding`.
  - cara mengisi slot partai per gelanggang (menggunakan `jumlah_partai` dan rentang jam).
  - cara menyusun `detail_jadwal_tanding` dan insert bulk.
- Setelah parity tercapai, baru evaluasi refactor.

Guard parity:

- Pengecekan pertandingan sudah terinput di `detail_jadwal_tanding` sebelum insert.

Side effect parity (opsional):

- CI3 memanggil `Penilaian_tanding_model->tugaskan_wasit_juri(...)`.
- CI4: tentukan apakah modul penugasan ada; jika belum, catat sebagai gap.

---

## 8) PDF (Parity)

CI3 memiliki opsi `langsung_buat_pdf` dan kemungkinan memilih `pdf_library`.

Rencana CI4:

- Jika parameter `langsung_buat_pdf` aktif:
  - Setelah transaksi sukses, generate PDF jadwal tanding.
  - Gunakan mekanisme PDF yang sudah dipakai pada modul jadwal CI4 (misal mPDF) agar konsisten.

Deliverable parity minimal:

- PDF bisa di-generate untuk jadwal yang baru dibuat, atau minimal redirect ke halaman PDF.

---

## 9) Rencana Implementasi Bertahap (Phase)

### Phase A - Skeleton (route + controller + view)

Deliverables:

- Route GET/POST terdaftar.
- Controller GET menampilkan form (data dummy minimal bila perlu).
- Controller POST validasi input + dump/flash payload (tanpa insert DB dulu).

Acceptance:

- Super admin bisa membuka halaman form.
- Submit POST tidak error, validation bekerja.

### Phase B - Port algoritma (service + transaksi)

Deliverables:

- Service `JadwalTandingOtomatisService` dibuat.
- Insert `jadwal_tanding` dan `detail_jadwal_tanding` dilakukan dalam transaksi.
- Guard anti-duplikasi aktif.
- Port awal algoritma CI3 sudah masuk ke CI4:
  - jalur `prestasi`: alokasi round-robin ke gelanggang sesuai `jumlah_partai[id_gelanggang]`
  - jalur `pemasalan`: query urutan pemasalan + `acakUrutanPertandingan()` + `kelompokkanPertandinganKeDalamPaket()` + alokasi paket ke gelanggang
  - nomor partai per gelanggang mulai dari partai terakhir existing + 1

Acceptance:

- Submit menghasilkan jadwal dan detail jadwal sesuai parameter.
- Tidak ada duplikasi pertandingan pada detail.
- Struktur alokasi utama sudah mendekati CI3 untuk mode `prestasi` dan `pemasalan`.

### Phase C - Parity UX + PDF

Deliverables:

- View form parity CI3 (field sama, opsi PDF sama).
- PDF generation bekerja bila checkbox aktif.
- Field `jumlah_selang_seling` untuk mode pemasalan tersedia.

Acceptance:

- Hasil alur pengguna sama seperti CI3 (GET -> POST -> hasil + opsional PDF).
- Jika `langsung_buat_pdf` dicentang, sistem auto-generate PDF jadwal tanding per gelanggang (mPDF) dan simpan `jadwal_tanding.pdf_path`.

### Phase D - Hardening + QA

Deliverables:

- Logging error yang jelas.
- Penanganan edge case: gelanggang kosong, jam invalid, slot partai kurang, pertandingan habis, dsb.
- Document update status parity.
- Parity side-effect CI3: penugasan wasit/juri (butuh modul penilaian/perangkat di CI4).

Acceptance:

- Skenario error menghasilkan pesan yang informatif.
- Tidak ada partial insert (transaksi aman).

Catatan implementasi (temuan saat ini):

- CI3 melakukan penugasan juri via `Penilaian_tanding_model::tugaskan_wasit_juri($pertandingan, $id_gelanggang)`.
- Di CI4 sudah ditambahkan migration/model/service untuk `penilaian_tanding` dan `perangkat_pertandingan` + hook scheduling.
- Runtime verified (DB: `db_testing_event`): create penilaian tanding + assign juri sukses.
- Catatan: tabel `penilaian_tanding` dan `perangkat_pertandingan` tidak punya `created_at/updated_at`, maka model CI4 wajib `useTimestamps=false`.
- Skema aktif: metadata `jumlah_juri` diambil via join sampai `kategori_lomba.jumlah_juri` (bukan kolom di `pertandingan`).

---

## 10) Checklist Parity (Definition of Done)

- View parity: field name payload sama dengan CI3.
- Controller parity: ada GET form dan POST handler.
- Validation parity: rule minimal sesuai CI3.
- Algoritma parity: menghasilkan `jadwal_tanding` + `detail_jadwal_tanding` terisi.
- Guard parity: tidak meng-insert pertandingan yang sudah ada di detail.
- PDF parity: opsi langsung generate PDF tersedia dan berfungsi (minimal).
- Dokumentasi: flow CI4 dan mapping CI3->CI4 tertulis.

---

## 11) Risiko dan Catatan

- Risiko terbesar adalah perbedaan schema CI3 vs CI4 (atau asumsi query CI3 terhadap schema) yang mengubah cara mengambil daftar pertandingan eligible.
- Jika modul penugasan wasit/juri belum tersedia di CI4, parity side-effect harus ditandai sebagai gap.
- Bulk insert detail jadwal harus dioptimasi (batch insert) untuk menghindari timeouts.
