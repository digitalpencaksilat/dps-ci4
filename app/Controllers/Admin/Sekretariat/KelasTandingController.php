<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriTandingService;
use CodeIgniter\Exceptions\PageNotFoundException;

class KelasTandingController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/kelas_tanding/index', $this->viewData(['rows' => (new SekretariatKategoriTandingService())->listKelas()]));
    }

    public function show(int $id): string
    {
        $service = new SekretariatKategoriTandingService();
        $row = $service->getKelas($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/kelas_tanding/show', $this->viewData([
            'row' => $row,
            'poolRows' => $service->listPoolByKelas($id),
            'pesertaRows' => $service->listPesertaByKelas($id),
        ], 'Detail Kelas Tanding'));
    }

    private function viewData(array $data, string $title = 'Daftar Kelas Tanding'): array
    {
        return $data + ['title' => $title, 'activeMenu' => 'kelas_tanding', 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')];
    }
}
