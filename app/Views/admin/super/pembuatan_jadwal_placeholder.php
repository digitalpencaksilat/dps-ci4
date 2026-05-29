<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card">
    <p class="eyebrow mb-1">Mode Pembuatan Jadwal</p>
    <h2 class="section-title h3 mb-2"><?= esc($moduleTitle ?? 'Modul') ?></h2>
    <p class="muted-copy mb-4"><?= esc($moduleDescription ?? 'Modul ini sedang dalam proses migrasi ke CodeIgniter 4.') ?></p>

    <div class="alert alert-warning mb-0">
        Halaman ini masih stub. Berikutnya: parity fitur CI3 + integrasi modul jadwal yang sudah ada.
    </div>
</section>
<?= $this->endSection() ?>
