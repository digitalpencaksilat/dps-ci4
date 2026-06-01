<?php

namespace App\Services;

/**
 * ImportJadwalSeniPoolCommitService
 *
 * Commits validated import data (from ImportJadwalSeniPoolExcelService) to the
 * database for the "pool" seni scheduling system.
 *
 * Mirrors CI3: Import_excel_jadwal_seni_pool_model::import_data()
 * and its private helpers:
 *   _create_kontingen, _create_pendaftar, _create_kelompok_peserta_seni,
 *   _create_penampilan_seni, _create_detail_jadwal_seni
 *
 * All writes are wrapped in a single database transaction.
 */
class ImportJadwalSeniPoolCommitService
{
    // -------------------------------------------------------------------------
    // Public entry point
    // -------------------------------------------------------------------------

    /**
     * Commit validated import data to the database.
     *
     * @param array $validatedData  Output of ImportJadwalSeniPoolExcelService::validateAndExtract()
     *                              Keys expected:
     *                                'kontingen'                    – string[]  unique kontingen names
     *                                'anggota_kelompok_peserta_seni'– flat array of peserta rows
     *                                'data_kompetisi_seni'          – flat array of kompetisi entries
     *                                'data_penampilan'              – nested array [usia][jk][jenis][nama][pool][]
     * @param int   $idJadwalSeni   Target jadwal_seni row
     *
     * @return array ['status' => bool, 'message' => string, 'jumlah_penampilan' => int]
     */
    public function commit(array $validatedData, int $idJadwalSeni): array
    {
        $db = db_connect();
        $db->transStart();

        try {
            // ── 1. Kontingen ──────────────────────────────────────────────
            $this->createKontingen($db, $validatedData['kontingen'] ?? []);

            // ── 2. Pendaftar ──────────────────────────────────────────────
            // Build kelompok_peserta_seni groups from flat anggota list first
            $dataKelompokPesertaSeni = $this->pisahkanAnggotaKelompokPesertaSeni(
                $validatedData['anggota_kelompok_peserta_seni'] ?? []
            );

            $this->createPendaftar($db, $dataKelompokPesertaSeni);

            // ── 3. Kompetisi seni (pool) ───────────────────────────────────
            $dataPenampilan = $validatedData['data_penampilan'] ?? [];
            $dataPenampilan = $this->createKompetisiSeni($db, $dataPenampilan);

            // ── 4. Kelompok peserta seni + peserta_seni ────────────────────
            $this->createKelompokPesertaSeni($db, $dataKelompokPesertaSeni, $dataPenampilan);

            // ── 5. Penampilan seni ─────────────────────────────────────────
            $dataPenampilan = $this->createPenampilanSeni($db, $dataPenampilan);

            // ── 6. Detail jadwal seni ──────────────────────────────────────
            $jumlahPenampilan = $this->createDetailJadwalSeni($db, $idJadwalSeni, $dataPenampilan);

            $db->transComplete();

            if (! $db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal (transStatus false).');
            }

            return [
                'status'             => true,
                'jumlah_penampilan'  => $jumlahPenampilan,
                'message'            => "Berhasil mengimport {$jumlahPenampilan} penampilan ke jadwal seni.",
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ImportJadwalSeniPoolCommitService] {msg}', ['msg' => $e->getMessage()]);
            return [
                'status'  => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ];
        }
    }

    // -------------------------------------------------------------------------
    // Step 1 – Kontingen
    // -------------------------------------------------------------------------

