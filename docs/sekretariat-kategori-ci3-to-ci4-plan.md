# Sekretariat Kategori CI3 to CI4 Migration Plan

Plan ini dibuat berdasarkan scan source CI3 di `htdocs/dps` dan kondisi CI4 di `htdocs/dps-ci4`.

Scope migrasi:

- Daftar kelas tanding
- Daftar pool tanding
- Daftar pertandingan tanding
- Kuota kelas prestasi tanding
- Daftar kategori seni
- Daftar pool seni
- Daftar penampilan sistem pool
- Daftar battle seni
- Kuota kelas prestasi seni

## Prinsip Utama

- Gunakan database yang sudah ada.
- Jangan membuat migration database baru.
- Jangan mengubah schema tabel.
- Jangan rename table atau column.
- Jangan membuat seeder/backfill.
- Porting dilakukan pada controller, model/query, service, view, routes, dan menu CI4.
- Behavior lama dari CI3 harus dijadikan acuan.

## Source Map CI3

| Modul | CI3 Controller | CI3 Model | CI3 View Utama |
|---|---|---|---|
| Daftar kelas tanding | `application/controllers/resources/Kelas_tanding.php` | `application/models/resources/Kelas_tanding_model.php` | `application/views/shared_pages/kelas_tanding/*` |
| Daftar pool tanding | `application/controllers/resources/Kompetisi_tanding.php` | `application/models/resources/Kompetisi_tanding_model.php` | `application/views/shared_pages/kompetisi_tanding/*` |
| Daftar pertandingan tanding | `application/controllers/resources/Pertandingan.php` | `application/models/resources/Pertandingan_model.php` | `application/views/shared_pages/pertandingan/*` |
| Kuota kelas prestasi tanding | `Kelas_tanding::kuota_kelas_prestasi_tanding()` | `Kelas_tanding_model` | `shared_pages/kelas_tanding/kuota_kelas_prestasi` |
| Daftar kategori seni | `application/controllers/resources/Sub_kategori_seni.php` | `application/models/resources/Sub_kategori_seni_model.php` | `application/views/shared_pages/sub_kategori_seni/*` |
| Daftar pool seni | `application/controllers/resources/Kompetisi_seni.php` | `application/models/resources/Kompetisi_seni_model.php` | `application/views/shared_pages/kompetisi_seni/*` |
| Daftar penampilan sistem pool | `Sub_kategori_seni::edit/update()` | `Sub_kategori_seni_model` | `shared_pages/sub_kategori_seni/edit` |
| Daftar battle seni | `Kompetisi_seni::drawing_seni_battle_prestasi()` and detail pool battle | `Battle_seni_model`, `Kompetisi_seni_model` | `admin/sekretariat/drawing_seni_battle_prestasi`, `shared_pages/kompetisi_seni/detail` |
| Kuota kelas prestasi seni | `Sub_kategori_seni::kuota_kelas_prestasi_seni()` | `Sub_kategori_seni_model` | `shared_pages/sub_kategori_seni/kuota_kelas_prestasi` |

## Existing Tables Used

Tanding:

```text
kategori_usia
kategori_lomba
kelas_tanding
kompetisi_tanding
peserta_tanding
pertandingan
jadwal_tanding
perolehan_medali_tanding
penilaian_tanding
```

Seni:

```text
kategori_usia
kategori_lomba
sub_kategori_seni
kompetisi_seni
kelompok_peserta_seni
peserta_seni
penampilan_seni
battle_seni
detail_jadwal_seni
jadwal_seni
perolehan_medali_seni
penilaian_seni
```

## Current CI4 State

Already available in `dps-ci4`:

- `PesertaTandingController`
- `KelompokPesertaSeniController`
- `PesertaTandingModel`
- `KelompokPesertaSeniModel`
- `PesertaSeniModel`
- `KategoriTandingService`
- `KategoriSeniService`
- `SekretariatPesertaKontingenService`
- peserta tanding and kelompok seni views
- routes for peserta tanding and kelompok seni

Missing in `dps-ci4`:

- CI4 model for `kelas_tanding`
- CI4 model for `kompetisi_tanding`
- CI4 model for `pertandingan`
- CI4 model for `sub_kategori_seni`
- CI4 model for `kompetisi_seni`
- CI4 model for `battle_seni`
- admin sekretariat controllers for category, pool, pertandingan, and battle modules
- admin sekretariat views for category, pool, pertandingan, and battle modules
- sidebar menu for kategori tanding/seni modules

## Target CI4 Architecture

### Models

