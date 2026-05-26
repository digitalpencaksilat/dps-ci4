<?php

namespace App\Models;

use CodeIgniter\Model;

class PerolehanMedaliSeniModel extends Model
{
    protected $table            = 'perolehan_medali_seni';
    protected $primaryKey       = 'id_perolehan_medali_seni';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_kelompok_peserta_seni',
        'jenis_medali',
    ];

    protected bool $allowEmptyInserts = false;

    public function getPemenangSeniPortal(): array
    {
        return $this->db->table($this->table)
            ->select("perolehan_medali_seni.*, kelompok_peserta_seni.*, kontingen.*, kompetisi_seni.nomor_pool, sub_kategori_seni.nama_seni, sub_kategori_seni.jenis_seni, kategori_lomba.nama_kategori_lomba, kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin,
                (SELECT GROUP_CONCAT(CONCAT_WS(' ', pendaftar.nama_pendaftar) SEPARATOR ',<br>')
                 FROM pendaftar JOIN peserta_seni ON peserta_seni.id_pendaftar = pendaftar.id_pendaftar
                 WHERE peserta_seni.id_kelompok_peserta_seni = kelompok_peserta_seni.id_kelompok_peserta_seni) as anggota_kelompok_peserta_seni,
                (SELECT GROUP_CONCAT(DISTINCT pendaftar.nama_sekolah ORDER BY pendaftar.nama_pendaftar SEPARATOR ', ')
                 FROM pendaftar JOIN peserta_seni ON peserta_seni.id_pendaftar = pendaftar.id_pendaftar
                 WHERE peserta_seni.id_kelompok_peserta_seni = kelompok_peserta_seni.id_kelompok_peserta_seni
                   AND pendaftar.nama_sekolah IS NOT NULL
                   AND pendaftar.nama_sekolah != '') as sekolah_kelompok_peserta_seni")
            ->join('kelompok_peserta_seni', 'kelompok_peserta_seni.id_kelompok_peserta_seni = perolehan_medali_seni.id_kelompok_peserta_seni')
            ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen')
            ->join('kompetisi_seni', 'kompetisi_seni.id_kompetisi_seni = kelompok_peserta_seni.id_kompetisi_seni')
            ->join('sub_kategori_seni', 'sub_kategori_seni.id_sub_kategori_seni = kompetisi_seni.id_sub_kategori_seni')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = sub_kategori_seni.id_kategori_lomba')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia')
            ->get()
            ->getResult();
    }

    public function get_prediksi_medali()
    {
        $kpsSubquery = $this->db->table('kelompok_peserta_seni')
            ->select('id_kompetisi_seni, COUNT(*) as peserta_count')
            ->groupBy('id_kompetisi_seni')
            ->getCompiledSelect();

        return $this->db->table('kompetisi_seni ks')
            ->select("
                ks.id_kompetisi_seni AS id_kom, 
                sks.id_sub_kategori_seni AS id_sks, 
                kl.nama_kategori_lomba, 
                ku.nama_kategori_usia, 
                ku.jenis_kelamin, 
                sks.jumlah_peserta AS jp, 
                sks.jenis_seni,
                sks.nama_seni,
                kl.semua_dapat_medali,

                SUM(IF(kps.peserta_count >= 1, sks.jumlah_peserta, 0)) AS emas,

                SUM(IF(kps.peserta_count >= 2, sks.jumlah_peserta, 0)) AS perak,

                SUM(IF(kps.peserta_count >= 3,
                    IF(kl.semua_dapat_medali = 1,
                        COALESCE(sks.jumlah_peserta, 0) * (kps.peserta_count - 2), 
                        IF(kps.peserta_count >= 4, 
                            COALESCE(sks.jumlah_peserta, 0) * 2, 
                            COALESCE(sks.jumlah_peserta, 0)
                        )
                    ), 
                    0
                )) AS perunggu
            ")
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join("({$kpsSubquery}) kps", 'kps.id_kompetisi_seni = ks.id_kompetisi_seni', 'left')
            ->groupBy('ku.id_kategori_usia')
            ->get()
            ->getResult();
    }
}
