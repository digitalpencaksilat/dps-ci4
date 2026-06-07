<?php

namespace App\Services;

/**
 * Service data untuk fitur Pencetakan Bagan (parity CI3 Sekretariat::cetak_semua_bagan
 * + Kategori_lomba::cetak_bagan). Menyediakan daftar kategori lomba beserta kompetisi
 * (tanding / seni battle / seni pool) yang punya bagan untuk dicetak.
 */
class PencetakanBaganService
{
    /**
     * Daftar kategori lomba untuk halaman index pencetakan (tanding + seni),
     * lengkap dengan ringkasan jumlah pool yang sudah punya bagan.
     *
     * @return array<int,object>
     */
    public function listKategoriLomba(): array
    {
        return db_connect()->table('kategori_lomba kl')
            ->select('kl.id_kategori_lomba, kl.nama_kategori_lomba, kl.jenis_perlombaan, kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin, ku.min_umur')
            ->select('(SELECT COUNT(*) FROM kompetisi_tanding kom JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding WHERE kt.id_kategori_lomba = kl.id_kategori_lomba AND kom.bagan_pertandingan IS NOT NULL AND kom.bagan_pertandingan != "") AS jumlah_bagan_tanding', false)
            ->select('(SELECT COUNT(*) FROM kompetisi_seni ks JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba AND sks.sistem_penampilan = "battle" AND ks.bagan_battle_seni IS NOT NULL AND ks.bagan_battle_seni != "") AS jumlah_bagan_seni_battle', false)
            ->select('(SELECT COUNT(*) FROM kompetisi_seni ks JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba AND sks.sistem_penampilan = "pool") AS jumlah_pool_seni', false)
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('kl.nama_kategori_lomba', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->get()->getResult();
    }

    public function getKategoriLomba(int $idKategoriLomba): ?object
    {
        return db_connect()->table('kategori_lomba kl')
            ->select('kl.id_kategori_lomba, kl.nama_kategori_lomba, kl.jenis_perlombaan, kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kl.id_kategori_lomba', $idKategoriLomba)
            ->get()->getRow();
    }

    /**
     * Kompetisi tanding yang punya peserta (parity: jumlah_peserta_tanding > 1).
     * Jika $idKategoriLomba null, ambil seluruh kategori tanding (cetak semua).
     *
     * @return array<int,object>
     */
    public function listKompetisiTanding(?int $idKategoriLomba = null): array
    {
        $builder = $this->kompetisiTandingBaseQuery();
        if ($idKategoriLomba !== null) {
            $builder->where('kt.id_kategori_lomba', $idKategoriLomba);
        }

        return $builder
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    /**
     * Kompetisi seni battle yang punya bagan (parity: jumlah_kelompok > 1, sistem battle).
     *
     * @return array<int,object>
     */
    public function listKompetisiSeniBattle(?int $idKategoriLomba = null): array
    {
        $builder = $this->kompetisiSeniBaseQuery('battle');
        if ($idKategoriLomba !== null) {
            $builder->where('sks.id_kategori_lomba', $idKategoriLomba);
        }

        return $builder
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->orderBy('ks.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    /**
     * Kompetisi seni pool (sistem pool). Hasil penampilan diambil terpisah per pool.
     *
     * @return array<int,object>
     */
    public function listKompetisiSeniPool(?int $idKategoriLomba = null): array
    {
        $builder = $this->kompetisiSeniBaseQuery('pool');
        if ($idKategoriLomba !== null) {
            $builder->where('sks.id_kategori_lomba', $idKategoriLomba);
        }

        return $builder
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->orderBy('ks.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    /**
     * Daftar penampilan (hasil) untuk satu pool seni, parity dengan tabel hasil pool legacy.
     *
     * @return array<int,object>
     */
    public function listPenampilanPool(int $idKompetisiSeni): array
    {
        return db_connect()->table('detail_jadwal_seni djs')
            ->select('djs.nomor_partai, ps.nilai_akhir, ps.waktu_tampil, ps.diskualifikasi, kps.nomor_undi, k.nama_kontingen, pms.jenis_medali AS jenis_medali_pool')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni psn JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE psn.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni', false)
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('perolehan_medali_seni pms', 'pms.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni', 'left')
            ->where('kps.id_kompetisi_seni', $idKompetisiSeni)
            ->where('djs.id_penampilan_seni IS NOT NULL', null, false)
            ->where('djs.id_battle_seni IS NULL', null, false)
            ->orderBy('djs.nomor_partai', 'ASC')
            ->get()->getResult();
    }

    private function kompetisiTandingBaseQuery()
    {
        return db_connect()->table('kompetisi_tanding kom')
            ->select('kom.id_kompetisi_tanding, kom.nomor_pool, kom.bagan_pertandingan, kt.id_kategori_lomba, kt.label, kt.berat_minimal, kt.berat_maksimal, kt.juara_tiga_bersama, kl.peraturan_pertandingan, kl.jenis_perlombaan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('CASE WHEN ku.jenis_kelamin = "putra" THEN "Men" WHEN ku.jenis_kelamin = "putri" THEN "Women" END AS gender', false)
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta_tanding', false)
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kt.label !=', 'sisipan')
            ->where('kom.bagan_pertandingan IS NOT NULL', null, false)
            ->where('kom.bagan_pertandingan !=', '')
            ->where('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) > 1', null, false);
    }

    private function kompetisiSeniBaseQuery(string $sistemPenampilan)
    {
        $builder = db_connect()->table('kompetisi_seni ks')
            ->select('ks.id_kompetisi_seni, ks.nomor_pool, ks.bagan_battle_seni, sks.id_kategori_lomba, sks.nama_seni, sks.jenis_seni, sks.sistem_penampilan, sks.juara_tiga_bersama, kl.jenis_perlombaan, kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('CASE WHEN ku.jenis_kelamin = "putra" THEN "Men" WHEN ku.jenis_kelamin = "putri" THEN "Women" END AS gender', false)
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kompetisi_seni = ks.id_kompetisi_seni) AS jumlah_kelompok_peserta_seni', false)
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('sks.sistem_penampilan', $sistemPenampilan)
            ->where('(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kompetisi_seni = ks.id_kompetisi_seni) > 1', null, false);

        if ($sistemPenampilan === 'battle') {
            $builder->where('ks.bagan_battle_seni IS NOT NULL', null, false)
                ->where('ks.bagan_battle_seni !=', '');
        }

        return $builder;
    }
}
