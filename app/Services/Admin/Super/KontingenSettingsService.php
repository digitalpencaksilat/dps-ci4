<?php

namespace App\Services\Admin\Super;

use App\Models\SiteBuilderSettingModel;

class KontingenSettingsService
{
    private const FIELD_CONFIG = [
        'aktifkan_tagihan_biaya_kontingen' => [
            'label' => 'Aktifkan Tagihan Biaya Kontingen',
            'type' => 'boolean',
            'default' => 0,
            'help' => 'Jika aktif, kontingen akan memiliki tagihan biaya kontingen terpisah dari tagihan peserta.',
            'config' => 'pendaftaran/biaya_registrasi_kontingen',
        ],
        'biaya_pendaftaran_kontingen_dalam_negeri' => [
            'label' => 'Biaya Kontingen Dalam Negeri',
            'type' => 'currency',
            'default' => 0,
            'help' => 'Biaya default yang otomatis disalin ke data kontingen domestik saat registrasi.',
            'config' => 'pendaftaran/biaya_registrasi_kontingen',
        ],
        'biaya_pendaftaran_kontingen_luar_negeri' => [
            'label' => 'Biaya Kontingen Luar Negeri',
            'type' => 'currency',
            'default' => 0,
            'help' => 'Biaya default untuk kontingen luar negeri saat registrasi atau input admin.',
            'config' => 'pendaftaran/biaya_registrasi_kontingen',
        ],
        'max_atlet_per_kontingen' => [
            'label' => 'Max Atlet per Kontingen',
            'type' => 'number',
            'default' => 2000,
            'help' => 'Batas jumlah atlet yang dapat ditambahkan oleh satu kontingen.',
            'config' => 'pendaftaran/max_atlet_per_kontingen',
        ],
    ];

    private SiteBuilderSettingModel $settings;

    public function __construct()
    {
        $this->settings = new SiteBuilderSettingModel();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function fields(): array
    {
        return self::FIELD_CONFIG;
    }

    /**
     * @return array<string, int>
     */
    public function currentValues(): array
    {
        $values = [];

        foreach (self::FIELD_CONFIG as $key => $meta) {
            $fallback = get_setting($key, (string) $meta['config']);
            if ($fallback === null || $fallback === '') {
                $fallback = $meta['default'];
            }

            $values[$key] = (int) $this->settings->getValue($key, $fallback);
        }

        return $values;
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function rules(): array
    {
        return [
            'aktifkan_tagihan_biaya_kontingen' => [
                'label' => self::FIELD_CONFIG['aktifkan_tagihan_biaya_kontingen']['label'],
                'rules' => 'required|in_list[0,1]',
            ],
            'biaya_pendaftaran_kontingen_dalam_negeri' => [
                'label' => self::FIELD_CONFIG['biaya_pendaftaran_kontingen_dalam_negeri']['label'],
                'rules' => 'required|integer|greater_than_equal_to[0]',
            ],
            'biaya_pendaftaran_kontingen_luar_negeri' => [
                'label' => self::FIELD_CONFIG['biaya_pendaftaran_kontingen_luar_negeri']['label'],
                'rules' => 'required|integer|greater_than_equal_to[0]',
            ],
            'max_atlet_per_kontingen' => [
                'label' => self::FIELD_CONFIG['max_atlet_per_kontingen']['label'],
                'rules' => 'required|integer|greater_than[0]',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function save(array $payload): void
    {
        foreach (array_keys(self::FIELD_CONFIG) as $key) {
            $value = (int) ($payload[$key] ?? self::FIELD_CONFIG[$key]['default']);
            $this->settings->setScalar($key, $value);
        }
    }
}
