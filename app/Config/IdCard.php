<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class IdCard extends BaseConfig
{
    /**
     * Default layout configuration for ID Card elements.
     * Used as fallback when DB setting 'id_card_layout' is not found.
     *
     * Each key maps to CSS inset shorthand: "top right bottom left"
     * or individual CSS properties (width, height, font-size, display, etc.)
     */
    public array $fotoAtlet = [
        'photo_position' => '4.8cm 0 0 1.5cm',
        'photo_width'    => '39mm',
        'photo_height'   => '49mm',
        'photo_display'  => 'none',
    ];

    public array $namaAtlet = [
        'athlete_name_position'         => '10.8cm 0 0 1.2cm',
        'athlete_name_container_width'  => '100%',
        'athlete_name_font_size'        => '13px',
        'athlete_name_font_color'       => '#0a0909e2',
        'athlete_name_text_transform'   => 'uppercase',
        'athlete_name_text_align'       => 'left',
        'athlete_name_display'          => 'block',
        'athlete_name_font_weight'      => 'bold',
        'athlete_name_white_space'      => 'nowrap',
    ];

    public array $namaKontingen = [
        'contingent_name_position'          => '11.7cm 0 0 1.2cm',
        'contingent_name_container_width'   => '100%',
        'contingent_name_font_size'         => '11px',
        'contingent_name_font_color'        => '#0a0909e2',
        'contingent_name_text_transform'    => 'uppercase',
        'contingent_name_text_align'        => 'left',
        'contingent_name_display'           => 'block',
        'contingent_name_font_weight'       => 'bold',
        'contingent_name_white_space'       => 'nowrap',
    ];

    public array $barcode = [
        'barcode_display'  => 'none',
        'barcode_position' => '4.6cm 0 0 5cm',
        'barcode_width'    => '4cm',
    ];

    public array $pertandingan = [
        'match_category_display'               => 'none',
        'match_category_width'                 => '100%',
        'match_category_position'              => '11cm 0 0 0',
        'match_category_font_size'             => '14px',
        'match_category_font_color'            => '#0a0909e2',
        'match_category_font_weight'           => 'bolder',
        'match_category_white_space'           => 'nowrap',
        'matches_table_display'                => 'none',
        'matches_table_width'                  => '80%',
        'matches_table_position'               => '7.75cm 10% 0 10%',
        'matches_table_font_size'              => '12px',
        'matches_table_font_color'             => '#0a0909e2',
        'matches_table_position_text_align'    => 'left',
        'matches_table_font_weight'            => 'bold',
    ];

    /**
     * @return array<string, array<string, string>>
     */
    public function allDefaults(): array
    {
        return [
            'foto_atlet'    => $this->fotoAtlet,
            'nama_atlet'    => $this->namaAtlet,
            'nama_kontingen' => $this->namaKontingen,
            'barcode'       => $this->barcode,
            'pertandingan'  => $this->pertandingan,
        ];
    }
}
