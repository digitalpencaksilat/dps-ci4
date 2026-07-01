<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;

class RenderSertifikatCommand extends BaseCommand
{
    protected $group = 'DPS';
    protected $name = 'sertifikat:render-local';
    protected $description = 'Render HTML sertifikat lokal menjadi ZIP PNG memakai Playwright.';

    public function run(array $params): void
    {
        $input = CLI::getOption('input') ?? ($params[0] ?? null);
        $output = CLI::getOption('output') ?? (WRITEPATH . 'sertifikat-render');
        $scale = (int) (CLI::getOption('scale') ?? 2);
        $chunkSize = (int) (CLI::getOption('chunk-size') ?? 20);

        if ($input === null || $input === '') {
            CLI::error('Wajib isi --input=/path/to/sertifikat.html');
            return;
        }

        $inputPath = realpath((string) $input);
        if ($inputPath === false || ! is_file($inputPath)) {
            CLI::error('File input tidak ditemukan: ' . $input);
            return;
        }

        $root = ROOTPATH;
        $script = $root . 'tools/sertifikat-renderer.js';
        if (! is_file($script)) {
            CLI::error('Renderer JS tidak ditemukan: ' . $script);
            return;
        }

        if (! is_dir((string) $output)) {
            mkdir((string) $output, 0777, true);
        }

        $cmd = implode(' ', [
            'node',
            escapeshellarg($script),
            '--input', escapeshellarg($inputPath),
            '--output', escapeshellarg((string) $output),
            '--scale', escapeshellarg((string) max(1, $scale)),
            '--chunk-size', escapeshellarg((string) max(1, $chunkSize)),
        ]);

        CLI::write('Menjalankan renderer sertifikat lokal...', 'yellow');
        CLI::write($cmd, 'dark_gray');

        $exitCode = 0;
        passthru($cmd, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('Renderer sertifikat lokal gagal. Exit code: ' . $exitCode);
        }

        CLI::write('Selesai. Output: ' . $output, 'green');
    }
}
