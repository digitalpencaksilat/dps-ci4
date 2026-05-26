<?php

namespace App\Services;

class SekretariatStatistikService
{
    public function getPendaftaranStats(): array
    {
        return [
            'summary' => $this->pendaftaranSummary(),
            'kontingenProgress' => $this->kontingenProgress(),
            'pendaftarProgress' => $this->pendaftarProgress(),
            'tandingBreakdown' => $this->tandingBreakdown(),
            'seniBreakdown' => $this->seniBreakdown(),
            'kontingenProvinsi' => $this->topKontingenByRegion('provinsi'),
            'kontingenKabupaten' => $this->topKontingenByRegion('kabupaten_kota'),
            'pendaftarProvinsi' => $this->topPendaftarByRegion('provinsi'),
            'pendaftarKabupaten' => $this->topPendaftarByRegion('kabupaten_kota'),
        ];
    }

    public function getTandingStats(): array
    {
        return [
            'summary' => $this->tandingSummary(),
            'paymentByCategory' => $this->tandingPaymentByCategory(),
            'poolByCategory' => $this->tandingPoolByCategory(),
            'matchesByCategory' => $this->tandingMatchesByCategory(),
            'tableRows' => $this->tandingTableRows(),
        ];
    }

    public function getSeniStats(): array
    {
        return [
            'summary' => $this->seniSummary(),
            'jenisDistribution' => $this->seniJenisDistribution(),
            'jenisByCategory' => $this->seniJenisByCategory(),
            'poolByCategory' => $this->seniPoolByCategory(),
            'tableRows' => $this->seniTableRows(),
        ];
    }

    private function pendaftaranSummary(): array
    {
        $db = db_connect();

        return [
            'kontingen' => (int) $db->table('kontingen')->countAllResults(),
            'pendaftar' => (int) $db->table('pendaftar')->countAllResults(),
            'pesertaTanding' => (int) $db->table('peserta_tanding')->countAllResults(),
            'kelompokSeni' => (int) $db->table('kelompok_peserta_seni')->countAllResults(),
            'kontingenTanpaPendaftar' => (int) $db->table('kontingen k')->join('pendaftar p', 'p.id_kontingen = k.id_kontingen', 'left')->where('p.id_pendaftar IS NULL', null, false)->countAllResults(),
            'pendaftarTanpaKategori' => (int) $db->table('pendaftar p')->join('peserta_tanding pt', 'pt.id_pendaftar = p.id_pendaftar', 'left')->join('peserta_seni ps', 'ps.id_pendaftar = p.id_pendaftar', 'left')->where('pt.id_peserta_tanding IS NULL', null, false)->where('ps.id_peserta_seni IS NULL', null, false)->countAllResults(),
        ];
    }

    private function kontingenProgress(): array
    {
        $rows = db_connect()->table('kontingen')
            ->select('DATE(tanggal_daftar) AS tanggal, COUNT(*) AS jumlah_harian', false)
            ->where('tanggal_daftar IS NOT NULL', null, false)
            ->groupBy('DATE(tanggal_daftar)')
            ->orderBy('DATE(tanggal_daftar)', 'ASC')
            ->get()->getResultArray();

        return $this->toCumulativeSeries($rows, 'jumlah_harian');
    }

    private function pendaftarProgress(): array
    {
        $rows = db_connect()->table('pendaftar')
            ->select('DATE(tanggal_daftar) AS tanggal, COUNT(*) AS jumlah_harian', false)
            ->where('tanggal_daftar IS NOT NULL', null, false)
            ->groupBy('DATE(tanggal_daftar)')
            ->orderBy('DATE(tanggal_daftar)', 'ASC')
            ->get()->getResultArray();

        return $this->toCumulativeSeries($rows, 'jumlah_harian');
    }

