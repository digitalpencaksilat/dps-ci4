<?php

namespace App\Models;

use CodeIgniter\Model;

class PertandinganModel extends Model
{
    protected $table = 'pertandingan';
    protected $primaryKey = 'id_pertandingan';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_kompetisi_tanding',
        'id_atlet_merah',
        'id_atlet_biru',
        'id_pemenang',
        'babak',
        'nomor_pertandingan',
        'nomor_pertandingan_selanjutnya',
        'jenis_kemenangan',
        'skor_merah',
        'skor_biru',
        'keterangan',
        // parity CI3 penilaian_tanding
        'jumlah_juri',
        'format_penilaian',
        'peraturan_pertandingan',
    ];
}
