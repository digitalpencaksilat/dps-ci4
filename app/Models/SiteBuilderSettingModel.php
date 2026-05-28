<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteBuilderSettingModel extends Model
{
    protected $table = 'site_builder_settings';
    protected $primaryKey = 'setting';
    protected $returnType = 'array';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['setting', 'value', 'is_array'];

    public function getValue(string $key, mixed $default = null): mixed
    {
        $row = $this->where('setting', $key)->first();
        if ($row === null) {
            return $default;
        }

        if ((int) ($row['is_array'] ?? 0) === 1) {
            $decoded = json_decode((string) ($row['value'] ?? ''), true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
        }

        return $row['value'] ?? $default;
    }

    public function setScalar(string $key, string|int|float|bool $value): void
    {
        $this->upsertValue($key, (string) $value, 0);
    }

    private function upsertValue(string $key, string $value, int $isArray): void
    {
        $data = [
            'setting' => $key,
            'value' => $value,
            'is_array' => $isArray,
        ];

        if ($this->where('setting', $key)->first() !== null) {
            $this->update($key, [
                'value' => $value,
                'is_array' => $isArray,
            ]);
            return;
        }

        $this->insert($data);
    }
}
