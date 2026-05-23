
const layar = {
	data_nilai: null,
	pertandingan: null,
	id_pertandingan: null,
	ronde_pertandingan: null,
	waktu_sekarang: 1,
	waktu_per_ronde: null,
	stopwatch: null,
	stinger_kemenangan_poin : false,
	stinger_sudut_kemenangan_poin : null,
	$stinger_nilai_kemenangan_poin : 0,
	init: function ($data_nilai, $pertandingan) {
		layar.start_animation();
		layar.set_variable($data_nilai, $pertandingan);
		layar.update_tampilan_nilai();
		layar.update_timer();
		layar.refresh_status_pertandingan();
	},
	set_variable: function ($data_nilai, $pertandingan) {
		layar.data_nilai = $data_nilai;
		layar.pertandingan = $pertandingan;
		layar.id_pertandingan = $pertandingan.id_pertandingan;
		layar.waktu_per_ronde = $pertandingan.waktu_per_ronde;

		if (layar.ronde_pertandingan !== $pertandingan.ronde_pertandingan) {
			layar.ronde_pertandingan = $pertandingan.ronde_pertandingan;
		}

		layar.stopwatch = $('.stopwatch');
		layar.ronde_sekarang = $pertandingan.ronde_pertandingan;
		layar.waktu_sekarang = $pertandingan.data_waktu[layar.ronde_sekarang][1];
	},
	start_animation: function () {
		console.log('asdaswdeasd')
		$('#competition-title').addClass('animated fadeInDown').removeClass('opacity');
		$('#header-tanding').addClass('animated fadeInDown').removeClass('opacity');
		setTimeout(() => {
			$('#nomor-partai').addClass('animated fadeInLeft').removeClass('opacity');
			$('#waktu').addClass('animated fadeInDown').removeClass('opacity');
			$('#ronde').addClass('animated fadeInRight').removeClass('opacity');
			setTimeout(() => {
				$('#box-nilai-pembantu-wasit > div').each(function (index, element) {	
					setTimeout(() => {
						$(element).addClass('animated fadeInDown').removeClass('opacity');
					}, 700 * index);
				});
			}, 700);
		}, 700);
	},
	update_tampilan_nilai: function () {

		$status_stinger = false;
		$.each(layar.data_nilai, function (key, perangkat_pertandingan) {
			$.each(perangkat_pertandingan.penilaian_tanding, function (key_sudut, nilai_sudut) {
				$.each(nilai_sudut.ronde_pertandingan, function (index_ronde, nilai_ronde) {
					if (index_ronde == layar.ronde_pertandingan) {
						$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-' + key_sudut + '-berjalan').html(nilai_ronde.ringkasan.nilai_akhir);
					}
				});
			});

			$nilai_akhir_kuning_per_pw_ronde_berlangsung = perangkat_pertandingan.penilaian_tanding.merah.ronde_pertandingan[layar.ronde_pertandingan].ringkasan.nilai_akhir;
			$nilai_akhir_biru_per_pw_ronde_berlangsung = perangkat_pertandingan.penilaian_tanding.biru.ronde_pertandingan[layar.ronde_pertandingan].ringkasan.nilai_akhir;

			// HIGHLIGHT RONDE BERJALAN PER PEMBANTU WASIT
			if ($nilai_akhir_kuning_per_pw_ronde_berlangsung > $nilai_akhir_biru_per_pw_ronde_berlangsung) {
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-biru-berjalan').removeClass('bg-gradient-180-blue text-white');
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-merah-berjalan').addClass('bg-gradient-180-yellow');
			} else if ($nilai_akhir_kuning_per_pw_ronde_berlangsung < $nilai_akhir_biru_per_pw_ronde_berlangsung) {
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-biru-berjalan').addClass('bg-gradient-180-blue text-white');
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-merah-berjalan').removeClass('bg-gradient-180-yellow');
			} else {
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-biru-berjalan').removeClass('bg-gradient-180-blue text-white');
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-merah-berjalan').removeClass('bg-gradient-180-yellow');
			}

			if($nilai_akhir_biru_per_pw_ronde_berlangsung > 200){
				layar.start_stinger_kemenangan_poin('biru', $nilai_akhir_biru_per_pw_ronde_berlangsung);
				$status_stinger = true;
			}else if($nilai_akhir_kuning_per_pw_ronde_berlangsung > 200){
				console.log($nilai_akhir_kuning_per_pw_ronde_berlangsung)
				layar.start_stinger_kemenangan_poin('merah', $nilai_akhir_kuning_per_pw_ronde_berlangsung);
				$status_stinger = true;
			}
		});
		
		if($status_stinger == false){
			layar.end_stinger_kemenangan_poin();
		}

		$('#skor_kuning').html(layar.pertandingan.skor_merah);
		$('#skor_biru').html(layar.pertandingan.skor_biru);
		$('.ronde_pertandingan').html('Ronde ' + layar.ronde_pertandingan);


		// UNTUK SCORE BESAR / MODE SEDERHANA / TAMPILAN TIDAK RINCI
		// BLOK KODE MODE SEDERHANA DENGAN SEULURH NILAI DIJUMLAHKAN DAN DIBAGI SEBANYAK JUMLAH JURI
		$nilai_akhir_kuning_ronde_berlangsung = 0;
		$nilai_akhir_biru_ronde_berlangsung = 0;
		$.each(layar.data_nilai, function (key, perangkat_pertandingan) {
			$.each(perangkat_pertandingan.penilaian_tanding, function (key_sudut, nilai_sudut) {
				if(key_sudut == 'merah'){
					$nilai_akhir_kuning_ronde_berlangsung += nilai_sudut.ronde_pertandingan[layar.ronde_pertandingan].ringkasan.nilai_akhir;
				}else{
					$nilai_akhir_biru_ronde_berlangsung += nilai_sudut.ronde_pertandingan[layar.ronde_pertandingan].ringkasan.nilai_akhir;
				}
			});
		});

		$nilai_akhir_kuning_ronde_berlangsung = $nilai_akhir_kuning_ronde_berlangsung / $(layar.data_nilai).length;
		$nilai_akhir_biru_ronde_berlangsung = $nilai_akhir_biru_ronde_berlangsung / $(layar.data_nilai).length;

		$('#nilai_akhir_biru_ronde_berlangsung').html($nilai_akhir_biru_ronde_berlangsung);
		$('#nilai_akhir_kuning_ronde_berlangsung').html($nilai_akhir_kuning_ronde_berlangsung);


		if ($('.highlight-nilai-akhir-per-ronde').length > 0) {
			if ($nilai_akhir_kuning_ronde_berlangsung > $nilai_akhir_biru_ronde_berlangsung) {
				$('#nilai_akhir_biru_ronde_berlangsung').removeClass('bg-gradient-180-blue');

				$('#nilai_akhir_kuning_ronde_berlangsung').removeClass('bg-gradient-180-gray-dark text-white');
				$('#nilai_akhir_kuning_ronde_berlangsung').addClass('bg-gradient-180-yellow text-black');
			} else if ($nilai_akhir_biru_ronde_berlangsung > $nilai_akhir_kuning_ronde_berlangsung) {
				$('#nilai_akhir_biru_ronde_berlangsung').addClass('bg-gradient-180-blue');
				$('#nilai_akhir_biru_ronde_berlangsung').removeClass('bg-gradient-180-gray-dark');

				$('#nilai_akhir_kuning_ronde_berlangsung').removeClass('bg-gradient-180-yellow text-black');
				$('#nilai_akhir_kuning_ronde_berlangsung').addClass('bg-gradient-180-gray-dark text-white');
			} else {
				$('#nilai_akhir_biru_ronde_berlangsung').removeClass('bg-gradient-180-blue');
				$('#nilai_akhir_biru_ronde_berlangsung').addClass('bg-gradient-180-gray-dark');

				$('#nilai_akhir_kuning_ronde_berlangsung').removeClass('bg-gradient-180-yellow text-black');
				$('#nilai_akhir_kuning_ronde_berlangsung').addClass('bg-gradient-180-gray-dark text-white');
			}
		}

		if ($('.highlight-nilai-ronde').length > 0) {
			// UNTUK SCORE BESAR / MODE SEDERHANA / TAMPILAN TIDAK RINCI
			if (layar.pertandingan.skor_merah > layar.pertandingan.skor_biru) {
				$('#skor_biru').removeClass('bg-gradient-180-blue');

				$('#skor_kuning').removeClass('bg-gradient-180-gray-dark text-white');
				$('#skor_kuning').addClass('bg-gradient-180-yellow text-black');
			} else if (layar.pertandingan.skor_biru > layar.pertandingan.skor_merah) {
				$('#skor_biru').addClass('bg-gradient-180-blue');
				$('#skor_biru').removeClass('bg-gradient-180-gray-dark');

				$('#skor_kuning').removeClass('bg-gradient-180-yellow text-black');
				$('#skor_kuning').addClass('bg-gradient-180-gray-dark text-white');
			} else {
				$('#skor_biru').removeClass('bg-gradient-180-blue');
				$('#skor_biru').addClass('bg-gradient-180-gray-dark');

				$('#skor_kuning').removeClass('bg-gradient-180-yellow text-black');
				$('#skor_kuning').addClass('bg-gradient-180-gray-dark text-white');
			}
		}
	},
	update_timer: function () {
		if (layar.pertandingan.status_pertandingan == 'berlangsung') {
			if(layar.stopwatch.runner('info').running == undefined){
				// baru awal masuk halaman dengan kondisi pertandingan telah berjalan
				layar.stopwatch.runner({
					countdown: true,
					startAt: layar.waktu_sekarang,
					stopAt: 0,
					milliseconds: true,
				})
				layar.stopwatch.runner('start');	
			}else{
				layar.stopwatch.runner('start');
			}
		}else if (layar.pertandingan.status_pertandingan !== 'berlangsung') {
			layar.stopwatch.runner('stop');
			if (layar.waktu_sekarang == 0) {
				layar.stopwatch.html('00:00');
			} else {
				layar.stopwatch.runner({
					countdown: true,
					startAt: layar.waktu_sekarang,
					stopAt: 0,
					milliseconds: true,
				})
			}
		}
	},
	stinger_ganti_ronde: ($ronde) => {
		if(layar.stinger_kemenangan_poin == false){
			stinger.set_text('Ronde ' + $ronde);
			stinger.start_animation(function () {
				setTimeout(() => {
					stinger.end_animation();
				}, 6000);
			});
		}
	},
	start_stinger_kemenangan_poin: ($sudut, $poin) => {
		if(layar.stinger_kemenangan_poin == false){
			layar.stinger_kemenangan_poin = true;
			stinger.set_font_size('30em');
			stinger.remove_class('bg-dark');
			if($sudut == 'merah'){
				stinger.remove_class('bg-blue');
				stinger.set_class('bg-yellow');
			}else{
				stinger.remove_class('bg-yellow');
				stinger.set_class('bg-blue');
			}
			stinger.set_text($poin);
			stinger.start_animation();
		}else{

		}
	},
	end_stinger_kemenangan_poin: () => {
		if(layar.stinger_kemenangan_poin == true){
			stinger.set_font_size('10em'); // mengembalikan ke setingan semula
			layar.stinger_kemenangan_poin = false;

			stinger.end_animation();

			setTimeout(() => {
				stinger.remove_class('bg-blue');
				stinger.remove_class('bg-yellow');
				stinger.set_class('bg-dark');
			}, 3000);
		}
	},
	refresh_status_pertandingan: () => {
		$.post("layar/refresh-status-pertandingan/" + layar.id_pertandingan,
			function (data, textStatus, jqXHR) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else {

					//Eksekusi stinger pergantian ronde secara otomatis
					if (layar.ronde_pertandingan !== null && layar.ronde_pertandingan !== data.pertandingan.ronde_pertandingan) {
						layar.stinger_ganti_ronde(data.pertandingan.ronde_pertandingan);
					}

					layar.set_variable(data.data_nilai, data.pertandingan);
					layar.update_tampilan_nilai();
					layar.update_timer();
				}
			},
			"json"
		).always(function () {
			setTimeout(() => {
				layar.refresh_status_pertandingan();
			}, 1000);
		});
	}
}
