<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportJadwalSeniPoolExcelService
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
     * Validasi format Excel dan ekstrak data
     * Return: ['status' => bool, 'data' => [...], 'message' => [...]]
     */
    public function validateAndExtract($rawData)
    {
        $status = true;
        $messages = [];
        
        $kontingen = [];
        $anggotaKelompokPesertaSeni = [];
        $dataKompetisiSeni = [];
        $dataPenampilan = [];
        
        $loopIndex = 0;

        foreach ($rawData as $rowIndex => $row) {
            // Even rows (0, 2, 4...) contain match data
            if ($rowIndex % 2 == 0) {
                $nomorPartai = trim($row[0] ?? '');
                $namaKategoriUsia = trim($row[1] ?? '');
                $jenisKelamin = strtolower(trim($row[2] ?? ''));
                $jenisSeni = strtolower(trim($row[3] ?? ''));
                $namaSeni = trim($row[4] ?? '');
                $nomorPool = trim($row[5] ?? '');
                $namaAtlet = trim($row[6] ?? '');
                $babak = strtolower(trim($row[7] ?? ''));
                
                $excelRowNum = $rowIndex + 2; // +1 for header, +1 for 1-indexed
                
                // Standardize jenis_kelamin
                $jenisKelamin = $this->standardizeJenisKelamin($jenisKelamin);
                if ($jenisKelamin === false) {
                    $messages[] = "❌ Baris $excelRowNum: Jenis Kelamin '{$row[2]}' tidak valid. Gunakan: PUTRA atau PUTRI.";
                    $status = false;
                    continue;
                }
                
                // Validate babak
                if (!in_array($babak, ['penyisihan', 'elimination', 'final'])) {
                    $messages[] = "❌ Baris $excelRowNum: Babak '$babak' tidak valid. Gunakan: penyisihan, elimination, atau final.";
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
                // Check sistem_penampilan must be 'pool'
                if ($sistemPenampilan !== 'pool') {
                    $messages[] = "❌ Baris $excelRowNum: Sistem penampilan harus 'pool', ditemukan '{$sistemPenampilan}'.";
                    $status = false;
                    continue;
                }

                if ($subKategoriId === null) {
                    $messages[] = "❌ Baris $excelRowNum: ID sub kategori seni tidak valid.";
                    $status = false;
                    continue;
                }

                // Get kontingen from next row (odd row)
                $namaKontingen = trim($rawData[$rowIndex + 1][6] ?? '');
                if (empty($namaKontingen)) {
                    $messages[] = "❌ Baris " . ($excelRowNum + 1) . ": Nama kontingen harus diisi.";
                    $status = false;
                    continue;
                }
                
                // Check if athlete name is Winner/PP reference
                if ($this->isWinnerReference($namaAtlet)) {
                    $messages[] = "❌ Baris $excelRowNum: Sistem pool tidak mendukung referensi pemenang partai. Gunakan nama atlet nyata.";
                    $status = false;
                    continue;
                }
                
                if (empty($namaAtlet)) {
                    $messages[] = "❌ Baris $excelRowNum: Nama atlet harus diisi.";
                    $status = false;
                    continue;
                }
                
                // Extract data
                $anggotaKelompokPesertaSeni[$loopIndex] = [
                    'nama_pendaftar' => $namaAtlet,
                    'nama_kategori_usia' => $namaKategoriUsia,
                    'jenis_kelamin' => $jenisKelamin,
                    'jenis_seni' => $jenisSeni,
                    'nama_seni' => $namaSeni,
                    'nomor_pool' => $nomorPool,
                    'nama_kontingen' => $namaKontingen,
                ];
                
                $dataPenampilan[$namaKategoriUsia][$jenisKelamin][$jenisSeni][$namaSeni][$nomorPool][] = [
                    'id_sub_kategori_seni' => $subKategoriId,
                    'id_kompetisi_seni' => null,
                    'nomor_partai' => $nomorPartai,
                    'nama_atlet' => $namaAtlet,
                    'nama_kontingen' => $namaKontingen,
                    'babak' => $babak,
                    'prioritas_babak' => $this->getPrioritasBabak($babak),
                ];
                
                $kontingen[] = $namaKontingen;
                $loopIndex++;
            }
        }
        
        if (!$status) {
            return [
                'status' => false,
                'messages' => $messages
            ];
        }
        
        return [
            'status' => true,
            'kontingen' => array_unique($kontingen),
            'anggota_kelompok_peserta_seni' => $anggotaKelompokPesertaSeni,
            'data_kompetisi_seni' => $this->extractKompetisiSeni($dataPenampilan),
            'data_penampilan' => $dataPenampilan,
            'messages' => $messages
        ];
    }

    /**
     * Extract unique kompetisi_seni entries
     */
    private function extractKompetisiSeni($dataPenampilan)
    {
        $kompetisi = [];
        
        foreach ($dataPenampilan as $usia => $byUsia) {
            foreach ($byUsia as $gender => $byGender) {
                foreach ($byGender as $jenisSeni => $byJenis) {
                    foreach ($byJenis as $namaSeni => $byNama) {
                        foreach ($byNama as $nomorPool => $entries) {
                            $kompetisi[] = [
                                'nama_kategori_usia' => $usia,
                                'jenis_kelamin' => $gender,
                                'jenis_seni' => $jenisSeni,
                                'nama_seni' => $namaSeni,
                                'nomor_pool' => $nomorPool,
                            ];
                        }
                    }
                }
            }
        }
        
        return $kompetisi;
    }

    /**
     * Check if name is Winner/PP reference
     */
    private function isWinnerReference($nama)
    {
        if (empty($nama)) {
            return true;
        }
        
        $lower = strtolower($nama);
        return stripos($lower, 'pp') !== false ||
               stripos($lower, 'pemenang partai') !== false ||
               stripos($lower, 'winner') !== false;
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
     * Get prioritas babak
     */
    private function getPrioritasBabak($babak)
    {
        $map = [
            'penyisihan' => 1,
            'elimination' => 1,
            'final' => 2,
        ];
        
        return $map[strtolower($babak)] ?? 0;
    }

    /**
     * Generate preview token dan simpan ke cache
     */
    public function generatePreviewToken($validatedData, $idJadwalSeni)
    {
        $token = bin2hex(random_bytes(16));
        $cacheDir = WRITEPATH . 'cache/import_jadwal_seni_pool_preview/';
        
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
        $cacheFile = WRITEPATH . 'cache/import_jadwal_seni_pool_preview/' . $token . '.json';
        
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
