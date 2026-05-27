<?php

namespace App\Services\Admin\Super;

use CodeIgniter\HTTP\Files\UploadedFile;

class FileSettingService
{
    private SettingWriterService $writer;

    public function __construct()
    {
        $this->writer = new SettingWriterService();
    }

    /**
     * Store uploaded file under public/uploads and persist its public URL in DB setting.
     */
    public function storePublicFile(string $settingKey, UploadedFile $file, string $targetSubdir, array $allowedMimes, int $maxSizeKb): string
    {
        if (! $file->isValid()) {
            throw new \RuntimeException('File upload tidak valid.');
        }

        $mime = strtolower((string) $file->getMimeType());
        if (! in_array($mime, array_map('strtolower', $allowedMimes), true)) {
            throw new \RuntimeException('Tipe file tidak diizinkan.');
        }

        if ($file->getSizeByUnit('kb') > $maxSizeKb) {
            throw new \RuntimeException('Ukuran file melebihi batas.');
        }

        $targetDir = rtrim(FCPATH, '/') . '/uploads/' . trim($targetSubdir, '/');
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0775, true) && ! is_dir($targetDir)) {
            throw new \RuntimeException('Gagal membuat folder upload.');
        }

        $newName = $file->getRandomName();
        $file->move($targetDir, $newName, true);

        $publicUrl = base_url('uploads/' . trim($targetSubdir, '/') . '/' . $newName);
        $this->writer->setString($settingKey, (string) $publicUrl);

        return (string) $publicUrl;
    }

    public function setString(string $settingKey, string $value): void
    {
        $this->writer->setString($settingKey, $value);
    }
}
