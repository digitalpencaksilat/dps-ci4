<div class="modal fade" id="modalAturPolaJadwal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleAturPolaJadwal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <form action="<?= base_url('jadwal-tanding/update-pola-penjadwalan/' . $jadwal_tanding->id_jadwal_tanding) ?>" method="post" id="formAturPolaPenjadwalan">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleAturPolaJadwal">
						Resort Match Number
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="jenis_pola" class="form-label">Jenis Pola</label>
                        <select name="jenis_pola_penjadwalan" id="jenis_pola" class="form-select">
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
                    <button type="submit" class="btn btn-primary">Resort Match</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Handle form submission
        $('#formAturPolaPenjadwalan').submit(function (event) {
            event.preventDefault(); // Prevent default form submission
            var formData = $(this).serialize(); // Serialize form data

            // Perform AJAX post
            $('#modalAturPolaJadwal').modal('hide'); 
            waitingDialog.show('Sedang mengatur ulang pola penjadwalan...');
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    waitingDialog.hide();
                    Swal.fire({
                        title: "Berhasil !",
                        text: "Pola penjadwalan berhasil dibuat ulang",
                        icon: "success",
                        confirmButtonText: "OK",
                    }).then((result) => {
                        location.reload();
                    });
                },
                error: function (xhr, status, error) {
                    waitingDialog.hide();
                    console.error('Error updating schedule pattern:', error);
                    Swal.fire('Error', 'Gagal mengatur ulang pola penjadwalan. Silahkan coba lagi.', 'error');
                }
            });
        });
    });
</script>
