
const juri = {
	/**
	 * !!! script tanding tidak dirancang untuk cross format penilaian
	 * !!! script ini hanya untuk format penilaian IPSI 2012
	 */
	data_nilai: null,
	data_waktu: null,
	waktu_sekarang: null,
	pertandingan: null,
	id_pertandingan: null,
	ronde_pertandingan: null,
	nilai_akhir_merah: 0,
	nilai_akhir_biru: 0,
	pemenang : null,
	init: function ($data_nilai, $pertandingan, $pemenang, $mode = 'juri') {
		juri.start_animation();
		juri.set_variable($data_nilai, $pertandingan, $pemenang, $mode = 'juri');
		juri.update_tampilan_nilai();
		juri.refresh_status_pertandingan();
		juri.set_ronde();
	},
	set_variable: function ($data_nilai, $pertandingan, $pemenang, $mode = 'juri') {
		juri.data_nilai = $data_nilai;
		juri.pertandingan = $pertandingan;
		juri.data_waktu = $pertandingan.data_waktu;
		juri.waktu_sekarang = $pertandingan.data_waktu[$pertandingan.ronde_pertandingan][1];
		juri.mode = $mode;
		juri.id_pertandingan = $pertandingan.id_pertandingan;
		juri.ronde_pertandingan = $pertandingan.ronde_pertandingan;
		juri.pemenang = $pemenang;
	},
	start_animation: function(){
		$('#header-tanding').addClass('animated fadeInDown').removeClass('opacity');
		setTimeout(() => {
			$('#tabel-nilai-juri tr').each(function (index, element) {
				setTimeout(() => {	
					$(element).addClass('animated fadeInDown').removeClass('opacity');
				}, 300 * index);
			});
			setTimeout(() => {
				$('#button-merah button').each(function (index, element) {
					setTimeout(() => {	
						$(element).addClass('animated fadeIn').removeClass('opacity');
					}, 150 * index);
				});
				$('#button-biru button').each(function (index, element) {
					setTimeout(() => {	
						$(element).addClass('animated fadeIn').removeClass('opacity');
					}, 150 * index);
				});
			}, 1700);
		}, 600);
	},
	update_tampilan_nilai: function () {
		/**
		 * Fungsi ini hanya dieksekusi ketika inisiasi halaman dan juri berhasil update data 
		 * ke database
		 */
		$.each(juri.data_nilai, function (sudut, v) {
			$.each(v.ronde_pertandingan, function (ronde, nilai) {
				let jumlah_rincian_nilai = nilai['rincian'].length;
				$('.' + sudut + '-ronde-' + ronde+'-nilai').empty();
				$('.' + sudut + '-ronde-' + ronde+'-hukuman').empty();
				
				$rincian_nilai = '';
				$rincian_hukuman = '';

				if (jumlah_rincian_nilai > 0) {
					$.each(nilai['rincian'], function (index, nilai) {
						
						if(parseInt(nilai['nilai']) > 0){
							$rincian_nilai += nilai['nilai'];
							if (index < jumlah_rincian_nilai - 1) {
								$rincian_nilai += ', ';
							}
						}else{
							$rincian_hukuman += nilai['nilai'];
							if (index < jumlah_rincian_nilai - 1) {
								$rincian_hukuman += ', ';
							}
						}
					});
				} else {
					$('.' + sudut + '-ronde-' + ronde+'-nilai').html('<span>&emsp;</span>');
				}

				$('.' + sudut + '-ronde-' + ronde+'-nilai').html($rincian_nilai);
				$('.' + sudut + '-ronde-' + ronde+'-hukuman').html($rincian_hukuman);
				$('.' + sudut + '-ronde-' + ronde + '-total').html(nilai['ringkasan']['nilai_akhir']);

			});

			$('#total_nilai_akhir_' + sudut).html(v.ringkasan.nilai_akhir);

			if (sudut == 'merah') {
				juri.nilai_akhir_merah = v.ringkasan.nilai_akhir;
			} else {
				juri.nilai_akhir_biru = v.ringkasan.nilai_akhir;
			}
		});

		if (juri.mode == 'monitoring-nilai') {
			if (juri.nilai_akhir_merah > juri.nilai_akhir_biru) {
				$('#total_nilai_akhir_biru').parent().removeClass('bg-gradient-180-blue');
				$('#total_nilai_akhir_biru').removeClass('text-white');
				$('#total_nilai_akhir_merah').parent().addClass('bg-gradient-180-red');
				$('#total_nilai_akhir_merah').addClass('text-white');
			} else if (juri.nilai_akhir_merah < juri.nilai_akhir_biru) {
				$('#total_nilai_akhir_merah').parent().removeClass('bg-gradient-180-red');
				$('#total_nilai_akhir_merah').removeClass('text-white');

				$('#total_nilai_akhir_biru').parent().addClass('bg-gradient-180-blue');
				$('#total_nilai_akhir_biru').addClass('text-white');
			} else {
				$('#total_nilai_akhir_merah').parent().removeClass('bg-gradient-180-red');
				$('#total_nilai_akhir_merah').removeClass('text-white');
				$('#total_nilai_akhir_biru').parent().removeClass('bg-gradient-180-blue');
				$('#total_nilai_akhir_biru').removeClass('text-white');
			}
		}

		juri.highlight_pemenang($pemenang);

	},
	set_ronde: function(){
		$('td.ronde-1, td.ronde-2, td.ronde-3').removeClass('bg-warning text-white');
		$('td.ronde-'+juri.ronde_pertandingan).addClass('bg-warning text-white');
		
	},
	hitung_ringkasan_nilai: function ($temp_nilai) {
		/**
		 * !!! digunakan untuk menghitung total ringkasan nilai, hukuman dan nilai akhir
		 */
		$.each($temp_nilai, function (sudut, v) {

			$total_nilai_sudut = 0;
			$total_hukuman_sudut = 0;
			$nilai_akhir_sudut = 0;

			$.each(v.ronde_pertandingan, function (ronde, value_ronde) {

				$total_nilai_ronde = 0;
				$total_hukuman_ronde = 0;
				$nilai_akhir_ronde = 0;

				$.each(value_ronde.rincian, function (index_nilai, nilai) {
					if (parseInt(nilai['nilai']) < 0) {
						$total_hukuman_ronde += parseInt(nilai['nilai']);
					} else {
						if (nilai['nilai'] == '1+1') {
							$total_nilai_ronde += 2;
						} else if (nilai['nilai'] == '1+2') {
							$total_nilai_ronde += 3;
						} else if (nilai['nilai'] == '1+3') {
							$total_nilai_ronde += 4;
						} else {
							$total_nilai_ronde += parseInt(nilai['nilai']);
						}
					}
				});
				$nilai_akhir_ronde = $total_nilai_ronde + $total_hukuman_ronde;

				//ganti nilai hasil hitungan terbaru ke object data_nilai
				$temp_nilai[sudut].ronde_pertandingan[ronde].ringkasan.total_nilai = $total_nilai_ronde;
				$temp_nilai[sudut].ronde_pertandingan[ronde].ringkasan.total_hukuman = $total_hukuman_ronde;
				$temp_nilai[sudut].ronde_pertandingan[ronde].ringkasan.nilai_akhir = $nilai_akhir_ronde;

				$total_nilai_sudut += $total_nilai_ronde;
				$total_hukuman_sudut += $total_hukuman_ronde;
				$nilai_akhir_sudut += $nilai_akhir_ronde;

			});

			$temp_nilai[sudut].ringkasan['total_nilai'] = $total_nilai_sudut;
			$temp_nilai[sudut].ringkasan['total_hukuman'] = $total_hukuman_sudut;
			$temp_nilai[sudut].ringkasan['nilai_akhir'] = $nilai_akhir_sudut;

		});
		return $temp_nilai;
	},
	hitung_kategori_nilai: function ($temp_nilai) {
		/**
		 * !!! digunakan untuk menghitung jumlah pukulan, tendangan dll
		 */
		$.each($temp_nilai, function (sudut, v) {

			$total_nilai_sudut = 0;
			$total_hukuman_sudut = 0;
			$nilai_akhir_sudut = 0;


			$.each(v.kategori_nilai, function (jenis_nilai, jumlah) {
				//mereset jumlah ke 0, untuk KESELURUHAN BABAK;
				$temp_nilai[sudut].kategori_nilai[jenis_nilai] = 0;
			});

			$.each(v.ronde_pertandingan, function (ronde, value_ronde) {

				$.each(value_ronde.kategori_nilai, function (jenis_nilai, jumlah) {
					//mereset jumlah ke 0, untuk PER BABAK;
					$temp_nilai[sudut].ronde_pertandingan[ronde].kategori_nilai[jenis_nilai] = 0;
				});

				$.each(value_ronde.rincian, function (index_nilai, nilai) {
					if (nilai['nilai'] == 1) {
						$temp_nilai[sudut].kategori_nilai.pukulan += 1;
						$temp_nilai[sudut].ronde_pertandingan[ronde].kategori_nilai.pukulan += 1;
					} else if (nilai['nilai'] == 2) {
						$temp_nilai[sudut].kategori_nilai.tendangan += 1;
						$temp_nilai[sudut].ronde_pertandingan[ronde].kategori_nilai.tendangan += 1;
					} else if (nilai['nilai'] == 3) {
						$temp_nilai[sudut].kategori_nilai.jatuhan += 1;
						$temp_nilai[sudut].ronde_pertandingan[ronde].kategori_nilai.jatuhan += 1;
					} else if (nilai['nilai'] == '1+1') {
						$temp_nilai[sudut].kategori_nilai.pukulan_diawali_tangkisan += 1;
						$temp_nilai[sudut].ronde_pertandingan[ronde].kategori_nilai.pukulan_diawali_tangkisan += 1;
					} else if (nilai['nilai'] == '1+2') {
						$temp_nilai[sudut].kategori_nilai.tendangan_diawali_tangkisan += 1;
						$temp_nilai[sudut].ronde_pertandingan[ronde].kategori_nilai.tendangan_diawali_tangkisan += 1;
					} else if (nilai['nilai'] == '1+3') {
						$temp_nilai[sudut].kategori_nilai.jatuhan_diawali_tangkapan += 1;
						$temp_nilai[sudut].ronde_pertandingan[ronde].kategori_nilai.jatuhan_diawali_tangkapan += 1;
					} else if (nilai['nilai'] < 0) {
						$temp_nilai[sudut].kategori_nilai.hukuman += 1;
						$temp_nilai[sudut].ronde_pertandingan[ronde].kategori_nilai.hukuman += 1;
					}
				});
			});
		});
		return $temp_nilai;
	},
	pilih_pemenang: function ($temp_nilai){
		/**
		 * Digunakan untuk memilih pemenang
		 * 
			Apabila terjadi nilai sama 
			1. Dilihat dari hukuman paling sedikit
			2. Mendapat nilai teknik terbanyak mengikuti urutan
				1+3, 3, 1+2, 1+1, 1
			3. Tambahan 1 babak penuh
			4. timbangan teringan
		 */
		$nilai_akhir_merah = $temp_nilai['merah'].ringkasan['nilai_akhir'];
		$nilai_akhir_biru = $temp_nilai['biru'].ringkasan['nilai_akhir'];
		
		if($nilai_akhir_merah == 0 &&
			$nilai_akhir_biru == 0	
		){
			return null;
		}else if($nilai_akhir_merah > $nilai_akhir_biru){
			return 'merah';
		}else if($nilai_akhir_biru > $nilai_akhir_merah){
			return 'biru';
		}else{
			// Nilai Sama
			$total_hukuman_merah = $temp_nilai['merah'].ringkasan['total_hukuman'];
			$total_hukuman_biru = $temp_nilai['biru'].ringkasan['total_hukuman'];

			if($total_hukuman_merah > $total_hukuman_biru){
				return 'merah';
			}else if($total_hukuman_biru > $total_hukuman_merah){
				return 'biru';
			}else{


				// Dilihat dari nilai teknik terbanyak 1 + 3
				$nilai_teknik_merah = $temp_nilai['merah']['kategori_nilai']['jatuhan_diawali_tangkapan'];
				$nilai_teknik_biru = $temp_nilai['biru']['kategori_nilai']['jatuhan_diawali_tangkapan']
				if($nilai_teknik_merah > $nilai_teknik_biru){
					return 'merah';
				}else if($nilai_teknik_biru > $nilai_teknik_merah){
					return 'biru';
				}else{

					
					// Dilihat dari nilai teknik terbanyak  (3)
					$nilai_teknik_merah = $temp_nilai['merah']['kategori_nilai']['jatuhan'];
					$nilai_teknik_biru = $temp_nilai['biru']['kategori_nilai']['jatuhan'];
					if($nilai_teknik_merah > $nilai_teknik_biru){
						return 'merah';
					}else if($nilai_teknik_biru > $nilai_teknik_merah){
						return 'biru';
					}else{


						// Dilihat dari nilai teknik terbanyak  (1 + 2)
						$nilai_teknik_merah = $temp_nilai['merah']['kategori_nilai']['tendangan_diawali_tangkisan'];
						$nilai_teknik_biru = $temp_nilai['biru']['kategori_nilai']['tendangan_diawali_tangkisan']
						if($nilai_teknik_merah > $nilai_teknik_biru){
							return 'merah';
						}else if($nilai_teknik_biru > $nilai_teknik_merah){
							return 'biru';
						}else{


							// Dilihat dari nilai teknik terbanyak  ( 2 )
							$nilai_teknik_merah = $temp_nilai['merah']['kategori_nilai']['tendangan'];
							$nilai_teknik_biru = $temp_nilai['biru']['kategori_nilai']['tendangan']
							if($nilai_teknik_merah > $nilai_teknik_biru){
								return 'merah';
							}else if($nilai_teknik_biru > $nilai_teknik_merah){
								return 'biru';
							}else{


								// Dilihat dari nilai teknik terbanyak  ( 1 + 1 )
								$nilai_teknik_merah = $temp_nilai['merah']['kategori_nilai']['pukulan_diawali_tangkisan'];
								$nilai_teknik_biru = $temp_nilai['biru']['kategori_nilai']['pukulan_diawali_tangkisan']
								
								if($nilai_teknik_merah > $nilai_teknik_biru){
									return 'merah';
								}else if($nilai_teknik_biru > $nilai_teknik_merah){
									return 'biru';
								}else{

									
									// Dilihat dari nilai teknik terbanyak  ( 1 )
									$nilai_teknik_merah = $temp_nilai['merah']['kategori_nilai']['pukulan'];
									$nilai_teknik_biru = $temp_nilai['biru']['kategori_nilai']['pukulan']
									if($nilai_teknik_merah > $nilai_teknik_biru){
										return 'merah';
									}else if($nilai_teknik_biru > $nilai_teknik_merah){
										return 'biru';
									}else{

										// Babak Tambahan
										return null;
									}
								}

							}
						}
					}

				}
			}
		}
	},
	highlight_pemenang: function($pemenang){
		if($pemenang == 'merah'){
			$('.score-merah').addClass('bg-gradient-180-red');
			$('.score-merah').removeClass('bg-gradient-180-dark');

			$('.score-biru').removeClass('bg-gradient-180-indigo');
			$('.score-biru').addClass('bg-gradient-180-dark');
		}else if($pemenang == 'biru'){
			$('.score-biru').addClass('bg-gradient-180-indigo');
			$('.score-biru').removeClass('bg-gradient-180-dark');

			$('.score-merah').removeClass('bg-gradient-180-red');
			$('.score-merah').addClass('bg-gradient-180-dark');
		}else{
			$('.score-biru').removeClass('bg-gradient-180-indigo');
			$('.score-merah').removeClass('bg-gradient-180-red');

			$('.score-biru').addClass('bg-gradient-180-dark');
			$('.score-merah').addClass('bg-gradient-180-dark');
		}
	},
	edit_penilaian_tanding: function ($sudut, $nilai = null) {
		/**
		 * !!! script ini juga digunakan untuk menambahkan hukuman
		 * @params int nilai, jika bernilai null, maka sedang menghapus nilai 
		 */
		$timestamp = new Date().getTime();
		$temp_nilai = juri.data_nilai;
		if ($nilai == null) {
			$temp_nilai[$sudut]['ronde_pertandingan'][juri.ronde_pertandingan]['rincian'].pop();
		} else {
			$nilai = {
				nilai: $nilai,
				waktu_pertandingan: juri.waktu_sekarang,
				timestamp: $timestamp
			}
			$temp_nilai[$sudut]['ronde_pertandingan'][juri.ronde_pertandingan]['rincian'].push($nilai);
		}

		$temp_nilai = juri.hitung_ringkasan_nilai($temp_nilai);
		$temp_nilai = juri.hitung_kategori_nilai($temp_nilai);
		$pemenang = juri.pilih_pemenang($temp_nilai);

		$.post('juri/edit-penilaian-tanding/' + juri.id_pertandingan, {
			penilaian_merah: JSON.stringify($temp_nilai['merah']),
			penilaian_biru: JSON.stringify($temp_nilai['biru']),
			pemenang : $pemenang
		},
			function (data, textStatus, jqXHR) {
				if (data.status == true) {
					juri.data_nilai = $temp_nilai;
					juri.update_tampilan_nilai();
				} else {
					Swal.fire('Error', 'Gagal mengubah penilaian', 'error');
				}
			}, "json"
		);
	},
	refresh_status_pertandingan: () => {
		$.post("perangkat-pertandingan/refresh-status-pertandingan/" + juri.id_pertandingan,
			function (data, textStatus, jqXHR) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else {
					if(juri.pertandingan.format_penilaian !== data.pertandingan.format_penilaian ||
						data.data_nilai == null
					){
						window.location.reload();
					}else{
						juri.set_variable(data.data_nilai, data.pertandingan, data.pemenang);
						juri.update_tampilan_nilai();
						juri.set_ronde();
					}
				}
			},
			"json"
		).always(function(){
			setTimeout(() => {	
				juri.refresh_status_pertandingan();
			}, 3000);
		});
	}
}
