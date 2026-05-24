<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\MedalTallyService;

class MedalTallyController extends BaseController
{
    public function aggregate(): string
    {
        return $this->render('admin/sekretariat/medal_tally/aggregate', 'Akumulasi Perolehan Medali', [
            'rows' => (new MedalTallyService())->getAkumulasiMedali(),
            'reportTitle' => 'Akumulasi Perolehan Medali',
        ]);
    }

    public function byAgeCategory(): string
    {
        return $this->render('admin/sekretariat/medal_tally/by_age_category', 'Perolehan Medali Per Kategori Usia', [
            'categoryRows' => (new MedalTallyService())->getAkumulasiMedaliByKategoriUsia(),
            'reportTitle' => 'Perolehan Medali Per Kategori Usia',
        ]);
    }

    public function bySchool(): string
    {
        return $this->render('admin/sekretariat/medal_tally/by_school', 'Perolehan Medali Berdasarkan Sekolah', [
            'categoryRows' => (new MedalTallyService())->getAkumulasiMedaliBerdasarkanSekolah(),
            'reportTitle' => 'Perolehan Medali Berdasarkan Sekolah',
        ]);
    }

    public function aggregateExclusive(): string
    {
        return $this->render('admin/sekretariat/medal_tally/aggregate_exclusive', 'Akumulasi Perolehan Medali Eksklusif', [
            'rows' => (new MedalTallyService())->getAkumulasiMedaliEksklusif(),
            'reportTitle' => 'Akumulasi Perolehan Medali Eksklusif',
        ]);
    }

    public function byAgeCategoryExclusive(): string
    {
        return $this->render('admin/sekretariat/medal_tally/by_age_category_exclusive', 'Perolehan Medali Per Kategori Usia Eksklusif', [
            'categoryRows' => (new MedalTallyService())->getAkumulasiMedaliByKategoriUsiaEksklusif(),
            'reportTitle' => 'Perolehan Medali Per Kategori Usia Eksklusif',
        ]);
    }

    public function tanding(): string
    {
        return $this->render('admin/sekretariat/medal_tally/tanding', 'Perolehan Medali Tanding', [
            'rows' => (new MedalTallyService())->getPerolehanMedaliTanding(),
            'reportTitle' => 'Perolehan Medali Tanding',
        ]);
    }

    public function seni(): string
    {
        return $this->render('admin/sekretariat/medal_tally/seni', 'Perolehan Medali Seni', [
            'rows' => (new MedalTallyService())->getPerolehanMedaliSeni(),
            'reportTitle' => 'Perolehan Medali Seni',
        ]);
    }

    private function render(string $view, string $title, array $data): string
    {
        return view($view, $data + [
            'title'      => $title,
            'activeMenu' => 'medal_tally',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
        ]);
    }
}
