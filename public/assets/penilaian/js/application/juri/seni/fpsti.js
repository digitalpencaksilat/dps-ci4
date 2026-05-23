const juri = {
	penampilan_seni : null,
	id_penampilan_seni: null,
	data_nilai: null,
	mode: null,
	init_penilaian_seni: function ($penampilan_seni, $data_nilai, $mode = 'juri') {
		juri.set_variable($penampilan_seni, $data_nilai, $mode);
		juri.update_tampilan_nilai();
		juri.refresh_status_seni();
	},
	set_variable: function ($penampilan_seni, $data_nilai, $mode = 'juri') {
		juri.mode = $mode;
		juri.penampilan_seni = $penampilan_seni;
		juri.id_penampilan_seni = $penampilan_seni.id_penampilan_seni;
		juri.data_nilai = $data_nilai;
	},
	update_data_nilai: function ($element) {

		juri.hitung_total_nilai();
		juri.hitung_total_hukuman();
		juri.hitung_nilai_akhir();

		$($element).attr('disabled', true);
		$.post("juri/edit-penilaian-seni/" + juri.id_penampilan_seni, {
			data_nilai: JSON.stringify(juri.data_nilai),
			nilai_akhir: juri.data_nilai.penilaian.ringkasan.nilai_akhir
		},
			function (data, textStatus, jqXHR) {
				$($element).removeAttr('disabled');
				if (data.status == true) {
					juri.update_tampilan_nilai();
				} else {
					console.log('gagal update nilai')
				}
			}, "json"
		);
	},
	edit_nilai_kebenaran_jurus: function ($jurus, $nomor_rangkaian_gerak, $perubahan, $element) {
		/**
		* @params $perubahan int
		* @params $jurus string
		* @params $nomor_rangkaian_gerak int
		* @params $perubahan int
		* @params $element DOM
		* fungsi ini digunakan untuk mengedit unsur nilai kebenaran jurus, dapat diaplikasikan
		* pada penilaian kategori tunggal ipsi, tunggal tapak suci. beregu ipsi maupun penilaian lainnya
		* yang memuat komposisi nilai kebenaran jurus
		*/
		$input_elemment = $($element).parents('.container_' + $jurus + '_' + $nomor_rangkaian_gerak).find('input');
		$nilai_maksimal = parseInt(juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus].rangkaian_gerak[$nomor_rangkaian_gerak].nilai_maksimal);
		$jumlah_kesalahan_selanjutnya = parseInt($input_elemment.val()) + parseInt($perubahan);
		if (
			$jumlah_kesalahan_selanjutnya <= $nilai_maksimal &&
			$jumlah_kesalahan_selanjutnya >= 0) {

			juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus].rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_kesalahan += $perubahan;
			juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus].rangkaian_gerak[$nomor_rangkaian_gerak].nilai_diperoleh -= $perubahan;
			juri.data_nilai.penilaian.unsur_nilai.kebenaran.nilai_diperoleh -= $perubahan;
			juri.data_nilai.penilaian.unsur_nilai.kebenaran.total_kesalahan_gerak += $perubahan;

			juri.update_data_nilai($element);
		}
	},
	edit_unsur_nilai: function ($jenis_unsur_nilai, $perubahan, $element) {
		/**
		* @params $perubahan int
		* @params $element DOM
		* fungsi ini digunakan untuk mengedit unsur nilai selain kebenaran jurus
		* contoh unsur nilai : kemantapan, wiraga, wirasa
		*/
		$input_elemment = $($element).parents('.container_' + $jenis_unsur_nilai).find('input');
		$nilai_maksimal = parseInt(juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_maksimal);
		$nilai_minimal = parseInt(juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_minimal);
		$nilai_diperoleh_selanjutnya = parseInt($input_elemment.val()) + parseInt($perubahan);
		if (
			$nilai_diperoleh_selanjutnya <= $nilai_maksimal &&
			$nilai_diperoleh_selanjutnya >= $nilai_minimal) {

			juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_diperoleh += $perubahan;

			juri.update_data_nilai($element);
		}
	},
	edit_unsur_nilai_pilihan_ganda: function ($jenis_unsur_nilai, $data, $element) {
		/**
		* @params $nilai_diperoleh_terbaru int
		* @params $element DOM
		* fungsi ini digunakan untuk mengedit unsur nilai selain kebenaran jurus dengan model pilihan ganda
		* contoh unsur nilai : kemantapan, wiraga, wirasa
		*/
		$input_elemment = $($element).parents('.container_' + $jenis_unsur_nilai).find('input');

		juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_diperoleh = $data.bobot_nilai;
		juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].metadata.terpilih = $data.terpilih;
		$semua_button = $('.pilihan_ganda_'+$jenis_unsur_nilai).find('button');
		$.each($semua_button, function (i, v) { 
			 $(v).addClass('btn-secondary').removeClass('btn-danger');
		});
		$($element).addClass('btn-danger').removeClass('btn-secondary');

		juri.update_data_nilai($element);
	},
	edit_hukuman: function ($jenis_hukuman, $data, $element) {

		if (juri.data_nilai.penilaian.hukuman[$jenis_hukuman].tipe == 'pilihan ganda') {
			if ($data.nilai_hukuman == 'reset') {
				if (juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman !== 0) {
					juri.data_nilai.penilaian.ringkasan.total_hukuman -= juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman;
					juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman = 0;
					juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.terpilih = '';
				} else {
					return false;
				}
			} else {
				juri.data_nilai.penilaian.ringkasan.total_hukuman += $data.nilai_hukuman;
				juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman += $data.nilai_hukuman;
				juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.terpilih = $data.terpilih;
			}
		} else if (juri.data_nilai.penilaian.hukuman[$jenis_hukuman].tipe == 'repetisi') {
			if ($data.nilai_hukuman == 'reset') {
				juri.data_nilai.penilaian.ringkasan.total_hukuman -= juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman;
				juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman = 0;
				juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.jumlah_repetisi = 0;
			} else {
				if (
					juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.jumlah_repetisi +
					$data.jumlah_repetisi >= 0) {
					nilai_hukuman = ($data.jumlah_repetisi * juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.faktor_pengali);
					juri.data_nilai.penilaian.ringkasan.total_hukuman += nilai_hukuman;
					juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.jumlah_repetisi += $data.jumlah_repetisi;
					juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman += nilai_hukuman;
				} else {
					return false;
				}
			}
		} else if (juri.data_nilai.penilaian.hukuman[$jenis_hukuman].tipe == 'satu kali') {
			if ($data.nilai_hukuman == 'reset') {
				if (juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman !== 0) {
					juri.data_nilai.penilaian.ringkasan.total_hukuman -= juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman;
					juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman = 0;
				} else {
					return false;
				}
			} else {
				juri.data_nilai.penilaian.ringkasan.total_hukuman += $data.nilai_hukuman;
				juri.data_nilai.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman += $data.nilai_hukuman;
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
			juri.data_nilai.penilaian.ringkasan.total_nilai += unsur_nilai.nilai_diperoleh;
		});
	},
	hitung_total_hukuman: function () {

		juri.data_nilai.penilaian.ringkasan.total_hukuman = 0;
		$.each(juri.data_nilai.penilaian.hukuman, function (i, hukuman) {
			juri.data_nilai.penilaian.ringkasan.total_hukuman += hukuman.detail_hukuman.nilai_hukuman;
		});
	},
	hitung_nilai_akhir: function () {
		juri.data_nilai.penilaian.ringkasan.nilai_akhir = juri.data_nilai.penilaian.ringkasan.total_nilai - juri.data_nilai.penilaian.ringkasan.total_hukuman;
	},
	update_tampilan_nilai: function () {
		/**
		 * fungsi ini digunakan untuk mengatur tampilan nilai pada ui juri. 
		 * fungsi ini dipanggil saat pertama kali memasuki halaman mauapun setelah
		 * melakukan perubahan data ke server
		 */
		$.each(juri.data_nilai.penilaian, function (jenis, value_jenis) {
			if (jenis == "unsur_nilai") {
				$.each(value_jenis, function (key_jenis_unsur_seni, value_jenis_unsur_seni) {
					if (key_jenis_unsur_seni == 'kebenaran') {
						$.each(value_jenis_unsur_seni.jurus, function (jurus, value_jurus) {
							// jurus = {tangan_kosong, Golok, Toya}  
							$.each(value_jurus.rangkaian_gerak, function (nomor_rangkaian_gerak, value_nomor_rangkaian_gerak) {
								$('.kebenaran_' + jurus + '_' + nomor_rangkaian_gerak).val(value_nomor_rangkaian_gerak.jumlah_kesalahan);
								$('.kebenaran_' + jurus + '_' + nomor_rangkaian_gerak).attr('max', value_nomor_rangkaian_gerak.nilai_maksimal);
							});
						});
						$('.total_kesalahan_gerak').html(value_jenis_unsur_seni.total_kesalahan_gerak);
						$('.total_nilai_kebenaran').val(value_jenis_unsur_seni.nilai_diperoleh);
					} else {
						$('.nilai_' + key_jenis_unsur_seni).val(value_jenis_unsur_seni.nilai_diperoleh);
					}
				});
			} else if (jenis == 'hukuman') {
				$.each(value_jenis, function (jenis_hukuman, hukuman) {
					detail_hukuman = hukuman.detail_hukuman
					if (hukuman.tipe == 'pilihan ganda' || hukuman.tipe == 'satu kali') {
						$('.nilai_hukuman_' + jenis_hukuman).val(detail_hukuman.nilai_hukuman);
						if (detail_hukuman.nilai_hukuman > 0) {
							$('.btn_hukuman_' + jenis_hukuman).attr('disabled', true);
						} else {
							$('.btn_hukuman_' + jenis_hukuman).removeAttr('disabled');
						}
					} else if (hukuman.tipe == 'repetisi') {
						$('.jumlah_repetisi_' + jenis_hukuman).val(detail_hukuman.jumlah_repetisi);
						$('.nilai_hukuman_' + jenis_hukuman).val(detail_hukuman.nilai_hukuman);
					}
				});
			} else {
				$('.total_nilai').val(value_jenis.total_nilai);
				$('.total_hukuman').val(value_jenis.total_hukuman);

				if (juri.mode == 'juri') {
					$('.nilai_akhir').val(value_jenis.nilai_akhir);
				} else {
					$('.nilai_akhir').html(value_jenis.nilai_akhir);
				}
			}
		});
	},
	diskualifikasi_peserta: function () {
		Swal.fire('info', 'Diskualifikasi peserta akan dilakukan oleh sekretaris pertandingan', 'info');
	},
	refresh_status_seni: () => {
		$.post("juri/refresh-status-seni/" + juri.id_penampilan_seni,
			function (data, textStatus, jqXHR) {
				if (
					data.status === true && data.reload === true ||
					data.data_nilai === null // ketika juri sedang tidak ditugaskan (formasi 3 juri)
				) {
					window.location.reload();
				} else {
					if(juri.penampilan_seni.format_penilaian !== data.penampilan_seni.format_penilaian ||
						data.data_nilai == null
					){
						window.location.reload();
					}else{
						juri.set_variable(data.penampilan_seni, data.data_nilai);
						juri.update_tampilan_nilai();
					}
				}
			},
			"json"
		).always(function(){
			setTimeout(() => {	
				juri.refresh_status_seni(juri.id_penampilan_seni, null);
			}, 3000);
		});
	},
}
