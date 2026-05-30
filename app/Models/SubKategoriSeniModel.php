<?php

namespace App\Models;

use CodeIgniter\Model;

class SubKategoriSeniModel extends Model
{
    protected $table = 'sub_kategori_seni';
    protected $primaryKey = 'id_sub_kategori_seni';
    protected $returnType = 'object';
    protected $allowedFields = ['id_kategori_lomba', 'nama_seni', 'jenis_seni', 'jumlah_peserta', 'waktu', 'biaya_pendaftaran_dn', 'biaya_pendaftaran_ln', 'format_penilaian', 'sistem_penampilan', 'keterangan'];

    /**
     * Parity CI3: otomatis menambahkan pool kompetisi_seni bila pool belum ada atau kapasitas penuh.
     *
     * - Jika belum ada pool sama sekali: buat pool #1.
     * - Jika jumlah peserta (kelompok) >= total kapasitas: tambah pool baru (bisa lebih dari 1).
     */
    public function otomatis_menambahkan_pool(int $id_sub_kategori_seni, ?int $max_peserta = 4, string $keterangan = '')
    {
        $db = db_connect();

        $sub = $db->table('sub_kategori_seni')
            ->select('id_sub_kategori_seni')
            ->where('id_sub_kategori_seni', $id_sub_kategori_seni)
            ->get()
            ->getRow();

        if ($sub === null) {
            throw new \RuntimeException('Sub kategori seni tidak ditemukan.');
        }

        $poolRows = $db->table('kompetisi_seni')
            ->select('id_kompetisi_seni, nomor_pool, max_peserta')
            ->where('id_sub_kategori_seni', $id_sub_kategori_seni)
            ->orderBy('nomor_pool', 'ASC')
            ->get()
            ->getResult();

        if ($poolRows === []) {
            return $db->table('kompetisi_seni')->insert([
                'id_sub_kategori_seni' => $id_sub_kategori_seni,
                'nomor_pool' => 1,
                'max_peserta' => (int) ($max_peserta ?? 4),
                'perhitungan_medali' => 1,
                'keterangan' => $keterangan,
            ]);
        }

        $jumlahKelompok = (int) $db->table('kelompok_peserta_seni kps')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->where('ks.id_sub_kategori_seni', $id_sub_kategori_seni)
            ->countAllResults();

        $totalKapasitas = (int) $db->table('kompetisi_seni')
            ->selectSum('max_peserta', 'total')
            ->where('id_sub_kategori_seni', $id_sub_kategori_seni)
            ->get()
            ->getRow('total');

        if ($jumlahKelompok < $totalKapasitas) {
            return true;
        }

        $last = end($poolRows);
        $kapasitasPoolTerakhir = (int) (($last->max_peserta ?? 0) < 1 ? (int) ($max_peserta ?? 4) : (int) $last->max_peserta);
        $sisa = $jumlahKelompok - $totalKapasitas;

        $jumlahPoolTambah = $sisa < $kapasitasPoolTerakhir ? 1 : (int) ceil($sisa / $kapasitasPoolTerakhir);

        for ($i = 1; $i <= $jumlahPoolTambah; $i++) {
            $db->table('kompetisi_seni')->insert([
                'id_sub_kategori_seni' => $id_sub_kategori_seni,
                'nomor_pool' => ((int) ($last->nomor_pool ?? 0)) + $i,
                'max_peserta' => (int) ($last->max_peserta ?? ($max_peserta ?? 4)),
                'perhitungan_medali' => 1,
                'keterangan' => $keterangan,
            ]);
        }

        return true;
    }
}
