const SharedTimer = {
  timer_interval: null,
  waktu_sekarang: 0,
  status: "berhenti", // atau "sedang_tampil"
  selector: ".waktu_tampil",

  init: function (
    waktu_awal,
    status_awal = "berhenti",
    target_selector = ".waktu_tampil"
  ) {
    SharedTimer.waktu_sekarang = waktu_awal;
    SharedTimer.status = status_awal;
    SharedTimer.selector = target_selector;
    SharedTimer.update_display();
  },

  start: function () {
    SharedTimer.status = "sedang_tampil";
    if (SharedTimer.timer_interval) clearInterval(SharedTimer.timer_interval);

    SharedTimer.timer_interval = setInterval(() => {
      SharedTimer.waktu_sekarang++;
      SharedTimer.update_display();
    }, 1000);
  },

  pause: function () {
    SharedTimer.status = "berhenti";
    if (SharedTimer.timer_interval) clearInterval(SharedTimer.timer_interval);
    SharedTimer.update_display();
  },

  reset: function () {
    SharedTimer.status = "berhenti";
    SharedTimer.waktu_sekarang = 0;
    if (SharedTimer.timer_interval) clearInterval(SharedTimer.timer_interval);
    SharedTimer.update_display();
  },

  sync: function (waktu, status) {
    SharedTimer.waktu_sekarang = waktu;
    SharedTimer.status = status;
    if (SharedTimer.timer_interval) clearInterval(SharedTimer.timer_interval);
    if (status === "sedang_tampil") SharedTimer.start();
    else SharedTimer.update_display();
  },

  update_display: function () {
    const menit = Math.floor(SharedTimer.waktu_sekarang / 60)
      .toString()
      .padStart(2, "0");
    const detik = (SharedTimer.waktu_sekarang % 60).toString().padStart(2, "0");
    document.querySelector(
      SharedTimer.selector
    ).textContent = `${menit}:${detik}`;
  },

  get_waktu: function () {
    return SharedTimer.waktu_sekarang;
  },

  get_status: function () {
    return SharedTimer.status;
  },
};
