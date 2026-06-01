<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$routePrefix = $routePrefix ?? 'admin/super/jadwal-seni';
$idJadwal    = (int) ($jadwal->id_jadwal_seni ?? 0);
?>
<div class="row">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card mb-3">
            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                <a href="<?= base_url($routePrefix . '/' . $idJadwal) ?>" class="text-decoration-none muted-copy small mb-2 d-block">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail Jadwal Seni
                </a>
                <h6 class="card-title mb-1">Set Match Sequence</h6>
                <p class="muted-copy small mb-0">
                    Arena <?= esc($jadwal->nama_gelanggang ?? '-') ?>
                    <?php if (! empty($jadwal->keterangan ?? null)): ?>
                        — <?= esc($jadwal->keterangan) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-body">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Drag &amp; drop baris partai untuk mengubah urutan. Nomor partai pada kolom kiri tetap berdasarkan urutan baris setelah drop, lalu klik <strong>Update</strong> untuk menyimpan.
                </div>

                <form action="<?= base_url($routePrefix . '/update-urutan-partai-seni/' . $idJadwal) ?>" method="post" id="formUrutanPartaiSeni">
                    <?= csrf_field() ?>

                    <div class="row g-2 mb-3 overflow-auto" style="max-height: 70vh;">
                        <div class="col-2 col-md-1">
                            <div class="d-flex flex-column gap-2" id="kolomNomorPartai">
                                <?php foreach (($details ?? []) as $row): ?>
                                    <div class="border rounded p-1 bg-white d-flex align-items-center justify-content-center" style="height:80px;">
                                        <input type="number" class="form-control form-control-sm text-center fw-bold"
                                            name="nomor_partai[]"
                                            value="<?= (int) ($row->nomor_partai ?? 0) ?>" min="1" required>
                                        <input type="hidden" name="id_detail_jadwal_seni[]"
                                            value="<?= (int) ($row->id_detail_jadwal_seni ?? 0) ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-10 col-md-11">
                            <ul class="list-group" id="listPertandinganSeni">
                                <?php foreach (($details ?? []) as $row): ?>
                                    <?php
                                    $isBattle = ! empty($row->id_battle_seni);
                                    ?>
                                    <li class="list-group-item p-2 mb-2 d-flex align-items-center gap-2"
                                        style="height:80px; cursor:move;">
                                        <input type="hidden" name="id_penampilan_seni[]"
                                            value="<?= (int) ($row->id_penampilan_seni ?? 0) ?>">
                                        <input type="hidden" name="id_battle_seni[]"
                                            value="<?= (int) ($row->id_battle_seni ?? 0) ?>">

                                        <i class="fas fa-grip-vertical text-muted me-1"></i>

                                        <?php if ($isBattle): ?>
                                            <!-- BATTLE -->
                                            <div class="text-center" style="min-width:100px;">
                                                <span class="badge bg-dark">BATTLE</span><br>
                                                <span class="small text-capitalize">
                                                    <?= esc(($row->nama_kategori_usia_battle ?? '-') . ' ' . ($row->jenis_kelamin_battle ?? '-')) ?><br>
                                                    <?= esc(($row->jenis_seni_battle ?? '-') . ' ' . ($row->nama_seni_battle ?? '-')) ?>
                                                </span>
                                            </div>
                                            <div class="flex-fill text-center small text-capitalize border-start border-end px-2">
                                                <?php if (! empty($row->anggota_kelompok_peserta_seni_biru ?? null)): ?>
                                                    <span class="fw-bold text-primary"><?= esc($row->anggota_kelompok_peserta_seni_biru) ?></span><br>
                                                    <span class="text-muted"><?= esc($row->nama_kontingen_biru ?? '-') ?></span>
                                                <?php else: ?>
                                                    <u class="fw-bold text-primary">
                                                        <?= ($row->babak_battle ?? '') === 'Perebutan Juara Tiga' ? 'Loser' : 'Winner' ?> of Match <?= esc($row->calon_anggota_kelompok_peserta_seni_biru ?? '?') ?>
                                                    </u>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-center fw-bold small" style="min-width:90px;">
                                                <?= esc($row->babak_battle ?? '-') ?>
                                            </div>
                                            <div class="flex-fill text-center small text-capitalize border-start px-2">
                                                <?php if (! empty($row->anggota_kelompok_peserta_seni_merah ?? null)): ?>
                                                    <span class="fw-bold text-danger"><?= esc($row->anggota_kelompok_peserta_seni_merah) ?></span><br>
                                                    <span class="text-muted"><?= esc($row->nama_kontingen_merah ?? '-') ?></span>
                                                <?php else: ?>
                                                    <u class="fw-bold text-danger">
                                                        <?= ($row->babak_battle ?? '') === 'Perebutan Juara Tiga' ? 'Loser' : 'Winner' ?> of Match <?= esc($row->calon_anggota_kelompok_peserta_seni_merah ?? '?') ?>
                                                    </u>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <!-- POOL -->
                                            <div class="text-center" style="min-width:140px;">
                                                <span class="badge bg-secondary">POOL</span><br>
                                                <span class="small text-capitalize">
                                                    <?= esc(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '-')) ?><br>
                                                    <?= esc(($row->jenis_seni ?? '-') . ' ' . ($row->nama_seni ?? '-')) ?> <span class="text-muted">Pool <?= esc($row->nomor_pool ?? '-') ?></span>
                                                </span>
                                            </div>
                                            <div class="flex-fill text-center small text-capitalize border-start border-end px-2">
                                                <span class="fw-bold"><?= esc($row->anggota_kelompok_peserta_seni ?? '-') ?></span><br>
                                                <span class="text-muted"><?= esc($row->nama_kontingen ?? '-') ?></span>
                                            </div>
                                            <div class="text-center fw-bold small" style="min-width:90px;">
                                                <?= esc($row->babak_pool ?? '-') ?>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <a href="<?= base_url($routePrefix . '/' . $idJadwal) ?>" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Urutan
                        </button>
                    </div>

                    <?php if (empty($details ?? [])): ?>
                        <div class="text-center muted-copy py-4">Belum ada partai pada arena ini.</div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery UI sortable: ensure jQuery + jQuery UI are loaded -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
(function () {
    function bindSortable() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.sortable !== 'function') {
            console.warn('jQuery UI sortable belum tersedia.');
            return;
        }
        jQuery('#listPertandinganSeni').sortable({
            placeholder: 'list-group-item bg-light',
            cursor: 'move',
            tolerance: 'pointer',
            axis: 'y',
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindSortable);
    } else {
        bindSortable();
    }
})();
</script>
<?= $this->endSection() ?>
