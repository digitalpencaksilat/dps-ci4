<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriTandingService;
use App\Services\SistemGugurTunggalService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PoolTandingController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/pool_tanding/index', $this->viewData(['rows' => (new SekretariatKategoriTandingService())->listPool()]));
    }

    public function show(int $id): string
    {
        $service = new SekretariatKategoriTandingService();
        $row = $service->getPool($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/pool_tanding/show', $this->viewData([
            'row' => $row,
            'peserta' => $service->listPesertaByPool($id),
            'pertandinganRows' => $service->listPertandinganByPool($id),
        ], 'Detail Pool Tanding'));
    }

    public function update(int $id)
    {
        if (! $this->validate(['max_peserta' => 'required|integer', 'perhitungan_medali' => 'required|in_list[0,1]'])) {
            return redirect()->to(base_url('admin/sekretariat/pool-tanding/' . $id))->with('status', false)->with('message', $this->validator->getErrors());
        }

        (new SekretariatKategoriTandingService())->updatePool($id, $this->request->getPost());
        return redirect()->to(base_url('admin/sekretariat/pool-tanding/' . $id))->with('status', true)->with('message', 'Pool tanding berhasil diperbarui.');
    }

    public function acakBagan(int $id)
    {
        try {
            $mode = (string) ($this->request->getPost('mode') ?? 'full_random_persilat');
            $result = (new SistemGugurTunggalService())->acakBaganTanding($id, $mode);
            return redirect()->to(base_url('admin/sekretariat/pool-tanding/' . $id))->with('status', true)->with('message', 'Bagan tanding berhasil dibuat: ' . $result['jumlah_pertandingan'] . ' pertandingan.');
        } catch (\Throwable $e) {
            return redirect()->to(base_url('admin/sekretariat/pool-tanding/' . $id))->with('status', false)->with('message', $e->getMessage());
        }
    }

    public function printBagan(int $id): string
    {
        $row = (new SekretariatKategoriTandingService())->getPool($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/pool_tanding/print_bagan', ['row' => $row]);
    }

    private function viewData(array $data, string $title = 'Daftar Pool Tanding'): array
    {
        return $data + ['title' => $title, 'activeMenu' => 'pool_tanding', 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')];
    }
}
