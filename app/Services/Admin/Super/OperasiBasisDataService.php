<?php

namespace App\Services\Admin\Super;

class OperasiBasisDataService
{
    /**
     * Parity CI3: Hapus seluruh peserta/kelompok yang belum lunas.
     *
     * Catatan: di CI3 status menggunakan string 'belum_lunas' (juga ada 'menunggu').
     * Di CI4 kita menghapus baris peserta tanding + kelompok peserta seni yang terikat pembayaran belum lunas.
     */
    public function hapusAtletBelumLunas(): array
    {
        $db = db_connect();
        $db->transStart();

        try {
            // Tanding
            $tandingIds = array_column(
                $db->table('peserta_tanding pt')
                    ->select('pt.id_peserta_tanding')
                    ->join('pembayaran pb', 'pb.id_pembayaran = pt.id_pembayaran', 'left')
                    ->where('pb.status_pembayaran', 'belum_lunas')
                    ->get()
                    ->getResultArray(),
                'id_peserta_tanding'
            );
            $tandingIds = array_values(array_unique(array_filter(array_map('intval', $tandingIds), static fn (int $id): bool => $id > 0)));
            if ($tandingIds !== []) {
                $db->table('peserta_tanding')->whereIn('id_peserta_tanding', $tandingIds)->delete();
            }

            // Seni (hapus kelompok; peserta_seni diasumsikan cascade/terjaga constraint seperti di CI3)
            $kelompokIds = array_column(
                $db->table('kelompok_peserta_seni kps')
                    ->select('kps.id_kelompok_peserta_seni')
                    ->join('pembayaran pb', 'pb.id_pembayaran = kps.id_pembayaran', 'left')
                    ->where('pb.status_pembayaran', 'belum_lunas')
                    ->get()
                    ->getResultArray(),
                'id_kelompok_peserta_seni'
            );
            $kelompokIds = array_values(array_unique(array_filter(array_map('intval', $kelompokIds), static fn (int $id): bool => $id > 0)));
            if ($kelompokIds !== []) {
                $db->table('kelompok_peserta_seni')->whereIn('id_kelompok_peserta_seni', $kelompokIds)->delete();
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['status' => false, 'message' => $e->getMessage()];
        }

        if (! $db->transStatus()) {
            return ['status' => false, 'message' => 'Gagal menghapus atlet belum lunas.'];
        }

        return [
            'status' => true,
            'message' => sprintf(
                'Penghapusan atlet belum lunas selesai. Tanding terhapus: %d, Seni (kelompok) terhapus: %d.',
                count($tandingIds ?? []),
                count($kelompokIds ?? [])
            ),
        ];
    }

    public function buatPoolBaru(bool $wrapTransaction = true): array
    {
        $db = db_connect();
        if ($wrapTransaction) {
            $db->transStart();
        }

        try {
            $kelasRows = $db->table('kelas_tanding')->select('id_kelas_tanding')->get()->getResult();
            $kelasModel = new \App\Models\KelasTandingModel();

            foreach ($kelasRows as $row) {
                $idKelas = (int) ($row->id_kelas_tanding ?? 0);
                if ($idKelas <= 0) {
                    continue;
                }

                $kelasModel->otomatis_menambahkan_pool($idKelas);
            }

            $subRows = $db->table('sub_kategori_seni')
                ->select('id_sub_kategori_seni')
                ->get()
                ->getResult();
            $subKategoriSeniModel = new \App\Models\SubKategoriSeniModel();

            foreach ($subRows as $row) {
                $idSub = (int) ($row->id_sub_kategori_seni ?? 0);
                if ($idSub <= 0) {
                    continue;
                }

                $kapasitasDefault = (int) $db->table('sub_kategori_seni sks')
                    ->select('COALESCE(MAX(ks.max_peserta), 4) AS kapasitas', false)
                    ->join('kompetisi_seni ks', 'ks.id_sub_kategori_seni = sks.id_sub_kategori_seni', 'left')
                    ->where('sks.id_sub_kategori_seni', $idSub)
                    ->get()
                    ->getRow('kapasitas');

                $subKategoriSeniModel->otomatis_menambahkan_pool($idSub, $kapasitasDefault > 0 ? $kapasitasDefault : 4);
            }

            if ($wrapTransaction) {
                $db->transComplete();
            }
        } catch (\Throwable $e) {
            if ($wrapTransaction) {
                $db->transRollback();
            }
            return ['status' => false, 'message' => $e->getMessage()];
        }

        if ($wrapTransaction && ! $db->transStatus()) {
            return ['status' => false, 'message' => 'Gagal membuat pool baru.'];
        }

        return ['status' => true, 'message' => 'Berhasil membuat pool baru.'];
    }

    public function buatKategoriUntukPartaiTambahan(): array
    {
        // CI3 parity: call otomatis_menambahkan_pool(..., 'Partai Tambahan') for tanding + seni.
        $db = db_connect();
        $db->transStart();

        try {
            $kelasRows = $db->table('kelas_tanding')->select('id_kelas_tanding')->get()->getResult();
            $kelasModel = new \App\Models\KelasTandingModel();
            foreach ($kelasRows as $row) {
                $idKelas = (int) ($row->id_kelas_tanding ?? 0);
                if ($idKelas <= 0) {
                    continue;
                }
                $kelasModel->otomatis_menambahkan_pool($idKelas, null, 'Partai Tambahan');
            }

            $subRows = $db->table('sub_kategori_seni')->select('id_sub_kategori_seni')->get()->getResult();
            $subModel = new \App\Models\SubKategoriSeniModel();
            foreach ($subRows as $row) {
                $idSub = (int) ($row->id_sub_kategori_seni ?? 0);
                if ($idSub <= 0) {
                    continue;
                }

                $kapasitasDefault = (int) $db->table('sub_kategori_seni sks')
                    ->select('COALESCE(MAX(ks.max_peserta), 4) AS kapasitas', false)
                    ->join('kompetisi_seni ks', 'ks.id_sub_kategori_seni = sks.id_sub_kategori_seni', 'left')
                    ->where('sks.id_sub_kategori_seni', $idSub)
                    ->get()
                    ->getRow('kapasitas');

                $subModel->otomatis_menambahkan_pool($idSub, $kapasitasDefault > 0 ? $kapasitasDefault : 4, 'Partai Tambahan');
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['status' => false, 'message' => $e->getMessage()];
        }

        if (! $db->transStatus()) {
            return ['status' => false, 'message' => 'Gagal membuat kategori partai tambahan.'];
        }

        return ['status' => true, 'message' => 'Berhasil membuat kategori untuk partai tambahan.'];
    }

    public function resetDatabase(): array
    {
        // High-risk operation; mirrors CI3 table empty + reset auto-increment.
        $db = db_connect();
        $tables = $db->listTables();

        $keep = [
            'admin',
            'kategori_usia',
            'kategori_lomba',
            'kelas_tanding',
            'sub_kategori_seni',
            'kompetisi_tanding',
            'kompetisi_seni',
            'gelanggang',
            'broadcast_graphic',
            'perangkat_pertandingan',
        ];

        $db->transStart();
        try {
            foreach ($tables as $table) {
                if (in_array($table, $keep, true)) {
                    continue;
                }
                $db->table($table)->emptyTable();
                try {
                    $db->query("ALTER TABLE `$table` AUTO_INCREMENT = 1");
                } catch (\Throwable $e) {
                    // Ignore tables without AI.
                }
            }

            // Remove extra pools and recreate missing pools.
            $db->table('kompetisi_seni')->where('nomor_pool >', 1)->delete();
            $db->table('kompetisi_tanding')->where('nomor_pool >', 1)->delete();

            $this->buatPoolBaru();

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['status' => false, 'message' => $e->getMessage()];
        }

        if (! $db->transStatus()) {
            return ['status' => false, 'message' => 'Gagal reset database.'];
        }

        $this->hapusFileResetDatabase([
            FCPATH . 'uploads/jadwal-pdf/seni',
            FCPATH . 'uploads/jadwal-pdf/tanding',
            FCPATH . 'uploads/bukti-pembayaran',
            FCPATH . 'uploads/peserta/foto/thumbnail',
            FCPATH . 'uploads/peserta/foto',
            FCPATH . 'uploads/peserta/arsip',
            FCPATH . 'uploads/official/foto/thumbnail',
            FCPATH . 'uploads/official/foto',
        ]);

        return ['status' => true, 'message' => 'Reset database selesai.'];
    }

    private function hapusFileResetDatabase(array $directories): void
    {
        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $items = scandir($directory);
            if ($items === false) {
                continue;
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $path = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $item;
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        }
    }

    /**
     * CI3 parity: remove `kompetisi_seni` rows that have no kelompok.
     */
    public function hapusPoolSeniKosong(): int
    {
        $db = db_connect();

        $rows = $db->table('kompetisi_seni ks')
            ->select('ks.id_kompetisi_seni')
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kompetisi_seni = ks.id_kompetisi_seni) AS jumlah_kelompok', false)
            ->having('jumlah_kelompok = 0')
            ->get()
            ->getResult();

        $ids = array_values(array_unique(array_filter(array_map(static fn ($row): int => (int) ($row->id_kompetisi_seni ?? 0), $rows), static fn (int $id): bool => $id > 0)));
        if ($ids === []) {
            return 0;
        }

        $db->table('kompetisi_seni')->whereIn('id_kompetisi_seni', $ids)->delete();

        return count($ids);
    }

    /**
     * CI3 parity: delete kontingen where jenis_pendaftaran = 'excel'.
     * Note: relies on DB constraints/cascade as per existing schema.
     */
    public function hapusDataDariExcel(): array
    {
        $db = db_connect();
        $db->transStart();

        try {
            $ids = array_column(
                $db->table('kontingen')
                    ->select('id_kontingen')
                    ->where('jenis_pendaftaran', 'excel')
                    ->get()
                    ->getResultArray(),
                'id_kontingen'
            );

            $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0)));
            if ($ids !== []) {
                $db->table('kontingen')->whereIn('id_kontingen', $ids)->delete();
            }

