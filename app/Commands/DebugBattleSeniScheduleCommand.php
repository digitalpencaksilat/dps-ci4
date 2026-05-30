<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DebugBattleSeniScheduleCommand extends BaseCommand
{
    protected $group = 'Debug';
    protected $name = 'debug:battle-seni-schedule';
    protected $description = 'Print scheduled battle_seni rows joined with jadwal_seni/detail_jadwal_seni.';

    public function run(array $params)
    {
        $db = db_connect();

        $rows = $db->table('detail_jadwal_seni djs')
            ->select('djs.id_detail_jadwal_seni, djs.id_jadwal_seni, djs.nomor_partai, djs.nomor_urut, bs.id_battle_seni, bs.id_kompetisi_seni, bs.nomor_battle, bs.babak, bs.jenis_kemenangan, js.id_gelanggang')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni', 'left')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni', 'left')
            ->where('djs.id_battle_seni IS NOT NULL', null, false)
            ->orderBy('js.id_gelanggang', 'ASC')
            ->orderBy('djs.nomor_partai', 'ASC')
            ->get()
            ->getResultArray();

        CLI::write('count: ' . count($rows));
        foreach ($rows as $row) {
            CLI::write(json_encode($row, JSON_UNESCAPED_UNICODE));
        }

        return 0;
    }
}
