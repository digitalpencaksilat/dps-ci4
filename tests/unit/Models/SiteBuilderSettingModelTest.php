<?php

namespace Tests\Unit\Models;

use App\Models\SiteBuilderSettingModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

class SiteBuilderSettingModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected function setUp(): void
    {
        parent::setUp();

        $db = db_connect();
        $forge = \Config\Database::forge();

        if (! $db->tableExists('site_builder_settings')) {
            $forge->addField([
                'setting' => ['type' => 'VARCHAR', 'constraint' => 100],
                'value' => ['type' => 'TEXT', 'null' => true],
                'is_array' => ['type' => 'INT', 'constraint' => 1, 'default' => 0],
            ]);
            $forge->addKey('setting', true);
            $forge->createTable('site_builder_settings', true);
        }

        $db->table('site_builder_settings')->truncate();
    }

    public function testGetValueReturnsDefaultWhenKeyNotFound(): void
    {
        $model = new SiteBuilderSettingModel();

        $this->assertNull($model->getValue('nonexistent'));
        $this->assertSame('default', $model->getValue('nonexistent', 'default'));
        $this->assertSame(42, $model->getValue('nonexistent', 42));
    }

    public function testGetValueReturnsStringValue(): void
    {
        $db = db_connect();
        $db->table('site_builder_settings')->insert([
            'setting' => 'app_name',
            'value' => 'My App',
            'is_array' => 0,
        ]);

        $model = new SiteBuilderSettingModel();
        $this->assertSame('My App', $model->getValue('app_name'));
    }

    public function testGetValueReturnsDecodedArrayValue(): void
    {
        $db = db_connect();
        $db->table('site_builder_settings')->insert([
            'setting' => 'menu_items',
            'value' => json_encode(['home', 'about', 'contact']),
            'is_array' => 1,
        ]);

        $model = new SiteBuilderSettingModel();
        $result = $model->getValue('menu_items');

        $this->assertIsArray($result);
        $this->assertSame(['home', 'about', 'contact'], $result);
    }

    public function testGetValueReturnsDefaultOnInvalidJson(): void
    {
        $db = db_connect();
        $db->table('site_builder_settings')->insert([
            'setting' => 'broken',
            'value' => '{invalid',
            'is_array' => 1,
        ]);

        $model = new SiteBuilderSettingModel();
        $this->assertSame('fallback', $model->getValue('broken', 'fallback'));
    }

    public function testSetScalarInsertsNewValue(): void
    {
        $model = new SiteBuilderSettingModel();
        $model->setScalar('new_key', 'new_value');

        $this->assertSame('new_value', $model->getValue('new_key'));
    }

    public function testSetScalarUpdatesExistingValue(): void
    {
        $model = new SiteBuilderSettingModel();
        $model->setScalar('key', 'first');
        $model->setScalar('key', 'second');

        $this->assertSame('second', $model->getValue('key'));
    }
}
