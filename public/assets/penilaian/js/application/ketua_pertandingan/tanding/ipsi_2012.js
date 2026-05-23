
const ketua_pertandingan = {
	data_nilai: null,
	pertandingan: null,
	id_pertandingan: null,
	ronde_pertandingan: null,
	data_waktu: null,
	waktu_sekarang: 1,
	stopwatch: null,
	ringkasan_total_nilai_per_ronde : {
		"merah" : {
			"1" : 0,
			"2" : 0,
			"3" : 0
		}, // sudut : [ronde, ronde, ronde],
		"biru" :  {
			"1" : 0,
			"2" : 0,
			"3" : 0
		} // sudut : [ronde, ronde, ronde]
	},
	ringkasan_hukuman_per_ronde : {
		"merah" : {
			"1" : 0,
			"2" : 0,
			"3" : 0
		}, // sudut : [ronde, ronde, ronde],
		"biru" :  {
			"1" : 0,
			"2" : 0,
			"3" : 0
		} // sudut : [ronde, ronde, ronde]
	},
	ringkasan_nilai_akhir_per_ronde : {
		"merah" : {
			"1" : 0,
			"2" : 0,
			"3" : 0
		}, // sudut : [ronde, ronde, ronde],
		"biru" :  {
			"1" : 0,
			"2" : 0,
			"3" : 0
		} // sudut : [ronde, ronde, ronde]
	},
	ringkasan_total_nilai_semua_juri_semua_ronde : {
		"merah" : 0,
		"biru" : 0
	}, //semua juri semua ronde
	ringkasan_total_hukuman_semua_juri_semua_ronde : {
		"merah" : 0,
		"biru" : 0
	}, //semua juri semua ronde
	ringkasan_nilai_akhir_semua_juri_semua_ronde : {
		"merah" : 0,
		"biru" : 0
	}, //semua juri semua ronde
	init: function ($data_nilai, $pertandingan, $autorefresh = true) {
		ketua_pertandingan.set_variable($data_nilai, $pertandingan);
		ketua_pertandingan.update_tampilan_nilai();
		ketua_pertandingan.update_timer();
		if ($autorefresh == true) {
			ketua_pertandingan.refresh_status_pertandingan();
		}
	},
	set_variable: function ($data_nilai, $pertandingan) {		
		/**
		* Deklarasi object ringkasan nilai dan hukuman persudut perronde dan ringkasan gabungan semua ronde semua juri
		* dilakukan perhitungan secara manual karena 
		* 1. tidak dapat diakomodir oleh tabel penilaian_tanding mauapun tabel pertandingan.
		* 2. harus melalui proses penjumlahan dari semua juri.
		*/

		ketua_pertandingan.ringkasan_total_nilai_per_ronde = {
			"merah" : {
				"1" : 0,
				"2" : 0,
				"3" : 0
			}, // sudut : [ronde, ronde, ronde],
			"biru" :  {
				"1" : 0,
				"2" : 0,
				"3" : 0
			} // sudut : [ronde, ronde, ronde]
		};
		
		ketua_pertandingan.ringkasan_hukuman_per_ronde = {
			"merah" : {
				"1" : 0,
				"2" : 0,
				"3" : 0
			}, // sudut : [ronde, ronde, ronde],
			"biru" :  {
				"1" : 0,
				"2" : 0,
				"3" : 0
			} // sudut : [ronde, ronde, ronde]
		};

		ketua_pertandingan.ringkasan_nilai_akhir_per_ronde = {
			"merah" : {
				"1" : 0,
				"2" : 0,
				"3" : 0
			}, // sudut : [ronde, ronde, ronde],
			"biru" :  {
				"1" : 0,
				"2" : 0,
				"3" : 0
			} // sudut : [ronde, ronde, ronde]
		};
		
		ketua_pertandingan.ringkasan_total_nilai_semua_juri_semua_ronde = {
			"merah" : 0,
			"biru" : 0
		};
		ketua_pertandingan.ringkasan_total_hukuman_semua_juri_semua_ronde = {
			"merah" : 0,
			"biru" : 0
		};
		ketua_pertandingan.ringkasan_nilai_akhir_semua_juri_semua_ronde = {
			"merah" : 0,
			"biru" : 0
		};

		ketua_pertandingan.data_nilai = $data_nilai;
		ketua_pertandingan.pertandingan = $pertandingan;
		ketua_pertandingan.id_pertandingan = $pertandingan.id_pertandingan;
		ketua_pertandingan.ronde_pertandingan = $pertandingan.ronde_pertandingan;

		ketua_pertandingan.stopwatch = $('.stopwatch');
		ketua_pertandingan.ronde_sekarang = $pertandingan.ronde_pertandingan;
		ketua_pertandingan.waktu_sekarang = $pertandingan.data_waktu[ketua_pertandingan.ronde_sekarang][1];
	},
	update_tampilan_nilai: function () {
		$.each(ketua_pertandingan.data_nilai, function (key, perangkat_pertandingan) {
			$nilai_akhir = { merah: 0, biru: 0 };
			$.each(perangkat_pertandingan.penilaian_tanding, function (key_sudut, nilai_sudut) {

				$.each(nilai_sudut.ronde_pertandingan, function (index_ronde, nilai_ronde) {

					let jumlah_rincian_nilai = nilai_ronde['rincian'].length;
					$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-nilai-' + key_sudut + '-' + index_ronde).empty();
					$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-hukuman-' + key_sudut + '-' + index_ronde).empty();
					
					$rincian_nilai = '';
					$rincian_hukuman = '';

					if (jumlah_rincian_nilai > 0) {
						$.each(nilai_ronde['rincian'], function (index, nilai) {
							if(parseInt(nilai['nilai']) > 0){
								$rincian_nilai += nilai['nilai'];
								if (index < jumlah_rincian_nilai - 1) {
									$rincian_nilai += ', ';
								}
							}else{
								$rincian_hukuman += nilai['nilai'];
								if (index < jumlah_rincian_nilai - 1) {
									$rincian_hukuman += ', ';
								}
							}

						});
					}

					//Untuk Nilat PERBABAK
					$('.juri-' + perangkat_pertandingan.id_perangkat_pertandingan + '-nilai-' + key_sudut + '-' + index_ronde).html($rincian_nilai);
					$('.juri-' + perangkat_pertandingan.id_perangkat_pertandingan + '-hukuman-' + key_sudut + '-' + index_ronde).html($rincian_hukuman);
					$('.juri-' + perangkat_pertandingan.id_perangkat_pertandingan + '-jumlah-' + key_sudut + '-' + index_ronde).html('<span>' + nilai_ronde['ringkasan']['nilai_akhir'] + '</span>');


					//Rekap Nilai Semua Juri Dalam Satu Ronde
					ketua_pertandingan.ringkasan_total_nilai_per_ronde[key_sudut][index_ronde] += parseInt(nilai_ronde['ringkasan']['total_nilai']);
					ketua_pertandingan.ringkasan_hukuman_per_ronde[key_sudut][index_ronde] += parseInt(nilai_ronde['ringkasan']['total_hukuman']);
					ketua_pertandingan.ringkasan_nilai_akhir_per_ronde[key_sudut][index_ronde] += parseInt(nilai_ronde['ringkasan']['nilai_akhir']);

					//Rekap Nilai Semua Juri Semua Ronde
					ketua_pertandingan.ringkasan_total_nilai_semua_juri_semua_ronde[key_sudut] += parseInt(nilai_ronde['ringkasan']['total_nilai']);
					ketua_pertandingan.ringkasan_total_hukuman_semua_juri_semua_ronde[key_sudut] += parseInt(nilai_ronde['ringkasan']['total_hukuman']);
					ketua_pertandingan.ringkasan_nilai_akhir_semua_juri_semua_ronde[key_sudut] += parseInt(nilai_ronde['ringkasan']['nilai_akhir']);
				});


				//Menampilkan ringkasan total nilai perronde persudut
				$.each(ketua_pertandingan.ringkasan_total_nilai_per_ronde, function (index_sudut, nilai_per_ronde) { 
					$.each(nilai_per_ronde, function (index_ronde, nilai) { 
						$('.ronde-'+index_ronde+'-'+index_sudut+'-total-nilai').html(nilai);
					});
				});

				//Menampilkan ringkasan hukuman perronde persudut
				$.each(ketua_pertandingan.ringkasan_hukuman_per_ronde, function (index_sudut, nilai_per_ronde) { 
					$.each(nilai_per_ronde, function (index_ronde, nilai) { 
						$('.ronde-'+index_ronde+'-'+index_sudut+'-total-hukuman').html(nilai);
					});
				});

				//Menampilkan ringkasan nilai akhir perronde persudut
				$.each(ketua_pertandingan.ringkasan_nilai_akhir_per_ronde, function (index_sudut, nilai_per_ronde) { 
					$.each(nilai_per_ronde, function (index_ronde, nilai) { 
						$('.ronde-'+index_ronde+'-'+index_sudut+'-nilai-akhir').html(nilai);
					});
				});

				//Untuk Nilai Semua Babak
				$('.juri-' + perangkat_pertandingan.id_perangkat_pertandingan + '-' + key_sudut + '-total-nilai-semua-ronde').html(nilai_sudut['ringkasan']['total_nilai']);
				$('.juri-' + perangkat_pertandingan.id_perangkat_pertandingan + '-' + key_sudut + '-total-hukuman-semua-ronde').html(nilai_sudut['ringkasan']['total_hukuman']);
				$('.juri-' + perangkat_pertandingan.id_perangkat_pertandingan + '-' + key_sudut + '-nilai-akhir-semua-ronde').html(nilai_sudut['ringkasan']['nilai_akhir']);
				$nilai_akhir[key_sudut] = nilai_sudut.ringkasan.nilai_akhir;
			});
		});
		
		//SKOR, Rekapitulasi nilai semua ronde semua juri


		$.each(ketua_pertandingan.ringkasan_total_nilai_semua_juri_semua_ronde, function (index_sudut, nilai) { 
			$('.semua-ronde-'+index_sudut+'-total-nilai').html(nilai);
		});


		$.each(ketua_pertandingan.ringkasan_total_hukuman_semua_juri_semua_ronde, function (index_sudut, nilai) { 
			$('.semua-ronde-'+index_sudut+'-total-hukuman').html(nilai);
		});


		$.each(ketua_pertandingan.ringkasan_nilai_akhir_semua_juri_semua_ronde, function (index_sudut, nilai) { 
			$('.semua-ronde-'+index_sudut+'-nilai-akhir').html(nilai);
		});

		$('#skor_merah').html(ketua_pertandingan.pertandingan.skor_merah);
		$('#skor_biru').html(ketua_pertandingan.pertandingan.skor_biru);
		$('.ronde_pertandingan').html('Ronde ' + ketua_pertandingan.ronde_pertandingan);
	},
	update_timer: function () {
		ketua_pertandingan.stopwatch.timer({
			format: '%M:%S',
			action: 'start',
			countdown: true,
			duration: ketua_pertandingan.waktu_sekarang
		})
		if (ketua_pertandingan.pertandingan.status_pertandingan !== 'berlangsung') {
			ketua_pertandingan.stopwatch.timer('remove');
		}
	},

	refresh_status_pertandingan: () => {
		$.post("ketua-pertandingan/refresh-status-pertandingan/" + ketua_pertandingan.id_pertandingan,
			function (data, textStatus, jqXHR) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else {
					ketua_pertandingan.set_variable(data.data_nilai, data.pertandingan);
					ketua_pertandingan.update_tampilan_nilai();
					ketua_pertandingan.update_timer();
				}
			},
			"json"
		).always(function () {
			setTimeout(() => {
				ketua_pertandingan.refresh_status_pertandingan();
			}, 2000);
		});
	}
}
