<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="admin-card">
    <div class="mb-4">
        <p class="eyebrow mb-1">Pesilat Terbaik</p>
        <h3 class="section-title h4 mb-0">Urutan penampilan seni pool</h3>
        <p class="muted-copy mb-0 mt-2">Data penampilan seni sistem pool yang sudah tampil, disusun mendekati tabel urutan poin pada aplikasi CI3.</p>
    </div>

    <?= view('admin/sekretariat/pool_seni/_urutan_poin_table', ['rows' => $rows ?? []]) ?>
</section>
<?= $this->endSection() ?>
