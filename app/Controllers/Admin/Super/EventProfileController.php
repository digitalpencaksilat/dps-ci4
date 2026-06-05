<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\EventProfileService;
use CodeIgniter\HTTP\RedirectResponse;

class EventProfileController extends BaseController
{
    private EventProfileService $eventProfileService;

    public function __construct()
    {
        $this->eventProfileService = new EventProfileService();
    }

    public function edit(): string
    {
        return view('admin/super/pengaturan_event/profil_kejuaraan', $this->viewData([
            'fields' => $this->eventProfileService->labels(),
            'values' => $this->eventProfileService->currentValues(),
            'errors' => session()->getFlashdata('errors') ?? [],
            'categoryCards' => $this->eventProfileService->getCategoryCards(),
        ], 'Profil Kejuaraan'));
    }

    public function update(): RedirectResponse
    {
        $rules = $this->eventProfileService->rules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $payload = [];
        foreach (array_keys($this->eventProfileService->labels()) as $field) {
            $payload[$field] = $this->request->getPost($field);
        }

        $this->eventProfileService->save($payload);

        $cardKeys = ['tanding', 'tunggal', 'ganda', 'regu'];
        $activeMap = [];
        foreach ($cardKeys as $key) {
            $activeMap[$key] = (bool) $this->request->getPost('card_' . $key);
        }
        $this->eventProfileService->saveCategoryCards($activeMap);

        return redirect()->to(base_url('admin/super/pengaturan-event/profil-kejuaraan'))->with('status', true)->with('message', 'Profil kejuaraan berhasil diperbarui.');
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