    /**
     * Insert kontingen rows that do not yet exist in the database.
     * Mirrors CI3: _create_kontingen()
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @param string[] $namaKontingenList  Unique kontingen names from Excel
     */
    private function createKontingen($db, array $namaKontingenList): void
    {
        if (empty($namaKontingenList)) {
            return;
        }

        // Fetch existing names (case-insensitive comparison)
        $existing = $db->table('kontingen')
            ->select('nama_kontingen')
            ->get()
            ->getResultArray();
        $existingNames = array_map('strtolower', array_column($existing, 'nama_kontingen'));

        $batch = [];
        foreach ($namaKontingenList as $namaKontingen) {
            $namaKontingen = trim($namaKontingen);
            if ($namaKontingen === '') {
                continue;
            }

            // Skip if already in DB (case-insensitive)
            if (in_array(strtolower($namaKontingen), $existingNames, true)) {
                continue;
            }

            $batch[] = [
                'nama_kontingen'                => $namaKontingen,
                'jenis_kontingen'               => 'dalam_negeri',
                'perguruan'                     => 'ipsi',
                'email_kontingen'               => '-',
                'nomor_telepon_kontingen'       => '-',
                'alamat_kontingen'              => '-',
                'username'                      => 'auto_' . strtolower(preg_replace('/[^a-z0-9]/i', '_', $namaKontingen)) . '_' . time(),
                'password'                      => password_hash($namaKontingen, PASSWORD_BCRYPT, ['cost' => 10]),
                'nama_penanggungjawab'          => '-',
                'jabatan_penanggungjawab'       => '-',
                'nomor_telepon_penanggungjawab' => '-',
                'negara'                        => 'Indonesia',
                'alamat_lengkap'                => '-',
                'keterangan'                    => 'Auto-created from Excel import',
                'jenis_pendaftaran'             => 'excel',
            ];

            // Add to existingNames immediately to prevent duplicates within the same batch
            $existingNames[] = strtolower($namaKontingen);
        }

        if (! empty($batch)) {
            $db->table('kontingen')->insertBatch($batch);
        }
    }

    // -------------------------------------------------------------------------
    // Step 2 – Pendaftar
    // -------------------------------------------------------------------------

    /**
     * Split comma-separated athlete names into individual group members.
     * Mirrors CI3: _pisahkan_anggota_kelompok_peserta_seni()
     *
     * Input: flat array of peserta rows (each row = one Excel line, may have
     *        comma-separated names for group events).
     * Output: array of groups, each group = array of individual peserta rows.
     *
     * @param array $anggotaList  Flat list from validateAndExtract()
     * @return array              Grouped: [ [peserta, ...], [peserta, ...], ... ]
     */
    private function pisahkanAnggotaKelompokPesertaSeni(array $anggotaList): array
    {
        $kelompok = [];

        foreach ($anggotaList as $peserta) {
            $namaList = explode(',', $peserta['nama_pendaftar']);

            if (count($namaList) > 1) {
                // Multiple athletes in one cell → one group, multiple members
                $group = [];
                foreach ($namaList as $nama) {
                    $member                  = $peserta;
                    $member['nama_pendaftar'] = trim($nama);
                    $group[]                 = $member;
                }
                $kelompok[] = $group;
            } else {
                // Single athlete → group of one
                $peserta['nama_pendaftar'] = trim($peserta['nama_pendaftar']);
                $kelompok[]               = [$peserta];
            }
        }

        return $kelompok;
    }

