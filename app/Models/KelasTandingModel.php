<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasTandingModel extends Model
{
    protected $table = 'kelas_tanding';
    protected $primaryKey = 'id_kelas_tanding';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kategori_lomba', 'label', 'berat_minimal', 'berat_maksimal', 'juara_tiga_bersama', 'jumlah_ronde', 'waktu_per_ronde', 'waktu_istirahat', 'format_penilaian', 'biaya_pendaftaran_dn', 'biaya_pendaftaran_ln', 'keterangan'];

    public function distribusikan_peserta_tanding(int $id_kelas_tanding, string $mode = 'prestasi'): bool
    {
        $db = db_connect();
        $db->transStart();

        try {
            $this->otomatis_menambahkan_pool($id_kelas_tanding);
            $dataKompetisiTanding = $this->getPoolsByKelas($id_kelas_tanding);

            if ($dataKompetisiTanding === []) {
                log_message('debug', 'Tidak perlu distribusi karena tidak ada pool di kelas tanding {id}', ['id' => $id_kelas_tanding]);
                $db->transComplete();
                return true;
            }

            if ($mode === 'komposisi_seimbang') {
                $this->distribusi_komposisi_seimbang($dataKompetisiTanding, $id_kelas_tanding);
            } elseif ($mode === 'komposisi_lengkap') {
                $this->distribusi_komposisi_lengkap($dataKompetisiTanding, $id_kelas_tanding);
            } elseif ($mode !== 'prestasi') {
                $this->distribusi_pemasalan($dataKompetisiTanding, $id_kelas_tanding);
            } else {
                $this->distribusi_prestasi($dataKompetisiTanding, $id_kelas_tanding);
            }

            $this->pisahkan_atlet_bertemu_kontingen_sendiri($id_kelas_tanding);
            $this->distribukan_peserta_tanding_tanpa_lawan($id_kelas_tanding);
            $this->delete_kompetisi_tanding_kosong($id_kelas_tanding);

            log_message('debug', 'Selesai melakukan distribusi peserta tanding di kelas tanding {id}', ['id' => $id_kelas_tanding]);
            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        if (! $db->transStatus()) {
            throw new \RuntimeException('Gagal distribusi peserta tanding.');
        }

        return true;
    }

    public function otomatis_menambahkan_pool(int $id_kelas_tanding, ?int $max_peserta = null, string $keterangan = ' ')
    {
        $kelas = $this->getKelasDetail($id_kelas_tanding);
        if ($kelas === null) {
            throw new \RuntimeException('Kelas tanding tidak ditemukan.');
        }

        $pools = $this->getPoolsByKelas($id_kelas_tanding);
        if (($kelas->jenis_perlombaan ?? null) === 'pemasalan') {
            $kapasitas = (int) $this->getTotalCapacityByKelas($id_kelas_tanding);
            $jumlahPeserta = (int) $this->countPesertaByKelas($id_kelas_tanding);

            if ($pools === []) {
                return $this->createPool($id_kelas_tanding, 1, $max_peserta ?? 4, $keterangan);
            }

            while ($jumlahPeserta >= $kapasitas) {
                $last = end($pools);
                $nextPool = $this->createPool($id_kelas_tanding, ((int) $last->nomor_pool) + 1, $max_peserta ?? ((int) $last->max_peserta ?: 4), $keterangan);
                $pools[] = (object) [
                    'id_kompetisi_tanding' => $nextPool,
                    'id_kelas_tanding' => $id_kelas_tanding,
                    'nomor_pool' => ((int) $last->nomor_pool) + 1,
                    'max_peserta' => $max_peserta ?? ((int) $last->max_peserta ?: 4),
                ];
                $kapasitas += $max_peserta ?? ((int) $last->max_peserta ?: 4);
            }

            return true;
        }

        if ($pools === []) {
            return $this->createPool($id_kelas_tanding, 1, $max_peserta ?? 16, 'Terinput otomatis oleh sistem');
        }

        return true;
    }

    public function pisahkan_atlet_bertemu_kontingen_sendiri(int $id_kelas_tanding): bool
    {
        $semuaKompetisiTanding = $this->getPoolsByKelas($id_kelas_tanding);
        $bermasalah = $this->getPesertaByKelas($id_kelas_tanding, null, null, 'jumlah_peserta_tanding_kontingen_sama > 1');

        foreach ($bermasalah as $peserta) {
            $pesertaFresh = (new PesertaTandingModel())->findDetailed((int) $peserta->id_peserta_tanding);
            if ($pesertaFresh === null) {
                continue;
            }

            $statusPenukaranAtas = false;
            foreach ($semuaKompetisiTanding as $pool) {
                if ((int) $pool->nomor_pool <= (int) $pesertaFresh->nomor_pool) {
                    continue;
                }

                $idPeserta2 = $this->cek_boleh_tukar_peserta_tanding($pesertaFresh, (int) $pool->id_kompetisi_tanding, (int) $pesertaFresh->id_kontingen);
                if ($idPeserta2 !== null) {
                    $this->swapPesertaPool((int) $pesertaFresh->id_peserta_tanding, (int) $idPeserta2, (int) $pool->id_kompetisi_tanding, (int) $pesertaFresh->id_kompetisi_tanding);
                    $statusPenukaranAtas = true;
                    break;
                }
            }

            if (! $statusPenukaranAtas) {
                for ($i = count($semuaKompetisiTanding) - 1; $i >= 0; $i--) {
                    $pool = $semuaKompetisiTanding[$i];
                    $idPeserta2 = $this->cek_boleh_tukar_peserta_tanding($pesertaFresh, (int) $pool->id_kompetisi_tanding, (int) $pesertaFresh->id_kontingen);
                    if ($idPeserta2 !== null) {
                        $this->swapPesertaPool((int) $pesertaFresh->id_peserta_tanding, (int) $idPeserta2, (int) $pool->id_kompetisi_tanding, (int) $pesertaFresh->id_kompetisi_tanding);
                        break;
                    }
                }
            }
        }

        return true;
    }

    public function cek_boleh_tukar_peserta_tanding(object $peserta_tanding_bermasalah, int $id_kompetisi_tanding, int $id_kontingen): ?int
    {
        $pesertaPadaPoolTujuan = $this->getPesertaByPool($id_kompetisi_tanding);
        $pesertaPadaPoolAsal = $this->getPesertaByPool((int) $peserta_tanding_bermasalah->id_kompetisi_tanding);

        foreach ($pesertaPadaPoolTujuan as $peserta) {
            if ((int) $peserta->id_kontingen === (int) $peserta_tanding_bermasalah->id_kontingen || abs((float) $peserta->berat_badan - (float) $peserta_tanding_bermasalah->berat_badan) > 4) {
                return null;
            }
        }

        $idPeserta2 = null;
        foreach ($pesertaPadaPoolTujuan as $pesertaTujuan) {
            foreach ($pesertaPadaPoolAsal as $pesertaAsal) {
                if ((int) $pesertaTujuan->id_kontingen === (int) $pesertaAsal->id_kontingen || abs((float) $pesertaTujuan->berat_badan - (float) $pesertaAsal->berat_badan) > 4) {
                    $idPeserta2 = null;
                    break;
                }
                $idPeserta2 = (int) $pesertaTujuan->id_peserta_tanding;
            }
        }

        return $idPeserta2;
    }

    public function delete_kompetisi_tanding_kosong(int $id_kelas_tanding): bool
    {
        $poolKosong = $this->getPoolsByKelas($id_kelas_tanding, 'jumlah_peserta_tanding = 0');
        if ($poolKosong === []) {
            return true;
        }

        db_connect()->table('kompetisi_tanding')->whereIn('id_kompetisi_tanding', array_map(static fn ($row) => (int) $row->id_kompetisi_tanding, $poolKosong))->delete();
        return true;
    }

    public function distribukan_peserta_tanding_tanpa_lawan(int $id_kelas_tanding, bool $otomatis_pindah_kelas = false, int $toleransi_berat_badan = 3): bool
    {
        $pool = $this->getPoolSatuAtletByKelas($id_kelas_tanding);
        if ($pool === null) {
            return true;
        }

        if ((int) $pool->nomor_pool > 1) {
            $atletTanpaLawan = $this->getSinglePesertaByPool((int) $pool->id_kompetisi_tanding);
            if ($atletTanpaLawan === null) {
                return true;
            }

            for ($n = ((int) $pool->nomor_pool) - 1; $n >= 1; $n--) {
                $poolSebelumnya = $this->getPoolByNomor($id_kelas_tanding, $n);
                if ($poolSebelumnya === null) {
                    continue;
                }

                if ((int) $poolSebelumnya->jumlah_peserta > 2) {
                    $atletDipindah = $this->getPesertaKandidatPindah((int) $poolSebelumnya->id_kompetisi_tanding, (int) $atletTanpaLawan->id_kontingen, (int) round(((int) $poolSebelumnya->jumlah_peserta) / 2));
                    if ($atletDipindah !== []) {
                        foreach ($atletDipindah as $value) {
                            $this->assignPesertaToPool((int) $value->id_peserta_tanding, (int) $pool->id_kompetisi_tanding);
                        }
                        return true;
                    }
                } elseif ((int) $poolSebelumnya->jumlah_peserta === 2 && (int) $poolSebelumnya->max_peserta > 2) {
                    $pesertaPoolSebelumnya = $this->getPesertaByPool((int) $poolSebelumnya->id_kompetisi_tanding);
                    $bolehGabung = true;
                    foreach ($pesertaPoolSebelumnya as $p) {
                        if ((int) $p->id_kontingen === (int) $atletTanpaLawan->id_kontingen) {
                            $bolehGabung = false;
                            break;
                        }
                    }

                    if ($bolehGabung) {
                        $this->assignPesertaToPool((int) $atletTanpaLawan->id_peserta_tanding, (int) $poolSebelumnya->id_kompetisi_tanding);
                        return true;
                    }
                }
            }
        } elseif ($otomatis_pindah_kelas) {
            $this->otomatisPindahKelasPesertaTanpaLawan($pool, $toleransi_berat_badan);
        }

        return true;
    }

    private function distribusi_pemasalan(array $dataKompetisiTanding, int $id_kelas_tanding): void
    {
        $dataPeserta = $this->getPesertaByKelas($id_kelas_tanding, 'p.berat_badan ASC');
        $dataKompetisiTanding = $this->filterPoolsByCapacity($dataKompetisiTanding, count($dataPeserta));
        $index = 0;

        foreach ($dataKompetisiTanding as $pool) {
            for ($i = 0; $i < (int) $pool->max_peserta; $i++) {
                if (! isset($dataPeserta[$index])) {
                    break 2;
                }
                $this->assignPesertaToPool((int) $dataPeserta[$index]->id_peserta_tanding, (int) $pool->id_kompetisi_tanding);
                $index++;
            }
        }
    }

    private function distribusi_prestasi(array $dataKompetisiTanding, int $id_kelas_tanding): void
    {
        $dataPeserta = $this->getPesertaByKelas($id_kelas_tanding, 'k.nama_kontingen ASC, p.berat_badan ASC');
        $jumlahPeserta = count($dataPeserta);
        $dataKompetisiTanding = $this->filterPoolsByCapacity($dataKompetisiTanding, $jumlahPeserta);
        $index = 0;

        while ($index < $jumlahPeserta) {
            foreach ($dataKompetisiTanding as $pool) {
                if (! isset($dataPeserta[$index])) {
                    break 2;
                }
                $this->assignPesertaToPool((int) $dataPeserta[$index]->id_peserta_tanding, (int) $pool->id_kompetisi_tanding);
                $index++;
            }
        }
    }

    private function distribusi_komposisi_seimbang(array $dataKompetisiTanding, int $id_kelas_tanding): void
    {
        $dataPeserta = $this->getPesertaByKelas($id_kelas_tanding, 'p.berat_badan ASC, p.tinggi_badan ASC');
        $this->distribusiKomposisiBase($dataKompetisiTanding, $dataPeserta);
    }

    private function distribusi_komposisi_lengkap(array $dataKompetisiTanding, int $id_kelas_tanding): void
    {
        $dataPeserta = $this->getPesertaByKelas($id_kelas_tanding, 'p.berat_badan ASC, p.tinggi_badan ASC, p.tanggal_lahir ASC');
        $this->distribusiKomposisiBase($dataKompetisiTanding, $dataPeserta);
    }

    private function distribusiKomposisiBase(array $dataKompetisiTanding, array $dataPeserta): void
    {
        if ($dataPeserta === []) {
            return;
        }

        $pools = $this->filterPoolsByCapacity($dataKompetisiTanding, count($dataPeserta));
        $antrian = array_values($dataPeserta);
        $total = count($antrian);
        $idx = 0;

        foreach ($pools as $pool) {
            $kontingenDiPool = [];
            for ($slot = 0; $slot < (int) $pool->max_peserta; $slot++) {
                if ($idx >= $total) {
                    break 2;
                }

                $peserta = $antrian[$idx];
                $idKontingen = (int) $peserta->id_kontingen;
                if (in_array($idKontingen, $kontingenDiPool, true)) {
                    $swapIdx = null;
                    for ($j = $idx + 1; $j < $total; $j++) {
                        if (! in_array((int) $antrian[$j]->id_kontingen, $kontingenDiPool, true)) {
                            $swapIdx = $j;
                            break;
                        }
                    }
                    if ($swapIdx !== null) {
                        $tmp = $antrian[$idx];
                        $antrian[$idx] = $antrian[$swapIdx];
                        $antrian[$swapIdx] = $tmp;
                        $peserta = $antrian[$idx];
                        $idKontingen = (int) $peserta->id_kontingen;
                    }
                }

                $this->assignPesertaToPool((int) $peserta->id_peserta_tanding, (int) $pool->id_kompetisi_tanding);
                $kontingenDiPool[] = $idKontingen;
                $idx++;
            }
        }
    }

    private function getKelasDetail(int $id_kelas_tanding): ?object
    {
        return db_connect()->table('kelas_tanding kt')
            ->select('kt.*, kl.jenis_perlombaan')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt JOIN kompetisi_tanding k ON k.id_kompetisi_tanding = pt.id_kompetisi_tanding WHERE k.id_kelas_tanding = kt.id_kelas_tanding) AS jumlah_peserta_tanding', false)
            ->select('(SELECT COALESCE(SUM(k.max_peserta), 0) FROM kompetisi_tanding k WHERE k.id_kelas_tanding = kt.id_kelas_tanding) AS max_peserta', false)
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->where('kt.id_kelas_tanding', $id_kelas_tanding)
            ->get()->getRow();
    }

    private function getPoolsByKelas(int $id_kelas_tanding, ?string $having = null): array
    {
        $query = db_connect()->table('kompetisi_tanding kom')
            ->select('kom.*')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta_tanding', false)
            ->where('kom.id_kelas_tanding', $id_kelas_tanding)
            ->orderBy('kom.nomor_pool', 'ASC');

        if ($having !== null) {
            $query->having($having, null, false);
        }

        return $query->get()->getResult();
    }

    private function getPoolByNomor(int $id_kelas_tanding, int $nomor_pool): ?object
    {
        return db_connect()->table('kompetisi_tanding kom')
            ->select('kom.*')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta', false)
            ->where('kom.id_kelas_tanding', $id_kelas_tanding)
            ->where('kom.nomor_pool', $nomor_pool)
            ->get()->getRow();
    }

    private function getPoolSatuAtletByKelas(int $id_kelas_tanding): ?object
    {
        return db_connect()->table('kompetisi_tanding kom')
            ->select('kom.id_kompetisi_tanding, kom.nomor_pool, kom.max_peserta, kt.label, kt.id_kelas_tanding, kt.id_kategori_lomba, kt.berat_maksimal')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta', false)
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->where('kt.id_kelas_tanding', $id_kelas_tanding)
            ->having('jumlah_peserta = 1', null, false)
            ->get()->getRow();
    }

    private function getPesertaByKelas(int $id_kelas_tanding, ?string $orderBy = null, ?int $limit = null, ?string $having = null): array
    {
        $query = db_connect()->table('peserta_tanding pt')
            ->select('pt.*')
            ->select('p.id_kontingen, p.berat_badan, p.tinggi_badan, p.tanggal_lahir')
            ->select('k.nama_kontingen')
            ->select('kom.nomor_pool, kom.max_peserta')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt2 JOIN pendaftar p2 ON p2.id_pendaftar = pt2.id_pendaftar WHERE pt2.id_kompetisi_tanding = pt.id_kompetisi_tanding AND p2.id_kontingen = p.id_kontingen) AS jumlah_peserta_tanding_kontingen_sama', false)
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->where('kom.id_kelas_tanding', $id_kelas_tanding);

        if ($having !== null) {
            $query->having($having, null, false);
        }
        if ($orderBy !== null) {
            foreach (explode(',', $orderBy) as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                $pieces = preg_split('/\s+/', $part);
                $query->orderBy($pieces[0], strtoupper($pieces[1] ?? 'ASC'));
            }
        }
        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get()->getResult();
    }

    private function getPesertaByPool(int $id_kompetisi_tanding): array
    {
        return db_connect()->table('peserta_tanding pt')
            ->select('pt.*, p.id_kontingen, p.berat_badan, p.tinggi_badan')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->where('pt.id_kompetisi_tanding', $id_kompetisi_tanding)
            ->get()->getResult();
    }

    private function getSinglePesertaByPool(int $id_kompetisi_tanding): ?object
    {
        return db_connect()->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding, p.tinggi_badan, p.id_kontingen')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->where('pt.id_kompetisi_tanding', $id_kompetisi_tanding)
            ->get()->getRow();
    }

    private function getPesertaKandidatPindah(int $id_kompetisi_tanding, int $id_kontingen_hindari, int $limit): array
    {
        return db_connect()->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->where('pt.id_kompetisi_tanding', $id_kompetisi_tanding)
            ->where('p.id_kontingen !=', $id_kontingen_hindari)
            ->orderBy('p.berat_badan', 'DESC')
            ->orderBy('p.tinggi_badan', 'DESC')
            ->limit($limit)
            ->get()->getResult();
    }

    private function otomatisPindahKelasPesertaTanpaLawan(object $pool, int $toleransi_berat_badan): void
    {
        $dataAtletKelasLain = $this->getKandidatAtletKelasLain($pool, $toleransi_berat_badan, true);
        if ($dataAtletKelasLain === []) {
            $dataAtletKelasLain = $this->getKandidatAtletKelasLain($pool, $toleransi_berat_badan, false);
        }

        $atletTanpaLawan = db_connect()->table('peserta_tanding')->where('id_kompetisi_tanding', $pool->id_kompetisi_tanding)->get()->getRow();
        if ($atletTanpaLawan === null) {
            return;
        }

        $beratTerdekat = null;
        $kelasTujuan = null;
        $idKompetisiTujuan = null;
        foreach ($dataAtletKelasLain as $atlet) {
            if ($beratTerdekat === null || abs((float) $pool->berat_maksimal - (float) $beratTerdekat) > abs((float) $atlet->berat_badan - (float) $pool->berat_maksimal)) {
                $beratTerdekat = $atlet->berat_badan;
                $kelasTujuan = $atlet->label;
                $idKompetisiTujuan = $atlet->id_kompetisi_tanding;
            }
        }

        if ($idKompetisiTujuan !== null) {
            db_connect()->table('peserta_tanding')->where('id_peserta_tanding', $atletTanpaLawan->id_peserta_tanding)->update([
                'id_kompetisi_tanding' => $idKompetisiTujuan,
                'keterangan' => 'System : otomatis dipindahkan dari kelas ' . $pool->label . ' ke kelas ' . $kelasTujuan,
            ]);
        }
    }

    private function getKandidatAtletKelasLain(object $pool, int $toleransi_berat_badan, bool $havingSatuPeserta): array
    {
        $query = db_connect()->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding, p.nama_pendaftar, p.berat_badan, kom.id_kompetisi_tanding, kt.label')
            ->select('(SELECT COUNT(*) FROM peserta_tanding x WHERE x.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta', false)
            ->join('kompetisi_tanding kom', 'pt.id_kompetisi_tanding = kom.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->where('kt.id_kategori_lomba', $pool->id_kategori_lomba)
            ->where('kt.berat_maksimal <', ((float) $pool->berat_maksimal + $toleransi_berat_badan))
            ->where('kt.berat_maksimal >', ((float) $pool->berat_maksimal - $toleransi_berat_badan))
            ->where('kom.id_kompetisi_tanding !=', $pool->id_kompetisi_tanding);

        if ($havingSatuPeserta) {
            $query->having('jumlah_peserta = 1', null, false);
        }

        return $query->get()->getResult();
    }

    private function filterPoolsByCapacity(array $pools, int $jumlahPeserta): array
    {
        $rekapitulasi = 0;
        $used = [];
        foreach ($pools as $pool) {
            if ($rekapitulasi < $jumlahPeserta) {
                $rekapitulasi += (int) $pool->max_peserta;
                $used[] = $pool;
            }
        }
        return $used;
    }

    private function countPesertaByKelas(int $id_kelas_tanding): int
    {
        return (int) db_connect()->table('peserta_tanding pt')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->where('kom.id_kelas_tanding', $id_kelas_tanding)
            ->countAllResults();
    }

    private function getTotalCapacityByKelas(int $id_kelas_tanding): int
    {
        return (int) (db_connect()->table('kompetisi_tanding')->select('COALESCE(SUM(max_peserta),0) AS total', false)->where('id_kelas_tanding', $id_kelas_tanding)->get()->getRow()->total ?? 0);
    }

    private function createPool(int $id_kelas_tanding, int $nomor_pool, int $max_peserta, string $keterangan): int
    {
        $db = db_connect();
        $db->table('kompetisi_tanding')->insert([
            'id_kelas_tanding' => $id_kelas_tanding,
            'nomor_pool' => $nomor_pool,
            'max_peserta' => $max_peserta,
            'bagan_pertandingan' => '{}',
            'perhitungan_medali' => 1,
            'keterangan' => $keterangan,
        ]);

        return (int) $db->insertID();
    }

    private function assignPesertaToPool(int $id_peserta_tanding, int $id_kompetisi_tanding): void
    {
        db_connect()->table('peserta_tanding')->where('id_peserta_tanding', $id_peserta_tanding)->update(['id_kompetisi_tanding' => $id_kompetisi_tanding]);
    }

    private function swapPesertaPool(int $idPeserta1, int $idPeserta2, int $poolTujuan, int $poolAsal): void
    {
        $db = db_connect();
        $db->table('peserta_tanding')->where('id_peserta_tanding', $idPeserta1)->update(['id_kompetisi_tanding' => $poolTujuan]);
        $db->table('peserta_tanding')->where('id_peserta_tanding', $idPeserta2)->update(['id_kompetisi_tanding' => $poolAsal]);
    }
}
