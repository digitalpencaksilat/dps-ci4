<?php

namespace App\Services;

use App\Models\ArsipPendaftarModel;

class ArsipPendaftarService
{
    public function syncUploads(int $idPendaftar, array $files): void
    {
        $slots = get_active_arsip_pendaftar_ci4();
        $required = get_required_arsip_pendaftar_ci4();

        foreach ($required as $slotName => $slot) {
            $fieldName = $this->fieldName($slot['nama_arsip'] ?? $slotName);
            $uploaded = $files[$fieldName] ?? null;
            $existing = $this->findByJenisArsip($idPendaftar, (string) ($slot['nama_arsip'] ?? ''));

            if (($uploaded === null || ! $uploaded->isValid() || $uploaded->getError() === UPLOAD_ERR_NO_FILE) && $existing === null) {
                throw new \RuntimeException('Arsip ' . ($slot['nama_arsip'] ?? $slotName) . ' wajib diunggah.');
            }
        }

        foreach ($slots as $slotName => $slot) {
            $jenisArsip = (string) ($slot['nama_arsip'] ?? $slotName);
            $fieldName = $this->fieldName($jenisArsip);
            $uploaded = $files[$fieldName] ?? null;

            if ($uploaded === null || ! $uploaded->isValid() || $uploaded->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $this->validateUpload($uploaded, $slot, $jenisArsip);
            $savedName = $this->storeFile($uploaded);

            $existing = $this->findByJenisArsip($idPendaftar, $jenisArsip);
            if ($existing !== null) {
                $this->deletePhysicalFile($existing->nama_arsip);
                (new ArsipPendaftarModel())->update($existing->id_arsip_pendaftar, ['nama_arsip' => $savedName]);
            } else {
                (new ArsipPendaftarModel())->insert([
                    'id_pendaftar' => $idPendaftar,
                    'nama_arsip'   => $savedName,
                    'jenis_arsip'  => $jenisArsip,
                ]);
            }
        }
    }

    private function validateUpload($uploaded, array $slot, string $jenisArsip): void
    {
        if (! $uploaded->isValid()) {
            throw new \RuntimeException('Upload arsip ' . $jenisArsip . ' tidak valid.');
        }

        $allowed = array_map('strtolower', explode('|', (string) ($slot['allowed_types'] ?? '')));
        $ext = strtolower((string) $uploaded->getExtension());
        if ($allowed !== [''] && ! in_array($ext, $allowed, true)) {
            throw new \RuntimeException('Tipe file untuk arsip ' . $jenisArsip . ' tidak diizinkan.');
        }

        $mime = strtolower((string) $uploaded->getMimeType());
        $allowedMimeMap = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        ];

        if (isset($allowedMimeMap[$ext])) {
            $allowedMimes = $allowedMimeMap[$ext];
            if (! in_array($mime, $allowedMimes, true)) {
                throw new \RuntimeException('MIME type file untuk arsip ' . $jenisArsip . ' tidak sesuai.');
            }
        }

        $maxKb = (int) ($slot['max_size'] ?? 0);
        if ($maxKb > 0 && ($uploaded->getSizeByUnit('kb') > $maxKb)) {
            throw new \RuntimeException('Ukuran file untuk arsip ' . $jenisArsip . ' melebihi batas ' . $maxKb . ' KB.');
        }
    }

    private function storeFile($uploaded): string
    {
        $targetDir = FCPATH . 'uploads/peserta/arsip';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetIndex = $targetDir . '/index.html';
        if (! is_file($targetIndex)) {
            file_put_contents($targetIndex, '');
        }

        $name = $uploaded->getRandomName();
        $uploaded->move($targetDir, $name, true);

        return $name;
    }

    private function deletePhysicalFile(string $fileName): void
    {
        $path = FCPATH . 'uploads/peserta/arsip/' . $fileName;
        if (is_file($path)) {
            unlink($path);
        }
    }

    private function fieldName(string $namaArsip): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($namaArsip)) ?? 'arsip');
    }

    private function findByJenisArsip(int $idPendaftar, string $jenisArsip): ?object
    {
        return (new ArsipPendaftarModel())
            ->where('id_pendaftar', $idPendaftar)
            ->where('jenis_arsip', $jenisArsip)
            ->first();
    }
}
