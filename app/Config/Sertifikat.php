<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Sertifikat extends BaseConfig
{
    /**
     * Default layout positions ported from legacy config/print/sertifikat.php.
     * DB-first via SertifikatService::getLayoutConfig(); these are fallbacks.
     */
    public array $defaults = [
        'font_family'   => "'Verdana'",
        'font_weight'   => 'bolder',

        'nomor_position'    => '27.4601% 0 0 62.7613%',
        'nomor_font_size'   => '18px',
        'nomor_text_align'  => 'left',
        'nomor_display'     => 'block',

        'nama_position'    => '35.4689% auto auto 52.7714%',
        'nama_font_size'   => '24px',
        'nama_text_align'  => 'center',
        'nama_display'     => 'block',

        'kategori_position'   => '52.5334% auto auto 53.0372%',
        'kategori_font_size'  => '24px',
        'kategori_text_align' => 'center',
        'kategori_display'    => 'block',

        'kontingen_position'   => '44.2751% auto auto 52.6575%',
        'kontingen_font_size'  => '23px',
        'kontingen_text_align' => 'center',
        'kontingen_display'    => 'block',

        'sekolah_position'   => '60.6543% auto auto 51.2326%',
        'sekolah_font_size'  => '18px',
        'sekolah_text_align' => 'center',
        'sekolah_display'    => 'none',

        'qrcode_position'   => '73.9044% 0 0 19.2656%',
        'qrcode_font_size'  => '140px',
        'qrcode_text_align' => 'left',
        'qrcode_display'    => 'none',
    ];
}
