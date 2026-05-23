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
        $data['perbolehkan_kontingen_mendaftar'] = (bool) ci3_config_item('perbolehkan_kontingen_mendaftar', 'pendaftaran/akses_pendaftaran');
        $data['recaptchaSiteKey'] = (new RecaptchaService())->siteKey();
        $data['recaptchaEnabled'] = (new RecaptchaService())->isConfigured();

        return view('pendaftaran/template', $data);
    }

    public function submitRegistrasi()
    {
        $allowRegister = (bool) (ci3_config_item('perbolehkan_kontingen_mendaftar', 'pendaftaran/akses_pendaftaran') ?? true);
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
            'brand_abbreviation' => (string) (ci3_config_item('brand_abbreviation', 'pendaftaran/profil_kejuaraan') ?? ''),
            'event_name' => (string) (ci3_config_item('event_name', 'pendaftaran/profil_kejuaraan') ?? ''),
            'landing_page_description' => (string) (ci3_config_item('landing_page_description', 'pendaftaran/profil_kejuaraan') ?? ''),
            'event_host' => (string) (ci3_config_item('event_host', 'pendaftaran/profil_kejuaraan') ?? ''),
            'registration_start' => (string) (ci3_config_item('registration_start', 'pendaftaran/profil_kejuaraan') ?? ''),
            'registration_end' => (string) (ci3_config_item('registration_end', 'pendaftaran/profil_kejuaraan') ?? ''),
            'event_location' => (string) (ci3_config_item('event_location', 'pendaftaran/profil_kejuaraan') ?? ''),
            'date_start' => (string) (ci3_config_item('date_start', 'pendaftaran/profil_kejuaraan') ?? ''),
            'date_end' => (string) (ci3_config_item('date_end', 'pendaftaran/profil_kejuaraan') ?? ''),
            'technical_meeting_date' => (string) (ci3_config_item('technical_meeting_date', 'pendaftaran/profil_kejuaraan') ?? ''),
            'technical_meeting_location' => (string) (ci3_config_item('technical_meeting_location', 'pendaftaran/profil_kejuaraan') ?? ''),
            'contact_person' => (string) (ci3_config_item('contact_person', 'pendaftaran/profil_kejuaraan') ?? ''),
            'countdown' => (string) (ci3_config_item('countdown', 'pendaftaran/profil_kejuaraan') ?? ''),
            'fight_category' => (string) (ci3_config_item('fight_category', 'pendaftaran/profil_kejuaraan') ?? ''),
        ];
    }
}
