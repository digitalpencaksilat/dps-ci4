const juri = {
  penampilan_seni: null,
  id_penampilan_seni: null,
  data_nilai: null,
  mode: null,
  button_audio: new Audio("assets/penilaian/audio/button.mp3"),
  init_penilaian_seni: function (
    $penampilan_seni,
    $data_nilai,
    $mode = "juri",
    $kelas_aksen_warna = "bg-gradient-180-warning"
  ) {
    juri.set_variable($penampilan_seni, $data_nilai, $mode, $kelas_aksen_warna);
    juri.update_tampilan_nilai();
    juri.refresh_status_seni();
    $data_pointer = juri.pointer.get_data_pointer();
    juri.pointer.update_tampilan_pointer($data_pointer);
  },
  set_variable: function (
    $penampilan_seni,
    $data_nilai,
    $mode = "juri",
    $kelas_aksen_warna = "bg-gradient-180-warning"
  ) {
    juri.mode = $mode;
    juri.penampilan_seni = $penampilan_seni;
    juri.id_penampilan_seni = $penampilan_seni.id_penampilan_seni;
    juri.data_nilai = $data_nilai;
    juri.kelas_aksen_warna = $kelas_aksen_warna;
    juri.data_nilai = $data_nilai;
  },
  set_hukuman_ringkasan: function ($penampilan_seni, $data_nilai) {
    // !!! Digunakan untuk sinkronisasi hukuman
    // !!! Sistem juri hanya bisa menampilkan hukuman dan total nilai, kalkulasi dilakukan backend
    // !!! Sistem juri tidak mensubmit nilai hukuman ke server
    juri.data_nilai.penilaian.hukuman = $data_nilai.penilaian.hukuman;
    juri.data_nilai.penilaian.ringkasan = $data_nilai.penilaian.ringkasan;
  },
  update_data_nilai: function ($element) {
    juri.hitung_total_nilai();
    juri.hitung_total_hukuman();
    juri.hitung_nilai_akhir();

    // $($element).prop('disabled', true);
    $.post(
      "juri/edit-penilaian-seni/" + juri.id_penampilan_seni,
      {
        data_nilai: JSON.stringify(juri.data_nilai),
        nilai_akhir: juri.data_nilai.penilaian.ringkasan.nilai_akhir,
      },
      function (data, textStatus, jqXHR) {
        // $($element).removeAttr('disabled');
        if (data.status == true) {
        } else {
          console.log("gagal update nilai");
          window.location.reload();
        }
      },
      "json"
    );
    // Update nilai tidak perlu menunggu respon server
    juri.update_tampilan_nilai();
    $data_pointer = juri.pointer.get_data_pointer();
    juri.pointer.update_tampilan_pointer($data_pointer);
  },
  edit_nilai_kebenaran_jurus: function (
    $jurus,
    $nomor_rangkaian_gerak,
    $perubahan,
    $element = null
  ) {
    /**
     * @params $perubahan int
     * @params $jurus string
     * @params $nomor_rangkaian_gerak int
     * @params $perubahan int
     * @params $element DOM
     * fungsi ini digunakan untuk mengedit unsur nilai kebenaran jurus, dapat diaplikasikan
     * pada penilaian kategori tunggal ipsi, tunggal tapak suci. beregu ipsi maupun penilaian lainnya
     * yang memuat komposisi nilai kebenaran jurus
     * !!! Mode Per Kolom Jurus menggunakan pendekatan penambahan hukuman
     * Dapat diakses oleh tombol per kolom rangkaian gerak (model lama, IPSI 2012)
     * Maupun tombol sekuensial / pointer
     */

    $nilai_maksimal = parseFloat(
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].nilai_maksimal
    );
    $nilai_diperoleh =
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].nilai_diperoleh;
    $nilai_diperoleh_selanjutnya = Number(
      $nilai_diperoleh + $perubahan
    ).toFixed(2); // Mengambil decimal 2 digit, return berupa string
    $nilai_diperoleh_selanjutnya = Number($nilai_diperoleh_selanjutnya); // mengubah ke number

    $jumlah_gerakan = parseFloat(
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_gerakan
    );
    if (
      $nilai_diperoleh_selanjutnya <= $nilai_maksimal &&
      $nilai_diperoleh_selanjutnya >= 0
    ) {
      // Nilai jurus berjalan
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[
        $jurus
      ].rangkaian_gerak[$nomor_rangkaian_gerak].nilai_diperoleh =
        $nilai_diperoleh_selanjutnya;

      $total_nilai_maksimal =
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.nilai_maksimal;

      // Seluruh nilai kebenaran
      $total_nilai_diperoleh =
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.nilai_diperoleh;
      $total_nilai_diperoleh = Number(
        $total_nilai_diperoleh + $perubahan
      ).toFixed(2);
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.nilai_diperoleh = Number(
        $total_nilai_diperoleh
      );

      if ($perubahan > 0) {
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[
          $jurus
        ].rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_kesalahan -= 1;
        // Total gerakan yang benar bertambah
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.total_kesalahan_gerak -= 1;
      } else {
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[
          $jurus
        ].rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_kesalahan += 1;
        // Total kesalahan gerak bertambah
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.total_kesalahan_gerak += 1;
      }
    } else {
      console.log("macet");
    }

    juri.update_data_nilai($element); // Langsung diupdate
    juri.button_audio.play();
  },
  pointer: {
    /**
     * !!! Pointer gerakan dimulai dari 1, bukan 0
     */
    pindah_gerakan: function ($arah_gerakan, $perubahan_nilai, $element) {
      $data_pointer = juri.pointer.get_data_pointer();
      juri.edit_nilai_kebenaran_jurus(
        $data_pointer.pointer_jurus,
        $data_pointer.pointer_rangkaian_gerak,
        $perubahan_nilai,
        $element,
        "sequence"
      );
      juri.pointer.move_pointer($data_pointer, $arah_gerakan);
      // juri.update_data_nilai($element);
    },
    pindah_pointer_rangkaian_gerak: function ($arah_gerakan) {
      $data_pointer = juri.pointer.get_data_pointer();
      $nomor_rangkaian_gerak_selanjutnya =
        $data_pointer.pointer_rangkaian_gerak + $arah_gerakan;
      if (
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[
          $data_pointer.pointer_jurus
        ].rangkaian_gerak[$nomor_rangkaian_gerak_selanjutnya] !== undefined
      ) {
        // dapat berpindah rangkaian gerak;
        $data_pointer.pointer_rangkaian_gerak += $arah_gerakan;
        $data_pointer.pointer_gerakan = 1;
        juri.button_audio.play();
      } else {
        // rangkaian_gerak sudah maksimal, perlu pindah jurus
        $daftar_jurus = Object.keys(
          juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus
        );
        $index_jurus_saat_ini = $daftar_jurus.indexOf(
          $data_pointer.pointer_jurus
        );
        $pointer_jurus_selanjutnya =
          $daftar_jurus[$index_jurus_saat_ini + $arah_gerakan];

        if ($pointer_jurus_selanjutnya != undefined) {
          // Jurus selanjutnya valid, misal dari tangan kosong ke golok
          $data_pointer.pointer_jurus = $pointer_jurus_selanjutnya;
          $data_pointer.pointer_rangkaian_gerak += $arah_gerakan;
          $data_pointer.pointer_gerakan = 1;
        } else {
          // Jurus sudah habis, kunci semua tombaol;
          // juri.pointer.lock_tombol();
        }
      }
      juri.pointer.set_data_pointer($data_pointer);
      juri.pointer.update_tampilan_pointer($data_pointer);
    },
    move_pointer: function ($data_pointer, $arah_gerakan) {
      // Hanya Berfungsi untuk memindahkan pointer, tanpa mengubah nilai apapun
      juri.pointer.unlock_tombol();
      $new_pointer_gerakan = $data_pointer.pointer_gerakan + $arah_gerakan;
      if (
        $new_pointer_gerakan > 0 &&
        $new_pointer_gerakan <= $data_pointer.jumlah_gerakan
      ) {
        // tidak perlu pindah rangkaian gerak
        console.log("pindah gerakan");
        $data_pointer.pointer_gerakan += $arah_gerakan;
      } else {
        $nomor_rangkaian_gerak_selanjutnya =
          $data_pointer.pointer_rangkaian_gerak + 1;
        if (
          juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[
            $data_pointer.pointer_jurus
          ].rangkaian_gerak[$nomor_rangkaian_gerak_selanjutnya] !== undefined
        ) {
          // dapat berpindah rangkaian gerak;
          $data_pointer.pointer_rangkaian_gerak += $arah_gerakan;
          $data_pointer.pointer_gerakan = 1;
          console.log("pindah rangkaian gerak");
        } else {
          // rangkaian_gerak sudah maksimal, perlu pindah jurus
          $daftar_jurus = Object.keys(
            juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus
          );
          $index_jurus_saat_ini = $daftar_jurus.indexOf(
            $data_pointer.pointer_jurus
          );
          $pointer_jurus_selanjutnya = $daftar_jurus[$index_jurus_saat_ini + 1];

          if ($pointer_jurus_selanjutnya != undefined) {
            // Jurus selanjutnya valid, misal dari tangan kosong ke golok
            $data_pointer.pointer_jurus = $pointer_jurus_selanjutnya;
            $data_pointer.pointer_rangkaian_gerak += $arah_gerakan;
            $data_pointer.pointer_gerakan = 1;
            console.log("pindah jurus");
          } else {
            // Jurus sudah habis, kunci semua tombaol;
            // juri.pointer.lock_tombol();
          }
        }
      }

      juri.pointer.set_data_pointer($data_pointer);
      juri.pointer.update_tampilan_pointer($data_pointer);
    },
    reset_pointer: function ($button) {
      $daftar_jurus = Object.keys(
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus
      );
      juri.pointer.set_pointer_jurus($daftar_jurus[0]);
      juri.pointer.set_pointer_rangkaian_gerak(1);
      juri.pointer.set_pointer_gerakan(1);
      juri.update_data_nilai($button);
      juri.pointer.unlock_tombol();
    },
    // KHUSUS SISTEM POINTER
    get_data_pointer: function () {
      // Mengambil semua data pointer, untuk kategori ganda tidak ada pointer
      if (juri.data_nilai.penilaian.unsur_nilai.kebenaran != undefined) {
        $pointer_jurus = juri.pointer.get_pointer_jurus();
        $pointer_rangkaian_gerak = juri.pointer.get_pointer_rangkaian_gerak();
        return {
          pointer_jurus: $pointer_jurus,
          pointer_rangkaian_gerak: $pointer_rangkaian_gerak,
          pointer_gerakan: juri.pointer.get_pointer_gerakan(),
          jumlah_kesalahan_rangkaian_gerak:
            juri.pointer.get_jumlah_kesalahan_rangkaian_gerak(
              $pointer_jurus,
              $pointer_rangkaian_gerak
            ),
          jumlah_kebenaran_rangkaian_gerak:
            juri.pointer.get_jumlah_kebenaran_rangkaian_gerak(
              $pointer_jurus,
              $pointer_rangkaian_gerak
            ),
          jumlah_gerakan: juri.pointer.get_jumlah_gerakan(
            $pointer_jurus,
            $pointer_rangkaian_gerak
          ),
          nilai_diperoleh_rangkaian_gerak:
            juri.pointer.get_nilai_diperoleh_rangkaian_gerak(
              $pointer_jurus,
              $pointer_rangkaian_gerak
            ),
          nilai_maksimal_rangkaian_gerak:
            juri.pointer.get_nilai_maksimal_rangkaian_gerak(
              $pointer_jurus,
              $pointer_rangkaian_gerak
            ),
        };
      } else {
        return null;
      }
    },
    set_data_pointer: function ($data_pointer) {
      juri.pointer.set_pointer_jurus($data_pointer.pointer_jurus);
      juri.pointer.set_pointer_rangkaian_gerak(
        $data_pointer.pointer_rangkaian_gerak
      );
      juri.pointer.set_pointer_gerakan($data_pointer.pointer_gerakan);
      // juri.pointer.set_jumlah_kesalahan_rangkaian_gerak($data_pointer.pointer_jurus, $data_pointer.pointer_rangkaian_gerak, $data_pointer.jumlah_kesalahan_rangkaian_gerak);
      // juri.pointer.set_nilai_diperoleh_rangkaian_gerak($data_pointer.pointer_jurus, $data_pointer.pointer_rangkaian_gerak, $data_pointer.nilai_diperoleh_rangkaian_gerak);
    },
    update_tampilan_pointer: function ($data_pointer) {
      // Untuk Kategori Ganda tidak ada pointer
      if ($data_pointer !== null) {
        $(".pointer_jurus").html($data_pointer.pointer_jurus.replace("_", " "));
        $(".pointer_rangkaian_gerak").html(
          $data_pointer.pointer_rangkaian_gerak
        );
        $(".pointer_gerakan").html($data_pointer.pointer_gerakan);
        $(".jumlah_kesalahan_rangkaian_gerak").html(
          $data_pointer.jumlah_kesalahan_rangkaian_gerak
        );
        $(".jumlah_kebenaran_rangkaian_gerak").html(
          $data_pointer.jumlah_kebenaran_rangkaian_gerak
        );
      }

      $(".container_rangkaian_gerak").removeClass(
        "bg-gradient-180-blue bg-gradient-180-red bg-gradient-180-warning text-white"
      );
      $(
        ".container_" +
          $data_pointer.pointer_jurus +
          "_" +
          $data_pointer.pointer_rangkaian_gerak
      ).addClass(juri.kelas_aksen_warna + " text-white");
    },
    get_pointer_jurus: function () {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata
        .pointer_jurus;
    },
    get_pointer_rangkaian_gerak: function () {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata
        .pointer_rangkaian_gerak;
    },
    get_pointer_gerakan: function () {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata
        .pointer_gerakan;
    },
    get_jumlah_gerakan: function ($nama_jurus, $nomor_rangkaian_gerak) {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$nama_jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_gerakan;
    },
    get_jumlah_kesalahan_rangkaian_gerak: function (
      $nama_jurus,
      $nomor_rangkaian_gerak
    ) {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$nama_jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_kesalahan;
    },
    get_jumlah_kebenaran_rangkaian_gerak: function (
      $nama_jurus,
      $nomor_rangkaian_gerak
    ) {
      $jumlah_kesalahan =
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$nama_jurus]
          .rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_kesalahan;
      $jumlah_gerakan =
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$nama_jurus]
          .rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_gerakan;
      return $jumlah_gerakan - $jumlah_kesalahan;
    },
    get_nilai_diperoleh_rangkaian_gerak: function (
      $nama_jurus,
      $nomor_rangkaian_gerak
    ) {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$nama_jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].nilai_diperoleh;
    },
    get_nilai_maksimal_rangkaian_gerak: function (
      $nama_jurus,
      $nomor_rangkaian_gerak
    ) {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$nama_jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].nilai_maksimal;
    },

    set_pointer_jurus: function ($pointer_jurus) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_jurus =
        $pointer_jurus;
    },
    set_pointer_rangkaian_gerak: function ($pointer_rangkaian_gerak) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_rangkaian_gerak =
        $pointer_rangkaian_gerak;
    },
    set_pointer_gerakan: function ($pointer_gerakan) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_gerakan =
        $pointer_gerakan;
    },
    set_jumlah_kesalahan_rangkaian_gerak: function (
      $nama_jurus,
      $nomor_rangkaian_gerak,
      $jumlah_kesalahan
    ) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[
        $nama_jurus
      ].rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_kesalahan =
        $jumlah_kesalahan;
    },
    set_nilai_diperoleh_rangkaian_gerak: function (
      $nama_jurus,
      $nomor_rangkaian_gerak,
      $nilai_diperoleh
    ) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[
        $nama_jurus
      ].rangkaian_gerak[$nomor_rangkaian_gerak].nilai_diperoleh =
        $nilai_diperoleh;
    },
    set_nilai_maksimal_rangkaian_gerak: function (
      $nama_jurus,
      $nomor_rangkaian_gerak,
      $nilai_maksimal
    ) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[
        $nama_jurus
      ].rangkaian_gerak[$nomor_rangkaian_gerak].nilai_maksimal =
        $nilai_maksimal;
    },
    lock_tombol: function () {
      $(".button_gerakan_benar, .button_gerakan_salah")
        .prop("disabled", true)
        .addClass("btn-disabled");
    },
    unlock_tombol: function () {
      $(".button_gerakan_benar, .button_gerakan_salah")
        .removeAttr("disabled")
        .removeClass("btn-disabled");
    },
  },
  edit_unsur_nilai: function ($jenis_unsur_nilai, $perubahan, $element) {
    /**
     * @params $perubahan int
     * @params $element DOM
     * fungsi ini digunakan untuk mengedit unsur nilai selain kebenaran jurus
     * contoh unsur nilai : kemantapan, wiraga, wirasa
     */
    $input_elemment = $($element)
      .parents(".container_" + $jenis_unsur_nilai)
      .find("input");
    $nilai_maksimal = parseFloat(
      juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_maksimal
    );
    $nilai_minimal = parseFloat(
      juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_minimal
    );
    $bobot_unsur_nilai_selanjutnya =
      parseFloat($input_elemment.val()) + parseFloat($perubahan);

    $bobot_unsur_nilai_selanjutnya = Number(
      $bobot_unsur_nilai_selanjutnya
    ).toFixed(2);
    if (
      $bobot_unsur_nilai_selanjutnya <= $nilai_maksimal &&
      $bobot_unsur_nilai_selanjutnya >= $nilai_minimal
    ) {
      juri.data_nilai.penilaian.unsur_nilai[
        $jenis_unsur_nilai
      ].nilai_diperoleh += $perubahan;

      juri.update_data_nilai($element);
    }
  },
  edit_hukuman: function ($jenis_hukuman, $data, $element) {
    if (
      juri.data_nilai.penilaian.hukuman[$jenis_hukuman].tipe == "pilihan ganda"
    ) {
      if ($data.nilai_hukuman == "reset") {
        if (
          juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman
            .nilai_hukuman !== 0
        ) {
          juri.data_nilai.penilaian.ringkasan.total_hukuman -=
            juri.data_nilai.penilaian.hukuman[
              $jenis_hukuman
            ].detail_hukuman.nilai_hukuman;
          juri.data_nilai.penilaian.hukuman[
            $jenis_hukuman
          ].detail_hukuman.nilai_hukuman = 0;
          juri.data_nilai.penilaian.hukuman[
            $jenis_hukuman
          ].detail_hukuman.terpilih = "";
        } else {
          return false;
        }
      } else {
        juri.data_nilai.penilaian.ringkasan.total_hukuman +=
          $data.nilai_hukuman;
        juri.data_nilai.penilaian.hukuman[
          $jenis_hukuman
        ].detail_hukuman.nilai_hukuman += $data.nilai_hukuman;
        juri.data_nilai.penilaian.hukuman[
          $jenis_hukuman
        ].detail_hukuman.terpilih = $data.terpilih;
      }
    } else if (
      juri.data_nilai.penilaian.hukuman[$jenis_hukuman].tipe == "repetisi"
    ) {
      if ($data.nilai_hukuman == "reset") {
        juri.data_nilai.penilaian.ringkasan.total_hukuman -=
          juri.data_nilai.penilaian.hukuman[
            $jenis_hukuman
          ].detail_hukuman.nilai_hukuman;
        juri.data_nilai.penilaian.hukuman[
          $jenis_hukuman
        ].detail_hukuman.nilai_hukuman = 0;
        juri.data_nilai.penilaian.hukuman[
          $jenis_hukuman
        ].detail_hukuman.jumlah_repetisi = 0;
      } else {
        if (
          juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman
            .jumlah_repetisi +
            $data.jumlah_repetisi >=
          0
        ) {
          nilai_hukuman =
            $data.jumlah_repetisi *
            juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman
              .faktor_pengali;
          juri.data_nilai.penilaian.ringkasan.total_hukuman += nilai_hukuman;
          juri.data_nilai.penilaian.hukuman[
            $jenis_hukuman
          ].detail_hukuman.jumlah_repetisi += $data.jumlah_repetisi;
          juri.data_nilai.penilaian.hukuman[
            $jenis_hukuman
          ].detail_hukuman.nilai_hukuman += nilai_hukuman;
        } else {
          return false;
        }
      }
    } else if (
      juri.data_nilai.penilaian.hukuman[$jenis_hukuman].tipe == "satu kali"
    ) {
      if ($data.nilai_hukuman == "reset") {
        if (
          juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman
            .nilai_hukuman !== 0
        ) {
          juri.data_nilai.penilaian.ringkasan.total_hukuman -=
            juri.data_nilai.penilaian.hukuman[
              $jenis_hukuman
            ].detail_hukuman.nilai_hukuman;
          juri.data_nilai.penilaian.hukuman[
            $jenis_hukuman
          ].detail_hukuman.nilai_hukuman = 0;
        } else {
          return false;
        }
      } else {
        juri.data_nilai.penilaian.ringkasan.total_hukuman +=
          $data.nilai_hukuman;
        juri.data_nilai.penilaian.hukuman[
          $jenis_hukuman
        ].detail_hukuman.nilai_hukuman += $data.nilai_hukuman;
      }
    }
    juri.update_data_nilai($element);
  },
  hitung_total_nilai: function () {
    /**
     * fungsi ini digunakan untuk menghitung seluruh total unsur nilai (tanpa dikurangi hukuman)
     */
    juri.data_nilai.penilaian.ringkasan.total_nilai = 0;
    $.each(juri.data_nilai.penilaian.unsur_nilai, function (i, unsur_nilai) {
      $bobot_unsur_nilai = unsur_nilai.nilai_diperoleh;
      $bobot_unsur_nilai = Number($bobot_unsur_nilai).toFixed(2); // Mengambil decimal 2 digit, return berupa string
      $bobot_unsur_nilai = Number($bobot_unsur_nilai); // mengubah ke number

      juri.data_nilai.penilaian.ringkasan.total_nilai += $bobot_unsur_nilai;
    });
    // Nilai Minimal diperlukan karena dalam sistem penilaian 2022 nilai minimal bukan 0;
    $nilai_minimal = juri.data_nilai.penilaian.ringkasan.nilai_minimal;
    $total_nilai =
      $nilai_minimal + juri.data_nilai.penilaian.ringkasan.total_nilai;

    $total_nilai = Number($total_nilai).toFixed(2); // Mengambil decimal 2 digit, return berupa string
    $total_nilai = Number($total_nilai); // mengubah ke number

    juri.data_nilai.penilaian.ringkasan.total_nilai = $total_nilai;
  },
  hitung_total_hukuman: function () {
    juri.data_nilai.penilaian.ringkasan.total_hukuman = 0;
    $.each(juri.data_nilai.penilaian.hukuman, function (i, hukuman) {
      juri.data_nilai.penilaian.ringkasan.total_hukuman +=
        hukuman.detail_hukuman.nilai_hukuman;
    });
  },
  hitung_nilai_akhir: function () {
    juri.data_nilai.penilaian.ringkasan.nilai_akhir =
      juri.data_nilai.penilaian.ringkasan.total_nilai -
      juri.data_nilai.penilaian.ringkasan.total_hukuman;
  },
  update_tampilan_nilai: function () {
    /**
     * fungsi ini digunakan untuk mengatur tampilan nilai pada ui juri.
     * fungsi ini dipanggil saat pertama kali memasuki halaman mauapun setelah
     * melakukan perubahan data ke server
     */
    $.each(juri.data_nilai.penilaian, function (jenis, value_jenis) {
      if (jenis == "unsur_nilai") {
        $.each(
          value_jenis,
          function (key_jenis_unsur_seni, value_jenis_unsur_seni) {
            if (key_jenis_unsur_seni == "kebenaran") {
              $.each(
                value_jenis_unsur_seni.jurus,
                function (jurus, value_jurus) {
                  // jurus = {tangan_kosong, Golok, Toya}
                  $.each(
                    value_jurus.rangkaian_gerak,
                    function (
                      nomor_rangkaian_gerak,
                      value_nomor_rangkaian_gerak
                    ) {
                      $(
                        ".kebenaran_" + jurus + "_" + nomor_rangkaian_gerak
                      ).val(value_nomor_rangkaian_gerak.jumlah_kesalahan);
                      $(
                        ".kebenaran_" + jurus + "_" + nomor_rangkaian_gerak
                      ).attr("max", value_nomor_rangkaian_gerak.nilai_maksimal);
                    }
                  );
                }
              );
              $total_pengurangan_kebenaran_gerak = (
                value_jenis_unsur_seni.nilai_maksimal -
                value_jenis_unsur_seni.nilai_diperoleh
              ).toFixed(2);
              $(".total_pengurangan_kebenaran_gerak").html(
                $total_pengurangan_kebenaran_gerak
              );
              $(".total_nilai_kebenaran").val(
                value_jenis_unsur_seni.nilai_diperoleh.toFixed(2)
              );
            } else {
              $(".nilai_" + key_jenis_unsur_seni).val(
                value_jenis_unsur_seni.nilai_diperoleh.toFixed(2)
              );
            }
          }
        );
      } else if (jenis == "hukuman") {
        $.each(value_jenis, function (jenis_hukuman, hukuman) {
          detail_hukuman = hukuman.detail_hukuman;
          if (hukuman.tipe == "pilihan ganda" || hukuman.tipe == "satu kali") {
            $(".nilai_hukuman_" + jenis_hukuman).val(
              detail_hukuman.nilai_hukuman
            );
            if (detail_hukuman.nilai_hukuman > 0) {
              $(".btn_hukuman_" + jenis_hukuman).attr("disabled", true);
            } else {
              $(".btn_hukuman_" + jenis_hukuman).removeAttr("disabled");
            }
          } else if (hukuman.tipe == "repetisi") {
            $(".jumlah_repetisi_" + jenis_hukuman).val(
              detail_hukuman.jumlah_repetisi
            );
            $(".nilai_hukuman_" + jenis_hukuman).val(
              detail_hukuman.nilai_hukuman
            );
          }
        });
      } else {
        $(".total_nilai").val(value_jenis.total_nilai);
        $(".total_hukuman").val(value_jenis.total_hukuman);

        if (juri.mode == "juri") {
          $(".nilai_akhir").val(value_jenis.nilai_akhir);
        } else {
          $(".nilai_akhir").html(value_jenis.nilai_akhir);
        }
      }
    });
  },
  update_tampilan_nilai_akhir: function () {
    /**
     * fungsi ini digunakan untuk mengatur tampilan nilai pada ui juri.
     * fungsi ini dipanggil saat pertama kali memasuki halaman mauapun setelah
     * melakukan perubahan data ke server
     */
    $.each(juri.data_nilai.penilaian, function (jenis, value_jenis) {
      if (jenis == "ringkasan") {
        $(".total_nilai").val(value_jenis.total_nilai);
        $(".total_hukuman").val(value_jenis.total_hukuman);

        if (juri.mode == "juri") {
          $(".nilai_akhir").val(value_jenis.nilai_akhir);
        } else {
          $(".nilai_akhir").html(value_jenis.nilai_akhir);
        }
      }
    });
  },
  update_akses_penilaian: function ($akses_penilaian) {
    if ($akses_penilaian == "dibuka") {
      // Remove the overlay
      var overlay = document.getElementById("overlay");
      if (overlay) {
        overlay.classList.remove("slideInDown");
        overlay.classList.add("slideOutUp");
        setTimeout(function () {
          overlay.remove();
        }, 1000); // Matches the animation duration
      }
    } else {
      var overlay = document.getElementById("overlay");
      if (!overlay) {
        // Append the overlay to the body
        var overlay = document.createElement("div");
        overlay.id = "overlay";
        overlay.className =
          "position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex justify-content-center align-items-center animated slideInDown";

        // Add text to overlay
        var overlayText = document.createElement("div");
        overlayText.className = "text-white h1";
        overlayText.innerText = "Scoring Access Locked";
        overlay.appendChild(overlayText);

        document.body.appendChild(overlay);
      }
    }
  },
  diskualifikasi_peserta: function () {
    Swal.fire(
      "info",
      "Diskualifikasi peserta akan dilakukan oleh sekretaris pertandingan",
      "info"
    );
  },
  refresh_status_seni: () => {
    $.post(
      "juri/refresh-status-seni/" + juri.id_penampilan_seni,
      function (data, textStatus, jqXHR) {
        if (
          (data.status === true && data.reload === true) ||
          data.data_nilai === null // ketika juri sedang tidak ditugaskan (formasi 3 juri)
        ) {
          window.location.reload();
        } else {
          if (
            juri.penampilan_seni.format_penilaian !==
              data.penampilan_seni.format_penilaian ||
            data.data_nilai == null
          ) {
            window.location.reload();
          } else {
            /**
             * !!! INI YANG MENYEBABKAN INPUT NILAI DARI KP TIDAK SINKRON,
             * !!! KARENA JURI TIDAK MENGUBAH VARIABLE YANG ADA,
             * !!! SOLUSI, DI BACKEND, UPDATE DARI JURI HANYA DIPAKAI UNTUK MENGUBAH UNSER NILAI SELAIN HUKUMAN
             */
            juri.set_hukuman_ringkasan(data.penampilan_seni, data.data_nilai);
            juri.update_tampilan_nilai_akhir(); //Hanya update nilai akhir,
            juri.update_akses_penilaian(data.penampilan_seni.akses_penilaian); //Update akses penilaian
          }
        }
      },
      "json"
    ).always(function () {
      setTimeout(() => {
        juri.refresh_status_seni(juri.id_penampilan_seni, null);
      }, 2000);
    });
  },
};
