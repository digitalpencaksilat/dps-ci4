<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\SettingWriterService;

class ArsipPendaftarSettingsController extends BaseController
{
    public function index(): string
    {
        return view('admin/super/pengaturan_event/arsip_pendaftar', [
            'title' => 'Pengaturan Arsip Pendaftar',
            'activeMenu' => 'pengaturan_event',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'arsipSlots' => get_arsip_pendaftar_config_ci4(),
        ]);
    }

    public function store()
    {
        if (! $this->request->isAJAX()) {
            // This endpoint is intended for in-page actions.
            // Non-AJAX callers still get JSON to keep behavior predictable.
        }
        $nama = trim((string) $this->request->getPost('nama_arsip'));
        if ($nama === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'Nama arsip tidak boleh kosong']);
        }

        $slots = get_arsip_pendaftar_config_ci4();
        $next = 1;
        foreach (array_keys($slots) as $key) {
            if (preg_match('/^slot_(\d+)$/', (string) $key, $m)) {
                $next = max($next, ((int) $m[1]) + 1);
            }
        }
        $slotName = 'slot_' . $next;

        $slots[$slotName] = [
            'nama_arsip' => $nama,
            'allowed_types' => (string) ($this->request->getPost('allowed_types') ?? 'png|jpg|jpeg'),
            'max_size' => (int) ($this->request->getPost('max_size') ?? 5000),
            'required' => (bool) ((int) ($this->request->getPost('required') ?? 0)),
            'active' => (bool) ((int) ($this->request->getPost('active') ?? 0)),
        ];

        (new SettingWriterService())->setString('arsip_pendaftar_slots', json_encode($slots));
        return $this->response->setJSON(['status' => true, 'message' => 'Slot arsip berhasil ditambahkan']);
    }

    public function update()
    {
        $slotName = trim((string) $this->request->getPost('slot_name'));
        $nama = trim((string) $this->request->getPost('nama_arsip'));
        if ($slotName === '' || $nama === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'Slot name dan nama arsip tidak boleh kosong']);
        }

        $slots = get_arsip_pendaftar_config_ci4();
        if (! array_key_exists($slotName, $slots)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Slot arsip tidak ditemukan']);
        }

        $slots[$slotName] = array_merge($slots[$slotName], [
            'nama_arsip' => $nama,
            'allowed_types' => (string) ($this->request->getPost('allowed_types') ?? ($slots[$slotName]['allowed_types'] ?? 'png|jpg|jpeg')),
            'max_size' => (int) ($this->request->getPost('max_size') ?? ($slots[$slotName]['max_size'] ?? 5000)),
            'required' => (bool) ((int) ($this->request->getPost('required') ?? 0)),
            'active' => (bool) ((int) ($this->request->getPost('active') ?? 0)),
        ]);

        (new SettingWriterService())->setString('arsip_pendaftar_slots', json_encode($slots));
        return $this->response->setJSON(['status' => true, 'message' => 'Slot arsip berhasil diperbarui']);
    }

    public function delete()
    {
        $slotName = trim((string) $this->request->getPost('slot_name'));
        if ($slotName === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'Slot name tidak boleh kosong']);
        }

        $slots = get_arsip_pendaftar_config_ci4();
        if (! array_key_exists($slotName, $slots)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Slot arsip tidak ditemukan']);
        }

        unset($slots[$slotName]);
        (new SettingWriterService())->setString('arsip_pendaftar_slots', json_encode($slots));
        return $this->response->setJSON(['status' => true, 'message' => 'Slot arsip berhasil dihapus']);
    }

    public function toggleActive()
    {
        $slotName = trim((string) $this->request->getPost('slot_name'));
        $active = (bool) ((int) ($this->request->getPost('active') ?? 0));

        if ($slotName === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'Slot name tidak boleh kosong']);
        }

        $slots = get_arsip_pendaftar_config_ci4();
        if (! array_key_exists($slotName, $slots)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Slot arsip tidak ditemukan']);
        }

        $slots[$slotName]['active'] = $active;
        (new SettingWriterService())->setString('arsip_pendaftar_slots', json_encode($slots));

        return $this->response->setJSON([
            'status' => true,
            'message' => 'Status slot berhasil diubah',
            'active' => $active,
        ]);
    }
}
