<?php
/**
 * Modal Import Excel Jadwal Tanding
 *
 * Variables expected:
 *   $jadwal        - object jadwal_tanding
 *   $routePrefix   - string, e.g. 'admin/sekretariat/jadwal-tanding'
 */
$routePrefix = $routePrefix ?? 'admin/sekretariat/jadwal-tanding';
$idJadwal    = $jadwal->id_jadwal_tanding ?? 0;
?>

<!-- Modal Import Excel -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-labelledby="modalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportExcelLabel">
                    <i class="fas fa-file-excel me-2 text-success"></i>Import Excel Jadwal Tanding
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Step 1: Upload -->
                <div id="importStep1">
                    <div class="alert alert-info small mb-3">
                        <strong>Format Excel (9 kolom):</strong><br>
                        <code>A: No. Partai &nbsp;|&nbsp; B: Kategori Usia &nbsp;|&nbsp; C: Jenis Kelamin &nbsp;|&nbsp; D: Kategori Lomba &nbsp;|&nbsp; E: Kelas/Label &nbsp;|&nbsp; F: No. Pool &nbsp;|&nbsp; G: Sudut Biru &nbsp;|&nbsp; H: Babak &nbsp;|&nbsp; I: Sudut Merah</code><br>
                        <span class="text-muted">Setiap partai = 2 baris: baris 1 (nama atlet), baris 2 (nama kontingen).</span><br>
                        <span class="text-muted">Untuk slot Winner/PP, isi kolom G atau I dengan: <code>Winner 5</code> (nomor partai sumber).</span>
                    </div>

                    <div id="dropZoneImport" class="border border-2 border-dashed rounded p-4 text-center mb-3"
                         style="cursor:pointer; border-color:#adb5bd !important; transition: border-color .2s;">
                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2 d-block"></i>
                        <p class="mb-1 text-muted">Drag &amp; drop file Excel di sini, atau</p>
                        <label class="btn btn-sm btn-outline-secondary mb-0">
                            Pilih File
                            <input type="file" id="fileExcelInput" accept=".xlsx,.xls" class="d-none">
                        </label>
                        <p class="small text-muted mt-2 mb-0" id="fileExcelName">Belum ada file dipilih</p>
                    </div>

                    <div id="importValidasiError" class="alert alert-danger d-none">
                        <strong>Validasi gagal:</strong>
                        <ul class="mb-0 mt-1 small" id="importValidasiErrorList"></ul>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnValidasiExcel" disabled>
                            <span id="btnValidasiSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
                            Validasi &amp; Preview
                        </button>
                    </div>
                </div>

                <!-- Step 2: Preview & Konfirmasi -->
                <div id="importStep2" class="d-none">
                    <div class="alert alert-success small mb-3" id="importSummaryAlert">
                        <i class="fas fa-check-circle me-1"></i>
                        <span id="importSummaryText"></span>
                    </div>

                    <div class="table-responsive mb-3" id="importPreviewTableWrap" style="max-height:320px; overflow-y:auto;">
                        <table class="table table-sm table-bordered align-middle small mb-0" id="importPreviewTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>No. Partai</th>
                                    <th>Babak</th>
                                    <th>Kategori</th>
                                    <th class="text-info">Sudut Biru</th>
                                    <th class="text-danger">Sudut Merah</th>
                                </tr>
                            </thead>
                            <tbody id="importPreviewTbody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnImportUlang">
                            <i class="fas fa-arrow-left me-1"></i>Upload Ulang
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-success" id="btnKonfirmasiImport">
                                <span id="btnKonfirmasiSpinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
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
    let currentSummary = null;

    const dropZone        = document.getElementById('dropZoneImport');
    const fileInput       = document.getElementById('fileExcelInput');
    const fileNameLabel   = document.getElementById('fileExcelName');
    const btnValidasi     = document.getElementById('btnValidasiExcel');
    const btnValidasiSpinner = document.getElementById('btnValidasiSpinner');
    const validasiError   = document.getElementById('importValidasiError');
    const validasiList    = document.getElementById('importValidasiErrorList');
    const step1           = document.getElementById('importStep1');
    const step2           = document.getElementById('importStep2');
    const summaryText     = document.getElementById('importSummaryText');
    const previewTbody    = document.getElementById('importPreviewTbody');
    const btnKonfirmasi   = document.getElementById('btnKonfirmasiImport');
    const btnKonfirmasiSpinner = document.getElementById('btnKonfirmasiSpinner');
    const btnUlang        = document.getElementById('btnImportUlang');

    // Reset modal saat ditutup
    document.getElementById('modalImportExcel').addEventListener('hidden.bs.modal', function () {
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
        currentSummary = null;
        previewTbody.innerHTML = '';
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

        fetch(ROUTE_BASE + '/' + ID_JADWAL + '/import-excel', {
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
                const errors = res.errors || [res.message || 'Terjadi kesalahan.'];
                showError(errors);
                return;
            }

            currentToken   = res.token;
            currentSummary = res.summary;

            summaryText.textContent = res.message
                + ' | Peserta: ' + res.summary.jumlah_peserta
                + ' | Kontingen: ' + res.summary.jumlah_kontingen;

            renderPreview(res.preview_rows || []);

            step1.classList.add('d-none');
            step2.classList.remove('d-none');
        })
        .catch(function (err) {
            btnValidasiSpinner.classList.add('d-none');
            btnValidasi.disabled = false;
            showError(['Gagal menghubungi server: ' + err.message]);
        });
    });

    function renderPreview(rows) {
        previewTbody.innerHTML = '';
        if (!rows || rows.length === 0) {
            previewTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Data siap diimport (preview detail tidak tersedia).</td></tr>';
            return;
        }
        rows.forEach(function (r) {
            const tr = document.createElement('tr');
            tr.innerHTML = '<td class="fw-bold">' + esc(r.nomor_partai) + '</td>'
                + '<td>' + esc(r.babak) + '</td>'
                + '<td class="small">' + esc(r.kategori) + '</td>'
                + '<td class="text-primary">' + esc(r.atlet_biru || '-') + '</td>'
                + '<td class="text-danger">' + esc(r.atlet_merah || '-') + '</td>';
            previewTbody.appendChild(tr);
        });
    }

    function esc(str) {
        if (!str) return '-';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

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

        fetch(ROUTE_BASE + '/' + ID_JADWAL + '/import-excel-commit', {
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

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalImportExcel'));
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

    // CSRF helpers — token di-regenerate setiap request, jadi ambil yang terbaru
    let currentCsrfHash = CSRF_HASH;

    function getCsrfToken() {
        // Prioritas: 1) cookie (paling fresh), 2) cached dari response sebelumnya, 3) initial render
        const cookieVal = getCookie('csrf_cookie_name');
        if (cookieVal) return cookieVal;
        return currentCsrfHash;
    }

    function updateCsrfToken(response) {
        // CI4 mengirim token baru via header setelah regenerate
        const newToken = response.headers.get('X-CSRF-TOKEN');
        if (newToken) {
            currentCsrfHash = newToken;
        }
        // Juga cek dari cookie
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
