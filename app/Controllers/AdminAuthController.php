<?php

namespace App\Controllers;

use App\Services\AdminAuthService;

class AdminAuthController extends BaseController
{
    public function login()
    {
        $currentRole = (string) session()->get('level');
        $auth = new AdminAuthService();

        if ($currentRole !== '' && $currentRole !== 'kontingen') {
            return redirect()->to($auth->dashboardUrlFor($currentRole));
        }

        return view('admin/auth/login', [
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
        ]);
    }

    public function attemptLogin()
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(base_url('admin'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        $auth = new AdminAuthService();

        try {
            $role = $auth->attempt(
                (string) $this->request->getPost('username'),
                (string) $this->request->getPost('password')
            );
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        if ($role === null) {
            return redirect()->to(base_url('admin'))->withInput()->with('status', false)->with('message', 'Username atau password admin salah.');
        }

        if ($role === 'kontingen') {
            $auth->logout();

            return redirect()->to(base_url('admin'))->withInput()->with('status', false)->with('message', 'Akun tersebut bukan akun admin.');
        }

        $redirect = $auth->dashboardUrlFor($role);
        if ($redirect === base_url('admin')) {
            return redirect()->to(base_url('admin'))->with('status', true)->with('message', 'Login berhasil, tetapi dashboard untuk role ini belum tersedia.');
        }

        return redirect()->to($redirect);
    }

    public function logout()
    {
        (new AdminAuthService())->logout();

        return redirect()->to(base_url('admin'));
    }
}
