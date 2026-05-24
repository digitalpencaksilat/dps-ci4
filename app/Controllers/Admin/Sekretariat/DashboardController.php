<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/dashboard', [
            'title' => 'Dashboard Sekretariat',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
        ]);
    }
}
