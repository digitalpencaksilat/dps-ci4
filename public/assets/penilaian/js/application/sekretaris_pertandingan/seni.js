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
        document.getElementById("modal_penentuan_juara"),
      );
      sekretaris_pertandingan.modal_ganti_format_penilaian =
        new bootstrap.Modal(
          document.getElementById("modal_ganti_format_penilaian"),
        );
    }

    if (typeof io !== 'undefined') {
      sekretaris_pertandingan.socket = io(typeof SOCKET_URL !== 'undefined' ? SOCKET_URL : 'http://localhost:3000');
    }

    sekretaris_pertandingan.set_variable(
      $penampilan_seni_berlangsung,
      $waktu_tampil,
    );

    if (sekretaris_pertandingan.socket) {
        sekretaris_pertandingan.socket.emit('JOIN_ROOM', sekretaris_pertandingan.id_penampilan_seni);
        sekretaris_pertandingan.socket.emit('KONTROL_WAKTU', {
            id_pertandingan: sekretaris_pertandingan.id_penampilan_seni,
            action: sekretaris_pertandingan.status_penampilan,
            waktu: sekretaris_pertandingan.waktu_tampil
        });
    }

    sekretaris_pertandingan.set_timer();

    sekretaris_pertandingan.refresh_status_seni();
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
    sekretaris_pertandingan.stopwatch.timer("remove");
    sekretaris_pertandingan.stopwatch.timer({
      format: "%M:%S",
      editable: true, // Izinkan edit via klik
      seconds: sekretaris_pertandingan.waktu_tampil,
    });
    
    if (sekretaris_pertandingan.status_penampilan !== "sedang_tampil") {
        // Jika tidak sedang tanding, matikan mesin timernya (tapi teks tetap tampil)
        sekretaris_pertandingan.stopwatch.timer("remove");
    }

    // Gunakan off() agar tidak terjadi penumpukan listener saat dipanggil berkali-kali
    sekretaris_pertandingan.stopwatch.off('timerEdited').on('timerEdited', function(e) {
        sekretaris_pertandingan.waktu_tampil = sekretaris_pertandingan.stopwatch.data('seconds');
        if (sekretaris_pertandingan.socket) {
            console.log("Sekretaris Seni manual ubah waktu ke: ", sekretaris_pertandingan.waktu_tampil);
            sekretaris_pertandingan.socket.emit('KONTROL_WAKTU', {
                id_pertandingan: sekretaris_pertandingan.id_penampilan_seni,
                action: sekretaris_pertandingan.status_penampilan,
                waktu: sekretaris_pertandingan.waktu_tampil
            });
        }
    });
  },
  toggle_timer: function () {
    $status_penampilan = $(".btn-toggle-waktu-tampil").data(
      "status-penampilan",
    ); // status penampilan yang akan dituju
    
    // Force a pause instantly to capture the accurate passing time before network request!
    if ($status_penampilan == "berhenti") {
        sekretaris_pertandingan.stopwatch.timer("pause");
    }
    
    $waktu_sekarang = sekretaris_pertandingan.stopwatch.data("seconds");
    if ($waktu_sekarang == null || typeof $waktu_sekarang === "undefined") {
        $waktu_sekarang = sekretaris_pertandingan.waktu_tampil;
    }
    console.log("Sekretaris Seni menginisiasi aksi: " + $status_penampilan + " pada detik: " + $waktu_sekarang);

    $.post(
      "sekretaris-pertandingan/toggle-timer-seni/" +
        sekretaris_pertandingan.id_penampilan_seni,
      { status_penampilan: $status_penampilan, waktu_tampil: $waktu_sekarang },
      function (data, textStatus, jqXHR) {
        if (data.status == true) {
          if ($status_penampilan == "sedang_tampil") {
            /**
             * digunakan saat pertama kali memasuki halaman dan
             */
            sekretaris_pertandingan.stopwatch.timer("remove");
            sekretaris_pertandingan.stopwatch.timer({
              format: "%M:%S",
              action: "start",
              seconds: $waktu_sekarang,
            });
            sekretaris_pertandingan.waktu_tampil = $waktu_sekarang;
            
            $(".btn-toggle-waktu-tampil").data("status-penampilan", "berhenti");
            $(".btn-toggle-waktu-tampil")
              .html("stop")
              .addClass("btn-danger")
              .removeClass("btn-success");
              
          } else if ($status_penampilan == "berhenti") {
            // Timer is already forced paused prior to AJAX
            $(".btn-toggle-waktu-tampil").data("status-penampilan", "sedang_tampil");
            $(".btn-toggle-waktu-tampil")
              .html("start")
              .addClass("btn-success")
              .removeClass("btn-danger");
              
            sekretaris_pertandingan.waktu_tampil = $waktu_sekarang;
          }
          sekretaris_pertandingan.beepAudio.play();
          
          if (sekretaris_pertandingan.socket) {
              console.log("Sekretaris Seni broadcast KONTROL_WAKTU => ", $status_penampilan, "waktu:", sekretaris_pertandingan.waktu_tampil);
              sekretaris_pertandingan.socket.emit('KONTROL_WAKTU', {
                  id_pertandingan: sekretaris_pertandingan.id_penampilan_seni,
                  action: $status_penampilan,
                  waktu: sekretaris_pertandingan.waktu_tampil
              });
          }
        } else {
          // Revert pause if network failed
          if ($status_penampilan == "berhenti") {
              sekretaris_pertandingan.stopwatch.timer("resume");
          }
          Swal.fire("Error", "Failed to start clock, System Error !", "error");
        }
      },
      "json",
    );
  },
  reset_timer: function () {
    Swal.fire({
      title: "Are You Sure ?",
      text: "Clock will be reset to 0 !!!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, Reset",
    }).then((result) => {
      if (result.value) {
        $.post(
          "sekretaris-pertandingan/toggle-timer-seni/" +
            sekretaris_pertandingan.id_penampilan_seni,
          { status_penampilan: "berhenti", waktu_tampil: 0 },
          function (data, textStatus, jqXHR) {
            if (data.status == true) {
              sekretaris_pertandingan.waktu_tampil = 0;
              sekretaris_pertandingan.stopwatch.timer("remove");
              sekretaris_pertandingan.stopwatch.timer({
                format: "%M:%S",
                action: "start",
                seconds: sekretaris_pertandingan.waktu_tampil,
              });
              sekretaris_pertandingan.stopwatch.timer("pause");
              $(".btn-toggle-waktu-tampil").data(
                "status-penampilan",
                "sedang_tampil",
              );
              $(".btn-toggle-waktu-tampil").html("start");

              if (sekretaris_pertandingan.socket) {
                  console.log("Sekretaris Seni broadcast RESET: waktu = 0");
                  sekretaris_pertandingan.socket.emit('KONTROL_WAKTU', {
                      id_pertandingan: sekretaris_pertandingan.id_penampilan_seni,
                      action: "berhenti",
                      waktu: 0
                  });
              }
            } else {
              Swal.fire(
                "Error",
                "Failed to reset clock, System error !",
                "error",
              );
            }
          },
          "json",
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
              window.location.reload();
            } else {
              Swal.fire("Error", data.message, "error");
            }
          },
          "json",
        );
      } else
        /* Read more about handling dismissals below */
        result.dismiss === Swal.DismissReason.cancel;
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
                    true,
                  );
                });
              }, 1000);
            } else if (data.status == true && data.input_medali == true) {
              if (ui.animateOut !== undefined) {
                ui.animateOut();
                setTimeout(() => {
                  ui.animateInNavigasiPartai();
                }, 2000);
              }
              $(".btn_selesai, .button-timer, .btn-diskualifikasi").prop(
                "disabled",
                true,
              );
              setTimeout(() => {
                sekretaris_pertandingan.open_modal_input_juara(
                  sekretaris_pertandingan.id_penampilan_seni,
                );
              }, 4000);
            } else {
              Swal.fire("Error", data.message, "error");
            }
          },
          "json",
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
          window.location.reload();
        } else {
          Swal.fire("error", data.message, "error");
        }
      },
      "json",
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
            "checked",
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
      },
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
              "success",
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
        "JSON",
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
          "json",
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
              $(".btn_selesai, .btn-timer").removeAttr("disabled");
            } else {
              Swal.fire("Error", data.message, "error");
            }

            $(".btn-batal-diskualifikasi").fadeOut("fast", function () {
              $(".btn-diskualifikasi").fadeIn();
            });
          },
          "json",
        );
      }
    });
  },
  open_modal_set_manual_waktu: function () {
    if (sekretaris_pertandingan.status_penampilan == "sedang_tampil") {
      Swal.fire("Error", "Please Stop the clock first", "error");
    } else {
      sekretaris_pertandingan.set_label_modal_atur_manual_waktu(
        sekretaris_pertandingan.waktu_tampil,
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
    sekretaris_pertandingan.status_penampilan = "berhenti";

    // Update state tombol secara manual agar sinkron
    $(".btn-toggle-waktu-tampil").data("status-penampilan", "sedang_tampil");
    $(".btn-toggle-waktu-tampil")
      .html("start")
      .addClass("btn-success")
      .removeClass("btn-danger");

    if (sekretaris_pertandingan.socket) {
        sekretaris_pertandingan.socket.emit('KONTROL_WAKTU', {
            id_pertandingan: sekretaris_pertandingan.id_penampilan_seni,
            action: "berhenti",
            waktu: sekretaris_pertandingan.waktu_tampil
        });
    }

    $.post(
      "sekretaris-pertandingan/toggle-timer-seni/" +
        sekretaris_pertandingan.id_penampilan_seni,
      {
        status_penampilan: "berhenti",
        waktu_tampil: sekretaris_pertandingan.waktu_tampil,
      },
      function (data, textStatus, jqXHR) {
        if (data.status == true) {
          setTimeout(() => {
            waitingDialog.hide();
            sekretaris_pertandingan.set_timer();
          }, 1000);
        } else {
          Swal.fire("Error", "Failed Setting Clock Manually !", "error");
        }
      },
      "json",
    );
  },
  update_manual_waktu_sekarang: function ($el) {
    $waktu_sekarang_terbaru = $el.value;
    sekretaris_pertandingan.waktu_tampil = $waktu_sekarang_terbaru;
    sekretaris_pertandingan.set_label_modal_atur_manual_waktu(
      $waktu_sekarang_terbaru,
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
    $semua_button,
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
            window.location.reload();
          }
        },
        "json",
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
        "json",
      ).always(function () {
        setTimeout(() => {
          sekretaris_pertandingan.refresh_status_seni();
        }, 1000);
      });
    }
  },
};
