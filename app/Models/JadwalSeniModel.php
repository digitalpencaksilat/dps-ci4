<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalSeniModel extends Model
{
    protected $table      = 'jadwal_seni';
    protected $primaryKey = 'id_jadwal_seni';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id_gelanggang',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'keterangan',
        'nomor_partai_awal',
        'nomor_partai_akhir',
        'jumlah_penampilan',
        'pdf_path',
    ];
    protected $useTimestamps = false; // CI3 legacy table does not have created_at/updated_at

    public function get_all()
    {
        return $this->db->table($this->table . ' js')
            ->select("
                js.*,
                g.nama_gelanggang,
                g.nomor_gelanggang,
                (SELECT nomor_partai FROM detail_jadwal_seni WHERE detail_jadwal_seni.id_jadwal_seni = js.id_jadwal_seni ORDER BY nomor_partai ASC LIMIT 1) as partai_awal,
                (SELECT nomor_partai FROM detail_jadwal_seni WHERE detail_jadwal_seni.id_jadwal_seni = js.id_jadwal_seni ORDER BY nomor_partai DESC LIMIT 1) as partai_akhir,
                (SELECT COUNT(*) FROM detail_jadwal_seni WHERE id_jadwal_seni = js.id_jadwal_seni) as jumlah_penampilan
            ")
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang')
            ->orderBy('js.tanggal', 'asc')
            ->orderBy('js.jam_mulai', 'asc')
            ->get()
            ->getResult();
    }

    public function findWithGelanggang($id)
    {
        return $this->db->table($this->table . ' js')
            ->select('js.*, g.nama_gelanggang, g.nomor_gelanggang, js.keterangan as keterangan_jadwal')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang')
            ->where('js.id_jadwal_seni', $id)
            ->get()
            ->getRow();
    }

    public function get_detail_jadwal($id_jadwal_seni)
    {
        return $this->db->table('detail_jadwal_seni djs')
            ->select('
                djs.*,
                js.id_gelanggang,
                g.nama_gelanggang,
                kps.keterangan as keterangan_kelompok,
                kps.nomor_undi,
                k.nama_kontingen,
                sks.nama_seni,
                sks.jenis_seni,
                ku.nama_kategori_usia,
                ku.jenis_kelamin,
                ks.nomor_pool,
                ps.status_penampilan,
                ps.waktu_tampil,
                ps.nilai_akhir,
                ps.catatan_nilai_sama,
                ps.diskualifikasi,
                ps.babak as babak_pool,
                JSON_UNQUOTE(JSON_EXTRACT(ps.catatan_nilai_sama, "$.standar_deviasi")) as standar_deviasi,
                JSON_UNQUOTE(JSON_EXTRACT(ps.catatan_nilai_sama, "$.median_kebenaran")) as median_kebenaran,
                pms.jenis_medali as jenis_medali_pool,
                bs.nomor_battle,
                bs.nomor_battle_selanjutnya,
                bs.babak as babak_battle,
                bs.jenis_kemenangan as jenis_kemenangan_battle,
                bs.id_penampilan_seni_pemenang,
                bs.id_kompetisi_seni as id_kompetisi_seni_battle,
                ksb.nomor_pool as nomor_pool_battle,
                sksb.nama_seni as nama_seni_battle,
                sksb.jenis_seni as jenis_seni_battle,
                kub.nama_kategori_usia as nama_kategori_usia_battle,
                kub.jenis_kelamin as jenis_kelamin_battle
            ', false)
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni psn JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE psn.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) as anggota_kelompok_peserta_seni', false)
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM penampilan_seni psb JOIN kelompok_peserta_seni kpsb ON kpsb.id_kelompok_peserta_seni = psb.id_kelompok_peserta_seni JOIN peserta_seni psn ON psn.id_kelompok_peserta_seni = kpsb.id_kelompok_peserta_seni JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE psb.id_penampilan_seni = bs.id_penampilan_seni_biru) as anggota_kelompok_peserta_seni_biru', false)
            ->select('(SELECT k2.nama_kontingen FROM penampilan_seni psb JOIN kelompok_peserta_seni kpsb ON kpsb.id_kelompok_peserta_seni = psb.id_kelompok_peserta_seni JOIN kontingen k2 ON k2.id_kontingen = kpsb.id_kontingen WHERE psb.id_penampilan_seni = bs.id_penampilan_seni_biru) as nama_kontingen_biru', false)
            ->select('(SELECT psb.nilai_akhir FROM penampilan_seni psb WHERE psb.id_penampilan_seni = bs.id_penampilan_seni_biru) as nilai_akhir_biru', false)
            ->select('(SELECT psb.id_penampilan_seni FROM penampilan_seni psb WHERE psb.id_penampilan_seni = bs.id_penampilan_seni_biru) as id_penampilan_seni_biru', false)
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM penampilan_seni psm JOIN kelompok_peserta_seni kpsm ON kpsm.id_kelompok_peserta_seni = psm.id_kelompok_peserta_seni JOIN peserta_seni psn ON psn.id_kelompok_peserta_seni = kpsm.id_kelompok_peserta_seni JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE psm.id_penampilan_seni = bs.id_penampilan_seni_merah) as anggota_kelompok_peserta_seni_merah', false)
            ->select('(SELECT k2.nama_kontingen FROM penampilan_seni psm JOIN kelompok_peserta_seni kpsm ON kpsm.id_kelompok_peserta_seni = psm.id_kelompok_peserta_seni JOIN kontingen k2 ON k2.id_kontingen = kpsm.id_kontingen WHERE psm.id_penampilan_seni = bs.id_penampilan_seni_merah) as nama_kontingen_merah', false)
            ->select('(SELECT psm.nilai_akhir FROM penampilan_seni psm WHERE psm.id_penampilan_seni = bs.id_penampilan_seni_merah) as nilai_akhir_merah', false)
            ->select('(SELECT psm.id_penampilan_seni FROM penampilan_seni psm WHERE psm.id_penampilan_seni = bs.id_penampilan_seni_merah) as id_penampilan_seni_merah', false)
            ->select('(SELECT djs2.nomor_partai FROM detail_jadwal_seni djs2 JOIN battle_seni bs2 ON bs2.id_battle_seni = djs2.id_battle_seni WHERE bs2.id_kompetisi_seni = bs.id_kompetisi_seni AND bs2.nomor_battle_selanjutnya = bs.nomor_battle AND bs2.nomor_battle % 2 = 1 LIMIT 1) as calon_anggota_kelompok_peserta_seni_biru', false)
            ->select('(SELECT g2.nama_gelanggang FROM detail_jadwal_seni djs2 JOIN jadwal_seni js2 ON js2.id_jadwal_seni = djs2.id_jadwal_seni JOIN gelanggang g2 ON g2.id_gelanggang = js2.id_gelanggang JOIN battle_seni bs2 ON bs2.id_battle_seni = djs2.id_battle_seni WHERE bs2.id_kompetisi_seni = bs.id_kompetisi_seni AND bs2.nomor_battle_selanjutnya = bs.nomor_battle AND bs2.nomor_battle % 2 = 1 LIMIT 1) as gelanggang_calon_anggota_kelompok_peserta_seni_biru', false)
            ->select('(SELECT djs2.nomor_partai FROM detail_jadwal_seni djs2 JOIN battle_seni bs2 ON bs2.id_battle_seni = djs2.id_battle_seni WHERE bs2.id_kompetisi_seni = bs.id_kompetisi_seni AND bs2.nomor_battle_selanjutnya = bs.nomor_battle AND bs2.nomor_battle % 2 = 0 LIMIT 1) as calon_anggota_kelompok_peserta_seni_merah', false)
            ->select('(SELECT g2.nama_gelanggang FROM detail_jadwal_seni djs2 JOIN jadwal_seni js2 ON js2.id_jadwal_seni = djs2.id_jadwal_seni JOIN gelanggang g2 ON g2.id_gelanggang = js2.id_gelanggang JOIN battle_seni bs2 ON bs2.id_battle_seni = djs2.id_battle_seni WHERE bs2.id_kompetisi_seni = bs.id_kompetisi_seni AND bs2.nomor_battle_selanjutnya = bs.nomor_battle AND bs2.nomor_battle % 2 = 0 LIMIT 1) as gelanggang_calon_anggota_kelompok_peserta_seni_merah', false)
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni', 'left')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba klb', 'klb.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = klb.id_kategori_usia', 'left')
            ->join('perolehan_medali_seni pms', 'pms.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni', 'left')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni', 'left')
            ->join('kompetisi_seni ksb', 'ksb.id_kompetisi_seni = bs.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sksb', 'sksb.id_sub_kategori_seni = ksb.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba klbb', 'klbb.id_kategori_lomba = sksb.id_kategori_lomba', 'left')
            ->join('kategori_usia kub', 'kub.id_kategori_usia = klbb.id_kategori_usia', 'left')
            ->where('djs.id_jadwal_seni', $id_jadwal_seni)
            ->orderBy('djs.nomor_partai', 'asc')
            ->get()
            ->getResult();
    }

    /**
     * Resequence nomor partai mulai dari $nomorPartaiBaruMulai.
     * Menggunakan ROW_NUMBER() agar nomor partai berurutan rapi tanpa duplikat,
     * tapi mengurutkan sesuai nomor_partai saat ini agar urutan logis tetap terjaga.
     *
     * Parity dengan CI3: Detail_jadwal_seni_model::resequence_nomor_partai().
     */
    public function resequenceNomorPartai($idJadwalSeni, $nomorPartaiBaruMulai)
    {
        $sql = "
            UPDATE detail_jadwal_seni t
            JOIN (
                SELECT id_detail_jadwal_seni,
                    ROW_NUMBER() OVER (ORDER BY nomor_partai) + ? - 1 AS new_nomor
                FROM detail_jadwal_seni
                WHERE id_jadwal_seni = ?
            ) AS subquery
            ON t.id_detail_jadwal_seni = subquery.id_detail_jadwal_seni
            SET t.nomor_partai = subquery.new_nomor
            WHERE t.id_jadwal_seni = ?
        ";

        return $this->db->query($sql, [
            (int) $nomorPartaiBaruMulai,
            (int) $idJadwalSeni,
            (int) $idJadwalSeni,
        ]);
    }

    /**
     * Update urutan partai berdasarkan drag-drop dari halaman pengaturan urutan.
     * Parity dengan CI3: Jadwal_seni::update_urutan_partai_seni().
     *
     * Strategi 2-tahap: NULL-kan dulu id_penampilan_seni & id_battle_seni untuk
     * menghindari unique-key collision, lalu set ulang dengan nilai baru.
     *
     * @param int   $idJadwalSeni
     * @param array $detailIds   id_detail_jadwal_seni[]
     * @param array $penampilanIds id_penampilan_seni[]
     * @param array $battleIds   id_battle_seni[]
     * @param array $nomorPartai nomor_partai[]
     * @return bool
     */
    public function updateUrutanPartai($idJadwalSeni, array $detailIds, array $penampilanIds, array $battleIds, array $nomorPartai)
    {
        if (count($detailIds) !== count($penampilanIds) || count($detailIds) !== count($nomorPartai)) {
            return false;
        }

        $this->db->transStart();
        $tabel = $this->db->table('detail_jadwal_seni');

        // Tahap 1: NULL-kan id_penampilan_seni & id_battle_seni untuk hindari konflik unique key
        foreach ($detailIds as $id) {
            $tabel->where('id_detail_jadwal_seni', (int) $id)
                ->update([
                    'id_penampilan_seni' => null,
                    'id_battle_seni'     => null,
                ]);
        }

        // Tahap 2: Set ulang dengan nilai baru
        foreach ($detailIds as $i => $id) {
            $tabel->where('id_detail_jadwal_seni', (int) $id)
                ->update([
                    'id_penampilan_seni' => empty($penampilanIds[$i]) ? null : (int) $penampilanIds[$i],
                    'id_battle_seni'     => empty($battleIds[$i]) ? null : (int) $battleIds[$i],
                    'nomor_partai'       => (int) $nomorPartai[$i],
                ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }
}
