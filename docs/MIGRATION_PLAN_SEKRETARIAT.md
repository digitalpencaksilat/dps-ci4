# MIGRATION PLAN: CI3 → CI4 — Modul Peserta & Kontingen (Admin Sekretariat)

> **Source:** `/htdocs/dps/application/` (CodeIgniter 3)  
> **Target:** `/htdocs/dps-ci4/app/` (CodeIgniter 4)  
> **Role:** Admin Sekretariat  
> **Tanggal:** 24 Mei 2026

---

## 📋 Executive Summary

Migrasi modul Peserta Tanding, Peserta Seni, Kelompok Peserta Seni, dan Kontingen dari CodeIgniter 3 ke CodeIgniter 4 untuk role admin sekretariat. Total: **~38 file, ~6,650 lines** of code.

---

## 🔍 Kondisi Saat Ini

| Layer | CI3 (Source) | CI4 (Existing) | Status |
|-------|-------------|----------------|--------|
| **Controllers** | 4 file, ~1,780 lines | 1 DashboardController (placeholder) | ❌ Empty |
| **Models** | 4 file, ~907 lines | 4 shell (hanya table+pk) | ❌ Empty |
| **Migrations** | N/A | 0 file | ❌ Empty |
| **Routes** | ~30 routes under `resources/*` | 2 routes (`/` + `dashboard`) | ❌ Minimal |
| **Views** | ~15 view files | 0 view files | ❌ Empty |

---

## 📁 File Mapping

### Controllers

| CI3 (`application/controllers/resources/`) | CI4 (`app/Controllers/Admin/Sekretariat/`) | Lines |
|---|---|---|
| `Kontingen.php` | `KontingenController.php` | 719 |
| `Peserta_tanding.php` | `PesertaTandingController.php` | 492 |
| `Peserta_seni.php` | `PesertaSeniController.php` | 234 |
| `Kelompok_peserta_seni.php` | `KelompokPesertaSeniController.php` | 335 |

### Models

| CI3 (`application/models/resources/`) | CI4 (`app/Models/`) | Lines |
|---|---|---|
| `Kontingen_model.php` | `KontingenModel.php` | 557 |
| `Peserta_tanding_model.php` | `PesertaTandingModel.php` | 161 |
| `Peserta_seni_model.php` | `PesertaSeniModel.php` | 36 |
| `Kelompok_peserta_seni_model.php` | `KelompokPesertaSeniModel.php` | 153 |

### Views

| CI3 (`application/views/`) | CI4 (`app/Views/admin/sekretariat/`) |
|---|---|
| `shared_pages/kontingen/all_with_details.php` | `kontingen/all_with_details.php` |
| `shared_pages/kontingen/detail.php` | `kontingen/detail.php` |
| `shared_pages/kontingen/all.php` | `kontingen/rekap_atlet.php` |
| `shared_pages/peserta_tanding/all.php` | `peserta_tanding/all.php` |
| `shared_pages/peserta_tanding/export.php` | `peserta_tanding/export.php` |
| `shared_pages/peserta_tanding/detail.php` | `peserta_tanding/edit.php` |
| `shared_pages/peserta_tanding/pindah_pool.php` | `peserta_tanding/pindah_pool.php` |
| `shared_pages/peserta_tanding/edit_nomor_bagan.php` | `peserta_tanding/edit_nomor_bagan.php` |
| `shared_pages/peserta_seni/all.php` | `peserta_seni/all.php` |
| `shared_pages/peserta_seni/export.php` | `peserta_seni/export.php` |
| `shared_pages/kelompok_peserta_seni/all.php` | `kelompok_peserta_seni/all.php` |
| `shared_pages/kelompok_peserta_seni/detail.php` | `kelompok_peserta_seni/edit.php` |
| `shared_pages/kelompok_peserta_seni/pindah_pool.php` | `kelompok_peserta_seni/pindah_pool.php` |
| `admin/sekretariat/components/sidenav.php` | `components/sidenav.php` |
| `admin/sekretariat/components/topnav.php` | `components/topnav.php` |
| `admin/sekretariat/components/footer.php` | `components/footer.php` |
| `admin/sekretariat/components/detail_arsip_peserta.php` | `components/detail_arsip_peserta.php` |
| `admin/sekretariat/cetak_id_card_perkontingen.php` | `print/id_card_perkontingen.php` |
| `admin/sekretariat/cetak_id_card_perpeserta.php` | `print/id_card_perpeserta.php` |
| `print/kontingen/ringkasan_data.php` | `print/ringkasan_data.php` |
| `print/kontingen/arsip_pendaftar.php` | `print/arsip_pendaftar.php` |
| `print/kontingen/diagram_jadwal.php` | `print/diagram_jadwal.php` |
| `shared_pages/perolehan_medali_kontingen/*` | `medali/*` |

