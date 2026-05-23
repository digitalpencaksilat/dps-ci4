<button type="button" class="btn btn-outline-primary mb-4 mt-0" data-bs-toggle="modal" data-bs-target="#modalInsertJadwalSeni">
	Add Schedule
</button>
<div class="modal fade" id="modalInsertJadwalSeni" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form action="<?= base_url('jadwal-seni/create') ?>" novalidate="novalidate" id="formInsertJadwalSeni" class="needs-validation" method="post" accept-charset="utf-8">
				<div class="modal-header">
					<h5 class="modal-title">Add Artistic Schedule</h5>
					<button type="button" class="btn btn-link m-0" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="id_gelanggang" class="form-label">Arena</label>
						<select class="form-select" name="id_gelanggang" id="id_gelanggang">
							<option selected> --- Choose Arena ---</option>
							<?php foreach ($data_gelanggang as $key => $gelanggang): ?>
								<option value="<?= $gelanggang->id_gelanggang?>">Arena <?= $gelanggang->nama_gelanggang?></option>
							<?php endforeach?>
						</select>
					</div>
					<div class="mb-3">
						<label for="tanggal" class="form-label">Date</label>
						<input type="date"
							class="form-control" name="tanggal" id="tanggal" aria-describedby="helpTanggal" placeholder="Date">
					</div>
					<div class="mb-3">
						<div class="form-group clockpicker">
							<label class="form-label" for="jam_mulai">Start Time :</label>
							<input type="text" class="form-control" value="08:00" name="jam_mulai" required>
							<div class="invalid-feedback">
								Please select start time
							</div>
						</div>
					</div>
					<div class="col-12 p-0 mb-3">
						<div class="form-group clockpicker">
							<label class="form-label" for="jam_selesai">End Time :</label>
							<input type="text" class="form-control" value="22:00" name="jam_selesai" required>
							<div class="invalid-feedback">
								Please select end time
							</div>
						</div>
					</div>

					<div class="mb-2">
						<label for="gelanggang_keterangan">Notes</label>
						<input type="text" name="keterangan" value="<?= (!empty($data_gelanggang->keterangan)) ? $data_gelanggang->keterangan : set_value('keterangan'); ?>" id="gelanggang_keterangan" class="form-control"/>
						<small class="text-danger"><?= form_error('keterangan'); ?></small>
					</div>

				</div>
				<div class="modal-footer">
					<button class="btn btn-secondary mb-0 me-2" data-bs-dismiss="modal">Close</button>
					<button class="btn btn-primary m-0" type="submit">Add</button>
				</div>
			</form>
		</div>
	</div>
</div>
