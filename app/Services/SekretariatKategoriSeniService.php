<?php

namespace App\Services;

use App\Models\KompetisiSeniModel;
use App\Models\SubKategoriSeniModel;

class SekretariatKategoriSeniService
{
    public function listKategori(): array
    {
        return $this->kategoriBaseQuery()->orderBy('ku.min_umur', 'ASC')->orderBy('sks.jenis_seni', 'ASC')->orderBy('sks.nama_seni', 'ASC')->get()->getResult();
    }

    public function getKategori(int $id): ?object
    {
        return $this->kategoriBaseQuery()->where('sks.id_sub_kategori_seni', $id)->get()->getRow();
    }

    public function updateSistemPenampilan(int $id, string $sistem): bool
    {
        if (! in_array($sistem, ['pool', 'battle'], true)) {
            throw new \RuntimeException('Sistem penampilan tidak valid.');
        }

        $current = (new SubKategoriSeniModel())->find($id);
        if ($current === null) {
            throw new \RuntimeException('Kategori seni tidak ditemukan.');
        }

        if ((string) ($current->sistem_penampilan ?? '') !== $sistem && $this->hasRelatedPenampilanData($id)) {
            throw new \RuntimeException('Sistem penampilan tidak dapat diubah karena kategori ini sudah memiliki data battle, jadwal, atau penampilan.');
        }

        return (new SubKategoriSeniModel())->update($id, ['sistem_penampilan' => $sistem]);
    }

    public function hasRelatedPenampilanData(int $idSubKategori): bool
    {
        $db = db_connect();
        $battle = $db->table('battle_seni bs')->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = bs.id_kompetisi_seni')->where('ks.id_sub_kategori_seni', $idSubKategori)->countAllResults();
        $jadwal = $db->table('detail_jadwal_seni djs')->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni', 'left')->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni', 'left')->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = bs.id_kompetisi_seni OR ks.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')->where('ks.id_sub_kategori_seni', $idSubKategori)->countAllResults();
        $penampilan = $db->table('penampilan_seni ps')->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')->where('ks.id_sub_kategori_seni', $idSubKategori)->countAllResults();

