<?php

namespace App\Services;

use App\Models\KontingenModel;
use App\Models\KelompokPesertaSeniModel;
use App\Models\PendaftarModel;
use App\Models\PesertaSeniModel;
use App\Models\PesertaTandingModel;

class SekretariatPesertaKontingenService
{
    public function dashboardStats(): array
    {
        $db = db_connect();

        return [
            'kontingen' => (int) $db->table('kontingen')->countAllResults(),
            'pendaftar' => (int) $db->table('pendaftar')->countAllResults(),
            'pesertaTanding' => (int) $db->table('peserta_tanding')->countAllResults(),
            'kelompokSeni' => (int) $db->table('kelompok_peserta_seni')->countAllResults(),
            'kontingenBelumInputPeserta' => (int) $db->table('kontingen k')
                ->join('pendaftar p', 'p.id_kontingen = k.id_kontingen', 'left')
                ->where('p.id_pendaftar IS NULL', null, false)
                ->countAllResults(),
            'pesertaBelumMemilihKategori' => (int) $db->table('pendaftar p')
                ->join('peserta_tanding pt', 'pt.id_pendaftar = p.id_pendaftar', 'left')
                ->join('peserta_seni ps', 'ps.id_pendaftar = p.id_pendaftar', 'left')
                ->where('pt.id_peserta_tanding IS NULL', null, false)
                ->where('ps.id_peserta_seni IS NULL', null, false)
                ->countAllResults(),
        ];
    }

    public function listKontingen(): array
    {
        return (new KontingenModel())
            ->baseSekretariatQuery()
            ->orderBy('k.nama_kontingen', 'ASC')
            ->get()
            ->getResult();
    }

    public function listKontingenForRekapAtlet(): array
    {
        return $this->listKontingen();
    }

    public function getKontingenDetail(int $idKontingen): ?array
    {
        $kontingen = (new KontingenModel())->findWithSummary($idKontingen);
        if ($kontingen === null) {
            return null;
        }

        return [
            'kontingen' => $kontingen,
            'pendaftar' => $this->listPendaftarByKontingen($idKontingen),
            'pesertaTanding' => $this->listPesertaTandingByKontingen($idKontingen),
            'kelompokSeni' => $this->listKelompokSeniByKontingen($idKontingen),
            'pesertaSeni' => $this->listPesertaSeniByKontingen($idKontingen),
            'official' => $this->listOfficialByKontingen($idKontingen),
            'pendaftarTandingOptions' => $this->availablePendaftarForTanding($idKontingen),
            'pendaftarSeniOptions' => $this->availablePendaftarForSeni($idKontingen),
            'kompetisiTandingOptions' => $this->listKompetisiTanding(),
            'kompetisiSeniOptions' => $this->listKompetisiSeni(),
        ];
    }

