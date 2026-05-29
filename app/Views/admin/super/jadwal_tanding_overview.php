<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Overview Jadwal Tanding</h6>
        <p class="muted-copy small mb-0">Ringkasan jadwal tanding untuk mode super admin pembuatan jadwal.</p>
    </div>
    <div class="card-body px-0">
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Arena</th>
                            <th>Tanggal</th>
                            <th>Jumlah Partai</th>
                            <th>Partai Awal</th>
                            <th>Partai Akhir</th>
                            <th>Keterangan</th>
                            <th class="text-end">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($rows ?? []) as $row) : ?>
                            <tr>
                                <td>Arena <?= esc($row->nama_gelanggang ?? '-') ?></td>
                                <td><?= esc($row->tanggal_formatted ?? $row->tanggal ?? '-') ?></td>
                                <td><?= esc((string) ($row->jumlah_partai ?? 0)) ?></td>
                                <td><?= esc((string) ($row->nomor_partai_awal ?? '-')) ?></td>
                                <td><?= esc((string) ($row->nomor_partai_akhir ?? '-')) ?></td>
                                <td><?= esc($row->keterangan_jadwal ?? '-') ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-danger" href="<?= base_url('admin/super/jadwal-tanding/' . $row->id_jadwal_tanding) ?>">Lihat</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (empty($rows ?? [])) : ?>
            <div class="text-center muted-copy py-4">Belum ada jadwal tanding.</div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
