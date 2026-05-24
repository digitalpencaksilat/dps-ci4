<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Perolehan Medali</p>
            <h2 class="section-title h3 mb-2"><?= esc($reportTitle ?? $title ?? 'Perolehan Medali Berdasarkan Sekolah') ?></h2>
            <p class="muted-copy mb-0">Medali tanding dihitung dari sekolah atlet. Medali seni mengikuti sekolah pertama dalam kelompok.</p>
        </div>
        <button type="button" class="btn btn-outline-danger rounded-pill px-4" onclick="window.print()">Print</button>
    </div>

    <?php $categoryRows = $categoryRows ?? []; ?>
    <?php if ($categoryRows === []) : ?>
        <div class="text-center muted-copy py-4">Belum ada kategori usia.</div>
    <?php else : ?>
        <ul class="nav nav-pills gap-2 mb-4" role="tablist">
            <?php foreach (array_keys($categoryRows) as $index => $categoryName) : ?>
                <?php $tabId = 'school-category-' . md5($categoryName); ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#<?= esc($tabId) ?>" type="button" role="tab"><?= esc($categoryName) ?></button>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content">
            <?php foreach ($categoryRows as $categoryName => $rows) : ?>
                <?php $tabId = 'school-category-' . md5($categoryName); ?>
                <div class="tab-pane fade <?= array_key_first($categoryRows) === $categoryName ? 'show active' : '' ?>" id="<?= esc($tabId) ?>" role="tabpanel">
                    <h3 class="section-title h5 mb-3"><?= esc($categoryName) ?></h3>
                    <?= view('shared_components/medal_tally/school_table', ['rows' => $rows, 'tableId' => $tabId . '-table']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
