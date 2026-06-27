<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use RuntimeException;

class RenderIdCardCommand extends BaseCommand
{
    protected $group = 'DPS';
    protected $name = 'id-card:render-local';
    protected $description = 'Render HTML ID Card lokal menjadi ZIP PNG memakai Playwright.';

    public function run(array $params): void
    {
        $input = CLI::getOption('input') ?? ($params[0] ?? null);
        $output = CLI::getOption('output') ?? (WRITEPATH . 'id-card-render');
        $scale = (int) (CLI::getOption('scale') ?? 3);
        $chunkSize = (int) (CLI::getOption('chunk-size') ?? 50);

        if ($input === null || $input === '') {
            CLI::error('Wajib isi --input=/path/to/id-card.html');
            return;
        }

        $inputPath = realpath((string) $input);
        if ($inputPath === false || ! is_file($inputPath)) {
            CLI::error('File input tidak ditemukan: ' . $input);
            return;
        }

        $root = ROOTPATH;
        $script = $root . 'tools/id-card-renderer.js';
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

        CLI::write('Menjalankan renderer lokal...', 'yellow');
        CLI::write($cmd, 'dark_gray');

        $exitCode = 0;
        passthru($cmd, $exitCode);

        if ($exitCode !== 0) {
            throw new RuntimeException('Renderer lokal gagal. Exit code: ' . $exitCode);
        }

        CLI::write('Selesai. Output: ' . $output, 'green');
    }
}