    public function listPendaftarByKontingen(int $idKontingen): array
    {
        return (new PendaftarModel())
            ->baseSekretariatQuery()
            ->where('p.id_kontingen', $idKontingen)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function listPendaftar(): array
    {
        return (new PendaftarModel())
            ->baseSekretariatQuery()
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function listOfficialByKontingen(int $idKontingen): array
    {
        return db_connect()->table('official o')
            ->select('o.*, k.nama_kontingen')
            ->join('kontingen k', 'k.id_kontingen = o.id_kontingen', 'left')
            ->where('o.id_kontingen', $idKontingen)
            ->orderBy('o.nama_official', 'ASC')
            ->get()
            ->getResult();
    }

    public function listPendaftarForBpjs(): array
    {
        return (new PendaftarModel())
            ->baseSekretariatQuery()
            ->select('k.nomor_telepon_penanggungjawab', false)
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function listPesertaTanding(?int $idKontingen = null): array
    {
        $query = (new PesertaTandingModel())
            ->baseSekretariatQuery()
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC');

        if ($idKontingen !== null) {
            $query->where('p.id_kontingen', $idKontingen);
        }

        return $query->get()->getResult();
    }

    public function listPesertaTandingByKontingen(int $idKontingen): array
    {
        return $this->listPesertaTanding($idKontingen);
    }

    public function getPesertaTandingDetail(int $idPesertaTanding): ?object
    {
        return (new PesertaTandingModel())->findDetailed($idPesertaTanding);
    }

    public function listPoolTandingForPeserta(int $idPesertaTanding): array
    {
        $record = $this->getPesertaTandingDetail($idPesertaTanding);
        if ($record === null || empty($record->id_kelas_tanding)) {
            return [];
        }

        return db_connect()->table('kompetisi_tanding kom')
            ->select([
                'kom.id_kompetisi_tanding',
                'kom.nomor_pool',
                'kom.max_peserta',
                'kt.label',
                'kt.berat_minimal',
                'kt.berat_maksimal',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kl.jenis_perlombaan',
                '(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta_tanding',
            ])
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kom.id_kelas_tanding', (int) $record->id_kelas_tanding)
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()
            ->getResult();
    }

    public function availablePendaftarForTanding(?int $idKontingen = null): array
    {
        $query = db_connect()->table('pendaftar p')
            ->select('p.*, k.nama_kontingen')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('peserta_tanding pt', 'pt.id_pendaftar = p.id_pendaftar', 'left')
            ->where('pt.id_peserta_tanding IS NULL', null, false)
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC');

        if ($idKontingen !== null) {
            $query->where('p.id_kontingen', $idKontingen);
        }

        return $query->get()->getResult();
    }

    public function listKompetisiTanding(): array
    {
        return db_connect()->table('kompetisi_tanding kom')
            ->select([
                'kom.id_kompetisi_tanding',
                'kom.nomor_pool',
                'kom.max_peserta',
                'kt.label',
                'kt.berat_minimal',
                'kt.berat_maksimal',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kl.jenis_perlombaan',
                '(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta_tanding',
            ])
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()
            ->getResult();
    }

    public function getKompetisiTandingByPendaftar(int $idPendaftar, ?int $ignorePesertaTanding = null): array
    {
        $pendaftar = (new PendaftarModel())->find($idPendaftar);
        if ($pendaftar === null) {
            return [];
        }

        $umur = $this->calculateAge($pendaftar->tanggal_lahir ?? null);
        // Settings are stored in DB via super admin module.
        $checkAge = (string) (get_setting('perbolehkan_memilih_kategori_usia') ?? '1') !== '1';
        $checkWeight = (string) (get_setting('perbolehkan_memilih_kelas_tanding') ?? '1') !== '1';
        $allowSameKontingen = (string) (get_setting('perbolehkan_atlet_dari_kontingen_yang_sama') ?? '1') === '1';

        $items = db_connect()->table('kompetisi_tanding kom')
            ->select([
                'kom.id_kompetisi_tanding',
                'kom.id_kelas_tanding',
                'kom.max_peserta',
                'kom.nomor_pool',
                'kt.label',
                'kt.berat_minimal',
                'kt.berat_maksimal',
                'kt.biaya_pendaftaran_dn',
                'kt.biaya_pendaftaran_ln',
                'kl.id_kategori_lomba',
                'kl.kuota_peserta',
                'kl.jenis_perlombaan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'ku.min_umur',
                'ku.max_umur',
                '(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding' . ($ignorePesertaTanding !== null ? ' AND pt.id_peserta_tanding != ' . (int) $ignorePesertaTanding : '') . ') AS jumlah_peserta_tanding',
                '(SELECT COUNT(*) FROM peserta_tanding pt JOIN kompetisi_tanding kom2 ON kom2.id_kompetisi_tanding = pt.id_kompetisi_tanding JOIN kelas_tanding kt2 ON kt2.id_kelas_tanding = kom2.id_kelas_tanding WHERE kt2.id_kategori_lomba = kl.id_kategori_lomba' . ($ignorePesertaTanding !== null ? ' AND pt.id_peserta_tanding != ' . (int) $ignorePesertaTanding : '') . ') AS jumlah_peserta_kategori',
                '(SELECT COUNT(*) FROM peserta_tanding pt JOIN pendaftar p2 ON p2.id_pendaftar = pt.id_pendaftar WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding AND p2.id_kontingen = ' . (int) $pendaftar->id_kontingen . ($ignorePesertaTanding !== null ? ' AND pt.id_peserta_tanding != ' . (int) $ignorePesertaTanding : '') . ') AS jumlah_satu_kontingen',
            ])
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('ku.jenis_kelamin', $pendaftar->jenis_kelamin)
            ->where('LOWER(kt.label) !=', 'sisipan')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()
            ->getResult();

        $byClass = [];
        foreach ($items as $item) {
            if ($checkAge && $umur !== null && ($umur < (int) $item->min_umur || $umur > (int) $item->max_umur)) {
                continue;
            }
            if ($checkWeight && ((float) $pendaftar->berat_badan < (float) $item->berat_minimal || (float) $pendaftar->berat_badan > (float) $item->berat_maksimal)) {
                continue;
            }

            $item->disabled = false;
            $item->message = null;
            if ((int) $item->jumlah_peserta_tanding >= (int) $item->max_peserta) {
                $item->disabled = true;
                $item->message = 'Kuota penuh';
            } elseif ((int) $item->kuota_peserta > 0 && (int) $item->jumlah_peserta_kategori >= (int) $item->kuota_peserta) {
                $item->disabled = true;
                $item->message = 'Kuota kategori penuh';
            } elseif (! $allowSameKontingen && (int) $item->jumlah_satu_kontingen > 0 && strtolower((string) $item->jenis_perlombaan) === 'prestasi') {
                $item->disabled = true;
                $item->message = 'Atlet kontingen ini sudah ada di kelas ini';
            }

            $classId = (int) $item->id_kelas_tanding;
            if (! isset($byClass[$classId]) || ($byClass[$classId]->disabled && ! $item->disabled)) {
                $byClass[$classId] = $item;
            }
        }

        return array_values($byClass);
    }

    public function assertPesertaTandingEligible(int $idPendaftar, int $idKompetisiTanding, ?int $ignorePesertaTanding = null): void
    {
        foreach ($this->getKompetisiTandingByPendaftar($idPendaftar, $ignorePesertaTanding) as $item) {
            if ((int) $item->id_kompetisi_tanding === $idKompetisiTanding) {
                if (! empty($item->disabled)) {
                    throw new \RuntimeException((string) ($item->message ?? 'Kategori tanding tidak tersedia.'));
                }
                return;
            }
        }

        throw new \RuntimeException('Kategori tanding tidak sesuai dengan data atlet.');
    }

    public function createPesertaTanding(array $payload): int
    {
        $model = new PesertaTandingModel();
        $idPendaftar = (int) ($payload['id_pendaftar'] ?? 0);
        $idKompetisi = (int) ($payload['id_kompetisi_tanding'] ?? 0);

        if ((new PendaftarModel())->find($idPendaftar) === null) {
            throw new \RuntimeException('Peserta tidak ditemukan.');
        }

        if ($model->where('id_pendaftar', $idPendaftar)->first() !== null) {
            throw new \RuntimeException('Peserta sudah masuk kategori tanding.');
        }

        $this->assertPesertaTandingEligible($idPendaftar, $idKompetisi);

        $db = db_connect();
        $db->transStart();

        $model->insert([
            'id_pendaftar' => $idPendaftar,
            'id_kompetisi_tanding' => $idKompetisi,
            'id_pembayaran' => null,
            // Optional field (not always present in POST payload).
            'nomor_bagan' => ($payload['nomor_bagan'] ?? '') !== '' ? $payload['nomor_bagan'] : null,
            'keterangan' => trim((string) ($payload['keterangan'] ?? '')),
            'status' => (string) ($payload['status'] ?? 'OK'),
            'status_sertifikat' => 'belum_dicetak',
            'nomor_sertifikat' => null,
        ]);

        $id = (int) $model->getInsertID();
        $kompetisi = $this->getKompetisiTanding($idKompetisi);
        if ($kompetisi !== null && strtolower((string) $kompetisi->jenis_perlombaan) === 'pemasalan') {
            $this->ensureTandingPoolAvailable((int) $kompetisi->id_kelas_tanding);
        }

        $db->transComplete();

        if (! $db->transStatus() || $id <= 0) {
            throw new \RuntimeException('Gagal menambahkan peserta tanding.');
        }

        return $id;
    }

    public function updatePesertaTanding(int $idPesertaTanding, array $payload): bool
    {
        $model = new PesertaTandingModel();
        $record = $model->find($idPesertaTanding);
        if ($record === null) {
            throw new \RuntimeException('Peserta tanding tidak ditemukan.');
        }

        $idKompetisi = (int) ($payload['id_kompetisi_tanding'] ?? 0);
        $this->assertPesertaTandingEligible((int) $record->id_pendaftar, $idKompetisi, $idPesertaTanding);
        $oldKompetisi = $this->getKompetisiTanding((int) $record->id_kompetisi_tanding);
        $newKompetisi = $this->getKompetisiTanding($idKompetisi);
        if ($record->id_pembayaran !== null && $oldKompetisi !== null && $newKompetisi !== null && ((float) $oldKompetisi->biaya_pendaftaran_dn !== (float) $newKompetisi->biaya_pendaftaran_dn || (float) $oldKompetisi->biaya_pendaftaran_ln !== (float) $newKompetisi->biaya_pendaftaran_ln)) {
            throw new \RuntimeException('Kategori berbayar tidak bisa diganti setelah masuk pembayaran.');
        }

        $updated = $model->update($idPesertaTanding, [
            'id_kompetisi_tanding' => $idKompetisi,
            'nomor_bagan' => ($payload['nomor_bagan'] ?? '') !== '' ? $payload['nomor_bagan'] : null,
            'keterangan' => trim((string) ($payload['keterangan'] ?? '')),
            'status' => (string) ($payload['status'] ?? $record->status ?? 'OK'),
        ]);

        if ($newKompetisi !== null && strtolower((string) $newKompetisi->jenis_perlombaan) === 'pemasalan') {
            $this->ensureTandingPoolAvailable((int) $newKompetisi->id_kelas_tanding);
        }

        return $updated;
    }

    public function ensureTandingPoolAvailable(int $idKelasTanding): bool
    {
        $db = db_connect();
        $last = $db->table('kompetisi_tanding')->where('id_kelas_tanding', $idKelasTanding)->orderBy('nomor_pool', 'DESC')->get()->getRowArray();
        if ($last === null) {
            return false;
        }

        $count = (int) $db->table('peserta_tanding')->where('id_kompetisi_tanding', (int) $last['id_kompetisi_tanding'])->countAllResults();
        if ($count < (int) $last['max_peserta']) {
            return true;
        }

        unset($last['id_kompetisi_tanding']);
        $last['nomor_pool'] = ((int) $last['nomor_pool']) + 1;
        $db->table('kompetisi_tanding')->insert($last);
        return true;
    }

    public function deletePesertaTanding(int $idPesertaTanding): bool
    {
        $model = new PesertaTandingModel();
        $record = $model->find($idPesertaTanding);
        if ($record === null) {
            throw new \RuntimeException('Peserta tanding tidak ditemukan.');
        }

        if ($record->id_pembayaran !== null) {
            throw new \RuntimeException('Peserta tanding yang sudah masuk pembayaran tidak bisa dihapus.');
        }

        return $model->delete($idPesertaTanding);
    }

    public function listPesertaSeni(?int $idKontingen = null): array
    {
        $query = (new PesertaSeniModel())
            ->baseSekretariatQuery()
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('ps.id_kelompok_peserta_seni', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC');

        if ($idKontingen !== null) {
            $query->where('p.id_kontingen', $idKontingen);
        }

        return $query->get()->getResult();
    }

    public function listPesertaSeniByKontingen(int $idKontingen): array
    {
        return $this->listPesertaSeni($idKontingen);
    }

    public function getPesertaSeniDetail(int $idPesertaSeni): ?object
    {
        return (new PesertaSeniModel())->findDetailed($idPesertaSeni);
    }

    public function listKelompokSeni(?int $idKontingen = null): array
    {
        $query = (new KelompokPesertaSeniModel())
            ->baseSekretariatQuery()
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('kps.id_kelompok_peserta_seni', 'DESC');

        if ($idKontingen !== null) {
            $query->where('kps.id_kontingen', $idKontingen);
        }

        return $query->get()->getResult();
    }

    public function listKelompokSeniByKontingen(int $idKontingen): array
    {
        return $this->listKelompokSeni($idKontingen);
    }

    public function listNomorSertifikatTanding(): array
    {
        return db_connect()->table('perolehan_medali_tanding pmt')
            ->select([
                'pmt.jenis_medali',
                'pt.id_peserta_tanding',
                'pt.nomor_sertifikat',
                'p.id_pendaftar',
                'p.nama_pendaftar',
                'p.nama_sekolah',
                'k.id_kontingen',
                'k.nama_kontingen',
                'k.provinsi',
                'kom.nomor_pool',
                'kt.label',
                'kt.berat_minimal',
                'kt.berat_maksimal',
                'kl.jenis_perlombaan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
            ])
            ->join('peserta_tanding pt', 'pt.id_peserta_tanding = pmt.id_peserta_tanding')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function listNomorSertifikatSeni(): array
    {
        return db_connect()->table('perolehan_medali_seni pms')
            ->select([
                'pms.jenis_medali',
                'kps.id_kelompok_peserta_seni',
                'k.nama_kontingen',
                'k.provinsi',
                'kom.nomor_pool',
                'sks.nama_seni',
                'sks.jenis_seni',
                'kl.jenis_perlombaan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                '(SELECT GROUP_CONCAT(p2.nama_pendaftar SEPARATOR ", ") FROM pendaftar p2 JOIN peserta_seni ps2 ON ps2.id_pendaftar = p2.id_pendaftar WHERE ps2.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni',
                '(SELECT GROUP_CONCAT(DISTINCT p2.nama_sekolah SEPARATOR ", ") FROM pendaftar p2 JOIN peserta_seni ps2 ON ps2.id_pendaftar = p2.id_pendaftar WHERE ps2.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni AND p2.nama_sekolah IS NOT NULL AND p2.nama_sekolah != "") AS nama_sekolah',
            ], false)
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = pms.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->get()
            ->getResult();
    }

    public function listPesertaSeniForSertifikat(): array
    {
        return db_connect()->table('peserta_seni ps')
            ->select('ps.id_kelompok_peserta_seni, ps.nomor_sertifikat, p.nama_pendaftar')
            ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
            ->orderBy('ps.id_kelompok_peserta_seni', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function getKelompokSeniDetail(int $idKelompok): ?object
    {
        return (new KelompokPesertaSeniModel())->findDetailed($idKelompok);
    }

    public function listPoolSeniForKelompok(int $idKelompok): array
    {
        $record = $this->getKelompokSeniDetail($idKelompok);
        if ($record === null) {
            return [];
        }

        $kompetisi = $this->getKompetisiSeni((int) $record->id_kompetisi_seni);
        if ($kompetisi === null || empty($kompetisi->id_sub_kategori_seni)) {
            return [];
        }

        return db_connect()->table('kompetisi_seni kom')
            ->select([
                'kom.id_kompetisi_seni',
                'kom.id_sub_kategori_seni',
                'kom.nomor_pool',
                'kom.max_peserta',
                'sks.nama_seni',
                'sks.jenis_seni',
                'sks.jumlah_peserta',
                'sks.sistem_penampilan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'ku.min_umur',
                'ku.max_umur',
                'kl.kuota_peserta',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kompetisi_seni = kom.id_kompetisi_seni) AS jumlah_kelompok_peserta_seni',
            ])
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kom.id_sub_kategori_seni', (int) $kompetisi->id_sub_kategori_seni)
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()
            ->getResult();
    }

    public function listKompetisiSeni(): array
    {
        return $this->listKompetisiSeniPendaftaran();
    }

    public function listKompetisiSeniPendaftaran(bool $isAdmin = true, ?array $where = null): array
    {
        $query = db_connect()->table('kompetisi_seni kom')
            ->select([
                'kom.id_kompetisi_seni',
                'kom.id_sub_kategori_seni',
                'kom.nomor_pool',
                'kom.max_peserta',
                'sks.nama_seni',
                'sks.jenis_seni',
                'sks.jumlah_peserta',
                'sks.sistem_penampilan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'ku.min_umur',
                'ku.max_umur',
                'kl.kuota_peserta',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kompetisi_seni = kom.id_kompetisi_seni) AS jumlah_kelompok_peserta_seni',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni kom2 ON kom2.id_kompetisi_seni = kps.id_kompetisi_seni WHERE kom2.id_sub_kategori_seni = sks.id_sub_kategori_seni) AS jumlah_sub_kategori',
            ])
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia');

        if ($where !== null) {
            $query->where($where);
        }

        $items = $query
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()
            ->getResult();

        $bySub = [];
        foreach ($items as $item) {
            $item->disabled = false;
            $item->message = null;
            if ((int) $item->jumlah_kelompok_peserta_seni >= (int) $item->max_peserta) {
                $item->disabled = true;
                $item->message = 'Kuota penuh';
            } elseif ((int) $item->kuota_peserta > 0 && (int) $item->jumlah_sub_kategori >= (int) $item->kuota_peserta) {
                $item->disabled = true;
                $item->message = 'Kuota kategori penuh';
            }

            $subId = (int) $item->id_sub_kategori_seni;
            if (! isset($bySub[$subId]) || ($bySub[$subId]->disabled && ! $item->disabled)) {
                $bySub[$subId] = $item;
            }
        }

        return array_values($bySub);
    }

    public function getPendaftarByKompetisiSeni(int $idKompetisiSeni, int $idKontingen): array
    {
        $kompetisi = $this->getKompetisiSeni($idKompetisiSeni);
        if ($kompetisi === null) {
            return [];
        }

        $checkAge = (string) (get_setting('perbolehkan_memilih_kategori_usia') ?? '1') !== '1';
        $rows = db_connect()->table('pendaftar p')
            ->select('p.*, k.nama_kontingen')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('peserta_seni ps', 'ps.id_pendaftar = p.id_pendaftar', 'left')
            ->where('p.id_kontingen', $idKontingen)
            ->where('p.jenis_kelamin', $kompetisi->jenis_kelamin)
            ->where('ps.id_peserta_seni IS NULL', null, false)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();

        if (! $checkAge) {
            return $rows;
        }

        return array_values(array_filter($rows, function (object $row) use ($kompetisi): bool {
            $umur = $this->calculateAge($row->tanggal_lahir ?? null);
            return $umur === null || ($umur >= (int) $kompetisi->min_umur && $umur <= (int) $kompetisi->max_umur);
        }));
    }

    public function assertKelompokSeniEligible(int $idKontingen, int $idKompetisiSeni, array $idPendaftar): void
    {
        $this->validateSeniMemberCount($idKompetisiSeni, $idPendaftar);

        $available = [];
        foreach ($this->getPendaftarByKompetisiSeni($idKompetisiSeni, $idKontingen) as $row) {
            $available[(int) $row->id_pendaftar] = true;
        }

        foreach ($idPendaftar as $id) {
            if (! isset($available[(int) $id])) {
                throw new \RuntimeException('Atlet seni tidak sesuai kontingen, kategori, atau sudah dipakai.');
            }
        }

        foreach ($this->listKompetisiSeniPendaftaran(true, ['kom.id_kompetisi_seni' => $idKompetisiSeni]) as $item) {
            if (! empty($item->disabled)) {
                throw new \RuntimeException((string) ($item->message ?? 'Kategori seni tidak tersedia.'));
            }
        }
    }

    public function availablePendaftarForSeni(?int $idKontingen = null): array
    {
        $query = db_connect()->table('pendaftar p')
            ->select('p.*, k.nama_kontingen')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('peserta_seni ps', 'ps.id_pendaftar = p.id_pendaftar', 'left')
            ->where('ps.id_peserta_seni IS NULL', null, false)
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC');

        if ($idKontingen !== null) {
            $query->where('p.id_kontingen', $idKontingen);
        }

        return $query->get()->getResult();
    }

    public function createKelompokSeni(array $payload): int
    {
        $idKontingen = (int) ($payload['id_kontingen'] ?? 0);
        $idKompetisi = (int) ($payload['id_kompetisi_seni'] ?? 0);
        $idPendaftar = $this->normalizeIdArray($payload['id_pendaftar'] ?? []);

        if ((new KontingenModel())->find($idKontingen) === null) {
            throw new \RuntimeException('Kontingen tidak ditemukan.');
        }

        $this->assertKelompokSeniEligible($idKontingen, $idKompetisi, $idPendaftar);

        $db = db_connect();
        $db->transStart();

        $model = new KelompokPesertaSeniModel();
        $model->insert([
            'id_kompetisi_seni' => $idKompetisi,
            'id_kontingen' => $idKontingen,
            'id_pembayaran' => null,
            'status' => (string) ($payload['status'] ?? 'ok'),
            'keterangan' => trim((string) ($payload['keterangan'] ?? '')),
            'nomor_undi' => (int) ($payload['nomor_undi'] ?? 0),
        ]);

        $idKelompok = (int) $model->getInsertID();
        foreach ($idPendaftar as $id) {
            $db->table('peserta_seni')->insert([
                'id_pendaftar' => $id,
                'id_kelompok_peserta_seni' => $idKelompok,
                'status_sertifikat' => 'belum_dicetak',
                'nomor_sertifikat' => null,
            ]);
        }

        $kompetisi = $this->getKompetisiSeni($idKompetisi);
        if ($kompetisi !== null) {
            $this->ensureSeniPoolAvailable((int) $kompetisi->id_sub_kategori_seni);
        }

        $db->transComplete();

        if (! $db->transStatus() || $idKelompok <= 0) {
            throw new \RuntimeException('Gagal membuat kelompok seni.');
        }

        return $idKelompok;
    }

    public function updateKelompokSeni(int $idKelompok, array $payload): bool
    {
        $model = new KelompokPesertaSeniModel();
        $record = $model->find($idKelompok);
        if ($record === null) {
            throw new \RuntimeException('Kelompok seni tidak ditemukan.');
        }

        $idKompetisi = (int) ($payload['id_kompetisi_seni'] ?? 0);
        $oldKompetisi = $this->getKompetisiSeni((int) $record->id_kompetisi_seni);
        $newKompetisi = $this->getKompetisiSeni($idKompetisi);
        if ($record->id_pembayaran !== null && $oldKompetisi !== null && $newKompetisi !== null && ((float) $oldKompetisi->biaya_pendaftaran_dn !== (float) $newKompetisi->biaya_pendaftaran_dn || (float) $oldKompetisi->biaya_pendaftaran_ln !== (float) $newKompetisi->biaya_pendaftaran_ln)) {
            throw new \RuntimeException('Kategori berbayar tidak bisa diganti setelah masuk pembayaran.');
        }

        $members = array_map(static fn (object $row): int => (int) $row->id_pendaftar, $this->listPesertaSeniByKelompok($idKelompok));
        $this->validateSeniMemberCount($idKompetisi, $members);

        $updated = $model->update($idKelompok, [
            'id_kompetisi_seni' => $idKompetisi,
            'status' => (string) ($payload['status'] ?? $record->status ?? 'ok'),
            'keterangan' => trim((string) ($payload['keterangan'] ?? '')),
            'nomor_undi' => (int) ($payload['nomor_undi'] ?? $record->nomor_undi ?? 0),
        ]);

        if ($newKompetisi !== null) {
            $this->ensureSeniPoolAvailable((int) $newKompetisi->id_sub_kategori_seni);
        }

        return $updated;
    }

    public function deleteKelompokSeni(int $idKelompok): bool
    {
        $model = new KelompokPesertaSeniModel();
        $record = $model->find($idKelompok);
        if ($record === null) {
            throw new \RuntimeException('Kelompok seni tidak ditemukan.');
        }

        if ($record->id_pembayaran !== null) {
            throw new \RuntimeException('Kelompok seni yang sudah masuk pembayaran tidak bisa dihapus.');
        }

        $db = db_connect();
        $db->transStart();
        $db->table('peserta_seni')->where('id_kelompok_peserta_seni', $idKelompok)->delete();
        $db->table('kelompok_peserta_seni')->where('id_kelompok_peserta_seni', $idKelompok)->delete();
        $db->transComplete();

        return $db->transStatus();
    }

    public function listPesertaSeniByKelompok(int $idKelompok): array
    {
        return (new PesertaSeniModel())
            ->baseSekretariatQuery()
            ->where('ps.id_kelompok_peserta_seni', $idKelompok)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function addPesertaSeni(int $idKelompok, int $idPendaftar): int
    {
        $kelompok = (new KelompokPesertaSeniModel())->find($idKelompok);
        if ($kelompok === null) {
            throw new \RuntimeException('Kelompok seni tidak ditemukan.');
        }

        if ($kelompok->id_pembayaran !== null) {
            throw new \RuntimeException('Kelompok seni yang sudah masuk pembayaran tidak bisa ditambah anggota.');
        }

        $pendaftar = (new PendaftarModel())
            ->where('id_kontingen', $kelompok->id_kontingen)
            ->find($idPendaftar);
        if ($pendaftar === null) {
            throw new \RuntimeException('Peserta tidak ditemukan pada kontingen kelompok ini.');
        }

        $model = new PesertaSeniModel();
        if ($model->where('id_pendaftar', $idPendaftar)->first() !== null) {
            throw new \RuntimeException('Peserta sudah masuk kelompok seni.');
        }

        $available = array_column($this->getPendaftarByKompetisiSeni((int) $kelompok->id_kompetisi_seni, (int) $kelompok->id_kontingen), null, 'id_pendaftar');
        if (! isset($available[$idPendaftar])) {
            throw new \RuntimeException('Atlet seni tidak sesuai kontingen, kategori, atau sudah dipakai.');
        }

        $existing = array_map(static fn (object $row): int => (int) $row->id_pendaftar, $this->listPesertaSeniByKelompok($idKelompok));
        $this->validateSeniMemberCount((int) $kelompok->id_kompetisi_seni, array_merge($existing, [$idPendaftar]));

        $model->insert([
            'id_pendaftar' => $idPendaftar,
            'id_kelompok_peserta_seni' => $idKelompok,
            'status_sertifikat' => 'belum_dicetak',
            'nomor_sertifikat' => null,
        ]);

        $id = (int) $model->getInsertID();
        if ($id <= 0) {
            throw new \RuntimeException('Gagal menambahkan peserta seni.');
        }

        return $id;
    }

    public function deletePesertaSeni(int $idPesertaSeni): bool
    {
        $model = new PesertaSeniModel();
        $record = $model->find($idPesertaSeni);
        if ($record === null) {
            throw new \RuntimeException('Peserta seni tidak ditemukan.');
        }

        $kelompok = (new KelompokPesertaSeniModel())->find((int) $record->id_kelompok_peserta_seni);
        if ($kelompok !== null && $kelompok->id_pembayaran !== null) {
            throw new \RuntimeException('Anggota kelompok yang sudah masuk pembayaran tidak bisa dihapus.');
        }

        return $model->delete($idPesertaSeni);
    }

    public function ensureSeniPoolAvailable(int $idSubKategoriSeni): bool
    {
        $db = db_connect();
        $last = $db->table('kompetisi_seni')->where('id_sub_kategori_seni', $idSubKategoriSeni)->orderBy('nomor_pool', 'DESC')->get()->getRowArray();
        if ($last === null) {
            return false;
        }

        $count = (int) $db->table('kelompok_peserta_seni')->where('id_kompetisi_seni', (int) $last['id_kompetisi_seni'])->countAllResults();
        if ($count < (int) $last['max_peserta']) {
            return true;
        }

        unset($last['id_kompetisi_seni']);
        $last['nomor_pool'] = ((int) $last['nomor_pool']) + 1;
        $db->table('kompetisi_seni')->insert($last);
        return true;
    }

    private function validateSeniMemberCount(int $idKompetisi, array $idPendaftar): void
    {
        $kompetisi = db_connect()->table('kompetisi_seni kom')
            ->select('kom.id_kompetisi_seni, sks.jenis_seni, sks.jumlah_peserta')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->where('kom.id_kompetisi_seni', $idKompetisi)
            ->get()
            ->getRow();

        if ($kompetisi === null) {
            throw new \RuntimeException('Kategori seni tidak ditemukan.');
        }

        $count = count($idPendaftar);
        $required = (int) $kompetisi->jumlah_peserta;
        $strictTypes = ['tunggal', 'ganda', 'beregu', 'solo kreatif', 'perorangan', 'berpasangan', 'berkelompok'];
        $strictMatch = in_array(strtolower((string) $kompetisi->jenis_seni), $strictTypes, true);

        if (($strictMatch && $count !== $required) || (! $strictMatch && $count < $required)) {
            throw new \RuntimeException('Jumlah atlet yang dipilih tidak sesuai kebutuhan kategori seni.');
        }
    }

    private function getKompetisiTanding(int $idKompetisi): ?object
    {
        return db_connect()->table('kompetisi_tanding kom')
            ->select('kom.*, kt.biaya_pendaftaran_dn, kt.biaya_pendaftaran_ln, kl.jenis_perlombaan')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->where('kom.id_kompetisi_tanding', $idKompetisi)
            ->get()
            ->getRow();
    }

    private function getKompetisiSeni(int $idKompetisi): ?object
    {
        return db_connect()->table('kompetisi_seni kom')
            ->select('kom.*, sks.jenis_seni, sks.jumlah_peserta, sks.biaya_pendaftaran_dn, sks.biaya_pendaftaran_ln, ku.jenis_kelamin, ku.min_umur, ku.max_umur')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kom.id_kompetisi_seni', $idKompetisi)
            ->get()
            ->getRow();
    }

    private function calculateAge(?string $birthDate): ?int
    {
        if (! $birthDate) {
            return null;
        }

        try {
            return (new \DateTimeImmutable($birthDate))->diff(new \DateTimeImmutable('today'))->y;
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeIdArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value))));
    }

    public function createKontingen(array $payload): int
    {
        $model = new KontingenModel();
        $email = trim((string) ($payload['email_kontingen'] ?? ''));

        if ($email !== '' && $model->where('email_kontingen', $email)->first() !== null) {
            throw new \RuntimeException('Email kontingen sudah terdaftar.');
        }

        $jenisKontingen = (string) ($payload['jenis_kontingen'] ?? 'dalam_negeri');
        $password = (string) ($payload['password'] ?? '');

        $model->insert([
            'id_pembayaran' => null,
            'nama_kontingen' => trim((string) ($payload['nama_kontingen'] ?? '')),
            'singkatan_nama_kontingen' => trim((string) ($payload['singkatan_nama_kontingen'] ?? '')),
            'jenis_kontingen' => $jenisKontingen,
            'perguruan' => (string) ($payload['perguruan'] ?? 'ipsi'),
            'email_kontingen' => $email,
            'nomor_telepon_kontingen' => trim((string) ($payload['nomor_telepon_kontingen'] ?? '')),
            'alamat_kontingen' => trim((string) ($payload['alamat_kontingen'] ?? '')),
            'username' => trim((string) ($payload['username'] ?? $email)),
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]),
            'nama_penanggungjawab' => trim((string) ($payload['nama_penanggungjawab'] ?? '')),
            'jabatan_penanggungjawab' => trim((string) ($payload['jabatan_penanggungjawab'] ?? '')),
            'nomor_telepon_penanggungjawab' => trim((string) ($payload['nomor_telepon_penanggungjawab'] ?? '')),
            'negara' => $jenisKontingen === 'dalam_negeri' ? 'indonesia' : trim((string) ($payload['negara'] ?? '')),
            'provinsi' => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['provinsi'] ?? '')) : null,
            'kabupaten_kota' => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kabupaten_kota'] ?? '')) : null,
            'kecamatan' => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kecamatan'] ?? '')) : null,
            'kelurahan' => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kelurahan'] ?? '')) : null,
            'alamat_lengkap' => trim((string) ($payload['alamat_lengkap'] ?? '')),
            'alamat_penanggungjawab' => trim((string) ($payload['alamat_penanggungjawab'] ?? '')),
            'keterangan' => trim((string) ($payload['keterangan'] ?? '')),
            'pembayaran_dn' => (int) (get_setting('biaya_pendaftaran_kontingen_dalam_negeri') ?? 0),
            'pembayaran_ln' => (int) (get_setting('biaya_pendaftaran_kontingen_luar_negeri') ?? 0),
            'status_data' => 'belum_final',
            'jenis_pendaftaran' => 'input_admin',
        ]);

        $id = (int) $model->getInsertID();
        if ($id <= 0) {
            throw new \RuntimeException('Gagal membuat kontingen.');
        }

        return $id;
    }

