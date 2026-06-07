<?php

namespace App\Services;

use App\Models\BattleSeniModel;
use App\Models\PenampilanSeniModel;
use App\Models\KompetisiSeniModel;
use App\Models\KelompokPesertaSeniModel;

class ImportJadwalSeniBattleCommitService
{
    private PenilaianSeniService $penilaianSeniService;

    public function __construct(?PenilaianSeniService $penilaianSeniService = null)
    {
        $this->penilaianSeniService = $penilaianSeniService ?? new PenilaianSeniService();
    }

    /**
     * Commit data import battle seni ke database
     * @param array $validatedData hasil dari ImportJadwalSeniBattleExcelService::validateAndExtract()
     * @param int $idJadwalSeni id jadwal seni target
     */
    public function commit(array $validatedData, int $idJadwalSeni): array
    {
        $db = db_connect();
        $db->transBegin();

        try {
            $kontingenList = $validatedData['kontingen'] ?? [];
            $pesertaSeni = $validatedData['peserta_seni'] ?? [];
            $dataKompetisiSeni = $validatedData['data_kompetisi_seni'] ?? [];
            $dataBattleSeni = $validatedData['data_battle_seni'] ?? [];

            // Get jadwal_seni for id_gelanggang reference
            $jadwalSeni = $db->table('jadwal_seni')
                ->where('id_jadwal_seni', $idJadwalSeni)
                ->get()
                ->getRow();

            if (!$jadwalSeni) {
                $db->transRollback();
                return [
                    'status' => false,
                    'message' => 'Jadwal seni tidak ditemukan (id: ' . $idJadwalSeni . ').',
                ];
            }

            $idGelanggang = (int) $jadwalSeni->id_gelanggang;

            // 1. Buat kontingen baru jika belum ada
            $this->createKontingen($kontingenList);

            // 2. Buat pendaftar baru jika belum ada
            $this->createPendaftar($pesertaSeni);

            // 3. Buat kompetisi_seni jika belum ada (return updated map)
            $dataKompetisiSeni = $this->createKompetisiSeni($dataKompetisiSeni);

            // 4. Buat kelompok_peserta_seni + peserta_seni
            $this->createKelompokPesertaSeni($pesertaSeni, $dataKompetisiSeni);

            // 5. Buat battle_seni + penampilan_seni + assign juri
            $dataBattleSeni = $this->createBattleSeni($dataBattleSeni, $idGelanggang);

            // 6. Buat detail_jadwal_seni
            $jumlahPartai = $this->createDetailJadwalSeni($idJadwalSeni, $dataBattleSeni);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return [
                    'status' => false,
                    'message' => 'Transaksi database gagal.',
                ];
            }

            $db->transCommit();

            return [
                'status' => true,
                'jumlah_partai' => $jumlahPartai,
                'message' => "Berhasil mengimport $jumlahPartai partai battle ke jadwal seni.",
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', '[ImportJadwalSeniBattleCommitService] {message}', ['message' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Buat kontingen yang belum ada di database
     */
    private function createKontingen(array $kontingenList): void
    {
        $db = db_connect();

        foreach ($kontingenList as $namaKontingen) {
            $namaKontingen = trim((string) $namaKontingen);
            if ($namaKontingen === '') {
                continue;
            }

            $existing = $db->table('kontingen')
                ->where('nama_kontingen', $namaKontingen)
                ->get()
                ->getRow();

            if ($existing) {
                continue;
            }

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
                'keterangan' => 'Auto-created from Excel import (battle seni)',
                'jenis_pendaftaran' => 'excel',
            ]);
        }
    }

    /**
     * Buat pendaftar yang belum ada di database
     */
    private function createPendaftar(array $pesertaSeni): void
    {
        $db = db_connect();

        foreach ($pesertaSeni as $kelompok) {
            foreach ($kelompok as $peserta) {
                $namaPendaftar = trim((string) ($peserta['nama_pendaftar'] ?? ''));
                $namaKontingen = trim((string) ($peserta['nama_kontingen'] ?? ''));
                $jenisKelamin = strtolower(trim((string) ($peserta['jenis_kelamin'] ?? '')));

                if ($namaPendaftar === '' || $namaKontingen === '') {
                    continue;
                }

                // Get id_kontingen
                $kontingen = $db->table('kontingen')
                    ->where('nama_kontingen', $namaKontingen)
                    ->get()
                    ->getRow();

                if (!$kontingen) {
                    continue;
                }

                $idKontingen = (int) $kontingen->id_kontingen;

                // Check if pendaftar already exists in this kontingen
                $existing = $db->table('pendaftar')
                    ->where('nama_pendaftar', $namaPendaftar)
                    ->where('id_kontingen', $idKontingen)
                    ->get()
                    ->getRow();

                if ($existing) {
                    continue;
                }

                $db->table('pendaftar')->insert([
                    'id_kontingen' => $idKontingen,
                    'nama_pendaftar' => $namaPendaftar,
                    'jenis_kelamin' => $jenisKelamin,
                    'tinggi_badan' => 0,
                    'berat_badan' => 0,
                    'tempat_lahir' => '-',
                    'keterangan' => 'Auto-created from Excel import (battle seni)',
                ]);
            }
        }
    }

