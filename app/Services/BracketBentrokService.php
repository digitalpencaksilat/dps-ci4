<?php

namespace App\Services;

class BracketBentrokService
{
    /**
     * Ambil data bracket bentrok (GROUP BY HAVING COUNT > 1)
     *
     * @param int $idJadwalTanding
     * @return array
     */
    public function ambilDataBracketBentrok(int $idJadwalTanding): array
    {
        $db = db_connect();

        $query = "
            SELECT
                kt.id_kompetisi_tanding,
                p.nomor_pertandingan AS nomor_pertandingan_tujuan,
                p_lama.nomor_pertandingan_selanjutnya,
                (p_lama.nomor_pertandingan % 2) AS sisi,
                COUNT(*) AS jumlah,
                GROUP_CONCAT(DISTINCT p_lama.nomor_pertandingan ORDER BY p_lama.nomor_pertandingan SEPARATOR ', ') AS daftar_partai_sumber
            FROM detail_jadwal_tanding d
            JOIN pertandingan p ON p.id_pertandingan = d.id_pertandingan
            JOIN kompetisi_tanding kt ON kt.id_kompetisi_tanding = p.id_kompetisi_tanding
            JOIN pertandingan p_lama
                ON p_lama.id_kompetisi_tanding = kt.id_kompetisi_tanding
                AND p_lama.nomor_pertandingan_selanjutnya = p.nomor_pertandingan
            WHERE d.id_jadwal_tanding = ?
            GROUP BY kt.id_kompetisi_tanding, p.nomor_pertandingan, p_lama.nomor_pertandingan_selanjutnya, (p_lama.nomor_pertandingan % 2)
            HAVING COUNT(*) > 1
        ";

        return $db->query($query, [$idJadwalTanding])->getResult();
    }

    /**
     * Validasi apakah jadwal tanding siap ditampilkan (tidak ada bracket bentrok)
     *
     * @param int $idJadwalTanding
     * @return array ['status' => bool, 'message' => array of strings]
     */
    public function validasiJadwalSiapDitampilkan(int $idJadwalTanding): array
    {
        $hasil = $this->ambilDataBracketBentrok($idJadwalTanding);

        if (empty($hasil)) {
            return ['status' => true, 'message' => []];
        }

        $pesan = [];
        foreach ($hasil as $row) {
            $sisi = ((int) $row->sisi === 1) ? 'BIRU/BLUE' : 'MERAH/RED';
            $pesan[] = '❌ Jadwal tidak bisa ditampilkan karena struktur bracket ganda terdeteksi. '
                . 'Partai tujuan ' . $row->nomor_pertandingan_tujuan
                . ' pada sisi ' . $sisi
                . ' menerima ' . $row->jumlah . ' feeder sekaligus'
                . ' (partai sumber: ' . $row->daftar_partai_sumber . '). '
                . 'Periksa data hasil import Excel atau bersihkan data pertandingan ganda di database.';
        }

        return ['status' => false, 'message' => $pesan];
    }

    /**
     * Perbaiki bracket bentrok secara otomatis
     *
     * @param int  $idJadwalTanding
     * @param bool $skipTransaction Jika true, tidak start/commit transaction sendiri (dipanggil dari dalam transaction)
     * @return array ['status' => bool, 'message' => string with <br> separator]
     */
    public function perbaikiBracketBentrokOtomatis(int $idJadwalTanding, bool $skipTransaction = false): array
    {
        $bentrok = $this->ambilDataBracketBentrok($idJadwalTanding);

        if (empty($bentrok)) {
            return ['status' => true, 'message' => 'Tidak ada bracket bentrok yang perlu diperbaiki.'];
        }

        $db = db_connect();

        if (!$skipTransaction) {
            $db->transBegin();
        }

        $berhasil = 0;
        $dilewati = [];
        $log = [];

        foreach ($bentrok as $group) {
            $rows = $db->table('pertandingan p')
                ->select('p.id_pertandingan, p.id_kompetisi_tanding, p.nomor_pertandingan, p.nomor_pertandingan_selanjutnya, d.nomor_partai')
                ->join('detail_jadwal_tanding d', 'd.id_pertandingan = p.id_pertandingan', 'left')
                ->where('p.id_kompetisi_tanding', $group->id_kompetisi_tanding)
                ->where('p.nomor_pertandingan_selanjutnya', $group->nomor_pertandingan_selanjutnya)
                ->where('(p.nomor_pertandingan % 2) = ' . (int) $group->sisi, null, false)
                ->orderBy('d.nomor_partai', 'ASC')
                ->orderBy('p.id_pertandingan', 'ASC')
                ->get()
                ->getResult();

            // Record pertama dipertahankan, sisanya diperbaiki bila aman.
            array_shift($rows);

            foreach ($rows as $row) {
                $nomorBaru = ((int) $row->nomor_pertandingan % 2 === 0)
                    ? ((int) $row->nomor_pertandingan - 1)
                    : ((int) $row->nomor_pertandingan + 1);

                // Cek apakah nomor baru sudah terpakai oleh pertandingan lain
                $nomorSudahTerpakai = $db->table('pertandingan')
                    ->where('id_kompetisi_tanding', $row->id_kompetisi_tanding)
                    ->where('nomor_pertandingan', $nomorBaru)
                    ->where('id_pertandingan !=', $row->id_pertandingan)
                    ->countAllResults();

                if ($nomorSudahTerpakai > 0) {
                    $dilewati[] = 'Partai ' . $row->nomor_partai . ' dilewati karena kandidat nomor internal ' . $nomorBaru . ' sudah dipakai.';
                    continue;
                }

                $db->table('pertandingan')
                    ->where('id_pertandingan', $row->id_pertandingan)
                    ->update(['nomor_pertandingan' => $nomorBaru]);

                if ($db->affectedRows() >= 0) {
                    $berhasil++;
                    $log[] = 'Partai ' . $row->nomor_partai . ': nomor internal ' . $row->nomor_pertandingan . ' diubah menjadi ' . $nomorBaru . '.';
                }
            }
        }

        // Re-detect bentrok setelah perbaikan
        $sisaBentrok = $this->ambilDataBracketBentrok($idJadwalTanding);

        if (!$skipTransaction) {
            if ($db->transStatus() === false) {
                $db->transRollback();
                return ['status' => false, 'message' => 'Gagal memperbaiki bracket bentrok. Tidak ada data yang diubah.'];
            }
            $db->transCommit();
        }

        $message = 'Perbaikan bracket selesai. Data diubah: ' . $berhasil . '. ';
        if (!empty($log)) {
            $message .= implode('<br>', $log) . '<br>';
        }
        if (!empty($dilewati)) {
            $message .= 'Catatan: ' . implode('<br>', $dilewati) . '<br>';
        }
        if (!empty($sisaBentrok)) {
            $message .= 'Masih ada ' . count($sisaBentrok) . ' grup bentrok yang perlu dicek manual.';
            return ['status' => false, 'message' => $message];
        }

        return ['status' => true, 'message' => $message . 'Tidak ada bracket bentrok tersisa.'];
    }
}
