<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RedirectResponse;

class ModeController extends BaseController
{
    public function menuTipe(): string
    {
        return view('admin/super/menu_tipe_super_admin', [
            'title' => 'Pilih Mode Super Admin',
            'activeMenu' => 'mode_super',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ]);
    }

    public function pengaturanEvent(): RedirectResponse
    {
        session()->set('tipe_super_admin', 'pengaturan_event');

        return redirect()->to(base_url('admin/super/dashboard-pengaturan-event'));
    }

    public function pengaturanKategoriLomba(): RedirectResponse
    {
        session()->set('tipe_super_admin', 'perngaturan_kategori_lomba');

        return redirect()->to(base_url('admin/super/menu-tipe'));
    }
}
