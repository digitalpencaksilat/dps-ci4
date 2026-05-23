	const layar = {
		penampilan_seni_berlangsung: null,
		stopwatch: null,
		data_nilai : null,
		init: function ($penampilan_seni_berlangsung, $data_nilai) {
			if (typeof io !== 'undefined') {
				layar.socket = io(typeof SOCKET_URL !== 'undefined' ? SOCKET_URL : 'http://localhost:3000');
			}

			layar.set_variable($penampilan_seni_berlangsung, $data_nilai);
			layar.stopwatch = $('.waktu_tampil');
			
			if (layar.socket) {
				layar.socket.emit('JOIN_ROOM', layar.penampilan_seni_berlangsung.id_penampilan_seni);
				
				layar.socket.on('UPDATE_WAKTU', function(data) {
					console.log('⚡ Layar Seni menerima UPDATE_WAKTU!', data.action, ' detik: ', data.waktu);
					layar.penampilan_seni_berlangsung.waktu_tampil = data.waktu;
					layar.penampilan_seni_berlangsung.status_penampilan = data.action;
					
					// Identical to original update_timer logic:
					layar.stopwatch.timer({
						format: "%M:%S",
						action: "start",
						seconds: data.waktu
					});
					
					// If it's supposed to be stopped/paused, remove the interval instance (leaves text frozen)
					if (data.action !== 'sedang_tampil') {
						layar.stopwatch.timer("remove");
						console.log('Waktu di-freeze (berhenti) di detik ke-', data.waktu);
					}
				});
			}
			layar.update_tampilan_nilai();
			layar.update_timer();
			layar.refresh_status_seni();
		},
		set_variable : function ($penampilan_seni_berlangsung, $data_nilai) { 
			layar.penampilan_seni_berlangsung = $penampilan_seni_berlangsung;
			layar.data_nilai = $data_nilai;
		},
		update_tampilan_nilai: function () {
			$('.nilai_akhir').html(layar.penampilan_seni_berlangsung.nilai_akhir);
			console.log(layar.penampilan_seni_berlangsung.nilai_akhir);
			$.each(layar.data_nilai[layar.penampilan_seni_berlangsung.id_penampilan_seni], function (index_juri, penilaian_juri) {
				$penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
				$.each($penilaian.unsur_nilai, function (jenis_unsur_nilai, value_unsur_nilai) {
					$('.' + jenis_unsur_nilai + '_' + penilaian_juri.id_perangkat_pertandingan).html(value_unsur_nilai.nilai_diperoleh)
				});

				$('.nilai_akhir' + '_' + penilaian_juri.id_perangkat_pertandingan).html($penilaian.ringkasan.nilai_akhir)
				$('.total_hukuman' + '_' + penilaian_juri.id_perangkat_pertandingan).html($penilaian.ringkasan.total_hukuman)

				if (layar.data_nilai[layar.penampilan_seni_berlangsung.id_penampilan_seni].length == 5) {
					if (penilaian_juri.terpilih == 1) {
						$('.juri_' + penilaian_juri.id_perangkat_pertandingan).addClass('bg-gradient-180-primary text-white').removeClass('text-decoration-line-through');
					} else {
						$('.juri_' + penilaian_juri.id_perangkat_pertandingan).addClass('text-decoration-line-through').removeClass('bg-gradient-180-primary text-white');
					}
				}
			});
		},
		update_timer: function () {
			layar.stopwatch.timer({
				format: '%M:%S',
				action: 'start',
				seconds: layar.penampilan_seni_berlangsung.waktu_tampil
			})
			if (layar.penampilan_seni_berlangsung.status_penampilan !== 'sedang_tampil') {
				layar.stopwatch.timer('remove');
			}
		},
		refresh_status_seni: () => {
			$.post("layar/refresh-status-seni/" + layar.penampilan_seni_berlangsung.id_penampilan_seni,
				function (data, textStatus, jqXHR) {
					if (
						data.status === true && data.reload === true ||
						layar.penampilan_seni_berlangsung.format_penilaian !== data.penampilan_seni_berlangsung.format_penilaian
					) {
						location.reload();
					} else if (data.status === false && typeof data.penampilan_seni_berlangsung !== 'undefined') {
						layar.set_variable(data.penampilan_seni_berlangsung, data.data_nilai);
						layar.update_tampilan_nilai();
						if (typeof layar.socket === 'undefined' || !layar.socket.connected) {
							layar.update_timer();
						}
					}
				},
				"json"
			).always(function () {
				setTimeout(() => {
					layar.refresh_status_seni();
				}, 1000);
			});
		},
	}
