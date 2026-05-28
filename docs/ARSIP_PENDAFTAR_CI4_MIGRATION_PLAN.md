# Migrasi Arsip Pendaftar ke CodeIgniter 4 - Planning Detail

**Status**: Planning  
**Priority**: High  
**Scope**: Complete feature parity dengan CI3 + CI4 best practices  
**Estimasi**: 6-8 jam development + 2-3 jam QA  

---

## 1. OVERVIEW MIGRASI

### Current State (CI3)
- Settings: Database (`site_builder_settings`) + Config file fallback
- Upload: `Arsip_pendaftar` controller (create/edit/delete)
- Validation: Helper function `validate_arsip_upload()`
- Views: Separate views untuk settings, upload, display

### Target State (CI4)
- Settings: Database-first dengan config fallback (sudah ada ✅)
- Upload: Dedicated `ArsipPendaftarController` dengan proper CI4 patterns
- Validation: Service-based validation dengan helper wrapper
- Views: Modern CI4 views dengan proper escaping & accessibility
- Architecture: Service layer + Model + Controller separation

---

## 2. CURRENT CI4 IMPLEMENTATION STATUS

### ✅ SUDAH LENGKAP
| Komponen | File | Status |
|----------|------|--------|
| Routes (Settings) | `app/Config/Routes.php:138-142` | ✅ Complete |
| Controller (Settings) | `app/Controllers/Admin/Super/ArsipPendaftarSettingsController.php` | ✅ Complete |
| View (Settings) | `app/Views/admin/super/pengaturan_event/arsip_pendaftar.php` | ✅ Complete |
| Helper (Config) | `app/Helpers/arsip_pendaftar_helper.php` | ✅ Complete |
| Model (Archive) | `app/Models/ArsipPendaftarModel.php` | ✅ Complete |
| Service (Upload) | `app/Services/ArsipPendaftarService.php` | ✅ Complete |
| Setting Writer | `app/Services/Admin/Super/SettingWriterService.php` | ✅ Complete |

### ⚠️ GAPS & MISSING FEATURES

#### Gap 1: Toggle Active Status Endpoint
- **CI3**: `Super_admin::toggle_slot_active()` (line 1015-1047)
- **CI4**: Missing
- **Impact**: Cannot toggle slot active/inactive without full edit
- **Fix**: Add `toggleActive()` method to `ArsipPendaftarSettingsController`

#### Gap 2: Upload/Edit/Delete Controller
- **CI3**: `Arsip_pendaftar::create()`, `edit()`, `delete()` (lines 12-117)
- **CI4**: Missing dedicated controller
- **Impact**: Peserta tidak bisa upload arsip
- **Fix**: Create `ArsipPendaftarController` with proper CI4 patterns

#### Gap 3: Validation Helper Wrapper
- **CI3**: `validate_arsip_upload($slot_name, $file_data)` helper
- **CI4**: Validation ada di service, tidak ter-expose sebagai helper
- **Impact**: Cannot validate outside service context
- **Fix**: Add helper wrapper `validate_arsip_upload_ci4()`

#### Gap 4: View Integration
- **CI3**: Separate views untuk settings, upload, display
- **CI4**: Settings view ada ✅, upload view ada ✅, tapi perlu verify integration
- **Impact**: Need to verify arsip slots are properly passed to forms
- **Fix**: Verify & enhance view data passing

---

## 3. DETAILED IMPLEMENTATION PLAN

### PHASE 1: Toggle Active Status (Priority: HIGH)
**Objective**: Enable real-time slot activation/deactivation  
**Effort**: 1 hour

#### 1.1 Add Route
**File**: `app/Config/Routes.php`
```php
// Line 142 (after delete route)
$routes->post('pengaturan-event/arsip-pendaftar/toggle', 'Admin\\Super\\ArsipPendaftarSettingsController::toggleActive');
```

#### 1.2 Add Controller Method
**File**: `app/Controllers/Admin/Super/ArsipPendaftarSettingsController.php`
```php
public function toggleActive()
{
    $slotName = trim((string) $this->request->getPost('slot_name'));
    $active = (bool) ((int) ($this->request->getPost('active') ?? 0));
    
    if ($slotName === '') {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Slot name tidak boleh kosong'
        ]);
    }
    
    $slots = get_arsip_pendaftar_config_ci4();
    if (!array_key_exists($slotName, $slots)) {
        return $this->response->setJSON([
            'status' => false,
            'message' => 'Slot arsip tidak ditemukan'
        ]);
    }
    
    $slots[$slotName]['active'] = $active;
    (new SettingWriterService())->setString('arsip_pendaftar_slots', json_encode($slots));
    
    return $this->response->setJSON([
        'status' => true,
        'message' => 'Status slot berhasil diubah',
        'active' => $active
    ]);
}
```

