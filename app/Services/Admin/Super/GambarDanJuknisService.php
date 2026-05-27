<?php

namespace App\Services\Admin\Super;

class GambarDanJuknisService
{
    /**
     * @var array<string, array{label:string, subdir:string, mimes:string[], maxKb:int}>
     */
    private array $files = [
        'technical_handbook' => [
            'label' => 'Technical Handbook (PDF/DOC)',
            'subdir' => 'juknis',
            'mimes' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'maxKb' => 15000,
        ],
        'poster' => [
            'label' => 'Poster Event (PNG/JPG)',
            'subdir' => 'assets',
            'mimes' => ['image/png', 'image/jpeg'],
            'maxKb' => 5000,
        ],
        'event_big_logo' => [
            'label' => 'Event Big Logo (PNG/JPG)',
            'subdir' => 'assets',
            'mimes' => ['image/png', 'image/jpeg'],
            'maxKb' => 5000,
        ],
        'event_logo' => [
            'label' => 'Event Logo (PNG/JPG)',
            'subdir' => 'assets',
            'mimes' => ['image/png', 'image/jpeg'],
            'maxKb' => 5000,
        ],
        'event_host_big_logo' => [
            'label' => 'Logo Penyelenggara Besar (PNG/JPG)',
            'subdir' => 'assets',
            'mimes' => ['image/png', 'image/jpeg'],
            'maxKb' => 5000,
        ],
        'event_host_logo' => [
            'label' => 'Logo Penyelenggara (PNG/JPG)',
            'subdir' => 'assets',
            'mimes' => ['image/png', 'image/jpeg'],
            'maxKb' => 5000,
        ],
    ];

    private FileSettingService $fileSetting;

    public function __construct()
    {
        $this->fileSetting = new FileSettingService();
    }

    /**
     * @return array<string, array{label:string, subdir:string, mimes:string[], maxKb:int}>
     */
    public function fileDefinitions(): array
    {
        return $this->files;
    }

    /**
     * @return array<string, string>
     */
    public function currentValues(): array
    {
        $values = [];
        foreach (array_keys($this->files) as $key) {
            $values[$key] = (string) (get_setting($key, 'pendaftaran/gambar_dan_juknis')
                ?? $this->ci3DefaultUrl($key)
                ?? '');
        }
        return $values;
    }

    private function ci3DefaultUrl(string $key): ?string
    {
        // CI3 config stores file_name + upload_path. We'll best-effort map to /uploads/... public.
        $fileName = ci3_config_item($key . '.file_name', 'pendaftaran/gambar_dan_juknis');
        $uploadPath = ci3_config_item($key . '.upload_path', 'pendaftaran/gambar_dan_juknis');
        if (! is_string($fileName) || trim($fileName) === '') {
            return null;
        }
        if (! is_string($uploadPath) || trim($uploadPath) === '') {
            return null;
        }

        // Example CI3: ./uploads/assets/
        $uploadPath = str_replace('\\', '/', $uploadPath);
        $uploadPath = preg_replace('#^\./#', '', $uploadPath);
        $uploadPath = trim((string) $uploadPath, '/');

        return base_url($uploadPath . '/' . ltrim($fileName, '/'));
    }
}
