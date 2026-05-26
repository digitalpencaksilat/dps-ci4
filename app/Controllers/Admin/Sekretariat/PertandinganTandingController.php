<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriTandingService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PertandinganTandingController extends BaseController
{
    public function index(): string
    {
        $service = new SekretariatKategoriTandingService();
        return view('admin/sekretariat/pertandingan_tanding/index', $this->viewData(['rows' => $service->listPertandingan(), 'poolOptions' => $service->listPool()]));
    }

    public function urutanPoin(): string
    {
        $service = new SekretariatKategoriTandingService();

        return view('admin/sekretariat/pertandingan_tanding/urutan_poin', $this->viewData([
            'rows' => $service->listPertandinganUrutanPoin(),
        ], 'Urutan Poin Pertandingan', 'pesilat_terbaik_pertandingan_tanding'));
    }

    public function show(int $id): string
    {
        $service = new SekretariatKategoriTandingService();
        $row = $service->getPertandingan($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/pertandingan_tanding/show', $this->viewData(['row' => $row, 'poolOptions' => $service->listPool(), 'pesertaOptions' => $service->listPesertaByPool((int) $row->id_kompetisi_tanding)], 'Detail Pertandingan Tanding'));
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(base_url('admin/sekretariat/pertandingan-tanding'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        (new SekretariatKategoriTandingService())->createPertandingan($this->request->getPost());
        return redirect()->to(base_url('admin/sekretariat/pertandingan-tanding'))->with('status', true)->with('message', 'Pertandingan berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(base_url('admin/sekretariat/pertandingan-tanding/' . $id))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        (new SekretariatKategoriTandingService())->updatePertandingan($id, $this->request->getPost());
        return redirect()->to(base_url('admin/sekretariat/pertandingan-tanding/' . $id))->with('status', true)->with('message', 'Pertandingan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        try {
            (new SekretariatKategoriTandingService())->deletePertandingan($id);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/pertandingan-tanding/' . $id))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/pertandingan-tanding'))->with('status', true)->with('message', 'Pertandingan berhasil dihapus.');
    }

    private function rules(): array
    {
        return ['id_kompetisi_tanding' => 'required|integer', 'babak' => 'required', 'nomor_pertandingan' => 'required|integer'];
    }

    private function viewData(array $data, string $title = 'Daftar Pertandingan Tanding', string $activeMenu = 'pertandingan_tanding'): array
    {
        return $data + ['title' => $title, 'activeMenu' => $activeMenu, 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')];
    }
}