```text
app/Models/KategoriUsiaModel.php
app/Models/KategoriLombaModel.php
app/Models/KelasTandingModel.php
app/Models/KompetisiTandingModel.php
app/Models/PertandinganModel.php
app/Models/SubKategoriSeniModel.php
app/Models/KompetisiSeniModel.php
app/Models/BattleSeniModel.php
app/Models/PenampilanSeniModel.php
```

`PenampilanSeniModel` is needed because CI3 battle seni creates and links `penampilan_seni` records.

### Services

```text
app/Services/SekretariatKategoriTandingService.php
app/Services/SekretariatKategoriSeniService.php
```

### Controllers

```text
app/Controllers/Admin/Sekretariat/KelasTandingController.php
app/Controllers/Admin/Sekretariat/PoolTandingController.php
app/Controllers/Admin/Sekretariat/PertandinganTandingController.php
app/Controllers/Admin/Sekretariat/KuotaPrestasiTandingController.php

app/Controllers/Admin/Sekretariat/KategoriSeniAdminController.php
app/Controllers/Admin/Sekretariat/PoolSeniController.php
app/Controllers/Admin/Sekretariat/SistemPoolSeniController.php
app/Controllers/Admin/Sekretariat/BattleSeniController.php
app/Controllers/Admin/Sekretariat/KuotaPrestasiSeniController.php
```

### Views

```text
app/Views/admin/sekretariat/kelas_tanding/
app/Views/admin/sekretariat/pool_tanding/
app/Views/admin/sekretariat/pertandingan_tanding/
app/Views/admin/sekretariat/kuota_prestasi_tanding/

app/Views/admin/sekretariat/kategori_seni/
app/Views/admin/sekretariat/pool_seni/
app/Views/admin/sekretariat/sistem_pool_seni/
app/Views/admin/sekretariat/battle_seni/
app/Views/admin/sekretariat/kuota_prestasi_seni/
```

## Route Plan

Add routes inside the existing `admin/sekretariat` group in `app/Config/Routes.php`.

```php
// Kategori Tanding
$routes->get('kelas-tanding', 'Admin\\Sekretariat\\KelasTandingController::index');
$routes->get('kelas-tanding/(:num)', 'Admin\\Sekretariat\\KelasTandingController::show/$1');
$routes->post('kelas-tanding', 'Admin\\Sekretariat\\KelasTandingController::store');
$routes->post('kelas-tanding/(:num)/update', 'Admin\\Sekretariat\\KelasTandingController::update/$1');
$routes->post('kelas-tanding/(:num)/delete', 'Admin\\Sekretariat\\KelasTandingController::delete/$1');

$routes->get('pool-tanding', 'Admin\\Sekretariat\\PoolTandingController::index');
$routes->get('pool-tanding/(:num)', 'Admin\\Sekretariat\\PoolTandingController::show/$1');
$routes->post('pool-tanding/(:num)/update', 'Admin\\Sekretariat\\PoolTandingController::update/$1');

$routes->get('pertandingan-tanding', 'Admin\\Sekretariat\\PertandinganTandingController::index');
$routes->get('pertandingan-tanding/(:num)', 'Admin\\Sekretariat\\PertandinganTandingController::show/$1');
$routes->post('pertandingan-tanding', 'Admin\\Sekretariat\\PertandinganTandingController::store');
$routes->post('pertandingan-tanding/(:num)/update', 'Admin\\Sekretariat\\PertandinganTandingController::update/$1');
$routes->post('pertandingan-tanding/(:num)/delete', 'Admin\\Sekretariat\\PertandinganTandingController::delete/$1');

$routes->get('kuota-prestasi-tanding', 'Admin\\Sekretariat\\KuotaPrestasiTandingController::index');

// Kategori Seni
$routes->get('kategori-seni', 'Admin\\Sekretariat\\KategoriSeniAdminController::index');
$routes->get('kategori-seni/(:num)', 'Admin\\Sekretariat\\KategoriSeniAdminController::show/$1');
$routes->get('kategori-seni/(:num)/edit', 'Admin\\Sekretariat\\KategoriSeniAdminController::edit/$1');
$routes->post('kategori-seni', 'Admin\\Sekretariat\\KategoriSeniAdminController::store');
$routes->post('kategori-seni/(:num)/update', 'Admin\\Sekretariat\\KategoriSeniAdminController::update/$1');
$routes->post('kategori-seni/(:num)/delete', 'Admin\\Sekretariat\\KategoriSeniAdminController::delete/$1');

$routes->get('pool-seni', 'Admin\\Sekretariat\\PoolSeniController::index');
$routes->get('pool-seni/(:num)', 'Admin\\Sekretariat\\PoolSeniController::show/$1');
$routes->post('pool-seni/(:num)/update', 'Admin\\Sekretariat\\PoolSeniController::update/$1');
$routes->post('pool-seni/(:num)/beri-nomor-undi', 'Admin\\Sekretariat\\PoolSeniController::beriNomorUndi/$1');

$routes->get('sistem-pool-seni', 'Admin\\Sekretariat\\SistemPoolSeniController::index');
$routes->post('sistem-pool-seni/(:num)/update', 'Admin\\Sekretariat\\SistemPoolSeniController::update/$1');

$routes->get('battle-seni', 'Admin\\Sekretariat\\BattleSeniController::index');
$routes->get('battle-seni/(:num)', 'Admin\\Sekretariat\\BattleSeniController::show/$1');

$routes->get('kuota-prestasi-seni', 'Admin\\Sekretariat\\KuotaPrestasiSeniController::index');
```