        return $battle > 0 || $jadwal > 0 || $penampilan > 0;
    }

    public function listPool(): array
    {
        return $this->poolBaseQuery()->orderBy('ku.min_umur', 'ASC')->orderBy('sks.jenis_seni', 'ASC')->orderBy('sks.nama_seni', 'ASC')->orderBy('ks.nomor_pool', 'ASC')->get()->getResult();
    }

    public function getPool(int $id): ?object
    {
        return $this->poolBaseQuery()->where('ks.id_kompetisi_seni', $id)->get()->getRow();
    }

    public function listPoolByKategori(int $idSubKategori): array
    {
        return $this->poolBaseQuery()
            ->where('ks.id_sub_kategori_seni', $idSubKategori)
            ->orderBy('ks.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    public function listKelompokByKategori(int $idSubKategori): array
    {
        return db_connect()->table('kelompok_peserta_seni kps')
            ->select('kps.*, k.nama_kontingen, ks.nomor_pool, sks.nama_seni, sks.jenis_seni, sks.sistem_penampilan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni', false)
            ->select('(SELECT status_pembayaran FROM pembayaran pb WHERE pb.id_pembayaran = kps.id_pembayaran) AS status_pembayaran', false)
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('sks.id_sub_kategori_seni', $idSubKategori)
            ->orderBy('ks.nomor_pool', 'ASC')
            ->orderBy('kps.nomor_undi', 'ASC')
            ->get()->getResult();
    }

    public function updatePool(int $id, array $data): bool
    {
        return (new KompetisiSeniModel())->update($id, [
            'nomor_pool' => $data['nomor_pool'] ?? null,
            'max_peserta' => (int) ($data['max_peserta'] ?? 0),
            'perhitungan_medali' => (int) ($data['perhitungan_medali'] ?? 0),
            'keterangan' => $data['keterangan'] ?? '',
        ]);
    }

    public function beriNomorUndi(int $idKompetisi): bool
    {
        $rows = db_connect()->table('kelompok_peserta_seni')->where('id_kompetisi_seni', $idKompetisi)->orderBy('id_kelompok_peserta_seni', 'ASC')->get()->getResult();
        foreach ($rows as $index => $row) {
            db_connect()->table('kelompok_peserta_seni')->where('id_kelompok_peserta_seni', $row->id_kelompok_peserta_seni)->update(['nomor_undi' => $index + 1]);
        }

        return true;
    }

    public function listBattle(): array
    {
        return $this->battleBaseQuery()->orderBy('ku.min_umur', 'ASC')->orderBy('sks.nama_seni', 'ASC')->orderBy('ks.nomor_pool', 'ASC')->orderBy('bs.nomor_battle', 'ASC')->get()->getResult();
    }

    public function listBattleUrutanPoin(): array
    {
        return $this->battleBaseQuery()
            ->where('bs.jenis_kemenangan !=', 'BYE')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->orderBy('ks.nomor_pool', 'ASC')
            ->orderBy('bs.nomor_battle', 'ASC')
            ->get()->getResult();
    }

    public function listBattleByPool(int $idKompetisi): array
    {
        return $this->battleBaseQuery()
            ->where('bs.id_kompetisi_seni', $idKompetisi)
            ->where('bs.jenis_kemenangan !=', 'BYE')
            ->orderBy('bs.nomor_battle', 'ASC')
            ->get()->getResult();
    }

    public function listPenampilanByPool(int $idKompetisi, string $babak): array
    {
        return db_connect()->table('detail_jadwal_seni djs')
            ->select('djs.*, ps.id_penampilan_seni, ps.id_kelompok_peserta_seni, ps.nilai_akhir, ps.waktu_tampil, ps.status_penampilan, ps.catatan_nilai_sama, ps.babak AS babak_pool, kps.nomor_undi, k.nama_kontingen, ks.nomor_pool, sks.nama_seni, sks.jenis_seni, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni psn JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE psn.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni', false)
            ->select('(SELECT g.nama_gelanggang FROM gelanggang g JOIN jadwal_seni js ON js.id_gelanggang = g.id_gelanggang WHERE js.id_jadwal_seni = djs.id_jadwal_seni) AS nama_gelanggang', false)
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('ks.id_kompetisi_seni', $idKompetisi)
            ->where('ps.babak', $babak)
            ->orderBy('djs.nomor_partai', 'ASC')
            ->get()->getResult();
    }

    public function listPenampilanUrutanPoinPool(): array
    {
        return db_connect()->table('detail_jadwal_seni djs')
            ->select('djs.*, ps.id_penampilan_seni, ps.id_kelompok_peserta_seni, ps.nilai_akhir, ps.waktu_tampil, ps.status_penampilan, ps.catatan_nilai_sama, ps.babak AS babak_pool, kps.nomor_undi, k.nama_kontingen, ks.nomor_pool, sks.nama_seni, sks.jenis_seni, ku.nama_kategori_usia, ku.jenis_kelamin, pms.jenis_medali')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni psn JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE psn.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni', false)
            ->select('(SELECT g.nama_gelanggang FROM gelanggang g JOIN jadwal_seni js ON js.id_gelanggang = g.id_gelanggang WHERE js.id_jadwal_seni = djs.id_jadwal_seni) AS nama_gelanggang', false)
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('perolehan_medali_seni pms', 'pms.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni', 'left')
            ->where('sks.sistem_penampilan', 'pool')
            ->where('ps.babak IS NOT NULL', null, false)
            ->where('ps.status_penampilan', 'sudah_tampil')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->orderBy('ks.nomor_pool', 'ASC')
            ->orderBy('ps.babak', 'ASC')
            ->orderBy('djs.nomor_partai', 'ASC')
            ->get()->getResult();
    }

    public function getBattle(int $id): ?object
    {
        return $this->battleBaseQuery()->where('bs.id_battle_seni', $id)->get()->getRow();
    }

    public function listKelompokByPool(int $idKompetisi): array
    {
        return db_connect()->table('kelompok_peserta_seni kps')
            ->select('kps.*, k.nama_kontingen')
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni', false)
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->where('kps.id_kompetisi_seni', $idKompetisi)
            ->orderBy('kps.nomor_undi', 'ASC')
            ->get()->getResult();
    }

    public function kuotaPrestasi(): array
    {
        $rows = array_values(array_filter($this->listKategori(), static fn ($row): bool => ($row->jenis_perlombaan ?? '') === 'prestasi'));

        return ['tersedia' => $this->filterKuota($rows, '<'), 'penuh' => $this->filterKuota($rows, '='), 'kelebihan' => $this->filterKuota($rows, '>'), 'rows' => $rows];
    }

    private function kategoriBaseQuery()
    {
        return db_connect()->table('sub_kategori_seni sks')
            ->select('sks.*, sks.keterangan AS keterangan, kl.jenis_perlombaan, kl.kuota_peserta, ku.nama_kategori_usia, ku.jenis_kelamin, ku.min_umur, ku.max_umur')
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni) AS jumlah_kelompok_peserta_seni', false)
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN pembayaran pb ON pb.id_pembayaran = kps.id_pembayaran JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni AND pb.status_pembayaran = "lunas") AS jumlah_kelompok_peserta_seni_lunas', false)
            ->select('(SELECT COALESCE(SUM(ks.max_peserta), 0) FROM kompetisi_seni ks WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni) AS total_kapasitas_kelompok_peserta_seni', false)
            ->select('(SELECT COUNT(*) FROM kompetisi_seni ks WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni) AS jumlah_pool', false)
            ->select('(SELECT COUNT(*) FROM battle_seni bs JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = bs.id_kompetisi_seni WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni AND bs.jenis_kemenangan != "BYE") AS jumlah_partai_battle_seni', false)
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia');
    }

    private function poolBaseQuery()
    {
        return db_connect()->table('kompetisi_seni ks')
            ->select('ks.*, ks.keterangan AS keterangan_kompetisi_seni, sks.nama_seni, sks.jenis_seni, sks.jumlah_peserta, sks.sistem_penampilan, sks.juara_tiga_bersama, kl.jenis_perlombaan, kl.kuota_peserta, kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps WHERE kps.id_kompetisi_seni = ks.id_kompetisi_seni) AS jumlah_kelompok_peserta_seni', false)
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN pembayaran pb ON pb.id_pembayaran = kps.id_pembayaran WHERE kps.id_kompetisi_seni = ks.id_kompetisi_seni AND pb.status_pembayaran = "lunas") AS jumlah_kelompok_peserta_seni_lunas', false)
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia');
    }

    private function battleBaseQuery()
    {
        return db_connect()->table('battle_seni bs')
            ->select('bs.*, bs.keterangan AS keterangan_battle_seni, ks.nomor_pool, sks.nama_seni, sks.jenis_seni, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT ps.id_kelompok_peserta_seni FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS id_kelompok_peserta_seni_merah', false)
            ->select('(SELECT ps.id_penampilan_seni FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS id_penampilan_seni_merah', false)
            ->select('(SELECT ps.nilai_akhir FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS nilai_akhir_merah', false)
            ->select('(SELECT ps.waktu_tampil FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS waktu_tampil_merah', false)
            ->select('(SELECT ps.status_penampilan FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS status_penampilan_seni_merah', false)
            ->select('(SELECT ps.catatan_nilai_sama FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS catatan_nilai_sama_merah', false)
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM penampilan_seni ps JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni JOIN peserta_seni psn ON psn.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS anggota_kelompok_peserta_seni_merah', false)
            ->select('(SELECT k.nama_kontingen FROM penampilan_seni ps JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni JOIN kontingen k ON k.id_kontingen = kps.id_kontingen WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_merah) AS nama_kontingen_merah', false)
            ->select('(SELECT ps.id_kelompok_peserta_seni FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS id_kelompok_peserta_seni_biru', false)
            ->select('(SELECT ps.id_penampilan_seni FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS id_penampilan_seni_biru', false)
            ->select('(SELECT ps.nilai_akhir FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS nilai_akhir_biru', false)
            ->select('(SELECT ps.waktu_tampil FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS waktu_tampil_biru', false)
            ->select('(SELECT ps.status_penampilan FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS status_penampilan_seni_biru', false)
            ->select('(SELECT ps.catatan_nilai_sama FROM penampilan_seni ps WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS catatan_nilai_sama_biru', false)
            ->select('(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM penampilan_seni ps JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni JOIN peserta_seni psn ON psn.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni JOIN pendaftar p ON p.id_pendaftar = psn.id_pendaftar WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS anggota_kelompok_peserta_seni_biru', false)
            ->select('(SELECT k.nama_kontingen FROM penampilan_seni ps JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni JOIN kontingen k ON k.id_kontingen = kps.id_kontingen WHERE ps.id_penampilan_seni = bs.id_penampilan_seni_biru) AS nama_kontingen_biru', false)
            ->select('(SELECT djs.nomor_partai FROM detail_jadwal_seni djs JOIN battle_seni bsl ON djs.id_battle_seni = bsl.id_battle_seni WHERE bsl.id_kompetisi_seni = ks.id_kompetisi_seni AND bsl.nomor_battle_selanjutnya = bs.nomor_battle AND bsl.nomor_battle % 2 = 1) AS calon_anggota_kelompok_peserta_seni_biru', false)
            ->select('(SELECT g2.nama_gelanggang FROM detail_jadwal_seni djs2 JOIN jadwal_seni js2 ON js2.id_jadwal_seni = djs2.id_jadwal_seni JOIN gelanggang g2 ON g2.id_gelanggang = js2.id_gelanggang JOIN battle_seni bsl2 ON djs2.id_battle_seni = bsl2.id_battle_seni WHERE bsl2.id_kompetisi_seni = ks.id_kompetisi_seni AND bsl2.nomor_battle_selanjutnya = bs.nomor_battle AND bsl2.nomor_battle % 2 = 1 LIMIT 1) AS gelanggang_calon_anggota_kelompok_peserta_seni_biru', false)
            ->select('(SELECT djs.nomor_partai FROM detail_jadwal_seni djs JOIN battle_seni bsl ON djs.id_battle_seni = bsl.id_battle_seni WHERE bsl.id_kompetisi_seni = ks.id_kompetisi_seni AND bsl.nomor_battle_selanjutnya = bs.nomor_battle AND bsl.nomor_battle % 2 = 0) AS calon_anggota_kelompok_peserta_seni_merah', false)
            ->select('(SELECT g2.nama_gelanggang FROM detail_jadwal_seni djs2 JOIN jadwal_seni js2 ON js2.id_jadwal_seni = djs2.id_jadwal_seni JOIN gelanggang g2 ON g2.id_gelanggang = js2.id_gelanggang JOIN battle_seni bsl2 ON djs2.id_battle_seni = bsl2.id_battle_seni WHERE bsl2.id_kompetisi_seni = ks.id_kompetisi_seni AND bsl2.nomor_battle_selanjutnya = bs.nomor_battle AND bsl2.nomor_battle % 2 = 0 LIMIT 1) AS gelanggang_calon_anggota_kelompok_peserta_seni_merah', false)
            ->select('(SELECT nomor_partai FROM detail_jadwal_seni djs WHERE djs.id_battle_seni = bs.id_battle_seni) AS nomor_partai', false)
            ->select('(SELECT djs.id_detail_jadwal_seni FROM detail_jadwal_seni djs WHERE djs.id_battle_seni = bs.id_battle_seni) AS id_detail_jadwal_seni', false)
            ->select('(SELECT djs.id_jadwal_seni FROM detail_jadwal_seni djs WHERE djs.id_battle_seni = bs.id_battle_seni) AS id_jadwal_seni', false)
            ->select('(SELECT g.id_gelanggang FROM gelanggang g JOIN jadwal_seni js ON g.id_gelanggang = js.id_gelanggang JOIN detail_jadwal_seni djs ON js.id_jadwal_seni = djs.id_jadwal_seni WHERE djs.id_battle_seni = bs.id_battle_seni) AS id_gelanggang', false)
            ->select('(SELECT g.nama_gelanggang FROM gelanggang g JOIN jadwal_seni js ON g.id_gelanggang = js.id_gelanggang JOIN detail_jadwal_seni djs ON js.id_jadwal_seni = djs.id_jadwal_seni WHERE djs.id_battle_seni = bs.id_battle_seni) AS nama_gelanggang', false)
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = bs.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia');
    }

    private function filterKuota(array $rows, string $operator): array
    {
        return array_values(array_filter($rows, static function ($row) use ($operator): bool {
            $jumlah = (int) ($row->jumlah_kelompok_peserta_seni ?? 0);
            $max = (int) ($row->total_kapasitas_kelompok_peserta_seni ?? 0);
            return $operator === '<' ? $jumlah < $max : ($operator === '=' ? $jumlah === $max : $jumlah > $max);
        }));
    }
}
