<div class="modal fade" id="modalUbahKeteranganTanding<?= esc((string) $jadwal->id_jadwal_tanding) ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/sekretariat/jadwal-tanding/' . $jadwal->id_jadwal_tanding . '/update-keterangan') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Schedule Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <input type="text" class="form-control" name="keterangan" value="<?= esc($jadwal->keterangan_jadwal ?? $jadwal->keterangan ?? '') ?>" placeholder="Enter schedule notes">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                    <button class="btn btn-danger" type="submit">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
