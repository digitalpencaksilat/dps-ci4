<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriTandingService;

class KuotaPrestasiTandingController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/kuota_prestasi_tanding/index', (new SekretariatKategoriTandingService())->kuotaPrestasi() + ['title' => 'Kuota Kelas Prestasi Tanding', 'activeMenu' => 'kuota_prestasi_tanding', 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')]);
    }
}
