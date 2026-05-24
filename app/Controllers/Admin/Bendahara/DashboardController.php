<?php

namespace App\Controllers\Admin\Bendahara;

use App\Controllers\BaseController;
use App\Services\PembayaranAdminService;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $service = new PembayaranAdminService();

        return view('admin/bendahara/dashboard', [
            'title'               => 'Dashboard Bendahara',
            'activeMenu'          => 'dashboard',
            'eventName'           => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'           => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'           => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Bendahara'),
            'pageStatus'          => 'Dashboard bendahara sekarang menampilkan ringkasan pembayaran, antrian verifikasi, dan rekap kontingen dari data transaksi aktif.',
            'summary'             => $service->dashboardRecap(),
            'waitingTransactions' => $service->waitingTransactions(),
            'kontingenRows'       => array_slice($service->kontingenRecap(), 0, 5),
        ]);
    }
}
