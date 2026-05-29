<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class JadwalTandingOtomatisService
{
    private BaseConnection $db;
    private PenilaianTandingService $penilaianTandingService;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->penilaianTandingService = new PenilaianTandingService($this->db);
    }

    public function generate(array $pengaturan): array
    {
        $tanggal = (string) ($pengaturan['tanggal'] ?? '');
        $jamMulai = (string) ($pengaturan['jam_mulai'] ?? '');
        $jamSelesai = (string) ($pengaturan['jam_selesai'] ?? '');
        $keterangan = (string) ($pengaturan['keterangan'] ?? '');
        $jenisPenjadwalan = (string) ($pengaturan['jenis_penjadwalan'] ?? 'prestasi');

        $dataIdGelanggang = $this->normalizeIntArray($pengaturan['id_gelanggang'] ?? []);
        $jumlahPartaiInput = $this->normalizeIntArray($pengaturan['jumlah_partai'] ?? []);
        $babakPertandingan = $this->normalizeStringArray($pengaturan['babak_pertandingan'] ?? []);
        $urutanIdKelasTanding = $this->normalizeIntArray($pengaturan['urutan_id_kelas_tanding'] ?? []);
        $jumlahSelangSeling = max(1, (int) ($pengaturan['jumlah_selang_seling'] ?? 2));

        if ($dataIdGelanggang === [] || $jumlahPartaiInput === []) {
            return ['status' => false, 'message' => 'Pengaturan gelanggang tidak valid.'];
        }

        if (count($dataIdGelanggang) !== count($jumlahPartaiInput)) {
            return ['status' => false, 'message' => 'Jumlah gelanggang dan jumlah partai tidak sama.'];
        }

        if ($babakPertandingan === [] || $urutanIdKelasTanding === []) {
            return ['status' => false, 'message' => 'Babak pertandingan dan urutan kelas tanding wajib diisi.'];
        }

        $jumlahPartai = [];
        foreach ($dataIdGelanggang as $index => $idGelanggang) {
            $jumlahPartai[$idGelanggang] = (int) ($jumlahPartaiInput[$index] ?? 0);
        }

        $this->db->transStart();

        $partaiTerakhirGelanggang = $this->getArrayPartaiTerakhirGelanggang($dataIdGelanggang);
        $matches = $jenisPenjadwalan === 'pemasalan'
            ? $this->fetchMatchesPemasalan($urutanIdKelasTanding, $babakPertandingan)
            : $this->fetchMatchesPrestasi($urutanIdKelasTanding, $babakPertandingan);

        if ($matches === []) {
            $this->db->transComplete();
            return ['status' => false, 'message' => 'Tidak ada pertandingan eligible untuk dijadwalkan.'];
        }

        $matchIds = array_map(static fn (array $row): int => (int) $row['id_pertandingan'], $matches);
        if (! $this->cekPertandinganTerinput($matchIds)) {
            $this->db->transComplete();
            return ['status' => false, 'message' => 'Pertandingan telah terinput di jadwal.'];
        }

        if ($jenisPenjadwalan === 'pemasalan') {
            $matches = $this->acakUrutanPertandingan($jumlahSelangSeling, $matches);
            $paketPertandingan = $this->kelompokkanPertandinganKeDalamPaket($matches);
            $paketPerGelanggang = $this->alokasiPaketKeGelanggang($paketPertandingan, $dataIdGelanggang, $jumlahPartai);
            $result = $this->persistPaketPerGelanggang($paketPerGelanggang, $dataIdGelanggang, $tanggal, $jamMulai, $jamSelesai, $keterangan, $partaiTerakhirGelanggang);
        } else {
            $pertandinganPerGelanggang = $this->alokasiPrestasiKeGelanggang($matches, $dataIdGelanggang, $jumlahPartai);
            $result = $this->persistPertandinganPerGelanggang($pertandinganPerGelanggang, $dataIdGelanggang, $tanggal, $jamMulai, $jamSelesai, $keterangan, $partaiTerakhirGelanggang);
        }

        // Kalau persist gagal, rollback transaksi supaya tidak ada partial insert.
        if (! ($result['status'] ?? false)) {
            $this->db->transRollback();
            return $result;
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            return ['status' => false, 'message' => 'Gagal generate jadwal (transaksi DB gagal).'];
        }

        return $result;
    }

    private function fetchMatchesPrestasi(array $urutanIdKelasTanding, array $babakPertandingan): array
    {
        $escaped = implode(',', array_map(static fn (int $id): string => (string) $id, $urutanIdKelasTanding));

        return $this->db->table('pertandingan')
            ->select(
                'pertandingan.id_pertandingan,
                pertandingan.nomor_pertandingan,
                kategori_lomba.id_kategori_lomba,
                kategori_lomba.jumlah_juri,
                kategori_usia.id_kategori_usia as id_ku,
                kategori_usia.max_umur,
                kategori_usia.nama_kategori_usia,
                CASE pertandingan.babak
                    WHEN "Final" THEN 1
                    WHEN "Perebutan Juara Tiga" THEN 2/3
                    WHEN "Semi Final" THEN 1/2
                    WHEN "1/4 Final" THEN 1/4
                    WHEN "1/8 Final" THEN 1/8
                    WHEN "1/16 Final" THEN 1/16
                    WHEN "1/32 Final" THEN 1/32
                    ELSE 999
                END AS nilai_babak',
                false
            )
            ->join('kompetisi_tanding', 'pertandingan.id_kompetisi_tanding = kompetisi_tanding.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kompetisi_tanding.id_kelas_tanding = kelas_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kelas_tanding.id_kategori_lomba = kategori_lomba.id_kategori_lomba')
            ->join('kategori_usia', 'kategori_lomba.id_kategori_usia = kategori_usia.id_kategori_usia')
            ->where('pertandingan.jenis_kemenangan !=', 'BYE')
            ->whereIn('kelas_tanding.id_kelas_tanding', $urutanIdKelasTanding)
            ->whereIn('pertandingan.babak', $babakPertandingan)
            ->whereNotIn('pertandingan.id_pertandingan', static function ($builder) {
                $builder->select('id_pertandingan')->from('detail_jadwal_tanding')->where('id_pertandingan IS NOT NULL', null, false);
            })
            ->orderBy('nilai_babak', 'asc')
            ->orderBy('FIELD(kelas_tanding.id_kelas_tanding,' . $escaped . ')', '', false)
            ->orderBy('kelas_tanding.berat_maksimal', 'asc')
            ->orderBy('kelas_tanding.berat_minimal', 'asc')
            ->orderBy('kompetisi_tanding.nomor_pool', 'asc')
            ->orderBy('pertandingan.nomor_pertandingan', 'asc')
            ->get()
            ->getResultArray();
    }

    private function fetchMatchesPemasalan(array $urutanIdKelasTanding, array $babakPertandingan): array
    {
        $escaped = implode(',', array_map(static fn (int $id): string => (string) $id, $urutanIdKelasTanding));

        return $this->db->table('pertandingan')
            ->select(
                'pertandingan.id_pertandingan,
                pertandingan.nomor_pertandingan,
                CASE pertandingan.babak
                    WHEN "Final" THEN 1
                    WHEN "Perebutan Juara Tiga" THEN 2/3
                    WHEN "Semi Final" THEN 1/2
                    WHEN "1/4 Final" THEN 1/4
                    WHEN "1/8 Final" THEN 1/8
                    WHEN "1/16 Final" THEN 1/16
                    WHEN "1/32 Final" THEN 1/32
                    ELSE 999
                END AS nilai_babak,
                kategori_lomba.id_kategori_lomba,
                kategori_lomba.jumlah_juri,
                kategori_usia.id_kategori_usia as id_ku,
                kategori_usia.max_umur,
                kategori_usia.nama_kategori_usia,
                kategori_usia.id_kategori_usia,
                kelas_tanding.label,
                kompetisi_tanding.id_kompetisi_tanding,
                kompetisi_tanding.nomor_pool',
                false
            )
            ->join('kompetisi_tanding', 'pertandingan.id_kompetisi_tanding = kompetisi_tanding.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kompetisi_tanding.id_kelas_tanding = kelas_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kelas_tanding.id_kategori_lomba = kategori_lomba.id_kategori_lomba')
            ->join('kategori_usia', 'kategori_lomba.id_kategori_usia = kategori_usia.id_kategori_usia')
            ->where('pertandingan.jenis_kemenangan !=', 'BYE')
            ->whereIn('kelas_tanding.id_kelas_tanding', $urutanIdKelasTanding)
            ->whereIn('pertandingan.babak', $babakPertandingan)
            ->whereNotIn('pertandingan.id_pertandingan', static function ($builder) {
                $builder->select('id_pertandingan')->from('detail_jadwal_tanding')->where('id_pertandingan IS NOT NULL', null, false);
            })
            ->orderBy('FIELD(kelas_tanding.id_kelas_tanding,' . $escaped . ')', '', false)
            ->orderBy('kategori_usia.jenis_kelamin', 'asc')
            ->orderBy('kelas_tanding.berat_maksimal', 'asc')
            ->orderBy('kelas_tanding.berat_minimal', 'asc')
            ->orderBy('kompetisi_tanding.nomor_pool', 'asc')
            ->orderBy('pertandingan.nomor_pertandingan', 'asc')
            ->get()
            ->getResultArray();
    }

    private function alokasiPrestasiKeGelanggang(array $matches, array $dataIdGelanggang, array $jumlahPartai): array
    {
        $pertandinganPerGelanggang = [];
        foreach ($dataIdGelanggang as $idGelanggang) {
            $pertandinganPerGelanggang[$idGelanggang] = [];
        }

        $clonedPertandingan = array_values($matches);
        while ($clonedPertandingan !== []) {
            $adaProgress = false;

            foreach ($dataIdGelanggang as $idGelanggang) {
                $banyakPartai = count($pertandinganPerGelanggang[$idGelanggang] ?? []);
                $jumlahAlokasi = (int) ($jumlahPartai[$idGelanggang] ?? 0);

                if ($banyakPartai >= $jumlahAlokasi) {
                    continue;
                }

                $dataPertandingan = array_shift($clonedPertandingan);
                if ($dataPertandingan === null) {
                    break 2;
                }

                $pertandinganPerGelanggang[$idGelanggang][] = $dataPertandingan;
                $adaProgress = true;

                if ($clonedPertandingan === []) {
                    break 2;
                }
            }

            // Semua gelanggang penuh, sisa pertandingan tidak dialokasikan di batch ini.
            if (! $adaProgress) {
                break;
            }
        }

        return $pertandinganPerGelanggang;
    }

    private function persistPertandinganPerGelanggang(array $pertandinganPerGelanggang, array $dataIdGelanggang, string $tanggal, string $jamMulai, string $jamSelesai, string $keterangan, array $partaiTerakhirGelanggang): array
    {
        $arrayIdJadwalTanding = [];
        $detailCount = 0;

        foreach ($dataIdGelanggang as $idGelanggang) {
            $this->db->table('jadwal_tanding')->insert([
                'id_gelanggang' => $idGelanggang,
                'tanggal' => $tanggal,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'keterangan' => $keterangan,
            ]);

            $idJadwalTanding = (int) $this->db->insertID();
            if ($idJadwalTanding <= 0) {
                return ['status' => false, 'message' => 'Gagal insert jadwal_tanding.'];
            }

            $arrayIdJadwalTanding[] = $idJadwalTanding;

            foreach ($pertandinganPerGelanggang[$idGelanggang] ?? [] as $pertandingan) {
                $idPertandingan = (int) $pertandingan['id_pertandingan'];

                $ok = $this->db->table('detail_jadwal_tanding')->insert([
                    'id_jadwal_tanding' => $idJadwalTanding,
                    'nomor_partai' => $partaiTerakhirGelanggang[$idGelanggang]++,
                    'id_pertandingan' => $idPertandingan,
                ]);

                if (! $ok) {
                    return ['status' => false, 'message' => 'Gagal insert detail_jadwal_tanding.'];
                }

                // Parity CI3: tugaskan perangkat juri setelah pertandingan masuk jadwal.
                // NOTE: butuh data perangkat_pertandingan posisi=juri di gelanggang ini.
                if (! $this->penilaianTandingService->tugaskanWasitJuri($idPertandingan, $idGelanggang)) {
                    return ['status' => false, 'message' => 'Gagal menugaskan wasit/juri untuk pertandingan: ' . $idPertandingan];
                }

                $detailCount++;
            }
        }

        return [
            'status' => true,
            'message' => sprintf('Generate jadwal tanding otomatis berhasil. Jadwal: %d, Detail: %d', count($arrayIdJadwalTanding), $detailCount),
            'jadwal_ids' => $arrayIdJadwalTanding,
            'detail_count' => $detailCount,
        ];
    }

    private function persistPaketPerGelanggang(array $paketPerGelanggang, array $dataIdGelanggang, string $tanggal, string $jamMulai, string $jamSelesai, string $keterangan, array $partaiTerakhirGelanggang): array
    {
        $arrayIdJadwalTanding = [];
        $detailCount = 0;

        foreach ($dataIdGelanggang as $idGelanggang) {
            $this->db->table('jadwal_tanding')->insert([
                'id_gelanggang' => $idGelanggang,
                'tanggal' => $tanggal,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'keterangan' => $keterangan,
            ]);

            $idJadwalTanding = (int) $this->db->insertID();
            if ($idJadwalTanding <= 0) {
                return ['status' => false, 'message' => 'Gagal insert jadwal_tanding.'];
            }

            $arrayIdJadwalTanding[] = $idJadwalTanding;

            foreach ($paketPerGelanggang[$idGelanggang] ?? [] as $paket) {
                foreach ($paket as $pertandingan) {
                    $idPertandingan = (int) $pertandingan['id_pertandingan'];

                    $ok = $this->db->table('detail_jadwal_tanding')->insert([
                        'id_jadwal_tanding' => $idJadwalTanding,
                        'nomor_partai' => $partaiTerakhirGelanggang[$idGelanggang]++,
                        'id_pertandingan' => $idPertandingan,
                    ]);

                    if (! $ok) {
                        return ['status' => false, 'message' => 'Gagal insert detail_jadwal_tanding.'];
                    }

                    if (! $this->penilaianTandingService->tugaskanWasitJuri($idPertandingan, $idGelanggang)) {
                        return ['status' => false, 'message' => 'Gagal menugaskan wasit/juri untuk pertandingan: ' . $idPertandingan];
                    }

                    $detailCount++;
                }
            }
        }

        return [
            'status' => true,
            'message' => sprintf('Generate jadwal tanding otomatis berhasil. Jadwal: %d, Detail: %d', count($arrayIdJadwalTanding), $detailCount),
            'jadwal_ids' => $arrayIdJadwalTanding,
            'detail_count' => $detailCount,
        ];
    }

    private function alokasiPaketKeGelanggang(array $arrayPaketPertandingan, array $dataIdGelanggang, array $jumlahPartai): array
    {
        $paketPerGelanggang = [];
        $jumlahPaket = count($arrayPaketPertandingan);
        reset($dataIdGelanggang);

        while ($jumlahPaket > 0) {
            $idGelanggang = current($dataIdGelanggang);
            if ($idGelanggang === false) {
                reset($dataIdGelanggang);
                $idGelanggang = current($dataIdGelanggang);
            }

            if (! isset($paketPerGelanggang[$idGelanggang])) {
                $paketPerGelanggang[$idGelanggang] = [];
            }

            $banyakPartai = $this->hitungJumlahPartai($paketPerGelanggang[$idGelanggang]);
            $jumlahAlokasi = (int) ($jumlahPartai[$idGelanggang] ?? 0);

            if ($banyakPartai < $jumlahAlokasi) {
                $dataPertandingan = array_shift($arrayPaketPertandingan);
                if ($dataPertandingan === null) {
                    break;
                }

                $paketPerGelanggang[$idGelanggang][] = $dataPertandingan;

                if (next($dataIdGelanggang) === false) {
                    reset($dataIdGelanggang);
                }

                $jumlahPaket--;
            } else {
                // parity CI3: cari gelanggang lain yang masih punya kapasitas.
                reset($dataIdGelanggang);
                foreach ($dataIdGelanggang as $candidate) {
                    if (! isset($paketPerGelanggang[$candidate])) {
                        $paketPerGelanggang[$candidate] = [];
                    }

                    $banyakPartaiCandidate = $this->hitungJumlahPartai($paketPerGelanggang[$candidate]);
                    $kapasitasSisa = ((int) ($jumlahPartai[$candidate] ?? 0)) - $banyakPartaiCandidate;
                    if ($kapasitasSisa > 0) {
                        current($dataIdGelanggang);
                        break;
                    }
                    next($dataIdGelanggang);
                }

                // bila semua penuh, limpahkan sisa paket ke gelanggang pertama.
                if (current($dataIdGelanggang) === false) {
                    reset($dataIdGelanggang);
                }
            }
        }

        return $paketPerGelanggang;
    }

    private function cekPertandinganTerinput(array $arrayIdPertandingan): bool
    {
        if ($arrayIdPertandingan === []) {
            return true;
        }

        $rows = $this->db->table('detail_jadwal_tanding')
            ->select('id_pertandingan')
            ->whereIn('id_pertandingan', $arrayIdPertandingan)
            ->get()
            ->getResultArray();

        return count($rows) === 0;
    }

    private function getArrayPartaiTerakhirGelanggang(array $gelanggangIds): array
    {
        $out = [];
        foreach ($gelanggangIds as $idGelanggang) {
            $row = $this->db->table('detail_jadwal_tanding djt')
                ->select('MAX(djt.nomor_partai) AS nomor_partai_terakhir')
                ->join('jadwal_tanding jt', 'jt.id_jadwal_tanding = djt.id_jadwal_tanding')
                ->where('jt.id_gelanggang', $idGelanggang)
                ->get()
                ->getRowArray();
            $out[$idGelanggang] = ((int) ($row['nomor_partai_terakhir'] ?? 0)) + 1;
        }
        return $out;
    }

    private function acakUrutanPertandingan(int $maxDiambil, array $pertandingan): array
    {
        $jumlahDiambil = 0;
        $pertandinganTemp = [];
        $idKompetisiTanding = null;
        $idPaket = 1;

        foreach ($pertandingan as $kPertandingan => $vPertandingan) {
            if ($jumlahDiambil < $maxDiambil) {
                if ((int) $vPertandingan['id_kompetisi_tanding'] === (int) $idKompetisiTanding) {
                    $vPertandingan['id_select'] = $kPertandingan;
                    $vPertandingan['id_paket'] = $idPaket;
                    $pertandinganTemp[] = $vPertandingan;
                } else {
                    $idKompetisiTanding = (int) $vPertandingan['id_kompetisi_tanding'];
                    $vPertandingan['id_select'] = $kPertandingan;
                    $vPertandingan['id_paket'] = $idPaket;
                    $pertandinganTemp[] = $vPertandingan;
                    $jumlahDiambil++;
                }
            } else {
                if ((int) $vPertandingan['id_kompetisi_tanding'] === (int) $idKompetisiTanding) {
                    $vPertandingan['id_select'] = $kPertandingan;
                    $vPertandingan['id_paket'] = $idPaket;
                    $pertandinganTemp[] = $vPertandingan;
                } else {
                    $idKompetisiTanding = (int) $vPertandingan['id_kompetisi_tanding'];
                    usort($pertandinganTemp, static function (array $a, array $b): int {
                        return ($a['nilai_babak'] <=> $b['nilai_babak']);
                    });
                    $min = min(array_column($pertandinganTemp, 'id_select'));
                    foreach ($pertandinganTemp as &$value) {
                        $value['id_select'] = $min++;
                    }
                    unset($value);
                    foreach ($pertandinganTemp as $value) {
                        $pertandingan[$value['id_select']] = $value;
                    }
                    $pertandinganTemp = [];
                    $vPertandingan['id_select'] = $kPertandingan;
                    $vPertandingan['id_paket'] = ++$idPaket;
                    $pertandinganTemp[] = $vPertandingan;
                    $jumlahDiambil = 1;
                }
            }
        }

        return array_values($pertandingan);
    }

    private function kelompokkanPertandinganKeDalamPaket(array $pertandingan): array
    {
        $arrayPaketPertandingan = [];
        $arrayPertandinganTemp = [];
        $idPaket = 1;

        foreach ($pertandingan as $k => $v) {
            if ((int) ($v['id_paket'] ?? 1) === $idPaket) {
                $arrayPertandinganTemp[] = $v;
            } else {
                $arrayPaketPertandingan[] = $arrayPertandinganTemp;
                $arrayPertandinganTemp = [$v];
                $idPaket++;
            }

            if ($k === count($pertandingan) - 1) {
                $arrayPaketPertandingan[] = $arrayPertandinganTemp;
            }
        }

        return $arrayPaketPertandingan;
    }

    private function hitungJumlahPartai(array $kumpulanPaket): int
    {
        $banyakPartai = 0;
        foreach ($kumpulanPaket as $paket) {
            $banyakPartai += is_array($paket) ? count($paket) : 0;
        }
        return $banyakPartai;
    }

    private function normalizeIntArray($value): array
    {
        if (! is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $v) {
            $n = (int) $v;
            if ($n > 0) {
                $out[] = $n;
            }
        }
        return array_values($out);
    }

    private function normalizeStringArray($value): array
    {
        if (! is_array($value)) {
            return [];
        }
        $out = [];
        foreach ($value as $v) {
            $s = trim((string) $v);
            if ($s !== '') {
                $out[] = $s;
            }
        }
        return array_values(array_unique($out));
    }
}
