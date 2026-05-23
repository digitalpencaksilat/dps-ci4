const sekretaris_pertandingan = {
	stopwatch: null,
	id_pertandingan: null,
	data_nilai: null,
	data_waktu: null, //
	pertandingan: null,
	skor_merah: 0,
	skor_biru: 0,
	ronde_sekarang: null, //
	waktu_istirahat: null,
	waktu_sekarang: null,
	jumlah_ronde: null, //
	status_pertandingan: null,
	audio: {
		pakai_gong: true,
		beep_alarm : false,
		beep_alarm_audio: new Audio("assets/penilaian/audio/alarm.mp3"),
		gong_audio: new Audio("assets/penilaian/audio/gong/gong_1.mp3"), //Default gong sound
		init: function ($config) {
			$.each($config, function (i, v) { 
				 sekretaris_pertandingan.audio[i] = v;
			});
		},
		putar_gong: function () {
			//!!! Syarat mutlak pemutaran gong adalah ketika waktu bernilai 0 maupun berada di awal pertandingan
			if (sekretaris_pertandingan.waktu_sekarang == (sekretaris_pertandingan.pertandingan.waktu_per_ronde * 1000) || sekretaris_pertandingan.waktu_sekarang == 0) {
				sekretaris_pertandingan.audio.gong_audio.play();
			}
		},
		putar_beep_alarm: () => {
			if (sekretaris_pertandingan.audio.beep_alarm != '0' && 
			sekretaris_pertandingan.audio.beep_alarm != false &&
				parseInt(sekretaris_pertandingan.waktu_sekarang) < 10000 && 
				parseInt(sekretaris_pertandingan.waktu_sekarang) > 1 && 
				sekretaris_pertandingan.status_pertandingan == 'berlangsung') {
				sekretaris_pertandingan.audio.beep_alarm_audio.play();
			}
			setTimeout(() => {
				sekretaris_pertandingan.audio.putar_beep_alarm();
				//recursive
			}, 1000);
		},
		voice_over: {
			jatuhan_audio: {
				biru : new Audio("assets/penilaian/audio/voice-over/jatuhan_biru.mp3"),
				merah : new Audio("assets/penilaian/audio/voice-over/jatuhan_merah.mp3"),
			},
			putar_audio_jatuhan: function($el, $sudut){
				$($el).removeClass('bg-blue bg-red');
				sekretaris_pertandingan.audio.voice_over.jatuhan_audio[$sudut].play();

				setTimeout(() => {
					if($sudut == 'biru'){
						$($el).addClass('bg-blue');
					}else{
						$($el).addClass('bg-red');
					}
				}, 4000);

			}
		}
	},
	websocket: {
		connection: null,
		channel: null,
		init: function ($WSserver, $id_gelanggang) {
			sekretaris_pertandingan.websocket.connection = new WebSocket($WSserver); // WebSocket server address
			sekretaris_pertandingan.websocket.channel = $id_gelanggang;

			sekretaris_pertandingan.websocket.connection.onopen = function (event) {
				console.log("WebSocket connection opened.");
				// Automatically join a channel upon connection
				sekretaris_pertandingan.websocket.joinChannel(sekretaris_pertandingan.websocket.channel);
			};

			sekretaris_pertandingan.websocket.connection.onmessage = function (event) {
				var message = event.data;
				$("#messages").append("<p>" + message + "</p>");
			};

			sekretaris_pertandingan.websocket.connection.onerror = function (event) {
				console.error("WebSocket error: ", event);
			};

			sekretaris_pertandingan.websocket.connection.onclose = function (event) {
				console.log("WebSocket connection closed.");
			};


			// setInterval(() => {
			// 	sekretaris_pertandingan.websocket.update_timer(sekretaris_pertandingan.waktu_sekarang);
			// }, 100);
		},
		sendMessage: function (message) {
			var data = {
				type: "message",
				channel: sekretaris_pertandingan.websocket.channel,
				message: message
			};
			sekretaris_pertandingan.websocket.connection.send(JSON.stringify(data));
		},

		joinChannel: function (channel) {
			var data = {
				type: "join_channel",
				channel: sekretaris_pertandingan.websocket.channel
			};
			sekretaris_pertandingan.websocket.connection.send(JSON.stringify(data));
		},

		update_timer: function (waktu_sekarang, status_pertandingan) {
			if (waktu_sekarang == undefined) {
				waktu_sekarang = 0;
			} else if (waktu_sekarang > 0) {
				$('.button-play-state').removeAttr('disabled');
			}
			// sekretaris_pertandingan.websocket.sendMessage([waktu_sekarang, status_pertandingan]);
		}
	},
	developer: {
		open_modal_ganti_format_penilaian: function ($passkey = '1234') {
			Swal.fire({
				title: "Attention !",
				text: "Please Enter Your PIN Code",
				input: 'password',
				showCancelButton: true,
				
				confirmButtonText: 'Submit'
			}).then((result) => {
				if (result.value == $passkey) {
					$('#modal_ganti_format_penilaian').modal('show');
				} else {
					Swal.fire({
						icon: "error",
						title: "Oops...",
						text: "Wrong Passcode!",
					});
				}
			});
		},
		open_modal_ubah_waktu: function ($passkey = '1234') {
			Swal.fire({
				title: "Attention !",
				text: "Please Enter Your PIN Code",
				input: 'password',
				showCancelButton: true,
				
				confirmButtonText: 'Submit'
			}).then((result) => {
				if (result.value == $passkey) {
					$('#modal_ubah_waktu').modal('show');
				} else {
					Swal.fire({
						icon: "error",
						title: "Oops...",
						text: "Wrong Passcode!",
					});
				}
			});
		},
		open_modal_pengaturan_suara: function ($passkey = '1234') {
			Swal.fire({
				title: "Attention !",
				text: "Please Enter Your PIN Code",
				input: 'password',
				showCancelButton: true,
				
				confirmButtonText: 'Submit'
			}).then((result) => {
				if (result.value == $passkey) {
					$('#modal_pengaturan_suara').modal('show');
				} else {
					Swal.fire({
						icon: "error",
						title: "Oops...",
						text: "Wrong Passcode!",
					});
				}
			});
		}
	},
	init: function ($pertandingan) {
		sekretaris_pertandingan.stopwatch = $(".timer-tanding");

		sekretaris_pertandingan.modal_ubah_waktu = new bootstrap.Modal(document.getElementById('modal_ubah_waktu'));

		sekretaris_pertandingan.set_variable($pertandingan);
		sekretaris_pertandingan.update_tampilan_nilai();
		sekretaris_pertandingan.set_timer();
		sekretaris_pertandingan.refresh_status_pertandingan();

		sekretaris_pertandingan.audio.putar_beep_alarm();// proses khusus untuk beep alarm < 10 detik
	},
	set_variable: function ($pertandingan) {
		sekretaris_pertandingan.pertandingan = $pertandingan;
		sekretaris_pertandingan.id_pertandingan = $pertandingan.id_pertandingan;
		sekretaris_pertandingan.jumlah_ronde = $pertandingan.jumlah_ronde;
		sekretaris_pertandingan.data_waktu = JSON.parse($pertandingan.data_waktu);
		sekretaris_pertandingan.status_pertandingan = $pertandingan.status_pertandingan;
		sekretaris_pertandingan.skor_merah = parseInt($pertandingan.skor_merah);
		sekretaris_pertandingan.skor_biru = parseInt($pertandingan.skor_biru);
		sekretaris_pertandingan.ronde_sekarang = $pertandingan.ronde_pertandingan;
		sekretaris_pertandingan.waktu_sekarang = sekretaris_pertandingan.data_waktu[sekretaris_pertandingan.ronde_sekarang][1];

	},
	set_timer: function () {
		$('.button-play-state').removeAttr('disabled');

		if (sekretaris_pertandingan.waktu_sekarang == 0) {
			sekretaris_pertandingan.stopwatch.html('00:00');
		} else {
			sekretaris_pertandingan.stopwatch.runner({
				countdown: true,
				startAt: sekretaris_pertandingan.waktu_sekarang,
				stopAt: 0,
				milliseconds: true,
			}).on('runnerStart', function (eventObject, info) {
				$('.button-play-state').html('stop');

				sekretaris_pertandingan.waktu_sekarang = info.time;
				sekretaris_pertandingan.status_pertandingan = 'berlangsung';
				$data_waktu = sekretaris_pertandingan.data_waktu;
				$data_waktu[sekretaris_pertandingan.ronde_sekarang][1] = sekretaris_pertandingan.waktu_sekarang;
				sekretaris_pertandingan.data_waktu = $data_waktu;

				sekretaris_pertandingan.send_timer_data();

			}).on('runnerStop', function (eventObject, info) {

				sekretaris_pertandingan.waktu_sekarang = info.time;
				sekretaris_pertandingan.status_pertandingan = 'berhenti';
				$data_waktu = sekretaris_pertandingan.data_waktu;
				$data_waktu[sekretaris_pertandingan.ronde_sekarang][1] = sekretaris_pertandingan.waktu_sekarang;
				sekretaris_pertandingan.data_waktu = $data_waktu;

				sekretaris_pertandingan.send_timer_data();

				//BLOK MENGUBAH HTML
				$('.button-play-state').html('start');
				if (sekretaris_pertandingan.waktu_sekarang == 0) {
					$('.button-play-state').prop('disabled', true);
				}
				//END BLOK MENGUBAH HTML
			});

			$('.button-play-state').html('start');
		}
	},
	mulai_pertandingan: function ($id_pertandingan) {
		Swal.fire({
			title: "Do you want to start this match ?",
			text: "Please Make Sure All judges already input the score !",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: "Yes, Start Math !"
		}).then((result) => {
			if (result.value) {
				$.post("sekretaris-pertandingan/mulai-pertandingan/" + $id_pertandingan,
					function (data, textStatus, xhr) {
						if (data.status == true) {
							Swal.fire('Success', 'Match Played !', 'success');
						} else {
							Swal.fire('Error', data.message, 'error');
						}
					}, 'json');
			}
		});
	},
	toggle_timer: function () {
		sekretaris_pertandingan.stopwatch.runner('toggle');
	},
	reset_timer: function () {
		Swal.fire({
			title: "Are you sure ?",
			text: "Time will be set to default !",
			icon: "warning",
			showCancelButton: true,
			confirmButtonText: 'Yes, Reset !'
		}).then((result) => {
			if (result.value) {
				//Mereset waktu berdasarkan nilai kolom waktu_per_ronde
				$data_waktu = sekretaris_pertandingan.data_waktu;
				$data_waktu[sekretaris_pertandingan.ronde_sekarang][1] = sekretaris_pertandingan.pertandingan.waktu_per_ronde * 1000;

				sekretaris_pertandingan.websocket.update_timer(sekretaris_pertandingan.pertandingan.waktu_per_ronde * 1000, 'berhenti') // !!! Langsung perintah reset via websocket

				$.post("sekretaris-pertandingan/toggle-timer-tanding/" + sekretaris_pertandingan.id_pertandingan,
					{ 'status_pertandingan': 'berhenti', 'data_waktu': JSON.stringify($data_waktu) },
					function (data, textStatus, jqXHR) {
						if (data.status == true) {
							window.location.reload();
						} else {
							Swal.fire('Error', 'Unable to reset time clock !', 'error');
						}
					}, "json"
				);
			}
		});
	},
	send_timer_data: function () {
		// BISA MENGGUNAKAN AJAX MAUPUN WEBSOCKET. Digunakan untuk toggle timer

		// sekretaris_pertandingan.websocket.update_timer(sekretaris_pertandingan.waktu_sekarang, sekretaris_pertandingan.status_pertandingan) // !!! Langsung perintah reset via websocket

		if (sekretaris_pertandingan.websocket.connection == null) {

			$.post("sekretaris-pertandingan/toggle-timer-tanding/" + sekretaris_pertandingan.id_pertandingan,
				{ 'status_pertandingan': sekretaris_pertandingan.status_pertandingan, 'data_waktu': JSON.stringify(sekretaris_pertandingan.data_waktu) },
				function (data, textStatus, jqXHR) {
					if (data.status == true) {
						sekretaris_pertandingan.audio.putar_gong();
					} else {
						Swal.fire('Error', 'Network Connection Failed !', 'error');
					}
				}, "json"
			);
		} else {

		}
	},
	open_modal_set_manual_waktu: function () {
		if (sekretaris_pertandingan.status_pertandingan == 'berlangsung') {
			Swal.fire('Error', 'Please Stop the clock first', 'error');
		} else {
			$('#formManualAturWaktu [name="detik"]').val(parseInt(sekretaris_pertandingan.waktu_sekarang) / 1000);
			sekretaris_pertandingan.set_label_modal_atur_manual_waktu(parseInt(sekretaris_pertandingan.waktu_sekarang) / 1000);
			$('#modalManualAturWaktu').modal('show');
		}
	},
	tetapkan_perubahan_manual_waktu: function () {
		$('#modalManualAturWaktu').modal('hide');
		waitingDialog.show('Applying Change...');

		$puluh_menit = parseInt($('.puluh-menit').html());
		$satuan_menit = parseInt($('.satuan-menit').html());
		$puluh_detik = parseInt($('.puluh-detik').html());
		$satuan_detik = parseInt($('.satuan-detik').html());

		$puluh_menit = $puluh_menit * 600000;
		$satuan_menit = $satuan_menit * 60000;
		$puluh_detik = $puluh_detik * 10000;
		$satuan_detik = $satuan_detik * 1000;

		$waktu_sekarang_terbaru = $puluh_menit + $satuan_menit + $puluh_detik + $satuan_detik;

		sekretaris_pertandingan.waktu_sekarang = $waktu_sekarang_terbaru;
		$data_waktu = sekretaris_pertandingan.data_waktu;
		$data_waktu[sekretaris_pertandingan.ronde_sekarang][1] = $waktu_sekarang_terbaru;
		$.post("sekretaris-pertandingan/toggle-timer-tanding/" + sekretaris_pertandingan.id_pertandingan,
			{ 'status_pertandingan': 'berhenti', 'data_waktu': JSON.stringify($data_waktu) },
			function (data, textStatus, jqXHR) {
				if (data.status == true) {
					setTimeout(() => {
						waitingDialog.hide();
						sekretaris_pertandingan.set_timer();
					}, 1000);
				} else {
					Swal.fire('Error', 'Failed Setting Clock Manually !', 'error');
				}
			}, "json"
		);
	},
	update_manual_waktu_sekarang: function ($el) {
		$waktu_sekarang_terbaru = $el.value;
		sekretaris_pertandingan.set_label_modal_atur_manual_waktu($waktu_sekarang_terbaru);
		sekretaris_pertandingan.set_timer();
	},
	set_label_modal_atur_manual_waktu: function ($nilai_waktu) {
		$menit = parseInt($nilai_waktu / 60);
		$menit = sekretaris_pertandingan.numberPad($menit, 2);
		$detik = $nilai_waktu % 60;
		$detik = sekretaris_pertandingan.numberPad($detik, 2);

		$split_menit = $menit.toString().split("");
		$split_detik = $detik.toString().split("");

		$('#formManualAturWaktu .puluh-menit').html($split_menit[0]);
		$('#formManualAturWaktu .satuan-menit').html($split_menit[1]);
		$('#formManualAturWaktu .puluh-detik').html($split_detik[0]);
		$('#formManualAturWaktu .satuan-detik').html($split_detik[1]);
	},
	ubah_manual_digit_waktu: function ($element, $adder, $max, $button, $semua_button) {
		$nilai_digit = $($element).html();
		$nilai_digit_updated = (parseInt($nilai_digit) + $adder);
		if ($nilai_digit_updated <= $max && $nilai_digit_updated >= 0) {
			$($element).html($nilai_digit_updated);
			$($semua_button).prop('disabled', false);
			$($semua_button).removeAttr('disabled');
		}

		if ($nilai_digit_updated >= $max) {
			$($button).prop('disabled', true);
		} else if ($max && $nilai_digit_updated <= 0) {
			$($button).prop('disabled', true);
		}

	},
	numberPad: function (num, size) {
		num = num.toString();
		while (num.length < size) num = "0" + num;
		return num;
	},
	pindah_ronde: function ($ronde_tujuan) {
		if (sekretaris_pertandingan.status_pertandingan == 'berlangsung') {
			Swal.fire('Error', 'Stopwatch is still running, please stop it first !', 'error')
		} else {
			$jumlah_ronde = sekretaris_pertandingan.pertandingan.jumlah_ronde;
			if ($ronde_tujuan <= $jumlah_ronde && $ronde_tujuan > 0) {
				Swal.fire({
					title: "Change Round ?",
					text: "Match round will be change to " + $ronde_tujuan,
					icon: "info",
					showCancelButton: true,
					confirmButtonText: "Yes, Change"
				}).then((result) => {
					if (result.value) {
						$.post("sekretaris-pertandingan/pindah-ronde-tanding/" + sekretaris_pertandingan.id_pertandingan, {
							ronde_berikutnya: $ronde_tujuan
						},
							function (data, textStatus, jqXHR) {
								if (data.status == true) {
									// sekretaris_pertandingan.stopwatch.runner('remove');
									window.location.reload();
								} else {
									console.log('Failed to change round')
								}
							}, "json"
						);
					}
				})
			} else {
				Swal.fire('error', 'Invalid round !', 'error');
			}
		}
	},
	update_tampilan_nilai: function () {
		$('#skor_merah').html(sekretaris_pertandingan.skor_merah);
		$('#skor_biru').html(sekretaris_pertandingan.skor_biru);
	},
	update_tampilan_ronde: function () {
		$('.btn-change-round').removeClass('bg-warning').addClass('bg-dark');
		$('#btn_round_' + sekretaris_pertandingan.ronde_sekarang).addClass('bg-warning').removeClass('bg-dark');
	},
	pindah_partai: function ($id_pertandingan) {
		// !!! DINONAKTIFKAN UNTUK PERSILAT
		$.post("sekretaris-pertandingan/pindah-partai-tanding/" + $id_pertandingan,
			function (data, textStatus, jqXHR) {
				if (data.status == true) {
					window.location.reload();
				} else {
					Swal.fire('error', data.message, 'error');
				}
			},
			"json"
		);
	},
	refresh_status_pertandingan: () => {
		$waktu_sekarang = sekretaris_pertandingan.stopwatch.runner('info').time; //mengambil waktu terkini dari stopwatch

		$data_waktu = sekretaris_pertandingan.data_waktu;
		$data_waktu[sekretaris_pertandingan.ronde_sekarang][1] = $waktu_sekarang;

		$.post("perangkat-pertandingan/refresh-status-pertandingan/" + sekretaris_pertandingan.id_pertandingan,
			{
				data_waktu: (sekretaris_pertandingan.status_pertandingan == 'berlangsung') ?
					JSON.stringify($data_waktu) : null
			},
			function (data, textStatus, jqXHR) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else if (data.status == true && (typeof data.pindah_partai) != 'undefined' && data.pindah_partai == true) {
					if (ui.animateOut !== undefined) {
						ui.animateOut();
						setTimeout(() => {
							ui.animateInNavigasiPartai();
						}, 2000);
					}
				} else if (data.pertandingan !== undefined) {
					sekretaris_pertandingan.set_variable(data.pertandingan);
					sekretaris_pertandingan.update_tampilan_nilai();
					sekretaris_pertandingan.update_tampilan_ronde();
				}
			},
			"json"
		).always(function () {
			setTimeout(() => {
				sekretaris_pertandingan.refresh_status_pertandingan();
			}, 3000);
		});
	}
}
