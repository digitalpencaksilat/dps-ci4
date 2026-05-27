<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\AksesPemilihanKategoriService;
use CodeIgniter\HTTP\RedirectResponse;

class AksesPemilihanKategoriController extends BaseController
{
    private AksesPemilihanKategoriService $service;

    public function __construct()
    {
        $this->service = new AksesPemilihanKategoriService();
    }

    public function edit(): string
    {
        return view('admin/super/pengaturan_event/akses_pemilihan_kategori', $this->viewData([
            'fields' => $this->service->fields(),
            'values' => $this->service->currentValues(),
            'errors' => session()->getFlashdata('errors') ?? [],
        ], 'Akses Pemilihan Kategori Perlombaan'));
    }

    public function update(): RedirectResponse
    {
        if (! $this->validate($this->service->rules())) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $payload = [];
        foreach (array_keys($this->service->fields()) as $field) {
            $payload[$field] = $this->request->getPost($field) !== null;
        }

        $this->service->save($payload);

        return redirect()->to(base_url('admin/super/pengaturan-event/akses-pemilihan-kategori'))
            ->with('status', true)
            ->with('message', 'Pengaturan akses pemilihan kategori berhasil diperbarui.');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function viewData(array $data, string $title): array
    {
        return $data + [
            'title' => $title,
            'activeMenu' => 'pengaturan_event',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ];
    }
}
