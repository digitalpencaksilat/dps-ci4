<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<div class="row g-4">
    <div class="col-md-4">
        <a href="<?= base_url('development/data-pusher') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-cloud-arrow-up"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Data Pusher</h5>
                    <p class="small text-muted mb-0">Sinkronisasi data pertandingan ke Portal Digital Silat.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/database-manager') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-database"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">DB Manager</h5>
                    <p class="small text-muted mb-0">Kelola koneksi, ekspor/impor, dan pembersihan database.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/database-setup') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">DB Setup</h5>
                    <p class="small text-muted mb-0">Inisialisasi struktur database lokal dengan cepat.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/admin-utility') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Admin Utility</h5>
                    <p class="small text-muted mb-0">Reset password BCrypt dan utilitas akun admin.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/system-health') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-heart-pulse"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">System Health</h5>
                    <p class="small text-muted mb-0">Cek status folder, izin akses, dan lingkungan PHP.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/log-viewer') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-terminal"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Log Viewer</h5>
                    <p class="small text-muted mb-0">Pantau aktivitas dan error sistem secara real-time.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6">
        <a href="<?= base_url('development/explorer') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-signs-post"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Route Explorer</h5>
                    <p class="small text-muted mb-0">Lihat semua endpoint controller dan mapping routing.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6">
        <a href="<?= base_url('development/purger') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-trash"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Storage Purger</h5>
                    <p class="small text-muted mb-0">Bersihkan folder temp, cache, dan file sampah lainnya.</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?= $this->endSection() ?>
