<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$totalEmas = (int) array_sum(array_column($data_prediksi_medali_seni, 'emas')) + (int) array_sum(array_column($data_prediksi_medali_tanding, 'emas'));
$totalPerak = (int) array_sum(array_column($data_prediksi_medali_seni, 'perak')) + (int) array_sum(array_column($data_prediksi_medali_tanding, 'perak'));
$totalPerunggu = (int) array_sum(array_column($data_prediksi_medali_seni, 'perunggu')) + (int) array_sum(array_column($data_prediksi_medali_tanding, 'perunggu'));
?>

<section class="row g-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="admin-card h-100">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="small muted-copy mb-1">Emas</div>
                    <div class="h3 section-title mb-0"><?= esc((string) $totalEmas) ?></div>
                </div>
                <i class="fas fa-medal text-warning fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="admin-card h-100">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="small muted-copy mb-1">Perak</div>
                    <div class="h3 section-title mb-0"><?= esc((string) $totalPerak) ?></div>
                </div>
                <i class="fas fa-medal text-secondary fs-3"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="admin-card h-100">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    <div class="small muted-copy mb-1">Perunggu</div>
                    <div class="h3 section-title mb-0"><?= esc((string) $totalPerunggu) ?></div>
                </div>
                <i class="fas fa-medal" style="color: #7c4800; font-size: var(--fa-font-size-lg)"></i>
            </div>
        </div>
    </div>
</section>

<section class="admin-card mb-4">
    <div class="mb-3">
        <h3 class="section-title h5 mb-1">Rincian Tanding</h3>
        <p class="muted-copy mb-0">Prediksi medali berdasarkan kategori tanding.</p>
    </div>
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table admin-datatable-export align-middle mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th class="text-end">Emas</th>
                        <th class="text-end">Perak</th>
                        <th class="text-end">Perunggu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach (($data_prediksi_medali_tanding ?? []) as $data) : ?>
                        <tr>
                            <td class="text-center"><?= esc((string) $i++) ?></td>
                            <td><?= esc(($data->nama_kategori_usia ?? '-') . ' - ' . ($data->jenis_kelamin ?? '')) ?></td>
                            <td class="text-end"><?= esc((string) ($data->emas ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($data->perak ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($data->perunggu ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($data_prediksi_medali_tanding)) : ?><div class="text-center muted-copy py-4">Belum ada data prediksi medali tanding.</div><?php endif; ?>
    </div>
</section>

<section class="admin-card">
    <div class="mb-3">
        <h3 class="section-title h5 mb-1">Rincian Seni</h3>
        <p class="muted-copy mb-0">Prediksi medali berdasarkan kategori seni.</p>
    </div>
    <div class="admin-table-wrap">
        <div class="table-shell admin-table-scroller">
            <table class="table admin-table admin-datatable-export align-middle mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Usia</th>
                        <th class="text-end">Emas</th>
                        <th class="text-end">Perak</th>
                        <th class="text-end">Perunggu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach (($data_prediksi_medali_seni ?? []) as $data) : ?>
                        <tr>
                            <td class="text-center"><?= esc((string) $i++) ?></td>
                            <td><?= esc(($data->nama_kategori_usia ?? '-') . ' - ' . ($data->jenis_kelamin ?? '')) ?></td>
                            <td class="text-end"><?= esc((string) ($data->emas ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($data->perak ?? 0)) ?></td>
                            <td class="text-end"><?= esc((string) ($data->perunggu ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($data_prediksi_medali_seni)) : ?><div class="text-center muted-copy py-4">Belum ada data prediksi medali seni.</div><?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>