# DPS CI4

[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.7+-dd4814?style=for-the-badge&logo=codeigniter&logoColor=white)](https://codeigniter.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777bb4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952b3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![Status](https://img.shields.io/badge/status-active_development-c60000?style=for-the-badge)](#status)
[![Version](https://img.shields.io/badge/version-v0.3.0-1a1a1a?style=for-the-badge)](./VERSION)

Migrasi bertahap platform Digital Pencak Silat dari CodeIgniter 3 ke CodeIgniter 4 dengan pendekatan modern, lebih terstruktur, dan lebih siap dikembangkan untuk kebutuhan production berikutnya.

Project ini mempertahankan skema database dari sistem lama `dps`, sambil memindahkan alur public dan area kontingen ke arsitektur CI4 yang lebih bersih.

## Highlights

- Landing page public dengan visual yang diselaraskan ke project `dps`
- Registrasi kontingen native CodeIgniter 4
- Login dan dashboard kontingen dengan tema baru Bootstrap 5
- Sidebar `Merah Sport Arena` khusus area kontingen
- Modul peserta dengan modal dinamis, validasi ketat, dan dukungan arsip peserta
- Modul kategori tanding dan kategori seni untuk kontingen
- Modul pembayaran kontingen lengkap dengan upload bukti pembayaran
- Toastr, SweetAlert2, dan DataTables untuk UX yang lebih modern

## Status

Fondasi modul utama yang sudah tersedia:

- Landing page
- Registrasi kontingen
- Login kontingen
- Dashboard kontingen
- Modul peserta
- Modul kategori tanding
- Modul kategori seni
- Modul pembayaran

Dokumentasi QA dan triage bug tersedia di root project:

- `MIGRATION_PLAN_DPS_TO_CI4.md`
- `BUG_TRIAGE_DPS_CI4.md`
- `QA_CHECKLIST_DPS_CI4.md`

## Stack

- PHP 8.2+
- CodeIgniter 4.7+
- Bootstrap 5.3
- jQuery
- DataTables
- Toastr
- SweetAlert2
- MySQL / MariaDB

## Local Development

Jalankan dari root project:

```bash
php spark serve
```

Lalu buka:

```text
http://localhost:8080/
```

## Konfigurasi Dasar

Pengaturan utama ada di `.env`, terutama:

- `app.baseURL`
- `database.default.*`

Project ini saat ini diarahkan untuk memakai skema database yang sama dengan project `dps` lama.

## Struktur Penting

- `app/Controllers` : controller CI4
- `app/Services` : business/service layer migrasi
- `app/Views` : public pages dan area kontingen
- `public/assets` : asset CSS, JS, dan gambar
- `public/uploads` : file upload runtime
- `VERSION` : sumber versioning project

## QA Flow yang Disarankan

Urutan test paling efisien:

1. Landing page
2. Registrasi kontingen
3. Login kontingen
4. Dashboard kontingen
5. Peserta
6. Tanding
7. Seni
8. Pembayaran

Checklist rinci tersedia di `QA_CHECKLIST_DPS_CI4.md`.

## Catatan Migrasi

Project ini tidak dimaksudkan sebagai copy mentah CI3. Tujuannya adalah:

1. mempertahankan business flow utama lama
2. menghilangkan ketergantungan ke pola CI3 lama
3. menata ulang implementasi agar lebih maintainable di CI4

## Maintainer Notes

Jika Anda mengubah fitur besar, jangan lupa update:

- `VERSION`
- `CHANGELOG.md`
- `BUG_TRIAGE_DPS_CI4.md` bila ada bug baru
- `QA_CHECKLIST_DPS_CI4.md` bila flow test berubah
