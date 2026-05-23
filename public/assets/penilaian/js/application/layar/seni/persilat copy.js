const layar = {
  penampilan_seni_berlangsung: null,
  stopwatch: null,
  data_nilai: null,
  init: function ($penampilan_seni_berlangsung, $data_nilai) {
    layar.set_variable($penampilan_seni_berlangsung, $data_nilai);
    layar.stopwatch = $(".waktu_tampil");

    console.log("👀 layar.init dipanggil", layar.penampilan_seni_berlangsung);
    if (typeof socket !== "undefined") {
      // Kirim identitas perangkat ke server
      const identitas = {
        role: "Layar",
        nama_perangkat: nama_perangkat,
        nomor_partai: nomor_partai,
      };
      socket.emit("identitas_perangkat", identitas);
      console.log("📡 Identitas perangkat dikirim:", identitas);
      const roomId =
        "seni_" + layar.penampilan_seni_berlangsung.id_penampilan_seni;
      socket.emit(
        "gabung_seni",
        layar.penampilan_seni_berlangsung.id_penampilan_seni
      );
      console.log("📺 Layar gabung ke room:", roomId);

      // ✅ Listen trigger refresh dari sekretaris
      socket.on("trigger_refresh_seni", function (data) {
        console.log("📥 trigger_refresh_seni diterima di layar:", data);

        if (
          data.id_penampilan_seni ==
          layar.penampilan_seni_berlangsung.id_penampilan_seni
        ) {
          console.log("🔁 trigger cocok, memanggil refresh_status_seni()");
          layar.refresh_status_seni();
        } else {
          console.log("⚠️ ID tidak cocok, tidak refresh");
        }
      });
    } else {
      console.warn("⚠️ socket tidak tersedia!");
    }

    // ✅ Refresh data pertama kali langsung saat halaman dimuat
    layar.refresh_status_seni();

    // ✅ Fallback: polling ringan setiap 5 detik untuk menjaga sinkronisasi
    // setInterval(() => {
    //   console.log("🔄 Polling refresh_status_seni fallback");
    //   layar.refresh_status_seni();
    // }, 5000);
    ui.update_tampilan_nilai();
    layar.update_timer();
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
      // setTimeout(() => {
      //   layar.refresh_status_seni();
      // }, 1000);
    });
  },
};
