const layar = {
  interval_refresh: 1000,
  data_nilai: null,
  pertandingan: null,
  id_pertandingan: null,
  ronde_pertandingan: null,
  verifikasi_pertandingan: null,
  waktu_sekarang: 1,
  waktu_per_ronde: null,
  stopwatch: null,
  ringkasan_nilai: null,
  skor_biru_verifikasi: 0, // digunakan untuk  melacak jawaban verifikasi
  skor_merah_verifikasi: 0, // digunakan untuk  melacak jawaban verifikasi
  sistem_dialog_terdahulu: null, // Digunakan untuk melacak jawaban verifikasi
  init: function (
    $data_nilai,
    $pertandingan,
    $verifikasi_pertandingan,
    $interval_refresh = 1000,
  ) {
    layar.setup_modals();
    if (typeof io !== 'undefined') {
      layar.socket = io('http://localhost:3000');
    }
    ui.start_animation();
    layar.set_variable(
      $data_nilai,
      $pertandingan,
      $verifikasi_pertandingan,
      $interval_refresh,
    );
    if (layar.socket) {
      layar.socket.emit('JOIN_ROOM', layar.id_pertandingan);
      layar.socket.on('UPDATE_WAKTU', function(data) {
        console.log('Layar menerima UPDATE_WAKTU dari socket:', data);
        layar.waktu_sekarang = data.waktu;
        layar.pertandingan.status_pertandingan = data.action;
        let seconds = Math.floor(layar.waktu_sekarang / 1000);

        if (seconds <= 0) {
            layar.stopwatch.timer("remove");
            layar.stopwatch.html("00:00");
        } else {
            if (data.action == 'berlangsung') {
              layar.stopwatch.timer("remove");
              layar.stopwatch.timer({
                format: "%M:%S",
                seconds: 0,
                duration: seconds,
                countdown: true,
                action: 'start'
              });
            } else {
              layar.stopwatch.timer("remove");
              layar.stopwatch.timer({
                format: "%M:%S",
                seconds: 0,
                duration: seconds,
                countdown: true
              });
              layar.stopwatch.timer("pause");
            }
        }
      });
    }
    ui.update_tampilan_nilai();
    if (typeof layar.socket === 'undefined' || !layar.socket.connected) {
      layar.update_timer();
    }
    layar.refresh_status_pertandingan();
  },
  set_variable: function (
    $data_nilai,
    $pertandingan,
    $verifikasi_pertandingan,
    $interval_refresh,
  ) {
    layar.data_nilai = $data_nilai;
    layar.pertandingan = $pertandingan;
    layar.interval_refresh = $interval_refresh;

    layar.id_pertandingan = $pertandingan.id_pertandingan;
    layar.waktu_per_ronde = $pertandingan.waktu_per_ronde;

    if (layar.ronde_pertandingan !== $pertandingan.ronde_pertandingan) {
      layar.ronde_pertandingan = $pertandingan.ronde_pertandingan;
    }
    if ($pertandingan.ringkasan_nilai !== undefined) {
      layar.ringkasan_nilai = JSON.parse($pertandingan.ringkasan_nilai);
    }

    layar.verifikasi_pertandingan = $verifikasi_pertandingan;
    layar.stopwatch = $(".stopwatch");
    layar.ronde_sekarang = $pertandingan.ronde_pertandingan;
    layar.waktu_sekarang = $pertandingan.data_waktu[layar.ronde_sekarang][1];
  },
  setup_modals: function () {
    let elJatuhan = document.getElementById("modalVerifikasiJatuhan");
    if (elJatuhan && layar.modalVerifikasiJatuhan === null) {
      layar.modalVerifikasiJatuhan = new bootstrap.Modal(elJatuhan, {
        keyboard: false,
      });
    }

    let elPelanggaran = document.getElementById("modalVerifikasiPelanggaran");
    if (elPelanggaran && layar.modalVerifikasiPelanggaran === null) {
      layar.modalVerifikasiPelanggaran = new bootstrap.Modal(elPelanggaran, {
        keyboard: false,
      });
    }

    let elHasil = document.getElementById("modalHasilVerifikasi");
    if (elHasil && layar.modalHasilVerifikasi === null) {
      layar.modalHasilVerifikasi = new bootstrap.Modal(elHasil, {
        keyboard: false,
      });
    }
  },
  update_timer: function () {
    let seconds = Math.floor(layar.waktu_sekarang / 1000);

    if (seconds <= 0) {
        layar.stopwatch.timer("remove");
        layar.stopwatch.html("00:00");
        return;
    }

    if (layar.pertandingan.status_pertandingan == "berlangsung") {
      let state = layar.stopwatch.data("state");
      if (state !== "running") {
        layar.stopwatch.timer("remove");
        layar.stopwatch.timer({
          format: "%M:%S",
          seconds: 0,
          duration: seconds,
          countdown: true,
          action: 'start'
        });
      }
    } else if (layar.pertandingan.status_pertandingan !== "berlangsung") {
      layar.stopwatch.timer("remove");
      layar.stopwatch.timer({
        format: "%M:%S",
        seconds: 0,
        duration: seconds,
        countdown: true
      });
      layar.stopwatch.timer("pause");
    }
  },
  modalVerifikasiJatuhan: null,
  modalVerifikasiPelanggaran: null,
  modalHasilVerifikasi: null,
  // open_modal_verifikasi_jatuhan: function () {
  // 	if (layar.modalVerifikasiJatuhan._isShown === false) {
  // 		$modal = $(layar.modalVerifikasiJatuhan._element);
  // 		$.each($modal.find('div.card'), function (i, v) {
  // 			$(v).find('.card-body > p').html('Waiting Response');
  // 		});
  // 		layar.modalVerifikasiJatuhan.show();
  // 	}
  // },
  close_modal_verifikasi_jatuhan: function () {
    if (layar.modalVerifikasiJatuhan) {
      layar.modalVerifikasiJatuhan.hide();
    }
  },
  // open_modal_verifikasi_pelanggaran: function () {
  // 	//Diawal selalu reset warna dan tulisan kartu jawababn sistem dialog
  // 	$modal = $(layar.modalVerifikasiPelanggaran._element);
  // 	$.each($modal.find('div.card'), function (i, v) {
  // 		$(v).find('.card-body > p').html('Waiting Response');
  // 		$(v).addClass('bg-dark').removeClass('bg-red bg-blue');
  // 	});

  // 	layar.modalVerifikasiPelanggaran.show();
  // },
  close_modal_verifikasi_pelanggaran: function () {
    if (layar.modalVerifikasiPelanggaran) {
      layar.modalVerifikasiPelanggaran.hide();
    }
  },
  // open_modal_hasil_verifikasi: function ($background = null, $text = null) {
  // 	if (layar.modalHasilVerifikasi._isShown === false) {
  // 		$modal = $(layar.modalHasilVerifikasi._element);
  // 		if ($background != null) {
  // 			$modal.find('.modal-body').removeClass().addClass('modal-body bg-' + $background);
  // 		}

  // 		if ($text != null) {
  // 			$modal.find('#textModalHasilVerifikasi').html($text);
  // 		}
  // 		layar.modalHasilVerifikasi.show();

  // 		setTimeout(() => {
  // 			layar.modalHasilVerifikasi.hide();
  // 		}, 4000);
  // 	}
  // },
  periksa_sistem_dialog: function () {
    if (
      layar.verifikasi_pertandingan == null ||
      layar.verifikasi_pertandingan == undefined
    ) {
      layar.close_modal_verifikasi_pelanggaran();
      layar.close_modal_verifikasi_jatuhan();
    } else {
      if (layar.verifikasi_pertandingan.jenis_verifikasi === "jatuhan") {
        if (layar.verifikasi_pertandingan.status == "berlangsung") {
          ui.open_modal_verifikasi_jatuhan();
          layar.close_modal_verifikasi_pelanggaran();
        } else if (
          layar.verifikasi_pertandingan.status == "selesai" &&
          (layar.modalVerifikasiJatuhan && layar.modalVerifikasiJatuhan._isShown == true)
        ) {
          // Verifikasi selesai, namun modal menunggu jawaban masih terbuka, harus segera tampilkan hasil
          layar.close_modal_verifikasi_jatuhan();
          setTimeout(() => {
            if (layar.verifikasi_pertandingan.hasil_verifikasi == "biru") {
              ui.open_modal_hasil_verifikasi("blue", "Valid Drop!");
            } else if (
              layar.verifikasi_pertandingan.hasil_verifikasi == "merah"
            ) {
              ui.open_modal_hasil_verifikasi("red", "Valid Drop!");
            } else if (
              layar.verifikasi_pertandingan.hasil_verifikasi == "invalid"
            ) {
              ui.open_modal_hasil_verifikasi("warning", "Invalid Drop!");
            }
          }, 700);
        } else {
          // Batal
          layar.close_modal_verifikasi_jatuhan();
        }
      } else if (
        layar.verifikasi_pertandingan.jenis_verifikasi === "pelanggaran"
      ) {
        if (layar.verifikasi_pertandingan.status == "berlangsung") {
          ui.open_modal_verifikasi_pelanggaran();
          layar.close_modal_verifikasi_jatuhan();
        } else if (
          layar.verifikasi_pertandingan.status == "selesai" &&
          (layar.modalVerifikasiPelanggaran && layar.modalVerifikasiPelanggaran._isShown == true)
        ) {
          // Verifikasi selesai, namun modal menunggu jawaban masih terbuka, harus segera tampilkan hasil
          layar.close_modal_verifikasi_pelanggaran();
          setTimeout(() => {
            if (layar.verifikasi_pertandingan.hasil_verifikasi == "biru") {
              ui.open_modal_hasil_verifikasi("blue", "Valid Violation!");
            } else if (
              layar.verifikasi_pertandingan.hasil_verifikasi == "merah"
            ) {
              ui.open_modal_hasil_verifikasi("red", "Valid Violation!");
            } else if (
              layar.verifikasi_pertandingan.hasil_verifikasi == "invalid"
            ) {
              ui.open_modal_hasil_verifikasi("warning", "Invalid Violation!");
            }
          }, 700);
        } else {
          // Do nothing
          layar.close_modal_verifikasi_pelanggaran();
        }
      }
    }
  },
  refresh_status_pertandingan: () => {
    $.post(
      "layar/refresh-status-pertandingan/" + layar.id_pertandingan,
      function (data, textStatus, jqXHR) {
        if (data.status === true && data.reload === true) {
          window.location.reload();
        } else {
          //Eksekusi stinger pergantian ronde secara otomatis
          if (
            layar.ronde_pertandingan !== null &&
            layar.ronde_pertandingan !== data.pertandingan.ronde_pertandingan
          ) {
            if (typeof stinger !== "undefined") {
              stinger.set_text("Round " + data.pertandingan.ronde_pertandingan);
              stinger.start_animation(function () {
                setTimeout(() => {
                  stinger.end_animation();
                }, 6000);
              });
            }
          }

          layar.set_variable(
            data.data_nilai,
            data.pertandingan,
            data.verifikasi_pertandingan,
          );
          ui.update_tampilan_nilai();
          if (typeof layar.socket === 'undefined' || !layar.socket.connected) {
            layar.update_timer(); // Fallback timer jika socket disconnect
          }
          setTimeout(() => {
            layar.periksa_sistem_dialog();
          }, 1000);
        }
      },
      "json",
    ).always(function () {
      setTimeout(() => {
        layar.refresh_status_pertandingan();
      }, layar.interval_refresh);
    });
  },
};
