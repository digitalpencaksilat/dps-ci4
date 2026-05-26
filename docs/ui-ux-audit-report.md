# Laporan Audit UI/UX

## Tujuan

Dokumen ini menjadi acuan tim untuk menindaklanjuti hasil review UI/UX pada project `dps-ci4`.

Fokus audit:

- alur pendaftaran publik
- area kontingen
- area admin
- responsivitas mobile
- aksesibilitas dasar
- konsistensi istilah dan tampilan

Tanggal audit: 2026-05-26  
Project: `dps-ci4`

## Ringkasan Eksekutif

Status saat ini: `In Progress`

Secara umum, fondasi tampilan aplikasi sudah terbentuk dan alur utama sudah tersedia. Namun, masih ada beberapa masalah UI/UX yang berdampak langsung ke kejelasan alur, penggunaan di mobile, aksesibilitas, dan konsistensi antar modul.

Masalah dengan prioritas tertinggi saat audit awal ada pada:

- pesan halaman pendaftaran yang bertentangan dengan kondisi form aktif
- select wilayah yang gagal tanpa feedback
- navigasi kontingen di mobile yang kurang efisien
- tabel kontingen yang sulit dipakai di layar kecil
- submit pembayaran yang masih bisa dilakukan tanpa item terpilih
- beberapa kelemahan aksesibilitas dan sanitasi render data frontend

Progress tindak lanjut per 2026-05-26:

- selesai: 10 temuan
- belum selesai: 8 temuan
- fokus berikutnya: responsivitas tabel kontingen, `aria-label` tombol aksi, copy upload pembayaran, dan form tanding

## Tabel Hasil Review dan Status

