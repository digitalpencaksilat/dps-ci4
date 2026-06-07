<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$baseUrl = base_url('admin/super/drawing-prestasi/tanding');

/** Render label kategori untuk dropdown & heading. */
$labelKategori = static function ($row): string {
    $label = trim(($row->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($row->jenis_kelamin ?? '')));
    $label .= ', ' . ($row->label ?? '-') . ' Class';
    $berat = trim((string) ($row->berat_minimal ?? '')) !== '' || trim((string) ($row->berat_maksimal ?? '')) !== ''
        ? ' (' . ($row->berat_minimal ?? '-') . ' Kg - ' . ($row->berat_maksimal ?? '-') . ' Kg)'
        : '';
    $jumlah = ' - ' . (int) ($row->jumlah_peserta_tanding ?? 0) . ' Atlet';
    $pool = ($row->jenis_perlombaan ?? '') === 'pemasalan' ? ' Pool ' . ($row->nomor_pool ?? '-') : '';

    return $label . $berat . $pool . $jumlah;
};
?>

<section class="admin-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-1">
        <div>
            <p class="eyebrow mb-1">Drawing Prestasi</p>
            <h3 class="section-title h4 mb-0">Drawing Tanding</h3>
            <p class="muted-copy mb-0 small">Pilih satu kategori tanding prestasi untuk melihat bagan dan melakukan pengacakan.</p>
        </div>
    </div>

    <form action="<?= $baseUrl ?>" method="get" class="row g-2 align-items-end mt-2">
        <div class="col-12 col-lg-9">
            <label class="form-label small fw-semibold">Kategori Tanding Prestasi</label>
            <select class="form-select" name="id" required>
                <option value="" disabled <?= $selected === null ? 'selected' : '' ?>>-- Pilih kategori --</option>
                <?php foreach ($rows as $row) : ?>
                    <option value="<?= (int) $row->id_kompetisi_tanding ?>" <?= ($selected !== null && (int) $selected->id_kompetisi_tanding === (int) $row->id_kompetisi_tanding) ? 'selected' : '' ?>>
                        <?= esc($labelKategori($row)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-3">
            <button type="submit" class="btn btn-admin-brand rounded-pill w-100">
                <i class="fas fa-magnifying-glass me-1"></i> Tampilkan
            </button>
        </div>
    </form>

    <?php if ($rows === []) : ?>
        <p class="muted-copy mb-0 mt-3">Belum ada kategori tanding prestasi yang tersedia.</p>
    <?php endif; ?>

    <?php if ($selected !== null) : ?>
        <div class="row g-2 mt-3">
            <div class="col-6">
                <?php if ($prev !== null) : ?>
                    <a class="btn btn-outline-danger rounded-pill w-100 text-truncate" href="<?= $baseUrl . '?id=' . (int) $prev->id_kompetisi_tanding ?>">
                        <i class="fas fa-chevron-left me-1"></i> <?= esc(trim(($prev->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($prev->jenis_kelamin ?? '')) . ' - ' . ($prev->label ?? ''))) ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-6 text-end">
                <?php if ($next !== null) : ?>
                    <a class="btn btn-outline-danger rounded-pill w-100 text-truncate" href="<?= $baseUrl . '?id=' . (int) $next->id_kompetisi_tanding ?>">
                        <?= esc(trim(($next->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($next->jenis_kelamin ?? '')) . ' - ' . ($next->label ?? ''))) ?> <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($selected !== null) : ?>
    <?php $idKompetisi = (int) $selected->id_kompetisi_tanding; ?>
    <section class="admin-card">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h4 class="h5 mb-1"><?= esc($labelKategori($selected)) ?></h4>
                <p class="muted-copy mb-0 small">Bagan, daftar peserta, dan daftar pertandingan kategori terpilih.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="post" action="<?= base_url('admin/super/drawing-prestasi/tanding/' . $idKompetisi . '/sinkronkan-bagan') ?>" onsubmit="return confirmAdminAction(this, 'Sinkronkan Bagan?', 'Nama atlet & kontingen pada bagan akan disesuaikan dengan database.', 'Ya, Sinkronkan')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Sinkronkan Bagan</button>
                </form>
                <a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/super/drawing-prestasi/tanding/' . $idKompetisi . '/acak-manual') ?>" target="_blank">Manual Shuffle</a>
                <form method="post" action="<?= base_url('admin/super/drawing-prestasi/tanding/' . $idKompetisi . '/acak-bagan') ?>" onsubmit="return confirmAdminAction(this, 'Shuffle Formula?', 'Bagan lama yang belum dijadwalkan akan diganti dengan hasil acak formula.', 'Ya, Acak')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="mode" value="formula">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Shuffle Formula</button>
                </form>
                <form method="post" action="<?= base_url('admin/super/drawing-prestasi/tanding/' . $idKompetisi . '/acak-bagan') ?>" onsubmit="return confirmAdminAction(this, 'Full Random Persilat?', 'Bagan lama yang belum dijadwalkan akan diganti dengan full random + Persilat.', 'Ya, Acak')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="mode" value="full_random_persilat">
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill">Full Random Persilat</button>
                </form>
                <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?= base_url('admin/sekretariat/pool-tanding/' . $idKompetisi . '/bagan.pdf') ?>" target="_blank">Cetak Bagan</a>
            </div>
        </div>

        <ul class="nav nav-pills nav-fill mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#baganTanding" type="button" role="tab">Bagan</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pesertaTanding" type="button" role="tab">Daftar Peserta</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pertandinganTanding" type="button" role="tab">Daftar Pertandingan</button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="baganTanding" role="tabpanel">
                <?php if (! empty($selected->bagan_pertandingan)) : ?>
                    <?= view('shared_components/kompetisi_tanding/bagan_pertandingan', ['kompetisi_tanding' => $selected, 'toggle_early_match' => true]) ?>
                <?php else : ?>
                    <p class="muted-copy mb-0">Bagan belum tersedia. Silakan lakukan drawing terlebih dahulu.</p>
                <?php endif; ?>
            </div>

            <div class="tab-pane fade" id="pesertaTanding" role="tabpanel">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0" id="tabelPesertaDrawingTanding">
                        <thead>
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Nama Atlet</th>
                                <th>Kontingen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($peserta as $i => $item) : ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($item->nama_pendaftar ?? '-') ?></td>
                                    <td class="text-uppercase"><?= esc($item->nama_kontingen ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($peserta === []) : ?>
                                <tr><td colspan="3" class="text-center muted-copy">Belum ada peserta pada kategori ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="pertandinganTanding" role="tabpanel">
                <?= view('admin/sekretariat/pertandingan_tanding/_urutan_poin_table', ['rows' => $pertandinganRows ?? []]) ?>
            </div>
        </div>
    </section>
<?php endif; ?>
<?= $this->endSection() ?>
