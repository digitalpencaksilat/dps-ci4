<?php

namespace App\Controllers;

use App\Models\KontingenModel;
use App\Services\DashboardKontingenService;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $idKontingen = (int) session()->get('id_kontingen');
        $kontingen = (new KontingenModel())->find($idKontingen);
        $summary = (new DashboardKontingenService())->summary($idKontingen);
        $allowPeserta = (bool) (ci3_config_item('perbolehkan_kontingen_input_atlet', 'pendaftaran/akses_pendaftaran') ?? false);
        $allowKategori = (bool) (ci3_config_item('perbolehkan_kontingen_memilih_kategori', 'pendaftaran/akses_pendaftaran') ?? false);
        $allowPayment = (bool) (ci3_config_item('perbolehkan_kontingen_melunasi_pembayaran', 'pendaftaran/akses_pendaftaran') ?? false);

        return view('kontingen/dashboard/index', [
            'title'       => 'Dashboard Kontingen',
            'kontingen'   => $kontingen,
            'summary'     => $summary,
            'featureAccess' => [
                'peserta' => ['enabled' => $allowPeserta, 'label' => $allowPeserta ? 'Aktif' : 'Ditutup'],
                'tanding' => ['enabled' => $allowKategori, 'label' => $allowKategori ? 'Aktif' : 'Ditutup'],
                'seni' => ['enabled' => $allowKategori, 'label' => $allowKategori ? 'Aktif' : 'Ditutup'],
                'pembayaran' => ['enabled' => $allowPayment, 'label' => $allowPayment ? 'Aktif' : 'Ditutup'],
            ],
            'activeMenu'  => 'dashboard',
            'eventName'   => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'   => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
        ]);
    }
}
