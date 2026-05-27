<?php

namespace App\Controllers;

use App\Models\SiteBuilderMenusModel;
use App\Services\KontingenRegistrationService;
use App\Services\RecaptchaService;

class PendaftaranController extends BaseController
{
    public function index()
    {
        $data = $this->baseViewData();
        $data['main_view'] = 'pendaftaran/pages/home';

        return view('pendaftaran/template', $data);
    }

    public function registrasi()
    {
        $data = $this->baseViewData();
        $data['main_view'] = 'pendaftaran/pages/registrasi';
        $data['perbolehkan_kontingen_mendaftar'] = (string) (get_setting('perbolehkan_kontingen_mendaftar') ?? '0') === '1';
        $data['recaptchaSiteKey'] = (new RecaptchaService())->siteKey();
        $data['recaptchaEnabled'] = (new RecaptchaService())->isConfigured();

        return view('pendaftaran/template', $data);
    }

    public function submitRegistrasi()
    {
        $allowRegister = (string) (get_setting('perbolehkan_kontingen_mendaftar') ?? '1') === '1';
        if (! $allowRegister) {
            return redirect()->to(base_url('registrasi'))->with('status', false)->with('message', 'Pendaftaran kontingen sedang ditutup.');
        }

        $rules = [
            'nama_kontingen'                => 'required|max_length[150]',
            'email_kontingen'               => 'required|valid_email|max_length[150]',
            'password'                      => 'required|min_length[6]',
            'retype_password'               => 'required|matches[password]',
            'jenis_kontingen'               => 'required|in_list[dalam_negeri,luar_negeri]',
            'nama_penanggungjawab'          => 'required|max_length[150]',
            'jabatan_penanggungjawab'       => 'required|max_length[255]',
            'nomor_telepon_penanggungjawab' => 'required|numeric|min_length[9]|max_length[15]',
            'nomor_telepon_kontingen'       => 'permit_empty|numeric|min_length[9]|max_length[15]',
            'alamat_lengkap'                => 'required|min_length[8]',
        ];

        if (($this->request->getPost('jenis_kontingen') ?? 'dalam_negeri') === 'dalam_negeri') {
            $rules['provinsi'] = 'required|max_length[150]';
            $rules['kabupaten_kota'] = 'required|max_length[150]';
            $rules['kecamatan'] = 'required|max_length[150]';
            $rules['kelurahan'] = 'required|max_length[150]';
        } else {
            $rules['negara'] = 'required|max_length[150]';
        }

        if (! $this->validate($rules)) {
            return redirect()->to(base_url('registrasi'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        $recaptcha = new RecaptchaService();
        if ($recaptcha->isConfigured()) {
            $token = (string) $this->request->getPost('g-recaptcha-response');
            $valid = $recaptcha->verify($token, $this->request->getIPAddress());

            if (! $valid) {
                return redirect()->to(base_url('registrasi'))->withInput()->with('status', false)->with('message', 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
            }
        }

        try {
            $ok = (new KontingenRegistrationService())->create($this->request->getPost());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('registrasi'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        if (! $ok) {
            return redirect()->to(base_url('registrasi'))->withInput()->with('status', false)->with('message', 'Gagal mendaftarkan kontingen.');
        }

        return redirect()->to(base_url('pendaftaran/login'))->with('status', true)->with('message', 'Pendaftaran kontingen berhasil. Silakan login.');
    }

    public function downloadJuknis()
    {
        return $this->response->setStatusCode(501)->setBody('Not implemented');
    }

    public function downloadFormExcel()
    {
        $path = FCPATH . 'assets/excel/FORM_EXCEL.xlsx';
        if (! is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        return $this->response->download($path, null);
    }

    private function baseViewData(): array
    {
        $menusModel = new SiteBuilderMenusModel();

        return [
            'nav_items' => $menusModel->findAll(),
            'akses'     => [
                'akses_cek_data'     => false,
                'akses_lihat_jadwal' => false,
                'akses_lihat_bagan'  => false,
                'akses_live_jadwal'  => false,
                'akses_live_medali'  => false,
                'akses_lihat_kuota'  => false,
            ],
            'event'     => $this->loadEventConfig(),
        ];
    }

    private function loadEventConfig(): array
    {
        return [
            'brand_abbreviation' => (string) (get_setting('brand_abbreviation') ?? ''),
            'event_name' => (string) (get_setting('event_name') ?? ''),
            'landing_page_description' => (string) (get_setting('landing_page_description') ?? ''),
            'event_host' => (string) (get_setting('event_host') ?? ''),
            'registration_start' => (string) (get_setting('registration_start') ?? ''),
            'registration_end' => (string) (get_setting('registration_end') ?? ''),
            'event_location' => (string) (get_setting('event_location') ?? ''),
            'date_start' => (string) (get_setting('date_start') ?? ''),
            'date_end' => (string) (get_setting('date_end') ?? ''),
            'technical_meeting_date' => (string) (get_setting('technical_meeting_date') ?? ''),
            'technical_meeting_location' => (string) (get_setting('technical_meeting_location') ?? ''),
            'contact_person' => (string) (get_setting('contact_person') ?? ''),
            'countdown' => (string) (get_setting('countdown') ?? ''),
            'fight_category' => (string) (get_setting('fight_category') ?? ''),
        ];
    }
}
