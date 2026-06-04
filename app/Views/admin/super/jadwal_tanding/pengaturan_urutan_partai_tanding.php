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
                    Drag &amp; drop baris untuk mengubah urutan. Nomor partai otomatis mengikuti urutan setelah drop. Edit nomor langsung di kolom <strong>Match</strong> bila perlu, lalu klik <strong>Update</strong>.
                </div>

                <form action="<?= base_url($routePrefix . '/update-urutan-partai-tanding/' . $idJadwal) ?>" method="post" id="formUrutanPartaiTanding">
                    <?= csrf_field() ?>

                    <div class="overflow-auto mb-3" style="max-height: 70vh;">
                        <ul class="list-group" id="listPertandinganTanding">
                            <?php foreach (($details ?? []) as $row): ?>
                                <li class="list-group-item p-0" style="min-height:70px; cursor:move;">
                                    <div class="row g-0 align-items-stretch" style="min-height:70px;">
                                        <!-- Nomor Partai -->
                                        <div class="col-1 d-flex align-items-center justify-content-center border-end bg-light">
                                            <input type="number" class="form-control form-control-sm text-center fw-bold border-0 bg-transparent"
                                                name="nomor_partai[]"
                                                value="<?= (int) ($row->nomor_partai ?? 0) ?>"
                                                min="1" required
                                                style="width:60px;">
                                            <input type="hidden" name="id_detail_jadwal_tanding[]" value="<?= (int) ($row->id_detail_jadwal_tanding ?? 0) ?>">
                                            <input type="hidden" name="id_pertandingan[]" value="<?= (int) ($row->id_pertandingan ?? 0) ?>">
                                        </div>

                                        <!-- Kategori & Kelas -->
                                        <div class="col-2 d-flex align-items-center border-end small text-capitalize px-2">
                                            <div class="w-100 text-center">
                                                <span class="d-block">
                                                    <?= esc(($row->nama_kategori_usia ?? '-') . ' ' . ($row->jenis_kelamin ?? '')) ?>
                                                </span>
                                                <span class="d-block text-muted">
                                                    <?= esc($row->label ?? '-') ?>
                                                    <?= ($row->jenis_perlombaan ?? '') === 'pemasalan' ? ' Pool ' . esc((string) ($row->nomor_pool ?? '-')) : '' ?>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Atlet Biru -->
                                        <div class="col d-flex align-items-center border-end px-2 small text-capitalize">
                                            <div class="w-100 text-center">
                                                <?php if (empty($row->nama_atlet_biru)): ?>
                                                    <?php if (! empty($row->calon_atlet_biru)): ?>
                                                        <?php if (($row->babak ?? '') === 'Perebutan Juara Tiga'): ?>
                                                            <u class="fw-bold d-block" style="color:var(--corner-blue);">
                                                                Kalah dari Partai Ke <?= esc((string) $row->calon_atlet_biru) ?>
                                                            </u>
                                                            <span class="text-muted">Dari Gelanggang <?= esc($row->gelanggang_calon_atlet_biru ?? '-') ?></span>
                                                        <?php else: ?>
                                                            <u class="fw-bold d-block" style="color:var(--corner-blue);">
                                                                Pemenang Partai Ke <?= esc((string) $row->calon_atlet_biru) ?>
                                                            </u>
                                                            <span class="text-muted">Dari Gelanggang <?= esc($row->gelanggang_calon_atlet_biru ?? '-') ?></span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">TBD</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="fw-bold d-block" style="color:var(--corner-blue);"><?= esc($row->nama_atlet_biru) ?></span>
                                                    <span class="text-muted"><?= esc($row->nama_kontingen_biru ?? '-') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Babak -->
                                        <div class="col-2 d-flex align-items-center justify-content-center border-end fw-bold small">
                                            <?= esc($row->babak ?? '-') ?>
                                        </div>

                                        <!-- Atlet Merah -->
                                        <div class="col d-flex align-items-center px-2 small text-capitalize">
                                            <div class="w-100 text-center">
                                                <?php if (empty($row->nama_atlet_merah)): ?>
                                                    <?php if (! empty($row->calon_atlet_merah)): ?>
                                                        <?php if (($row->babak ?? '') === 'Perebutan Juara Tiga'): ?>
                                                            <u class="fw-bold d-block" style="color:var(--corner-red);">
                                                                Kalah dari Partai Ke <?= esc((string) $row->calon_atlet_merah) ?>
                                                            </u>
                                                            <span class="text-muted">Dari Gelanggang <?= esc($row->gelanggang_calon_atlet_merah ?? '-') ?></span>
                                                        <?php else: ?>
                                                            <u class="fw-bold d-block" style="color:var(--corner-red);">
                                                                Pemenang Partai Ke <?= esc((string) $row->calon_atlet_merah) ?>
                                                            </u>
                                                            <span class="text-muted">Dari Gelanggang <?= esc($row->gelanggang_calon_atlet_merah ?? '-') ?></span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">TBD</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="fw-bold d-block" style="color:var(--corner-red);"><?= esc($row->nama_atlet_merah) ?></span>
                                                    <span class="text-muted"><?= esc($row->nama_kontingen_merah ?? '-') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Drag Handle -->
                                        <div class="col-auto d-flex align-items-center pe-2 text-muted" style="width:36px;">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end px-3 px-md-0">
                        <a href="<?= base_url($routePrefix . '/' . $idJadwal) ?>" class="btn btn-outline-dark">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-dark">
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

