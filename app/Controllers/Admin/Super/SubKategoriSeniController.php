<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Models\KategoriLombaModel;
use App\Models\KompetisiSeniModel;
use App\Models\SubKategoriSeniModel;
use App\Services\Admin\Super\SubKategoriSeniService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class SubKategoriSeniController extends BaseController
{
    private KategoriLombaModel $kategoriLombaModel;
    private KompetisiSeniModel $kompetisiSeniModel;
    private SubKategoriSeniModel $subKategoriSeniModel;
    private SubKategoriSeniService $subKategoriSeniService;

    public function __construct()
    {
        $this->kategoriLombaModel = new KategoriLombaModel();
        $this->kompetisiSeniModel = new KompetisiSeniModel();
        $this->subKategoriSeniModel = new SubKategoriSeniModel();
        $this->subKategoriSeniService = new SubKategoriSeniService();
    }

    public function index(): string
    {
        return view('admin/super/sub_kategori_seni/index', $this->viewData([
            'rows' => $this->listRows(),
            'kategoriLombaRows' => $this->seniKategoriLombaRows(),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Sub Kategori Seni'));
    }

    public function show(int $id): string
    {
        $row = $this->findDetail($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Sub kategori seni tidak ditemukan.');
        }

        $poolRows = $this->kompetisiSeniModel
            ->where('id_sub_kategori_seni', $id)
            ->orderBy('nomor_pool', 'ASC')
            ->findAll();

        return view('admin/super/sub_kategori_seni/show', $this->viewData([
            'row' => $row,
            'poolRows' => $poolRows,
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Detail Sub Kategori Seni'));
    }

    public function edit(int $id): string
    {
        $row = $this->findDetail($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Sub kategori seni tidak ditemukan.');
        }

        return view('admin/super/sub_kategori_seni/edit', $this->viewData([
            'row' => $row,
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
            'errors' => session()->getFlashdata('errors') ?? [],
        ], 'Edit Sub Kategori Seni'));
    }

    public function store(): RedirectResponse
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $kategoriLombaIds = array_values(array_unique(array_filter(array_map('intval', (array) $this->request->getPost('id_kategori_lomba')))));
        if ($kategoriLombaIds === []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Pilih minimal satu kategori lomba seni.');
        }

        $validKategoriLombaIds = array_map(static fn ($row): int => (int) $row->id_kategori_lomba, $this->seniKategoriLombaRows());
        $kategoriLombaIds = array_values(array_intersect($kategoriLombaIds, $validKategoriLombaIds));
        if ($kategoriLombaIds === []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Kategori lomba seni tidak valid.');
        }

        try {
            $this->subKategoriSeniService->createWithInitialPools($kategoriLombaIds, $this->payload(), (int) $this->request->getPost('max_peserta'));
        } catch (\Throwable) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Sub kategori seni gagal ditambahkan atau pool awal gagal dibuat.');
        }

        return redirect()->to(base_url('admin/super/sub-kategori-seni'))->with('status', true)->with('message', 'Sub kategori seni dan pool awal berhasil ditambahkan.');
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->subKategoriSeniModel->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Sub kategori seni tidak ditemukan.');
        }

        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $this->subKategoriSeniModel->update($id, $this->payload());

        return redirect()->to(base_url('admin/super/sub-kategori-seni/' . $id . '/edit'))->with('status', true)->with('message', 'Sub kategori seni berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($this->subKategoriSeniModel->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Sub kategori seni tidak ditemukan.');
        }

        try {
            $this->subKategoriSeniModel->delete($id);
        } catch (\Throwable) {
            return redirect()->to(base_url('admin/super/sub-kategori-seni'))->with('status', false)->with('message', 'Sub kategori seni tidak dapat dihapus karena masih digunakan data lain.');
        }

        return redirect()->to(base_url('admin/super/sub-kategori-seni'))->with('status', true)->with('message', 'Sub kategori seni berhasil dihapus.');
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

    /**
     * @return list<object>
     */
    private function listRows(): array
    {
        return db_connect()
            ->table('sub_kategori_seni sks')
            ->select('sks.*, kl.nama_kategori_lomba, kl.jenis_perlombaan, kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT COUNT(*) FROM kompetisi_seni ks WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni) AS jumlah_pool', false)
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('ku.id_kategori_usia', 'ASC')
            ->orderBy('kl.id_kategori_lomba', 'ASC')
            ->orderBy('sks.id_sub_kategori_seni', 'ASC')
            ->get()
            ->getResult();
    }

    private function findDetail(int $id): ?object
    {
        return db_connect()
            ->table('sub_kategori_seni sks')
            ->select('sks.*, kl.nama_kategori_lomba, kl.jenis_perlombaan, kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->where('sks.id_sub_kategori_seni', $id)
            ->get()
            ->getRow();
    }

    /**
     * @return list<object>
     */
    private function seniKategoriLombaRows(): array
    {
        return $this->kategoriLombaModel
            ->select('kategori_lomba.*, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kategori_lomba.id_kategori_usia', 'left')
            ->where('LOWER(kategori_lomba.nama_kategori_lomba)', 'seni')
            ->orderBy('ku.id_kategori_usia', 'ASC')
            ->orderBy('kategori_lomba.id_kategori_lomba', 'ASC')
            ->findAll();
    }

    /**
     * @return array<string, string>
     */
    private function rules(bool $create = true): array
    {
        return [
            'id_kategori_lomba' => $create ? 'required' : 'permit_empty',
            'nama_seni' => 'required|max_length[100]',
            'jenis_seni' => 'required|in_list[tunggal,ganda,beregu,solo kreatif,perorangan,berpasangan,berkelompok]',
            'jumlah_peserta' => 'required|integer|greater_than[0]',
            'waktu' => 'permit_empty|integer|greater_than_equal_to[0]',
            'biaya_pendaftaran_dn' => 'permit_empty|numeric|greater_than_equal_to[0]',
            'biaya_pendaftaran_ln' => 'permit_empty|numeric|greater_than_equal_to[0]',
            'format_penilaian' => 'permit_empty',
            'sistem_penampilan' => 'required|in_list[pool,battle]',
            'keterangan' => 'permit_empty|max_length[255]',
            'max_peserta' => $create ? 'required|integer|greater_than[0]' : 'permit_empty',
        ];
    }

    /**
     * @return array<string, string|int|float|null>
     */
    private function payload(bool $includeJenisSeni = true): array
    {
        $data = [
            'nama_seni' => trim((string) $this->request->getPost('nama_seni')),
            'jumlah_peserta' => (int) $this->request->getPost('jumlah_peserta'),
            'waktu' => $this->request->getPost('waktu') === '' ? null : (int) $this->request->getPost('waktu'),
            'biaya_pendaftaran_dn' => $this->request->getPost('biaya_pendaftaran_dn') === '' ? null : (float) $this->request->getPost('biaya_pendaftaran_dn'),
            'biaya_pendaftaran_ln' => $this->request->getPost('biaya_pendaftaran_ln') === '' ? null : (float) $this->request->getPost('biaya_pendaftaran_ln'),
            'format_penilaian' => 'persilat.json',
            'sistem_penampilan' => (string) $this->request->getPost('sistem_penampilan'),
            'keterangan' => trim((string) $this->request->getPost('keterangan')),
        ];

        if ($includeJenisSeni) {
            $data['jenis_seni'] = (string) $this->request->getPost('jenis_seni');
        }

        return $data;
    }
}
