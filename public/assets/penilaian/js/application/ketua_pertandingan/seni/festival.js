const ketua_pertandingan = {
  id_penampilan_seni_berlangsung: null,
  penampilan_seni_berlangsung: null,
  penilaian_terpilih: null, // berformat array, berisi id_perangkat_pertandingan yang terpilih
  init: function (
    $id_penampilan_seni,
    $data_nilai,
    $penampilan_seni_berlangsung,
    $autorefresh = true
  ) {
    /**
     * @params object $data_nilai
     * $data_nilai adalah hasil query result()
     * dari kolom penilaian_seni,
     * dapat berisi 3 record untuk 3 juri maupun 5 record
     * untuk 5 juri
     */
    ketua_pertandingan.id_penampilan_seni_berlangsung = $id_penampilan_seni;
    ketua_pertandingan.penampilan_seni_berlangsung =
      $penampilan_seni_berlangsung;
    ketua_pertandingan.update_tampilan_nilai($data_nilai);
    console.log("Autorefresh:", $autorefresh, typeof $autorefresh); // Tambahkan ini
    if ($autorefresh == true) {
      ketua_pertandingan.refresh_status_seni();
    }

    if ($(".tabel_ketua_pertandingan").length != 0) {
      $(".tabel_ketua_pertandingan").DataTable({
        language: {
          paginate: {
            next: ">",
            previous: "<",
          },
        },
        paging: true,
        searching: false,
        ordering: true,
        pageLength: 5,
        info: true,
        responsive: true,
        columnDefs: [
          {
            className: "dt-center",
            targets: "_all",
          },
        ],
        dom: "Bfrtip",
        buttons: ["copy", "csv", "excel", "pdf", "print"],
      });
    }
  },
  diskualifikasi_peserta: function () {
    Swal.fire(
      "info",
      "Diskualifikasi peserta akan dilakukan oleh sekretaris pertandingan",
      "info"
    );
  },
  update_tampilan_nilai: function ($data_nilai) {
    $waktu_tampil = parseInt(
      ketua_pertandingan.penampilan_seni_berlangsung.waktu_tampil
    );
    $menit = parseInt($waktu_tampil / 60);
    $detik = parseInt($waktu_tampil % 60);
    $waktu_tampil =
      $menit.toString().padStart(2, "0") +
      ":" +
      $detik.toString().padStart(2, "0");

    $(
      ".waktu_tampil_" +
        ketua_pertandingan.penampilan_seni_berlangsung.id_penampilan_seni
    ).html($waktu_tampil);
    $(
      ".nilai_akhir_" +
        ketua_pertandingan.penampilan_seni_berlangsung.id_penampilan_seni
    ).html(ketua_pertandingan.penampilan_seni_berlangsung.nilai_akhir);

    if (ketua_pertandingan.penampilan_seni_berlangsung.diskualifikasi == 1) {
      $(
        ".keterangan_" +
          ketua_pertandingan.penampilan_seni_berlangsung.id_penampilan_seni
      ).html('<span class="badge badge-danger">Diskualifikasi</span>');
    } else {
      $(
        ".keterangan_" +
          ketua_pertandingan.penampilan_seni_berlangsung.id_penampilan_seni
      ).html(" ");
    }

    ketua_pertandingan.update_unsur_nilai($data_nilai);
    ketua_pertandingan.update_jenis_hukuman($data_nilai);

    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
      $("tbody tr td").removeClass("bg-gradient-180-red  text-white");
      $.each(penampilan_seni, function (index_juri, penilaian_juri) {
        $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;

        //update nilai akhir per juri
        $(
          ".penampilan_seni_" +
            id_penampilan_seni +
            " .nilai_akhir_juri_" +
            penilaian_juri.id_perangkat_pertandingan
        ).html($penilaian.ringkasan.nilai_akhir);
      });
    });

    ketua_pertandingan.pilih_penilaian_juri($data_nilai);
  },
  pilih_penilaian_juri: function ($data_nilai) {
    /**
     * fungsi ini digunakan untuk memberikan penanda / highlight pada penilaian juri yang dipilih
     * atau tidak dicoret dalam sistem 5 wasit juri
     */
    $("tbody tr").removeClass(
      "bg-gradient-180-red  text-white text-decoration-line-through"
    );

    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function (index_juri, penilaian_juri) {
        if (penampilan_seni.length == 3) {
          //untuk sistem 3 wasit juri, yang di highliht hanya penampilan berlangsung
          $(
            "tbody tr.penampilan_seni_" +
              ketua_pertandingan.penampilan_seni_berlangsung,
            id_penampilan_seni
          ).addClass("bg-gradient-180-red  text-white");
        } else {
          $.each(penampilan_seni, function (index_juri, penilaian_juri) {
            if (
              penilaian_juri.terpilih == 1 &&
              id_penampilan_seni ==
                ketua_pertandingan.penampilan_seni_berlangsung
                  .id_penampilan_seni
            ) {
              $(
                "tbody tr.penampilan_seni_" +
                  id_penampilan_seni +
                  " .juri_" +
                  penilaian_juri.id_perangkat_pertandingan
              ).addClass("bg-gradient-180-red  text-white");
            } else if (
              penilaian_juri.terpilih == 0 &&
              id_penampilan_seni !==
                ketua_pertandingan.penampilan_seni_berlangsung
                  .id_penampilan_seni
            ) {
              //ketika bukan juri terpilih, dan penampilan sedang TIDAK BERLANGSUNG
              $(
                "tbody tr.penampilan_seni_" +
                  id_penampilan_seni +
                  " .juri_" +
                  penilaian_juri.id_perangkat_pertandingan
              ).addClass("text-decoration-line-through");
            } else {
            }
          });
        }
      });
    });
  },
  update_unsur_nilai: function ($data_nilai) {
    /**
     * fungsi ini digunakan pada tahap perhitungan nilai sama
     */
    $total_nilai = new Object();

    //MEMBUAT WADAH ARRAY
    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
      $total_nilai[id_penampilan_seni] = new Object();
      $.each(penampilan_seni, function (index_juri, penilaian_juri) {
        $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
        $.each(
          $penilaian.unsur_nilai,
          function (jenis_unsur_nilai, unsur_nilai) {
            $(
              ".penampilan_seni_" +
                id_penampilan_seni +
                " ." +
                jenis_unsur_nilai +
                "_juri_" +
                penilaian_juri.id_perangkat_pertandingan
            ).html(unsur_nilai.nilai_diperoleh);
            $total_nilai[id_penampilan_seni][jenis_unsur_nilai] = 0;
          }
        );
      });
    });

    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function (index_juri, penilaian_juri) {
        $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
        $.each(
          $penilaian.unsur_nilai,
          function (jenis_unsur_nilai, unsur_nilai) {
            if (penilaian_juri.terpilih == 1) {
              $total_nilai[id_penampilan_seni][jenis_unsur_nilai] +=
                unsur_nilai.nilai_diperoleh;
            }
          }
        );
      });
    });

    $.each($total_nilai, function (id_penampilan_seni, unsur_nilai) {
      $.each(unsur_nilai, function (jenis_unsur_nilai, nilai_diperoleh) {
        $(
          ".penampilan_seni_" +
            id_penampilan_seni +
            " .total_nilai_" +
            jenis_unsur_nilai
        ).html(nilai_diperoleh);
      });
    });
  },
  update_jenis_hukuman: function ($data_nilai) {
    /**
     * fungsi ini digunakan pada tahap perhitungan nilai sama
     */
    $total_hukuman = new Object();
    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
      $total_hukuman[id_penampilan_seni] = {
        rincian_hukuman: {},
        total_hukuman: 0,
      };

      $.each(penampilan_seni, function (index_juri, penilaian_juri) {
        $jenis_hukuman = JSON.parse(penilaian_juri.penilaian).penilaian.hukuman;
        $.each($jenis_hukuman, function (nama_hukuman, data_hukuman) {
          if (
            $total_hukuman[id_penampilan_seni]["rincian_hukuman"][
              nama_hukuman
            ] == undefined
          ) {
            $total_hukuman[id_penampilan_seni]["rincian_hukuman"][
              nama_hukuman
            ] = 0;
          }
          $total_hukuman[id_penampilan_seni]["rincian_hukuman"][nama_hukuman] +=
            data_hukuman.detail_hukuman.nilai_hukuman;
        });
        $total_hukuman[id_penampilan_seni]["total_hukuman"] += JSON.parse(
          penilaian_juri.penilaian
        ).penilaian.ringkasan.total_hukuman;
      });
    });

    $.each($total_hukuman, function (id_penampilan_seni, data_hukuman) {
      $.each(
        data_hukuman["rincian_hukuman"],
        function (nama_hukuman, nilai_hukuman) {
          $(
            ".penampilan_seni_" +
              id_penampilan_seni +
              " .hukuman_" +
              nama_hukuman
          ).html(nilai_hukuman);
        }
      );
      $(".penampilan_seni_" + id_penampilan_seni + " .total_hukuman").html(
        data_hukuman["total_hukuman"]
      );
    });
  },
  refresh_status_seni: function () {
    $.post(
      "ketua-pertandingan/refresh-status-seni/" +
        ketua_pertandingan.id_penampilan_seni_berlangsung,
      function (data, textStatus, jqXHR) {
        if (data.status === true && data.reload === true) {
          location.reload();
        } else if (
          data.status === false &&
          typeof data.data_nilai !== "undefined"
        ) {
          ketua_pertandingan.id_penampilan_seni_berlangsung =
            data.penampilan_seni_berlangsung.id_penampilan_seni;
          ketua_pertandingan.penampilan_seni_berlangsung =
            data.penampilan_seni_berlangsung;
          ketua_pertandingan.update_tampilan_nilai(data.data_nilai);
        } else {
          $("tbody tr").removeClass(
            "bg-gradient-180-red text-white text-decoration-line-through"
          );
        }
      },
      "json"
    ).always(function () {
      setTimeout(() => {
        ketua_pertandingan.refresh_status_seni();
      }, 1000);
    });
  },
};
