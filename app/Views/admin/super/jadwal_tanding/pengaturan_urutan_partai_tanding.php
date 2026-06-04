<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
$routePrefix = $routePrefix ?? 'admin/super/jadwal-tanding';
$idJadwal    = (int) ($jadwal->id_jadwal_tanding ?? 0);
?>
<div class="row">
    <div class="col-12 px-0 px-md-2">
        <div class="admin-card mb-3">
            <div class="card-header pb-0 border-bottom-0 bg-transparent px-0">
                <a href="<?= base_url($routePrefix . '/' . $idJadwal) ?>" class="text-decoration-none muted-copy small mb-2 d-block">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail Jadwal Tanding
                </a>
                <h6 class="card-title mb-1">Set Match Sequence</h6>
                <p class="muted-copy small mb-0">
                    Arena <?= esc($jadwal->nama_gelanggang ?? '-') ?>
                    <?php if (! empty($jadwal->keterangan_jadwal ?? $jadwal->keterangan ?? null)): ?>
                        — <?= esc($jadwal->keterangan_jadwal ?? $jadwal->keterangan) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="admin-card">
            <div class="card-body px-0 px-md-3">
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Drag &amp; drop baris partai untuk mengubah urutan. Nomor partai pada kolom kiri tetap berdasarkan urutan baris setelah drop, lalu klik <strong>Update</strong> untuk menyimpan.
                </div>

                <form action="<?= base_url($routePrefix . '/update-urutan-partai-tanding/' . $idJadwal) ?>" method="post" id="formUrutanPartaiTanding">
                    <?= csrf_field() ?>

                    <div class="row g-2 mb-3 overflow-auto" style="max-height: 70vh;">
                        <div class="col-2 col-md-1">
                            <div class="d-flex flex-column gap-2" id="kolomNomorPartai">
                                <?php foreach (($details ?? []) as $row): ?>
                                    <div class="border rounded p-1 bg-white d-flex align-items-center justify-content-center" style="height:80px;">
                                        <input type="number" class="form-control form-control-sm text-center fw-bold"
                                            name="nomor_partai[]"
                                            value="<?= (int) ($row->nomor_partai ?? 0) ?>" min="1" required>
                                        <input type="hidden" name="id_detail_jadwal_tanding[]"
                                            value="<?= (int) ($row->id_detail_jadwal_tanding ?? 0) ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-10 col-md-11">
                            <ul class="list-group" id="listPertandinganTanding">
                                <?php foreach (($details ?? []) as $row): ?>
                                    <li class="list-group-item p-2 mb-2 d-flex align-items-center gap-2"
                                        style="height:80px; cursor:move;">
                                        <input type="hidden" name="id_pertandingan[]"
                                            value="<?= (int) ($row->id_pertandingan ?? 0) ?>">

                                        <i class="fas fa-grip-vertical text-muted me-1"></i>

                                        <!-- Kategori + Kelas -->
                                        <div class="text-center" style="min-width:140px;">
                                            <span class="small text-capitalize">
                                                <?= esc(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '')) ?><br>
                                                <?= esc($row->label ?? '-') ?>
                                                <?= ($row->jenis_perlombaan ?? '') === 'pemasalan' ? ' Pool ' . esc((string) ($row->nomor_pool ?? '-')) : '' ?>
                                            </span>
                                        </div>

                                        <!-- Atlet Biru -->
                                        <div class="flex-fill text-center small text-capitalize border-start border-end px-2">
                                            <?php if (empty($row->nama_atlet_biru)): ?>
                                                <?php if (! empty($row->calon_atlet_biru)): ?>
                                                    <?php if (($row->babak ?? '') === 'Perebutan Juara Tiga'): ?>
                                                        <u class="fw-bold text-primary d-block">
                                                            Kalah dari Partai Ke <?= esc((string) $row->calon_atlet_biru) ?>
                                                        </u>
                                                        <span class="text-muted small">Dari Gelanggang <?= esc($row->gelanggang_calon_atlet_biru ?? '-') ?></span>
                                                    <?php else: ?>
                                                        <u class="fw-bold text-primary d-block">
                                                            Pemenang Partai Ke <?= esc((string) $row->calon_atlet_biru) ?>
                                                        </u>
                                                        <span class="text-muted small">Dari Gelanggang <?= esc($row->gelanggang_calon_atlet_biru ?? '-') ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="d-block text-muted fst-italic">TBD</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="fw-bold text-primary d-block"><?= esc($row->nama_atlet_biru) ?></span>
                                                <span class="text-muted small"><?= esc($row->nama_kontingen_biru ?? '-') ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Babak -->
                                        <div class="text-center fw-bold small" style="min-width:90px;">
                                            <?= esc($row->babak ?? '-') ?>
                                        </div>

                                        <!-- Atlet Merah -->
                                        <div class="flex-fill text-center small text-capitalize border-start px-2">
                                            <?php if (empty($row->nama_atlet_merah)): ?>
                                                <?php if (! empty($row->calon_atlet_merah)): ?>
                                                    <?php if (($row->babak ?? '') === 'Perebutan Juara Tiga'): ?>
                                                        <u class="fw-bold text-danger d-block">
                                                            Kalah dari Partai Ke <?= esc((string) $row->calon_atlet_merah) ?>
                                                        </u>
                                                        <span class="text-muted small">Dari Gelanggang <?= esc($row->gelanggang_calon_atlet_merah ?? '-') ?></span>
                                                    <?php else: ?>
                                                        <u class="fw-bold text-danger d-block">
                                                            Pemenang Partai Ke <?= esc((string) $row->calon_atlet_merah) ?>
                                                        </u>
                                                        <span class="text-muted small">Dari Gelanggang <?= esc($row->gelanggang_calon_atlet_merah ?? '-') ?></span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="d-block text-muted fst-italic">TBD</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="fw-bold text-danger d-block"><?= esc($row->nama_atlet_merah) ?></span>
                                                <span class="text-muted small"><?= esc($row->nama_kontingen_merah ?? '-') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end px-3 px-md-0">
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

<!-- jQuery UI sortable -->
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
        jQuery('#listPertandinganTanding').sortable({
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
