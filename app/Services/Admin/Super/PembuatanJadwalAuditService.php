<?php

namespace App\Services\Admin\Super;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class PembuatanJadwalAuditService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function getDashboardAudit(): array
    {
        $tandingBelum = $this->getPertandinganBelumDijadwalkan();
        $byeTerjadwal = $this->getPertandinganByeTerjadwal();
        $poolBelum = $this->getSeniPoolBelumDijadwalkan();
        $battleBelum = $this->getSeniBattleBelumDijadwalkan();
        $mismatch = $this->getPenampilanSeniMismatch();

        return [
            'count_pertandingan_belum_dijadwalkan' => count($tandingBelum),
            'count_pertandingan_bye_terjadwal' => count($byeTerjadwal),
            'count_seni_pool_belum_dijadwalkan' => count($poolBelum),
            'count_seni_battle_belum_dijadwalkan' => count($battleBelum),
            'count_mismatch_sistem_penampilan' => count($mismatch),
            'data_pertandingan_belum_dijadwalkan' => $tandingBelum,
            'data_pertandingan_bye_terjadwal' => $byeTerjadwal,
            'data_penampilan_seni_pool_belum_dijadwalkan' => $poolBelum,
            'data_battle_seni_belum_dijadwalkan' => $battleBelum,
            'data_penampilan_seni_tidak_sesuai_sistem_penampilan' => $mismatch,
        ];
    }

    private function getPertandinganBelumDijadwalkan(): array
    {
        return $this->db->table('pertandingan p')
            ->select('p.id_pertandingan, p.id_kompetisi_tanding, p.babak, p.nomor_pertandingan, p.nomor_pertandingan_selanjutnya, p.jenis_kemenangan')
            ->select('kom.id_kelas_tanding, ku.nama_kategori_usia, ku.jenis_kelamin, kt.label AS nama_kelas')
            ->select('(SELECT djt.nomor_partai FROM detail_jadwal_tanding djt WHERE djt.id_pertandingan = p.id_pertandingan LIMIT 1) AS nomor_partai', false)
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = p.id_kompetisi_tanding', 'left')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->where('p.jenis_kemenangan !=', 'BYE')
            // CI3 mengecek 'nomor_partai IS NULL', tapi di schema CI4 nomor_partai disimpan di detail_jadwal_tanding.
            ->where('NOT EXISTS (SELECT 1 FROM detail_jadwal_tanding djt WHERE djt.id_pertandingan = p.id_pertandingan)', null, false)
            ->orderBy('ku.nama_kategori_usia', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->orderBy('p.nomor_pertandingan', 'ASC')
            ->get()
            ->getResult();
    }

    private function getPertandinganByeTerjadwal(): array
    {
        return $this->db->table('pertandingan p')
            ->select('p.id_pertandingan, p.id_kompetisi_tanding, p.babak, p.nomor_pertandingan, p.jenis_kemenangan')
            ->select('kom.id_kelas_tanding, ku.nama_kategori_usia, ku.jenis_kelamin, kt.label AS nama_kelas')
            ->select('(SELECT djt.nomor_partai FROM detail_jadwal_tanding djt WHERE djt.id_pertandingan = p.id_pertandingan LIMIT 1) AS nomor_partai', false)
            ->select('(SELECT g.nama_gelanggang FROM gelanggang g JOIN jadwal_tanding jt ON jt.id_gelanggang = g.id_gelanggang JOIN detail_jadwal_tanding djt ON djt.id_jadwal_tanding = jt.id_jadwal_tanding WHERE djt.id_pertandingan = p.id_pertandingan LIMIT 1) AS nama_gelanggang', false)
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = p.id_kompetisi_tanding', 'left')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->where('p.jenis_kemenangan', 'BYE')
            // CI3 mengecek pertandingan.nomor_partai, tapi schema aktual menyimpan nomor partai di detail_jadwal_tanding.
            ->where('EXISTS (SELECT 1 FROM detail_jadwal_tanding djt WHERE djt.id_pertandingan = p.id_pertandingan)', null, false)
            ->orderBy('nomor_partai', 'ASC')
            ->get()
            ->getResult();
    }

    private function getSeniPoolBelumDijadwalkan(): array
    {
        return $this->db->table('kelompok_peserta_seni kps')
            ->select('kps.id_kelompok_peserta_seni, kps.id_kompetisi_seni, kps.nomor_undi, kps.keterangan')
            ->select('kom.nomor_pool, sks.nama_seni, sks.jenis_seni, sks.sistem_penampilan, ku.nama_kategori_usia, ku.jenis_kelamin, k.nama_kontingen')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni psn JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE psn.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni', false)
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->where('sks.sistem_penampilan', 'pool')
            ->where('(SELECT COUNT(*) FROM detail_jadwal_seni djs JOIN penampilan_seni ps ON djs.id_penampilan_seni = ps.id_penampilan_seni WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) = 0', null, false)
            ->orderBy('ku.nama_kategori_usia', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->orderBy('kps.nomor_undi', 'ASC')
            ->get()
            ->getResult();
    }

    private function getSeniBattleBelumDijadwalkan(): array
    {
        return $this->db->table('battle_seni bs')
            ->select('bs.id_battle_seni, bs.id_kompetisi_seni, bs.babak, bs.nomor_battle, bs.nomor_battle_selanjutnya, bs.jenis_kemenangan')
            ->select('ks.nomor_pool, sks.nama_seni, sks.jenis_seni, sks.sistem_penampilan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT k.nama_kontingen FROM penampilan_seni ps JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni JOIN kontingen k ON k.id_kontingen = kps.id_kontingen WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS nama_kontingen_merah', false)
            ->select('(SELECT k.nama_kontingen FROM penampilan_seni ps JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni JOIN kontingen k ON k.id_kontingen = kps.id_kontingen WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS nama_kontingen_biru', false)
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = bs.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->where('sks.sistem_penampilan', 'battle')
            ->where('bs.jenis_kemenangan !=', 'BYE')
            ->where('NOT EXISTS (SELECT 1 FROM detail_jadwal_seni djs WHERE djs.id_battle_seni = bs.id_battle_seni)', null, false)
            ->orderBy('ku.nama_kategori_usia', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->orderBy('bs.nomor_battle', 'ASC')
            ->get()
            ->getResult();
    }

    private function getPenampilanSeniMismatch(): array
    {
        return $this->db->table('detail_jadwal_seni djs')
            ->select('djs.id_detail_jadwal_seni, djs.id_jadwal_seni, djs.id_battle_seni, djs.id_penampilan_seni, djs.nomor_partai')
            ->select('js.tanggal, js.jam_mulai, js.jam_selesai, g.nama_gelanggang')
            ->select('bs.babak AS babak_battle, sksb.sistem_penampilan AS sistem_penampilan_battle, sksb.nama_seni AS nama_seni_battle, kub.nama_kategori_usia AS nama_kategori_usia_battle, kub.jenis_kelamin AS jenis_kelamin_battle')
            ->select('ps.babak AS babak_pool, sksp.sistem_penampilan AS sistem_penampilan_pool, sksp.nama_seni AS nama_seni_pool, kup.nama_kategori_usia AS nama_kategori_usia_pool, kup.jenis_kelamin AS jenis_kelamin_pool')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni', 'left')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang', 'left')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni', 'left')
            ->join('kompetisi_seni ksb', 'ksb.id_kompetisi_seni = bs.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sksb', 'sksb.id_sub_kategori_seni = ksb.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba klb', 'klb.id_kategori_lomba = sksb.id_kategori_lomba', 'left')
            ->join('kategori_usia kub', 'kub.id_kategori_usia = klb.id_kategori_usia', 'left')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni', 'left')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')
            ->join('kompetisi_seni ksp', 'ksp.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sksp', 'sksp.id_sub_kategori_seni = ksp.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba klp', 'klp.id_kategori_lomba = sksp.id_kategori_lomba', 'left')
            ->join('kategori_usia kup', 'kup.id_kategori_usia = klp.id_kategori_usia', 'left')
            ->groupStart()
                ->groupStart()
                    ->where('djs.id_battle_seni IS NOT NULL', null, false)
                    ->where('sksb.sistem_penampilan', 'pool')
                ->groupEnd()
                ->orGroupStart()
                    ->where('djs.id_penampilan_seni IS NOT NULL', null, false)
                    ->where('sksp.sistem_penampilan', 'battle')
                ->groupEnd()
            ->groupEnd()
            ->orderBy('djs.nomor_partai', 'ASC')
            ->get()
            ->getResult();
    }
}
