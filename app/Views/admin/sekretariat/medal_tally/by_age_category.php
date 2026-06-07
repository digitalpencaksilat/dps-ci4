<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$reportName = (string) ($reportTitle ?? $title ?? 'Perolehan Medali Per Kategori Usia');
$brandName = (string) (get_setting('brand_name') ?? 'Digital Pencak Silat');
$brandAbbr = strtolower((string) (get_setting('brand_abbreviation') ?? 'dps'));
$brandLogoUrl = base_url('assets/images/brand/' . $brandAbbr . '/logo.png');
$categoryRows = $categoryRows ?? [];
$exportTables = [];
?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Perolehan Medali</p>
            <h2 class="section-title h3 mb-2"><?= esc($reportName) ?></h2>
            <p class="muted-copy mb-0">Rekap medali kontingen dipisahkan berdasarkan kategori usia.</p>
        </div>
    </div>

    <?php if ($categoryRows === []) : ?>
        <div class="text-center muted-copy py-4">Belum ada kategori usia.</div>
    <?php else : ?>
        <ul class="nav nav-pills gap-2 mb-4" role="tablist">
            <?php foreach (array_keys($categoryRows) as $index => $categoryName) : ?>
                <?php $tabId = 'category-' . md5($categoryName); ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#<?= esc($tabId) ?>" type="button" role="tab"><?= esc($categoryName) ?></button>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="tab-content">
            <?php foreach ($categoryRows as $categoryName => $rows) : ?>
                <?php
                $tabId = 'category-' . md5($categoryName);
                $tableId = $tabId . '-table';
                $exportTables[] = [
                    'id' => $tableId,
                    'title' => strtoupper($reportName . ' - ' . $categoryName),
                    'filename' => $reportName . ' - ' . $categoryName . ' - ' . $eventName,
                ];
                ?>
                <div class="tab-pane fade <?= array_key_first($categoryRows) === $categoryName ? 'show active' : '' ?>" id="<?= esc($tabId) ?>" role="tabpanel">
                    <h3 class="section-title h5 mb-3"><?= esc($categoryName) ?></h3>
                    <?= view('shared_components/medal_tally/contingent_table', ['rows' => $rows, 'tableId' => $tableId]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        var medalWatermark = {
            logo: <?= json_encode($brandLogoUrl) ?>,
            text: 'Powered by <strong>' + <?= json_encode($brandName) ?> + '</strong> &copy; ' + new Date().getFullYear()
        };
        var medalPrintHeaderTpl = <?= json_encode(view('shared_components/print/medal_export_header', ['title' => '__TITLE__', 'subtitle' => $eventName])) ?>;
        var exportTables = <?= json_encode($exportTables) ?>;
        exportTables.forEach(function(t) {
            window.initAdminExportTable('#' + t.id, {
                title: t.title,
                filename: t.filename,
                orientation: 'landscape',
                preset: 'wide-report',
                medalTally: true,
                printHeaderHtml: medalPrintHeaderTpl.replace('__TITLE__', t.title),
                watermark: medalWatermark
            });
        });
    });
</script>
<?= $this->endSection() ?>
