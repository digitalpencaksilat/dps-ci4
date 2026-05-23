const sekretaris_pertandingan = {
  waktu_tampil: 0,
  stopwatch: null,
  modal_penentuan_juara: null,
  modal_ganti_format_penilaian: null,
  id_penampilan_seni: null,
  status_penampilan: null,
  beepAudio: new Audio("assets/penilaian/audio/beep.mp3"),
  alarmAudio: new Audio("assets/penilaian/audio/alarm.mp3"),
  init: function ($penampilan_seni_berlangsung, $waktu_tampil) {
    sekretaris_pertandingan.stopwatch = $(".timer-seni");
    $("#sortPenentuanJuara").sortable();

    if (
      $("#modal_penentuan_juara").length > 0 &&
      $("#modal_ganti_format_penilaian").length > 0
    ) {
      sekretaris_pertandingan.modal_penentuan_juara = new bootstrap.Modal(
        document.getElementById("modal_penentuan_juara")
      );
      sekretaris_pertandingan.modal_ganti_format_penilaian =
        new bootstrap.Modal(
          document.getElementById("modal_ganti_format_penilaian")
        );
    }

    sekretaris_pertandingan.set_variable(
      $penampilan_seni_berlangsung,
      $waktu_tampil
    );
    sekretaris_pertandingan.set_timer();
    // 🔌 Join room socket SENI
    if (typeof socket !== "undefined") {
      const identitas = {
        role: "Sekretaris",
        nama_perangkat: nama_perangkat,
        nomor_partai: nomor_partai,
      };
      socket.emit("identitas_perangkat", identitas);
      console.log("📡 Identitas perangkat dikirim:", identitas);
      const roomId = "seni_" + $penampilan_seni_berlangsung.id_penampilan_seni;
      socket.emit(
        "gabung_seni",
        $penampilan_seni_berlangsung.id_penampilan_seni
      );
      console.log("🧾 Sekretaris gabung ke room:", roomId);
    }

    // sekretaris_pertandingan.refresh_status_seni();
  },
  set_variable: function ($penampilan_seni_berlangsung, $waktu_tampil) {
    sekretaris_pertandingan.id_penampilan_seni =
      $penampilan_seni_berlangsung.id_penampilan_seni;
    sekretaris_pertandingan.sistem_penampilan =
      $penampilan_seni_berlangsung.sistem_penampilan;
    sekretaris_pertandingan.status_penampilan =
      $penampilan_seni_berlangsung.status_penampilan;
    sekretaris_pertandingan.waktu_tampil = $waktu_tampil;
  },
  set_timer: function () {
    const waktuMs = sekretaris_pertandingan.waktu_tampil * 1000;

    // 🔒 Hindari multiple runner instance jika sebelumnya sudah aktif
    if (sekretaris_pertandingan.stopwatch.data("runnerObj")) {
      sekretaris_pertandingan.stopwatch.runner("stop").runner("reset");
    }

    // ⏱️ Inisialisasi ulang plugin runner
    sekretaris_pertandingan.stopwatch
      .runner({
        countdown: true,
        startAt: waktuMs,
        stopAt: 0,
        milliseconds: true,
        format: "%M:%S.%N", // ⬅️ format yang kamu inginkan
      })
      .on("runnerStart", function (eventObject, info) {
        $(".btn-toggle-waktu-tampil")
          .data("status-penampilan", "berhenti")
          .html("pause")
          .addClass("btn-danger")
          .removeClass("btn-success");

        sekretaris_pertandingan.status_penampilan = "sedang_tampil";
        sekretaris_pertandingan.waktu_tampil = info.time;

        if (typeof socket !== "undefined") {
          socket.emit("trigger_refresh_seni", {
            id_penampilan_seni: sekretaris_pertandingan.id_penampilan_seni,
            waktu_tampil: sekretaris_pertandingan.waktu_tampil,
            status_penampilan: sekretaris_pertandingan.status_penampilan,
          });
        }

        sekretaris_pertandingan.beepAudio.play();
      })
      .on("runnerStop", function (eventObject, info) {
        sekretaris_pertandingan.status_penampilan = "berhenti";
        sekretaris_pertandingan.waktu_tampil = info.time;

        $(".btn-toggle-waktu-tampil")
          .data("status-penampilan", "sedang_tampil")
          .html("resume")
          .addClass("btn-success")
          .removeClass("btn-danger");

        if (typeof socket !== "undefined") {
          socket.emit("trigger_refresh_seni", {
            id_penampilan_seni: sekretaris_pertandingan.id_penampilan_seni,
            waktu_tampil: sekretaris_pertandingan.waktu_tampil,
            status_penampilan: sekretaris_pertandingan.status_penampilan,
          });
        }
      });
  },
  toggle_timer: function () {
    const $btn = $(".btn-toggle-waktu-tampil");
    const status_penampilan = $btn.data("status-penampilan"); // "sedang_tampil" atau "berhenti"
    const runnerInfo = sekretaris_pertandingan.stopwatch.runner("info");
    const waktu_sekarang = runnerInfo?.time || 0;

    $.post(
      "sekretaris-pertandingan/toggle-timer-seni/" +
        sekretaris_pertandingan.id_penampilan_seni,
      {
        status_penampilan: status_penampilan,
        waktu_tampil: Math.floor(waktu_sekarang / 1000), // kirim ke server dalam detik
      },
      function (data) {
        if (data.status === true) {
          if (typeof socket !== "undefined") {
            socket.emit("trigger_refresh_seni", {
              id_penampilan_seni: sekretaris_pertandingan.id_penampilan_seni,
              waktu_tampil: Math.floor(waktu_sekarang / 1000),
              status_penampilan:
                status_penampilan === "sedang_tampil"
                  ? "sedang_tampil"
                  : "berhenti",
            });
            console.log("🔔 trigger_refresh_seni dikirim oleh sekretaris", {
              id_penampilan_seni: sekretaris_pertandingan.id_penampilan_seni,
              waktu_tampil: Math.floor(waktu_sekarang / 1000),
              status_penampilan:
                status_penampilan === "sedang_tampil"
                  ? "sedang_tampil"
                  : "berhenti",
            });
          }

          if (status_penampilan === "sedang_tampil") {
            // START timer
            sekretaris_pertandingan.stopwatch
              .runner("reset")
              .runner({
                countdown: false,
                startAt: sekretaris_pertandingan.waktu_tampil,
                milliseconds: true,
                format: "%M:%S.%N",
              })
              .runner("start");

            $btn
              .data("status-penampilan", "berhenti")
              .html("pause")
              .removeClass("btn-success")
              .addClass("btn-danger");
          } else {
            // PAUSE timer
            sekretaris_pertandingan.stopwatch.runner("stop");
            sekretaris_pertandingan.waktu_tampil = Math.floor(
              waktu_sekarang / 1000
            );

            $btn
              .data("status-penampilan", "sedang_tampil")
              .html("resume")
              .removeClass("btn-danger")
              .addClass("btn-success");
          }

          sekretaris_pertandingan.beepAudio.play();
        } else {
          Swal.fire("Error", "Failed to toggle clock, System Error!", "error");
        }
      },
      "json"
    );
  },
  reset_timer: function () {
    Swal.fire({
      title: "Are You Sure?",
      text: "Clock will be reset to 0!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, Reset",
    }).then((result) => {
      if (result.value) {
        sekretaris_pertandingan.stopwatch.runner("reset");
        sekretaris_pertandingan.stopwatch.html("00:00");

        sekretaris_pertandingan.waktu_tampil = 0;
        sekretaris_pertandingan.status_penampilan = "berhenti";

        $(".btn-toggle-waktu-tampil")
          .data("status-penampilan", "sedang_tampil")
          .html("start")
          .removeClass("btn-danger btn-success");

        $.post(
          "sekretaris-pertandingan/toggle-timer-seni/" +
            sekretaris_pertandingan.id_penampilan_seni,
          { status_penampilan: "berhenti", waktu_tampil: 0 },
          function (data, textStatus, jqXHR) {
            if (data.status == true && typeof socket !== "undefined") {
              socket.emit("trigger_refresh_seni", {
                id_penampilan_seni: sekretaris_pertandingan.id_penampilan_seni,
                waktu_tampil: sekretaris_pertandingan.waktu_tampil,
                status_penampilan: sekretaris_pertandingan.status_penampilan,
              });
              console.log("🔁 trigger_refresh_seni dikirim oleh sekretaris");
            }
          },
          "json"
        );
      }
    });
  },
  mulai_penampilan_seni: ($id_penampilan_seni) => {
    Swal.fire({
      title: "Are you sure ?",
      text: "Artistic Performance will be started",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, Start !",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.value) {
        $.post(
          "sekretaris-pertandingan/mulai-penampilan/" + $id_penampilan_seni,
          function (data, textStatus, xhr) {
            if (data.status == true) {
              if (typeof socket !== "undefined") {
                socket.emit("trigger_refresh_seni", {
                  id_penampilan_seni:
                    sekretaris_pertandingan.id_penampilan_seni,
                });
                console.log("🔔 trigger_refresh_seni dikirim oleh sekretaris");
              }

              window.location.reload();
            } else {
              Swal.fire("Error", data.message, "error");
            }
          },
          "json"
        );
      } else result.dismiss === Swal.DismissReason.cancel;
      /* Read more about handling dismissals below */
    });
  },
  selesai_penampilan: function () {
    Swal.fire({
      title: "Are you sure ?",
      text: "This artistic performance will be finished!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, Finish It",
    }).then((result) => {
      if (result.value) {
        $.post(
          "sekretaris-pertandingan/selesaikan-penampilan-seni/" +
            sekretaris_pertandingan.id_penampilan_seni,
          function (data, textStatus, jqXHR) {
            if (data.status == true && data.input_medali == false) {
              if (typeof socket !== "undefined") {
                socket.emit("trigger_refresh_seni", {
                  id_penampilan_seni:
                    sekretaris_pertandingan.id_penampilan_seni,
                });
                console.log("🔔 trigger_refresh_seni dikirim oleh sekretaris");
              }

              setTimeout(() => {
                Swal.fire({
                  title: "Success",
                  text: "Artistic Performance finished !",
                  icon: "success",
                  showCancelButton: false,
                  confirmButtonText: "OK",
                }).then((result) => {
                  if (ui.animateOut !== undefined) {
                    ui.animateOut();
                    setTimeout(() => {
                      ui.animateInNavigasiPartai();
                    }, 2000);
                  }
                  $(".btn_selesai, .button-timer, .btn-diskualifikasi").prop(
                    "disabled",
                    true
                  );
                });
              }, 1000);
            } else if (data.status == true && data.input_medali == true) {
              if (typeof socket !== "undefined") {
                socket.emit("trigger_refresh_seni", {
                  id_penampilan_seni:
                    sekretaris_pertandingan.id_penampilan_seni,
                });
                console.log("🔔 trigger_refresh_seni dikirim oleh sekretaris");
              }

              if (ui.animateOut !== undefined) {
                ui.animateOut();
                setTimeout(() => {
                  ui.animateInNavigasiPartai();
                }, 2000);
              }
              $(".btn_selesai, .button-timer, .btn-diskualifikasi").prop(
                "disabled",
                true
              );
              setTimeout(() => {
                sekretaris_pertandingan.open_modal_input_juara(
                  sekretaris_pertandingan.id_penampilan_seni
                );
              }, 4000);
            } else {
              Swal.fire("Error", data.message, "error");
            }
          },
          "json"
        );
      }
    });
  },
  pindah_partai: function ($partai_selanjutnya) {
    $.post(
      "sekretaris-pertandingan/pindah-partai-seni",
      {
        partai_selanjutnya: $partai_selanjutnya,
      },
      function (data, textStatus, jqXHR) {
        if (data.status == true) {
          if (typeof socket !== "undefined") {
            socket.emit("trigger_refresh_seni", {
              id_penampilan_seni: sekretaris_pertandingan.id_penampilan_seni,
            });
            console.log("🔔 trigger_refresh_seni dikirim oleh sekretaris");
          }

          window.location.reload();
        } else {
          Swal.fire("error", data.message, "error");
        }
      },
      "json"
    );
  },
  open_modal_input_juara: function () {
    // Digunakan pada sistem pool maupun sistem battle
    $.getJSON(
      "sekretaris-pertandingan/get-data-penentuan-juara/" +
        sekretaris_pertandingan.sistem_penampilan +
        "/" +
        sekretaris_pertandingan.id_penampilan_seni,
      function (data, textStatus, jqXHR) {
        if (sekretaris_pertandingan.sistem_penampilan == "pool") {
          sekretaris_pertandingan.modal_penentuan_juara.show();
        } else {
          // $('.label-penampilan-biru').html(data.penampilan_seni_biru.anggota_kelompok_peserta_seni+' ( '+data.penampilan_seni_biru.nilai_akhir+' )');
          // $('.label-penampilan-merah').html(data.penampilan_seni_merah.anggota_kelompok_peserta_seni+' ( '+data.penampilan_seni_merah.nilai_akhir+' )');

          // $('#penampilan_seni_biru').val(data.penampilan_seni_biru.id_penampilan_seni);
          // $('#penampilan_seni_merah').val(data.penampilan_seni_merah.id_penampilan_seni);

          $("#penampilan_seni_biru, #penampilan_seni_merah").removeAttr(
            "checked"
          );

          if (
            data.penampilan_seni_biru.nilai_akhir >
            data.penampilan_seni_merah.nilai_akhir
          ) {
            $("#penampilan_seni_biru").prop("checked", true);
          } else if (
            data.penampilan_seni_biru.nilai_akhir <
            data.penampilan_seni_merah.nilai_akhir
          ) {
            $("#penampilan_seni_merah").prop("checked", true);
          } else {
            $("#penampilan_seni_biru").prop("checked", true);
          }
          sekretaris_pertandingan.modal_penentuan_juara.show();
        }
      }
    );
  },
  submit_input_juara_seni: function () {
    if (sekretaris_pertandingan.sistem_penampilan == "pool") {
      $array_urutan_juara = [];
      jenis_medali_form = document.getElementById("formJenisMedali");
      console.log(jenis_medali_form);
      jenis_medali_form = new FormData(jenis_medali_form);
      console.log(jenis_medali_form);

      $.ajax({
        url: "sekretaris-pertandingan/input-manual-juara-seni/",
        type: "POST",
        data: jenis_medali_form,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (data, textStatus, jqXHR) {
          console.log(data);
          if (data.status == true) {
            $("#modal_penentuan_juara").modal("hide");
            Swal.fire(
              "success",
              "Medals data inputted successfully",
              "success"
            );
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          //if fails
        },
      });
    } else {
      $.post(
        "sekretaris-pertandingan/pilih-pemenang-battle-seni/" +
          sekretaris_pertandingan.id_penampilan_seni,
        $("#form_keputusan_pemenang").serialize(),
        function (data, textStatus, jqXHR) {
          if (data.status == true) {
            $("#modal_penentuan_juara").modal("hide");
            Swal.fire("success", "Medals Inputed", "success");
          }
        },
        "JSON"
      );
    }
  },
  diskualifikasi_peserta: function () {
    Swal.fire({
      title: "Warning !",
      text: "Final score will be set to 0",
      icon: "warning",
      type: "info",
      showCancelButton: true,
      confirmButtonText: "Yes, Disqualify participants !",
    }).then((result) => {
      if (result.value) {
        $.post(
          "sekretaris-pertandingan/diskualifikasi-penampilan-seni/" +
            sekretaris_pertandingan.id_penampilan_seni,
          function (data, textStatus, jqXHR) {
            $(".btn-diskualifikasi").fadeOut("fast", function () {
              $(".btn-batal-diskualifikasi").fadeIn();
            });
          },
          "json"
        );
      }
    });
  },
  batalkan_diskualifikasi_peserta: function () {
    Swal.fire({
      title: "Perhatian !",
      text: "Detailed score will be restored",
      icon: "warning",
      type: "info",
      showCancelButton: true,
      confirmButtonText: "Yes, Restore!",
    }).then((result) => {
      if (result.value) {
        $.post(
          "sekretaris-pertandingan/batalkan-diskualifikasi-penampilan-seni/" +
            sekretaris_pertandingan.id_penampilan_seni,
          function (data, textStatus, jqXHR) {
            if (data.status == true) {
              if (typeof socket !== "undefined") {
                socket.emit("trigger_refresh_seni", {
                  id_penampilan_seni:
                    sekretaris_pertandingan.id_penampilan_seni,
                });
                console.log("🔔 trigger_refresh_seni dikirim oleh sekretaris");
              }
              $(".btn_selesai, .btn-timer").removeAttr("disabled");
            } else {
              Swal.fire("Error", data.message, "error");
            }

            $(".btn-batal-diskualifikasi").fadeOut("fast", function () {
              $(".btn-diskualifikasi").fadeIn();
            });
          },
          "json"
        );
      }
    });
  },
  open_modal_set_manual_waktu: function () {
    if (sekretaris_pertandingan.status_penampilan == "sedang_tampil") {
      Swal.fire("Error", "Please Stop the clock first", "error");
    } else {
      sekretaris_pertandingan.set_label_modal_atur_manual_waktu(
        sekretaris_pertandingan.waktu_tampil
      );
      $("#modalManualAturWaktu").modal("show");
    }
  },
  tetapkan_perubahan_manual_waktu: function () {
    $("#modalManualAturWaktu").modal("hide");
    waitingDialog.show("Applying Change...");

    $puluh_menit = parseInt($(".puluh-menit").html());
    $satuan_menit = parseInt($(".satuan-menit").html());
    $puluh_detik = parseInt($(".puluh-detik").html());
    $satuan_detik = parseInt($(".satuan-detik").html());

    $puluh_menit = $puluh_menit * 600;
    $satuan_menit = $satuan_menit * 60;
    $puluh_detik = $puluh_detik * 10;

    $waktu_sekarang_terbaru =
      $puluh_menit + $satuan_menit + $puluh_detik + $satuan_detik;

    sekretaris_pertandingan.waktu_tampil = $waktu_sekarang_terbaru;
    $.post(
      "sekretaris-pertandingan/toggle-timer-seni/" +
        sekretaris_pertandingan.id_penampilan_seni,
      {
        status_penampilan: "berhenti",
        waktu_tampil: sekretaris_pertandingan.waktu_tampil,
      },
      function (data, textStatus, jqXHR) {
        if (data.status == true) {
          if (typeof socket !== "undefined") {
            socket.emit("trigger_refresh_seni", {
              id_penampilan_seni: sekretaris_pertandingan.id_penampilan_seni,
            });
            console.log("🔔 trigger_refresh_seni dikirim oleh sekretaris");
          }
          setTimeout(() => {
            waitingDialog.hide();
            sekretaris_pertandingan.set_timer();
          }, 1000);
        } else {
          Swal.fire("Error", "Failed Setting Clock Manually !", "error");
        }
      },
      "json"
    );
  },
  update_manual_waktu_sekarang: function ($el) {
    $waktu_sekarang_terbaru = $el.value;
    sekretaris_pertandingan.set_label_modal_atur_manual_waktu(
      $waktu_sekarang_terbaru
    );
    sekretaris_pertandingan.set_timer();
  },
  set_label_modal_atur_manual_waktu: function ($nilai_waktu) {
    $menit = parseInt($nilai_waktu / 60);
    $menit = sekretaris_pertandingan.numberPad($menit, 2);
    $detik = $nilai_waktu % 60;
    $detik = sekretaris_pertandingan.numberPad($detik, 2);

    $split_menit = $menit.toString().split("");
    $split_detik = $detik.toString().split("");

    $("#formManualAturWaktu .puluh-menit").html($split_menit[0]);
    $("#formManualAturWaktu .satuan-menit").html($split_menit[1]);
    $("#formManualAturWaktu .puluh-detik").html($split_detik[0]);
    $("#formManualAturWaktu .satuan-detik").html($split_detik[1]);
  },
  ubah_manual_digit_waktu: function (
    $element,
    $adder,
    $max,
    $button,
    $semua_button
  ) {
    $nilai_digit = $($element).html();
    $nilai_digit_updated = parseInt($nilai_digit) + $adder;
    if ($nilai_digit_updated <= $max && $nilai_digit_updated >= 0) {
      $($element).html($nilai_digit_updated);
      $($semua_button).prop("disabled", false);
      $($semua_button).removeAttr("disabled");
    }

    if ($nilai_digit_updated >= $max) {
      $($button).prop("disabled", true);
    } else if ($max && $nilai_digit_updated <= 0) {
      $($button).prop("disabled", true);
    }
  },
  numberPad: function (num, size) {
    num = num.toString();
    while (num.length < size) num = "0" + num;
    return num;
  },
  refresh_status_seni: function () {
    /**
     * fungsi ini berbeda dengan fungsi pada perangkat_pertandingan
     * fungsi ini digunakan oleh sekretaris pertandingan untuk update waktu
     */
    $waktu = sekretaris_pertandingan.stopwatch.data("seconds");
    if ($waktu == null || $waktu == 0) {
      /*
       *	JIKA TIMER TIDAK BERJALAN, JANGAN UPDATE KE WAKTU 0.
       *	skrenario ini dibutuhkan jika menggunakan 2 user timer
       */
      $.post(
        "sekretaris-pertandingan/refresh-status-seni/" +
          sekretaris_pertandingan.id_penampilan_seni,
        function (data, textStatus, jqXHR) {
          if (data.status === true && data.reload === true) {
            if (typeof socket !== "undefined") {
              socket.emit("trigger_refresh_seni", {
                id_penampilan_seni: sekretaris_pertandingan.id_penampilan_seni,
              });
              console.log("🔔 trigger_refresh_seni dikirim oleh sekretaris");
            }
            window.location.reload();
          }
        },
        "json"
      ).always(function () {
        setTimeout(() => {
          sekretaris_pertandingan.refresh_status_seni();
        }, 1000);
      });
    } else {
      $.post(
        "sekretaris-pertandingan/refresh-status-seni/" +
          sekretaris_pertandingan.id_penampilan_seni,
        {
          waktu: $waktu,
        },
        function (data, textStatus, jqXHR) {
          if (data.status === true && data.reload === true) {
            window.location.reload();
          }
        },
        "json"
      ).always(function () {
        setTimeout(() => {
          sekretaris_pertandingan.refresh_status_seni();
        }, 1000);
      });
    }
  },
};
