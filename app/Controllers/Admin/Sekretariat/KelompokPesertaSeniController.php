<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatPesertaKontingenService;
use CodeIgniter\Exceptions\PageNotFoundException;

class KelompokPesertaSeniController extends BaseController
{
    public function index(): string
    {
        $service = new SekretariatPesertaKontingenService();

        return view('admin/sekretariat/kelompok_seni/index', [
            'title'      => 'Kelompok Peserta Seni',
            'activeMenu' => 'kelompok_seni',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'rows'       => $service->listKelompokSeni(),
            'kontingenRows' => $service->listKontingen(),
            'kompetisiOptions' => $service->listKompetisiSeni(),
            'pendaftarOptions' => $service->availablePendaftarForSeni(),
        ]);
    }

    public function show(int $id): string
    {
        $row = (new SekretariatPesertaKontingenService())->getKelompokSeniDetail($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $service = new SekretariatPesertaKontingenService();

        return view('admin/sekretariat/kelompok_seni/show', [
            'title'      => 'Detail Kelompok Seni',
            'activeMenu' => 'kelompok_seni',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'row'        => $row,
            'anggotaRows' => $service->listPesertaSeniByKelompok($id),
            'kompetisiOptions' => $service->listKompetisiSeni(),
            'poolOptions' => $service->listPoolSeniForKelompok($id),
            'pendaftarOptions' => $service->availablePendaftarForSeni((int) $row->id_kontingen),
        ]);
    }

    public function pendaftarByKompetisi(int $idKompetisi, int $idKontingen)
    {
        return $this->response->setJSON((new SekretariatPesertaKontingenService())->getPendaftarByKompetisiSeni($idKompetisi, $idKontingen));
    }

    public function store()
    {
        if (! $this->validate($this->kelompokRules(true))) {
            return redirect()->to(base_url('admin/sekretariat/kelompok-seni'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            $id = (new SekretariatPesertaKontingenService())->createKelompokSeni($this->request->getPost());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kelompok-seni'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->with('status', true)->with('message', 'Kelompok seni berhasil dibuat.');
    }

    public function update(int $id)
    {
        if (! $this->validate($this->kelompokRules(false))) {
            return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->updateKelompokSeni($id, $this->request->getPost());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->with('status', true)->with('message', 'Kelompok seni berhasil diperbarui.');
    }

    public function ajaxEditKelompok(int $id)
    {
        $service = new SekretariatPesertaKontingenService();
        $row = $service->getKelompokSeniDetail($id);
        if ($row === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        // Filter kompetisi seni berdasarkan jenis kelamin kelompok (sama seperti saat create)
        $kompetisiOptions = $service->listKompetisiSeniPendaftaran(true, ['ku.jenis_kelamin' => $row->jenis_kelamin]);

        return $this->response->setJSON([
            'id_kelompok_peserta_seni' => $row->id_kelompok_peserta_seni,
            'id_kompetisi_seni' => $row->id_kompetisi_seni,
            'keterangan' => $row->keterangan ?? '',
            'kompetisiOptions' => $kompetisiOptions,
        ]);
    }

    public function ajaxPindahPool(int $id)
    {
        $service = new SekretariatPesertaKontingenService();
        $row = $service->getKelompokSeniDetail($id);
        if ($row === null) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        return $this->response->setJSON([
            'id_kelompok_peserta_seni' => $row->id_kelompok_peserta_seni,
            'id_kompetisi_seni' => $row->id_kompetisi_seni,
            'jenis_seni' => $row->jenis_seni ?? '',
            'nama_seni' => $row->nama_seni ?? '',
            'nomor_undi' => $row->nomor_undi ?? 0,
            'keterangan' => $row->keterangan ?? '',
            'poolOptions' => $service->listPoolSeniForKelompok($id),
        ]);
    }

    public function delete(int $id)
    {
        try {
            (new SekretariatPesertaKontingenService())->deleteKelompokSeni($id);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kelompok-seni'))->with('status', true)->with('message', 'Kelompok seni berhasil dihapus.');
    }

    public function addMember(int $id)
    {
        if (! $this->validate(['id_pendaftar' => 'required|integer'])) {
            return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->addPesertaSeni($id, (int) $this->request->getPost('id_pendaftar'));
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->with('status', true)->with('message', 'Anggota kelompok berhasil ditambahkan.');
    }

    public function deleteMember(int $id, int $idPesertaSeni)
    {
        try {
            (new SekretariatPesertaKontingenService())->deletePesertaSeni($idPesertaSeni);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kelompok-seni/' . $id))->with('status', true)->with('message', 'Anggota kelompok berhasil dihapus.');
    }

    private function kelompokRules(bool $requireMembers): array
    {
        $rules = [
            'id_kompetisi_seni' => 'required|integer',
            'status' => 'permit_empty|in_list[ok,diskualifikasi]',
            'nomor_undi' => 'permit_empty|integer',
        ];

        if ($requireMembers) {
            $rules['id_kontingen'] = 'required|integer';
            $rules['id_pendaftar'] = 'required';
        }

        return $rules;
    }
}