    private function tandingBreakdown(): array
    {
        $rows = db_connect()->table('peserta_tanding pt')
            ->select('kl.jenis_perlombaan, COUNT(*) AS total')
            ->join('kompetisi_tanding ktd', 'ktd.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = ktd.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->groupBy('kl.jenis_perlombaan')
            ->get()->getResultArray();

        return $this->toPieSeries($rows, 'jenis_perlombaan', 'total');
    }

    private function seniBreakdown(): array
    {
        $rows = db_connect()->table('kelompok_peserta_seni kps')
            ->select('LOWER(sks.jenis_seni) AS jenis_seni, COUNT(*) AS total', false)
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->groupBy('LOWER(sks.jenis_seni)')
            ->get()->getResultArray();

        return $this->toPieSeries($rows, 'jenis_seni', 'total');
    }

    private function topKontingenByRegion(string $field): array
    {
        $rows = db_connect()->table('kontingen')
            ->select($field . ' AS wilayah, COUNT(*) AS total', false)
            ->where($field . ' IS NOT NULL', null, false)
            ->where($field . ' !=', '')
            ->groupBy($field)
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->toCategorySeries($rows, 'wilayah', 'total');
    }

    private function topPendaftarByRegion(string $field): array
    {
        $rows = db_connect()->table('pendaftar p')
            ->select('k.' . $field . ' AS wilayah, COUNT(*) AS total', false)
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen', 'left')
            ->where('k.' . $field . ' IS NOT NULL', null, false)
            ->where('k.' . $field . ' !=', '')
            ->groupBy('k.' . $field)
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        return $this->toCategorySeries($rows, 'wilayah', 'total');
    }

    private function tandingSummary(): array
    {
        $db = db_connect();
        $prestasi = (int) $db->table('peserta_tanding pt')->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')->where('kl.jenis_perlombaan', 'prestasi')->countAllResults();
        $pemasalan = (int) $db->table('peserta_tanding pt')->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')->where('kl.jenis_perlombaan', 'pemasalan')->countAllResults();
        $prediksiPrestasi = 0;
        $prediksiPemasalan = 0;
        foreach ((new SekretariatKategoriTandingService())->listKelas() as $kelas) {
            $prediksi = max((int) ($kelas->prediksi_jumlah_partai ?? 0), 0);
            if (($kelas->jenis_perlombaan ?? '') === 'prestasi') {
                $prediksiPrestasi += $prediksi;
                continue;
            }

            if (($kelas->jenis_perlombaan ?? '') === 'pemasalan') {
                $prediksiPemasalan += $prediksi;
            }
        }

        return [
            'totalPeserta' => $prestasi + $pemasalan,
            'jumlahPool' => (int) $db->table('kompetisi_tanding')->countAllResults(),
            'pesertaPrestasi' => $prestasi,
            'pesertaPemasalan' => $pemasalan,
            'prediksiPartaiPrestasi' => max($prediksiPrestasi, 0),
            'prediksiPartaiPemasalan' => max($prediksiPemasalan, 0),
        ];
    }

    private function tandingPaymentByCategory(): array
    {
        $rows = $this->tandingTableRows();
        $categories = [];
        $lunas = [];
        $belum = [];
        foreach ($rows as $row) {
            $categories[] = $row['kategori'];
            $lunas[] = (int) $row['jumlah_peserta_tanding_lunas'];
            $belum[] = (int) $row['peserta_belum_lunas'];
        }

        return ['categories' => $categories, 'series' => [['name' => 'Lunas', 'data' => $lunas], ['name' => 'Belum Lunas', 'data' => $belum]]];
    }

    private function tandingPoolByCategory(): array
    {
        $rows = $this->tandingTableRows();
        return $this->rowsToSimpleChart($rows, 'kategori', 'jumlah_pool');
    }

    private function tandingMatchesByCategory(): array
    {
        $rows = $this->tandingTableRows();
        return $this->rowsToSimpleChart($rows, 'kategori', 'jumlah_partai');
    }

    private function tandingTableRows(): array
    {
        $rows = db_connect()->table('kategori_lomba kl')
            ->select('CONCAT(ku.nama_kategori_usia, " ", UPPER(ku.jenis_kelamin)) AS kategori', false)
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt JOIN kompetisi_tanding kom ON kom.id_kompetisi_tanding = pt.id_kompetisi_tanding JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding WHERE kt.id_kategori_lomba = kl.id_kategori_lomba) AS jumlah_peserta_tanding', false)
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt JOIN pembayaran pb ON pb.id_pembayaran = pt.id_pembayaran JOIN kompetisi_tanding kom ON kom.id_kompetisi_tanding = pt.id_kompetisi_tanding JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding WHERE kt.id_kategori_lomba = kl.id_kategori_lomba AND pb.status_pembayaran = "lunas") AS jumlah_peserta_tanding_lunas', false)
            ->select('(SELECT COUNT(*) FROM kompetisi_tanding kom JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding WHERE kt.id_kategori_lomba = kl.id_kategori_lomba) AS jumlah_pool_tanding', false)
            ->select('(SELECT COUNT(*) FROM pertandingan p JOIN kompetisi_tanding kom ON kom.id_kompetisi_tanding = p.id_kompetisi_tanding JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding WHERE kt.id_kategori_lomba = kl.id_kategori_lomba AND p.jenis_kemenangan != "BYE") AS jumlah_partai_tanding', false)
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kl.nama_kategori_lomba', 'tanding')
            ->orderBy('ku.min_umur', 'ASC')
            ->get()->getResultArray();

