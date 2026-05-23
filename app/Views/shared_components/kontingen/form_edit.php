<form id="formEditKontingen" method="post" action="<?= base_url('kontingen/update/' . $kontingen->id_kontingen) ?>" enctype="multipart/form-data" novalidate>
	<div class="row">
		<div class="col-md-6">
			<div class="mb-3">
				<label class="form-label">Nama Kontingen :</label>
				<input class="form-control" placeholder="contoh : SDN 14 Bikini Bottom" name="nama_kontingen" type="text" required="required" value="<?= set_value('nama_kontingen', $kontingen->nama_kontingen) ?>">
				<div class="invalid-feedback">
					Wajib Diisi
				</div>
			</div>
			<div class="mb-3">
				<label class="form-label">Email Kontingen</label>
				<input class="form-control" placeholder="contoh : kontingen@gmail.com" name="email_kontingen" type="email" required="required" value="<?= set_value('email_kontingen', $kontingen->email_kontingen) ?>">
				<div class="invalid-feedback">
					Wajib Diisi dengan format email yang sesuai
				</div>
			</div>
			<div class="mb-2">
				<label class="form-label">Asal Kontingen</label>
				<div class="form-check">
					<label class="form-check-label">
						<input type="radio" class="form-check-input" name="jenis_kontingen" value="dalam_negeri" required="required" <?= ($kontingen->jenis_kontingen == 'dalam_negeri') ? 'checked' : '' ?> <?= set_radio('jenis_kontingen', 'dalam_negeri') ?>>
						Dalam Negeri
					</label>
				</div>
				<div class="form-check">
					<label class="form-check-label">
						<input type="radio" class="form-check-input" name="jenis_kontingen" value="luar_negeri" required="required" <?= ($kontingen->jenis_kontingen == 'luar_negeri') ? 'checked' : '' ?> <?= set_radio('jenis_konitngen', 'luar_negeri') ?>>
						Luar Negeri
					</label>
				</div>
			</div>
			<div class="mb-3">
				<label class="form-label">Nama Penanggungjawab :</label>
				<input class="form-control" name="nama_penanggungjawab" type="text" required="required" value="<?= set_value('nama_penanggungjawab', $kontingen->nama_penanggungjawab) ?>">
				<div class="invalid-feedback">
					Wajib Diisi
				</div>
			</div>
			<div class="mb-3">
				<label class="form-label">Nomor Telepon Penanggungjawab :</label>
				<input class="form-control" name="nomor_telepon_penanggungjawab" type="number" min="0" placeholder=" contoh : 08113555571" required="required" value="<?= set_value('nomor_telepon_penanggungjawab', $kontingen->nomor_telepon_penanggungjawab) ?>">
				<div class="invalid-feedback">
					Wajib Diisi (berupa angka 08xxxx)
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="mb-3 negara">
				<label class="form-label">Negara :</label>
				<select name="negara" id="inputnegara" class="form-control" required="required">
					<option value="" disabled>-- Isi Asal Kontingen Terlebih Dahulu --</option>
				</select>
				<div class="invalid-feedback">
					Wajib Diisi
				</div>
				<p class="form-text text-muted">
					Apabila data negara tidak ditemukan, silahkan pilih negara terdekat
				</p>
			</div>
			<div class="mb-3 provinsi">
				<label class="form-label">Provinsi :</label>
				<select name="provinsi" id="inputprovinsi" class="form-control">
					<option value="" disabled>-- Isi Asal Kontingen Terlebih Dahulu --</option>
				</select>
				<div class="invalid-feedback">
					Wajib Diisi
				</div>
			</div>
			<div class="mb-3 kabupaten_kota">
				<label class="form-label">Kabupaten / Kota :</label>
				<select name="kabupaten_kota" id="inputkabupaten_kota" class="form-control">
					<option value="" disabled>-- Isi Provinsi Terlebih Dahulu --</option>
				</select>
				<div class="invalid-feedback">
					Wajib Diisi
				</div>
			</div>
			<div class="mb-3 kecamatan">
				<label class="form-label" for="inputKecamatan">Kecamatan :</label>
				<select name="kecamatan" id="inputKecamatan" class="form-control">
					<option value="" disabled>-- Isi Data Lokasi Diatasnya Terlebih Dahulu --</option>
				</select>
				<div class="invalid-feedback">
					Wajib Diisi
				</div>
				<p class="form-text text-muted">
					Apabila data kecamatan tidak ditemukan, silahkan pilih kecamatan terdekat
				</p>
			</div>
			<div class="mb-3 kelurahan">
				<label class="form-label" for="inputKelurahan">Kelurahan :</label>
				<select name="kelurahan" id="inputKelurahan" class="form-control">
					<option value="" disabled>-- Isi Data Lokasi Diatasnya Terlebih Dahulu --</option>
				</select>
				<div class="invalid-feedback">
					Wajib Diisi
				</div>
				<p class="form-text text-muted">
					Apabila data kelurahan tidak ditemukan, silahkan pilih kelurahan terdekat
				</p>
			</div>
			<div class="mb-3">
				<label for="alamat_lengkap" class="form-label">Alamat Lengkap</label>
				<textarea class="form-control" name="alamat_lengkap" id="alamat_lengkap" rows="3" required><?= $kontingen->alamat_lengkap ?></textarea>
				<div class="invalid-feedback">
					Wajib Diisi
				</div>
				<p class="form-text text-muted">
					Diperlukan untuk pengiriman sertifikat atau keperluan lainnya
				</p>
			</div>
		</div>
		<div class="col-lg-12 text-center mt-5">
			<button class="btn btn-primary w-100" type="submit">Edit</button>
		</div>
	</div>
