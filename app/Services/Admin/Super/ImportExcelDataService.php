<?php

namespace App\Services\Admin\Super;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportExcelDataService
{
    private const PREVIEW_TTL_SECONDS = 3600;

    private BaseConnection $db;
    private string $previewDir;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->previewDir = WRITEPATH . 'cache/import_excel_preview/';
        if (! is_dir($this->previewDir)) {
            mkdir($this->previewDir, 0775, true);
        }
    }

    public function buildPreviewFromUpload(UploadedFile $file, string $createdBy): array
    {
        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            throw new \RuntimeException('Tipe file tidak didukung. Gunakan .xlsx, .xls, atau .csv.');
        }
        if ($file->getSize() > 4 * 1024 * 1024) {
            throw new \RuntimeException('Ukuran file melebihi 4 MB.');
        }

        $uploadDir = WRITEPATH . 'uploads';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $tmpName = $file->getRandomName();
        if (! $file->move($uploadDir, $tmpName)) {
            throw new \RuntimeException('Gagal menyimpan file upload sementara untuk diproses.');
        }

        $path = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $tmpName;

        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($path);
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, false, false);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }

        $token = bin2hex(random_bytes(16));
        $result = $this->validateRows($rows);
        $payload = [
            'token' => $token,
            'created_at' => date('c'),
            'created_by' => $createdBy,
            'original_filename' => $file->getClientName(),
            'total_rows_in_excel' => max(0, count($rows) - 1),
        ];

        if (! $result['status']) {
            $payload += [
                'is_valid' => false,
                'errors' => $this->structureErrors($result['errors']),
                'error_count' => count($result['errors']),
            ];
        } else {
            $payload += [
                'is_valid' => true,
                'errors' => [],
                'error_count' => 0,
                'data_dari_excel' => $result['data_dari_excel'],
                'tanding' => $result['tanding'],
                'tunggal' => $result['tunggal'],
                'ganda' => $result['ganda'],
                'beregu' => $result['beregu'],
                'stats' => $this->computeStats($result),
            ];
        }

        $this->savePreview($token, $payload);
        return $payload;
    }

    public function validateRows(array $rows): array
    {
        foreach ($rows as $key => $row) {
            if (trim((string) ($row[0] ?? '')) === '') {
                unset($rows[$key]);
                continue;
            }
            for ($i = 0; $i <= 15; $i++) {
                $rows[$key][$i] = preg_replace('/[^\P{C}\s]/u', '', (string) ($row[$i] ?? ''));
            }
        }
        $rows = array_values($rows);
        array_shift($rows);

        $kompetisiTanding = $this->getKompetisiTandingAvailable();
        $kompetisiSeni = $this->getKompetisiSeniAvailable();
        $tanding = $tunggal = $ganda = $beregu = $errors = [];

        foreach ($rows as $key => $row) {
            $excelLine = $key + 2;
            $name = strtoupper(trim(str_replace('’', '', (string) ($row[0] ?? ''))));
            $kontingen = strtoupper(trim((string) ($row[1] ?? '')));
            $gender = $this->normalizeGender((string) ($row[8] ?? ''));
            $jenisKategori = trim((string) ($row[10] ?? ''));

            if ($name === '') {
                $errors[] = 'NAMA TIDAK VALID Baris ' . $excelLine;
            } else {
                $rows[$key][0] = $name;
            }
            if ($kontingen === '') {
                $errors[] = 'Kontingen tidak valid (' . $name . ') Baris ' . $excelLine;
            } else {
                $rows[$key][1] = $kontingen;
            }
            if ($gender === null) {
                $errors[] = 'Jenis Kelamin tidak valid ' . ($row[8] ?? '') . ' (' . $name . ') Baris ' . $excelLine;
            } else {
                $rows[$key][8] = $gender;
            }
            $rows[$key][6] = (int) ($row[6] ?? 0);

            if (strcasecmp($jenisKategori, 'Tanding') === 0) {
                $idKelas = $this->matchKelasTanding((string) ($row[9] ?? ''), (string) $gender, (string) ($row[11] ?? ''), $kompetisiTanding);
                if ($idKelas === null) {
                    $errors[] = 'Kategori (Tanding) ' . ($row[8] ?? '') . ' ' . ($row[9] ?? '') . ' Tidak Valid ' . $name . ' Baris ' . $excelLine;
                } else {
                    $rows[$key][9] = $idKelas;
                    $tanding[] = $rows[$key];
                }
                continue;
            }

            $idSub = $this->matchSubKategoriSeni((string) ($row[9] ?? ''), (string) $gender, $jenisKategori, (string) ($row[11] ?? ''), $kompetisiSeni);
            if ($idSub === null) {
                $errors[] = 'Kategori SENI ' . $jenisKategori . ' ' . ($row[11] ?? '') . ' Tidak Valid baris no ' . $excelLine;
                continue;
            }

            $rows[$key][9] = $idSub;
            match (strtolower($jenisKategori)) {
                'tunggal', 'perorangan', 'solo kreatif' => $tunggal[] = $rows[$key],
                'ganda', 'berpasangan' => $ganda[] = $rows[$key],
                'beregu', 'berkelompok' => $beregu[] = $rows[$key],
                default => $errors[] = 'Kategori ' . $jenisKategori . ' Tidak Valid baris no ' . $excelLine,
            };
        }

        $bereguGrouped = $this->groupSeni($beregu);
        if (! $bereguGrouped['status']) {
            $errors[] = $bereguGrouped['message'];
        }
        $gandaGrouped = $this->groupSeni($ganda);
        if (! $gandaGrouped['status']) {
            $errors[] = $gandaGrouped['message'];
        }

        if ($errors !== []) {
            return ['status' => false, 'errors' => $errors];
        }

        return [
            'status' => true,
            'data_dari_excel' => $rows,
            'tanding' => $tanding,
            'tunggal' => $tunggal,
            'ganda' => $gandaGrouped,
            'beregu' => $bereguGrouped,
        ];
    }

    public function commitPreview(array $payload): array
    {
        $this->db->transStart();
        $stats = $payload['stats'] ?? [];
        try {
            $data = $payload['data_dari_excel'] ?? [];
            $this->insertKontingen($data);
            $this->insertPendaftar($data);
            $this->insertOfficial($data);
            $this->insertPesertaTanding($payload['tanding'] ?? []);
            $this->insertSeniTunggal($payload['tunggal'] ?? []);
            $this->insertSeniGrouped($payload['ganda']['hasil'] ?? [], 2);
            $this->insertSeniGrouped($payload['beregu']['hasil'] ?? [], null);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
        $this->db->transComplete();
        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Gagal menyimpan data import Excel.');
        }
        return $stats;
    }

    private function insertKontingen(array $rows): void
    {
        $existing = array_map('strtoupper', array_column($this->db->table('kontingen')->select('nama_kontingen')->get()->getResultArray(), 'nama_kontingen'));
        $batch = [];
        foreach (array_unique(array_column($rows, 1)) as $name) {
            if ($name === '' || in_array(strtoupper($name), $existing, true)) {
                continue;
            }
            $batch[] = [
                'nama_kontingen' => $name,
                'email_kontingen' => 'kontingen@digitalpencaksilat.local',
                'password' => password_hash($name, PASSWORD_BCRYPT, ['cost' => 10]),
                'perguruan' => 'ipsi',
                'jenis_kontingen' => 'dalam_negeri',
                'provinsi' => ' ',
                'kabupaten_kota' => ' ',
                'nama_penanggungjawab' => ' ',
                'nomor_telepon_penanggungjawab' => ' ',
                'jenis_pendaftaran' => 'excel',
            ];
        }
        if ($batch !== []) {
            $this->db->table('kontingen')->insertBatch($batch);
        }
    }

    private function insertPendaftar(array $rows): void
    {
        $existing = $this->db->table('pendaftar p')->select('UPPER(p.nama_pendaftar) AS nama_pendaftar, UPPER(k.nama_kontingen) AS nama_kontingen')->join('kontingen k', 'k.id_kontingen = p.id_kontingen')->get()->getResultArray();
        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[$row['nama_kontingen'] . '|' . $row['nama_pendaftar']] = true;
        }
        $kontingenMap = $this->kontingenMap();
        $batch = [];
        foreach ($rows as $row) {
            $key = strtoupper($row[1]) . '|' . strtoupper($row[0]);
            if (isset($existingMap[$key])) {
                continue;
            }
            $batch[] = [
                'id_kontingen' => $kontingenMap[strtoupper($row[1])] ?? null,
                'nama_pendaftar' => $row[0],
                'jenis_kelamin' => $row[8],
                'tinggi_badan' => $row[5] ?? null,
                'berat_badan' => (int) ($row[6] ?? 0),
                'tempat_lahir' => ' (Tidak Terdata)',
                'tanggal_lahir' => $this->convertExcelDate($row[3] ?? null),
                'nama_sekolah' => $row[2] ?? null,
                'alamat' => $row[7] ?? null,
                'nomor_induk_kependudukan' => $row[14] ?? null,
                'nomor_kartu_keluarga' => $row[15] ?? null,
                'foto' => null,
            ];
            $existingMap[$key] = true;
        }
        if ($batch !== []) {
            $this->db->table('pendaftar')->insertBatch($batch);
        }
    }

    private function insertOfficial(array $rows): void
    {
        $kontingenMap = $this->kontingenMap();
        $existing = array_map('strtoupper', array_column($this->db->table('official')->select('nama_official')->get()->getResultArray(), 'nama_official'));
        $seen = $batch = [];
        foreach ($rows as $row) {
            $officialName = trim((string) ($row[11] ?? ''));
            $key = strtoupper($officialName);
            if ($officialName === '' || isset($seen[$key]) || in_array($key, $existing, true)) {
                continue;
            }
            $batch[] = ['id_kontingen' => $kontingenMap[strtoupper($row[1])] ?? null, 'nama_official' => $officialName, 'nomor_telepon' => '0'];
            $seen[$key] = true;
        }
        if ($batch !== []) {
            $this->db->table('official')->insertBatch($batch);
        }
    }

    private function insertPesertaTanding(array $rows): void
    {
        $pendaftarMap = $this->pendaftarMap();
        foreach ($rows as $row) {
            $idKompetisi = $this->lastKompetisiTanding((int) $row[9]);
            $idPendaftar = $pendaftarMap[strtoupper($row[1])][strtoupper($row[0])] ?? null;
            if (! $idKompetisi || ! $idPendaftar || $this->pesertaTandingExists($idPendaftar)) {
                continue;
            }
            $this->db->table('peserta_tanding')->insert(['id_pendaftar' => $idPendaftar, 'id_kompetisi_tanding' => $idKompetisi]);
            (new \App\Models\KelasTandingModel())->otomatis_menambahkan_pool((int) $row[9]);
        }
    }

    private function insertSeniTunggal(array $rows): void
    {
        foreach ($rows as $row) {
            $idKelompok = $this->createKelompokSeni(strtoupper($row[1]), (int) $row[9]);
            $this->insertPesertaSeni($idKelompok, [[strtoupper($row[1]), strtoupper($row[0])]]);
            $this->autoAddSeniPool((int) $row[9]);
        }
    }

    private function insertSeniGrouped(array $grouped, ?int $fixedAdder): void
    {
        foreach ($grouped as $kontingen => $categories) {
            foreach ($categories as $idSub => $rows) {
                $adder = $fixedAdder ?? (int) ($this->db->table('sub_kategori_seni')->select('jumlah_peserta')->where('id_sub_kategori_seni', (int) $idSub)->get()->getRow('jumlah_peserta') ?: 1);
                for ($i = 0; $i < count($rows); $i += $adder) {
                    $slice = array_slice($rows, $i, $adder);
                    $idKelompok = $this->createKelompokSeni(strtoupper($kontingen), (int) $idSub);
                    $members = array_map(static fn ($row) => [strtoupper($kontingen), strtoupper($row[0])], $slice);
                    $this->insertPesertaSeni($idKelompok, $members);
                    $this->autoAddSeniPool((int) $idSub);
                }
            }
        }
    }

    private function createKelompokSeni(string $kontingen, int $idSubKategori): int
    {
        $kontingenMap = $this->kontingenMap();
        $idKompetisi = $this->lastKompetisiSeni($idSubKategori);
        if (! $idKompetisi) {
            (new \App\Models\SubKategoriSeniModel())->otomatis_menambahkan_pool($idSubKategori);
            $idKompetisi = $this->lastKompetisiSeni($idSubKategori);
        }
        $this->db->table('kelompok_peserta_seni')->insert(['id_kontingen' => $kontingenMap[$kontingen] ?? null, 'id_kompetisi_seni' => $idKompetisi]);
        $idKelompok = (int) $this->db->insertID();
        $this->db->table('penampilan_seni')->insert(['id_kelompok_peserta_seni' => $idKelompok]);
        return $idKelompok;
    }

    private function insertPesertaSeni(int $idKelompok, array $members): void
    {
        $pendaftarMap = $this->pendaftarMap();
        $batch = [];
        foreach ($members as [$kontingen, $nama]) {
            $idPendaftar = $pendaftarMap[$kontingen][$nama] ?? null;
            if ($idPendaftar) {
                $batch[] = ['id_pendaftar' => $idPendaftar, 'id_kelompok_peserta_seni' => $idKelompok];
            }
        }
        if ($batch !== []) {
            $this->db->table('peserta_seni')->insertBatch($batch);
        }
    }

    public function columnLabels(): array
    {
        return [0 => ['label' => 'Nama Atlet', 'required' => true], 1 => ['label' => 'Nama Kontingen', 'required' => true], 2 => ['label' => 'Nama Sekolah', 'required' => false], 3 => ['label' => 'Tanggal Lahir', 'required' => false], 4 => ['label' => 'Umur', 'required' => false], 5 => ['label' => 'Tinggi Badan', 'required' => false], 6 => ['label' => 'Berat Badan', 'required' => false], 7 => ['label' => 'Alamat', 'required' => false], 8 => ['label' => 'Jenis Kelamin', 'required' => true], 9 => ['label' => 'Kategori Usia', 'required' => true], 10 => ['label' => 'Jenis Kategori', 'required' => true], 11 => ['label' => 'Kelas / Nama Seni', 'required' => true], 14 => ['label' => 'NIK', 'required' => false], 15 => ['label' => 'No. Kartu Keluarga', 'required' => false]];
    }

    private function normalizeGender(string $value): ?string
    {
        // DB (legacy DPS) stores gender as 'putra'/'putri'.
        // Normalize Excel variants to that canonical form.
        $value = strtolower(trim($value));
        $value = str_replace(' ', '', $value);

        return match ($value) {
            'l', 'lk', 'laki', 'laki-laki', 'pria', 'male', 'men', 'man', 'boy', 'boys', 'putra' => 'putra',
            'p', 'pr', 'perempuan', 'wanita', 'female', 'women', 'woman', 'girl', 'girls', 'putri' => 'putri',
            default => null,
        };
    }

    private function getKompetisiTandingAvailable(): array
    {
        return $this->db->table('kompetisi_tanding ktg')->select('kt.id_kelas_tanding, kt.label, ku.nama_kategori_usia, ku.jenis_kelamin')->join('kelas_tanding kt', 'kt.id_kelas_tanding = ktg.id_kelas_tanding')->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')->select('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = ktg.id_kompetisi_tanding) AS jumlah_peserta_tanding')->where('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = ktg.id_kompetisi_tanding) < ktg.max_peserta', null, false)->get()->getResult();
    }

    private function getKompetisiSeniAvailable(): array
    {
        return $this->db->table('kompetisi_seni ks')->select('sks.id_sub_kategori_seni, sks.nama_seni, sks.jenis_seni, sks.jumlah_peserta, ku.nama_kategori_usia, ku.jenis_kelamin')->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kompetisi_seni = ks.id_kompetisi_seni) AS jumlah_kelompok_peserta_seni')->where('(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kompetisi_seni = ks.id_kompetisi_seni) < ks.max_peserta', null, false)->get()->getResult();
    }

    private function matchKelasTanding(string $usia, string $gender, string $kelas, array $available): ?int
    {
        foreach ($available as $row) {
            if (strcasecmp(trim($row->nama_kategori_usia), trim($usia)) === 0 && strcasecmp(trim($row->jenis_kelamin), trim($gender)) === 0 && strcasecmp(trim($row->label), trim($kelas)) === 0) {
                return (int) $row->id_kelas_tanding;
            }
        }
        return null;
    }

    private function matchSubKategoriSeni(string $usia, string $gender, string $jenis, string $nama, array $available): ?int
    {
        foreach ($available as $row) {
            if (strcasecmp(trim($row->nama_kategori_usia), trim($usia)) === 0 && strcasecmp(trim($row->jenis_kelamin), trim($gender)) === 0 && strcasecmp(trim($row->jenis_seni), trim($jenis)) === 0 && strcasecmp(trim($row->nama_seni), trim($nama)) === 0) {
                return (int) $row->id_sub_kategori_seni;
            }
        }
        return null;
    }

    private function groupSeni(array $rows): array
    {
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row[1]][$row[9]][] = $row;
        }
        foreach ($grouped as $kontingen => $categories) {
            foreach ($categories as $idSub => $items) {
                $jumlah = (int) ($this->db->table('sub_kategori_seni')->select('jumlah_peserta')->where('id_sub_kategori_seni', (int) $idSub)->get()->getRow('jumlah_peserta') ?: 1);
                if ($jumlah > 0 && count($items) % $jumlah !== 0) {
                    return ['status' => false, 'message' => 'Jumlah Pesilat Tidak Lengkap! ' . $items[0][0] . ' - (' . $kontingen . '), kategori ' . $items[0][10] . ' ' . $items[0][11] . ' harus berkelipatan ' . $jumlah . ' atlet. Hanya ditemukan ' . count($items) . '.'];
                }
            }
        }
        return ['status' => true, 'hasil' => $grouped];
    }

    private function structureErrors(array $messages): array
    {
        $errors = [];
        foreach ($messages as $msg) {
            $plain = trim(strip_tags((string) $msg));
            $lc = strtolower($plain);
            $category = 'Lainnya';
            if (str_contains($lc, 'pesilat tidak lengkap') || str_contains($lc, 'berkelipatan')) {
                $category = 'Kelengkapan Pasangan/Beregu';
            } elseif (str_contains($lc, 'kontingen') || str_contains($lc, 'nama tidak valid')) {
                $category = 'Identitas';
            } elseif (str_contains($lc, 'jenis kelamin')) {
                $category = 'Demografi';
            } elseif (str_contains($lc, 'tanding')) {
                $category = 'Kompetisi Tanding';
            } elseif (str_contains($lc, 'seni') || str_contains($lc, 'kategori')) {
                $category = 'Kompetisi Seni';
            }
            preg_match('/baris(?:\s+no)?\s*(\d+)/i', $plain, $match);
            $errors[] = ['category' => $category, 'line' => isset($match[1]) ? (int) $match[1] : null, 'message' => $plain];
        }
        usort($errors, static fn ($a, $b) => [$a['category'], $a['line'] ?? 0] <=> [$b['category'], $b['line'] ?? 0]);
        return $errors;
    }

    private function computeStats(array $data): array
    {
        return ['total_baris' => count($data['data_dari_excel'] ?? []), 'tanding' => count($data['tanding'] ?? []), 'tunggal' => count($data['tunggal'] ?? []), 'ganda' => array_sum(array_map(static fn ($cats) => array_sum(array_map('count', $cats)), $data['ganda']['hasil'] ?? [])), 'beregu' => array_sum(array_map(static fn ($cats) => array_sum(array_map('count', $cats)), $data['beregu']['hasil'] ?? [])), 'kontingen_unik' => count(array_unique(array_column($data['data_dari_excel'] ?? [], 1)))];
    }

    private function kontingenMap(): array
    {
        $map = [];
        foreach ($this->db->table('kontingen')->select('id_kontingen, nama_kontingen')->get()->getResult() as $row) {
            $map[strtoupper($row->nama_kontingen)] = (int) $row->id_kontingen;
        }
        return $map;
    }

    private function pendaftarMap(): array
    {
        $map = [];
        foreach ($this->db->table('pendaftar p')->select('p.id_pendaftar, p.nama_pendaftar, k.nama_kontingen')->join('kontingen k', 'k.id_kontingen = p.id_kontingen')->get()->getResult() as $row) {
            $map[strtoupper($row->nama_kontingen)][strtoupper($row->nama_pendaftar)] = (int) $row->id_pendaftar;
        }
        return $map;
    }

    private function lastKompetisiTanding(int $idKelas): ?int
    {
        $id = $this->db->table('kompetisi_tanding')->select('id_kompetisi_tanding')->where('id_kelas_tanding', $idKelas)->orderBy('nomor_pool', 'DESC')->get()->getRow('id_kompetisi_tanding');
        return $id ? (int) $id : null;
    }

    private function lastKompetisiSeni(int $idSub): ?int
    {
        $id = $this->db->table('kompetisi_seni')->select('id_kompetisi_seni')->where('id_sub_kategori_seni', $idSub)->orderBy('nomor_pool', 'DESC')->get()->getRow('id_kompetisi_seni');
        return $id ? (int) $id : null;
    }

    private function pesertaTandingExists(int $idPendaftar): bool
    {
        return $this->db->table('peserta_tanding')->where('id_pendaftar', $idPendaftar)->countAllResults() > 0;
    }

    private function autoAddSeniPool(int $idSubKategori): void
    {
        $max = (int) ($this->db->table('kompetisi_seni')->select('max_peserta')->where('id_sub_kategori_seni', $idSubKategori)->orderBy('nomor_pool', 'DESC')->get()->getRow('max_peserta') ?: 4);
        (new \App\Models\SubKategoriSeniModel())->otomatis_menambahkan_pool($idSubKategori, $max);
    }

    private function convertExcelDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }
        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y', 'm/d/Y'] as $format) {
            $date = \DateTime::createFromFormat($format, trim((string) $value));
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }
        $timestamp = strtotime((string) $value);
        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }

    private function previewPath(string $token): ?string
    {
        return preg_match('/^[a-f0-9]{32}$/', $token) ? $this->previewDir . $token . '.json' : null;
    }

    public function savePreview(string $token, array $payload): bool
    {
        $path = $this->previewPath($token);
        return $path !== null && file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE)) !== false;
    }

    public function loadPreview(string $token): ?array
    {
        $path = $this->previewPath($token);
        if ($path === null || ! is_file($path)) {
            return null;
        }
        $decoded = json_decode((string) file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    public function deletePreview(string $token): void
    {
        $path = $this->previewPath($token);
        if ($path !== null && is_file($path)) {
            unlink($path);
        }
    }

    public function cleanupExpiredPreviews(): void
    {
        foreach (glob($this->previewDir . '*.json') ?: [] as $file) {
            if (time() - filemtime($file) > self::PREVIEW_TTL_SECONDS) {
                unlink($file);
            }
        }
    }
}
