<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasTandingModel extends Model
{
    protected $table = 'kelas_tanding';
    protected $primaryKey = 'id_kelas_tanding';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kategori_lomba', 'label', 'berat_minimal', 'berat_maksimal', 'juara_tiga_bersama', 'jumlah_ronde', 'waktu_per_ronde', 'waktu_istirahat', 'format_penilaian', 'biaya_pendaftaran_dn', 'biaya_pendaftaran_ln', 'keterangan'];
}
