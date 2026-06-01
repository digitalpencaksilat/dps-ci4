<?php
namespace App\Commands;

use App\Models\JadwalTandingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DebugTbd extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'debug:tbd';
    protected $description = 'Debug TBD partai di jadwal tanding';

    public function run(array $params)
    {
        $db = db_connect();
        $id = (int)($params[0] ?? 1);

        CLI::write("=== Debug TBD untuk jadwal_tanding id=$id ===", 'yellow');

        // Simulasi query JadwalTandingModel
        $rows = $db->query("
            SELECT
                djt.nomor_partai,
                p.babak,
                p.nomor_pertandingan,
                p.nomor_pertandingan_selanjutnya,
                p.id_atlet_biru,
                p.id_atlet_merah,
                atlet_biru.nama_pendaftar as nama_atlet_biru,
                atlet_merah.nama_pendaftar as nama_atlet_merah,
                (SELECT IF(p.babak != 'perebutan juara tiga',
                    (SELECT djt2.nomor_partai
                        FROM detail_jadwal_tanding djt2
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = p.id_kompetisi_tanding
                          AND p2.nomor_pertandingan_selanjutnya = p.nomor_pertandingan
                          AND p2.nomor_pertandingan % 2 = 1
                        LIMIT 1
                    ),
                    (SELECT djt2.nomor_partai
                        FROM detail_jadwal_tanding djt2
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = p.id_kompetisi_tanding
                          AND p2.babak = 'semi final'
                          AND p2.nomor_pertandingan % 2 = 1
                        LIMIT 1
                    )
                )) as calon_atlet_biru,
                (SELECT IF(p.babak != 'perebutan juara tiga',
                    (SELECT djt2.nomor_partai
                        FROM detail_jadwal_tanding djt2
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = p.id_kompetisi_tanding
                          AND p2.nomor_pertandingan_selanjutnya = p.nomor_pertandingan
                          AND p2.nomor_pertandingan % 2 = 0
                        LIMIT 1
                    ),
                    (SELECT djt2.nomor_partai
                        FROM detail_jadwal_tanding djt2
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = p.id_kompetisi_tanding
                          AND p2.babak = 'semi final'
                          AND p2.nomor_pertandingan % 2 = 0
                        LIMIT 1
                    )
                )) as calon_atlet_merah
            FROM detail_jadwal_tanding djt
            JOIN pertandingan p ON p.id_pertandingan = djt.id_pertandingan
            LEFT JOIN peserta_tanding pt_biru ON pt_biru.id_peserta_tanding = p.id_atlet_biru
            LEFT JOIN peserta_tanding pt_merah ON pt_merah.id_peserta_tanding = p.id_atlet_merah
            LEFT JOIN pendaftar atlet_biru ON atlet_biru.id_pendaftar = pt_biru.id_pendaftar
            LEFT JOIN pendaftar atlet_merah ON atlet_merah.id_pendaftar = pt_merah.id_pendaftar
            WHERE djt.id_jadwal_tanding = ?
            ORDER BY djt.nomor_partai
            LIMIT 30
        ", [$id])->getResult();

        CLI::write("\n[1] Sample 30 partai (biru/merah/calon):", 'cyan');
        $tbd_count = 0;
        foreach ($rows as $r) {
            $biru  = $r->nama_atlet_biru  ?? ($r->calon_atlet_biru  ? "→Pemenang {$r->calon_atlet_biru}"  : 'TBD');
            $merah = $r->nama_atlet_merah ?? ($r->calon_atlet_merah ? "→Pemenang {$r->calon_atlet_merah}" : 'TBD');
            $isTbd = ($biru === 'TBD' || $merah === 'TBD');
            if ($isTbd) $tbd_count++;
            $color = $isTbd ? 'red' : 'green';
            CLI::write(sprintf("  partai=%3d babak=%-15s biru=%-30s merah=%s",
                $r->nomor_partai,
                $r->babak ?: '(kosong)',
                $biru,
                $merah
            ), $color);
        }

        // Count total TBD
        $allRows = $db->query("
            SELECT
                p.id_atlet_biru, p.id_atlet_merah, p.babak,
                (SELECT djt2.nomor_partai FROM detail_jadwal_tanding djt2
                    JOIN pertandingan p2 ON p2.id_pertandingan=djt2.id_pertandingan
                    WHERE p2.id_kompetisi_tanding=p.id_kompetisi_tanding
                    AND p2.nomor_pertandingan_selanjutnya=p.nomor_pertandingan
                    AND p2.nomor_pertandingan % 2 = 1 LIMIT 1) as calon_biru,
                (SELECT djt2.nomor_partai FROM detail_jadwal_tanding djt2
                    JOIN pertandingan p2 ON p2.id_pertandingan=djt2.id_pertandingan
                    WHERE p2.id_kompetisi_tanding=p.id_kompetisi_tanding
                    AND p2.nomor_pertandingan_selanjutnya=p.nomor_pertandingan
                    AND p2.nomor_pertandingan % 2 = 0 LIMIT 1) as calon_merah
            FROM detail_jadwal_tanding djt
            JOIN pertandingan p ON p.id_pertandingan=djt.id_pertandingan
            WHERE djt.id_jadwal_tanding=?
        ", [$id])->getResult();

        $totalTbd = 0;
        $totalOk  = 0;
        $babakKosong = 0;
        foreach ($allRows as $r) {
            $biruOk  = $r->id_atlet_biru  !== null || $r->calon_biru  !== null;
            $merahOk = $r->id_atlet_merah !== null || $r->calon_merah !== null;
            if (!$biruOk || !$merahOk) $totalTbd++;
            else $totalOk++;
            if (empty($r->babak)) $babakKosong++;
        }

        CLI::write("\n[2] Summary:", 'cyan');
        CLI::write("  Total partai   : " . count($allRows));
        CLI::write("  OK (ada atlet/calon) : $totalOk", 'green');
        CLI::write("  TBD (tidak ada atlet/calon) : $totalTbd", $totalTbd > 0 ? 'red' : 'green');
        CLI::write("  Babak kosong   : $babakKosong", $babakKosong > 0 ? 'red' : 'green');

        // 3. Tampilkan semua partai TBD dengan detail nomor_pertandingan
        CLI::write("\n[3] Detail partai TBD:", 'cyan');
        $tbdRows = $db->query("
            SELECT
                djt.nomor_partai,
                p.babak,
                p.nomor_pertandingan as np,
                p.nomor_pertandingan_selanjutnya as nps,
                p.id_kompetisi_tanding as kt,
                p.id_atlet_biru,
                p.id_atlet_merah,
                (SELECT djt2.nomor_partai FROM detail_jadwal_tanding djt2
                    JOIN pertandingan p2 ON p2.id_pertandingan=djt2.id_pertandingan
                    WHERE p2.id_kompetisi_tanding=p.id_kompetisi_tanding
                    AND p2.nomor_pertandingan_selanjutnya=p.nomor_pertandingan
                    AND p2.nomor_pertandingan % 2 = 1 LIMIT 1) as calon_biru,
                (SELECT djt2.nomor_partai FROM detail_jadwal_tanding djt2
                    JOIN pertandingan p2 ON p2.id_pertandingan=djt2.id_pertandingan
                    WHERE p2.id_kompetisi_tanding=p.id_kompetisi_tanding
                    AND p2.nomor_pertandingan_selanjutnya=p.nomor_pertandingan
                    AND p2.nomor_pertandingan % 2 = 0 LIMIT 1) as calon_merah,
                (SELECT COUNT(*) FROM pertandingan p3
                    WHERE p3.id_kompetisi_tanding=p.id_kompetisi_tanding) as total_di_kt
            FROM detail_jadwal_tanding djt
            JOIN pertandingan p ON p.id_pertandingan=djt.id_pertandingan
            WHERE djt.id_jadwal_tanding=?
            HAVING (id_atlet_biru IS NULL AND calon_biru IS NULL)
               OR  (id_atlet_merah IS NULL AND calon_merah IS NULL)
            ORDER BY djt.nomor_partai
        ", [$id])->getResult();

        foreach ($tbdRows as $r) {
            CLI::write(sprintf(
                "  partai=%3d babak=%-15s kt=%4d np=%-4s nps=%-4s total_kt=%d | biru=%s merah=%s",
                $r->nomor_partai, $r->babak ?: '(kosong)', $r->kt,
                $r->np ?? 'NULL', $r->nps ?? 'NULL', $r->total_di_kt,
                $r->id_atlet_biru ?? ('calon='.(string)($r->calon_biru ?? 'NULL')),
                $r->id_atlet_merah ?? ('calon='.(string)($r->calon_merah ?? 'NULL'))
            ), 'red');

            // Cek semua pertandingan di kompetisi yang sama untuk debug
            $ktRows = $db->query("
                SELECT p2.nomor_pertandingan, p2.nomor_pertandingan_selanjutnya, djt2.nomor_partai, p2.id_atlet_biru, p2.id_atlet_merah
                FROM pertandingan p2
                JOIN detail_jadwal_tanding djt2 ON djt2.id_pertandingan=p2.id_pertandingan
                WHERE p2.id_kompetisi_tanding=? AND djt2.id_jadwal_tanding=?
                ORDER BY p2.nomor_pertandingan
            ", [$r->kt, $id])->getResult();
            CLI::write("    Semua pertandingan di kt={$r->kt}:", 'yellow');
            foreach ($ktRows as $k) {
                CLI::write(sprintf("      partai=%3d np=%-4s nps=%-4s biru=%-5s merah=%s",
                    $k->nomor_partai, $k->nomor_pertandingan ?? 'NULL',
                    $k->nomor_pertandingan_selanjutnya ?? 'NULL',
                    $k->id_atlet_biru ?? 'NULL', $k->id_atlet_merah ?? 'NULL'
                ));
            }
        }

        CLI::write("\nDONE", 'green');
    }
}
