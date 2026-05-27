<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
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
            <a href="<?= base_url('admin/super/mode-pengaturan-event') ?>" class="btn btn-primary rounded-pill">Masuk Mode Ini</a>
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
                <a href="<?= base_url('admin/super/mode-pengaturan-kategori-lomba') ?>" class="btn btn-primary rounded-pill">Masuk Mode Ini</a>
                <a href="<?= base_url('admin/super/kategori-usia') ?>" class="btn btn-outline-light rounded-pill">Lihat Kategori Usia</a>
                <a href="<?= base_url('admin/super/kategori-lomba') ?>" class="btn btn-outline-light rounded-pill">Lihat Kategori Lomba</a>
                <a href="<?= base_url('admin/super/sub-kategori-seni') ?>" class="btn btn-outline-light rounded-pill">Lihat Sub Kategori Seni</a>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection() ?>
