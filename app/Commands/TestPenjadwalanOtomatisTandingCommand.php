<?php

namespace App\Commands;

use App\Services\JadwalTandingOtomatisService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestPenjadwalanOtomatisTandingCommand extends BaseCommand
{
    protected $group = 'Testing';
    protected $name = 'test:penjadwalan-otomatis-tanding';
    protected $description = 'Dry run penjadwalan otomatis tanding (CI4) untuk cek readiness DB.';

    public function run(array $params)
    {
        $tanggal = date('Y-m-d');

        // Test payload: pemasalan + multi gelanggang.
        $pengaturan = [
            'tanggal' => $tanggal,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '22:00:00',
            'keterangan' => '[TEST] penjadwalan otomatis tanding',
            'id_gelanggang' => [1, 2],
            'jumlah_partai' => [2, 2],
            'babak_pertandingan' => ['Final'],
            'jenis_penjadwalan' => 'pemasalan',
            'urutan_id_kelas_tanding' => [105],
            'jumlah_selang_seling' => 2,
        ];

        CLI::write('START generate...');
        $service = new JadwalTandingOtomatisService();
        $result = $service->generate($pengaturan);
        CLI::write('DONE generate.');

        CLI::write(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return ($result['status'] ?? false) ? 0 : 1;
    }
}
