<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Models\KategoriLombaModel;
use App\Models\KategoriUsiaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class KategoriLombaController extends BaseController
{
    private KategoriLombaModel $kategoriLombaModel;
    private KategoriUsiaModel $kategoriUsiaModel;

    public function __construct()
    {
        $this->kategoriLombaModel = new KategoriLombaModel();
        $this->kategoriUsiaModel = new KategoriUsiaModel();
    }

    public function index(): string
    {
        $rows = $this->listRows();
        $kategoriUsiaRows = $this->kategoriUsiaModel->orderBy('id_kategori_usia', 'ASC')->findAll();

        return view('admin/super/kategori_lomba/index', $this->viewData([
            'rows' => $rows,
            'kategoriUsiaRows' => $kategoriUsiaRows,
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Kategori Lomba'));
    }

    public function edit(int $id): string
    {
        $row = $this->kategoriLombaModel->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Kategori lomba tidak ditemukan.');
        }

        return view('admin/super/kategori_lomba/edit', $this->viewData([
            'row' => $row,
            'kategoriUsiaRows' => $this->kategoriUsiaModel->orderBy('id_kategori_usia', 'ASC')->findAll(),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Edit Kategori Lomba'));
    }

    public function store(): RedirectResponse
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        $kategoriUsiaIds = array_values(array_filter(array_map('intval', (array) $this->request->getPost('id_kategori_usia'))));
        if ($kategoriUsiaIds === []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Pilih minimal satu kategori usia.');
        }

        $baseData = $this->payload(false);

        foreach ($kategoriUsiaIds as $kategoriUsiaId) {
            if ($this->kategoriUsiaModel->find($kategoriUsiaId) === null) {
                continue;
            }

            $this->kategoriLombaModel->insert($baseData + ['id_kategori_usia' => $kategoriUsiaId]);
        }

        return redirect()->to(base_url('admin/super/kategori-lomba'))->with('status', true)->with('message', 'Kategori lomba berhasil ditambahkan.');
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->kategoriLombaModel->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Kategori lomba tidak ditemukan.');
        }

        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        $this->kategoriLombaModel->update($id, $this->payload(false));

        return redirect()->to(base_url('admin/super/kategori-lomba'))->with('status', true)->with('message', 'Kategori lomba berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($this->kategoriLombaModel->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Kategori lomba tidak ditemukan.');
        }

        try {
            $this->kategoriLombaModel->delete($id);
        } catch (\Throwable) {
            return redirect()->to(base_url('admin/super/kategori-lomba'))->with('status', false)->with('message', 'Kategori lomba tidak dapat dihapus karena masih digunakan data lain.');
        }

        return redirect()->to(base_url('admin/super/kategori-lomba'))->with('status', true)->with('message', 'Kategori lomba berhasil dihapus.');
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
            ->table('kategori_lomba kl')
            ->select('kl.*, ku.nama_kategori_usia, ku.jenis_kelamin, ku.min_umur, ku.max_umur, ku.acuan_tanggal')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('ku.id_kategori_usia', 'ASC')
            ->orderBy('kl.id_kategori_lomba', 'ASC')
            ->get()
            ->getResult();
    }

    /**
     * @return array<string, string>
     */
    private function rules(bool $create = true): array
    {
        return [
            'id_kategori_usia' => 'required',
            'nama_kategori_lomba' => 'required|max_length[100]',
            'peraturan_pertandingan' => 'permit_empty',
            'jenis_perlombaan' => 'required|max_length[100]',
            'jumlah_juri' => 'permit_empty|integer|greater_than_equal_to[0]',
            'semua_dapat_medali' => 'required|in_list[0,1]',
            'kuota_peserta' => 'permit_empty|integer|greater_than_equal_to[0]',
        ];
    }

    /**
     * @return array<string, string|int|null>
     */
    private function payload(bool $singleKategoriUsia = true): array
    {
        $data = [
            'nama_kategori_lomba' => trim((string) $this->request->getPost('nama_kategori_lomba')),
            'peraturan_pertandingan' => 'PERSILAT',
            'jenis_perlombaan' => trim((string) $this->request->getPost('jenis_perlombaan')),
            'jumlah_juri' => $this->request->getPost('jumlah_juri') === '' ? null : (int) $this->request->getPost('jumlah_juri'),
            'semua_dapat_medali' => (int) $this->request->getPost('semua_dapat_medali'),
            'kuota_peserta' => $this->request->getPost('kuota_peserta') === '' ? null : (int) $this->request->getPost('kuota_peserta'),
        ];

        if ($singleKategoriUsia) {
            $data['id_kategori_usia'] = (int) $this->request->getPost('id_kategori_usia');
        }

        return $data;
    }
}
