
const monitor = {
	/**
	 * !!! script tanding tidak dirancang untuk cross format penilaian
	 * !!! script ini hanya untuk format penilaian IPSI 2012
	 */
	data_nilai: null,
	data_waktu: null,
	waktu_sekarang: null,
	pertandingan: null,
	id_pertandingan: null,
	pemenang : null,
	ronde_pertandingan: null,
	nilai_akhir_merah: 0,
	nilai_akhir_biru: 0,
	date: new Date(),
	init: function ($data_nilai, $pertandingan, $pemenang) {
		monitor.set_variable($data_nilai, $pertandingan, $pemenang);
		monitor.update_tampilan_nilai();
		monitor.refresh_status_pertandingan();
	},
	set_variable: function ($data_nilai, $pertandingan,  $pemenang) {
		monitor.data_nilai = $data_nilai;
		monitor.pertandingan = $pertandingan;
		monitor.data_waktu = $pertandingan.data_waktu;
		monitor.waktu_sekarang = $pertandingan.data_waktu[$pertandingan.ronde_pertandingan][1];
		monitor.id_pertandingan = $pertandingan.id_pertandingan;
		monitor.ronde_pertandingan = $pertandingan.ronde_pertandingan;
		monitor.pemenang = $pemenang;
	},
	update_tampilan_nilai: function () {
		/**
		 * Fungsi ini hanya dieksekusi ketika inisiasi halaman dan juri berhasil update data 
		 * ke database
		 */
		$.each(monitor.data_nilai, function (sudut, v) {
			$.each(v.ronde_pertandingan, function (ronde, nilai) {
				let jumlah_rincian_nilai = nilai['rincian'].length;
				$('.' + sudut + '-babak-' + ronde).empty();

				if (jumlah_rincian_nilai > 0) {
					$.each(nilai['rincian'], function (index, nilai) {
						$('.' + sudut + '-babak-' + ronde).append('<span>' + nilai['nilai'] + '</span>');

						if (index < jumlah_rincian_nilai - 1) {
							$('.' + sudut + '-babak-' + ronde).append('<span>,&nbsp;</span>');
						}
					});
				} else {
					$('.' + sudut + '-babak-' + ronde).html('<span>&emsp;</span>');
				}

				$('.' + sudut + '-babak-' + ronde + '-total').val(nilai['ringkasan']['nilai_akhir']);
			});

			$('#total_nilai_akhir_' + sudut).html(v.ringkasan.nilai_akhir);

			if (sudut == 'merah') {
				monitor.nilai_akhir_merah = v.ringkasan.nilai_akhir;
			} else {
				monitor.nilai_akhir_biru = v.ringkasan.nilai_akhir;
			}
		});

		if (monitor.pemenang == 'merah') {
			$('#total_nilai_akhir_biru').parent().removeClass('bg-gradient-180-blue');
			$('#total_nilai_akhir_biru').removeClass('text-white');
			$('#total_nilai_akhir_merah').parent().addClass('bg-gradient-180-red');
			$('#total_nilai_akhir_merah').addClass('text-white');
		} else if (monitor.pemenang == 'biru') {
			$('#total_nilai_akhir_merah').parent().removeClass('bg-gradient-180-red');
			$('#total_nilai_akhir_merah').removeClass('text-white');

			$('#total_nilai_akhir_biru').parent().addClass('bg-gradient-180-blue');
			$('#total_nilai_akhir_biru').addClass('text-white');
		} else {
			$('#total_nilai_akhir_merah').parent().removeClass('bg-gradient-180-red');
			$('#total_nilai_akhir_merah').removeClass('text-white');
			$('#total_nilai_akhir_biru').parent().removeClass('bg-gradient-180-blue');
			$('#total_nilai_akhir_biru').removeClass('text-white');
		}
	},
	refresh_status_pertandingan: () => {
		$.post("perangkat-pertandingan/refresh-status-pertandingan/" + monitor.id_pertandingan,
			function (data, textStatus, jqXHR) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else {
					if (monitor.pertandingan.format_penilaian !== data.pertandingan.format_penilaian ||
						data.data_nilai == null
					) {
						window.location.reload();
					}else{
						monitor.set_variable(data.data_nilai, data.pertandingan, data.pemenang);
						monitor.update_tampilan_nilai();
					}
				}
			},
			"json"
		).always(function () {
			setTimeout(() => {
				monitor.refresh_status_pertandingan();
			}, 3000);
		});
	}
}
