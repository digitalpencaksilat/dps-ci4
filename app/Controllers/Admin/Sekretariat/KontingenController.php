<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\ArsipPendaftarService;
use App\Services\SekretariatPesertaKontingenService;
use CodeIgniter\Exceptions\PageNotFoundException;

class KontingenController extends BaseController
{
    public function index(): string
    {
        return view('admin/sekretariat/kontingen/index', [
            'title'         => 'Data Kontingen',
            'activeMenu'    => 'kontingen',
            'kontingenSubmenu' => 'sub_kontingen',
            'eventName'     => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'     => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'     => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'kontingenRows' => (new SekretariatPesertaKontingenService())->listKontingen(),
        ]);
    }

    public function rekapAtlet(): string
    {
        return view('admin/sekretariat/kontingen/rekap_atlet', [
            'title'         => 'Rekap Atlet Kontingen',
            'activeMenu'    => 'kontingen',
            'kontingenSubmenu' => 'rekap_atlet',
            'eventName'     => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'     => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'     => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'kontingenRows' => (new SekretariatPesertaKontingenService())->listKontingenForRekapAtlet(),
        ]);
    }

    public function show(int $id): string
    {
        if ($id <= 0) {
            throw PageNotFoundException::forPageNotFound();
        }

        $detail = (new SekretariatPesertaKontingenService())->getKontingenDetail($id);
        if ($detail === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $arsipByPendaftar = [];
        foreach (($detail['pendaftar'] ?? []) as $item) {
            $arsipByPendaftar[$item->id_pendaftar] = get_arsip_pendaftar_by_peserta_ci4((int) $item->id_pendaftar);
        }

        return view('admin/sekretariat/kontingen/show', [
            'title'      => 'Detail Kontingen',
            'activeMenu' => 'kontingen',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'detail'     => $detail,
            'arsipByPendaftar' => $arsipByPendaftar,
            'arsipSlots' => get_active_arsip_pendaftar_ci4(),
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->kontingenRules(true))) {
            return redirect()->to(base_url('admin/sekretariat/kontingen'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            $id = (new SekretariatPesertaKontingenService())->createKontingen($this->request->getPost());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id))->with('status', true)->with('message', 'Kontingen berhasil dibuat.');
    }

    public function update(int $id)
    {
        if (! $this->validate($this->kontingenRules(false))) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=pendaftar'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->updateKontingen($id, $this->request->getPost());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=pendaftar'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id))->with('status', true)->with('message', 'Kontingen berhasil diperbarui.');
    }

    public function resetPassword(int $id)
    {
        if (! $this->validate(['password' => 'required|min_length[6]'])) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id))->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->resetKontingenPassword($id, (string) $this->request->getPost('password'));
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id))->with('status', true)->with('message', 'Password kontingen berhasil direset.');
    }

    public function delete(int $id)
    {
        try {
            (new SekretariatPesertaKontingenService())->deleteKontingen($id);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen'))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen'))->with('status', true)->with('message', 'Kontingen berhasil dihapus.');
    }

    public function storePendaftar(int $id)
    {
        if (! $this->validate($this->pendaftarRules())) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            $idPendaftar = (new SekretariatPesertaKontingenService())->createPendaftar($id, $this->request->getPost());
            (new ArsipPendaftarService())->syncUploads($idPendaftar, $this->request->getFiles());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=pendaftar'))->with('status', true)->with('message', 'Peserta berhasil ditambahkan.');
    }

    public function updatePendaftar(int $id, int $idPendaftar)
    {
        if (! $this->validate($this->pendaftarRules())) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=pendaftar'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->updatePendaftar($id, $idPendaftar, $this->request->getPost());
            (new ArsipPendaftarService())->syncUploads($idPendaftar, $this->request->getFiles());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=pendaftar'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=pendaftar'))->with('status', true)->with('message', 'Peserta berhasil diperbarui.');
    }

    public function deletePendaftar(int $id, int $idPendaftar)
    {
        try {
            (new SekretariatPesertaKontingenService())->deletePendaftar($id, $idPendaftar);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=pendaftar'))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=pendaftar'))->with('status', true)->with('message', 'Peserta berhasil dihapus.');
    }

    public function storePesertaTanding(int $id)
    {
        if (! $this->validate(['id_pendaftar' => 'required|integer', 'id_kompetisi_tanding' => 'required|integer'])) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=tanding'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->createPesertaTanding($this->request->getPost());
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=tanding'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=tanding'))->with('status', true)->with('message', 'Peserta tanding berhasil ditambahkan.');
    }

    public function storeKelompokSeni(int $id)
    {
        $payload = $this->request->getPost();
        $payload['id_kontingen'] = $id;

        if (! $this->validate(['id_kompetisi_seni' => 'required|integer', 'id_pendaftar' => 'required'])) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=seni'))->withInput()->with('status', false)->with('message', $this->validator->getErrors());
        }

        try {
            (new SekretariatPesertaKontingenService())->createKelompokSeni($payload);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=seni'))->withInput()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/kontingen/' . $id . '?tab=seni'))->with('status', true)->with('message', 'Kelompok seni berhasil ditambahkan.');
    }

    private function kontingenRules(bool $requirePassword): array
    {
        $rules = [
            'nama_kontingen' => 'required|max_length[255]',
            'email_kontingen' => 'required|valid_email|max_length[255]',
            'jenis_kontingen' => 'required|in_list[dalam_negeri,luar_negeri]',
            'perguruan' => 'required|in_list[ipsi,ts,psht,pamur]',
            'nama_penanggungjawab' => 'required|max_length[255]',
            'nomor_telepon_penanggungjawab' => 'required|max_length[255]',
        ];

        if ($requirePassword) {
            $rules['password'] = 'required|min_length[6]';
        }

        return $rules;
    }

    private function pendaftarRules(): array
    {
        return [
            'nama_pendaftar' => 'required|max_length[255]',
            'jenis_kelamin' => 'required|in_list[putra,putri]',
            'tinggi_badan' => 'required|numeric|greater_than[0]',
            'berat_badan' => 'required|numeric|greater_than[0]',
            'tempat_lahir' => 'required|max_length[255]',
            'tanggal_lahir' => 'required|valid_date[Y-m-d]',
            'nama_sekolah' => 'permit_empty|max_length[255]',
            'nomor_induk_kependudukan' => 'permit_empty|exact_length[16]|numeric',
            'nomor_kartu_keluarga' => 'permit_empty|exact_length[16]|numeric',
        ];
    }
}