</form>

<script>
	(() => {
		'use strict'
		const forms = document.querySelectorAll('#formEditKontingen')

		Array.from(forms).forEach(form => {
			form.addEventListener('submit', event => {
				if (!form.checkValidity()) {
					event.preventDefault()
					event.stopPropagation()
				}
				form.classList.add('was-validated')
			}, false)
		})
	})()


	$(function() {


        $('[name="negara"], [name="provinsi"], [name="kabupaten_kota"], [name="kecamatan"]').select2({
            theme: "bootstrap-5",
        });

		$jenis_kontingen = '<?= $kontingen->jenis_kontingen; ?>';
		$negara = '<?= $kontingen->negara; ?>';
		if ($jenis_kontingen == "dalam_negeri") {
			$('#formEditKontingen .provinsi, #formEditKontingen .kabupaten_kota, #formEditKontingen .kecamatan, #formEditKontingen .kelurahan').show('slow');
			$('#formEditKontingen .negara').hide('fast');
			set_negara();
			set_lokasi();
		} else {
			$('#formEditKontingen .provinsi, #formEditKontingen .kabupaten_kota, #formEditKontingen .kecamatan, #formEditKontingen .kelurahan').hide('slow');
			$('#formEditKontingen .negara').show('fast');
			set_negara();
		}

		$('#formEditKontingen').on('change', '[name="jenis_kontingen"]', function(e) {
			if (e.target.value == "dalam_negeri") {
				$('#formEditKontingen .provinsi, #formEditKontingen .kabupaten_kota, #formEditKontingen .kecamatan, #formEditKontingen .kelurahan').show('slow');
				get_provinsi(function() {
					$('#formEditKontingen [name="provinsi"] option[value="<?= $kontingen->provinsi ?>"]').attr('selected', 'selected');
				});
				$('#formEditKontingen .negara').hide('fast');
			} else {
				set_negara();
				$('#formEditKontingen .negara').show('fast');
				$('#formEditKontingen .provinsi, #formEditKontingen .kabupaten_kota, #formEditKontingen .kecamatan, #formEditKontingen .kelurahan').hide('slow');
			}
		});

	})


	function get_negara() {
		$.getJSON("<?php echo base_url("utilities/location/load-negara") ?>",
			function(data, textStatus, jqXHR) {
				$('#formEditKontingen [name="negara"]').html('');
				$.each(data, function(i, v) {
					$('#formEditKontingen [name="negara"]').append('<option data-id-negara="' + v + '" value="' + i + '">' + i + '</option>');
				});
			}
		);
	}


	function set_negara(){
		$.getJSON("<?php echo base_url("utilities/location/load-negara") ?>",
			function(data, textStatus, jqXHR) {
				$('#formEditKontingen [name="negara"]').html('');
				$.each(data, function(i, v) {
					if(v == $negara){
						$('#formEditKontingen [name="negara"]').append('<option selected data-id-negara="' + v + '" value="' + i + '">' + i + '</option>');
					}else{
						$('#formEditKontingen [name="negara"]').append('<option data-id-negara="' + v + '" value="' + i + '">' + i + '</option>');
					}
				});
			}
		);
	}

	function set_lokasi() {
		// SET LOKASI TIDAK MENGGUNAKAN METHOD YG MODULAR

		// Get Provinsi sdh otomatis mengambil daerah dibawahnya
		$.getJSON("<?php echo base_url("utilities/location/load-provinsi") ?>",
			function(data, textStatus, jqXHR) {
				$('#formEditKontingen [name="provinsi"]').html('');
				$.each(data, function(i, v) {
					$('#formEditKontingen [name="provinsi"]').append('<option data-id-provinsi="' + v + '" value="' + i + '">' + i + '</option>');
				});

				$('#formEditKontingen [name="provinsi"] [value="<?= $kontingen->provinsi ?>"]').attr('selected', 'selected');
				//GET JSON KABUPATEN_KOTA

				$.getJSON("<?php echo base_url("utilities/location/load-kabupaten-kota/") ?>" + $('[name="provinsi"]').find(':selected').data('id-provinsi'),
					function(data, textStatus, jqXHR) {
						$('#formEditKontingen [name="kabupaten_kota"]').html('');
						$.each(data, function(i, v) {
							$('#formEditKontingen [name="kabupaten_kota"]').append('<option data-id-kabupaten-kota="' + v + '" value="' + i + '">' + i + '</option>');
						});

						$('#formEditKontingen [name="kabupaten_kota"] [value="<?= $kontingen->kabupaten_kota ?>"]').attr('selected', 'selected');

						// Get Kabupaten kota sudah otomatis get kecamatan

						$.getJSON("<?php echo base_url("utilities/location/load-kecamatan/") ?>" + $('[name="kabupaten_kota"]').find(':selected').data('id-kabupaten-kota'),
							function(data, textStatus, jqXHR) {
								$('#formEditKontingen [name="kecamatan"]').html('');
								$.each(data, function(i, v) {
									$('#formEditKontingen [name="kecamatan"]').append('<option data-id-kecamatan="' + v + '" value="' + i + '">' + i + '</option>');
								});

								$('#formEditKontingen [name="kecamatan"] [value="<?= $kontingen->kecamatan ?>"]').attr('selected', 'selected');

								// LOAD KELURAHAN
								$.getJSON("<?php echo base_url("utilities/location/load-kelurahan/") ?>" + $('[name="kecamatan"]').find(':selected').data('id-kecamatan'),
									function(data, textStatus, jqXHR) {
										$('#formEditKontingen [name="kelurahan"]').html('');
										$.each(data, function(i, v) {
											$('#formEditKontingen [name="kelurahan"]').append('<option data-id-kelurahan="' + v + '" value="' + i + '">' + i + '</option>');
										});
										$('#formEditKontingen [name="kelurahan"] [value="<?= $kontingen->kelurahan ?>"]').attr('selected', 'selected');



										// INITIATE EVENT HANDLER

										$('#formEditKontingen').on('change', '[name="provinsi"]', function(e) {
											//GET JSON KABUPATEN_KOTA
											get_kabupaten_kota();
										});

										$('#formEditKontingen').on('change', '[name="kabupaten_kota"]', function(e) {
											//GET JSON KECAMATAN
											get_kecamatan();
										});

										$('#formEditKontingen').on('change', '[name="kecamatan"]', function(e) {
											//GET JSON KELURAHAN
											get_kelurahan();
										});
									}
								);
							}
						);
					}
				);
			}
		);

	}


	function get_provinsi($callback = null) {
		// Get Provinsi sdh otomatis mengambil daerah dibawahnya
		$.getJSON("<?php echo base_url("utilities/location/load-provinsi") ?>",
			function(data, textStatus, jqXHR) {
				$('#formEditKontingen [name="provinsi"]').html('');
				$.each(data, function(i, v) {
					$('#formEditKontingen [name="provinsi"]').append('<option data-id-provinsi="' + v + '" value="' + i + '">' + i + '</option>');
				});

				if ($callback !== null) {
					$callback();
				}
				//CALLBACK UPDATE KABUPATEN_KOTA
				//GET JSON KABUPATEN_KOTA
				get_kabupaten_kota();
			}
		);
	}

	function get_kabupaten_kota($callback = null) {
		$.getJSON("<?php echo base_url("utilities/location/load-kabupaten-kota/") ?>" + $('[name="provinsi"]').find(':selected').data('id-provinsi'),
			function(data, textStatus, jqXHR) {
				$('#formEditKontingen [name="kabupaten_kota"]').html('');
				$.each(data, function(i, v) {
					$('#formEditKontingen [name="kabupaten_kota"]').append('<option data-id-kabupaten-kota="' + v + '" value="' + i + '">' + i + '</option>');
				});

				if ($callback !== null) {
					$callback();
				}
				// Get Kabupaten kota sudah otomatis get kecamatan
				get_kecamatan();

			}
		);
	}

	function get_kecamatan() {
		$.getJSON("<?php echo base_url("utilities/location/load-kecamatan/") ?>" + $('[name="kabupaten_kota"]').find(':selected').data('id-kabupaten-kota'),
			function(data, textStatus, jqXHR) {
				$('#formEditKontingen [name="kecamatan"]').html('');
				$.each(data, function(i, v) {
					$('#formEditKontingen [name="kecamatan"]').append('<option data-id-kecamatan="' + v + '" value="' + i + '">' + i + '</option>');
				});
				get_kelurahan();
			}
		);
	}

	function get_kelurahan() {
		$.getJSON("<?php echo base_url("utilities/location/load-kelurahan/") ?>" + $('[name="kecamatan"]').find(':selected').data('id-kecamatan'),
			function(data, textStatus, jqXHR) {
				$('#formEditKontingen [name="kelurahan"]').html('');
				$.each(data, function(i, v) {
					$('#formEditKontingen [name="kelurahan"]').append('<option data-id-kelurahan="' + v + '" value="' + i + '">' + i + '</option>');
				});
			}
		);
	}
</script>