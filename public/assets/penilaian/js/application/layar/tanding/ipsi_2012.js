
const layar = {
	data_nilai: null,
	pertandingan: null,
	id_pertandingan: null,
	ronde_pertandingan: null,
	waktu_sekarang: 1,
	waktu_per_ronde: null,
	stopwatch: null,
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

		if(layar.ronde_pertandingan !== $pertandingan.ronde_pertandingan){
			layar.ronde_pertandingan = $pertandingan.ronde_pertandingan;
		}

		layar.stopwatch = $('.stopwatch');
		layar.ronde_sekarang = $pertandingan.ronde_pertandingan;
		layar.waktu_sekarang = $pertandingan.data_waktu[layar.ronde_sekarang][1];
	},
	start_animation: function(){
		$('#header-tanding').addClass('animated fadeInDown').removeClass('opacity');
		setTimeout(() => {
			$('#nomor-partai').addClass('animated fadeInLeft').removeClass('opacity');
			$('#waktu').addClass('animated fadeInDown').removeClass('opacity');
			$('#ronde').addClass('animated fadeInRight').removeClass('opacity');
			setTimeout(() => {
				$('#tabel-nilai-layar tr').each(function (index, element) {
					setTimeout(() => {	
						$(element).addClass('animated fadeInDown').removeClass('opacity');
					}, 700 * index);
				});
			}, 700);
		}, 700);
	},
	update_tampilan_nilai: function () {
		$.each(layar.data_nilai, function (key, perangkat_pertandingan) {
			
			$.each(perangkat_pertandingan.penilaian_tanding, function (key_sudut, nilai_sudut) {
				$.each(nilai_sudut.ronde_pertandingan, function (index_ronde, nilai_ronde) {
					$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-' + key_sudut + '-' + index_ronde).html(nilai_ronde.ringkasan.nilai_akhir);
				});
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-' + key_sudut + '-total').html(nilai_sudut.ringkasan.nilai_akhir);
			});
			if (perangkat_pertandingan.pemenang == 'merah') {
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-biru-total').removeClass('bg-gradient-180-blue text-white');
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-merah-total').addClass('bg-gradient-180-red text-white');
			} else if (perangkat_pertandingan.pemenang == 'biru') {
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-biru-total').addClass('bg-gradient-180-blue text-white');
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-merah-total').removeClass('bg-gradient-180-red text-white');
			} else {
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-biru-total').removeClass('bg-gradient-180-blue text-white');
				$('.' + perangkat_pertandingan.id_perangkat_pertandingan + '-merah-total').removeClass('bg-gradient-180-red text-white');
			}
		});

		$('#skor_merah').html(layar.pertandingan.skor_merah);
		$('#skor_biru').html(layar.pertandingan.skor_biru);
		$('.ronde_pertandingan').html('Ronde ' + layar.ronde_pertandingan);

		if ($('.big-score').length > 0) {
			// UNTUK SCORE BESAR / MODE SEDERHANA / TAMPILAN TIDAK RINCI
			if (layar.pertandingan.skor_merah > layar.pertandingan.skor_biru) {
				$('#skor_biru').removeClass('bg-gradient-180-blue text-white');
				$('#skor_merah').addClass('bg-gradient-180-red text-white');
			} else if (layar.pertandingan.skor_merah < layar.pertandingan.skor_biru) {
				$('#skor_biru').addClass('bg-gradient-180-blue text-white');
				$('#skor_merah').removeClass('bg-gradient-180-red text-white');
			} else {
				$('#skor_biru').removeClass('bg-gradient-180-blue text-white');
				$('#skor_merah').removeClass('bg-gradient-180-red text-white');
			}
		}
	},
	update_timer: function () {
		if(layar.waktu_sekarang == 0){
			layar.stopwatch.html('00:00');
		}else{
			layar.stopwatch.timer({
				format: '%M:%S',
				action: 'start',
				countdown: true,
				duration: layar.waktu_sekarang,
				callback: function () {
					layar.stopwatch.timer('pause');
				}
			})

		}
		if (layar.pertandingan.status_pertandingan !== 'berlangsung') {
			layar.stopwatch.timer('remove');
		}
	},
	refresh_status_pertandingan: () => {
		$.post("layar/refresh-status-pertandingan/" + layar.id_pertandingan,
			function (data, textStatus, jqXHR) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else {
					//Eksekusi stinger pergantian ronde secara otomatis
					if(layar.ronde_pertandingan !== null && layar.ronde_pertandingan !== data.pertandingan.ronde_pertandingan){
						if((typeof stinger !== 'undefined')){
							stinger.set_text('Ronde '+data.pertandingan.ronde_pertandingan);
							stinger.start_animation(function(){
								setTimeout(() => {
									stinger.end_animation();
								}, 6000);
							});
						}
					}

					layar.set_variable(data.data_nilai, data.pertandingan);
					layar.update_tampilan_nilai();
					layar.update_timer();
				}
			},
			"json"
		).always(function(){
			setTimeout(() => {
				layar.refresh_status_pertandingan();
			}, 1000);
		});
	}
}
