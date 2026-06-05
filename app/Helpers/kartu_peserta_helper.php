<?php

/**
 * Helper untuk mencetak kartu peserta.
 * Port dari CI3 kartu_peserta_helper.php
 */

if (! function_exists('get_partai_pertandingan')) {
    /**
     * Mencari semua partai pertandingan tanding untuk seorang peserta,
     * mengikuti rantai nomor_pertandingan_selanjutnya hingga Final.
     *
     * @param list<object> $pertandingan
     * @return list<array{gelanggang: string, nomor_partai: string, sudut: string, babak: string}>
     */
    function get_partai_pertandingan(array $pertandingan, int $idPesertaTanding): array
    {
        $arrayPartai = [];
        $nomorPertandinganSelanjutnya = null;
        $babak = null;
        $idKompetisiTanding = null;

        foreach ($pertandingan as $vPertandingan) {
            if ((int) $vPertandingan->id_atlet_merah === $idPesertaTanding) {
                $arrayPartai[] = [
                    'gelanggang'   => $vPertandingan->nama_gelanggang ?? '',
                    'nomor_partai' => $vPertandingan->nomor_partai ?? '',
                    'sudut'        => 'merah',
                    'babak'        => $vPertandingan->babak ?? '',
                ];

                $nomorPertandinganSelanjutnya = $vPertandingan->nomor_pertandingan_selanjutnya ?? null;
                $idKompetisiTanding = $vPertandingan->id_kompetisi_tanding ?? null;
                $babak = $vPertandingan->babak ?? null;
            } elseif ((int) $vPertandingan->id_atlet_biru === $idPesertaTanding) {
                $arrayPartai[] = [
                    'gelanggang'   => $vPertandingan->nama_gelanggang ?? '',
                    'nomor_partai' => $vPertandingan->nomor_partai ?? '',
                    'sudut'        => 'biru',
                    'babak'        => $vPertandingan->babak ?? '',
                ];

                $nomorPertandinganSelanjutnya = $vPertandingan->nomor_pertandingan_selanjutnya ?? null;
                $idKompetisiTanding = $vPertandingan->id_kompetisi_tanding ?? null;
                $babak = $vPertandingan->babak ?? null;
            }

            if ($nomorPertandinganSelanjutnya !== null && $idKompetisiTanding !== null) {
                if (
                    ($vPertandingan->id_kompetisi_tanding ?? null) == $idKompetisiTanding
                    && ($vPertandingan->nomor_pertandingan ?? null) == $nomorPertandinganSelanjutnya
                ) {
                    $sudut = ((int) ($vPertandingan->nomor_pertandingan ?? 0) % 2 === 1) ? 'biru' : 'merah';

                    $arrayPartai[] = [
                        'gelanggang'   => $vPertandingan->nama_gelanggang ?? '',
                        'nomor_partai' => $vPertandingan->nomor_partai ?? '',
                        'sudut'        => $sudut,
                        'babak'        => $vPertandingan->babak ?? '',
                    ];

                    $nomorPertandinganSelanjutnya = $vPertandingan->nomor_pertandingan_selanjutnya ?? null;
                    $babak = $vPertandingan->babak ?? null;
                }
            } elseif ($babak === 'Final') {
                break;
            }
        }

        return $arrayPartai;
    }
}

if (! function_exists('get_partai_battle_seni')) {
    /**
     * Mencari semua partai battle seni untuk sebuah kelompok peserta,
     * mengikuti rantai nomor_battle_selanjutnya hingga Final.
     *
     * @param list<object> $dataBattleSeni
     * @return list<array{gelanggang: string, nomor_partai: string, sudut: string, babak: string}>
     */
    function get_partai_battle_seni(array $dataBattleSeni, int $idKelompokPesertaSeni): array
    {
        $arrayPartai = [];
        $nomorBattleSelanjutnya = null;
        $babak = null;
        $idKompetisiSeni = null;

        foreach ($dataBattleSeni as $vBattleSeni) {
            if ((int) ($vBattleSeni->id_kelompok_peserta_seni_merah ?? 0) === $idKelompokPesertaSeni) {
                $arrayPartai[] = [
                    'gelanggang'   => $vBattleSeni->nama_gelanggang ?? '',
                    'nomor_partai' => $vBattleSeni->nomor_partai ?? '',
                    'sudut'        => 'merah',
                    'babak'        => $vBattleSeni->babak_battle ?? '',
                ];

                $nomorBattleSelanjutnya = $vBattleSeni->nomor_battle_selanjutnya ?? null;
                $idKompetisiSeni = $vBattleSeni->id_kompetisi_seni_battle ?? null;
                $babak = $vBattleSeni->babak_battle ?? null;
            } elseif ((int) ($vBattleSeni->id_kelompok_peserta_seni_biru ?? 0) === $idKelompokPesertaSeni) {
                $arrayPartai[] = [
                    'gelanggang'   => $vBattleSeni->nama_gelanggang ?? '',
                    'nomor_partai' => $vBattleSeni->nomor_partai ?? '',
                    'sudut'        => 'biru',
                    'babak'        => $vBattleSeni->babak_battle ?? '',
                ];

                $nomorBattleSelanjutnya = $vBattleSeni->nomor_battle_selanjutnya ?? null;
                $idKompetisiSeni = $vBattleSeni->id_kompetisi_seni_battle ?? null;
                $babak = $vBattleSeni->babak_battle ?? null;
            }

            if ($nomorBattleSelanjutnya !== null && $idKompetisiSeni !== null) {
                if (
                    ($vBattleSeni->id_kompetisi_seni_battle ?? null) == $idKompetisiSeni
                    && ($vBattleSeni->nomor_battle ?? null) == $nomorBattleSelanjutnya
                ) {
                    $sudut = ((int) ($vBattleSeni->nomor_battle ?? 0) % 2 === 1) ? 'biru' : 'merah';

                    $arrayPartai[] = [
                        'gelanggang'   => $vBattleSeni->nama_gelanggang ?? '',
                        'nomor_partai' => $vBattleSeni->nomor_partai ?? '',
                        'sudut'        => $sudut,
                        'babak'        => $vBattleSeni->babak_battle ?? '',
                    ];

                    $nomorBattleSelanjutnya = $vBattleSeni->nomor_battle_selanjutnya ?? null;
                    $babak = $vBattleSeni->babak_battle ?? null;
                }
            } elseif ($babak === 'Final') {
                break;
            }
        }

        return $arrayPartai;
    }
}
