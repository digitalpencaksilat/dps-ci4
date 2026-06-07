<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriSeniService;
use App\Services\SistemGugurTunggalService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PoolSeniController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/pool_seni/index', $this->viewData(['rows' => (new SekretariatKategoriSeniService())->listPool()]));
    }

    public function urutanPoin(): string
    {
        return view('admin/sekretariat/pool_seni/urutan_poin', $this->viewData([
            'rows' => (new SekretariatKategoriSeniService())->listPenampilanUrutanPoinPool(),
        ], 'Urutan Penampilan Seni Pool', 'pesilat_terbaik_pool_seni'));
    }

    public function show(int $id): string
    {
        $service = new SekretariatKategoriSeniService();
        $row = $service->getPool($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/pool_seni/show', $this->viewData([
            'row' => $row,
            'kelompok' => $service->listKelompokByPool($id),
            'battleRows' => $service->listBattleByPool($id),
            'penampilanPenyisihanRows' => $service->listPenampilanByPool($id, 'penyisihan'),
            'penampilanFinalRows' => $service->listPenampilanByPool($id, 'final'),
        ], 'Detail Pool Seni'));
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

    public function acakBaganBattle(int $id)
    {
        try {
            $mode = (string) ($this->request->getPost('mode') ?? 'full_random_persilat');
            $result = (new SistemGugurTunggalService())->acakBaganBattleSeni($id, $mode);
            return redirect()->to(base_url('admin/sekretariat/pool-seni/' . $id))->with('status', true)->with('message', 'Bagan battle seni berhasil dibuat: ' . $result['jumlah_battle'] . ' battle.');
        } catch (\Throwable $e) {
            return redirect()->to(base_url('admin/sekretariat/pool-seni/' . $id))->with('status', false)->with('message', $e->getMessage());
        }
    }

    public function updateBaganBattle(int $id)
    {
        $service = new SekretariatKategoriSeniService();
        $row = $service->getPool($id);

        $response = $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        if ($row === null) {
            return $response->setJSON(['status' => false]);
        }

        (new \App\Models\KompetisiSeniModel())
            ->update($id, ['bagan_battle_seni' => (string) $this->request->getPost('bagan_battle_seni')]);

        return $response->setJSON(['status' => true]);
    }

    public function printBagan(int $id): string
    {
        $row = (new SekretariatKategoriSeniService())->getPool($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/pool_seni/print_bagan', ['row' => $row]);
    }

    private function viewData(array $data, string $title = 'Daftar Pool Seni', string $activeMenu = 'pool_seni'): array
    {
        return $data + ['title' => $title, 'activeMenu' => $activeMenu, 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')];
    }
}
