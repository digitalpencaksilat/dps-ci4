<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * ImportJadwalTandingExcelService
 * 
 * Parity 100% dengan CI3: application/models/services/jadwal/Import_excel_jadwal_tanding_model.php
 * Flow: Parse Excel → Validate → Group → Extract Kompetisi → Validate Bracket → Preview (cached) → Commit
 */
class ImportJadwalTandingExcelService
{
    private const CACHE_DIR = WRITEPATH . 'cache/import_jadwal_tanding/';
    private const TOKEN_TTL = 3600; // 1 hour

    public function __construct()
    {
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }
    }

    /**
     * Parse Excel file dan extract data pertandingan
     */
    public function parseExcelFile($file): array
    {
        $spreadsheet = IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $data = [];

        foreach ($sheet->getRowIterator(1) as $row) {
            $cellIterator = $row->getCellIterator('A', 'J');
            $rowData = [];
            foreach ($cellIterator as $cell) {
                $rowData[] = trim((string)$cell->getValue());
            }
            if (!empty(array_filter($rowData))) {
                $data[] = $rowData;
            }
        }

        return $data;
    }

    /**
     * Validasi format Excel dan extract data terstruktur
     * Parity CI3: validasi_format_excel()
     */
    public function validateExcelFormat(array $dataFromExcel, int $idJadwalTanding): array
    {
        // Skip header
        array_shift($dataFromExcel);

        $db = db_connect();
        $statusPengecekan = true;
        $pesan = [];
        $dataPertandingan = [];
        $pesertaTanding = [];
        $kontingen = [];
        $loopIndex = 0;

        $totalRows = count($dataFromExcel);
        for ($baris = 0; $baris < $totalRows; $baris++) {
            $nilai = $dataFromExcel[$baris] ?? [];
            $nomorPartai = trim($nilai[0] ?? '');

            // Hanya proses baris pertandingan nyata: kolom A terisi
            if ($nomorPartai === '') {
                continue;
            }

            $nomorBarisExcel = $baris + 2;
            $namaKategoriUsia = trim($nilai[1] ?? '');
            $namaKategoriLomba = trim($nilai[3] ?? '');
            $label = trim($nilai[4] ?? '');
            $nomorPool = trim($nilai[5] ?? '');
            $namaAtletBiru = trim($nilai[6] ?? '');
            $babak = trim($nilai[7] ?? '');
            $namaAtletMerah = trim($nilai[8] ?? '');

            $jenisKelamin = $this->standarisasiJenisKelamin(trim($nilai[2] ?? ''));
            if ($jenisKelamin === false) {
                $pesan[] = "❌ Baris $nomorBarisExcel (Partai $nomorPartai): Jenis Kelamin '{$nilai[2]}' tidak valid. Gunakan: PUTRA/PUTRI atau MALE/FEMALE.";
                $statusPengecekan = false;
                continue;
            }

            $hasilValidasiPool = $this->validasiNomorPool($nomorPool, $nomorBarisExcel, $nomorPartai);
            if (!$hasilValidasiPool['status']) {
                $pesan = array_merge($pesan, $hasilValidasiPool['pesan']);
                $statusPengecekan = false;
                continue;
            }

            $hasilValidasiKelas = $this->validasiKategoriDanKelas($namaKategoriUsia, $jenisKelamin, $namaKategoriLomba, $label, $nomorBarisExcel);
            if (!$hasilValidasiKelas['status']) {
                $pesan = array_merge($pesan, $hasilValidasiKelas['pesan']);
                $statusPengecekan = false;
                continue;
            }

            // Baris kontingen opsional: baris berikutnya dengan kolom A kosong tapi G/I terisi
            $barisKontingen = [];
            $nextRow = $dataFromExcel[$baris + 1] ?? [];
            $nextNomorPartai = trim($nextRow[0] ?? '');
            $nextBiru = trim($nextRow[6] ?? '');
            $nextMerah = trim($nextRow[8] ?? '');
            if ($nextNomorPartai === '' && ($nextBiru !== '' || $nextMerah !== '')) {
                $barisKontingen = $nextRow;
            }

            $teksKontingenBiru = trim($barisKontingen[6] ?? '');
            $teksKontingenMerah = trim($barisKontingen[8] ?? '');

            $pertandinganAkanDiinput = [
                'id_kelas_tanding' => $hasilValidasiKelas['data']->id_kelas_tanding,
                'id_kompetisi_tanding' => null,
                'nomor_pertandingan' => null,
                'nomor_pertandingan_selanjutnya' => null,
                'nomor_partai' => $nomorPartai,
                'babak' => $this->standarisasiBabak($babak),
                'prioritas_babak' => $this->ambilNilaiBabak($babak),
                'nomor_baris_excel' => $nomorBarisExcel,
            ];

            foreach ([
                'biru' => [$namaAtletBiru, $teksKontingenBiru],
                'merah' => [$namaAtletMerah, $teksKontingenMerah],
            ] as $sudut => [$teksAtlet, $teksKontingen]) {
                $nomorWinner = $this->ekstrakNomorWinner($teksAtlet, $teksKontingen);

                if ($nomorWinner !== null) {
                    $pertandinganAkanDiinput["nama_atlet_$sudut"] = null;
                    $pertandinganAkanDiinput["nama_kontingen_atlet_$sudut"] = null;
                    $pertandinganAkanDiinput["nomor_partai_calon_atlet_$sudut"] = $nomorWinner;
                } elseif (!empty($teksAtlet)) {
                    $pesertaBaru = [
                        'nama_pendaftar' => $teksAtlet,
                        'nama_kontingen' => $teksKontingen,
                        'nama_kategori_usia' => $namaKategoriUsia,
                        'jenis_kelamin' => $jenisKelamin,
                        'label' => $label,
                        'nomor_pool' => $nomorPool,
                    ];
                    $pertandinganAkanDiinput["nama_atlet_$sudut"] = $teksAtlet;
                    $pertandinganAkanDiinput["nama_kontingen_atlet_$sudut"] = $teksKontingen;
                    $pesertaTanding[] = $pesertaBaru;
                    if ($teksKontingen !== '' && !in_array($teksKontingen, $kontingen, true)) {
                        $kontingen[] = $teksKontingen;
                    }
                    $loopIndex++;
                } else {
                    $pertandinganAkanDiinput["nama_atlet_$sudut"] = null;
                    $pertandinganAkanDiinput["nama_kontingen_atlet_$sudut"] = null;
                    $pertandinganAkanDiinput["is_bye_$sudut"] = true;
                }
            }

            $dataPertandingan[$namaKategoriUsia][$jenisKelamin][$label][$nomorPool][] = $pertandinganAkanDiinput;
        }

        // Validasi silang — cek PP reference ada di pool ini atau di DB
        foreach ($dataPertandingan as $kUsia => $v1) {
            foreach ($v1 as $kJk => $v2) {
                foreach ($v2 as $kLabel => $v3) {
                    foreach ($v3 as $kPool => $arrPool) {
                        $semuaNomor = array_column($arrPool, 'nomor_partai');
                        foreach ($arrPool as $p) {
                            foreach (['biru', 'merah'] as $sudut) {
                                $key = "nomor_partai_calon_atlet_$sudut";
                                if (!isset($p[$key]) || $p[$key] === '') {
                                    continue;
                                }

                                $nomorRef = $p[$key];
                                if (in_array($nomorRef, $semuaNomor)) {
                                    continue; // ada di Excel ini ✅
                                }

                                // Cek di DB
                                if ($idJadwalTanding !== null) {
                                    if ($this->cariPertandinganDiDb($nomorRef, $idJadwalTanding) !== null) {
                                        continue;
                                    }
                                }

                                $pesan[] = "❌ Baris {$p['nomor_baris_excel']} (Partai {$p['nomor_partai']}): "
                                    . "Slot $sudut mereferensikan 'Winner $nomorRef', namun Partai $nomorRef "
                                    . "tidak ditemukan dalam grup ($kUsia / $kJk / $kLabel / Pool $kPool) "
                                    . "maupun di data yang sudah pernah diimport. "
                                    . "→ Pastikan Partai $nomorRef ada di file Excel ini atau sudah diimport sebelumnya.";
                                $statusPengecekan = false;
                            }
                        }
                    }
                }
            }
        }

        // Kelompokkan pertandingan per kompetisi
        $dataPertandingan = $this->kelompokkanPertandingan($dataPertandingan);
        $dataKompetisiTanding = $this->ekstrakKompetisiTanding($dataPertandingan);

        // Validasi jalur winner ganda (bracket bentrok)
        $hasilValidasiBracket = $this->validasiJalurWinnerGanda($dataPertandingan);
        if (!$hasilValidasiBracket['status']) {
            $pesan = array_merge($pesan, $hasilValidasiBracket['pesan']);
            $statusPengecekan = false;
        }

        if (!$statusPengecekan) {
            return ['status' => false, 'message' => $pesan];
        }

        return [
            'status' => true,
            'message' => [],
            'data_pertandingan' => $dataPertandingan,
            'data_kompetisi_tanding' => $dataKompetisiTanding,
            'peserta_tanding' => $pesertaTanding,
            'kontingen' => $kontingen,
        ];
    }

    /**
     * Kelompokkan pertandingan per kompetisi (parity CI3: _kelompokkan_pertandingan)
     * Return nested 4-level array untuk kompatibilitas CommitService
     */
    private function kelompokkanPertandingan(array $dataPertandingan): array
    {
        $result = [];

        foreach ($dataPertandingan as $kUsia => $v1) {
            foreach ($v1 as $kJk => $v2) {
                foreach ($v2 as $kLabel => $v3) {
                    foreach ($v3 as $kPool => $arrPool) {
                        // Urutkan per babak (prioritas_babak descending)
                        usort($arrPool, function ($a, $b) {
                            return $b['prioritas_babak'] <=> $a['prioritas_babak'];
                        });

                        // Isi nomor_pertandingan dan nomor_pertandingan_selanjutnya
                        $arrPool = $this->isiNomorPertandingan($arrPool);

                        // Simpan nested 4-level structure
                        if (!isset($result[$kUsia])) {
                            $result[$kUsia] = [];
                        }
                        if (!isset($result[$kUsia][$kJk])) {
                            $result[$kUsia][$kJk] = [];
                        }
                        if (!isset($result[$kUsia][$kJk][$kLabel])) {
                            $result[$kUsia][$kJk][$kLabel] = [];
                        }

                        $result[$kUsia][$kJk][$kLabel][$kPool] = $arrPool;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Isi nomor_pertandingan dan nomor_pertandingan_selanjutnya (parity CI3: _isi_nomor_pertandingan)
     * 
     * Format nomor_pertandingan: {nomor_partai}{sufiks}
     * - sufiks '1' untuk biru (ganjil)
     * - sufiks '2' untuk merah (genap)
     * 
     * Contoh:
     * - Partai 165 (Final): nomor_pertandingan = "165"
     * - Partai 71 (SF biru ke 165): nomor_pertandingan = "711", nomor_pertandingan_selanjutnya = "165"
     * - Partai 72 (SF merah ke 165): nomor_pertandingan = "722", nomor_pertandingan_selanjutnya = "165"
     */
    private function isiNomorPertandingan(array $arrayPertandinganSatuPool): array
    {
        foreach ($arrayPertandinganSatuPool as $keyPertandingan => $pertandingan) {
            foreach (['biru', 'merah'] as $sudut) {
                $keyCalonAtlet = "nomor_partai_calon_atlet_$sudut";
                
                // Cek apakah ini slot Winner/PP (nama_atlet NULL, punya nomor_partai_calon_atlet)
                if (
                    $pertandingan["nama_atlet_$sudut"] === null &&
                    !isset($pertandingan["is_bye_$sudut"]) &&
                    isset($pertandingan[$keyCalonAtlet]) &&
                    $pertandingan[$keyCalonAtlet] !== ''
                ) {
                    // Update pertandingan sebelumnya (feeder)
                    $arrayPertandinganSatuPool = $this->updatePertandinganSebelumnya(
                        $pertandingan['nomor_partai'],
                        $pertandingan[$keyCalonAtlet],
                        $arrayPertandinganSatuPool,
                        $sudut
                    );
                }
            }
        }

        return $arrayPertandinganSatuPool;
    }

    /**
     * Update pertandingan feeder dengan nomor_pertandingan dan nomor_pertandingan_selanjutnya
     * Parity CI3: _update_pertandingan_sebelumnya
     */
    private function updatePertandinganSebelumnya(
        string $nomorPartaiSaatIni,
        string $nomorPartaiCalonAtlet,
        array $arrayPertandinganSatuPool,
        string $sudut
    ): array {
        $keyPartaiSebelumnya = null;
        $keyPartaiSaatIni = null;

        foreach ($arrayPertandinganSatuPool as $key => $pertandingan) {
            if ((string)$pertandingan['nomor_partai'] === (string)$nomorPartaiCalonAtlet) {
                $keyPartaiSebelumnya = $key;
            } elseif ((string)$pertandingan['nomor_partai'] === (string)$nomorPartaiSaatIni) {
                $keyPartaiSaatIni = $key;
            }
        }

        // Guard: partai tidak ditemukan di array ini (cross-import)
        if ($keyPartaiSebelumnya === null || $keyPartaiSaatIni === null) {
            return $arrayPertandinganSatuPool;
        }

        // Partai saat ini (Final/Semi Final) — nomor_pertandingan = nomor_partai sendiri
        $arrayPertandinganSatuPool[$keyPartaiSaatIni]['nomor_pertandingan'] = $nomorPartaiSaatIni;

        // Partai feeder — nomor_pertandingan = nomor_partai + sufiks (1=biru, 2=merah)
        $sufiks = ($sudut === 'biru') ? '1' : '2';
        $arrayPertandinganSatuPool[$keyPartaiSebelumnya]['nomor_pertandingan'] = $nomorPartaiCalonAtlet . $sufiks;
        $arrayPertandinganSatuPool[$keyPartaiSebelumnya]['nomor_pertandingan_selanjutnya'] = $nomorPartaiSaatIni;

        return $arrayPertandinganSatuPool;
    }

    /**
     * Ekstrak kompetisi_tanding dari data pertandingan (parity CI3: _ekstrak_kompetisi_tanding)
     * Return nested 4-level array untuk kompatibilitas CommitService
     */
    private function ekstrakKompetisiTanding(array $dataPertandingan): array
    {
        $result = [];

        foreach ($dataPertandingan as $kUsia => $v1) {
            foreach ($v1 as $kJk => $v2) {
                foreach ($v2 as $kLabel => $v3) {
                    foreach ($v3 as $kPool => $arrPool) {
                        // Ambil id_kelas_tanding dari pertandingan pertama
                        $idKelasTanding = $arrPool[0]['id_kelas_tanding'] ?? null;

                        // Build nested structure
                        if (!isset($result[$kUsia])) {
                            $result[$kUsia] = [];
                        }
                        if (!isset($result[$kUsia][$kJk])) {
                            $result[$kUsia][$kJk] = [];
                        }
                        if (!isset($result[$kUsia][$kJk][$kLabel])) {
                            $result[$kUsia][$kJk][$kLabel] = [];
                        }

                        $result[$kUsia][$kJk][$kLabel][$kPool] = [
                            'id_kelas_tanding' => $idKelasTanding,
                            'id_kompetisi_tanding' => null,
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Validasi jalur winner ganda (bracket bentrok) — parity CI3: _validasi_jalur_winner_ganda
     * dataPertandingan sudah nested 4-level
     */
    private function validasiJalurWinnerGanda(array $dataPertandingan): array
    {
        $pesan = [];

        foreach ($dataPertandingan as $kUsia => $v1) {
            foreach ($v1 as $kJk => $v2) {
                foreach ($v2 as $kLabel => $v3) {
                    foreach ($v3 as $kPool => $arrPool) {
                        // Cek apakah ada partai yang menerima 2+ feeder pada sisi yang sama
                        // Sisi ditentukan dari nomor_pertandingan feeder: ganjil=biru, genap=merah
                        $feeders = [];

                        foreach ($arrPool as $p) {
                            if (!isset($p['nomor_pertandingan_selanjutnya']) || $p['nomor_pertandingan_selanjutnya'] === null) {
                                continue;
                            }

                            $nomorTujuan = $p['nomor_pertandingan_selanjutnya'];
                            $sisi = ((int)$p['nomor_pertandingan'] % 2 === 1) ? 'biru' : 'merah';
                            $feeders[$nomorTujuan][$sisi][] = $p['nomor_partai'];
                        }

                        // Deteksi bentrok
                        foreach ($feeders as $nomorTujuan => $sisiData) {
                            foreach ($sisiData as $sisi => $sources) {
                                if (count($sources) > 1) {
                                    $nomorPartaiSumber = implode(', ', $sources);
                                    $sisiLabel = ($sisi === 'biru') ? 'BIRU/BLUE' : 'MERAH/RED';
                                    $pesan[] = "❌ Jadwal tidak bisa ditampilkan karena struktur bracket ganda terdeteksi. "
                                        . "Partai tujuan $nomorTujuan pada sisi $sisiLabel menerima " . count($sources) . " feeder sekaligus "
                                        . "(partai sumber: $nomorPartaiSumber). "
                                        . "Periksa data hasil import Excel atau bersihkan data pertandingan ganda di database.";
                                }
                            }
                        }
                    }
                }
            }
        }

        return [
            'status' => empty($pesan),
            'pesan' => $pesan,
        ];
    }

    /**
     * Generate token untuk cache preview
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Simpan preview ke cache
     */
    public function savePreview(string $token, array $data): void
    {
        $file = self::CACHE_DIR . $token . '.json';
        file_put_contents($file, json_encode($data));
    }

    /**
     * Load preview dari cache
     */
    public function loadPreview(string $token): ?array
    {
        $file = self::CACHE_DIR . $token . '.json';
        if (!file_exists($file) || (time() - filemtime($file)) > self::TOKEN_TTL) {
            return null;
        }
        return json_decode(file_get_contents($file), true);
    }

    /**
     * Delete preview cache
     */
    public function deletePreview(string $token): void
    {
        $file = self::CACHE_DIR . $token . '.json';
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // =========================================================================
    // Helper methods
    // =========================================================================

    private function standarisasiJenisKelamin(string $input): ?string
    {
        $input = strtoupper(trim($input));
        $map = [
            'PUTRA' => 'PUTRA',
            'PUTRI' => 'PUTRI',
            'MALE' => 'PUTRA',
            'FEMALE' => 'PUTRI',
            'M' => 'PUTRA',
            'F' => 'PUTRI',
        ];
        return $map[$input] ?? false;
    }

    private function standarisasiBabak(string $input): string
    {
        $input = strtolower(trim($input));
        $map = [
            'final' => 'final',
            'finale' => 'final',
            'semi final' => 'semi final',
            'semi-final' => 'semi final',
            'semifinal' => 'semi final',
            '1/2 final' => 'semi final',
            '1/2final' => 'semi final',
            '1/2-final' => 'semi final',
            'perempat final' => '1/4 final',
            '1/4 final' => '1/4 final',
            '1/4final' => '1/4 final',
            '1/4-final' => '1/4 final',
            'perdelapan final' => '1/8 final',
            '1/8 final' => '1/8 final',
            '1/8final' => '1/8 final',
            '1/8-final' => '1/8 final',
            '1/16 final' => '1/16 final',
            '1/16final' => '1/16 final',
            '1/16-final' => '1/16 final',
            'perebutan juara tiga' => 'perebutan juara tiga',
            'third place' => 'perebutan juara tiga',
            'third-place' => 'perebutan juara tiga',
            'thirdplace' => 'perebutan juara tiga',
            'playoff for third place' => 'perebutan juara tiga',
            'play-off for third place' => 'perebutan juara tiga',
            'penyisihan' => 'penyisihan',
        ];
        return $map[$input] ?? ucfirst($input);
    }

    private function ambilNilaiBabak(string $input): float
    {
        $input = strtolower(trim($input));
        $map = [
            'final' => 1.0,
            'finale' => 1.0,
            'semi final' => 0.5,
            'semi-final' => 0.5,
            'semifinal' => 0.5,
            '1/2 final' => 0.5,
            '1/2final' => 0.5,
            '1/2-final' => 0.5,
            '1/4 final' => 0.25,
            '1/4final' => 0.25,
            '1/4-final' => 0.25,
            '1/8 final' => 0.125,
            '1/8final' => 0.125,
            '1/8-final' => 0.125,
            '1/16 final' => 0.0625,
            '1/16final' => 0.0625,
            '1/16-final' => 0.0625,
            'perebutan juara tiga' => 0.5,
            'third place' => 0.5,
            'third-place' => 0.5,
            'thirdplace' => 0.5,
            'playoff for third place' => 0.5,
            'play-off for third place' => 0.5,
            'penyisihan' => 0,
        ];
        return $map[$input] ?? 0;
    }

    private function ekstrakNomorWinner(string $teksAtlet, string $teksKontingen): ?string
    {
        // CI3 pattern: /^\s*(winner|pp|pemenang\s+partai)\s*(\d+)\s*$/i
        $pattern = '/^\s*(winner|pp|pemenang\s+partai)\s*(\d+)\s*$/i';

        if (preg_match($pattern, $teksAtlet, $m)) {
            return $m[2];
        }
        if (preg_match($pattern, $teksKontingen, $m)) {
            return $m[2];
        }
        return null;
    }

    private function validasiNomorPool(string $nomorPool, int $nomorBarisExcel, string $nomorPartai): array
    {
        if (empty($nomorPool)) {
            return [
                'status' => false,
                'pesan' => ["❌ Baris $nomorBarisExcel (Partai $nomorPartai): Nomor Pool tidak boleh kosong."],
            ];
        }

        if (!is_numeric($nomorPool) && strcasecmp($nomorPool, 'auto') !== 0) {
            return [
                'status' => false,
                'pesan' => ["❌ Baris $nomorBarisExcel (Partai $nomorPartai): Nomor Pool '$nomorPool' tidak valid. Gunakan angka atau 'auto'."],
            ];
        }

        return ['status' => true, 'pesan' => []];
    }

    private function validasiKategoriDanKelas(string $namaKategoriUsia, string $jenisKelamin, string $namaKategoriLomba, string $label, int $nomorBarisExcel): array
    {
        $db = db_connect();

        $kelas = $db->table('kelas_tanding kt')
            ->select('kt.*')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('ku.nama_kategori_usia', $namaKategoriUsia)
            ->where('ku.jenis_kelamin', $jenisKelamin)
            ->where('kl.nama_kategori_lomba', $namaKategoriLomba)
            ->where('kt.label', $label)
            ->get()
            ->getRow();

        if ($kelas === null) {
            return [
                'status' => false,
                'pesan' => ["❌ Baris $nomorBarisExcel: Kategori tidak ditemukan di database ($namaKategoriUsia / $jenisKelamin / $namaKategoriLomba / $label)."],
                'data' => null,
            ];
        }

        return ['status' => true, 'pesan' => [], 'data' => $kelas];
    }

    private function cariPertandinganDiDb(string $nomorPartai, int $idJadwalTanding): ?object
    {
        $db = db_connect();
        return $db->table('detail_jadwal_tanding djt')
            ->select('djt.*, p.nomor_pertandingan')
            ->join('pertandingan p', 'p.id_pertandingan = djt.id_pertandingan')
            ->where('djt.id_jadwal_tanding', $idJadwalTanding)
            ->where('djt.nomor_partai', $nomorPartai)
            ->get()
            ->getRow();
    }
}
