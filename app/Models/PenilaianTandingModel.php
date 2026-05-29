<?php

namespace App\Models;

use CodeIgniter\Model;

class PenilaianTandingModel extends Model
{
    protected $table = 'penilaian_tanding';
    protected $primaryKey = 'id_penilaian_tanding';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_pertandingan',
        'id_perangkat_pertandingan',
        'penilaian_merah',
        'penilaian_biru',
        'pemenang',
    ];
    protected $useTimestamps = false;
}
