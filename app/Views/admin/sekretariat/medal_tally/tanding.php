<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$exportTitle = 'DATA PEROLEHAN MEDALI TANDING';
$exportFilename = 'Perolehan Medali Tanding - ' . $eventName;
$brandName = (string) (get_setting('brand_name') ?? 'Digital Pencak Silat');
$brandAbbr = strtolower((string) (get_setting('brand_abbreviation') ?? 'dps'));
$brandLogoUrl = base_url('assets/images/brand/' . $brandAbbr . '/logo.png');
$printHeaderHtml = view('shared_components/print/medal_export_header', [
    'title' => $exportTitle,
    'subtitle' => $eventName,
]);
?>
<section class="admin-card mb-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <p class="eyebrow mb-1">Perolehan Medali</p>
            <h2 class="section-title h3 mb-2"><?= esc($reportTitle ?? $title ?? 'Perolehan Medali Tanding') ?></h2>
            <p class="muted-copy mb-0">Daftar peraih medali kategori tanding. Gunakan tombol ekspor untuk Excel, PDF, atau cetak.</p>
        </div>
    </div>
    <div class="admin-table-wrap"><div class="table-shell admin-table-scroller">
        <table class="table admin-table align-middle mb-0" id="tabelPerolehanMedaliTanding">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontingen</th>
                    <th>Provinsi</th>
                    <th>Nama Sekolah</th>
                    <th>Kategori</th>
                    <th>Kelas</th>
                    <th class="text-center">Medali</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rows ?? []) as $row) : ?>
                    <tr>
                        <td class="fw-semibold text-capitalize"><?= esc($row['nama_pendaftar'] ?? '-') ?></td>
                        <td class="text-uppercase"><?= esc($row['nama_kontingen'] ?? '-') ?></td>
                        <td><?= esc($row['provinsi'] ?? '-') ?></td>
                        <td><?= esc($row['nama_sekolah'] ?? '-') ?></td>
                        <td class="text-capitalize"><?= esc(trim(($row['nama_kategori_usia'] ?? '-') . ' ' . ($row['jenis_kelamin'] ?? ''))) ?></td>
                        <td class="text-capitalize">
                            <?php if (($row['jenis_perlombaan'] ?? '') === 'pemasalan') : ?>
                                <?= esc(trim(($row['label'] ?? '-') . ' Pool ' . ($row['nomor_pool'] ?? '-'))) ?>
                            <?php else : ?>
                                <?= esc($row['label'] ?? '-') ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= view('admin/sekretariat/medal_tally/_medal_badge', ['medal' => $row['jenis_medali'] ?? null]) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div></div>
    <?php if (empty($rows)) : ?><div class="text-center muted-copy py-4">Belum ada data perolehan medali tanding.</div><?php endif; ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        window.initAdminExportTable('#tabelPerolehanMedaliTanding', {
            title: <?= json_encode($exportTitle) ?>,
            filename: <?= json_encode($exportFilename) ?>,
            orientation: 'landscape',
            preset: 'wide-report',
            printHeaderHtml: <?= json_encode($printHeaderHtml) ?>,
            excel: {
                columnWidths: { A: 32, B: 32, C: 22, D: 30, E: 22, F: 22, G: 16 },
                customize: function(xlsx) {
                    window.dpsMedalExcelCustomize(xlsx, 'G');
                }
            },
            printCustomize: function(win) {
                window.dpsMedalPrintCustomize(win, 8, {
                    watermark: {
                        logo: <?= json_encode($brandLogoUrl) ?>,
                        text: 'Powered by <strong>' + <?= json_encode($brandName) ?> + '</strong> &copy; ' + new Date().getFullYear()
                    }
                });
            }
        });
    });
</script>
<?= $this->endSection() ?>
