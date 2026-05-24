<?php

namespace App\Models;

use CodeIgniter\Model;

class PerolehanMedaliSeniModel extends Model
{
    protected $table            = 'perolehan_medali_seni';
    protected $primaryKey       = 'id_perolehan_medali_seni';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_kelompok_peserta_seni',
        'jenis_medali',
    ];

    protected bool $allowEmptyInserts = false;
}
