<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$eventName = get_setting('event_name') ?: ($eventName ?? 'Digital Pencak Silat');
$exportTitle = 'DAFTAR POOL TANDING';
$exportFilename = 'Daftar Pool Tanding - ' . $eventName;
$printHeaderHtml = view('shared_components/print/export_header', [
    'title' => $exportTitle,
    'subtitle' => $eventName,
]);
?>
<section class="admin-card">
    <div class="mb-4">
        <p class="eyebrow mb-1">Kategori Tanding</p>
        <h3 class="section-title h4 mb-0">Daftar pool tanding</h3>
    </div>
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table align-middle mb-0" id="tabelPoolTanding">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Kelas</th>
                        <th>Pool</th>
                        <th>Max</th>
                        <th>Peserta</th>
                        <th>Lunas</th>
                        <th>Medali</th>
                        <th>Keterangan</th>
                        <th class="text-end no-export">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($rows ?? []) as $row) : ?>
                        <tr>
                            <td><?= esc($row->nama_kategori_usia ?? '-') ?></td>
                            <td><?= esc($row->label ?? '-') ?></td>
                            <td><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                            <td><?= esc((string) ($row->max_peserta ?? 0)) ?></td>
                            <td><?= esc((string) ($row->jumlah_peserta_tanding ?? 0)) ?></td>
                            <td><?= esc((string) ($row->jumlah_peserta_tanding_lunas ?? 0)) ?></td>
                            <td><?= ((int) ($row->perhitungan_medali ?? 0) === 1) ? 'Ya' : 'Tidak' ?></td>
                            <td><?= esc($row->keterangan ?? '-') ?></td>
                            <td class="text-end no-export"><a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/pool-tanding/' . $row->id_kompetisi_tanding) ?>">Detail</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        window.initAdminExportTable('#tabelPoolTanding', {
            title: <?= json_encode($exportTitle) ?>,
            filename: <?= json_encode($exportFilename) ?>,
            orientation: 'landscape',
            preset: 'wide-report',
            printHeaderHtml: <?= json_encode($printHeaderHtml) ?>,
            excel: {
                columnWidths: { A: 18, B: 14, C: 10, D: 10, E: 12, F: 12, G: 12, H: 30 }
            },
            printCustomize: function(win) {
                $(win.document.head).append('<style>table tr td:nth-child(3), table tr td:nth-child(4), table tr td:nth-child(5), table tr td:nth-child(6), table tr td:nth-child(7){text-align:center!important;}</style>');
            },
            dataTable: {
                columnDefs: [
                    { targets: [2, 3, 4, 5, 6], className: 'text-center' },
                    { targets: -1, orderable: false, width: '10%' }
                ]
            }
        });
    });
</script>
<?= $this->endSection() ?>
