<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class JadwalTandingSwapService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function swapPeserta(int $idPeserta1, int $idPeserta2): void
    {
        if ($idPeserta1 <= 0 || $idPeserta2 <= 0 || $idPeserta1 === $idPeserta2) {
            throw new \InvalidArgumentException('Pilih dua atlet yang berbeda.');
        }

        if ($this->hasLockedMatches($idPeserta1) || $this->hasLockedMatches($idPeserta2)) {
            throw new \RuntimeException('Atlet tidak bisa ditukar karena sudah terlibat pertandingan yang memiliki skor atau pemenang.');
        }

        $this->db->transStart();

        foreach (['id_atlet_merah', 'id_atlet_biru'] as $field) {
            // Use a temporary negative sentinel so both IDs can be swapped without collisions.
            $this->db->table('pertandingan')->where($field, $idPeserta1)->update([$field => -$idPeserta1]);
            $this->db->table('pertandingan')->where($field, $idPeserta2)->update([$field => $idPeserta1]);
            $this->db->table('pertandingan')->where($field, -$idPeserta1)->update([$field => $idPeserta2]);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menukar atlet.');
        }

        log_message('info', 'Swap atlet tanding berhasil: {id1} <-> {id2}', [
            'id1' => $idPeserta1,
            'id2' => $idPeserta2,
        ]);
    }

    public function hasLockedMatches(int $idPeserta): bool
    {
        $row = $this->db->table('pertandingan p')
            ->select('COUNT(*) AS total')
            ->groupStart()
                ->where('p.id_atlet_merah', $idPeserta)
                ->orWhere('p.id_atlet_biru', $idPeserta)
            ->groupEnd()
            ->groupStart()
                ->where('p.id_pemenang IS NOT NULL', null, false)
                ->orWhere('p.skor_merah >', 0)
                ->orWhere('p.skor_biru >', 0)
            ->groupEnd()
            ->get()
            ->getRow();

        return (int) ($row->total ?? 0) > 0;
    }
}