<!-- jQuery UI: jQuery SUDAH ada di layout admin. Hanya tambah jQuery UI. -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
(function () {
    function bindSortable() {
        if (typeof jQuery === 'undefined') {
            console.warn('jQuery tidak tersedia — sortable dibatalkan.');
            return;
        }
        if (typeof jQuery.fn.sortable !== 'function') {
            console.warn('jQuery UI sortable belum tersedia — menunggu 300ms dan retry.');
            setTimeout(bindSortable, 300);
            return;
        }

        jQuery('#listPertandinganTanding').sortable({
            placeholder: 'list-group-item bg-light border border-dashed',
            cursor: 'move',
            tolerance: 'pointer',
            axis: 'y',
            handle: null,
            update: function () {
                // After sort, renumber the match numbers sequentially
                jQuery('#listPertandinganTanding input[name="nomor_partai[]"]').each(function (i) {
                    jQuery(this).val(i + 1);
                });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindSortable);
    } else {
        // DOM sudah ready — tapi jQuery UI mungkin belum selesai load.
        // Polling sampai sortable tersedia.
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.sortable === 'function') {
            bindSortable();
        } else {
            // Tunggu jQuery UI selesai load
            var checkInterval = setInterval(function () {
                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.sortable === 'function') {
                    clearInterval(checkInterval);
                    bindSortable();
                }
            }, 200);
            // Stop setelah 5 detik
            setTimeout(function () { clearInterval(checkInterval); }, 5000);
        }
    }
})();
</script>

<style>
/* Nomor partai input — compact */
#listPertandinganTanding input[name="nomor_partai[]"] {
    -moz-appearance: textfield;
}
#listPertandinganTanding input[name="nomor_partai[]"]::-webkit-outer-spin-button,
#listPertandinganTanding input[name="nomor_partai[]"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Drag handle visual */
#listPertandinganTanding .list-group-item .fa-grip-vertical {
    cursor: grab;
    font-size: 1.1rem;
}

/* Placeholder saat drag */
#listPertandinganTanding .ui-sortable-placeholder {
    visibility: visible !important;
    border: 2px dashed var(--admin-accent, #c60000) !important;
    background: rgba(198, 0, 0, 0.04) !important;
    min-height: 70px;
}

/* Dragging item */
#listPertandinganTanding .ui-sortable-helper {
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    border: 1px solid var(--admin-border, rgba(198, 0, 0, 0.08));
    background: #fff;
}
</style>
<?= $this->endSection() ?>
