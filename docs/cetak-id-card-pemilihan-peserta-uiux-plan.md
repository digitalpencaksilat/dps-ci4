# Planning: Peningkatan UI/UX Pemilihan Peserta — Halaman Cetak ID Card

Status: Draft / Proposal
Modul: `admin/sekretariat/id-card` (Cetak Per Peserta & Cetak Per Kontingen)
Tanggal: 2026-06-06

---

## 1. Konteks & Tujuan

Halaman cetak ID Card sekretariat sudah berfungsi (batch → PNG → ZIP). Namun komponen **pemilihan peserta** masih kasar dibanding modul lain di project CI4 ini dan dibanding legacy DPS. Tujuan planning ini: membuat pemilihan peserta lebih **presisi, cepat, dan nyaman**, sambil tetap menjaga parity bisnis (pilih per kontingen, per peserta, batch, cetak individual).

Prinsip:
- Selaras dengan visual language CI4 (Bootstrap 5, DataTables, Toastr, SweetAlert2, `admin-card`, `_action_toolbar`).
- Tidak mengubah skema DB.
- Hindari N+1 dan payload berlebih.

---

## 2. Kondisi Saat Ini (CI4)

### 2.1 `cetak_per_peserta.php`
- Harus **pilih 1 kontingen dulu** lewat dropdown → baru AJAX load daftar peserta.
- Daftar peserta ditampilkan sebagai **checkbox list polos** (bukan tabel), tanpa:
  - pencarian/search dalam daftar,
  - sorting kolom,
  - pagination,
  - kolom kategori yang terstruktur,
  - foto / status foto,
  - tombol cetak per baris (hanya batch).
- Tab Tanding / Seni terpisah, tapi tidak ada ringkasan jelas berapa total per tab vs terpilih.
- Tidak bisa memilih peserta dari **beberapa kontingen sekaligus** (karena list di-reset saat ganti kontingen).

### 2.2 `cetak_per_kontingen.php`
- Checkbox grid kontingen + quality selector (sudah diperbaiki).
- Belum ada search kontingen, belum ada DataTables, belum tampil rincian per kontingen yang rapi.

### 2.3 Gap utama vs Legacy
| Aspek | Legacy DPS | CI4 sekarang |
|------|-----------|--------------|
| Tabel peserta | DataTable lengkap (search, sort, paging) | Checkbox list polos |
| Load data | Semua peserta sekaligus (server-render) | Lazy per-kontingen (AJAX) |
| Filter kontingen | Custom search DataTable | Dropdown reset list |
| Cetak per baris | Ada tombol "Cetak" per peserta | Tidak ada |
| Multi-kontingen | Bisa (filter saja, pilihan tetap) | Tidak (reset saat ganti) |
| Pilih semua | Per tab + visible rows | Per tab |

---

## 3. Sasaran UX (Definition of Done)

1. Tabel peserta berbasis **DataTables** dengan search, sort, dan pagination.
2. **Filter kontingen** tanpa menghapus pilihan yang sudah dibuat (selection persist).
3. **Selection persist** lintas halaman pagination dan lintas filter/tab.
4. **Cetak per baris** (1 peserta langsung) + **cetak batch** (banyak peserta).
5. **Action bar lengket (sticky)** menampilkan jumlah terpilih (Tanding X / Seni Y / Total Z) + tombol aksi.
6. Kolom informatif: No, (checkbox), Nama, Kontingen, Kategori (badge), Status Foto, Aksi.
7. **Empty state**, **loading state**, dan **error state** yang jelas.
8. Responsive di layar kecil (kolom sekunder bisa collapse / responsive DataTables).
9. Quality selector (2×/3×/4×) tetap tersedia dan terhubung ke batch.
10. Konsisten dengan Toastr + SweetAlert2 untuk feedback.

---

## 4. Keputusan Desain

### 4.1 Strategi load data: Server-side DataTables
Daftar peserta bisa besar (ratusan–ribuan). Rekomendasi: **server-side processing DataTables** untuk Tanding & Seni, dengan endpoint terpisah:

- `GET id-card/data/peserta-tanding` (server-side: draw, start, length, search, order, filter kontingen)
- `GET id-card/data/peserta-seni`

