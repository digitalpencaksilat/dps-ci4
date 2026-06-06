<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <?= view('shared_components/admin/page_header', [
        'eyebrow'  => 'Sekretariat',
        'title'    => 'Jadwal Seni',
        'subtitle' => 'Daftar jadwal penampilan seni per arena.',
    ]) ?>
    <div class="card-body px-0">
        <?php if (session()->get('level') === 'super_admin'): ?>
            <?php if (session()->get('level') === 'super_admin'): ?>
                <div class="mb-3 p-3 border rounded bg-light">
                    <p class="form-label mb-1 fw-bold">PDF Engine untuk Update</p>
                    <small class="text-muted">Semua update jadwal menggunakan mPDF.</small>
                </div>
            <?php endif; ?>

            <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="btn btn-admin-brand rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalInsertJadwalSeni">
                    <i class="fas fa-plus me-1"></i> Create Schedule
                </button>
                <?php if (session()->get('level') === 'super_admin'): ?>
                    <button type="button" class="btn btn-outline-danger rounded-pill" id="btnUpdateAllPDF">Update all PDF</button>
                    <button type="button" class="btn btn-outline-danger rounded-pill" id="btnUpdateAllPDFWithScore">Update all PDF with Score</button>
                    <button type="button" class="btn btn-danger rounded-pill" id="btnUpdateSelectedSeni" disabled>Update Selected PDF <span id="btnUpdateSelectedSeniCount">(0)</span></button>
                    <button type="button" class="btn btn-danger rounded-pill" id="btnUpdateSelectedSeniScore" disabled>Update Selected PDF with Score <span id="btnUpdateSelectedSeniScoreCount">(0)</span></button>
                <?php endif; ?>
            </div>

            <?= view('shared_components/jadwal_seni/modal_insert', [
                'data_gelanggang' => $gelanggang,
                'routePrefix' => $routePrefix ?? 'admin/super/jadwal-seni',
            ]) ?>
        <?php endif; ?>

        <?= view('shared_components/jadwal_seni/tabel', [
            'data_jadwal_seni' => $rows,
            'routePrefix' => $routePrefix ?? 'admin/sekretariat/jadwal-seni',
        ]) ?>
    </div>
</section>

