<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriSeniService;

class SistemPoolSeniController extends BaseController
{
    public function index(): string
    {
        $service = new SekretariatKategoriSeniService();
        $rows = $service->listKategori();
        foreach ($rows as $row) {
            $row->has_related_penampilan_data = $service->hasRelatedPenampilanData((int) $row->id_sub_kategori_seni);
        }

        return view('admin/sekretariat/sistem_pool_seni/index', ['rows' => $rows, 'title' => 'Sistem Penampilan Pool', 'activeMenu' => 'sistem_pool_seni', 'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'), 'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'), 'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat')]);
    }

    public function update(int $id)
    {
        try {
            (new SekretariatKategoriSeniService())->updateSistemPenampilan($id, (string) $this->request->getPost('sistem_penampilan'));
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/sistem-pool-seni'))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/sistem-pool-seni'))->with('status', true)->with('message', 'Sistem penampilan berhasil diperbarui.');
    }
}
