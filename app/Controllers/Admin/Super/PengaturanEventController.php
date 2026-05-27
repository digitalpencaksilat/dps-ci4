<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\PengaturanEventService;

class PengaturanEventController extends BaseController
{
    private PengaturanEventService $pengaturanEventService;

    public function __construct()
    {
        $this->pengaturanEventService = new PengaturanEventService();
    }

    public function dashboard(): string
    {
        return view('admin/super/dashboard_pengaturan_event', $this->pengaturanEventService->dashboardData() + [
            'title' => 'Dashboard Pengaturan Event',
            'activeMenu' => 'pengaturan_event',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ]);
    }
}
