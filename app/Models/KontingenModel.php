<?php

namespace App\Models;

use CodeIgniter\Model;

class KontingenModel extends Model
{
    protected $table            = 'kontingen';
    protected $primaryKey       = 'id_kontingen';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_pembayaran',
        'nama_kontingen',
        'singkatan_nama_kontingen',
        'jenis_kontingen',
        'perguruan',
        'email_kontingen',
        'nomor_telepon_kontingen',
        'alamat_kontingen',
        'username',
        'password',
        'nama_penanggungjawab',
        'jabatan_penanggungjawab',
        'nomor_telepon_penanggungjawab',
        'negara',
        'provinsi',
        'kabupaten_kota',
        'kecamatan',
        'kelurahan',
        'alamat_lengkap',
        'alamat_penanggungjawab',
        'keterangan',
        'tanggal_daftar',
        'pembayaran_dn',
        'pembayaran_ln',
        'status_data',
        'jenis_pendaftaran',
    ];

    protected bool $allowEmptyInserts = false;

    public function baseSekretariatQuery()
    {
        return $this->db->table($this->table . ' k')
            ->select([
                'k.*',
                "DATE_FORMAT(k.tanggal_daftar, '%a, %d %M %Y') AS tanggal_daftar_formatted",
                '(SELECT status_pembayaran FROM pembayaran WHERE pembayaran.id_pembayaran = k.id_pembayaran) AS status_pembayaran',
                '(SELECT COUNT(*) FROM pendaftar WHERE pendaftar.id_kontingen = k.id_kontingen) AS jumlah_pendaftar',
                '(SELECT COUNT(*) FROM peserta_tanding pt JOIN pendaftar p ON p.id_pendaftar = pt.id_pendaftar WHERE p.id_kontingen = k.id_kontingen) AS jumlah_peserta_tanding',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kontingen = k.id_kontingen) AS jumlah_kelompok_peserta_seni',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE kps.id_kontingen = k.id_kontingen AND LOWER(sks.jenis_seni) = "tunggal") AS jumlah_kelompok_peserta_seni_tunggal',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE kps.id_kontingen = k.id_kontingen AND LOWER(sks.jenis_seni) = "ganda") AS jumlah_kelompok_peserta_seni_ganda',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE kps.id_kontingen = k.id_kontingen AND LOWER(sks.jenis_seni) = "beregu") AS jumlah_kelompok_peserta_seni_beregu',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE kps.id_kontingen = k.id_kontingen AND LOWER(sks.jenis_seni) = "solo kreatif") AS jumlah_kelompok_peserta_seni_solo_kreatif',
                '(SELECT COUNT(*) FROM official o WHERE o.id_kontingen = k.id_kontingen) AS jumlah_official',
            ]);
    }

    public function findWithSummary(int $idKontingen): ?object
    {
        return $this->baseSekretariatQuery()
            ->where('k.id_kontingen', $idKontingen)
            ->get()
            ->getRow();
    }
}
