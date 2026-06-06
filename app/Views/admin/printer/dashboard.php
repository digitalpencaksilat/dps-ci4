<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?= view('admin/super/_action_toolbar', [
    'eyebrow'     => 'Printer',
    'title'       => 'Dashboard Pencetakan Sertifikat',
    'description' => 'Kelola background, tata letak, domain QR Code, lalu cetak sertifikat peserta tanding dan seni.',
    'toolbarClass' => 'mb-4',
    'actions' => [
        [
            'tag' => 'button', 'label' => 'Upload Background', 'class' => 'btn-outline-secondary',
            'attrs' => ['data-bs-toggle' => 'modal', 'data-bs-target' => '#modalUpload'],
        ],
        [
            'tag' => 'a', 'href' => base_url('admin/printer/pengaturan-tata-letak'),
            'label' => 'Tata Letak', 'class' => 'btn-outline-secondary',
        ],
        [
            'tag' => 'a', 'href' => base_url('admin/printer/preview'),
            'label' => 'Preview', 'class' => 'btn-outline-danger',
            'attrs' => ['target' => '_blank'],
        ],
    ],
]) ?>

<!-- Statistik -->
<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="admin-card p-3 text-center h-100">
            <p class="eyebrow mb-1">Tanding</p>
            <span class="fs-3 fw-bold text-danger"><?= esc((string) $statistik['sudah_tanding']) ?> / <?= esc((string) $statistik['total_tanding']) ?></span>
            <p class="text-muted small mb-0">sertifikat dicetak</p>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="admin-card p-3 text-center h-100">
            <p class="eyebrow mb-1">Seni</p>
            <span class="fs-3 fw-bold text-danger"><?= esc((string) $statistik['sudah_seni']) ?> / <?= esc((string) $statistik['total_seni']) ?></span>
            <p class="text-muted small mb-0">sertifikat dicetak</p>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="admin-card p-3 text-center h-100">
            <p class="eyebrow mb-1">Background</p>
            <?php if ($hasBackground) : ?>
                <span class="status-badge success fs-6">Tersedia</span>
            <?php else : ?>
                <span class="status-badge neutral fs-6">Belum diunggah</span>
            <?php endif; ?>
            <p class="text-muted small mb-0">sertifikat.png</p>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="admin-card p-3 text-center h-100">
            <p class="eyebrow mb-1">Background Cetak</p>
            <span class="status-badge <?= $hideBg ? 'warning' : 'success' ?> fs-6"><?= $hideBg ? 'Disembunyikan' : 'Ditampilkan' ?></span>
            <p class="text-muted small mb-0">saat mencetak</p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Background preview -->
    <div class="col-lg-5">
        <section class="admin-card h-100">
            <p class="eyebrow mb-1">Sampel</p>
            <h3 class="section-title h5 mb-3">Background Sertifikat</h3>
            <?php if ($hasBackground) : ?>
                <img src="<?= esc($backgroundUrl) ?>" class="img-fluid rounded border mb-3" alt="Background sertifikat">
            <?php else : ?>
                <div class="alert alert-secondary small">Belum ada background. Unggah file PNG sampel sertifikat.</div>
            <?php endif; ?>
            <button class="btn btn-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalUpload">
                <i class="fa-solid fa-upload me-1"></i> Upload Background
            </button>
        </section>
    </div>

    <!-- Pengaturan -->
    <div class="col-lg-7">
        <section class="admin-card h-100">
            <p class="eyebrow mb-1">Pengaturan</p>
            <h3 class="section-title h5 mb-3">Domain QR & Background</h3>

            <div class="mb-4">
                <label class="form-label fw-semibold">Domain Hosting (QR Code)</label>
                <form action="<?= base_url('admin/printer/update-domain-hosting') ?>" method="post" class="d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="url" name="domain_hosting" class="form-control" value="<?= esc($domainHosting) ?>" placeholder="<?= base_url() ?>">
                    <button type="submit" class="btn btn-admin-brand rounded-pill px-4">Simpan</button>
                </form>
                <div class="form-text">Base URL untuk link QR Code pada sertifikat.</div>
            </div>

            <div>
                <label class="form-label fw-semibold">Tampilkan Background saat Cetak</label>
                <form action="<?= base_url('admin/printer/update-hide-background') ?>" method="post">
                    <?= csrf_field() ?>
                    <select name="hide_sertifikat_background" class="form-select" onchange="this.form.submit()">
                        <option value="0" <?= ! $hideBg ? 'selected' : '' ?>>Tampilkan Background</option>
                        <option value="1" <?= $hideBg ? 'selected' : '' ?>>Sembunyikan Background</option>
                    </select>
                </form>
            </div>
        </section>
    </div>
</div>

<!-- Pengaturan & Generate Nomor Sertifikat -->
<?php
$progressPct = ($statNomor['total'] ?? 0) > 0
    ? round(($statNomor['sudah'] / $statNomor['total']) * 100)
    : 0;