---

## 🔄 CI3 → CI4 Conversion Patterns

### Controller Patterns

| CI3 Pattern | CI4 Equivalent |
|---|---|
| `extends MY_Controller` | `extends BaseController` |
| `$this->load->model('Foo')` | `new FooModel()` or `model(FooModel::class)` |
| `$this->input->post('x')` | `$this->request->getPost('x')` |
| `$this->session->set_flashdata()` | `session()->setFlashdata()` |
| `$this->session->userdata('level')` | `session()->get('level')` |
| `$this->access_denied()` | `throw \CodeIgniter\Exceptions\PageNotFoundException` or 403 |
| `$this->not_found()` | `throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound()` |
| `$this->return_view($data)` | `return view('layout', $data)` |
| `redirect('foo')` | `return redirect()->to('foo')` |
| `$this->form_validation->run()` | `$this->validate($rules)` |
| `lang('key')` | `lang('Lang.key')` |
| `$this->config->item()` | `config('Group.item')` |
| `base_url('path')` | `base_url('path')` (same) |
| `$this->db->trans_begin()` | `$this->db->transStart()` |
| `$this->db->trans_commit()` | `$this->db->transComplete()` |
| `$this->db->trans_rollback()` | `$this->db->transRollback()` |

### Model Patterns

| CI3 Pattern | CI4 Equivalent |
|---|---|
| `extends MY_Model` | `extends CodeIgniter\Model` |
| `$this->table = 'foo'` | `protected $table = 'foo'` |
| `$this->primary_key = 'foo.id'` | `protected $primaryKey = 'id'` |
| `$this->order_by = 'foo.name'` | `protected $allowedFields = [...]` |
| `$this->db->select('...')->join(...)` | `$this->select('...')->join(...)` |
| `->result()` | `->getResult()` |
| `->result_array()` | `->getResultArray()` |
| `->row()` | `->getRow()` |
| `$this->find($id)` | `$this->find($id)` (built-in) |
| `$this->db->insert('t', $data)` | `$this->insert($data)` |
| `$this->db->update('t', $data, $where)` | `$this->update($id, $data)` |
| `$this->db->delete('t', $where)` | `$this->delete($id)` |
| `$this->db->affected_rows()` | `$this->db->affectedRows()` |

### View Patterns

| CI3 Pattern | CI4 Equivalent |
|---|---|
| `$this->load->view('template', $data)` | `return view('template', $data)` |
| `$data['main_view'] = 'pages/foo'` | nested `view('pages/foo', $data)` |
| `<?php echo $var; ?>` | `<?= esc($var) ?>` |
| `base_url('...')` | `base_url('...')` |
| `site_url('...')` | `site_url('...')` |

---

## 📐 Phase Detail

### Phase 1: Database Migrations (8 files)

Membuat tabel-tabel yang belum ada di CI4:

