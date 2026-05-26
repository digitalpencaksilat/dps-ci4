<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatPesertaKontingenService;

class DataBpjsController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/data_bpjs/index', [
            'title' => 'Data BPJS',
            'activeMenu' => 'data_bpjs',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'rows' => (new SekretariatPesertaKontingenService())->listPendaftarForBpjs(),
        ]);
    }
}
