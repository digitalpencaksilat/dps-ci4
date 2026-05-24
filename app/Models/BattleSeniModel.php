<?php

namespace App\Models;

use CodeIgniter\Model;

class BattleSeniModel extends Model
{
    protected $table = 'battle_seni';
    protected $primaryKey = 'id_battle_seni';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kompetisi_seni', 'id_penampilan_seni_merah', 'id_penampilan_seni_biru', 'id_pemenang', 'babak', 'nomor_battle', 'nomor_battle_selanjutnya', 'jenis_kemenangan', 'skor_merah', 'skor_biru', 'keterangan'];
}
