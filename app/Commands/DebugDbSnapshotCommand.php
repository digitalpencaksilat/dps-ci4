<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DebugDbSnapshotCommand extends BaseCommand
{
    protected $group = 'Debug';
    protected $name = 'debug:db-snapshot';
    protected $description = 'Print quick DB snapshot (counts + sample rows) for scheduling debug.';

    public function run(array $params)
    {
        $db = db_connect();

        $tables = [
            'gelanggang',
            'sub_kategori_seni',
            'kompetisi_seni',
            'kelompok_peserta_seni',
            'penampilan_seni',
            'battle_seni',
            'detail_jadwal_seni',
            'perangkat_pertandingan',
            'kategori_lomba',
        ];

        foreach ($tables as $table) {
            CLI::write('== ' . $table . ' ==');

            try {
                $count = $db->table($table)->countAllResults();
                CLI::write('count: ' . $count);

                $rows = $db->table($table)->limit(5)->get()->getResultArray();
                if ($rows === []) {
                    CLI::write('<empty>');
                } else {
                    foreach ($rows as $row) {
                        CLI::write(json_encode($row, JSON_UNESCAPED_UNICODE));
                    }
                }

                CLI::newLine();
            } catch (\Throwable $e) {
                CLI::error('error: ' . $e->getMessage());
                CLI::newLine();
            }
        }

        return 0;
    }
}
