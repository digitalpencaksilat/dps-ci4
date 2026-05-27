<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$currency = (string) ($default_currency ?? 'Rp.');

$renderSummaryRows = static function (array $groups, callable $labelResolver): string {
    ob_start();
    foreach ($groups as $key => $rows) {
        ?>
        <tr>
            <td class="text-wrap"><?= esc($labelResolver($key)) ?></td>
            <td class="text-center"><span class="status-badge neutral"><?= esc((string) count($rows)) ?></span></td>
        </tr>
        <?php
    }

    return (string) ob_get_clean();
};

$renderMaxPesertaRows = static function (array $rows, string $badgeClass): string {
    ob_start();

    if ($rows === []) {
        ?>
        <tr>
            <td colspan="2" class="text-center muted-copy py-3">Belum ada data</td>
        </tr>
        <?php
    } else {
        foreach ($rows as $row) {
            ?>
            <tr>
                <td class="text-wrap"><?= esc($row['kategori']) ?></td>
                <td class="text-center"><span class="status-badge <?= esc($badgeClass) ?>"><?= esc((string) $row['max_peserta']) ?></span></td>
            </tr>
            <?php
        }
    }

    return (string) ob_get_clean();
};
?>

<section class="admin-card admin-landing-card mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
        <div>
            <p class="eyebrow mb-1">Pengaturan Event</p>
            <h2 class="section-title h3 mb-3">Dashboard Pengaturan Event</h2>
            <p class="muted-copy mb-0">Ringkasan ini mengikuti dashboard CI3 untuk memeriksa konfigurasi medali, peraturan, pool, biaya, dan harga kategori usia.</p>
        </div>
        <div class="d-flex flex-wrap align-items-start gap-2">
            <span class="status-badge <?= ($activeMode ?? '') === 'pengaturan_event' ? 'success' : 'warning' ?>">
                Mode: <?= esc(($activeMode ?? '') === 'pengaturan_event' ? 'pengaturan_event' : 'belum aktif') ?>
            </span>
            <a href="<?= base_url('admin/super/pengaturan-event/profil-kejuaraan') ?>" class="btn btn-primary rounded-pill">Profil Kejuaraan</a>
            <a href="<?= base_url('admin/super/menu-utama') ?>" class="btn btn-outline-light rounded-pill">Menu Utama</a>
        </div>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100">
            <h3 class="h5 mb-3">Konfigurasi Medali</h3>
            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th>Konfigurasi Medali</th><th class="text-center">Jumlah Kelas</th></tr></thead><tbody><?= $renderSummaryRows($data_kategori_lomba_berdasarkan_semua_dapat_medali ?? [], static fn ($key): string => (string) $key === '1' ? 'Semua dapat medali' : 'Tidak semua dapat medali') ?></tbody></table></div></div>
        </section>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100">
            <h3 class="h5 mb-3">Peraturan Pertandingan</h3>
            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th>Jenis Peraturan</th><th class="text-center">Jumlah Kelas</th></tr></thead><tbody><?= $renderSummaryRows($data_kategori_lomba_berdasarkan_peraturan_pertandingan ?? [], static fn ($key): string => (string) $key) ?></tbody></table></div></div>
        </section>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100">
            <h3 class="h5 mb-3">Max Peserta Per Pool - Kategori Tanding</h3>
            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th>Kategori</th><th class="text-center">Max Per Pool</th></tr></thead><tbody><?= $renderMaxPesertaRows($data_max_peserta_tanding_per_kategori ?? [], 'info') ?></tbody></table></div></div>
        </section>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100">
            <h3 class="h5 mb-3">Pengaturan Juara Tiga Bersama</h3>
            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th>Juara Tiga Bersama</th><th class="text-center">Jumlah Kelas</th></tr></thead><tbody><?= $renderSummaryRows($data_kelas_tanding_berdasarkan_juara_tiga_bersama ?? [], static fn ($key): string => (string) $key === '1' ? 'Juara tiga bersama' : 'Perebutan juara tiga') ?></tbody></table></div></div>
        </section>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100">
            <h3 class="h5 mb-3">Format Penilaian Tanding</h3>
            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th>Format Penilaian</th><th class="text-center">Jumlah Kelas</th></tr></thead><tbody><?= $renderSummaryRows($data_kelas_tanding_berdasarkan_format_penilaian ?? [], static fn ($key): string => (string) $key) ?></tbody></table></div></div>
        </section>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100">
            <h3 class="h5 mb-3">Max Peserta Per Pool - Kategori Seni</h3>
            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th>Kategori</th><th class="text-center">Max Per Pool</th></tr></thead><tbody><?= $renderMaxPesertaRows($data_max_peserta_seni_per_kategori ?? [], 'success') ?></tbody></table></div></div>
        </section>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100">
            <h3 class="h5 mb-3">Pengaturan Biaya Pendaftaran Tanding</h3>
            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th>Biaya Pendaftaran</th><th class="text-center">Jumlah Kelas</th></tr></thead><tbody><?= $renderSummaryRows($data_kelas_tanding_berdasarkan_biaya_pendaftaran ?? [], static fn ($key): string => $currency . number_format((float) $key, 0, ',', '.')) ?></tbody></table></div></div>
        </section>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100">
            <h3 class="h5 mb-3">Sistem Penampilan Seni</h3>
            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th>Sistem Penampilan</th><th class="text-center">Jumlah Kelas</th></tr></thead><tbody><?= $renderSummaryRows($data_sub_kategori_seni_berdasarkan_sistem_penampilan ?? [], static fn ($key): string => ucfirst((string) $key)) ?></tbody></table></div></div>
        </section>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <section class="admin-card h-100 d-flex flex-column justify-content-between">
            <div>
                <p class="eyebrow mb-1">Keamanan</p>
                <h3 class="h5 mb-2">Role Super Admin</h3>
                <p class="muted-copy mb-0">Akses halaman ini tetap dilindungi filter <code>adminrole:super_admin</code>.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="status-badge neutral">Kategori Usia: <?= esc((string) count($data_kategori_usia ?? [])) ?></span>
                <span class="status-badge neutral">Kelas Tanding: <?= esc((string) count($data_kelas_tanding ?? [])) ?></span>
                <span class="status-badge neutral">Sub Kategori Seni: <?= esc((string) count($data_sub_kategori_seni ?? [])) ?></span>
            </div>
        </section>
    </div>
