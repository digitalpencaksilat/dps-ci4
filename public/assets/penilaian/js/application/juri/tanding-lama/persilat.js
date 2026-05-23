
const juri = {
	/**
	 * !!! script tanding tidak dirancang untuk cross format penilaian
	 * !!! script ini hanya untuk format penilaian PERSILAT
	 */
	data_nilai: null,
	data_waktu: null,
	waktu_sekarang: null,
	pertandingan: null,
	id_pertandingan: null,
	ronde_pertandingan: null,
	nilai_akhir_merah: 0,
	nilai_akhir_biru: 0,
	pemenang : null,
	verifikasi_pertandingan : null,
	jawaban_verifikasi_pertandingan : null,
	init: function ($data_nilai, $pertandingan, $pemenang, $verifikasi_pertandingan, $jawaban_verifikasi_pertandingan, $mode = 'juri') {
		juri.start_animation();
		juri.set_variable($data_nilai, $pertandingan, $pemenang, $verifikasi_pertandingan, $jawaban_verifikasi_pertandingan, $mode = 'juri');
		juri.update_tampilan_nilai();
		juri.refresh_status_pertandingan();
		juri.set_ronde();
	},
	set_variable: function ($data_nilai, $pertandingan, $pemenang, $verifikasi_pertandingan, $jawaban_verifikasi_pertandingan, $mode = 'juri') {
		juri.data_nilai = $data_nilai;
		juri.pertandingan = $pertandingan;
		juri.data_waktu = $pertandingan.data_waktu;
		juri.waktu_sekarang = $pertandingan.data_waktu[$pertandingan.ronde_pertandingan][1];
		juri.mode = $mode;
		juri.id_pertandingan = $pertandingan.id_pertandingan;
		juri.ronde_pertandingan = $pertandingan.ronde_pertandingan;
		juri.pemenang = $pemenang;
		juri.verifikasi_pertandingan = $verifikasi_pertandingan;
		juri.jawaban_verifikasi_pertandingan = $jawaban_verifikasi_pertandingan;
	},
	start_animation: function(){
		$('#header-tanding').addClass('animated fadeInDown').removeClass('opacity');
		setTimeout(() => {
			$('.card-container-juri').addClass('animated fadeIn').removeClass('opacity')
			
			setTimeout(() => {
				$('#tabel-nilai-juri tr').each(function (index, element) {
					setTimeout(() => {	
						$(element).addClass('animated fadeInDown').removeClass('opacity');
					}, 300 * index);
				});

				setTimeout(() => {
					$('#button-merah button').each(function (index, element) {
						setTimeout(() => {	
							$(element).addClass('animated fadeIn').removeClass('opacity');
						}, 350 * index);
					});
					$('#button-biru button').each(function (index, element) {
						setTimeout(() => {	
							$(element).addClass('animated fadeIn').removeClass('opacity');
						}, 350 * index);
					});
				}, 1700);

			}, 600);
		}, 600);
	},
	update_tampilan_nilai: function () {
		/**
		 * Fungsi ini hanya dieksekusi ketika inisiasi halaman dan juri berhasil update data 
		 * ke database
		 */
		$.each(juri.data_nilai, function (sudut, v) {
			$.each(v.ronde_pertandingan, function (ronde, nilai) {
				let jumlah_rincian_nilai = nilai['rincian'].length;
				$('.' + sudut + '-ronde-' + ronde + '-nilai').empty();
				$('.' + sudut + '-ronde-' + ronde + '-hukuman').empty();
				
				$rincian_nilai = '';
				$rincian_hukuman = '';
	
				if (jumlah_rincian_nilai > 0) {
					$.each(nilai['rincian'], function (index, nilai) {
						// Check if entry is soft-deleted
						if(nilai.is_deleted === true) {
							const timestamp = new Date(nilai.deleted_at * 1000).toLocaleTimeString();
							if(parseInt(nilai['nilai']) > 0) {
								// Soft-deleted regular scores
								$rincian_nilai += '<span class="fw-lighter text-decoration-line-through px-2 d-inline-block" title="deleted at ' + timestamp + '" style="color:#999999">' + nilai['nilai'] + '</span>';
							} else {
								// Soft-deleted penalty scores
								$rincian_hukuman += '<span class="fw-lighter text-decoration-line-through" title="deleted at ' + timestamp + '" style="color:#999999">' + nilai['nilai'] + '</span>';
								if (index < jumlah_rincian_nilai - 1) {
									$rincian_hukuman += ', ';
								}
							}
							return true; // Continue to next iteration
						}
						
						// Handle non-deleted entries
						if(parseInt(nilai['nilai']) > 0) {
							if(nilai['status'] == 'input') {
								$rincian_nilai += '<span class="fw-lighter text-decoration-line-through px-2 d-inline-block" style="color:#999999">' + nilai['nilai'] + '</span>';
							} else {
								$rincian_nilai += '<span class="px-2 d-inline-block">' + nilai['nilai'] + '</span>';
							}
						} else {
							$rincian_hukuman += nilai['nilai'];
							if (index < jumlah_rincian_nilai - 1) {
								$rincian_hukuman += ', ';
							}
						}
					});
				} else {
					$('.' + sudut + '-ronde-' + ronde + '-nilai').html('<span>&emsp;</span>');
				}
	
				$('.' + sudut + '-ronde-' + ronde + '-nilai').html($rincian_nilai);
				$('.' + sudut + '-ronde-' + ronde + '-hukuman').html($rincian_hukuman);
				$('.' + sudut + '-ronde-' + ronde + '-total').html(nilai['ringkasan']['nilai_akhir']);
			});
		});
	
		$('#total_nilai_akhir_biru').html(juri.pertandingan.skor_biru);
		$('#total_nilai_akhir_merah').html(juri.pertandingan.skor_merah);
	
		if (juri.pertandingan.skor_merah > juri.pertandingan.skor_biru) {
			$('#total_nilai_akhir_biru').parent().removeClass('bg-gradient-180-blue').addClass('bg-gradient-180-gray-dark');
			$('#total_nilai_akhir_merah').parent().addClass('bg-gradient-180-red').removeClass('bg-gradient-180-gray-dark');
		} else if (juri.pertandingan.skor_merah < juri.pertandingan.skor_biru) {
			$('#total_nilai_akhir_merah').parent().removeClass('bg-gradient-180-red').addClass('bg-gradient-180-gray-dark');;
			$('#total_nilai_akhir_biru').parent().addClass('bg-gradient-180-blue').removeClass('bg-gradient-180-gray-dark');;
		} else {
			$('#total_nilai_akhir_merah').parent().removeClass('bg-gradient-180-red').addClass('bg-gradient-180-gray-dark');;
			$('#total_nilai_akhir_biru').parent().removeClass('bg-gradient-180-blue').addClass('bg-gradient-180-gray-dark');;
		}

		// juri.highlight_pemenang($pemenang);

	},
	set_ronde: function(){
		$('td.ronde-1, td.ronde-2, td.ronde-3').removeClass('bg-warning');
		$('td.ronde-'+juri.ronde_pertandingan).addClass('bg-warning');
	},

	edit_penilaian_tanding: function ($sudut, $nilai = null, $btn) {
		// Disable the button temporarily
		$($btn).prop('disabled', true);

		// Construct a new entry for adding or specify the removal
		let entry;
		if ($nilai !== null) {
			entry = {
				nilai: $nilai,
				waktu_pertandingan: juri.waktu_sekarang,
				timestamp: null, // Use a timestamp from the client side
				status: 'input'
			};
		} else {
			entry = { action: 'remove' }; // Define an action to remove the latest entry
		}
	
		// Send only the new entry to the backend
		$.post('juri/edit-penilaian-tanding/' + juri.id_pertandingan, {
			sudut: $sudut,
			entry: JSON.stringify(entry)
		}, function (data, textStatus, jqXHR) {
			$($btn).prop('disabled', false); // Enable the button again
	
			if (data.status === true) {
				juri.data_nilai = data.response;
				juri.update_tampilan_nilai(); // Refresh the view
			} else {
				Swal.fire('Error', 'Gagal mengubah penilaian', 'error');
			}
		}, "json");
	},
	// edit_penilaian_tanding: function ($sudut, $nilai = null, $btn) {
	// 	/**
	// 	 * !!! script ini juga digunakan untuk menambahkan hukuman
	// 	 * @params int nilai, jika bernilai null, maka sedang menghapus nilai 
	// 	 */

	// 	// MENUTUP AKSES TOMBOL
	// 	if($sudut == 'merah'){
	// 		$('.btn-sudut-merah').prop('disabled', true);
	// 	}else{
	// 		$('.btn-sudut-biru').prop('disabled', true);
	// 	}
		
	// 	$updated_nilai = juri.data_nilai;
	// 	if ($nilai == null) {
	// 		$jumlah_entry_nilai = $updated_nilai[$sudut]['ronde_pertandingan'][juri.ronde_pertandingan]['rincian'].length;

	// 		$index_hapus = $jumlah_entry_nilai - 1;
	// 		while($index_hapus >= 0){
	// 			$nilai = $updated_nilai[$sudut]['ronde_pertandingan'][juri.ronde_pertandingan]['rincian'][$index_hapus].nilai;
	// 			if($nilai == 1 || $nilai == 2){
	// 				// Juri hanya bisa hapus nilai pukulan dan tendangan, tidak dengan hukuman maupun jatuhan
	// 				if($updated_nilai[$sudut]['ronde_pertandingan'][juri.ronde_pertandingan]['rincian'][$index_hapus]['status'] == 'input'){
	// 					$updated_nilai[$sudut]['ronde_pertandingan'][juri.ronde_pertandingan]['rincian'].splice($index_hapus, 1);
	// 					break;
	// 				}else{
	// 					$index_hapus--;
	// 				}
	// 			}else{
	// 				if($index_hapus > 0){
	// 					$index_hapus--;
	// 				}
	// 			}
	// 		}
	// 	} else {
	// 		$nilai = {
	// 			nilai: $nilai,
	// 			waktu_pertandingan: juri.waktu_sekarang,
	// 			timestamp: null,
	// 			status: 'input'
	// 		}
	// 		$updated_nilai[$sudut]['ronde_pertandingan'][juri.ronde_pertandingan]['rincian'].push($nilai);
	// 	}

	// 	//MEMBUKA AKSES TOMBOL
	// 	// setTimeout(() => {	
	// 	// 	$('button').prop('disabled', false);
	// 	// 	$('button').removeAttr('disabled');
	// 	// }, 600);

	// 	juri.data_nilai = $updated_nilai;
	// 	juri.kirim_nilai_tanding($updated_nilai);
	// },
	kirim_nilai_tanding: ($updated_nilai) => {
		$start_time = new Date().getTime();
		$.post('juri/edit-penilaian-tanding/' + juri.id_pertandingan, {
			penilaian_merah: JSON.stringify($updated_nilai['merah']),
			penilaian_biru: JSON.stringify($updated_nilai['biru']),
			pemenang : null
		},
			function (data, textStatus, jqXHR) {
				$request_time = new Date().getTime() - $start_time;
				if (data.status == true) {
					juri.data_nilai = data.response;
					juri.update_tampilan_nilai();
					setTimeout(() => {	
						// MEMBUKA AKASES SEMUA TOMBOL
						$('button').prop('disabled'	, false);
						$('button').removeAttr('disabled');
					}, 400);
				} else {
					Swal.fire('Error', 'Gagal mengubah penilaian', 'error');
				}
			}, "json"
		);
	},
	warning_pindah_babak : function(){
		Swal.fire({
			title: "Error",
			text: 'Perpindahan babak hanya dapat dilakukan oleh operator !',
			icon: "error"
		})
	},
	modalVerifikasiJatuhan : new bootstrap.Modal(document.getElementById('modalVerifikasiJatuhan'), {
		keyboard: false
	}),
	modalVerifikasiPelanggaran : new bootstrap.Modal(document.getElementById('modalVerifikasiPelanggaran'), {
		keyboard: false
	}),
	submit_jawaban_verifikasi_pertandingan: function($jawaban){
		$.post("jawaban-verifikasi-pertandingan/update/" + juri.jawaban_verifikasi_pertandingan.id_jawaban_verifikasi_pertandingan, 
			{"jawaban" : $jawaban},
			function (data, textStatus, jqXHR) {
				if (data.status === false) {
					Swal.fire({
						title: "Error",
						text: 'System Error, Failed sending verification answer',
						icon: "error"
					})
				} else {
					juri.close_modal_verifikasi_jatuhan();
					juri.close_modal_verifikasi_pelanggaran();
					Swal.fire({
						title: "Success",
						text: 'Answer Has Been Sent',
						icon: "success"
					})
				}
			},
			"json"
		).fail(function() {
			Swal.fire({
				title: "Error",
				text: 'System Error, Failed sending verification answer (connection lost)',
				icon: "error"
			})
		});
	},
	open_modal_verifikasi_jatuhan : function(){
		juri.modalVerifikasiJatuhan.show();
	},
	close_modal_verifikasi_jatuhan : function(){
		juri.modalVerifikasiJatuhan.hide();
	},
	open_modal_verifikasi_pelanggaran : function(){
		juri.modalVerifikasiPelanggaran.show();
	},
	close_modal_verifikasi_pelanggaran : function(){
		juri.modalVerifikasiPelanggaran.hide();
	},
	periksa_sistem_dialog: function(){
		if (juri.verifikasi_pertandingan == null || juri.verifikasi_pertandingan == undefined) {
			juri.close_modal_verifikasi_jatuhan();
			juri.close_modal_verifikasi_pelanggaran();
		} else {
			if (
				juri.verifikasi_pertandingan.jenis_verifikasi == 'jatuhan' && 
				juri.verifikasi_pertandingan.status == 'berlangsung' && 
				juri.jawaban_verifikasi_pertandingan.jawaban == null && 
				juri.modalVerifikasiJatuhan._isShown == false
			) {
				// juri belum menjawab
				juri.open_modal_verifikasi_jatuhan();
			} else if (
				juri.verifikasi_pertandingan.jenis_verifikasi == 'pelanggaran' && 
				juri.verifikasi_pertandingan.status == 'berlangsung' && 
				juri.jawaban_verifikasi_pertandingan.jawaban == null && 
				juri.modalVerifikasiPelanggaran._isShown == false
			) {
				juri.open_modal_verifikasi_pelanggaran();
			}
		}
	},
	refresh_status_pertandingan: () => {
		$.post("juri/refresh-status-pertandingan/" + juri.id_pertandingan,
			function (data, textStatus, jqXHR) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else {
					if(juri.pertandingan.format_penilaian !== data.pertandingan.format_penilaian ||
						data.data_nilai == null
					){
						window.location.reload();
					}else{
						juri.set_variable(data.data_nilai, data.pertandingan, data.pemenang, data.verifikasi_pertandingan, data.jawaban_verifikasi_pertandingan);
						juri.update_tampilan_nilai();
						juri.set_ronde();
						juri.periksa_sistem_dialog();
					}
				}
			},
			"json"
		).always(function(){
			setTimeout(() => {	
				juri.refresh_status_pertandingan();
			}, 3000);
		});
	}
}
