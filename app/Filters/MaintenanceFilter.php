<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $isMaintenance = strtolower((string) env('APP_MAINTENANCE', 'false')) === 'true';

        if (! $isMaintenance) {
            return null;
        }

        $path = trim($request->getUri()->getPath(), '/');
        $allowedPaths = [
            'maintenance',
            'admin',
            'admin/login',
            'pendaftaran/login',
        ];

        if (str_starts_with($path, 'development')) {
            return null;
        }

        if (in_array($path, $allowedPaths, true) || str_starts_with($path, 'assets/') || str_starts_with($path, 'uploads/')) {
            return null;
        }

        return service('response')
            ->setStatusCode(503)
            ->setHeader('Retry-After', '3600')
            ->setBody(view('shared_sections/dps_error_panel', [
                'code' => '503',
                'title' => 'Sedang Maintenance',
                'message' => 'Sistem sedang dalam pemeliharaan. Silakan coba kembali dalam beberapa saat.',
                'actionUrl' => base_url('maintenance'),
                'actionLabel' => 'Muat Ulang',
                'showHome' => false,
            ]));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
