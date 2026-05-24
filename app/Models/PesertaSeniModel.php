<?php

namespace App\Models;

use CodeIgniter\Model;

class PesertaSeniModel extends Model
{
    protected $table            = 'peserta_seni';
    protected $primaryKey       = 'id_peserta_seni';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_pendaftar',
        'id_kelompok_peserta_seni',
        'status_sertifikat',
        'nomor_sertifikat',
    ];

    protected bool $allowEmptyInserts = false;

    public function baseSekretariatQuery()
    {
        return $this->db->table($this->table . ' ps')
            ->select([
                'ps.*',
                'p.id_kontingen',
                'p.nama_pendaftar',
                'p.jenis_kelamin',
                'p.berat_badan',
                'p.tinggi_badan',
                'p.nama_sekolah',
                'k.nama_kontingen',
                'kps.id_kompetisi_seni',
                'kps.nomor_undi',
                'kom.nomor_pool',
                'sks.nama_seni',
                'sks.jenis_seni',
                'sks.sistem_penampilan',
                'ku.nama_kategori_usia',
                '(SELECT jenis_medali FROM perolehan_medali_seni WHERE perolehan_medali_seni.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS jenis_medali',
                '(SELECT GROUP_CONCAT(CONCAT_WS(" ", p2.nama_pendaftar) SEPARATOR " ,<br> ") FROM pendaftar p2 JOIN peserta_seni ps2 ON ps2.id_pendaftar = p2.id_pendaftar WHERE ps2.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left');
    }

    public function findDetailed(int $idPesertaSeni): ?object
    {
        return $this->baseSekretariatQuery()
            ->where('ps.id_peserta_seni', $idPesertaSeni)
            ->get()
            ->getRow();
    }
}
