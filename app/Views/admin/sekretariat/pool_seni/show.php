<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<section class="admin-card mb-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <div><p class="eyebrow mb-1">Pool Seni</p><h3 class="section-title h4 mb-0"><?= esc(($row->nama_seni ?? '-') . ' Pool ' . ($row->nomor_pool ?? '-')) ?></h3></div>
        <a class="btn btn-outline-secondary rounded-pill" href="<?= base_url('admin/sekretariat/pool-seni') ?>">Kembali</a>
    </div>
    <form method="post" action="<?= base_url('admin/sekretariat/pool-seni/' . $row->id_kompetisi_seni . '/update') ?>" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-md-3"><label class="form-label">Nomor Pool</label><input class="form-control" name="nomor_pool" value="<?= esc((string) ($row->nomor_pool ?? '')) ?>"></div>
        <div class="col-md-3"><label class="form-label">Max Peserta</label><input class="form-control" type="number" name="max_peserta" value="<?= esc((string) ($row->max_peserta ?? 0)) ?>"></div>
        <div class="col-md-3"><label class="form-label">Perhitungan Medali</label><select class="form-select" name="perhitungan_medali"><option value="1" <?= (int) ($row->perhitungan_medali ?? 0) === 1 ? 'selected' : '' ?>>Ya</option><option value="0" <?= (int) ($row->perhitungan_medali ?? 0) === 0 ? 'selected' : '' ?>>Tidak</option></select></div>
        <div class="col-md-12"><label class="form-label">Keterangan</label><textarea class="form-control" name="keterangan" rows="2"><?= esc($row->keterangan_kompetisi_seni ?? '') ?></textarea></div>
        <div class="col-12"><button class="btn btn-admin-brand rounded-pill">Simpan</button></div>
    </form>
    <form method="post" action="<?= base_url('admin/sekretariat/pool-seni/' . $row->id_kompetisi_seni . '/beri-nomor-undi') ?>" class="mt-2" onsubmit="return confirmAdminAction(this, 'Isi ulang nomor undi?', 'Nomor undi kelompok pada pool ini akan diurutkan ulang.', 'Lanjutkan')">
        <?= csrf_field() ?><button class="btn btn-outline-danger rounded-pill">Beri Nomor Undi</button>
    </form>
</section>
<section class="admin-card">
    <?php $isBattle = ($row->sistem_penampilan ?? '') === 'battle'; ?>
    <ul class="nav nav-pills nav-fill mb-4" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="<?= $isBattle ? '#battleSeni' : '#penampilanSeni' ?>" type="button"><?= $isBattle ? 'Battle' : 'Daftar Penampilan' ?></button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#kelompokSeni" type="button">Daftar Peserta</button></li>
    </ul>
    <div class="tab-content">
        <?php if ($isBattle) : ?>
            <div class="tab-pane fade show active" id="battleSeni">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3"><h4 class="h5 mb-0">Bagan Battle Seni</h4><div class="d-flex flex-wrap gap-2"><form method="post" action="<?= base_url('admin/sekretariat/pool-seni/' . $row->id_kompetisi_seni . '/acak-bagan-battle') ?>" onsubmit="return confirmAdminAction(this, 'Shuffle Formula?', 'Bagan lama yang belum dijadwalkan akan diganti dengan hasil acak formula.', 'Ya, Acak')"><?= csrf_field() ?><input type="hidden" name="mode" value="formula"><button class="btn btn-sm btn-outline-danger rounded-pill" type="submit">Shuffle Formula</button></form><form method="post" action="<?= base_url('admin/sekretariat/pool-seni/' . $row->id_kompetisi_seni . '/acak-bagan-battle') ?>" onsubmit="return confirmAdminAction(this, 'Full Random Persilat?', 'Bagan lama yang belum dijadwalkan akan diganti dengan full random + Persilat.', 'Ya, Acak')"><?= csrf_field() ?><input type="hidden" name="mode" value="full_random_persilat"><button class="btn btn-sm btn-danger rounded-pill" type="submit">Full Random Persilat</button></form><a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/pool-seni/' . $row->id_kompetisi_seni . '/bagan.pdf') ?>" target="_blank">Cetak Bagan</a></div></div>
                <?php if (! empty($row->bagan_battle_seni)) : ?>
                    <?= view('shared_components/kompetisi_seni/bagan_battle_seni', ['kompetisi_seni' => $row]) ?>
                <?php else : ?>
                    <p class="muted-copy">Bagan battle belum tersedia. Drawing belum dilakukan.</p>
                <?php endif; ?>
                <h4 class="h5 my-3">Daftar Battle</h4>
                <div class="table-responsive"><table class="table admin-table admin-datatable-export align-middle mb-0"><thead><tr><th>Babak</th><th>No</th><th>Merah</th><th>Biru</th><th>Partai</th><th>Gelanggang</th><th class="text-end no-export">Aksi</th></tr></thead><tbody><?php foreach (($battleRows ?? []) as $battle) : ?><tr><td><?= esc($battle->babak ?? '-') ?></td><td><?= esc((string) ($battle->nomor_battle ?? '-')) ?></td><td><?= esc($battle->anggota_kelompok_peserta_seni_merah ?? '-') ?> / <span class="text-uppercase"><?= esc($battle->nama_kontingen_merah ?? '-') ?></span></td><td><?= esc($battle->anggota_kelompok_peserta_seni_biru ?? '-') ?> / <span class="text-uppercase"><?= esc($battle->nama_kontingen_biru ?? '-') ?></span></td><td><?= esc((string) ($battle->nomor_partai ?? '-')) ?></td><td><?= esc($battle->nama_gelanggang ?? '-') ?></td><td class="text-end"><a class="btn btn-sm btn-outline-danger rounded-pill" href="<?= base_url('admin/sekretariat/battle-seni/' . $battle->id_battle_seni) ?>">Detail</a></td></tr><?php endforeach; ?></tbody></table></div>
            </div>
        <?php else : ?>
            <div class="tab-pane fade show active" id="penampilanSeni">
                <h4 class="h5 mb-3">Penyisihan</h4>
                <?= view('admin/sekretariat/pool_seni/_penampilan_table', ['rows' => $penampilanPenyisihanRows ?? []]) ?>
                <h4 class="h5 my-3">Final</h4>
                <?= view('admin/sekretariat/pool_seni/_penampilan_table', ['rows' => $penampilanFinalRows ?? []]) ?>
            </div>
        <?php endif; ?>
        <div class="tab-pane fade" id="kelompokSeni">
            <div class="table-responsive"><table class="table admin-table admin-datatable-export align-middle mb-0"><thead><tr><th>No Undi</th><th>Kontingen</th><th>Anggota</th></tr></thead><tbody><?php foreach (($kelompok ?? []) as $item) : ?><tr><td><?= esc((string) ($item->nomor_undi ?? '-')) ?></td><td class="text-uppercase"><?= esc($item->nama_kontingen ?? '-') ?></td><td><?= esc($item->anggota_kelompok_peserta_seni ?? '-') ?></td></tr><?php endforeach; ?></tbody></table></div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
