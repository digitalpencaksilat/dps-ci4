# Planning Parity Modul Printer (Sertifikat) — DPS Legacy → CI4

Status: DRAFT / belum dieksekusi
Tanggal: 2026-06-07
Referensi legacy: `/Applications/XAMPP/xamppfiles/htdocs/dps`
Target: `/Applications/XAMPP/xamppfiles/htdocs/dps-ci4`

---

## 1. Ringkasan & Tujuan

Modul **printer** di project lama adalah area login role `printer` (`users/printer/*`) yang menangani
pencetakan **sertifikat**, **kartu peserta (ID card)**, pengaturan **tata letak sertifikat**,
**nomor sertifikat**, serta beberapa setting pendukung (domain hosting untuk QR, sembunyikan
background, upload sampel sertifikat).

Tujuan plan ini: **mencapai parity fitur sertifikat** dari modul printer legacy ke arsitektur CI4,
mengikuti pola yang sudah terbukti di modul **ID Card** yang sudah lebih dulu dimigrasikan.

---

## 2. Hasil Investigasi Legacy

### 2.1 Controller legacy
`application/controllers/users/Printer.php` (role guard via `_remap`, `level == 'printer'`):

- `dashboard()` — kartu aksi: upload sertifikat, atur tata letak, domain hosting, hide background, nomor sertifikat.
- `sertifikat_peserta_tanding()` / `sertifikat_peserta_seni()` — daftar peserta untuk dicetak.
- `pengaturan_tata_letak_sertifikat()` — editor drag-drop tata letak.
- `preview_sertifikat()` — preview A4 landscape.
- `upload_sertifikat()` — upload PNG ke `uploads/sertifikat/sertifikat.png`.
- `update_domain_hosting()` / `update_hide_background()` — simpan ke `site_builder_settings`.
- **Nomor sertifikat**: `update_nomor_sertifikat_suffix()`, `generate_nomor_sertifikat_ajax()`,
  `generate_semua_nomor_sertifikat()`, `reset_nomor_sertifikat()` (passcode), `get_statistik_nomor_sertifikat_ajax()`.
- Banyak method cetak sertifikat lama (PDF dompdf) **sudah dikomentari** di legacy — alur aktif adalah
  cetak **editable + scan barcode** via `window.print()`.

### 2.2 Model legacy
`application/models/Printer_model.php`:

- Query peraih medali tanding/seni (`get_peraih_medali_*`), peserta non-medali (`get_peserta_non_medali_*`),
  detail spesifik (`get_specific_peraih_medali_*`), official.
- Ubah status sertifikat (`ubah_status_sertifikat_*` → `status_sertifikat = 'sudah_dicetak'`).
- Logika **nomor sertifikat** (counter global `MAX(SUBSTRING_INDEX(...))` gabungan tanding+seni,
  suffix dari `site_builder_settings`, generate single/bulk/reset/statistik).

### 2.3 View legacy
- Dashboard: `application/views/admin/printer/dashboard.php`.
- Tata letak: `application/views/admin/printer/pengaturan_tata_letak_sertifikat.php`
  (post ke `utilities/config/set`, target file `print/sertifikat.php`).
- Cetak: `application/views/print/sertifikat/pages/{editable_print,manual_print,pdf_print,preview}.php`.
- Style dinamis: `application/views/print/sertifikat/styles/sertifikat.php` (baca `config/print/sertifikat.php`).
- Default layout: `application/config/print/sertifikat.php` (posisi/inset, font, align, display tiap elemen:
  nomor, nama, kategori, kontingen, sekolah, qrcode).
- Scan barcode pakai `onScan.js`: index 2 = kode kategori (1=tanding, lainnya=seni), index 3–7 = id peserta + 1000.
- QR code (`pdf_print.php`/`manual_print.php`) memakai library QRCode JS dengan `qrcode_url` berbasis `domain_hosting`.

---

## 3. Apa yang SUDAH ada di CI4 (jangan dibuat ulang)

| Fitur legacy printer | Status CI4 | Lokasi CI4 |
|---|---|---|
| Nomor sertifikat (suffix, generate single/bulk, reset, statistik) | ✅ Sudah migrasi (ke role **sekretariat**) | [`NomorSertifikatController.php`](../../app/Controllers/Admin/Sekretariat/NomorSertifikatController.php), [`SekretariatPesertaKontingenService.php`](../../app/Services/SekretariatPesertaKontingenService.php), `app/Views/admin/sekretariat/nomor_sertifikat/` |
| Kartu Peserta / ID Card (tata letak DB, upload bg, cetak per kontingen/peserta, barcode) | ✅ Sudah migrasi | [`IdCardController.php`](../../app/Controllers/Admin/Sekretariat/IdCardController.php), [`IdCardService.php`](../../app/Services/IdCardService.php), [`Config/IdCard.php`](../../app/Config/IdCard.php), `app/Views/print/id_card/` |
| Editor tata letak (JS drag-drop) | ✅ Asset sudah ter-port (mendukung nomor, nama, kategori, kontingen, sekolah, qrcode) | [`public/assets/js/admin/certificate_editor.js`](../../public/assets/js/admin/certificate_editor.js) |
| Kolom `status_sertifikat` & `nomor_sertifikat` | ✅ Ada di skema | [`2026-05-24-000001_CreateSekretariatResourceTables.php`](../../app/Database/Migrations/2026-05-24-000001_CreateSekretariatResourceTables.php) |
| Setting key-value (`get_setting`, `SiteBuilderSettingModel`) | ✅ Ada | [`SiteBuilderSettingModel.php`](../../app/Models/SiteBuilderSettingModel.php), `Admin\Super\SettingWriterService` |

