<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $summary = $stats['summary'] ?? []; ?>
<section class="admin-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Statistik Sekretariat</p>
            <h2 class="section-title h3 mb-2">Progress pendaftaran</h2>
            <p class="muted-copy mb-0">Migrasi dari statistik sekretariat CI3 dengan penyajian yang lebih ringkas, responsif, dan siap dikembangkan.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-start">
            <a href="<?= base_url('admin/sekretariat/kontingen') ?>" class="btn btn-outline-danger rounded-pill px-4">Kelola Kontingen</a>
            <a href="<?= base_url('admin/sekretariat/pesilat-terbaik/pertandingan-tanding') ?>" class="btn btn-admin-brand rounded-pill px-4">Lihat Pesilat Terbaik</a>
        </div>
    </div>
</section>

<section class="row g-4 mb-4">
    <?php foreach ([
        ['label' => 'Kontingen', 'value' => $summary['kontingen'] ?? 0, 'icon' => 'fa-people-group'],
        ['label' => 'Pendaftar', 'value' => $summary['pendaftar'] ?? 0, 'icon' => 'fa-users'],
        ['label' => 'Peserta Tanding', 'value' => $summary['pesertaTanding'] ?? 0, 'icon' => 'fa-user-ninja'],
        ['label' => 'Kelompok Seni', 'value' => $summary['kelompokSeni'] ?? 0, 'icon' => 'fa-users-viewfinder'],
        ['label' => 'Kontingen Belum Input Data', 'value' => $summary['kontingenTanpaPendaftar'] ?? 0, 'icon' => 'fa-user-clock'],
        ['label' => 'Pendaftar Belum Pilih Kategori', 'value' => $summary['pendaftarTanpaKategori'] ?? 0, 'icon' => 'fa-list-check'],
    ] as $card) : ?>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="admin-card h-100">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="small muted-copy mb-1"><?= esc($card['label']) ?></div>
                        <div class="h3 section-title mb-0"><?= esc((string) $card['value']) ?></div>
                    </div>
                    <i class="fas <?= esc($card['icon']) ?> text-danger fs-3"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="row g-4 mb-4">
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Progress Penginputan Kontingen</h3><p class="muted-copy mb-3">Akumulasi kontingen berdasarkan tanggal daftar.</p><div id="chartKontingenProgress" style="min-height: 320px;"></div></div></div>
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Progress Penginputan Pendaftar</h3><p class="muted-copy mb-3">Akumulasi pendaftar dari waktu ke waktu.</p><div id="chartPendaftarProgress" style="min-height: 320px;"></div></div></div>
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Rincian Peserta Tanding</h3><p class="muted-copy mb-3">Perbandingan peserta prestasi dan pemasalan.</p><div id="chartTandingBreakdown" style="min-height: 320px;"></div></div></div>
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Rincian Kelompok Peserta Seni</h3><p class="muted-copy mb-3">Distribusi jenis seni yang aktif pada pendaftaran.</p><div id="chartSeniBreakdown" style="min-height: 320px;"></div></div></div>
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Sebaran Kontingen per Provinsi</h3><p class="muted-copy mb-3">Top 10 provinsi dengan jumlah kontingen tertinggi.</p><div id="chartKontingenProvinsi" style="min-height: 340px;"></div></div></div>
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Sebaran Atlet per Provinsi</h3><p class="muted-copy mb-3">Top 10 provinsi asal pendaftar terbanyak.</p><div id="chartPendaftarProvinsi" style="min-height: 340px;"></div></div></div>
</section>

