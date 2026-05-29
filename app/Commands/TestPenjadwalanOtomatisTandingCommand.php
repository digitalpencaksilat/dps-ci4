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

        // NOTE: this is a manual QA helper, not a production feature.
        // Usage: php spark test:penjadwalan-otomatis-tanding
        // Optional overrides (all optional):
        //   --jenis=prestasi|pemasalan
        //   --gelanggang=1,2
        //   --partai=2,2
        //   --babak=Final,Semi Final
        //   --kelas=105,106
        //   --selang=2
        $jenis = (string) CLI::getOption('jenis') ?: 'pemasalan';
        $gelanggang = (string) CLI::getOption('gelanggang') ?: '1,2';
        $partai = (string) CLI::getOption('partai') ?: '2,2';
        $babak = (string) CLI::getOption('babak') ?: 'Final';
        $kelas = (string) CLI::getOption('kelas') ?: '105';
        $selang = (int) (CLI::getOption('selang') ?: 2);

        $idGelanggang = array_values(array_filter(array_map('intval', explode(',', $gelanggang)), static fn ($v) => $v > 0));
        $jumlahPartai = array_values(array_filter(array_map('intval', explode(',', $partai)), static fn ($v) => $v > 0));
        $babakPertandingan = array_values(array_filter(array_map('trim', explode(',', $babak)), static fn ($v) => $v !== ''));
        $urutanKelas = array_values(array_filter(array_map('intval', explode(',', $kelas)), static fn ($v) => $v > 0));

        $pengaturan = [
            'tanggal' => $tanggal,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '22:00:00',
            'keterangan' => '[TEST] penjadwalan otomatis tanding',
            'id_gelanggang' => $idGelanggang,
            'jumlah_partai' => $jumlahPartai,
            'babak_pertandingan' => $babakPertandingan,
            'jenis_penjadwalan' => $jenis,
            'urutan_id_kelas_tanding' => $urutanKelas,
            'jumlah_selang_seling' => max(1, $selang),
        ];

        CLI::write('START generate...');
        $service = new JadwalTandingOtomatisService();
        $result = $service->generate($pengaturan);
        CLI::write('DONE generate.');

        CLI::write(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return ($result['status'] ?? false) ? 0 : 1;
    }
}
