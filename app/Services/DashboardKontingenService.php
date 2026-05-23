<?php

namespace App\Services;

use App\Models\KelompokPesertaSeniModel;
use App\Models\PembayaranModel;
use App\Models\PendaftarModel;
use App\Models\PesertaTandingModel;

class DashboardKontingenService
{
    public function summary(int $idKontingen): array
    {
        $db = db_connect();

        $jumlahAtlet = (new PendaftarModel())
            ->where('id_kontingen', $idKontingen)
            ->countAllResults();

        $jumlahTanding = (new PesertaTandingModel())
            ->select('peserta_tanding.id_peserta_tanding')
            ->join('pendaftar', 'pendaftar.id_pendaftar = peserta_tanding.id_pendaftar')
            ->where('pendaftar.id_kontingen', $idKontingen)
            ->countAllResults();

        $jumlahSeni = (new KelompokPesertaSeniModel())
            ->where('id_kontingen', $idKontingen)
            ->countAllResults();

        $tagihanBelumDibayar = $db->table('peserta_tanding pt')
            ->selectCount('pt.id_peserta_tanding', 'total')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->where('p.id_kontingen', $idKontingen)
            ->where('pt.id_pembayaran', null)
            ->get()
            ->getRow();

        $tagihanSeniBelumDibayar = $db->table('kelompok_peserta_seni')
            ->selectCount('id_kelompok_peserta_seni', 'total')
            ->where('id_kontingen', $idKontingen)
            ->where('id_pembayaran', null)
            ->get()
            ->getRow();

        $jumlahTransaksi = (new PembayaranModel())
            ->where('id_kontingen', $idKontingen)
            ->countAllResults();

        return [
            'jumlah_atlet'         => $jumlahAtlet,
            'jumlah_tanding'       => $jumlahTanding,
            'jumlah_seni'          => $jumlahSeni,
            'jumlah_tagihan'       => (int) ($tagihanBelumDibayar->total ?? 0) + (int) ($tagihanSeniBelumDibayar->total ?? 0),
            'jumlah_transaksi'     => $jumlahTransaksi,
            'jumlah_notifikasi'    => (int) ($tagihanBelumDibayar->total ?? 0) + (int) ($tagihanSeniBelumDibayar->total ?? 0),
        ];
    }
}
