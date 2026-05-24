<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriSeniService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PoolSeniController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/pool_seni/index', $this->viewData(['rows' => (new SekretariatKategoriSeniService())->listPool()]));
    }

    public function show(int $id): string
    {
        $service = new SekretariatKategoriSeniService();
        $row = $service->getPool($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/pool_seni/show', $this->viewData(['row' => $row, 'kelompok' => $service->listKelompokByPool($id)], 'Detail Pool Seni'));
    }

    public function update(int $id)
    {
        if (! $this->validate(['max_peserta' => 'required|integer', 'perhitungan_medali' => 'required|in_list[0,1]'])) {
            return redirect()->to(base_url('admin/sekretariat/pool-seni/' . $id))->with('status', false)->with('message', $this->validator->getErrors());
        }

        (new SekretariatKategoriSeniService())->updatePool($id, $this->request->getPost());
        return redirect()->to(base_url('admin/sekretariat/pool-seni/' . $id))->with('status', true)->with('message', 'Pool seni berhasil diperbarui.');
    }

    public function beriNomorUndi(int $id)
    {
        (new SekretariatKategoriSeniService())->beriNomorUndi($id);
        return redirect()->to(base_url('admin/sekretariat/pool-seni/' . $id))->with('status', true)->with('message', 'Nomor undi berhasil diisi ulang.');
    }

    private function viewData(array $data, string $title = 'Daftar Pool Seni'): array
    {
        return $data + ['title' => $title, 'activeMenu' => 'pool_seni', 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')];
    }
}
