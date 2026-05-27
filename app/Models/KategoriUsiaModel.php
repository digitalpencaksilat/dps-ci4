<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriUsiaModel extends Model
{
    protected $table = 'kategori_usia';
    protected $primaryKey = 'id_kategori_usia';
    protected $returnType = 'object';
    protected $allowedFields = ['nama_kategori_usia', 'min_umur', 'max_umur', 'jenis_kelamin', 'acuan_tanggal'];
}
