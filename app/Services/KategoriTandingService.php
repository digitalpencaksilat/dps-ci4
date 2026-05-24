<?php

namespace App\Services;

use App\Models\PendaftarModel;
use App\Models\PesertaTandingModel;

class KategoriTandingService
{
    public function listByKontingen(int $idKontingen): array
    {
        return db_connect()->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding',
                'pt.id_pendaftar',
                'pt.id_kompetisi_tanding',
                'pt.id_pembayaran',
                'pt.keterangan',
                'p.nama_pendaftar',
                'p.berat_badan',
                'p.tinggi_badan',
                'p.jenis_kelamin',
                'p.tanggal_lahir',
                'ku.nama_kategori_usia',
                'kt.label',
                'kt.berat_minimal',
                'kt.berat_maksimal',
                'kl.jenis_perlombaan',
                'kom.max_peserta',
                'kom.nomor_pool',
                '(SELECT status_pembayaran FROM pembayaran WHERE pembayaran.id_pembayaran = pt.id_pembayaran) AS status_pembayaran',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('p.id_kontingen', $idKontingen)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function availablePendaftar(int $idKontingen): array
    {
        return db_connect()->table('pendaftar p')
            ->select('p.*')
            ->join('peserta_tanding pt', 'pt.id_pendaftar = p.id_pendaftar', 'left')
            ->where('p.id_kontingen', $idKontingen)
            ->where('pt.id_peserta_tanding IS NULL', null, false)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function availableKompetisiForPendaftar(int $idPendaftar): array
    {
        $pendaftar = (new PendaftarModel())->find($idPendaftar);
        if ($pendaftar === null) {
            return [];
        }

        if ((int) $pendaftar->id_kontingen !== (int) session()->get('id_kontingen')) {
            return [];
        }

        return (new SekretariatPesertaKontingenService())->getKompetisiTandingByPendaftar($idPendaftar);
    }

    public function create(int $idKontingen, int $idPendaftar, int $idKompetisi): bool
    {
        $pendaftar = (new PendaftarModel())
            ->where('id_pendaftar', $idPendaftar)
            ->where('id_kontingen', $idKontingen)
            ->first();

        if ($pendaftar === null) {
            throw new \RuntimeException('Peserta tidak ditemukan.');
        }

        if ((new PesertaTandingModel())->where('id_pendaftar', $idPendaftar)->first() !== null) {
            throw new \RuntimeException('Peserta sudah terdaftar pada kategori tanding.');
        }

        (new SekretariatPesertaKontingenService())->createPesertaTanding([
            'id_pendaftar' => $idPendaftar,
            'id_kompetisi_tanding' => $idKompetisi,
        ]);

        return true;
    }

    public function update(object $record, int $idKompetisi): bool
    {
        return (new SekretariatPesertaKontingenService())->updatePesertaTanding((int) $record->id_peserta_tanding, ['id_kompetisi_tanding' => $idKompetisi]);
    }

    public function delete(object $record): bool
    {
        return (new PesertaTandingModel())->delete($record->id_peserta_tanding);
    }

    private function calculateAge(?string $birthDate): ?int
    {
        if (! $birthDate) {
            return null;
        }

        try {
            $dob = new \DateTimeImmutable($birthDate);
            $today = new \DateTimeImmutable('today');
            return $dob->diff($today)->y;
        } catch (\Throwable) {
            return null;
        }
    }
}
