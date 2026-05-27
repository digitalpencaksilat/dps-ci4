<?php

namespace App\Services\Admin\Super;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class EventProfileService
{
    private BaseConnection $db;

    /**
     * @var array<string, string>
     */
    private array $fields = [
        'event_name' => 'Nama Event',
        'landing_page_description' => 'Deskripsi Landing Page',
        'abbreviation' => 'Singkatan Event',
        'event_host' => 'Penyelenggara Event',
        'registration_start' => 'Awal Pendaftaran',
        'registration_end' => 'Batas Pendaftaran',
        'date_start' => 'Tanggal Mulai Event',
        'date_end' => 'Tanggal Selesai Event',
        'technical_meeting_date' => 'Tanggal Technical Meeting',
        'technical_meeting_location' => 'Lokasi Technical Meeting',
        'event_location' => 'Lokasi Event',
        'contact_person' => 'Contact Person',
        'countdown' => 'Countdown',
        'fight_category' => 'Kategori Pertandingan',
        'domain_hosting' => 'Domain Hosting',
    ];

    public function __construct()
    {
        $this->db = Database::connect();
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
            $values[$field] = (string) (get_setting($field) ?? ci3_config_item($field, 'pendaftaran/profil_kejuaraan') ?? '');
        }

        return $values;
    }

    /**
     * @param array<string, string|null> $payload
     */
    public function save(array $payload): void
    {
        foreach (array_keys($this->fields) as $field) {
            $value = trim((string) ($payload[$field] ?? ''));
            $existing = $this->db->table('site_builder_settings')->where('setting', $field)->get()->getRow();

            $data = [
                'setting' => $field,
                'value' => $value,
                'is_array' => 0,
            ];

            if ($existing !== null) {
                $this->db->table('site_builder_settings')->where('setting', $field)->update([
                    'value' => $value,
                    'is_array' => 0,
                ]);

                continue;
            }

            $this->db->table('site_builder_settings')->insert($data);
        }
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'event_name' => 'required|max_length[255]',
            'landing_page_description' => 'permit_empty|max_length[1000]',
            'abbreviation' => 'permit_empty|max_length[50]',
            'event_host' => 'required|max_length[255]',
            'registration_start' => 'permit_empty|max_length[100]',
            'registration_end' => 'permit_empty|max_length[100]',
            'date_start' => 'permit_empty|max_length[100]',
            'date_end' => 'permit_empty|max_length[100]',
            'technical_meeting_date' => 'permit_empty|max_length[100]',
            'technical_meeting_location' => 'permit_empty|max_length[255]',
            'event_location' => 'permit_empty|max_length[255]',
            'contact_person' => 'permit_empty|max_length[50]',
            'countdown' => 'permit_empty|max_length[100]',
            'fight_category' => 'permit_empty|max_length[255]',
            'domain_hosting' => 'permit_empty|max_length[255]|valid_url_strict[https,http]',
        ];
    }
}
