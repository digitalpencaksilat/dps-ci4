<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DebugImport extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'debug:import';
    protected $description = 'Debug import jadwal tanding state — find why pertandingan have NULL id_atlet';

    public function run(array $params)
    {
        $db = db_connect();
        CLI::write("=== DB: " . $db->getDatabase() . " ===", 'yellow');

        // 1. Latest jadwal_tanding
        $row = $db->table('jadwal_tanding')->select('id_jadwal_tanding,id_gelanggang,keterangan')
            ->orderBy('id_jadwal_tanding','DESC')->get(3)->getResult();
        CLI::write("\n[1] Latest 3 jadwal_tanding:", 'cyan');
        foreach ($row as $r) {
            CLI::write("  id={$r->id_jadwal_tanding} ket=" . substr((string)$r->keterangan,0,40));
        }
        if (empty($row)) { CLI::error("No jadwal_tanding"); return; }

        $id = (int)$row[0]->id_jadwal_tanding;
        CLI::write("\n[2] Inspecting jadwal_tanding id=$id", 'cyan');

        // 2. Total pertandingan + NULL counts
        $total = $db->table('detail_jadwal_tanding')->where('id_jadwal_tanding',$id)->countAllResults();
        $nullBoth = $db->query(
            "SELECT COUNT(*) c FROM detail_jadwal_tanding d JOIN pertandingan p ON p.id_pertandingan=d.id_pertandingan "
            ."WHERE d.id_jadwal_tanding=? AND p.id_atlet_biru IS NULL AND p.id_atlet_merah IS NULL",
            [$id]
        )->getRow()->c;
        $nullOne = $db->query(
            "SELECT COUNT(*) c FROM detail_jadwal_tanding d JOIN pertandingan p ON p.id_pertandingan=d.id_pertandingan "
            ."WHERE d.id_jadwal_tanding=? AND (p.id_atlet_biru IS NULL OR p.id_atlet_merah IS NULL)",
            [$id]
        )->getRow()->c;
        CLI::write("  Total partai      : $total");
        CLI::write("  NULL kedua-duanya : $nullBoth");
        CLI::write("  NULL salah satu   : $nullOne");

        // 3. Sample 5 pertandingan
        CLI::write("\n[3] Sample 5 pertandingan:", 'cyan');
        $samples = $db->query(
            "SELECT d.nomor_partai, p.id_pertandingan, p.id_kompetisi_tanding, p.babak, "
            ."p.id_atlet_biru, p.id_atlet_merah, p.nomor_pertandingan, p.nomor_pertandingan_selanjutnya "
            ."FROM detail_jadwal_tanding d JOIN pertandingan p ON p.id_pertandingan=d.id_pertandingan "
            ."WHERE d.id_jadwal_tanding=? ORDER BY d.nomor_partai LIMIT 5", [$id]
        )->getResult();
        foreach ($samples as $s) {
            CLI::write(sprintf("  partai=%s pert=%s kt=%s babak=%s biru=%s merah=%s np=%s nps=%s",
                $s->nomor_partai, $s->id_pertandingan, $s->id_kompetisi_tanding, $s->babak,
                $s->id_atlet_biru ?? 'NULL', $s->id_atlet_merah ?? 'NULL',
                $s->nomor_pertandingan, $s->nomor_pertandingan_selanjutnya));
        }

        // 4. Counts in related tables
        CLI::write("\n[4] Related table counts:", 'cyan');
        // Kompetisi tanding yang dipakai oleh pertandingan ini
        $ktIds = $db->query(
            "SELECT DISTINCT p.id_kompetisi_tanding FROM detail_jadwal_tanding d "
            ."JOIN pertandingan p ON p.id_pertandingan=d.id_pertandingan WHERE d.id_jadwal_tanding=?", [$id]
        )->getResultArray();
        $ktList = array_column($ktIds,'id_kompetisi_tanding');
        CLI::write("  kompetisi_tanding terlibat : " . count($ktList) . " ids: " . implode(',', array_slice($ktList,0,8)) . (count($ktList)>8?'...':''));

        if (!empty($ktList)) {
            $countPt = $db->table('peserta_tanding')->whereIn('id_kompetisi_tanding',$ktList)->countAllResults();
            CLI::write("  peserta_tanding di kompetisi tsb : $countPt");
            $countPtLinked = $db->query(
                "SELECT COUNT(DISTINCT pt.id_peserta_tanding) c FROM peserta_tanding pt "
                ."JOIN pertandingan p ON (p.id_atlet_biru=pt.id_peserta_tanding OR p.id_atlet_merah=pt.id_peserta_tanding) "
                ."JOIN detail_jadwal_tanding d ON d.id_pertandingan=p.id_pertandingan "
                ."WHERE d.id_jadwal_tanding=?", [$id]
            )->getRow()->c;
            CLI::write("  peserta_tanding ter-link ke pertandingan : $countPtLinked");

            // Sample 5 peserta_tanding di kompetisi pertama
            CLI::write("\n[5] Sample 5 peserta_tanding di kt={$ktList[0]}:", 'cyan');
            $pts = $db->query(
                "SELECT pt.id_peserta_tanding, pt.id_pendaftar, pt.id_kompetisi_tanding, "
                ."p.nama_pendaftar, k.nama_kontingen "
                ."FROM peserta_tanding pt JOIN pendaftar p ON p.id_pendaftar=pt.id_pendaftar "
                ."JOIN kontingen k ON k.id_kontingen=p.id_kontingen "
                ."WHERE pt.id_kompetisi_tanding=? LIMIT 5", [$ktList[0]]
            )->getResult();
            foreach ($pts as $pt) {
                CLI::write(sprintf("  pt=%s pendaftar=%s nama=%s kontingen=%s",
                    $pt->id_peserta_tanding, $pt->id_pendaftar, $pt->nama_pendaftar, $pt->nama_kontingen));
            }

            // Cek 5 partai di kt pertama dengan info
            CLI::write("\n[6] Pertandingan di kt={$ktList[0]} (first 5):", 'cyan');
            $part = $db->query(
                "SELECT p.id_pertandingan, p.babak, p.nomor_pertandingan, p.id_atlet_biru, p.id_atlet_merah, d.nomor_partai "
                ."FROM pertandingan p JOIN detail_jadwal_tanding d ON d.id_pertandingan=p.id_pertandingan "
                ."WHERE p.id_kompetisi_tanding=? AND d.id_jadwal_tanding=? ORDER BY d.nomor_partai LIMIT 5", [$ktList[0],$id]
            )->getResult();
            foreach ($part as $p) {
                CLI::write(sprintf("  partai=%s pert=%s babak=%s biru=%s merah=%s",
                    $p->nomor_partai, $p->id_pertandingan, $p->babak,
                    $p->id_atlet_biru ?? 'NULL', $p->id_atlet_merah ?? 'NULL'));
            }
        }

        // 7. Breakdown per babak
        CLI::write("\n[7] Breakdown per babak:", 'cyan');
        $babakStats = $db->query(
            "SELECT p.babak, COUNT(*) as jumlah, "
            ."SUM(CASE WHEN p.id_atlet_biru IS NULL THEN 1 ELSE 0 END) as null_biru, "
            ."SUM(CASE WHEN p.id_atlet_merah IS NULL THEN 1 ELSE 0 END) as null_merah "
            ."FROM detail_jadwal_tanding d JOIN pertandingan p ON p.id_pertandingan=d.id_pertandingan "
            ."WHERE d.id_jadwal_tanding=? GROUP BY p.babak ORDER BY jumlah DESC", [$id]
        )->getResult();
        foreach ($babakStats as $b) {
            CLI::write(sprintf("  %-20s jumlah=%3d null_biru=%3d null_merah=%3d",
                $b->babak ?: '(KOSONG)', $b->jumlah, $b->null_biru, $b->null_merah));
        }

        // 8. Cek nomor_pertandingan & nomor_pertandingan_selanjutnya
        CLI::write("\n[8] Sample nomor_pertandingan per pool (kt=138):", 'cyan');
        $nomorSample = $db->query(
            "SELECT d.nomor_partai, p.nomor_pertandingan, p.nomor_pertandingan_selanjutnya, p.babak, p.id_atlet_biru, p.id_atlet_merah "
            ."FROM detail_jadwal_tanding d "
            ."JOIN pertandingan p ON p.id_pertandingan=d.id_pertandingan "
            ."WHERE d.id_jadwal_tanding=? AND p.id_kompetisi_tanding=138 "
            ."ORDER BY d.nomor_partai", [$id]
        )->getResult();
        foreach ($nomorSample as $n) {
            CLI::write(sprintf("  partai=%3d np=%-5s nps=%-5s babak=%-20s biru=%-5s merah=%s",
                $n->nomor_partai,
                $n->nomor_pertandingan ?? 'NULL',
                $n->nomor_pertandingan_selanjutnya ?? 'NULL',
                $n->babak ?: '(KOSONG)',
                $n->id_atlet_biru ?? 'NULL',
                $n->id_atlet_merah ?? 'NULL'
            ));
        }

        // 9. Cek apakah subquery calon_atlet bisa resolve
        CLI::write("\n[9] Test subquery calon_atlet untuk partai Final di kt=138:", 'cyan');
        $finalTest = $db->query(
            "SELECT d.nomor_partai, p.nomor_pertandingan, p.babak,
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
            FROM detail_jadwal_tanding d
            JOIN pertandingan p ON p.id_pertandingan=d.id_pertandingan
            WHERE d.id_jadwal_tanding=? AND p.id_kompetisi_tanding=138
            ORDER BY d.nomor_partai", [$id]
        )->getResult();
        foreach ($finalTest as $f) {
            CLI::write(sprintf("  partai=%3d np=%-5s babak=%-20s calon_biru=%-5s calon_merah=%s",
                $f->nomor_partai,
                $f->nomor_pertandingan ?? 'NULL',
                $f->babak ?: '(KOSONG)',
                $f->calon_biru ?? 'NULL',
                $f->calon_merah ?? 'NULL'
            ));
        }

        CLI::write("\nDONE", 'green');
    }
}
