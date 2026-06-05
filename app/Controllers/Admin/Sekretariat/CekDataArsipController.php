<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Models\PendaftarModel;
use App\Models\ArsipPendaftarModel;

class CekDataArsipController extends BaseController
{
    /**
     * Halaman utama cek data arsip — tabel semua pendaftar + status arsip.
     */
    public function index()
    {
        helper('arsip_pendaftar');

        $activeArsip = get_active_arsip_pendaftar_ci4();

        $pendaftarModel = new PendaftarModel();
        $pendaftarRows = $pendaftarModel->select('pendaftar.id_pendaftar, pendaftar.nama_pendaftar, pendaftar.foto, kontingen.nama_kontingen')
            ->join('kontingen', 'kontingen.id_kontingen = pendaftar.id_kontingen', 'left')
            ->orderBy('kontingen.nama_kontingen', 'ASC')
            ->orderBy('pendaftar.nama_pendaftar', 'ASC')
            ->findAll();

        $pendaftarIds = array_column($pendaftarRows, 'id_pendaftar');
        $arsipGrouped = [];

        if (!empty($pendaftarIds)) {
            $arsipModel = new ArsipPendaftarModel();
            $allArsip = $arsipModel->whereIn('id_pendaftar', $pendaftarIds)->findAll();

            foreach ($allArsip as $arsip) {
                $arsipGrouped[$arsip->id_pendaftar][] = $arsip;
            }
        }

        return view('admin/sekretariat/cek_data_arsip/index', [
            'title'         => 'Cek Data Arsip',
            'activeMenu'    => 'cek_data_arsip',
            'eventName'     => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'     => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'     => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'activeArsip'   => $activeArsip,
            'pendaftarRows' => $pendaftarRows,
            'arsipGrouped'  => $arsipGrouped,
        ]);
    }

    /**
     * AJAX: detail arsip peserta untuk modal.
     */
    public function getDetailArsip()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        helper('arsip_pendaftar');

        $idPendaftar = (int) $this->request->getPost('id_pendaftar');
        if ($idPendaftar <= 0) {
            return $this->response->setBody('<div class="alert alert-danger">ID peserta tidak valid.</div>');
        }

        $pendaftarModel = new PendaftarModel();
        $pendaftar = $pendaftarModel->select('pendaftar.id_pendaftar, pendaftar.nama_pendaftar, kontingen.nama_kontingen')
            ->join('kontingen', 'kontingen.id_kontingen = pendaftar.id_kontingen', 'left')
            ->find($idPendaftar);

        if ($pendaftar === null) {
            return $this->response->setBody('<div class="alert alert-danger">Peserta tidak ditemukan.</div>');
        }

        $arsipModel = new ArsipPendaftarModel();
        $arsipPeserta = $arsipModel->where('id_pendaftar', $idPendaftar)->findAll();

        $activeArsip = get_active_arsip_pendaftar_ci4();

        return $this->response->setBody(
            view('admin/sekretariat/cek_data_arsip/_detail_modal_body', [
                'pendaftar'    => $pendaftar,
                'arsipPeserta' => $arsipPeserta,
                'activeArsip'  => $activeArsip,
            ])
        );
    }
}
