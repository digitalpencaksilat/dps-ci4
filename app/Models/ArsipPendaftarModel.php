<?php

namespace App\Models;

use CodeIgniter\Model;

class ArsipPendaftarModel extends Model
{
    protected $table            = 'arsip_pendaftar';
    protected $primaryKey       = 'id_arsip_pendaftar';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_pendaftar',
        'nama_arsip',
        'jenis_arsip',
        'slug',
        'is_required',
        'file_path',
        'status_verifikasi',
        'keterangan',
        'urutan',
    ];

    protected bool $allowEmptyInserts = false;
}
