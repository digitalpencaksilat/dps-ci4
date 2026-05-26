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

Status saat ini: `Needs Review`

Secara umum, fondasi tampilan aplikasi sudah terbentuk dan alur utama sudah tersedia. Namun, masih ada beberapa masalah UI/UX yang berdampak langsung ke kejelasan alur, penggunaan di mobile, aksesibilitas, dan konsistensi antar modul.

Masalah dengan prioritas tertinggi ada pada:

- pesan halaman pendaftaran yang bertentangan dengan kondisi form aktif
- select wilayah yang gagal tanpa feedback
- navigasi kontingen di mobile yang kurang efisien
- tabel kontingen yang sulit dipakai di layar kecil
- submit pembayaran yang masih bisa dilakukan tanpa item terpilih
- beberapa kelemahan aksesibilitas dan sanitasi render data frontend

## Tabel Hasil Review

| No | Prioritas | Area | Temuan | Dampak | Referensi File |
|---|---|---|---|---|---|
| 1 | Tinggi | Pendaftaran publik | Teks pembuka menyatakan fitur masih disiapkan untuk migrasi, tetapi form aktif dan bisa dipakai. | User bingung dan bisa ragu melanjutkan pendaftaran. | `app/Views/pendaftaran/pages/registrasi.php:7-9`, `:19-111` |
| 2 | Tinggi | Pendaftaran publik | Select wilayah bergantung API, tetapi saat gagal tidak ada loading state, error message, retry, atau blok submit. | User tidak tahu penyebab field wilayah kosong. | `app/Views/pendaftaran/pages/registrasi.php:144-153`, `:168-173` |
| 3 | Tinggi | Aksesibilitas form | Banyak label belum terhubung ke input melalui `for` dan `id`. | Screen reader terganggu, klik label tidak fokus ke field. | `app/Views/pendaftaran/pages/registrasi.php`, `app/Views/kontingen/peserta/index.php`, `app/Views/kontingen/tanding/index.php`, `app/Views/kontingen/seni/index.php`, `app/Views/admin/sekretariat/kontingen/_form.php` |
| 4 | Tinggi | Kontingen mobile | Sidebar kontingen tampil penuh di atas konten pada mobile dan belum punya toggle/collapse. | User HP harus scroll menu dulu sebelum melihat isi halaman. | `app/Views/layouts/kontingen.php:23-80`, `public/assets/css/kontingen-theme.css:672-682` |
| 5 | Tinggi | Tabel kontingen | Tabel dipaksa `nowrap`, sementara layout menyembunyikan overflow horizontal. | Tabel sempit, aksi bisa terdorong keluar layar. | `public/assets/css/kontingen-theme.css:23`, `:790-793`, view tabel kontingen terkait |
| 6 | Tinggi | Pembayaran | Tombol submit pembayaran tetap aktif walau tidak ada item dipilih dan total masih `Rp 0`. | Rawan submit tidak valid dan membingungkan user. | `app/Views/kontingen/pembayaran/index.php:73-85`, `:142-148` |
| 7 | Tinggi | Keamanan frontend | Data atlet seni dimasukkan ke `innerHTML` langsung dari response API. | Risiko tampilan rusak atau XSS bila data tidak aman. | `app/Views/kontingen/seni/index.php:200-209` |
| 8 | Sedang | Aksi tabel | Tombol dropdown aksi hanya menampilkan ikon tanpa `aria-label`. | Screen reader tidak bisa menjelaskan fungsi tombol. | `app/Views/kontingen/peserta/index.php:58-60`, `app/Views/kontingen/tanding/index.php:59-61`, `app/Views/kontingen/seni/index.php:59-61` |
| 9 | Sedang | Modal | Label tombol tutup modal tidak konsisten antara bahasa Inggris dan Indonesia. | Konsistensi antarmuka menurun. | `app/Views/kontingen/peserta/index.php:118`, `app/Views/kontingen/tanding/index.php:107`, `app/Views/kontingen/seni/index.php:106,153`, `app/Views/admin/sekretariat/kontingen/index.php:78` |
| 10 | Sedang | Pembayaran | Empty state pembayaran mencampur beberapa kondisi dalam satu pesan. | User sulit memahami status tagihan sebenarnya. | `app/Views/kontingen/pembayaran/index.php:27-28` |
| 11 | Sedang | Upload pembayaran | Batas file ditulis `10 MB`, tetapi validasi JS menampilkan `10240 KB`. | Tidak fatal, tetapi copy terasa tidak konsisten. | `app/Views/kontingen/pembayaran/index.php:76-77`, `:167-170` |
| 12 | Sedang | Form seni | Informasi jumlah atlet hanya berupa helper text, belum ada hitung live dan guard submit. | User bisa salah jumlah pilih atlet. | `app/Views/kontingen/seni/index.php:213-215` |
| 13 | Sedang | Form tanding | Daftar atlet tersedia dan atlet yang sudah terdaftar bercampur dalam satu select. | User berisiko bingung atau salah pilih. | `app/Views/kontingen/tanding/index.php:115-120` |
| 14 | Sedang | Navigasi admin | Label menu `Raw Tanding` dan `Raw Seni` terlalu teknis untuk operator. | Istilah terasa seperti menu internal developer. | `app/Views/layouts/admin.php:211-212` |
| 15 | Sedang | Konsistensi visual | Admin memakai font Inter, area publik dan kontingen memakai Poppins. | Perpindahan antar area terasa tidak seragam. | `app/Views/layouts/admin.php:11`, `app/Views/layouts/kontingen.php:11`, `app/Views/pendaftaran/template.php:14` |
| 16 | Sedang | Aksesibilitas keyboard | Focus state untuk elemen interaktif belum terlihat kuat atau belum ada `:focus-visible` yang jelas. | User keyboard sulit melacak posisi fokus. | `public/assets/css/admin/admin.css`, `public/assets/css/kontingen-theme.css` |
| 17 | Rendah | Sidebar admin mobile | Sidebar admin mobile sudah cukup baik, tetapi fokus kemungkinan belum terkunci saat panel terbuka. | User keyboard bisa tab ke elemen belakang panel. | `public/assets/css/admin/admin.css:846-865` |
| 18 | Rendah | Form admin kontingen | Input lokasi di admin masih free text, berbeda dari form publik yang bertingkat. | Data lokasi rawan tidak konsisten untuk filter dan laporan. | `app/Views/admin/sekretariat/kontingen/_form.php:58-76` |

