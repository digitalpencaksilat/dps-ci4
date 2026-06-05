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

    /**
     * Update max_peserta di semua pool (kompetisi_seni) milik sub_kategori_seni
     * yang berada di bawah kategori_lomba yang dipilih.
     *
     * @param list<int> $kategoriLombaIds
     * @return int Jumlah pool yang di-update
     */
    public function updateMaxPesertaPerPool(array $kategoriLombaIds, int $maxPeserta, bool $distribusiUlang = false): int
    {
        $db = db_connect();
        $updatedCount = 0;

        $db->transStart();

        try {
            foreach ($kategoriLombaIds as $idKategoriLomba) {
                $subKategoriRows = $this->subKategoriSeniModel
                    ->where('id_kategori_lomba', (int) $idKategoriLomba)
                    ->findAll();

                foreach ($subKategoriRows as $subKategori) {
                    $db->table('kompetisi_seni')
                        ->where('id_sub_kategori_seni', (int) $subKategori->id_sub_kategori_seni)
                        ->update(['max_peserta' => $maxPeserta]);

                    $updatedCount += $db->affectedRows();

                    if ($distribusiUlang) {
                        $this->distribusikanKelompokPesertaSeni((int) $subKategori->id_sub_kategori_seni);
                    }
                }
            }
        } catch (\Throwable $error) {
            $db->transRollback();
            throw $error;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Gagal mengubah jumlah peserta per pool.');
        }

        return $updatedCount;
    }

    /**
     * Distribusi ulang kelompok peserta seni ke pool.
     * Placeholder — implementasi detail bisa ditambah nanti sesuai kebutuhan.
     */
    private function distribusikanKelompokPesertaSeni(int $idSubKategoriSeni): void
    {
        log_message('info', '[SubKategoriSeni] Distribusi ulang diminta untuk id_sub_kategori_seni={id}', [
            'id' => $idSubKategoriSeni,
        ]);
    }
}