#### Migration #1: `CreateKontingenTable`
```sql
CREATE TABLE kontingen (
    id_kontingen INT AUTO_INCREMENT PRIMARY KEY,
    nama_kontingen VARCHAR(255) NOT NULL,
    email_kontingen VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    perguruan VARCHAR(100) DEFAULT 'ipsi',
    jenis_kontingen ENUM('dalam_negeri', 'luar_negeri') NOT NULL,
    negara VARCHAR(100) DEFAULT NULL,
    provinsi VARCHAR(150) DEFAULT NULL,
    kabupaten_kota VARCHAR(150) DEFAULT NULL,
    kecamatan VARCHAR(150) DEFAULT NULL,
    kelurahan VARCHAR(150) DEFAULT NULL,
    alamat_lengkap TEXT DEFAULT NULL,
    nama_penanggungjawab VARCHAR(255) DEFAULT NULL,
    jabatan_penanggungjawab VARCHAR(255) DEFAULT NULL,
    nomor_telepon_penanggungjawab VARCHAR(20) DEFAULT NULL,
    nomor_telepon_kontingen VARCHAR(20) DEFAULT NULL,
    id_pembayaran INT DEFAULT NULL,
    pembayaran_dn DECIMAL(10,2) DEFAULT 0,
    pembayaran_ln DECIMAL(10,2) DEFAULT 0,
    jenis_pendaftaran ENUM('web', 'excel', 'manual') DEFAULT 'web',
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### Migration #2: `CreatePesertaTandingTable`
```sql
CREATE TABLE peserta_tanding (
    id_peserta_tanding INT AUTO_INCREMENT PRIMARY KEY,
    id_pendaftar INT NOT NULL,
    id_kompetisi_tanding INT NOT NULL,
    id_pembayaran INT DEFAULT NULL,
    nomor_bagan INT DEFAULT NULL,
    keterangan TEXT DEFAULT NULL,
    nomor_sertifikat VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pendaftar) REFERENCES pendaftar(id_pendaftar) ON DELETE CASCADE,
    FOREIGN KEY (id_kompetisi_tanding) REFERENCES kompetisi_tanding(id_kompetisi_tanding) ON DELETE CASCADE
);
```

#### Migration #3: `CreateKelompokPesertaSeniTable`
```sql
CREATE TABLE kelompok_peserta_seni (
    id_kelompok_peserta_seni INT AUTO_INCREMENT PRIMARY KEY,
    id_kontingen INT NOT NULL,
    id_kompetisi_seni INT NOT NULL,
    id_pembayaran INT DEFAULT NULL,
    keterangan TEXT DEFAULT NULL,
    nomor_sertifikat VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kontingen) REFERENCES kontingen(id_kontingen) ON DELETE CASCADE,
    FOREIGN KEY (id_kompetisi_seni) REFERENCES kompetisi_seni(id_kompetisi_seni) ON DELETE CASCADE
);
```

#### Migration #4: `CreatePesertaSeniTable`
```sql
CREATE TABLE peserta_seni (
    id_peserta_seni INT AUTO_INCREMENT PRIMARY KEY,
    id_pendaftar INT NOT NULL,
    id_kelompok_peserta_seni INT NOT NULL,
    nomor_sertifikat VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pendaftar) REFERENCES pendaftar(id_pendaftar) ON DELETE CASCADE,
    FOREIGN KEY (id_kelompok_peserta_seni) REFERENCES kelompok_peserta_seni(id_kelompok_peserta_seni) ON DELETE CASCADE
);
```

#### Migration #5: `CreatePendaftarTable`
```sql
CREATE TABLE pendaftar (
    id_pendaftar INT AUTO_INCREMENT PRIMARY KEY,
    id_kontingen INT NOT NULL,
    nama_pendaftar VARCHAR(255) NOT NULL,
    jenis_kelamin ENUM('putra', 'putri') NOT NULL,
    tinggi_badan DECIMAL(5,2) NOT NULL,
    berat_badan DECIMAL(5,2) NOT NULL,
    tempat_lahir VARCHAR(255) NOT NULL,
    tanggal_lahir DATE NOT NULL,
    nama_sekolah VARCHAR(255) DEFAULT NULL,
    alamat TEXT DEFAULT NULL,
    nomor_induk_kependudukan VARCHAR(16) DEFAULT NULL UNIQUE,
    nomor_kartu_keluarga VARCHAR(16) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kontingen) REFERENCES kontingen(id_kontingen) ON DELETE CASCADE
);
```

#### Migration #6: `CreatePerolehanMedaliTandingTable`
```sql
CREATE TABLE perolehan_medali_tanding (
    id_perolehan_medali_tanding INT AUTO_INCREMENT PRIMARY KEY,
    id_peserta_tanding INT NOT NULL,
    jenis_medali ENUM('emas', 'perak', 'perunggu') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_peserta_tanding) REFERENCES peserta_tanding(id_peserta_tanding) ON DELETE CASCADE
);
```

#### Migration #7: `CreatePerolehanMedaliSeniTable`
```sql
CREATE TABLE perolehan_medali_seni (
    id_perolehan_medali_seni INT AUTO_INCREMENT PRIMARY KEY,
    id_kelompok_peserta_seni INT NOT NULL,
    jenis_medali ENUM('emas', 'perak', 'perunggu') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kelompok_peserta_seni) REFERENCES kelompok_peserta_seni(id_kelompok_peserta_seni) ON DELETE CASCADE
);
```

#### Migration #8: `CreatePembayaranTable`
```sql
CREATE TABLE pembayaran (
    id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
    id_kontingen INT NOT NULL,
    total_pembayaran DECIMAL(12,2) NOT NULL DEFAULT 0,
    status_pembayaran ENUM('menunggu', 'ditolak', 'lunas') DEFAULT 'menunggu',
    tanggal_pembayaran TIMESTAMP NULL DEFAULT NULL,
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kontingen) REFERENCES kontingen(id_kontingen) ON DELETE CASCADE
);
```

---

### Phase 2: Models (4 files, ~900 lines)

#### Model #1: `App/Models/KontingenModel.php`

**Source:** `application/models/resources/Kontingen_model.php` (557 lines)

**Methods to implement:**

| Method | Description | Returns |
|---|---|---|
| `select()` | Build join-based SELECT with subqueries for stats | `$this` |
| `select_rekap_bendahara()` | Build SELECT for bendahara report | `$this` |
| `create_instant($nama)` | Create kontingen on-the-fly | `int` |
| `get_progress_penginputan_kontingen()` | Get registration progress | `array` |
| `get_perolehan_medali_tanding()` | Get tanding medal per kontingen | `array` |
| `get_perolehan_medali_seni()` | Get seni medal per kontingen | `array` |
| `get_jumlah_kontingen_per_provinsi()` | Count kontingen by province | `array` |
| `get_jumlah_kontingen_per_kabupaten_kota()` | Count kontingen by regency | `array` |
| `get_all_data_diagram_jadwal($id)` | Get schedule diagram data | `array` |
| `get_akumulasi_medali()` | Get accumulated medals (all categories) | `array` |
| `get_akumulasi_medali_by_kategori_usia()` | Get medals by age category | `array` |
| `get_akumulasi_medali_berdasarkan_sekolah()` | Get medals by school name | `array` |
| `get_akumulasi_medali_eksklusif()` | Get exclusive medals (filtered) | `array` |
| `get_akumulasi_medali_by_kategori_usia_eksklusif()` | Get exclusive medals by category | `array` |

#### Model #2: `App/Models/PesertaTandingModel.php`

**Source:** `application/models/resources/Peserta_tanding_model.php` (161 lines)

**Methods to implement:**

| Method | Description | Returns |
|---|---|---|
| `select()` | Build complex SELECT with subqueries (status, medal, counts) | `$this` |
| `create($data)` | Insert with auto pool creation (transaction) | `int\|false` |
| `create_instant($nama, $jk, $kontingen, $id_kompetisi)` | Create on-the-fly for import | `int` |
| `update($where, $data, $autoPool)` | Update with optional auto pool (transaction) | `bool` |
| `get_rincian_jumlah_peserta_tanding()` | Get participant count per age category | `array` |

#### Model #3: `App/Models/PesertaSeniModel.php`

**Source:** `application/models/resources/Peserta_seni_model.php` (36 lines)

**Methods to implement:**

| Method | Description | Returns |
|---|---|---|
| `select()` | Build complex SELECT with GROUP_CONCAT (anggota, medal) | `$this` |

#### Model #4: `App/Models/KelompokPesertaSeniModel.php`

**Source:** `application/models/resources/Kelompok_peserta_seni_model.php` (153 lines)

**Methods to implement:**

| Method | Description | Returns |
|---|---|---|
| `select()` | Build complex SELECT with subqueries (pembayaran, anggota, medal, counts) | `$this` |
| `create_with_child_table($group, $pendaftar)` | Insert kelompok + peserta_seni rows (transaction) | `int\|false` |
| `get_rincian_jumlah_kelompok_peserta_seni()` | Get group count per age category | `array` |
| `create_penampilan_seni($id, $babak)` | Create penampilan_seni record | `int\|false` |

---

### Phase 3: Controllers (4 files, ~1,800 lines)

#### Controller #1: `App/Controllers/Admin/Sekretariat/KontingenController.php`

**Source:** `controllers/resources/Kontingen.php` (719 lines)

| Method | HTTP | Description |
|---|---|---|
| `index($id?)` | GET | List all kontingen or show detail |
| `rekapAtlet()` | GET | Rekap atlet per kontingen |
| `create()` | POST | Create new kontingen (by sekretariat) |
| `update($id)` | POST | Update kontingen data |
| `updatePassword($id)` | POST | Reset kontingen password |
| `delete($id)` | POST | Delete kontingen |
| `sendEmail($id)` | POST | Send custom email to kontingen |
| `ringkasanData($id)` | GET | Print kontingen summary (PDF) |
| `arsipPendaftar($id)` | GET | Print kontingen archive (PDF) |
| `idCard($id?)` | GET | Print ID cards (per kontingen) |
| `diagramJadwal($id)` | GET | Print schedule diagram (PDF) |
| `akumulasiMedali()` | GET | Accumulated medal table |
| `perolehanMedali()` | GET | Medal per age category |
| `medaliSekolah()` | GET | Medal by school |
| `akumulasiEksklusif()` | GET | Exclusive accumulated medals |
| `perolehanEksklusif()` | GET | Exclusive medals per category |
| `loadProvinsi()` | GET | Load province JSON |
| `loadKabupaten($id)` | GET | Load regency JSON |
| `loadKecamatan($id)` | GET | Load district JSON |
| `loadKelurahan($id)` | GET | Load village JSON |

#### Controller #2: `App/Controllers/Admin/Sekretariat/PesertaTandingController.php`

**Source:** `controllers/resources/Peserta_tanding.php` (492 lines)

| Method | HTTP | Description |
|---|---|---|
| `index()` | GET | List all peserta tanding |
| `export()` | GET | Export data peserta tanding |
| `create()` | POST | Add peserta to kompetisi tanding |
| `update($id)` | POST | Update peserta (change category) |
| `delete($id)` | POST | Delete peserta |
| `edit($id)` | GET | Edit form (change athlete/category) |
| `pindahPool($id)` | GET | Move peserta to different pool |
| `idCard($id)` | GET | Print single ID card |
| `sertifikat($id, $mode?)` | GET | Print/PDF certificate |
| `updateNomorBagan($id)` | POST | Update bagan number (super_admin only) |
| `editNomorBagan($id)` | GET | Edit bagan number form (super_admin only) |

#### Controller #3: `App/Controllers/Admin/Sekretariat/PesertaSeniController.php`

**Source:** `controllers/resources/Peserta_seni.php` (234 lines)

| Method | HTTP | Description |
|---|---|---|
| `index()` | GET | List all peserta seni |
| `export()` | GET | Export data peserta seni |
| `create()` | POST | Add peserta to kelompok seni |
| `update($id)` | POST | Update peserta (change pendaftar/kelompok) |
| `idCard($id)` | GET | Print single ID card |
| `sertifikat($id, $mode?)` | GET | Print/PDF certificate |

#### Controller #4: `App/Controllers/Admin/Sekretariat/KelompokPesertaSeniController.php`

**Source:** `controllers/resources/Kelompok_peserta_seni.php` (335 lines)

| Method | HTTP | Description |
|---|---|---|
| `index()` | GET | List all kelompok seni |
| `create()` | POST | Create kelompok + peserta (transaction) |
| `update($id)` | POST | Update kelompok (change kompetisi) |
| `delete($id)` | POST | Delete kelompok |
| `edit($id)` | GET | Edit form (change category/athletes) |
| `pindahPool($id)` | GET | Move kelompok to different pool |

---

### Phase 4: Views (~15 files, ~3,000 lines)

**Target structure:**
```
app/Views/admin/sekretariat/
├── dashboard.php
├── kontingen/
│   ├── all_with_details.php
│   ├── detail.php
│   └── rekap_atlet.php
├── peserta_tanding/
│   ├── all.php
│   ├── export.php
│   ├── edit.php
│   ├── pindah_pool.php
│   └── edit_nomor_bagan.php
├── peserta_seni/
│   ├── all.php
│   └── export.php
├── kelompok_peserta_seni/
│   ├── all.php
│   ├── edit.php
│   └── pindah_pool.php
├── print/
│   ├── id_card_perkontingen.php
│   ├── id_card_perpeserta.php
│   ├── ringkasan_data.php
│   ├── arsip_pendaftar.php
│   └── diagram_jadwal.php
├── medali/
│   ├── akumulasi.php
│   ├── per_kategori.php
│   └── berdasarkan_sekolah.php
└── components/
    ├── sidenav.php
    ├── topnav.php
    ├── footer.php
    └── detail_arsip_peserta.php
