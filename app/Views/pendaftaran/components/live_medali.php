<div class="section" id="live_medali">
	<div class="container">
		<div class="row">
			<div class="col">
				<h2 class="text-center my-2 fw-bold">Perolehan Medali</h2>
				<p class="mb-4 text-center"><a href="javascript:window.location.reload(true)"><u>Refresh halaman</u></a> untuk mendapatkan hasil terbaru</p>
				<ul class="nav nav-pills nav-pills-primary flex-nowrap overflow-auto" role="tablist">
					<?php $pane_number = 0; ?>
					<?php foreach ($data_akumulasi_medali_by_kategori_usia as $kategori_usia => $kontingen) : ?>
						<li class="nav-item mb-1" style="display: inline-block;">
							<a class="nav-link text-nowrap <?php echo ($pane_number == 0) ? "active" : "" ?>" data-toggle="tab" href="#tab<?= $pane_number++ ?>" role="tablist">
								<?= $kategori_usia ?>
							</a>
						</li>
					<?php endforeach ?>
				</ul>
				<div class="tab-content tab-space">
					<?php $pane_number = 0; ?>
					<?php foreach ($data_akumulasi_medali_by_kategori_usia as $kategori_usia => $kontingen) : ?>
						<div class="tab-pane <?php echo ($pane_number == 0) ? "active" : "" ?>" id="tab<?= $pane_number++ ?>">
							<div class="table-responsive">
								<table class="table table-hover table-striped tabel_live_medali w-100">
									<thead>
										<tr>
											<th>No</th>
											<th>Kontingen</th>
											<th>Emas</th>
											<th>Perak</th>
											<th>Perunggu</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$no = 1;
										foreach ($kontingen as $data) {
											echo '
													<tr>
														<td class="text-center">' . $no++ . '</td>
														<td>' . $data->nama_kontingen . '</td>
														<td>' . (intval($data->emas_tanding) + intval($data->emas_seni)) . '</td>
														<td>' . (intval($data->perak_tanding) + intval($data->perak_seni)) . '</td>
														<td>' . (intval($data->perunggu_tanding) + intval($data->perunggu_seni)) . '</td>
													</tr>
												';
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					<?php endforeach ?>
				</div>
			</div>
		</div>
	</div>

</div>