        return array_map(static function (array $row): array {
            $peserta = (int) ($row['jumlah_peserta_tanding'] ?? 0);
            $lunas = (int) ($row['jumlah_peserta_tanding_lunas'] ?? 0);
            $row['peserta_belum_lunas'] = max($peserta - $lunas, 0);
            $row['jumlah_pool'] = (int) ($row['jumlah_pool_tanding'] ?? 0);
            $row['jumlah_partai'] = (int) ($row['jumlah_partai_tanding'] ?? 0);
            return $row;
        }, $rows);
    }

    private function seniSummary(): array
    {
        $db = db_connect();
        return [
            'totalPeserta' => (int) $db->table('kelompok_peserta_seni')->countAllResults(),
            'jumlahPool' => (int) $db->table('kompetisi_seni')->countAllResults(),
            'tunggal' => (int) $this->countKelompokByJenisSeni('tunggal'),
            'ganda' => (int) $this->countKelompokByJenisSeni('ganda'),
            'beregu' => (int) $this->countKelompokByJenisSeni('beregu'),
        ];
    }

    private function seniJenisDistribution(): array
    {
        $rows = db_connect()->table('kelompok_peserta_seni kps')
            ->select('LOWER(sks.jenis_seni) AS jenis_seni, COUNT(*) AS total', false)
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->groupBy('LOWER(sks.jenis_seni)')
            ->orderBy('total', 'DESC')
            ->get()->getResultArray();

        return $this->toPieSeries($rows, 'jenis_seni', 'total');
    }

    private function seniJenisByCategory(): array
    {
        $rows = $this->seniTableRows();
        $categories = [];
        $tunggal = [];
        $ganda = [];
        $beregu = [];
        foreach ($rows as $row) {
            $categories[] = $row['kategori'];
            $tunggal[] = (int) $row['jumlah_kelompok_peserta_seni_tunggal'];
            $ganda[] = (int) $row['jumlah_kelompok_peserta_seni_ganda'];
            $beregu[] = (int) $row['jumlah_kelompok_peserta_seni_beregu'];
        }

        return ['categories' => $categories, 'series' => [['name' => 'Tunggal', 'data' => $tunggal], ['name' => 'Ganda', 'data' => $ganda], ['name' => 'Beregu', 'data' => $beregu]]];
    }

    private function seniPoolByCategory(): array
    {
        $rows = $this->seniTableRows();
        return $this->rowsToSimpleChart($rows, 'kategori', 'jumlah_pool');
    }

    private function seniTableRows(): array
    {
        $rows = db_connect()->table('kategori_lomba kl')
            ->select('CONCAT(ku.nama_kategori_usia, " - ", ku.jenis_kelamin) AS kategori', false)
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba AND LOWER(sks.jenis_seni) = "tunggal") AS jumlah_kelompok_peserta_seni_tunggal', false)
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba AND LOWER(sks.jenis_seni) = "ganda") AS jumlah_kelompok_peserta_seni_ganda', false)
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba AND LOWER(sks.jenis_seni) = "beregu") AS jumlah_kelompok_peserta_seni_beregu', false)
            ->select('(SELECT COUNT(*) FROM kompetisi_seni ks JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba) AS jumlah_pool_seni', false)
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kl.nama_kategori_lomba', 'seni')
            ->orderBy('ku.min_umur', 'ASC')
            ->get()->getResultArray();

        return array_map(static function (array $row): array {
            $row['jumlah_pool'] = (int) ($row['jumlah_pool_seni'] ?? 0);
            return $row;
        }, $rows);
    }

    private function countKelompokByJenisSeni(string $jenis): int
    {
        return db_connect()->table('kelompok_peserta_seni kps')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->where('LOWER(sks.jenis_seni)', strtolower($jenis))
            ->countAllResults();
    }

    private function toCumulativeSeries(array $rows, string $valueKey): array
    {
        $categories = [];
        $series = [];
        $running = 0;
        foreach ($rows as $row) {
            $categories[] = $row['tanggal'];
            $running += (int) ($row[$valueKey] ?? 0);
            $series[] = $running;
        }

        return ['categories' => $categories, 'series' => $series, 'lastValue' => $running];
    }

    private function toPieSeries(array $rows, string $labelKey, string $valueKey): array
    {
        $labels = [];
        $series = [];
        foreach ($rows as $row) {
            $labels[] = ucwords((string) ($row[$labelKey] ?? '-'));
            $series[] = (int) ($row[$valueKey] ?? 0);
        }

        return ['labels' => $labels, 'series' => $series];
    }

    private function toCategorySeries(array $rows, string $labelKey, string $valueKey): array
    {
        $categories = [];
        $series = [];
        foreach ($rows as $row) {
            $categories[] = (string) ($row[$labelKey] ?? '-');
            $series[] = (int) ($row[$valueKey] ?? 0);
        }

        return ['categories' => $categories, 'series' => $series];
    }

    private function rowsToSimpleChart(array $rows, string $labelKey, string $valueKey): array
    {
        $categories = [];
        $series = [];
        foreach ($rows as $row) {
            $categories[] = (string) ($row[$labelKey] ?? '-');
            $series[] = (int) ($row[$valueKey] ?? 0);
        }

        return ['categories' => $categories, 'series' => $series];
    }
}
