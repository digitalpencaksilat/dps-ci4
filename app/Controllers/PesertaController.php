<?php

namespace App\Controllers;

use App\Models\PendaftarModel;
use App\Services\ArsipPendaftarService;
use App\Services\PesertaService;

class PesertaController extends BaseController
{
    public function index(): string
    {
        $idKontingen = (int) session()->get('id_kontingen');

        $peserta = (new PendaftarModel())
            ->where('id_kontingen', $idKontingen)
            ->orderBy('nama_pendaftar', 'ASC')
            ->findAll();

        $arsipByPendaftar = [];
        foreach ($peserta as $item) {
            $arsipByPendaftar[$item->id_pendaftar] = get_arsip_pendaftar_by_peserta_ci4((int) $item->id_pendaftar);
        }

        return view('kontingen/peserta/index', [
            'title'      => 'Peserta Kontingen',
            'activeMenu' => 'peserta',
            'peserta'    => $peserta,
            'arsipByPendaftar' => $arsipByPendaftar,
            'arsipSlots' => get_active_arsip_pendaftar_ci4(),
            'allowCreate' => (bool) (ci3_config_item('perbolehkan_kontingen_input_atlet', 'pendaftaran/akses_pendaftaran') ?? false),
            'allowEdit'   => (bool) (ci3_config_item('perbolehkan_edit_biodata', 'pendaftaran/akses_pendaftaran') ?? false),
            'allowDelete' => (bool) (ci3_config_item('perbolehkan_undur_diri_atlet', 'pendaftaran/akses_pendaftaran') ?? false),
            'maxAtlet'    => (int) (ci3_config_item('max_atlet_per_kontingen', 'pendaftaran/max_atlet_per_kontingen') ?? 0),
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
        ]);
    }

    public function store()
    {
        if (! (bool) (ci3_config_item('perbolehkan_kontingen_input_atlet', 'pendaftaran/akses_pendaftaran') ?? false)) {
            return redirect()->to(base_url('kontingen/peserta'))->with('status', false)->with('message', 'Input atlet saat ini ditutup.');
        }

        if (! $this->validate($this->pesertaRules())) {
            return redirect()->to(base_url('kontingen/peserta'))->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('openPesertaModal', 'create');
        }

        try {
            $idPendaftar = (new PesertaService())->create((int) session()->get('id_kontingen'), $this->request->getPost());
            (new ArsipPendaftarService())->syncUploads($idPendaftar, $this->request->getFiles());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('kontingen/peserta'))->withInput()->with('status', false)->with('message', $e->getMessage())->with('openPesertaModal', 'create');
        }

        return redirect()->to(base_url('kontingen/peserta'))->with('status', true)->with('message', 'Peserta berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        if (! (bool) (ci3_config_item('perbolehkan_edit_biodata', 'pendaftaran/akses_pendaftaran') ?? false)) {
            return redirect()->to(base_url('kontingen/peserta'))->with('status', false)->with('message', 'Edit biodata peserta saat ini ditutup.');
        }

        $peserta = $this->ownedPeserta($id);
        if ($peserta === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $this->validate($this->pesertaRules())) {
            return redirect()->to(base_url('kontingen/peserta'))->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('openPesertaModal', 'edit')->with('openPesertaId', $id);
        }

        (new PesertaService())->update($peserta, $this->request->getPost());
        (new ArsipPendaftarService())->syncUploads($peserta->id_pendaftar, $this->request->getFiles());

        return redirect()->to(base_url('kontingen/peserta'))->with('status', true)->with('message', 'Peserta berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (! (bool) (ci3_config_item('perbolehkan_undur_diri_atlet', 'pendaftaran/akses_pendaftaran') ?? false)) {
            return redirect()->to(base_url('kontingen/peserta'))->with('status', false)->with('message', 'Hapus peserta saat ini ditutup.');
        }

        $peserta = $this->ownedPeserta($id);
        if ($peserta === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        (new PesertaService())->delete($peserta);

        return redirect()->to(base_url('kontingen/peserta'))->with('status', true)->with('message', 'Peserta berhasil dihapus.');
    }

    private function ownedPeserta(int $id): ?object
    {
        return (new PendaftarModel())
            ->where('id_pendaftar', $id)
            ->where('id_kontingen', (int) session()->get('id_kontingen'))
            ->first();
    }

    private function pesertaRules(): array
    {
        return [
            'nama_pendaftar'           => 'required|max_length[255]',
            'jenis_kelamin'            => 'required|in_list[putra,putri]',
            'tinggi_badan'             => 'required|numeric|greater_than[0]',
            'berat_badan'              => 'required|numeric|greater_than[0]',
            'tempat_lahir'             => 'required|max_length[255]',
            'tanggal_lahir'            => 'required|valid_date[Y-m-d]',
            'nama_sekolah'             => 'permit_empty|max_length[255]',
            'alamat'                   => 'permit_empty|max_length[1000]',
            'nomor_induk_kependudukan' => 'permit_empty|exact_length[16]|numeric',
            'nomor_kartu_keluarga'     => 'permit_empty|exact_length[16]|numeric',
        ];
    }
}
