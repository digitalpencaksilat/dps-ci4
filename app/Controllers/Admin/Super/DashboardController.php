<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('admin/super/dashboard', [
            'title' => 'Menu Utama Super Admin',
            'activeMenu' => 'super_home',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ]);
    }
}
