<div class="row h-100">
	<?php if (!isset($toggle_early_match) || isset($toggle_early_match) && $toggle_early_match !== FALSE): ?>
		<div class="d-print-none col-12 text-end">
			<button class="btn btn-outline-primary btn-sm"
				id="toggleEarlyMatchButton<?= $kompetisi_tanding->id_kompetisi_tanding; ?>">
				Toggle Early Match
			</button>
		</div>
	<?php endif; ?>
	<div class="col-12 overflow-scroll">
		<?php if ($kompetisi_tanding->peraturan_pertandingan == 'Tapak Suci'): ?>
			<div id="baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>" class="Tapak_Suci"></div>
		<?php elseif ($kompetisi_tanding->peraturan_pertandingan == 'IPSI 2012'): ?>
			<div id="baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>" class="IPSI_2012"></div>
		<?php elseif ($kompetisi_tanding->peraturan_pertandingan == 'PERSILAT' || $kompetisi_tanding->peraturan_pertandingan == 'IPSI 2022'): ?>
			<div id="baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>" class="PERSILAT"></div>
		<?php endif; ?>
	</div>
</div>
<script>
	// INISIALISASI BAGAN AGAR DAPAT DITAMPILKAN + BEBERAPA METHOD
	$matchData<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = <?php echo $kompetisi_tanding->bagan_pertandingan; ?>;
	$id_kompetisi_tanding<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = <?php echo $kompetisi_tanding->id_kompetisi_tanding ?>;
	$juara_tiga_bersama<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = <?php echo $kompetisi_tanding->juara_tiga_bersama ?>;
	let baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = {
		teamWidth: 250,
		scoreWidth: 35,
		matchMargin: 55,
		//save: saveFn,
		roundMargin: 50,
		init: $matchData<?= $kompetisi_tanding->id_kompetisi_tanding; ?>,
		save: saveFn,
		disableToolbar: true,
		decorator: {
			edit: edit_fn,
			render: render_fn
		}
	};

	$(document).ready(function() {


		if ($juara_tiga_bersama<?= $kompetisi_tanding->id_kompetisi_tanding; ?> == 1) {
			//UNTUK PEREBUTAN JUARA TIGA BERSAMA, MAKA CONSOLATION ROUND AKAN DIHILANGKAN
			baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?>.skipConsolationRound = true;
		} else {
			baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?>.skipConsolationRound = false;
		}

		$bracket<?= $kompetisi_tanding->id_kompetisi_tanding; ?> = $('#baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>').bracket(baganParameters<?= $kompetisi_tanding->id_kompetisi_tanding; ?>);
		toggle_early_match<?= $kompetisi_tanding->id_kompetisi_tanding; ?>();


		$('#toggleEarlyMatchButton<?= $kompetisi_tanding->id_kompetisi_tanding; ?>').on('click', function() {
			toggle_early_match<?= $kompetisi_tanding->id_kompetisi_tanding; ?>();
		});
	});


	function toggle_early_match<?= $kompetisi_tanding->id_kompetisi_tanding; ?>() {
		$.each($('#baganPertandingan<?= $kompetisi_tanding->id_kompetisi_tanding ?>' + ' .bracket'), function(i_bracket, bracket) {
			$.each($(bracket).find('.round').first().find('.teamContainer'), function(i, e) {
				if ($(e).find('.na').length == 1) {
					$(e).toggle();
				}
			});
		});
	}


	function onhover(data, hover) {
		$('#matchCallback').text(data)
	}

	function saveFn(data, userData) {
		$.post('<?= base_url('kompetisi-tanding/update-bagan-pertandingan/' . $kompetisi_tanding->id_kompetisi_tanding) ?>', {
				bagan_pertandingan: JSON.stringify(data)
			},
			function(data, textStatus, jqXHR) {
				if (data.status !== true) {
					Swal.fire('Error', 'Gagal edit bagan, server tidak merespon !', 'error');
				}
			},
			"json"
		);
	}

	/* Edit function is called when team label is clicked */
	function edit_fn(container, data, doneCb) {
		// if ($('#modalEditBagan').length > 0) {

		// 	if (data !== null) {

		// 	}
		// 	$('#modalEditBagan').find('[name="id_pendaftar"]').val(data.id_pendaftar);
		// 	$('#modalEditBagan').find('[name="id_peserta_tanding"]').val(data.id_peserta_tanding);
		// 	$('#modalEditBagan').find('[name="id_kontingen"]').val(data.id_kontingen);
		// 	// 
		// 	$('#modalEditBagan').find('[name="nama_pendaftar"]').val(data.nama_pendaftar);
		// 	$('#modalEditBagan').find('[name="nama_kontingen"]').val(data.nama_kontingen);
		// 	$('#modalEditBagan').modal('show');

		// 	$('#buttonSaveEditBagan').bind('click', function (e) {
		// 		$nama_pendaftar = $('#modalEditBagan').find('[name="nama_pendaftar"]').val();
		// 		$nama_kontingen = $('#modalEditBagan').find('[name="nama_kontingen"]').val();
		// 		if ($nama_pendaftar == '' && $nama_kontingen == '') {
		// 			doneCb(null);
		// 		} else {
		// 			doneCb({
		// 				id_pendaftar: $('#modalEditBagan').find('[name="id_pendaftar"]').val(),
		// 				id_peserta_tanding: $('#modalEditBagan').find('[name="id_peserta_tanding"]').val(),
		// 				id_kontingen: $('#modalEditBagan').find('[name="id_kontingen"]').val(),
		// 				nama_pendaftar: $('#modalEditBagan').find('[name="nama_pendaftar"]').val(),
		// 				nama_kontingen: $('#modalEditBagan').find('[name="nama_kontingen"]').val(),
		// 			});
		// 		}
		// 		$('#modalEditBagan').modal('hide');
		// 	});
		// } else {
		// 	Swal.fire('Error', 'Tidak dapat edit bagan !', 'error');
		// }
	}

	function render_fn(container, data, score, state) {
		switch (state) {
			case 'empty-bye':
				container.append('BYE')
				return;
			case 'empty-tbd':
				container.append('TBD')
				return;

			case 'entry-no-score':
			case 'entry-default-win':
			case 'entry-complete':
				$string = '<div style="width: 20%;padding: 2px 6px;  height: 100%;float:left;">';
				$string += '<img style="max-width:100%;" src="http://localhost/dps/assets/images/bendera/id.png"/> ';
				$string += '</div>';
				$string += '<div style="width: 80%; height: 100%;float:left;">';
				$string += '<p class="nama_atlet_bagan">' + data.nama_pendaftar + '</p>';
				$string += '<p class="kontingen_bagan">' + data.nama_kontingen.toUpperCase() + '</p>';
				$string += '</div>';
				container.append($string)
				return;
		}
	}
</script>