<?php

namespace App\Models;

use CodeIgniter\Model;

class ArsipPendaftarModel extends Model
{
    protected $table            = 'arsip_pendaftar';
    protected $primaryKey       = 'id_arsip_pendaftar';
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false;

    protected bool $allowEmptyInserts = false;
}