    /**
     * Buat kompetisi_seni yang belum ada, isi id_kompetisi_seni ke map
     */
    private function createKompetisiSeni(array $dataKompetisiSeni): array
    {
        $db = db_connect();

        foreach ($dataKompetisiSeni as $kUsia => $byUsia) {
            foreach ($byUsia as $kGender => $byGender) {
                foreach ($byGender as $kJenis => $byJenis) {
                    foreach ($byJenis as $kNama => $byNama) {
                        foreach ($byNama as $nomorPool => $info) {
                            $idSubKategori = (int) $info['id_sub_kategori_seni'];
                            $idKompetisi = $info['id_kompetisi_seni'];

                            if ($idKompetisi === null) {
                                // Find last pool to inherit max_peserta
                                $poolTerakhir = $db->table('kompetisi_seni')
                                    ->where('id_sub_kategori_seni', $idSubKategori)
                                    ->orderBy('nomor_pool', 'DESC')
                                    ->get()
                                    ->getRow();

                                $maxPeserta = $poolTerakhir ? (int) $poolTerakhir->max_peserta : 16;

                                // If 'auto', use last pool number; else use given pool number
                                $poolToInsert = strcasecmp((string) $nomorPool, 'auto') === 0
                                    ? ($poolTerakhir ? $poolTerakhir->nomor_pool : 1)
                                    : $nomorPool;

                                // Check if this exact pool already exists
                                $existing = $db->table('kompetisi_seni')
                                    ->where('id_sub_kategori_seni', $idSubKategori)
                                    ->where('nomor_pool', $poolToInsert)
                                    ->get()
                                    ->getRow();

                                if ($existing) {
                                    $idKompetisi = (int) $existing->id_kompetisi_seni;
                                } else {
                                    $db->table('kompetisi_seni')->insert([
                                        'id_sub_kategori_seni' => $idSubKategori,
                                        'nomor_pool' => $poolToInsert,
                                        'max_peserta' => $maxPeserta,
                                        'perhitungan_medali' => 1,
                                        'keterangan' => 'Import Excel Jadwal Seni Battle',
                                    ]);
                                    $idKompetisi = (int) $db->insertID();
                                }
                            }

                            $dataKompetisiSeni[$kUsia][$kGender][$kJenis][$kNama][$nomorPool]['id_kompetisi_seni'] = $idKompetisi;
                        }
                    }
                }
            }
        }

        return $dataKompetisiSeni;
    }

