<div class="row">
	<div class="col-12 col-xl-4">
		<form action="<?= base_url('kompetisi-tanding/buat-bagan-manual/' . $kompetisi_tanding->id_kompetisi_tanding) ?>" method="POST" id="formBuatBaganManual">

			<div class="card mb-2">
				<div class="card-header">
					<h6 class="card-title">
						List of Athletes
					</h6>
				</div>
				<div class="card-body min-vh-75 max-vh-100 overflow-scroll">
					<div class="row">
						<div class="col-12">
							<div class="table-responsive">
								<table class="table table-bordered" id="tabelDaftarAtlet">
									<thead>
										<tr>
											<th scope="col">#</th>
											<th scope="col">Name</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($data_peserta_tanding as $peserta_tanding) : ?>
											<tr>
												<td class="col-12 col-md-4 col-xxl-2">
													<div class="my-1">
														<input type="hidden" name="id_peserta_tanding[]" value="<?= $peserta_tanding->id_peserta_tanding ?>">
														<input type="hidden" class="nama_pendaftar" name="nama_pendaftar[]" value="<?= $peserta_tanding->nama_pendaftar ?>">
														<input type="hidden" class="nama_kontingen" name="nama_kontingen[]" value="<?= $peserta_tanding->nama_kontingen ?>">
														<input type="hidden" class="url_bendera" name="url_bendera[]" value="<?= bendera($peserta_tanding->negara) ?>">
														<select class="form-select select-slot-peserta-tanding" name="urutan_slot[]">
															<?php for ($slot = 0; $slot < $jumlah_peserta_tanding; $slot++) : ?>
																<option value="<?= $slot ?>"><?= ($slot + 1) ?></option>
															<?php endfor; ?>
															<option value="-" disabled selected>-</option>
														</select>
													</div>
												</td>
												<td class="col-12 col-md-8 col-xxl-10 ps-3">
													<div class="row">
														<div class="col-2 px-1 d-flex justify-content-center align-items-center">
															<img src="<?= bendera($peserta_tanding->negara) ?>" alt="<?= $peserta_tanding->negara ?>" class="img-fluid">
														</div>
														<div class="col-10">
															<p class="m-0 fw-bold">
																<?= ucwords($peserta_tanding->nama_pendaftar) ?>
															</p>
															<p class="text-sm text-decoration-italic m-0">
																<?= ucwords($peserta_tanding->nama_kontingen) ?>
															</p>
														</div>
													</div>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12 col-md-6">
					<?php $this->load->view('shared_components/kompetisi-tanding/roulette'); ?>
				</div>
				<div class="col-12 col-md-6">
					<button class="btn btn-primary w-100" onclick="confirm_submit('<?= lang('apakah_anda_yakin') ?>', this, 'The bracket will be created, the old bracket will be deleted !', 'Yes, Shuffle')" type="button">
						<?= lang('buat_bagan') ?>
					</button>
				</div>
			</div>
		</form>
	</div>
	<div class="col-12 col-xl-8">
		<div class="card mb-3">
			<div class="card-header">
				<div class="row">
					<div class="col-12 col-md-6">

						<h6 class="card-title">
							<?=
							$kompetisi_tanding->nama_kategori_usia . ' - ' . ucwords($kompetisi_tanding->gender) .
								', Class ' . $kompetisi_tanding->label . ' (' . $kompetisi_tanding->berat_minimal . ' Kg - ' . $kompetisi_tanding->berat_maksimal . ' Kg)'
							?>
							<?= ($kompetisi_tanding->jenis_perlombaan == 'pemasalan') ? ' Pool ' . $kompetisi_tanding->nomor_pool : '' ?>
						</h6>
					</div>


					<?php if (isset($prev_kompetisi_tanding) && isset($next_kompetisi_tanding)): ?>
						<div class="col-12 col-md-6">
							<div class="card">
								<div class="card-body py-2">
									<div class="row">
										<?php if ($prev_kompetisi_tanding !== NULL): ?>
											<div class="col-12 col-md-6">
												<a href="<?= base_url('kompetisi-tanding/drawing-tanding-prestasi?id_kompetisi_tanding=' . $prev_kompetisi_tanding->id_kompetisi_tanding) ?>" class="btn btn-lg btn-outline-primary w-100 m-0">
													< Prev
														</a>
											</div>
										<?php else: ?>
											<div class="col-12 col-md-6">
											</div>
										<?php endif; ?>

										<?php if ($next_kompetisi_tanding !== NULL): ?>
											<div class="col-12 col-md-6">
												<a href="<?= base_url('kompetisi-tanding/drawing-tanding-prestasi?id_kompetisi_tanding=' . $next_kompetisi_tanding->id_kompetisi_tanding) ?>" class="btn btn-lg btn-outline-primary w-100 m-0">
													Next >
												</a>
											</div>
										<?php else: ?>
											<div class="col-12 col-md-6">
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="card-body">
				<?php $this->load->view('shared_components/kompetisi_tanding/bagan_pertandingan', ['kompetisi_tanding' => $kompetisi_tanding]); ?>
				<?php $this->load->view('shared_components/kompetisi_tanding/modal_edit_bagan', ['kompetisi_tanding' => $kompetisi_tanding]); ?>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		//menutuo sude-nav
		$('.sidenav-toggler').click();
		$('#tabelDaftarAtlet').on('change', '.select-slot-peserta-tanding', function(e) {
			$slot_terpilih = e.target.value;
			if ($slot_terpilih !== '-') {
				$nama_pendaftar_terpilih = $(e.target).parent().find('.nama_pendaftar')[0].value;
				$nama_kontingen_terpilih = $(e.target).parent().find('.nama_kontingen')[0].value;
				$url_bendera = $(e.target).parent().find('.url_bendera')[0].value;

				live_update_bagan($slot_terpilih, $nama_pendaftar_terpilih, $nama_kontingen_terpilih, $url_bendera);

				// Enable all options
				$('.select-slot-peserta-tanding option').prop('disabled', false);

				// Disable selected option
				$('.select-slot-peserta-tanding option[value="' + $slot_terpilih + '"]').prop('disabled', true);
			}
		});
		$jumlah_peserta_tanding = <?= count($data_peserta_tanding) ?>;
		$jumlah_pertandingan_awal = <?= $jumlah_pertandingan_awal ?>;
		$('#formBuatBaganManual').on('submit', function() {
			$('.select-slot-peserta-tanding option').prop('disabled', false);
		});
	});


	function live_update_bagan($slot_terpilih, $nama_pendaftar_terpilih, $nama_kontingen_terpilih, $url_bendera) {
		$slot_terpilih = parseInt($slot_terpilih) + 1; // Harus ditambah 1 karena value asli dimulai dari 0
		$updated_match_data = $matchData<?= $kompetisi_tanding->id_kompetisi_tanding; ?>;

		$.each($updated_match_data.teams, function(i, v) {
			if (v[0] != null) {

				$nomor_slot_dicari = v[0].nomor_slot; // mencari di sudut biru
				if ($nomor_slot_dicari == $slot_terpilih) {
					$updated_match_data.teams[i][0].nama_pendaftar = $nama_pendaftar_terpilih + ' (#' + $slot_terpilih + ")";
					$updated_match_data.teams[i][0].nama_kontingen = $nama_kontingen_terpilih;
					$updated_match_data.teams[i][0].url_bendera = $url_bendera;
				} else {
					if (v[1] != null) {
						$nomor_slot_dicari = v[1].nomor_slot; // mencari di sudut merah
						if ($nomor_slot_dicari == $slot_terpilih) {
							$updated_match_data.teams[i][1].nama_pendaftar = $nama_pendaftar_terpilih + ' (#' + $slot_terpilih + ")";
							$updated_match_data.teams[i][1].nama_kontingen = $nama_kontingen_terpilih;
							$updated_match_data.teams[i][1].url_bendera = $url_bendera;
						}
					}
				}
			}
		});
		$matchData<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = $updated_match_data;
		buat_bagan();

		$('#toggleEarlyMatchButton<?= $kompetisi_tanding->id_kompetisi_tanding; ?>').click();
	}

	function buat_bagan() {
		$bracket<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = $('#baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>').bracket(baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?>);
	}
</script>