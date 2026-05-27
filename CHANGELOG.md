# Changelog

Semua perubahan penting pada project ini akan dicatat di file ini.

Format changelog ini mengikuti gaya sederhana berbasis versi.

## v0.3.0 - 2026-05-24

### Added

- Modul sekretariat untuk kelola kontingen, data atlet, peserta tanding, dan kelompok seni.
- Dashboard sekretariat dengan ringkasan kontingen, pendaftar, peserta tanding, dan kelompok seni.
- Service bersama `SekretariatPesertaKontingenService` untuk alur peserta dan kontingen lintas modul.
- Route dan menu admin sekretariat untuk kontingen, atlet, peserta tanding, dan peserta seni.
- Migration fallback untuk tabel resource sekretariat pada instalasi CI4 baru.
- Dokumen rencana dan status migrasi modul sekretariat.

### Changed

- Query kategori tanding dan seni memakai service bersama agar validasi kontingen dan sekretariat konsisten.
- Model kontingen, pendaftar, peserta tanding, peserta seni, dan arsip pendaftar diperketat dengan `allowedFields`.
- Tampilan tabel admin dan tab detail kontingen disesuaikan agar lebih nyaman di data lebar.

### Fixed

- File upload bukti pembayaran baru tidak lagi muncul sebagai kandidat commit karena rule ignore diperketat.

## v0.4.0 - 2026-05-27

### Added

- Modul super admin tahap lanjut untuk pemilihan mode, dashboard pengaturan event, CRUD kategori usia, CRUD kategori lomba, dan tabel read-only sub kategori seni.
- Route admin super baru untuk mode pengaturan event dan pengaturan kategori lomba.
- Feedback inline per field pada registrasi publik dan modal peserta kontingen.
- Select all, count per kategori, dan ringkasan item terpilih pada checkout pembayaran kontingen.
- Badge risiko `Read-only`, `Sensitive`, dan `Destructive` pada dashboard development.

### Changed

- Label navigasi admin distandarkan agar lebih konsisten antara bendahara, sekretariat, dan kontingen.
- Footer admin diselaraskan dengan gaya kontingen termasuk copy hak cipta dan tampilan versi aplikasi.
- Tipografi area admin dirapikan agar lebih selaras dengan area kontingen.
- Status tracker migrasi CI4 diperbarui untuk modul admin super pengaturan kategori lomba.

### Fixed

- Link arsip peserta di modal kontingen tidak lagi dirender dengan `innerHTML`.
- `old()` value registrasi publik kini di-escape dengan aman untuk mencegah markup rusak.
- Validasi registrasi publik kini tampil dekat field yang salah dan fokus ke error pertama.
- Slot arsip wajib peserta kini benar-benar `required` saat create dan kondisional saat edit.
- Tombol submit checkout kontingen kini menunggu item terpilih dan bukti transfer valid.

## v0.2.0 - 2026-05-24

### Added

- Login admin native CodeIgniter 4 untuk role bendahara, sekretariat, dan super admin.
- Filter role admin dan routing dashboard admin per role.
- Dashboard bendahara berisi ringkasan pembayaran, antrian verifikasi, dan metrik transaksi.
- Modul pembayaran bendahara untuk daftar transaksi, status menunggu, lunas, belum dibayar, riwayat tanding, dan riwayat seni.
- Modul rekap kontingen bendahara dengan detail kontingen, item belum dibayar, dan pembuatan transaksi dari item terpilih.
- Nota pembayaran HTML dan PDF untuk arsip bendahara.
- Layout admin baru dengan sidebar, topbar, DataTables, Toastr, SweetAlert2, dan footer versi aplikasi.
- Popup/modal preview bukti pembayaran pada rincian transaksi dan nota.

### Changed

- Area admin bendahara dibuat lebih responsif dengan sidebar fixed dan scrollable.
- Tabel admin dibuat scroll horizontal di area tabel, bukan membuat halaman melebar.
- Card antrian verifikasi pada dashboard bendahara dibuat full width.
- Tabel item tanding dan seni pada rincian transaksi menggunakan DataTables agar tersedia pagination.
- Preview bukti pembayaran dibatasi ukurannya agar tidak terlalu besar di layar.
- Proses bayar item terpilih di detail kontingen menyimpan pilihan secara aman walau tabel memakai DataTables.

### Fixed

- Halaman kontingen bendahara gagal diakses karena query memakai kolom `pt.id_kontingen` yang tidak ada pada schema.
- Total rekap kontingen berpotensi dobel akibat join banyak tabel sekaligus.
- Sidebar admin meninggalkan area kosong putih saat halaman panjang discroll.
- Tombol `Bayar Item Terpilih` salah menampilkan toast seolah belum ada item dipilih.
- Tombol `Lihat Bukti` sebelumnya membuka tab baru, sekarang tampil di popup.

## v0.1.0 - 2026-05-23

### Added

- Dokumen rencana migrasi `MIGRATION_PLAN_DPS_TO_CI4.md`
- Dokumen triage bug `BUG_TRIAGE_DPS_CI4.md`
- Dokumen checklist QA `QA_CHECKLIST_DPS_CI4.md`
- Fondasi login kontingen native CodeIgniter 4
- Filter autentikasi kontingen
- Dashboard kontingen dengan tema baru Bootstrap 5
- Modul peserta kontingen
- Modal dinamis peserta dengan tab `Data Peserta` dan `Arsip Peserta`
- Dukungan arsip peserta berbasis `arsip_pendaftar_slots`
- Modul kategori tanding kontingen
- Modul kategori seni kontingen
- Modul pembayaran kontingen
- Integrasi Toastr, SweetAlert2, dan DataTables pada area kontingen
- Footer versioning berbasis file `VERSION`

### Changed

- Landing page disusun ulang agar lebih dekat secara visual ke project `dps`
- Sidebar area kontingen diubah ke tema `Merah Sport Arena`
- Halaman dashboard kontingen dibersihkan dari elemen migrasi/dev yang tidak perlu
- Tabel peserta, tanding, seni, dan pembayaran diarahkan ke pola UI yang lebih production-ready
- Validasi peserta diperketat untuk NIK, KK, tinggi badan, dan berat badan

### Fixed

- Error route dan view awal migrasi landing page
- Error dashboard yang memakai asumsi kolom pembayaran lama yang tidak sesuai schema
- Inkonistensi flash message dan konfirmasi hapus dengan mengganti ke Toastr dan SweetAlert2
- Layout responsive dasar untuk area kontingen di desktop, tablet, dan mobile

## v0.1.1 - 2026-05-23

### Added

- Integrasi reCAPTCHA opsional pada halaman registrasi
- Throttling percobaan login kontingen berbasis IP dan email

### Changed

- CSRF diaktifkan untuk request web

### Fixed

- Pesan error login kontingen lebih informatif saat throttling aktif

### Security

- Validasi upload diperketat (extension, MIME type, dan size) untuk arsip peserta dan bukti pembayaran
- Proteksi direktori upload dengan menambahkan file `index.html`
