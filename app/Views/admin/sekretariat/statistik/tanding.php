<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $summary = $stats['summary'] ?? []; ?>
<section class="admin-card mb-4"><div><p class="eyebrow mb-1">Statistik Sekretariat</p><h2 class="section-title h3 mb-2">Statistik tanding</h2><p class="muted-copy mb-0">Migrasi statistik tanding CI3 dengan fokus yang lebih kuat pada distribusi, pembayaran, pool, dan jumlah partai.</p></div></section>

<section class="row g-4 mb-4">
    <?php foreach ([
        ['label' => 'Total Peserta', 'value' => $summary['totalPeserta'] ?? 0],
        ['label' => 'Jumlah Pool', 'value' => $summary['jumlahPool'] ?? 0],
        ['label' => 'Peserta Prestasi', 'value' => $summary['pesertaPrestasi'] ?? 0],
        ['label' => 'Peserta Pemasalan', 'value' => $summary['pesertaPemasalan'] ?? 0],
        ['label' => 'Prediksi Partai Prestasi', 'value' => $summary['prediksiPartaiPrestasi'] ?? 0],
        ['label' => 'Prediksi Partai Pemasalan', 'value' => $summary['prediksiPartaiPemasalan'] ?? 0],
    ] as $card) : ?>
        <div class="col-12 col-sm-6 col-xl-4"><div class="admin-card h-100"><div class="small muted-copy mb-1"><?= esc($card['label']) ?></div><div class="h3 section-title mb-0"><?= esc((string) $card['value']) ?></div></div></div>
    <?php endforeach; ?>
</section>

<section class="row g-4 mb-4">
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Pembayaran Peserta per Kategori</h3><p class="muted-copy mb-3">Perbandingan peserta lunas dan belum lunas.</p><div id="chartTandingPayment" style="min-height: 360px;"></div></div></div>
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Jumlah Pool per Kategori</h3><p class="muted-copy mb-3">Distribusi pool tanding untuk setiap kategori usia.</p><div id="chartTandingPool" style="min-height: 360px;"></div></div></div>
    <div class="col-12"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Jumlah Partai per Kategori</h3><p class="muted-copy mb-3">Partai non-BYE yang sudah terbentuk pada tiap kategori.</p><div id="chartTandingMatches" style="min-height: 360px;"></div></div></div>
</section>

<section class="admin-card">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3"><div><h3 class="section-title h5 mb-1">Ringkasan Kategori Tanding</h3><p class="muted-copy mb-0">Padanan tabel statistik tanding CI3 dalam format yang lebih bersih.</p></div></div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller"><table class="table admin-table admin-datatable align-middle mb-0"><thead><tr><th>Kategori</th><th class="text-end">Peserta</th><th class="text-end">Peserta Lunas</th><th class="text-end">Belum Lunas</th><th class="text-end">Jumlah Pool</th><th class="text-end">Jumlah Partai</th></tr></thead><tbody><?php foreach (($stats['tableRows'] ?? []) as $row) : ?><tr><td><?= esc($row['kategori'] ?? '-') ?></td><td class="text-end"><?= esc((string) ($row['jumlah_peserta_tanding'] ?? 0)) ?></td><td class="text-end"><?= esc((string) ($row['jumlah_peserta_tanding_lunas'] ?? 0)) ?></td><td class="text-end"><?= esc((string) ($row['peserta_belum_lunas'] ?? 0)) ?></td><td class="text-end"><?= esc((string) ($row['jumlah_pool'] ?? 0)) ?></td><td class="text-end"><?= esc((string) ($row['jumlah_partai'] ?? 0)) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const stackedBar = (categories, series) => ({ chart: { type: 'bar', stacked: true, toolbar: { show: false } }, series, colors: ['#16a34a', '#dc2626'], plotOptions: { bar: { borderRadius: 4, horizontal: false } }, dataLabels: { enabled: false }, xaxis: { categories }, grid: { borderColor: '#f1f5f9' }, legend: { position: 'bottom' } });
    const horizontalBar = (categories, data, color) => ({ chart: { type: 'bar', toolbar: { show: false } }, series: [{ name: 'Total', data }], colors: [color], plotOptions: { bar: { horizontal: true, borderRadius: 6 } }, dataLabels: { enabled: false }, xaxis: { categories }, grid: { borderColor: '#f1f5f9' } });
    new ApexCharts(document.querySelector('#chartTandingPayment'), stackedBar(<?= json_encode($stats['paymentByCategory']['categories'] ?? []) ?>, <?= json_encode($stats['paymentByCategory']['series'] ?? []) ?>)).render();
    new ApexCharts(document.querySelector('#chartTandingPool'), horizontalBar(<?= json_encode($stats['poolByCategory']['categories'] ?? []) ?>, <?= json_encode($stats['poolByCategory']['series'] ?? []) ?>, '#0284c7')).render();
    new ApexCharts(document.querySelector('#chartTandingMatches'), horizontalBar(<?= json_encode($stats['matchesByCategory']['categories'] ?? []) ?>, <?= json_encode($stats['matchesByCategory']['series'] ?? []) ?>, '#b91c1c')).render();
</script>
<?= $this->endSection() ?>
