<?php

namespace App\Services\Admin\Super;

class KontenLandingService
{
    private SettingWriterService $writer;

    /**
     * CI3 config keys (pendaftaran/konten_halaman_landing.php) that we persist into DB.
     *
     * @var array<string, string>
     */
    private array $fields = [
        'nama_kejuaraan' => 'Nama Kejuaraan',
        'deskripsi' => 'Deskripsi',
        'singkatan_nama_kejuaraan' => 'Singkatan Nama Kejuaraan',
        'panitia_kejuaraan' => 'Panitia Kejuaraan',
        'awal_pendaftaran' => 'Awal Pendaftaran',
        'batas_pendaftaran' => 'Batas Pendaftaran',
        'tanggal_mulai' => 'Tanggal Mulai',
        'tanggal_selesai' => 'Tanggal Selesai',
        'tanggal_tm' => 'Tanggal Technical Meeting',
        'lokasi_tm' => 'Lokasi Technical Meeting',
        'lokasi_pertandingan' => 'Lokasi Pertandingan',
        'nara_hubung' => 'Narahubung (WhatsApp)',
    ];

    public function __construct()
    {
        $this->writer = new SettingWriterService();
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, string>
     */
    public function currentValues(): array
    {
        $values = [];
        foreach (array_keys($this->fields) as $field) {
            $values[$field] = (string) (get_setting($field, 'pendaftaran/konten_halaman_landing')
                ?? ci3_config_item($field, 'pendaftaran/konten_halaman_landing')
                ?? '');
        }

        return $values;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'nama_kejuaraan' => 'required|max_length[255]',
            'deskripsi' => 'permit_empty|max_length[1000]',
            'singkatan_nama_kejuaraan' => 'permit_empty|max_length[100]',
            'panitia_kejuaraan' => 'permit_empty|max_length[255]',
            'awal_pendaftaran' => 'permit_empty|max_length[100]',
            'batas_pendaftaran' => 'permit_empty|max_length[100]',
            'tanggal_mulai' => 'permit_empty|max_length[100]',
            'tanggal_selesai' => 'permit_empty|max_length[100]',
            'tanggal_tm' => 'permit_empty|max_length[100]',
            'lokasi_tm' => 'permit_empty|max_length[255]',
            'lokasi_pertandingan' => 'permit_empty|max_length[255]',
            'nara_hubung' => 'permit_empty|max_length[30]',
        ];
    }

    /**
     * @param array<string, string|null> $payload
     */
    public function save(array $payload): void
    {
        foreach (array_keys($this->fields) as $field) {
            $this->writer->setString($field, (string) ($payload[$field] ?? ''));
        }
    }
}