    /**
     * Insert pendaftar rows that do not yet exist in the database.
     * Mirrors CI3: _create_pendaftar()
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @param array $dataKelompokPesertaSeni  Output of pisahkanAnggotaKelompokPesertaSeni()
     */
    private function createPendaftar($db, array $dataKelompokPesertaSeni): void
    {
        // Build lookup: strtolower(nama_kontingen) => id_kontingen
        $kontingenRows = $db->table('kontingen')
            ->select('id_kontingen, nama_kontingen')
            ->get()
            ->getResultArray();

        $kontingenMap = [];
        foreach ($kontingenRows as $row) {
            $kontingenMap[strtolower($row['nama_kontingen'])] = (int) $row['id_kontingen'];
        }

        // Build lookup of existing pendaftar: strtolower("nama|kontingen") => true
        $existingRows = $db->table('pendaftar p')
            ->select('p.nama_pendaftar, k.nama_kontingen')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->get()
            ->getResultArray();

        $existingMap = [];
        foreach ($existingRows as $row) {
            $key              = strtolower(trim($row['nama_pendaftar']) . '|' . trim($row['nama_kontingen']));
            $existingMap[$key] = true;
        }

        // Deduplicate within Excel data (same name + same kontingen = same entity)
        // Mirrors CI3 filtered_pendaftar logic
        $filtered = [];
        foreach ($dataKelompokPesertaSeni as $kelompok) {
            foreach ($kelompok as $peserta) {
                $namaPendaftar  = trim($peserta['nama_pendaftar']);
                $namaKontingen  = trim($peserta['nama_kontingen']);
                $dedupeKey      = strtolower($namaPendaftar . '|' . $namaKontingen);

                if (! isset($filtered[$dedupeKey])) {
                    $filtered[$dedupeKey] = $peserta;
                }
            }
        }

        $batch = [];
        foreach ($filtered as $peserta) {
            $namaPendaftar = trim($peserta['nama_pendaftar']);
            $namaKontingen = trim($peserta['nama_kontingen']);
            $existKey      = strtolower($namaPendaftar . '|' . $namaKontingen);

            // Skip if already in DB
            if (isset($existingMap[$existKey])) {
                continue;
            }

            $idKontingen = $kontingenMap[strtolower($namaKontingen)] ?? null;
            if ($idKontingen === null) {
                log_message('warning', '[ImportJadwalSeniPoolCommitService] Kontingen tidak ditemukan untuk pendaftar: {nama} / {kontingen}', [
                    'nama'      => $namaPendaftar,
                    'kontingen' => $namaKontingen,
                ]);
                continue;
            }

            $batch[] = [
                'id_kontingen'   => $idKontingen,
                'nama_pendaftar' => $namaPendaftar,
                'jenis_kelamin'  => strtolower($peserta['jenis_kelamin'] ?? 'putra'),
                'tinggi_badan'   => 0,
                'berat_badan'    => 0,
                'tempat_lahir'   => '(tidak terdata)',
                'keterangan'     => 'Auto-created from Excel import',
            ];

            // Prevent duplicates within the same batch
            $existingMap[$existKey] = true;
        }

        if (! empty($batch)) {
            $db->table('pendaftar')->insertBatch($batch);
        }
    }

    // -------------------------------------------------------------------------
    // Step 3 – Kompetisi seni (pool)
    // -------------------------------------------------------------------------

    /**
     * Ensure kompetisi_seni rows exist for every pool in the penampilan data.
     * Injects 'id_kompetisi_seni' into each entry of $dataPenampilan.
     * Mirrors CI3: _create_kompetisi_seni()
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @param array $dataPenampilan  Nested [usia][jk][jenis][nama][pool][]
     * @return array                 Same structure with id_kompetisi_seni populated
     */
    private function createKompetisiSeni($db, array $dataPenampilan): array
    {
        foreach ($dataPenampilan as $kUsia => $byUsia) {
            foreach ($byUsia as $kJk => $byJk) {
                foreach ($byJk as $kJenis => $byJenis) {
                    foreach ($byJenis as $kNama => $byNama) {
                        foreach ($byNama as $nomorPool => $entries) {
                            if (empty($entries)) {
                                continue;
                            }

                            $idSubKategoriSeni = $entries[0]['id_sub_kategori_seni'];

                            // Check if kompetisi_seni already exists for this sub_kategori + pool
                            $existing = $db->table('kompetisi_seni')
                                ->where('id_sub_kategori_seni', $idSubKategoriSeni)
                                ->where('nomor_pool', $nomorPool)
                                ->get()
                                ->getRow();

                            if ($existing) {
                                $idKompetisiSeni = (int) $existing->id_kompetisi_seni;
                            } else {
                                // Get last pool for this sub_kategori to inherit max_peserta
                                $lastPool = $db->table('kompetisi_seni')
                                    ->where('id_sub_kategori_seni', $idSubKategoriSeni)
                                    ->orderBy('nomor_pool', 'DESC')
                                    ->get()
                                    ->getRow();

                                $maxPeserta = ($lastPool !== null) ? (int) $lastPool->max_peserta : 100;

                                $db->table('kompetisi_seni')->insert([
                                    'id_sub_kategori_seni' => $idSubKategoriSeni,
                                    'nomor_pool'           => $nomorPool,
                                    'max_peserta'          => $maxPeserta,
                                    'keterangan'           => 'Import Excel Jadwal Seni Pool',
                                ]);
                                $idKompetisiSeni = (int) $db->insertID();
                            }

                            // Inject id_kompetisi_seni into every entry in this pool
                            foreach ($entries as $idx => $entry) {
                                $dataPenampilan[$kUsia][$kJk][$kJenis][$kNama][$nomorPool][$idx]['id_kompetisi_seni'] = $idKompetisiSeni;
                            }
                        }
                    }
                }
            }
        }

        return $dataPenampilan;
    }

