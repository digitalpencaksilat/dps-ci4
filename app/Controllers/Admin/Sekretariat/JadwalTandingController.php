<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Models\GelanggangModel;
use App\Models\JadwalTandingModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class JadwalTandingController extends BaseController
{
    public function index(): string
    {
        $model = new JadwalTandingModel();
        return view('admin/sekretariat/jadwal_tanding/index', $this->viewData([
            'rows'         => $model->get_all(),
            'gelanggang'   => (new GelanggangModel())->findAll(),
        ]));
    }

    public function show(int $id): string
    {
        $model = new JadwalTandingModel();
        $jadwal = $model->findWithGelanggang($id);
        if ($jadwal === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/jadwal_tanding/show', $this->viewData([
            'jadwal'     => $jadwal,
            'details'    => $model->get_detail_jadwal($id),
            'peserta'    => (new \App\Models\PesertaTandingModel())->findAll(),
        ], 'Schedule Arena ' . esc($jadwal->nama_gelanggang ?? 'Arena ' . $id)));
    }

    public function create()
    {
        if (! $this->validate([
            'id_gelanggang' => 'required|is_natural_no_zero',
            'tanggal'       => 'required|valid_date',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
        ])) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        $model = new JadwalTandingModel();
        $model->insert([
            'id_gelanggang' => $this->request->getPost('id_gelanggang'),
            'tanggal'        => $this->request->getPost('tanggal'),
            'jam_mulai'      => $this->request->getPost('jam_mulai'),
            'jam_selesai'    => $this->request->getPost('jam_selesai'),
            'keterangan'     => $this->request->getPost('keterangan'),
        ]);

        return redirect()->to(base_url('admin/sekretariat/jadwal-tanding'))->with('status', true)->with('message', 'Jadwal tanding berhasil ditambahkan.');
    }

    public function createFromModal()
    {
        if (! $this->validate([
            'id_gelanggang' => 'required|is_natural_no_zero',
            'tanggal'       => 'required|valid_date',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
        ])) {
            return $this->response->setJSON(['status' => false, 'message' => $this->validator->getErrors()]);
        }

        $model = new JadwalTandingModel();
        $model->insert([
            'id_gelanggang' => $this->request->getPost('id_gelanggang'),
            'tanggal'        => $this->request->getPost('tanggal'),
            'jam_mulai'      => $this->request->getPost('jam_mulai'),
            'jam_selesai'    => $this->request->getPost('jam_selesai'),
            'keterangan'     => $this->request->getPost('keterangan'),
        ]);

        return $this->response->setJSON(['status' => true, 'message' => 'Jadwal berhasil ditambahkan.', 'id' => $model->getInsertID()]);
    }

    public function updateKeterangan(int $id)
    {
        $model = new JadwalTandingModel();
        if ($model->find($id) === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        $model->update($id, ['keterangan' => $this->request->getPost('keterangan') ?? '']);

        return $this->response->setJSON(['status' => true, 'message' => 'Keterangan berhasil diperbarui.']);
    }

    public function delete(int $id)
    {
        $model = new JadwalTandingModel();
        if ($model->find($id) === null) {
            return redirect()->back()->with('status', false)->with('message', 'Jadwal tidak ditemukan.');
        }

        db_connect()->table('detail_jadwal_tanding')->where('id_jadwal_tanding', $id)->delete();
        $model->delete($id);

        return redirect()->to(base_url('admin/sekretariat/jadwal-tanding'))->with('status', true)->with('message', 'Jadwal berhasil dihapus.');
    }

    public function createPdfAjax(int $id, int $withScore = 0)
    {
        return $this->response->setJSON(['status' => false, 'message' => 'Fitur PDF generation belum tersedia.']);
    }

    public function getAllIdsAjax()
    {
        $model = new JadwalTandingModel();
        $rows = $model->get_all();
        $data = [];
        foreach ($rows as $row) {
            $data[] = ['id' => $row->id_jadwal_tanding, 'nama' => 'Arena ' . ($row->nama_gelanggang ?? '') . ' - ' . ($row->keterangan_jadwal ?? '')];
        }
        return $this->response->setJSON(['status' => true, 'data' => $data]);
    }

    public function tukarAtlet()
    {
        return redirect()->back()->with('status', false)->with('message', 'Fitur tukar atlet belum tersedia.');
    }

    public function sortirUlang(int $id)
    {
        return redirect()->back()->with('status', false)->with('message', 'Fitur sortir ulang belum tersedia.');
    }

    public function polaPenjadwalan(int $id)
    {
        return redirect()->back()->with('status', false)->with('message', 'Fitur pola penjadwalan belum tersedia.');
    }

    private function viewData(array $data, string $title = 'Daftar Jadwal Tanding'): array
    {
        return $data + [
            'title'      => $title,
            'activeMenu' => 'jadwal_tanding',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
        ];
    }
}