## Ringkasan Prioritas

| Prioritas | Jumlah Temuan | Fokus |
|---|---|---|
| Tinggi | 7 | Kejelasan alur utama, mobile usability, validasi aksi penting, keamanan render frontend |
| Sedang | 9 | Aksesibilitas, konsistensi istilah, kualitas interaksi form dan navigasi |
| Rendah | 2 | Penyempurnaan keyboard flow dan standarisasi input admin |

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

- perbaiki copy halaman pendaftaran
- tambah feedback gagal pada select wilayah
- disable submit pembayaran saat belum ada item dipilih
- ganti `innerHTML` dengan render aman berbasis DOM API atau `textContent`

### Sprint 2

- buat navigasi kontingen mobile menjadi collapsible atau off-canvas
- rapikan tabel kontingen untuk viewport kecil
- tambah `id` dan `for` pada semua field utama
- tambah `aria-label` pada tombol aksi ikon

### Sprint 3

- rapikan empty state pembayaran
- seragamkan label bahasa Indonesia pada modal dan menu
- tambah `:focus-visible` pada elemen interaktif
- evaluasi penyatuan font lintas area bila memang ingin satu identitas visual

## Checklist Acceptance UI/UX

Gunakan checklist ini setelah perbaikan dilakukan.

1. Halaman pendaftaran tidak menampilkan pesan yang bertentangan dengan status form.
2. Semua select wilayah menampilkan loading, error, atau state kosong yang jelas.
3. User mobile bisa membuka halaman kontingen tanpa harus scroll menu panjang lebih dulu.
4. Tabel kontingen tetap bisa dibaca dan dioperasikan di layar kecil.
5. Submit pembayaran tidak bisa dilakukan bila belum ada item dipilih.
6. Semua label form utama terhubung ke input dengan `for` dan `id`.
7. Tombol ikon punya nama aksesibel yang jelas.
8. Focus keyboard terlihat jelas di nav, tombol, link, dropdown, dan card interaktif.
9. Pesan empty state membedakan kondisi tidak ada data, sudah diproses, dan sudah dibayar.
10. Render data dinamis frontend tidak memakai `innerHTML` untuk data user/API tanpa sanitasi aman.

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
