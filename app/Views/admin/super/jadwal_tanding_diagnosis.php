<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="admin-card h-100">
            <div class="card-body">
                <div class="muted-copy small mb-1">Pertandingan Belum Dijadwalkan</div>
                <div class="display-6 fw-bold text-danger"><?= esc((string) ($count_pertandingan_belum_dijadwalkan ?? 0)) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="admin-card h-100">
            <div class="card-body">
                <div class="muted-copy small mb-1">BYE Terjadwal</div>
                <div class="display-6 fw-bold text-warning"><?= esc((string) ($count_pertandingan_bye_terjadwal ?? 0)) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="admin-card mb-4">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Pertandingan Tanding Belum Dijadwalkan</h6>
        <p class="muted-copy small mb-0">Sumber data dari audit service mode pembuatan jadwal.</p>
    </div>
    <div class="card-body px-0">
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Pertandingan</th>
                            <th>Babak</th>
                            <th>Kategori Usia</th>
                            <th>Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($data_pertandingan_belum_dijadwalkan ?? []) as $row) : ?>
                            <tr>
                                <td><?= esc((string) ($row->nomor_pertandingan ?? '-')) ?></td>
                                <td><?= esc($row->babak ?? '-') ?></td>
                                <td><?= esc(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '')) ?></td>
                                <td><?= esc($row->nama_kelas ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (empty($data_pertandingan_belum_dijadwalkan ?? [])) : ?>
            <div class="text-center muted-copy py-4">Tidak ada pertandingan tanding yang belum dijadwalkan.</div>
        <?php endif; ?>
    </div>
</div>

<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Pertandingan BYE yang Sudah Masuk Jadwal</h6>
    </div>
    <div class="card-body px-0">
        <div class="admin-table-wrap">
            <div class="table-shell admin-table-scroller">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nomor Partai</th>
                            <th>No. Pertandingan</th>
                            <th>Babak</th>
                            <th>Gelanggang</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($data_pertandingan_bye_terjadwal ?? []) as $row) : ?>
                            <tr>
                                <td><?= esc((string) ($row->nomor_partai ?? '-')) ?></td>
                                <td><?= esc((string) ($row->nomor_pertandingan ?? '-')) ?></td>
                                <td><?= esc($row->babak ?? '-') ?></td>
                                <td><?= esc($row->nama_gelanggang ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (empty($data_pertandingan_bye_terjadwal ?? [])) : ?>
            <div class="text-center muted-copy py-4">Tidak ada pertandingan BYE yang terjadwal.</div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
