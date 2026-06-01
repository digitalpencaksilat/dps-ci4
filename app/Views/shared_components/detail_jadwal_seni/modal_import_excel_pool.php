<?php
/**
 * Modal Import Excel Jadwal Seni Pool
 *
 * Variables expected:
 *   $jadwal        - object jadwal_seni
 *   $routePrefix   - string, e.g. 'admin/super/jadwal-seni'
 */
$routePrefix = $routePrefix ?? 'admin/super/jadwal-seni';
$idJadwal    = $jadwal->id_jadwal_seni ?? 0;
?>

<!-- Modal Import Excel Seni Pool -->
<div class="modal fade" id="modalImportExcelPool" tabindex="-1" aria-labelledby="modalImportExcelPoolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportExcelPoolLabel">
                    <i class="fas fa-file-excel me-2 text-success"></i>Import Excel Jadwal Seni Pool
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Step 1: Upload -->
                <div id="importPoolStep1">
                    <div class="alert alert-info small mb-3">
                        <strong>Format Excel (8 kolom, 2 baris per penampilan):</strong><br>
                        <code>A: No. Partai &nbsp;|&nbsp; B: Kategori Usia &nbsp;|&nbsp; C: Jenis Kelamin &nbsp;|&nbsp; D: Jenis Seni &nbsp;|&nbsp; E: Nama Seni &nbsp;|&nbsp; F: No. Pool &nbsp;|&nbsp; G: Nama Atlet &nbsp;|&nbsp; H: Babak</code><br>
                        <span class="text-muted">Baris ganjil (1,3,5...): data penampilan. Baris genap (2,4,6...): nama kontingen di kolom G.</span><br>
                        <span class="text-muted">Babak yang diterima: <code>penyisihan</code>, <code>elimination</code>, <code>final</code>.</span><br>
                        <span class="text-muted">Untuk ganda/regu, pisahkan nama atlet dengan koma (,).</span>
                    </div>

                    <div id="dropZoneImportPool" class="border border-2 border-dashed rounded p-4 text-center mb-3"
                         style="cursor:pointer; border-color:#adb5bd !important; transition: border-color .2s;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                        <p class="mb-1 text-muted">Drag &amp; drop file Excel di sini, atau</p>
                        <label class="btn btn-sm btn-outline-secondary mb-0">
                            Pilih File
                            <input type="file" id="fileExcelInputPool" accept=".xlsx,.xls" class="d-none">
                        </label>
                        <p class="small text-muted mt-2 mb-0" id="fileExcelNamePool">Belum ada file dipilih</p>
                    </div>

                    <div id="importPoolValidasiError" class="alert alert-danger d-none">
                        <strong>Validasi gagal:</strong>
                        <ul class="mb-0 mt-1 small" id="importPoolValidasiErrorList"></ul>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnValidasiExcelPool" disabled>
                            <span id="btnValidasiSpinnerPool" class="spinner-border spinner-border-sm me-1 d-none"></span>
                            Validasi &amp; Preview
                        </button>
                    </div>
                </div>

                <!-- Step 2: Preview & Konfirmasi -->
                <div id="importPoolStep2" class="d-none">
                    <div class="alert alert-success small mb-3" id="importPoolSummaryAlert">
                        <i class="fas fa-check-circle me-1"></i>
                        <span id="importPoolSummaryText"></span>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnImportUlangPool">
                            <i class="fas fa-arrow-left me-1"></i>Upload Ulang
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success" id="btnKonfirmasiImportPool">
                                <span id="btnKonfirmasiSpinnerPool" class="spinner-border spinner-border-sm me-1 d-none"></span>
                                <i class="fas fa-save me-1"></i>Simpan ke Jadwal
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const ID_JADWAL   = <?= (int) $idJadwal ?>;
    const ROUTE_BASE  = '<?= base_url($routePrefix) ?>';
    const CSRF_NAME   = '<?= csrf_token() ?>';
    const CSRF_HASH   = '<?= csrf_hash() ?>';

    let currentToken  = null;

    const dropZone        = document.getElementById('dropZoneImportPool');
    const fileInput       = document.getElementById('fileExcelInputPool');
    const fileNameLabel   = document.getElementById('fileExcelNamePool');
    const btnValidasi     = document.getElementById('btnValidasiExcelPool');
    const btnValidasiSpinner = document.getElementById('btnValidasiSpinnerPool');
    const validasiError   = document.getElementById('importPoolValidasiError');
    const validasiList    = document.getElementById('importPoolValidasiErrorList');
    const step1           = document.getElementById('importPoolStep1');
    const step2           = document.getElementById('importPoolStep2');
    const summaryText     = document.getElementById('importPoolSummaryText');
    const btnKonfirmasi   = document.getElementById('btnKonfirmasiImportPool');
    const btnKonfirmasiSpinner = document.getElementById('btnKonfirmasiSpinnerPool');
    const btnUlang        = document.getElementById('btnImportUlangPool');

    // Reset modal saat ditutup
    document.getElementById('modalImportExcelPool').addEventListener('hidden.bs.modal', function () {
        resetModal();
    });

    function resetModal() {
        fileInput.value = '';
        fileNameLabel.textContent = 'Belum ada file dipilih';
        btnValidasi.disabled = true;
        validasiError.classList.add('d-none');
        validasiList.innerHTML = '';
        step1.classList.remove('d-none');
        step2.classList.add('d-none');
        currentToken = null;
    }

    // Drag & drop
    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.style.borderColor = '#0d6efd';
    });
    dropZone.addEventListener('dragleave', function () {
        dropZone.style.borderColor = '#adb5bd';
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.style.borderColor = '#adb5bd';
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            onFileSelected(files[0]);
        }
    });
    dropZone.addEventListener('click', function (e) {
        if (e.target !== fileInput) fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (this.files.length > 0) onFileSelected(this.files[0]);
    });

    function onFileSelected(file) {
        const allowed = ['xlsx', 'xls'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowed.includes(ext)) {
            showError(['Format file harus .xlsx atau .xls']);
            btnValidasi.disabled = true;
            fileNameLabel.textContent = 'File tidak valid';
            return;
        }
        validasiError.classList.add('d-none');
        fileNameLabel.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        btnValidasi.disabled = false;
    }

    // Validasi & Preview
    btnValidasi.addEventListener('click', function () {
        const file = fileInput.files[0];
        if (!file) return;

        btnValidasi.disabled = true;
        btnValidasiSpinner.classList.remove('d-none');
        validasiError.classList.add('d-none');

        const formData = new FormData();
        formData.append('file_excel', file);
        formData.append(CSRF_NAME, getCsrfToken());

        fetch(ROUTE_BASE + '/' + ID_JADWAL + '/import-excel-pool', {
            method: 'POST',
            body: formData,
        })
        .then(r => {
            updateCsrfToken(r);
            return r.json();
        })
        .then(function (res) {
            btnValidasiSpinner.classList.add('d-none');

            if (!res.status) {
                btnValidasi.disabled = false;
                const errors = res.errors || res.messages || [res.message || 'Terjadi kesalahan.'];
                showError(Array.isArray(errors) ? errors : [errors]);
                return;
            }

            currentToken = res.token;
            summaryText.textContent = res.message
                + ' | Penampilan: ' + (res.summary.jumlah_penampilan || 0)
                + ' | Peserta: ' + (res.summary.jumlah_peserta || 0)
                + ' | Kontingen: ' + (res.summary.jumlah_kontingen || 0);

            step1.classList.add('d-none');
            step2.classList.remove('d-none');
        })
        .catch(function (err) {
            btnValidasiSpinner.classList.add('d-none');
            btnValidasi.disabled = false;
            showError(['Gagal menghubungi server: ' + err.message]);
        });
    });

    // Upload ulang
    btnUlang.addEventListener('click', function () {
        step2.classList.add('d-none');
        step1.classList.remove('d-none');
        currentToken = null;
        fileInput.value = '';
        fileNameLabel.textContent = 'Belum ada file dipilih';
        btnValidasi.disabled = true;
    });

    // Konfirmasi simpan
    btnKonfirmasi.addEventListener('click', function () {
        if (!currentToken) return;

        btnKonfirmasi.disabled = true;
        btnKonfirmasiSpinner.classList.remove('d-none');

        const formData = new FormData();
        formData.append('token', currentToken);
        formData.append(CSRF_NAME, getCsrfToken());

        fetch(ROUTE_BASE + '/' + ID_JADWAL + '/import-excel-pool-commit', {
            method: 'POST',
            body: formData,
        })
        .then(r => {
            updateCsrfToken(r);
            return r.json();
        })
        .then(function (res) {
            btnKonfirmasiSpinner.classList.add('d-none');
            btnKonfirmasi.disabled = false;

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalImportExcelPool'));
            if (modal) modal.hide();

            if (res.status) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(res.message || 'Import berhasil.');
                } else {
                    alert(res.message || 'Import berhasil.');
                }
                setTimeout(function () { location.reload(); }, 1200);
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(res.message || 'Import gagal.');
                } else {
                    alert('Gagal: ' + (res.message || 'Import gagal.'));
                }
            }
        })
        .catch(function (err) {
            btnKonfirmasiSpinner.classList.add('d-none');
            btnKonfirmasi.disabled = false;
            if (typeof toastr !== 'undefined') {
                toastr.error('Gagal menghubungi server: ' + err.message);
            } else {
                alert('Gagal: ' + err.message);
            }
        });
    });

    function showError(errors) {
        validasiList.innerHTML = '';
        errors.forEach(function (msg) {
            const li = document.createElement('li');
            li.textContent = msg;
            validasiList.appendChild(li);
        });
        validasiError.classList.remove('d-none');
    }

    // CSRF helpers
    let currentCsrfHash = CSRF_HASH;

    function getCsrfToken() {
        const cookieVal = getCookie('csrf_cookie_name');
        if (cookieVal) return cookieVal;
        return currentCsrfHash;
    }

    function updateCsrfToken(response) {
        const newToken = response.headers.get('X-CSRF-TOKEN');
        if (newToken) {
            currentCsrfHash = newToken;
        }
        const cookieVal = getCookie('csrf_cookie_name');
        if (cookieVal) {
            currentCsrfHash = cookieVal;
        }
    }

    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? decodeURIComponent(match[2]) : null;
    }
})();
</script>