    // -------------------------------------------------------------------------
    // Step 4 – Kelompok peserta seni + peserta_seni
    // -------------------------------------------------------------------------

    /**
     * Create kelompok_peserta_seni and link individual peserta_seni members.
     * Mirrors CI3: _create_kelompok_peserta_seni()
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @param array $dataKelompokPesertaSeni  Output of pisahkanAnggotaKelompokPesertaSeni()
     * @param array $dataPenampilan           Nested array with id_kompetisi_seni populated
     */
    private function createKelompokPesertaSeni($db, array $dataKelompokPesertaSeni, array $dataPenampilan): void
    {
        foreach ($dataKelompokPesertaSeni as $kelompok) {
            if (empty($kelompok)) {
                continue;
            }

            $first         = $kelompok[0];
            $namaKontingen = trim($first['nama_kontingen']);
            $namaUsia      = $first['nama_kategori_usia'];
            $jenisKelamin  = $first['jenis_kelamin'];
            $jenisSeni     = $first['jenis_seni'];
            $namaSeni      = $first['nama_seni'];
            $nomorPool     = $first['nomor_pool'];

            // Resolve id_kompetisi_seni from the already-populated dataPenampilan
            $idKompetisiSeni = $dataPenampilan[$namaUsia][$jenisKelamin][$jenisSeni][$namaSeni][$nomorPool][0]['id_kompetisi_seni'] ?? null;

            if ($idKompetisiSeni === null) {
                log_message('warning', '[ImportJadwalSeniPoolCommitService] id_kompetisi_seni tidak ditemukan untuk kelompok: {k}', [
                    'k' => "{$namaUsia}|{$jenisKelamin}|{$jenisSeni}|{$namaSeni}|{$nomorPool}",
                ]);
                continue;
            }

            // Resolve id_kontingen
            $kontingenRow = $db->table('kontingen')
                ->where('LOWER(nama_kontingen)', strtolower($namaKontingen))
                ->get()
                ->getRow();

            if ($kontingenRow === null) {
                log_message('warning', '[ImportJadwalSeniPoolCommitService] Kontingen tidak ditemukan: {k}', ['k' => $namaKontingen]);
                continue;
            }

            $idKontingen = (int) $kontingenRow->id_kontingen;

            // Check if this kelompok_peserta_seni already exists
            // (same kontingen + same kompetisi_seni)
            $existingKelompok = $db->table('kelompok_peserta_seni')
                ->where('id_kontingen', $idKontingen)
                ->where('id_kompetisi_seni', $idKompetisiSeni)
                ->get()
                ->getRow();

            if ($existingKelompok) {
                // Already exists — skip to avoid duplicates
                continue;
            }

            // Insert kelompok_peserta_seni
            $db->table('kelompok_peserta_seni')->insert([
                'id_kontingen'    => $idKontingen,
                'id_kompetisi_seni' => $idKompetisiSeni,
            ]);
            $idKelompokPesertaSeni = (int) $db->insertID();

            if ($idKelompokPesertaSeni === 0) {
                throw new \RuntimeException("Gagal insert kelompok_peserta_seni untuk kontingen: {$namaKontingen}");
            }

            // Insert peserta_seni for each member
            foreach ($kelompok as $peserta) {
                $namaPendaftar = trim($peserta['nama_pendaftar']);

                $pendaftarRow = $db->table('pendaftar p')
                    ->select('p.id_pendaftar')
                    ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
                    ->where('LOWER(p.nama_pendaftar)', strtolower($namaPendaftar))
                    ->where('LOWER(k.nama_kontingen)', strtolower($namaKontingen))
                    ->get()
                    ->getRow();

                if ($pendaftarRow === null) {
                    log_message('warning', '[ImportJadwalSeniPoolCommitService] Pendaftar tidak ditemukan: {nama} / {kontingen}', [
                        'nama'      => $namaPendaftar,
                        'kontingen' => $namaKontingen,
                    ]);
                    continue;
                }

                $db->table('peserta_seni')->insert([
                    'id_kelompok_peserta_seni' => $idKelompokPesertaSeni,
                    'id_pendaftar'             => (int) $pendaftarRow->id_pendaftar,
                ]);
            }
        }
    }

