<?php

namespace App\Models;

use CodeIgniter\Model;

class KontingenModel extends Model
{
    protected $table            = 'kontingen';
    protected $primaryKey       = 'id_kontingen';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected bool $allowEmptyInserts = false;
}
