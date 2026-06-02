<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success alert-custom alert-dismissible fade show text-white mb-4" role="alert" style="background-color: #10b981;">
        <i class="fas fa-circle-check me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger alert-custom alert-dismissible fade show text-white mb-4" role="alert" style="background-color: var(--brand-primary);">
        <i class="fas fa-triangle-exclamation me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="glass-card">
    <div class="card-header-custom">
        <h5><i class="fas fa-trash"></i> Storage Breakdown & Cleanup</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="bg-light font-oswald text-muted small uppercase">
                    <tr>
                        <th class="ps-4 py-3">Target Directory</th>
                        <th>Files</th>
                        <th>Total Size</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($folders as $key => $info) : ?>
                        <tr>
                            <td class="ps-4 py-2">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box me-3" style="width: 35px; height: 35px; font-size: 1rem;">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-oswald text-dark" style="font-size: 0.9rem;"><?= ucfirst($key) ?></h6>
                                        <p class="small text-muted mb-0" style="font-size: 0.65rem;">
                                            <?php if ($key === 'temp') : ?>
                                                FCPATH/temp
                                            <?php elseif ($key === 'logs') : ?>
                                                WRITEPATH/logs
                                            <?php elseif ($key === 'cache') : ?>
                                                WRITEPATH/cache
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="small fw-bold text-dark"><?= $info['count'] ?> <span class="text-muted fw-normal">items</span></td>
                            <td class="small"><span class="badge bg-light text-dark border"><?= esc($info['size']) ?></span></td>
                            <td class="text-end pe-4">
                                <a href="<?= base_url('development/purger/clean/' . $key) ?>" class="btn btn-custom btn-outline-brand py-1 px-3" onclick="return confirmAdminAction(this, 'Purge <?= ucfirst($key) ?>?', 'Semua file dalam folder <?= $key ?> akan dihapus permanen.', 'Ya, Purge')">
                                    <i class="fas fa-eraser me-1"></i> Purge
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="p-3 bg-white rounded-4 border border-dashed text-center">
    <p class="small text-muted mb-0" style="font-size: 0.75rem;">
        <i class="fas fa-circle-info me-2"></i> Purging folders will permanently delete all files within them.
    </p>
</div>

<?= $this->endSection() ?>
