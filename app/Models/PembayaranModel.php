<?php

namespace App\Models;

use CodeIgniter\Model;

class PembayaranModel extends Model
{
    protected $table            = 'pembayaran';
    protected $primaryKey       = 'id_pembayaran';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected bool $allowEmptyInserts = false;
}
