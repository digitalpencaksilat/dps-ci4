<?php

namespace App\Services\Admin\Super;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * Small helper to persist settings into `site_builder_settings`.
 * This keeps the super admin controllers/services consistent.
 */
class SettingWriterService
{
    private BaseConnection $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function setString(string $key, string $value): void
    {
        $this->upsert($key, trim($value), 0);
    }

    public function setBool(string $key, bool $value): void
    {
        $this->upsert($key, $value ? '1' : '0', 0);
    }

    public function setArray(string $key, array $value): void
    {
        $this->upsert($key, json_encode($value, JSON_UNESCAPED_UNICODE), 1);
    }

    private function upsert(string $key, string $value, int $isArray): void
    {
        $existing = $this->db->table('site_builder_settings')->where('setting', $key)->get()->getRow();

        $data = [
            'setting' => $key,
            'value' => $value,
            'is_array' => $isArray,
        ];

        if ($existing !== null) {
            $this->db->table('site_builder_settings')->where('setting', $key)->update([
                'value' => $value,
                'is_array' => $isArray,
            ]);
            return;
        }

        $this->db->table('site_builder_settings')->insert($data);
    }
}