Route final can be reduced during implementation. CI3 role behavior should be preserved where possible.

## Role Behavior From CI3

- `kelas_tanding create/delete`: `super_admin`
- `sub_kategori_seni create/delete/update`: mostly `super_admin`
- `kompetisi_tanding update`: `sekretariat`
- `pertandingan create/update/delete`: `sekretariat` or `super_admin`
- drawing routes: `sekretariat`

Safe CI4 default for sekretariat:

- can view master category data
- can update pool/keterangan/nomor pool/max peserta where CI3 allowed sekretariat
- can manage pertandingan/drawing where CI3 allowed sekretariat
- should not create/delete master categories unless explicitly approved

## Sidebar Plan

Update `app/Views/layouts/admin.php`.

Proposed menu:

```text
Kategori Tanding
- Daftar Kelas Tanding
- Daftar Pool
- Daftar Pertandingan
- Kuota Kelas Prestasi

Kategori Seni
- Daftar Kategori Seni
- Daftar Pool Seni
- Sistem Penampilan Pool
- Daftar Battle Seni
- Kuota Kelas Prestasi Seni
```

Active menu keys:

```text
kelas_tanding
pool_tanding
pertandingan_tanding
kuota_prestasi_tanding
kategori_seni_admin
pool_seni
sistem_pool_seni
battle_seni
kuota_prestasi_seni
```

## Module Details

### Daftar Kelas Tanding

CI3 source:

- `resources/Kelas_tanding.php`
- `resources/Kelas_tanding_model.php`
- `shared_pages/kelas_tanding/all`
- `shared_pages/kelas_tanding/detail`

Target CI4:

- `KelasTandingController`
- `KelasTandingModel`
- `SekretariatKategoriTandingService`
- `admin/sekretariat/kelas_tanding/index.php`
- `admin/sekretariat/kelas_tanding/show.php`

Data to show from CI3 `Kelas_tanding_model::select()`:

- `kelas_tanding.*`
- kategori lomba
- kategori usia
- jumlah peserta tanding
- jumlah peserta tanding lunas
- prediksi jumlah partai
- total max peserta from all pools
- jumlah partai tanding
- jumlah partai belum dijadwalkan
- jumlah pool

Initial target:

- list/detail read-only
- create/update/delete deferred unless approved

### Daftar Pool Tanding

CI3 source:

- `resources/Kompetisi_tanding.php`
- `resources/Kompetisi_tanding_model.php`
- `shared_pages/kompetisi_tanding/all`
- `shared_pages/kompetisi_tanding/detail`
- `admin/sekretariat/drawing_tanding_prestasi`
- `shared_components/kompetisi_tanding/acak_bagan_manual`

Target CI4:

- `PoolTandingController`
- `KompetisiTandingModel`
- `SekretariatKategoriTandingService`
- `admin/sekretariat/pool_tanding/index.php`
- `admin/sekretariat/pool_tanding/show.php`

Fields updateable by sekretariat in CI3:

```text
max_peserta
perhitungan_medali
nomor_pool
keterangan
```

Initial target:

- list/detail/update pool
- drawing bracket deferred to later phase

### Daftar Pertandingan Tanding

CI3 source:

- `resources/Pertandingan.php`
- `resources/Pertandingan_model.php`
- `shared_pages/pertandingan/all`
- `shared_pages/pertandingan/detail`
- `shared_pages/pertandingan/pertandingan_instant`
- `shared_pages/pertandingan/urutan_poin_pertandingan`

