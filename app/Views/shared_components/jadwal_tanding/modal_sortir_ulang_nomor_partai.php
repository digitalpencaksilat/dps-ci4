<?php
$routePrefix = (string) ($routePrefix ?? 'admin/sekretariat/jadwal-tanding');
$superRoute = ($routePrefix === 'admin/sekretariat/jadwal-tanding') ? 'admin/super/jadwal-tanding' : $routePrefix;
?>
<div class="modal fade" id="modalSortirNomorPartai" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <form action="<?= base_url($superRoute . '/sortir-ulang/' . ($jadwal_tanding->id_jadwal_tanding ?? 0)) ?>" method="post" id="formSortirNomorPartai">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sortir Ulang Nomor Partai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nomor Partai Awal</label>
                        <input type="text" class="form-control" name="nomor_partai_awal"
                               onchange="ganti_partai_awal(this, <?= esc((string) ($jadwal_tanding->jumlah_partai ?? 0)) ?>)"
                               value="<?= esc((string) ($jadwal_tanding->nomor_partai_awal ?? 0)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Partai Akhir</label>
                        <input type="text" class="form-control" name="nomor_partai_akhir" id="nomor_partai_akhir"
                               value="<?= esc((string) ((int) ($jadwal_tanding->nomor_partai_awal ?? 0) + (int) ($jadwal_tanding->jumlah_partai ?? 0))) ?>" disabled>
                        <small class="form-text text-muted">Otomatis Muncul</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-danger">Urutkan Ulang</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
function ganti_partai_awal(element, jumlah_partai) {
    var nomor_partai_awal = element.value;
    var nomor_partai_akhir = parseInt(nomor_partai_awal) + parseInt(jumlah_partai);
    document.getElementById('nomor_partai_akhir').value = nomor_partai_akhir;
}
</script>
