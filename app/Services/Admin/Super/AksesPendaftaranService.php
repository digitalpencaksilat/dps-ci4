<?php

namespace App\Services\Admin\Super;

class AksesPendaftaranService
{
    private SettingWriterService $writer;

    /**
     * @var array<string, string>
     */
    private array $fields = [
        'perbolehkan_kontingen_mendaftar' => 'Perbolehkan kontingen mendaftar',
        'perbolehkan_kontingen_login' => 'Perbolehkan kontingen login',
        'perbolehkan_kontingen_input_atlet' => 'Perbolehkan input atlet',
        'perbolehkan_kontingen_memilih_kategori' => 'Perbolehkan memilih kategori',
        'perbolehkan_kontingen_melunasi_pembayaran' => 'Perbolehkan melunasi pembayaran',
        'perbolehkan_undur_diri_atlet' => 'Perbolehkan undur diri atlet',
        'perbolehkan_ganti_atlet_dan_kategori' => 'Perbolehkan ganti atlet dan kategori',
        'perbolehkan_edit_biodata' => 'Perbolehkan edit biodata',
        'perbolehkan_kontingen_input_official' => 'Perbolehkan input official',
    ];

    public function __construct()
    {
        $this->writer = new SettingWriterService();
    }

    /**
     * @return array<string, string>
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, bool>
     */
    public function currentValues(): array
    {
        $values = [];
        foreach (array_keys($this->fields) as $field) {
            $dbValue = get_setting($field, 'pendaftaran/akses_pendaftaran');
            if ($dbValue !== null) {
                $values[$field] = (string) $dbValue === '1';
                continue;
            }
            $values[$field] = (bool) (ci3_config_item($field, 'pendaftaran/akses_pendaftaran') ?? false);
        }

        return $values;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        // checkboxes can be absent; we validate only if present.
        $rules = [];
        foreach (array_keys($this->fields) as $field) {
            $rules[$field] = 'permit_empty|in_list[0,1,on]';
        }
        return $rules;
    }

    /**
     * @param array<string, bool> $payload
     */
    public function save(array $payload): void
    {
        foreach (array_keys($this->fields) as $field) {
            $this->writer->setBool($field, (bool) ($payload[$field] ?? false));
        }
    }
}