**Catatan arsitektur:** Role `printer` legacy **tidak dipertahankan terpisah** di CI4. Tugas printer
sudah dikonsolidasikan ke role **sekretariat** (lihat `AdminRoleFilter` hanya kenal
`kontingen, bendahara, sekretariat, super_admin`). Plan ini mengikuti keputusan tsb.

---

## 4. GAP yang harus dikerjakan (scope parity ini)

Yang **belum** ada di CI4 dan menjadi target plan:

1. **Cetak Sertifikat** peserta tanding & seni (editable + scan barcode + `window.print()`).
2. **Pengaturan tata letak sertifikat** (editor drag-drop, simpan ke DB settings JSON — bukan tulis file config).
3. **Upload background sertifikat** (PNG → `public/uploads/sertifikat/sertifikat.png`).
4. **Setting Domain Hosting** (base URL untuk QR code sertifikat).
5. **Setting Hide Background Sertifikat**.
6. **QR Code** pada sertifikat (link ke bagan, berbasis domain hosting).
7. (Opsional) sertifikat **official** dan **pemesanan medali** — di legacy sudah dikomentari/terpisah;
   default **out-of-scope** kecuali diminta.

---

## 5. Desain Target CI4

Mengikuti pola `IdCard` (Config default + Service layout DB-first + Controller tipis + view `print/`).

### 5.1 File baru

| Layer | File | Isi |
|---|---|---|
| Config | `app/Config/Sertifikat.php` | Default layout tiap elemen (port dari `config/print/sertifikat.php`), method `allDefaults()` |
| Service | `app/Services/SertifikatService.php` | `getLayoutConfig()`/`saveLayoutConfig()` (DB key `sertifikat_layout`, merge file default), query peraih medali & peserta (tanding/seni), `ubahStatusSertifikat*`, `uploadBackground()`, `hasBackground()`, `backgroundUrl()`, helper QR url dari domain hosting, decode barcode |
| Controller | `app/Controllers/Admin/Sekretariat/SertifikatController.php` | `index` (dashboard), `pengaturanTataLetak`, `simpanTataLetak`, `uploadBackground`, `updateDomainHosting`, `updateHideBackground`, `preview`, `cetakTanding/$1`, `cetakSeni/$1`, `apiPeserta*` (scan barcode), `pesertaTanding`, `pesertaSeni` |
| View dashboard | `app/Views/admin/sekretariat/sertifikat/index.php` | Kartu: upload bg, atur tata letak, domain hosting, hide background, link daftar cetak |
| View tata letak | `app/Views/admin/sekretariat/sertifikat/pengaturan_tata_letak.php` | Canvas editor + `certificate_editor.js` (sudah ada) |
| View daftar | `app/Views/admin/sekretariat/sertifikat/cetak_tanding.php`, `cetak_seni.php` | DataTables peserta + tombol cetak |
| View print | `app/Views/print/sertifikat/template.php` | Template print (mirror `print/id_card/template.php`) |
| View print | `app/Views/print/sertifikat/pages/{editable_print,preview}.php` | Halaman cetak editable + preview |
| View print | `app/Views/print/sertifikat/styles/sertifikat.php` | Style dinamis baca layout dari Service (bukan `$this->config->item`) |
| Asset | `public/uploads/sertifikat/` | Folder target upload (buat + `.gitignore`/`index.html`) |
| Asset (cek) | QR code JS (`qrcode.min.js`) | Sediakan di `public/assets/` bila belum ada (legacy: `assets/qrcode/js`) |

### 5.2 Routing (tambah di grup `admin/sekretariat`)
File: [`app/Config/Routes.php`](../../app/Config/Routes.php) (grup `adminrole:sekretariat`).

