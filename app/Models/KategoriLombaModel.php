<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriLombaModel extends Model
{
    protected $table = 'kategori_lomba';
    protected $primaryKey = 'id_kategori_lomba';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kategori_usia', 'jenis_perlombaan', 'kuota_peserta'];
}
