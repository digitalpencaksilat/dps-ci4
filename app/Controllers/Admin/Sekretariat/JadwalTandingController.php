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

        // Validasi bracket bentrok sebelum render — TIDAK mengosongkan data,
        // hanya menampilkan banner peringatan agar super_admin bisa pakai tombol fix.
        $validasi = (new \App\Services\BracketBentrokService())->validasiJadwalSiapDitampilkan($id);
        $bracketBentrokError = ! $validasi['status'];
        $bracketBentrokMessage = $bracketBentrokError ? implode('<br>', $validasi['message']) : '';

        return view('admin/sekretariat/jadwal_tanding/show', $this->viewData([
            'jadwal'                => $jadwal,
            'details'               => $model->get_detail_jadwal($id),
            'peserta'               => (new \App\Models\PesertaTandingModel())->findAll(),
            'bracketBentrokError'   => $bracketBentrokError,
            'bracketBentrokMessage' => $bracketBentrokMessage,
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

    public function importExcel(int $id)
    {
        $model = new JadwalTandingModel();
        $jadwal = $model->find($id);
        if ($jadwal === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        $file = $this->request->getFile('file_excel');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => false, 'message' => 'File Excel tidak valid atau tidak ditemukan.']);
        }

        $allowedExtensions = ['xlsx', 'xls'];
        if (!in_array(strtolower($file->getExtension()), $allowedExtensions)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Format file harus .xlsx atau .xls']);
        }

        $service = new \App\Services\ImportJadwalTandingExcelService();

        try {
            $dataFromExcel = $service->parseExcelFile($file);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal membaca file: ' . $e->getMessage()]);
        }

        if (count($dataFromExcel) < 3) {
            return $this->response->setJSON(['status' => false, 'message' => 'File Excel kosong atau hanya berisi header.']);
        }

        $result = $service->validateExcelFormat($dataFromExcel, $id);

        if (!$result['status']) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $result['message'],
            ]);
        }

        // Simpan preview ke cache
        $token = $service->generateToken();
        $result['id_jadwal_tanding'] = $id;
        $service->savePreview($token, $result);

        // Hitung ringkasan
        $jumlahPartai = 0;
        foreach ($result['data_pertandingan'] as $v1) {
            foreach ($v1 as $v2) {
                foreach ($v2 as $v3) {
                    foreach ($v3 as $arrPool) {
                        $jumlahPartai += count($arrPool);
                    }
                }
            }
        }

        return $this->response->setJSON([
            'status' => true,
            'message' => "Validasi berhasil. Ditemukan $jumlahPartai partai siap diimport.",
            'token' => $token,
            'summary' => [
                'jumlah_partai' => $jumlahPartai,
                'jumlah_peserta' => count($result['peserta_tanding']),
                'jumlah_kontingen' => count(array_unique($result['kontingen'])),
            ],
        ]);
    }

    public function importExcelCommit(int $id)
    {
        $token = (string) $this->request->getPost('token');
        if (empty($token)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak valid.']);
        }

        $excelService = new \App\Services\ImportJadwalTandingExcelService();
        $payload = $excelService->loadPreview($token);

        if ($payload === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sesi import sudah kedaluwarsa. Silakan upload ulang.']);
        }

        if ((int)($payload['id_jadwal_tanding'] ?? 0) !== $id) {
            return $this->response->setJSON(['status' => false, 'message' => 'Token tidak sesuai dengan jadwal ini.']);
        }

        $commitService = new \App\Services\ImportJadwalTandingCommitService();
        $result = $commitService->commit($payload, $id);

        // Hapus preview setelah commit
        $excelService->deletePreview($token);

        return $this->response->setJSON($result);
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