<section class="row g-4">
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Sebaran Kontingen per Kabupaten/Kota</h3><p class="muted-copy mb-3">Top wilayah kontingen untuk pemantauan penyebaran regional.</p><div id="chartKontingenKabupaten" style="min-height: 340px;"></div></div></div>
    <div class="col-12 col-xl-6"><div class="admin-card h-100"><h3 class="section-title h5 mb-1">Sebaran Atlet per Kabupaten/Kota</h3><p class="muted-copy mb-3">Top wilayah pendaftar untuk pemetaan sumber peserta.</p><div id="chartPendaftarKabupaten" style="min-height: 340px;"></div></div></div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const cssVar = (name, fallback) => (getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback);
    const brandPrimary = cssVar('--brand-primary', '#c60000');
    const brandDark = cssVar('--admin-accent-dark', '#8f0b14');
    const brandGold = cssVar('--brand-secondary', '#c5a017');
    const gridColor = cssVar('--admin-border', '#f1f5f9');
    // Palet selaras tema: turunan merah brand + emas sebagai aksen sekunder.
    const statistikColors = [brandPrimary, '#e23b3b', brandGold, brandDark, '#d98324', '#a30000'];
    const lineOptions = (categories, data, color) => ({ chart: { type: 'area', toolbar: { show: false } }, series: [{ name: 'Total', data }], colors: [color], dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 3 }, fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05 } }, xaxis: { categories }, yaxis: { labels: { formatter: (value) => Math.round(value) } }, grid: { borderColor: gridColor }, tooltip: { y: { formatter: (value) => value + ' data' } } });
    const donutOptions = (labels, series) => ({ chart: { type: 'donut' }, labels, series, colors: statistikColors, legend: { position: 'bottom' }, dataLabels: { enabled: true }, stroke: { width: 0 } });
    const barOptions = (categories, data, color) => ({ chart: { type: 'bar', toolbar: { show: false } }, series: [{ name: 'Total', data }], colors: [color], plotOptions: { bar: { horizontal: true, borderRadius: 6, distributed: false } }, dataLabels: { enabled: false }, xaxis: { categories }, grid: { borderColor: gridColor } });

    new ApexCharts(document.querySelector('#chartKontingenProgress'), lineOptions(<?= json_encode($stats['kontingenProgress']['categories'] ?? []) ?>, <?= json_encode($stats['kontingenProgress']['series'] ?? []) ?>, brandPrimary)).render();
    new ApexCharts(document.querySelector('#chartPendaftarProgress'), lineOptions(<?= json_encode($stats['pendaftarProgress']['categories'] ?? []) ?>, <?= json_encode($stats['pendaftarProgress']['series'] ?? []) ?>, brandDark)).render();
    new ApexCharts(document.querySelector('#chartTandingBreakdown'), donutOptions(<?= json_encode($stats['tandingBreakdown']['labels'] ?? []) ?>, <?= json_encode($stats['tandingBreakdown']['series'] ?? []) ?>)).render();
    new ApexCharts(document.querySelector('#chartSeniBreakdown'), donutOptions(<?= json_encode($stats['seniBreakdown']['labels'] ?? []) ?>, <?= json_encode($stats['seniBreakdown']['series'] ?? []) ?>)).render();
    new ApexCharts(document.querySelector('#chartKontingenProvinsi'), barOptions(<?= json_encode($stats['kontingenProvinsi']['categories'] ?? []) ?>, <?= json_encode($stats['kontingenProvinsi']['series'] ?? []) ?>, brandPrimary)).render();
    new ApexCharts(document.querySelector('#chartPendaftarProvinsi'), barOptions(<?= json_encode($stats['pendaftarProvinsi']['categories'] ?? []) ?>, <?= json_encode($stats['pendaftarProvinsi']['series'] ?? []) ?>, brandGold)).render();
    new ApexCharts(document.querySelector('#chartKontingenKabupaten'), barOptions(<?= json_encode($stats['kontingenKabupaten']['categories'] ?? []) ?>, <?= json_encode($stats['kontingenKabupaten']['series'] ?? []) ?>, brandDark)).render();
    new ApexCharts(document.querySelector('#chartPendaftarKabupaten'), barOptions(<?= json_encode($stats['pendaftarKabupaten']['categories'] ?? []) ?>, <?= json_encode($stats['pendaftarKabupaten']['series'] ?? []) ?>, '#e23b3b')).render();
</script>
<?= $this->endSection() ?>
