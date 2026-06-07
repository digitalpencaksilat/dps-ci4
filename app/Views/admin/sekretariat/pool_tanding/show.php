<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$poolTitle = trim(($row->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($row->jenis_kelamin ?? '')) . ', Kelas ' . ($row->label ?? '-'));
$rangeBerat = trim((string) ($row->berat_minimal ?? '')) !== '' || trim((string) ($row->berat_maksimal ?? '')) !== ''
    ? ' (' . esc((string) ($row->berat_minimal ?? '-')) . ' Kg - ' . esc((string) ($row->berat_maksimal ?? '-')) . ' Kg)'
    : '';
$poolLabel = ($row->jenis_perlombaan ?? '') === 'pemasalan' ? ' Pool ' . ($row->nomor_pool ?? '-') : '';
?>

<section class="admin-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <p class="eyebrow mb-1">Pool Tanding</p>
            <h3 class="section-title h4 mb-1"><?= esc($poolTitle) ?><?= $rangeBerat ?><?= esc($poolLabel) ?></h3>
            <?php if (! empty($row->keterangan)) : ?>
                <p class="muted-copy mb-0"><?= esc($row->keterangan) ?></p>
            <?php endif; ?>
        </div>
        <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/sekretariat/pool-tanding') ?>">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>
</section>

<section class="admin-card">
    <ul class="nav nav-pills nav-fill mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#baganTanding" type="button" role="tab" aria-controls="baganTanding" aria-selected="true">
                Bagan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pertandinganTanding" type="button" role="tab" aria-controls="pertandinganTanding" aria-selected="false">
                Daftar Pertandingan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pesertaTanding" type="button" role="tab" aria-controls="pesertaTanding" aria-selected="false">
                Peserta Pool
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#editPoolTanding" type="button" role="tab" aria-controls="editPoolTanding" aria-selected="false">
                Edit
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="baganTanding" role="tabpanel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h4 class="h5 mb-1">Bagan Pertandingan</h4>
                    <p class="muted-copy mb-0 small">Struktur bracket dan jalur peserta pool ini.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <form method="post" action="<?= base_url('admin/sekretariat/pool-tanding/' . $row->id_kompetisi_tanding . '/acak-bagan') ?>" onsubmit="return confirmAdminAction(this, 'Shuffle Formula?', 'Bagan lama yang belum dijadwalkan akan diganti dengan hasil acak formula.', 'Ya, Acak')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="mode" value="formula">
                        <button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">Shuffle Formula</button>
                    </form>
                    <form method="post" action="<?= base_url('admin/sekretariat/pool-tanding/' . $row->id_kompetisi_tanding . '/acak-bagan') ?>" onsubmit="return confirmAdminAction(this, 'Full Random Persilat?', 'Bagan lama yang belum dijadwalkan akan diganti dengan full random + Persilat.', 'Ya, Acak')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="mode" value="full_random_persilat">
                        <button class="btn btn-sm btn-danger rounded-pill" type="submit">Full Random Persilat</button>
                    </form>
                    <a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/pool-tanding/' . $row->id_kompetisi_tanding . '/bagan.pdf') ?>" target="_blank">Cetak Bagan</a>
                </div>
            </div>

            <?php if (! empty($row->bagan_pertandingan)) : ?>
                <?= view('shared_components/kompetisi_tanding/bagan_pertandingan', ['kompetisi_tanding' => $row, 'toggle_early_match' => false]) ?>
            <?php else : ?>
                <p class="muted-copy mb-0">Bagan belum tersedia. Drawing belum dilakukan.</p>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="pertandinganTanding" role="tabpanel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h4 class="h5 mb-1">Daftar Pertandingan</h4>
                    <p class="muted-copy mb-0 small">Tampilan tabel mengikuti parity daftar pertandingan legacy, termasuk label pemenang partai sebelumnya.</p>
                </div>
            </div>
            <?= view('admin/sekretariat/pertandingan_tanding/_urutan_poin_table', ['rows' => $pertandinganRows ?? []]) ?>
        </div>

        <div class="tab-pane fade" id="pesertaTanding" role="tabpanel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h4 class="h5 mb-1">Peserta Pool</h4>
                    <p class="muted-copy mb-0 small">Daftar atlet yang masuk ke pool tanding ini.</p>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table admin-table admin-datatable-export align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kontingen</th>
                            <th class="text-end no-export">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($peserta ?? []) as $item) : ?>
                            <tr>
                                <td><?= esc($item->nama_pendaftar ?? '-') ?></td>
                                <td class="text-uppercase"><?= esc($item->nama_kontingen ?? '-') ?></td>
                                <td class="text-end no-export">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-danger rounded-pill dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Aksi
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('admin/sekretariat/pool-tanding/' . $item->id_kompetisi_tanding) ?>#baganTanding">
                                                    <i class="fas fa-project-diagram me-2 text-muted"></i> Lihat Bagan
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('admin/sekretariat/peserta-tanding/' . $item->id_peserta_tanding . '/edit-kelas') ?>">
                                                    <i class="fas fa-exchange-alt me-2 text-muted"></i> Ganti Kelas Tanding
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('admin/sekretariat/peserta-tanding/' . $item->id_peserta_tanding . '/pindah-pool') ?>">
                                                    <i class="fas fa-random me-2 text-muted"></i> Pindah Pool
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="post" action="<?= base_url('admin/sekretariat/peserta-tanding/' . $item->id_peserta_tanding . '/delete') ?>" onsubmit="return confirmAdminAction(this, 'Undur diri peserta?', 'Peserta akan keluar dari kategori tanding.', 'Undur Diri')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-user-slash me-2"></i> Undur Diri
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="editPoolTanding" role="tabpanel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h4 class="h5 mb-1">Edit Pool Tanding</h4>
                    <p class="muted-copy mb-0 small">Pengaturan pool dipindahkan ke tab seperti project lama.</p>
                </div>
            </div>
            <form method="post" action="<?= base_url('admin/sekretariat/pool-tanding/' . $row->id_kompetisi_tanding . '/update') ?>" class="row g-3 needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="col-md-3">
                    <label class="form-label">Nomor Pool</label>
                    <input class="form-control" name="nomor_pool" type="number" value="<?= esc((string) ($row->nomor_pool ?? '')) ?>" required>
                    <div class="invalid-feedback">Nomor pool wajib diisi.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Max Peserta</label>
                    <input class="form-control" name="max_peserta" type="number" value="<?= esc((string) ($row->max_peserta ?? 0)) ?>" required>
                    <div class="invalid-feedback">Max peserta wajib diisi.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Perhitungan Medali</label>
                    <select class="form-select" name="perhitungan_medali" required>
                        <option value="1" <?= (int) ($row->perhitungan_medali ?? 0) === 1 ? 'selected' : '' ?>>Dihitung</option>
                        <option value="0" <?= (int) ($row->perhitungan_medali ?? 0) === 0 ? 'selected' : '' ?>>Tidak dihitung</option>
                    </select>
                    <div class="invalid-feedback">Perhitungan medali wajib dipilih.</div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Keterangan</label>
                    <textarea class="form-control" name="keterangan" rows="3"><?= esc($row->keterangan ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <button class="btn btn-admin-brand rounded-pill" type="submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
