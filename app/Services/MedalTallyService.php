<?php

namespace App\Services;

use Config\Database;

class MedalTallyService
{
    private array $medals = ['emas', 'perak', 'perunggu'];

    public function getPerolehanMedaliTanding(): array
    {
        $rows = $this->db()->table('perolehan_medali_tanding pmt')
            ->select('pmt.*, pt.id_peserta_tanding, pt.id_kompetisi_tanding, p.id_pendaftar, p.nama_pendaftar, p.nama_sekolah, k.id_kontingen, k.nama_kontingen, k.provinsi, ktg.nomor_pool, kt.label, kt.berat_minimal, kt.berat_maksimal, kl.nama_kategori_lomba, kl.jenis_perlombaan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('peserta_tanding pt', 'pt.id_peserta_tanding = pmt.id_peserta_tanding')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding ktg', 'ktg.id_kompetisi_tanding = pt.id_kompetisi_tanding', 'left')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = ktg.id_kelas_tanding', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('pmt.id_perolehan_medali_tanding', 'DESC')
            ->get()
            ->getResultArray();

        return $rows;
    }

    public function getPerolehanMedaliSeni(): array
    {
        return $this->db()->table('perolehan_medali_seni pms')
            ->select('pms.*, kps.id_kelompok_peserta_seni, kps.id_kompetisi_seni, k.id_kontingen, k.nama_kontingen, k.provinsi, ks.nomor_pool, sks.nama_seni, sks.jenis_seni, kl.nama_kategori_lomba, kl.jenis_perlombaan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni', false)
            ->select('(SELECT p.nama_sekolah FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni LIMIT 1) AS nama_sekolah', false)
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = pms.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('pms.id_perolehan_medali_seni', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getAkumulasiMedali(): array
    {
        return $this->buildContingentRows(null, false);
    }

    public function getAkumulasiMedaliByKategoriUsia(): array
    {
        return $this->buildByKategoriUsia(false);
    }

    public function getAkumulasiMedaliBerdasarkanSekolah(): array
    {
        $result = [];
        foreach ($this->getKategoriUsiaRows() as $kategori) {
            $result[(string) $kategori['nama_kategori_usia']] = $this->buildSchoolRows((int) $kategori['id_kategori_usia']);
        }

        return $result;
    }

    public function getAkumulasiMedaliEksklusif(): array
    {
        return $this->buildContingentRows(null, true);
    }

    public function getAkumulasiMedaliByKategoriUsiaEksklusif(): array
    {
        return $this->buildByKategoriUsia(true);
    }

    public function getPertandinganBelumInputMedali(): array
    {
        return $this->db()->table('pertandingan p')
            ->select('p.*')
            ->where('p.pemenang IS NOT NULL', null, false)
            ->where('NOT EXISTS (SELECT 1 FROM perolehan_medali_tanding pmt WHERE pmt.id_peserta_tanding IN (p.id_atlet_merah, p.id_atlet_biru))', null, false)
            ->get()
            ->getResultArray();
    }

    public function getPenampilanSeniPoolTanpaMedali(): array
    {
        return $this->db()->table('penampilan_seni ps')
            ->select('ps.*, kps.id_kompetisi_seni, k.nama_kontingen')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->where('ps.nilai_akhir IS NOT NULL', null, false)
            ->where('NOT EXISTS (SELECT 1 FROM perolehan_medali_seni pms WHERE pms.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni)', null, false)
            ->get()
            ->getResultArray();
    }

    private function buildByKategoriUsia(bool $exclusive): array
    {
        $result = [];
        foreach ($this->getKategoriUsiaRows() as $kategori) {
            $result[(string) $kategori['nama_kategori_usia']] = $this->buildContingentRows((int) $kategori['id_kategori_usia'], $exclusive);
        }

        return $result;
    }

    private function buildContingentRows(?int $idKategoriUsia, bool $exclusive): array
    {
        $rows = [];
        foreach ($this->db()->table('kontingen')->select('id_kontingen, nama_kontingen, provinsi')->get()->getResultArray() as $kontingen) {
            $row = $this->emptyContingentRow($kontingen);
            $rows[(int) $kontingen['id_kontingen']] = $row;
        }

        foreach ($this->aggregateTanding($idKategoriUsia, $exclusive) as $item) {
            $id = (int) $item['id_kontingen'];
            if (isset($rows[$id])) {
                $rows[$id][$item['jenis_medali'] . '_tanding'] = (int) $item['jumlah'];
            }
        }

        foreach ($this->aggregateSeni($idKategoriUsia, $exclusive) as $item) {
            $id = (int) $item['id_kontingen'];
            if (isset($rows[$id])) {
                $rows[$id][$item['jenis_medali'] . '_seni'] = (int) $item['jumlah'];
            }
        }

        return $this->rankRows(array_map(fn (array $row): array => $this->normalizeTotals($row), array_values($rows)));
    }

    private function buildSchoolRows(int $idKategoriUsia): array
    {
        $rows = [];

        foreach ($this->aggregateTandingBySchool($idKategoriUsia) as $item) {
            $school = (string) ($item['nama_sekolah'] ?? '');
            $rows[$school] ??= $this->emptySchoolRow($school);
            $rows[$school][$item['jenis_medali'] . '_tanding'] = (int) $item['jumlah'];
        }

        foreach ($this->aggregateSeniBySchool($idKategoriUsia) as $item) {
            $school = (string) ($item['nama_sekolah'] ?? '');
            $rows[$school] ??= $this->emptySchoolRow($school);
            $rows[$school][$item['jenis_medali'] . '_seni'] = (int) $item['jumlah'];
        }

        return $this->rankRows(array_map(fn (array $row): array => $this->normalizeTotals($row), array_values($rows)));
    }

    private function aggregateTanding(?int $idKategoriUsia, bool $exclusive): array
    {
        $builder = $this->db()->table('perolehan_medali_tanding pmt')
            ->select('k.id_kontingen, pmt.jenis_medali, COUNT(*) AS jumlah')
            ->join('peserta_tanding pt', 'pt.id_peserta_tanding = pmt.id_peserta_tanding')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding ktg', 'ktg.id_kompetisi_tanding = pt.id_kompetisi_tanding', 'left')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = ktg.id_kelas_tanding', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba', 'left')
            ->groupBy('k.id_kontingen, pmt.jenis_medali');

        if ($idKategoriUsia !== null) {
            $builder->where('kl.id_kategori_usia', $idKategoriUsia);
        }
        if ($exclusive) {
            $builder->where('EXISTS (SELECT 1 FROM pertandingan pr WHERE pr.id_kompetisi_tanding = pt.id_kompetisi_tanding AND pr.babak = "Semi Final")', null, false);
        }

        return $builder->get()->getResultArray();
    }

    private function aggregateSeni(?int $idKategoriUsia, bool $exclusive): array
    {
        $builder = $this->db()->table('perolehan_medali_seni pms')
            ->select('k.id_kontingen, pms.jenis_medali, COUNT(*) AS jumlah')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = pms.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->groupBy('k.id_kontingen, pms.jenis_medali');

        if ($idKategoriUsia !== null) {
            $builder->where('kl.id_kategori_usia', $idKategoriUsia);
        }
        if ($exclusive) {
            $builder->where('kps.id_kompetisi_seni IN (SELECT id_kompetisi_seni FROM kelompok_peserta_seni GROUP BY id_kompetisi_seni HAVING COUNT(*) >= 3)', null, false);
        }

        return $builder->get()->getResultArray();
    }

    private function aggregateTandingBySchool(int $idKategoriUsia): array
    {
        return $this->db()->table('perolehan_medali_tanding pmt')
            ->select('p.nama_sekolah, pmt.jenis_medali, COUNT(*) AS jumlah')
            ->join('peserta_tanding pt', 'pt.id_peserta_tanding = pmt.id_peserta_tanding')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kompetisi_tanding ktg', 'ktg.id_kompetisi_tanding = pt.id_kompetisi_tanding', 'left')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = ktg.id_kelas_tanding', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba', 'left')
            ->where('kl.id_kategori_usia', $idKategoriUsia)
            ->groupBy('p.nama_sekolah, pmt.jenis_medali')
            ->get()
            ->getResultArray();
    }

    private function aggregateSeniBySchool(int $idKategoriUsia): array
    {
        return $this->db()->table('perolehan_medali_seni pms')
            ->select('(SELECT p.nama_sekolah FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni LIMIT 1) AS nama_sekolah', false)
            ->select('pms.jenis_medali, COUNT(*) AS jumlah')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = pms.id_kelompok_peserta_seni')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->where('kl.id_kategori_usia', $idKategoriUsia)
            ->groupBy('nama_sekolah, pms.jenis_medali')
            ->get()
            ->getResultArray();
    }

    private function getKategoriUsiaRows(): array
    {
        return $this->db()->table('kategori_usia')
            ->select('id_kategori_usia, nama_kategori_usia, MIN(min_umur) AS min_umur')
            ->groupBy('id_kategori_usia, nama_kategori_usia')
            ->orderBy('min_umur', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function emptyContingentRow(array $kontingen): array
    {
        return [
            'id_kontingen' => (int) $kontingen['id_kontingen'],
            'nama_kontingen' => (string) $kontingen['nama_kontingen'],
            'provinsi' => (string) ($kontingen['provinsi'] ?? ''),
            'emas_tanding' => 0,
            'perak_tanding' => 0,
            'perunggu_tanding' => 0,
            'emas_seni' => 0,
            'perak_seni' => 0,
            'perunggu_seni' => 0,
            'total_emas' => 0,
            'total_perak' => 0,
            'total_perunggu' => 0,
        ];
    }

    private function emptySchoolRow(string $school): array
    {
        return [
            'nama_sekolah' => $school,
            'emas_tanding' => 0,
            'perak_tanding' => 0,
            'perunggu_tanding' => 0,
            'emas_seni' => 0,
            'perak_seni' => 0,
            'perunggu_seni' => 0,
            'total_emas' => 0,
            'total_perak' => 0,
            'total_perunggu' => 0,
        ];
    }

    private function normalizeTotals(array $row): array
    {
        foreach ($this->medals as $medal) {
            $row[$medal . '_tanding'] = (int) ($row[$medal . '_tanding'] ?? 0);
            $row[$medal . '_seni'] = (int) ($row[$medal . '_seni'] ?? 0);
            $row['total_' . $medal] = $row[$medal . '_tanding'] + $row[$medal . '_seni'];
        }

        return $row;
    }

    private function rankRows(array $rows): array
    {
        usort($rows, static function (array $a, array $b): int {
            return [$b['total_emas'], $b['total_perak'], $b['total_perunggu']] <=> [$a['total_emas'], $a['total_perak'], $a['total_perunggu']];
        });

        return $rows;
    }

    private function db()
    {
        return Database::connect();
    }
}
