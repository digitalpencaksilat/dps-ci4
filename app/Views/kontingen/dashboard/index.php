<?= $this->extend('layouts/kontingen') ?>

<?= $this->section('content') ?>
<section class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="stat-card border-danger-subtle">
            <div>
                <p class="stat-label">Jumlah Atlet</p>
                <h2 class="stat-value"><?= esc((string) ($summary['jumlah_atlet'] ?? 0)) ?></h2>
            </div>
            <div class="stat-icon bg-danger-subtle text-danger">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card border-warning-subtle">
            <div>
                <p class="stat-label">Kategori Tanding</p>
                <h2 class="stat-value"><?= esc((string) ($summary['jumlah_tanding'] ?? 0)) ?></h2>
            </div>
            <div class="stat-icon bg-warning-subtle text-warning">
                <i class="fas fa-fist-raised"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card border-info-subtle">
            <div>
                <p class="stat-label">Kategori Seni</p>
                <h2 class="stat-value"><?= esc((string) ($summary['jumlah_seni'] ?? 0)) ?></h2>
            </div>
            <div class="stat-icon bg-info-subtle text-info">
                <i class="fas fa-drum"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stat-card border-success-subtle">
            <div>
                <p class="stat-label">Tagihan Aktif</p>
                <h2 class="stat-value"><?= esc((string) ($summary['jumlah_tagihan'] ?? 0)) ?></h2>
            </div>
            <div class="stat-icon bg-success-subtle text-success">
                <i class="fas fa-wallet"></i>
            </div>
        </div>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-12">
        <div class="panel-card h-100">
            <div class="panel-header">
                <div>
                    <p class="eyebrow mb-1">Akses Cepat</p>
                    <h3 class="panel-title mb-0">Menu Utama Kontingen</h3>
                </div>
            </div>
            <p class="text-muted mb-4">Gunakan menu berikut untuk mengelola peserta, kategori tanding, kategori seni, dan pembayaran kontingen.</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <a href="<?= base_url('kontingen/peserta') ?>" class="shortcut-card text-decoration-none text-reset">
                        <div>
                            <h4>Peserta</h4>
                            <p>Kelola data atlet dan biodata peserta.</p>
                        </div>
                        <span class="badge text-bg-danger">Aktif</span>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="<?= base_url('kontingen/tanding') ?>" class="shortcut-card text-decoration-none text-reset">
                        <div>
                            <h4>Kategori Tanding</h4>
                            <p>Pilih dan kelola kategori tanding atlet.</p>
                        </div>
                        <span class="badge text-bg-danger">Aktif</span>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="<?= base_url('kontingen/seni') ?>" class="shortcut-card text-decoration-none text-reset">
                        <div>
                            <h4>Kategori Seni</h4>
                            <p>Atur kelompok peserta seni dan kategorinya.</p>
                        </div>
                        <span class="badge text-bg-danger">Aktif</span>
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="<?= base_url('kontingen/pembayaran') ?>" class="shortcut-card text-decoration-none text-reset">
                        <div>
                            <h4>Pembayaran</h4>
                            <p>Lihat checkout, bukti bayar, dan status transaksi.</p>
                        </div>
                        <span class="badge text-bg-danger">Aktif</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
