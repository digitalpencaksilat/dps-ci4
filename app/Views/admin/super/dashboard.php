<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-4 super-mode-grid align-items-stretch">
    <div class="col-12 col-lg-6">
        <a href="<?= base_url('admin/super/mode-pengaturan-event') ?>" class="super-mode-card super-mode-card-event text-decoration-none text-reset d-flex flex-column h-100">
            <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <div class="super-mode-icon">
                        <i class="fas fa-sliders"></i>
                    </div>
                    <?php if (($activeMode ?? '') === 'pengaturan_event') : ?>
                        <span class="status-badge success">Aktif</span>
                    <?php endif; ?>
                </div>
                <div class="super-mode-copy">
                    <p class="eyebrow mb-2">Pengaturan Event</p>
                    <h3 class="h3 section-title mb-3">Mode Pengaturan Event</h3>
                    <p class="muted-copy mb-0">Masuk ke area pengaturan event dengan navigasi khusus pengaturan event.</p>
                </div>
                <div class="super-mode-link mt-4">
                    <span>Pilih Mode</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </section>
        </a>
    </div>

    <div class="col-12 col-lg-6">
        <a href="<?= base_url('admin/super/mode-pengaturan-kategori-lomba') ?>" class="super-mode-card super-mode-card-kategori text-decoration-none text-reset d-flex flex-column h-100">
            <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <div class="super-mode-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <?php if (($activeMode ?? '') === 'perngaturan_kategori_lomba') : ?>
                        <span class="status-badge success">Aktif</span>
                    <?php endif; ?>
                </div>
                <div class="super-mode-copy">
                    <p class="eyebrow mb-2">Kategori Lomba</p>
                    <h3 class="h3 section-title mb-3">Mode Pengaturan Kategori Lomba</h3>
                    <p class="muted-copy mb-0">Masuk ke area kategori usia, kategori lomba, dan sub kategori seni.</p>
                </div>
                <div class="super-mode-link mt-4">
                    <span>Pilih Mode</span>
                    <i class="fas fa-arrow-right"></i>
                </div>
            </section>
        </a>
    </div>
</div>
<?= $this->endSection() ?>
