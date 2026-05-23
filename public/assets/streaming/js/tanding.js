
const tanding = {
	data_nilai: null,
	pertandingan: null,
	id_pertandingan: null,
	ronde_pertandingan: null,
	waktu_sekarang: 1,
	waktu_per_ronde: null,
	stopwatch: null,
	ringkasan_nilai: null,
	broadcast_graphic: {
		id_broadcast_graphic: null,
		scene: null,
		status: null,
		init: function ($broadcast_graphic) {
			tanding.broadcast_graphic.set_variable($broadcast_graphic);
			tanding.broadcast_graphic.refresh_animation();
			tanding.broadcast_graphic.refresh_status_broadcast_graphic();
		},
		refresh_animation: function () {
			if (tanding.broadcast_graphic.status == 'active') {
				tanding.broadcast_graphic.animate_in();
			} else if (tanding.broadcast_graphic.status == 'deactive') {
				tanding.broadcast_graphic.animate_out();
			} else if (tanding.broadcast_graphic.status == 'timed-3s') {
				tanding.broadcast_graphic.animate_in();
				setTimeout(() => {
					tanding.broadcast_graphic.deactive();
				}, 10000); // 6000 + 3000
			} else if (tanding.broadcast_graphic.status == 'timed-5s') {
				tanding.broadcast_graphic.animate_in();
				setTimeout(() => {
					tanding.broadcast_graphic.deactive();
				}, 13000); // 6000 + 3000
			} else if (tanding.broadcast_graphic.status == 'refresh') {
				tanding.broadcast_graphic.animate_out();
				setTimeout(() => {
					tanding.broadcast_graphic.deactive(); // setiap tombol refresh di operator ditekan, maka graphic akan mati / deactive
					setTimeout(() => {	
						window.location.reload();
					}, 3000);
				}, 4000);
			}
		},
		set_variable: function ($broadcast_graphic) {
			tanding.broadcast_graphic.id_broadcast_graphic = $broadcast_graphic.id_broadcast_graphic;
			tanding.broadcast_graphic.scene = $broadcast_graphic.scene;
			tanding.broadcast_graphic.status = $broadcast_graphic.status;
			tanding.broadcast_graphic.autorefresh = parseInt($broadcast_graphic.autorefresh);
		},
		change_duration: function ($type = "forward") {
			$('.'+$type+'.animated').each(function (i, e) {
				if ($(e).hasClass('delay-1s')) {
					$(e).removeClass('delay-1s').addClass('delay-5s');
				} else if ($(e).hasClass('delay-2s')) {
					$(e).removeClass('delay-2s').addClass('delay-4s');
				} else if ($(e).hasClass('delay-3s')) {
					$(e).removeClass('delay-3s').addClass('delay-3s');
				} else if ($(e).hasClass('delay-4s')) {
					$(e).removeClass('delay-4s').addClass('delay-2s');
				} else if ($(e).hasClass('delay-5s')) {
					$(e).removeClass('delay-5s').addClass('delay-1s');
				} else if ($(e).hasClass('delay-6s')) {
					$(e).removeClass('delay-6s');
				} else {
					$(e).removeClass('delay-1s').addClass('delay-6s');
				}
			})
		},
		animate_in: function () {
			tanding.broadcast_graphic.change_duration('forward');
			$('.slide-in-down').addClass('backward animated slideInDown').removeClass('forward slideOutUp opacity');
			$('.slide-in-up').addClass('backward animated slideInUp').removeClass('forward slideOutDown opacity');
			$('.slide-in-right').addClass('backward animated slideInRight').removeClass('forward slideOutRight opacity');
			$('.slide-in-left').addClass('backward animated slideInLeft').removeClass('forward slideOutLeft opacity');
		},
		animate_out: function () {
			if ($('.opacity').length == 0) {
				tanding.broadcast_graphic.change_duration('backward');
				// Apabila pada awal load halaman sudah posisi deactive, maka tidak perlu menjalankan semua animasi
				$('.slide-in-down').addClass('forward animated slideOutUp').removeClass('backward slideInDown opacity');
				$('.slide-in-up').addClass('forward animated slideOutDown').removeClass('backward slideInUp opacity')
				$('.slide-in-right').addClass('forward animated slideOutRight').removeClass('backward slideInLeft opacity');
				$('.slide-in-left').addClass('forward animated slideOutLeft').removeClass('backward slideInright opacity');
			}
		},
		refresh_status_broadcast_graphic: () => {
			$.post("broadcast-graphic/refresh-status-broadcast-graphic/" + tanding.broadcast_graphic.id_broadcast_graphic,
				function (data, textStatus, jqXHR) {
					tanding.broadcast_graphic.set_variable(data);
					tanding.broadcast_graphic.refresh_animation();
				},
				"json"
			).always(function () {
				setTimeout(() => {
					tanding.broadcast_graphic.refresh_status_broadcast_graphic();
				}, 2000);
			});
		},
		active: function(callback){
			$.post("broadcast-graphic/update/" + tanding.broadcast_graphic.id_broadcast_graphic,
				{ 'status': 'active' }
				,
				function (data, textStatus, jqXHR) {
					callback();
				},
				"json"
			);
		},
		deactive: function(){
			$.post("broadcast-graphic/update/" + tanding.broadcast_graphic.id_broadcast_graphic,
				{ 'status': 'deactive' }
				,
				function (data, textStatus, jqXHR) {

				},
				"json"
			);
		}
	},
	sistem_dialog: {
		skor_biru_verifikasi: 0, // digunakan untuk  melacak jawaban verifikasi
		skor_merah_verifikasi: 0, // digunakan untuk  melacak jawaban verifikasi
		sistem_dialog_terdahulu: null, // Digunakan untuk melacak jawaban verifikasi
		periksa_sistem_dialog: function () {
			if (tanding.pertandingan.sistem_dialog === 'verifikasi jatuhan') {
				tanding.sistem_dialog.open_modal_verifikasi_jatuhan();
				// Highlight setiap kali refresh
				tanding.sistem_dialog.highlight_jawaban_verifikasi_jatuhan();
			} else if (tanding.pertandingan.sistem_dialog === 'verifikasi pelanggaran') {
				if (tanding.sistem_dialog.modalVerifikasiPelanggaran._isShown == false) {
					tanding.sistem_dialog.open_modal_verifikasi_pelanggaran();
				}
				// Highlight setiap kali refresh
				tanding.sistem_dialog.highlight_jawaban_verifikasi_pelanggaran();
			} else {
				tanding.sistem_dialog.close_modal_verifikasi_pelanggaran();
				tanding.sistem_dialog.close_modal_verifikasi_jatuhan();
				if (tanding.sistem_dialog.sistem_dialog_terdahhulu == 'verifikasi jatuhan' && tanding.pertandingan.sistem_dialog == null && tanding.pertandingan.skor_biru - tanding.sistem_dialog.skor_biru_verifikasi == 3) {
					// Valid Drop Sudut Biru
					console.log('jatuhan biru sah')
					tanding.sistem_dialog.open_modal_hasil_verifikasi('blue', 'Valid Drop!');
				} else if (tanding.sistem_dialog.sistem_dialog_terdahhulu == 'verifikasi jatuhan'  && tanding.pertandingan.sistem_dialog == null && tanding.pertandingan.skor_merah - tanding.sistem_dialog.skor_merah_verifikasi == 3) {
					// Valid Drop Sudut Biru
					console.log('jatuhan merah sah')
					tanding.sistem_dialog.open_modal_hasil_verifikasi('red', 'Valid Drop!');
				} else if (
					tanding.sistem_dialog.sistem_dialog_terdahhulu == 'verifikasi jatuhan' && 
					tanding.pertandingan.sistem_dialog == null &&
					tanding.pertandingan.skor_merah == tanding.sistem_dialog.skor_merah_verifikasi && 
					tanding.pertandingan.skor_biru == tanding.sistem_dialog.skor_biru_verifikasi
				) {

					tanding.sistem_dialog.open_modal_hasil_verifikasi('warning', 'Invalid Drop!');

				} else if (tanding.sistem_dialog.sistem_dialog_terdahhulu == 'verifikasi pelanggaran' && tanding.pertandingan.sistem_dialog == null && tanding.pertandingan.skor_biru < tanding.sistem_dialog.skor_biru_verifikasi) {
					// Pelanggaran Sah Sudut Biru
					console.log('pelanggaran biru sah')
					tanding.sistem_dialog.open_modal_hasil_verifikasi('blue', 'Valid Violation!');
				} else if (tanding.sistem_dialog.sistem_dialog_terdahhulu == 'verifikasi pelanggaran' && tanding.pertandingan.sistem_dialog == null && tanding.pertandingan.skor_merah < tanding.sistem_dialog.skor_merah_verifikasi) {
					// Pelanggaran Sah Sudut Biru
					console.log('pelanggaran merah sah')
					tanding.sistem_dialog.open_modal_hasil_verifikasi('red', 'Valid Violation!');
				} else if (
					tanding.sistem_dialog.sistem_dialog_terdahhulu == 'verifikasi pelanggaran' && 
					tanding.pertandingan.sistem_dialog == null &&
					tanding.pertandingan.skor_merah == tanding.sistem_dialog.skor_merah_verifikasi && 
					tanding.pertandingan.skor_biru == tanding.sistem_dialog.skor_biru_verifikasi
				) {
					tanding.sistem_dialog.open_modal_hasil_verifikasi('warning', 'Invalid Violation!');
				}
			}
			tanding.sistem_dialog.skor_biru_verifikasi = tanding.pertandingan.skor_biru;
			tanding.sistem_dialog.skor_merah_verifikasi = tanding.pertandingan.skor_merah;
			tanding.sistem_dialog.sistem_dialog_terdahhulu = tanding.pertandingan.sistem_dialog;
		},
		setModalSistemDialog:  function(){
			if(document.getElementById('modalVerifikasiJatuhan') !== null){
				tanding.sistem_dialog.modalVerifikasiJatuhan = new bootstrap.Modal(document.getElementById('modalVerifikasiJatuhan'), {
					keyboard: false
				})
			}else{
				tanding.sistem_dialog.modalVerifikasiJatuhan = null;
			}

			if(document.getElementById('modalVerifikasiPelanggaran') !== null){
				tanding.sistem_dialog.modalVerifikasiPelanggaran = new bootstrap.Modal(document.getElementById('modalVerifikasiPelanggaran'), {
					keyboard: false
				})
			}else{
				tanding.sistem_dialog.modalVerifikasiPelanggaran = null;
			}

			if(document.getElementById('modalHasilVerifikasi') !== null){
				tanding.sistem_dialog.modalHasilVerifikasi = new bootstrap.Modal(document.getElementById('modalHasilVerifikasi'), {
					keyboard: false
				})
			}else{
				tanding.sistem_dialog.modalHasilVerifikasi = null;
			}
		},
		open_modal_verifikasi_jatuhan: function () {
		
			if (tanding.sistem_dialog.modalVerifikasiJatuhan !== null && 
				tanding.sistem_dialog.modalVerifikasiJatuhan._isShown === false) {
				$modal = $(tanding.sistem_dialog.modalVerifikasiJatuhan._element);
				$.each($modal.find('div.card'), function (i, v) {
					$(v).find('.card-body > p').html('Waiting Response');
				});
				tanding.sistem_dialog.modalVerifikasiJatuhan.show();
				tanding.sistem_dialog.highlight_jawaban_verifikasi_jatuhan();
			}
		},
		close_modal_verifikasi_jatuhan: function () {
			tanding.sistem_dialog.modalVerifikasiJatuhan.hide();
		},
		highlight_jawaban_verifikasi_jatuhan: function () {
			$modal = $(tanding.sistem_dialog.modalVerifikasiJatuhan._element);
		
			$.each(tanding.data_nilai.juri, function (i, v) {
				$card = $modal.find('.card-jawaban-sistem-dialog-' + (i + 1));
				if (v.jawaban_sistem_dialog == 'merah') {
					$card.addClass('bg-gradient-180-red').removeClass('bg-dark');
					$card.find('.card-body > p').html('MERAH');
				} else if (v.jawaban_sistem_dialog == 'biru') {
					$card.addClass('bg-gradient-180-blue').removeClass('bg-dark');
					$card.find('.card-body > p').html('BIRU');
				} else if (v.jawaban_sistem_dialog == 'tidak sah') {
					$card.addClass('bg-gradient-180-warning').removeClass('bg-dark');
					$card.find('.card-body > p').html('TIDAK SAH');
				}
			});
		},
		open_modal_verifikasi_pelanggaran: function () {
			if(tanding.sistem_dialog.modalVerifikasiPelanggaran !== null){
				//Diawal selalu reset warna dan tulisan kartu jawababn sistem dialog
				$modal = $(tanding.sistem_dialog.modalVerifikasiPelanggaran._element);
				$.each($modal.find('div.card'), function (i, v) {
					$(v).find('.card-body > p').html('Waiting Response');
					$(v).addClass('bg-dark').removeClass('bg-red bg-blue');
				});
			
				tanding.sistem_dialog.modalVerifikasiPelanggaran.show();
				tanding.sistem_dialog.highlight_jawaban_verifikasi_pelanggaran();
			}
		},
		close_modal_verifikasi_pelanggaran: function () {
			if(tanding.sistem_dialog.modalVerifikasiPelanggaran !== null){
				tanding.sistem_dialog.modalVerifikasiPelanggaran.hide();
			}
		},
		highlight_jawaban_verifikasi_pelanggaran: function () {
			if(tanding.sistem_dialog.modalVerifikasiPelanggaran !== null){
				$modal = $(tanding.sistem_dialog.modalVerifikasiPelanggaran._element);
			
				$.each(tanding.data_nilai.juri, function (i, v) {
					$card = $modal.find('.card-jawaban-sistem-dialog-' + (i + 1));
					if (v.jawaban_sistem_dialog == 'merah') {
						$card.addClass('bg-gradient-180-red').removeClass('bg-dark');
						$card.find('.card-body > p').html('MERAH');
					} else if (v.jawaban_sistem_dialog == 'biru') {
						$card.addClass('bg-gradient-180-blue').removeClass('bg-dark');
						$card.find('.card-body > p').html('BIRU');
					} else if (v.jawaban_sistem_dialog == 'tidak sah') {
						$card.addClass('bg-gradient-180-warning').removeClass('bg-dark');
						$card.find('.card-body > p').html('TIDAK SAH');
					}
				});
			}
		
		},
		open_modal_hasil_verifikasi: function ($background = null, $text = null) {
			if (tanding.sistem_dialog.modalHasilVerifikasi !== null &&
				tanding.sistem_dialog.modalHasilVerifikasi._isShown === false) {
				$modal = $(tanding.sistem_dialog.modalHasilVerifikasi._element);
				if ($background != null) {
					$modal.find('.modal-body').removeClass().addClass('modal-body bg-' + $background);
				}
		
				if ($text != null) {
					$modal.find('#textModalHasilVerifikasi').html($text);
				}
				tanding.sistem_dialog.modalHasilVerifikasi.show();
		
				setTimeout(() => {
					tanding.sistem_dialog.modalHasilVerifikasi.hide();
				}, 4000);
			}
		},
		close_modal_verifikasi_jatuhan: function () {
			if(tanding.sistem_dialog.modalVerifikasiJatuhan !== null){
				tanding.sistem_dialog.modalVerifikasiJatuhan.hide();
			}
		},
	},
	init: function ($data_nilai, $pertandingan) {
		tanding.set_variable($data_nilai, $pertandingan);
		tanding.sistem_dialog.setModalSistemDialog();
		tanding.update_tampilan_nilai();
		tanding.update_timer();
		tanding.refresh_status_pertandingan();
	},
	set_variable: function ($data_nilai, $pertandingan) {
		tanding.data_nilai = $data_nilai;
		tanding.pertandingan = $pertandingan;
		tanding.id_gelanggang = $pertandingan.id_gelanggang;
		
		if ($pertandingan.ringkasan_nilai !== undefined && $pertandingan.ringkasan_nilai !== '') {
			tanding.ringkasan_nilai = JSON.parse($pertandingan.ringkasan_nilai);
		}
		tanding.id_pertandingan = $pertandingan.id_pertandingan;
		tanding.waktu_per_ronde = $pertandingan.waktu_per_ronde;

		if (tanding.ronde_pertandingan !== $pertandingan.ronde_pertandingan) {
			tanding.ronde_pertandingan = $pertandingan.ronde_pertandingan;
		}

		tanding.stopwatch = $('.stopwatch');
		tanding.ronde_sekarang = $pertandingan.ronde_pertandingan;
		tanding.waktu_sekarang = $pertandingan.data_waktu[tanding.ronde_sekarang][1];
	},
	update_tampilan_nilai: function () {
		$.each(tanding.data_nilai.juri, function (key, perangkat_pertandingan) {

			$.each(perangkat_pertandingan.penilaian_tanding, function (key_sudut, nilai_sudut) {
				$element = $('.juri-' + perangkat_pertandingan.id_perangkat_pertandingan + '-total');

				$nilai_lama = $element.data('totalNilaiAkhir' + key_sudut);
				$hukuman_lama = $element.data('totalHukuman' + key_sudut);

				$nilai_baru = nilai_sudut.ringkasan.nilai_akhir;
				$hukuman_baru = nilai_sudut.ringkasan.total_hukuman;
				if (
					$nilai_lama !== undefined && $nilai_baru - $nilai_lama > 0 &&  // Murni dari penambahan poin, bukan pembatalan hukuman
					$hukuman_lama !== undefined && $hukuman_baru == $hukuman_lama ||
					$hukuman_lama !== undefined && $hukuman_baru - $hukuman_lama < 0 // -10 - (-5) < 0
				) {
					if (key_sudut == 'merah') {
						$element.removeClass('bg-dark');
						$element.addClass('bg-gradient-180-red');
					} else {
						$element.removeClass('bg-dark');
						$element.addClass('bg-gradient-180-blue');
					}

					if ($nilai_baru - $nilai_lama == 1) {
						$element.find('.icon-pukulan').removeClass('d-none');
					} else if ($nilai_baru - $nilai_lama == 2) {
						$element.find('.icon-tendangan').removeClass('d-none');
					} else if ($nilai_baru - $nilai_lama == 3) {
						$element.find('.icon-jatuhan').removeClass('d-none');
					} else {
						// cek apakah hukuman berubah
						if ($hukuman_baru - $hukuman_lama < 0) {
							$element.find('.icon-hukuman').removeClass('d-none');
						}
					}

					tanding.reset_highlight_juri($element, 2000);
				}

				$element.data('totalNilaiAkhir' + key_sudut, nilai_sudut.ringkasan.nilai_akhir);
				$element.data('totalHukuman' + key_sudut, nilai_sudut.ringkasan.total_hukuman);
			});
		});

		$('.skor_merah').html(tanding.pertandingan.skor_merah);
		$('.skor_biru').html(tanding.pertandingan.skor_biru);
		$('.ronde_pertandingan').html(tanding.ronde_pertandingan);
		tanding.highlight_hukuman();
	},
	highlight_hukuman: function () {
		/**
		 * !!! Hanya mengambil data nilai dari 1 juri karena dalam peraturan 2022, jatuhan dan hukuman diinput serentak oleh dewan
		 * Urutan pelacakan :
		 * 1. Melacak nilai -10, di seluruh ronde
		 * 2. Melacak nilai -5 di seluruh ronde
		 * 3. Melacak nilai -2 di ronde berlangsung apabila -10 dan -5 tidak ditemukan
		 * 4. Melacak nilai -1 di ronde berlangsung apabila -10 dan -5 tidak ditemukan
		 * 5. Melacak binaan apabila hukuman tidak ditemukan
		 */

		$highlight_peringatan = {
			"merah": 0, // tidak ada peringatan
			"biru": 0 // tidaka da peringatan
		};

		$highlight_teguran = {
			"merah": 0, // tidak ada teguran
			"biru": 0 // tidaka da teguran
		};

		$highlight_binaan = {
			"merah": 0, // tidak ada binaan
			"biru": 0 // tidaka da binaan
		};

		// Melacak nilai -10 dan -5 di seluruh ronde
		$.each(tanding.data_nilai.juri[0].penilaian_tanding, function (sudut, nilai_per_sudut) {
			$.each(nilai_per_sudut.ronde_pertandingan, function (index_ronde, nilai_per_ronde) {
				$rincian_nilai_per_ronde = nilai_per_ronde.rincian;
				$.each($rincian_nilai_per_ronde, function (index_entry_nilai, entry_nilai) {
					$bobot_nilai = parseInt(entry_nilai.nilai);
					if (
						$bobot_nilai == -10 ||
						$bobot_nilai == -5 && $highlight_peringatan[sudut] > $bobot_nilai // mencegah -5 menggantikan -10 yg sudah disimpan
					) {
						$highlight_peringatan[sudut] = $bobot_nilai;
					}
				});
			});
		});

		// Melacak nilai -1 dan -2 di ronde berjalan apabila -5 dan -10 tidak ditemukan
		$.each(tanding.data_nilai.juri[0].penilaian_tanding, function (sudut, nilai_per_sudut) {
			$.each(nilai_per_sudut.ronde_pertandingan[tanding.ronde_pertandingan].rincian, function (index_entry_nilai, entry_nilai) {
				$bobot_nilai = parseInt(entry_nilai.nilai);
				if (
					$bobot_nilai == -2 ||
					$bobot_nilai == -1 && $highlight_teguran[sudut] > $bobot_nilai // mencegah -1 menggantikan -2 yg sudah disimpan
				) {
					$highlight_teguran[sudut] = $bobot_nilai;
				}
			});
		});

		$.each(tanding.data_nilai.juri[0].penilaian_tanding, function (sudut, nilai_per_sudut) {
			if ($highlight_binaan[sudut] == 0) {
				if (nilai_per_sudut.ronde_pertandingan[tanding.ronde_pertandingan].catatan.binaan == 1) {
					$highlight_binaan[sudut] = 'I';
				} else if (nilai_per_sudut.ronde_pertandingan[tanding.ronde_pertandingan].catatan.binaan == 2) {
					$highlight_binaan[sudut] = 'II';
				}
			}
		});

		// Menyalakan highlight indikator
		$.each($highlight_binaan, function (sudut, bobot_nilai) {
			$('.indikator-pelanggaran-' + sudut + ' .indikator-binaan').css('background-color', '#222222');
			switch (bobot_nilai) {
				case 'I':
					$('.indikator-pelanggaran-' + sudut + ' .indikator-binaan-1').css('background-color', '#ab8f09');
					break;
				case 'II':
					$('.indikator-pelanggaran-' + sudut + ' .indikator-binaan-1').css('background-color', '#ab8f09');
					$('.indikator-pelanggaran-' + sudut + ' .indikator-binaan-2').css('background-color', '#ab8f09');
					break;
			}
		});

		// Menyalakan highlight indikator Teguran
		$.each($highlight_teguran, function (sudut, bobot_nilai) {
			$('.indikator-pelanggaran-' + sudut + ' .indikator-teguran').css('background-color', '#222222');
			switch (bobot_nilai) {
				case -1:
					$('.indikator-pelanggaran-' + sudut + ' .indikator-teguran-1').css('background-color', '#b14511');
					break;
				case -2:
					$('.indikator-pelanggaran-' + sudut + ' .indikator-teguran-1').css('background-color', '#b14511');
					$('.indikator-pelanggaran-' + sudut + ' .indikator-teguran-2').css('background-color', '#b14511');
					break;
			}
		});


		// Menyalakan highlight indikator peringatan
		$.each($highlight_peringatan, function (sudut, bobot_nilai) {
			$('.indikator-pelanggaran-' + sudut + ' .indikator-peringatan').css('background-color', '#222222');
			switch (bobot_nilai) {
				case -5:
					$('.indikator-pelanggaran-' + sudut + ' .indikator-peringatan-1').css('background-color', '#6d0106');
					break;
				case -10:
					$('.indikator-pelanggaran-' + sudut + ' .indikator-peringatan-1').css('background-color', '#6d0106');
					$('.indikator-pelanggaran-' + sudut + ' .indikator-peringatan-2').css('background-color', '#6d0106');
					break;
			}
		});

	},
	reset_highlight_juri: function ($element, $timeout = 3000) {
		if ($element.data('highlightTimeout')) {
			clearTimeout($element.data('highlightTimeout'));
		}
		const timeoutId = setTimeout(() => {
			$element.addClass('bg-white');
			$element.removeClass('bg-warning');
			$element.removeData('highlightTimeout');
		}, $timeout);
		$element.data('highlightTimeout', timeoutId);
	},
	update_timer: function () {

		if (tanding.pertandingan.status_pertandingan == 'berlangsung') {
			if(tanding.stopwatch.runner('info').running == undefined){
				// baru awal masuk halaman dengan kondisi pertandingan telah berjalan
				tanding.stopwatch.runner({
					countdown: true,
					startAt: tanding.waktu_sekarang,
					stopAt: 0,
					milliseconds: true,
				})
				tanding.stopwatch.runner('start');	
			}else{
				tanding.stopwatch.runner('start');
			}
		}else if (tanding.pertandingan.status_pertandingan !== 'berlangsung') {
			tanding.stopwatch.runner('stop');
			if (tanding.waktu_sekarang == 0) {
				tanding.stopwatch.html('00:00');
			} else {
				tanding.stopwatch.runner({
					countdown: true,
					startAt: tanding.waktu_sekarang,
					stopAt: 0,
					milliseconds: true,
				})
			}
		}
	},
	refresh_status_pertandingan: () => {
		// DIGUNAKAN UNTUK MENGAMBIL DATA NILAI
		$.post("broadcast-graphic/refresh-status-pertandingan/" +tanding.id_gelanggang+'/'+tanding.id_pertandingan,
			function (data, textStatus, jqXHR) {
				if (data.status === true && data.reload === true) {
					if(tanding.broadcast_graphic.autorefresh == 1){
						tanding.broadcast_graphic.deactive();
						setTimeout(() => {
							tanding.broadcast_graphic.active(
								function(){
									window.location.reload();
								}
							);
						}, 9000);
					}
				} else {
					tanding.set_variable(data.data_nilai, data.pertandingan);
					tanding.update_tampilan_nilai();
					tanding.update_timer();
					setTimeout(() => {
						tanding.refresh_status_pertandingan();
					}, 1000);
				}
			},
			"json"
		);
		tanding.sistem_dialog.periksa_sistem_dialog();
	},
	refresh_status_pertandingan_standby: ($id_gelanggang) => {
		$.post("broadcast-graphic/refresh-status-pertandingan-standby/" + $id_gelanggang,
			function (data, textStatus, jqXHR) {
				if (data.reload === true) {
					location.reload();
				} else {
					setTimeout(() => {
						tanding.refresh_status_pertandingan_standby($id_gelanggang);
					}, 2000);
				}
			},
			"json"
		);
	},
}
