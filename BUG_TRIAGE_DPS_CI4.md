# Bug Triage DPS CI4

Dokumen ini merangkum bug, gap perilaku, dan risiko teknis yang perlu diuji atau ditindaklanjuti setelah migrasi bertahap dari project `dps` (CI3) ke `dps-ci4` (CI4).

Status dokumen ini adalah living document. Setiap bug yang ditemukan saat QA sebaiknya ditambahkan dengan status terbaru.

## Legend Status

- `open`: belum dikerjakan
- `watch`: belum tentu bug, perlu validasi saat QA
- `partial`: fondasi sudah ada, masih perlu penyempurnaan
- `resolved`: sudah diperbaiki dan perlu verifikasi QA

## Ringkasan Area

Area yang sudah memiliki fondasi migrasi:

1. Landing page public
2. Registrasi kontingen
3. Login kontingen
4. Dashboard kontingen
5. Modul peserta
6. Modul kategori tanding
7. Modul kategori seni
8. Modul pembayaran

## Triage Bug dan Gap

### 1. Landing Page

#### BUG-001 - Fallback asset event belum final
- Status: `watch`
- Area: Landing public
- Risiko: jika `event_logo` atau `poster` kosong di database, beberapa area visual bisa tampil kosong atau kurang baik.
- Gejala yang perlu dicek:
  - logo brand kosong di navbar
  - poster hero kosong
  - favicon kosong
- Tindak lanjut yang disarankan:
  - tambahkan fallback image lokal yang eksplisit
  - tambah pengecekan visual saat QA

#### BUG-002 - Countdown bergantung pada format config lama
- Status: `watch`
- Area: Landing public
- Risiko: jika nilai `countdown` di config/database tidak valid untuk `new Date(...)`, countdown bisa gagal diam-diam.
- Gejala yang perlu dicek:
  - countdown tidak bergerak
  - muncul `NaN`
- Tindak lanjut yang disarankan:
  - validasi format tanggal countdown
  - fallback message jika parsing gagal

#### BUG-003 - Footer landing perlu verifikasi visual akhir
- Status: `partial`
- Area: Landing public
- Risiko: secara teknis sudah dirapikan, tapi perlu dicek ulang apakah spacing, warna, dan alignment sudah benar di semua device.
- Tindak lanjut yang disarankan:
  - QA desktop/mobile portrait/mobile landscape

### 2. Registrasi Kontingen

#### BUG-010 - Loader wilayah perlu validasi dataset lengkap
- Status: `watch`
- Area: Registrasi kontingen
- Risiko: asset JSON wilayah mungkin tidak lengkap untuk semua level daerah.
- Gejala yang perlu dicek:
  - dropdown kabupaten/kecamatan/kelurahan tidak terisi untuk wilayah tertentu
  - state dropdown tidak reset dengan benar saat parent berubah
- Tindak lanjut yang disarankan:
  - tes beberapa provinsi berbeda
  - log bila file JSON tidak ditemukan

#### BUG-011 - Registrasi kontingen belum memakai reCAPTCHA seperti CI3
- Status: `open`
- Area: Registrasi kontingen
- Risiko: flow CI3 lama memiliki validasi reCAPTCHA untuk public registration, sedangkan flow CI4 saat ini belum mem-port perlindungan tersebut.
- Dampak:
  - proteksi bot lebih rendah dibanding project lama
- Tindak lanjut yang disarankan:
  - port konfigurasi reCAPTCHA ke CI4
  - pasang validasi server-side

#### BUG-012 - Validasi field registrasi belum 100% parity dengan CI3
- Status: `partial`
- Area: Registrasi kontingen
- Risiko: form sudah aktif, tetapi parity penuh terhadap perilaku CI3 lama masih perlu diuji untuk edge case tertentu.
- Contoh:
  - format nomor telepon internasional/lokal
  - perilaku saat email sudah terdaftar
  - field wilayah optional/required berdasarkan jenis kontingen

### 3. Login dan Session Kontingen

#### BUG-020 - Session kontingen perlu diuji lintas flow penuh
- Status: `watch`
- Area: Auth kontingen
- Risiko: login dasar sudah aktif, tapi masih perlu diuji pada alur panjang seperti tambah peserta, pilih kategori, dan pembayaran dalam satu sesi.
- Tindak lanjut yang disarankan:
  - uji session persistence
  - uji logout dan login ulang

### 4. Dashboard Kontingen

#### BUG-030 - Dashboard summary perlu validasi data real
- Status: `watch`
- Area: Dashboard kontingen
- Risiko: summary sudah disesuaikan dengan schema lama, tetapi tetap perlu dicocokkan dengan data nyata di database aktif.
- Yang perlu dicek:
  - jumlah atlet
  - jumlah kategori tanding
  - jumlah kategori seni
  - jumlah tagihan aktif
  - total transaksi

### 5. Modul Peserta

#### BUG-040 - Upload arsip peserta existing perlu verifikasi browser flow
- Status: `partial`
- Area: Peserta
- Risiko: fondasi create/update arsip sudah ada, tapi perilaku update file existing perlu dibuktikan langsung lewat browser.
- Yang perlu dicek:
  - file existing muncul pada modal edit
  - file baru menggantikan file lama
  - record `arsip_pendaftar` ter-update benar
  - file lama benar-benar terhapus dari disk

