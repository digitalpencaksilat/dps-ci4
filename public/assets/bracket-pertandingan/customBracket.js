function change_matchData($peserta, $juara_tiga_bersama) {
    $jumlah_peserta = $peserta.length;
    $jumlah_pertandingan_awal = hitung_pertandingan($jumlah_peserta); //JUMLAH PERTANDINGAN PADA RONDE AWAL
    // create_new_matchdata berfungsi membuat arrayobject bagan yang isi arraynya adalah null
    $matchData = create_new_matchdata($jumlah_pertandingan_awal);
    //$array_peserta adalah wadah id untuk peserta_tanding
    $array_peserta = create_array_peserta($jumlah_pertandingan_awal);


    /** 
        BLOK KODE UNTUK MEMASUKKAN TEAM
        kode di bawah ini sudah termasuk penanganan BYE. Pola penyebaran peserta telah didokumentasikan
        oleh pengembang program jquery.bracket 
    **/

    //index_peserta digunakan untuk memanggil anggota array $peserta dari hasil query database
    var index_peserta = 0;

    //let i digunakan sebagai pola penyebaran peserta pada kolom pertandingan
    for (let i = 0; i <= 1; i++) {
        for (let j = 0; j < $jumlah_pertandingan_awal / 2; j++) {
            /*
                بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيم   
                dikarenakan program jquery bracket tidak memiliki fitur id_team, maka diperlukan $array_peserta sebagai wadah sementara id_peserta_tanding, setelah itu akan dipergunakan saat meninputkan data pertandingan
            */

            if (index_peserta < $jumlah_peserta) {
                $array_peserta[(j)][i] = $peserta[index_peserta][2];
                // UNTUK INSERT PESERTA KE DALAM BAGAN BLOK ATAS
                $matchData.teams[j][i] = '<p class="nama_atlet_bagan bye">' +$peserta[index_peserta][0] + '</p><p class="kontingen_bagan">' + $peserta[index_peserta][1] + '</p>';
                index_peserta++;
            }

            if (index_peserta < $jumlah_peserta) {
                // UNTUK INSERT PESERTA KE DALAM BAGAN BLOK BAWAH
                let h = ($jumlah_pertandingan_awal / 2) + j; // untuk index dimensi pertama dari object $matchData.teams[h]
                if (h >= 1) {
                    //untuk babak yang tidak langsung final (lebih dari 1 Pertandingan awal), jika langsung final maka kode tidak dieksekusi karena pada bagan yang langsung final tidak ada pertandingan blok bawah
                    $array_peserta[h][i] = $peserta[index_peserta][2];
                    $matchData.teams[h][i] = '<p class="nama_atlet_bagan bye">' +$peserta[index_peserta][0] + '</p><p class="kontingen_bagan">' + $peserta[index_peserta][1] + "</p>";
                    index_peserta++;
                }
            }
        }
    }



    /*
    BLOK KODE UNTUK MEMBUAT ARRAY PERTANDINGAN YANG NANTINYA AKAN DIINSERT KE DATABASE 
    round_index mendefinisikan index ronda pertandingan seperti 1/4 final, final etc
    */
    var round_index = 0;
    var nomor_pertandingan = 1;
    var $data_pertandingan = []; // array yang akan diinsert ke database

    for (var i = $jumlah_pertandingan_awal; i >= 1; i /= 2) {
        /*
            VARIABLE i ADALAH VARIBALE YANG MENENTUKAN BABAK PERTANDINGAN SEPERTI 1/8 FINAL. 1/4 FINAL DST
        */
        for (var pertandingan = 0; pertandingan < i; pertandingan++) {
            $matchData.results[0][round_index][pertandingan] = [null, null, ' Pertandingan ke ' + nomor_pertandingan + ' (' + get_babak(i) + ')'];


            /*
                Blok kode dibawah ini adalah untuk mendapatkan id_atlet SAJA. untuk id_pertandingan dll ada di blok bawahnya
            */
            $id_pesilat_merah = get_id_atlet(nomor_pertandingan, 0);
            $id_pesilat_biru = get_id_atlet(nomor_pertandingan, 1);
            if ($id_pesilat_merah != 'null' && $id_pesilat_biru == 'null') {
                // jika pesilat merah menang bye
                $id_pemenang = $id_pesilat_merah;
                $jenis_kemenangan = 'BYE';
                $status = 'selesai';
            } else if ($id_pesilat_merah == 'null' && $id_pesilat_biru != 'null') {
                //jika pesilat biru menang bye
                $id_pemenang = $id_pesilat_biru;
                $jenis_kemenangan = 'BYE';
                $status = 'selesai';
            } else if ($id_pesilat_merah != 'null' && $id_pesilat_biru != 'null') {
                //jika pertandingan belum dilaksanakan dan pesilat merah biru ada
                $id_pemenang = 'null';
                $jenis_kemenangan = 'null';
                $status = 'belum_dimulai';
            } else {
                //jika pesilat merah biru sama sama berasal dari pertandingan dengan kemenangan bye sehingga harus otomatis membuat pertandingan selanjutnya;

                /*
                    id_match_1 dan id_match_2 adalah id_pertandingan pada babak tanding sebelumnya, misalnya pertandingan sekarang adalah Semi Final, maka pertandingan sebelumnya adalah pertandingan 1/4 Final;
                */
                $id_match_1 = ((nomor_pertandingan - $jumlah_pertandingan_awal) * 2) - 2;
                $id_match_2 = ((nomor_pertandingan - $jumlah_pertandingan_awal) * 2) - 1;
                $id_pemenang = 'null';
                $jenis_kemenangan = 'null';
                $id_pesilat_merah = $data_pertandingan[$id_match_1].id_pemenang;
                $id_pesilat_biru = $data_pertandingan[$id_match_2].id_pemenang;
            }

            /* nomor_pertandingan_selanjutnya adalah nomor_pertandingan pada babak selanjutnya sesuai dengan bagan
            
                nomor_pertandingan_selanjutnya diperoleh dengan cara menjumlahkan $jumlah_pertandingan_awal dengan nomor pertandingan saat ini dibagi 2 ($jumlah_pertandingan_awal + parseInt((nomor_pertandingan / 2).toFixed()) , 
                menggunakan toFixed karena hasil pembagian menjadi desimal seperti 0.5 yang dibulatkan menjadi 1.
                ini merupakan keterbatasan algoritma yg kami buat
            */
            if (i == 1) {
                // pertandingan final
                $nomor_pertandingan_selanjutnya = 'null';
            } else {
                $nomor_pertandingan_selanjutnya = $jumlah_pertandingan_awal + parseInt((nomor_pertandingan / 2).toFixed());
                /*nomor_pertandingan_selanjutnya*/
            }

            $data_pertandingan.push(
                {
                    nomor_pertandingan: nomor_pertandingan,
                    nomor_pertandingan_selanjutnya: $nomor_pertandingan_selanjutnya,
                    id_pesilat_merah: $id_pesilat_merah,
                    id_pesilat_biru: $id_pesilat_biru,
                    babak: get_babak(i),
                    jenis_kemenangan: $jenis_kemenangan,
                    id_pemenang: $id_pemenang
                })

            nomor_pertandingan++;
            /*UNTUK KOMPETISI DENGAN PEREBUTAN JUARA 3 (Bukan juara 3 bersama) */
            if ($juara_tiga_bersama == 0 && i == 1) {
                $data_pertandingan.push(
                    {
                        nomor_pertandingan: nomor_pertandingan,
                        nomor_pertandingan_selanjutnya: $nomor_pertandingan_selanjutnya,
                        id_pesilat_merah: $id_pesilat_merah,
                        id_pesilat_biru: $id_pesilat_biru,
                        babak: 'Perebutan Juara Tiga',
                        jenis_kemenangan: $jenis_kemenangan,
                        id_pemenang: $id_pemenang
                    })
                $matchData.results[0][round_index][1] = [null, null, ' Pertandingan ke ' + nomor_pertandingan + ' (Perebutan Juara Tiga)'];
            }

        }
        round_index++;
    }

    /*BLOK KODE UNTUK MEMBUAT ARRAY PERTANDINGAN*/
    return { matchData: JSON.stringify($matchData), data_pertandingan: JSON.stringify($data_pertandingan) };
}

