<?php

namespace App\Services\Pdf;

use Mpdf\Mpdf;

class MpdfService
{
    public function make(array $config = []): Mpdf
    {
        $defaults = [
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 12,
            'margin_right'  => 12,
            'margin_bottom' => 12,
            'margin_left'   => 12,
            'tempDir'       => WRITEPATH . 'cache/mpdf',
        ];

        $tempDir = $config['tempDir'] ?? $defaults['tempDir'];
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        $config['tempDir'] = $tempDir;

        return new Mpdf(array_replace($defaults, $config));
    }
}