    public function updateKontingen(int $idKontingen, array $payload): bool
    {
        $model = new KontingenModel();
        $kontingen = $model->find($idKontingen);
        if ($kontingen === null) {
            throw new \RuntimeException('Kontingen tidak ditemukan.');
        }

        $email = trim((string) ($payload['email_kontingen'] ?? ''));
        $existingEmail = $email === '' ? null : $model
            ->where('email_kontingen', $email)
            ->where('id_kontingen !=', $idKontingen)
            ->first();

        if ($existingEmail !== null) {
            throw new \RuntimeException('Email kontingen sudah dipakai kontingen lain.');
        }

        $jenisKontingen = (string) ($payload['jenis_kontingen'] ?? $kontingen->jenis_kontingen);

        return $model->update($idKontingen, [
            'nama_kontingen' => trim((string) ($payload['nama_kontingen'] ?? $kontingen->nama_kontingen)),
            'singkatan_nama_kontingen' => trim((string) ($payload['singkatan_nama_kontingen'] ?? '')),
            'jenis_kontingen' => $jenisKontingen,
            'perguruan' => (string) ($payload['perguruan'] ?? $kontingen->perguruan),
            'email_kontingen' => $email,
            'nomor_telepon_kontingen' => trim((string) ($payload['nomor_telepon_kontingen'] ?? '')),
            'alamat_kontingen' => trim((string) ($payload['alamat_kontingen'] ?? '')),
            'username' => trim((string) ($payload['username'] ?? $kontingen->username)),
            'nama_penanggungjawab' => trim((string) ($payload['nama_penanggungjawab'] ?? '')),
            'jabatan_penanggungjawab' => trim((string) ($payload['jabatan_penanggungjawab'] ?? '')),
            'nomor_telepon_penanggungjawab' => trim((string) ($payload['nomor_telepon_penanggungjawab'] ?? '')),
            'negara' => $jenisKontingen === 'dalam_negeri' ? 'indonesia' : trim((string) ($payload['negara'] ?? '')),
            'provinsi' => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['provinsi'] ?? '')) : null,
            'kabupaten_kota' => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kabupaten_kota'] ?? '')) : null,
            'kecamatan' => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kecamatan'] ?? '')) : null,
            'kelurahan' => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kelurahan'] ?? '')) : null,
            'alamat_lengkap' => trim((string) ($payload['alamat_lengkap'] ?? '')),
            'alamat_penanggungjawab' => trim((string) ($payload['alamat_penanggungjawab'] ?? '')),
            'keterangan' => trim((string) ($payload['keterangan'] ?? '')),
        ]);
    }

    public function resetKontingenPassword(int $idKontingen, string $password): bool
    {
        if (strlen($password) < 6) {
            throw new \RuntimeException('Password minimal 6 karakter.');
        }

        return (new KontingenModel())->update($idKontingen, [
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]),
        ]);
    }

    public function deleteKontingen(int $idKontingen): bool
    {
        $model = new KontingenModel();
        if ($model->find($idKontingen) === null) {
            throw new \RuntimeException('Kontingen tidak ditemukan.');
        }

        return $model->delete($idKontingen);
    }

    public function createPendaftar(int $idKontingen, array $payload): int
    {
        if ((new KontingenModel())->find($idKontingen) === null) {
            throw new \RuntimeException('Kontingen tidak ditemukan.');
        }

        $model = new PendaftarModel();
        $model->insert($this->pendaftarPayload($idKontingen, $payload));

        $id = (int) $model->getInsertID();
        if ($id <= 0) {
            throw new \RuntimeException('Gagal menambahkan peserta.');
        }

        return $id;
    }

    public function updatePendaftar(int $idKontingen, int $idPendaftar, array $payload): bool
    {
        $model = new PendaftarModel();
        $pendaftar = $model->where('id_kontingen', $idKontingen)->find($idPendaftar);
        if ($pendaftar === null) {
            throw new \RuntimeException('Peserta tidak ditemukan pada kontingen ini.');
        }

        return $model->update($idPendaftar, $this->pendaftarPayload($idKontingen, $payload, $pendaftar));
    }

    public function deletePendaftar(int $idKontingen, int $idPendaftar): bool
    {
        $model = new PendaftarModel();
        $pendaftar = $model->where('id_kontingen', $idKontingen)->find($idPendaftar);
        if ($pendaftar === null) {
            throw new \RuntimeException('Peserta tidak ditemukan pada kontingen ini.');
        }

        $db = db_connect();
        $db->transStart();
        $db->table('peserta_seni')->where('id_pendaftar', $idPendaftar)->delete();
        $db->table('peserta_tanding')->where('id_pendaftar', $idPendaftar)->delete();
        $db->table('pendaftar')->where('id_pendaftar', $idPendaftar)->delete();
        $db->transComplete();

        return $db->transStatus();
    }

    private function pendaftarPayload(int $idKontingen, array $payload, ?object $existing = null): array
    {
        return [
            'id_kontingen' => $idKontingen,
            'nama_pendaftar' => ucwords(strtolower(trim((string) ($payload['nama_pendaftar'] ?? $existing->nama_pendaftar ?? '')))),
            'jenis_kelamin' => (string) ($payload['jenis_kelamin'] ?? $existing->jenis_kelamin ?? 'putra'),
            'tinggi_badan' => (float) ($payload['tinggi_badan'] ?? $existing->tinggi_badan ?? 0),
            'berat_badan' => (float) ($payload['berat_badan'] ?? $existing->berat_badan ?? 0),
            'tempat_lahir' => trim((string) ($payload['tempat_lahir'] ?? $existing->tempat_lahir ?? '')),
            'tanggal_lahir' => $payload['tanggal_lahir'] ?? $existing->tanggal_lahir ?? null,
            'nama_sekolah' => trim((string) ($payload['nama_sekolah'] ?? $existing->nama_sekolah ?? '')),
            'alamat' => trim((string) ($payload['alamat'] ?? $existing->alamat ?? '')),
            'foto' => $existing->foto ?? null,
            'status_data' => (string) ($payload['status_data'] ?? $existing->status_data ?? 'belum_final'),
            'keterangan' => trim((string) ($payload['keterangan'] ?? $existing->keterangan ?? '')),
            'nomor_induk_kependudukan' => trim((string) ($payload['nomor_induk_kependudukan'] ?? $existing->nomor_induk_kependudukan ?? '')),
            'nomor_kartu_keluarga' => trim((string) ($payload['nomor_kartu_keluarga'] ?? $existing->nomor_kartu_keluarga ?? '')),
        ];
    }
}
