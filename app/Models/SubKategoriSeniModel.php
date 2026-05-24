<?php

namespace App\Models;

use CodeIgniter\Model;

class SubKategoriSeniModel extends Model
{
    protected $table = 'sub_kategori_seni';
    protected $primaryKey = 'id_sub_kategori_seni';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kategori_lomba', 'nama_seni', 'jenis_seni', 'jumlah_peserta', 'waktu', 'biaya_pendaftaran_dn', 'biaya_pendaftaran_ln', 'format_penilaian', 'sistem_penampilan', 'keterangan'];
}