</div>

<section class="admin-card">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Detail Kategori Usia</p>
            <h3 class="section-title h4 mb-0">Harga dan Kelas per Kategori</h3>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach (($data_kategori_usia ?? []) as $kategoriUsia) : ?>
            <?php
            $kelasPutra = array_values(array_filter($data_kelas_tanding ?? [], static fn ($row): bool => ($row->nama_kategori_usia ?? null) === ($kategoriUsia->nama_kategori_usia ?? null) && ($row->jenis_kelamin ?? null) === 'putra'));
            $kelasPutri = array_values(array_filter($data_kelas_tanding ?? [], static fn ($row): bool => ($row->nama_kategori_usia ?? null) === ($kategoriUsia->nama_kategori_usia ?? null) && ($row->jenis_kelamin ?? null) === 'putri'));
            $seniPutra = array_values(array_filter($data_sub_kategori_seni ?? [], static fn ($row): bool => ($row->nama_kategori_usia ?? null) === ($kategoriUsia->nama_kategori_usia ?? null) && ($row->jenis_kelamin ?? null) === 'putra'));
            $seniPutri = array_values(array_filter($data_sub_kategori_seni ?? [], static fn ($row): bool => ($row->nama_kategori_usia ?? null) === ($kategoriUsia->nama_kategori_usia ?? null) && ($row->jenis_kelamin ?? null) === 'putri'));
            ?>
            <div class="col-12">
                <section class="admin-card h-100">
                    <h4 class="h5 mb-3"><?= esc((string) ($kategoriUsia->nama_kategori_usia ?? '-')) ?></h4>
                    <div class="row g-3">
                        <div class="col-12 col-xl-6">
                            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th colspan="3" class="text-center">Putra</th></tr><tr><th class="text-center">Kelas</th><th class="text-center">Berat</th><th class="text-center">Biaya Pendaftaran</th></tr></thead><tbody><?php if ($kelasPutra === []) : ?><tr><td colspan="3" class="text-center muted-copy py-3">Belum ada data</td></tr><?php else : ?><?php foreach ($kelasPutra as $row) : ?><tr><td class="text-center"><?= esc((string) ($row->label ?? '-')) ?></td><td class="text-center"><?= esc(trim((string) ($row->berat_minimal ?? '-') . ' - ' . (string) ($row->berat_maksimal ?? '-') . ' kg')) ?></td><td class="text-end"><?= esc($currency . number_format((float) ($row->biaya_pendaftaran_dn ?? 0), 0, ',', '.')) ?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></div>
                        </div>
                        <div class="col-12 col-xl-6">
                            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th colspan="3" class="text-center">Putri</th></tr><tr><th class="text-center">Kelas</th><th class="text-center">Berat</th><th class="text-center">Biaya Pendaftaran</th></tr></thead><tbody><?php if ($kelasPutri === []) : ?><tr><td colspan="3" class="text-center muted-copy py-3">Belum ada data</td></tr><?php else : ?><?php foreach ($kelasPutri as $row) : ?><tr><td class="text-center"><?= esc((string) ($row->label ?? '-')) ?></td><td class="text-center"><?= esc(trim((string) ($row->berat_minimal ?? '-') . ' - ' . (string) ($row->berat_maksimal ?? '-') . ' kg')) ?></td><td class="text-end"><?= esc($currency . number_format((float) ($row->biaya_pendaftaran_dn ?? 0), 0, ',', '.')) ?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></div>
                        </div>
                        <div class="col-12 col-xl-6">
                            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th colspan="3" class="text-center">Seni Putra</th></tr><tr><th class="text-center">Jenis Seni</th><th class="text-center">Jurus Seni</th><th class="text-center">Biaya Pendaftaran</th></tr></thead><tbody><?php if ($seniPutra === []) : ?><tr><td colspan="3" class="text-center muted-copy py-3">Belum ada data</td></tr><?php else : ?><?php foreach ($seniPutra as $row) : ?><tr><td class="text-center"><?= esc(ucwords((string) ($row->jenis_seni ?? '-'))) ?></td><td class="text-center"><?= esc(ucwords((string) ($row->nama_seni ?? '-'))) ?></td><td class="text-end"><?= esc($currency . number_format((float) ($row->biaya_pendaftaran_dn ?? 0), 0, ',', '.')) ?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></div>
                        </div>
                        <div class="col-12 col-xl-6">
                            <div class="admin-table-wrap"><div class="table-shell"><table class="table admin-table align-middle mb-0"><thead><tr><th colspan="3" class="text-center">Seni Putri</th></tr><tr><th class="text-center">Jenis Seni</th><th class="text-center">Jurus Seni</th><th class="text-center">Biaya Pendaftaran</th></tr></thead><tbody><?php if ($seniPutri === []) : ?><tr><td colspan="3" class="text-center muted-copy py-3">Belum ada data</td></tr><?php else : ?><?php foreach ($seniPutri as $row) : ?><tr><td class="text-center"><?= esc(ucwords((string) ($row->jenis_seni ?? '-'))) ?></td><td class="text-center"><?= esc(ucwords((string) ($row->nama_seni ?? '-'))) ?></td><td class="text-end"><?= esc($currency . number_format((float) ($row->biaya_pendaftaran_dn ?? 0), 0, ',', '.')) ?></td></tr><?php endforeach; ?><?php endif; ?></tbody></table></div></div>
                        </div>
                    </div>
                </section>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?= $this->endSection() ?>
