<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Models\KategoriLombaModel;
use App\Models\KelasTandingModel;
use App\Services\Admin\Super\KelasTandingService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class KelasTandingController extends BaseController
{
    private KategoriLombaModel $kategoriLombaModel;
    private KelasTandingModel $kelasTandingModel;
    private KelasTandingService $service;

    public function __construct()
    {
        $this->kategoriLombaModel = new KategoriLombaModel();
        $this->kelasTandingModel = new KelasTandingModel();
        $this->service = new KelasTandingService();
    }

    public function index(): string
    {
        return view('admin/super/kelas_tanding/index', $this->viewData([
            'rows' => $this->service->listKelas(),
            'kategoriLombaRows' => $this->tandingKategoriLombaRows(),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Kelas Tanding'));
    }

    public function show(int $id): string
    {
        $row = $this->service->getKelas($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Kelas tanding tidak ditemukan.');
        }

        return view('admin/super/kelas_tanding/show', $this->viewData([
            'row' => $row,
            'poolRows' => $this->service->listPoolByKelas($id),
            'pesertaRows' => $this->service->listPesertaByKelas($id),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Detail Kelas Tanding'));
    }

    public function edit(int $id): string
    {
        $row = $this->service->getKelas($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Kelas tanding tidak ditemukan.');
        }

        return view('admin/super/kelas_tanding/edit', $this->viewData([
            'row' => $row,
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
            'errors' => session()->getFlashdata('errors') ?? [],
        ], 'Edit Kelas Tanding'));
    }

    public function store(): RedirectResponse
    {
        if (! $this->validate($this->singleRules())) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $kategoriLombaIds = $this->validKategoriLombaIds();
        if ($kategoriLombaIds === []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Pilih minimal satu kategori lomba tanding.');
        }

        try {
            $this->service->createSingle($kategoriLombaIds, $this->payload(), (int) $this->request->getPost('max_peserta'));
        } catch (\Throwable) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Kelas tanding gagal ditambahkan atau pool awal gagal dibuat.');
        }

        return redirect()->to(base_url('admin/super/kelas-tanding'))->with('status', true)->with('message', 'Kelas tanding berhasil ditambahkan.');
    }

    public function storeMultiple(): RedirectResponse
    {
        if (! $this->validate($this->multipleRules())) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $kategoriLombaIds = $this->validKategoriLombaIds();
        if ($kategoriLombaIds === []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Pilih minimal satu kategori lomba tanding.');
        }

        try {
            $this->service->createMultiple($kategoriLombaIds, $this->basePayload(), [
                'label_awal' => strtoupper(substr((string) $this->request->getPost('label_awal'), 0, 1)),
                'selisih_berat' => $this->normalizedNumber('selisih_berat'),
                'jumlah_kelas' => (int) $this->request->getPost('jumlah_kelas'),
                'berat_awal' => $this->normalizedNumber('berat_awal'),
                'kelas_bebas' => (int) $this->request->getPost('kelas_bebas'),
                'kelas_mini' => (int) $this->request->getPost('kelas_mini'),
            ], (int) $this->request->getPost('max_peserta'));
        } catch (\Throwable) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Generate kelas tanding gagal.');
        }

        return redirect()->to(base_url('admin/super/kelas-tanding'))->with('status', true)->with('message', 'Kelas tanding berhasil digenerate.');
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->kelasTandingModel->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Kelas tanding tidak ditemukan.');
        }

        if (! $this->validate($this->updateRules())) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $this->service->updateKelas($id, $this->payload(true));

        return redirect()->to(base_url('admin/super/kelas-tanding/' . $id . '/edit'))->with('status', true)->with('message', 'Kelas tanding berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($this->kelasTandingModel->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Kelas tanding tidak ditemukan.');
        }

        try {
            $this->service->deleteKelas($id);
        } catch (\Throwable) {
            return redirect()->to(base_url('admin/super/kelas-tanding'))->with('status', false)->with('message', 'Kelas tanding tidak dapat dihapus karena masih digunakan data lain.');
        }

        return redirect()->to(base_url('admin/super/kelas-tanding'))->with('status', true)->with('message', 'Kelas tanding berhasil dihapus.');
    }

    public function autoTambahPool(int $id): RedirectResponse
    {
        try {
            $maxPeserta = $this->request->getPost('max_peserta') === '' ? null : (int) $this->request->getPost('max_peserta');
            $this->service->autoTambahPool($id, $maxPeserta);
        } catch (\Throwable) {
            return redirect()->back()->with('status', false)->with('message', 'Pool baru gagal ditambahkan.');
        }

        return redirect()->to(base_url('admin/super/kelas-tanding/' . $id))->with('status', true)->with('message', 'Pool baru berhasil ditambahkan.');
    }

    public function updateJumlahPesertaPerPool(): RedirectResponse
    {
        if (! $this->validate(['id_kategori_lomba' => 'required', 'max_peserta' => 'required|integer|greater_than[0]'])) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            $this->service->updateJumlahPesertaPerPool($this->validKategoriLombaIds(), (int) $this->request->getPost('max_peserta'), $this->request->getPost('otomatis_distribusi') !== null);
        } catch (\Throwable $error) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $error->getMessage());
        }

        return redirect()->to(base_url('admin/super/kelas-tanding'))->with('status', true)->with('message', 'Kategori tanding berhasil diubah.');
    }

    private function viewData(array $data, string $title): array
    {
        return $data + [
            'title' => $title,
            'activeMenu' => 'pengaturan_kategori_lomba',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
        ];
    }

    private function tandingKategoriLombaRows(): array
    {
        return $this->kategoriLombaModel
            ->select('kategori_lomba.*, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kategori_lomba.id_kategori_usia', 'left')
            ->where('LOWER(kategori_lomba.nama_kategori_lomba)', 'tanding')
            ->orderBy('ku.id_kategori_usia', 'ASC')
            ->orderBy('kategori_lomba.id_kategori_lomba', 'ASC')
            ->findAll();
    }

    private function validKategoriLombaIds(): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('id_kategori_lomba')))));
        $validIds = array_map(static fn ($row): int => (int) $row->id_kategori_lomba, $this->tandingKategoriLombaRows());

        return array_values(array_intersect($ids, $validIds));
    }

    private function singleRules(): array
    {
        return $this->updateRules() + [
            'id_kategori_lomba' => 'required',
            'max_peserta' => 'required|integer|greater_than[0]',
        ];
    }

    private function multipleRules(): array
    {
        return [
            'id_kategori_lomba' => 'required',
            'label_awal' => 'required|alpha|max_length[1]',
            'selisih_berat' => 'required|numeric|greater_than[0]',
            'jumlah_kelas' => 'required|integer|greater_than[0]',
            'berat_awal' => 'required|numeric|greater_than_equal_to[0]',
            'jumlah_ronde' => 'required|integer|greater_than[0]',
            'waktu_per_ronde' => 'required|integer|greater_than[0]',
            'waktu_istirahat' => 'required|integer|greater_than_equal_to[0]',
            'juara_tiga_bersama' => 'required|in_list[0,1]',
            'biaya_pendaftaran_dn' => 'permit_empty|regex_match[/^[0-9\\.,]+$/]',
            'biaya_pendaftaran_ln' => 'permit_empty|regex_match[/^[0-9\\.,]+$/]',
            'max_peserta' => 'required|integer|greater_than[0]',
        ];
    }

    private function updateRules(): array
    {
        return [
            'label' => 'required|max_length[100]',
            'berat_minimal' => 'required|numeric|greater_than_equal_to[0]',
            'berat_maksimal' => 'required|numeric|greater_than_equal_to[0]',
            'jumlah_ronde' => 'required|integer|greater_than[0]',
            'waktu_per_ronde' => 'required|integer|greater_than[0]',
            'waktu_istirahat' => 'required|integer|greater_than_equal_to[0]',
            'juara_tiga_bersama' => 'required|in_list[0,1]',
            'biaya_pendaftaran_dn' => 'permit_empty|regex_match[/^[0-9\\.,]+$/]',
            'biaya_pendaftaran_ln' => 'permit_empty|regex_match[/^[0-9\\.,]+$/]',
            'keterangan' => 'permit_empty|max_length[255]',
        ];
    }

    private function payload(bool $includeKeterangan = false): array
    {
        $data = $this->basePayload() + [
            'label' => trim((string) $this->request->getPost('label')),
            'berat_minimal' => $this->normalizedNumber('berat_minimal'),
            'berat_maksimal' => $this->normalizedNumber('berat_maksimal'),
        ];

        if ($includeKeterangan) {
            $data['keterangan'] = trim((string) $this->request->getPost('keterangan'));
        }

        return $data;
    }

    private function basePayload(): array
    {
        return [
            'jumlah_ronde' => (int) $this->request->getPost('jumlah_ronde'),
            'waktu_per_ronde' => (int) $this->request->getPost('waktu_per_ronde'),
            'waktu_istirahat' => (int) $this->request->getPost('waktu_istirahat'),
            'juara_tiga_bersama' => (int) $this->request->getPost('juara_tiga_bersama'),
            'format_penilaian' => 'PERSILAT',
            'biaya_pendaftaran_dn' => $this->request->getPost('biaya_pendaftaran_dn') === '' ? null : $this->normalizedCurrency('biaya_pendaftaran_dn'),
            'biaya_pendaftaran_ln' => $this->request->getPost('biaya_pendaftaran_ln') === '' ? null : $this->normalizedCurrency('biaya_pendaftaran_ln'),
        ];
    }

    private function normalizedNumber(string $key): float
    {
        return (float) str_replace(',', '.', (string) $this->request->getPost($key));
    }

    private function normalizedCurrency(string $key): float
    {
        return (float) str_replace(['.', ','], ['', '.'], (string) $this->request->getPost($key));
    }
}
