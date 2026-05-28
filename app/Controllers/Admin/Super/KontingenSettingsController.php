<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\KontingenSettingsService;
use CodeIgniter\HTTP\RedirectResponse;

class KontingenSettingsController extends BaseController
{
    private KontingenSettingsService $service;

    public function __construct()
    {
        $this->service = new KontingenSettingsService();
    }

    public function edit(): string
    {
        return view('admin/super/pengaturan_event/pengaturan_kontingen', $this->viewData([
            'fields' => $this->service->fields(),
            'values' => $this->service->currentValues(),
            'errors' => session()->getFlashdata('errors') ?? [],
        ], 'Pengaturan Kontingen'));
    }

    public function update(): RedirectResponse
    {
        $payload = [];
        foreach ($this->service->fields() as $field => $meta) {
            $value = (string) ($this->request->getPost($field) ?? '');
            $payload[$field] = ($meta['type'] ?? '') === 'currency'
                ? preg_replace('/\D+/', '', $value)
                : $value;
        }

        if (! $this->validateData($payload, $this->service->rules())) {
            return redirect()->back()
                ->withInput()
                ->with('status', false)
                ->with('message', $this->validator->getErrors())
                ->with('errors', $this->validator->getErrors());
        }

        $this->service->save($payload);

        return redirect()->to(base_url('admin/super/pengaturan-event/pengaturan-kontingen'))
            ->with('status', true)
            ->with('message', 'Pengaturan biaya kontingen dan max atlet berhasil diperbarui.');
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
