<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriSeniService;

class KuotaPrestasiSeniController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/kuota_prestasi_seni/index', (new SekretariatKategoriSeniService())->kuotaPrestasi() + ['title' => 'Kuota Kelas Prestasi Seni', 'activeMenu' => 'kuota_prestasi_seni', 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')]);
    }
}