| No | Cek | Status | Prioritas | Area | Temuan | Referensi File | Catatan Tindak Lanjut |
|---|---|---|---|---|---|---|---|
| 1 | [x] | Selesai | Tinggi | Pendaftaran publik | Teks pembuka menyatakan fitur masih disiapkan untuk migrasi, tetapi form aktif dan bisa dipakai. | `app/Views/pendaftaran/pages/registrasi.php:7-9`, `:19-111` | Copy sudah disesuaikan menjadi instruksi pendaftaran aktif. |
| 2 | [x] | Selesai | Tinggi | Pendaftaran publik | Select wilayah bergantung API, tetapi saat gagal tidak ada loading state, error message, retry, atau blok submit. | `app/Views/pendaftaran/pages/registrasi.php:144-153`, `:168-173` | Sudah ada loading, error inline, toastr, required wilayah dalam negeri, dan blok submit saat wilayah gagal/tidak lengkap. |
| 3 | [x] | Selesai | Tinggi | Aksesibilitas form | Banyak label belum terhubung ke input melalui `for` dan `id`. | `app/Views/pendaftaran/pages/registrasi.php`, `app/Views/kontingen/peserta/index.php`, `app/Views/kontingen/tanding/index.php`, `app/Views/kontingen/seni/index.php`, `app/Views/admin/sekretariat/kontingen/_form.php` | Label dan field utama sudah dipasangkan dengan `for`/`id`; grup checkbox seni diberi `aria-labelledby`. |
| 4 | [x] | Selesai | Tinggi | Kontingen mobile | Sidebar kontingen tampil penuh di atas konten pada mobile dan belum punya toggle/collapse. | `app/Views/layouts/kontingen.php:23-80`, `public/assets/css/kontingen-theme.css:672-682` | Sidebar mobile kontingen sudah mengikuti model admin: overlay, panel rounded, tombol buka/tutup, Escape close. |
| 5 | [ ] | Belum | Tinggi | Tabel kontingen | Tabel dipaksa `nowrap`, sementara layout menyembunyikan overflow horizontal. | `public/assets/css/kontingen-theme.css:23`, `:790-793`, view tabel kontingen terkait | Belum dikerjakan. Perlu desain responsif tabel/card atau prioritas kolom. |
| 6 | [x] | Selesai | Tinggi | Pembayaran | Tombol submit pembayaran tetap aktif walau tidak ada item dipilih dan total masih `Rp 0`. | `app/Views/kontingen/pembayaran/index.php:73-85`, `:142-148` | Tombol submit sudah disabled saat tidak ada checkbox terpilih. |
| 7 | [x] | Selesai | Tinggi | Keamanan frontend | Data atlet seni dimasukkan ke `innerHTML` langsung dari response API. | `app/Views/kontingen/seni/index.php:200-209` | Render data atlet sudah diganti ke DOM API dan `textContent`; `innerHTML` tersisa hanya untuk mengosongkan container. |
| 8 | [ ] | Belum | Sedang | Aksi tabel | Tombol dropdown aksi hanya menampilkan ikon tanpa `aria-label`. | `app/Views/kontingen/peserta/index.php:58-60`, `app/Views/kontingen/tanding/index.php:59-61`, `app/Views/kontingen/seni/index.php:59-61` | Belum dikerjakan. Tambahkan `aria-label` kontekstual per baris. |
| 9 | [x] | Selesai | Sedang | Modal | Label tombol tutup modal tidak konsisten antara bahasa Inggris dan Indonesia. | `app/Views/kontingen/peserta/index.php:118`, `app/Views/kontingen/tanding/index.php:107`, `app/Views/kontingen/seni/index.php:106,153`, `app/Views/admin/sekretariat/kontingen/index.php:78` | Modal kontingen sudah memakai `aria-label="Tutup"`; admin sudah sesuai. |
| 10 | [x] | Selesai | Sedang | Pembayaran | Empty state pembayaran mencampur beberapa kondisi dalam satu pesan. | `app/Views/kontingen/pembayaran/index.php:27-28` | Empty state sudah dibedakan: belum ada tagihan, tagihan sedang diproses, dan semua pembayaran lunas. |
| 11 | [ ] | Belum | Sedang | Upload pembayaran | Batas file ditulis `10 MB`, tetapi validasi JS menampilkan `10240 KB`. | `app/Views/kontingen/pembayaran/index.php:76-77`, `:167-170` | Belum dikerjakan. Pesan validasi perlu menampilkan `10 MB` atau `10 MB (10240 KB)`. |
| 12 | [x] | Selesai | Sedang | Form seni | Informasi jumlah atlet hanya berupa helper text, belum ada hitung live dan guard submit. | `app/Views/kontingen/seni/index.php:213-215` | Sudah ada counter live, validasi strict/minimal, submit disabled, dan guard submit. |
| 13 | [ ] | Belum | Sedang | Form tanding | Daftar atlet tersedia dan atlet yang sudah terdaftar bercampur dalam satu select. | `app/Views/kontingen/tanding/index.php:115-120` | Belum dikerjakan. Mode tambah perlu hanya tampilkan atlet tersedia; mode edit tetap mempertahankan atlet saat ini. |
| 14 | [ ] | Belum | Sedang | Navigasi admin | Label menu `Raw Tanding` dan `Raw Seni` terlalu teknis untuk operator. | `app/Views/layouts/admin.php:211-212` | Belum dikerjakan. Usulan: `Data Mentah Tanding` dan `Data Mentah Seni`, atau istilah operator yang disepakati. |
| 15 | [x] | Selesai | Sedang | Konsistensi visual | Admin memakai font Inter, area publik dan kontingen memakai Poppins. | `app/Views/layouts/admin.php:11`, `app/Views/layouts/kontingen.php:11`, `app/Views/pendaftaran/template.php:14` | Semua referensi font literal `Inter` sudah diganti ke `Poppins`; heading `Oswald` tetap dipertahankan. |
| 16 | [ ] | Belum | Sedang | Aksesibilitas keyboard | Focus state untuk elemen interaktif belum terlihat kuat atau belum ada `:focus-visible` yang jelas. | `public/assets/css/admin/admin.css`, `public/assets/css/kontingen-theme.css` | Belum dikerjakan. Perlu style `:focus-visible` untuk nav, tombol, dropdown, link, checkbox card. |
| 17 | [ ] | Belum | Rendah | Sidebar admin mobile | Sidebar admin mobile sudah cukup baik, tetapi fokus kemungkinan belum terkunci saat panel terbuka. | `public/assets/css/admin/admin.css:846-865` | Belum dikerjakan. Perlu focus management/focus trap saat sidebar admin terbuka. |
| 18 | [ ] | Belum | Rendah | Form admin kontingen | Input lokasi di admin masih free text, berbeda dari form publik yang bertingkat. | `app/Views/admin/sekretariat/kontingen/_form.php:58-76` | Belum dikerjakan. Perlu keputusan: pakai select wilayah seperti publik atau tetap free text dengan normalisasi. |

## Ringkasan Prioritas

| Prioritas | Jumlah Temuan | Fokus |
|---|---|---|
| Tinggi | 7 | Kejelasan alur utama, mobile usability, validasi aksi penting, keamanan render frontend |
| Sedang | 9 | Aksesibilitas, konsistensi istilah, kualitas interaksi form dan navigasi |
| Rendah | 2 | Penyempurnaan keyboard flow dan standarisasi input admin |

## Ringkasan Status

