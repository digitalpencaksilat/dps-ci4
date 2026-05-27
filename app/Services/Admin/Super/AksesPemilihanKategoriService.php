<?php

namespace App\Services\Admin\Super;

class AksesPemilihanKategoriService
{
    private SettingWriterService $writer;

    /**
     * @var array<string, string>
     */
    private array $fields = [
        'perbolehkan_memilih_kategori_usia' => 'Perbolehkan memilih kategori usia (manual)',
        'perbolehkan_memilih_kelas_tanding' => 'Perbolehkan memilih kelas tanding (tanpa kunci berat)',
        'perbolehkan_atlet_dari_kontingen_yang_sama' => 'Perbolehkan atlet dari kontingen yang sama',
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
            $dbValue = get_setting($field, 'pendaftaran/akses_pemilihan_kategori_perlombaan');
            if ($dbValue !== null) {
                $values[$field] = (string) $dbValue === '1';
                continue;
            }
            $values[$field] = (bool) (ci3_config_item($field, 'pendaftaran/akses_pemilihan_kategori_perlombaan') ?? false);
        }

        return $values;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
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
