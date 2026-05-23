# QA Checklist DPS CI4

Checklist QA ini digunakan untuk menguji hasil migrasi project `dps` (CI3) ke `dps-ci4` (CI4) secara sistematis.

Gunakan checklist ini per build, lalu tandai hasil:

- `[ ]` belum diuji
- `[x]` lolos
- `[!]` gagal / perlu follow-up

## Persiapan

Sebelum test:

- [ ] `php spark serve` berjalan di `http://localhost:8080`
- [ ] database aktif dan terhubung ke schema yang sama dengan project `dps`
- [ ] ada minimal satu akun kontingen valid untuk login
- [ ] folder upload bisa ditulis:
  - [ ] `public/uploads/peserta/arsip`
  - [ ] `public/uploads/bukti-pembayaran`

## A. Landing Page Public

URL: `/`

### Visual dan Struktur
- [ ] Hero tampil dengan background event
- [ ] Poster event tampil atau fallback tampil rapi
- [ ] Countdown tampil
- [ ] CTA `Daftar Sekarang` tampil
- [ ] CTA `Unduh Proposal` tampil
- [ ] Section `Informasi Event` tampil rapi
- [ ] Section `Mengapa Harus Ikut` tampil rapi
- [ ] Section `Kategori Pertandingan` tampil rapi
- [ ] Section `Timeline Kegiatan` tampil rapi
- [ ] Closing CTA tampil rapi
- [ ] Footer tampil dan teks rata tengah

### Interaksi
- [ ] Klik `Daftar Sekarang` menuju `/registrasi`
- [ ] Klik `Unduh Proposal` memberi respons sesuai file/route yang tersedia
- [ ] Scroll reveal effect bekerja di section

### Responsive
- [ ] Desktop lebar
- [ ] Tablet portrait
- [ ] Mobile portrait
- [ ] Mobile landscape

## B. Registrasi Kontingen

URL: `/registrasi`

### Visual dan Form
- [ ] Form registrasi tampil penuh
- [ ] Field dasar tampil lengkap
- [ ] Toggle jenis kontingen bekerja

### Validasi
- [ ] Submit kosong menampilkan error
- [ ] Email invalid ditolak
- [ ] Password dan konfirmasi password harus sama
- [ ] Nomor telepon PJ hanya menerima angka sesuai rule

### Loader Wilayah
- [ ] Mode `Dalam Negeri` menampilkan provinsi
- [ ] Memilih provinsi memuat kabupaten/kota
- [ ] Memilih kabupaten/kota memuat kecamatan
- [ ] Memilih kecamatan memuat kelurahan
- [ ] Mode `Luar Negeri` menggunakan negara

### Submit Sukses
- [ ] Registrasi dengan data valid berhasil
- [ ] Redirect ke login kontingen setelah sukses

### Responsive
- [ ] Desktop
- [ ] Mobile portrait
- [ ] Mobile landscape

## C. Login Kontingen

URL: `/pendaftaran/login`

### Validasi dan Session
- [ ] Login dengan kredensial salah menampilkan error
- [ ] Login dengan kredensial benar masuk ke dashboard
- [ ] Logout berhasil kembali ke halaman login

### Responsive
- [ ] Desktop
- [ ] Mobile portrait
- [ ] Mobile landscape

## D. Dashboard Kontingen

URL: `/kontingen/dashboard`

### Visual
- [ ] Sidebar merah sporty tampil baik
- [ ] Card statistik tampil seragam
- [ ] Footer versioning tampil di bawah
- [ ] Tidak ada elemen migrasi/dev yang mengganggu

### Navigasi
- [ ] Shortcut ke peserta aktif
- [ ] Shortcut ke tanding aktif
- [ ] Shortcut ke seni aktif
- [ ] Shortcut ke pembayaran aktif
- [ ] Submenu pembayaran terbuka dengan benar

### Data
- [ ] Jumlah atlet masuk akal
- [ ] Jumlah tanding masuk akal
- [ ] Jumlah seni masuk akal
- [ ] Jumlah tagihan aktif masuk akal

### Responsive
- [ ] Desktop
- [ ] Tablet portrait
- [ ] Mobile portrait
- [ ] Mobile landscape

## E. Modul Peserta

URL: `/kontingen/peserta`

### Tabel
- [ ] DataTables aktif
- [ ] Kolom tampil lengkap:
  - [ ] Nama peserta
  - [ ] Jenis kelamin
  - [ ] Tanggal lahir format Indonesia
  - [ ] Tempat lahir
  - [ ] Sekolah
  - [ ] Tinggi badan
  - [ ] Berat badan
  - [ ] NIK
  - [ ] Nomor Kartu Keluarga
  - [ ] Aksi

### Tambah Peserta
- [ ] Tombol `Tambah Peserta` membuka modal
- [ ] Modal memiliki 2 tab:
  - [ ] Data Peserta
  - [ ] Arsip Peserta
- [ ] Tab Data Peserta bisa diisi penuh
- [ ] Tab Arsip Peserta menampilkan slot aktif
- [ ] Label wajib/opsional tampil benar
- [ ] Allowed types tampil
- [ ] Max size tampil

### Validasi Peserta
- [ ] NIK hanya angka
- [ ] NIK tepat 16 digit
- [ ] KK hanya angka
- [ ] KK tepat 16 digit
- [ ] Tinggi badan hanya angka
- [ ] Berat badan hanya angka
- [ ] Sekolah boleh kosong

### Arsip Peserta
- [ ] Upload arsip wajib dipaksa bila slot required
- [ ] Upload tipe file tidak sesuai ditolak
- [ ] Upload file terlalu besar ditolak
- [ ] Submit sukses menyimpan peserta + arsip

