<?php

namespace App\Controllers\Development;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        return view('development/dashboard', [
            'title' => 'Development Dashboard',
        ]);
    }
}
