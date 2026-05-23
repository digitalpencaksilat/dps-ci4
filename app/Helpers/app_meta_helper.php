<?php

if (! function_exists('app_version')) {
    function app_version(): string
    {
        static $version;

        if ($version !== null) {
            return $version;
        }

        $path = ROOTPATH . 'VERSION';
        if (! is_file($path)) {
            return $version = 'v0.0.0';
        }

        $contents = trim((string) file_get_contents($path));

        return $version = $contents !== '' ? $contents : 'v0.0.0';
    }
}

if (! function_exists('format_tanggal_indo')) {
    function format_tanggal_indo(?string $date): string
    {
        if (! $date) {
            return '-';
        }

        try {
            $dt = new DateTimeImmutable($date);
        } catch (Throwable) {
            return $date;
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $day = $dt->format('j');
        $month = $months[(int) $dt->format('n')] ?? $dt->format('m');
        $year = $dt->format('Y');

        return $day . ' ' . $month . ' ' . $year;
    }
}
