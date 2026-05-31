<?php

namespace App\Services;

use App\Models\JadwalSeniModel;
use CodeIgniter\Database\BaseConnection;

class JadwalSeniPoolSwapService
{
    private BaseConnection $db;
    private PenilaianSeniService $penilaianSeniService;
    private JadwalSeniModel $jadwalSeniModel;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->penilaianSeniService = new PenilaianSeniService($this->db);
        $this->jadwalSeniModel = new JadwalSeniModel();
    }

    public function swapPenampilan(int $idPenampilan1, int $idPenampilan2): array
    {
        if ($idPenampilan1 <= 0 || $idPenampilan2 <= 0 || $idPenampilan1 === $idPenampilan2) {
            throw new \InvalidArgumentException('Pilih dua penampilan yang berbeda.');
        }

        $penampilan1 = $this->getPenampilanRow($idPenampilan1);
        $penampilan2 = $this->getPenampilanRow($idPenampilan2);

        if ($penampilan1 === null || $penampilan2 === null) {
            throw new \RuntimeException('Data penampilan seni tidak ditemukan.');
        }

        if (strtolower((string) ($penampilan1->jenis_seni ?? '')) !== strtolower((string) ($penampilan2->jenis_seni ?? ''))) {
            throw new \RuntimeException('Kelompok seni hanya bisa ditukar jika jenis seni sama.');
        }

        if ($this->isLocked($penampilan1) || $this->isLocked($penampilan2)) {
            throw new \RuntimeException('Penampilan tidak bisa ditukar karena sudah memiliki penilaian final atau status tampil.');
        }

        $detail1 = $this->getPoolDetailByPenampilan($idPenampilan1);
        $detail2 = $this->getPoolDetailByPenampilan($idPenampilan2);

        if ($detail1 === null || $detail2 === null) {
            throw new \RuntimeException('Penampilan belum terhubung ke detail jadwal pool.');
        }

        $jadwalIds = array_values(array_unique([(int) $detail1->id_jadwal_seni, (int) $detail2->id_jadwal_seni]));
        $gelanggangMap = $this->getGelanggangMap($jadwalIds);

        $this->db->transStart();

        // Mirror CI3 behavior: swap competition slot ownership at the group level.
        $this->db->table('kelompok_peserta_seni')
            ->where('id_kelompok_peserta_seni', (int) $penampilan1->id_kelompok_peserta_seni)
            ->update(['id_kompetisi_seni' => (int) $penampilan2->id_kompetisi_seni]);

        $this->db->table('kelompok_peserta_seni')
            ->where('id_kelompok_peserta_seni', (int) $penampilan2->id_kelompok_peserta_seni)
            ->update(['id_kompetisi_seni' => (int) $penampilan1->id_kompetisi_seni]);

        $this->db->table('detail_jadwal_seni')
            ->where('id_detail_jadwal_seni', (int) $detail1->id_detail_jadwal_seni)
            ->update(['id_penampilan_seni' => null]);

        $this->db->table('detail_jadwal_seni')
            ->where('id_detail_jadwal_seni', (int) $detail2->id_detail_jadwal_seni)
            ->update(['id_penampilan_seni' => (int) $idPenampilan1]);

        $this->db->table('detail_jadwal_seni')
            ->where('id_detail_jadwal_seni', (int) $detail1->id_detail_jadwal_seni)
            ->update(['id_penampilan_seni' => (int) $idPenampilan2]);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menukar penampilan seni pool.');
        }

        foreach ([
            [$idPenampilan1, $gelanggangMap[(int) $detail2->id_jadwal_seni] ?? 0],
            [$idPenampilan2, $gelanggangMap[(int) $detail1->id_jadwal_seni] ?? 0],
        ] as [$idPenampilan, $idGelanggang]) {
            if ((int) $idGelanggang > 0) {
                $this->penilaianSeniService->tugaskanWasitJuri((int) $idPenampilan, (int) $idGelanggang);
            }
        }

        foreach ($jadwalIds as $idJadwal) {
            $this->syncJadwalSeniRange($idJadwal);
        }

        log_message('info', 'Swap penampilan seni pool berhasil: {id1} <-> {id2}', [
            'id1' => $idPenampilan1,
            'id2' => $idPenampilan2,
        ]);

        return $jadwalIds;
    }

    private function getPenampilanRow(int $idPenampilan): ?object
    {
        return $this->db->table('penampilan_seni ps')
            ->select('ps.id_penampilan_seni, ps.id_kelompok_peserta_seni, ps.status_penampilan, ps.nilai_akhir, ps.waktu_tampil, ps.diskualifikasi, kps.id_kompetisi_seni, sks.jenis_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->where('ps.id_penampilan_seni', $idPenampilan)
            ->get()
            ->getRow();
    }

    private function getPoolDetailByPenampilan(int $idPenampilan): ?object
    {
        return $this->db->table('detail_jadwal_seni')
            ->select('id_detail_jadwal_seni, id_jadwal_seni, id_penampilan_seni, nomor_partai')
            ->where('id_penampilan_seni', $idPenampilan)
            ->where('id_battle_seni IS NULL', null, false)
            ->get()
            ->getRow();
    }

    private function getGelanggangMap(array $jadwalIds): array
    {
        if ($jadwalIds === []) {
            return [];
        }

        $rows = $this->db->table('jadwal_seni')
            ->select('id_jadwal_seni, id_gelanggang')
            ->whereIn('id_jadwal_seni', $jadwalIds)
            ->get()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->id_jadwal_seni] = (int) ($row->id_gelanggang ?? 0);
        }

        return $map;
    }

    private function isLocked(object $penampilan): bool
    {
        if (! empty($penampilan->status_penampilan) && strtolower((string) $penampilan->status_penampilan) !== 'belum_tampil') {
            return true;
        }

        if ($penampilan->nilai_akhir !== null || $penampilan->waktu_tampil !== null || (int) ($penampilan->diskualifikasi ?? 0) === 1) {
            return true;
        }

        $row = $this->db->table('penilaian_seni')
            ->select('COUNT(*) AS total')
            ->where('id_penampilan_seni', (int) $penampilan->id_penampilan_seni)
            ->where('status_ready', 1)
            ->get()
            ->getRow();

        return (int) ($row->total ?? 0) > 0;
    }

    private function syncJadwalSeniRange(int $idJadwal): void
    {
        $range = $this->db->table('detail_jadwal_seni')
            ->select('MIN(nomor_partai) AS awal, MAX(nomor_partai) AS akhir, COUNT(*) AS total')
            ->where('id_jadwal_seni', $idJadwal)
            ->get()
            ->getRow();

        $this->jadwalSeniModel->update($idJadwal, [
            'nomor_partai_awal' => $range->awal ?? null,
            'nomor_partai_akhir' => $range->akhir ?? null,
            'jumlah_penampilan' => (int) ($range->total ?? 0),
        ]);
    }
}
