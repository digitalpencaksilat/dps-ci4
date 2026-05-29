<?php

namespace App\Services;

use App\Models\PenilaianTandingModel;
use App\Models\PerangkatPertandinganModel;
use App\Models\PertandinganModel;
use CodeIgniter\Database\BaseConnection;

class PenilaianTandingService
{
    private BaseConnection $db;
    private PenilaianTandingModel $penilaianModel;
    private PerangkatPertandinganModel $perangkatModel;
    private PertandinganModel $pertandinganModel;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->penilaianModel = new PenilaianTandingModel();
        $this->perangkatModel = new PerangkatPertandinganModel();
        $this->pertandinganModel = new PertandinganModel();
    }

    public function createPenilaianForPertandingan(int $idPertandingan): bool
    {
        $meta = $this->fetchPertandinganMeta($idPertandingan);
        if ($meta === null) {
            log_message('error', 'Pertandingan tidak ditemukan, ID: {id}', ['id' => $idPertandingan]);
            return false;
        }

        $jumlahJuri = (int) ($meta['jumlah_juri'] ?? 0);
        if ($jumlahJuri <= 0) {
            log_message('error', 'Pertandingan memiliki jumlah juri = 0, ID: {id}', ['id' => $idPertandingan]);
            return false;
        }

        // DB aktif tidak punya kolom pertandingan.format_penilaian. CI3 fallback pakai PERSILAT.
        $format = (string) ($meta['format_penilaian'] ?? $meta['peraturan_pertandingan'] ?? 'PERSILAT');
        $jsonPenilaian = $this->getJsonPenilaian($format);
        if ($jsonPenilaian === null) {
            log_message('error', 'Format penilaian tidak dikenali, ID: {id}', ['id' => $idPertandingan]);
            return false;
        }

        for ($i = 0; $i < $jumlahJuri; $i++) {
            $ok = $this->penilaianModel->insert([
                'id_pertandingan' => $idPertandingan,
                'id_perangkat_pertandingan' => null,
                'penilaian_merah' => $jsonPenilaian,
                'penilaian_biru' => $jsonPenilaian,
                'pemenang' => '',
            ]);

            if (! $ok) {
                log_message('error', 'Data penilaian tanding tidak dapat diinput, ID: {id}', ['id' => $idPertandingan]);
                return false;
            }
        }

        return true;
    }

    public function tugaskanWasitJuri(int $idPertandingan, int $idGelanggang): bool
    {
        $meta = $this->fetchPertandinganMeta($idPertandingan);
        if ($meta === null) {
            log_message('error', 'Pertandingan tidak ditemukan, ID: {id}', ['id' => $idPertandingan]);
            return false;
        }

        $jumlahJuri = (int) ($meta['jumlah_juri'] ?? 0);
        if ($jumlahJuri <= 0) {
            log_message('error', 'Pertandingan memiliki jumlah juri = 0, ID: {id}', ['id' => $idPertandingan]);
            return false;
        }

        $dataPenilaian = $this->penilaianModel
            ->where('id_pertandingan', $idPertandingan)
            ->orderBy('id_penilaian_tanding', 'ASC')
            ->findAll();

        // Parity CI3: bila belum ada data penilaian, buat dulu.
        if (count($dataPenilaian) === 0) {
            if (! $this->createPenilaianForPertandingan($idPertandingan)) {
                return false;
            }
            $dataPenilaian = $this->penilaianModel
                ->where('id_pertandingan', $idPertandingan)
                ->orderBy('id_penilaian_tanding', 'ASC')
                ->findAll();
        }

        if ($jumlahJuri > count($dataPenilaian)) {
            $this->penilaianModel->where('id_pertandingan', $idPertandingan)->delete();
            if (! $this->createPenilaianForPertandingan($idPertandingan)) {
                return false;
            }
            $dataPenilaian = $this->penilaianModel
                ->where('id_pertandingan', $idPertandingan)
                ->orderBy('id_penilaian_tanding', 'ASC')
                ->findAll();
        }

        $dataJuri = $this->perangkatModel->getByGelanggangAndPosisi($idGelanggang, 'juri');
        if (count($dataJuri) < $jumlahJuri) {
            log_message('error', 'Jumlah perangkat juri kurang. gelanggang={gelanggang}, butuh={butuh}, ada={ada}', [
                'gelanggang' => $idGelanggang,
                'butuh' => $jumlahJuri,
                'ada' => count($dataJuri),
            ]);
            return false;
        }

        foreach ($dataPenilaian as $k => $penilaian) {
            $juri = $dataJuri[$k] ?? null;
            if ($juri === null) {
                return false;
            }

            $updated = $this->penilaianModel->update($penilaian->id_penilaian_tanding, [
                'id_perangkat_pertandingan' => $juri->id_perangkat_pertandingan,
            ]);

            if (! $updated) {
                log_message('error', 'Gagal update perangkat penilaian tanding. penilaian={penilaian}', [
                    'penilaian' => $penilaian->id_penilaian_tanding,
                ]);
                return false;
            }
        }

        return true;
    }

    private function getJsonPenilaian(string $formatPenilaian = 'PERSILAT'): ?string
    {
        $formatPenilaian = strtolower(str_replace(' ', '_', $formatPenilaian));
        // CI3 path: assets/penilaian/format-penilaian/tanding/
        // CI4 project stores public assets under /public.
        $baseDir = ROOTPATH . 'public/assets/penilaian/format-penilaian/tanding/';
        if (! is_dir($baseDir)) {
            $legacyDir = ROOTPATH . 'assets/penilaian/format-penilaian/tanding/';
            if (is_dir($legacyDir)) {
                $baseDir = $legacyDir;
            }
        }

        $filePath = strpos($formatPenilaian, 'json') !== false
            ? $baseDir . $formatPenilaian
            : $baseDir . $formatPenilaian . '.json';

        if (! is_file($filePath)) {
            log_message('error', 'format penilaian tidak valid {path}', ['path' => $filePath]);
            return null;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $decoded = json_decode($content);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return json_encode($decoded, JSON_UNESCAPED_LINE_TERMINATORS);
    }

    private function fetchPertandinganMeta(int $idPertandingan): ?array
    {
        $builder = $this->db->table('pertandingan')
            ->select('kategori_lomba.id_kategori_lomba, kategori_lomba.jumlah_juri, kategori_lomba.peraturan_pertandingan')
            ->join('kompetisi_tanding', 'pertandingan.id_kompetisi_tanding = kompetisi_tanding.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kompetisi_tanding.id_kelas_tanding = kelas_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kelas_tanding.id_kategori_lomba = kategori_lomba.id_kategori_lomba')
            ->where('pertandingan.id_pertandingan', $idPertandingan);

        $query = $builder->get();
        if ($query === false) {
            log_message('error', 'Query fetchPertandinganMeta gagal: {error}', ['error' => (string) $this->db->error()['message']]);
            return null;
        }

        $row = $query->getRowArray();
        return $row ?: null;
    }
}
