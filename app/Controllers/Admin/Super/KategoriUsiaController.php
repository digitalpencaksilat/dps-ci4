<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Models\KategoriUsiaModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

class KategoriUsiaController extends BaseController
{
    private KategoriUsiaModel $kategoriUsiaModel;

    public function __construct()
    {
        $this->kategoriUsiaModel = new KategoriUsiaModel();
    }

    public function index(): string
    {
        $rows = $this->kategoriUsiaModel
            ->orderBy('id_kategori_usia', 'ASC')
            ->findAll();

        return view('admin/super/kategori_usia/index', $this->viewData([
            'rows' => $rows,
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Kategori Usia'));
    }

    public function show(int $id): string
    {
        $row = $this->kategoriUsiaModel->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Kategori usia tidak ditemukan.');
        }

        return view('admin/super/kategori_usia/show', $this->viewData([
            'row' => $row,
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Detail Kategori Usia'));
    }

    public function edit(int $id): string
    {
        $row = $this->kategoriUsiaModel->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Kategori usia tidak ditemukan.');
        }

        return view('admin/super/kategori_usia/edit', $this->viewData([
            'row' => $row,
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
            'errors' => session()->getFlashdata('errors') ?? [],
        ], 'Edit Kategori Usia'));
    }

    public function store(): RedirectResponse
    {
        $rules = $this->rules();
        $jenisKelamin = (array) $this->request->getPost('jenis_kelamin');

        if ($jenisKelamin === []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', 'Pilih minimal satu jenis kelamin.');
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $baseData = $this->payload();
        unset($baseData['jenis_kelamin']);

        foreach ($jenisKelamin as $gender) {
            $gender = (string) $gender;
            if (! in_array($gender, ['putra', 'putri'], true)) {
                continue;
            }

            $this->kategoriUsiaModel->insert($baseData + ['jenis_kelamin' => $gender]);
        }

        return redirect()->to(base_url('admin/super/kategori-usia'))->with('status', true)->with('message', 'Kategori usia berhasil ditambahkan.');
    }

    public function update(int $id): RedirectResponse
    {
        if ($this->kategoriUsiaModel->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Kategori usia tidak ditemukan.');
        }

        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $this->kategoriUsiaModel->update($id, $this->payload(false));

        return redirect()->to(base_url('admin/super/kategori-usia'))->with('status', true)->with('message', 'Kategori usia berhasil diperbarui.');
    }

    public function delete(int $id): RedirectResponse
    {
        if ($this->kategoriUsiaModel->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Kategori usia tidak ditemukan.');
        }

        try {
            $this->kategoriUsiaModel->delete($id);
        } catch (\Throwable) {
            return redirect()->to(base_url('admin/super/kategori-usia'))->with('status', false)->with('message', 'Kategori usia tidak dapat dihapus karena masih digunakan data lain.');
        }

        return redirect()->to(base_url('admin/super/kategori-usia'))->with('status', true)->with('message', 'Kategori usia berhasil dihapus.');
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
     * @return array<string, string>
     */
    private function rules(bool $create = true): array
    {
        return [
            'nama_kategori_usia' => 'required|max_length[100]',
            'jenis_kelamin' => $create ? 'required' : 'required|in_list[putra,putri]',
            'min_umur' => 'required|integer|greater_than_equal_to[0]',
            'max_umur' => 'required|integer|greater_than_equal_to[0]',
            'acuan_tanggal' => 'permit_empty|valid_date[Y-m-d]',
        ];
    }

    /**
     * @return array<string, string|int|null>
     */
    private function payload(bool $includeGender = true): array
    {
        $data = [
            'nama_kategori_usia' => trim((string) $this->request->getPost('nama_kategori_usia')),
            'min_umur' => (int) $this->request->getPost('min_umur'),
            'max_umur' => (int) $this->request->getPost('max_umur'),
            'acuan_tanggal' => $this->request->getPost('acuan_tanggal') ?: null,
        ];

        if ($includeGender) {
            $data['jenis_kelamin'] = (string) $this->request->getPost('jenis_kelamin');
        }

        return $data;
    }
}
