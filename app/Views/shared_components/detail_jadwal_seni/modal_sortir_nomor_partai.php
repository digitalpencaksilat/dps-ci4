<?php
/**
 * Modal Sort Match Numbers (Resequence) untuk Jadwal Seni.
 *
 * Variables expected:
 *   $jadwal        - object jadwal_seni (harus punya id_jadwal_seni)
 *   $routePrefix   - string, e.g. 'admin/super/jadwal-seni'
 *   $modalSuffix   - string opsional, untuk membedakan instance modal pada tab pool/battle (e.g. 'Pool', 'Battle')
 */
$routePrefix = $routePrefix ?? 'admin/super/jadwal-seni';
$idJadwal    = $jadwal->id_jadwal_seni ?? 0;
$suffix      = $modalSuffix ?? '';
$modalId     = 'modalSortirNomorPartaiSeni' . $suffix;
?>
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-md">
        <form action="<?= base_url($routePrefix . '/resequence-nomor-partai') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="id_jadwal_seni" value="<?= (int) $idJadwal ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sort Match Numbers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-secondary small mb-3">
                        Re-sequence nomor partai pada arena ini berurutan rapi mulai dari nomor yang dipilih.
                        Urutan logis (sesuai nomor partai saat ini) akan tetap dipertahankan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="nomor_partai_baru_mulai_<?= $suffix ?: 'default' ?>">Nomor Partai Baru (Mulai dari)</label>
                        <input type="number" class="form-control" name="nomor_partai_baru_mulai"
                            id="nomor_partai_baru_mulai_<?= $suffix ?: 'default' ?>" value="1" min="1" required>
                        <small class="text-muted">Semua partai pada arena ini akan diurutkan ulang dimulai dari nomor ini.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Execute</button>
                </div>
            </div>
        </form>
    </div>
</div>