#### BUG-041 - Slot arsip aktif/wajib dari DB perlu validasi struktur data nyata
- Status: `watch`
- Area: Peserta
- Risiko: helper CI4 sudah mengikuti konsep lama, tetapi kalau JSON `arsip_pendaftar_slots` di `site_builder_settings` tidak konsisten, render/validasi bisa bermasalah.
- Yang perlu dicek:
  - setiap slot punya `nama_arsip`
  - `allowed_types` valid
  - `max_size` valid
  - `required` dan `active` benar

#### BUG-042 - Validasi numerik peserta perlu uji edge case
- Status: `resolved`
- Area: Peserta
- Catatan:
  - NIK dan KK sudah dipersempit menjadi `numeric` + `exact_length[16]`
  - tinggi dan berat sudah `numeric` + `greater_than[0]`
- Yang masih perlu dicek:
  - input copy-paste non-digit
  - input kosong pada edit
  - form modal pada mobile

### 6. Modul Kategori Tanding

#### BUG-050 - Filter kategori tanding belum parity penuh dengan model CI3
- Status: `partial`
- Area: Tanding
- Risiko: implementasi CI4 sudah mempertimbangkan gender, umur, berat, kuota, dan atlet kontingen yang sama, tetapi belum sekompleks logic CI3 lama di semua edge case.
- Yang perlu dicek:
  - atlet di batas umur minimum/maksimum
  - atlet di batas berat minimum/maksimum
  - kuota pool penuh
  - kelas yang seharusnya disabled

#### BUG-051 - Edit kategori tanding perlu uji saat sudah terikat pembayaran
- Status: `watch`
- Area: Tanding
- Risiko: delete sudah dibatasi bila `id_pembayaran` terisi, tapi perubahan kategori setelah terikat pembayaran tetap perlu divalidasi terhadap business rule produksi.

### 7. Modul Kategori Seni

#### BUG-060 - Validasi jumlah anggota kategori seni perlu QA khusus
- Status: `partial`
- Area: Seni
- Risiko: logic create sudah menerapkan jumlah anggota berdasar jenis seni, tetapi perlu divalidasi dengan data kategori nyata di database.
- Yang perlu dicek:
  - tunggal
  - ganda
  - beregu
  - solo kreatif
  - kategori lain jika ada

#### BUG-061 - Tabel seni menampilkan tinggi/berat sebagai agregat string
- Status: `watch`
- Area: Seni
- Risiko: secara UI sudah memenuhi permintaan kolom, tapi format agregasi berat/tinggi anggota perlu dievaluasi apakah nyaman dibaca user.
- Tindak lanjut yang disarankan:
  - tentukan apakah format dipertahankan comma-separated atau diubah multiline

### 8. Modul Pembayaran

#### BUG-070 - Upload bukti pembayaran perlu verifikasi permission folder di environment nyata
- Status: `watch`
- Area: Pembayaran
- Risiko: modul pembayaran menyimpan file ke `public/uploads/bukti-pembayaran`, sehingga permission folder di environment lokal/produksi perlu dipastikan benar.
- Yang perlu dicek:
  - upload berhasil
  - file tersimpan
  - file bisa dibuka dari detail transaksi

#### BUG-071 - Daftar transaksi menunggu/lunas baru sisi kontingen
- Status: `partial`
- Area: Pembayaran
- Risiko: saat ini fokus hanya sisi kontingen. Approval dari admin/bendahara belum dimigrasikan penuh.
- Dampak:
  - transaksi bisa dibuat
  - status lunas/menunggu harus dibantu oleh data existing atau intervensi admin lama

#### BUG-072 - Total checkout perlu uji kombinasi item real
- Status: `watch`
- Area: Pembayaran
- Risiko: total di frontend sudah dihitung dari checkbox terpilih, tapi perlu dicocokkan dengan transaksi yang benar-benar tersimpan di DB untuk kombinasi tanding+seni.

### 9. Responsive dan UI/UX

#### BUG-080 - Responsive perlu divalidasi di browser nyata
- Status: `watch`
- Area: Semua halaman kontingen dan landing
- Risiko: CSS hardening sudah dilakukan, tetapi tetap perlu diuji manual di:
  - desktop
  - tablet portrait
  - mobile portrait
  - mobile landscape

#### BUG-081 - DataTables perlu QA pada layar kecil
- Status: `watch`
- Area: Peserta, tanding, seni, pembayaran
- Risiko: walau DataTables sudah terpasang dan styling dasar sudah ada, pengalaman di landscape mobile masih perlu divalidasi.

## Prioritas QA Selanjutnya

Urutan test yang paling disarankan:

1. Registrasi kontingen
2. Login kontingen
3. Tambah peserta + upload arsip
4. Edit peserta + ganti arsip
5. Tambah kategori tanding
6. Tambah kategori seni
7. Buat transaksi pembayaran
8. Buka menunggu konfirmasi
9. Buka rincian transaksi

## Format Pelaporan Bug yang Disarankan

Saat QA menemukan bug, gunakan format berikut:

```md
### BUG-NEW
- Halaman: /kontingen/peserta
- Device: mobile portrait
- Langkah:
  1. Klik tambah peserta
  2. Isi data
  3. Pindah ke tab arsip
  4. Upload file
  5. Submit
- Hasil aktual: modal tertutup tapi data tidak masuk
- Hasil yang diharapkan: data peserta dan arsip tersimpan
- Screenshot / log: ...
```
