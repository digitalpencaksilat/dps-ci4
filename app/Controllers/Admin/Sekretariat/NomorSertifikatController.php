<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatPesertaKontingenService;

class NomorSertifikatController extends BaseController
{
    public function index(): string
    {
        $service = new SekretariatPesertaKontingenService();

        return view('admin/sekretariat/nomor_sertifikat/index', [
            'title' => 'Nomor Sertifikat',
            'activeMenu' => 'nomor_sertifikat',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'dataPerolehanMedaliTanding' => $service->listNomorSertifikatTanding(),
            'dataPerolehanMedaliSeni' => $service->listNomorSertifikatSeni(),
            'dataPesertaSeni' => $service->listPesertaSeniForSertifikat(),
        ]);
    }
}
