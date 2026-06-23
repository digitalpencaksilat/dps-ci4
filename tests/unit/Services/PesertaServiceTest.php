<?php

namespace Tests\Unit\Services;

use App\Services\PesertaService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class PesertaServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $forge = \Config\Database::forge();

        $forge->dropTable('kontingen', true);
        $forge->dropTable('pendaftar', true);
        if (! $db->tableExists('kontingen')) {
            $forge->addField([
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'nama_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255],
                'email_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'jenis_kontingen' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'dalam_negeri'],
            ]);
            $forge->addKey('id_kontingen', true);
            $forge->createTable('kontingen', true);
        }

        if (! $db->tableExists('pendaftar')) {
            $forge->addField([
                'id_pendaftar' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11],
                'nama_pendaftar' => ['type' => 'VARCHAR', 'constraint' => 255],
                'jenis_kelamin' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'putra'],
                'tinggi_badan' => ['type' => 'FLOAT', 'null' => true],
                'berat_badan' => ['type' => 'FLOAT', 'null' => true],
                'tempat_lahir' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'tanggal_lahir' => ['type' => 'DATE', 'null' => true],
                'nama_sekolah' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'alamat' => ['type' => 'TEXT', 'null' => true],
                'nomor_induk_kependudukan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'nomor_kartu_keluarga' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'status_data' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'belum_final'],
                'keterangan' => ['type' => 'TEXT', 'null' => true],
            ]);
            $forge->addKey('id_pendaftar', true);
            $forge->createTable('pendaftar', true);
        }

        $db->table('kontingen')->truncate();
        $db->table('pendaftar')->truncate();

        $db->table('kontingen')->insert([
            'nama_kontingen' => 'Test Kontingen',
            'email_kontingen' => 't@t.com',
            'password' => 'x',
            'jenis_kontingen' => 'dalam_negeri',
        ]);
        $this->idKontingen = (int) $db->insertID();
    }

    private int $idKontingen;

    public function testCreateReturnsNewPendaftarId(): void
    {
        $id = (new PesertaService())->create($this->idKontingen, [
            'nama_pendaftar' => 'Atlet Baru',
            'jenis_kelamin' => 'putra',
            'berat_badan' => 65.5,
            'tinggi_badan' => 170,
        ]);

        $this->assertGreaterThan(0, $id);
    }

    public function testUpdateModifiesPendaftarData(): void
    {
        $db = db_connect();
        $db->table('pendaftar')->insert([
            'id_kontingen' => $this->idKontingen,
            'nama_pendaftar' => 'Nama Lama',
            'berat_badan' => 60,
            'jenis_kelamin' => 'putra',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
        ]);
        $id = (int) $db->insertID();

        $pendaftar = (object) ['id_pendaftar' => $id, 'jenis_kelamin' => 'putra', 'tinggi_badan' => 0, 'berat_badan' => 60, 'tanggal_lahir' => '2000-01-01'];
        (new PesertaService())->update($pendaftar, ['nama_pendaftar' => 'Nama Baru', 'berat_badan' => 62.5]);

        $updated = $db->table('pendaftar')->where('id_pendaftar', $id)->get()->getRow();
        $this->assertSame('Nama Baru', $updated->nama_pendaftar);
    }

    public function testDeleteRemovesPendaftar(): void
    {
        $db = db_connect();
        $db->table('pendaftar')->insert([
            'id_kontingen' => $this->idKontingen,
            'nama_pendaftar' => 'Atlet Dihapus',
            'jenis_kelamin' => 'putra',
            'tempat_lahir' => 'Test',
            'tanggal_lahir' => '2000-01-01',
        ]);
        $id = (int) $db->insertID();

        (new PesertaService())->delete((object) ['id_pendaftar' => $id]);
        $this->assertNull($db->table('pendaftar')->where('id_pendaftar', $id)->get()->getRow());
    }
}