    /**
     * Buat kelompok_peserta_seni + peserta_seni untuk setiap grup atlet
     */
    private function createKelompokPesertaSeni(array $pesertaSeni, array $dataKompetisiSeni): void
    {
        $db = db_connect();

        foreach ($pesertaSeni as $kelompok) {
            if (count($kelompok) === 0) {
                continue;
            }

            $first = $kelompok[0];
            $namaUsia = $first['nama_kategori_usia'];
            $jenisKelamin = $first['jenis_kelamin'];
            $jenisSeni = $first['jenis_seni'];
            $namaSeni = $first['nama_seni'];
            $nomorPool = $first['nomor_pool'];
            $namaKontingen = $first['nama_kontingen'];

            $idKompetisi = $dataKompetisiSeni[$namaUsia][$jenisKelamin][$jenisSeni][$namaSeni][$nomorPool]['id_kompetisi_seni'] ?? null;
            if ($idKompetisi === null) {
                continue;
            }

            // Get id_kontingen
            $kontingen = $db->table('kontingen')
                ->where('nama_kontingen', $namaKontingen)
                ->get()
                ->getRow();

            if (!$kontingen) {
                continue;
            }

            $idKontingen = (int) $kontingen->id_kontingen;

            // Check if this kelompok already exists (by matching all members)
            $namaPendaftarList = array_map(fn($p) => trim((string) $p['nama_pendaftar']), $kelompok);
            sort($namaPendaftarList);
            $namaPendaftarKey = strtolower(implode('|', $namaPendaftarList));

            $existingKelompok = $db->table('kelompok_peserta_seni kps')
                ->select('kps.id_kelompok_peserta_seni')
                ->where('kps.id_kompetisi_seni', $idKompetisi)
                ->where('kps.id_kontingen', $idKontingen)
                ->get()
                ->getResult();

            $foundExisting = false;
            foreach ($existingKelompok as $exKps) {
                $members = $db->table('peserta_seni ps')
                    ->select('p.nama_pendaftar')
                    ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
                    ->where('ps.id_kelompok_peserta_seni', (int) $exKps->id_kelompok_peserta_seni)
                    ->get()
                    ->getResult();

                $exNames = array_map(fn($r) => trim((string) $r->nama_pendaftar), $members);
                sort($exNames);
                $exKey = strtolower(implode('|', $exNames));

                if ($exKey === $namaPendaftarKey) {
                    $foundExisting = true;
                    break;
                }
            }

            if ($foundExisting) {
                continue;
            }

            // Create kelompok_peserta_seni
            $db->table('kelompok_peserta_seni')->insert([
                'id_kompetisi_seni' => $idKompetisi,
                'id_kontingen' => $idKontingen,
                'keterangan' => 'Import Excel Jadwal Seni Battle',
            ]);
            $idKelompokPesertaSeni = (int) $db->insertID();

            // Create peserta_seni for each member
            foreach ($kelompok as $peserta) {
                $namaPendaftar = trim((string) $peserta['nama_pendaftar']);

                $pendaftar = $db->table('pendaftar')
                    ->where('nama_pendaftar', $namaPendaftar)
                    ->where('id_kontingen', $idKontingen)
                    ->get()
                    ->getRow();

                if (!$pendaftar) {
                    continue;
                }

                $db->table('peserta_seni')->insert([
                    'id_kelompok_peserta_seni' => $idKelompokPesertaSeni,
                    'id_pendaftar' => (int) $pendaftar->id_pendaftar,
                ]);
            }
        }
    }

    /**
     * Buat battle_seni + penampilan_seni untuk biru dan merah, assign juri
     * Return updated dataBattleSeni dengan id_battle_seni
     */
    private function createBattleSeni(array $dataBattleSeni, int $idGelanggang): array
    {
        $db = db_connect();

        foreach ($dataBattleSeni as $kUsia => $byUsia) {
            foreach ($byUsia as $kGender => $byGender) {
                foreach ($byGender as $kJenis => $byJenis) {
                    foreach ($byJenis as $kNama => $byNama) {
                        foreach ($byNama as $nomorPool => $battles) {
                            foreach ($battles as $keyBattle => $battle) {
                                // Resolve blue corner kelompok_peserta_seni
                                $idPenampilanBiru = null;
                                if (!empty($battle['nama_anggota_kelompok_peserta_seni_biru'])) {
                                    $idKelompokBiru = $this->findKelompokPesertaSeni(
                                        $battle['nama_anggota_kelompok_peserta_seni_biru'],
                                        $battle['nama_kontingen_anggota_kelompok_peserta_seni_biru'],
                                        (int) $battle['id_sub_kategori_seni']
                                    );

                                    if ($idKelompokBiru !== null) {
                                        $idPenampilanBiru = $this->createPenampilanSeniIfMissing($idKelompokBiru);
                                    }
                                }

                                // Resolve red corner kelompok_peserta_seni
                                $idPenampilanMerah = null;
                                if (!empty($battle['nama_anggota_kelompok_peserta_seni_merah'])) {
                                    $idKelompokMerah = $this->findKelompokPesertaSeni(
                                        $battle['nama_anggota_kelompok_peserta_seni_merah'],
                                        $battle['nama_kontingen_anggota_kelompok_peserta_seni_merah'],
                                        (int) $battle['id_sub_kategori_seni']
                                    );

                                    if ($idKelompokMerah !== null) {
                                        $idPenampilanMerah = $this->createPenampilanSeniIfMissing($idKelompokMerah);
                                    }
                                }

                                // Get id_kompetisi_seni
                                $kompetisi = $db->table('kompetisi_seni')
                                    ->where('id_sub_kategori_seni', (int) $battle['id_sub_kategori_seni'])
                                    ->where('nomor_pool', $nomorPool)
                                    ->get()
                                    ->getRow();

                                if (!$kompetisi) {
                                    continue;
                                }

                                // Insert battle_seni
                                $db->table('battle_seni')->insert([
                                    'id_kompetisi_seni' => (int) $kompetisi->id_kompetisi_seni,
                                    'nomor_battle' => $battle['nomor_battle'],
                                    'nomor_battle_selanjutnya' => $battle['nomor_battle_selanjutnya'],
                                    'id_penampilan_seni_biru' => $idPenampilanBiru,
                                    'id_penampilan_seni_merah' => $idPenampilanMerah,
                                    'babak' => $battle['babak'],
                                    'jenis_kemenangan' => 'TBD',
                                ]);
                                $idBattleSeni = (int) $db->insertID();

                                // Assign juri to both penampilan
                                if ($idPenampilanBiru !== null) {
                                    $this->penilaianSeniService->tugaskanWasitJuri($idPenampilanBiru, $idGelanggang);
                                }
                                if ($idPenampilanMerah !== null) {
                                    $this->penilaianSeniService->tugaskanWasitJuri($idPenampilanMerah, $idGelanggang);
                                }

                                $dataBattleSeni[$kUsia][$kGender][$kJenis][$kNama][$nomorPool][$keyBattle]['id_battle_seni'] = $idBattleSeni;
                            }
                        }
                    }
                }
            }
        }

        return $dataBattleSeni;
    }

