<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Perolehan Medali</p>
            <h2 class="section-title h3 mb-2"><?= esc($reportTitle ?? $title ?? 'Akumulasi Perolehan Medali') ?></h2>
            <p class="muted-copy mb-0">Total medali tanding dan seni seluruh kategori usia.</p>
        </div>
        <button type="button" class="btn btn-outline-danger rounded-pill px-4" onclick="window.print()">Print</button>
    </div>
    <?= view('shared_components/medal_tally/contingent_table', ['rows' => $rows ?? [], 'tableId' => 'aggregateMedalTable']) ?>
</section>
<?= $this->endSection() ?>
