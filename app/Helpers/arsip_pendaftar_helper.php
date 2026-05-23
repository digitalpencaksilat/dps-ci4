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
