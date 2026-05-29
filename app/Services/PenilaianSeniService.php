<?php

namespace App\Services;

use App\Models\PenampilanSeniModel;
use CodeIgniter\Database\BaseConnection;

class PenilaianSeniService
{
    private BaseConnection $db;
    private PenampilanSeniModel $penampilanModel;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->penampilanModel = new PenampilanSeniModel();
    }

    public function tugaskanWasitJuri(int $idPenampilanSeni, int $idGelanggang): bool
    {
        $penampilan = $this->fetchPenampilanMeta($idPenampilanSeni);
        if ($penampilan === null) {
            log_message('error', 'Penampilan seni tidak ditemukan, ID: {id}', ['id' => $idPenampilanSeni]);
            return false;
        }

        $jumlahJuri = max(0, (int) ($penampilan->jumlah_juri ?? 0));
        if ($jumlahJuri === 0) {
            log_message('error', 'Jumlah juri seni tidak valid untuk penampilan: {id}', ['id' => $idPenampilanSeni]);
            return false;
        }

        $existing = $this->db->table('penilaian_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->countAllResults();

        if ($existing < $jumlahJuri) {
            $this->db->table('penilaian_seni')->where('id_penampilan_seni', $idPenampilanSeni)->delete();
            if (! $this->createPenilaianSeni($idPenampilanSeni, $jumlahJuri)) {
                return false;
            }
        }

        $juri = $this->db->table('perangkat_pertandingan')
            ->select('id_perangkat_pertandingan')
            ->where('id_gelanggang', $idGelanggang)
            ->where('posisi', 'juri')
            ->orderBy('id_perangkat_pertandingan', 'ASC')
            ->get()
            ->getResult();

        if (count($juri) < $jumlahJuri) {
            log_message('error', 'Juri gelanggang tidak mencukupi untuk penampilan seni {id}. Butuh {butuh}, tersedia {tersedia}', [
                'id' => $idPenampilanSeni,
                'butuh' => $jumlahJuri,
                'tersedia' => count($juri),
            ]);
            return false;
        }

        $penilaianRows = $this->db->table('penilaian_seni')
            ->select('id_penilaian_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->orderBy('id_penilaian_seni', 'ASC')
            ->get()
            ->getResult();

        foreach ($penilaianRows as $index => $row) {
            $assigned = $juri[$index] ?? null;
            if ($assigned === null) {
                return false;
            }

            $updated = $this->db->table('penilaian_seni')
                ->where('id_penilaian_seni', (int) $row->id_penilaian_seni)
                ->update([
                    'id_perangkat_pertandingan' => (int) $assigned->id_perangkat_pertandingan,
                    'status_ready' => 0,
                ]);

            if (! $updated) {
                return false;
            }
        }

        return true;
    }

    private function createPenilaianSeni(int $idPenampilanSeni, int $jumlahJuri): bool
    {
        for ($i = 0; $i < $jumlahJuri; $i++) {
            $ok = $this->db->table('penilaian_seni')->insert([
                'id_penampilan_seni' => $idPenampilanSeni,
                'id_perangkat_pertandingan' => null,
                'status_ready' => 0,
            ]);

            if (! $ok) {
                log_message('error', 'Gagal membuat penilaian_seni untuk penampilan: {id}', ['id' => $idPenampilanSeni]);
                return false;
            }
        }

        return true;
    }

    private function fetchPenampilanMeta(int $idPenampilanSeni): ?object
    {
        return $this->db->table('penampilan_seni ps')
            ->select('ps.id_penampilan_seni, ps.id_kelompok_peserta_seni, kl.jumlah_juri')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->where('ps.id_penampilan_seni', $idPenampilanSeni)
            ->get()
            ->getRow();
    }
}
