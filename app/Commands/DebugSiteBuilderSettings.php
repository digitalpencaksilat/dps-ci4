<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DebugSiteBuilderSettings extends BaseCommand
{
    protected $group       = 'Debug';
    protected $name        = 'debug:site-builder-settings';
    protected $description = 'Print count and keys from site_builder_settings.';

    public function run(array $params)
    {
        $db = db_connect();
        $count = (int) $db->table('site_builder_settings')->countAllResults();

        CLI::write('site_builder_settings count: ' . $count);

        $rows = $db->table('site_builder_settings')
            ->select('setting, value, is_array')
            ->orderBy('setting', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $value = (string) ($row['value'] ?? '');
            $value = preg_replace('/\s+/', ' ', $value) ?? $value;
            $value = substr($value, 0, 120);
            CLI::write(($row['setting'] ?? '-') . ' = ' . $value);
        }
    }
}