#### 1.3 Update View
**File**: `app/Views/admin/super/pengaturan_event/arsip_pendaftar.php`
- Add toggle checkbox in table row (line 49)
- Add AJAX handler for toggle in script section

---

### PHASE 2: Upload/Edit/Delete Controller (Priority: HIGH)
**Objective**: Enable peserta to upload/manage arsip  
**Effort**: 2.5 hours

#### 2.1 Create New Controller
**File**: `app/Controllers/ArsipPendaftarController.php`

**Responsibilities**:
- `create()` - POST upload arsip baru
- `update($id)` - POST ganti arsip existing
- `delete($id)` - POST hapus arsip
- Role-based access (kontingen + sekretariat)
- File validation & storage

**Key Features**:
- Validate user owns the peserta (kontingen) or is sekretariat
- Use `ArsipPendaftarService::syncUploads()` for upload
- Return JSON for AJAX or redirect for form submission
- Proper error handling & user feedback

**Pseudo-code**:
```php
namespace App\Controllers;

use App\Models\ArsipPendaftarModel;
use App\Models\PendaftarModel;
use App\Services\ArsipPendaftarService;

class ArsipPendaftarController extends BaseController
{
    public function create()
    {
        // Validate peserta exists & user has access
        // Validate files via ArsipPendaftarService
        // Store files
        // Return success/error
    }
    
    public function update(int $id)
    {
        // Validate arsip exists & user has access
        // Validate new file
        // Replace old file
        // Return success/error
    }
    
    public function delete(int $id)
    {
        // Validate arsip exists & user has access
        // Delete physical file
        // Delete DB record
        // Return success/error
    }
}
```

#### 2.2 Add Routes
**File**: `app/Config/Routes.php`
```php
// Peserta arsip management (kontingen & sekretariat)
$routes->post('peserta/(:num)/arsip', 'ArsipPendaftarController::create/$1');
$routes->post('peserta/(:num)/arsip/(:num)/update', 'ArsipPendaftarController::update/$1/$2');
$routes->post('peserta/(:num)/arsip/(:num)/delete', 'ArsipPendaftarController::delete/$1/$2');
```

#### 2.3 Enhance ArsipPendaftarService
**File**: `app/Services/ArsipPendaftarService.php`

**Add Methods**:
- `deleteArchive(int $idArsip)` - Delete single archive
- `validateSlotExists(string $slotName)` - Validate slot is active
- `getArchivesByPeserta(int $idPendaftar)` - Get all archives for peserta

---

### PHASE 3: Validation Helper Wrapper (Priority: MEDIUM)
**Objective**: Expose validation as reusable helper  
**Effort**: 1 hour

#### 3.1 Add Helper Function
**File**: `app/Helpers/arsip_pendaftar_helper.php`

```php
if (! function_exists('validate_arsip_upload_ci4')) {
    /**
     * Validate arsip upload based on slot config
     * 
     * @param string $slotName
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @return array ['valid' => bool, 'message' => string]
     */
    function validate_arsip_upload_ci4(string $slotName, $file): array
    {
        $slots = get_arsip_pendaftar_config_ci4();
        
        if (!isset($slots[$slotName])) {
            return [
                'valid' => false,
                'message' => 'Slot arsip tidak ditemukan'
            ];
        }
        
        $slot = $slots[$slotName];
        
        // Delegate to service
        try {
            (new ArsipPendaftarService())->validateUpload($file, $slot, $slot['nama_arsip'] ?? $slotName);
            return ['valid' => true, 'message' => 'Valid'];
        } catch (\RuntimeException $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }
}
```

#### 3.2 Add Helper Functions for Slot Management
```php
if (! function_exists('get_slot_config_ci4')) {
    function get_slot_config_ci4(string $slotName): ?array
    {
        $slots = get_arsip_pendaftar_config_ci4();
        return $slots[$slotName] ?? null;
    }
}

if (! function_exists('count_active_arsip_pendaftar_ci4')) {
    function count_active_arsip_pendaftar_ci4(): int
    {
        return count(get_active_arsip_pendaftar_ci4());
    }
}

if (! function_exists('get_max_arsip_slot_ci4')) {
    function get_max_arsip_slot_ci4(): int
    {
        $slots = get_arsip_pendaftar_config_ci4();
        $max = 0;
        foreach (array_keys($slots) as $key) {
            if (preg_match('/^slot_(\d+)$/', (string) $key, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        return $max;
    }
}
```

---

### PHASE 4: View Enhancement & Integration (Priority: MEDIUM)
**Objective**: Ensure views properly display & validate arsip slots  
**Effort**: 1.5 hours

#### 4.1 Verify Settings View
**File**: `app/Views/admin/super/pengaturan_event/arsip_pendaftar.php`
- ✅ Already has toggle checkbox (line 49)
- ✅ Already has add/edit/delete modals
- ⚠️ Need to add toggle AJAX handler

