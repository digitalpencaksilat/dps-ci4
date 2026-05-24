<?php

namespace App\Models;

use CodeIgniter\Model;

class PesertaTandingModel extends Model
{
    protected $table            = 'peserta_tanding';
    protected $primaryKey       = 'id_peserta_tanding';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_pendaftar',
        'id_kompetisi_tanding',
        'id_pembayaran',
        'nomor_bagan',
        'keterangan',
        'status',
        'status_sertifikat',
        'nomor_sertifikat',
    ];

    protected bool $allowEmptyInserts = false;

    public function baseSekretariatQuery()
    {
        return $this->db->table($this->table . ' pt')
            ->select([
                'pt.*',
                'p.id_kontingen',
                'p.nama_pendaftar',
                'p.jenis_kelamin',
                'p.berat_badan',
                'p.tinggi_badan',
                'p.tanggal_lahir',
                'p.nama_sekolah',
                'p.nomor_induk_kependudukan',
                'p.nomor_kartu_keluarga',
                'k.nama_kontingen',
                'k.jenis_kontingen',
                'kom.nomor_pool',
                'kom.max_peserta',
                'kt.label',
                'kt.berat_minimal',
                'kt.berat_maksimal',
                'kt.id_kelas_tanding',
                'kt.biaya_pendaftaran_dn',
                'kt.biaya_pendaftaran_ln',
                'kl.jenis_perlombaan',
                'ku.nama_kategori_usia',
                'ku.min_umur',
                'ku.max_umur',
                '(SELECT status_pembayaran FROM pembayaran WHERE pembayaran.id_pembayaran = pt.id_pembayaran) AS status_pembayaran',
                "(SELECT DATE_FORMAT(tanggal_pembayaran, '%a, %d %M %Y') FROM pembayaran WHERE pembayaran.id_pembayaran = pt.id_pembayaran) AS tanggal_pembayaran",
                '(SELECT jenis_medali FROM perolehan_medali_tanding WHERE perolehan_medali_tanding.id_peserta_tanding = pt.id_peserta_tanding) AS jenis_medali',
                '(SELECT COUNT(*) FROM peserta_tanding pt2 WHERE pt2.id_kompetisi_tanding = pt.id_kompetisi_tanding) AS jumlah_peserta_tanding',
                '(SELECT COUNT(*) FROM peserta_tanding pt2 JOIN pendaftar p2 ON p2.id_pendaftar = pt2.id_pendaftar WHERE pt2.id_kompetisi_tanding = pt.id_kompetisi_tanding AND p2.id_kontingen = p.id_kontingen) AS jumlah_peserta_tanding_kontingen_sama',
                'TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) AS umur_pendaftar',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding', 'left')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left');
    }

    public function findDetailed(int $idPesertaTanding): ?object
    {
        return $this->baseSekretariatQuery()
            ->where('pt.id_peserta_tanding', $idPesertaTanding)
            ->get()
            ->getRow();
    }
}
