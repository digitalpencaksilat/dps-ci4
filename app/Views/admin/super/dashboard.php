<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card">
    <p class="eyebrow mb-1">Portal Admin</p>
    <h2 class="section-title h3 mb-3"><?= esc($title ?? 'Dashboard Super Admin') ?></h2>
    <p class="muted-copy mb-4">Landing minimal super admin sudah aktif agar alur login admin role ini tidak buntu. Area super admin penuh tetap menunggu migrasi modul lanjutan.</p>
    <div class="d-flex flex-wrap gap-3">
        <span class="status-badge warning">Role aktif: super_admin</span>
        <span class="status-badge neutral">User: <?= esc($adminName ?? 'Super Admin') ?></span>
    </div>
</section>
<?= $this->endSection() ?>
