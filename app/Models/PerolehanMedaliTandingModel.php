<?php

namespace App\Models;

use CodeIgniter\Model;

class PerolehanMedaliTandingModel extends Model
{
    protected $table            = 'perolehan_medali_tanding';
    protected $primaryKey       = 'id_perolehan_medali_tanding';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_peserta_tanding',
        'jenis_medali',
    ];

    protected bool $allowEmptyInserts = false;

    public function getPemenangTandingPortal(): array
    {
        return $this->db->table($this->table)
            ->select('perolehan_medali_tanding.*, peserta_tanding.*, pendaftar.*, kontingen.*, kompetisi_tanding.nomor_pool, kelas_tanding.label, kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin')
            ->join('peserta_tanding', 'peserta_tanding.id_peserta_tanding = perolehan_medali_tanding.id_peserta_tanding')
            ->join('pendaftar', 'pendaftar.id_pendaftar = peserta_tanding.id_pendaftar')
            ->join('kontingen', 'kontingen.id_kontingen = pendaftar.id_kontingen')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = peserta_tanding.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia')
            ->get()
            ->getResult();
    }

    public function get_prediksi_medali()
    {
        $subquery = $this->db->table('kompetisi_tanding kt')
            ->select('
                kt.id_kompetisi_tanding as id_kom,
                kl.id_kelas_tanding as id_kt,
                klb.nama_kategori_lomba,
                ku.nama_kategori_usia,
                ku.id_kategori_usia,
                ku.jenis_kelamin,
                klb.semua_dapat_medali,
                COUNT(pt.id_peserta_tanding) as peserta_count
            ')
            ->join('kelas_tanding kl', 'kl.id_kelas_tanding = kt.id_kelas_tanding')
            ->join('kategori_lomba klb', 'klb.id_kategori_lomba = kl.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = klb.id_kategori_usia')
            ->join('peserta_tanding pt', 'pt.id_kompetisi_tanding = kt.id_kompetisi_tanding', 'left')
            ->groupBy('kt.id_kompetisi_tanding');

        return $this->db->table("({$subquery->getCompiledSelect()}) sub")
            ->select("
                sub.id_kom, 
                sub.id_kt, 
                sub.nama_kategori_lomba, 
                sub.nama_kategori_usia, 
                sub.jenis_kelamin, 
                sub.semua_dapat_medali,
                SUM(IF(sub.peserta_count >= 1, 1, 0)) as emas,
                SUM(IF(sub.peserta_count >= 2, 1, 0)) as perak,
                SUM(
                    IF(sub.peserta_count >= 3, 
                        IF(sub.semua_dapat_medali = 1, 
                            (sub.peserta_count - 2),
                            IF(sub.peserta_count >= 4, 2, 1)
                        ), 
                    0)
                ) as perunggu
            ")
            ->groupBy('sub.id_kategori_usia')
            ->get()
            ->getResult();
    }
}