            // CI3 parity: after deleting Excel kontingen, remove empty tanding pools, recreate missing/full pools,
            // and normalize nomor_pool sequence through the same pool builder used by the maintenance action.
            $poolKosongIds = array_column(
                $db->table('kompetisi_tanding kt')
                    ->select('kt.id_kompetisi_tanding')
                    ->select('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kt.id_kompetisi_tanding) AS jumlah_peserta', false)
                    ->having('jumlah_peserta = 0')
                    ->get()
                    ->getResultArray(),
                'id_kompetisi_tanding'
            );
            $poolKosongIds = array_values(array_unique(array_filter(array_map('intval', $poolKosongIds), static fn (int $id): bool => $id > 0)));
            if ($poolKosongIds !== []) {
                $db->table('kompetisi_tanding')->whereIn('id_kompetisi_tanding', $poolKosongIds)->delete();
            }

            $poolResult = $this->buatPoolBaru(false);
            if (($poolResult['status'] ?? false) !== true) {
                $db->transRollback();
                return ['status' => false, 'message' => (string) ($poolResult['message'] ?? 'Gagal memperbaiki pool setelah hapus data Excel.')];
            }

            $this->urutkanNomorPoolTanding();

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            return ['status' => false, 'message' => $e->getMessage()];
        }

        if (! $db->transStatus()) {
            return ['status' => false, 'message' => 'Gagal hapus data dari Excel.'];
        }

        return [
            'status' => true,
            'message' => sprintf(
                'Data dari Excel berhasil diproses. Kontingen terhapus: %d, pool tanding kosong terhapus: %d.',
                count($ids ?? []),
                count($poolKosongIds ?? [])
            ),
            'data' => [
                'kontingen' => count($ids ?? []),
                'pool_tanding_kosong' => count($poolKosongIds ?? []),
            ],
        ];
    }

    private function urutkanNomorPoolTanding(): void
    {
        $db = db_connect();
        $kelasRows = $db->table('kelas_tanding')->select('id_kelas_tanding')->get()->getResult();

        foreach ($kelasRows as $kelas) {
            $idKelas = (int) ($kelas->id_kelas_tanding ?? 0);
            if ($idKelas <= 0) {
                continue;
            }

            $poolRows = $db->table('kompetisi_tanding')
                ->select('id_kompetisi_tanding')
                ->where('id_kelas_tanding', $idKelas)
                ->orderBy('nomor_pool', 'ASC')
                ->orderBy('id_kompetisi_tanding', 'ASC')
                ->get()
                ->getResult();

            $nomorPool = 1;
            foreach ($poolRows as $pool) {
                $idPool = (int) ($pool->id_kompetisi_tanding ?? 0);
                if ($idPool <= 0) {
                    continue;
                }

                $db->table('kompetisi_tanding')
                    ->where('id_kompetisi_tanding', $idPool)
                    ->update(['nomor_pool' => $nomorPool++]);
            }
        }
    }

    public function previewHapusDataKosong(): array
    {
        $db = db_connect();

        $kontingenKosong = (int) $db->query(
            'SELECT COUNT(*) AS total FROM kontingen k WHERE (SELECT COUNT(*) FROM pendaftar p WHERE p.id_kontingen = k.id_kontingen) = 0'
        )->getRow('total');

        $pendaftarKosong = (int) $db->query(
            'SELECT COUNT(*) AS total FROM pendaftar p WHERE (SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_pendaftar = p.id_pendaftar) = 0 AND (SELECT COUNT(*) FROM peserta_seni ps WHERE ps.id_pendaftar = p.id_pendaftar) = 0'
        )->getRow('total');

        return [
            'kontingen_kosong' => $kontingenKosong,
            'pendaftar_kosong' => $pendaftarKosong,
        ];
    }

    public function hapusDataKosong(string $mode): array
    {
        if (! in_array($mode, ['kontingen_kosong', 'pendaftar_kosong', 'semua'], true)) {
            return ['status' => false, 'message' => 'Mode tidak valid.'];
        }

        $db = db_connect();
        $db->transStart();

        $deleted = [
            'kontingen_kosong' => 0,
            'pendaftar_kosong' => 0,
        ];

        if ($mode === 'pendaftar_kosong' || $mode === 'semua') {
            $pendaftarIds = array_column(
                $db->query(
                    'SELECT p.id_pendaftar FROM pendaftar p WHERE (SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_pendaftar = p.id_pendaftar) = 0 AND (SELECT COUNT(*) FROM peserta_seni ps WHERE ps.id_pendaftar = p.id_pendaftar) = 0'
                )->getResultArray(),
                'id_pendaftar'
            );

            $pendaftarIds = array_values(array_unique(array_filter(array_map('intval', $pendaftarIds), static fn (int $id): bool => $id > 0)));
            if ($pendaftarIds !== []) {
                $db->table('pendaftar')->whereIn('id_pendaftar', $pendaftarIds)->delete();
                $deleted['pendaftar_kosong'] = count($pendaftarIds);
            }
        }

        if ($mode === 'kontingen_kosong' || $mode === 'semua') {
            $kontingenIds = array_column(
                $db->query(
                    'SELECT k.id_kontingen FROM kontingen k WHERE (SELECT COUNT(*) FROM pendaftar p WHERE p.id_kontingen = k.id_kontingen) = 0'
                )->getResultArray(),
                'id_kontingen'
            );

            $kontingenIds = array_values(array_unique(array_filter(array_map('intval', $kontingenIds), static fn (int $id): bool => $id > 0)));
            if ($kontingenIds !== []) {
                $db->table('kontingen')->whereIn('id_kontingen', $kontingenIds)->delete();
                $deleted['kontingen_kosong'] = count($kontingenIds);
            }
        }

        $db->transComplete();
        if (! $db->transStatus()) {
            return ['status' => false, 'message' => 'Gagal menghapus data kosong. Silahkan cek constraint DB/log.'];
        }

        return [
            'status' => true,
            'message' => 'Berhasil menghapus: kontingen kosong ' . $deleted['kontingen_kosong'] . ', pendaftar kosong ' . $deleted['pendaftar_kosong'] . '.',
            'data' => $deleted,
        ];
    }

    public function previewHapusPesertaBerdasarkanKategoriUsia(string $jenisPeserta, array $kategoriUsiaIds): array
    {
        if (! in_array($jenisPeserta, ['tanding', 'seni'], true) || $kategoriUsiaIds === []) {
            return ['status' => false, 'message' => 'Input tidak valid.'];
        }

        $kategoriUsiaIds = array_values(array_unique(array_filter(array_map('intval', $kategoriUsiaIds), static fn (int $id): bool => $id > 0)));
        if ($kategoriUsiaIds === []) {
            return ['status' => false, 'message' => 'Input tidak valid.'];
        }

        $db = db_connect();

        // Find eligible pendaftar IDs by category age and type.
        if ($jenisPeserta === 'tanding') {
            $rows = $db->table('peserta_tanding pt')
                ->distinct()
                ->select('p.id_pendaftar')
                ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
                ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
                ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
                ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
                ->where('kl.nama_kategori_lomba', 'tanding')
                ->whereIn('kl.id_kategori_usia', $kategoriUsiaIds)
                ->get()->getResult();
        } else {
            $rows = $db->table('peserta_seni ps')
                ->distinct()
                ->select('p.id_pendaftar')
                ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
                ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
                ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
                ->where('kl.nama_kategori_lomba', 'seni')
                ->whereIn('kl.id_kategori_usia', $kategoriUsiaIds)
                ->get()->getResult();
        }

        $pendaftarIds = array_values(array_unique(array_filter(array_map(static fn ($row): int => (int) ($row->id_pendaftar ?? 0), $rows), static fn (int $id): bool => $id > 0)));
        if ($pendaftarIds === []) {
            return ['status' => false, 'message' => 'Tidak ada peserta pada kategori usia yang dipilih.'];
        }

        $preview = [
            'jenis_peserta' => $jenisPeserta,
            'jumlah_kategori_usia' => count($kategoriUsiaIds),
            'pendaftar' => count($pendaftarIds),
            'peserta' => 0,
            'jadwal_detail' => 0,
            'penilaian' => 0,
            'medali' => 0,
            'partai' => 0,
            'penampilan' => 0,
            'battle' => 0,
            'kelompok' => 0,
        ];

        if ($jenisPeserta === 'tanding') {
            $pesertaIds = array_column(
                $db->table('peserta_tanding')->select('id_peserta_tanding')->whereIn('id_pendaftar', $pendaftarIds)->get()->getResultArray(),
                'id_peserta_tanding'
            );
            $pesertaIds = array_values(array_unique(array_filter(array_map('intval', $pesertaIds), static fn (int $id): bool => $id > 0)));
            $preview['peserta'] = count($pesertaIds);

            if ($pesertaIds !== []) {
                $pertandinganIds = array_column(
                    $db->table('pertandingan')
                        ->select('id_pertandingan')
                        ->groupStart()
                        ->whereIn('id_atlet_merah', $pesertaIds)
                        ->orWhereIn('id_atlet_biru', $pesertaIds)
                        ->orWhereIn('id_pemenang', $pesertaIds)
                        ->groupEnd()
                        ->get()->getResultArray(),
                    'id_pertandingan'
                );
                $pertandinganIds = array_values(array_unique(array_filter(array_map('intval', $pertandinganIds), static fn (int $id): bool => $id > 0)));

                $preview['partai'] = count($pertandinganIds);
                if ($pertandinganIds !== []) {
                    $preview['jadwal_detail'] = (int) $db->table('detail_jadwal_tanding')->whereIn('id_pertandingan', $pertandinganIds)->countAllResults();
                    $preview['penilaian'] = (int) $db->table('penilaian_tanding')->whereIn('id_pertandingan', $pertandinganIds)->countAllResults();
                }

                $preview['medali'] = (int) $db->table('perolehan_medali_tanding')->whereIn('id_peserta_tanding', $pesertaIds)->countAllResults();
            }
        } else {
            $pesertaIds = array_column(
                $db->table('peserta_seni')->select('id_peserta_seni')->whereIn('id_pendaftar', $pendaftarIds)->get()->getResultArray(),
                'id_peserta_seni'
            );
            $pesertaIds = array_values(array_unique(array_filter(array_map('intval', $pesertaIds), static fn (int $id): bool => $id > 0)));

            $kelompokIds = array_column(
                $db->table('peserta_seni')->distinct()->select('id_kelompok_peserta_seni')->whereIn('id_pendaftar', $pendaftarIds)->get()->getResultArray(),
                'id_kelompok_peserta_seni'
            );
            $kelompokIds = array_values(array_unique(array_filter(array_map('intval', $kelompokIds), static fn (int $id): bool => $id > 0)));

            $preview['peserta'] = count($pesertaIds);
            $preview['kelompok'] = count($kelompokIds);

            if ($kelompokIds !== []) {
                $penampilanIds = array_column(
                    $db->table('penampilan_seni')->select('id_penampilan_seni')->whereIn('id_kelompok_peserta_seni', $kelompokIds)->get()->getResultArray(),
                    'id_penampilan_seni'
                );
                $penampilanIds = array_values(array_unique(array_filter(array_map('intval', $penampilanIds), static fn (int $id): bool => $id > 0)));

                $preview['penampilan'] = count($penampilanIds);
                $preview['medali'] = (int) $db->table('perolehan_medali_seni')->whereIn('id_kelompok_peserta_seni', $kelompokIds)->countAllResults();

                if ($penampilanIds !== []) {
                    $battleIds = array_column(
                        $db->table('battle_seni')
                            ->select('id_battle_seni')
                            ->groupStart()
                            ->whereIn('id_penampilan_seni_biru', $penampilanIds)
                            ->orWhereIn('id_penampilan_seni_merah', $penampilanIds)
                            ->orWhereIn('id_penampilan_seni_pemenang', $penampilanIds)
                            ->groupEnd()
                            ->get()->getResultArray(),
                        'id_battle_seni'
                    );
                    $battleIds = array_values(array_unique(array_filter(array_map('intval', $battleIds), static fn (int $id): bool => $id > 0)));

                    $preview['battle'] = count($battleIds);
                    $preview['jadwal_detail'] = (int) $db->table('detail_jadwal_seni')
                        ->groupStart()
                        ->whereIn('id_penampilan_seni', $penampilanIds)
                        ->orWhereIn('id_battle_seni', $battleIds !== [] ? $battleIds : [0])
                        ->groupEnd()
                        ->countAllResults();
                    $preview['penilaian'] = (int) $db->table('penilaian_seni')->whereIn('id_penampilan_seni', $penampilanIds)->countAllResults();
                }
            }
        }

        return ['status' => true, 'message' => 'Preview berhasil dibuat.', 'data' => $preview];
    }

    public function hapusPesertaBerdasarkanKategoriUsia(string $jenisPeserta, array $kategoriUsiaIds): array
    {
        $preview = $this->previewHapusPesertaBerdasarkanKategoriUsia($jenisPeserta, $kategoriUsiaIds);
        if (($preview['status'] ?? false) !== true) {
            return ['status' => false, 'message' => (string) ($preview['message'] ?? 'Input tidak valid.')];
        }

        $db = db_connect();
        $db->transStart();

        // Recompute id_pendaftar list reliably.
        $jenisPeserta = (string) $preview['data']['jenis_peserta'];

        if ($jenisPeserta === 'tanding') {
            $rows = $db->table('peserta_tanding pt')
                ->distinct()
                ->select('p.id_pendaftar')
                ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
                ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
                ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
                ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
                ->where('kl.nama_kategori_lomba', 'tanding')
                ->whereIn('kl.id_kategori_usia', $kategoriUsiaIds)
                ->get()->getResult();
        } else {
            $rows = $db->table('peserta_seni ps')
                ->distinct()
                ->select('p.id_pendaftar')
                ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
                ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
                ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
                ->where('kl.nama_kategori_lomba', 'seni')
                ->whereIn('kl.id_kategori_usia', $kategoriUsiaIds)
                ->get()->getResult();
        }

        $pendaftarIds = array_values(array_unique(array_filter(array_map(static fn ($row): int => (int) ($row->id_pendaftar ?? 0), $rows), static fn (int $id): bool => $id > 0)));
        if ($pendaftarIds === []) {
            $db->transComplete();
            return ['status' => false, 'message' => 'Tidak ada peserta pada kategori usia yang dipilih.'];
        }

        // CI3 parity: delete pendaftar (and rely on FK cascade for children).
        $db->table('pendaftar')->whereIn('id_pendaftar', $pendaftarIds)->delete();

        $db->transComplete();
        if (! $db->transStatus()) {
            return ['status' => false, 'message' => 'Gagal menghapus peserta. Silahkan cek constraint DB/log.'];
        }

        return ['status' => true, 'message' => 'Berhasil menghapus peserta berdasarkan kategori usia.'];
    }
}