    // -------------------------------------------------------------------------
    // Step 5 – Penampilan seni
    // -------------------------------------------------------------------------

    /**
     * Create penampilan_seni rows and inject id_penampilan_seni back into the
     * data array so Step 6 can link them to detail_jadwal_seni.
     * Mirrors CI3: _create_penampilan_seni()
     *
     * Lookup key: kontingen name + kategori_usia + jenis_kelamin + jenis_seni +
     *             nama_seni + nomor_pool → kelompok_peserta_seni row.
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @param array $dataPenampilan  Nested array with id_kompetisi_seni populated
     * @return array                 Same structure with id_penampilan_seni populated
     */
    private function createPenampilanSeni($db, array $dataPenampilan): array
    {
        foreach ($dataPenampilan as $kUsia => $byUsia) {
            foreach ($byUsia as $kJk => $byJk) {
                foreach ($byJk as $kJenis => $byJenis) {
                    foreach ($byJenis as $kNama => $byNama) {
                        foreach ($byNama as $nomorPool => $entries) {
                            foreach ($entries as $idx => $penampilan) {
                                $namaKontingen = trim($penampilan['nama_kontingen'] ?? '');

                                if (empty($namaKontingen)) {
                                    continue;
                                }

                                // Build the concatenated anggota string the same way CI3 does:
                                // "nama1, <br> nama2" — used to match kelompok_peserta_seni
                                // For pool system we look up by kontingen + kompetisi_seni
                                $idKompetisiSeni = $penampilan['id_kompetisi_seni'] ?? null;
                                if ($idKompetisiSeni === null) {
                                    continue;
                                }

                                // Find kelompok_peserta_seni by kontingen + kompetisi_seni
                                $kelompokRow = $db->table('kelompok_peserta_seni kps')
                                    ->select('kps.id_kelompok_peserta_seni')
                                    ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
                                    ->where('LOWER(k.nama_kontingen)', strtolower($namaKontingen))
                                    ->where('kps.id_kompetisi_seni', $idKompetisiSeni)
                                    ->get()
                                    ->getRow();

                                if ($kelompokRow === null) {
                                    log_message('warning', '[ImportJadwalSeniPoolCommitService] kelompok_peserta_seni tidak ditemukan: kontingen={k}, id_kompetisi_seni={ks}', [
                                        'k'  => $namaKontingen,
                                        'ks' => $idKompetisiSeni,
                                    ]);
                                    continue;
                                }

                                $idKelompokPesertaSeni = (int) $kelompokRow->id_kelompok_peserta_seni;
                                $babak                 = strtolower($penampilan['babak'] ?? 'final');

                                // Insert penampilan_seni — use raw table() to include 'babak'
                                // which is not in PenampilanSeniModel::$allowedFields
                                $db->table('penampilan_seni')->insert([
                                    'id_kelompok_peserta_seni' => $idKelompokPesertaSeni,
                                    'babak'                    => $babak,
                                ]);
                                $idPenampilanSeni = (int) $db->insertID();

                                if ($idPenampilanSeni === 0) {
                                    throw new \RuntimeException("Gagal insert penampilan_seni untuk kontingen: {$namaKontingen}");
                                }

                                // Inject back into the data array for Step 6
                                $dataPenampilan[$kUsia][$kJk][$kJenis][$kNama][$nomorPool][$idx]['id_penampilan_seni'] = $idPenampilanSeni;
                            }
                        }
                    }
                }
            }
        }

        return $dataPenampilan;
    }