Alternatif (lebih sederhana, kalau data < ~1500): **client-side DataTables** dengan satu fetch semua peserta. Diputuskan saat implementasi berdasar volume real. Default rekomendasi: **server-side** agar aman skala besar dan hemat memori.

### 4.2 Selection persistence
Masalah klasik DataTables: checkbox di halaman lain hilang saat paging. Solusi:
- Simpan pilihan di **Set/objek JS** (`selectedTanding`, `selectedSeni`) keyed by id.
- Pada event `draw.dt`, re-apply `checked` berdasarkan Set.
- Submit batch membaca dari Set, bukan dari DOM checkbox yang terlihat.

### 4.3 Filter kontingen
- Dropdown kontingen → kirim sebagai parameter ke endpoint server-side (atau `column().search()` untuk client-side).
- Mengubah filter **tidak** mereset Set pilihan (beda dari perilaku sekarang).

### 4.4 Satu halaman, dua tab
Pertahankan tab Tanding/Seni, tapi:
- Badge jumlah per tab (total data) + badge jumlah terpilih per tab.
- Action bar global menampilkan gabungan terpilih.

### 4.5 Komponen visual
- Bungkus dengan `admin-card` + `_action_toolbar` (konsisten modul lain).
- Badge kategori: Tanding = `bg-primary`, Seni = `bg-info`.
- Status foto: badge hijau "Foto OK" / abu "Tanpa Foto" (memakai info ketersediaan file).
- Tombol cetak per baris: `btn-outline-danger btn-sm`.

---

## 5. Perubahan Backend (Controller/Service)

### 5.1 Endpoint baru (server-side DataTables)
Di `IdCardController`:
- `dataPesertaTanding()` → JSON `{ draw, recordsTotal, recordsFiltered, data: [...] }`
- `dataPesertaSeni()` → idem

Kolom data per baris: `id`, `nama_pendaftar`, `nama_kontingen`, `kategori_label`, `has_foto` (bool), `id_kontingen`.

### 5.2 Service
Tambah di `IdCardService`:
- `paginatePesertaTanding(array $params): array` (limit/offset/search/order/filter kontingen + count)
- `paginatePesertaSeni(array $params): array`
- Reuse query join yang sudah ada (`apiPesertaTanding` saat ini) + tambah `LIMIT/OFFSET`, `WHERE ... LIKE`, `ORDER BY`, dan `COUNT(*)` untuk total/filtered.
- Hitung `has_foto` via cek kolom `foto` non-empty (cek `is_file` opsional, hindari overhead I/O pada list besar — cukup non-empty untuk badge awal; verifikasi file dilakukan saat render kartu).

Catatan performa:
- Pastikan join hanya kolom yang dibutuhkan (sudah dilakukan).
- Index: andalkan PK/FK existing (`id_kontingen`, `id_pendaftar`).

### 5.3 Route
Tambah di group `admin/sekretariat`:
```
$routes->get('id-card/data/peserta-tanding', '...IdCardController::dataPesertaTanding');
$routes->get('id-card/data/peserta-seni', '...IdCardController::dataPesertaSeni');
```
Endpoint lama `apiPesertaTanding/Seni` bisa tetap (dipakai dropdown lain) atau dideprecate.

---

## 6. Perubahan Frontend (View/JS)

### 6.1 `cetak_per_peserta.php`
- Ganti checkbox list → 2 tabel DataTables (`#tblTanding`, `#tblSeni`) server-side.
- Kolom: checkbox | No | Nama | Kontingen | Kategori | Foto | Aksi (Cetak).
- Toolbar atas: filter kontingen (select2 opsional), quality selector, search bawaan DataTables.
- Action bar sticky bawah: "Tanding: X · Seni: Y · Total: Z" + tombol "Cetak Terpilih", "Pilih Semua (hasil filter)", "Bersihkan Pilihan".
- JS:
  - Inisialisasi DataTables server-side dengan `ajax` ke endpoint.
  - `selectedTanding`/`selectedSeni` Set untuk persistence.
  - `draw.dt` → re-check.
  - "Pilih Semua hasil filter" → ambil semua id sesuai filter (endpoint `?ids_only=1` mengembalikan array id, supaya tidak perlu load semua baris).
  - Submit batch via form tersembunyi ke iframe off-screen (pola yang sudah benar sekarang).

