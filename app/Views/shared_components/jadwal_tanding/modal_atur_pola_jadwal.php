<?php
$routePrefix = (string) ($routePrefix ?? 'admin/sekretariat/jadwal-tanding');
// Pastikan route super_admin untuk super-admin features
$superRoute = ($routePrefix === 'admin/sekretariat/jadwal-tanding') ? 'admin/super/jadwal-tanding' : $routePrefix;
?>
<div class="modal fade" id="modalAturPolaJadwal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <form action="<?= base_url($superRoute . '/pola-penjadwalan/' . ($jadwal_tanding->id_jadwal_tanding ?? 0)) ?>" method="post" id="formAturPolaPenjadwalan">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resort Match Number</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jenis Pola</label>
                        <select name="jenis_pola_penjadwalan" class="form-select">
                            <option value="prestasi">Prestasi</option>
                            <option value="pemasalan_seling_1">Pemasalan Seling 1</option>
                            <option value="pemasalan_seling_2" selected>Pemasalan Seling 2</option>
                            <option value="pemasalan_seling_3">Pemasalan Seling 3</option>
                            <option value="pemasalan_seling_4">Pemasalan Seling 4</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Resort Match</button>
                </div>
            </div>
        </form>
    </div>
</div>
