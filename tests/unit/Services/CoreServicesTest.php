<?php

namespace Tests\Unit\Services;

use App\Services\KategoriTandingService;
use App\Services\KategoriSeniService;
use App\Services\PesertaService;
use App\Services\AdminAuthService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class CoreServicesTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();
        $forge = \Config\Database::forge();

        // Admin
        $forge->dropTable('admin', true);
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

        // Kontingen
        $forge->dropTable('kontingen', true);
        $forge->addField([
            'id_kontingen' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255],
            'email_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'jenis_kontingen' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'dalam_negeri'],
        ]);
        $forge->addKey('id_kontingen', true);
        $forge->createTable('kontingen', true);

        // Pendaftar
        $forge->dropTable('pendaftar', true);
        $forge->addField([
            'id_pendaftar' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_kontingen' => ['type' => 'INT', 'constraint' => 11],
            'nama_pendaftar' => ['type' => 'VARCHAR', 'constraint' => 255],
            'jenis_kelamin' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'putra'],
            'berat_badan' => ['type' => 'FLOAT', 'null' => true],
            'tinggi_badan' => ['type' => 'FLOAT', 'null' => true],
            'tempat_lahir' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'tanggal_lahir' => ['type' => 'DATE', 'null' => true],
            'nama_sekolah' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'alamat' => ['type' => 'TEXT', 'null' => true],
            'nomor_induk_kependudukan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'nomor_kartu_keluarga' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status_data' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'belum_final'],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
        ]);
        $forge->addKey('id_pendaftar', true);
        $forge->createTable('pendaftar', true);

        // Pembayaran
        $forge->dropTable('pembayaran', true);
        $forge->addField([
            'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'status_pembayaran' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'belum_dibayar'],
        ]);
        $forge->addKey('id_pembayaran', true);
        $forge->createTable('pembayaran', true);

        // Kategori_usia
        $forge->dropTable('kategori_usia', true);
        $forge->addField([
            'id_kategori_usia' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_kategori_usia' => ['type' => 'VARCHAR', 'constraint' => 255],
            'min_umur' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'max_umur' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'jenis_kelamin' => ['type' => 'VARCHAR', 'constraint' => 10],
        ]);
        $forge->addKey('id_kategori_usia', true);
        $forge->createTable('kategori_usia', true);

        // Kategori_lomba
        $forge->dropTable('kategori_lomba', true);
        $forge->addField([
            'id_kategori_lomba' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_kategori_usia' => ['type' => 'INT', 'constraint' => 11],
            'nama_kategori_lomba' => ['type' => 'VARCHAR', 'constraint' => 255],
            'jenis_perlombaan' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);
        $forge->addKey('id_kategori_lomba', true);
        $forge->createTable('kategori_lomba', true);

        // Kelas_tanding
        $forge->dropTable('kelas_tanding', true);
        $forge->addField([
            'id_kelas_tanding' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_kategori_lomba' => ['type' => 'INT', 'constraint' => 11],
            'label' => ['type' => 'VARCHAR', 'constraint' => 255],
            'berat_minimal' => ['type' => 'FLOAT', 'null' => true],
            'berat_maksimal' => ['type' => 'FLOAT', 'null' => true],
            'biaya_pendaftaran_dn' => ['type' => 'DECIMAL', 'constraint' => '15,2', 'null' => true],
        ]);
        $forge->addKey('id_kelas_tanding', true);
        $forge->createTable('kelas_tanding', true);

        // Kompetisi_tanding
        $forge->dropTable('kompetisi_tanding', true);
        $forge->addField([
            'id_kompetisi_tanding' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_kelas_tanding' => ['type' => 'INT', 'constraint' => 11],
            'max_peserta' => ['type' => 'INT', 'constraint' => 11],
            'nomor_pool' => ['type' => 'INT', 'constraint' => 11],
        ]);
        $forge->addKey('id_kompetisi_tanding', true);
        $forge->createTable('kompetisi_tanding', true);

        // Peserta_tanding
        $forge->dropTable('peserta_tanding', true);
        $forge->addField([
            'id_peserta_tanding' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_pendaftar' => ['type' => 'INT', 'constraint' => 11],
            'id_kompetisi_tanding' => ['type' => 'INT', 'constraint' => 11],
            'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
        ]);
        $forge->addKey('id_peserta_tanding', true);
        $forge->createTable('peserta_tanding', true);

        // Sub_kategori_seni
        $forge->dropTable('sub_kategori_seni', true);
        $forge->addField([
            'id_sub_kategori_seni' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_kategori_lomba' => ['type' => 'INT', 'constraint' => 11],
            'nama_seni' => ['type' => 'VARCHAR', 'constraint' => 255],
            'jenis_seni' => ['type' => 'VARCHAR', 'constraint' => 50],
            'jumlah_peserta' => ['type' => 'INT', 'constraint' => 11],
            'sistem_penampilan' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);
        $forge->addKey('id_sub_kategori_seni', true);
        $forge->createTable('sub_kategori_seni', true);

        // Kompetisi_seni
        $forge->dropTable('kompetisi_seni', true);
        $forge->addField([
            'id_kompetisi_seni' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_sub_kategori_seni' => ['type' => 'INT', 'constraint' => 11],
            'max_peserta' => ['type' => 'INT', 'constraint' => 11],
            'nomor_pool' => ['type' => 'INT', 'constraint' => 11],
        ]);
        $forge->addKey('id_kompetisi_seni', true);
        $forge->createTable('kompetisi_seni', true);

        // Kelompok_peserta_seni
        $forge->dropTable('kelompok_peserta_seni', true);
        $forge->addField([
            'id_kelompok_peserta_seni' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_kontingen' => ['type' => 'INT', 'constraint' => 11],
            'id_kompetisi_seni' => ['type' => 'INT', 'constraint' => 11],
            'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'nomor_undi' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $forge->addKey('id_kelompok_peserta_seni', true);
        $forge->createTable('kelompok_peserta_seni', true);

        // Peserta_seni
        $forge->dropTable('peserta_seni', true);
        $forge->addField([
            'id_peserta_seni' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'id_pendaftar' => ['type' => 'INT', 'constraint' => 11],
            'id_kelompok_peserta_seni' => ['type' => 'INT', 'constraint' => 11],
        ]);
        $forge->addKey('id_peserta_seni', true);
        $forge->createTable('peserta_seni', true);

        // Seed data
        $db = db_connect();
        $db->table('kontingen')->insert(['nama_kontingen' => 'Test Kontingen', 'jenis_kontingen' => 'dalam_negeri']);
        $this->kontingenId = (int) $db->insertID();

        session()->set('id_kontingen', $this->kontingenId);
    }

    private int $kontingenId;

    // ─── PesertaService ───

    public function testPesertaCreate(): void
    {
        $id = (new PesertaService())->create($this->kontingenId, [
            'nama_pendaftar' => 'Atlet Test',
            'jenis_kelamin' => 'putra',
            'berat_badan' => 65.5,
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
        ]);
        $this->assertGreaterThan(0, $id);
    }

    public function testPesertaUpdate(): void
    {
        $db = db_connect();
        $db->table('pendaftar')->insert([
            'id_kontingen' => $this->kontingenId,
            'nama_pendaftar' => 'Old Name',
            'jenis_kelamin' => 'putra',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
        ]);
        $id = (int) $db->insertID();

        $peserta = (object) ['id_pendaftar' => $id, 'jenis_kelamin' => 'putra', 'berat_badan' => 60, 'tinggi_badan' => 170, 'tanggal_lahir' => '2000-01-01'];
        (new PesertaService())->update($peserta, ['nama_pendaftar' => 'New Name']);

        $row = $db->table('pendaftar')->where('id_pendaftar', $id)->get()->getRow();
        $this->assertSame('New Name', $row->nama_pendaftar);
    }

    public function testPesertaDelete(): void
    {
        $db = db_connect();
        $db->table('pendaftar')->insert([
            'id_kontingen' => $this->kontingenId,
            'nama_pendaftar' => 'Delete Me',
            'jenis_kelamin' => 'putra',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
        ]);
        $id = (int) $db->insertID();

        (new PesertaService())->delete((object) ['id_pendaftar' => $id]);
        $this->assertNull($db->table('pendaftar')->where('id_pendaftar', $id)->get()->getRow());
    }

    // ─── AdminAuthService ───

    public function testAdminAuthSuccess(): void
    {
        db_connect()->table('admin')->insert([
            'username' => 'superadmin',
            'password' => password_hash('correct123', PASSWORD_DEFAULT),
            'nama' => 'Super Admin',
            'level' => 'super_admin',
        ]);

        $result = (new AdminAuthService())->attempt('superadmin', 'correct123');
        $this->assertSame('super_admin', $result);
    }

    public function testAdminAuthWrongPassword(): void
    {
        db_connect()->table('admin')->insert([
            'username' => 'sekretaris',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'nama' => 'Sekretaris',
            'level' => 'sekretariat',
        ]);

        $result = (new AdminAuthService())->attempt('sekretaris', 'wrong');
        $this->assertNull($result);
    }

    public function testAdminAuthNonexistent(): void
    {
        $result = (new AdminAuthService())->attempt('nonexistent', 'password');
        $this->assertNull($result);
    }

    public function testAdminDashboardUrls(): void
    {
        $service = new AdminAuthService();
        $this->assertStringContainsString('admin/bendahara/dashboard', $service->dashboardUrlFor('bendahara'));
        $this->assertStringContainsString('admin/sekretariat/dashboard', $service->dashboardUrlFor('sekretariat'));
        $this->assertStringContainsString('admin/printer/dashboard', $service->dashboardUrlFor('printer'));
        $this->assertStringContainsString('admin/super/dashboard', $service->dashboardUrlFor('super_admin'));
    }

    // ─── KategoriTandingService ───

    // Note: listByKontingen() uses MySQL-specific subquery syntax (correlated subqueries
    // referencing pembayaran table) that cannot run on SQLite in-memory test DB.
    // The method is tested functionally against MySQL in production.

    public function testTandingCreateThrowsForNonexistent(): void
    {
        $this->expectException(\RuntimeException::class);
        (new KategoriTandingService())->create($this->kontingenId, 99999, 1);
    }

    // ─── KategoriSeniService ───

    // Note: listByKontingen() uses MySQL GROUP_CONCAT(... SEPARATOR ...) syntax
    // that is not supported by SQLite in-memory test DB. The method is tested
    // functionally against MySQL in production.

    public function testSeniDeleteRemovesBoth(): void
    {
        $db = db_connect();
        $db->table('kelompok_peserta_seni')->insert([
            'id_kontingen' => $this->kontingenId, 'id_kompetisi_seni' => 1,
        ]);
        $db->table('peserta_seni')->insert([
            'id_pendaftar' => 1, 'id_kelompok_peserta_seni' => 1,
        ]);

        (new KategoriSeniService())->delete((object) ['id_kelompok_peserta_seni' => 1]);

        $this->assertNull($db->table('kelompok_peserta_seni')->where('id_kelompok_peserta_seni', 1)->get()->getRow());
        $this->assertNull($db->table('peserta_seni')->where('id_kelompok_peserta_seni', 1)->get()->getRow());
    }
}
