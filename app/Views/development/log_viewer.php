<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .log-terminal { height: clamp(400px, 60vh, 600px); }
    .log-line { padding: 4px 0; border-bottom: 1px solid #2d2d2d; }
    .log-error { color: #f87171; border-left: 3px solid #f87171; padding-left: 10px; }
    .log-info { color: #4ade80; border-left: 3px solid #4ade80; padding-left: 10px; }
    .log-debug { color: #60a5fa; border-left: 3px solid #60a5fa; padding-left: 10px; }
    .file-item { display: flex; align-items: center; padding: 10px 15px; border-radius: 10px; text-decoration: none !important; color: var(--text-main); margin-bottom: 5px; transition: var(--transition); background: #f8f9fa; border-left: 4px solid transparent; font-size: 0.8rem; }
    .file-item:hover { background: white; transform: translateX(5px); }
    .file-item.active { background: white; border-left-color: var(--brand-primary); font-weight: 600; color: var(--brand-primary); }
</style>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="glass-card">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-copy"></i> Log Files</h5>
            </div>
            <div class="card-body p-3">
                <div style="max-height: 400px; overflow-y: auto;">
                    <?php if (empty($files)) : ?>
                        <p class="small text-muted text-center py-4">No logs found.</p>
                    <?php endif; ?>
                    <?php foreach ($files as $f) : ?>
                        <a href="<?= base_url('development/log-viewer/' . $f) ?>" class="file-item <?= ($f == $current_file) ? 'active' : '' ?>">
                            <i class="fas fa-file-code me-2 opacity-50"></i>
                            <span class="text-truncate"><?= esc($f) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="<?= base_url('development/log-viewer/clear') ?>" class="btn btn-custom btn-outline-brand w-100 py-2" onclick="return confirmAdminAction(this, 'Hapus Semua Log?', 'Semua file log akan dihapus permanen.', 'Ya, Hapus')">
                        <i class="fas fa-trash me-2"></i> Clear
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="glass-card">
            <div class="card-header-custom d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-terminal"></i> <?= $current_file ? esc($current_file) : 'Select a file' ?></h5>
            </div>
            <div class="card-body p-4">
                <div class="terminal-container log-terminal">
                    <?php if (empty($logs)) : ?>
                        <div class="text-center opacity-30 mt-5">
                            <i class="fas fa-coffee fa-3x mb-3"></i>
                            <p>Log file is empty or not selected.</p>
                        </div>
                    <?php endif; ?>
                    <?php foreach ($logs as $log) : ?>
                        <div class="log-line <?= 'log-' . strtolower($log['type']) ?>">
                            <?= esc($log['content']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
