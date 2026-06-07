<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$exportTitle = strtoupper((string) ($reportTitle ?? $title ?? 'Akumulasi Perolehan Medali'));
$exportFilename = ($reportTitle ?? 'Akumulasi Perolehan Medali') . ' - ' . $eventName;
$brandName = (string) (get_setting('brand_name') ?? 'Digital Pencak Silat');
$brandAbbr = strtolower((string) (get_setting('brand_abbreviation') ?? 'dps'));
$brandLogoUrl = base_url('assets/images/brand/' . $brandAbbr . '/logo.png');
$printHeaderHtml = view('shared_components/print/medal_export_header', [
    'title' => $exportTitle,
    'subtitle' => $eventName,
]);
?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Perolehan Medali</p>
            <h2 class="section-title h3 mb-2"><?= esc($reportTitle ?? $title ?? 'Akumulasi Perolehan Medali') ?></h2>
            <p class="muted-copy mb-0">Total medali tanding dan seni seluruh kategori usia.</p>
        </div>
    </div>
    <?= view('shared_components/medal_tally/contingent_table', ['rows' => $rows ?? [], 'tableId' => 'aggregateMedalTable']) ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        window.initAdminExportTable('#aggregateMedalTable', {
            title: <?= json_encode($exportTitle) ?>,
            filename: <?= json_encode($exportFilename) ?>,
            orientation: 'landscape',
            preset: 'wide-report',
            medalTally: true,
            printHeaderHtml: <?= json_encode($printHeaderHtml) ?>,
            watermark: {
                logo: <?= json_encode($brandLogoUrl) ?>,
                text: 'Powered by <strong>' + <?= json_encode($brandName) ?> + '</strong> &copy; ' + new Date().getFullYear()
            }
        });
    });
</script>
<?= $this->endSection() ?>
