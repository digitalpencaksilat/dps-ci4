<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\KontenLandingService;
use CodeIgniter\HTTP\RedirectResponse;

class KontenLandingController extends BaseController
{
    private KontenLandingService $service;

    public function __construct()
    {
        $this->service = new KontenLandingService();
    }

    public function edit(): string
    {
        return view('admin/super/pengaturan_event/konten_landing', $this->viewData([
            'fields' => $this->service->labels(),
            'values' => $this->service->currentValues(),
            'errors' => session()->getFlashdata('errors') ?? [],
        ], 'Konten Halaman Landing'));
    }

    public function update(): RedirectResponse
    {
        if (! $this->validate($this->service->rules())) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $payload = [];
        foreach (array_keys($this->service->labels()) as $field) {
            $payload[$field] = $this->request->getPost($field);
        }

        $this->service->save($payload);

        return redirect()->to(base_url('admin/super/pengaturan-event/konten-landing'))
            ->with('status', true)
            ->with('message', 'Konten landing berhasil diperbarui.');
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
