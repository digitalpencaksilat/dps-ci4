<?= $this->extend('development/layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .tool-risk-badges {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-bottom: 0.75rem;
    }

    .tool-risk-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .tool-risk-badge.read-only {
        background: rgba(14, 165, 233, 0.15);
        color: #0369a1;
    }

    .tool-risk-badge.sensitive {
        background: rgba(245, 158, 11, 0.18);
        color: #b45309;
    }

    .tool-risk-badge.destructive {
        background: rgba(220, 38, 38, 0.14);
        color: #b91c1c;
    }
</style>

<div class="row g-4">
    <div class="col-md-4">
        <a href="<?= base_url('development/data-pusher') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="tool-risk-badges">
                        <span class="tool-risk-badge sensitive">Sensitive</span>
                    </div>
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-cloud-arrow-up"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Data Pusher</h5>
                    <p class="small text-muted mb-0">Sinkronisasi data pertandingan ke Portal Digital Silat. Gunakan hanya saat target sinkronisasi sudah diverifikasi.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/database-manager') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="tool-risk-badges">
                        <span class="tool-risk-badge destructive">Destructive</span>
                        <span class="tool-risk-badge sensitive">Sensitive</span>
                    </div>
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-database"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">DB Manager</h5>
                    <p class="small text-muted mb-0">Kelola koneksi, ekspor/impor, dan pembersihan database. Perubahan dapat memengaruhi seluruh data lokal.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/database-setup') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="tool-risk-badges">
                        <span class="tool-risk-badge destructive">Destructive</span>
                    </div>
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">DB Setup</h5>
                    <p class="small text-muted mb-0">Inisialisasi struktur database lokal dengan cepat. Jalankan hanya pada environment yang memang siap direset.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/admin-utility') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="tool-risk-badges">
                        <span class="tool-risk-badge sensitive">Sensitive</span>
                    </div>
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Admin Utility</h5>
                    <p class="small text-muted mb-0">Reset password BCrypt dan utilitas akun admin. Gunakan hanya untuk pemulihan akun yang terotorisasi.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-4">
        <a href="<?= base_url('development/system-health') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="tool-risk-badges">
                        <span class="tool-risk-badge read-only">Read-only</span>
                    </div>
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
                    <div class="tool-risk-badges">
                        <span class="tool-risk-badge read-only">Read-only</span>
                        <span class="tool-risk-badge sensitive">Sensitive</span>
                    </div>
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-terminal"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Log Viewer</h5>
                    <p class="small text-muted mb-0">Pantau aktivitas dan error sistem secara real-time. Hindari membagikan log tanpa meninjau data sensitif.</p>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-6">
        <a href="<?= base_url('development/explorer') ?>" class="text-decoration-none">
            <div class="glass-card tool-card">
                <div class="card-body p-4 text-center">
                    <div class="tool-risk-badges">
                        <span class="tool-risk-badge read-only">Read-only</span>
                    </div>
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
                    <div class="tool-risk-badges">
                        <span class="tool-risk-badge destructive">Destructive</span>
                    </div>
                    <div class="icon-box mx-auto mb-3">
                        <i class="fas fa-trash"></i>
                    </div>
                    <h5 class="font-oswald text-dark mb-2">Storage Purger</h5>
                    <p class="small text-muted mb-0">Bersihkan folder temp, cache, dan file sampah lainnya. Pastikan tidak ada file debug yang masih dibutuhkan.</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?= $this->endSection() ?>
