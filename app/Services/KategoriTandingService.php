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

        $umur = $this->calculateAge($pendaftar->tanggal_lahir);

        $items = db_connect()->table('kompetisi_tanding kom')
            ->select([
                'kom.id_kompetisi_tanding',
                'kom.max_peserta',
                'kom.nomor_pool',
                'kt.id_kelas_tanding',
                'kt.label',
                'kt.berat_minimal',
                'kt.berat_maksimal',
                'kt.biaya_pendaftaran_dn',
                'kt.biaya_pendaftaran_ln',
                'kl.kuota_peserta',
                'kl.jenis_perlombaan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'ku.min_umur',
                'ku.max_umur',
                '(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta_tanding',
                '(SELECT COUNT(*) FROM peserta_tanding pt JOIN kompetisi_tanding k2 ON k2.id_kompetisi_tanding = pt.id_kompetisi_tanding WHERE k2.id_kelas_tanding = kt.id_kelas_tanding) AS jumlah_peserta_tanding_per_kelas',
                '(SELECT COUNT(*) FROM peserta_tanding pt JOIN pendaftar p2 ON p2.id_pendaftar = pt.id_pendaftar WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding AND p2.id_kontingen = ' . (int) $pendaftar->id_kontingen . ') AS jumlah_satu_kontingen',
            ])
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('ku.jenis_kelamin', $pendaftar->jenis_kelamin)
            ->where('kt.label !=', 'sisipan')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->get()
            ->getResult();

        $filtered = [];
        foreach ($items as $item) {
            if ($umur !== null && ($umur < (int) $item->min_umur || $umur > (int) $item->max_umur)) {
                continue;
            }

            if ((float) $pendaftar->berat_badan < (float) $item->berat_minimal || (float) $pendaftar->berat_badan > (float) $item->berat_maksimal) {
                continue;
            }

            $disabled = false;
            $message = null;

            if ((int) $item->jumlah_peserta_tanding >= (int) $item->max_peserta) {
                $disabled = true;
                $message = 'Kuota penuh';
            }

            if (! $disabled && (int) $item->jumlah_satu_kontingen > 0 && $item->jenis_perlombaan === 'prestasi') {
                $disabled = true;
                $message = 'Atlet kontingen ini sudah ada di kelas ini';
            }

            $item->disabled = $disabled;
            $item->message = $message;
            $filtered[] = $item;
        }

        return $filtered;
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

        return (new PesertaTandingModel())->insert([
            'id_pendaftar' => $idPendaftar,
            'id_kompetisi_tanding' => $idKompetisi,
            'id_pembayaran' => null,
            'nomor_bagan' => null,
            'keterangan' => '',
            'status' => 'OK',
            'status_sertifikat' => 'belum_dicetak',
            'nomor_sertifikat' => null,
        ]) !== false;
    }

    public function update(object $record, int $idKompetisi): bool
    {
        return (new PesertaTandingModel())->update($record->id_peserta_tanding, [
            'id_kompetisi_tanding' => $idKompetisi,
        ]);
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