function create_new_matchdata($jumlah_pertandingan_awal) {
    /*
    Fungsi ini untuk membuat array object matchdata sesuai dengan jumlah pertandingan
    */
    // untuk menginsert team
    $matchData = { teams: [[]], results: [[[]]] };
    for (let index = 0; index < $jumlah_pertandingan_awal; index++) {
        $matchData.teams[index] = [null, null]
    }
    // untuk menginsert hasil pertandingan
    var round_index = 0;
    for (var i = $jumlah_pertandingan_awal; i >= 1; i /= 2) {
        $matchData.results[0][round_index] = [];
        for (var pertandingan = 0; pertandingan < i; pertandingan++) {
            $matchData.results[0][round_index][pertandingan] = [null, null];// membuat array hasil
        }
        round_index++;
    }
    return $matchData;
}

function create_array_peserta($jumlah_pertandingan_awal) {
    /*
    Fungsi ini untuk membuat wadah kosong $array_peserta
    */
    // untuk menginsert team, Harus menggunakan "null"
    $array_peserta = [[]];
    for (let index = 0; index < $jumlah_pertandingan_awal; index++) {
        $array_peserta[index] = ["null", "null"]
    }
    return $array_peserta;
}

function get_babak($babak) {
    /*
    fungsi ini untuk mendapatkan jenis babak pertandingan berdasarkan jumlah pertandingan
    contoh : jumlah pertandingan = 4, maka jenis babaknya adalah 1/4 final
    */
    if ($babak == 1) {
        return 'Final';
    } else if ($babak == 2) {
        return 'Semi Final'
    } else {
        return '1/' + $babak + ' Final'
    }
}

function get_id_atlet($nomor_pertandingan, $sudut) {
    /*
    untuk $nomor_pertandingan perlu dikurangi 1 karena index array berawal dari 0
    sudut merah = 0;
    sudut biru = 1;
    untuk peserta bye, maka variable id akan bernilai undefined
    */
    if ($nomor_pertandingan <= $jumlah_pertandingan_awal) {
        return $array_peserta[($nomor_pertandingan - 1)][$sudut];
    } else {
        return 'null';
    }
}

function hitung_pertandingan($jumlah_peserta) {
    for (let i = 1; i <= 7; i++) { // kapasitas peserta sementara sampai dengan 2 pangkat 7 peserta ( 128 peserta)
        if (Math.pow(2, i) >= $jumlah_peserta) {
            /*
            mencari jumlah pertandingan yang paling dekat dengan jumlah peserta. 
            contoh, apabila jumlah peserta = 20; maka jumlah pertandingan adalah 32 / 2 = 16 pertandingan.
            */

            return Math.pow(2, i) / 2;
        }
    }
    console.log('Peserta melebihi 128 orang')
}

function cut_nama(nama) {
    var new_nama = nama.split(" ");
    if (new_nama.length > 1) {
        return new_nama[0] + " " + new_nama[1];
    } else {
        return new_nama[0];
    }
}
