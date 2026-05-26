<?php

namespace App\Models;

use CodeIgniter\Model;

class KompetisiTandingModel extends Model
{
    protected $table = 'kompetisi_tanding';
    protected $primaryKey = 'id_kompetisi_tanding';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kelas_tanding', 'max_peserta', 'perhitungan_medali', 'nomor_pool', 'keterangan', 'bagan_pertandingan'];
}
