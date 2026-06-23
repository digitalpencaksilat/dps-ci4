<?php

namespace Tests\Unit\Services;

use App\Services\AdminAuthService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class AdminAuthServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $forge = \Config\Database::forge();

        if (! $db->tableExists('admin')) {
            $forge->addField([
                'id_admin' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'username' => ['type' => 'VARCHAR', 'constraint' => 100],
                'password' => ['type' => 'VARCHAR', 'constraint' => 255],
                'nama' => ['type' => 'VARCHAR', 'constraint' => 150],
                'level' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'sekretariat'],
                'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            ]);
            $forge->addKey('id_admin', true);
            $forge->createTable('admin', true);
        }

        $db->table('admin')->truncate();
    }

    public function testAttemptReturnsNullForNonexistentUser(): void
    {
        $service = new AdminAuthService();
        $result = $service->attempt('nonexistent', 'password123');
        $this->assertNull($result);
    }

    public function testAttemptReturnsNullForWrongPassword(): void
    {
        $db = db_connect();
        $db->table('admin')->insert([
            'username' => 'superadmin',
            'password' => password_hash('correct_password', PASSWORD_DEFAULT),
            'nama' => 'Super Admin',
            'level' => 'super_admin',
        ]);

        $service = new AdminAuthService();
        $result = $service->attempt('superadmin', 'wrong_password');
        $this->assertNull($result);
    }

    public function testAttemptReturnsLevelOnSuccess(): void
    {
        $db = db_connect();
        $db->table('admin')->insert([
            'username' => 'sekretaris',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
            'nama' => 'Sekretaris',
            'level' => 'sekretariat',
        ]);

        $service = new AdminAuthService();
        $result = $service->attempt('sekretaris', 'secret123');
        $this->assertSame('sekretariat', $result);
        $this->assertSame('sekretariat', session()->get('level'));
        $this->assertSame('sekretaris', session()->get('username'));
    }

    public function testAttemptNormalizesUsernameCaseInsensitive(): void
    {
        $db = db_connect();
        $db->table('admin')->insert([
            'username' => 'bendahara',
            'password' => password_hash('secret123', PASSWORD_DEFAULT),
            'nama' => 'Bendahara',
            'level' => 'bendahara',
        ]);

        $service = new AdminAuthService();
        $result = $service->attempt('BENDAHARA', 'secret123');
        $this->assertSame('bendahara', $result);
    }

    public function testDashboardUrlForMapsAllRoles(): void
    {
        $service = new AdminAuthService();

        $this->assertStringContainsString('admin/bendahara/dashboard', $service->dashboardUrlFor('bendahara'));
        $this->assertStringContainsString('admin/sekretariat/dashboard', $service->dashboardUrlFor('sekretariat'));
        $this->assertStringContainsString('admin/printer/dashboard', $service->dashboardUrlFor('printer'));
        $this->assertStringContainsString('admin/super/dashboard', $service->dashboardUrlFor('super_admin'));
        $this->assertStringContainsString('admin', $service->dashboardUrlFor('unknown'));
    }

    public function testLogoutCanBeCalledWithoutError(): void
    {
        session()->set('level', 'super_admin');
        session()->set('id_admin', 1);

        (new AdminAuthService())->logout();

        $this->assertTrue(true);
    }
}
