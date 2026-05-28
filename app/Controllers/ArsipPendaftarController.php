<?php

namespace App\Controllers;

use App\Models\ArsipPendaftarModel;
use App\Models\PendaftarModel;
use App\Services\ArsipPendaftarService;

class ArsipPendaftarController extends BaseController
{
    public function create(int $idPendaftar)
    {
        $peserta = $this->getPesertaWithAccess($idPendaftar);
        if ($peserta === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        try {
            (new ArsipPendaftarService())->syncUploads($idPendaftar, $this->request->getFiles());
            return redirect()->back()->with('status', true)->with('message', 'Arsip berhasil diunggah.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $e->getMessage());
        }
    }

    public function update(int $idPendaftar, int $idArsip)
    {
        $peserta = $this->getPesertaWithAccess($idPendaftar);
        if ($peserta === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $arsip = (new ArsipPendaftarModel())->find($idArsip);
        if ($arsip === null || $arsip->id_pendaftar !== $idPendaftar) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        try {
            (new ArsipPendaftarService())->syncUploads($idPendaftar, $this->request->getFiles());
            return redirect()->back()->with('status', true)->with('message', 'Arsip berhasil diperbarui.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $e->getMessage());
        }
    }

    public function delete(int $idPendaftar, int $idArsip)
    {
        $peserta = $this->getPesertaWithAccess($idPendaftar);
        if ($peserta === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $arsip = (new ArsipPendaftarModel())->find($idArsip);
        if ($arsip === null || $arsip->id_pendaftar !== $idPendaftar) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        try {
            (new ArsipPendaftarService())->deleteArchive($idArsip);
            return redirect()->back()->with('status', true)->with('message', 'Arsip berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('status', false)->with('message', $e->getMessage());
        }
    }

    private function getPesertaWithAccess(int $idPendaftar): ?object
    {
        $userLevel = (string) session()->get('level');
        $idKontingen = (int) session()->get('id_kontingen');

        $peserta = (new PendaftarModel())->find($idPendaftar);
        if ($peserta === null) {
            return null;
        }

        if ($userLevel === 'kontingen' && $peserta->id_kontingen !== $idKontingen) {
            return null;
        }

        if (!in_array($userLevel, ['kontingen', 'sekretariat'], true)) {
            return null;
        }

        return $peserta;
    }
}
