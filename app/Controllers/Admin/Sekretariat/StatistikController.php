<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatStatistikService;

class StatistikController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/statistik/index', $this->viewData([
            'stats' => (new SekretariatStatistikService())->getPendaftaranStats(),
        ], 'Progress Pendaftaran', 'statistik_pendaftaran'));
    }

    public function tanding(): string
    {
        return view('admin/sekretariat/statistik/tanding', $this->viewData([
            'stats' => (new SekretariatStatistikService())->getTandingStats(),
        ], 'Statistik Tanding', 'statistik_tanding'));
    }

    public function seni(): string
    {
        return view('admin/sekretariat/statistik/seni', $this->viewData([
            'stats' => (new SekretariatStatistikService())->getSeniStats(),
        ], 'Statistik Seni', 'statistik_seni'));
    }

    private function viewData(array $data, string $title, string $activeMenu): array
    {
        return $data + [
            'title' => $title,
            'activeMenu' => $activeMenu,
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
        ];
    }
}
