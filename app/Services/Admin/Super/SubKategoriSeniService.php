<?php

namespace App\Services\Admin\Super;

use App\Models\KompetisiSeniModel;
use App\Models\SubKategoriSeniModel;
use RuntimeException;

class SubKategoriSeniService
{
    private SubKategoriSeniModel $subKategoriSeniModel;
    private KompetisiSeniModel $kompetisiSeniModel;

    public function __construct()
    {
        $this->subKategoriSeniModel = new SubKategoriSeniModel();
        $this->kompetisiSeniModel = new KompetisiSeniModel();
    }

    /**
     * @param list<int> $kategoriLombaIds
     * @param array<string, string|int|float|null> $payload
     */
    public function createWithInitialPools(array $kategoriLombaIds, array $payload, int $maxPeserta): void
    {
        $db = db_connect();
        $db->transStart();

        try {
            foreach ($kategoriLombaIds as $kategoriLombaId) {
                $idSubKategoriSeni = $this->subKategoriSeniModel->insert($payload + [
                    'id_kategori_lomba' => $kategoriLombaId,
                ], true);

                if (! is_numeric($idSubKategoriSeni)) {
                    throw new RuntimeException('Sub kategori seni gagal ditambahkan.');
                }

                $poolId = $this->kompetisiSeniModel->insert([
                    'id_sub_kategori_seni' => (int) $idSubKategoriSeni,
                    'nomor_pool' => 1,
                    'max_peserta' => $maxPeserta,
                    'perhitungan_medali' => 1,
                    'keterangan' => '',
                ], true);

                if (! is_numeric($poolId)) {
                    throw new RuntimeException('Pool awal gagal ditambahkan.');
                }
            }
        } catch (\Throwable $error) {
            $db->transRollback();

            throw $error;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Transaksi sub kategori seni gagal.');
        }
    }
}
