<div class="modal fade" id="modalEditBaganSeni" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalEditBaganSeniTitle" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="modalEditBaganSeniTitle">Edit Bagan Battle Seni</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
			</div>
			<div class="modal-body">
				<form id="formEditBaganSeni">
					<input type="hidden" name="id_kontingen">
					<input type="hidden" name="id_kelompok_peserta_seni">
					<div class="mb-3">
						<label for="anggota_kelompok_peserta_seni" class="form-label">Nama Atlet</label>
						<input type="text" class="form-control" name="anggota_kelompok_peserta_seni" id="anggota_kelompok_peserta_seni" placeholder="">
					</div>
					<div class="mb-3">
						<label for="nama_kontingen_seni" class="form-label">Nama Kontingen</label>
						<input type="text" class="form-control" name="nama_kontingen" id="nama_kontingen_seni" placeholder="">
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-admin-brand rounded-pill" id="buttonSaveEditBaganSeni">Simpan</button>
			</div>
		</div>
	</div>
</div>