**Action**: Add toggle event listener in script section

#### 4.2 Verify Peserta Upload View
**File**: `app/Views/kontingen/peserta/index.php`
- ✅ Already has arsip tab (line 139)
- ✅ Already has file inputs (line 87)
- ✅ Already has client-side validation (line 110-136)
- ⚠️ Need to verify `arsipSlots` data is passed

**Action**: Verify `PesertaController::index()` passes `arsipSlots` data

#### 4.3 Verify Sekretariat Form
**File**: `app/Views/admin/sekretariat/pendaftar/_form.php`
- ✅ Already has arsip tab (line 61)
- ✅ Already has file inputs (line 87)
- ✅ Already has validation (line 110-136)
- ⚠️ Need to verify `arsipSlots` data is passed

**Action**: Verify sekretariat controller passes `arsipSlots` data

#### 4.4 Add Toggle Handler to Settings View
**Location**: `app/Views/admin/super/pengaturan_event/arsip_pendaftar.php` (script section)

```javascript
// Add toggle event listener
document.querySelectorAll('[data-action="toggle"]').forEach((checkbox) => {
    checkbox.addEventListener('change', async () => {
        const slotName = checkbox.dataset.slotName || '';
        const active = checkbox.checked ? 1 : 0;
        
        const form = document.createElement('form');
        form.innerHTML = <?= json_encode(csrf_field()) ?>;
        
        const slotInput = document.createElement('input');
        slotInput.type = 'hidden';
        slotInput.name = 'slot_name';
        slotInput.value = slotName;
        form.appendChild(slotInput);
        
        const activeInput = document.createElement('input');
        activeInput.type = 'hidden';
        activeInput.name = 'active';
        activeInput.value = active;
        form.appendChild(activeInput);
        
        const res = await postJson(<?= json_encode(base_url('admin/super/pengaturan-event/arsip-pendaftar/toggle')) ?>, form);
        if (!res.status) {
            checkbox.checked = !active;
            return alert(res.message || 'Gagal mengubah status.');
        }
    });
});
```

---

### PHASE 5: Testing & Verification (Priority: HIGH)
**Objective**: Ensure all features work correctly  
**Effort**: 2-3 hours

#### 5.1 Unit Tests
**File**: `tests/Unit/Services/ArsipPendaftarServiceTest.php`

**Test Cases**:
- `testValidateUploadSuccess()` - Valid file passes
- `testValidateUploadInvalidType()` - Invalid file type rejected
- `testValidateUploadExceedsSize()` - File too large rejected
- `testSyncUploadsCreatesNew()` - New upload created
- `testSyncUploadsUpdatesExisting()` - Existing upload replaced
- `testSyncUploadsRequiredValidation()` - Required slots validated

#### 5.2 Feature Tests
**File**: `tests/Feature/ArsipPendaftarTest.php`

**Test Cases**:
- `testSettingsPageLoads()` - Settings page accessible
- `testAddSlotSuccess()` - Add slot works
- `testEditSlotSuccess()` - Edit slot works
- `testDeleteSlotSuccess()` - Delete slot works
- `testToggleSlotActive()` - Toggle active works
- `testPesertaUploadSuccess()` - Peserta can upload
- `testPesertaUploadValidation()` - Upload validation works
- `testAccessControl()` - Only authorized users can access

#### 5.3 Manual QA Checklist

**Settings Management**:
- [ ] Super admin dapat akses pengaturan arsip
- [ ] Dapat tambah slot baru
- [ ] Dapat edit slot existing
- [ ] Dapat hapus slot
- [ ] Dapat toggle active/inactive tanpa reload
- [ ] Data tersimpan di database
- [ ] Fallback ke config file jika DB kosong

**Peserta Upload (Kontingen)**:
- [ ] Kontingen dapat upload arsip saat create peserta
- [ ] Kontingen dapat upload arsip saat edit peserta
- [ ] Validasi file type (hanya JPG/PNG)
- [ ] Validasi file size sesuai slot config
- [ ] Required slots harus diisi
- [ ] Optional slots bisa dikosongkan
- [ ] File tersimpan di `uploads/peserta/arsip/`
- [ ] DB record tersimpan dengan jenis_arsip yang benar

**Peserta Upload (Sekretariat)**:
- [ ] Sekretariat dapat upload arsip saat create peserta
- [ ] Sekretariat dapat upload arsip saat edit peserta
- [ ] Validasi sama dengan kontingen
- [ ] File tersimpan dengan benar

**Access Control**:
- [ ] Kontingen hanya bisa upload untuk peserta mereka sendiri
- [ ] Sekretariat bisa upload untuk peserta manapun
- [ ] Non-authorized user tidak bisa akses

