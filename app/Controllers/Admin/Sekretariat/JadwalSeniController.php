<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Models\GelanggangModel;
use App\Models\JadwalSeniModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class JadwalSeniController extends BaseController
{
    public function index(): string
    {
        $model = new JadwalSeniModel();
        return view('admin/sekretariat/jadwal_seni/index', $this->viewData([
            'rows'         => $model->get_all(),
            'gelanggang'   => (new GelanggangModel())->findAll(),
        ]));
    }

    public function show(int $id): string
    {
        $model = new JadwalSeniModel();
        $jadwal = $model->findWithGelanggang($id);
        if ($jadwal === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $allDetails = $model->get_detail_jadwal($id);
        $battleDetails = array_filter($allDetails, fn($d) => !empty($d->id_battle_seni));
        $poolDetails   = array_filter($allDetails, fn($d) => !empty($d->id_penampilan_seni));

        return view('admin/sekretariat/jadwal_seni/show', $this->viewData([
            'jadwal'        => $jadwal,
            'details'       => $allDetails,
            'battleDetails' => $battleDetails,
            'poolDetails'   => $poolDetails,
        ], 'Schedule Seni Arena ' . esc($jadwal->nama_gelanggang ?? 'Arena ' . $id)));
    }

    public function createPdfAjax(int $id, int $withScore = 0)
    {
        return $this->response->setJSON(['status' => false, 'message' => 'Fitur PDF generation belum tersedia.']);
    }

    public function getAllIdsAjax()
    {
        $model = new JadwalSeniModel();
        $rows = $model->get_all();
        $data = [];
        foreach ($rows as $row) {
            $data[] = ['id' => $row->id_jadwal_seni, 'nama' => 'Arena ' . ($row->nama_gelanggang ?? '') . ' - ' . ($row->keterangan_jadwal ?? '')];
        }
        return $this->response->setJSON(['status' => true, 'data' => $data]);
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

        $model = new JadwalSeniModel();
        $model->insert([
            'id_gelanggang' => $this->request->getPost('id_gelanggang'),
            'tanggal'        => $this->request->getPost('tanggal'),
            'jam_mulai'      => $this->request->getPost('jam_mulai'),
            'jam_selesai'    => $this->request->getPost('jam_selesai'),
            'keterangan'     => $this->request->getPost('keterangan'),
        ]);

        return redirect()->to(base_url('admin/sekretariat/jadwal-seni'))->with('status', true)->with('message', 'Jadwal seni berhasil ditambahkan.');
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

        $model = new JadwalSeniModel();
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
        $model = new JadwalSeniModel();
        if ($model->find($id) === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        $model->update($id, ['keterangan' => $this->request->getPost('keterangan') ?? '']);

        return $this->response->setJSON(['status' => true, 'message' => 'Keterangan berhasil diperbarui.']);
    }

    public function delete(int $id)
    {
        $model = new JadwalSeniModel();
        if ($model->find($id) === null) {
            return redirect()->back()->with('status', false)->with('message', 'Jadwal tidak ditemukan.');
        }

        db_connect()->table('detail_jadwal_seni')->where('id_jadwal_seni', $id)->delete();
        $model->delete($id);

        return redirect()->to(base_url('admin/sekretariat/jadwal-seni'))->with('status', true)->with('message', 'Jadwal berhasil dihapus.');
    }

    private function viewData(array $data, string $title = 'Daftar Jadwal Seni'): array
    {
        return $data + [
            'title'      => $title,
            'activeMenu' => 'jadwal_seni',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
        ];
    }
}
