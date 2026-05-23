const layar = {
  penampilan_seni_berlangsung: null,
  stopwatch: null,
  data_nilai: null,
  init: function ($penampilan_seni_berlangsung, $data_nilai) {
    layar.set_variable($penampilan_seni_berlangsung, $data_nilai);
    layar.stopwatch = $(".waktu_tampil");
    ui.update_tampilan_nilai();
    layar.update_timer();
    layar.refresh_status_seni();
    ui.start_animation();
  },
  set_variable: function ($penampilan_seni_berlangsung, $data_nilai) {
    layar.penampilan_seni_berlangsung = $penampilan_seni_berlangsung;
    layar.data_nilai = $data_nilai;
  },
  update_timer: function () {
    layar.stopwatch.timer({
      format: "%M:%S",
      action: "start",
      seconds: layar.penampilan_seni_berlangsung.waktu_tampil,
    });
    if (
      layar.penampilan_seni_berlangsung.status_penampilan !== "sedang_tampil"
    ) {
      layar.stopwatch.timer("remove");
    }
  },
  refresh_status_seni: () => {
    $.post(
      "layar/refresh-status-seni/" +
        layar.penampilan_seni_berlangsung.id_penampilan_seni,
      function (data, textStatus, jqXHR) {
        if (
          (data.status === true && data.reload === true) ||
          layar.penampilan_seni_berlangsung.format_penilaian !==
            data.penampilan_seni_berlangsung.format_penilaian ||
          layar.data_nilai[layar.penampilan_seni_berlangsung.id_penampilan_seni]
            .length !==
            data.data_nilai[
              layar.penampilan_seni_berlangsung.id_penampilan_seni
            ].length
        ) {
          setTimeout(() => {
            location.reload();
          }, 5000);
        } else if (
          data.status === false &&
          typeof data.penampilan_seni_berlangsung !== "undefined"
        ) {
          layar.set_variable(data.penampilan_seni_berlangsung, data.data_nilai);
          ui.update_tampilan_nilai();
          layar.update_timer();
        }
      },
      "json"
    ).always(function () {
      setTimeout(() => {
        layar.refresh_status_seni();
      }, 1000);
    });
  },
};
