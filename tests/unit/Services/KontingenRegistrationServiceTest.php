<?php

namespace Tests\Unit\Services;

use App\Services\KontingenRegistrationService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class KontingenRegistrationServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $forge = \Config\Database::forge();

        $forge->dropTable('kontingen', true);
        if (! $db->tableExists('kontingen')) {
            $forge->addField([
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'nama_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255],
                'singkatan_nama_kontingen' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'jenis_kontingen' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'dalam_negeri'],
                'perguruan' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'ipsi'],
                'email_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255],
                'nomor_telepon_kontingen' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'alamat_kontingen' => ['type' => 'TEXT', 'null' => true],
                'username' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'password' => ['type' => 'VARCHAR', 'constraint' => 255],
                'nama_penanggungjawab' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'jabatan_penanggungjawab' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'nomor_telepon_penanggungjawab' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'alamat_penanggungjawab' => ['type' => 'TEXT', 'null' => true],
                'negara' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'provinsi' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'kabupaten_kota' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'kecamatan' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'kelurahan' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'alamat_lengkap' => ['type' => 'TEXT', 'null' => true],
                'keterangan' => ['type' => 'TEXT', 'null' => true],
                'pembayaran_dn' => ['type' => 'FLOAT', 'null' => true],
                'pembayaran_ln' => ['type' => 'FLOAT', 'null' => true],
                'status_data' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'belum_final'],
                'jenis_pendaftaran' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'web'],
            ]);
            $forge->addKey('id_kontingen', true);
            $forge->createTable('kontingen', true);
        }

        $db->table('kontingen')->truncate();
    }

    public function testCreateRegistersKontingen(): void
    {
        $service = new KontingenRegistrationService();
        $result = $service->create([
            'nama_kontingen' => 'Perguruan Silat Nusantara',
            'email_kontingen' => 'psn@example.com',
            'password' => 'secret123',
            'nama_penanggungjawab' => 'Budi Santoso',
            'nomor_telepon_penanggungjawab' => '081234567890',
            'alamat_lengkap' => 'Jl. Merdeka No. 1',
            'provinsi' => 'JAWA BARAT',
            'kabupaten_kota' => 'KOTA BANDUNG',
        ]);

        $this->assertTrue($result);

        $kontingen = db_connect()->table('kontingen')->where('email_kontingen', 'psn@example.com')->get()->getRow();
        $this->assertNotNull($kontingen);
        $this->assertSame('Perguruan Silat Nusantara', $kontingen->nama_kontingen);
        $this->assertTrue(password_verify('secret123', $kontingen->password));
    }

    public function testCreateThrowsExceptionWhenEmailExists(): void
    {
        $db = db_connect();
        $db->table('kontingen')->insert([
            'nama_kontingen' => 'Existing',
            'email_kontingen' => 'exists@example.com',
            'password' => password_hash('test', PASSWORD_DEFAULT),
            'jenis_kontingen' => 'dalam_negeri',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Email kontingen sudah terdaftar');

        (new KontingenRegistrationService())->create([
            'nama_kontingen' => 'New Kontingen',
            'email_kontingen' => 'exists@example.com',
            'password' => 'secret123',
        ]);
    }

    public function testCreateNormalizesPhoneNumber(): void
    {
        (new KontingenRegistrationService())->create([
            'nama_kontingen' => 'Test',
            'email_kontingen' => 'phone@example.com',
            'password' => 'secret123',
            'nomor_telepon_penanggungjawab' => '+62 812-3456-7890',
            'nama_penanggungjawab' => 'Test PJ',
            'alamat_lengkap' => 'Test',
        ]);

        $kontingen = db_connect()->table('kontingen')->where('email_kontingen', 'phone@example.com')->get()->getRow();
        $this->assertSame('081234567890', $kontingen->nomor_telepon_penanggungjawab);
    }
}
