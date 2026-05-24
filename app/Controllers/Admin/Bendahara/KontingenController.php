<?php

namespace App\Controllers\Admin\Bendahara;

use App\Controllers\BaseController;
use App\Services\PembayaranAdminService;
use App\Services\PembayaranKontingenService;
use CodeIgniter\Exceptions\PageNotFoundException;

class KontingenController extends BaseController
{
    public function index(): string
    {
        return view('admin/bendahara/kontingen/index', [
            'title'          => 'Rekap Kontingen',
            'activeMenu'     => 'kontingen',
            'eventName'      => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'      => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'      => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Bendahara'),
            'kontingenRows'  => (new PembayaranAdminService())->kontingenRecap(),
        ]);
    }

    public function show(int $id): string
    {
        if ($id <= 0) {
            throw PageNotFoundException::forPageNotFound();
        }

        $detail = (new PembayaranAdminService())->kontingenDetail($id);
        if ($detail === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/bendahara/kontingen/show', [
            'title'          => 'Detail Kontingen',
            'activeMenu'     => 'kontingen',
            'paymentSubmenu' => 'all',
            'eventName'      => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'      => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'      => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Bendahara'),
            'detail'         => $detail,
            'accounts'       => (new PembayaranKontingenService())->accounts(),
        ]);
    }
}