<?php if (session()->get('level') === 'super_admin'): ?>
<div class="modal fade" id="progressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Memproses PDF Jadwal</h5></div>
            <div class="modal-body">
                <p id="progressText">Memuat daftar jadwal...</p>
                <div class="progress" style="height: 25px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                </div>
                <div class="mt-3"><small id="progressDetail" class="text-muted"></small></div>
                <div id="progressErrors" class="mt-3 alert alert-danger" style="display: none;">
                    <strong>Error:</strong>
                    <ul id="errorList" class="mb-0"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft rounded-pill px-4" id="btnCloseProgress" disabled>Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressDetail = document.getElementById('progressDetail');
    const progressErrors = document.getElementById('progressErrors');
    const errorList = document.getElementById('errorList');
    const btnCloseProgress = document.getElementById('btnCloseProgress');
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function updateCsrfToken(response) {
        const newToken = response.headers.get('X-CSRF-TOKEN');
        if (newToken) csrfHash = newToken;
    }

    async function processPDFSequential(jadwalList, withScore = false) {
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated';
        progressText.textContent = 'Memproses ' + jadwalList.length + ' jadwal...';
        progressDetail.textContent = '';
        progressErrors.style.display = 'none';
        errorList.innerHTML = '';
        btnCloseProgress.disabled = true;
        progressModal.show();

        const total = jadwalList.length;
        let success = 0, failed = 0;
        const errors = [];

        for (let i = 0; i < jadwalList.length; i++) {
            const jadwal = jadwalList[i];
            const current = i + 1;
            const percentage = Math.round((current / total) * 100);

            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';
            progressDetail.textContent = current + '/' + total + ' - ' + jadwal.nama;

            try {
                const url = '<?= base_url(($routePrefix ?? "admin/sekretariat/jadwal-seni") . "/create-pdf-ajax/") ?>' + jadwal.id + '/' + (withScore ? '1' : '0');
                const body = new URLSearchParams();
                body.append('pdf_library', 'mpdf');
                body.append(csrfName, csrfHash);
                const pdfResponse = await fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString(),
                });
                updateCsrfToken(pdfResponse);
                const pdfResult = await pdfResponse.json();
                if (pdfResult.status) success++; else { failed++; errors.push(jadwal.nama + ': ' + pdfResult.message); }
            } catch (error) {
                failed++;
                errors.push(jadwal.nama + ': ' + error.message);
            }
        }

        progressBar.classList.remove('progress-bar-animated');
        if (failed === 0) {
            progressBar.classList.add('bg-success');
            progressText.textContent = 'Selesai! Semua ' + success + ' jadwal PDF berhasil diupdate';
        } else {
            progressBar.classList.add('bg-warning');
            progressText.textContent = 'Selesai dengan error. Berhasil: ' + success + ', Gagal: ' + failed;
            progressErrors.style.display = 'block';
            errors.forEach(function(err) {
                var li = document.createElement('li');
                li.textContent = err;
                errorList.appendChild(li);
            });
        }
        progressDetail.textContent = '';
        btnCloseProgress.disabled = false;
    }

    async function processAllPDF(withScore = false) {
        progressBar.style.width = '0%';
        progressBar.textContent = '0%';
        progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated';
        progressText.textContent = 'Memuat daftar jadwal...';
        progressDetail.textContent = '';
        progressErrors.style.display = 'none';
        errorList.innerHTML = '';
        btnCloseProgress.disabled = true;
        progressModal.show();

        try {
            const response = await fetch('<?= base_url(($routePrefix ?? "admin/sekretariat/jadwal-seni") . "/get-all-ids-ajax") ?>');
            const data = await response.json();
            if (!data.status) throw new Error(data.message || 'Gagal memuat daftar jadwal');
            await processPDFSequential(data.data, withScore);
        } catch (error) {
            progressBar.classList.remove('progress-bar-animated');
            progressBar.classList.add('bg-danger');
            progressText.textContent = 'Error: ' + error.message;
            btnCloseProgress.disabled = false;
        }
    }

    document.getElementById('btnUpdateAllPDF').addEventListener('click', function() {
        Swal.fire({ title: 'Apakah Anda Yakin?', text: 'Semua jadwal PDF akan diperbarui (sistem otomatis terdeteksi)', icon: 'question', showCancelButton: true, confirmButtonText: 'Update', cancelButtonText: 'Batal' }).then(function(result) { if (result.isConfirmed) processAllPDF(false); });
    });

    document.getElementById('btnUpdateAllPDFWithScore').addEventListener('click', function() {
        Swal.fire({ title: 'Apakah Anda Yakin?', text: 'Semua jadwal PDF akan diperbarui dengan skor (sistem otomatis terdeteksi)', icon: 'question', showCancelButton: true, confirmButtonText: 'Update', cancelButtonText: 'Batal' }).then(function(result) { if (result.isConfirmed) processAllPDF(true); });
    });

    document.getElementById('btnUpdateSelectedSeni').addEventListener('click', function() {
        var selected = window.getSelectedJadwalSeni ? window.getSelectedJadwalSeni() : [];
        if (selected.length === 0) { Swal.fire('Peringatan', 'Pilih minimal 1 jadwal terlebih dahulu', 'warning'); return; }
        Swal.fire({ title: 'Apakah Anda Yakin?', text: selected.length + ' jadwal terpilih akan diperbarui', icon: 'question', showCancelButton: true, confirmButtonText: 'Update', cancelButtonText: 'Batal' }).then(function(result) { if (result.isConfirmed) processPDFSequential(selected, false); });
    });

    document.getElementById('btnUpdateSelectedSeniScore').addEventListener('click', function() {
        var selected = window.getSelectedJadwalSeni ? window.getSelectedJadwalSeni() : [];
        if (selected.length === 0) { Swal.fire('Peringatan', 'Pilih minimal 1 jadwal terlebih dahulu', 'warning'); return; }
        Swal.fire({ title: 'Apakah Anda Yakin?', text: selected.length + ' jadwal terpilih akan diperbarui dengan skor', icon: 'question', showCancelButton: true, confirmButtonText: 'Update', cancelButtonText: 'Batal' }).then(function(result) { if (result.isConfirmed) processPDFSequential(selected, true); });
    });

    btnCloseProgress.addEventListener('click', function() {
        progressModal.hide();
        if (window.resetSelectionJadwalSeni) window.resetSelectionJadwalSeni();
        location.reload();
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
