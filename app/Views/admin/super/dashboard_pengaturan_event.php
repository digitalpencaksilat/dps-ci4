<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Event</p>
            <h2 class="section-title h3 mb-3">Dashboard Pengaturan Event</h2>
            <p class="muted-copy mb-0">Halaman awal migrasi pengaturan event sudah aktif. Data summary CI3 akan dimigrasikan pada tahap berikutnya.</p>
        </div>
        <div class="d-flex flex-wrap align-items-start gap-2">
            <span class="status-badge <?= ($activeMode ?? '') === 'pengaturan_event' ? 'success' : 'warning' ?>">
                Mode: <?= esc(($activeMode ?? '') === 'pengaturan_event' ? 'pengaturan_event' : 'belum aktif') ?>
            </span>
            <a href="<?= base_url('admin/super/menu-tipe') ?>" class="btn btn-outline-light rounded-pill">Ganti Mode</a>
        </div>
    </div>
</section>

<div class="row g-4">
    <div class="col-12 col-md-6 col-xl-3">
        <section class="admin-card h-100">
            <p class="eyebrow mb-1">Stage 1</p>
            <h3 class="h5 mb-2">Halaman Super</h3>
            <p class="muted-copy mb-0">Route, controller, dan view awal tersedia untuk cross-check.</p>
        </section>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <section class="admin-card h-100">
            <p class="eyebrow mb-1">Berikutnya</p>
            <h3 class="h5 mb-2">Summary Event</h3>
            <p class="muted-copy mb-0">Query dashboard CI3 akan dipindahkan ke service/model CI4.</p>
        </section>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <section class="admin-card h-100">
            <p class="eyebrow mb-1">Berikutnya</p>
            <h3 class="h5 mb-2">Kategori Lomba</h3>
            <p class="muted-copy mb-0">CRUD kategori usia, kategori lomba, dan sub kategori seni akan dibuat bertahap.</p>
        </section>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <section class="admin-card h-100">
            <p class="eyebrow mb-1">Keamanan</p>
            <h3 class="h5 mb-2">Role Super Admin</h3>
            <p class="muted-copy mb-0">Akses halaman ini dilindungi filter <code>adminrole:super_admin</code>.</p>
        </section>
    </div>
</div>
<?= $this->endSection() ?>
