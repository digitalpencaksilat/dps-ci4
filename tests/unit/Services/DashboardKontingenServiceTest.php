<?php

namespace Tests\Unit\Services;

use App\Services\DashboardKontingenService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class DashboardKontingenServiceTest extends CIUnitTestCase
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
        $forge->dropTable('peserta_tanding', true);
        $forge->dropTable('kelompok_peserta_seni', true);
        $forge->dropTable('pembayaran', true);
        $forge->dropTable('kompetisi_tanding', true);
        $forge->dropTable('kompetisi_seni', true);
        if (! $db->tableExists('kontingen')) {
            $forge->addField([
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'nama_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255],
                'email_kontingen' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            ]);
            $forge->addKey('id_kontingen', true);
            $forge->createTable('kontingen', true);
        }

        if (! $db->tableExists('pendaftar')) {
            $forge->addField([
                'id_pendaftar' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11],
                'nama_pendaftar' => ['type' => 'VARCHAR', 'constraint' => 255],
                'jenis_kelamin' => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
                'tempat_lahir' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'tanggal_lahir' => ['type' => 'DATE', 'null' => true],
            ]);
            $forge->addKey('id_pendaftar', true);
            $forge->createTable('pendaftar', true);
        }

        if (! $db->tableExists('kompetisi_tanding')) {
            $forge->addField([
                'id_kompetisi_tanding' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kelas_tanding' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'max_peserta' => ['type' => 'INT', 'constraint' => 11, 'default' => 4],
                'nomor_pool' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
                'bagan_pertandingan' => ['type' => 'TEXT', 'null' => true],
            ]);
            $forge->addKey('id_kompetisi_tanding', true);
            $forge->createTable('kompetisi_tanding', true);
        }

        if (! $db->tableExists('kompetisi_seni')) {
            $forge->addField([
                'id_kompetisi_seni' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_sub_kategori_seni' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'max_peserta' => ['type' => 'INT', 'constraint' => 11, 'default' => 4],
                'nomor_pool' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
            ]);
            $forge->addKey('id_kompetisi_seni', true);
            $forge->createTable('kompetisi_seni', true);
        }

        if (! $db->tableExists('peserta_tanding')) {
            $forge->addField([
                'id_peserta_tanding' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_pendaftar' => ['type' => 'INT', 'constraint' => 11],
                'id_kompetisi_tanding' => ['type' => 'INT', 'constraint' => 11],
                'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            ]);
            $forge->addKey('id_peserta_tanding', true);
            $forge->createTable('peserta_tanding', true);
        }

        if (! $db->tableExists('kelompok_peserta_seni')) {
            $forge->addField([
                'id_kelompok_peserta_seni' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11],
                'id_kompetisi_seni' => ['type' => 'INT', 'constraint' => 11],
                'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
            ]);
            $forge->addKey('id_kelompok_peserta_seni', true);
            $forge->createTable('kelompok_peserta_seni', true);
        }

        if (! $db->tableExists('pembayaran')) {
            $forge->addField([
                'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11],
                'status_pembayaran' => ['type' => 'VARCHAR', 'constraint' => 50],
            ]);
            $forge->addKey('id_pembayaran', true);
            $forge->createTable('pembayaran', true);
        }

        $tables = ['kontingen', 'pendaftar', 'peserta_tanding', 'kelompok_peserta_seni', 'pembayaran', 'kompetisi_tanding', 'kompetisi_seni'];
        foreach ($tables as $t) {
            if ($db->tableExists($t)) {
                $db->table($t)->truncate();
            }
        }

        $db->table('kontingen')->insert(['nama_kontingen' => 'Test', 'email_kontingen' => 't@t.com', 'password' => 'x']);
        $this->idKontingen = (int) $db->insertID();
    }

    private int $idKontingen;

    public function testSummaryReturnsExpectedKeys(): void
    {
        $result = (new DashboardKontingenService())->summary($this->idKontingen);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('jumlah_atlet', $result);
        $this->assertArrayHasKey('jumlah_tanding', $result);
        $this->assertArrayHasKey('jumlah_seni', $result);
        $this->assertArrayHasKey('jumlah_tagihan', $result);
        $this->assertArrayHasKey('jumlah_transaksi', $result);
    }

    public function testSummaryCountsAtlet(): void
    {
        $db = db_connect();
        $db->table('pendaftar')->insert(['id_kontingen' => $this->idKontingen, 'nama_pendaftar' => 'A1', 'jenis_kelamin' => 'putra', 'tempat_lahir' => 'X', 'tanggal_lahir' => '2000-01-01']);
        $db->table('pendaftar')->insert(['id_kontingen' => $this->idKontingen, 'nama_pendaftar' => 'A2', 'jenis_kelamin' => 'putra', 'tempat_lahir' => 'X', 'tanggal_lahir' => '2000-01-01']);
        $db->table('pendaftar')->insert(['id_kontingen' => $this->idKontingen, 'nama_pendaftar' => 'A3', 'jenis_kelamin' => 'putra', 'tempat_lahir' => 'X', 'tanggal_lahir' => '2000-01-01']);

        $result = (new DashboardKontingenService())->summary($this->idKontingen);
        $this->assertSame(3, $result['jumlah_atlet']);
    }
}