```php
// Sertifikat
$routes->get('sertifikat', 'Admin\Sekretariat\SertifikatController::index');
$routes->get('sertifikat/pengaturan-tata-letak', 'Admin\Sekretariat\SertifikatController::pengaturanTataLetak');
$routes->post('sertifikat/simpan-tata-letak', 'Admin\Sekretariat\SertifikatController::simpanTataLetak');
$routes->post('sertifikat/upload-background', 'Admin\Sekretariat\SertifikatController::uploadBackground');
$routes->post('sertifikat/update-domain-hosting', 'Admin\Sekretariat\SertifikatController::updateDomainHosting');
$routes->post('sertifikat/update-hide-background', 'Admin\Sekretariat\SertifikatController::updateHideBackground');
$routes->get('sertifikat/preview', 'Admin\Sekretariat\SertifikatController::preview');
$routes->get('sertifikat/cetak-tanding', 'Admin\Sekretariat\SertifikatController::cetakTandingList');
$routes->get('sertifikat/cetak-seni', 'Admin\Sekretariat\SertifikatController::cetakSeniList');
$routes->get('sertifikat/cetak/(:segment)/(:num)', 'Admin\Sekretariat\SertifikatController::cetakSingle/$1/$2');
$routes->get('sertifikat/api/peserta-tanding/(:num)', 'Admin\Sekretariat\SertifikatController::apiPesertaTanding/$1');
$routes->get('sertifikat/api/peserta-seni/(:num)', 'Admin\Sekretariat\SertifikatController::apiPesertaSeni/$1');
```

### 5.3 Sidebar / menu
Tambah item menu "Cetak Sertifikat" di sidebar sekretariat (di sekitar item ID Card & Nomor Sertifikat)
pada layout admin: [`app/Views/layouts/admin.php`](../../app/Views/layouts/admin.php). Gunakan `activeMenu`.

---

## 6. Keputusan & Penyimpangan dari Legacy (untuk dikonfirmasi)

1. **Tata letak disimpan ke DB (JSON) bukan tulis ulang file config**, sama seperti `IdCardService`
   (key `sertifikat_layout`). Lebih aman & konsisten; tidak menulis ke `app/Config`.
2. **Tidak menambah role `printer` baru**; fitur masuk ke role **sekretariat** (sesuai pola CI4 saat ini).
   → *Konfirmasi: setuju konsolidasi ke sekretariat, atau perlu role/area khusus printer?*
3. **Cetak pakai `window.print()`** (parity alur editable legacy). Opsi PDF server (mPDF) tidak diambil
   kecuali diminta — legacy method PDF-nya sudah dikomentari.
4. **Official & pemesanan medali**: default out-of-scope.

---

## 7. Optimasi Query yang Direncanakan

- Query peraih medali/peserta dipindah ke Service dengan **select kolom eksplisit** (hindari `SELECT *`),
  sama seperti `IdCardService`.
- Hindari query inline di view (legacy `dashboard.php` query langsung di view) — pindah ke Service/Controller.
- Statistik & daftar peserta diambil sekali (hindari N+1); barcode scan via 1 endpoint AJAX per peserta.
- Reuse skema barcode `IdCardService::barcodeValueTanding/Seni` agar konsisten dengan kartu peserta.

---

## 8. Rencana Validasi

- `php -l` untuk tiap file PHP baru.
- `php spark routes` memastikan route sertifikat terdaftar di grup sekretariat.
- Uji manual alur:
  1. Dashboard sertifikat tampil (role sekretariat) + redirect role lain.
  2. Upload background PNG → file masuk `public/uploads/sertifikat/sertifikat.png`.
  3. Editor tata letak → simpan → nilai persist (DB) → tercermin di preview & cetak.
  4. Domain hosting & hide background tersimpan dan memengaruhi QR/background.
  5. Cetak tanding & seni: data benar, scan barcode mengisi nama/kategori/kontingen, `status_sertifikat` jadi `sudah_dicetak`.
  6. Preview A4 landscape sesuai layout.
- Bila ada unit test pattern (`tests/unit/Services/...`), tambahkan test untuk `SertifikatService`
  (layout merge, decode barcode, QR url, statistik) mengikuti gaya test yang ada.

---

## 9. Urutan Eksekusi (Saran)

1. `Config/Sertifikat.php` (port default layout).
2. `SertifikatService.php` (layout DB-first + query + upload bg + QR/domain helper).
3. Routes + `SertifikatController.php`.
4. View dashboard + pengaturan tata letak (pakai `certificate_editor.js`).
5. View print template + styles dinamis + halaman cetak/preview.
6. Daftar cetak (tanding/seni) + endpoint AJAX scan barcode + ubah status.
7. Menu sidebar.
8. Validasi (syntax, routes, QA manual) + update `CHANGELOG.md` & `VERSION`.

---

## 10. Risiko / Catatan

- **Library QR code**: pastikan tersedia di CI4 (`public/assets/`); legacy memakai `assets/qrcode/js`.
- **Konversi posisi**: `certificate_editor.js` memakai `PX_TO_CM = 37.795` & elemen `qrcode`; pastikan
  format inset di `Config/Sertifikat.php` & style dinamis kompatibel dengan output editor.
- **`utilities/config/set`** legacy (penyimpan tata letak lama) **tidak dipakai** — diganti endpoint DB.
- Verifikasi nama kolom relasi (`status_sertifikat`, `nomor_sertifikat`) sudah benar di kedua tabel
  `peserta_tanding` & `peserta_seni` (sudah dikonfirmasi ada di migrasi).
- Jangan menyentuh implementasi **Nomor Sertifikat** & **ID Card** yang sudah jalan; hanya
  tautkan (link) dari dashboard sertifikat bila perlu.
