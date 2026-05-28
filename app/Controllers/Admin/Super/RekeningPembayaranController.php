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
        $accounts = $this->normalizeAccounts($this->request->getPost('accounts'));
        $errors = $this->validateAccounts($accounts);
        if ($errors !== []) {
            return redirect()->back()->withInput()->with('status', false)->with('message', $errors)->with('errors', $errors);
        }

        $this->service->saveAccounts($accounts);

        return redirect()->to(base_url('admin/super/pengaturan-event/rekening-pembayaran'))
            ->with('status', true)
            ->with('message', 'Rekening pembayaran berhasil diperbarui.');
    }

    /**
     * @param mixed $accounts
     * @return array<int, array<string, mixed>>
     */
    private function normalizeAccounts($accounts): array
    {
        if (! is_array($accounts)) {
            return [];
        }

        $normalized = [];
        foreach ($accounts as $row) {
            if (! is_array($row)) {
                continue;
            }

            $normalized[] = [
                'bank_name' => trim((string) ($row['bank_name'] ?? '')),
                'bank_account_name' => trim((string) ($row['bank_account_name'] ?? '')),
                'bank_account_number' => trim((string) ($row['bank_account_number'] ?? '')),
                'active' => ! empty($row['active']),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $accounts
     * @return array<string, string>
     */
    private function validateAccounts(array $accounts): array
    {
        $errors = [];
        foreach ($accounts as $index => $row) {
            if (mb_strlen((string) $row['bank_name']) > 50) {
                $errors['accounts.' . $index . '.bank_name'] = 'Nama bank maksimal 50 karakter.';
            }
            if (mb_strlen((string) $row['bank_account_name']) > 100) {
                $errors['accounts.' . $index . '.bank_account_name'] = 'Nama pemilik maksimal 100 karakter.';
            }
            if (mb_strlen((string) $row['bank_account_number']) > 50) {
                $errors['accounts.' . $index . '.bank_account_number'] = 'Nomor rekening maksimal 50 karakter.';
            }
        }

        return $errors;
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
