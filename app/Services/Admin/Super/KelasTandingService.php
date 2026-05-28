<?php

namespace App\Services\Admin\Super;

use App\Models\KelasTandingModel;
use App\Models\KompetisiTandingModel;
use App\Services\SekretariatKategoriTandingService;
use App\Services\SistemGugurTunggalService;
use RuntimeException;

class KelasTandingService
{
    private KelasTandingModel $kelasTandingModel;
    private KompetisiTandingModel $kompetisiTandingModel;
    private SekretariatKategoriTandingService $readService;

    public function __construct()
    {
        $this->kelasTandingModel = new KelasTandingModel();
        $this->kompetisiTandingModel = new KompetisiTandingModel();
        $this->readService = new SekretariatKategoriTandingService();
    }

    public function listKelas(): array
    {
        return $this->readService->listKelas();
    }

    public function getKelas(int $id): ?object
    {
        return $this->readService->getKelas($id);
    }

    public function listPoolByKelas(int $id): array
    {
        return $this->readService->listPoolByKelas($id);
    }

    public function listPesertaByKelas(int $id): array
    {
        return $this->readService->listPesertaByKelas($id);
    }

    public function createSingle(array $kategoriLombaIds, array $payload, int $maxPeserta): void
    {
        $this->createRows($kategoriLombaIds, [$payload + ['keterangan' => ' 1 (diinput otomatis oleh sistem)']], $maxPeserta);
    }

    public function createMultiple(array $kategoriLombaIds, array $payload, array $generator, int $maxPeserta): void
    {
        $rows = [];
        $labelAscii = ord((string) $generator['label_awal']);
        $beratMinimal = (float) $generator['berat_awal'];
        $selisihBerat = (float) $generator['selisih_berat'];
        $jumlahKelas = (int) $generator['jumlah_kelas'];

        if ($jumlahKelas < 1) {
            throw new RuntimeException('Jumlah kelas tidak valid.');
        }

        for ($i = 1; $i <= $jumlahKelas; $i++) {
            $rows[] = $payload + [
                'label' => chr($labelAscii),
                'berat_minimal' => $beratMinimal,
                'berat_maksimal' => $beratMinimal + $selisihBerat,
            ];
            $labelAscii++;
            $beratMinimal += $selisihBerat;
        }

        if ((int) ($generator['kelas_bebas'] ?? 0) === 1) {
            $rows[] = $payload + [
                'label' => 'Bebas',
                'berat_minimal' => $beratMinimal,
                'berat_maksimal' => $beratMinimal + 100,
            ];
        }

        if ((int) ($generator['kelas_mini'] ?? 0) === 1) {
            $rows[] = $payload + [
                'label' => 'Mini',
                'berat_minimal' => 0,
                'berat_maksimal' => (float) $generator['berat_awal'],
            ];
        }

        $this->createRows($kategoriLombaIds, $rows, $maxPeserta);
    }

    public function updateKelas(int $id, array $payload): bool
    {
        $this->assertValidWeightRange($payload);

        return $this->kelasTandingModel->update($id, $payload);
    }

    public function deleteKelas(int $id): bool
    {
        $db = db_connect();
        $poolIds = array_map(static fn ($row): int => (int) $row->id_kompetisi_tanding, $this->kompetisiTandingModel->where('id_kelas_tanding', $id)->findAll());

        if ($poolIds !== []) {
            $hasPeserta = $db->table('peserta_tanding')->whereIn('id_kompetisi_tanding', $poolIds)->countAllResults() > 0;
            $hasPertandingan = $db->table('pertandingan')->whereIn('id_kompetisi_tanding', $poolIds)->countAllResults() > 0;
            if ($hasPeserta || $hasPertandingan) {
                throw new RuntimeException('Kelas tanding masih digunakan peserta atau pertandingan.');
            }
        }

        $db->transStart();
        $db->table('kompetisi_tanding')->where('id_kelas_tanding', $id)->delete();
        $this->kelasTandingModel->delete($id);
        $db->transComplete();

        return $db->transStatus();
    }

    public function autoTambahPool(int $idKelasTanding, ?int $maxPeserta = null): void
    {
        if ($this->kelasTandingModel->find($idKelasTanding) === null) {
            throw new RuntimeException('Kelas tanding tidak ditemukan.');
        }

        $last = $this->kompetisiTandingModel->where('id_kelas_tanding', $idKelasTanding)->orderBy('nomor_pool', 'DESC')->first();
        $finalMaxPeserta = $maxPeserta ?? (int) ($last->max_peserta ?? 16);
        $baganPertandingan = $last->bagan_pertandingan ?? $this->defaultBaganTemplate($finalMaxPeserta);

        $this->insertPool($idKelasTanding, $last === null ? 1 : ((int) $last->nomor_pool) + 1, $finalMaxPeserta, $baganPertandingan, 'Terinput otomatis oleh sistem');
    }

    public function updateJumlahPesertaPerPool(array $kategoriLombaIds, int $maxPeserta, bool $redistribute = false): void
    {
        if ($kategoriLombaIds === []) {
            throw new RuntimeException('Silahkan pilih kategori tanding yang akan diubah.');
        }

        $db = db_connect();
        $db->transStart();

        foreach ($kategoriLombaIds as $kategoriLombaId) {
            $kelasRows = $this->kelasTandingModel->where('id_kategori_lomba', $kategoriLombaId)->findAll();
            foreach ($kelasRows as $kelas) {
                $idKelasTanding = (int) $kelas->id_kelas_tanding;
                if (! $redistribute) {
                    $this->assertPoolCapacityCanBeReduced($idKelasTanding, $maxPeserta);
                }

                $this->ensureCapacityForKelas($idKelasTanding, $maxPeserta);
                $this->kompetisiTandingModel->where('id_kelas_tanding', $idKelasTanding)->set(['max_peserta' => $maxPeserta])->update();
                if ($redistribute) {
                    $this->redistributePeserta($idKelasTanding);
                }
            }
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            throw new RuntimeException('Gagal mengubah max peserta per pool.');
        }
    }

