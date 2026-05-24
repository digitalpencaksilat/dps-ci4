<?php

namespace App\Models;

use CodeIgniter\Model;

class PerolehanMedaliTandingModel extends Model
{
    protected $table            = 'perolehan_medali_tanding';
    protected $primaryKey       = 'id_perolehan_medali_tanding';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'id_peserta_tanding',
        'jenis_medali',
    ];

    protected bool $allowEmptyInserts = false;
}
