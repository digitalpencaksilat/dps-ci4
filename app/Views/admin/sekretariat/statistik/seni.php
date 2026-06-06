<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $summary = $stats['summary'] ?? []; ?>
<section class="admin-card mb-4"><div><p class="eyebrow mb-1">Statistik Sekretariat</p><h2 class="section-title h3 mb-2">Statistik seni</h2><p class="muted-copy mb-0">Migrasi statistik seni CI3 dengan pemisahan yang lebih jelas antara distribusi jenis seni, pool, dan kategori usia.</p></div></section>

<section class="row g-4 mb-4">
    <?php foreach ([
        ['label' => 'Total Peserta', 'value' => $summary['totalPeserta'] ?? 0],
        ['label' => 'Jumlah Pool', 'value' => $summary['jumlahPool'] ?? 0],
        ['label' => 'Tunggal', 'value' => $summary['tunggal'] ?? 0],
        ['label' => 'Ganda', 'value' => $summary['ganda'] ?? 0],
        ['label' => 'Beregu', 'value' => $summary['beregu'] ?? 0],
    ] as $card) : ?>
        <div class="col-12 col-sm-6 col-xl-4"><div class="admin-card h-100"><div class="small muted-copy mb-1"><?= esc($card['label']) ?></div><div class="h3 section-title mb-0"><?= esc((string) $card['value']) ?></div></div></div>
    <?php endforeach; ?>
</section>

<section class="row g-4 mb-4">
    <div class="col-12 col-xl-5"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Distribusi Jenis Seni</h3><p class="muted-copy mb-3">Proporsi tunggal, ganda, dan beregu.</p><div id="chartSeniJenisDistribution" style="min-height: 340px;"></div></div></div>
    <div class="col-12 col-xl-7"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Jenis Seni per Kategori</h3><p class="muted-copy mb-3">Perbandingan peserta seni per kategori usia dan jenis kelamin.</p><div id="chartSeniJenisByCategory" style="min-height: 340px;"></div></div></div>
    <div class="col-12"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Jumlah Pool per Kategori</h3><p class="muted-copy mb-3">Pool aktif untuk tiap kategori seni.</p><div id="chartSeniPoolByCategory" style="min-height: 340px;"></div></div></div>
</section>

<section class="admin-card">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3"><div><h3 class="section-title h5 mb-1">Ringkasan Kategori Seni</h3><p class="muted-copy mb-0">Padanan statistik seni CI3 dengan fokus ringkas dan mudah dibaca.</p></div></div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller"><table class="table admin-table admin-datatable-export align-middle mb-0"><thead><tr><th>Kategori</th><th class="text-end">Tunggal</th><th class="text-end">Ganda</th><th class="text-end">Beregu</th><th class="text-end">Jumlah Pool</th></tr></thead><tbody><?php foreach (($stats['tableRows'] ?? []) as $row) : ?><tr><td><?= esc($row['kategori'] ?? '-') ?></td><td class="text-end"><?= esc((string) ($row['jumlah_kelompok_peserta_seni_tunggal'] ?? 0)) ?></td><td class="text-end"><?= esc((string) ($row['jumlah_kelompok_peserta_seni_ganda'] ?? 0)) ?></td><td class="text-end"><?= esc((string) ($row['jumlah_kelompok_peserta_seni_beregu'] ?? 0)) ?></td><td class="text-end"><?= esc((string) ($row['jumlah_pool'] ?? 0)) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const cssVar = (name, fallback) => (getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback);
    const brandPrimary = cssVar('--brand-primary', '#c60000');
    const brandGold = cssVar('--brand-secondary', '#c5a017');
    const brandDark = cssVar('--admin-accent-dark', '#8f0b14');
    const gridColor = cssVar('--admin-border', '#f1f5f9');
    const seniColors = [brandPrimary, brandGold, brandDark];
    const donut = (labels, series) => ({ chart: { type: 'donut' }, labels, series, colors: seniColors, legend: { position: 'bottom' }, dataLabels: { enabled: true }, stroke: { width: 0 } });
    const stackedBar = (categories, series) => ({ chart: { type: 'bar', stacked: true, toolbar: { show: false } }, series, colors: seniColors, plotOptions: { bar: { borderRadius: 4 } }, dataLabels: { enabled: false }, xaxis: { categories }, grid: { borderColor: gridColor }, legend: { position: 'bottom' } });
    const horizontalBar = (categories, data) => ({ chart: { type: 'bar', toolbar: { show: false } }, series: [{ name: 'Pool', data }], colors: [brandDark], plotOptions: { bar: { horizontal: true, borderRadius: 6 } }, dataLabels: { enabled: false }, xaxis: { categories }, grid: { borderColor: gridColor } });
    new ApexCharts(document.querySelector('#chartSeniJenisDistribution'), donut(<?= json_encode($stats['jenisDistribution']['labels'] ?? []) ?>, <?= json_encode($stats['jenisDistribution']['series'] ?? []) ?>)).render();
    new ApexCharts(document.querySelector('#chartSeniJenisByCategory'), stackedBar(<?= json_encode($stats['jenisByCategory']['categories'] ?? []) ?>, <?= json_encode($stats['jenisByCategory']['series'] ?? []) ?>)).render();
    new ApexCharts(document.querySelector('#chartSeniPoolByCategory'), horizontalBar(<?= json_encode($stats['poolByCategory']['categories'] ?? []) ?>, <?= json_encode($stats['poolByCategory']['series'] ?? []) ?>)).render();
</script>
<?= $this->endSection() ?>
