<?php

namespace App\Models;

use CodeIgniter\Model;

class KelompokPesertaSeniModel extends Model
{
    protected $table            = 'kelompok_peserta_seni';
    protected $primaryKey       = 'id_kelompok_peserta_seni';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected bool $allowEmptyInserts = false;
}
