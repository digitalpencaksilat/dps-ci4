<div class="row mb-3">
    <div class="col-12">
        <div class="card bg-light border-0">
            <div class="card-body py-2">
                <h6 class="mb-1">Nama: <span class="text-dark fw-bold text-capitalize"><?= esc($pendaftar->nama_pendaftar) ?></span></h6>
                <p class="text-muted small mb-0">Kontingen: <span class="text-dark fw-bold"><?= esc($pendaftar->nama_kontingen) ?></span></p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($activeArsip)): ?>
    <div class="alert alert-warning text-center" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        Tidak ada arsip yang aktif. Silakan aktifkan arsip di pengaturan sistem.
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($activeArsip as $key => $arsipConfig): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-header py-2 bg-primary text-white">
                        <h6 class="text-white small mb-0 fw-semibold"><?= esc($arsipConfig['nama_arsip']) ?></h6>
                    </div>
                    <div class="card-body p-3">
                        <?php
                        $found = false;
                        foreach ($arsipPeserta as $ars):
                            if ($ars->jenis_arsip === $arsipConfig['nama_arsip']):
                                $found = true;
                                $fileUrl = base_url('uploads/peserta/arsip/' . $ars->nama_arsip);
                        ?>
                                <div class="position-relative mb-2">
                                    <img src="<?= $fileUrl ?>"
                                         class="img-fluid rounded shadow-sm w-100"
                                         style="height: 180px; object-fit: cover; cursor: pointer;"
                                         onclick="showImageModal('<?= esc($fileUrl, 'js') ?>', '<?= esc($arsipConfig['nama_arsip'], 'js') ?>', '<?= esc($pendaftar->nama_pendaftar, 'js') ?>')"
                                         alt="<?= esc($arsipConfig['nama_arsip']) ?>">
                                    <span class="badge bg-success position-absolute top-0 end-0 m-2">
                                        <i class="fas fa-check me-1"></i>Tersedia
                                    </span>
                                </div>
                                <div class="d-grid gap-1">
                                    <a href="<?= $fileUrl ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-external-link-alt me-1"></i>Buka di Tab Baru
                                    </a>
                                    <a href="<?= $fileUrl ?>" download class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-download me-1"></i>Download
                                    </a>
                                </div>
                        <?php
                            endif;
                        endforeach;
                        if (!$found):
                        ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-image fa-3x text-muted opacity-50 mb-2"></i>
                                <p class="text-muted small mb-1">Belum diupload</p>
                                <span class="badge bg-secondary">Tidak Tersedia</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
