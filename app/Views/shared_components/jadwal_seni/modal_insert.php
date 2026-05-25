<div class="modal fade" id="modalInsertJadwalSeni" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('admin/sekretariat/jadwal-seni/create') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Add Artistic Schedule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Arena</label>
                        <select class="form-select" name="id_gelanggang" required>
                            <option value="">--- Choose Arena ---</option>
                            <?php foreach (($data_gelanggang ?? []) as $gelanggang): ?>
                                <option value="<?= esc((string) $gelanggang->id_gelanggang) ?>">Arena <?= esc($gelanggang->nama_gelanggang) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="tanggal" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Time</label>
                        <input type="time" class="form-control" name="jam_mulai" value="08:00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Time</label>
                        <input type="time" class="form-control" name="jam_selesai" value="22:00" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <input type="text" class="form-control" name="keterangan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Close</button>
                    <button class="btn btn-danger" type="submit">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>
