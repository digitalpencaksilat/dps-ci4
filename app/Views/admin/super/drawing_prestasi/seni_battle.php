<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php
$baseUrl = base_url('admin/super/drawing-prestasi/seni-battle');

$labelKategori = static function ($row): string {
    $label = trim(($row->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($row->jenis_kelamin ?? '')));
    $label .= ', ' . ucwords((string) ($row->jenis_seni ?? '')) . ' ' . ($row->nama_seni ?? '');
    $pool = ($row->jenis_perlombaan ?? '') === 'pemasalan' ? ' Pool ' . ($row->nomor_pool ?? '-') : '';
    $jumlah = ' - ' . (int) ($row->jumlah_kelompok_peserta_seni ?? 0) . ' Peserta';

    return $label . $pool . $jumlah;
};
?>

<section class="admin-card mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-1">
        <div>
            <p class="eyebrow mb-1">Drawing Prestasi</p>
            <h3 class="section-title h4 mb-0">Drawing Seni Battle</h3>
            <p class="muted-copy mb-0 small">Pilih satu kategori seni prestasi bersistem battle untuk melihat bagan dan melakukan pengacakan.</p>
        </div>
    </div>

    <form action="<?= $baseUrl ?>" method="get" class="row g-2 align-items-end mt-2">
        <div class="col-12 col-lg-9">
            <label class="form-label small fw-semibold">Kategori Seni Battle Prestasi</label>
            <select class="form-select" name="id" required>
                <option value="" disabled <?= $selected === null ? 'selected' : '' ?>>-- Pilih kategori --</option>
                <?php foreach ($rows as $row) : ?>
                    <option value="<?= (int) $row->id_kompetisi_seni ?>" <?= ($selected !== null && (int) $selected->id_kompetisi_seni === (int) $row->id_kompetisi_seni) ? 'selected' : '' ?>>
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
        <p class="muted-copy mb-0 mt-3">Belum ada kategori seni battle prestasi yang tersedia.</p>
    <?php endif; ?>

    <?php if ($selected !== null) : ?>
        <div class="row g-2 mt-3">
            <div class="col-6">
                <?php if ($prev !== null) : ?>
                    <a class="btn btn-outline-danger rounded-pill w-100 text-truncate" href="<?= $baseUrl . '?id=' . (int) $prev->id_kompetisi_seni ?>">
                        <i class="fas fa-chevron-left me-1"></i> <?= esc(trim(($prev->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($prev->jenis_seni ?? '')) . ' ' . ($prev->nama_seni ?? ''))) ?>
                    </a>
                <?php endif; ?>
            </div>
            <div class="col-6 text-end">
                <?php if ($next !== null) : ?>
                    <a class="btn btn-outline-danger rounded-pill w-100 text-truncate" href="<?= $baseUrl . '?id=' . (int) $next->id_kompetisi_seni ?>">
                        <?= esc(trim(($next->nama_kategori_usia ?? '') . ' - ' . ucwords((string) ($next->jenis_seni ?? '')) . ' ' . ($next->nama_seni ?? ''))) ?> <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php if ($selected !== null) : ?>
    <?php
    $idKompetisi = (int) $selected->id_kompetisi_seni;
    $jumlahKelompok = (int) ($selected->jumlah_kelompok_peserta_seni ?? 0);
    ?>

    <?php if (($selected->sistem_penampilan ?? '') !== 'battle') : ?>
        <section class="admin-card">
            <h4 class="h5 mb-2">Ooopss</h4>
            <p class="muted-copy mb-0">Kategori ini tidak dikonfigurasi dengan sistem penampilan <strong>battle</strong>, sehingga drawing battle tidak dapat dilakukan.</p>
        </section>
    <?php elseif ($jumlahKelompok <= 1) : ?>
        <section class="admin-card">
            <h4 class="h5 mb-2">Ooopss</h4>
            <p class="muted-copy">Pool ini hanya berisi <?= count($kelompok) ?> peserta, minimal 2 peserta diperlukan untuk drawing battle.</p>
            <ul class="list-group">
                <?php foreach ($kelompok as $item) : ?>
                    <li class="list-group-item">
                        <?= esc($item->anggota_kelompok_peserta_seni ?? '-') ?><br><strong class="text-uppercase"><?= esc($item->nama_kontingen ?? '-') ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php else : ?>
        <section class="admin-card">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h4 class="h5 mb-1"><?= esc($labelKategori($selected)) ?></h4>
                    <p class="muted-copy mb-0 small">Bagan battle dan daftar battle kategori terpilih.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <form method="post" action="<?= base_url('admin/super/drawing-prestasi/seni-battle/' . $idKompetisi . '/sinkronkan-bagan') ?>" onsubmit="return confirmAdminAction(this, 'Sinkronkan Bagan?', 'Nama anggota & kontingen pada bagan akan disesuaikan dengan database.', 'Ya, Sinkronkan')">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Sinkronkan Bagan</button>
                    </form>
                    <form method="post" action="<?= base_url('admin/super/drawing-prestasi/seni-battle/' . $idKompetisi . '/acak-bagan') ?>" onsubmit="return confirmAdminAction(this, 'Shuffle Formula?', 'Bagan lama yang belum dijadwalkan akan diganti dengan hasil acak formula.', 'Ya, Acak')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="mode" value="formula">
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">Shuffle Formula</button>
                    </form>
                    <form method="post" action="<?= base_url('admin/super/drawing-prestasi/seni-battle/' . $idKompetisi . '/acak-bagan') ?>" onsubmit="return confirmAdminAction(this, 'Full Random Persilat?', 'Bagan lama yang belum dijadwalkan akan diganti dengan full random + Persilat.', 'Ya, Acak')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="mode" value="full_random_persilat">
                        <button type="submit" class="btn btn-sm btn-danger rounded-pill">Full Random Persilat</button>
                    </form>
                    <a class="btn btn-sm btn-outline-secondary rounded-pill" href="<?= base_url('admin/sekretariat/pool-seni/' . $idKompetisi . '/bagan.pdf') ?>" target="_blank">Cetak Bagan</a>
                </div>
            </div>

            <ul class="nav nav-pills nav-fill mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#baganBattle" type="button" role="tab">Bagan Battle</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#daftarBattle" type="button" role="tab">Daftar Battle</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#daftarPeserta" type="button" role="tab">Daftar Peserta</button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="baganBattle" role="tabpanel">
                    <?php if (! empty($selected->bagan_battle_seni)) : ?>
                        <?= view('shared_components/kompetisi_seni/bagan_battle_seni', ['kompetisi_seni' => $selected]) ?>
                        <?= view('shared_components/kompetisi_seni/modal_edit_bagan') ?>
                    <?php else : ?>
                        <p class="muted-copy mb-0">Bagan battle belum tersedia. Silakan lakukan drawing terlebih dahulu.</p>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="daftarBattle" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Babak</th><th>No</th><th>Merah</th><th>Biru</th><th>Partai</th><th>Gelanggang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (($battleRows ?? []) as $battle) : ?>
                                    <tr>
                                        <td><?= esc($battle->babak ?? '-') ?></td>
                                        <td><?= esc((string) ($battle->nomor_battle ?? '-')) ?></td>
                                        <td><?= esc($battle->anggota_kelompok_peserta_seni_merah ?? '-') ?> / <span class="text-uppercase"><?= esc($battle->nama_kontingen_merah ?? '-') ?></span></td>
                                        <td><?= esc($battle->anggota_kelompok_peserta_seni_biru ?? '-') ?> / <span class="text-uppercase"><?= esc($battle->nama_kontingen_biru ?? '-') ?></span></td>
                                        <td><?= esc((string) ($battle->nomor_partai ?? '-')) ?></td>
                                        <td><?= esc($battle->nama_gelanggang ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (($battleRows ?? []) === []) : ?>
                                    <tr><td colspan="6" class="text-center muted-copy">Belum ada battle. Lakukan drawing terlebih dahulu.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="daftarPeserta" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr><th>No Undi</th><th>Anggota</th><th>Kontingen</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kelompok as $item) : ?>
                                    <tr>
                                        <td><?= esc((string) ($item->nomor_undi ?? '-')) ?></td>
                                        <td><?= esc($item->anggota_kelompok_peserta_seni ?? '-') ?></td>
                                        <td class="text-uppercase"><?= esc($item->nama_kontingen ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
