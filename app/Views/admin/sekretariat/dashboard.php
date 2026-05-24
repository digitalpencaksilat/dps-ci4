<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card admin-landing-card">
    <p class="eyebrow mb-1">Portal Admin</p>
    <h2 class="section-title h3 mb-3"><?= esc($title ?? 'Dashboard Sekretariat') ?></h2>
    <p class="muted-copy mb-4">Landing minimal sekretariat sudah aktif agar login admin role ini tidak berhenti di halaman login. Migrasi modul sekretariat penuh dilanjutkan pada fase berikutnya.</p>
    <div class="d-flex flex-wrap gap-3">
        <span class="status-badge warning">Role aktif: sekretariat</span>
        <span class="status-badge neutral">User: <?= esc($adminName ?? 'Admin Sekretariat') ?></span>
    </div>
</section>
<?= $this->endSection() ?>
