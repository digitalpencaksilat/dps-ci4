<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\FileSettingService;
use App\Services\Admin\Super\GambarDanJuknisService;
use CodeIgniter\HTTP\RedirectResponse;

class GambarDanJuknisController extends BaseController
{
    private GambarDanJuknisService $service;
    private FileSettingService $fileSetting;

    public function __construct()
    {
        $this->service = new GambarDanJuknisService();
        $this->fileSetting = new FileSettingService();
    }

    public function edit(): string
    {
        return view('admin/super/pengaturan_event/gambar_dan_juknis', $this->viewData([
            'files' => $this->service->fileDefinitions(),
            'values' => $this->service->currentValues(),
            'errors' => session()->getFlashdata('errors') ?? [],
        ], 'Gambar dan Juknis'));
    }

    public function update(): RedirectResponse
    {
        $defs = $this->service->fileDefinitions();
        $errors = [];

        foreach ($defs as $key => $def) {
            $uploaded = $this->request->getFile($key);
            if ($uploaded && $uploaded->isValid() && ! $uploaded->hasMoved()) {
                try {
                    $this->fileSetting->storePublicFile($key, $uploaded, $def['subdir'], $def['mimes'], $def['maxKb']);
                } catch (\Throwable $e) {
                    $errors[$key] = $e->getMessage();
                }
            }
        }

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $errors)->with('errors', $errors);
        }

        return redirect()->to(base_url('admin/super/pengaturan-event/gambar-dan-juknis'))
            ->with('status', true)
            ->with('message', 'Pengaturan gambar dan juknis berhasil diperbarui.');
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