| Status | Jumlah | Nomor Temuan |
|---|---:|---|
| Selesai | 10 | 1, 2, 3, 4, 6, 7, 9, 10, 12, 15 |
| Belum | 8 | 5, 8, 11, 13, 14, 16, 17, 18 |

## Rekomendasi Urutan Perbaikan

| Urutan | Fokus | Alasan |
|---|---|---|
| 1 | Perbaiki copy halaman pendaftaran | Kontradiksi paling terlihat user pada alur publik |
| 2 | Tambah loading dan error state pada select wilayah | Masalah langsung menghambat pengisian form |
| 3 | Benahi navigasi kontingen mobile | Dampak ke hampir semua halaman kontingen |
| 4 | Perbaiki responsivitas tabel kontingen | Pengaruh besar pada penggunaan via HP |
| 5 | Cegah submit pembayaran tanpa item | Melindungi alur bisnis penting |
| 6 | Ganti render `innerHTML` di modul seni | Mengurangi risiko keamanan dan layout rusak |
| 7 | Rapikan `id`/`for`, `aria-label`, dan `:focus-visible` | Perbaikan aksesibilitas lintas modul |
| 8 | Rapikan empty state, istilah menu, dan konsistensi bahasa/font | Meningkatkan kualitas akhir antarmuka |

## Rekomendasi Tindak Lanjut per Sprint

### Sprint 1

- [x] perbaiki copy halaman pendaftaran
- [x] tambah feedback gagal pada select wilayah
- [x] disable submit pembayaran saat belum ada item dipilih
- [x] ganti `innerHTML` dengan render aman berbasis DOM API atau `textContent`

### Sprint 2

- [x] buat navigasi kontingen mobile menjadi collapsible atau off-canvas
- [ ] rapikan tabel kontingen untuk viewport kecil
- [x] tambah `id` dan `for` pada semua field utama
- [ ] tambah `aria-label` pada tombol aksi ikon

### Sprint 3

- [x] rapikan empty state pembayaran
- [ ] seragamkan label bahasa Indonesia pada modal dan menu
- [ ] tambah `:focus-visible` pada elemen interaktif
- [x] evaluasi penyatuan font lintas area bila memang ingin satu identitas visual

## Checklist Acceptance UI/UX

Gunakan checklist ini setelah perbaikan dilakukan.

1. [x] Halaman pendaftaran tidak menampilkan pesan yang bertentangan dengan status form.
2. [x] Semua select wilayah menampilkan loading, error, atau state kosong yang jelas.
3. [x] User mobile bisa membuka halaman kontingen tanpa harus scroll menu panjang lebih dulu.
4. [ ] Tabel kontingen tetap bisa dibaca dan dioperasikan di layar kecil.
5. [x] Submit pembayaran tidak bisa dilakukan bila belum ada item dipilih.
6. [x] Semua label form utama terhubung ke input dengan `for` dan `id`.
7. [ ] Tombol ikon punya nama aksesibel yang jelas.
8. [ ] Focus keyboard terlihat jelas di nav, tombol, link, dropdown, dan card interaktif.
9. [x] Pesan empty state membedakan kondisi tidak ada data, sudah diproses, dan sudah dibayar.
10. [x] Render data dinamis frontend tidak memakai `innerHTML` untuk data user/API tanpa sanitasi aman.

## Risiko Bila Tidak Ditindaklanjuti

### Risiko tinggi

1. User gagal atau ragu menyelesaikan pendaftaran.
2. Pengguna kontingen kesulitan mengoperasikan halaman di mobile.
3. Aksi pembayaran tidak valid bisa lolos dari sisi frontend.
4. Data dinamis berisiko merusak UI atau membuka celah XSS.

### Risiko menengah

1. Konsistensi UI antar modul makin jauh.
2. Aksesibilitas rendah untuk keyboard dan screen reader.
3. Istilah menu dan status membingungkan operator baru.

## Kesenjangan Verifikasi

Audit ini berbasis review kode dan belum mencakup:

- uji browser langsung desktop dan mobile
- uji keyboard-only
- uji screen reader
- verifikasi backend untuk submit pembayaran kosong
- verifikasi fallback API lokasi saat error jaringan

## Kesimpulan

Project ini sudah punya fondasi UI yang cukup untuk operasional, tetapi masih perlu perapihan pada area yang langsung memengaruhi kejelasan alur, kenyamanan mobile, aksesibilitas, dan keamanan render frontend. Dokumen ini sebaiknya dipakai sebagai acuan backlog UI/UX jangka dekat, dengan fokus awal pada temuan prioritas tinggi.
