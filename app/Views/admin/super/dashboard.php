<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card">
    <p class="eyebrow mb-1">Portal Admin</p>
    <h2 class="section-title h3 mb-3"><?= esc($title ?? 'Dashboard Super Admin') ?></h2>
    <p class="muted-copy mb-4">Landing super admin sudah aktif untuk cross-check migrasi mode pengaturan event dan kategori lomba.</p>
    <div class="d-flex flex-wrap gap-3">
        <span class="status-badge warning">Role aktif: super_admin</span>
        <span class="status-badge neutral">User: <?= esc($adminName ?? 'Super Admin') ?></span>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-4">
        <a href="<?= base_url('admin/super/menu-tipe') ?>" class="btn btn-primary rounded-pill">Pilih Mode Super Admin</a>
        <a href="<?= base_url('admin/super/dashboard-pengaturan-event') ?>" class="btn btn-outline-light rounded-pill">Dashboard Pengaturan Event</a>
    </div>
</section>
<?= $this->endSection() ?>
