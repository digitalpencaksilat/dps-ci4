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
        'nomor_partai_awal',
        'nomor_partai_akhir',
        'jumlah_partai',
        'pdf_path',
    ];
    protected $useTimestamps = true;

    public function get_all()
    {
        return $this->db->table($this->table . ' jt')
            ->select("
                jt.*,
                g.nama_gelanggang,
                g.nomor_gelanggang,
                (SELECT nomor_partai FROM detail_jadwal_tanding WHERE detail_jadwal_tanding.id_jadwal_tanding = jt.id_jadwal_tanding ORDER BY nomor_partai ASC LIMIT 1) as partai_awal,
                (SELECT nomor_partai FROM detail_jadwal_tanding WHERE detail_jadwal_tanding.id_jadwal_tanding = jt.id_jadwal_tanding ORDER BY nomor_partai DESC LIMIT 1) as partai_akhir,
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
                p.id_atlet_biru
            ')
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
}
