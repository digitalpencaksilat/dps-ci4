<div class="modal fade" id="modalUbahKeteranganSeni<?= $jadwal->id_jadwal_seni ?>" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<form action="<?= base_url('jadwal-seni/update-keterangan/' . $jadwal->id_jadwal_seni) ?>" method="post" accept-charset="utf-8" class="form-ubah-keterangan-seni">
				<div class="modal-header">
					<h5 class="modal-title">Edit Schedule Notes</h5>
					<button type="button" class="btn btn-link m-0" data-bs-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">×</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="keterangan<?= $jadwal->id_jadwal_seni ?>" class="form-label">Notes</label>
						<input type="text" class="form-control" name="keterangan" id="keterangan<?= $jadwal->id_jadwal_seni ?>" value="<?= $jadwal->keterangan_jadwal ?>" placeholder="Enter schedule notes">
					</div>
					<div class="alert alert-info mb-0" role="alert" style="font-size: 0.85rem;">
						<i class="fas fa-info-circle me-1"></i>
						Keterangan diubah akan otomatis memperbarui file PDF jadwal. Proses ini membutuhkan beberapa detik.
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-secondary mb-0 me-2" data-bs-dismiss="modal" type="button">Close</button>
					<button class="btn btn-primary m-0" type="submit">Update</button>
				</div>
			</form>
		</div>
	</div>
</div>