    private function createRows(array $kategoriLombaIds, array $rows, int $maxPeserta): void
    {
        $db = db_connect();
        $db->transStart();

        try {
            foreach ($rows as $row) {
                $this->assertValidWeightRange($row);
                foreach ($kategoriLombaIds as $kategoriLombaId) {
                    $id = $this->kelasTandingModel->insert($row + ['id_kategori_lomba' => $kategoriLombaId], true);
                    if (! is_numeric($id)) {
                        throw new RuntimeException('Kelas tanding gagal ditambahkan.');
                    }
                    $this->insertPool((int) $id, 1, $maxPeserta, $this->defaultBaganTemplate($maxPeserta), 'Terinput otomatis oleh sistem');
                }
            }
        } catch (\Throwable $error) {
            $db->transRollback();
            throw $error;
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            throw new RuntimeException('Transaksi kelas tanding gagal.');
        }
    }

    private function insertPool(int $idKelasTanding, int $nomorPool, int $maxPeserta, ?string $baganPertandingan, string $keterangan): void
    {
        $id = $this->kompetisiTandingModel->insert([
            'id_kelas_tanding' => $idKelasTanding,
            'nomor_pool' => $nomorPool,
            'max_peserta' => $maxPeserta > 0 ? $maxPeserta : 16,
            'bagan_pertandingan' => $baganPertandingan,
            'perhitungan_medali' => 1,
            'keterangan' => $keterangan,
        ], true);

        if (! is_numeric($id)) {
            throw new RuntimeException('Pool tanding gagal ditambahkan.');
        }
    }

    private function redistributePeserta(int $idKelasTanding): void
    {
        $db = db_connect();
        $poolRows = $this->kompetisiTandingModel->where('id_kelas_tanding', $idKelasTanding)->orderBy('nomor_pool', 'ASC')->findAll();
        $pesertaRows = $db->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen', 'left')
            ->where('kom.id_kelas_tanding', $idKelasTanding)
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('p.berat_badan', 'ASC')
            ->get()
            ->getResult();

        $index = 0;
        foreach ($pesertaRows as $peserta) {
            if ($poolRows === []) {
                break;
            }
            $pool = $poolRows[$index % count($poolRows)];
            $db->table('peserta_tanding')->where('id_peserta_tanding', (int) $peserta->id_peserta_tanding)->update(['id_kompetisi_tanding' => (int) $pool->id_kompetisi_tanding]);
            $index++;
        }
    }

    private function assertValidWeightRange(array $payload): void
    {
        $beratMinimal = isset($payload['berat_minimal']) ? (float) $payload['berat_minimal'] : null;
        $beratMaksimal = isset($payload['berat_maksimal']) ? (float) $payload['berat_maksimal'] : null;

        if ($beratMinimal !== null && $beratMaksimal !== null && $beratMaksimal < $beratMinimal) {
            throw new RuntimeException('Berat maksimal harus lebih besar atau sama dengan berat minimal.');
        }
    }

    private function assertPoolCapacityCanBeReduced(int $idKelasTanding, int $maxPeserta): void
    {
        $db = db_connect();
        $poolRows = $this->kompetisiTandingModel->where('id_kelas_tanding', $idKelasTanding)->findAll();
        foreach ($poolRows as $pool) {
            $jumlahPeserta = (int) $db->table('peserta_tanding')->where('id_kompetisi_tanding', (int) $pool->id_kompetisi_tanding)->countAllResults();
            if ($jumlahPeserta > $maxPeserta) {
                throw new RuntimeException('Tidak bisa mengurangi max peserta karena ada pool yang jumlah pesertanya melebihi batas baru. Gunakan opsi distribusi ulang.');
            }
        }
    }

    private function ensureCapacityForKelas(int $idKelasTanding, int $maxPeserta): void
    {
        $db = db_connect();
        $jumlahPeserta = (int) $db->table('peserta_tanding pt')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->where('kom.id_kelas_tanding', $idKelasTanding)
            ->countAllResults();

        $jumlahPool = (int) $this->kompetisiTandingModel->where('id_kelas_tanding', $idKelasTanding)->countAllResults();
        $kapasitasSaatIni = $jumlahPool * $maxPeserta;

        while ($kapasitasSaatIni < $jumlahPeserta) {
            $last = $this->kompetisiTandingModel->where('id_kelas_tanding', $idKelasTanding)->orderBy('nomor_pool', 'DESC')->first();
            $this->insertPool(
                $idKelasTanding,
                $last === null ? 1 : ((int) $last->nomor_pool) + 1,
                $maxPeserta,
                $last->bagan_pertandingan ?? $this->defaultBaganTemplate($maxPeserta),
                'Terinput otomatis oleh sistem'
            );
            $jumlahPool++;
            $kapasitasSaatIni = $jumlahPool * $maxPeserta;
        }
    }

    private function defaultBaganTemplate(int $maxPeserta): string
    {
        $kapasitas = $maxPeserta > 0 ? $maxPeserta : 8;
        $jumlahPertandinganAwal = max(1, (int) ceil($kapasitas / 2));

        return (string) (new SistemGugurTunggalService())->getTemplateBagan($jumlahPertandinganAwal, $kapasitas, true);
    }
}

