<?php

namespace App\Controllers\Development;

use App\Controllers\BaseController;
use Config\Database;

class SystemHealthController extends BaseController
{
    public function index()
    {
        $directories = [
            ['path' => 'writable/logs', 'full' => WRITEPATH . 'logs'],
            ['path' => 'writable/cache', 'full' => WRITEPATH . 'cache'],
            ['path' => 'uploads', 'full' => FCPATH . 'uploads'],
            ['path' => 'temp', 'full' => FCPATH . 'temp'],
            ['path' => 'uploads/assets', 'full' => FCPATH . 'uploads/assets'],
            ['path' => 'uploads/peserta', 'full' => FCPATH . 'uploads/peserta'],
        ];

        $directoryStatus = [];
        foreach ($directories as $dir) {
            $directoryStatus[] = [
                'path' => $dir['path'],
                'exists' => is_dir($dir['full']),
                'writable' => is_dir($dir['full']) && is_writable($dir['full']),
                'perms' => is_dir($dir['full']) ? substr(sprintf('%o', fileperms($dir['full'])), -4) : 'N/A',
            ];
        }

        $phpEnv = [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_execution_time' => ini_get('max_execution_time'),
            'mysqli_loaded' => extension_loaded('mysqli'),
            'gd_loaded' => extension_loaded('gd'),
            'curl_loaded' => extension_loaded('curl'),
            'mbstring_loaded' => extension_loaded('mbstring'),
            'openssl_loaded' => extension_loaded('openssl'),
        ];

        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? (PHP_SAPI === 'cli-server' ? 'PHP Built-in Server' : 'Unknown');
        $free = disk_free_space('.');
        $total = disk_total_space('.');
        $usedPercent = $total > 0 ? 100 - round(($free / $total) * 100) : 0;

        $serverInfo = [
            'os' => PHP_OS,
            'server_software' => $serverSoftware,
            'disk_free_space' => $this->formatBytes($free),
            'disk_total_space' => $this->formatBytes($total),
            'disk_used_percent' => $usedPercent,
        ];

        return view('development/system_health', [
            'title' => 'System Health Checker',
            'directoryStatus' => $directoryStatus,
            'phpEnv' => $phpEnv,
            'serverInfo' => $serverInfo,
        ]);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
