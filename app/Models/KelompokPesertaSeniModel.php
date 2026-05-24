<?php

namespace App\Models;

use CodeIgniter\Model;

class KelompokPesertaSeniModel extends Model
{
    protected $table            = 'kelompok_peserta_seni';
    protected $primaryKey       = 'id_kelompok_peserta_seni';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_kompetisi_seni',
        'id_kontingen',
        'id_pembayaran',
        'status',
        'keterangan',
        'nomor_undi',
    ];

    protected bool $allowEmptyInserts = false;

    public function baseSekretariatQuery()
    {
        return $this->db->table($this->table . ' kps')
            ->select([
                'kps.*',
                'k.nama_kontingen',
                'k.jenis_kontingen',
                'kom.nomor_pool',
                'kom.max_peserta',
                'sks.nama_seni',
                'sks.jenis_seni',
                'sks.jumlah_peserta',
                'sks.sistem_penampilan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                '(SELECT status_pembayaran FROM pembayaran WHERE pembayaran.id_pembayaran = kps.id_pembayaran) AS status_pembayaran',
                "(SELECT DATE_FORMAT(tanggal_pembayaran, '%a, %d %M %Y') FROM pembayaran WHERE pembayaran.id_pembayaran = kps.id_pembayaran) AS tanggal_pembayaran",
                '(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ",<br>") FROM pendaftar p JOIN peserta_seni ps ON ps.id_pendaftar = p.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni',
                '(SELECT GROUP_CONCAT(TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) SEPARATOR ",<br>") FROM pendaftar p JOIN peserta_seni ps ON ps.id_pendaftar = p.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS umur_anggota_kelompok_peserta_seni',
                '(SELECT GROUP_CONCAT(p.nama_sekolah SEPARATOR ",<br>") FROM pendaftar p JOIN peserta_seni ps ON ps.id_pendaftar = p.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS nama_sekolah',
                '(SELECT COUNT(*) FROM peserta_seni ps WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS jumlah_peserta_seni',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps2 WHERE kps2.id_kompetisi_seni = kps.id_kompetisi_seni) AS jumlah_kelompok_peserta_seni',
                '(SELECT jenis_medali FROM perolehan_medali_seni WHERE perolehan_medali_seni.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS jenis_medali',
            ])
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left');
    }

    public function findDetailed(int $idKelompok): ?object
    {
        return $this->baseSekretariatQuery()
            ->where('kps.id_kelompok_peserta_seni', $idKelompok)
            ->get()
            ->getRow();
    }
}
