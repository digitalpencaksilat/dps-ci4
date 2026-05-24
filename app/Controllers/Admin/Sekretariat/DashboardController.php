<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatPesertaKontingenService;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $service = new SekretariatPesertaKontingenService();

        return view('admin/sekretariat/dashboard', [
            'title' => 'Dashboard Sekretariat',
            'activeMenu' => 'dashboard',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'stats' => $service->dashboardStats(),
            'kontingenRows' => array_slice($service->listKontingen(), 0, 8),
        ]);
    }
}
