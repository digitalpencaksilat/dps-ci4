<div class="modal fade" id="modalEditBagan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalEditBaganTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
		<div class="modal-content">
			<form id="formEditBagan">
				<div class="modal-header">
					<h5 class="modal-title" id="modalEditBaganTitle">Edit Bagan Tanding</h5>
				</div>
				<div class="modal-body">
					<input type="hidden" name="id_pendaftar">
					<input type="hidden" name="id_kontingen">
					<input type="hidden" name="id_peserta_tanding">
					<div class="mb-3">
						<label for="nama_pendaftar" class="form-label">Nama Atlet </label>
						<input type="text" class="form-control" name="nama_pendaftar" id="nama_pendaftar" aria-describedby="helpNamaAtlet" placeholder="">
					</div>
					<div class="mb-3">
						<label for="nama_kontingen" class="form-label">Nama Kontingen </label>
						<input type="text" class="form-control" name="nama_kontingen" id="nama_kontingen" aria-describedby="helpNamaKontingen" placeholder="">
					</div>
				</div>
				<div class="modal-footer">
					<button type="reset" class="btn btn-secondary">Reset</button>
					<button type="button" class="btn btn-primary" id="buttonSaveEditBagan">Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>
