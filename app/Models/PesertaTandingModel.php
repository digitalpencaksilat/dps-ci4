<?php

namespace App\Models;

use CodeIgniter\Model;

class PesertaTandingModel extends Model
{
    protected $table            = 'peserta_tanding';
    protected $primaryKey       = 'id_peserta_tanding';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected bool $allowEmptyInserts = false;
}
