<?php

namespace App\Models;

use CodeIgniter\Model;

class PerangkatPertandinganModel extends Model
{
    protected $table = 'perangkat_pertandingan';
    protected $primaryKey = 'id_perangkat_pertandingan';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_gelanggang',
        'nama',
        'username',
        'password',
        'posisi',
        'session_id',
    ];
    protected $useTimestamps = false;

    public function getByGelanggangAndPosisi(int $idGelanggang, string $posisi): array
    {
        return $this->where('id_gelanggang', $idGelanggang)
            ->where('posisi', $posisi)
            ->orderBy('nama', 'ASC')
            ->findAll();
    }
}
