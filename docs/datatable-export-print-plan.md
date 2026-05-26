# DataTable Export/Print Scalability Plan

## Status: FONDASI SELESAI, ROLLOUT BERJALAN

**Update: 26 Mei 2026** — Tahap 1-3 selesai. Tahap 5 kontingen sudah dimigrasi.

## Tujuan

Membangun fondasi export Excel dan print untuk DataTable yang:

1. tetap menghasilkan output yang mirip dengan CI3
2. scalable untuk banyak tabel dengan kolom berbeda-beda
3. mudah dimaintain tanpa script export panjang di setiap view
4. aman dimigrasikan bertahap tanpa merusak halaman yang sudah berjalan

## Masalah Saat Ini

Berdasarkan audit di `docs/ui-ux-audit-report.md`, masalah utamanya adalah:

- asset Buttons belum jadi fondasi global
- script export masih tersebar di view
- beberapa view masih memakai pola CI3 langsung
- style export/print belum punya standar bersama
- tabel dengan kebutuhan berbeda diperlakukan dengan pendekatan copy-paste

## Prinsip Solusi

Yang disamakan bukan isi semua tabel, tetapi kerangka export-nya.

Artinya:

- setiap tabel boleh punya kolom, urutan, alignment, dan kebutuhan masing-masing
- tetapi engine export, style dasar, header print, naming file, dan perilaku `.no-export` harus terpusat

## Target Arsitektur

### 1. Asset global di layout

Semua asset DataTables Buttons yang dibutuhkan dimuat sekali di layout.

Target file:
- `app/Views/layouts/admin.php`
- `app/Views/layouts/kontingen.php`

Asset yang dipertimbangkan:
- `buttons.dataTables.min.css`
- `dataTables.buttons.min.js`
- `buttons.html5.min.js`
- `buttons.print.min.js`
- `buttons.colVis.min.js`
- `jszip.min.js`
- `pdfmake.min.js`
- `vfs_fonts.js`

Catatan:
- `pdfmake` hanya dipertahankan jika memang ingin parity CI3 untuk PDF client-side
- bila fokus tahap awal hanya Excel + Print, PDF bisa ditunda

### 2. Helper export global

Buat satu helper JS global untuk inisialisasi DataTable export.

Target implementasi:
- helper inline di layout tahap awal, lalu bila stabil bisa dipindah ke asset JS khusus
- nama yang disarankan: `window.initAdminExportTable()`

Tanggung jawab helper:
- merge default config + config spesifik tabel
- generate tombol `excelHtml5`, `print`, `colvis`
- fallback aman jika plugin tertentu tidak tersedia
- standardisasi `exportOptions.columns = ':visible:not(.no-export)'`
- standardisasi title, filename, orientation, dan class button
- inject header print dengan format yang sama
- apply style print dasar agar konsisten

### 3. Config per tabel

Setiap tabel tidak lagi menyimpan script export panjang, tetapi hanya mendefinisikan config kecil.

Contoh struktur config:

```js
window.initAdminExportTable('#tabelKompetisiTanding', {
  title: 'Daftar Pool Tanding',
  filename: 'Daftar Pool Tanding',
  orientation: 'landscape',
  preset: 'wide-report',
  excludeColumns: '.no-export',
  printHeader: {
    title: 'Daftar Pool Tanding',
    subtitle: 'Nama Event'
  },
  excel: {
    columnWidths: {
      A: 8,
      B: 24,
      C: 14
    }
  }
});
```

Artinya:
- perbedaan kolom tetap ditangani masing-masing tabel
- tetapi engine export tetap satu pintu

### 4. Preset style export

Agar scalable, sediakan preset output yang bisa dipakai ulang.

Preset yang disarankan:
- `simple-list`
- `wide-report`
- `summary-table`

Isi preset bisa mencakup:
- ukuran font print
- padding tabel
- zebra striping
- alignment default
- orientasi default
- lebar tabel di print preview

Dengan cara ini, tabel tidak perlu mendefinisikan semuanya dari nol.

### 5. Partial/header print CI4

Header print jangan dirakit inline dengan potongan view CI3.

Target file baru yang disarankan:
- `app/Views/shared_components/print/export_header.php`

Isi partial:
- title
- subtitle / nama event
- tanggal cetak
- identitas aplikasi bila diperlukan

Lalu helper export global akan mengonsumsi HTML header ini dalam bentuk string yang aman untuk print.

## Tahap Implementasi

## Tahap 1 — Fondasi bersama ✅ DONE

### Tujuan
Menyiapkan dasar export yang reusable tanpa langsung menyentuh semua tabel.

