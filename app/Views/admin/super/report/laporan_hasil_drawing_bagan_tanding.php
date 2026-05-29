<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$data_peserta_tanding_tanpa_lawan = $data_peserta_tanding_tanpa_lawan ?? [];
$data_peserta_tanding_bertemu_kontingen_sendiri_diatas_dua_peserta = $data_peserta_tanding_bertemu_kontingen_sendiri_diatas_dua_peserta ?? [];
$data_peserta_tanding_bertemu_kontingen_sendiri_dua_peserta = $data_peserta_tanding_bertemu_kontingen_sendiri_dua_peserta ?? [];
$data_kelas_tanding = $data_kelas_tanding ?? [];
?>

<div class="admin-card">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <p class="eyebrow mb-1">Pembuatan Jadwal</p>
                <h2 class="section-title h4 mb-1">Laporan Hasil Drawing Bagan Tanding</h2>
                <p class="muted-copy mb-0">Parity CI3: tab peserta tanpa lawan, kontingen ketemu sendiri, kelas kuota tersisa.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('admin/super/drawing-tanding') ?>" class="btn btn-outline-secondary rounded-pill">Kembali</a>
            </div>
        </div>
    </div>

    <div class="card-body px-0">
        <ul class="nav nav-pills nav-pills-primary nav-fill p-1 mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#peserta_tanding_tanpa_lawan" role="tab" aria-selected="true">Peserta tanpa lawan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#kontingen_sendiri_dua" role="tab" aria-selected="false">Kontingen sendiri (pool 2 peserta)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#kontingen_sendiri_lebih" role="tab" aria-selected="false">Kontingen sendiri (>2 peserta)</a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#kelas_kuota" role="tab" aria-selected="false">Kelas kuota tersisa</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="peserta_tanding_tanpa_lawan" role="tabpanel">
                <div class="admin-card mb-3">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Kompetisi tanding dengan 1 peserta</h3>
                    </div>
                    <div class="card-body px-0">
                        <div class="admin-table-wrap">
                            <div class="table-shell admin-table-scroller" style="max-height: 520px;">
                                <table class="table admin-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Kontingen</th>
                                            <th>Kategori</th>
                                            <th>Kelas</th>
                                            <th class="text-end">BB</th>
                                            <th class="text-end">Pool</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data_peserta_tanding_tanpa_lawan as $row) : ?>
                                            <tr>
                                                <td class="fw-semibold"><?= esc((string) ($row->nama_pendaftar ?? '-')) ?></td>
                                                <td><?= esc((string) ($row->nama_kontingen ?? '-')) ?></td>
                                                <td><?= esc(trim((string) ($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? ''))) ?></td>
                                                <td><?= esc((string) ($row->label ?? '-')) ?></td>
                                                <td class="text-end"><?= esc((string) ($row->berat_badan ?? '-')) ?></td>
                                                <td class="text-end"><?= esc((string) ($row->nomor_pool ?? '-')) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if ($data_peserta_tanding_tanpa_lawan === []) : ?>
                                            <tr><td colspan="6" class="text-center muted-copy py-4">Tidak ada</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="kontingen_sendiri_dua" role="tabpanel">
                <div class="admin-card mb-3">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Peserta tanding bertemu kontingen sendiri (pool 2 peserta)</h3>
                    </div>
                    <div class="card-body px-0">
                        <?= $this->include('admin/super/report/_tabel_peserta_kontingen_sendiri') ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="kontingen_sendiri_lebih" role="tabpanel">
                <div class="admin-card mb-3">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Peserta tanding bertemu kontingen sendiri (pool > 2 peserta)</h3>
                    </div>
                    <div class="card-body px-0">
                        <?= $this->setData(['rows' => $data_peserta_tanding_bertemu_kontingen_sendiri_diatas_dua_peserta])->include('admin/super/report/_tabel_peserta_kontingen_sendiri') ?>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="kelas_kuota" role="tabpanel">
                <div class="admin-card mb-3">
                    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                        <h3 class="section-title h6 mb-1">Kelas tanding masih menyisakan kuota</h3>
                    </div>
                    <div class="card-body px-0">
                        <div class="admin-table-wrap">
                            <div class="table-shell admin-table-scroller" style="max-height: 520px;">
                                <table class="table admin-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Kategori</th>
                                            <th>Kelas</th>
                                            <th class="text-end">Peserta</th>
                                            <th class="text-end">Max</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data_kelas_tanding as $row) : ?>
                                            <tr>
                                                <td><?= esc(trim((string) ($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? ''))) ?></td>
                                                <td class="fw-semibold"><?= esc((string) ($row->label ?? '-')) ?></td>
                                                <td class="text-end"><?= esc((string) ($row->jumlah_peserta_tanding ?? 0)) ?></td>
                                                <td class="text-end"><?= esc((string) ($row->max_peserta ?? 0)) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if ($data_kelas_tanding === []) : ?>
                                            <tr><td colspan="4" class="text-center muted-copy py-4">Tidak ada</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info small" role="alert">
            Data pakai subquery COUNT peserta_tanding (tanpa kolom fisik jumlah_peserta_tanding).
        </div>
    </div>
</div>
<?= $this->endSection() ?>
