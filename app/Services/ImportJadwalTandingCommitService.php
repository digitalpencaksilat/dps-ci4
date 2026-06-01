<?php

namespace App\Services;

class ImportJadwalTandingCommitService
{
    /**
     * Commit data import ke database
     */
    public function commit(array $validatedData, int $idJadwalTanding): array
    {
        $db = db_connect();
        $db->transStart();

        try {
            $dataKompetisiTanding = $validatedData['data_kompetisi_tanding'];
            $dataPertandingan = $validatedData['data_pertandingan'];
            $pesertaTanding = $validatedData['peserta_tanding'];

            // 1. Buat kompetisi_tanding (pool) jika belum ada
            $mapKompetisiTanding = $this->buatKompetisiTandingJikaBelumAda($dataKompetisiTanding);

            // 2. Cari/buat peserta_tanding
            $mapPesertaTanding = $this->cariAtauBuatPesertaTanding($pesertaTanding, $mapKompetisiTanding);

            // 3. Insert pertandingan + detail_jadwal_tanding
            $jumlahPartai = $this->insertPertandinganDanDetail(
                $dataPertandingan,
                $mapKompetisiTanding,
                $mapPesertaTanding,
                $idJadwalTanding
            );

            // 4. Generate bagan dari jadwal excel
            $this->generateBaganDariJadwal($mapKompetisiTanding);

            $db->transComplete();

            if (!$db->transStatus()) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            return [
                'status' => true,
                'jumlah_partai' => $jumlahPartai,
                'message' => "Berhasil mengimport $jumlahPartai partai ke jadwal tanding.",
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            return [
                'status' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Buat kompetisi_tanding (pool) jika belum ada
     */
    private function buatKompetisiTandingJikaBelumAda(array $dataKompetisiTanding): array
    {
        $db = db_connect();
        $map = [];

        foreach ($dataKompetisiTanding as $kUsia => $v1) {
            foreach ($v1 as $kJk => $v2) {
                foreach ($v2 as $kLabel => $v3) {
                    foreach ($v3 as $nomorPool => $info) {
                        $idKelasTanding = (int)$info['id_kelas_tanding'];
                        $idKompetisiTanding = $info['id_kompetisi_tanding'];

                        if ($idKompetisiTanding === null) {
                            // Cek apakah sudah ada
                            $existing = $db->table('kompetisi_tanding')
                                ->where('id_kelas_tanding', $idKelasTanding)
                                ->where('nomor_pool', $nomorPool)
                                ->get()
                                ->getRow();

                            if ($existing) {
                                $idKompetisiTanding = (int)$existing->id_kompetisi_tanding;
                            } else {
                                // Buat baru
                                $db->table('kompetisi_tanding')->insert([
                                    'id_kelas_tanding' => $idKelasTanding,
                                    'nomor_pool' => $nomorPool,
                                    'max_peserta' => 16,
                                    'keterangan' => 'Import Excel Jadwal Tanding',
                                ]);
                                $idKompetisiTanding = (int)$db->insertID();
                            }
                        }

                        $mapKey = "$kUsia|$kJk|$kLabel|$nomorPool";
                        $map[$mapKey] = [
                            'id_kompetisi_tanding' => $idKompetisiTanding,
                            'id_kelas_tanding' => $idKelasTanding,
                        ];
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Cari peserta_tanding berdasarkan nama + kontingen, atau buat baru
     * Jika pendaftar tidak ada, buat otomatis (fallback untuk testing/import awal)
     */
    private function cariAtauBuatPesertaTanding(array $pesertaTanding, array $mapKompetisiTanding): array
    {
        $db = db_connect();
        $map = [];

        foreach ($pesertaTanding as $peserta) {
            $namaPendaftar = $peserta['nama_pendaftar'];
            $namaKontingen = $peserta['nama_kontingen'];
            $namaKategoriUsia = $peserta['nama_kategori_usia'];
            $jenisKelamin = $peserta['jenis_kelamin'];
            $label = $peserta['label'];
            $nomorPool = $peserta['nomor_pool'];

            $mapKey = "$namaKategoriUsia|$jenisKelamin|$label|$nomorPool";
            $idKompetisiTanding = $mapKompetisiTanding[$mapKey]['id_kompetisi_tanding'] ?? null;

            if ($idKompetisiTanding === null) continue;

            // Cari peserta_tanding yang sudah ada
            $existing = $db->table('peserta_tanding pt')
                ->select('pt.id_peserta_tanding')
                ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
                ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
                ->where('p.nama_pendaftar', $namaPendaftar)
                ->where('k.nama_kontingen', $namaKontingen)
                ->where('pt.id_kompetisi_tanding', $idKompetisiTanding)
                ->get()
                ->getRow();

            if ($existing) {
                // Gunakan key yang sama dengan yang dipakai di insertPertandinganDanDetail
                $pesertaKey = strtolower("$namaPendaftar|$namaKontingen");
                $map[$pesertaKey] = (int)$existing->id_peserta_tanding;
            } else {
                // Cari pendaftar
                $pendaftar = $db->table('pendaftar p')
                    ->select('p.id_pendaftar')
                    ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
                    ->where('p.nama_pendaftar', $namaPendaftar)
                    ->where('k.nama_kontingen', $namaKontingen)
                    ->get()
                    ->getRow();

                if ($pendaftar) {
                    // Cek apakah sudah terdaftar di kompetisi ini
                    $existingPt = $db->table('peserta_tanding')
                        ->where('id_pendaftar', $pendaftar->id_pendaftar)
                        ->where('id_kompetisi_tanding', $idKompetisiTanding)
                        ->get()
                        ->getRow();

                    if ($existingPt) {
                        $pesertaKey = strtolower("$namaPendaftar|$namaKontingen");
                        $map[$pesertaKey] = (int)$existingPt->id_peserta_tanding;
                    } else {
                        // Buat peserta_tanding baru
                        $db->table('peserta_tanding')->insert([
                            'id_pendaftar' => $pendaftar->id_pendaftar,
                            'id_kompetisi_tanding' => $idKompetisiTanding,
                            'keterangan' => 'Import Excel Jadwal Tanding',
                        ]);
                        $pesertaKey = strtolower("$namaPendaftar|$namaKontingen");
                        $map[$pesertaKey] = (int)$db->insertID();
                    }
                } else {
                    // Pendaftar tidak ditemukan — buat otomatis (fallback)
                    $idKontingen = $this->cariAtauBuatKontingen($namaKontingen);
                    if ($idKontingen) {
                        // Buat pendaftar baru (isi semua kolom NOT NULL dengan default aman)
                        $db->table('pendaftar')->insert([
                            'id_kontingen' => $idKontingen,
                            'nama_pendaftar' => $namaPendaftar,
                            'jenis_kelamin' => $jenisKelamin,
                            'tinggi_badan' => 0,
                            'berat_badan' => 0,
                            'tempat_lahir' => '-',
                            'keterangan' => 'Auto-created from Excel import',
                        ]);
                        $idPendaftar = (int)$db->insertID();

                        // Buat peserta_tanding
                        $db->table('peserta_tanding')->insert([
                            'id_pendaftar' => $idPendaftar,
                            'id_kompetisi_tanding' => $idKompetisiTanding,
                            'keterangan' => 'Import Excel Jadwal Tanding',
                        ]);
                        $pesertaKey = strtolower("$namaPendaftar|$namaKontingen");
                        $map[$pesertaKey] = (int)$db->insertID();

                        log_message('info', 'Auto-created pendaftar: {nama} / {kontingen}', [
                            'nama' => $namaPendaftar,
                            'kontingen' => $namaKontingen,
                        ]);
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Cari atau buat kontingen
     * Auto-fill semua kolom NOT NULL dengan default aman
     */
    private function cariAtauBuatKontingen(string $namaKontingen): ?int
    {
        $db = db_connect();
        
        $existing = $db->table('kontingen')
            ->where('nama_kontingen', $namaKontingen)
            ->get()
            ->getRow();

        if ($existing) {
            return (int)$existing->id_kontingen;
        }

        // Buat kontingen baru — isi semua kolom NOT NULL dengan default aman
        $username = 'auto_' . strtolower(preg_replace('/[^a-z0-9]/i', '_', $namaKontingen)) . '_' . time();
        $db->table('kontingen')->insert([
            'nama_kontingen' => $namaKontingen,
            'jenis_kontingen' => 'dalam_negeri',
            'perguruan' => 'ipsi',
            'email_kontingen' => '-',
            'nomor_telepon_kontingen' => '-',
            'alamat_kontingen' => '-',
            'username' => $username,
            'password' => '-',
            'nama_penanggungjawab' => '-',
            'jabatan_penanggungjawab' => '-',
            'nomor_telepon_penanggungjawab' => '-',
            'negara' => 'Indonesia',
            'alamat_lengkap' => '-',
            'keterangan' => 'Auto-created from Excel import',
            'jenis_pendaftaran' => 'excel',
        ]);

        return (int)$db->insertID();
    }

    /**
     * Insert pertandingan + detail_jadwal_tanding
     */
    private function insertPertandinganDanDetail(
        array $dataPertandingan,
        array $mapKompetisiTanding,
        array $mapPesertaTanding,
        int $idJadwalTanding
    ): int {
        $db = db_connect();
        $jumlahPartai = 0;

        foreach ($dataPertandingan as $kUsia => $v1) {
            foreach ($v1 as $kJk => $v2) {
                foreach ($v2 as $kLabel => $v3) {
                    foreach ($v3 as $nomorPool => $arrPool) {
                        $mapKey = "$kUsia|$kJk|$kLabel|$nomorPool";
                        $idKompetisiTanding = $mapKompetisiTanding[$mapKey]['id_kompetisi_tanding'] ?? null;

                        if ($idKompetisiTanding === null) continue;

                        foreach ($arrPool as $pertandingan) {
                            // Cari id_peserta_tanding untuk biru dan merah
                            $idAtletBiru = null;
                            $idAtletMerah = null;

                            if (!empty($pertandingan['nama_atlet_biru']) && !empty($pertandingan['nama_kontingen_atlet_biru'])) {
                                $pesertaKey = strtolower($pertandingan['nama_atlet_biru'] . '|' . $pertandingan['nama_kontingen_atlet_biru']);
                                $idAtletBiru = $mapPesertaTanding[$pesertaKey] ?? null;
                            }

                            if (!empty($pertandingan['nama_atlet_merah']) && !empty($pertandingan['nama_kontingen_atlet_merah'])) {
                                $pesertaKey = strtolower($pertandingan['nama_atlet_merah'] . '|' . $pertandingan['nama_kontingen_atlet_merah']);
                                $idAtletMerah = $mapPesertaTanding[$pesertaKey] ?? null;
                            }

                            // Insert pertandingan
                            $nomorPertandingan = $pertandingan['nomor_pertandingan'] ?? $pertandingan['nomor_partai'];
                            $nomorPertandinganSelanjutnya = $pertandingan['nomor_pertandingan_selanjutnya'] ?? null;

                            $db->table('pertandingan')->insert([
                                'id_kompetisi_tanding' => $idKompetisiTanding,
                                'id_atlet_biru' => $idAtletBiru,
                                'id_atlet_merah' => $idAtletMerah,
                                'babak' => $pertandingan['babak'],
                                'nomor_pertandingan' => $nomorPertandingan,
                                'nomor_pertandingan_selanjutnya' => $nomorPertandinganSelanjutnya,
                            ]);
                            $idPertandingan = (int)$db->insertID();

                            // Insert detail_jadwal_tanding
                            $db->table('detail_jadwal_tanding')->insert([
                                'id_jadwal_tanding' => $idJadwalTanding,
                                'id_pertandingan' => $idPertandingan,
                                'nomor_partai' => $pertandingan['nomor_partai'],
                            ]);

                            $jumlahPartai++;
                        }
                    }
                }
            }
        }

        return $jumlahPartai;
    }

    /**
     * Generate bagan dari jadwal excel untuk setiap kompetisi_tanding yang terpengaruh
     */
    private function generateBaganDariJadwal(array $mapKompetisiTanding): void
    {
        $processed = [];
        foreach ($mapKompetisiTanding as $info) {
            $idKompetisiTanding = (int)$info['id_kompetisi_tanding'];
            if (in_array($idKompetisiTanding, $processed)) continue;

            try {
                $kompetisiModel = new \App\Models\KompetisiTandingModel();
                $kompetisiModel->generate_bagan_dari_jadwal_excel($idKompetisiTanding);
            } catch (\Throwable $e) {
                log_message('warning', 'Gagal generate bagan untuk kompetisi_tanding {id}: {msg}', [
                    'id' => $idKompetisiTanding,
                    'msg' => $e->getMessage(),
                ]);
            }

            $processed[] = $idKompetisiTanding;
        }
    }
}
