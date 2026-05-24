<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatPesertaKontingenService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PesertaTandingController extends BaseController
{
    public function index(): string
    {
        $service = new SekretariatPesertaKontingenService();

        return view('admin/sekretariat/peserta_tanding/index', [
            'title'      => 'Peserta Tanding',
            'activeMenu' => 'peserta_tanding',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'rows'       => $service->listPesertaTanding(),
            'pendaftarOptions' => $service->availablePendaftarForTanding(),
            'kompetisiOptions' => $service->listKompetisiTanding(),
        ]);
    }

    public function show(int $id): string
    {
        $row = (new SekretariatPesertaKontingenService())->getPesertaTandingDetail($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/peserta_tanding/show', [
            'title'      => 'Detail Peserta Tanding',
            'activeMenu' => 'peserta_tanding',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'row'        => $row,
            'kompetisiOptions' => (new SekretariatPesertaKontingenService())->listKompetisiTanding(),
        ]);
    }

    public function byPendaftar(int $idPendaftar)
    {
        return $this->response->setJSON((new SekretariatPesertaKontingenService())->getKompetisiTandingByPendaftar($idPendaftar));
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->to(base_url('admin/sekretariat/peserta-tanding'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->createPesertaTanding($this->request->getPost());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/peserta-tanding'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/peserta-tanding'))->with('status', true)->with('message', 'Peserta tanding berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if (! $this->validate(['id_kompetisi_tanding' => 'required|integer'])) {
            return redirect()->to(base_url('admin/sekretariat/peserta-tanding/' . $id))->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->updatePesertaTanding($id, $this->request->getPost());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/peserta-tanding/' . $id))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/peserta-tanding/' . $id))->with('status', true)->with('message', 'Peserta tanding berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        try {
            (new SekretariatPesertaKontingenService())->deletePesertaTanding($id);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/peserta-tanding/' . $id))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/peserta-tanding'))->with('status', true)->with('message', 'Peserta tanding berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'id_pendaftar' => 'required|integer',
            'id_kompetisi_tanding' => 'required|integer',
        ];
    }
}
