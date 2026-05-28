<?php

namespace Tests\Unit\Services;

use App\Services\Admin\Super\RekeningPembayaranService;
use App\Services\PembayaranKontingenService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class RekeningPembayaranServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();
        helper('ci3_compat');

        $db = db_connect();
        $forge = \Config\Database::forge();

        if (! $db->tableExists('site_builder_settings')) {
            $forge->addField([
                'id_site_builder_settings' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'setting' => ['type' => 'VARCHAR', 'constraint' => 255],
                'value' => ['type' => 'TEXT'],
                'is_array' => ['type' => 'INT', 'constraint' => 1],
            ]);
            $forge->addKey('id_site_builder_settings', true);
            $forge->createTable('site_builder_settings', true);
        }

        $db->table('site_builder_settings')->where('setting', 'rekening_pembayaran_accounts')->delete();
    }

    public function testCurrentAccountsDefaultsToEmptyArray(): void
    {
        $this->assertSame([], (new RekeningPembayaranService())->currentAccounts());
    }

    public function testSaveAccountsStoresJsonArray(): void
    {
        $service = new RekeningPembayaranService();
        $service->saveAccounts([
            [
                'bank_name' => 'BCA',
                'bank_account_name' => 'DPS Event',
                'bank_account_number' => '1234567890',
                'active' => true,
            ],
        ]);

        $row = db_connect()->table('site_builder_settings')->where('setting', 'rekening_pembayaran_accounts')->get()->getRowArray();

        $this->assertNotNull($row);
        $this->assertSame('1', (string) $row['is_array']);
        $this->assertSame('BCA', json_decode((string) $row['value'], true)[0]['bank_name']);
    }

    public function testCurrentAccountsReturnsSavedAccounts(): void
    {
        $service = new RekeningPembayaranService();
        $service->saveAccounts([
            [
                'bank_name' => 'BRI',
                'bank_account_name' => 'Panitia',
                'bank_account_number' => '987654321',
                'active' => false,
            ],
        ]);

        $accounts = $service->currentAccounts();

        $this->assertCount(1, $accounts);
        $this->assertSame('BRI', $accounts[0]['bank_name']);
        $this->assertFalse($accounts[0]['active']);
    }

    public function testEmptyRowsAreFilteredOnSave(): void
    {
        $service = new RekeningPembayaranService();
        $service->saveAccounts([
            ['bank_name' => '', 'bank_account_name' => '', 'bank_account_number' => '', 'active' => true],
        ]);

        $this->assertSame([], $service->currentAccounts());
    }

    public function testPaymentServiceReturnsOnlyActiveAccounts(): void
    {
        (new RekeningPembayaranService())->saveAccounts([
            ['bank_name' => 'BCA', 'bank_account_name' => 'Aktif', 'bank_account_number' => '111', 'active' => true],
            ['bank_name' => 'BRI', 'bank_account_name' => 'Nonaktif', 'bank_account_number' => '222', 'active' => false],
        ]);

        $accounts = (new PembayaranKontingenService())->accounts();

        $this->assertCount(1, $accounts);
        $this->assertSame('BCA', $accounts[0]['bank_name']);
        $this->assertArrayNotHasKey('display_qrcode', $accounts[0]);
        $this->assertArrayNotHasKey('file_name', $accounts[0]);
    }
}
