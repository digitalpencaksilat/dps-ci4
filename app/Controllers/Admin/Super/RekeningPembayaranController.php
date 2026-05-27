<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\RekeningPembayaranService;
use CodeIgniter\HTTP\RedirectResponse;

class RekeningPembayaranController extends BaseController
{
    private RekeningPembayaranService $service;

    public function __construct()
    {
        $this->service = new RekeningPembayaranService();
    }

    public function edit(): string
    {
        return view('admin/super/pengaturan_event/rekening_pembayaran', $this->viewData([
            'accounts' => $this->service->currentAccounts(),
            'errors' => session()->getFlashdata('errors') ?? [],
        ], 'Rekening Pembayaran'));
    }

    public function update(): RedirectResponse
    {
        if (! $this->validate($this->service->rules())) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $this->validator->getErrors())->with('errors', $this->validator->getErrors());
        }

        $errors = [];
        $accounts = [];
        for ($i = 1; $i <= 5; $i++) {
            $key = 'account_' . $i;
            $accounts[] = [
                'key' => $key,
                'bank_name' => (string) $this->request->getPost($key . '_bank_name'),
                'bank_account_name' => (string) $this->request->getPost($key . '_bank_account_name'),
                'bank_account_number' => (string) $this->request->getPost($key . '_bank_account_number'),
                'active' => $this->request->getPost($key . '_active') !== null,
                'display_qrcode' => $this->request->getPost($key . '_display_qrcode') !== null,
            ];

            $qr = $this->request->getFile($key . '_qrcode');
            if ($qr && $qr->isValid() && ! $qr->hasMoved()) {
                try {
                    $this->service->storeQr($key, $qr);
                } catch (\Throwable $e) {
                    $errors[$key . '_qrcode'] = $e->getMessage();
                }
            }
        }

        if ($errors !== []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $errors)->with('errors', $errors);
        }

        $this->service->saveAccounts($accounts);

        return redirect()->to(base_url('admin/super/pengaturan-event/rekening-pembayaran'))
            ->with('status', true)
            ->with('message', 'Rekening pembayaran berhasil diperbarui.');
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function viewData(array $data, string $title): array
    {
        return $data + [
            'title' => $title,
            'activeMenu' => 'pengaturan_event',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ];
    }
}
