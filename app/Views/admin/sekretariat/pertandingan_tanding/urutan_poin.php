<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="admin-card">
    <div class="mb-4">
        <p class="eyebrow mb-1">Pesilat Terbaik</p>
        <h3 class="section-title h4 mb-0">Urutan poin pertandingan</h3>
        <p class="muted-copy mb-0 mt-2">Daftar pertandingan non-BYE untuk sekretariat dengan susunan arena, poin, dan peserta yang mendekati tampilan CI3.</p>
    </div>

    <?= view('admin/sekretariat/pertandingan_tanding/_urutan_poin_table', ['rows' => $rows ?? []]) ?>
</section>
<?= $this->endSection() ?>