Target CI4:

- `PertandinganTandingController`
- `PertandinganModel`
- `SekretariatKategoriTandingService`
- `admin/sekretariat/pertandingan_tanding/index.php`
- `admin/sekretariat/pertandingan_tanding/show.php`
- `admin/sekretariat/pertandingan_tanding/_form.php`

Initial target:

- list/detail/manual create/update
- scoring integration deferred

### Kuota Kelas Prestasi Tanding

CI3 source:

- `Kelas_tanding::kuota_kelas_prestasi_tanding()`
- `Kelas_tanding_model`
- `shared_pages/kelas_tanding/kuota_kelas_prestasi`

Target CI4:

- `KuotaPrestasiTandingController`
- `admin/sekretariat/kuota_prestasi_tanding/index.php`

Data groups:

- tersedia: `jumlah_peserta_tanding < max_peserta`
- penuh: `jumlah_peserta_tanding = max_peserta`
- kelebihan: `jumlah_peserta_tanding > max_peserta`
- total peserta prestasi
- prediksi jumlah partai

Initial target:

- read-only dashboard

### Daftar Kategori Seni

CI3 source:

- `resources/Sub_kategori_seni.php`
- `resources/Sub_kategori_seni_model.php`
- `shared_pages/sub_kategori_seni/all`
- `shared_pages/sub_kategori_seni/detail`
- `shared_pages/sub_kategori_seni/edit`

Target CI4:

- `KategoriSeniAdminController`
- `SubKategoriSeniModel`
- `SekretariatKategoriSeniService`
- `admin/sekretariat/kategori_seni/index.php`
- `admin/sekretariat/kategori_seni/show.php`
- `admin/sekretariat/kategori_seni/edit.php`

Fields updated by CI3 `Sub_kategori_seni::update()`:

```text
nama_seni
jumlah_peserta
waktu
biaya_pendaftaran_dn
biaya_pendaftaran_ln
format_penilaian
sistem_penampilan
keterangan
```

Initial target:

- list/detail
- edit `sistem_penampilan` through `SistemPoolSeniController`
- create/delete deferred

### Daftar Pool Seni

CI3 source:

- `resources/Kompetisi_seni.php`
- `resources/Kompetisi_seni_model.php`
- `shared_pages/kompetisi_seni/all`
- `shared_pages/kompetisi_seni/detail`
- `admin/sekretariat/drawing_seni_pool_prestasi`
- `admin/sekretariat/drawing_seni_battle_prestasi`

Target CI4:

- `PoolSeniController`
- `KompetisiSeniModel`
- `SekretariatKategoriSeniService`
- `admin/sekretariat/pool_seni/index.php`
- `admin/sekretariat/pool_seni/show.php`

Initial target:

- list/detail/nomor undi
- drawing battle/pool deferred

### Sistem Penampilan Pool

CI3 source:

- `sub_kategori_seni.sistem_penampilan`
- `Sub_kategori_seni::update()`
- `shared_pages/sub_kategori_seni/edit`

Target CI4:

- `SistemPoolSeniController`
- `SubKategoriSeniModel`
- `admin/sekretariat/sistem_pool_seni/index.php`

Known values:

```text
battle
pool
```

Initial target:

- list/update with warning if related battle/jadwal/penampilan data exists

### Daftar Battle Seni

CI3 source:

- `Kompetisi_seni::drawing_seni_battle_prestasi()`
- `Kompetisi_seni::halaman_acak_bagan_manual()`
- `Kompetisi_seni::buat_bagan_manual()`
- `Battle_seni_model`
- `Kompetisi_seni_model`
- `admin/sekretariat/drawing_seni_battle_prestasi`
- `shared_pages/kompetisi_seni/detail`
- `shared_components/kompetisi_seni/acak_bagan_manual`

Target CI4:

- `BattleSeniController`
- `BattleSeniModel`
- `SekretariatKategoriSeniService`
- `admin/sekretariat/battle_seni/index.php`
- `admin/sekretariat/battle_seni/show.php`

Initial target:

- list/detail
- drawing deferred to later phase

### Kuota Kelas Prestasi Seni

CI3 source:

- `Sub_kategori_seni::kuota_kelas_prestasi_seni()`
- `Sub_kategori_seni_model`
- `shared_pages/sub_kategori_seni/kuota_kelas_prestasi`

Target CI4:

- `KuotaPrestasiSeniController`
- `admin/sekretariat/kuota_prestasi_seni/index.php`

Data groups:

