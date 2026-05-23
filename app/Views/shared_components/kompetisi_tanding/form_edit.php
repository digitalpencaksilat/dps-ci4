<form id="formEditKompetisiTanding" class="needs-validation" action="<?= base_url('kompetisi-tanding/update/' . $kompetisi_tanding->id_kompetisi_tanding) ?>" method="post" enctype="multipart/form-data" novalidate>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label>Max Peserta :</label>
                <input class="form-control" name="max_peserta" type="number" value="<?= $kompetisi_tanding->max_peserta ?>" placeholder="Max Peserta" required>
                <div class="invalid-feedback">
					Max peserta wajib diisi
                </div>
            </div>
            <div class="form-group">
                <label>Nomor Pool :</label>
                <input class="form-control" name="nomor_pool" type="number" value="<?= $kompetisi_tanding->nomor_pool ?>" placeholder="Nomor Pool" required>
                <div class="invalid-feedback">
					Nomor pool wajib diisi
                </div>
            </div>

            <p class="form-label">Hitung perolehan medali  :</p>
            <div class="form-check">
                <input type="radio" class="form-check-input" value="1" id="perhitungan_medali_1" name="perhitungan_medali" 
					required <?= ($kompetisi_tanding->perhitungan_medali == 1) ? 'checked' : '' ?>>

                <label class="form-check-label" for="perhitungan_medali_1">Dihitung</label>
            </div>
            <div class="form-check mb-3">
                <input type="radio" class="form-check-input" value="0" id="perhitungan_medali_0" name="perhitungan_medali" 
					required <?= ($kompetisi_tanding->perhitungan_medali == 0) ? 'checked' : '' ?>>

                <label class="form-check-label" for="perhitungan_medali_0">Tidak dihitung</label>
                <div class="invalid-feedback">Perhitungan medali wajib dipilih</div>
            </div>
			<div class="mb-3">
			  <label for="keterangan" class="form-label">Keterangan</label>
			  <textarea class="form-control" name="keterangan" id="keterangan" rows="3"><?= $kompetisi_tanding->keterangan?></textarea>
			</div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <button type="submit" class="w-100 btn btn-primary">Edit</button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {
        let form = $('#formEditKompetisiTanding')[0]

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            } else {
                setTimeout(function() {
                    waitingDialog.show('Sedang mengubah data');
                }, 500)
            }
            form.classList.add('was-validated')
        }, false)
    });
</script>
