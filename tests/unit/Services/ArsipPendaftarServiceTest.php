<?php

namespace Tests\Unit\Services;

use App\Models\ArsipPendaftarModel;
use App\Models\PendaftarModel;
use App\Services\ArsipPendaftarService;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class ArsipPendaftarServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();
        helper('arsip_pendaftar');

        // Setup mock config in DB
        $db = db_connect();
        $forge = \Config\Database::forge();

        // Create table if not exists for testing
        if (!$db->tableExists('site_builder_settings')) {
            $forge->addField([
                'id_site_builder_settings' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'setting'                  => ['type' => 'VARCHAR', 'constraint' => 255],
                'value'                    => ['type' => 'TEXT'],
                'is_array'                 => ['type' => 'INT', 'constraint' => 1],
            ]);
            $forge->addKey('id_site_builder_settings', true);
            $forge->createTable('site_builder_settings', true);
        }

        if (!$db->tableExists('arsip_pendaftar')) {
            $forge->addField([
                'id_arsip_pendaftar' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'id_pendaftar'       => ['type' => 'INT', 'constraint' => 11],
                'nama_arsip'         => ['type' => 'VARCHAR', 'constraint' => 255],
                'jenis_arsip'        => ['type' => 'VARCHAR', 'constraint' => 255],
                'slug'               => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'is_required'        => ['type' => 'INT', 'constraint' => 1, 'default' => 0],
                'file_path'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'status_verifikasi'  => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'pending'],
                'keterangan'         => ['type' => 'TEXT', 'null' => true],
                'urutan'             => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            ]);
            $forge->addKey('id_arsip_pendaftar', true);
            $forge->createTable('arsip_pendaftar', true);
        }

        $db->table('site_builder_settings')->where('setting', 'arsip_pendaftar_slots')->delete();
        $db->table('site_builder_settings')->insert([
            'setting' => 'arsip_pendaftar_slots',
            'value' => json_encode([
                'slot_1' => [
                    'nama_arsip' => 'Akta Kelahiran',
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 5000,
                    'required' => true,
                    'active' => true
                ]
            ]),
            'is_array' => 1
        ]);
        
        // We cannot easily test physical file uploads without creating temp files and mocking
        // So we will focus on the db state methods like deleteArchive, findByJenisArsip (indirectly), validateSlotExists
    }

    public function testValidateSlotExistsReturnsTrueForActiveSlot()
    {
        $service = new ArsipPendaftarService();
        $this->assertTrue($service->validateSlotExists('slot_1'));
    }

    public function testValidateSlotExistsReturnsFalseForNonExistentSlot()
    {
        $service = new ArsipPendaftarService();
        $this->assertFalse($service->validateSlotExists('slot_99'));
    }

    public function testDeleteArchiveThrowsExceptionWhenNotFound()
    {
        $service = new ArsipPendaftarService();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Arsip tidak ditemukan.');
        
        $service->deleteArchive(9999);
    }
}