**Data Integrity**:
- [ ] Arsip lama dihapus saat upload baru
- [ ] Jenis arsip sesuai dengan slot yang dipilih
- [ ] Nama file unik & tidak bentrok

---

## 4. FILE STRUCTURE & CHANGES

### New Files to Create
```
app/Controllers/ArsipPendaftarController.php          (NEW)
tests/Unit/Services/ArsipPendaftarServiceTest.php     (NEW)
tests/Feature/ArsipPendaftarTest.php                  (NEW)
```

### Files to Modify
```
app/Config/Routes.php                                  (ADD 2 routes)
app/Controllers/Admin/Super/ArsipPendaftarSettingsController.php  (ADD 1 method)
app/Helpers/arsip_pendaftar_helper.php                (ADD 4 functions)
app/Services/ArsipPendaftarService.php                (ADD 3 methods)
app/Views/admin/super/pengaturan_event/arsip_pendaftar.php  (ADD toggle handler)
```

### Files to Verify (No Changes)
```
app/Models/ArsipPendaftarModel.php                    (OK)
app/Views/kontingen/peserta/index.php                 (OK)
app/Views/admin/sekretariat/pendaftar/_form.php       (OK)
app/Controllers/PesertaController.php                 (OK)
```

---

## 5. MIGRATION CHECKLIST

### Pre-Implementation
- [ ] Review this plan with team
- [ ] Identify any CI3 features not covered
- [ ] Prepare test database with sample data
- [ ] Set up test environment

### Implementation
- [ ] Phase 1: Toggle Active Status
  - [ ] Add route
  - [ ] Add controller method
  - [ ] Update view
  - [ ] Test manually
  
- [ ] Phase 2: Upload/Edit/Delete Controller
  - [ ] Create controller
  - [ ] Add routes
  - [ ] Enhance service
  - [ ] Test manually
  
- [ ] Phase 3: Validation Helper
  - [ ] Add helper functions
  - [ ] Test helper functions
  
- [ ] Phase 4: View Enhancement
  - [ ] Verify settings view
  - [ ] Verify peserta upload view
  - [ ] Verify sekretariat form
  - [ ] Add toggle handler
  
- [ ] Phase 5: Testing
  - [ ] Write unit tests
  - [ ] Write feature tests
  - [ ] Run all tests
  - [ ] Manual QA checklist

### Post-Implementation
- [ ] Code review
- [ ] Security review (file upload, access control)
- [ ] Performance check
- [ ] Update migration status doc
- [ ] Deploy to staging
- [ ] User acceptance testing
- [ ] Deploy to production

---

## 6. CI4 BEST PRACTICES APPLIED

### Architecture
- ✅ Service layer for business logic (`ArsipPendaftarService`)
- ✅ Model for data access (`ArsipPendaftarModel`)
- ✅ Controller for request handling (`ArsipPendaftarController`)
- ✅ Helper for utility functions

### Security
- ✅ CSRF protection via `csrf_field()`
- ✅ Input validation via CI4 validation rules
- ✅ File type & size validation
- ✅ Role-based access control (filters)
- ✅ Output escaping in views (`esc()`)
- ✅ Proper error handling

### Code Quality
- ✅ Type hints for parameters & return types
- ✅ Proper exception handling
- ✅ Consistent naming conventions (camelCase for methods)
- ✅ Comprehensive comments & docblocks
- ✅ No hardcoded values (use config/helpers)

### Testing
- ✅ Unit tests for service logic
- ✅ Feature tests for controller flows
- ✅ Manual QA checklist for user flows

---

## 7. ESTIMATED TIMELINE

| Phase | Task | Hours | Status |
|-------|------|-------|--------|
| 1 | Toggle Active Status | 1 | Pending |
| 2 | Upload/Edit/Delete Controller | 2.5 | Pending |
| 3 | Validation Helper | 1 | Pending |
| 4 | View Enhancement | 1.5 | Pending |
| 5 | Testing & QA | 2-3 | Pending |
| **Total** | | **8-9** | |

---

## 8. DEPENDENCIES & BLOCKERS

### Dependencies
- ✅ CI4 framework setup (already done)
- ✅ Database schema (already done)
- ✅ File upload directory (already exists)
- ✅ Image optimizer service (already exists)

### Potential Blockers
- ⚠️ File upload permissions on server
- ⚠️ Image optimizer service availability
- ⚠️ Database connection issues
- ⚠️ Session/auth issues

---

## 9. ROLLBACK PLAN

If issues arise:
1. Revert code changes via git
2. Keep database settings (no schema changes)
3. Clear uploaded files if needed
4. Restart application

---

## 10. SUCCESS CRITERIA

✅ All features from CI3 working in CI4  
✅ All tests passing (unit + feature)  
✅ Manual QA checklist 100% complete  
✅ No security vulnerabilities  
✅ Performance acceptable  
✅ Code review approved  
✅ Documentation updated  
