<?php

namespace Tests\Unit\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class ArsipPendaftarHelperTest extends CIUnitTestCase
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
                ],
                'slot_2' => [
                    'nama_arsip' => 'Surat Kesehatan',
                    'allowed_types' => 'png|jpg|jpeg',
                    'max_size' => 5000,
                    'required' => true,
                    'active' => false
                ]
            ]),
            'is_array' => 1
        ]);
    }

    public function testGetArsipPendaftarConfigReturnsArray()
    {
        $config = get_arsip_pendaftar_config_ci4();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('slot_1', $config);
        $this->assertArrayHasKey('slot_2', $config);
    }

    public function testGetActiveArsipPendaftarReturnsOnlyActive()
    {
        $active = get_active_arsip_pendaftar_ci4();
        
        $this->assertCount(1, $active);
        $this->assertArrayHasKey('slot_1', $active);
        $this->assertArrayNotHasKey('slot_2', $active);
    }

    public function testGetRequiredArsipPendaftarReturnsOnlyRequiredAndActive()
    {
        $required = get_required_arsip_pendaftar_ci4();
        
        $this->assertCount(1, $required);
        $this->assertArrayHasKey('slot_1', $required);
    }

    public function testUrlArsipPendaftar()
    {
        $url = url_arsip_pendaftar_ci4('test.jpg');
        
        $this->assertStringContainsString('uploads/peserta/arsip/test.jpg', $url);
    }

    public function testGetSlotConfigReturnsSpecificSlot()
    {
        $slot = get_slot_config_ci4('slot_1');
        
        $this->assertIsArray($slot);
        $this->assertEquals('Akta Kelahiran', $slot['nama_arsip']);
    }

    public function testCountActiveArsipPendaftar()
    {
        $count = count_active_arsip_pendaftar_ci4();
        
        $this->assertEquals(1, $count);
    }

    public function testGetMaxArsipSlot()
    {
        $max = get_max_arsip_slot_ci4();
        
        $this->assertEquals(2, $max);
    }
}
