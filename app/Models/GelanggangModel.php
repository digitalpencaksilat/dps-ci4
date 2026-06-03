<?php

namespace App\Models;

use CodeIgniter\Model;

class GelanggangModel extends Model
{
    protected $table      = 'gelanggang';
    protected $primaryKey = 'id_gelanggang';
    protected $returnType = 'object';
    protected $allowedFields = [
        'nama_gelanggang',
        'nomor_gelanggang',
        'keterangan',
        'tipe_gong',
        'beep_alarm',
    ];
    protected $useTimestamps = false;
}