```

**Key conversions in views:**
- Replace `<?php echo $var; ?>` with `<?= esc($var) ?>`
- Replace `$this->session->flashdata('status')` with `session()->getFlashdata('status')`
- Replace `$this->session->userdata('level')` with `session()->get('level')`
- Replace CI3 form helpers with CI4 form helpers
- Update JS routes to use new URL paths

---

### Phase 5: Routes

Routes to register in `app/Config/Routes.php` (inside existing `admin/sekretariat` group):

```php
$routes->group('admin/sekretariat', ['filter' => 'adminrole:sekretariat'], static function ($routes): void {
    // Dashboard (existing)
    $routes->get('/', 'Admin\\Sekretariat\\DashboardController::index');
    $routes->get('dashboard', 'Admin\\Sekretariat\\DashboardController::index');
    
    // ============ KONTINGEN ============
    $routes->get('kontingen', 'Admin\\Sekretariat\\KontingenController::index');
    $routes->get('kontingen/rekap-atlet', 'Admin\\Sekretariat\\KontingenController::rekapAtlet');
    $routes->get('kontingen/(:num)', 'Admin\\Sekretariat\\KontingenController::index/$1');
    $routes->get('kontingen/(:num)/ringkasan', 'Admin\\Sekretariat\\KontingenController::ringkasanData/$1');
    $routes->get('kontingen/(:num)/arsip', 'Admin\\Sekretariat\\KontingenController::arsipPendaftar/$1');
    $routes->get('kontingen/(:num)/id-card', 'Admin\\Sekretariat\\KontingenController::idCard/$1');
    $routes->get('kontingen/(:num)/diagram-jadwal', 'Admin\\Sekretariat\\KontingenController::diagramJadwal/$1');
    $routes->post('kontingen/create', 'Admin\\Sekretariat\\KontingenController::create');
    $routes->post('kontingen/(:num)/update', 'Admin\\Sekretariat\\KontingenController::update/$1');
    $routes->post('kontingen/(:num)/update-password', 'Admin\\Sekretariat\\KontingenController::updatePassword/$1');
    $routes->post('kontingen/(:num)/delete', 'Admin\\Sekretariat\\KontingenController::delete/$1');
    $routes->post('kontingen/(:num)/kirim-email', 'Admin\\Sekretariat\\KontingenController::sendEmail/$1');
    
    // Medali
    $routes->get('perolehan-medali', 'Admin\\Sekretariat\\KontingenController::perolehanMedali');
    $routes->get('akumulasi-medali', 'Admin\\Sekretariat\\KontingenController::akumulasiMedali');
    $routes->get('medali-sekolah', 'Admin\\Sekretariat\\KontingenController::medaliSekolah');
    $routes->get('perolehan-medali-eksklusif', 'Admin\\Sekretariat\\KontingenController::perolehanEksklusif');
    $routes->get('akumulasi-medali-eksklusif', 'Admin\\Sekretariat\\KontingenController::akumulasiEksklusif');
    
    // ============ PESERTA TANDING ============
    $routes->get('peserta-tanding', 'Admin\\Sekretariat\\PesertaTandingController::index');
    $routes->get('peserta-tanding/export', 'Admin\\Sekretariat\\PesertaTandingController::export');
    $routes->get('peserta-tanding/(:num)/edit', 'Admin\\Sekretariat\\PesertaTandingController::edit/$1');
    $routes->get('peserta-tanding/(:num)/pindah-pool', 'Admin\\Sekretariat\\PesertaTandingController::pindahPool/$1');
    $routes->get('peserta-tanding/(:num)/id-card', 'Admin\\Sekretariat\\PesertaTandingController::idCard/$1');
    $routes->get('peserta-tanding/(:num)/sertifikat', 'Admin\\Sekretariat\\PesertaTandingController::sertifikat/$1');
    $routes->get('peserta-tanding/(:num)/sertifikat/(:any)', 'Admin\\Sekretariat\\PesertaTandingController::sertifikat/$1/$2');
    $routes->post('peserta-tanding/create', 'Admin\\Sekretariat\\PesertaTandingController::create');
    $routes->post('peserta-tanding/(:num)/update', 'Admin\\Sekretariat\\PesertaTandingController::update/$1');
    $routes->post('peserta-tanding/(:num)/delete', 'Admin\\Sekretariat\\PesertaTandingController::delete/$1');
    $routes->post('peserta-tanding/(:num)/update-nomor-bagan', 'Admin\\Sekretariat\\PesertaTandingController::updateNomorBagan/$1');
    
    // ============ PESERTA SENI ============
    $routes->get('peserta-seni', 'Admin\\Sekretariat\\PesertaSeniController::index');
    $routes->get('peserta-seni/export', 'Admin\\Sekretariat\\PesertaSeniController::export');
    $routes->get('peserta-seni/(:num)/id-card', 'Admin\\Sekretariat\\PesertaSeniController::idCard/$1');
    $routes->get('peserta-seni/(:num)/sertifikat', 'Admin\\Sekretariat\\PesertaSeniController::sertifikat/$1');
    $routes->get('peserta-seni/(:num)/sertifikat/(:any)', 'Admin\\Sekretariat\\PesertaSeniController::sertifikat/$1/$2');
    $routes->post('peserta-seni/create', 'Admin\\Sekretariat\\PesertaSeniController::create');
    $routes->post('peserta-seni/(:num)/update', 'Admin\\Sekretariat\\PesertaSeniController::update/$1');
    
    // ============ KELOMPOK PESERTA SENI ============
    $routes->get('kelompok-seni', 'Admin\\Sekretariat\\KelompokPesertaSeniController::index');
    $routes->get('kelompok-seni/(:num)/edit', 'Admin\\Sekretariat\\KelompokPesertaSeniController::edit/$1');
    $routes->get('kelompok-seni/(:num)/pindah-pool', 'Admin\\Sekretariat\\KelompokPesertaSeniController::pindahPool/$1');
    $routes->post('kelompok-seni/create', 'Admin\\Sekretariat\\KelompokPesertaSeniController::create');
    $routes->post('kelompok-seni/(:num)/update', 'Admin\\Sekretariat\\KelompokPesertaSeniController::update/$1');
    $routes->post('kelompok-seni/(:num)/delete', 'Admin\\Sekretariat\\KelompokPesertaSeniController::delete/$1');
    
    // ============ LOCATION HELPERS ============
    $routes->get('location/provinsi', 'Admin\\Sekretariat\\KontingenController::loadProvinsi');
    $routes->get('location/kabupaten/(:segment)', 'Admin\\Sekretariat\\KontingenController::loadKabupaten/$1');
    $routes->get('location/kecamatan/(:segment)', 'Admin\\Sekretariat\\KontingenController::loadKecamatan/$1');
    $routes->get('location/kelurahan/(:segment)', 'Admin\\Sekretariat\\KontingenController::loadKelurahan/$1');
});
```

---

### Phase 6: Services & Helpers (~6 files)

| CI3 Source | CI4 Target | Description |
|---|---|---|
| `libraries/Arsip_pendaftar_library.php` | `Services/ArsipPendaftarService.php` | Arkib pendaftar management |
| `libraries/Bukti_pembayaran_library.php` | `Services/BuktiPembayaranService.php` | Payment proof handling |
| `libraries/Pertandingan_library.php` | `Services/PertandinganService.php` | Match management |
| `libraries/Penilaian_seni_library.php` | `Services/PenilaianSeniService.php` | Artistic scoring |
| `libraries/Bagan_pertandingan_library.php` | `Services/BaganPertandinganService.php` | Bracket management |
| `helpers/kartu_peserta_helper` | `Helpers/kartu_peserta_helper.php` | ID card generation helper |

---

## 📊 Summary

| Phase | Files | Lines | Priority | Dependencies |
|-------|-------|-------|----------|-------------|
| 1. Migrations | 8 | ~400 | 🔴 HIGH | None |
| 2. Models | 4 | ~900 | 🔴 HIGH | Phase 1 |
| 3. Controllers | 4 | ~1,800 | 🔴 HIGH | Phase 2 |
| 4. Views | ~21 | ~3,000 | 🟡 MEDIUM | Phase 3 |
| 5. Routes | 1 | ~50 | 🟡 MEDIUM | Phase 3 |
| 6. Services | ~6 | ~500 | 🟢 LOW | Phase 2 |
| **TOTAL** | **~44** | **~6,650** | | |

---

## ⚠️ Notes & Risks

1. **Database compatibility**: CI3 source uses MySQL-specific queries (GROUP_CONCAT, subqueries in SELECT). CI4 supports these natively via Query Builder raw queries.
2. **Transaction handling**: CI3 uses `trans_begin/commit/rollback`, CI4 uses `transStart/transComplete/transRollback`. Auto-pool logic must be preserved.
3. **Config files**: CI3 uses `$this->config->item('key', 'file')`, CI4 uses `config('File.key')`. Need to ensure config files are ported.
4. **Language files**: CI3 uses `lang('key')`, CI4 uses `lang('Lang.key')`. All `lang()` calls need namespace prefix.
5. **Session**: CI3: `$this->session->userdata('key')`, CI4: `session()->get('key')`.
6. **Validation**: CI3 uses config files for validation rules, CI4 uses inline arrays. Must convert all validation rule sets.
7. **PDF printing**: Templates in `print/` folder use direct HTML→PDF printing. Must preserve paper_size, layout, and QR code generation.
8. **Medal queries**: Complex subquery-based medal queries in Kontingen_model must be carefully converted — they are the backbone of the scoring system.
9. **Location JSON**: Province/regency/district/village JSON files in `assets/location/` — ensure these are copied to CI4 public/assets.

---

## ✅ Verification Checklist

Per phase, verify:

- [ ] **Phase 1**: `php spark migrate:status` shows all migrations
- [ ] **Phase 2**: `php spark db:table kontingen` shows correct structure
- [ ] **Phase 2**: Model unit tests pass (create, read, update, delete)
- [ ] **Phase 3**: All routes return 200 for GET, redirect for POST
- [ ] **Phase 3**: Auth filter correctly restricts access to 'sekretariat' only
- [ ] **Phase 4**: Views render without PHP errors
- [ ] **Phase 4**: All JS/jQuery interactions work on new URL paths
- [ ] **Phase 5**: `php spark routes` shows all registered routes
- [ ] **Phase 6**: Service method calls return expected data types
- [ ] **Full**: End-to-end flow: login → dashboard → kontingen → peserta → medali → print
