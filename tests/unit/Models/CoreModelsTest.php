<?php

namespace Tests\Unit\Models;

use App\Models\AdminModel;
use App\Models\GelanggangModel;
use App\Models\KontingenModel;
use App\Models\PembayaranModel;
use App\Models\PendaftarModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * Comprehensive model tests — verifies CRUD operations for all core models.
 * Uses DatabaseTestTrait with SQLite in-memory for fast execution.
 */
class CoreModelsTest extends CIUnitTestCase
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
            'keterangan' => ['type' => 'TEXT', 'null' => true],
        ]);
        $forge->addKey('id_admin', true);
        $forge->createTable('admin', true);

        // Kontingen (simplified)
        $forge->dropTable('kontingen', true);
        $forge->addField([
            'id_kontingen' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255],
            'email_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'jenis_kontingen' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'dalam_negeri'],
            'provinsi' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'tanggal_daftar' => ['type' => 'DATETIME', 'null' => true],
            'status_data' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'aktif'],
            'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
        ]);
        $forge->addKey('id_kontingen', true);
        $forge->createTable('kontingen', true);

        // Pembayaran
        $forge->dropTable('pembayaran', true);
        $forge->addField([
            'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'status_pembayaran' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'belum_dibayar'],
            'tanggal_pembayaran' => ['type' => 'DATETIME', 'null' => true],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
        ]);
        $forge->addKey('id_pembayaran', true);
        $forge->createTable('pembayaran', true);

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
            'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status_data' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'belum_final'],
            'nomor_induk_kependudukan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'nomor_kartu_keluarga' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
        $forge->addKey('id_pendaftar', true);
        $forge->createTable('pendaftar', true);

        // Gelanggang
        $forge->dropTable('gelanggang', true);
        $forge->addField([
            'id_gelanggang' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama_gelanggang' => ['type' => 'VARCHAR', 'constraint' => 255],
            'nomor_gelanggang' => ['type' => 'INT', 'constraint' => 11],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
        ]);
        $forge->addKey('id_gelanggang', true);
        $forge->createTable('gelanggang', true);

        // Seed kontingen
        (new KontingenModel())->insert([
            'nama_kontingen' => 'Test',
            'email_kontingen' => 'test@test.com',
            'password' => 'x',
            'jenis_kontingen' => 'dalam_negeri',
        ]);
        $this->kontingenId = (int) db_connect()->insertID();
    }

    private int $kontingenId;

    // ─── Admin ───

    public function testAdminInsertAndFind(): void
    {
        $id = (new AdminModel())->insert([
            'username' => 'superadmin',
            'password' => password_hash('secret', PASSWORD_DEFAULT),
            'nama' => 'Super Admin',
            'level' => 'super_admin',
        ]);
        $this->assertGreaterThan(0, $id);

        $row = (new AdminModel())->find($id);
        $this->assertSame('superadmin', $row->username);
        $this->assertSame('super_admin', $row->level);
        $this->assertTrue(password_verify('secret', $row->password));
    }

    public function testAdminUpdateAndDelete(): void
    {
        $model = new AdminModel();
        $id = $model->insert(['username' => 'temp', 'password' => 'x', 'nama' => 'T', 'level' => 'printer']);
        $model->update($id, ['nama' => 'Updated']);

        $this->assertSame('Updated', $model->find($id)->nama);

        $model->delete($id);
        $this->assertNull($model->find($id));
    }

    // ─── Kontingen ───

    public function testKontingenFind(): void
    {
        $row = (new KontingenModel())->find($this->kontingenId);
        $this->assertNotNull($row);
        $this->assertSame('Test', $row->nama_kontingen);
    }

    public function testKontingenUpdate(): void
    {
        $model = new KontingenModel();
        $model->update($this->kontingenId, ['nama_kontingen' => 'Updated Kontingen']);
        $this->assertSame('Updated Kontingen', $model->find($this->kontingenId)->nama_kontingen);
    }

    public function testKontingenFindNonexistent(): void
    {
        $this->assertNull((new KontingenModel())->find(99999));
    }

    // ─── Pembayaran ───

    public function testPembayaranCrud(): void
    {
        $model = new PembayaranModel();
        $id = $model->insert(['status_pembayaran' => 'menunggu_konfirmasi']);

        $this->assertGreaterThan(0, $id);
        $this->assertSame('menunggu_konfirmasi', $model->find($id)->status_pembayaran);

        $model->update($id, ['status_pembayaran' => 'lunas']);
        $this->assertSame('lunas', $model->find($id)->status_pembayaran);
    }

    public function testPembayaranCount(): void
    {
        $model = new PembayaranModel();
        $model->insert(['status_pembayaran' => 'lunas']);
        $model->insert(['status_pembayaran' => 'lunas']);
        $model->insert(['status_pembayaran' => 'belum_dibayar']);

        $this->assertSame(2, $model->where('status_pembayaran', 'lunas')->countAllResults());
    }

    // ─── Pendaftar ───

    public function testPendaftarInsert(): void
    {
        $id = (new PendaftarModel())->insert([
            'id_kontingen' => $this->kontingenId,
            'nama_pendaftar' => 'Atlet Baru',
            'jenis_kelamin' => 'putra',
            'berat_badan' => 65.5,
        ]);
        $this->assertGreaterThan(0, $id);

        $row = (new PendaftarModel())->find($id);
        $this->assertSame('Atlet Baru', $row->nama_pendaftar);
        $this->assertSame('putra', $row->jenis_kelamin);
    }

    public function testPendaftarUpdateDelete(): void
    {
        $model = new PendaftarModel();
        $id = $model->insert([
            'id_kontingen' => $this->kontingenId,
            'nama_pendaftar' => 'Old',
            'jenis_kelamin' => 'putri',
        ]);

        $model->update($id, ['nama_pendaftar' => 'New']);
        $this->assertSame('New', $model->find($id)->nama_pendaftar);

        $model->delete($id);
        $this->assertNull($model->find($id));
    }

    // ─── Gelanggang ───

    public function testGelanggangCrud(): void
    {
        $id = (new GelanggangModel())->insert([
            'nama_gelanggang' => 'Arena 1',
            'nomor_gelanggang' => 1,
        ]);
        $this->assertGreaterThan(0, $id);

        $row = (new GelanggangModel())->find($id);
        $this->assertSame('Arena 1', $row->nama_gelanggang);

        (new GelanggangModel())->update($id, ['nama_gelanggang' => 'Arena Updated']);
        $this->assertSame('Arena Updated', (new GelanggangModel())->find($id)->nama_gelanggang);

        (new GelanggangModel())->delete($id);
        $this->assertNull((new GelanggangModel())->find($id));
    }
}
