<?php

namespace App\Models;

use CodeIgniter\Model;

class PenampilanSeniModel extends Model
{
    protected $table = 'penampilan_seni';
    protected $primaryKey = 'id_penampilan_seni';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kelompok_peserta_seni', 'nilai_akhir', 'waktu_tampil', 'status_penampilan', 'catatan_nilai_sama'];
}
