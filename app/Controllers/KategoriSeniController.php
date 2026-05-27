<?php

namespace App\Controllers;

use App\Services\KategoriSeniService;
use CodeIgniter\Exceptions\PageNotFoundException;

class KategoriSeniController extends BaseController
{
    public function index(): string
    {
        $service = new KategoriSeniService();
        $idKontingen = (int) session()->get('id_kontingen');

        return view('kontingen/seni/index', [
            'title'            => 'Kategori Seni',
            'activeMenu'       => 'seni',
            'eventName'        => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'        => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'kelompokSeni'     => $service->listByKontingen($idKontingen),
            'kompetisiSeni'    => $service->availableKompetisi($idKontingen),
            'allowCreate'      => (string) (get_setting('perbolehkan_kontingen_memilih_kategori') ?? '0') === '1',
            'allowDelete'      => (string) (get_setting('perbolehkan_undur_diri_atlet') ?? '0') === '1',
            'allowEdit'        => (string) (get_setting('perbolehkan_ganti_atlet_dan_kategori') ?? '0') === '1',
        ]);
    }

    public function options(int $idKompetisi)
    {
        return $this->response->setJSON((new KategoriSeniService())->availablePendaftarByKompetisi($idKompetisi, (int) session()->get('id_kontingen')));
    }

    public function store()
    {
        if ((string) (get_setting('perbolehkan_kontingen_memilih_kategori') ?? '0') !== '1') {
            return redirect()->to(base_url('kontingen/seni'))->with('status', false)->with('message', 'Pemilihan kategori seni sedang ditutup.');
        }

        $idPendaftar = $this->request->getPost('id_pendaftar') ?? [];
        if (! is_array($idPendaftar)) {
            $idPendaftar = [];
        }

        if (! $this->validate(['id_kompetisi_seni' => 'required|integer'])) {
            return redirect()->to(base_url('kontingen/seni'))->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new KategoriSeniService())->create(
                (int) session()->get('id_kontingen'),
                (int) $this->request->getPost('id_kompetisi_seni'),
                array_map('intval', $idPendaftar),
                $this->request->getPost('keterangan')
            );
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('kontingen/seni'))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('kontingen/seni'))->with('status', true)->with('message', 'Kategori seni berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if ((string) (get_setting('perbolehkan_ganti_atlet_dan_kategori') ?? '0') !== '1') {
            return redirect()->to(base_url('kontingen/seni'))->with('status', false)->with('message', 'Perubahan kategori seni sedang ditutup.');
        }

        $record = $this->ownedRecord($id);
        if ($record === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate(['id_kompetisi_seni' => 'required|integer'])) {
            return redirect()->to(base_url('kontingen/seni'))->with('status', false)->with('message', $this->validator->getErrors());
        }

        (new KategoriSeniService())->update($record, (int) $this->request->getPost('id_kompetisi_seni'));

        return redirect()->to(base_url('kontingen/seni'))->with('status', true)->with('message', 'Kategori seni berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if ((string) (get_setting('perbolehkan_undur_diri_atlet') ?? '0') !== '1') {
            return redirect()->to(base_url('kontingen/seni'))->with('status', false)->with('message', 'Penghapusan kategori seni sedang ditutup.');
        }

        $record = $this->ownedRecord($id);
        if ($record === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ($record->id_pembayaran !== null) {
            return redirect()->to(base_url('kontingen/seni'))->with('status', false)->with('message', 'Kategori seni yang sudah masuk pembayaran tidak bisa dihapus.');
        }

        (new KategoriSeniService())->delete($record);

        return redirect()->to(base_url('kontingen/seni'))->with('status', true)->with('message', 'Kategori seni berhasil dihapus.');
    }

    private function ownedRecord(int $id): ?object
    {
        return db_connect()->table('kelompok_peserta_seni')
            ->where('id_kelompok_peserta_seni', $id)
            ->where('id_kontingen', (int) session()->get('id_kontingen'))
            ->get()
            ->getRow();
    }
}