### Edit Peserta
- [ ] Dropdown aksi tiga titik tampil
- [ ] `Edit` membuka modal
- [ ] Data peserta existing terisi di form
- [ ] Arsip existing tampil per slot
- [ ] Arsip bisa diganti

### Hapus Peserta
- [ ] `Hapus` tampil jika akses mengizinkan
- [ ] Konfirmasi memakai SweetAlert
- [ ] Hapus berhasil menghilangkan data dari tabel

### Responsive
- [ ] Desktop
- [ ] Mobile portrait
- [ ] Mobile landscape

## F. Modul Kategori Tanding

URL: `/kontingen/tanding`

### Tabel
- [ ] DataTables aktif
- [ ] Kolom tampil lengkap:
  - [ ] Nama atlet
  - [ ] Berat
  - [ ] Tinggi
  - [ ] Kategori usia
  - [ ] Jenis kelamin
  - [ ] Kelas tanding
  - [ ] Pembayaran
  - [ ] Aksi

### Tambah Kategori Tanding
- [ ] Tombol tambah membuka modal
- [ ] Dropdown peserta tampil
- [ ] Memilih peserta memuat kategori tanding otomatis
- [ ] Kategori yang disabled ditandai dengan benar
- [ ] Submit sukses menyimpan kategori tanding

### Edit Kategori Tanding
- [ ] Aksi tiga titik tampil
- [ ] `Edit` membuka modal
- [ ] Kategori lama termuat
- [ ] Submit sukses memperbarui kategori

### Hapus Kategori Tanding
- [ ] Hapus memakai SweetAlert
- [ ] Tidak bisa hapus jika sudah masuk pembayaran

### Responsive
- [ ] Desktop
- [ ] Mobile portrait
- [ ] Mobile landscape

## G. Modul Kategori Seni

URL: `/kontingen/seni`

### Tabel
- [ ] DataTables aktif
- [ ] Kolom tampil lengkap:
  - [ ] Nama anggota
  - [ ] Berat
  - [ ] Tinggi
  - [ ] Kategori usia
  - [ ] Jenis kelamin
  - [ ] Kategori seni
  - [ ] Pembayaran
  - [ ] Aksi

### Tambah Kategori Seni
- [ ] Tombol tambah membuka modal
- [ ] Dropdown kategori seni tampil
- [ ] Memilih kategori seni memuat atlet tersedia
- [ ] Bantuan jumlah anggota tampil sesuai jenis seni
- [ ] Submit sukses menyimpan kelompok seni

### Edit Kategori Seni
- [ ] Aksi tiga titik tampil
- [ ] `Edit` membuka modal
- [ ] Kategori lama termuat
- [ ] Submit sukses memperbarui kategori

### Hapus Kategori Seni
- [ ] Hapus memakai SweetAlert
- [ ] Tidak bisa hapus jika sudah masuk pembayaran

### Responsive
- [ ] Desktop
- [ ] Mobile portrait
- [ ] Mobile landscape

## H. Modul Pembayaran Kontingen

URL: `/kontingen/pembayaran`

### Navigasi
- [ ] Submenu sidebar tampil:
  - [ ] Tagihan
  - [ ] Menunggu Konfirmasi
  - [ ] Pembayaran Lunas

### Tagihan
- [ ] Item tanding belum dibayar tampil
- [ ] Item seni belum dibayar tampil
- [ ] Checkbox dapat dipilih
- [ ] Total pembayaran berubah saat item dicentang
- [ ] Informasi rekening tampil

### Upload Bukti
- [ ] Upload file gambar valid berhasil
- [ ] Upload file invalid ditolak
- [ ] Submit membuat transaksi pembayaran
- [ ] Redirect ke halaman menunggu konfirmasi

## I. Pembayaran Menunggu Konfirmasi

URL: `/kontingen/pembayaran/menunggu-konfirmasi`

- [ ] Tabel transaksi tampil
- [ ] DataTables aktif
- [ ] Tanggal tampil rapi
- [ ] Total tampil benar
- [ ] Aksi lihat detail aktif

## J. Pembayaran Lunas

URL: `/kontingen/pembayaran/lunas`

- [ ] Tabel transaksi tampil
- [ ] DataTables aktif
- [ ] Tanggal tampil rapi
- [ ] Total tampil benar
- [ ] Aksi lihat detail aktif

## K. Rincian Pembayaran

URL: `/kontingen/pembayaran/{id}`

- [ ] Detail item tanding tampil dalam tabel
- [ ] Detail item seni tampil dalam tabel
- [ ] Total pembayaran tampil benar
- [ ] Status pembayaran tampil benar
- [ ] Link bukti pembayaran dapat dibuka

## L. Global UX

- [ ] Toastr tampil untuk sukses
- [ ] Toastr tampil untuk error
- [ ] SweetAlert digunakan untuk konfirmasi hapus
- [ ] Tidak ada alert Bootstrap lama yang mengganggu flow

## M. Responsive Final Pass

Test halaman berikut pada:
- [ ] Desktop
- [ ] Tablet portrait
- [ ] Mobile portrait
- [ ] Mobile landscape

Halaman:
- [ ] `/`
- [ ] `/registrasi`
- [ ] `/pendaftaran/login`
- [ ] `/kontingen/dashboard`
- [ ] `/kontingen/peserta`
- [ ] `/kontingen/tanding`
- [ ] `/kontingen/seni`
- [ ] `/kontingen/pembayaran`

Checklist umum responsive:
- [ ] tidak ada elemen terpotong
- [ ] tidak ada overflow horizontal aneh
- [ ] modal tetap usable
- [ ] tabel tetap bisa dibaca / discroll dengan benar
- [ ] footer tetap rapi
