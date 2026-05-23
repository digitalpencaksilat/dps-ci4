<div class="modal fade" id="modalSortirNomorPartai" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
	role="dialog" aria-labelledby="modalTitleSortirUlangNomorPartai" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
		
		<form action="<?= base_url('jadwal-tanding/sortir-ulang-nomor-partai/'.$jadwal_tanding->id_jadwal_tanding)?>" method="post" id="formSortirNomorPartai">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modalTitleSortirUlangNomorPartai">
						Sortir Ulang Nomor Partai
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label for="" class="form-label">Nomor Partai Awal</label>
						<input
							type="text"
							class="form-control"
							name="nomor_partai_awal"
							onchange="ganti_partai_awal(this, <?= $jadwal_tanding->jumlah_partai?>)"
							placeholder="Nomor Awal"
							value="<?= $jadwal_tanding->nomor_partai_awal?>"
						/>
					</div>
					<div class="mb-3">
						<label for="nomor_partai_akhir" class="form-label">Nomor Partai Akhir</label>
						<input
							type="number"
							class="form-control"
							name="nomor_partai_akhir"
							id="nomor_partai_akhir"
							aria-describedby="helpNomorPartaiAkhir"
							placeholder="Otomatis Terisi"
							value="<?= intval($jadwal_tanding->nomor_partai_awal) + intval($jadwal_tanding->jumlah_partai)?>"
							disabled
						/>
						<small id="helpNomorPartaiAkhir" class="form-text text-muted">Otomatis Muncul</small>
					</div>
					
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
						Tutup
					</button>
					<button type="submit" class="btn btn-primary">Urutkan Ulang</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script>
	function ganti_partai_awal(element, $jumlah_partai){
		$nomor_partai_awal = $(element).val();
		$nomor_partai_akhir = parseInt($nomor_partai_awal) + parseInt($jumlah_partai);
		$('#formSortirNomorPartai').find('[name="nomor_partai_akhir"]').val($nomor_partai_akhir);
	}
</script>