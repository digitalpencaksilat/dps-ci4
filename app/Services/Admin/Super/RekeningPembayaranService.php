<?php

namespace App\Services\Admin\Super;

class RekeningPembayaranService
{
    private const SETTING_KEY = 'rekening_pembayaran_accounts';

    private SettingWriterService $writer;

    public function __construct()
    {
        $this->writer = new SettingWriterService();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function currentAccounts(int $max = 5): array
    {
        $raw = get_setting(self::SETTING_KEY);
        if (! is_array($raw)) {
            return [];
        }

        $accounts = [];
        foreach ($raw as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $accounts[] = [
                'key' => (string) ($item['key'] ?? ('account_' . ($index + 1))),
                'bank_name' => trim((string) ($item['bank_name'] ?? '')),
                'bank_account_name' => trim((string) ($item['bank_account_name'] ?? '')),
                'bank_account_number' => trim((string) ($item['bank_account_number'] ?? '')),
                'active' => ! empty($item['active']),
            ];
        }

        return $accounts;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @param array<int, array<string, mixed>> $accounts
     */
    public function saveAccounts(array $accounts): void
    {
        $clean = [];
        $counter = 1;

        foreach ($accounts as $acc) {
            $bankName = trim((string) ($acc['bank_name'] ?? ''));
            $accountName = trim((string) ($acc['bank_account_name'] ?? ''));
            $accountNumber = trim((string) ($acc['bank_account_number'] ?? ''));

            if ($bankName === '' && $accountName === '' && $accountNumber === '') {
                continue;
            }

            $clean[] = [
                'key' => 'account_' . $counter++,
                'bank_name' => $bankName,
                'bank_account_name' => $accountName,
                'bank_account_number' => $accountNumber,
                'active' => (bool) ($acc['active'] ?? false),
            ];
        }

        $this->writer->setArray(self::SETTING_KEY, $clean);
    }
}
