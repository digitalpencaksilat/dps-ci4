<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;

class PengadaanMedaliController extends BaseController
{
    public function index()
    {
        $tandingModel = new \App\Models\PerolehanMedaliTandingModel();
        $seniModel = new \App\Models\PerolehanMedaliSeniModel();

        $data = [
            'title' => 'Prediksi Pengadaan Medali',
            'breadcrumb' => 'Prediksi Pengadaan Medali',
            'data_prediksi_medali_tanding' => $tandingModel->get_prediksi_medali(),
            'data_prediksi_medali_seni' => $seniModel->get_prediksi_medali(),
        ];

        return $this->renderView('admin/sekretariat/pengadaan_medali', $data['title'], $data);
    }

    private function renderView(string $view, string $title, array $data): string
    {
        return view($view, $data + [
            'title'      => $title,
            'activeMenu' => 'pengadaan_medali',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
        ]);
    }
}
