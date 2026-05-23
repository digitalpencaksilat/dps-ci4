<?php if ($this->session->userdata('level') == 'sekretariat' && $this->uri->segment(4) == NULL || $this->session->userdata('level') == 'super_admin') : ?>
	<div class="row mt-4">
		<div class="col-12 col-md-4">
			<a type="button" target="_blank" href="<?= base_url('kompetisi-tanding/bagan/' . $kompetisi_tanding->id_kompetisi_tanding) ?>/print" class="m-0 w-100 btn btn-info">
				Print Chart
			</a>
		</div>
		<div class="col-12 col-md-4">
			<form action="<?= base_url('kompetisi-tanding/sinkronkan-bagan/' . $kompetisi_tanding->id_kompetisi_tanding) ?>" method="POST">
				<button type="button" onclick="confirm_submit('<?= lang('apakah_anda_yakin')?>', this, 'This chart will be synchronized, All modifications will be reset and adjusted according to the database !', 'Yes, Synchronize', true)" class="w-100 btn btn-outline-info">
					Synchronize Match Chart
				</button>
			</form>
		</div>
		<div class="col-12 col-md-4">
			<div class="dropdown">
				<button class="btn btn-primary dropdown-toggle w-100" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
					Shuffle Options
				</button>
				<ul class="dropdown-menu dropdown-menu-lg w-100" aria-labelledby="dropdownMenuButton">
					<li>
						<a class="dropdown-item py-2 w-100" href="<?= base_url('kompetisi-tanding/halaman-acak-bagan-manual/' . $kompetisi_tanding->id_kompetisi_tanding) ?>" target="_blank">
							Manual Shuffle
						</a>
					</li>
					<li>
						<form action="<?= base_url('kompetisi-tanding/acak-bagan/' . $kompetisi_tanding->id_kompetisi_tanding) ?>" method="POST">
							<button type="button" onclick="confirm_submit('<?= lang('apakah_anda_yakin')?>', this, 'This chart will be shuffled, All match data will be lost !', 'Start Drawing', true)" class="dropdown-item py-2 w-100">
								Shuffle (With Formula)
							</button>
						</form>
					</li>
					<li>
						<form action="<?= base_url('kompetisi-tanding/acak-bagan/' . $kompetisi_tanding->id_kompetisi_tanding) ?>" method="POST">
							<input type="hidden" name="random" value="true">
							<button type="button" onclick="confirm_submit('<?= lang('apakah_anda_yakin')?>', this, 'This chart will be shuffled, All match data will be lost !', 'Start Drawing', true)" class="dropdown-item py-2 w-100">
								Shuffle (Full Random Seed  + Persilat Standard)
							</button>
						</form>
					</li>
				</ul>
			</div>
		</div>
	</div>
<?php endif; ?>
