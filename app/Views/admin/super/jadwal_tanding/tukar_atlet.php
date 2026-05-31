<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="admin-card mb-4">
    <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
        <h6 class="card-title">Tukar Atlet</h6>
        <p class="muted-copy small mb-0">Satu halaman sederhana untuk tukar atlet tanding dan tukar kelompok peserta seni pool.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="admin-card h-100">
            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                <h6 class="card-title mb-1">Tukar Atlet Tanding</h6>
                <p class="muted-copy small mb-0">Pilih dua atlet tanding yang ingin ditukar pada data pertandingan.</p>
            </div>
            <div class="card-body px-0">
                <form action="<?= base_url(($routePrefixTanding ?? 'admin/super/jadwal-tanding') . '/tukar-atlet') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Atlet 1</label>
                        <select class="form-select" name="id_atlet_1" required>
                            <option value="">Pilih atlet</option>
                            <?php foreach (($data_peserta_tanding ?? []) as $pesertaTanding) : ?>
                                <option value="<?= esc((string) $pesertaTanding->id_peserta_tanding) ?>">
                                    <?= esc(($pesertaTanding->nama_pendaftar ?? ('Peserta #' . ($pesertaTanding->id_peserta_tanding ?? '-'))) . ' - ' . ($pesertaTanding->nama_kontingen ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Atlet 2</label>
                        <select class="form-select" name="id_atlet_2" required>
                            <option value="">Pilih atlet</option>
                            <?php foreach (($data_peserta_tanding ?? []) as $pesertaTanding) : ?>
                                <option value="<?= esc((string) $pesertaTanding->id_peserta_tanding) ?>">
                                    <?= esc(($pesertaTanding->nama_pendaftar ?? ('Peserta #' . ($pesertaTanding->id_peserta_tanding ?? '-'))) . ' - ' . ($pesertaTanding->nama_kontingen ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger">Tukar Atlet Tanding</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="admin-card h-100">
            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                <h6 class="card-title mb-1">Tukar Atlet Seni Pool</h6>
                <p class="muted-copy small mb-0">Pilih dua slot penampilan seni pool yang ingin ditukar.</p>
            </div>
            <div class="card-body px-0">
                <form action="<?= base_url(($routePrefixSeni ?? 'admin/super/jadwal-seni') . '/tukar-kelompok-peserta-seni-pool') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Peserta Seni 1</label>
                        <select class="form-select" name="id_penampilan_seni_1" required>
                            <option value="">Pilih peserta seni</option>
                            <?php foreach (($poolSwapCandidates ?? []) as $candidate) : ?>
                                <option value="<?= esc((string) $candidate->id_penampilan_seni) ?>">
                                    <?= esc(($candidate->nama_seni ?? '-') . ' - Arena ' . ($candidate->nama_gelanggang ?? '-') . ' - Partai ' . ($candidate->nomor_partai ?? '-') . ' - ' . ($candidate->nama_kontingen ?? '-') . (! empty($candidate->anggota_kelompok) ? ' - ' . $candidate->anggota_kelompok : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Peserta Seni 2</label>
                        <select class="form-select" name="id_penampilan_seni_2" required>
                            <option value="">Pilih peserta seni</option>
                            <?php foreach (($poolSwapCandidates ?? []) as $candidate) : ?>
                                <option value="<?= esc((string) $candidate->id_penampilan_seni) ?>">
                                    <?= esc(($candidate->nama_seni ?? '-') . ' - Arena ' . ($candidate->nama_gelanggang ?? '-') . ' - Partai ' . ($candidate->nomor_partai ?? '-') . ' - ' . ($candidate->nama_kontingen ?? '-') . (! empty($candidate->anggota_kelompok) ? ' - ' . $candidate->anggota_kelompok : '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger">Tukar Atlet Seni Pool</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
