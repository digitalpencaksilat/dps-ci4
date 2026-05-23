const seni = {
    data_nilai: null,
    penampilan_seni_berlangsung: null,
    waktu_tampil: 1,
    stopwatch: null,
    ringkasan_nilai: null,
    broadcast_graphic: {
        id_broadcast_graphic: null,
        scene: null,
        status: null,
        init: function ($broadcast_graphic) {
            seni.broadcast_graphic.set_variable($broadcast_graphic);
            seni.broadcast_graphic.refresh_animation();
            seni.broadcast_graphic.refresh_status_broadcast_graphic();
        },
        refresh_animation: function () {
            if (seni.broadcast_graphic.status == 'active') {
                seni.broadcast_graphic.animate_in();
            } else if (seni.broadcast_graphic.status == 'deactive') {
                seni.broadcast_graphic.animate_out();
            } else if (seni.broadcast_graphic.status == 'timed-3s') {
                seni.broadcast_graphic.animate_in();
                setTimeout(() => {
                    seni.broadcast_graphic.deactive();
                }, 10000); // 6000 + 3000
            } else if (seni.broadcast_graphic.status == 'timed-5s') {
                seni.broadcast_graphic.animate_in();
                setTimeout(() => {
                    seni.broadcast_graphic.deactive();
                }, 13000); // 6000 + 3000
            } else if (seni.broadcast_graphic.status == 'refresh') {
                seni.broadcast_graphic.animate_out();
                setTimeout(() => {
                    seni.broadcast_graphic.deactive(); // setiap tombol refresh di operator ditekan, maka graphic akan mati / deactive
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                }, 4000);
            }
        },
        set_variable: function ($broadcast_graphic) {
            seni.broadcast_graphic.id_broadcast_graphic = $broadcast_graphic.id_broadcast_graphic;
            seni.broadcast_graphic.scene = $broadcast_graphic.scene;
            seni.broadcast_graphic.status = $broadcast_graphic.status;
            seni.broadcast_graphic.autorefresh = parseInt($broadcast_graphic.autorefresh);
        },
        change_duration: function ($type = "forward") {
            $('.' + $type + '.animated').each(function (i, e) {
                if ($(e).hasClass('delay-1s')) {
                    $(e).removeClass('delay-1s').addClass('delay-5s');
                } else if ($(e).hasClass('delay-2s')) {
                    $(e).removeClass('delay-2s').addClass('delay-4s');
                } else if ($(e).hasClass('delay-3s')) {
                    $(e).removeClass('delay-3s').addClass('delay-3s');
                } else if ($(e).hasClass('delay-4s')) {
                    $(e).removeClass('delay-4s').addClass('delay-2s');
                } else if ($(e).hasClass('delay-5s')) {
                    $(e).removeClass('delay-5s').addClass('delay-1s');
                } else if ($(e).hasClass('delay-6s')) {
                    $(e).removeClass('delay-6s');
                } else {
                    $(e).removeClass('delay-1s').addClass('delay-6s');
                }
            })
        },
        animate_in: function () {
            seni.broadcast_graphic.change_duration('forward');
            $('.slide-in-down').addClass('backward animated slideInDown').removeClass('forward slideOutUp opacity');
            $('.slide-in-up').addClass('backward animated slideInUp').removeClass('forward slideOutDown opacity');
            $('.slide-in-right').addClass('backward animated slideInRight').removeClass('forward slideOutRight opacity');
            $('.slide-in-left').addClass('backward animated slideInLeft').removeClass('forward slideOutLeft opacity');
            $('.flip-in-x').addClass('backward animated flipInX').removeClass('forward flipOutX opacity');
        },
        animate_out: function () {
            if ($('.opacity').length == 0) {
                seni.broadcast_graphic.change_duration('backward');
                // Apabila pada awal load halaman sudah posisi deactive, maka tidak perlu menjalankan semua animasi
                $('.slide-in-down').addClass('forward animated slideOutUp').removeClass('backward slideInDown opacity');
                $('.slide-in-up').addClass('forward animated slideOutDown').removeClass('backward slideInUp opacity')
                $('.slide-in-right').addClass('forward animated slideOutRight').removeClass('backward slideInLeft opacity');
                $('.slide-in-left').addClass('forward animated slideOutLeft').removeClass('backward slideInright opacity');
                $('.flip-in-x').addClass('forward animated flipOutX').removeClass('backward flipInX opacity');
            }
        },
        refresh_status_broadcast_graphic: () => {
            $.post("broadcast-graphic/refresh-status-broadcast-graphic/" + seni.broadcast_graphic.id_broadcast_graphic,
                function (data, textStatus, jqXHR) {
                    seni.broadcast_graphic.set_variable(data);
                    seni.broadcast_graphic.refresh_animation();
                },
                "json"
            ).always(function () {
                setTimeout(() => {
                    seni.broadcast_graphic.refresh_status_broadcast_graphic();
                }, 2000);
            });
        },
        active: function (callback) {
            $.post("broadcast-graphic/update/" + seni.broadcast_graphic.id_broadcast_graphic,
                { 'status': 'active' }
                ,
                function (data, textStatus, jqXHR) {
                    callback();
                },
                "json"
            );
        },
        deactive: function () {
            $.post("broadcast-graphic/update/" + seni.broadcast_graphic.id_broadcast_graphic,
                { 'status': 'deactive' }
                ,
                function (data, textStatus, jqXHR) {

                },
                "json"
            );
        }
    },
    init: function ($data_nilai, $penampilan_seni_berlangsung, $id_gelanggang) {
        seni.set_variable($data_nilai, $penampilan_seni_berlangsung, $id_gelanggang);
        seni.update_tampilan_nilai();
        seni.update_timer();
        seni.refresh_status_seni();
    },
    set_variable: function ($data_nilai, $penampilan_seni_berlangsung, $id_gelanggang) {
        seni.data_nilai = $data_nilai;
        seni.penampilan_seni_berlangsung = $penampilan_seni_berlangsung;
        seni.id_gelanggang = $id_gelanggang;

        if ($penampilan_seni_berlangsung.ringkasan_nilai !== undefined && $penampilan_seni_berlangsung.ringkasan_nilai !== '') {
            seni.ringkasan_nilai = JSON.parse($penampilan_seni_berlangsung.ringkasan_nilai);
        }


        seni.stopwatch = $('.stopwatch');
        seni.waktu_tampil = $penampilan_seni_berlangsung.waktu_tampil;
    },
    update_tampilan_nilai: function () {
        if(seni.penampilan_seni_berlangsung.nilai_akhir !== null){
            $('.nilai_akhir').html(seni.penampilan_seni_berlangsung.nilai_akhir.toFixed(4));
        }
        
        $.each(seni.data_nilai[seni.penampilan_seni_berlangsung.id_penampilan_seni], function (index_juri, penilaian_juri) {
            $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;

            $.each($penilaian.unsur_nilai, function (jenis_unsur_nilai, value_unsur_nilai) {
                $('.' + jenis_unsur_nilai + '_' + penilaian_juri.id_perangkat_pertandingan).html(value_unsur_nilai.nilai_diperoleh)
            });

            $('.nilai_akhir' + '_' + penilaian_juri.id_perangkat_pertandingan).html($penilaian.ringkasan.nilai_akhir)
            $('.total_hukuman' + '_' + penilaian_juri.id_perangkat_pertandingan).html($penilaian.ringkasan.total_hukuman)

            if (penilaian_juri.terpilih == 1) {
                $('.juri_' + penilaian_juri.id_perangkat_pertandingan).addClass('bg-gradient-180-warning text-white').removeClass('text-decoration-line-through');
            } else {
                $('.juri_' + penilaian_juri.id_perangkat_pertandingan).addClass('text-decoration-line-through').removeClass('bg-gradient-180-warning text-white');
            }
        
        });

        if(seni.penampilan_seni_berlangsung.catatan_nilai_sama !== ''){
            $catatan_nilai_sama = JSON.parse(seni.penampilan_seni_berlangsung.catatan_nilai_sama);
            $.each($catatan_nilai_sama, function (i, kompenen_nilai_sama) { 
                 $('.'+i).html(kompenen_nilai_sama);
            });
        }
    },
    update_timer: function () {
        seni.stopwatch.timer({
            format: '%M:%S',
            action: 'start',
            seconds: seni.penampilan_seni_berlangsung.waktu_tampil
        })
        if (seni.penampilan_seni_berlangsung.status_penampilan !== 'sedang_tampil') {
            seni.stopwatch.timer('remove');
        }

    },
    refresh_status_seni: () => {
        // DIGUNAKAN UNTUK MENGAMBIL DATA NILAI
        $.post("broadcast-graphic/refresh-status-seni/" + seni.id_gelanggang + '/' + seni.penampilan_seni_berlangsung.id_penampilan_seni,
            function (data, textStatus, jqXHR) {
                if (data.status === true && data.reload === true) {
                    if (seni.broadcast_graphic.autorefresh == 1) {
                        seni.broadcast_graphic.deactive();
                        setTimeout(() => {
                            seni.broadcast_graphic.active(
                                function () {
                                    window.location.reload();
                                }
                            );
                        }, 9000);
                    }
                } else {
                    seni.set_variable(data.data_nilai, data.penampilan_seni_berlangsung, seni.id_gelanggang);
                    seni.update_tampilan_nilai();
                    seni.update_timer();
                    setTimeout(() => {
                        seni.refresh_status_seni();
                    }, 1000);
                }
            },
            "json"
        );
    },
    refresh_status_seni_standby: ($id_gelanggang) => {
        $.post("broadcast-graphic/refresh-status-seni-standby/" + $id_gelanggang,
            function (data, textStatus, jqXHR) {
                if (data.reload === true) {
                    location.reload();
                } else {
                    setTimeout(() => {
                        seni.refresh_status_seni_standby($id_gelanggang);
                    }, 4000);
                }
            },
            "json"
        );
    },
}
