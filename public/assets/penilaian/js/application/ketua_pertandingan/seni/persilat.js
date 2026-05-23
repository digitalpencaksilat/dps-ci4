const ketua_pertandingan = {
  id_penampilan_seni_berlangsung: null,
  penampilan_seni_berlangsung: null,
  semua_penampilan_seni: null,
  data_nilai: null,
  penilaian_terpilih: null, // berformat array, berisi id_perangkat_pertandingan yang terpilih
  init: function (
    $id_penampilan_seni,
    $data_nilai,
    $penampilan_seni_berlangsung,
    $semua_penampilan_seni,
    $autorefresh = true,
  ) {
    /**
     * @params object $data_nilai
     * $data_nilai adalah hasil query result()
     * dari kolom penilaian_seni,
     * dapat berisi 3 record untuk 3 juri maupun 5 record
     * untuk 5 juri
     */
    ketua_pertandingan.set_variable(
      $id_penampilan_seni,
      $data_nilai,
      $penampilan_seni_berlangsung,
      $semua_penampilan_seni,
    );
    ketua_pertandingan.update_tampilan_nilai($data_nilai);
    if ($autorefresh == true) {
      ketua_pertandingan.refresh_status_seni();
    }
  },
  set_variable: function (
    $id_penampilan_seni,
    $data_nilai,
    $penampilan_seni_berlangsung,
    $semua_penampilan_seni,
  ) {
    ketua_pertandingan.id_penampilan_seni_berlangsung = $id_penampilan_seni;
    ketua_pertandingan.data_nilai = $data_nilai;
    ketua_pertandingan.penampilan_seni_berlangsung =
      $penampilan_seni_berlangsung;
    ketua_pertandingan.semua_penampilan_seni = $semua_penampilan_seni;
  },
  update_tampilan_nilai: function ($data_nilai) {
    if (ketua_pertandingan.penampilan_seni_berlangsung.diskualifikasi == 1) {
      $(
        ".keterangan_" +
          ketua_pertandingan.penampilan_seni_berlangsung.id_penampilan_seni,
      ).html('<span class="badge badge-danger">Diskualifikasi</span>');
    } else {
      $(
        ".keterangan_" +
          ketua_pertandingan.penampilan_seni_berlangsung.id_penampilan_seni,
      ).html(" ");
    }

    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function (index_juri, penilaian_juri) {
        $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
        //update nilai akhir per juri
        if (
          $(".penampilan_seni_" + id_penampilan_seni).hasClass("blue-corner")
        ) {
          $warna_highlight = "bg-blue text-white";
        } else if (
          $(".penampilan_seni_" + id_penampilan_seni).hasClass("red-corner")
        ) {
          $warna_highlight = "bg-red text-white";
        } else {
          $warna_highlight = "bg-warning";
        }

        $(
          ".penampilan_seni_" +
            id_penampilan_seni +
            " .nilai_akhir_juri_" +
            penilaian_juri.id_perangkat_pertandingan,
        )
          .html($penilaian.ringkasan.nilai_akhir)
          .removeClass($warna_highlight);
      });
    });

    ketua_pertandingan.update_tampilan_urutan_nilai_tiap_juri($data_nilai);
    ketua_pertandingan.update_tampilan_unsur_nilai($data_nilai);
    ketua_pertandingan.update_tampilan_jenis_hukuman($data_nilai);
    ketua_pertandingan.update_tampilan_panel_input_hukuman($data_nilai);
    ketua_pertandingan.update_ringkasan_nilai(
      ketua_pertandingan.penampilan_seni_berlangsung,
    );
    ketua_pertandingan.pilih_penilaian_juri($data_nilai);
    ketua_pertandingan.hitung_dan_tampilkan_median_kebenaran();
    ketua_pertandingan.hitung_dan_tampilkan_median_kebenaran_battle();
  },
  update_tampilan_panel_input_hukuman: function ($data_nilai) {
    // Update Tampilan Hukuman pada panel input
    // Cukup ambil nilai 1 juri
    $sampel_nilai =
      $data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung][0];
    $sampel_nilai = JSON.parse($sampel_nilai.penilaian).penilaian;

    $.each($sampel_nilai.hukuman, function (jenis_hukuman, hukuman) {
      detail_hukuman = hukuman.detail_hukuman;
      if (hukuman.tipe == "pilihan ganda" || hukuman.tipe == "satu kali") {
        $(
          ".penampilan_seni_" +
            ketua_pertandingan.id_penampilan_seni_berlangsung +
            " .nilai_hukuman_" +
            jenis_hukuman,
        ).html(detail_hukuman.nilai_hukuman);
      } else if (hukuman.tipe == "repetisi") {
        $(
          ".penampilan_seni_" +
            ketua_pertandingan.id_penampilan_seni_berlangsung +
            " .jumlah_repetisi_" +
            jenis_hukuman,
        ).html(detail_hukuman.jumlah_repetisi);
        $(
          ".penampilan_seni_" +
            ketua_pertandingan.id_penampilan_seni_berlangsung +
            " .nilai_hukuman_" +
            jenis_hukuman,
        ).html(detail_hukuman.nilai_hukuman);
      }
    });
    $(
      ".penampilan_seni_" +
        ketua_pertandingan.id_penampilan_seni_berlangsung +
        " " +
        ".total_hukuman",
    ).html($sampel_nilai.ringkasan.total_hukuman);
  },
  update_tampilan_urutan_nilai_tiap_juri: function ($data_nilai) {
    $.each(
      ketua_pertandingan.semua_penampilan_seni,
      function (index, penampilan_seni) {
        $kumpulan_nilai = $data_nilai[penampilan_seni.id_penampilan_seni];

        let urutan_total_nilai = [];
        for (var key in $kumpulan_nilai) {
          $total_nilai_per_juri = JSON.parse($kumpulan_nilai[key].penilaian)
            .penilaian.ringkasan.total_nilai;
          urutan_total_nilai.push([
            $kumpulan_nilai[key].id_perangkat_pertandingan,
            $total_nilai_per_juri,
            parseInt(key) + 1,
          ]);
        }
        urutan_total_nilai.sort(function (a, b) {
          return a[1] - b[1];
        });
        // Contoh array urutan_total_nilai
        // [
        // 	41, // id_perangkat_pertandingan
        // 	9.95, // nilai akhir
        // 	1 // nomor juri
        // ]

        $.each(
          $(".kolom_total_nilai_" + penampilan_seni.id_penampilan_seni),
          function (i, element) {
            $(element)
              .find(".nomor_juri")
              .html("Juri " + urutan_total_nilai[i][2]);
            $(element)
              .find(".bobot_total_nilai")
              .html(urutan_total_nilai[i][1]);

            $(element)
              .find(".kolom_bobot_total_nilai")
              .empty()
              .append(
                `<p class="fw-bolder text-center my-1 h5 total_nilai_juri_` +
                  urutan_total_nilai[i][0] +
                  ` juri_` +
                  urutan_total_nilai[i][0] +
                  `">
							` +
                  urutan_total_nilai[i][1] +
                  `
						</p>`,
              );
          },
        );
      },
    );
  },
  update_ringkasan_nilai: function ($penampilan_seni_berlangsung) {
    /**
     * Update catatan nilai sama, median, waktu dll untuk semua peserta
     * Update Nilai Akhir penampilan berlangsung
     */
    $.each(
      ketua_pertandingan.semua_penampilan_seni,
      function (index, penampilan_seni) {
        if (
          penampilan_seni.catatan_nilai_sama !== undefined &&
          penampilan_seni.catatan_nilai_sama !== ""
        ) {
          $catatan_nilai_sama = JSON.parse(penampilan_seni.catatan_nilai_sama);
          $.each($catatan_nilai_sama, function (i, kompenen_nilai_sama) {
            $("." + i + "_" + penampilan_seni.id_penampilan_seni).html(
              Number(kompenen_nilai_sama).toFixed(6),
            );
          });
        }

        // NILAI AKHIR
        if (penampilan_seni.nilai_akhir == null) {
          $(".nilai_akhir_" + penampilan_seni.id_penampilan_seni).html("0");
        } else {
          $(".nilai_akhir_" + penampilan_seni.id_penampilan_seni).html(
            Number(penampilan_seni.nilai_akhir).toFixed(3),
          );
        }
        // NILAI AKHIR

        // WAKTU TAMPIL
        $(".waktu_" + penampilan_seni.id_penampilan_seni)
          .timer({
            format: "%M:%S",
            action: "start",
            seconds: penampilan_seni.waktu_tampil,
          })
          .timer("remove");
        // WAKTU TAMPIL
      },
    );
  },
  pilih_penilaian_juri: function ($data_nilai) {
    /**
     * fungsi ini digunakan untuk memberikan penanda / highlight pada penilaian juri yang dipilih
     */
    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function (index_juri, penilaian_juri) {
        if (
          $(".penampilan_seni_" + id_penampilan_seni).hasClass("blue-corner")
        ) {
          $warna_highlight = "bg-blue text-white";
        } else if (
          $(".penampilan_seni_" + id_penampilan_seni).hasClass("red-corner")
        ) {
          $warna_highlight = "bg-red text-white";
        } else {
          $warna_highlight = "bg-warning text-white";
        }

        if (penilaian_juri.terpilih == 1 && id_penampilan_seni) {
          $(
            ".penampilan_seni_" +
              id_penampilan_seni +
              " .juri_" +
              penilaian_juri.id_perangkat_pertandingan,
          ).addClass($warna_highlight);
        } else {
          $(
            ".penampilan_seni_" +
              id_penampilan_seni +
              " .juri_" +
              penilaian_juri.id_perangkat_pertandingan,
          ).removeClass($warna_highlight);
        }
      });
    });
  },
  diskualifikasi_peserta: function () {
    Swal.fire(
      "info",
      "Diskualifikasi peserta akan dilakukan oleh sekretaris pertandingan",
      "info",
    );
  },
  update_tampilan_unsur_nilai: function ($data_nilai) {
    /**
     * fungsi ini digunakan pada tahap perhitungan nilai sama
     */

    // Data unsur nilai kebenaran, kemantapan dll
    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
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
                penilaian_juri.id_perangkat_pertandingan,
            ).html(unsur_nilai.nilai_diperoleh.toFixed(2));
          },
        );
      });
    });

    // tampilan catatan_nilai_sama untuk rekapitulasi seluruh penampilan
    $.each(
      ketua_pertandingan.semua_penampilan_seni,
      function (index, penampilan_seni) {
        if (
          penampilan_seni.catatan_nilai_sama !== undefined &&
          penampilan_seni.catatan_nilai_sama !== ""
        ) {
          $catatan_nilai_sama = JSON.parse(penampilan_seni.catatan_nilai_sama);
          $id_penampilan_seni = penampilan_seni.id_penampilan_seni;
          $(
            ".penampilan_seni_" + $id_penampilan_seni + " .catatan_nilai_sama",
          ).empty();
          $.each($catatan_nilai_sama, function (index_nilai, nilai) {
            $(
              ".penampilan_seni_" +
                $id_penampilan_seni +
                " .catatan_nilai_sama",
            ).append(
              '<span class="d-block">' +
                index_nilai +
                " = " +
                nilai +
                "</span>",
            );

            $(".penampilan_seni_" + $id_penampilan_seni + " ." + index_nilai)
              .empty()
              .append('<span class="d-block text-end">' + nilai + "</span>");
          });
        }
      },
    );
    // data nilai diperoleh dan nilai akhir
    $.each($data_nilai, function (id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function (index_juri, penilaian_juri) {
        $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
        $(
          ".penampilan_seni_" +
            id_penampilan_seni +
            " .total_nilai_juri_" +
            penilaian_juri.id_perangkat_pertandingan,
        ).html($penilaian.ringkasan.total_nilai);
        $(
          ".penampilan_seni_" +
            id_penampilan_seni +
            " .total_hukuman_juri_" +
            penilaian_juri.id_perangkat_pertandingan,
        ).html($penilaian.ringkasan.total_hukuman);
        $(
          ".penampilan_seni_" +
            id_penampilan_seni +
            " .nilai_akhiri_juri_" +
            penilaian_juri.id_perangkat_pertandingan,
        ).html($penilaian.ringkasan.nilai_akhir);
      });
    });
  },
  update_tampilan_jenis_hukuman: function ($data_nilai) {
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
          penilaian_juri.penilaian,
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
              nama_hukuman,
          ).html(nilai_hukuman);
        },
      );
      $(".penampilan_seni_" + id_penampilan_seni + " .total_hukuman").html(
        data_hukuman["total_hukuman"],
      );
    });
  },

  reset_semua_hukuman: function ($element) {
    // Get all jury scores for current performance
    const $seluruh_nilai_juri =
      ketua_pertandingan.data_nilai[
        ketua_pertandingan.id_penampilan_seni_berlangsung
      ];

    if (!$seluruh_nilai_juri || $seluruh_nilai_juri.length === 0) {
      console.error("No jury data found");
      return false;
    }

    try {
      // Reset penalties for each jury
      $.each($seluruh_nilai_juri, function (index_juri, penilaian_juri) {
        const $penilaian_per_juri = JSON.parse(penilaian_juri.penilaian);
        const $daftar_hukuman = $penilaian_per_juri.penilaian.hukuman;

        // Store original total score before resetting penalties
        const originalTotalNilai =
          $penilaian_per_juri.penilaian.ringkasan.total_nilai;

        // Reset all penalty types
        $.each($daftar_hukuman, function (jenis_hukuman, isi_jenis_hukuman) {
          switch (isi_jenis_hukuman.tipe) {
            case "pilihan ganda":
              $penilaian_per_juri.penilaian.hukuman[
                jenis_hukuman
              ].detail_hukuman = {
                nilai_hukuman: 0,
                terpilih: "",
              };
              break;

            case "repetisi":
              $penilaian_per_juri.penilaian.hukuman[
                jenis_hukuman
              ].detail_hukuman = {
                nilai_hukuman: 0,
                jumlah_repetisi: 0,
                faktor_pengali: isi_jenis_hukuman.detail_hukuman.faktor_pengali,
              };
              break;

            case "satu kali":
              $penilaian_per_juri.penilaian.hukuman[
                jenis_hukuman
              ].detail_hukuman = {
                nilai_hukuman: 0,
                faktor_pengali: isi_jenis_hukuman.detail_hukuman.faktor_pengali,
              };
              break;
          }
        });

        // Reset total penalties and recalculate final score
        $penilaian_per_juri.penilaian.ringkasan.total_hukuman = 0;
        $penilaian_per_juri.penilaian.ringkasan.nilai_akhir =
          originalTotalNilai;

        // Update the jury data
        ketua_pertandingan.data_nilai[
          ketua_pertandingan.id_penampilan_seni_berlangsung
        ][index_juri].penilaian = JSON.stringify($penilaian_per_juri);
      });

      // Send updates to server
      ketua_pertandingan.edit_penilaian_seni($element);
      return true;
    } catch (error) {
      console.error("Error resetting penalties:", error);
      return false;
    }
  },
  // Improved single penalty edit function
  edit_hukuman: function ($jenis_hukuman, $data, $element) {
    const $sampel_nilai =
      ketua_pertandingan.data_nilai[
        ketua_pertandingan.id_penampilan_seni_berlangsung
      ][0].penilaian;
    const $parsed_sampel = JSON.parse($sampel_nilai).penilaian;

    if (!$parsed_sampel.hukuman[$jenis_hukuman]) {
      console.error("Invalid penalty type");
      return false;
    }

    const $hukuman = $parsed_sampel.hukuman[$jenis_hukuman];
    const $seluruh_nilai_juri =
      ketua_pertandingan.data_nilai[
        ketua_pertandingan.id_penampilan_seni_berlangsung
      ];

    try {
      if ($data.nilai_hukuman === "reset") {
        return ketua_pertandingan._reset_single_penalty(
          $jenis_hukuman,
          $hukuman,
          $seluruh_nilai_juri,
          $element,
        );
      }

      return ketua_pertandingan._add_penalty(
        $jenis_hukuman,
        $data,
        $hukuman,
        $seluruh_nilai_juri,
        $element,
      );
    } catch (error) {
      console.error("Error editing penalty:", error);
      return false;
    }
  },
  // Helper function to reset a single penalty
  _reset_single_penalty: function (
    $jenis_hukuman,
    $hukuman,
    $seluruh_nilai_juri,
    $element,
  ) {
    if ($hukuman.detail_hukuman.nilai_hukuman === 0) {
      return false;
    }

    $.each($seluruh_nilai_juri, function (index_juri, penilaian_juri) {
      const $penilaian = JSON.parse(penilaian_juri.penilaian);

      // Remove penalty value from total
      $penilaian.penilaian.ringkasan.total_hukuman -=
        $hukuman.detail_hukuman.nilai_hukuman;

      // Recalculate final score
      $penilaian.penilaian.ringkasan.nilai_akhir =
        $penilaian.penilaian.ringkasan.total_nilai -
        $penilaian.penilaian.ringkasan.total_hukuman;

      // Reset penalty details
      if ($hukuman.tipe === "pilihan ganda") {
        $penilaian.penilaian.hukuman[$jenis_hukuman].detail_hukuman.terpilih =
          "";
      }
      $penilaian.penilaian.hukuman[
        $jenis_hukuman
      ].detail_hukuman.nilai_hukuman = 0;
      if ($hukuman.tipe === "repetisi") {
        $penilaian.penilaian.hukuman[
          $jenis_hukuman
        ].detail_hukuman.jumlah_repetisi = 0;
      }

      ketua_pertandingan.data_nilai[
        ketua_pertandingan.id_penampilan_seni_berlangsung
      ][index_juri].penilaian = JSON.stringify($penilaian);
    });

    ketua_pertandingan.edit_penilaian_seni($element);
    return true;
  },
  // Helper function to add a penalty
  _add_penalty: function (
    $jenis_hukuman,
    $data,
    $hukuman,
    $seluruh_nilai_juri,
    $element,
  ) {
    if (
      $hukuman.tipe === "repetisi" &&
      $hukuman.detail_hukuman.jumlah_repetisi + $data.jumlah_repetisi < 0
    ) {
      return false;
    }

    $.each($seluruh_nilai_juri, function (index_juri, penilaian_juri) {
      const $penilaian = JSON.parse(penilaian_juri.penilaian);

      // Calculate penalty value
      let penaltyValue = $data.nilai_hukuman;
      if ($hukuman.tipe === "repetisi") {
        penaltyValue =
          $data.jumlah_repetisi * $hukuman.detail_hukuman.faktor_pengali;
      }

      // Update totals
      $penilaian.penilaian.ringkasan.total_hukuman += penaltyValue;
      $penilaian.penilaian.ringkasan.nilai_akhir =
        $penilaian.penilaian.ringkasan.total_nilai -
        $penilaian.penilaian.ringkasan.total_hukuman;

      // Update penalty details
      if ($hukuman.tipe === "pilihan ganda") {
        $penilaian.penilaian.hukuman[$jenis_hukuman].detail_hukuman.terpilih =
          $data.terpilih;
      }
      if ($hukuman.tipe === "repetisi") {
        $penilaian.penilaian.hukuman[
          $jenis_hukuman
        ].detail_hukuman.jumlah_repetisi += $data.jumlah_repetisi;
      }
      $penilaian.penilaian.hukuman[
        $jenis_hukuman
      ].detail_hukuman.nilai_hukuman += penaltyValue;

      ketua_pertandingan.data_nilai[
        ketua_pertandingan.id_penampilan_seni_berlangsung
      ][index_juri].penilaian = JSON.stringify($penilaian);
    });

    ketua_pertandingan.edit_penilaian_seni($element);
    return true;
  },
  edit_penilaian_seni: function ($element) {
    $($element).prop("disabled", true);
    $.post(
      "ketua-pertandingan/edit-penilaian-seni/" +
        ketua_pertandingan.id_penampilan_seni_berlangsung,
      {
        data_nilai: JSON.stringify(
          ketua_pertandingan.data_nilai[
            ketua_pertandingan.id_penampilan_seni_berlangsung
          ],
        ), // Hanya update penilaian penampilan berlangsung
      },
      function (data, textStatus, jqXHR) {
        if (data.status == true) {
          ketua_pertandingan.set_variable(
            data.penampilan_seni_berlangsung.id_penampilan_seni,
            data.data_nilai,
            data.penampilan_seni_berlangsung,
          );
          ketua_pertandingan.update_tampilan_nilai(data.data_nilai);
          $($element).removeAttr("disabled");
        } else {
          console.log("gagal update nilai");
        }
      },
      "json",
    );
  },
  ganti_akses_penilaian: function ($value) {
    $.post(
      "ketua-pertandingan/ganti-akses-penilaian/" +
        ketua_pertandingan.id_penampilan_seni_berlangsung,
      { akses_penilaian: $value },
      function (data, textStatus, jqXHR) {
        if ($("#btn-toggle-akses-penilaian").length > 0) {
          // Logika Tombol Tunggal (Premium)
          const $btn = $("#btn-toggle-akses-penilaian");
          if (data.akses_penilaian == "dibuka") {
            $btn.removeClass("btn-success").addClass("btn-danger");
            $btn.find("span").html("Lock Scoring");
            $btn.attr("onclick", "ketua_pertandingan.ganti_akses_penilaian('ditutup')");
          } else {
            $btn.removeClass("btn-danger").addClass("btn-success");
            $btn.find("span").html("Unlock Scoring");
            $btn.attr("onclick", "ketua_pertandingan.ganti_akses_penilaian('dibuka')");
          }
        } else {
          // Logika Dua Tombol (Fallback)
          if (data.akses_penilaian == "dibuka") {
            $(".btn-buka-akses-penilaian").fadeOut("fast", function () {
              $(".btn-tutup-akses-penilaian").fadeIn();
            });
          } else {
            $(".btn-tutup-akses-penilaian").fadeOut("fast", function () {
              $(".btn-buka-akses-penilaian").fadeIn();
            });
          }
        }
      },
      "json",
    );
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
          "ketua-pertandingan/diskualifikasi-penampilan-seni/" +
            ketua_pertandingan.id_penampilan_seni_berlangsung,
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
          "ketua-pertandingan/batalkan-diskualifikasi-penampilan-seni/" +
            ketua_pertandingan.id_penampilan_seni_berlangsung,
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
  hitung_dan_tampilkan_median_kebenaran: function () {
    const id = ketua_pertandingan.id_penampilan_seni_berlangsung;
    const dataJuri = ketua_pertandingan.data_nilai[id];

    if (!dataJuri || dataJuri.length === 0) return;

    let nilaiKebenaran = [];

    // 1️⃣ Ambil nilai kebenaran dari tiap juri
    $.each(dataJuri, function (i, penilaian_juri) {
      const penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;

      if (
        penilaian.unsur_nilai &&
        penilaian.unsur_nilai.kebenaran &&
        penilaian.unsur_nilai.kebenaran.nilai_diperoleh !== undefined
      ) {
        nilaiKebenaran.push(
          parseFloat(penilaian.unsur_nilai.kebenaran.nilai_diperoleh),
        );
      }
    });

    if (nilaiKebenaran.length === 0) return;

    // 2️⃣ Urutkan nilai
    nilaiKebenaran.sort((a, b) => a - b);

    // 3️⃣ Hitung median
    let median;
    const mid = Math.floor(nilaiKebenaran.length / 2);

    if (nilaiKebenaran.length % 2 === 0) {
      median = (nilaiKebenaran[mid - 1] + nilaiKebenaran[mid]) / 2;
    } else {
      median = nilaiKebenaran[mid];
    }

    // 4️⃣ Tampilkan
    $(".kebenaran_median_" + id).html(median.toFixed(3));
  },
  hitung_dan_tampilkan_median_kebenaran_battle: function () {
    const semuaPenampilan = ketua_pertandingan.semua_penampilan_seni;
    const dataNilai = ketua_pertandingan.data_nilai;

    if (!semuaPenampilan || !dataNilai) return;

    // 🔁 Loop tiap penampilan (biru & merah)
    $.each(semuaPenampilan, function (idx, penampilan) {
      const id = penampilan.id_penampilan_seni;
      const dataJuri = dataNilai[id];

      if (!dataJuri || dataJuri.length === 0) return;

      let nilaiKebenaran = [];

      // 1️⃣ Ambil nilai kebenaran dari tiap juri
      $.each(dataJuri, function (i, penilaian_juri) {
        if (!penilaian_juri.penilaian) return;

        const penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;

        if (
          penilaian.unsur_nilai &&
          penilaian.unsur_nilai.kebenaran &&
          penilaian.unsur_nilai.kebenaran.nilai_diperoleh !== undefined &&
          penilaian.unsur_nilai.kebenaran.nilai_diperoleh !== null
        ) {
          nilaiKebenaran.push(
            parseFloat(penilaian.unsur_nilai.kebenaran.nilai_diperoleh),
          );
        }
      });

      if (nilaiKebenaran.length === 0) return;

      // 2️⃣ Urutkan nilai
      nilaiKebenaran.sort((a, b) => a - b);

      // 3️⃣ Hitung median
      let median;
      const mid = Math.floor(nilaiKebenaran.length / 2);

      if (nilaiKebenaran.length % 2 === 0) {
        median = (nilaiKebenaran[mid - 1] + nilaiKebenaran[mid]) / 2;
      } else {
        median = nilaiKebenaran[mid];
      }

      // 4️⃣ Tampilkan (3 angka di belakang koma)
      $(".kebenaran_median_" + id).html(median.toFixed(3));
    });
  },

  refresh_status_seni: () => {
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
          if (
            ketua_pertandingan.data_nilai[
              ketua_pertandingan.id_penampilan_seni_berlangsung
            ].length !==
            data.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung]
              .length
          ) {
            window.location.reload();
          } else {
            ketua_pertandingan.set_variable(
              data.penampilan_seni_berlangsung.id_penampilan_seni,
              data.data_nilai,
              data.penampilan_seni_berlangsung,
              data.semua_penampilan_seni,
            );
            ketua_pertandingan.update_tampilan_nilai(data.data_nilai);

            // Update indikator kesiapan juri jika data tersedia
            if (typeof data.status_ready_juri !== "undefined" && data.status_ready_juri !== null) {
              ketua_pertandingan.update_tampilan_ready_juri(data.status_ready_juri);
            }
          }
        } else {
          // Tidak ada penampilan seni berlangsung, langsung tunjukkan summary
          if (
            ketua_pertandingan.penampilan_seni_berlangsung.sistem_penampilan ==
            "battle"
          ) {
            if ($("#summaryNav").length > 0) {
              $("#summaryNav").trigger("mouseover");
              document.getElementById("summaryNav").click();
            }
            $("tbody tr").removeClass(
              "bg-red bg-blue bg-warning text-white text-decoration-line-through",
            );
          } else {
            if ($("#summaryNav").length > 0) {
              $("#summaryNav").trigger("mouseover");
              document.getElementById("summaryNav").click();
            }
          }
        }
      },
      "json",
    ).always(function () {
      setTimeout(() => {
        ketua_pertandingan.refresh_status_seni();
      }, 3000);
    });
  },

  /**
   * Update tampilan indikator kesiapan juri di halaman Ketua Pertandingan.
   * Dipanggil otomatis setiap polling refresh_status_seni (setiap ~3 detik).
   *
   * @param {Array} status_ready_juri - Array objek {id_perangkat_pertandingan, status_ready, nama}
   */
  update_tampilan_ready_juri: function(status_ready_juri) {
    const $container = $('#monitor-ready-juri');
    if ($container.length === 0 || !status_ready_juri || status_ready_juri.length === 0) return;

    let html = '';
    let allReady = true;

    $.each(status_ready_juri, function(i, juri) {
      const isReady = parseInt(juri.status_ready) === 1;
      if (!isReady) allReady = false;

      const namaJuri = juri.nama ? juri.nama : 'Juri ' + (i + 1);
      const badgeClass = isReady ? 'bg-success' : 'bg-primary';
      const badgeIcon = isReady ? '✅' : '🔵';
      const badgeText = isReady ? 'Ready' : 'Ready?';

      html += `
        <div class="d-flex align-items-center justify-content-between mb-2 px-2 py-2 rounded"
             style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);">
          <span class="text-white small fw-semibold text-truncate me-2" style="max-width: 60%;" title="${namaJuri}">
            ${namaJuri}
          </span>
          <span class="badge ${badgeClass} px-2 py-1" style="font-size: 0.7rem; white-space: nowrap;">
            ${badgeIcon} ${badgeText}
          </span>
        </div>`;
    });

    // Tambah ringkasan di bawah
    const totalReady = status_ready_juri.filter(j => parseInt(j.status_ready) === 1).length;
    const totalJuri = status_ready_juri.length;
    const summaryClass = allReady ? 'text-success' : 'text-warning';

    html += `
      <div class="mt-2 pt-2 border-top border-secondary text-center">
        <small class="${summaryClass} fw-bold">
          ${allReady ? '✅ Semua Juri Ready' : totalReady + ' / ' + totalJuri + ' Juri Ready'}
        </small>
      </div>`;

    $container.html(html);
  },
};

