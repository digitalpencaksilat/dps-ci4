<?php

namespace App\Services;

class SistemGugurTunggalService
{
    private const MODE_FORMULA = 'formula';
    private const MODE_FULL_RANDOM_PERSILAT = 'full_random_persilat';
    private const KAPASITAS_TEMPLATE_PERSILAT = 20;
    private const PLACEHOLDER_NOFLAG = 'noflag';

    public function acakBaganTanding(int $idKompetisiTanding, string $mode = self::MODE_FULL_RANDOM_PERSILAT): array
    {
        $db = db_connect();
        $db->transStart();

        try {
            $this->assertNoScheduledRows('pertandingan', 'id_pertandingan', 'detail_jadwal_tanding', $idKompetisiTanding, 'id_kompetisi_tanding');
            $peserta = $this->getPesertaTanding($idKompetisiTanding, $mode);

            if (count($peserta) < 2) {
                throw new \RuntimeException('Minimal 2 peserta diperlukan untuk membuat bagan tanding.');
            }

            $standarPersilat = $mode === self::MODE_FULL_RANDOM_PERSILAT;
            $generated = $this->generateBracketDanDataPertandinganTanding($peserta, 1, $standarPersilat);

            $db->table('pertandingan')->where('id_kompetisi_tanding', $idKompetisiTanding)->delete();
            foreach ($generated['data_pertandingan'] as $match) {
                $db->table('pertandingan')->insert($match + [
                    'id_kompetisi_tanding' => $idKompetisiTanding,
                    'keterangan' => '',
                ]);
            }

            foreach ($this->flattenTeams($generated['bagan']['teams'], 'id_peserta_tanding') as $slot => $idPesertaTanding) {
                $db->table('peserta_tanding')->where('id_peserta_tanding', $idPesertaTanding)->update(['nomor_bagan' => $slot]);
            }

            $db->table('kompetisi_tanding')->where('id_kompetisi_tanding', $idKompetisiTanding)->update([
                'bagan_pertandingan' => json_encode($generated['bagan']),
            ]);

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        if (! $db->transStatus()) {
            throw new \RuntimeException('Gagal membuat bagan tanding.');
        }

        return ['jumlah_peserta' => count($peserta), 'jumlah_pertandingan' => count($generated['data_pertandingan']), 'bagan' => $generated['bagan']];
    }

    public function acakBaganBattleSeni(int $idKompetisiSeni, string $mode = self::MODE_FULL_RANDOM_PERSILAT): array
    {
        $db = db_connect();
        $db->transStart();

        try {
            $this->assertNoScheduledRows('battle_seni', 'id_battle_seni', 'detail_jadwal_seni', $idKompetisiSeni, 'id_kompetisi_seni');
            $kelompok = $this->getKelompokSeni($idKompetisiSeni, $mode);

            if (count($kelompok) < 2) {
                throw new \RuntimeException('Minimal 2 kelompok diperlukan untuk membuat bagan battle seni.');
            }

            $standarPersilat = $mode === self::MODE_FULL_RANDOM_PERSILAT;
            $generated = $this->generateBracketDanDataBattle($kelompok, 1, $standarPersilat);

            $oldPenampilanIds = $db->table('penampilan_seni ps')
                ->select('ps.id_penampilan_seni')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->where('kps.id_kompetisi_seni', $idKompetisiSeni)
                ->get()->getResultArray();

            $db->table('battle_seni')->where('id_kompetisi_seni', $idKompetisiSeni)->delete();
            $ids = array_column($oldPenampilanIds, 'id_penampilan_seni');
            if ($ids !== []) {
                $db->table('penampilan_seni')->whereIn('id_penampilan_seni', $ids)->delete();
            }

            $penampilanIds = [];
            foreach ($kelompok as $row) {
                $db->table('penampilan_seni')->insert([
                    'id_kelompok_peserta_seni' => $row['id_kelompok_peserta_seni'],
                    'status_penampilan' => 'belum_tampil',
                ]);
                $penampilanIds[(int) $row['id_kelompok_peserta_seni']] = (int) $db->insertID();
            }

            foreach ($generated['data_battle_seni'] as $battle) {
                $winner = $battle['id_kelompok_peserta_seni_pemenang'];
                $db->table('battle_seni')->insert([
                    'id_kompetisi_seni' => $idKompetisiSeni,
                    'id_penampilan_seni_merah' => $this->mapPenampilanId($battle['id_kelompok_peserta_seni_merah'], $penampilanIds),
                    'id_penampilan_seni_biru' => $this->mapPenampilanId($battle['id_kelompok_peserta_seni_biru'], $penampilanIds),
                    'id_pemenang' => $this->mapPenampilanId($winner, $penampilanIds),
                    'babak' => $battle['babak'],
                    'nomor_battle' => $battle['nomor_battle'],
                    'nomor_battle_selanjutnya' => $battle['nomor_battle_selanjutnya'],
                    'jenis_kemenangan' => $battle['jenis_kemenangan'],
                    'keterangan' => '',
                ]);
            }

            foreach ($this->flattenTeams($generated['bagan']['teams'], 'id_kelompok_peserta_seni') as $slot => $idKelompok) {
                $db->table('kelompok_peserta_seni')->where('id_kelompok_peserta_seni', $idKelompok)->update(['nomor_undi' => $slot]);
            }

            $db->table('kompetisi_seni')->where('id_kompetisi_seni', $idKompetisiSeni)->update([
                'bagan_battle_seni' => json_encode($generated['bagan']),
            ]);

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        if (! $db->transStatus()) {
            throw new \RuntimeException('Gagal membuat bagan battle seni.');
        }

        return ['jumlah_kelompok' => count($kelompok), 'jumlah_battle' => count($generated['data_battle_seni']), 'bagan' => $generated['bagan']];
    }

    public function generateBaganTandingDariJadwal(int $idKompetisiTanding): array
    {
        $db = db_connect();
        $kompetisi = $db->table('kompetisi_tanding')->where('id_kompetisi_tanding', $idKompetisiTanding)->get()->getRowArray();
        if ($kompetisi === null) {
            throw new \RuntimeException('Kompetisi tanding tidak ditemukan.');
        }

        $matches = $this->getPertandinganExisting($idKompetisiTanding);
        if ($matches === []) {
            throw new \RuntimeException('Tidak ada pertandingan existing untuk kompetisi ini.');
        }

        $participants = $this->participantsFromExistingMatches($matches, 'tanding');
        if (count($participants) < 2) {
            throw new \RuntimeException('Minimal 2 peserta diperlukan untuk membuat bagan dari jadwal.');
        }

        $generated = $this->generateBracketDanDataPertandinganTanding($participants, (int) ($kompetisi['juara_tiga_bersama'] ?? 1), true);
        $bagan = $this->mergePertandinganExistingKeBagan($generated['bagan'], $matches);

        $db->table('kompetisi_tanding')->where('id_kompetisi_tanding', $idKompetisiTanding)->update([
            'bagan_pertandingan' => json_encode($bagan),
        ]);

        return ['jumlah_peserta' => count($participants), 'jumlah_pertandingan' => count($matches), 'bagan' => $bagan];
    }

    public function generateBaganBattleSeniDariJadwal(int $idKompetisiSeni): array
    {
        $db = db_connect();
        $kompetisi = $db->table('kompetisi_seni')->where('id_kompetisi_seni', $idKompetisiSeni)->get()->getRowArray();
        if ($kompetisi === null) {
            throw new \RuntimeException('Kompetisi seni tidak ditemukan.');
        }

        $battles = $this->getBattleExisting($idKompetisiSeni);
        if ($battles === []) {
            throw new \RuntimeException('Tidak ada battle existing untuk kompetisi ini.');
        }

        $participants = $this->participantsFromExistingMatches($battles, 'battle');
        if (count($participants) < 2) {
            throw new \RuntimeException('Minimal 2 kelompok diperlukan untuk membuat bagan battle dari jadwal.');
        }

        $generated = $this->generateBracketDanDataBattle($participants, (int) ($kompetisi['juara_tiga_bersama'] ?? 1), true);
        $bagan = $this->mergeBattleExistingKeBagan($generated['bagan'], $battles);

        $db->table('kompetisi_seni')->where('id_kompetisi_seni', $idKompetisiSeni)->update([
            'bagan_battle_seni' => json_encode($bagan),
        ]);

        return ['jumlah_kelompok' => count($participants), 'jumlah_battle' => count($battles), 'bagan' => $bagan];
    }

    public function getTemplateBagan(int $jumlahPertandinganAwal, int $jumlahPeserta, bool $encode = true): array|string
    {
        $bracket = $jumlahPeserta > 0 && $jumlahPeserta <= self::KAPASITAS_TEMPLATE_PERSILAT
            ? $this->createTemplateDariStandarPersilat($jumlahPertandinganAwal, $jumlahPeserta)
            : $this->createTemplateBracket($jumlahPertandinganAwal, $jumlahPeserta);

        return $encode ? json_encode($bracket) : $bracket;
    }

    public function generateBracketDanDataPertandinganTanding(array $dataPesertaTanding, int $juaraTigaBersama = 1, bool $standarPersilat = false): array
    {
        $jumlahPeserta = count($dataPesertaTanding);
        $jumlahAwal = $this->hitungPertandinganAwal($jumlahPeserta);
        $arrayPeserta = $this->createArrayPeserta($jumlahAwal);
        $bracket = $standarPersilat && $jumlahPeserta <= self::KAPASITAS_TEMPLATE_PERSILAT
            ? $this->createTemplateDariStandarPersilat($jumlahAwal, $jumlahPeserta)
            : $this->createTemplateBracket($jumlahAwal, $jumlahPeserta);

        $this->isiTemplateBracketTanding($bracket, $arrayPeserta, $dataPesertaTanding, $standarPersilat);
        $dataPertandingan = $this->buildDataRows($bracket, $arrayPeserta, $juaraTigaBersama, 'pertandingan');

        return ['bracket' => json_encode($bracket), 'bagan' => $bracket, 'data_pertandingan' => $dataPertandingan];
    }

    public function generateBracketDanDataBattle(array $dataKelompokPesertaSeni, int $juaraTigaBersama = 1, bool $standarPersilat = false): array
    {
        $jumlahPeserta = count($dataKelompokPesertaSeni);
        $jumlahAwal = $this->hitungPertandinganAwal($jumlahPeserta);
        $arrayPeserta = $this->createArrayPeserta($jumlahAwal);
        $bracket = $standarPersilat && $jumlahPeserta <= self::KAPASITAS_TEMPLATE_PERSILAT
            ? $this->createTemplateDariStandarPersilat($jumlahAwal, $jumlahPeserta)
            : $this->createTemplateBracket($jumlahAwal, $jumlahPeserta);

        $this->isiTemplateBracketBattle($bracket, $arrayPeserta, $dataKelompokPesertaSeni, $standarPersilat);
        $dataBattle = $this->buildDataRows($bracket, $arrayPeserta, $juaraTigaBersama, 'battle');

        return ['bracket' => json_encode($bracket), 'bagan' => $bracket, 'data_battle_seni' => $dataBattle];
    }

    public function hitungPertandinganAwal(int $jumlahPeserta): int
    {
        for ($i = 1; $i <= 7; $i++) {
            if (2 ** $i >= $jumlahPeserta) {
                return (int) ((2 ** $i) / 2);
            }
        }

        throw new \RuntimeException('Jumlah peserta melebihi kapasitas bagan.');
    }

    public function getBabak(int $babak): string
    {
        if ($babak === 1) {
            return 'Final';
        }

        if ($babak === 2) {
            return 'Semi Final';
        }

        return '1/' . $babak . ' Final';
    }

    public function createArrayPeserta(int $jumlahPertandinganAwal): array
    {
        return array_fill(0, $jumlahPertandinganAwal, [null, null]);
    }

    public function createTemplateDariStandarPersilat(int $jumlahPertandinganAwal, int $jumlahPeserta): array
    {
        $path = FCPATH . 'assets/bracket-pertandingan/template_persilat.json';
        if (! is_file($path)) {
            throw new \RuntimeException('Template Persilat tidak ditemukan.');
        }

        $template = json_decode((string) file_get_contents($path), true);
        if (! isset($template[(string) $jumlahPeserta])) {
            throw new \RuntimeException('Template Persilat untuk jumlah peserta ini tidak tersedia.');
        }

        return [
            'teams' => $this->transformTemplateTeam($template[(string) $jumlahPeserta]),
            'results' => $this->createResultsTemplate($jumlahPertandinganAwal),
        ];
    }

    public function createTemplateBracket(int $jumlahPertandinganAwal, int $jumlahPeserta = 0): array
    {
        $bracket = ['teams' => array_fill(0, $jumlahPertandinganAwal, [null, null]), 'results' => $this->createResultsTemplate($jumlahPertandinganAwal)];
        $jumlahIdeal = $this->hitungPertandinganAwal($jumlahPeserta) * 2;
        $indexPeserta = 0;
        $indexPengurutanSlot = 1;

        for ($sudut = 0; $sudut <= 1; $sudut++) {
            for ($j = 0; $j < $jumlahPertandinganAwal / 2; $j++) {
                $this->fillTemplateSlot($bracket, $j, $sudut, $indexPeserta, $jumlahPeserta, $jumlahIdeal, $indexPengurutanSlot);
                $indexBlokBawah = (int) (($jumlahPertandinganAwal / 2) + $j);
                if ($indexBlokBawah >= 1) {
                    $this->fillTemplateSlot($bracket, $indexBlokBawah, $sudut, $indexPeserta, $jumlahPeserta, $jumlahIdeal, $indexPengurutanSlot);
                }
            }
        }

        return $bracket;
    }

    public function transformTemplateTeam(array $originalArray): array
    {
        $result = [];
        foreach ($originalArray as $row) {
            $transformedRow = [];
            foreach ($row as $item) {
                $transformedRow[] = $item === null ? null : [
                    'nomor_slot' => (int) $item,
                    'id_pendaftar' => null,
                    'id_peserta_tanding' => null,
                    'id_kelompok_peserta_seni' => null,
                    'id_kontingen' => null,
                    'nama_pendaftar' => 'Number ' . $item,
                    'anggota_kelompok_peserta_seni' => 'Number ' . $item,
                    'nama_kontingen' => 'Team ' . $item,
                    'negara' => 'Team ' . $item,
                    'url_bendera' => $this->flagUrl(self::PLACEHOLDER_NOFLAG),
                ];
            }
            $result[] = $transformedRow;
        }

        return $result;
    }

    public function isiTemplateBracketTanding(array &$bracket, array &$arrayPeserta, array $dataPesertaTanding, bool $modePersilat = false): void
    {
        $this->isiTemplateBracket($bracket, $arrayPeserta, $dataPesertaTanding, $modePersilat, 'tanding');
    }

    public function isiTemplateBracketBattle(array &$bracket, array &$arrayPeserta, array $dataKelompokPesertaSeni, bool $modePersilat = false): void
    {
        $this->isiTemplateBracket($bracket, $arrayPeserta, $dataKelompokPesertaSeni, $modePersilat, 'battle');
    }

    private function getPesertaTanding(int $idKompetisiTanding, string $mode): array
    {
        $builder = db_connect()->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding, pt.nomor_bagan, p.id_pendaftar, p.nama_pendaftar, p.id_kontingen, k.nama_kontingen, k.negara')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen', 'left')
            ->where('pt.id_kompetisi_tanding', $idKompetisiTanding);

        if ($mode === self::MODE_FORMULA) {
            $builder->orderBy('k.nama_kontingen', 'ASC')->orderBy('p.nama_pendaftar', 'ASC');
            return $builder->get()->getResultArray();
        }

        $rows = $builder->orderBy('RAND()', '', false)->get()->getResultArray();

        return $this->applyTandingMatchFixing($rows);
    }

    private function applyTandingMatchFixing(array $peserta): array
    {
        $fixed = array_fill(0, count($peserta), null);

        foreach ($peserta as $key => $row) {
            $nomorBagan = trim((string) ($row['nomor_bagan'] ?? ''));
            if ($nomorBagan === '' || strpos($nomorBagan, ',') === false) {
                continue;
            }

            $candidateSlots = array_values(array_filter(array_map('trim', explode(',', $nomorBagan)), 'is_numeric'));
            if ($candidateSlots === []) {
                $fixed[$key] = $row;
                continue;
            }

            $slot = ((int) $candidateSlots[array_rand($candidateSlots)]) - 1;
            if (! array_key_exists($slot, $fixed) || $fixed[$slot] !== null) {
                $fixed[$key] = $row;
                continue;
            }

            $fixed[$slot] = $row;
        }

        foreach ($peserta as $row) {
            $nomorBagan = trim((string) ($row['nomor_bagan'] ?? ''));
            if ($nomorBagan !== '' && strpos($nomorBagan, ',') !== false) {
                continue;
            }

            foreach ($fixed as $key => $existing) {
                if ($existing === null) {
                    $fixed[$key] = $row;
                    break;
                }
            }
        }

        return $fixed;
    }

    private function getKelompokSeni(int $idKompetisiSeni, string $mode): array
    {
        $builder = db_connect()->table('kelompok_peserta_seni kps')
            ->select('kps.id_kelompok_peserta_seni, kps.id_kontingen, k.nama_kontingen, k.negara')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni', false)
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->where('kps.id_kompetisi_seni', $idKompetisiSeni);

        if ($mode === self::MODE_FORMULA) {
            $builder->orderBy('k.nama_kontingen', 'ASC')->orderBy('kps.id_kelompok_peserta_seni', 'ASC');
        } else {
            $builder->orderBy('RAND()', '', false);
        }

        return $builder->get()->getResultArray();
    }

    private function isiTemplateBracket(array &$bracket, array &$arrayPeserta, array $participants, bool $modePersilat, string $type): void
    {
        if ($modePersilat) {
            $indexPeserta = 0;
            foreach ($bracket['teams'] as $keyMatch => $match) {
                foreach ($match as $keySudut => $slot) {
                    if ($slot !== null && (int) $slot['nomor_slot'] === $indexPeserta + 1) {
                        $this->placeParticipant($bracket, $arrayPeserta, $participants[$indexPeserta] ?? null, $keyMatch, $keySudut, $type);
                        $indexPeserta++;
                    }
                }
            }
            return;
        }

        $jumlahAwal = count($arrayPeserta);
        $jumlahIdeal = $jumlahAwal * 2;
        $indexPeserta = 0;
        for ($sudut = 0; $sudut <= 1; $sudut++) {
            for ($j = 0; $j < $jumlahAwal / 2; $j++) {
                if ($indexPeserta < $jumlahIdeal) {
                    $this->placeParticipant($bracket, $arrayPeserta, $participants[$indexPeserta] ?? null, $j, $sudut, $type);
                    $indexPeserta++;
                }

                $indexBlokBawah = (int) (($jumlahAwal / 2) + $j);
                if ($indexPeserta < $jumlahIdeal && $indexBlokBawah >= 1) {
                    $this->placeParticipant($bracket, $arrayPeserta, $participants[$indexPeserta] ?? null, $indexBlokBawah, $sudut, $type);
                    $indexPeserta++;
                }
            }
        }
    }

    private function buildDataRows(array &$bracket, array $arrayPeserta, int $juaraTigaBersama, string $type): array
    {
        $jumlahAwal = count($arrayPeserta);
        $data = [];
        $roundIndex = 0;
        $number = 1;

        for ($i = $jumlahAwal; $i >= 1; $i /= 2) {
            for ($match = 0; $match < $i; $match++) {
                $bracket['results'][0][$roundIndex][$match] = [null, null, ' Match ' . $number . ' (' . $this->getBabak((int) $i) . ')'];
                $blue = $this->getIdAtlet($number, 0, $jumlahAwal, $arrayPeserta);
                $red = $this->getIdAtlet($number, 1, $jumlahAwal, $arrayPeserta);
                $winner = null;
                $jenisKemenangan = 'TBD';

                if ($red !== null && $blue === null) {
                    $winner = $red;
                    $jenisKemenangan = 'BYE';
                } elseif ($red === null && $blue !== null) {
                    $winner = $blue;
                    $jenisKemenangan = 'BYE';
                } elseif ($red === null && $blue === null && $number > $jumlahAwal) {
                    $idMatch1 = (($number - $jumlahAwal) * 2) - 2;
                    $idMatch2 = (($number - $jumlahAwal) * 2) - 1;
                    $blue = $this->getWinnerFromRow($data[$idMatch1] ?? [], $type);
                    $red = $this->getWinnerFromRow($data[$idMatch2] ?? [], $type);
                }

                $nextNumber = $i == 1 ? null : $jumlahAwal + (int) ceil($number / 2);
                $data[] = $this->formatRow($type, $number, $nextNumber, $blue, $red, $winner, $this->getBabak((int) $i), $jenisKemenangan);
                $number++;

                if ($juaraTigaBersama === 0 && $i == 1 && $jumlahAwal * 2 > 3) {
                    $data[] = $this->formatRow($type, $number, $nextNumber, $blue, $red, $winner, 'Perebutan Juara Tiga', $jenisKemenangan);
                    $bracket['results'][0][$roundIndex][1] = [null, null, ' Match ' . $number . ' (Perebutan Juara Tiga)'];
                }
            }
            $roundIndex++;
        }

        return $data;
    }

    private function formatRow(string $type, int $number, ?int $nextNumber, ?int $blue, ?int $red, ?int $winner, string $babak, string $jenisKemenangan): array
    {
        if ($type === 'battle') {
            return [
                'nomor_battle' => $number,
                'nomor_battle_selanjutnya' => $nextNumber,
                'id_kelompok_peserta_seni_merah' => $red,
                'id_kelompok_peserta_seni_biru' => $blue,
                'babak' => $babak,
                'jenis_kemenangan' => $jenisKemenangan,
                'id_kelompok_peserta_seni_pemenang' => $winner,
            ];
        }

        return [
            'nomor_pertandingan' => $number,
            'nomor_pertandingan_selanjutnya' => $nextNumber,
            'id_atlet_merah' => $red,
            'id_atlet_biru' => $blue,
            'babak' => $babak,
            'jenis_kemenangan' => $jenisKemenangan,
            'id_pemenang' => $winner,
        ];
    }

    private function placeParticipant(array &$bracket, array &$arrayPeserta, ?array $participant, int $match, int $corner, string $type): void
    {
        if ($participant === null) {
            $arrayPeserta[$match][$corner] = null;
            $bracket['teams'][$match][$corner] = null;
            return;
        }

        if ($type === 'battle') {
            $id = (int) $participant['id_kelompok_peserta_seni'];
            $arrayPeserta[$match][$corner] = $id;
            $bracket['teams'][$match][$corner] = [
                'id_kelompok_peserta_seni' => $id,
                'id_kontingen' => isset($participant['id_kontingen']) ? (int) $participant['id_kontingen'] : null,
                'anggota_kelompok_peserta_seni' => (string) ($participant['anggota_kelompok_peserta_seni'] ?? '-'),
                'nama_kontingen' => (string) ($participant['nama_kontingen'] ?? '-'),
                'negara' => (string) ($participant['negara'] ?? 'noflag'),
                'url_bendera' => $this->flagUrl((string) ($participant['negara'] ?? 'noflag')),
            ];
            return;
        }

        $id = (int) $participant['id_peserta_tanding'];
        $arrayPeserta[$match][$corner] = $id;
        $bracket['teams'][$match][$corner] = [
            'id_pendaftar' => isset($participant['id_pendaftar']) ? (int) $participant['id_pendaftar'] : null,
            'id_peserta_tanding' => $id,
            'id_kontingen' => isset($participant['id_kontingen']) ? (int) $participant['id_kontingen'] : null,
            'nama_pendaftar' => (string) ($participant['nama_pendaftar'] ?? '-'),
            'nama_kontingen' => (string) ($participant['nama_kontingen'] ?? '-'),
            'negara' => (string) ($participant['negara'] ?? 'noflag'),
            'url_bendera' => $this->flagUrl((string) ($participant['negara'] ?? 'noflag')),
        ];
    }

    private function fillTemplateSlot(array &$bracket, int $match, int $corner, int &$indexPeserta, int $jumlahPeserta, int $jumlahIdeal, int &$slotNumber): void
    {
        if ($indexPeserta >= $jumlahIdeal) {
            return;
        }

        $bracket['teams'][$match][$corner] = $indexPeserta < $jumlahPeserta ? [
            'nomor_slot' => $slotNumber,
            'id_pendaftar' => null,
            'id_peserta_tanding' => null,
            'id_kelompok_peserta_seni' => null,
            'id_kontingen' => null,
            'nama_pendaftar' => 'Number ' . $slotNumber . ' (#' . ($indexPeserta + 1) . ')',
            'anggota_kelompok_peserta_seni' => 'Number ' . $slotNumber . ' (#' . ($indexPeserta + 1) . ')',
            'nama_kontingen' => 'Team ' . $slotNumber . ' (#' . ($indexPeserta + 1) . ')',
        ] : [
            'id_pendaftar' => null,
            'id_peserta_tanding' => null,
            'id_kelompok_peserta_seni' => null,
            'id_kontingen' => null,
            'nama_pendaftar' => 'BYE',
            'anggota_kelompok_peserta_seni' => 'BYE',
            'nama_kontingen' => 'BYE',
        ];

        $indexPeserta++;
        $slotNumber++;
    }

    private function createResultsTemplate(int $jumlahPertandinganAwal): array
    {
        $results = [[]];
        $roundIndex = 0;
        for ($i = $jumlahPertandinganAwal; $i >= 1; $i /= 2) {
            $results[0][$roundIndex] = array_fill(0, (int) $i, [null, null]);
            $roundIndex++;
        }

        return $results;
    }

    private function getIdAtlet(int $nomorPertandingan, int $sudut, int $jumlahPertandinganAwal, array $arrayPeserta): ?int
    {
        return $nomorPertandingan <= $jumlahPertandinganAwal ? $arrayPeserta[$nomorPertandingan - 1][$sudut] : null;
    }

    private function getWinnerFromRow(array $row, string $type): ?int
    {
        return $type === 'battle' ? ($row['id_kelompok_peserta_seni_pemenang'] ?? null) : ($row['id_pemenang'] ?? null);
    }

    private function mapPenampilanId(?int $idKelompok, array $penampilanIds): ?int
    {
        return $idKelompok === null ? null : ($penampilanIds[$idKelompok] ?? null);
    }

    private function flattenTeams(array $teams, string $key): array
    {
        $slot = 1;
        $result = [];
        foreach ($teams as $match) {
            foreach ($match as $corner) {
                if (is_array($corner) && isset($corner[$key])) {
                    $result[$slot] = (int) $corner[$key];
                }
                $slot++;
            }
        }

        return $result;
    }

    private function getPertandinganExisting(int $idKompetisiTanding): array
    {
        return db_connect()->table('pertandingan p')
            ->select('p.id_pertandingan, p.nomor_pertandingan, p.nomor_pertandingan_selanjutnya, p.id_atlet_merah, p.id_atlet_biru, p.id_pemenang, p.babak, p.jenis_kemenangan')
            ->select('pm.id_pendaftar AS merah_id_pendaftar, pm.nama_pendaftar AS merah_nama_pendaftar, pm.id_kontingen AS merah_id_kontingen, km.nama_kontingen AS merah_nama_kontingen, km.negara AS merah_negara')
            ->select('pb.id_pendaftar AS biru_id_pendaftar, pb.nama_pendaftar AS biru_nama_pendaftar, pb.id_kontingen AS biru_id_kontingen, kb.nama_kontingen AS biru_nama_kontingen, kb.negara AS biru_negara')
            ->join('peserta_tanding ptm', 'ptm.id_peserta_tanding = p.id_atlet_merah', 'left')
            ->join('pendaftar pm', 'pm.id_pendaftar = ptm.id_pendaftar', 'left')
            ->join('kontingen km', 'km.id_kontingen = pm.id_kontingen', 'left')
            ->join('peserta_tanding ptb', 'ptb.id_peserta_tanding = p.id_atlet_biru', 'left')
            ->join('pendaftar pb', 'pb.id_pendaftar = ptb.id_pendaftar', 'left')
            ->join('kontingen kb', 'kb.id_kontingen = pb.id_kontingen', 'left')
            ->where('p.id_kompetisi_tanding', $idKompetisiTanding)
            ->orderBy('p.nomor_pertandingan', 'ASC')
            ->get()->getResultArray();
    }

    private function getBattleExisting(int $idKompetisiSeni): array
    {
        return db_connect()->table('battle_seni bs')
            ->select('bs.id_battle_seni, bs.nomor_battle, bs.nomor_battle_selanjutnya, bs.id_penampilan_seni_merah, bs.id_penampilan_seni_biru, bs.id_pemenang, bs.babak, bs.jenis_kemenangan')
            ->select('psm.id_kelompok_peserta_seni AS merah_id_kelompok, kpsm.id_kontingen AS merah_id_kontingen, km.nama_kontingen AS merah_nama_kontingen, km.negara AS merah_negara')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = psm.id_kelompok_peserta_seni) AS merah_anggota', false)
            ->select('psb.id_kelompok_peserta_seni AS biru_id_kelompok, kpsb.id_kontingen AS biru_id_kontingen, kb.nama_kontingen AS biru_nama_kontingen, kb.negara AS biru_negara')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = psb.id_kelompok_peserta_seni) AS biru_anggota', false)
            ->join('penampilan_seni psm', 'psm.id_penampilan_seni = bs.id_penampilan_seni_merah', 'left')
            ->join('kelompok_peserta_seni kpsm', 'kpsm.id_kelompok_peserta_seni = psm.id_kelompok_peserta_seni', 'left')
            ->join('kontingen km', 'km.id_kontingen = kpsm.id_kontingen', 'left')
            ->join('penampilan_seni psb', 'psb.id_penampilan_seni = bs.id_penampilan_seni_biru', 'left')
            ->join('kelompok_peserta_seni kpsb', 'kpsb.id_kelompok_peserta_seni = psb.id_kelompok_peserta_seni', 'left')
            ->join('kontingen kb', 'kb.id_kontingen = kpsb.id_kontingen', 'left')
            ->where('bs.id_kompetisi_seni', $idKompetisiSeni)
            ->orderBy('bs.nomor_battle', 'ASC')
            ->get()->getResultArray();
    }

    private function participantsFromExistingMatches(array $rows, string $type): array
    {
        $participants = [];
        foreach ($rows as $row) {
            if ($type === 'battle') {
                $this->appendBattleParticipant($participants, $row, 'merah');
                $this->appendBattleParticipant($participants, $row, 'biru');
                continue;
            }

            $this->appendTandingParticipant($participants, $row, 'merah');
            $this->appendTandingParticipant($participants, $row, 'biru');
        }

        return array_values($participants);
    }

    private function appendTandingParticipant(array &$participants, array $row, string $corner): void
    {
        $idPeserta = (int) ($row['id_atlet_' . $corner] ?? 0);
        if ($idPeserta <= 0 || isset($participants[$idPeserta])) {
            return;
        }

        $negara = (string) ($row[$corner . '_negara'] ?? self::PLACEHOLDER_NOFLAG);
        $participants[$idPeserta] = [
            'id_peserta_tanding' => $idPeserta,
            'id_pendaftar' => $row[$corner . '_id_pendaftar'] !== null ? (int) $row[$corner . '_id_pendaftar'] : null,
            'id_kontingen' => $row[$corner . '_id_kontingen'] !== null ? (int) $row[$corner . '_id_kontingen'] : null,
            'nama_pendaftar' => (string) ($row[$corner . '_nama_pendaftar'] ?? '-'),
            'nama_kontingen' => (string) ($row[$corner . '_nama_kontingen'] ?? '-'),
            'negara' => $negara,
            'url_bendera' => $this->flagUrl($negara),
        ];
    }

    private function appendBattleParticipant(array &$participants, array $row, string $corner): void
    {
        $idKelompok = (int) ($row[$corner . '_id_kelompok'] ?? 0);
        if ($idKelompok <= 0 || isset($participants[$idKelompok])) {
            return;
        }

        $negara = (string) ($row[$corner . '_negara'] ?? self::PLACEHOLDER_NOFLAG);
        $participants[$idKelompok] = [
            'id_kelompok_peserta_seni' => $idKelompok,
            'id_kontingen' => $row[$corner . '_id_kontingen'] !== null ? (int) $row[$corner . '_id_kontingen'] : null,
            'anggota_kelompok_peserta_seni' => (string) ($row[$corner . '_anggota'] ?? '-'),
            'nama_kontingen' => (string) ($row[$corner . '_nama_kontingen'] ?? '-'),
            'negara' => $negara,
            'url_bendera' => $this->flagUrl($negara),
        ];
    }

    private function mergePertandinganExistingKeBagan(array $bagan, array $matches): array
    {
        $matchIndex = [];
        foreach ($matches as $row) {
            $matchIndex[(int) $row['nomor_pertandingan']] = $row;
        }

        foreach ($bagan['results'][0] as $roundIndex => $round) {
            foreach ($round as $matchIndexRound => $result) {
                $label = (string) ($result[2] ?? '');
                if (! preg_match('/Match\s+(\d+)/', $label, $matchNumber)) {
                    continue;
                }

                $nomorPertandingan = (int) $matchNumber[1];
                if (! isset($matchIndex[$nomorPertandingan])) {
                    continue;
                }

                $row = $matchIndex[$nomorPertandingan];
                $bagan['results'][0][$roundIndex][$matchIndexRound][0] = $this->resolveSudutPemenang($row['id_pemenang'] ?? null, $row['id_atlet_biru'] ?? null, $row['id_atlet_merah'] ?? null);
                $bagan['results'][0][$roundIndex][$matchIndexRound][1] = $this->labelJenisKemenangan((string) ($row['jenis_kemenangan'] ?? ''));
            }
        }

        return $bagan;
    }

    private function mergeBattleExistingKeBagan(array $bagan, array $battles): array
    {
        $battleIndex = [];
        foreach ($battles as $row) {
            $battleIndex[(int) $row['nomor_battle']] = $row;
        }

        foreach ($bagan['results'][0] as $roundIndex => $round) {
            foreach ($round as $matchIndexRound => $result) {
                $label = (string) ($result[2] ?? '');
                if (! preg_match('/Match\s+(\d+)/', $label, $matchNumber)) {
                    continue;
                }

                $nomorBattle = (int) $matchNumber[1];
                if (! isset($battleIndex[$nomorBattle])) {
                    continue;
                }

                $row = $battleIndex[$nomorBattle];
                $bagan['results'][0][$roundIndex][$matchIndexRound][0] = $this->resolveSudutPemenang($row['id_pemenang'] ?? null, $row['id_penampilan_seni_biru'] ?? null, $row['id_penampilan_seni_merah'] ?? null);
                $bagan['results'][0][$roundIndex][$matchIndexRound][1] = $this->labelJenisKemenangan((string) ($row['jenis_kemenangan'] ?? ''));
            }
        }

        return $bagan;
    }

    private function resolveSudutPemenang($winner, $blue, $red): ?int
    {
        if ($winner === null) {
            return null;
        }

        if ((int) $winner === (int) $blue) {
            return 0;
        }

        if ((int) $winner === (int) $red) {
            return 1;
        }

        return null;
    }

    private function labelJenisKemenangan(string $jenisKemenangan): ?string
    {
        $jenisKemenangan = strtoupper(trim($jenisKemenangan));
        if ($jenisKemenangan === '' || $jenisKemenangan === 'TBD') {
            return null;
        }

        return $jenisKemenangan;
    }

    private function flagUrl(string $negara): string
    {
        return function_exists('bendera') ? (string) \bendera($negara) : '';
    }

    private function assertNoScheduledRows(string $sourceTable, string $sourceKey, string $scheduleTable, int $competitionId, string $competitionKey): void
    {
        $count = db_connect()->table($scheduleTable . ' d')
            ->join($sourceTable . ' s', 's.' . $sourceKey . ' = d.' . $sourceKey)
            ->where('s.' . $competitionKey, $competitionId)
            ->countAllResults();

        if ($count > 0) {
            throw new \RuntimeException('Bagan sudah terhubung jadwal dan tidak dapat diacak ulang.');
        }
    }
}