    // -------------------------------------------------------------------------
    // Step 6 – Detail jadwal seni + tugaskan wasit/juri
    // -------------------------------------------------------------------------

    /**
     * Insert detail_jadwal_seni rows and assign wasit/juri via PenilaianSeniService.
     * Mirrors CI3: _create_detail_jadwal_seni()
     *
     * @param \CodeIgniter\Database\BaseConnection $db
     * @param int   $idJadwalSeni    Target jadwal_seni
     * @param array $dataPenampilan  Nested array with id_penampilan_seni populated
     * @return int                   Number of detail rows inserted
     */
    private function createDetailJadwalSeni($db, int $idJadwalSeni, array $dataPenampilan): int
    {
        // Fetch jadwal_seni to get id_gelanggang for wasit/juri assignment
        $jadwalSeni = $db->table('jadwal_seni')
            ->where('id_jadwal_seni', $idJadwalSeni)
            ->get()
            ->getRow();

        if ($jadwalSeni === null) {
            throw new \RuntimeException("jadwal_seni tidak ditemukan: id={$idJadwalSeni}");
        }

        $idGelanggang      = (int) $jadwalSeni->id_gelanggang;
        $penilaianService  = new PenilaianSeniService($db);
        $jumlahPenampilan  = 0;
        $nomorUrut         = 1; // running sequence within this jadwal

        foreach ($dataPenampilan as $kUsia => $byUsia) {
            foreach ($byUsia as $kJk => $byJk) {
                foreach ($byJk as $kJenis => $byJenis) {
                    foreach ($byJenis as $kNama => $byNama) {
                        foreach ($byNama as $nomorPool => $entries) {
                            foreach ($entries as $penampilan) {
                                $idPenampilanSeni = $penampilan['id_penampilan_seni'] ?? null;

                                if ($idPenampilanSeni === null) {
                                    // Penampilan was skipped in Step 5 (e.g. missing kelompok)
                                    continue;
                                }

                                $db->table('detail_jadwal_seni')->insert([
                                    'id_jadwal_seni'    => $idJadwalSeni,
                                    'nomor_partai'      => $penampilan['nomor_partai'],
                                    'id_penampilan_seni' => $idPenampilanSeni,
                                    'nomor_urut'        => $nomorUrut,
                                ]);

                                $nomorUrut++;
                                $jumlahPenampilan++;

                                // Assign wasit/juri — log warning on failure but don't abort
                                if (! $penilaianService->tugaskanWasitJuri($idPenampilanSeni, $idGelanggang)) {
                                    log_message('warning', '[ImportJadwalSeniPoolCommitService] Gagal menugaskan wasit/juri untuk penampilan_seni: {id}', [
                                        'id' => $idPenampilanSeni,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $jumlahPenampilan;
    }
}
