<?php

namespace Tests\Unit\Services;

use App\Services\Admin\Super\KelasTandingService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class KelasTandingServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $forge = \Config\Database::forge();

        $forge->dropTable('pertandingan', true);
        $forge->dropTable('peserta_tanding', true);
        $forge->dropTable('pendaftar', true);
        $forge->dropTable('kontingen', true);
        $forge->dropTable('kompetisi_tanding', true);
        $forge->dropTable('kelas_tanding', true);
        $forge->dropTable('kategori_lomba', true);
        $forge->dropTable('kategori_usia', true);

        if (! $db->tableExists('kategori_usia')) {
            $forge->addField([
                'id_kategori_usia' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'nama_kategori_usia' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'jenis_kelamin' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'putra'],
                'min_umur' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'max_umur' => ['type' => 'INT', 'constraint' => 11, 'default' => 99],
            ]);
            $forge->addKey('id_kategori_usia', true);
            $forge->createTable('kategori_usia', true);
        }

        if (! $db->tableExists('kategori_lomba')) {
            $forge->addField([
                'id_kategori_lomba' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kategori_usia' => ['type' => 'INT', 'constraint' => 11],
                'nama_kategori_lomba' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'tanding'],
                'jenis_perlombaan' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'prestasi'],
                'kuota_peserta' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'peraturan_pertandingan' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'PERSILAT'],
            ]);
            $forge->addKey('id_kategori_lomba', true);
            $forge->createTable('kategori_lomba', true);
        }

        if (! $db->tableExists('kelas_tanding')) {
            $forge->addField([
                'id_kelas_tanding' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kategori_lomba' => ['type' => 'INT', 'constraint' => 11],
                'label' => ['type' => 'VARCHAR', 'constraint' => 100],
                'berat_minimal' => ['type' => 'FLOAT', 'null' => true],
                'berat_maksimal' => ['type' => 'FLOAT', 'null' => true],
                'juara_tiga_bersama' => ['type' => 'INT', 'constraint' => 1, 'default' => 1],
                'jumlah_ronde' => ['type' => 'INT', 'constraint' => 11, 'default' => 3],
                'waktu_per_ronde' => ['type' => 'INT', 'constraint' => 11, 'default' => 120],
                'waktu_istirahat' => ['type' => 'INT', 'constraint' => 11, 'default' => 60],
                'format_penilaian' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'biaya_pendaftaran_dn' => ['type' => 'FLOAT', 'null' => true],
                'biaya_pendaftaran_ln' => ['type' => 'FLOAT', 'null' => true],
                'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            ]);
            $forge->addKey('id_kelas_tanding', true);
            $forge->createTable('kelas_tanding', true);
        }

        if (! $db->tableExists('kompetisi_tanding')) {
            $forge->addField([
                'id_kompetisi_tanding' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kelas_tanding' => ['type' => 'INT', 'constraint' => 11],
                'max_peserta' => ['type' => 'INT', 'constraint' => 11, 'default' => 16],
                'perhitungan_medali' => ['type' => 'INT', 'constraint' => 1, 'default' => 1],
                'nomor_pool' => ['type' => 'INT', 'constraint' => 11, 'default' => 1],
                'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'bagan_pertandingan' => ['type' => 'TEXT', 'null' => true],
            ]);
            $forge->addKey('id_kompetisi_tanding', true);
            $forge->createTable('kompetisi_tanding', true);
        }

        if (! $db->tableExists('kontingen')) {
            $forge->addField([
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'nama_kontingen' => ['type' => 'VARCHAR', 'constraint' => 100],
            ]);
            $forge->addKey('id_kontingen', true);
            $forge->createTable('kontingen', true);
        }

        if (! $db->tableExists('pendaftar')) {
            $forge->addField([
                'id_pendaftar' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kontingen' => ['type' => 'INT', 'constraint' => 11],
                'nama_pendaftar' => ['type' => 'VARCHAR', 'constraint' => 150],
                'berat_badan' => ['type' => 'FLOAT', 'null' => true],
                'jenis_kelamin' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'putra'],
            ]);
            $forge->addKey('id_pendaftar', true);
            $forge->createTable('pendaftar', true);
        }

        if (! $db->tableExists('peserta_tanding')) {
            $forge->addField([
                'id_peserta_tanding' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_pendaftar' => ['type' => 'INT', 'constraint' => 11],
                'id_kompetisi_tanding' => ['type' => 'INT', 'constraint' => 11],
                'id_pembayaran' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'nomor_bagan' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'status' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'OK'],
            ]);
            $forge->addKey('id_peserta_tanding', true);
            $forge->createTable('peserta_tanding', true);
        }

        if (! $db->tableExists('pertandingan')) {
            $forge->addField([
                'id_pertandingan' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_kompetisi_tanding' => ['type' => 'INT', 'constraint' => 11],
            ]);
            $forge->addKey('id_pertandingan', true);
            $forge->createTable('pertandingan', true);
        }

        $db->table('kategori_usia')->truncate();
        $db->table('kategori_lomba')->truncate();
        $db->table('kelas_tanding')->truncate();
        $db->table('kompetisi_tanding')->truncate();
        $db->table('kontingen')->truncate();
        $db->table('pendaftar')->truncate();
        $db->table('peserta_tanding')->truncate();
        $db->table('pertandingan')->truncate();

        $db->table('kategori_usia')->insert(['nama_kategori_usia' => 'Dewasa', 'jenis_kelamin' => 'putra', 'min_umur' => 18, 'max_umur' => 35]);
        $idKategoriUsia = (int) $db->insertID();
        $db->table('kategori_lomba')->insert(['id_kategori_usia' => $idKategoriUsia, 'nama_kategori_lomba' => 'tanding', 'jenis_perlombaan' => 'prestasi', 'kuota_peserta' => 0, 'peraturan_pertandingan' => 'PERSILAT']);
        $this->idKategoriLomba = (int) $db->insertID();
    }

    private int $idKategoriLomba;

    public function testCreateSingleCreatesPoolWithTemplateBagan(): void
    {
        $service = new KelasTandingService();
        $service->createSingle([$this->idKategoriLomba], [
            'label' => 'A',
            'berat_minimal' => 45,
            'berat_maksimal' => 50,
            'jumlah_ronde' => 3,
            'waktu_per_ronde' => 120,
            'waktu_istirahat' => 60,
            'juara_tiga_bersama' => 1,
            'format_penilaian' => 'PERSILAT',
            'biaya_pendaftaran_dn' => 0,
            'biaya_pendaftaran_ln' => 0,
        ], 16);

        $pool = db_connect()->table('kompetisi_tanding')->get()->getRowArray();
        $this->assertNotNull($pool);
        $this->assertNotSame('', (string) ($pool['bagan_pertandingan'] ?? ''));
    }

    public function testUpdateKelasRejectsInvalidWeightRange(): void
    {
        $db = db_connect();
        $db->table('kelas_tanding')->insert(['id_kategori_lomba' => $this->idKategoriLomba, 'label' => 'A', 'berat_minimal' => 50, 'berat_maksimal' => 55, 'juara_tiga_bersama' => 1]);
        $idKelas = (int) $db->insertID();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Berat maksimal');

        (new KelasTandingService())->updateKelas($idKelas, ['berat_minimal' => 60, 'berat_maksimal' => 50]);
    }

    public function testUpdateJumlahPesertaPerPoolRequiresRedistributeWhenReducingBelowExistingCount(): void
    {
        $db = db_connect();
        $db->table('kelas_tanding')->insert(['id_kategori_lomba' => $this->idKategoriLomba, 'label' => 'A', 'berat_minimal' => 45, 'berat_maksimal' => 50, 'juara_tiga_bersama' => 1]);
        $idKelas = (int) $db->insertID();
        $db->table('kompetisi_tanding')->insert(['id_kelas_tanding' => $idKelas, 'nomor_pool' => 1, 'max_peserta' => 4, 'perhitungan_medali' => 1, 'bagan_pertandingan' => '{}']);
        $idPool = (int) $db->insertID();

        $db->table('kontingen')->insert(['nama_kontingen' => 'Kontingen A']);
        $idKontingen = (int) $db->insertID();
        for ($i = 1; $i <= 3; $i++) {
            $db->table('pendaftar')->insert(['id_kontingen' => $idKontingen, 'nama_pendaftar' => 'Atlet ' . $i, 'berat_badan' => 48, 'jenis_kelamin' => 'putra']);
            $idPendaftar = (int) $db->insertID();
            $db->table('peserta_tanding')->insert(['id_pendaftar' => $idPendaftar, 'id_kompetisi_tanding' => $idPool, 'status' => 'OK']);
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('distribusi ulang');

        (new KelasTandingService())->updateJumlahPesertaPerPool([$this->idKategoriLomba], 2, false);
    }
}
