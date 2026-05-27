<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;

class PengaturanEventController extends BaseController
{
    public function dashboard(): string
    {
        return view('admin/super/dashboard_pengaturan_event', [
            'title' => 'Dashboard Pengaturan Event',
            'activeMenu' => 'pengaturan_event',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ]);
    }
}
