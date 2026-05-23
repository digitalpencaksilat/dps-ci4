<div class="modal fade" id="modalTukarAtlet" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="true" role="dialog" aria-labelledby="modalTitleTukarAtlet" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
        <form action="<?= base_url('jadwal-tanding/tukar-atlet-tanding/') ?>" method="post" id="formTukarAtlet" onsubmit="submit_tukar_atlet_tanding()">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleTukarAtlet">Swap Athletes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_atlet_1" class="form-label">Athlete 1</label>
                        <select class="form-select" name="id_atlet_1" id="id_atlet_1">
                            <?php foreach ($data_peserta_tanding as $peserta_tanding): ?>
                                <option value="<?= $peserta_tanding->id_peserta_tanding ?>">
                                    <?= $peserta_tanding->nama_pendaftar . ' - ' . $peserta_tanding->nama_kontingen ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_atlet_2" class="form-label">Athlete 2</label>
                        <select class="form-select" name="id_atlet_2" id="id_atlet_2">
                            <?php foreach ($data_peserta_tanding as $peserta_tanding): ?>
                                <option value="<?= $peserta_tanding->id_peserta_tanding ?>">
                                    <?= $peserta_tanding->nama_pendaftar . ' - ' . $peserta_tanding->nama_kontingen ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Swap</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#id_atlet_1').select2({
            theme: "bootstrap-5",
            dropdownParent: $("#modalTukarAtlet")
        });

        $('#id_atlet_2').select2({
            theme: "bootstrap-5",
            dropdownParent: $("#modalTukarAtlet")
        });
    });

    function submit_tukar_atlet_tanding() {
        $('#modalTukarAtlet').modal('hide');
        waitingDialog.show('Swapping athletes...');
    }
</script>