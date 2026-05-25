<?php

namespace App\Controllers\Development;

use App\Controllers\BaseController;

class LogViewerController extends BaseController
{
    public function index($file = null)
    {
        $logPath = WRITEPATH . 'logs/';

        if (! is_dir($logPath)) {
            return view('development/log_viewer', [
                'title' => 'Log Viewer',
                'files' => [],
                'current_file' => null,
                'logs' => [],
            ]);
        }

        $allFiles = array_diff(scandir($logPath), ['.', '..', 'index.html']);
        arsort($allFiles);

        $files = array_values($allFiles);
        $currentFile = $file ?: ($files !== [] ? reset($files) : null);
        $logs = [];

        if ($currentFile && file_exists($logPath . $currentFile)) {
            $content = file_get_contents($logPath . $currentFile);
            $logs = $this->parseLogs($content);
        }

        return view('development/log_viewer', [
            'title' => 'Log Viewer',
            'files' => $files,
            'current_file' => $currentFile,
            'logs' => $logs,
        ]);
    }

    public function clear()
    {
        $logPath = WRITEPATH . 'logs/';

        if (is_dir($logPath)) {
            $files = array_diff(scandir($logPath), ['.', '..', 'index.html']);
            foreach ($files as $file) {
                $filePath = $logPath . $file;
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }
        }

        session()->setFlashdata('success', 'All log files have been cleared.');
        return redirect()->to(base_url('development/log-viewer'));
    }

    private function parseLogs(string $content): array
    {
        if ($content === '' || $content === '0') {
            return [];
        }

        $lines = explode("\n", $content);
        $parsed = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $type = 'DEBUG';
            if (strpos($line, 'ERROR') !== false) {
                $type = 'ERROR';
            } elseif (strpos($line, 'INFO') !== false) {
                $type = 'INFO';
            } elseif (strpos($line, 'DEBUG') !== false) {
                $type = 'DEBUG';
            }

            $parsed[] = [
                'type' => $type,
                'content' => $line,
            ];
        }

        return array_reverse($parsed);
    }
}
