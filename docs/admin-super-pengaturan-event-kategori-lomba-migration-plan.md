# Admin Super Pengaturan Event dan Kategori Lomba Migration Plan

Dokumen ini adalah plan terpisah untuk migrasi halaman-halaman CI3 pada mode **pengaturan event** dan mode **pengaturan kategori lomba** dari repo lama `/Applications/XAMPP/xamppfiles/htdocs/dps/` ke repo CI4 ini.

## Scope

Scope utama:

- Mode pemilihan super admin untuk pengaturan event.
- Dashboard pengaturan event.
- Side navigation pengaturan event.
- Mode pemilihan super admin untuk pengaturan kategori lomba.
- Side navigation pengaturan kategori lomba.
- CRUD kategori usia.
- CRUD kategori lomba.
- CRUD sub kategori seni.
- Otomatis pembuatan pool untuk sub kategori seni.

Out of scope tahap awal:

- Migrasi penuh semua form pengaturan event yang menyentuh upload, rekening pembayaran, landing page, sponsor, dan digital scoring.
- Perubahan schema database kecuali audit menemukan field CI4 yang belum cocok dengan DB CI3.
- Perubahan behavior kontingen, sekretariat, bendahara, pembayaran, PDF, jadwal, atau bagan kecuali dibutuhkan untuk menjaga kategori tetap berfungsi.

## Status CI4 Saat Ini

Temuan awal di repo CI4:

- `app/Controllers/Admin/Super/DashboardController.php` sudah ada, tetapi masih minimal.
- `app/Config/Routes.php` belum memiliki route group eksplisit untuk `admin/super-admin`.
- `app/Config/Filters.php` sudah memiliki filter `adminrole`.
- CSRF aktif global di `app/Config/Filters.php`.
- Model kategori sudah ada:
  - `app/Models/KategoriUsiaModel.php`
  - `app/Models/KategoriLombaModel.php`
  - `app/Models/SubKategoriSeniModel.php`
- View khusus super admin pengaturan event dan kategori lomba belum ditemukan.
- Tracker sudah ditambahkan di `docs/migration-ci4-status.md` untuk:
  - `Admin super pengaturan event`
  - `Admin super pengaturan kategori lomba`

## Source CI3 Yang Perlu Diaudit

### Controller Super Admin

Source utama:

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/users/Super_admin.php`

Method prioritas:

- `index()`
- `menu_tipe_super_admin()`
- `mode_pengaturan_event()`
- `mode_pengaturan_kategori_lomba()`
- `dashboard_pengaturan_event()`

Catatan penting:

- CI3 memakai session key `tipe_super_admin`.
- Nilai mode CI3 perlu dipertahankan untuk parity, termasuk typo historis `perngaturan_kategori_lomba` jika masih dipakai menu/session.

### Controller Kategori

Source CI3:

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/resources/Kategori_usia.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/resources/Kategori_lomba.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/resources/Sub_kategori_seni.php`

Method prioritas:

- `index()`
- `edit()`
- `create()`
- `update()`
- `delete()`
- `otomatis_menambahkan_pool()` untuk sub kategori seni.

### Model CI3

Source CI3:

- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Kategori_usia_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Kategori_lomba_model.php`
- `/Applications/XAMPP/xamppfiles/htdocs/dps/application/models/resources/Sub_kategori_seni_model.php`

Model pendukung dashboard/event:

- `Kelas_tanding_model`
- `Kompetisi_tanding_model`
- `Kompetisi_seni_model`
- `Peserta_tanding_model`
- `Kelompok_peserta_seni_model`
- `Penilaian_seni_model`

### Views CI3

Pengaturan event:

- `application/views/admin/super_admin/dashboard_pengaturan_event.php`
- `application/views/admin/super_admin/menu_tipe_super_admin.php`
- `application/views/admin/super_admin/components/sideNavPengaturanEvent.php`
- `application/views/admin/super_admin/components/sideNavPengaturanKategoriLomba.php`

Halaman pengaturan event lanjutan yang ditunda:

- `pengaturan_profil_kejuaraan.php`
- `pengaturan_gambar_dan_juknis.php`
- `pengaturan_akses_pendaftaran_peserta.php`
- `pengaturan_rekening_pembayaran.php`
- `pengaturan_akses_pemilihan_kategori_perlombaan.php`
- `pengaturan_konten_halaman_landing.php`
- `pengaturan_video_sponsor.php`
- `pengaturan_digital_scoring_tanding.php`

Kategori lomba:

- `application/views/shared_pages/kategori_usia/all.php`
- `application/views/shared_pages/kategori_usia/detail.php`
- `application/views/shared_pages/kategori_usia/edit.php`
- `application/views/shared_pages/kategori_lomba/all.php`
- `application/views/shared_pages/kategori_lomba/edit.php`
- `application/views/shared_pages/sub_kategori_seni/all.php`
- `application/views/shared_pages/sub_kategori_seni/detail.php`
- `application/views/shared_pages/sub_kategori_seni/edit.php`

## Target CI4 Files

Controller target yang disarankan:

- `app/Controllers/Admin/Super/ModeController.php`
- `app/Controllers/Admin/Super/PengaturanEventController.php`
- `app/Controllers/Admin/Super/KategoriUsiaController.php`
- `app/Controllers/Admin/Super/KategoriLombaController.php`
- `app/Controllers/Admin/Super/SubKategoriSeniController.php`

Service target yang disarankan:

- `app/Services/Admin/Super/PengaturanEventService.php`
- `app/Services/Admin/Super/KategoriUsiaService.php`
- `app/Services/Admin/Super/KategoriLombaService.php`
- `app/Services/Admin/Super/SubKategoriSeniService.php`

View target yang disarankan:

- `app/Views/admin/super/menu_tipe_super_admin.php`
- `app/Views/admin/super/dashboard_pengaturan_event.php`
- `app/Views/admin/super/components/side_nav_pengaturan_event.php`
- `app/Views/admin/super/components/side_nav_pengaturan_kategori_lomba.php`
- `app/Views/admin/super/kategori_usia/index.php`
- `app/Views/admin/super/kategori_usia/show.php`
- `app/Views/admin/super/kategori_usia/edit.php`
- `app/Views/admin/super/kategori_lomba/index.php`
- `app/Views/admin/super/kategori_lomba/edit.php`
- `app/Views/admin/super/sub_kategori_seni/index.php`
- `app/Views/admin/super/sub_kategori_seni/show.php`
- `app/Views/admin/super/sub_kategori_seni/edit.php`

## Route Plan

Tambahkan route group eksplisit di `app/Config/Routes.php`:

```php
$routes->group('admin/super-admin', ['filter' => 'adminrole:super_admin'], static function ($routes): void {
    $routes->get('/', 'Admin\\Super\\DashboardController::index');
    $routes->get('dashboard', 'Admin\\Super\\DashboardController::index');

    $routes->get('menu-tipe', 'Admin\\Super\\ModeController::menuTipe');
    $routes->get('mode-pengaturan-event', 'Admin\\Super\\ModeController::pengaturanEvent');
    $routes->get('mode-pengaturan-kategori-lomba', 'Admin\\Super\\ModeController::pengaturanKategoriLomba');

    $routes->get('dashboard-pengaturan-event', 'Admin\\Super\\PengaturanEventController::dashboard');

    $routes->get('kategori-usia', 'Admin\\Super\\KategoriUsiaController::index');
    $routes->get('kategori-usia/(:num)', 'Admin\\Super\\KategoriUsiaController::show/$1');
    $routes->get('kategori-usia/(:num)/edit', 'Admin\\Super\\KategoriUsiaController::edit/$1');
    $routes->post('kategori-usia', 'Admin\\Super\\KategoriUsiaController::store');
    $routes->post('kategori-usia/(:num)/update', 'Admin\\Super\\KategoriUsiaController::update/$1');
    $routes->post('kategori-usia/(:num)/delete', 'Admin\\Super\\KategoriUsiaController::delete/$1');

    $routes->get('kategori-lomba', 'Admin\\Super\\KategoriLombaController::index');
    $routes->get('kategori-lomba/(:num)/edit', 'Admin\\Super\\KategoriLombaController::edit/$1');
    $routes->post('kategori-lomba', 'Admin\\Super\\KategoriLombaController::store');
    $routes->post('kategori-lomba/(:num)/update', 'Admin\\Super\\KategoriLombaController::update/$1');
    $routes->post('kategori-lomba/(:num)/delete', 'Admin\\Super\\KategoriLombaController::delete/$1');

    $routes->get('sub-kategori-seni', 'Admin\\Super\\SubKategoriSeniController::index');
    $routes->get('sub-kategori-seni/(:num)', 'Admin\\Super\\SubKategoriSeniController::show/$1');
    $routes->get('sub-kategori-seni/(:num)/edit', 'Admin\\Super\\SubKategoriSeniController::edit/$1');
    $routes->post('sub-kategori-seni', 'Admin\\Super\\SubKategoriSeniController::store');
    $routes->post('sub-kategori-seni/(:num)/update', 'Admin\\Super\\SubKategoriSeniController::update/$1');
    $routes->post('sub-kategori-seni/(:num)/delete', 'Admin\\Super\\SubKategoriSeniController::delete/$1');
});
```

Route alias dari URL CI3 lama hanya boleh ditambahkan jika masih ada menu/link lama yang membutuhkannya. Alias tetap harus aman dan tidak boleh bypass `adminrole:super_admin`.

## Model dan Data Impact

### Kategori Usia

Tabel: `kategori_usia`

Field CI3 yang perlu dipastikan di CI4:

- `id_kategori_usia`
- `nama_kategori_usia`
- `jenis_kelamin`
- `min_umur`
- `max_umur`
- `acuan_tanggal`

Gap awal CI4:

- `KategoriUsiaModel::$allowedFields` belum mencantumkan `acuan_tanggal`.

### Kategori Lomba

Tabel: `kategori_lomba`

Field CI3 yang perlu dipastikan di CI4:

- `id_kategori_lomba`
- `id_kategori_usia`
- `nama_kategori_lomba`
- `peraturan_pertandingan`
- `jenis_perlombaan`
- `jumlah_juri`
- `semua_dapat_medali`
- `kuota_peserta`

Gap awal CI4:

- `KategoriLombaModel::$allowedFields` masih terbatas pada `id_kategori_usia`, `jenis_perlombaan`, dan `kuota_peserta`.

### Sub Kategori Seni

Tabel: `sub_kategori_seni`

Field CI3 yang perlu dipastikan di CI4:

- `id_sub_kategori_seni`
- `id_kategori_lomba`
- `nama_seni`
- `jenis_seni`
- `jumlah_peserta`
- `waktu`
- `biaya_pendaftaran_dn`
- `biaya_pendaftaran_ln`
- `format_penilaian`
- `sistem_penampilan`
- `keterangan`

Gap awal CI4:

- `SubKategoriSeniModel::$allowedFields` terlihat sudah mencakup field utama, tetapi tetap perlu dibandingkan dengan schema DB aktual dan CI3 model.

## Validation Plan

Kategori usia:

- `nama_kategori_usia`: required.
- `jenis_kelamin`: required, domain sesuai CI3.
- `min_umur`: required, integer, minimal 0.
- `max_umur`: required, integer, tidak boleh lebih kecil dari `min_umur`.
- `acuan_tanggal`: required atau permit empty sesuai CI3, format tanggal valid.

Kategori lomba:

- `id_kategori_usia`: required, valid existing category.
- `nama_kategori_lomba`: required, domain sesuai CI3.
- `peraturan_pertandingan`: required.
- `jenis_perlombaan`: required.
- `jumlah_juri`: integer jika diisi.
- `semua_dapat_medali`: boolean/domain sesuai CI3.
- `kuota_peserta`: integer jika diisi.

Sub kategori seni:

- `id_kategori_lomba`: required, harus kategori lomba seni.
- `nama_seni`: required.
- `jenis_seni`: required, domain sesuai CI3.
- `jumlah_peserta`: required, integer lebih dari 0.
- `waktu`: integer jika diisi.
- `biaya_pendaftaran_dn`: numeric jika diisi.
- `biaya_pendaftaran_ln`: numeric jika diisi.
- `format_penilaian`: required.
- `sistem_penampilan`: required, domain sesuai CI3.
- `keterangan`: permit empty.
- `max_peserta`: required saat create jika otomatis pool membutuhkan nilai ini.

## Security Plan

- Semua form POST harus menyertakan `csrf_field()` karena CSRF global aktif.
- Route CRUD harus berada di balik `adminrole:super_admin`.
- Jika ada read-only route untuk sekretariat, pisahkan route/filter dan jangan campur akses CRUD.
- Semua output dari database atau input admin harus memakai `esc()` di view.
- Query harus memakai CI4 model/query builder, bukan SQL string concatenation.
- Delete kategori harus menangani dependency error tanpa menampilkan detail DB sensitif.
- Jangan log NIK, KK, bukti pembayaran, token, atau data sensitif lain saat debugging.

## Implementation Stages

### Stage 0 - Audit Detail

Tujuan:

- Baca source CI3 controller, model, dan view terkait.
- Cocokkan semua field CI3 dengan model CI4 dan schema DB.
- Tentukan route CI4 final dan kebutuhan alias route lama.
- Update `docs/migration-ci4-status.md` jika ada scope baru atau blocker.

Acceptance:

- Mapping source CI3 ke target CI4 selesai.
- Gap model/field terdokumentasi.
- Route/filter impact jelas.

### Stage 1 - Mode Super Admin dan Dashboard Pengaturan Event Read-only

Implementasi:

- `ModeController`
- `PengaturanEventController::dashboard`
- View menu tipe super admin.
- View dashboard pengaturan event.
- Side nav pengaturan event.

Behavior:

- Set session `tipe_super_admin` sesuai CI3.
- Dashboard menampilkan summary seperti CI3.
- Controller tetap tipis; query summary ditaruh di service/model.

Acceptance:

- Super admin bisa masuk mode pengaturan event.
- Dashboard tampil tanpa error.
- Role selain super admin tidak bisa mengakses route.

### Stage 2 - Kategori Usia CRUD

Implementasi:

- `KategoriUsiaController`
- View index, show, edit.
- Update `KategoriUsiaModel::$allowedFields` jika perlu.

Behavior:

- Create mendukung pilihan `jenis_kelamin[]` seperti CI3 jika CI3 membuat beberapa row.
- Update single row.
- Delete mengikuti CI3, tetapi harus graceful jika gagal karena dependency/FK.

Acceptance:

- Create, edit, detail, dan delete bekerja sesuai CI3.
- Form memakai CSRF.
- Output view sudah di-escape.

### Stage 3 - Kategori Lomba CRUD

Implementasi:

- `KategoriLombaController`
- View index dan edit.
- Update `KategoriLombaModel::$allowedFields` agar mencakup field CI3.

Behavior:

- Create mendukung banyak `id_kategori_usia[]` dan `nama_kategori_lomba[]` jika CI3 melakukannya.
- Update single kategori lomba.
- Delete mengikuti CI3, dengan error handling jika data sudah dipakai.

Acceptance:

- Data kategori lomba baru muncul di flow kontingen/sekretariat yang membaca kategori.
- Field lomba tersimpan sesuai CI3.
- Tidak ada mass assignment field yang tidak diizinkan.

### Stage 4 - Sub Kategori Seni CRUD dan Otomatis Pool

Implementasi:

- `SubKategoriSeniController`
- View index, show, edit.
- Service untuk otomatis pembuatan pool.

Behavior:

- Create sub kategori seni untuk kategori lomba seni.
- Pembuatan sub kategori dan pool otomatis harus dalam transaction.
- Jika pool gagal dibuat, insert sub kategori harus rollback.

Acceptance:

- Sub kategori seni dan pool otomatis terbentuk sesuai CI3.
- Tidak ada orphan sub kategori seni ketika proses pool gagal.
- Detail menampilkan data terkait dengan benar.

### Stage 5 - Form Pengaturan Event Lanjutan

Implementasi tahap lanjutan setelah CRUD kategori stabil:

- Profil kejuaraan.
- Gambar dan juknis.
- Akses pendaftaran peserta.
- Rekening pembayaran.
- Akses pemilihan kategori perlombaan.
- Konten landing.
- Video sponsor.
- Digital scoring tanding.

Catatan:

- Stage ini sebaiknya dipisahkan lagi karena menyentuh upload, pembayaran, public landing, dan pendaftaran.

## Verification Plan

Syntax check minimal untuk file yang disentuh:

```bash
php -l app/Config/Routes.php
php -l app/Controllers/Admin/Super/ModeController.php
php -l app/Controllers/Admin/Super/PengaturanEventController.php
php -l app/Controllers/Admin/Super/KategoriUsiaController.php
php -l app/Controllers/Admin/Super/KategoriLombaController.php
php -l app/Controllers/Admin/Super/SubKategoriSeniController.php
```

Test otomatis yang disarankan:

- Super admin bisa akses mode dan dashboard.
- Guest ditolak atau redirect login.
- Bendahara, sekretariat, dan kontingen ditolak dari CRUD super admin.
- Kategori usia create/update/delete valid dan invalid.
- Kategori lomba create/update/delete valid dan invalid.
- Sub kategori seni create/update/delete valid dan invalid.
- Pembuatan pool otomatis rollback saat gagal.

Manual QA:

- Login sebagai super admin.
- Buka menu tipe super admin.
- Masuk mode pengaturan event.
- Cek dashboard pengaturan event.
- Masuk mode pengaturan kategori lomba.
- Create/edit/delete kategori usia dummy.
- Create/edit/delete kategori lomba dummy.
- Create/edit/delete sub kategori seni dummy.
- Verifikasi pool otomatis.
- Verifikasi data kategori muncul di flow kontingen kategori tanding/seni.
- Coba akses route sebagai role selain super admin.

Jika test DB lokal belum siap, dokumentasikan blocker di `docs/migration-ci4-status.md`.

## Rollback Plan

Rollback code:

- Hapus route group `admin/super-admin` yang baru ditambahkan.
- Hapus controller, service, dan view baru untuk scope ini.
- Kembalikan perubahan model `allowedFields` jika belum dipakai flow lain.

Rollback data QA:

- Catat ID data dummy yang dibuat untuk:
  - `kategori_usia`
  - `kategori_lomba`
  - `sub_kategori_seni`
  - pool/kompetisi otomatis
- Hapus child rows terlebih dahulu jika FK aktif.
- Jangan hapus kategori yang sudah dipakai peserta, jadwal, pembayaran, atau bagan.

## Risks and Open Questions

- Route super admin final perlu dipastikan: pakai `admin/super-admin` atau mengikuti URL CI3 lama.
- Session mode typo `perngaturan_kategori_lomba` perlu dipertahankan jika masih dipakai menu lama.
- Delete kategori bisa berdampak ke peserta, kompetisi, jadwal, pembayaran, dan PDF.
- Otomatis pool wajib transaction untuk mencegah orphan rows.
- Model CI4 kategori belum lengkap terhadap field CI3.
- View CI3 mungkin memiliki asumsi layout/helper yang belum ada di CI4.
- Pengaturan event lanjutan sebaiknya menjadi plan lanjutan karena cakupannya lebih sensitif.

## Done Criteria

Plan ini dianggap selesai diimplementasikan jika:

- Source CI3 terkait sudah dipetakan ke target CI4 atau dicatat out of scope.
- Route super admin eksplisit dan dilindungi `adminrole:super_admin`.
- Super admin bisa memilih mode pengaturan event dan kategori lomba.
- Dashboard pengaturan event tampil sesuai CI3.
- CRUD kategori usia parity dengan CI3.
- CRUD kategori lomba parity dengan CI3.
- CRUD sub kategori seni dan otomatis pool parity dengan CI3.
- Semua form POST memakai CSRF.
- Semua output user/admin-controlled di view memakai escaping.
- Kontingen kategori tanding/seni tetap berfungsi setelah data baru dibuat.
- `php -l` pass untuk semua file PHP yang disentuh.
- `composer test` atau `vendor/bin/phpunit` dijalankan, atau blocker test DB didokumentasikan.
- `docs/migration-ci4-status.md` diperbarui sesuai status aktual.