### 6.2 `cetak_per_kontingen.php`
- Upgrade grid → DataTables kontingen dengan kolom: checkbox | Kontingen | Tanding | Seni | Total | Aksi (Cetak).
- Search + sort + paging.
- Selection persist sama seperti peserta.

### 6.3 Komponen bersama
- Buat partial JS util kecil (opsional) untuk handler progress SweetAlert (saat ini diduplikasi di 2 view) agar DRY.

---

## 7. Rencana Implementasi (Bertahap)

### Fase 1 — Backend pagination/search
- Tambah method service `paginatePeserta*` + endpoint controller + route.
- Validasi via unit/manual: response JSON benar untuk draw/search/order/filter.

### Fase 2 — Tabel peserta DataTables + selection persist
- Rewrite `cetak_per_peserta.php` ke DataTables server-side.
- Implement Set-based selection + `draw.dt` re-check.
- Cetak per baris + batch terpilih.

### Fase 3 — Filter & "Pilih semua hasil filter"
- Dropdown kontingen → param endpoint.
- Endpoint `ids_only` untuk select-all lintas halaman.

### Fase 4 — Cetak per kontingen DataTables
- Upgrade `cetak_per_kontingen.php` ke DataTables + selection persist.

### Fase 5 — Polish UI/UX
- Sticky action bar, badge status foto, empty/loading/error state, responsive, Toastr feedback.
- DRY handler progress.

### Fase 6 — Validasi & QA
- `php -l`, cek route, smoke test query (CLI), uji manual flow lengkap.

Catatan: Fase 1–2 sudah menghasilkan peningkatan UX yang signifikan dan bisa dirilis lebih dulu.

---

## 8. Tes / Validasi

1. `php -l` semua file PHP yang diubah.
2. Endpoint server-side: uji draw/search/order/paging/filter kontingen mengembalikan data benar.
3. Selection persist: pilih di halaman 1, pindah ke halaman 3, kembali → tetap tercentang.
4. Filter kontingen tidak menghapus pilihan.
5. "Pilih semua hasil filter" memilih seluruh id sesuai filter (bukan hanya halaman aktif).
6. Cetak per baris (1 peserta) → PNG terunduh.
7. Cetak batch campuran Tanding+Seni → ZIP berisi semua kartu, filename benar.
8. Empty state (kontingen tanpa peserta), loading state, error state tampil benar.
9. Responsive di viewport kecil.
10. Tidak ada regresi pada cetak per kontingen.

---

## 9. Risiko & Tradeoff

- **Server-side vs client-side**: server-side lebih aman untuk data besar tapi lebih banyak kode. Bila volume kecil, client-side lebih sederhana. Putuskan saat tahu jumlah peserta real.
- **Select-all lintas halaman**: butuh endpoint `ids_only`; tanpa itu, select-all hanya halaman aktif (kurang intuitif).
- **Status foto via `is_file`** pada list besar = banyak stat I/O. Mitigasi: badge awal cukup berdasar kolom `foto` non-empty; verifikasi fisik hanya saat render kartu (sudah ada).
- **Konsistency barcode seni** (id_peserta_seni vs id_kelompok) di luar scope planning ini.

---

## 10. Berkas yang Kemungkinan Berubah

Baru:
- (opsional) partial JS util progress handler.

Diubah:
- `app/Controllers/Admin/Sekretariat/IdCardController.php` (endpoint data + ids_only)
- `app/Services/IdCardService.php` (paginate + count + search/order)
- `app/Config/Routes.php` (route data)
- `app/Views/admin/sekretariat/id_card/cetak_per_peserta.php` (rewrite ke DataTables)
- `app/Views/admin/sekretariat/id_card/cetak_per_kontingen.php` (upgrade ke DataTables)

---

## 11. Pertanyaan Terbuka

1. Perkiraan jumlah peserta maksimum (untuk putuskan server-side vs client-side)?
2. Perlukah kolom **foto thumbnail** kecil di tabel, atau cukup badge status?
3. Perlukah **cetak per kontingen langsung dari tabel peserta** (mis. tombol cepat), atau cukup dari halaman per-kontingen?
4. Perlu **select2** untuk dropdown kontingen (search) atau cukup `<select>` biasa?
5. Perlukah menyimpan preferensi quality terakhir (localStorage)?