- tersedia: `jumlah_kelompok_peserta_seni < total_kapasitas_kelompok_peserta_seni`
- penuh: `jumlah_kelompok_peserta_seni = total_kapasitas_kelompok_peserta_seni`
- kelebihan: `jumlah_kelompok_peserta_seni > total_kapasitas_kelompok_peserta_seni`
- total kelompok seni prestasi

Initial target:

- read-only dashboard

## Implementation Milestones

### Milestone 1: Model and Service Read Layer

Create CI4 models:

```text
KelasTandingModel
KompetisiTandingModel
PertandinganModel
SubKategoriSeniModel
KompetisiSeniModel
BattleSeniModel
PenampilanSeniModel
KategoriLombaModel
KategoriUsiaModel
```

Create services:

```text
SekretariatKategoriTandingService
SekretariatKategoriSeniService
```

Goal:

- CI3 list/detail queries can be called from CI4
- no complex write actions yet

### Milestone 2: Tanding Read UI

Implement:

- `KelasTandingController`
- `PoolTandingController`
- `PertandinganTandingController`
- `KuotaPrestasiTandingController`
- list/detail/kuota views
- routes
- sidebar

Goal:

- tanding menu appears in sekretariat
- existing database data appears correctly
- detail pages open correctly

### Milestone 3: Tanding Actions

Implement safe actions:

- update pool tanding fields: `max_peserta`, `perhitungan_medali`, `nomor_pool`, `keterangan`
- create/update pertandingan manual
- delete pertandingan only if safe
- no master kelas CRUD unless approved

### Milestone 4: Seni Read UI

Implement:

- `KategoriSeniAdminController`
- `PoolSeniController`
- `BattleSeniController`
- `KuotaPrestasiSeniController`
- list/detail/kuota views
- routes
- sidebar

Goal:

- kategori seni, pool seni, battle seni, and kuota seni show data from existing database

### Milestone 5: Seni Actions

Implement safe actions:

- update `sistem_penampilan`
- beri nomor undi pool seni
- update pool seni if needed
- battle list/detail
- drawing deferred until read UI is stable

### Milestone 6: Drawing and Bagan

Port complex logic from CI3:

Tanding:

- `Kompetisi_tanding_model::acak_bagan_tanding()`
- `Kompetisi_tanding_model::buat_bagan_manual()`
- `Kompetisi_tanding_model::simpan_bagan_dan_data_pertandingan()`

Seni:

- `Kompetisi_seni_model::acak_bagan_battle_seni()`
- `Kompetisi_seni_model::buat_bagan_manual()`
- `Kompetisi_seni_model::simpan_bagan_dan_data_battle_seni()`

This phase touches `pertandingan`, `battle_seni`, `penampilan_seni`, and scoring-related tables. Do it after list/detail/action basics are stable.

## Safety Constraints

- Do not create database migrations.
- Do not change table structure.
- Do not refactor scoring modules during initial migration.
- Do not implement drawing before list/detail data is verified.
- Do not delete master data already used by participants or schedules.
- Do not change `sistem_penampilan` if battle/jadwal data exists without a warning and explicit confirmation.

## Known Risks

- CI3 models use many complex subqueries and aliases; CI4 queries must preserve expected alias names.
- Battle seni depends on `penampilan_seni`, not only `battle_seni`.
- Pertandingan tanding depends on `detail_jadwal_tanding`, `jadwal_tanding`, and `penilaian_tanding`.
- CI3 role behavior is mixed between `sekretariat` and `super_admin`; preserve restrictions unless the product decision says otherwise.
- CI3 views cannot be copied directly; they need conversion to CI4 `layouts/admin` and current admin UI style.

## Acceptance Criteria

A module is complete when:

- route exists inside `admin/sekretariat`
- sidebar menu exists
- controller has required `index/show` actions
- model reads existing table without schema changes
- service output matches CI3 behavior
- view displays main data
- view output uses `esc()`
- POST actions use CSRF
- `php -l` passes for touched PHP files
- manual QA list/detail/action is recorded
- no database migration was added

## Recommended Execution Order

1. Build models and read services for tanding/seni master data.
2. Implement read-only pages for:
   - daftar kelas tanding
   - daftar pool tanding
   - kuota kelas prestasi tanding
   - daftar kategori seni
   - daftar pool seni
   - kuota kelas prestasi seni
3. Implement read-only pages for:
   - daftar pertandingan tanding
   - daftar battle seni
4. Add safe update actions:
   - pool tanding
   - sistem penampilan seni
   - nomor undi seni
5. Port drawing/bagan logic only after read UI is stable.
