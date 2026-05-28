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

        $allowedMimesLower = array_map('strtolower', $allowedMimes);
        $mime = strtolower((string) $file->getMimeType());

        // Some browsers/servers may report generic MIME (e.g. application/octet-stream).
        // If MIME is not in the allowlist, fall back to extension-based allow for known types.
        if (! in_array($mime, $allowedMimesLower, true)) {
            $ext = strtolower((string) $file->getExtension());
            $allowedExt = [];
            foreach ($allowedMimesLower as $m) {
                if ($m === 'application/pdf') {
                    $allowedExt[] = 'pdf';
                } elseif ($m === 'application/msword') {
                    $allowedExt[] = 'doc';
                } elseif ($m === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
                    $allowedExt[] = 'docx';
                } elseif ($m === 'image/jpeg') {
                    $allowedExt[] = 'jpg';
                    $allowedExt[] = 'jpeg';
                } elseif ($m === 'image/png') {
                    $allowedExt[] = 'png';
                }
            }

            if ($ext === '' || ! in_array($ext, array_unique($allowedExt), true)) {
                throw new \RuntimeException('Tipe file tidak diizinkan.');
            }
        }

        $sizeBytes = (int) $file->getSize();
        $maxBytes = $maxSizeKb * 1024;
        if ($sizeBytes <= 0 || $sizeBytes > $maxBytes) {
            $sizeKb = round($sizeBytes / 1024, 2);
            throw new \RuntimeException('Ukuran file melebihi batas. Maks ' . $maxSizeKb . ' KB, ukuran file ' . $sizeKb . ' KB.');
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