### File target
- `app/Views/layouts/admin.php` ✅
- `app/Views/layouts/kontingen.php` ✅

### Pekerjaan
1. ✅ Tambahkan asset Buttons di layout yang relevan.
2. ✅ Helper global `initAdminExportTable()` diextract ke `public/assets/js/admin-export-datatable.js`.
3. ✅ Default config export bersama sudah ada.
4. ✅ Tabel biasa tetap bisa memakai `initAdminDataTable()` / `initKontingenDataTable()` tanpa export.
5. ✅ Helper export tidak merusak halaman yang tidak memakai Buttons.

### Output tahap 1
- ✅ Layout siap mendukung export secara global
- ✅ Tidak perlu lagi inject asset Buttons di tiap view baru
- ✅ Tidak ada duplikasi helper di dua layout

## Tahap 2 — Bangun contract config per tabel ✅ DONE

### Tujuan
Mendefinisikan format config yang sederhana dan stabil.

### Aturan
- ✅ semua tabel wajib pakai `.no-export` untuk kolom aksi
- ✅ semua judul dan filename ditetapkan dari config
- ✅ custom berat tidak boleh langsung disimpan sebagai script besar di view jika masih bisa dijadikan opsi config

### Output tahap 2
- ✅ ada standar baku untuk migrasi tabel lain

## Tahap 3 — Pilot migration ✅ DONE

Lakukan pada dua tabel yang saat ini paling jelas dan paling banyak indikasi legacy.

### Pilot 1 ✅
Target file:
- `app/Views/shared_components/kontingen/tabel_cetak_id_card.php`

Pekerjaan:
- ✅ hapus inject asset Buttons dari view
- ✅ ganti script DataTable lokal dengan config ke helper global
- ✅ ganti `get_instance()` untuk event name dengan data/helper CI4
- ✅ ganti `$this->load->view()` untuk header print ke pendekatan CI4
- ✅ pertahankan hasil print/excel semirip mungkin dengan CI3

### Pilot 2 ✅
Target file:
- `app/Views/shared_components/kompetisi_tanding/tabel.php`

Pekerjaan:
- pecah kebutuhan export inti dari kebutuhan plugin tambahan
- aktifkan dulu `excelHtml5`, `print`, `colvis`
- evaluasi apakah `searchPanes`, `searchBuilder`, `pdfHtml5` tetap dipakai atau ditunda
- ganti semua pola legacy CI3 ke pola CI4
- pertahankan urutan kolom export yang sama dengan tampilan yang diinginkan

### Output tahap 3
- dua tabel reference implementation untuk modul lain
- pola migrasi bisa dicopy sebagai standar

## Tahap 4 — Parity pass terhadap CI3

### Tujuan
Memastikan hasil export cukup mirip dengan CI3 pada level output, bukan pada level kode.

### Checklist parity per tabel
1. tombol yang muncul sama atau setara
2. kolom yang ikut export sama
3. kolom aksi tidak ikut export
4. urutan kolom sama
5. judul file dan judul print sama/serupa
6. header print sama secara visual utama
7. alignment penting sama
8. angka dan teks panjang tetap terbaca
9. mobile view tidak rusak

### Metode kerja
- ambil satu tabel CI3 sebagai referensi visual
- cocokkan hasil export CI4
- jika perlu, tambahkan override kecil pada config tabel, bukan mengubah helper global terlalu spesifik

## Tahap 5 — Rollout bertahap ke tabel lain 🔄 IN PROGRESS

Setelah dua pilot stabil, lanjutkan ke modul lain dengan pola yang sama.

Prioritas yang disarankan:
1. sekretariat ✅
   - ✅ peserta tanding (sudah dari awal)
   - ✅ pool tanding (sudah dari awal)
   - ✅ pool seni (sudah dari awal)
   - ✅ pendaftar — `admin-datatable-export` auto-init (26 Mei 2026)
   - ✅ kontingen — `admin-datatable-export` auto-init (26 Mei 2026)
   - ✅ kelas tanding — `admin-datatable-export` + `no-export` (26 Mei 2026)
   - ✅ kategori seni — `admin-datatable-export` + `no-export` (26 Mei 2026)
   - ✅ kelompok seni — `admin-datatable-export` + `no-export` (26 Mei 2026)
   - ✅ data bpjs — `admin-datatable-export` (26 Mei 2026)
   - ✅ pertandingan tanding — `admin-datatable-export` + `no-export` (26 Mei 2026)
   - ✅ battle seni — `admin-datatable-export` + `no-export` (26 Mei 2026)
   - ✅ sistem pool seni — `admin-datatable-export` (26 Mei 2026)
   - ✅ kuota prestasi tanding — `admin-datatable-export` (26 Mei 2026)
   - ✅ kuota prestasi seni — `admin-datatable-export` (26 Mei 2026)
   - ✅ statistik tanding/seni — `admin-datatable-export` (26 Mei 2026)
   - ✅ medal tally — `admin-datatable-export` (26 Mei 2026)
   - ✅ pengadaan medali — `admin-datatable-export` (26 Mei 2026)
   - ✅ nomor sertifikat — `admin-datatable-export` (26 Mei 2026)
   - ✅ show pages (pool_tanding, pool_seni, kategori_seni, kelas_tanding, kontingen, jadwal_tanding) — `admin-datatable-export` + `no-export` (26 Mei 2026)
