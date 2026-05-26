<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; }
    .badge-success { background: #e6fffa; color: #047857; border: 1px solid #b2f5ea; }
    .badge-danger { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; }
    .progress-custom { height: 10px; border-radius: 20px; background: #edf2f7; overflow: hidden; }
    .ext-badge { padding: 3px 8px; border-radius: 6px; font-size: 0.65rem; font-weight: 600; }
</style>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="glass-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-folder-open"></i> Izin Direktori</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr class="font-oswald text-muted small text-uppercase">
                                <th class="ps-4">Path</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($directoryStatus as $dir) : ?>
                                <tr>
                                    <td class="ps-4 py-2 font-monospace text-muted" style="font-size: 0.75rem;"><?= esc($dir['path']) ?></td>
                                    <td class="text-end pe-4">
                                        <?php if ($dir['writable']) : ?>
                                            <span class="status-badge badge-success">OK</span>
                                        <?php else : ?>
                                            <span class="status-badge badge-danger">LOCKED</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-hard-drive"></i> Disk Usage</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-oswald text-muted small">Available: <?= esc($serverInfo['disk_free_space']) ?></span>
                    <span class="font-oswald text-dark"><?= $serverInfo['disk_used_percent'] ?>% Used</span>
                </div>
                <div class="progress progress-custom">
                    <div class="progress-bar bg-brand" role="progressbar" style="width: <?= $serverInfo['disk_used_percent'] ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="glass-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-code"></i> PHP Environment</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex flex-column gap-2" style="font-size: 0.85rem;">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Version</span>
                        <b class="text-brand"><?= esc($phpEnv['php_version']) ?></b>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Memory Limit</span>
                        <b><?= esc($phpEnv['memory_limit']) ?></b>
                    </div>
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <span class="text-muted">Max Upload</span>
                        <b><?= esc($phpEnv['upload_max_filesize']) ?></b>
                    </div>
                </div>

                <h6 class="font-oswald text-muted mt-4 mb-3" style="font-size: 0.7rem;">Extensions</h6>
                <div class="d-flex flex-wrap gap-1">
                    <?php foreach ($phpEnv as $key => $val) : ?>
                        <?php if (strpos($key, '_loaded') !== false) : ?>
                            <span class="ext-badge <?= $val ? 'badge-success' : 'badge-danger' ?>">
                                <?= strtoupper(str_replace('_loaded', '', $key)) ?>
                            </span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="glass-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-server"></i> Server Info</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box me-3" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-laptop"></i></div>
                    <div>
                        <h6 class="mb-0 font-oswald" style="font-size: 0.9rem;"><?= esc($serverInfo['os']) ?></h6>
                        <p class="small text-muted mb-0" style="font-size: 0.7rem;">Operating System</p>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <div class="icon-box me-3" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-globe"></i></div>
                    <div>
                        <h6 class="mb-0 font-oswald" style="font-size: 0.9rem;"><?= esc($serverInfo['server_software']) ?></h6>
                        <p class="small text-muted mb-0" style="font-size: 0.7rem;">Server Software</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
