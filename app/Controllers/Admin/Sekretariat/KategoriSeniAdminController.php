<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriSeniService;
use CodeIgniter\Exceptions\PageNotFoundException;

class KategoriSeniAdminController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/kategori_seni/index', $this->viewData(['rows' => (new SekretariatKategoriSeniService())->listKategori()]));
    }

    public function show(int $id): string
    {
        $service = new SekretariatKategoriSeniService();
        $row = $service->getKategori($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/kategori_seni/show', $this->viewData([
            'row' => $row,
            'poolRows' => $service->listPoolByKategori($id),
            'kelompokRows' => $service->listKelompokByKategori($id),
        ], 'Detail Kategori Seni'));
    }

    private function viewData(array $data, string $title = 'Daftar Kategori Seni'): array
    {
        return $data + ['title' => $title, 'activeMenu' => 'kategori_seni_admin', 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')];
    }
}
