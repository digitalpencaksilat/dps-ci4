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
            $savedName = $this->storeFile($uploaded, $idPendaftar, $jenisArsip);

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

        $ext = strtolower((string) $uploaded->getExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            throw new \RuntimeException('Arsip ' . $jenisArsip . ' hanya boleh berupa file JPG, JPEG, atau PNG.');
        }

        $mime = strtolower((string) $uploaded->getMimeType());
        if (! in_array($mime, ['image/jpeg', 'image/png'], true)) {
            throw new \RuntimeException('MIME type file untuk arsip ' . $jenisArsip . ' tidak sesuai.');
        }

        $maxKb = (int) ($slot['max_size'] ?? 0);
        if ($maxKb > 0 && ($uploaded->getSizeByUnit('kb') > $maxKb)) {
            throw new \RuntimeException('Ukuran file untuk arsip ' . $jenisArsip . ' melebihi batas ' . $maxKb . ' KB.');
        }

        (new ImageOptimizerService())->detectImageMeta($uploaded);
    }

    private function storeFile($uploaded, int $idPendaftar, string $jenisArsip): string
    {
        $targetDir = FCPATH . 'uploads/peserta/arsip';
        $slug = $this->slugify($jenisArsip, 'arsip');
        $name = 'arsip-peserta-' . $idPendaftar . '-' . $slug . '-' . date('YmdHis') . '-' . $this->randomSuffix();

        return (new ImageOptimizerService())->optimizeAndStore($uploaded, $targetDir, $name, 1600, 82, 6);
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

    private function slugify(string $value, string $fallback): string
    {
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $value), '-'));

        return $slug !== '' ? $slug : $fallback;
    }

    private function randomSuffix(): string
    {
        try {
            return bin2hex(random_bytes(2));
        } catch (\Throwable) {
            return substr((string) mt_rand(1000, 9999), 0, 4);
        }
    }

    private function findByJenisArsip(int $idPendaftar, string $jenisArsip): ?object
    {
        return (new ArsipPendaftarModel())
            ->where('id_pendaftar', $idPendaftar)
            ->where('jenis_arsip', $jenisArsip)
            ->first();
    }

    public function deleteArchive(int $idArsip): bool
    {
        $arsip = (new ArsipPendaftarModel())->find($idArsip);
        if ($arsip === null) {
            throw new \RuntimeException('Arsip tidak ditemukan.');
        }

        $this->deletePhysicalFile($arsip->nama_arsip);
        return (new ArsipPendaftarModel())->delete($idArsip);
    }

    public function validateSlotExists(string $slotName): bool
    {
        $slots = get_arsip_pendaftar_config_ci4();
        return isset($slots[$slotName]) && !empty($slots[$slotName]['active']);
    }

    public function getArchivesByPeserta(int $idPendaftar): array
    {
        return (new ArsipPendaftarModel())
            ->where('id_pendaftar', $idPendaftar)
            ->orderBy('nama_arsip', 'ASC')
            ->findAll();
    }
}
