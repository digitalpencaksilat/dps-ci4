<?php

namespace App\Models;

use CodeIgniter\Model;

class KompetisiSeniModel extends Model
{
    protected $table = 'kompetisi_seni';
    protected $primaryKey = 'id_kompetisi_seni';
    protected $returnType = 'object';
    protected $allowedFields = ['id_sub_kategori_seni', 'nomor_pool', 'max_peserta', 'perhitungan_medali', 'keterangan', 'bagan_battle_seni'];
}
