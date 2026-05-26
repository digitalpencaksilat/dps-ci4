<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriSeniService;
use CodeIgniter\Exceptions\PageNotFoundException;

class BattleSeniController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/battle_seni/index', $this->viewData(['rows' => (new SekretariatKategoriSeniService())->listBattle()]));
    }

    public function urutanPoin(): string
    {
        return view('admin/sekretariat/battle_seni/urutan_poin', $this->viewData([
            'rows' => (new SekretariatKategoriSeniService())->listBattleUrutanPoin(),
        ], 'Urutan Poin Battle Seni', 'pesilat_terbaik_battle_seni'));
    }

    public function show(int $id): string
    {
        $row = (new SekretariatKategoriSeniService())->getBattle($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/battle_seni/show', $this->viewData(['row' => $row], 'Detail Battle Seni'));
    }

    private function viewData(array $data, string $title = 'Daftar Battle Seni', string $activeMenu = 'battle_seni'): array
    {
        return $data + ['title' => $title, 'activeMenu' => $activeMenu, 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')];
    }
}
