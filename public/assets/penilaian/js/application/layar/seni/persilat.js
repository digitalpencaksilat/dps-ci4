const layar = {
  penampilan_seni_berlangsung: null,
  stopwatch: null,
  data_nilai: null,
  init: function ($penampilan_seni_berlangsung, $data_nilai) {
    if (typeof io !== 'undefined') {
      layar.socket = io(typeof SOCKET_URL !== 'undefined' ? SOCKET_URL : 'http://localhost:3000');
    }

    layar.set_variable($penampilan_seni_berlangsung, $data_nilai);
    layar.stopwatch = $(".waktu_tampil");
    
    if (layar.socket) {
        console.log('✅ Layar Seni berhasil inisialisasi socket! Bersiap join room: ', layar.penampilan_seni_berlangsung.id_penampilan_seni);
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
          if (typeof layar.socket === 'undefined' || !layar.socket.connected) {
              layar.update_timer();
          }
        }
      },
      "json",
    ).always(function () {
      setTimeout(() => {
        layar.refresh_status_seni();
      }, 1000);
    });
  },
};
