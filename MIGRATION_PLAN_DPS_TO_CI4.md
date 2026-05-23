# Migration Plan DPS CI3 to CI4

## Tujuan

Migrasikan project `dps` berbasis CodeIgniter 3 ke `dps-ci4` berbasis CodeIgniter 4 secara bertahap tanpa mengubah skema database yang saat ini sudah dipakai oleh `dps`.

Target jangka dekat:

1. Landing page utama pendaftaran
2. Halaman login kontingen
3. Dashboard kontingen baru
4. Modul pendaftaran peserta
5. Modul kategori tanding
6. Modul kategori seni
7. Modul pembayaran

## Keputusan Scope Saat Ini

Untuk fase awal, halaman berikut di-skip dulu dan jika sudah ada rewrite parsial di `dps-ci4` akan dibersihkan:

1. `jadwal`
2. `cek-data`
3. `lihat-kuota`
4. `live-jadwal`
5. `live-medali`

Landing page utama dan halaman `registrasi` tetap masuk scope awal.

## Keputusan Teknis

1. Database tetap memakai schema yang sama dengan project `dps`
2. Migrasi ditujukan menjadi full CodeIgniter 4, bukan hybrid CI3-CI4
3. Route harus explicit dan `autoRoute` tetap nonaktif
4. Area kontingen tidak lagi memakai tema Argon
5. Dashboard kontingen akan memakai Bootstrap 5 dengan arah visual mengacu ke `portal-digitalsilat`

## Referensi Source CI3

### Public / Landing

- Controller utama: `dps/application/controllers/users/Pendaftaran.php`
- View utama: `dps/application/views/pendaftaran/*`
- Route: `dps/application/config/routes/users.php`

### Login Kontingen

- Page login: `users/Pendaftaran::login()`
- View login: `dps/application/views/kontingen/login.php`
- Submit login: `users/Kontingen::login_submit()`
- Auth model: `dps/application/models/users/User_kontingen_model.php`

### Area Kontingen Nyata

Walaupun CI3 memiliki `kontingen/dashboard.php`, pusat kerja user kontingen sebenarnya ada di modul berikut:

1. `pendaftar`
2. `peserta-tanding`
3. `kelompok-peserta-seni`
4. `pembayaran`

Controller CI3 yang relevan:

- `dps/application/controllers/resources/Pendaftar.php`
- `dps/application/controllers/resources/Peserta_tanding.php`
- `dps/application/controllers/resources/Kelompok_peserta_seni.php`
- `dps/application/controllers/resources/Pembayaran.php`

## Arah Arsitektur CI4

### Controller yang Direncanakan

1. `App\Controllers\Public\PendaftaranController`
2. `App\Controllers\Auth\KontingenAuthController`
3. `App\Controllers\Kontingen\DashboardController`
4. `App\Controllers\Kontingen\PendaftarController`
5. `App\Controllers\Kontingen\PesertaTandingController`
6. `App\Controllers\Kontingen\PesertaSeniController`
7. `App\Controllers\Kontingen\PembayaranController`

### Service yang Direncanakan

1. `KontingenAuthService`
2. `LandingService`
3. `DashboardKontingenService`
4. `PendaftarService`
5. `PesertaTandingService`
6. `PesertaSeniService`
7. `PembayaranService`

### Layout View

1. `app/Views/layouts/public.php`
2. `app/Views/layouts/kontingen.php`

## Tema Dashboard Kontingen Baru

Dashboard kontingen CI4 akan memakai tema baru berbasis Bootstrap 5, tidak lagi memakai Argon.

### Referensi visual

Mengacu ke `portal-digitalsilat`:

- Font body: `Poppins`
- Font heading/penekanan: `Oswald`
- Warna utama: `#C60000`
- Warna aksen: `#FFD700`
- Warna gelap: `#1A1A1A`

### Karakter visual yang diinginkan

1. Bersih dan modern
2. Ringan dan mudah dikembangkan
3. Bootstrap 5 native
4. Card statistik ringkas
5. Sidebar kiri plus topbar

### Struktur dashboard kontingen baru

Dashboard baru akan menjadi hub operasional, berisi:

1. Statistik jumlah atlet
2. Statistik kategori tanding
3. Statistik kategori seni
4. Statistik item/tagihan belum dibayar
5. Shortcut ke modul utama
6. Ringkasan informasi kontingen dan event

## Tahapan Implementasi

### Fase 0 - Cleanup Scope

1. Bersihkan route public CI4 yang di-skip
2. Bersihkan method controller CI4 yang hanya melayani halaman yang di-skip
3. Bersihkan view CI4 untuk halaman yang di-skip

### Fase 1 - Fondasi Full CI4

1. Route explicit
2. Layout public dan kontingen terpisah
3. Auth kontingen native CI4
4. Filter auth kontingen
5. Adapter config/settings sementara bila masih dibutuhkan

### Fase 2 - Landing Page

1. Migrasikan landing utama agar tetap seperti `dps`
2. Migrasikan `registrasi`
3. Pertahankan `download-form-excel`

### Fase 3 - Login Kontingen

1. Halaman login kontingen baru
2. Submit login native CI4
3. Logout
4. Lupa/reset password menyusul bila diperlukan

### Fase 4 - Dashboard Kontingen Baru

1. Layout baru Bootstrap 5
2. Sidebar + topbar
3. Stat cards dan shortcut modul

### Fase 5 - Modul Pendaftar

1. List atlet
2. Tambah atlet
3. Edit atlet
4. Hapus atlet
5. Upload foto

### Fase 6 - Modul Kategori Tanding

1. List peserta tanding
2. Tambah kategori tanding
3. Edit kategori tanding
4. Hapus/undur diri sesuai aturan lama

### Fase 7 - Modul Kategori Seni

1. List kelompok peserta seni
2. Tambah kategori seni
3. Edit kategori seni
4. Hapus/undur diri sesuai aturan lama

### Fase 8 - Modul Pembayaran

1. Menunggu pembayaran
2. Checkout item
3. Upload bukti pembayaran
4. Menunggu konfirmasi
5. Pembayaran lunas
6. Rincian pembayaran

## Prinsip Migrasi

1. Skema database tidak diubah pada fase awal
2. Business rule lama dipindahkan dulu, baru dirapikan
3. Landing dipertahankan mendekati CI3
4. Dashboard kontingen didesain ulang secara modern
5. Hindari copy mentah pola CI3 seperti `_remap`, `MY_Controller`, dan `$this->load`

## Fokus Implementasi Saat Ini

1. Menyimpan planning ini di root project
2. Membersihkan public pages CI4 yang di-skip
3. Menyiapkan auth kontingen CI4
4. Menyiapkan dashboard kontingen baru dengan Bootstrap 5
5. Menyiapkan dasar untuk migrasi modul kontingen berikutnya
