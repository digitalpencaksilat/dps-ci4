<?php

namespace App\Controllers\Development;

use App\Controllers\BaseController;

class PurgerController extends BaseController
{
    public function index()
    {
        $folders = [
            'temp' => $this->getFolderInfo(FCPATH . 'temp'),
            'logs' => $this->getFolderInfo(WRITEPATH . 'logs'),
            'cache' => $this->getFolderInfo(WRITEPATH . 'cache'),
        ];

        return view('development/purger', [
            'title' => 'Asset & Storage Purger',
            'folders' => $folders,
        ]);
    }

    public function clean(string $folderKey)
    {
        $targetMap = [
            'temp' => FCPATH . 'temp',
            'logs' => WRITEPATH . 'logs',
            'cache' => WRITEPATH . 'cache',
        ];

        if (! isset($targetMap[$folderKey])) {
            session()->setFlashdata('error', 'Invalid folder target.');
            return redirect()->to(base_url('development/purger'));
        }

        $path = $targetMap[$folderKey];
        if (! is_dir($path)) {
            session()->setFlashdata('error', "Folder <b>{$folderKey}</b> does not exist.");
            return redirect()->to(base_url('development/purger'));
        }

        $files = array_diff(scandir($path), ['.', '..', 'index.html', '.htaccess']);
        $count = 0;

        foreach ($files as $file) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullPath)) {
                $this->deleteRecursive($fullPath);
            } else {
                unlink($fullPath);
            }
            $count++;
        }

        session()->setFlashdata('success', "Successfully purged {$count} items from <b>{$folderKey}</b>.");
        return redirect()->to(base_url('development/purger'));
    }

    private function getFolderInfo(string $path): array
    {
        if (! is_dir($path)) {
            return ['size' => '0 B', 'count' => 0];
        }

        $files = array_diff(scandir($path), ['.', '..', 'index.html', '.htaccess']);
        $size = 0;
        $count = 0;

        foreach ($files as $file) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_file($fullPath)) {
                $size += filesize($fullPath);
                $count++;
            }
        }

        return [
            'size' => $this->formatBytes($size),
            'count' => $count,
        ];
    }

    private function deleteRecursive(string $dir): bool
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteRecursive($path) : unlink($path);
        }
        return rmdir($dir);
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
