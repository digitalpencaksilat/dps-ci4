<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Provide small globals that CI3 views expect: $this->agent and $this->session.
 *
 * This is a temporary migration aid to keep legacy views working with minimal
 * markup changes. We'll remove it once views are fully ported.
 */
class Ci3Globals implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Views are executed in the context of CodeIgniter\View\View ($this).
        // Add properties that old views access.
        $view = Services::renderer();

        // Agent service is not always enabled; only set if available.
        if (method_exists(Services::class, 'userAgent')) {
            $view->agent = Services::userAgent();
        }

        $view->session = Services::session();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
