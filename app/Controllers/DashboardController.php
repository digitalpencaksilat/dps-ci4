<?php

namespace App\Controllers;

use App\Models\KontingenModel;
use App\Services\DashboardKontingenService;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $idKontingen = (int) session()->get('id_kontingen');
        $kontingen = (new KontingenModel())->find($idKontingen);
        $summary = (new DashboardKontingenService())->summary($idKontingen);

        return view('kontingen/dashboard/index', [
            'title'       => 'Dashboard Kontingen',
            'kontingen'   => $kontingen,
            'summary'     => $summary,
            'activeMenu'  => 'dashboard',
            'eventName'   => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'   => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
        ]);
    }
}
