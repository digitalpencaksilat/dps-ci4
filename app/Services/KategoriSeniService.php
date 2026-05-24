<?php

namespace App\Services;

use App\Models\KelompokPesertaSeniModel;
use App\Models\PendaftarModel;

class KategoriSeniService
{
    public function listByKontingen(int $idKontingen): array
    {
        return db_connect()->table('kelompok_peserta_seni kps')
            ->select([
                'kps.id_kelompok_peserta_seni',
                'kps.id_kontingen',
                'kps.id_kompetisi_seni',
                'kps.id_pembayaran',
                'kps.keterangan',
                'kps.nomor_undi',
                'kom.nomor_pool',
                'kps.id_kompetisi_seni',
                'sks.nama_seni',
                'sks.jenis_seni',
                'sks.jumlah_peserta',
                'sks.sistem_penampilan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                '(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni',
                '(SELECT GROUP_CONCAT(CAST(p.berat_badan AS CHAR) SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS berat_anggota_kelompok_peserta_seni',
                '(SELECT GROUP_CONCAT(CAST(p.tinggi_badan AS CHAR) SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS tinggi_anggota_kelompok_peserta_seni',
                '(SELECT status_pembayaran FROM pembayaran WHERE pembayaran.id_pembayaran = kps.id_pembayaran) AS status_pembayaran',
            ])
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kps.id_kontingen', $idKontingen)
            ->orderBy('kps.id_kelompok_peserta_seni', 'DESC')
            ->get()
            ->getResult();
    }

    public function availableKompetisi(int $idKontingen): array
    {
        return (new SekretariatPesertaKontingenService())->listKompetisiSeniPendaftaran(false);
    }

    public function availablePendaftarByKompetisi(int $idKompetisi, int $idKontingen): array
    {
        if ($idKontingen !== (int) session()->get('id_kontingen')) {
            return [];
        }

        return (new SekretariatPesertaKontingenService())->getPendaftarByKompetisiSeni($idKompetisi, $idKontingen);
    }

    public function create(int $idKontingen, int $idKompetisi, array $idPendaftar, ?string $keterangan): bool
    {
        (new SekretariatPesertaKontingenService())->createKelompokSeni([
            'id_kompetisi_seni' => $idKompetisi,
            'id_kontingen' => $idKontingen,
            'id_pendaftar' => $idPendaftar,
            'keterangan' => $keterangan ?? '',
        ]);

        return true;
    }

    public function update(object $record, int $idKompetisi): bool
    {
        return (new SekretariatPesertaKontingenService())->updateKelompokSeni((int) $record->id_kelompok_peserta_seni, ['id_kompetisi_seni' => $idKompetisi]);
    }

    public function delete(object $record): bool
    {
        $db = db_connect();
        $db->transStart();
        $db->table('peserta_seni')->where('id_kelompok_peserta_seni', $record->id_kelompok_peserta_seni)->delete();
        $db->table('kelompok_peserta_seni')->where('id_kelompok_peserta_seni', $record->id_kelompok_peserta_seni)->delete();
        $db->transComplete();

        return $db->transStatus();
    }
}
