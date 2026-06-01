<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportJadwalSeniBattleExcelService
{
    protected $db;
    protected $subKategoriSeniModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->subKategoriSeniModel = model('SubKategoriSeniModel');
    }

    /**
     * Parse Excel file dan return raw data
     */
    public function parseExcel($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();
            
            // Remove header row
            array_shift($data);
            
            return $data;
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Gagal membaca file Excel: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validasi format Excel dan ekstrak data untuk battle system
     * Battle system: 2 athletes per entry (blue+red), Winner/PP reference support, bracket numbering
     * Return: ['status' => bool, 'data' => [...], 'message' => [...]]
     */
    public function validateAndExtract($rawData)
    {
        $status = true;
        $messages = [];
        
        $kontingen = [];
        $anggotaKelompokPesertaSeniBiru = [];
        $anggotaKelompokPesertaSeniMerah = [];
        $kelompokPesertaSeni = [];
        $dataBattleSeni = [];
        
        $loopIndex = 0;

        foreach ($rawData as $rowIndex => $row) {
            // Even rows (0, 2, 4...) contain battle data
            if ($rowIndex % 2 == 0) {
                $nomorPartai = trim($row[0] ?? '');
                $namaKategoriUsia = trim($row[1] ?? '');
                $jenisKelamin = strtolower(trim($row[2] ?? ''));
                $jenisSeni = strtolower(trim($row[3] ?? ''));
                $namaSeni = trim($row[4] ?? '');
                $nomorPool = trim($row[5] ?? '');
                $namaAtletBiru = trim($row[6] ?? '');
                $babak = strtolower(trim($row[7] ?? ''));
                $namaAtletMerah = trim($row[8] ?? '');
                
                $excelRowNum = $rowIndex + 2; // +1 for header, +1 for 1-indexed
                
                // Standardize jenis_kelamin
                $jenisKelamin = $this->standardizeJenisKelamin($jenisKelamin);
                if ($jenisKelamin === false) {
                    $messages[] = "❌ Baris $excelRowNum: Jenis Kelamin '{$row[2]}' tidak valid. Gunakan: PUTRA atau PUTRI.";
                    $status = false;
                    continue;
                }
                
                // Validate pool number is numeric
                if (!is_numeric($nomorPool)) {
                    $messages[] = "❌ Baris $excelRowNum: Nomor pool harus berupa angka, ditemukan: '$nomorPool'.";
                    $status = false;
                    continue;
                }
                
                // Lookup sub_kategori_seni
                $subKategori = $this->subKategoriSeniModel
                    ->select('sub_kategori_seni.*')
                    ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = sub_kategori_seni.id_kategori_lomba')
                    ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia')
                    ->where('kategori_usia.nama_kategori_usia', $namaKategoriUsia)
                    ->where('kategori_usia.jenis_kelamin', $jenisKelamin)
                    ->where('sub_kategori_seni.jenis_seni', $jenisSeni)
                    ->where('sub_kategori_seni.nama_seni', $namaSeni)
                    ->first();
                
                if (!$subKategori) {
                    $messages[] = "❌ Baris $excelRowNum: Sub kategori seni tidak ditemukan ($namaKategoriUsia / $jenisKelamin / $jenisSeni / $namaSeni).";
                    $status = false;
                    continue;
                }

                $subKategoriId = is_array($subKategori)
                    ? ($subKategori['id_sub_kategori_seni'] ?? null)
                    : ($subKategori->id_sub_kategori_seni ?? null);
                $sistemPenampilan = is_array($subKategori)
                    ? ($subKategori['sistem_penampilan'] ?? null)
                    : ($subKategori->sistem_penampilan ?? null);
                
                // Check sistem_penampilan must be 'battle'
                if ($sistemPenampilan !== 'battle') {
                    $messages[] = "❌ Baris $excelRowNum: Sistem penampilan harus 'battle', ditemukan '{$sistemPenampilan}'.";
                    $status = false;
                    continue;
                }

                if ($subKategoriId === null) {
                    $messages[] = "❌ Baris $excelRowNum: ID sub kategori seni tidak valid.";
                    $status = false;
                    continue;
                }
                
                // Standardize babak
                $babakStd = $this->standardizeBabak($babak);
                if ($babakStd === false) {
                    $messages[] = "❌ Baris $excelRowNum: Babak '$babak' tidak valid.";
                    $status = false;
                    continue;
                }
                
                // Initialize battle entry
                $battleSeniAkanDiinput = [
                    'id_sub_kategori_seni' => $subKategoriId,
                    'id_kompetisi_seni' => null,
                    'nomor_battle' => null,
                    'nomor_battle_selanjutnya' => null,
                    'nomor_partai' => $nomorPartai,
                    'babak' => $babakStd,
                    'prioritas_babak' => $this->getPrioritasBabak($babakStd),
                ];
                
                // 1. Check blue corner
                $namaKontingenBiru = trim($rawData[$rowIndex + 1][6] ?? '');
                
                if ($this->isWinnerReference($namaAtletBiru)) {
                    // Blue corner comes from winner of previous match
                    $battleSeniAkanDiinput['nama_anggota_kelompok_peserta_seni_biru'] = null;
                    $battleSeniAkanDiinput['nama_kontingen_anggota_kelompok_peserta_seni_biru'] = null;
                    $battleSeniAkanDiinput['nomor_partai_calon_kelompok_peserta_seni_biru'] = $this->extractMatchNumber($namaAtletBiru);
                } else {
                    // Blue corner has actual athlete
                    if (empty($namaAtletBiru)) {
                        $messages[] = "❌ Baris $excelRowNum: Nama atlet sudut biru harus diisi.";
                        $status = false;
                        continue;
                    }
                    if (empty($namaKontingenBiru)) {
                        $messages[] = "❌ Baris " . ($excelRowNum + 1) . ": Nama kontingen sudut biru harus diisi.";
                        $status = false;
                        continue;
                    }
                    
                    $namaAtletBiru = str_replace(',', ', ', $namaAtletBiru); // normalize comma spacing
                    
                    $anggotaKelompokPesertaSeniBiru[$loopIndex] = [
                        'nama_pendaftar' => $namaAtletBiru,
                        'nama_kategori_usia' => $namaKategoriUsia,
                        'jenis_kelamin' => $jenisKelamin,
                        'jenis_seni' => $jenisSeni,
                        'nama_seni' => $namaSeni,
                        'nomor_pool' => $nomorPool,
                        'nama_kontingen' => $namaKontingenBiru,
                    ];
                    
                    $battleSeniAkanDiinput['nama_anggota_kelompok_peserta_seni_biru'] = $namaAtletBiru;
                    $battleSeniAkanDiinput['nama_kontingen_anggota_kelompok_peserta_seni_biru'] = $namaKontingenBiru;
                    
                    $kelompokPesertaSeni[] = $anggotaKelompokPesertaSeniBiru[$loopIndex];
                    $kontingen[] = $namaKontingenBiru;
                }
                
                // 2. Check red corner
                $namaKontingenMerah = trim($rawData[$rowIndex + 1][8] ?? '');
                
                if ($this->isWinnerReference($namaAtletMerah)) {
                    // Red corner comes from winner of previous match
                    $battleSeniAkanDiinput['nama_anggota_kelompok_peserta_seni_merah'] = null;
                    $battleSeniAkanDiinput['nama_kontingen_anggota_kelompok_peserta_seni_merah'] = null;
                    $battleSeniAkanDiinput['nomor_partai_calon_kelompok_peserta_seni_merah'] = $this->extractMatchNumber($namaAtletMerah);
                } else {
                    // Red corner has actual athlete
                    if (empty($namaAtletMerah)) {
                        $messages[] = "❌ Baris $excelRowNum: Nama atlet sudut merah harus diisi.";
                        $status = false;
                        continue;
                    }
                    if (empty($namaKontingenMerah)) {
                        $messages[] = "❌ Baris " . ($excelRowNum + 1) . ": Nama kontingen sudut merah harus diisi.";
                        $status = false;
                        continue;
                    }
                    
                    $namaAtletMerah = str_replace(',', ', ', $namaAtletMerah); // normalize comma spacing
                    
                    $anggotaKelompokPesertaSeniMerah[$loopIndex] = [
                        'nama_pendaftar' => $namaAtletMerah,
                        'nama_kategori_usia' => $namaKategoriUsia,
                        'jenis_kelamin' => $jenisKelamin,
                        'jenis_seni' => $jenisSeni,
                        'nama_seni' => $namaSeni,
                        'nomor_pool' => $nomorPool,
                        'nama_kontingen' => $namaKontingenMerah,
                    ];
                    
                    $battleSeniAkanDiinput['nama_anggota_kelompok_peserta_seni_merah'] = $namaAtletMerah;
                    $battleSeniAkanDiinput['nama_kontingen_anggota_kelompok_peserta_seni_merah'] = $namaKontingenMerah;
                    
                    $kelompokPesertaSeni[] = $anggotaKelompokPesertaSeniMerah[$loopIndex];
                    $kontingen[] = $namaKontingenMerah;
                }
                
                // Store battle data grouped by category
                $dataBattleSeni[$namaKategoriUsia][$jenisKelamin][$jenisSeni][$namaSeni][$nomorPool][] = $battleSeniAkanDiinput;
                
                $loopIndex++;
            }
        }
        
        if (!$status) {
            return [
                'status' => false,
                'messages' => $messages
            ];
        }
        
        // Process battle grouping and bracket numbering
        $dataBattleSeni = $this->kelompokkanBattleSeni($dataBattleSeni);
        $dataKompetisiSeni = $this->extractKompetisiSeni($dataBattleSeni);
        $pesertaSeni = $this->pisahkanAnggotaKelompokPesertaSeni($kelompokPesertaSeni);
        
        return [
            'status' => true,
            'kontingen' => array_unique($kontingen),
            'anggota_kelompok_peserta_seni_biru' => $anggotaKelompokPesertaSeniBiru,
            'anggota_kelompok_peserta_seni_merah' => $anggotaKelompokPesertaSeniMerah,
            'data_kompetisi_seni' => $dataKompetisiSeni,
            'peserta_seni' => $pesertaSeni,
            'data_battle_seni' => $dataBattleSeni,
            'messages' => $messages
        ];
    }

    /**
     * Extract unique kompetisi_seni entries from battle data
     */
    private function extractKompetisiSeni($dataBattleSeni)
    {
        $kompetisi = [];
        
        foreach ($dataBattleSeni as $usia => $byUsia) {
            foreach ($byUsia as $gender => $byGender) {
                foreach ($byGender as $jenisSeni => $byJenis) {
                    foreach ($byJenis as $namaSeni => $byNama) {
                        foreach ($byNama as $nomorPool => $battles) {
                            if (count($battles) > 0) {
                                $idSubKategori = $battles[0]['id_sub_kategori_seni'];
                                $idKompetisi = $this->getIdKompetisiSeni($idSubKategori, $nomorPool);
                                
                                $kompetisi[$usia][$gender][$jenisSeni][$namaSeni][$nomorPool] = [
                                    'id_sub_kategori_seni' => $idSubKategori,
                                    'id_kompetisi_seni' => $idKompetisi,
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $kompetisi;
    }

    /**
     * Get id_kompetisi_seni from database
     */
    private function getIdKompetisiSeni($idSubKategoriSeni, $nomorPool)
    {
        $kompetisi = $this->db->table('kompetisi_seni')
            ->where('id_sub_kategori_seni', $idSubKategoriSeni)
            ->where('nomor_pool', $nomorPool)
            ->get()
            ->getRow();
        
        return $kompetisi ? $kompetisi->id_kompetisi_seni : null;
    }

    /**
     * Process battle grouping: sort by babak and assign nomor_battle
     */
    private function kelompokkanBattleSeni($dataBattleSeni)
    {
        // Loop 1: Sort battles by babak priority (final first)
        foreach ($dataBattleSeni as $kUsia => $byUsia) {
            foreach ($byUsia as $kGender => $byGender) {
                foreach ($byGender as $kJenis => $byJenis) {
                    foreach ($byJenis as $kNama => $byNama) {
                        foreach ($byNama as $nomorPool => $battles) {
                            $battles = $this->sortBattleSeni($battles);
                            $dataBattleSeni[$kUsia][$kGender][$kJenis][$kNama][$nomorPool] = $battles;
                        }
                    }
                }
            }
        }
        
        // Loop 2: Assign nomor_battle and nomor_battle_selanjutnya
        foreach ($dataBattleSeni as $kUsia => $byUsia) {
            foreach ($byUsia as $kGender => $byGender) {
                foreach ($byGender as $kJenis => $byJenis) {
                    foreach ($byJenis as $kNama => $byNama) {
                        foreach ($byNama as $nomorPool => $battles) {
                            $battles = $this->isiNomorBattle($battles);
                            $dataBattleSeni[$kUsia][$kGender][$kJenis][$kNama][$nomorPool] = $battles;
                        }
                    }
                }
            }
        }
        
        return $dataBattleSeni;
    }

    /**
     * Sort battles by prioritas_babak (descending - final first)
     */
    private function sortBattleSeni($battles)
    {
        usort($battles, function ($a, $b) {
            if ($a['prioritas_babak'] > $b['prioritas_babak']) {
                return -1;
            } elseif ($a['prioritas_babak'] < $b['prioritas_babak']) {
                return 1;
            }
            return 0;
        });
        
        return $battles;
    }

    /**
     * Assign nomor_battle based on bracket logic (odd for blue, even for red)
     */
    private function isiNomorBattle($battles)
    {
        foreach ($battles as $key => $battle) {
            if ($battle['nama_anggota_kelompok_peserta_seni_biru'] === null) {
                // Blue corner from previous match - nomor_battle must be ODD
                $battles = $this->updateBattleSeniSebelumnya(
                    $battle['nomor_partai'],
                    $battle['nomor_partai_calon_kelompok_peserta_seni_biru'],
                    $battles,
                    'biru'
                );
            }
            if ($battle['nama_anggota_kelompok_peserta_seni_merah'] === null) {
                // Red corner from previous match - nomor_battle must be EVEN
                $battles = $this->updateBattleSeniSebelumnya(
                    $battle['nomor_partai'],
                    $battle['nomor_partai_calon_kelompok_peserta_seni_merah'],
                    $battles,
                    'merah'
                );
            }
        }
        
        return $battles;
    }

    /**
     * Update nomor_battle for previous match based on corner (odd/even logic)
     */
    private function updateBattleSeniSebelumnya($nomorPartaiSaatIni, $nomorPartaiSebelumnya, $battles, $sudut)
    {
        $keySebelumnya = null;
        $keySaatIni = null;
        
        // Find array keys
        foreach ($battles as $key => $battle) {
            if ($battle['nomor_partai'] == $nomorPartaiSebelumnya) {
                $keySebelumnya = $key;
            } elseif ($battle['nomor_partai'] == $nomorPartaiSaatIni) {
                $keySaatIni = $key;
            }
        }
        
        // Assign nomor_battle to current match
        $battles[$keySaatIni]['nomor_battle'] = $nomorPartaiSaatIni;
        
        // Assign nomor_battle to previous match based on corner
        if ($sudut === 'biru') {
            // Blue corner: nomor_battle must be ODD
            $nomorBattle = (floor($nomorPartaiSebelumnya / 2) * 2) + 1;
        } else {
            // Red corner: nomor_battle must be EVEN
            $nomorBattle = (floor($nomorPartaiSebelumnya / 2) * 2);
        }
        
        $battles[$keySebelumnya]['nomor_battle'] = $nomorBattle;
        $battles[$keySebelumnya]['nomor_battle_selanjutnya'] = $nomorPartaiSaatIni;
        
        return $battles;
    }

    /**
     * Separate comma-separated athlete names into individual entries
     */
    private function pisahkanAnggotaKelompokPesertaSeni($kelompokPesertaSeni)
    {
        $result = [];
        $keyKelompok = 0;
        
        foreach ($kelompokPesertaSeni as $peserta) {
            $namaPeserta = explode(',', $peserta['nama_pendaftar']);
            
            if (count($namaPeserta) > 1) {
                // Multiple athletes (team/doubles)
                foreach ($namaPeserta as $nama) {
                    $pesertaBaru = $peserta;
                    $pesertaBaru['nama_pendaftar'] = trim($nama);
                    $result[$keyKelompok][] = $pesertaBaru;
                }
            } else {
                // Single athlete
                $result[$keyKelompok][] = $peserta;
            }
            
            $keyKelompok++;
        }
        
        return $result;
    }

    /**
     * Check if name is Winner/PP reference
     */
    private function isWinnerReference($nama)
    {
        if (empty($nama)) {
            return true;
        }

        $lower = strtolower(trim((string) $nama));

        // Toleran terhadap variasi penulisan di file nyata:
        // winner, wiiner, pemenang partai, pp 123
        if (stripos($lower, 'pemenang partai') !== false) {
            return true;
        }

        if (preg_match('/\bpp\s*\d+/i', $lower)) {
            return true;
        }

        if (preg_match('/\bw[iy]*n+n*e*r\b/i', $lower)) {
            return true;
        }

        return false;
    }

    /**
     * Extract match number from winner reference string
     */
    private function extractMatchNumber($text)
    {
        return preg_replace('/\D+/', '', (string) $text);
    }

    /**
     * Standardize jenis_kelamin
     */
    private function standardizeJenisKelamin($value)
    {
        $lower = strtolower(trim($value));
        
        if (in_array($lower, ['putra', 'male', 'pria', 'laki'])) {
            return 'putra';
        }
        if (in_array($lower, ['putri', 'female', 'wanita', 'perempuan'])) {
            return 'putri';
        }
        
        return false;
    }

    /**
     * Standardize babak
     */
    private function standardizeBabak($babak)
    {
        $lower = strtolower(trim($babak));
        
        $map = [
            'final' => 'Final',
            'perebutan juara tiga' => 'Perebutan Juara Tiga',
            'perebutan juara 3' => 'Perebutan Juara Tiga',
            'semi final' => 'Semi Final',
            'semifinal' => 'Semi Final',
            '1/4 final' => '1/4 Final',
            'perempat final' => '1/4 Final',
            '1/8 final' => '1/8 Final',
            '1/16 final' => '1/16 Final',
            '1/32 final' => '1/32 Final',
        ];
        
        return $map[$lower] ?? false;
    }

    /**
     * Get prioritas babak (higher = later round)
     */
    private function getPrioritasBabak($babak)
    {
        $map = [
            'Final' => 10,
            'Perebutan Juara Tiga' => 9,
            'Semi Final' => 8,
            '1/4 Final' => 7,
            '1/8 Final' => 6,
            '1/16 Final' => 5,
            '1/32 Final' => 4,
        ];
        
        return $map[$babak] ?? 0;
    }

    /**
     * Generate preview token dan simpan ke cache
     */
    public function generatePreviewToken($validatedData, $idJadwalSeni)
    {
        $token = bin2hex(random_bytes(16));
        $cacheDir = WRITEPATH . 'cache/import_jadwal_seni_battle_preview/';
        
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . $token . '.json';
        $data = [
            'id_jadwal_seni' => $idJadwalSeni,
            'validated_data' => $validatedData,
            'created_at' => time(),
            'expires_at' => time() + 3600, // 1 hour TTL
        ];
        
        file_put_contents($cacheFile, json_encode($data));
        
        return $token;
    }

    /**
     * Retrieve preview data dari cache
     */
    public function getPreviewData($token)
    {
        $cacheFile = WRITEPATH . 'cache/import_jadwal_seni_battle_preview/' . $token . '.json';
        
        if (!file_exists($cacheFile)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        
        // Check TTL
        if ($data['expires_at'] < time()) {
            unlink($cacheFile);
            return null;
        }
        
        return $data;
    }
}
