<?php

use App\Models\ArsipPendaftarModel;

if (! function_exists('get_arsip_pendaftar_config_ci4')) {
    function get_arsip_pendaftar_config_ci4(): array
    {
        $db = db_connect();
        $row = $db->table('site_builder_settings')->where('setting', 'arsip_pendaftar_slots')->get()->getRow();

        if ($row && ! empty($row->value)) {
            $decoded = json_decode($row->value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $path = APPPATH . 'Config/ci3/pendaftaran/arsip_pendaftar.php';
        if (! is_file($path)) {
            return [];
        }

        if (! defined('BASEPATH')) {
            define('BASEPATH', APPPATH);
        }

        $config = [];
        require $path;

        return $config;
    }
}

if (! function_exists('get_active_arsip_pendaftar_ci4')) {
    function get_active_arsip_pendaftar_ci4(): array
    {
        return array_filter(get_arsip_pendaftar_config_ci4(), static fn ($slot) => isset($slot['active']) && $slot['active'] === true);
    }
}

if (! function_exists('get_required_arsip_pendaftar_ci4')) {
    function get_required_arsip_pendaftar_ci4(): array
    {
        return array_filter(get_active_arsip_pendaftar_ci4(), static fn ($slot) => isset($slot['required']) && $slot['required'] === true);
    }
}

if (! function_exists('get_arsip_pendaftar_by_peserta_ci4')) {
    function get_arsip_pendaftar_by_peserta_ci4(int $idPendaftar): array
    {
        return (new ArsipPendaftarModel())
            ->where('id_pendaftar', $idPendaftar)
            ->orderBy('nama_arsip', 'ASC')
            ->findAll();
    }
}

if (! function_exists('url_arsip_pendaftar_ci4')) {
    function url_arsip_pendaftar_ci4(string $namaArsip): string
    {
        return base_url('uploads/peserta/arsip/' . $namaArsip);
    }
}

if (! function_exists('validate_arsip_upload_ci4')) {
    function validate_arsip_upload_ci4(string $slotName, $file): array
    {
        $slots = get_arsip_pendaftar_config_ci4();
        
        if (!isset($slots[$slotName])) {
            return [
                'valid' => false,
                'message' => 'Slot arsip tidak ditemukan'
            ];
        }
        
        $slot = $slots[$slotName];
        
        try {
            (new \App\Services\ArsipPendaftarService())->validateUpload($file, $slot, $slot['nama_arsip'] ?? $slotName);
            return ['valid' => true, 'message' => 'Valid'];
        } catch (\RuntimeException $e) {
            return ['valid' => false, 'message' => $e->getMessage()];
        }
    }
}

if (! function_exists('get_slot_config_ci4')) {
    function get_slot_config_ci4(string $slotName): ?array
    {
        $slots = get_arsip_pendaftar_config_ci4();
        return $slots[$slotName] ?? null;
    }
}

if (! function_exists('count_active_arsip_pendaftar_ci4')) {
    function count_active_arsip_pendaftar_ci4(): int
    {
        return count(get_active_arsip_pendaftar_ci4());
    }
}

if (! function_exists('get_max_arsip_slot_ci4')) {
    function get_max_arsip_slot_ci4(): int
    {
        $slots = get_arsip_pendaftar_config_ci4();
        $max = 0;
        foreach (array_keys($slots) as $key) {
            if (preg_match('/^slot_(\d+)$/', (string) $key, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        return $max;
    }
}
