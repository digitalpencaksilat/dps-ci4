<?php

namespace App\Controllers;

use App\Services\KontingenAuthService;

class KontingenAuthController extends BaseController
{
    public function login(): string
    {
        if (session()->get('level') === 'kontingen' && session()->get('id_kontingen')) {
            return redirect()->to(base_url('kontingen/dashboard'));
        }

        return view('kontingen/auth/login', [
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'allowLogin' => (string) (get_setting('perbolehkan_kontingen_login') ?? '1') === '1',
            'allowForgotPassword' => (string) (get_setting('perbolehkan_lupa_password') ?? '0') === '1',
        ]);
    }

    public function attemptLogin()
    {
        $rules = [
            'email_kontingen' => 'required|valid_email',
            'password'        => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        $allowLogin = (string) (get_setting('perbolehkan_kontingen_login') ?? '1') === '1';
        if (! $allowLogin) {
            return redirect()->to(base_url('pendaftaran/login'))->with('status', false)->with('message', 'Proses pendaftaran ditutup');
        }

        $auth = new KontingenAuthService();
        try {
            $success = $auth->attempt(
                (string) $this->request->getPost('email_kontingen'),
                (string) $this->request->getPost('password')
            );
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('pendaftaran/login'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        if (! $success) {
            return redirect()->to(base_url('pendaftaran/login'))->withInput()->with('status', false)->with('message', 'Username atau password salah.');
        }

        return redirect()->to(base_url('kontingen/dashboard'));
    }

    public function logout()
    {
        (new KontingenAuthService())->logout();

        return redirect()->to(base_url('pendaftaran/login'));
    }
}
