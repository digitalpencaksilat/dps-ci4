const layar = {
  penampilan_seni_berlangsung: null,
  stopwatch: null,
  status_penampilan: null,
  data_nilai: null,
  init: function ($penampilan_seni_berlangsung, $data_nilai) {
    layar.set_variable($penampilan_seni_berlangsung, $data_nilai);
    layar.status_penampilan = null;
    layar.stopwatch = $(".waktu_tampil");

    console.log("👀 layar.init dipanggil", layar.penampilan_seni_berlangsung);
    if (typeof socket !== "undefined") {
      socket.off("trigger_refresh_seni"); // Hindari listener ganda
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
          if (typeof data.status_penampilan !== "undefined") {
            layar.status_penampilan = data.status_penampilan;
          }
          if (typeof data.waktu_tampil !== "undefined") {
            layar.penampilan_seni_berlangsung.waktu_tampil = data.waktu_tampil;
          }
          if (typeof data.nilai_akhir !== "undefined") {
            layar.penampilan_seni_berlangsung.nilai_akhir = data.nilai_akhir;
          }

          console.log("🔁 trigger cocok, update timer dan nilai");
          layar.update_timer();
          ui.update_tampilan_nilai();
        } else {
          console.log("⚠️ ID tidak cocok, tidak update");
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
    const waktuMs = layar.penampilan_seni_berlangsung.waktu_tampil;

    // Reset runner total
    layar.stopwatch.runner("stop");
    layar.stopwatch.runner("reset");
    layar.stopwatch.html("00:00.000"); // force clear view

    const status =
      layar.status_penampilan ??
      layar.penampilan_seni_berlangsung.status_penampilan;

    console.log("⏱️ Update Timer - Status:", status, "| Waktu:", waktuMs);

    if (status === "sedang_tampil") {
      console.log("▶️ Runner dimulai:", waktuMs);
      layar.stopwatch
        .runner({
          countdown: false,
          startAt: waktuMs,
          milliseconds: true,
          format: "%M:%S:%N",
        })
        .runner("start");
    } else {
      console.log("⏸️ Runner disiapkan (pause):", waktuMs);
      layar.stopwatch.runner({
        countdown: false,
        startAt: waktuMs,
        milliseconds: true,
        format: "%M:%S.%N",
      });
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
        }

        if (
          data.status !== true &&
          typeof data.penampilan_seni_berlangsung !== "undefined"
        ) {
          layar.penampilan_seni_berlangsung.waktu_tampil =
            data.penampilan_seni_berlangsung.waktu_tampil;

          if (
            typeof data.penampilan_seni_berlangsung.status_penampilan !==
            "undefined"
          ) {
            layar.status_penampilan =
              data.penampilan_seni_berlangsung.status_penampilan;
          }

          layar.data_nilai = data.data_nilai;
          ui.update_tampilan_nilai();
          layar.update_timer(); // ⬅️ penting agar waktu tampil ikut berubah
        }
      },
      "json"
    );
  },
};
