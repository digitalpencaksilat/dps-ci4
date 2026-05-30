<?php

namespace App\Commands;

use App\Services\JadwalSeniOtomatisService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestPenjadwalanOtomatisSeniCommand extends BaseCommand
{
    protected $group = 'Testing';
    protected $name = 'test:penjadwalan-otomatis-seni';
    protected $description = 'Dry run penjadwalan otomatis seni pool/battle (CI4) untuk cek readiness DB.';

    /**
     * @var array<string, string>
     */
    protected $options = [
        'mode' => 'pool|battle',
        'gelanggang' => 'comma-separated id_gelanggang, contoh: 1,2',
        'sub' => 'comma-separated id_sub_kategori_seni, contoh: 10,11',
        'pool' => 'comma-separated kapasitas pool per gelanggang, contoh: 2,2 (mode=pool)',
        'partai' => 'comma-separated jumlah partai per gelanggang, contoh: 4,4 (mode=battle)',
        'babak' => 'comma-separated babak battle, contoh: Final,Semi Final (mode=battle)',
        'jenis' => 'prestasi|pemasalan_seling_1|pemasalan_seling_2|pemasalan_seling_3 (mode=battle)',
    ];

    public function run(array $params)
    {
        // NOTE: this is a manual QA helper, not a production feature.
        // Usage examples:
        //   php spark test:penjadwalan-otomatis-seni --mode=pool --gelanggang=1,2 --pool=2,2 --sub=1,2
        //   php spark test:penjadwalan-otomatis-seni --mode=battle --gelanggang=1 --partai=4 --sub=1 --babak=Final --jenis=prestasi

        $mode = (string) (CLI::getOption('mode') ?: 'pool');
        if (! in_array($mode, ['pool', 'battle'], true)) {
            CLI::error('mode harus pool|battle');
            return 1;
        }

        $tanggal = date('Y-m-d');
        $gelanggang = (string) CLI::getOption('gelanggang') ?: '1';
        $sub = (string) CLI::getOption('sub') ?: '';

        $idGelanggang = array_values(array_filter(array_map('intval', explode(',', $gelanggang)), static fn ($v) => $v > 0));
        $urutanIdSub = array_values(array_filter(array_map('intval', explode(',', $sub)), static fn ($v) => $v > 0));

        if ($idGelanggang === []) {
            CLI::error('gelanggang wajib, contoh: --gelanggang=1,2');
            return 1;
        }
        if ($urutanIdSub === []) {
            CLI::error('sub wajib, contoh: --sub=10,11');
            return 1;
        }

        $service = new JadwalSeniOtomatisService();

        if ($mode === 'pool') {
            $pool = (string) CLI::getOption('pool') ?: '1';
            $jumlahPoolList = array_values(array_filter(array_map('intval', explode(',', $pool)), static fn ($v) => $v > 0));

            $jumlahPool = [];
            foreach ($idGelanggang as $idx => $gid) {
                $jumlahPool[$gid] = (int) ($jumlahPoolList[$idx] ?? $jumlahPoolList[0] ?? 1);
            }

            $pengaturan = [
                'tanggal' => $tanggal,
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '22:00:00',
                'keterangan' => '[TEST] penjadwalan otomatis seni pool',
                'id_gelanggang' => $idGelanggang,
                'jumlah_pool' => $jumlahPool,
                'urutan_id_sub_kategori_seni' => $urutanIdSub,
            ];

            CLI::write('START generate pool...');
            $result = $service->generatePool($pengaturan);
            CLI::write('DONE generate pool.');
            CLI::write(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return ($result['status'] ?? false) ? 0 : 1;
        }

        $partai = (string) CLI::getOption('partai') ?: '1';
        $jumlahPartaiList = array_values(array_filter(array_map('intval', explode(',', $partai)), static fn ($v) => $v > 0));

        $jumlahPartai = [];
        foreach ($idGelanggang as $idx => $gid) {
            $jumlahPartai[$gid] = (int) ($jumlahPartaiList[$idx] ?? $jumlahPartaiList[0] ?? 1);
        }

        $babak = (string) CLI::getOption('babak') ?: 'Final';
        $babakBattle = array_values(array_filter(array_map('trim', explode(',', $babak)), static fn ($v) => $v !== ''));

        $jenis = (string) (CLI::getOption('jenis') ?: 'prestasi');

        $pengaturan = [
            'tanggal' => $tanggal,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '22:00:00',
            'keterangan' => '[TEST] penjadwalan otomatis seni battle',
            'id_gelanggang' => $idGelanggang,
            'jumlah_partai' => $jumlahPartai,
            'urutan_id_sub_kategori_seni' => $urutanIdSub,
            'babak_battle_seni' => $babakBattle,
            'jenis_penjadwalan' => $jenis,
        ];

        CLI::write('START generate battle...');
        $result = $service->generateBattle($pengaturan);
        CLI::write('DONE generate battle.');
        CLI::write(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return ($result['status'] ?? false) ? 0 : 1;
    }
}
