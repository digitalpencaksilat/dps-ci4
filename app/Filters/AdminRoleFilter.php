<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminRoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $level = (string) $session->get('level');
        $allowedRoles = array_values(array_filter(array_map('strval', (array) $arguments)));

        if ($level === '') {
            return redirect()->to($this->loginUrl());
        }

        if ($allowedRoles === []) {
            return null;
        }

        if ($level === 'super_admin') {
            return null;
        }

        if (! in_array($level, $allowedRoles, true)) {
            $redirect = $this->dashboardUrlFor($level);
            if ($redirect !== null) {
                return redirect()->to($redirect);
            }

            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/html/error_403'));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function loginUrl(): string
    {
        return base_url('admin');
    }

    private function dashboardUrlFor(string $level): ?string
    {
        return match ($level) {
            'kontingen' => base_url('kontingen/dashboard'),
            'bendahara' => base_url('admin/bendahara/dashboard'),
            'sekretariat' => base_url('admin/sekretariat/dashboard'),
            'super_admin' => base_url('admin/super/dashboard'),
            default => null,
        };
    }
}