    /**
     * Find kelompok_peserta_seni by member names + kontingen + sub_kategori_seni
     */
    private function findKelompokPesertaSeni(string $namaAnggota, string $namaKontingen, int $idSubKategoriSeni): ?int
    {
        $db = db_connect();

        // Split by comma to handle multi-member groups
        $namaList = array_map('trim', explode(',', $namaAnggota));
        $namaList = array_filter($namaList);
        sort($namaList);
        $targetKey = strtolower(implode('|', $namaList));

        // Get all kelompok in this kontingen + sub_kategori
        $kelompokRows = $db->table('kelompok_peserta_seni kps')
            ->select('kps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->where('k.nama_kontingen', $namaKontingen)
            ->where('ks.id_sub_kategori_seni', $idSubKategoriSeni)
            ->get()
            ->getResult();

        foreach ($kelompokRows as $kps) {
            $members = $db->table('peserta_seni ps')
                ->select('p.nama_pendaftar')
                ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
                ->where('ps.id_kelompok_peserta_seni', (int) $kps->id_kelompok_peserta_seni)
                ->get()
                ->getResult();

            $names = array_map(fn($r) => trim((string) $r->nama_pendaftar), $members);
            sort($names);
            $key = strtolower(implode('|', $names));

            if ($key === $targetKey) {
                return (int) $kps->id_kelompok_peserta_seni;
            }
        }

        return null;
    }

    /**
     * Create penampilan_seni for kelompok_peserta_seni if not exists
     */
    private function createPenampilanSeniIfMissing(int $idKelompokPesertaSeni): int
    {
        $db = db_connect();

        $existing = $db->table('penampilan_seni')
            ->where('id_kelompok_peserta_seni', $idKelompokPesertaSeni)
            ->get()
            ->getRow();

        if ($existing) {
            return (int) $existing->id_penampilan_seni;
        }

        $db->table('penampilan_seni')->insert([
            'id_kelompok_peserta_seni' => $idKelompokPesertaSeni,
            'status_penampilan' => 'belum_tampil',
            'catatan_nilai_sama' => '',
        ]);

        return (int) $db->insertID();
    }

    /**
     * Insert detail_jadwal_seni untuk setiap battle
     * Return jumlah partai yang berhasil di-insert
     */
    private function createDetailJadwalSeni(int $idJadwalSeni, array $dataBattleSeni): int
    {
        $db = db_connect();
        $jumlah = 0;

        foreach ($dataBattleSeni as $byUsia) {
            foreach ($byUsia as $byGender) {
                foreach ($byGender as $byJenis) {
                    foreach ($byJenis as $byNama) {
                        foreach ($byNama as $battles) {
                            foreach ($battles as $battle) {
                                if (empty($battle['id_battle_seni'])) {
                                    continue;
                                }

                                $db->table('detail_jadwal_seni')->insert([
                                    'id_jadwal_seni' => $idJadwalSeni,
                                    'id_battle_seni' => (int) $battle['id_battle_seni'],
                                    'nomor_partai' => (int) $battle['nomor_partai'],
                                ]);

                                $jumlah++;
                            }
                        }
                    }
                }
            }
        }

        return $jumlah;
    }
}
