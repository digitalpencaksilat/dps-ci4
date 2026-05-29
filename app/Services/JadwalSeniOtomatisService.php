<?php

namespace App\Services;

use App\Models\JadwalSeniModel;
use CodeIgniter\Database\BaseConnection;

class JadwalSeniOtomatisService
{
    private BaseConnection $db;
    private PenilaianSeniService $penilaianSeniService;

    public function __construct(?BaseConnection $db = null, ?PenilaianSeniService $penilaianSeniService = null)
    {
        $this->db = $db ?? db_connect();
        $this->penilaianSeniService = $penilaianSeniService ?? new PenilaianSeniService($this->db);
    }

    /**
     * Parity CI3: Penjadwalan_otomatis_seni_model::buat_jadwal_sistem_pool
     */
    public function generatePool(array $pengaturan): array
    {
        try {
            $required = ['tanggal', 'jam_mulai', 'jam_selesai', 'keterangan', 'id_gelanggang', 'jumlah_pool', 'urutan_id_sub_kategori_seni'];
            foreach ($required as $f) {
                if (! array_key_exists($f, $pengaturan)) {
                    return ['status' => false, 'message' => 'Parameter ' . $f . ' tidak ditemukan'];
                }
            }

            $idGelanggang = $this->normalizeIntArray($pengaturan['id_gelanggang']);
            if ($idGelanggang === []) {
                return ['status' => false, 'message' => 'Minimal 1 gelanggang harus dipilih.'];
            }

            $jumlahPool = $this->normalizeIntMap($pengaturan['jumlah_pool']);
            $urutanIdSub = $this->normalizeIntArray($pengaturan['urutan_id_sub_kategori_seni']);
            if ($urutanIdSub === []) {
                return ['status' => false, 'message' => 'Minimal 1 sub kategori seni harus dipilih.'];
            }

            $kompetisi = $this->fetchKompetisiPool($urutanIdSub);
            if ($kompetisi === []) {
                return ['status' => false, 'message' => 'Tidak ada kompetisi seni yang ditemukan'];
            }

            // CI3: total kapasitas pool harus >= jumlah kompetisi.
            $totalKapasitas = 0;
            foreach ($idGelanggang as $gid) {
                $totalKapasitas += (int) ($jumlahPool[$gid] ?? 0);
            }
            if ($totalKapasitas < count($kompetisi)) {
                return ['status' => false, 'message' => 'Kapasitas pool tidak mencukupi untuk semua kompetisi'];
            }

            // Validasi penampilan belum terjadwal.
            $idKompetisi = array_values(array_unique(array_map(static fn ($row): int => (int) $row->id_kompetisi_seni, $kompetisi)));
            $kelompok = $this->db->table('kelompok_peserta_seni')
                ->select('id_kelompok_peserta_seni')
                ->whereIn('id_kompetisi_seni', $idKompetisi)
                ->get()
                ->getResult();

            if ($kelompok === []) {
                return ['status' => false, 'message' => 'Tidak ada kelompok peserta yang ditemukan'];
            }

            $idKelompok = array_values(array_unique(array_map(static fn ($row): int => (int) $row->id_kelompok_peserta_seni, $kelompok)));
            $penampilan = $this->db->table('penampilan_seni')
                ->select('id_penampilan_seni')
                ->whereIn('id_kelompok_peserta_seni', $idKelompok)
                ->get()
                ->getResult();

            $idPenampilan = array_values(array_unique(array_filter(array_map(static fn ($row): int => (int) $row->id_penampilan_seni, $penampilan))));
            if ($idPenampilan !== []) {
                $terpakai = $this->db->table('detail_jadwal_seni')
                    ->select('id_penampilan_seni')
                    ->whereIn('id_penampilan_seni', $idPenampilan)
                    ->countAllResults();

                if ($terpakai > 0) {
                    return ['status' => false, 'message' => 'Penampilan seni telah terjadwal sebelumnya!'];
                }
            }

            $kompetisiPerGelanggang = $this->distributeKompetisiToGelanggang($kompetisi, $idGelanggang, $jumlahPool);

            $jadwalIds = [];
            $detailCount = 0;

            $this->db->transStart();

            $partaiTerakhir = (new JadwalSeniModel())->get_all();
            $partaiByGelanggang = $this->fetchLastNomorPartaiByGelanggang();

            foreach ($idGelanggang as $gid) {
                if (! isset($kompetisiPerGelanggang[$gid]) || $kompetisiPerGelanggang[$gid] === []) {
                    continue;
                }

                $ok = $this->db->table('jadwal_seni')->insert([
                    'id_gelanggang' => $gid,
                    'tanggal' => $pengaturan['tanggal'],
                    'jam_mulai' => $pengaturan['jam_mulai'],
                    'jam_selesai' => $pengaturan['jam_selesai'],
                    'keterangan' => $pengaturan['keterangan'] ?? '',
                ]);
                if (! $ok) {
                    $this->db->transRollback();
                    return ['status' => false, 'message' => 'Gagal insert jadwal_seni.'];
                }

                $idJadwal = (int) $this->db->insertID();
                $jadwalIds[] = $idJadwal;

                foreach ($kompetisiPerGelanggang[$gid] as $komp) {
                    $rowsKelompok = $this->db->table('kelompok_peserta_seni')
                        ->select('id_kelompok_peserta_seni')
                        ->where('id_kompetisi_seni', (int) $komp->id_kompetisi_seni)
                        ->orderBy('nomor_undi', 'ASC')
                        ->get()
                        ->getResult();

                    if ($rowsKelompok === []) {
                        continue;
                    }

                    foreach ($rowsKelompok as $index => $kps) {
                        $idPenampilanSeni = $this->createPenampilanSeniIfMissing((int) $kps->id_kelompok_peserta_seni);
                        if ($idPenampilanSeni <= 0) {
                            $this->db->transRollback();
                            return ['status' => false, 'message' => 'Gagal membuat penampilan_seni.'];
                        }

                        $nomorPartai = (int) ($partaiByGelanggang[$gid] ?? 1);
                        $partaiByGelanggang[$gid] = $nomorPartai + 1;

                        $ok = $this->db->table('detail_jadwal_seni')->insert([
                            'id_jadwal_seni' => $idJadwal,
                            'nomor_partai' => $nomorPartai,
                            'id_penampilan_seni' => $idPenampilanSeni,
                            'nomor_urut' => $index + 1,
                        ]);

                        if (! $ok) {
                            $this->db->transRollback();
                            return ['status' => false, 'message' => 'Gagal insert detail_jadwal_seni (pool).'];
                        }
                        $detailCount++;

                        if (! $this->penilaianSeniService->tugaskanWasitJuri($idPenampilanSeni, $gid)) {
                            $this->db->transRollback();
                            return ['status' => false, 'message' => 'Gagal menugaskan wasit/juri untuk penampilan: ' . $idPenampilanSeni];
                        }

                        $this->db->table('penampilan_seni')
                            ->where('id_penampilan_seni', $idPenampilanSeni)
                            ->update(['status_penampilan' => 'belum_tampil']);
                    }
                }
            }

            $this->db->transComplete();
            if (! $this->db->transStatus()) {
                return ['status' => false, 'message' => 'Gagal generate jadwal (transaksi DB gagal).'];
            }

            return [
                'status' => true,
                'message' => sprintf('Generate jadwal seni pool otomatis berhasil. Jadwal: %d, Detail: %d', count($jadwalIds), $detailCount),
                'jadwal_ids' => $jadwalIds,
            ];
        } catch (\Throwable $e) {
            log_message('error', '[JadwalSeniOtomatisService] pool error: {message}', ['message' => $e->getMessage()]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Parity CI3: Penjadwalan_otomatis_seni_model::buat_jadwal_sistem_battle_prestasi + pemasalan_seling_N
     */
    public function generateBattle(array $pengaturan): array
    {
        try {
            $required = ['tanggal', 'jam_mulai', 'jam_selesai', 'keterangan', 'id_gelanggang', 'jumlah_partai', 'urutan_id_sub_kategori_seni', 'babak_battle_seni', 'jenis_penjadwalan'];
            foreach ($required as $f) {
                if (! array_key_exists($f, $pengaturan)) {
                    return ['status' => false, 'message' => 'Parameter ' . $f . ' tidak ditemukan'];
                }
            }

            $jenis = (string) ($pengaturan['jenis_penjadwalan'] ?? 'prestasi');
            if (! in_array($jenis, ['prestasi', 'pemasalan_seling_1', 'pemasalan_seling_2', 'pemasalan_seling_3'], true)) {
                return ['status' => false, 'message' => 'Jenis penjadwalan tidak valid.'];
            }

            $idGelanggang = $this->normalizeIntArray($pengaturan['id_gelanggang']);
            if ($idGelanggang === []) {
                return ['status' => false, 'message' => 'Minimal 1 gelanggang harus dipilih.'];
            }

            $jumlahPartai = $this->normalizeIntMap($pengaturan['jumlah_partai']);
            $urutanIdSub = $this->normalizeIntArray($pengaturan['urutan_id_sub_kategori_seni']);
            $babak = array_values(array_filter(array_map('strval', (array) $pengaturan['babak_battle_seni'])));

            $battles = $this->fetchBattleRows($urutanIdSub, $babak, $jenis);
            if ($battles === []) {
                return ['status' => false, 'message' => 'Tidak ada data battle yang ditemukan'];
            }

            // Validasi battle belum terjadwal.
            $battleIds = array_values(array_unique(array_map(static fn ($row): int => (int) $row->id_battle_seni, $battles)));
            $already = $this->db->table('detail_jadwal_seni')
                ->whereIn('id_battle_seni', $battleIds)
                ->countAllResults();
            if ($already > 0) {
                return ['status' => false, 'message' => 'Battle seni telah terjadwal sebelumnya!'];
            }

            $battlePerGelanggang = $this->distributeBattleToGelanggang($battles, $idGelanggang, $jumlahPartai);

            $jadwalIds = [];
            $detailCount = 0;

            $this->db->transStart();

            $partaiByGelanggang = $this->fetchLastNomorPartaiByGelanggang();

            foreach ($idGelanggang as $gid) {
                if (! isset($battlePerGelanggang[$gid]) || $battlePerGelanggang[$gid] === []) {
                    continue;
                }

                $ok = $this->db->table('jadwal_seni')->insert([
                    'id_gelanggang' => $gid,
                    'tanggal' => $pengaturan['tanggal'],
                    'jam_mulai' => $pengaturan['jam_mulai'],
                    'jam_selesai' => $pengaturan['jam_selesai'],
                    'keterangan' => $pengaturan['keterangan'] ?? '',
                ]);
                if (! $ok) {
                    $this->db->transRollback();
                    return ['status' => false, 'message' => 'Gagal insert jadwal_seni.'];
                }

                $idJadwal = (int) $this->db->insertID();
                $jadwalIds[] = $idJadwal;

                foreach ($battlePerGelanggang[$gid] as $idx => $battle) {
                    $nomorPartai = (int) ($partaiByGelanggang[$gid] ?? 1);
                    $partaiByGelanggang[$gid] = $nomorPartai + 1;

                    $ok = $this->db->table('detail_jadwal_seni')->insert([
                        'id_jadwal_seni' => $idJadwal,
                        'nomor_partai' => $nomorPartai,
                        'id_battle_seni' => (int) $battle->id_battle_seni,
                        'nomor_urut' => $idx + 1,
                    ]);

                    if (! $ok) {
                        $this->db->transRollback();
                        return ['status' => false, 'message' => 'Gagal insert detail_jadwal_seni (battle).'];
                    }
                    $detailCount++;

                    // Parity CI3: tugaskan juri untuk kedua penampilan di battle.
                    $idBiru = (int) ($battle->id_penampilan_seni_biru ?? 0);
                    $idMerah = (int) ($battle->id_penampilan_seni_merah ?? 0);
                    if ($idBiru > 0 && ! $this->penilaianSeniService->tugaskanWasitJuri($idBiru, $gid)) {
                        $this->db->transRollback();
                        return ['status' => false, 'message' => 'Gagal menugaskan wasit/juri untuk penampilan: ' . $idBiru];
                    }
                    if ($idMerah > 0 && ! $this->penilaianSeniService->tugaskanWasitJuri($idMerah, $gid)) {
                        $this->db->transRollback();
                        return ['status' => false, 'message' => 'Gagal menugaskan wasit/juri untuk penampilan: ' . $idMerah];
                    }
                }
            }

            $this->db->transComplete();
            if (! $this->db->transStatus()) {
                return ['status' => false, 'message' => 'Gagal generate jadwal (transaksi DB gagal).'];
            }

            return [
                'status' => true,
                'message' => sprintf('Generate jadwal seni battle otomatis berhasil. Jadwal: %d, Detail: %d', count($jadwalIds), $detailCount),
                'jadwal_ids' => $jadwalIds,
            ];
        } catch (\Throwable $e) {
            log_message('error', '[JadwalSeniOtomatisService] battle error: {message}', ['message' => $e->getMessage()]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    private function fetchKompetisiPool(array $urutanIdSub): array
    {
        // CI3 memakai FIELD() untuk urutan sub kategori.
        $field = implode(',', array_map(static fn (int $id): string => (string) $id, $urutanIdSub));

        $builder = $this->db->table('kompetisi_seni ks');
        $builder->select('ks.id_kompetisi_seni');
        $builder->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni');
        $builder->whereIn('sks.id_sub_kategori_seni', $urutanIdSub);
        $builder->orderBy("FIELD(sks.id_sub_kategori_seni, {$field})", '', false);

        return $builder->get()->getResult();
    }

    private function fetchBattleRows(array $urutanIdSub, array $babak, string $jenis): array
    {
        if ($urutanIdSub === [] || $babak === []) {
            return [];
        }

        $field = implode(',', array_map(static fn (int $id): string => (string) $id, $urutanIdSub));

        $builder = $this->db->table('battle_seni bs');
        $builder->select('bs.id_battle_seni, bs.id_penampilan_seni_merah, bs.id_penampilan_seni_biru');
        $builder->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = bs.id_kompetisi_seni');
        $builder->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni');
        $builder->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba');
        $builder->whereIn('sks.id_sub_kategori_seni', $urutanIdSub);
        $builder->whereIn('bs.babak', $babak);
        $builder->where('bs.jenis_kemenangan !=', 'BYE');
        if ($jenis === 'prestasi') {
            $builder->where('kl.jenis_perlombaan', 'prestasi');
        }

        // Urut: babak, urutan kategori, pool, nomor_battle (approx parity).
        $builder->orderBy("FIELD(sks.id_sub_kategori_seni, {$field})", '', false);
        $builder->orderBy('ks.nomor_pool', 'ASC');
        $builder->orderBy('bs.nomor_battle', 'ASC');

        return $builder->get()->getResult();
    }

    private function distributeKompetisiToGelanggang(array $kompetisi, array $idGelanggang, array $jumlahPool): array
    {
        $result = [];
        foreach ($idGelanggang as $gid) {
            $result[$gid] = [];
        }

        $queue = $kompetisi;
        $venueIndex = 0;
        $venueCycleNoProgress = 0;

        while ($queue !== []) {
            $gid = $idGelanggang[$venueIndex] ?? null;
            if ($gid === null) {
                $venueIndex = 0;
                continue;
            }

            $cap = (int) ($jumlahPool[$gid] ?? 0);
            if (count($result[$gid]) < $cap) {
                $result[$gid][] = array_shift($queue);
                $venueCycleNoProgress = 0;
            }

            $venueIndex++;
            if ($venueIndex >= count($idGelanggang)) {
                $venueIndex = 0;
                $venueCycleNoProgress++;
                if ($venueCycleNoProgress > 1) {
                    throw new \RuntimeException('Tidak dapat mengalokasikan semua kompetisi: kapasitas pool tidak mencukupi');
                }
            }
        }

        return $result;
    }

    private function distributeBattleToGelanggang(array $battles, array $idGelanggang, array $jumlahPartai): array
    {
        $result = [];
        foreach ($idGelanggang as $gid) {
            $result[$gid] = [];
        }

        $queue = $battles;
        $venueIndex = 0;
        $venueCycleNoProgress = 0;

        while ($queue !== []) {
            $gid = $idGelanggang[$venueIndex] ?? null;
            if ($gid === null) {
                $venueIndex = 0;
                continue;
            }

            $cap = (int) ($jumlahPartai[$gid] ?? 0);
            if (count($result[$gid]) < $cap) {
                $result[$gid][] = array_shift($queue);
                $venueCycleNoProgress = 0;
            }

            $venueIndex++;
            if ($venueIndex >= count($idGelanggang)) {
                $venueIndex = 0;
                $venueCycleNoProgress++;
                if ($venueCycleNoProgress > 1) {
                    throw new \RuntimeException('Tidak dapat mengalokasikan semua battle: kapasitas tidak mencukupi');
                }
            }
        }

        return $result;
    }

    private function createPenampilanSeniIfMissing(int $idKelompokPesertaSeni): int
    {
        $existing = $this->db->table('penampilan_seni')
            ->select('id_penampilan_seni')
            ->where('id_kelompok_peserta_seni', $idKelompokPesertaSeni)
            ->get()
            ->getRow();

        if ($existing !== null) {
            return (int) $existing->id_penampilan_seni;
        }

        $ok = $this->db->table('penampilan_seni')->insert([
            'id_kelompok_peserta_seni' => $idKelompokPesertaSeni,
            'status_penampilan' => null,
            'nilai_akhir' => null,
            'waktu_tampil' => null,
            'catatan_nilai_sama' => null,
        ]);

        return $ok ? (int) $this->db->insertID() : 0;
    }

    private function fetchLastNomorPartaiByGelanggang(): array
    {
        $rows = $this->db->table('detail_jadwal_seni djs')
            ->select('js.id_gelanggang, MAX(djs.nomor_partai) as max_partai')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->groupBy('js.id_gelanggang')
            ->get()
            ->getResult();

        $out = [];
        foreach ($rows as $row) {
            $gid = (int) ($row->id_gelanggang ?? 0);
            if ($gid <= 0) {
                continue;
            }
            $out[$gid] = ((int) ($row->max_partai ?? 0)) + 1;
        }

        return $out;
    }

    private function normalizeIntArray($value): array
    {
        $arr = is_array($value) ? $value : [$value];
        $ids = [];
        foreach ($arr as $v) {
            $id = (int) $v;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        return array_values(array_unique($ids));
    }

    private function normalizeIntMap($value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $k => $v) {
            $key = (int) $k;
            if ($key <= 0) {
                continue;
            }
            $out[$key] = (int) $v;
        }
        return $out;
    }
}
