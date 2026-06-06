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
        return $this->renderShow($id);
    }

    public function editKelas(int $id): string
    {
        return $this->renderShow($id, 'editPesertaTandingModal');
    }

    public function pindahPool(int $id): string
    {
        return $this->renderShow($id, 'pindahPoolTandingModal');
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

    public function ajaxEditKelas(int $id)
    {
        $service = new SekretariatPesertaKontingenService();
        $row = $service->getPesertaTandingDetail($id);
        if ($row === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        // Filter kompetisi berdasarkan jenis kelamin pendaftar (sama seperti saat create)
        $kompetisiOptions = $service->getKompetisiTandingByPendaftar((int) $row->id_pendaftar, $id);

        return $this->response->setJSON([
            'id_peserta_tanding' => $row->id_peserta_tanding,
            'id_kompetisi_tanding' => $row->id_kompetisi_tanding,
            'keterangan' => $row->keterangan ?? '',
            'kompetisiOptions' => $kompetisiOptions,
        ]);
    }

    public function ajaxPindahPool(int $id)
    {
        $service = new SekretariatPesertaKontingenService();
        $row = $service->getPesertaTandingDetail($id);
        if ($row === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        return $this->response->setJSON([
            'id_peserta_tanding' => $row->id_peserta_tanding,
            'id_kompetisi_tanding' => $row->id_kompetisi_tanding,
            'label' => $row->label ?? '-',
            'nama_kategori_usia' => $row->nama_kategori_usia ?? '-',
            'keterangan' => $row->keterangan ?? '',
            'poolOptions' => $service->listPoolTandingForPeserta($id),
        ]);
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

    private function renderShow(int $id, ?string $openModal = null): string
    {
        $service = new SekretariatPesertaKontingenService();
        $row = $service->getPesertaTandingDetail($id);
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
            'kompetisiOptions' => $service->listKompetisiTanding(),
            'poolOptions' => $service->listPoolTandingForPeserta($id),
            'openModal' => $openModal,
        ]);
    }
}
