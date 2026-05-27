<?php

namespace App\Services\Admin\Super;

use CodeIgniter\HTTP\Files\UploadedFile;

class RekeningPembayaranService
{
    private SettingWriterService $writer;
    private FileSettingService $fileSetting;

    public function __construct()
    {
        $this->writer = new SettingWriterService();
        $this->fileSetting = new FileSettingService();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function currentAccounts(int $max = 5): array
    {
        $accounts = [];
        for ($i = 1; $i <= $max; $i++) {
            $key = 'account_' . $i;

            // Prefer DB settings; fallback to CI3 config.
            $bankName = (string) (get_setting($key . '_bank_name', 'pendaftaran/rekening_pembayaran') ?? ci3_config_item($key . '.bank_name', 'pendaftaran/rekening_pembayaran') ?? '');
            $accountName = (string) (get_setting($key . '_bank_account_name', 'pendaftaran/rekening_pembayaran') ?? ci3_config_item($key . '.bank_account_name', 'pendaftaran/rekening_pembayaran') ?? '');
            $accountNumber = (string) (get_setting($key . '_bank_account_number', 'pendaftaran/rekening_pembayaran') ?? ci3_config_item($key . '.bank_account_number', 'pendaftaran/rekening_pembayaran') ?? '');

            $activeRaw = get_setting($key . '_active', 'pendaftaran/rekening_pembayaran');
            $displayRaw = get_setting($key . '_display_qrcode', 'pendaftaran/rekening_pembayaran');

            $active = $activeRaw !== null
                ? ((string) $activeRaw === '1')
                : (bool) (ci3_config_item($key . '.active', 'pendaftaran/rekening_pembayaran') ?? false);

            $displayQr = $displayRaw !== null
                ? ((string) $displayRaw === '1')
                : (bool) (ci3_config_item($key . '.display_qrcode', 'pendaftaran/rekening_pembayaran') ?? false);

            $qrcodeUrl = (string) (get_setting($key . '_qrcode', 'pendaftaran/rekening_pembayaran') ?? $this->ci3QrUrl($key) ?? '');

            $accounts[] = [
                'key' => $key,
                'bank_name' => $bankName,
                'bank_account_name' => $accountName,
                'bank_account_number' => $accountNumber,
                'active' => $active,
                'display_qrcode' => $displayQr,
                'qrcode' => $qrcodeUrl,
            ];
        }

        return $accounts;
    }

    private function ci3QrUrl(string $accountKey): ?string
    {
        $fileName = ci3_config_item($accountKey . '.file_name', 'pendaftaran/rekening_pembayaran');
        $uploadPath = ci3_config_item($accountKey . '.upload_path', 'pendaftaran/rekening_pembayaran');
        if (! is_string($fileName) || trim($fileName) === '') {
            return null;
        }
        if (! is_string($uploadPath) || trim($uploadPath) === '') {
            return null;
        }

        $uploadPath = str_replace('\\', '/', $uploadPath);
        $uploadPath = preg_replace('#^\./#', '', $uploadPath);
        $uploadPath = trim((string) $uploadPath, '/');
        return base_url($uploadPath . '/' . ltrim($fileName, '/'));
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        $rules = [];
        for ($i = 1; $i <= 5; $i++) {
            $key = 'account_' . $i;
            $rules[$key . '_bank_name'] = 'permit_empty|max_length[50]';
            $rules[$key . '_bank_account_name'] = 'permit_empty|max_length[100]';
            $rules[$key . '_bank_account_number'] = 'permit_empty|max_length[50]';
            $rules[$key . '_active'] = 'permit_empty|in_list[0,1,on]';
            $rules[$key . '_display_qrcode'] = 'permit_empty|in_list[0,1,on]';
        }
        return $rules;
    }

    /**
     * @param array<int, array<string, mixed>> $accounts
     */
    public function saveAccounts(array $accounts): void
    {
        foreach ($accounts as $acc) {
            $key = (string) ($acc['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $this->writer->setString($key . '_bank_name', (string) ($acc['bank_name'] ?? ''));
            $this->writer->setString($key . '_bank_account_name', (string) ($acc['bank_account_name'] ?? ''));
            $this->writer->setString($key . '_bank_account_number', (string) ($acc['bank_account_number'] ?? ''));
            $this->writer->setBool($key . '_active', (bool) ($acc['active'] ?? false));
            $this->writer->setBool($key . '_display_qrcode', (bool) ($acc['display_qrcode'] ?? false));
        }
    }

    public function storeQr(string $accountKey, UploadedFile $file): string
    {
        return $this->fileSetting->storePublicFile(
            $accountKey . '_qrcode',
            $file,
            'qrcode-pembayaran',
            ['image/png', 'image/jpeg'],
            50000
        );
    }
}