$previewSuffix = $suffix !== '' ? '/' . $suffix : '';
?>
<section class="admin-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <p class="eyebrow mb-1">Penomoran</p>
            <h3 class="section-title h5 mb-0"><i class="fa-solid fa-certificate text-warning me-2"></i>Pengaturan &amp; Generate Nomor Sertifikat</h3>
        </div>
        <span class="status-badge neutral fs-6"><?= esc((string) ($statNomor['sudah'] ?? 0)) ?> / <?= esc((string) ($statNomor['total'] ?? 0)) ?> tergenerate</span>
    </div>

    <div class="row g-4">
        <!-- Suffix & preview -->
        <div class="col-lg-6">
            <form action="<?= base_url('admin/printer/update-nomor-sertifikat-suffix') ?>" method="post">
                <?= csrf_field() ?>
                <label class="form-label fw-semibold">Suffix Nomor Sertifikat</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="inputSuffix" name="nomor_sertifikat_suffix"
                           value="<?= esc($suffix) ?>" placeholder="HAKA/XI/2026">
                    <button type="submit" class="btn btn-admin-brand px-3">Simpan</button>
                </div>
                <div class="form-text">Format akhir: <code id="previewNomor">0001<?= esc($previewSuffix) ?></code></div>
            </form>
        </div>

        <!-- Progress -->
        <div class="col-lg-6">
            <label class="form-label fw-semibold">Progress Generate</label>
            <div class="progress" style="height: 1.5rem;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progressPct ?>%;"
                     aria-valuenow="<?= $progressPct ?>" aria-valuemin="0" aria-valuemax="100">
                    <?= $progressPct ?>%
                </div>
            </div>
            <p class="text-muted small mb-0 mt-1">
                <?= esc((string) ($statNomor['belum'] ?? 0)) ?> peserta belum memiliki nomor sertifikat.
            </p>
        </div>
    </div>

    <hr class="my-4">

    <div class="d-flex flex-wrap gap-2">
        <form action="<?= base_url('admin/printer/generate-semua-nomor-sertifikat') ?>" method="post" id="formGenerateSemua">
            <?= csrf_field() ?>
            <button type="button" class="btn btn-admin-brand rounded-pill px-4" id="btnGenerateSemua" <?= ($statNomor['belum'] ?? 0) < 1 ? 'disabled' : '' ?>>
                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generate Semua (Belum Ada)
            </button>
        </form>
        <form action="<?= base_url('admin/printer/reset-nomor-sertifikat') ?>" method="post" id="formResetNomor">
            <?= csrf_field() ?>
            <input type="hidden" name="pass_code" id="resetPasscode">
            <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="btnResetNomor">
                <i class="fa-solid fa-rotate-left me-1"></i> Reset Semua Nomor
            </button>
        </form>
    </div>
</section>

<!-- Aksi Cetak -->
<div class="row g-4">
    <div class="col-md-6">
        <a href="<?= base_url('admin/printer/cetak-tanding') ?>" class="admin-card d-flex align-items-center gap-3 p-4 text-decoration-none text-reset h-100">
            <i class="fa-solid fa-hand-fist fa-2x text-danger"></i>
            <div>
                <h3 class="section-title h6 mb-1">Cetak Sertifikat Tanding</h3>
                <p class="text-muted small mb-0"><?= esc((string) $statistik['total_tanding']) ?> peserta</p>
            </div>
            <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
        </a>
    </div>
    <div class="col-md-6">
        <a href="<?= base_url('admin/printer/cetak-seni') ?>" class="admin-card d-flex align-items-center gap-3 p-4 text-decoration-none text-reset h-100">
            <i class="fa-solid fa-masks-theater fa-2x text-danger"></i>
            <div>
                <h3 class="section-title h6 mb-1">Cetak Sertifikat Seni</h3>
                <p class="text-muted small mb-0"><?= esc((string) $statistik['total_seni']) ?> peserta</p>
            </div>
            <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
        </a>
    </div>
</div>

<!-- Modal Upload -->
<div class="modal fade" id="modalUpload" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= base_url('admin/printer/upload-background') ?>" method="post" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Upload Background Sertifikat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <label for="fileSertifikat" class="form-label">File PNG (max 3 MB)</label>
                <input type="file" class="form-control" id="fileSertifikat" name="sertifikat" accept="image/png" required>
                <div class="form-text">Disimpan sebagai sertifikat.png dan menimpa file lama.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset dihapus: konfirmasi reset kini memakai SweetAlert (lihat section scripts). -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function () {
        // Live preview suffix
        var input = document.getElementById('inputSuffix');
        var preview = document.getElementById('previewNomor');
        if (input && preview) {
            input.addEventListener('input', function () {
                var val = this.value.trim();
                preview.textContent = '0001' + (val ? '/' + val : '');
            });
        }

        // Konfirmasi Generate Semua (SweetAlert)
        var btnGenerate = document.getElementById('btnGenerateSemua');
        if (btnGenerate) {
            btnGenerate.addEventListener('click', function () {
                Swal.fire({
                    title: 'Generate Semua Nomor?',
                    text: 'Nomor sertifikat akan dibuat untuk semua peserta yang belum memilikinya.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Generate',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#c60000'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        document.getElementById('formGenerateSemua').submit();
                    }
                });
            });
        }

        // Konfirmasi Reset Semua Nomor (SweetAlert + input passcode)
        var btnReset = document.getElementById('btnResetNomor');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                Swal.fire({
                    title: 'Reset Semua Nomor?',
                    html: 'Tindakan ini menghapus <strong>semua</strong> nomor sertifikat (tanding &amp; seni) dan tidak dapat dibatalkan.<br>Masukkan passcode keamanan untuk melanjutkan.',
                    icon: 'warning',
                    input: 'password',
                    inputPlaceholder: 'Passcode keamanan',
                    inputAttributes: { autocapitalize: 'off', autocorrect: 'off' },
                    showCancelButton: true,
                    confirmButtonText: 'Reset',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#c60000',
                    inputValidator: function (value) {
                        if (!value) { return 'Passcode wajib diisi!'; }
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        document.getElementById('resetPasscode').value = result.value;
                        document.getElementById('formResetNomor').submit();
                    }
                });
            });
        }
    })();
</script>
<?= $this->endSection() ?>
