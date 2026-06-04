<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalTandingModel extends Model
{
    protected $table      = 'jadwal_tanding';
    protected $primaryKey = 'id_jadwal_tanding';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_gelanggang',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'keterangan',

        // db_testing_event schema still uses CI3 legacy field name.
        'nama_file',
        'pdf_path',
    ];
    protected $useTimestamps = false; // CI3 legacy table does not have created_at/updated_at

    public function get_all()
    {
        return $this->db->table($this->table . ' jt')
            ->select("
                jt.*,
                g.nama_gelanggang,
                g.nomor_gelanggang,
                (SELECT nomor_partai FROM detail_jadwal_tanding WHERE detail_jadwal_tanding.id_jadwal_tanding = jt.id_jadwal_tanding ORDER BY nomor_partai ASC LIMIT 1) as nomor_partai_awal,
                (SELECT nomor_partai FROM detail_jadwal_tanding WHERE detail_jadwal_tanding.id_jadwal_tanding = jt.id_jadwal_tanding ORDER BY nomor_partai DESC LIMIT 1) as nomor_partai_akhir,
                (SELECT COUNT(*) FROM detail_jadwal_tanding WHERE id_jadwal_tanding = jt.id_jadwal_tanding) as jumlah_partai,
                jt.keterangan as keterangan_jadwal
            ")
            ->join('gelanggang g', 'g.id_gelanggang = jt.id_gelanggang')
            ->orderBy('jt.tanggal', 'asc')
            ->orderBy('jt.jam_mulai', 'asc')
            ->get()
            ->getResult();
    }

    public function findWithGelanggang($id)
    {
        return $this->db->table($this->table . ' jt')
            ->select('jt.*, g.nama_gelanggang, g.nomor_gelanggang, jt.keterangan as keterangan_jadwal')
            ->join('gelanggang g', 'g.id_gelanggang = jt.id_gelanggang')
            ->where('jt.id_jadwal_tanding', $id)
            ->get()
            ->getRow();
    }

    public function get_detail_jadwal($id_jadwal_tanding)
    {
        return $this->db->table('detail_jadwal_tanding djt')
            ->select('
                djt.*,
                p.nomor_pertandingan,
                p.babak,
                p.skor_merah,
                p.skor_biru,
                p.id_pemenang,
                p.jenis_kemenangan,
                p.nomor_pertandingan_selanjutnya,
                kt.nomor_pool,
                kl.label as label,
                klb.nama_kategori_lomba,
                klb.jenis_perlombaan,
                ku.nama_kategori_usia,
                ku.jenis_kelamin,
                atlet_merah.nama_pendaftar as nama_atlet_merah,
                atlet_biru.nama_pendaftar as nama_atlet_biru,
                kontingen_merah.nama_kontingen as nama_kontingen_merah,
                kontingen_biru.nama_kontingen as nama_kontingen_biru,
                p.id_atlet_merah,
                p.id_atlet_biru,
                (SELECT IF(p.babak != "Perebutan Juara Tiga",
                    (SELECT djt2.nomor_partai
                        FROM detail_jadwal_tanding djt2
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = kt.id_kompetisi_tanding
                          AND p2.nomor_pertandingan_selanjutnya = p.nomor_pertandingan
                          AND p2.nomor_pertandingan % 2 = 1
                        LIMIT 1
                    ),
                    (SELECT djt2.nomor_partai
                        FROM detail_jadwal_tanding djt2
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = kt.id_kompetisi_tanding
                          AND p2.babak = "Semi Final"
                          AND p2.nomor_pertandingan % 2 = 1
                        LIMIT 1
                    )
                )) as calon_atlet_biru,
                (SELECT IF(p.babak != "Perebutan Juara Tiga",
                    (SELECT djt2.nomor_partai
                        FROM detail_jadwal_tanding djt2
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = kt.id_kompetisi_tanding
                          AND p2.nomor_pertandingan_selanjutnya = p.nomor_pertandingan
                          AND p2.nomor_pertandingan % 2 = 0
                        LIMIT 1
                    ),
                    (SELECT djt2.nomor_partai
                        FROM detail_jadwal_tanding djt2
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = kt.id_kompetisi_tanding
                          AND p2.babak = "Semi Final"
                          AND p2.nomor_pertandingan % 2 = 0
                        LIMIT 1
                    )
                )) as calon_atlet_merah,
                (SELECT IF(p.babak != "Perebutan Juara Tiga",
                    (SELECT g.nama_gelanggang
                        FROM detail_jadwal_tanding djt2
                        JOIN jadwal_tanding jt2 ON jt2.id_jadwal_tanding = djt2.id_jadwal_tanding
                        JOIN gelanggang g ON g.id_gelanggang = jt2.id_gelanggang
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = kt.id_kompetisi_tanding
                          AND p2.nomor_pertandingan_selanjutnya = p.nomor_pertandingan
                          AND p2.nomor_pertandingan % 2 = 1
                        LIMIT 1
                    ),
                    (SELECT g.nama_gelanggang
                        FROM detail_jadwal_tanding djt2
                        JOIN jadwal_tanding jt2 ON jt2.id_jadwal_tanding = djt2.id_jadwal_tanding
                        JOIN gelanggang g ON g.id_gelanggang = jt2.id_gelanggang
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = kt.id_kompetisi_tanding
                          AND p2.babak = "Semi Final"
                          AND p2.nomor_pertandingan % 2 = 1
                        LIMIT 1
                    )
                )) as gelanggang_calon_atlet_biru,
                (SELECT IF(p.babak != "Perebutan Juara Tiga",
                    (SELECT g.nama_gelanggang
                        FROM detail_jadwal_tanding djt2
                        JOIN jadwal_tanding jt2 ON jt2.id_jadwal_tanding = djt2.id_jadwal_tanding
                        JOIN gelanggang g ON g.id_gelanggang = jt2.id_gelanggang
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = kt.id_kompetisi_tanding
                          AND p2.nomor_pertandingan_selanjutnya = p.nomor_pertandingan
                          AND p2.nomor_pertandingan % 2 = 0
                        LIMIT 1
                    ),
                    (SELECT g.nama_gelanggang
                        FROM detail_jadwal_tanding djt2
                        JOIN jadwal_tanding jt2 ON jt2.id_jadwal_tanding = djt2.id_jadwal_tanding
                        JOIN gelanggang g ON g.id_gelanggang = jt2.id_gelanggang
                        JOIN pertandingan p2 ON p2.id_pertandingan = djt2.id_pertandingan
                        WHERE p2.id_kompetisi_tanding = kt.id_kompetisi_tanding
                          AND p2.babak = "Semi Final"
                          AND p2.nomor_pertandingan % 2 = 0
                        LIMIT 1
                    )
                )) as gelanggang_calon_atlet_merah
            ', false)
            ->join('pertandingan p', 'p.id_pertandingan = djt.id_pertandingan')
            ->join('kompetisi_tanding kt', 'kt.id_kompetisi_tanding = p.id_kompetisi_tanding')
            ->join('kelas_tanding kl', 'kl.id_kelas_tanding = kt.id_kelas_tanding')
            ->join('kategori_lomba klb', 'klb.id_kategori_lomba = kl.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = klb.id_kategori_usia')
            ->join('peserta_tanding pt_merah', 'pt_merah.id_peserta_tanding = p.id_atlet_merah', 'left')
            ->join('peserta_tanding pt_biru', 'pt_biru.id_peserta_tanding = p.id_atlet_biru', 'left')
            ->join('pendaftar atlet_merah', 'atlet_merah.id_pendaftar = pt_merah.id_pendaftar', 'left')
            ->join('pendaftar atlet_biru', 'atlet_biru.id_pendaftar = pt_biru.id_pendaftar', 'left')
            ->join('kontingen kontingen_merah', 'kontingen_merah.id_kontingen = atlet_merah.id_kontingen', 'left')
            ->join('kontingen kontingen_biru', 'kontingen_biru.id_kontingen = atlet_biru.id_kontingen', 'left')
            ->where('djt.id_jadwal_tanding', $id_jadwal_tanding)
            ->orderBy('djt.nomor_partai', 'asc')
            ->get()
            ->getResult();
    }

    /**
     * Query data pertandingan untuk keperluan pola penjadwalan.
     * Parity dengan CI3 Pertandingan_model::select() — joins ke kompetisi_tanding,
     * kelas_tanding, kategori_lomba, kategori_usia dengan computed nilai_babak.
     *
     * WHERE: pertandingan.id_pertandingan IN (subquery detail_jadwal_tanding)
     */
    public function getPertandinganPola(int $idJadwalTanding, string $orderBy): array
    {
        $subquery = $this->db->table('detail_jadwal_tanding')
            ->select('id_pertandingan')
            ->where('id_jadwal_tanding', $idJadwalTanding)
            ->getCompiledSelect();

        return $this->db->table('pertandingan p')
            ->select("
                p.id_pertandingan,
                p.id_kompetisi_tanding,
                p.nomor_pertandingan,
                p.babak,
                kt.nomor_pool,
                CASE p.babak
                    WHEN 'Final' THEN 1
                    WHEN 'Perebutan Juara Tiga' THEN 0.6
                    WHEN 'Semi Final' THEN 0.5
                    WHEN '1/4 Final' THEN 0.25
                    WHEN '1/8 Final' THEN 0.125
                    WHEN '1/16 Final' THEN 0.0625
                    WHEN '1/32 Final' THEN 0.031
                    WHEN '1/64 Final' THEN 0.0156
                    ELSE 0
                END AS nilai_babak,
                ku.min_umur,
                ku.jenis_kelamin,
                kl.label,
                kl.berat_maksimal,
                djt.id_detail_jadwal_tanding,
                djt.nomor_partai
            ", false)
            ->join('kompetisi_tanding kt', 'kt.id_kompetisi_tanding = p.id_kompetisi_tanding')
            ->join('kelas_tanding kl', 'kl.id_kelas_tanding = kt.id_kelas_tanding')
            ->join('kategori_lomba klb', 'klb.id_kategori_lomba = kl.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = klb.id_kategori_usia')
            ->join('detail_jadwal_tanding djt', 'djt.id_pertandingan = p.id_pertandingan AND djt.id_jadwal_tanding = ' . (int) $idJadwalTanding, 'inner')
            ->orderBy($orderBy)
            ->get()
            ->getResult();
    }

    /**
     * Resequence nomor partai tanding mulai dari $nomorPartaiBaruMulai.
     * Parity dengan CI3: Detail_jadwal_tanding_model::resequence_nomor_partai().
     */
    public function resequenceNomorPartai($idJadwalTanding, $nomorPartaiBaruMulai)
    {
        $sql = "
            UPDATE detail_jadwal_tanding t
            JOIN (
                SELECT id_detail_jadwal_tanding,
                    ROW_NUMBER() OVER (ORDER BY nomor_partai) + ? - 1 AS new_nomor
                FROM detail_jadwal_tanding
                WHERE id_jadwal_tanding = ?
            ) AS subquery
            ON t.id_detail_jadwal_tanding = subquery.id_detail_jadwal_tanding
            SET t.nomor_partai = subquery.new_nomor
            WHERE t.id_jadwal_tanding = ?
        ";

        return $this->db->query($sql, [
            (int) $nomorPartaiBaruMulai,
            (int) $idJadwalTanding,
            (int) $idJadwalTanding,
        ]);
    }

    /**
     * Ambil nomor_partai_awal (terkecil) untuk suatu jadwal tanding.
     * Parity dengan CI3: $jadwal_tanding->nomor_partai_awal.
     */
    public function getNomorPartaiAwal(int $idJadwalTanding): int
    {
        $row = $this->db->table('detail_jadwal_tanding')
            ->select('MIN(nomor_partai) AS awal')
            ->where('id_jadwal_tanding', $idJadwalTanding)
            ->get()
            ->getRow();

        return (int) ($row->awal ?? 0);
    }

    /**
     * Mengacak urutan pertandingan dengan pola selang-seling (parity CI3).
     *
     * HANYA UNTUK PEMASALAN — mengelompokkan pool per id_kompetisi_tanding
     * lalu menukar posisi antar pool dalam "paket" sejumlah $maxDiambil.
     *
     * @param int   $maxDiambil Jumlah pool maksimal untuk di-reshuffle (1-4)
     * @param array $pertandingan Array of objects dengan properti:
     *                             id_pertandingan, id_kompetisi_tanding, nilai_babak
     * @return array Pertandingan yang sudah diacak
     */
    public function acakUrutanPertandingan(int $maxDiambil, array $pertandingan): array
    {
        $jumlahDiambil = 0;
        $pertandinganTemp = [];
        $idKompetisiTanding = null;
        $idPaket = 1;

        foreach ($pertandingan as $kPertandingan => $vPertandingan) {
            if ($jumlahDiambil < $maxDiambil) {
                if ($vPertandingan->id_kompetisi_tanding == $idKompetisiTanding) {
                    $vPertandingan->id_select = $kPertandingan;
                    $vPertandingan->id_paket = $idPaket;
                    array_push($pertandinganTemp, $vPertandingan);
                } else {
                    $idKompetisiTanding = $vPertandingan->id_kompetisi_tanding;
                    $vPertandingan->id_select = $kPertandingan;
                    $vPertandingan->id_paket = $idPaket;
                    array_push($pertandinganTemp, $vPertandingan);
                    $jumlahDiambil++;
                }
            } else {
                if ($vPertandingan->id_kompetisi_tanding == $idKompetisiTanding) {
                    $vPertandingan->id_select = $kPertandingan;
                    $vPertandingan->id_paket = $idPaket;
                    array_push($pertandinganTemp, $vPertandingan);
                } else {
                    $idKompetisiTanding = $vPertandingan->id_kompetisi_tanding;

                    // Sort temp array by nilai_babak agar babak diurutkan
                    usort($pertandinganTemp, static function ($a, $b): int {
                        return ((float) $a->nilai_babak) <=> ((float) $b->nilai_babak);
                    });

                    // Ambil nilai min id_select
                    $min = (int) min(array_column($pertandinganTemp, 'id_select'));
                    foreach ($pertandinganTemp as $value) {
                        $value->id_select = $min++;
                    }

                    // Replace array $pertandingan dengan $pertandinganTemp
                    foreach ($pertandinganTemp as $value) {
                        $pertandingan[$value->id_select] = $value;
                    }

                    // Kosongkan temp
                    $pertandinganTemp = [];

                    $vPertandingan->id_select = $kPertandingan;
                    array_push($pertandinganTemp, $vPertandingan);
                    $vPertandingan->id_paket = ++$idPaket;

                    // Kelompok terakhir yang akan diambil
                    $jumlahDiambil = 1;
                }
            }
        }

        return $pertandingan;
    }

    /**
     * Update urutan partai berdasarkan drag-drop dari halaman pengaturan urutan.
     * Parity dengan CI3: Jadwal_tanding::update_urutan_partai_tanding().
     *
     * Strategi 2-tahap: NULL-kan dulu id_pertandingan untuk menghindari
     * unique-key collision, lalu set ulang dengan nilai baru.
     *
     * @param int   $idJadwalTanding
     * @param array $detailIds     id_detail_jadwal_tanding[]
     * @param array $pertandinganIds id_pertandingan[]
     * @param array $nomorPartai   nomor_partai[]
     * @return bool
     */
    public function updateUrutanPartai($idJadwalTanding, array $detailIds, array $pertandinganIds, array $nomorPartai)
    {
        if (count($detailIds) !== count($pertandinganIds) || count($detailIds) !== count($nomorPartai)) {
            return false;
        }

        $this->db->transStart();
        $tabel = $this->db->table('detail_jadwal_tanding');

        // Tahap 1: NULL-kan id_pertandingan untuk hindari konflik unique key
        foreach ($detailIds as $id) {
            $tabel->where('id_detail_jadwal_tanding', (int) $id)
                ->update([
                    'id_pertandingan' => null,
                ]);
        }

        // Tahap 2: Set ulang dengan nilai baru
        foreach ($detailIds as $i => $id) {
            $tabel->where('id_detail_jadwal_tanding', (int) $id)
                ->update([
                    'id_pertandingan' => empty($pertandinganIds[$i]) ? null : (int) $pertandinganIds[$i],
                    'nomor_partai'    => (int) $nomorPartai[$i],
                ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }
}
