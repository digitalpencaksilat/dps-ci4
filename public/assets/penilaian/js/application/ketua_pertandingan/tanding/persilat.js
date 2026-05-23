const ketua_pertandingan = {
  data_nilai: null,
  pertandingan: null,
  id_pertandingan: null,
  ronde_pertandingan: null,
  data_waktu: null,
  waktu_sekarang: 1,
  stopwatch: null,
  verifikasi_pertandingan: null, // hasil query find
  ringkasan_nilai: null,
  ringkasan_pembagi_nilai: {
    merah: {
      1: 0,
      2: 0,
    }, // sudut : [pukulan, tendangan],
    biru: {
      1: 0,
      2: 0,
    }, // sudut : [pukulan, tendangan]
  },
  ringkasan_total_nilai_per_ronde: {
    merah: {
      1: 0,
      2: 0,
      3: 0,
    }, // sudut : [ronde, ronde, ronde],
    biru: {
      1: 0,
      2: 0,
      3: 0,
    }, // sudut : [ronde, ronde, ronde]
  },
  ringkasan_hukuman_per_ronde: {
    merah: {
      1: 0,
      2: 0,
      3: 0,
    }, // sudut : [ronde, ronde, ronde],
    biru: {
      1: 0,
      2: 0,
      3: 0,
    }, // sudut : [ronde, ronde, ronde]
  },
  ringkasan_nilai_akhir_per_ronde: {
    merah: {
      1: 0,
      2: 0,
      3: 0,
    }, // sudut : [ronde, ronde, ronde],
    biru: {
      1: 0,
      2: 0,
      3: 0,
    }, // sudut : [ronde, ronde, ronde]
  },
  ringkasan_total_nilai_semua_juri_semua_ronde: {
    merah: 0,
    biru: 0,
  }, //semua juri semua ronde
  ringkasan_total_hukuman_semua_juri_semua_ronde: {
    merah: 0,
    biru: 0,
  }, //semua juri semua ronde
  ringkasan_nilai_akhir_semua_juri_semua_ronde: {
    merah: 0,
    biru: 0,
  }, //semua juri semua ronde
  init: function (
    $data_nilai,
    $pertandingan,
    $verifikasi_pertandingan_berlangsung,
    $riwayat_verifikasi_pertandingan,
    $jawaban_riwayat_verifikasi_pertandingan,
    $read_only = true,
  ) {
    ketua_pertandingan.set_variable(
      $data_nilai,
      $pertandingan,
      $verifikasi_pertandingan_berlangsung,
      $riwayat_verifikasi_pertandingan,
      $jawaban_riwayat_verifikasi_pertandingan,
    );
    ketua_pertandingan.update_tampilan_nilai();
    ketua_pertandingan.update_timer();
    if ($read_only == false) {
      ketua_pertandingan.refresh_status_pertandingan();
    }
  },
  set_variable: function (
    $data_nilai,
    $pertandingan,
    $verifikasi_pertandingan_berlangsung,
    $riwayat_verifikasi_pertandingan,
    jawaban_riwayat_verifikasi_pertandingan,
  ) {
    ketua_pertandingan.data_nilai = $data_nilai;
    ketua_pertandingan.pertandingan = $pertandingan;
    ketua_pertandingan.verifikasi_pertandingan_berlangsung =
      $verifikasi_pertandingan_berlangsung;
    ketua_pertandingan.riwayat_verifikasi_pertandingan =
      $riwayat_verifikasi_pertandingan;
    ketua_pertandingan.jawaban_riwayat_verifikasi_pertandingan =
      $jawaban_riwayat_verifikasi_pertandingan;
    ketua_pertandingan.id_pertandingan = $pertandingan.id_pertandingan;
    ketua_pertandingan.ronde_pertandingan = $pertandingan.ronde_pertandingan;
    if ($pertandingan.ringkasan_nilai !== undefined) {
      ketua_pertandingan.ringkasan_nilai = JSON.parse(
        $pertandingan.ringkasan_nilai,
      );
    }

    ketua_pertandingan.stopwatch = $(".stopwatch");
    ketua_pertandingan.ronde_sekarang = $pertandingan.ronde_pertandingan;
    ketua_pertandingan.waktu_sekarang =
      $pertandingan.data_waktu[ketua_pertandingan.ronde_sekarang][1];
  },
  update_tampilan_nilai: function () {
    // BLOK KODE UNTUK MENAMPILKAN RINCIAN NILAI
    $.each(
      ketua_pertandingan.data_nilai["juri"],
      function (key, perangkat_pertandingan) {
        $nilai_akhir = { merah: 0, biru: 0 };
        $.each(
          perangkat_pertandingan.penilaian_tanding,
          function (key_sudut, nilai_sudut) {
            $rincian_nilai_semua_ronde = "";
            $rincian_jatuhan_semua_ronde = "";
            $rincian_hukuman_semua_ronde = "";

            $.each(
              nilai_sudut.ronde_pertandingan,
              function (index_ronde, nilai_ronde) {
                let jumlah_rincian_nilai = nilai_ronde["rincian"].length;

                // Mereset nilai per babak
                $(
                  "." +
                    perangkat_pertandingan.id_perangkat_pertandingan +
                    "-nilai-" +
                    key_sudut +
                    "-" +
                    index_ronde,
                ).empty();
                $(
                  "." +
                    perangkat_pertandingan.id_perangkat_pertandingan +
                    "-hukuman-" +
                    key_sudut +
                    "-" +
                    index_ronde,
                ).empty();

                $rincian_nilai_per_ronde = "";
                $rincian_jatuhan_per_ronde = "";
                $rincian_hukuman_per_ronde = "";

                if (jumlah_rincian_nilai > 0) {
                  $.each(nilai_ronde["rincian"], function (index, nilai) {
                    $timestamp = ketua_pertandingan.get_timestamp(
                      nilai["timestamp"],
                    );

                    // Check if the entry is soft-deleted
                    if (nilai.is_deleted === true) {
                      // For soft-deleted entries
                      if (nilai["nilai"] > 0 && nilai["nilai"] < 3) {
                        // Pukulan dan tendangan
                        $rincian_nilai_per_ronde +=
                          '<span class="fw-lighter text-decoration-line-through px-2 py-1 d-inline-block" data-bs-toggle="popover" data-bs-placement="top" title="deleted at ' +
                          ketua_pertandingan.get_timestamp(nilai.deleted_at) +
                          '" style="color: #999999 !important;">' +
                          nilai["nilai"] +
                          "</span>";
                      } else if (nilai["nilai"] == 3) {
                        // Jatuhan
                        $rincian_jatuhan_per_ronde +=
                          '<span class="fw-lighter text-decoration-line-through px-2 py-1 d-inline-block" data-bs-toggle="popover" data-bs-placement="top" title="deleted at ' +
                          ketua_pertandingan.get_timestamp(nilai.deleted_at) +
                          '" style="color: #999999 !important;">' +
                          nilai["nilai"] +
                          "</span>";
                      } else if (nilai["nilai"] < 0) {
                        // Hukuman
                        $rincian_hukuman_per_ronde +=
                          '<span class="fw-lighter text-decoration-line-through px-2 py-1 d-inline-block" data-bs-toggle="popover" data-bs-placement="top" title="deleted at ' +
                          ketua_pertandingan.get_timestamp(nilai.deleted_at) +
                          '" style="color: #999999 !important;">' +
                          nilai["nilai"] +
                          "</span>";
                      }
                      return true; // Skip rest of processing for deleted entries
                    }

                    if (nilai["nilai"] > 0 && nilai["nilai"] < 3) {
                      // Hanya menampilkan poin pukulan dan tendangan
                      // DEVELOPMENT ONLY
                      if (
                        nilai["status"] == "input" ||
                        (nilai["status"] == "verified" &&
                          nilai["warna"] == null)
                      ) {
                        $rincian_nilai_per_ronde +=
                          '<span class="fw-lighter text-decoration-line-through px-2 py-1 d-inline-block" data-bs-toggle="popover" data-bs-placement="top" title="' +
                          nilai["status"] +
                          " == " +
                          nilai["id_nilai"] +
                          " - " +
                          $timestamp +
                          '">' +
                          nilai["nilai"] +
                          "</span>";
                      } else {
                        $rincian_nilai_per_ronde +=
                          '<span class="text-white px-2 py-1 d-inline-block" style="background-color:' +
                          nilai["warna"] +
                          '" data-bs-toggle="popover" data-bs-placement="top" title="' +
                          nilai["status"] +
                          " == " +
                          nilai["id_nilai"] +
                          " - " +
                          $timestamp +
                          '" >' +
                          nilai["nilai"] +
                          "</span>";
                      }
                    } else if (nilai["nilai"] == 3) {
                      // Hanya menampilkan poin Jatuhan
                      if (
                        nilai["status"] == "input" ||
                        (nilai["status"] == "verified" &&
                          nilai["warna"] == null)
                      ) {
                        $rincian_jatuhan_per_ronde +=
                          '<span class="fw-lighter text-decoration-line-through px-2 py-1 d-inline-block" data-bs-toggle="popover" data-bs-placement="top" title="' +
                          nilai["status"] +
                          " == " +
                          nilai["id_nilai"] +
                          " - " +
                          $timestamp +
                          '">' +
                          nilai["nilai"] +
                          "</span>";
                      } else {
                        $rincian_jatuhan_per_ronde +=
                          '<span class="text-white px-2 py-1 d-inline-block" style="background-color:' +
                          nilai["warna"] +
                          '" data-bs-toggle="popover" data-bs-placement="top" title="' +
                          nilai["status"] +
                          " == " +
                          nilai["id_nilai"] +
                          " - " +
                          $timestamp +
                          '" >' +
                          nilai["nilai"] +
                          "</span>";
                      }
                    } else if (nilai["nilai"] < 0) {
                      $rincian_hukuman_per_ronde +=
                        '<span class="fw-lighter  px-2 py-1 d-inline-block">' +
                        nilai["nilai"] +
                        "</span>";
                    }
                  });
                }

                $rincian_nilai_semua_ronde += $rincian_nilai_per_ronde;
                $rincian_jatuhan_semua_ronde += $rincian_jatuhan_per_ronde;
                $rincian_hukuman_semua_ronde += $rincian_hukuman_per_ronde;

                //Untuk Nilai PERBABAK
                $(
                  ".ronde-" +
                    index_ronde +
                    "-" +
                    "juri-" +
                    perangkat_pertandingan.id_perangkat_pertandingan +
                    "-nilai-" +
                    key_sudut,
                ).html($rincian_nilai_per_ronde);
                $(
                  ".ronde-" +
                    index_ronde +
                    "-" +
                    key_sudut +
                    "-rincian-nilai-jatuhan",
                ).html($rincian_jatuhan_per_ronde);
                $(
                  ".ronde-" +
                    index_ronde +
                    "-" +
                    key_sudut +
                    "-rincian-nilai-hukuman",
                ).html($rincian_hukuman_per_ronde);
                $(
                  ".ronde-" +
                    index_ronde +
                    "-" +
                    "juri-" +
                    perangkat_pertandingan.id_perangkat_pertandingan +
                    "-nilai-akhir-" +
                    key_sudut,
                ).html(
                  '<span class="text-white">' +
                    nilai_ronde["ringkasan"]["nilai_akhir"] +
                    "</span>",
                );
              },
            );

            // Nilai semua babak (tampilan utama KP khusus penilaian 2022)
            $(
              ".juri-" +
                perangkat_pertandingan.id_perangkat_pertandingan +
                "-nilai-" +
                key_sudut,
            )
              .empty()
              .append($rincian_nilai_semua_ronde);
            $(".semua-ronde-" + key_sudut + "-rincian-nilai-jatuhan")
              .empty()
              .html($rincian_jatuhan_semua_ronde);
            $(".semua-ronde-" + key_sudut + "-rincian-nilai-hukuman")
              .empty()
              .html($rincian_hukuman_semua_ronde);
          },
        );
      },
    );
    // END OF BLOK KODE UNTUK MENAMPILKAN RINCIAN NILAI

    // BLOK KODE UNTUK RINCIAN NILAI TERVERIFIKASI
    $.each(
      ketua_pertandingan.data_nilai["penilaian_verified"],
      function (sudut, penilaian) {
        $("." + sudut + "-nilai-verified").empty();
        $rincian_nilai = {
          semua_ronde: [],
          per_ronde: { 1: {}, 2: {}, 3: {} },
        };

        $.each(penilaian, function (i, isi_nilai) {
          if (
            isi_nilai[0]["entry_nilai"]["nilai"] > 0 &&
            isi_nilai[0]["entry_nilai"]["nilai"] < 3
          ) {
            $rincian_nilai["semua_ronde"].push(
              '<span class="text-white px-2 py-1 d-inline-block" style="background-color:' +
                isi_nilai[0]["entry_nilai"]["warna"] +
                '">' +
                isi_nilai[0]["entry_nilai"]["nilai"] +
                "</span>",
            );

            $rincian_nilai["per_ronde"][isi_nilai[0]["ronde"]][i] =
              '<span class="text-white px-2 py-1 d-inline-block" style="background-color:' +
              isi_nilai[0]["entry_nilai"]["warna"] +
              '">' +
              isi_nilai[0]["entry_nilai"]["nilai"] +
              "</span>";
          }
        });

        $.each(
          $rincian_nilai["per_ronde"],
          function (ronde, kumpulan_nilai_per_ronde) {
            $(".ronde-" + ronde + "-" + sudut + "-nilai-verified").empty();
            $.each(kumpulan_nilai_per_ronde, function (i, nilai) {
              $(".ronde-" + ronde + "-" + sudut + "-nilai-verified").append(
                nilai,
              );
            });
          },
        );

        $(".semua-ronde-" + sudut + "-nilai-verified").empty();
        $.each($rincian_nilai["semua_ronde"], function (i, nilai) {
          $(".semua-ronde-" + sudut + "-nilai-verified").append(nilai);
        });
      },
    );
    // END OF BLOK KODE UNTUK RINCIAN NILAI TERVERIFIKASI

    //BLOK KODE UNTUK MENAMPILKAN RINGKASAN NILAI PER RONDE
    $.each(
      ketua_pertandingan.data_nilai["ringkasan"]["per_ronde"],
      function (index_ronde, nilai_per_ronde) {
        $.each(nilai_per_ronde, function (index_sudut, nilai_sudut) {
          // Set Total Nilai Sah Pukulan Tendangan Per Ronde
          $pukulan_tendangan =
            nilai_sudut["pukulan"] + nilai_sudut["tendangan"] * 2;
          $(
            ".ronde-" + index_ronde + "-" + index_sudut + "-pukulan-tendangan",
          ).html($pukulan_tendangan);

          // Set Total Nilai Sah Jatuhan Per Ronde
          $jatuhan = nilai_sudut["jatuhan"] * 3;
          $(
            ".ronde-" + index_ronde + "-" + index_sudut + "-total-jatuhan",
          ).html($jatuhan);

          // Set Hukuman
          $hukuman =
            nilai_sudut["teguran_1"] * -1 +
            nilai_sudut["teguran_2"] * -2 +
            nilai_sudut["peringatan_1"] * -5 +
            nilai_sudut["peringatan_2"] * -10;
          $(
            ".ronde-" + index_ronde + "-" + index_sudut + "-total-hukuman",
          ).html($hukuman);

          $(".ronde-" + index_ronde + "-" + index_sudut + "-nilai-akhir").html(
            nilai_sudut["nilai_akhir"],
          );
        });
      },
    );
    //END BLOK KODE UNTUK MENAMPILKAN RINGKASAN NILAI PER RONDE

    // BLOK KODE NILAI BINAAN PER RONDE DAN SEMUA RONDE
    $(".semua-ronde-biru-binaan").empty();
    $(".semua-ronde-merah-binaan").empty();
    $.each(
      ketua_pertandingan.data_nilai["ringkasan"]["per_ronde"],
      function (index_ronde, nilai_per_ronde) {
        $.each(nilai_per_ronde, function (index_sudut, nilai_sudut) {
          // Binaan

          $(".ronde-" + index_ronde + "-" + index_sudut + "-binaan").empty();

          if (nilai_sudut["binaan_1"] !== 0) {
            $(".ronde-" + index_ronde + "-" + index_sudut + "-binaan").append(
              '<span class="me-3 badge bg-warning px-2 py-1 text-white">Binaan 1</span>',
            );
            $(".semua-ronde-" + index_sudut + "-binaan").append(
              '<span class="me-3 badge bg-warning px-2 py-1 text-white">Ronde ' +
                index_ronde +
                " - Binaan 1</span>",
            );
          }

          if (nilai_sudut["binaan_2"] !== 0) {
            $(".ronde-" + index_ronde + "-" + index_sudut + "-binaan").append(
              '<span class="me-3 badge bg-warning px-2 py-1 text-white">Binaan 2</span>',
            );
            $(".semua-ronde-" + index_sudut + "-binaan").append(
              '<span class="me-3 badge bg-warning px-2 py-1 text-white">Ronde ' +
                index_ronde +
                " - Binaan 2</span>",
            );
          }
          // End Of Binaan
        });
      },
    );
    // END OF BLOK KODE NILAI BINAAN PER RONDE DAN SEMUA RONDE

    // Set Pukulan Tendangan Semua Ronde
    $.each(
      ketua_pertandingan.data_nilai["ringkasan"]["semua_ronde"],
      function (index_sudut, nilai_sudut) {
        // Set Total Nilai Sah Pukulan Tendangan Semua Ronde
        $pukulan_tendangan =
          nilai_sudut["pukulan"] + nilai_sudut["tendangan"] * 2;
        $(".semua-ronde-" + index_sudut + "-pukulan-tendangan").html(
          $pukulan_tendangan,
        );

        // Set Total Nilai Sah Jatuhan Semua Ronde
        $jatuhan = nilai_sudut["jatuhan"] * 3;
        $(".semua-ronde-" + index_sudut + "-total-jatuhan").html($jatuhan);

        // Set Hukuman
        $hukuman =
          nilai_sudut["teguran_1"] * -1 +
          nilai_sudut["teguran_2"] * -2 +
          nilai_sudut["peringatan_1"] * -5 +
          nilai_sudut["peringatan_2"] * -10;
        $(".semua-ronde-" + index_sudut + "-total-hukuman").html($hukuman);
      },
    );

    $("#skor_merah, .skor_merah").html(
      ketua_pertandingan.pertandingan.skor_merah,
    );
    $("#skor_biru, .skor_biru").html(ketua_pertandingan.pertandingan.skor_biru);
    $(".ronde_pertandingan").html(
      "Round " + ketua_pertandingan.ronde_pertandingan,
    );

    ketua_pertandingan.highlight_nilai_akhir();

    // BLOK KODE UNTUK MENGUNCI TOMBOL HUKUMAN
    $.each(
      ketua_pertandingan.data_nilai["ringkasan"]["per_ronde"][
        ketua_pertandingan.ronde_pertandingan
      ],
      function (index_sudut, nilai_sudut) {
        // Set Hukuman
        $(".btn-input-hukuman-" + index_sudut).addClass("opacity-3");
        if (nilai_sudut["teguran_1"] == 1) {
          $(".btn-teguran-1-" + index_sudut).removeClass("opacity-3");
          $(".btn-teguran-1-" + index_sudut).off("click");

          if (nilai_sudut["teguran_2"] == 0) {
            // Perbolehkan hapus teguran 1 jika teguran 2 belum diinput
            $(".btn-teguran-1-" + index_sudut).on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "teguran_1",
                "hapus",
                this,
              );
            });
          }
        } else {
          //JIKA teguran 1 OFF
          $(".btn-teguran-1-" + index_sudut).addClass("opacity-3");
          $(".btn-teguran-1-" + index_sudut)
            .off("click")
            .on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "hukuman",
                -1,
                this,
              );
            });
        }

        if (nilai_sudut["teguran_2"] == 1) {
          $(".btn-teguran-2-" + index_sudut).removeClass("opacity-3");
          $(".btn-teguran-2-" + index_sudut)
            .off("click")
            .on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "teguran_2",
                "hapus",
                this,
              );
            });
        } else {
          //JIKA teguran 1 OFF KUNCI AKSES TEGURAN 2
          $(".btn-teguran-2-" + index_sudut).addClass("opacity-3");
          $(".btn-teguran-2-" + index_sudut).off("click");
          if (nilai_sudut["teguran_1"] == 1) {
            // Perbolehkan input ketika teguran 1 juga telah terinput
            $(".btn-teguran-2-" + index_sudut).on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "hukuman",
                -2,
                this,
              );
            });
          }
        }

        // BINAAN
        if (nilai_sudut["binaan_1"] == 1) {
          $(".btn-binaan-1-" + index_sudut).removeClass("opacity-3");
          $(".btn-binaan-1-" + index_sudut).off("click");

          if (nilai_sudut["binaan_2"] == 0) {
            // Perbolehkan hapus binaan 1 jika binaan 2 belum diinput
            $(".btn-binaan-1-" + index_sudut).on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "binaan_1",
                "hapus",
                this,
              );
            });
          }
        } else {
          //JIKA binaan 1 OFF
          $(".btn-binaan-1-" + index_sudut).addClass("opacity-3");
          $(".btn-binaan-1-" + index_sudut)
            .off("click")
            .on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "binaan",
                1,
                this,
              );
            });
        }

        if (nilai_sudut["binaan_2"] == 1) {
          $(".btn-binaan-2-" + index_sudut).removeClass("opacity-3");
          $(".btn-binaan-2-" + index_sudut)
            .off("click")
            .on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "binaan_2",
                "hapus",
                this,
              );
            });
        } else {
          //JIKA binaan 1 OFF KUNCI AKSES binaan 2
          $(".btn-binaan-2-" + index_sudut).addClass("opacity-3");
          $(".btn-binaan-2-" + index_sudut).off("click");
          if (nilai_sudut["binaan_1"] == 1) {
            // Perbolehkan input ketika binaan 1 juga telah terinput
            $(".btn-binaan-2-" + index_sudut).on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "binaan",
                2,
                this,
              );
            });
          }
        }
        // END OF BLOK binaan
      },
    );

    // KHUSUS PERINGATAN DI AKUMULASI PADA SEMUA RONDE
    $.each(
      ketua_pertandingan.data_nilai["ringkasan"]["semua_ronde"],
      function (index_sudut, nilai_sudut) {
        // BLOCK PERINGATAN
        if (nilai_sudut["peringatan_1"] == 1) {
          $(".btn-peringatan-1-" + index_sudut).removeClass("opacity-3");
          $(".btn-peringatan-1-" + index_sudut).off("click");

          if (nilai_sudut["peringatan_2"] == 0) {
            // Perbolehkan hapus peringatan 1 jika peringatan 2 belum diinput
            $(".btn-peringatan-1-" + index_sudut).on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "peringatan_1",
                "hapus",
                this,
              );
            });
          }
        } else {
          //JIKA peringatan 1 OFF
          $(".btn-peringatan-1-" + index_sudut).addClass("opacity-3");
          $(".btn-peringatan-1-" + index_sudut)
            .off("click")
            .on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "hukuman",
                -5,
                this,
              );
            });
        }

        if (nilai_sudut["peringatan_2"] == 1) {
          $(".btn-peringatan-2-" + index_sudut).removeClass("opacity-3");
          $(".btn-peringatan-2-" + index_sudut)
            .off("click")
            .on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "peringatan_2",
                "hapus",
                this,
              );
            });
        } else {
          //JIKA peringatan 1 OFF KUNCI AKSES peringatan 2
          $(".btn-peringatan-2-" + index_sudut).addClass("opacity-3");
          $(".btn-peringatan-2-" + index_sudut).off("click");
          if (nilai_sudut["peringatan_1"] == 1) {
            // Perbolehkan input ketika peringatan 1 juga telah terinput
            $(".btn-peringatan-2-" + index_sudut).on("click", function (e) {
              ketua_pertandingan.edit_penilaian_tanding(
                index_sudut,
                "hukuman",
                -10,
                this,
              );
            });
          }
        }
        // END OF BLOK PERINGATAN
      },
    );

    ketua_pertandingan.update_tampilan_riwayat_verifikasi();
    ketua_pertandingan.update_tampilan_ringkasan_nilai();

    // POPOVER - DEVELOPMENT PURPOSES
    var popoverTriggerList = [].slice.call(
      document.querySelectorAll('[data-bs-toggle="popover"]'),
    );
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  },
  update_tampilan_riwayat_verifikasi: function () {
    //BLOK KODE RIWAYAT VERIFIKASI PERTANDINGAN
    $("#tabel_riwayat_verifikasi_pertandingan").find("tbody").empty();
    $.each(
      ketua_pertandingan.riwayat_verifikasi_pertandingan,
      function (i, verifikasi_pertandingan) {
        if (
          typeof ketua_pertandingan.jawaban_riwayat_verifikasi_pertandingan[
            verifikasi_pertandingan.id_verifikasi_pertandingan
          ] != "undefined"
        ) {
          $jenis_verifikasi =
            verifikasi_pertandingan.jenis_verifikasi == "jatuhan"
              ? "Dropping"
              : "Violation";
          if (verifikasi_pertandingan.status == "batal") {
            $jenis_verifikasi += "<br> (canceled)";
          }
          $row =
            `
					<tr class="text-white text-center">
						<td class="align-middle bg-white text-dark">` +
            verifikasi_pertandingan.ronde_pertandingan +
            `</td>
						<td class="align-middle bg-white text-dark">` +
            Math.floor(verifikasi_pertandingan.waktu / 1000 / 60) +
            `:` +
            ((verifikasi_pertandingan.waktu / 1000) % 60)
              .toString()
              .padStart(2, "0") +
            `</td>
						<td class="align-middle bg-white text-dark">` +
            verifikasi_pertandingan.timestamp +
            `</td>
						<td class="align-middle bg-white text-dark">` +
            $jenis_verifikasi +
            `</td>
						` +
            ketua_pertandingan.ubah_warna_sudut_ke_td(
              ketua_pertandingan.jawaban_riwayat_verifikasi_pertandingan[
                verifikasi_pertandingan.id_verifikasi_pertandingan
              ][0].jawaban,
            ) +
            `
						` +
            ketua_pertandingan.ubah_warna_sudut_ke_td(
              ketua_pertandingan.jawaban_riwayat_verifikasi_pertandingan[
                verifikasi_pertandingan.id_verifikasi_pertandingan
              ][1].jawaban,
            ) +
            `
						` +
            ketua_pertandingan.ubah_warna_sudut_ke_td(
              ketua_pertandingan.jawaban_riwayat_verifikasi_pertandingan[
                verifikasi_pertandingan.id_verifikasi_pertandingan
              ][2].jawaban,
            ) +
            `
						` +
            ketua_pertandingan.ubah_warna_sudut_ke_td(
              verifikasi_pertandingan.hasil_verifikasi,
            ) +
            `
					</tr>
				`;
          $("#tabel_riwayat_verifikasi_pertandingan")
            .find("tbody")
            .append($row);
        }
      },
    );
    //END OF BLOK KODE RIWAYAT VERIFIKASI PERTANDINGAN
  },
  update_tampilan_ringkasan_nilai: function () {
    $.each(
      ketua_pertandingan.data_nilai["ringkasan"]["semua_ronde"],
      function (index_sudut, nilai_sudut) {
        // Set Total Nilai Sah Pukulan Tendangan Per Ronde
        $.each(nilai_sudut, function (jenis_ringkasan_nilai, bobot_nilai) {
          $(
            ".ringkasan_nilai_" + index_sudut + "_" + jenis_ringkasan_nilai,
          ).html(bobot_nilai);
        });
      },
    );
  },
  ubah_warna_sudut_ke_td: function ($sudut) {
    // DIgunakan oleh fungsi update_tampilan_riwayat_verifikasi
    if ($sudut == "biru") {
      return '<td class="align-middle bg-blue">Blue</td>';
    } else if ($sudut == "merah") {
      return '<td class="align-middle bg-red">Red</td>';
    } else if ($sudut == "invalid") {
      return '<td class="align-middle bg-warning">Invalid</td>';
    } else if ($sudut == null) {
      return '<td class="align-middle bg-white text-dark text-sm">No<br>Answer</td>';
    }
  },
  highlight_nilai_akhir: function () {
    ketua_pertandingan.pertandingan.skor_merah = parseInt(
      ketua_pertandingan.pertandingan.skor_merah,
    );
    ketua_pertandingan.pertandingan.skor_biru = parseInt(
      ketua_pertandingan.pertandingan.skor_biru,
    );
    if (
      ketua_pertandingan.pertandingan.skor_biru >
      ketua_pertandingan.pertandingan.skor_merah
    ) {
      // Merah Menang
      ketua_pertandingan.highlight_nilai_sudut("biru");
    } else if (
      ketua_pertandingan.pertandingan.skor_biru <
      ketua_pertandingan.pertandingan.skor_merah
    ) {
      // Biru Menang
      ketua_pertandingan.highlight_nilai_sudut("merah");
    } else {
      /**
       * Apabila terjadi nilai sama, maka
       * 1. Pemenang adalah atlet dengan nilai hukuman terkecil
       * 2. apabila nilai hukuman sama, maka pemenang adalah atlet dengan kekayaan nilai teknik
       */
      if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.peringatan_2 >
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.peringatan_2
      ) {
        // merah menang
        ketua_pertandingan.highlight_nilai_sudut("merah");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.peringatan_2 <
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.peringatan_2
      ) {
        // Biru Menang
        ketua_pertandingan.highlight_nilai_sudut("biru");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.peringatan_1 >
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.peringatan_1
      ) {
        // merah menang
        ketua_pertandingan.highlight_nilai_sudut("merah");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.peringatan_1 <
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.peringatan_1
      ) {
        // Biru Menang
        ketua_pertandingan.highlight_nilai_sudut("biru");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.teguran_2 >
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.teguran_2
      ) {
        // merah menang
        ketua_pertandingan.highlight_nilai_sudut("merah");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.teguran_2 <
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.teguran_2
      ) {
        // Biru Menang
        ketua_pertandingan.highlight_nilai_sudut("biru");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.teguran_1 >
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.teguran_1
      ) {
        // merah menang
        ketua_pertandingan.highlight_nilai_sudut("merah");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.teguran_1 <
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.teguran_1
      ) {
        // Biru Menang
        ketua_pertandingan.highlight_nilai_sudut("biru");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.binaan_2 >
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.binaan_2
      ) {
        // merah menang
        ketua_pertandingan.highlight_nilai_sudut("merah");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.binaan_2 <
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.binaan_2
      ) {
        // Biru Menang
        ketua_pertandingan.highlight_nilai_sudut("biru");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.binaan_1 >
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.binaan_1
      ) {
        // merah menang
        ketua_pertandingan.highlight_nilai_sudut("merah");
      } else if (
        ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.binaan_1 <
        ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.binaan_1
      ) {
        // Biru Menang
        ketua_pertandingan.highlight_nilai_sudut("biru");
      } else {
        // !!! Nilai Hukuman SAMA, Harus dihitung nilai teknik
        if (
          ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.jatuhan >
          ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.jatuhan
        ) {
          // biru menang
          ketua_pertandingan.highlight_nilai_sudut("biru");
        } else if (
          ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.jatuhan <
          ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.jatuhan
        ) {
          // Merah Menang
          ketua_pertandingan.highlight_nilai_sudut("merah");
        } else if (
          ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.tendangan >
          ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.tendangan
        ) {
          // biru menang
          ketua_pertandingan.highlight_nilai_sudut("biru");
        } else if (
          ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.tendangan <
          ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.tendangan
        ) {
          // Merah Menang
          ketua_pertandingan.highlight_nilai_sudut("merah");
        } else if (
          ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.pukulan >
          ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.pukulan
        ) {
          // biru menang
          ketua_pertandingan.highlight_nilai_sudut("biru");
        } else if (
          ketua_pertandingan.ringkasan_nilai.semua_ronde.biru.pukulan <
          ketua_pertandingan.ringkasan_nilai.semua_ronde.merah.pukulan
        ) {
          // Merah Menang
          ketua_pertandingan.highlight_nilai_sudut("merah");
        } else {
          // NIlai Benar benar sama
          ketua_pertandingan.highlight_nilai_sudut("sama");
        }
      }
    }
  },
  highlight_nilai_sudut: function ($sudut) {
    // untuk mengatur css highlight nilai akhir
    if ($sudut == "biru") {
      $("#skor_biru")
        .parent()
        .removeClass("bg-gradient-180-gray-dark")
        .addClass("bg-gradient-180-blue");
      $("#skor_merah")
        .parent()
        .removeClass("bg-gradient-180-red")
        .addClass("bg-gradient-180-gray-dark");
    } else if ($sudut == "merah") {
      $("#skor_biru")
        .parent()
        .removeClass("bg-gradient-180-blue")
        .addClass("bg-gradient-180-gray-dark");
      $("#skor_merah")
        .parent()
        .removeClass("bg-gradient-180-gray-dark")
        .addClass("bg-gradient-180-red text-white");
    } else {
      $("#skor_biru")
        .parent()
        .removeClass("bg-gradient-180-blue")
        .addClass("bg-gradient-180-gray-dark");
      $("#skor_merah")
        .parent()
        .removeClass("bg-g radient-180-red")
        .addClass("bg-gradient-180-gray-dark");
    }
  },
  edit_penilaian_tanding: function (
    $sudut,
    $mode = "jatuhan",
    $jumlah,
    $btn = null,
  ) {
    // $jumlah = hapus artinya menghapus nilai;
    $($btn).prop("disabled", true);
    if ($sudut == "merah") {
      $($btn).removeClass("bg-red");
    } else {
      $($btn).removeClass("bg-blue");
    }

    if ($mode == "hukuman") {
      $(".btn-input-hukuman-biru").prop("disabled", true);
      $(".btn-input-hukuman-merah").prop("disabled", true);
    }

    $.post(
      "ketua-pertandingan/edit-penilaian-tanding/" +
        ketua_pertandingan.id_pertandingan,
      { sudut: $sudut, mode: $mode, jumlah: $jumlah },
      function (data, textStatus, jqXHR) {
        if (data.status === false) {
          Swal.fire({
            title: "Error",
            text: "Gagal edit nilai",
            icon: "error",
          });
        } else {
          $($btn).prop("disabled", false);
          if ($mode == "hukuman") {
            setTimeout(() => {
              $(".btn-input-hukuman-biru").prop("disabled", false);
              $(".btn-input-hukuman-merah").prop("disabled", false);
            }, 2000);
          }
          if ($sudut == "merah") {
            $($btn).addClass("bg-red");
          } else {
            $($btn).addClass("bg-blue");
          }

          ketua_pertandingan.set_variable(
            data.data_nilai,
            data.pertandingan,
            data.riwayat_verifikasi_pertandingan,
          );
          ketua_pertandingan.update_tampilan_nilai();
          ketua_pertandingan.update_timer();
        }
      },
      "json",
    );
  },
  modalVerifikasiJatuhan:
    $("#modalVerifikasiJatuhan").length > 0
      ? new bootstrap.Modal(document.getElementById("modalVerifikasiJatuhan"), {
          keyboard: false,
        })
      : null,
  modalVerifikasiPelanggaran:
    $("#modalVerifikasiPelanggaran").length > 0
      ? new bootstrap.Modal(
          document.getElementById("modalVerifikasiPelanggaran"),
          {
            keyboard: false,
          },
        )
      : null,
  verifikasi_jatuhan: function () {
    waitingDialog.show("Starting Drop Verification..");
    $.post(
      "verifikasi-pertandingan/create/" + ketua_pertandingan.id_pertandingan,
      {
        jenis_verifikasi: "jatuhan",
        ronde_pertandingan: ketua_pertandingan.ronde_pertandingan,
        waktu: ketua_pertandingan.waktu_sekarang,
      },
      function (data, textStatus, jqXHR) {
        if (data.status === false) {
          Swal.fire({
            title: "Error",
            text: "Failed starting drop verification, Server Error!",
            icon: "error",
          });
        } else {
          setTimeout(() => {
            waitingDialog.hide();
            ketua_pertandingan.open_modal_verifikasi_jatuhan();
          }, 1000);
        }
      },
      "json",
    ).fail(function () {
      waitingDialog.hide();
      Swal.fire({
        title: "Error",
        text: "Failed starting dorp verification, connection lost!",
        icon: "error",
      });
    });
  },
  open_modal_verifikasi_jatuhan: function () {
    if (ketua_pertandingan.modalVerifikasiJatuhan != null) {
      //Diawal selalu reset warna dan tulisan kartu jawababn sistem dialog
      $modal = $(ketua_pertandingan.modalVerifikasiJatuhan._element);
      $.each($modal.find("div.card"), function (i, v) {
        $(v).find(".card-body > p").html("Waiting Response");
        $(v).addClass("bg-dark").removeClass("bg-red bg-blue");
      });

      ketua_pertandingan.modalVerifikasiJatuhan.show();
    }
  },
  close_modal_verifikasi_jatuhan: function () {
    if (ketua_pertandingan.modalVerifikasiJatuhan != null) {
      ketua_pertandingan.modalVerifikasiJatuhan.hide();
    }
  },
  highlight_jawaban_verifikasi_jatuhan: function ($id_verifikasi_pertandingan) {
    $.getJSON(
      "verifikasi-pertandingan/get-jawaban-verifikasi-pertandingan/" +
        $id_verifikasi_pertandingan,
      function (data, textStatus, jqXHR) {
        if (data.data_jawaban_verifikasi_pertandingan !== null) {
          $modal = $(ketua_pertandingan.modalVerifikasiJatuhan._element);
          $.each(
            data.data_jawaban_verifikasi_pertandingan,
            function (i, jawaban_verifikasi_pertandingan) {
              $card = $modal.find(".card-jawaban-sistem-dialog-" + (i + 1));
              if (jawaban_verifikasi_pertandingan.jawaban == "merah") {
                $card.addClass("bg-red").removeClass("bg-dark");
                $card.find(".card-body > p").html("RED");
              } else if (jawaban_verifikasi_pertandingan.jawaban == "biru") {
                $card.addClass("bg-blue").removeClass("bg-dark");
                $card.find(".card-body > p").html("BLUE");
              } else if (jawaban_verifikasi_pertandingan.jawaban == "invalid") {
                $card.addClass("bg-warning").removeClass("bg-dark");
                $card.find(".card-body > p").html("INVALID");
              }
            },
          );
        } else {
        }
      },
    );
  },
  verifikasi_pelanggaran: function () {
    waitingDialog.show("Starting Penalty Verification..");
    $.post(
      "verifikasi-pertandingan/create/" + ketua_pertandingan.id_pertandingan,
      {
        jenis_verifikasi: "pelanggaran",
        ronde_pertandingan: ketua_pertandingan.ronde_pertandingan,
        waktu: ketua_pertandingan.waktu_sekarang,
      },
      function (data, textStatus, jqXHR) {
        if (data.status === false) {
          Swal.fire({
            title: "Error",
            text: "Failed starting violation verification, Server Error",
            icon: "error",
          });
        } else {
          setTimeout(() => {
            waitingDialog.hide();
            ketua_pertandingan.open_modal_verifikasi_pelanggaran();
          }, 1000);
        }
      },
      "json",
    ).fail(function () {
      waitingDialog.hide();
      Swal.fire({
        title: "Error",
        text: "Failed starting violation verification, Connection Lost",
        icon: "error",
      });
    });
  },
  open_modal_verifikasi_pelanggaran: function () {
    if (ketua_pertandingan.modalVerifikasiPelanggaran != null) {
      //Diawal selalu reset warna dan tulisan kartu jawababn sistem dialog
      $modal = $(ketua_pertandingan.modalVerifikasiPelanggaran._element);
      $.each($modal.find("div.card"), function (i, v) {
        $(v).find(".card-body > p").html("Waiting Response");
        $(v).addClass("bg-dark").removeClass("bg-red bg-blue");
      });

      ketua_pertandingan.modalVerifikasiPelanggaran.show();
    }
  },
  close_modal_verifikasi_pelanggaran: function () {
    if (ketua_pertandingan.modalVerifikasiPelanggaran != null) {
      ketua_pertandingan.modalVerifikasiPelanggaran.hide();
    }
  },
  highlight_jawaban_verifikasi_pelanggaran: function (
    $id_verifikasi_pertandingan,
  ) {
    $.getJSON(
      "verifikasi-pertandingan/get-jawaban-verifikasi-pertandingan/" +
        $id_verifikasi_pertandingan,
      function (data, textStatus, jqXHR) {
        if (data.data_jawaban_verifikasi_pertandingan !== null) {
          $modal = $(ketua_pertandingan.modalVerifikasiPelanggaran._element);
          $.each(
            data.data_jawaban_verifikasi_pertandingan,
            function (i, jawaban_verifikasi_pertandingan) {
              $card = $modal.find(".card-jawaban-sistem-dialog-" + (i + 1));
              if (jawaban_verifikasi_pertandingan.jawaban == "merah") {
                $card.addClass("bg-red").removeClass("bg-dark");
                $card.find(".card-body > p").html("RED");
              } else if (jawaban_verifikasi_pertandingan.jawaban == "biru") {
                $card.addClass("bg-blue").removeClass("bg-dark");
                $card.find(".card-body > p").html("BLUE");
              } else if (jawaban_verifikasi_pertandingan.jawaban == "invalid") {
                $card.addClass("bg-warning").removeClass("bg-dark");
                $card.find(".card-body > p").html("INVALID");
              }
            },
          );
        } else {
        }
      },
    );
  },
  update_timer: function () {
    ketua_pertandingan.stopwatch.timer({
      format: "%M:%S",
      action: "start",
      countdown: true,
      duration: ketua_pertandingan.waktu_sekarang,
    });
    if (ketua_pertandingan.pertandingan.status_pertandingan !== "berlangsung") {
      ketua_pertandingan.stopwatch.timer("remove");
    }
  },
  periksa_sistem_dialog: function () {
    if (
      ketua_pertandingan.verifikasi_pertandingan_berlangsung == null ||
      ketua_pertandingan.verifikasi_pertandingan_berlangsung == undefined
    ) {
      if (ketua_pertandingan.modalVerifikasiJatuhan != null) {
        ketua_pertandingan.close_modal_verifikasi_jatuhan();
      }
      if (ketua_pertandingan.modalVerifikasiPelanggaran != null) {
        ketua_pertandingan.close_modal_verifikasi_pelanggaran();
      }
    } else {
      if (
        ketua_pertandingan.verifikasi_pertandingan_berlangsung
          .jenis_verifikasi == "jatuhan"
      ) {
        if (
          ketua_pertandingan.modalVerifikasiJatuhan != null &&
          ketua_pertandingan.modalVerifikasiJatuhan._isShown == false
        ) {
          ketua_pertandingan.open_modal_verifikasi_jatuhan();
        }
        // Highlight setiap kali refresh
        if (ketua_pertandingan.modalVerifikasiJatuhan != null) {
          ketua_pertandingan.highlight_jawaban_verifikasi_jatuhan(
            ketua_pertandingan.verifikasi_pertandingan_berlangsung
              .id_verifikasi_pertandingan,
          );
        }
      } else if (
        ketua_pertandingan.verifikasi_pertandingan_berlangsung
          .jenis_verifikasi == "pelanggaran"
      ) {
        if (
          ketua_pertandingan.modalVerifikasiPelanggaran != null &&
          ketua_pertandingan.modalVerifikasiPelanggaran._isShown == false
        ) {
          ketua_pertandingan.open_modal_verifikasi_pelanggaran();
        }
        // Highlight setiap kali refresh
        if (ketua_pertandingan.modalVerifikasiPelanggaran != null) {
          ketua_pertandingan.highlight_jawaban_verifikasi_pelanggaran(
            ketua_pertandingan.verifikasi_pertandingan_berlangsung
              .id_verifikasi_pertandingan,
          );
        }
      }
    }
  },
  batalkan_verifikasi: function () {
    waitingDialog.show("Closing dialog..");
    $.post(
      "verifikasi-pertandingan/update/" +
        ketua_pertandingan.verifikasi_pertandingan_berlangsung
          .id_verifikasi_pertandingan,
      { hasil_verifikasi: null, status: "batal" },
      function (data, textStatus, jqXHR) {
        if (data.status === false) {
          Swal.fire({
            title: "Error",
            text: "Failed to set verification result",
            icon: "error",
          });
        } else {
          // CALLBACK
          waitingDialog.hide();
          ketua_pertandingan.close_modal_verifikasi_jatuhan();
          ketua_pertandingan.close_modal_verifikasi_pelanggaran();
        }
      },
      "json",
    ).fail(function () {
      Swal.fire({
        title: "Error",
        text: "Failed to set verification result ! connection lost",
        icon: "error",
      });
    });
  },
  tetapkan_verifikasi: function ($hasil_verifikasi) {
    waitingDialog.show("Applying Result...");
    $.post(
      "verifikasi-pertandingan/update/" +
        ketua_pertandingan.verifikasi_pertandingan_berlangsung
          .id_verifikasi_pertandingan,
      { hasil_verifikasi: $hasil_verifikasi, status: "selesai" },
      function (data, textStatus, jqXHR) {
        if (data.status === false) {
          Swal.fire({
            title: "Error",
            text: "Failed to set verification result",
            icon: "error",
          });
        } else {
          // CALLBACK
          setTimeout(() => {
            waitingDialog.hide();
          }, 2000);
        }
      },
      "json",
    ).fail(function () {
      Swal.fire({
        title: "Error",
        text: "Failed to set verification result ! connection lost",
        icon: "error",
      });
    });
  },

  developer: {
    open_modal_developer_option: function ($passkey = "1234") {
      Swal.fire({
        title: "Attention !",
        text: "Please Enter Your PIN Code",
        input: "password",
        showCancelButton: true,

        confirmButtonText: "Submit",
      }).then((result) => {
        if (result.value == $passkey) {
          $("#modal_developer_option").modal("show");
        } else {
          Swal.fire({
            icon: "error",
            title: "Oops...",
            text: "Wrong Passcode!",
          });
        }
      });
    },
  },

  refresh_status_pertandingan: () => {
    $.post(
      "ketua-pertandingan/refresh-status-pertandingan/" +
        ketua_pertandingan.id_pertandingan,
      function (data, textStatus, jqXHR) {
        if (data.status === true && data.reload === true) {
          window.location.reload();
        } else {
          ketua_pertandingan.set_variable(
            data.data_nilai,
            data.pertandingan,
            data.verifikasi_pertandingan_berlangsung,
            data.riwayat_verifikasi_pertandingan,
            data.jawaban_riwayat_verifikasi_pertandingan,
          );
          ketua_pertandingan.update_tampilan_nilai();
          ketua_pertandingan.update_timer();
          ketua_pertandingan.periksa_sistem_dialog();
        }
      },
      "json",
    ).always(function () {
      setTimeout(() => {
        ketua_pertandingan.refresh_status_pertandingan();
      }, 3000);
    });
  },
  get_timestamp: function (unixTimestamp) {
    let date = new Date(unixTimestamp * 1000);
    let hours = date.getHours();
    let minutes = "0" + date.getMinutes();
    let seconds = "0" + date.getSeconds();
    let formatTime =
      hours + ":" + minutes.substr(-2) + ":" + seconds.substr(-2);
    return formatTime;
  },
};
