<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="admin-card">
    <div class="mb-4">
        <p class="eyebrow mb-1">Pesilat Terbaik</p>
        <h3 class="section-title h4 mb-0">Urutan poin battle seni</h3>
        <p class="muted-copy mb-0 mt-2">Daftar battle seni non-BYE dengan susunan poin dan lawan yang mengikuti tampilan sekretariat pada sistem lama.</p>
    </div>

    <?= view('admin/sekretariat/battle_seni/_urutan_poin_table', ['rows' => $rows ?? []]) ?>
</section>
<?= $this->endSection() ?>
