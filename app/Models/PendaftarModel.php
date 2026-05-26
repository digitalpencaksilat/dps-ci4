<?php

namespace App\Models;

use CodeIgniter\Model;

class PendaftarModel extends Model
{
    protected $table            = 'pendaftar';
    protected $primaryKey       = 'id_pendaftar';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_kontingen',
        'nama_pendaftar',
        'jenis_kelamin',
        'tinggi_badan',
        'berat_badan',
        'tempat_lahir',
        'tanggal_lahir',
        'nama_sekolah',
        'alamat',
        'foto',
        'status_data',
        'keterangan',
        'tanggal_daftar',
        'nomor_induk_kependudukan',
        'nomor_kartu_keluarga',
    ];

    protected bool $allowEmptyInserts = false;

    public function baseSekretariatQuery()
    {
        return $this->db->table($this->table . ' p')
            ->select([
                'p.*',
                'k.nama_kontingen',
                'k.jenis_kontingen',
                'k.provinsi',
                'k.jenis_pendaftaran',
                'TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) AS umur_pendaftar',
            ])
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen', 'left');
    }
}
