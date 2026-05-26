<?php

namespace App\Controllers;

use App\Models\PesertaTandingModel;
use App\Services\KategoriTandingService;
use CodeIgniter\Exceptions\PageNotFoundException;

class KategoriTandingController extends BaseController
{
    public function index(): string
    {
        $service = new KategoriTandingService();
        $idKontingen = (int) session()->get('id_kontingen');

        return view('kontingen/tanding/index', [
            'title'             => 'Kategori Tanding',
            'activeMenu'        => 'tanding',
            'eventName'         => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'         => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'pesertaTanding'    => $service->listByKontingen($idKontingen),
            'pendaftarTersedia' => $service->availablePendaftar($idKontingen),
            'allowCreate'       => (bool) (ci3_config_item('perbolehkan_kontingen_memilih_kategori', 'pendaftaran/akses_pendaftaran') ?? false),
            'allowDelete'       => (bool) (ci3_config_item('perbolehkan_undur_diri_atlet', 'pendaftaran/akses_pendaftaran') ?? false),
            'allowEdit'         => (bool) (ci3_config_item('perbolehkan_ganti_atlet_dan_kategori', 'pendaftaran/akses_pendaftaran') ?? false),
        ]);
    }

    public function options(int $idPendaftar)
    {
        return $this->response->setJSON((new KategoriTandingService())->availableKompetisiForPendaftar($idPendaftar));
    }

    public function store()
    {
        if (! (bool) (ci3_config_item('perbolehkan_kontingen_memilih_kategori', 'pendaftaran/akses_pendaftaran') ?? false)) {
            return redirect()->to(base_url('kontingen/tanding'))->with('status', false)->with('message', 'Pemilihan kategori tanding sedang ditutup.');
        }

        $rules = [
            'id_pendaftar'         => 'required|integer',
            'id_kompetisi_tanding' => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(base_url('kontingen/tanding'))->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new KategoriTandingService())->create(
                (int) session()->get('id_kontingen'),
                (int) $this->request->getPost('id_pendaftar'),
                (int) $this->request->getPost('id_kompetisi_tanding')
            );
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('kontingen/tanding'))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('kontingen/tanding'))->with('status', true)->with('message', 'Kategori tanding berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if (! (bool) (ci3_config_item('perbolehkan_ganti_atlet_dan_kategori', 'pendaftaran/akses_pendaftaran') ?? false)) {
            return redirect()->to(base_url('kontingen/tanding'))->with('status', false)->with('message', 'Perubahan kategori tanding sedang ditutup.');
        }

        $record = $this->ownedRecord($id);
        if ($record === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate(['id_kompetisi_tanding' => 'required|integer'])) {
            return redirect()->to(base_url('kontingen/tanding'))->with('status', false)->with('message', $this->validator->getErrors());
        }

        (new KategoriTandingService())->update($record, (int) $this->request->getPost('id_kompetisi_tanding'));

        return redirect()->to(base_url('kontingen/tanding'))->with('status', true)->with('message', 'Kategori tanding berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (! (bool) (ci3_config_item('perbolehkan_undur_diri_atlet', 'pendaftaran/akses_pendaftaran') ?? false)) {
            return redirect()->to(base_url('kontingen/tanding'))->with('status', false)->with('message', 'Penghapusan kategori tanding sedang ditutup.');
        }

        $record = $this->ownedRecord($id);
        if ($record === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($record->id_pembayaran !== null) {
            return redirect()->to(base_url('kontingen/tanding'))->with('status', false)->with('message', 'Kategori tanding yang sudah masuk pembayaran tidak bisa dihapus.');
        }

        (new KategoriTandingService())->delete($record);

        return redirect()->to(base_url('kontingen/tanding'))->with('status', true)->with('message', 'Kategori tanding berhasil dihapus.');
    }

    private function ownedRecord(int $id): ?object
    {
        return db_connect()->table('peserta_tanding pt')
            ->select('pt.*')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->where('pt.id_peserta_tanding', $id)
            ->where('p.id_kontingen', (int) session()->get('id_kontingen'))
            ->get()
            ->getRow();
    }
}
