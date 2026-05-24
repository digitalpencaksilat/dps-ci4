<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table            = 'admin';
    protected $primaryKey       = 'id_admin';
    protected $returnType       = 'object';
    protected $useAutoIncrement = true;
    protected $allowedFields    = [
        'nama',
        'username',
        'password',
        'foto',
        'level',
        'keterangan',
    ];
}
