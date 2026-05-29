<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <p class="eyebrow mb-1">Mode Super Admin</p>
    <h2 class="section-title h3 mb-3">Pilih Mode Pengaturan</h2>
    <p class="muted-copy mb-0">Halaman ini menjadi entry point awal untuk cross-check migrasi mode pengaturan event dan kategori lomba.</p>
</section>

<div class="row g-4">
    <div class="col-12 col-lg-6">
        <section class="admin-card h-100">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <p class="eyebrow mb-1">Pengaturan Event</p>
                    <h3 class="h4 section-title mb-2">Mode Pengaturan Event</h3>
                    <p class="muted-copy mb-0">Masuk ke dashboard awal pengaturan event untuk validasi migrasi bertahap.</p>
                </div>
                <?php if (($activeMode ?? '') === 'pengaturan_event') : ?>
                    <span class="status-badge success">Aktif</span>
                <?php endif; ?>
            </div>
            <a href="<?= base_url('admin/super/mode-pengaturan-event') ?>" class="btn btn-danger rounded-pill">Masuk Mode Ini</a>
        </section>
    </div>

    <div class="col-12 col-lg-6">
        <section class="admin-card h-100">
            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                <div>
                    <p class="eyebrow mb-1">Kategori Lomba</p>
                    <h3 class="h4 section-title mb-2">Mode Pengaturan Kategori Lomba</h3>
                    <p class="muted-copy mb-0">Menyiapkan jalur CRUD kategori usia, kategori lomba, dan sub kategori seni.</p>
                </div>
                <?php if (($activeMode ?? '') === 'perngaturan_kategori_lomba') : ?>
                    <span class="status-badge success">Aktif</span>
                <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('admin/super/mode-pengaturan-kategori-lomba') ?>" class="btn btn-danger rounded-pill">Masuk Mode Ini</a>
                <a href="<?= base_url('admin/super/kategori-usia') ?>" class="btn btn-outline-secondary rounded-pill">Lihat Kategori Usia</a>
                <a href="<?= base_url('admin/super/kategori-lomba') ?>" class="btn btn-outline-secondary rounded-pill">Lihat Kategori Lomba</a>
                <a href="<?= base_url('admin/super/sub-kategori-seni') ?>" class="btn btn-outline-secondary rounded-pill">Lihat Sub Kategori Seni</a>
            </div>
        </section>
    </div>

    <div class="col-12 col-lg-6">
        <a href="<?= base_url('admin/super/mode-pembuatan-jadwal') ?>" class="super-mode-card super-mode-card-jadwal text-decoration-none text-reset d-flex flex-column h-100">
            <section class="admin-card h-100 d-flex flex-column justify-content-between super-mode-card-inner">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <div class="super-mode-icon">
                        <i class="fas fa-calendar-days"></i>
                    </div>
                    <?php if (($activeMode ?? '') === 'pembuatan_jadwal') : ?>
                        <span class="status-badge success">Aktif</span>
                    <?php endif; ?>
                </div>
                <div class="super-mode-copy">
                    <p class="eyebrow mb-2">Pembuatan Jadwal</p>
                    <h3 class="h3 section-title mb-3">Mode Pembuatan Jadwal</h3>
                    <p class="muted-copy mb-0">Masuk ke area drawing, generate bagan, penjadwalan, dan validasi jadwal pertandingan.</p>
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
