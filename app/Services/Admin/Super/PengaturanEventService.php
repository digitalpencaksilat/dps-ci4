<?php

namespace App\Services\Admin\Super;

use App\Models\KategoriUsiaModel;
use CodeIgniter\Database\BaseConnection;
use Config\Database;

class PengaturanEventService
{
    private BaseConnection $db;
    private KategoriUsiaModel $kategoriUsiaModel;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->kategoriUsiaModel = new KategoriUsiaModel();
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardData(): array
    {
        $kelasTandingRows = $this->kelasTandingRows();
        $subKategoriSeniRows = $this->subKategoriSeniRows();
        $kategoriLombaRows = $this->kategoriLombaRows();

        return [
            'data_kelas_tanding' => $kelasTandingRows,
            'data_sub_kategori_seni' => $subKategoriSeniRows,
            'data_kategori_lomba' => $kategoriLombaRows,
            'data_kategori_usia' => $this->kategoriUsiaRows(),
            'data_kelas_tanding_berdasarkan_juara_tiga_bersama' => $this->groupByField($kelasTandingRows, 'juara_tiga_bersama'),
            'data_kelas_tanding_berdasarkan_format_penilaian' => $this->groupByField($kelasTandingRows, 'format_penilaian'),
            'data_kelas_tanding_berdasarkan_biaya_pendaftaran' => $this->groupByField($kelasTandingRows, 'biaya_pendaftaran_dn'),
            'data_sub_kategori_seni_berdasarkan_sistem_penampilan' => $this->groupByField($subKategoriSeniRows, 'sistem_penampilan'),
            'data_kategori_lomba_berdasarkan_peraturan_pertandingan' => $this->groupByField($kategoriLombaRows, 'peraturan_pertandingan'),
            'data_kategori_lomba_berdasarkan_semua_dapat_medali' => $this->groupByField($kategoriLombaRows, 'semua_dapat_medali'),
            'data_max_peserta_tanding_per_kategori' => $this->maxPesertaTandingPerKategori(),
            'data_max_peserta_seni_per_kategori' => $this->maxPesertaSeniPerKategori(),
            'kontingen_settings' => (new KontingenSettingsService())->currentValues(),
            'default_currency' => (string) (get_setting('default_currency') ?? 'Rp.'),
        ];
    }

    /**
     * @return list<object>
     */
    private function kategoriUsiaRows(): array
    {
        return $this->kategoriUsiaModel
            ->orderBy('min_umur', 'ASC')
            ->orderBy('nama_kategori_usia', 'ASC')
            ->findAll();
    }

    /**
     * @return list<object>
     */
    private function kategoriLombaRows(): array
    {
        return $this->db
            ->table('kategori_lomba kl')
            ->select('kl.*, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('kl.id_kategori_lomba', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * @return list<object>
     */
    private function kelasTandingRows(): array
    {
        return $this->db
            ->table('kelas_tanding kt')
            ->select('kt.*, kl.nama_kategori_lomba, kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('kt.id_kelas_tanding', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * @return list<object>
     */
    private function subKategoriSeniRows(): array
    {
        return $this->db
            ->table('sub_kategori_seni sks')
            ->select('sks.*, kl.nama_kategori_lomba, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('sks.id_sub_kategori_seni', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * @param list<object> $rows
     * @return array<string, list<object>>
     */
    private function groupByField(array $rows, string $field): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $key = (string) ($row->{$field} ?? '');
            $grouped[$key][] = $row;
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @return array<int, array{kategori: string, max_peserta: int|string}>
     */
    private function maxPesertaTandingPerKategori(): array
    {
        $rows = $this->db
            ->table('kompetisi_tanding kt')
            ->select('kt.max_peserta, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kelas_tanding klt', 'klt.id_kelas_tanding = kt.id_kelas_tanding', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = klt.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('kt.id_kompetisi_tanding', 'ASC')
            ->get()
            ->getResult();

        return $this->maxPesertaByKategori($rows);
    }

    /**
     * @return array<int, array{kategori: string, max_peserta: int|string}>
     */
    private function maxPesertaSeniPerKategori(): array
    {
        $rows = $this->db
            ->table('kompetisi_seni ks')
            ->select('ks.max_peserta, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('ks.id_kompetisi_seni', 'ASC')
            ->get()
            ->getResult();

        return $this->maxPesertaByKategori($rows);
    }

    /**
     * @param list<object> $rows
     * @return array<int, array{kategori: string, max_peserta: int|string}>
     */
    private function maxPesertaByKategori(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $kategori = trim(((string) ($row->nama_kategori_usia ?? '')) . ' ' . ucfirst((string) ($row->jenis_kelamin ?? '')));
            if ($kategori === '' || isset($grouped[$kategori])) {
                continue;
            }

            $grouped[$kategori] = [
                'kategori' => $kategori,
                'max_peserta' => $row->max_peserta ?? '-',
            ];
        }

        return array_values($grouped);
    }
}