2. bendahara
   - daftar pembayaran
   - rincian status pembayaran
3. kontingen ✅
   - ✅ `kontingen/tabel.php` (Rekap Kontingen) — dimigrasi 26 Mei 2026
   - ✅ `kontingen/tabel_detail.php` (Detail Kontingen) — dimigrasi 26 Mei 2026

## Struktur File yang Disarankan

### Existing files yang diubah
- ✅ `app/Views/layouts/admin.php` — helper diextract ke shared JS
- ✅ `app/Views/layouts/kontingen.php` — helper diextract ke shared JS
- ✅ `app/Views/shared_components/kontingen/tabel_cetak_id_card.php`
- ✅ `app/Views/shared_components/kompetisi_tanding/tabel.php`
- ✅ `app/Views/shared_components/kontingen/tabel.php`
- ✅ `app/Views/shared_components/kontingen/tabel_detail.php`

### New files created
- ✅ `app/Views/shared_components/print/export_header.php`
- ✅ `public/assets/js/admin-export-datatable.js` — shared export helper (auto-detect admin/kontingen context)

## Aturan Desain Agar Scalable

1. View hanya berisi HTML tabel + config ringan.
2. Tidak ada inject asset export di masing-masing view.
3. Tidak ada `get_instance()` atau `$this->load->view()` untuk kebutuhan export di view CI4.
4. Custom per tabel disimpan sebagai config, bukan copy script penuh.
5. Helper global hanya berisi logic reusable, bukan logic spesifik satu tabel.
6. Jika satu tabel benar-benar unik, buat opsi override kecil daripada fork total.

## Risiko dan Mitigasi

### Risiko 1
Hasil export CI4 tidak cukup mirip dengan CI3.

Mitigasi:
- fokus parity di dua tabel pilot dulu
- gunakan preset + override kecil per tabel
- verifikasi langsung hasil Excel dan print

### Risiko 2
Plugin tambahan seperti `searchPanes` atau `pdfHtml5` membuat halaman rapuh.

Mitigasi:
- tahap awal fokus ke `excelHtml5`, `print`, `colvis`
- aktifkan plugin tambahan hanya setelah fondasi stabil

### Risiko 3
Refactor terlalu besar sekaligus.

Mitigasi:
- lakukan bertahap
- jangan ubah semua view sekaligus
- gunakan pilot implementation sebelum rollout luas

## Acceptance Criteria

Planning ini dianggap berhasil diterapkan jika:

1. asset Buttons dimuat global, bukan per-view
2. minimal dua tabel pilot memakai helper export global
3. output Excel dan Print dari pilot mendekati hasil CI3
4. view pilot tidak lagi berisi script export panjang inline
5. pola config per tabel cukup jelas untuk dipakai tabel lain
6. tidak ada regresi pada DataTable non-export

## Manual QA yang Disarankan

Untuk setiap tabel pilot:

1. buka halaman desktop
2. pastikan tabel load tanpa console error
3. cek tombol export muncul
4. klik Excel dan cek file terdownload dengan nama benar
5. cek urutan kolom hasil export
6. cek kolom `.no-export` tidak ikut
7. klik Print dan cek header/title
8. cek wrapping teks dan alignment
9. cek tampilan mobile tidak rusak

## Rekomendasi Eksekusi Nyata

Urutan paling aman:

1. kerjakan fondasi layout + helper global
2. migrasikan `tabel_cetak_id_card.php`
3. migrasikan `kompetisi_tanding/tabel.php`
4. bandingkan hasil dengan CI3
5. jika sudah stabil, baru rollout ke tabel lain

## Kesimpulan

Pendekatan paling aman bukan menyamakan semua tabel secara paksa, tetapi membuat satu fondasi export yang konsisten dan fleksibel. Dengan begitu, setiap tabel tetap bisa punya kolom dan kebutuhan sendiri, tetapi hasil export tetap terasa satu sistem, lebih dekat ke CI3, dan jauh lebih mudah dikembangkan ke depan.
