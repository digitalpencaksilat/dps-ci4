<?php

namespace Tests\Unit\Services;

use App\Services\KontingenAuthService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class KontingenAuthServiceTest extends CIUnitTestCase
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
                'email_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255],
                'password' => ['type' => 'VARCHAR', 'constraint' => 255],
                'perguruan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'status_data' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'jenis_kontingen' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'dalam_negeri'],
            ]);
            $forge->addKey('id_kontingen', true);
            $forge->createTable('kontingen', true);
        }

        $db->table('kontingen')->truncate();
    }

    public function testAttemptReturnsFalseForNonexistentEmail(): void
    {
        $service = new KontingenAuthService();
        $this->assertFalse($service->attempt('no@email.com', 'pass'));
    }

    public function testAttemptReturnsFalseForWrongPassword(): void
    {
        db_connect()->table('kontingen')->insert([
            'nama_kontingen' => 'Test',
            'email_kontingen' => 'test@test.com',
            'password' => password_hash('correct', PASSWORD_DEFAULT),
            'jenis_kontingen' => 'dalam_negeri',
        ]);

        $this->assertFalse((new KontingenAuthService())->attempt('test@test.com', 'wrong'));
    }

    public function testAttemptSetsSessionOnSuccess(): void
    {
        db_connect()->table('kontingen')->insert([
            'nama_kontingen' => 'Test Kontingen',
            'email_kontingen' => 'test@test.com',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'perguruan' => 'PSHT',
            'status_data' => 'final',
            'jenis_kontingen' => 'dalam_negeri',
        ]);

        $result = (new KontingenAuthService())->attempt('test@test.com', 'secret');
        $this->assertTrue($result);
        $this->assertSame('kontingen', session()->get('level'));
    }

    public function testLogoutCanBeCalled(): void
    {
        (new KontingenAuthService())->logout();
        $this->assertTrue(true);
    }
}
