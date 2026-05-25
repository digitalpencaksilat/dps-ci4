<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .danger-zone { background: #fffafa; border: 2px dashed #ffcccc; }
    .db-info-box { background: var(--brand-dark); color: white; border-radius: 15px; padding: 20px; height: 100%; position: relative; overflow: hidden; }
    .db-info-box::after { content: '\f1c0'; font-family: FontAwesome; position: absolute; right: -20px; bottom: -20px; font-size: 6rem; opacity: 0.05; }
    .status-indicator { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; display: inline-block; margin-right: 8px; box-shadow: 0 0 10px #4ade80; }
</style>

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

<div class="row g-4">
    <div class="col-md-4">
        <div class="db-info-box">
            <h6 class="font-oswald text-muted mb-3" style="color: rgba(255,255,255,0.5) !important; font-size: 0.75rem;">Koneksi Aktif</h6>
            <div class="d-flex align-items-center mb-1">
                <span class="status-indicator"></span>
                <h3 class="font-oswald mb-0 text-white"><?= esc($current_db) ?></h3>
            </div>
            <p class="small opacity-50 mb-3">
                <?= esc(env('database.default.hostname') ?? '127.0.0.1') ?> (<?= esc(env('database.default.DBDriver') ?? 'MySQLi') ?>)
            </p>

            <div class="row text-center mt-auto">
                <div class="col-6 border-end border-secondary">
                    <h4 class="font-oswald mb-0"><?= count($tables) ?></h4>
                    <span class="small opacity-50" style="font-size: 0.65rem;">Tabel</span>
                </div>
                <div class="col-6">
                    <h4 class="font-oswald mb-0">Active</h4>
                    <span class="small opacity-50" style="font-size: 0.65rem;">Status</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="glass-card h-100">
            <div class="card-header-custom">
                <h5><i class="fas fa-right-left"></i> Ganti Database</h5>
            </div>
            <div class="card-body p-4">
                <?php if ($all_databases === []) : ?>
                    <p class="text-muted small mb-0">No databases found.</p>
                <?php else : ?>
                    <form action="<?= base_url('development/database-manager/switch') ?>" method="POST" class="row g-3">
                        <div class="col-md-8">
                            <select name="new_database" class="form-select form-select-custom">
                                <?php foreach ($all_databases as $db) : ?>
                                    <option value="<?= esc($db) ?>" <?= ($db === $current_db) ? 'selected' : '' ?>>
                                        <?= esc($db) ?><?= ($db === $current_db) ? ' (Aktif)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-custom btn-brand w-100">
                                <i class="fas fa-check me-1"></i> Ganti
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="glass-card">
            <div class="card-header-custom">
                <h5><i class="fas fa-cloud-arrow-down"></i> Ekspor & Impor</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-2">
                    <a href="<?= base_url('development/database-manager/export') ?>" class="btn btn-custom btn-outline-brand w-100">
                        <i class="fas fa-download me-2"></i> Ekspor (.sql)
                    </a>
                    <hr class="my-3 opacity-10">
                    <form action="<?= base_url('development/database-manager/import') ?>" method="POST" enctype="multipart/form-data">
                        <input type="file" name="sql_file" class="form-control form-control-custom mb-2" required>
                        <button type="submit" class="btn btn-custom btn-brand w-100">
                            <i class="fas fa-upload me-2"></i> Unggah & Timpa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="glass-card danger-zone border-brand">
            <div class="card-header-custom border-brand" style="background: rgba(198, 0, 0, 0.03);">
                <h5 class="text-danger"><i class="fas fa-triangle-exclamation"></i> Zona Bahaya</h5>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <button type="button" class="btn btn-custom btn-outline-brand w-100" data-bs-toggle="modal" data-bs-target="#emptyModal">
                        <i class="fas fa-eraser me-2"></i> Bersihkan Data
                    </button>
                    <button type="button" class="btn btn-custom btn-brand w-100" data-bs-toggle="modal" data-bs-target="#dropModal">
                        <i class="fas fa-bomb me-2"></i> Hancurkan Tabel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="emptyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <form action="<?= base_url('development/database-manager/empty-tables') ?>" method="POST">
                <div class="modal-body p-5">
                    <div class="text-center mb-4">
                        <div class="icon-box mx-auto mb-3">
                            <i class="fas fa-eraser"></i>
                        </div>
                        <h4 class="font-oswald">Konfirmasi Reset</h4>
                        <p class="small text-muted">Seluruh data pertandingan akan dihapus secara permanen.</p>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-oswald small text-muted">Kode Keamanan</label>
                        <input type="password" name="pass_code" class="form-control form-control-custom" placeholder="Masukkan kode keamanan" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-custom btn-brand">Eksekusi Pembersihan</button>
                        <button type="button" class="btn btn-link text-muted font-oswald text-decoration-none" data-bs-dismiss="modal">Batalkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="dropModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <form action="<?= base_url('development/database-manager/drop-tables') ?>" method="POST">
                <div class="modal-body p-5">
                    <div class="text-center mb-4">
                        <div class="icon-box mx-auto mb-3 bg-danger text-white shadow">
                            <i class="fas fa-bomb"></i>
                        </div>
                        <h4 class="font-oswald text-danger">Tindakan Destruktif</h4>
                        <p class="small text-muted">Seluruh tabel akan di-DROP dari database.</p>
                    </div>
                    <div class="mb-4">
                        <label class="form-label font-oswald small text-muted">Konfirmasi Kode Keamanan</label>
                        <input type="password" name="pass_code" class="form-control form-control-custom" placeholder="Diperlukan untuk konfirmasi" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-custom btn-brand">Hancurkan Database</button>
                        <button type="button" class="btn btn-link text-muted font-oswald text-decoration-none" data-bs-dismiss="modal">Batalkan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
