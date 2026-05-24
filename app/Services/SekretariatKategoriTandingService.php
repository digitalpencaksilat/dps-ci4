<?php

namespace App\Services;

use App\Models\KompetisiTandingModel;
use App\Models\PertandinganModel;

class SekretariatKategoriTandingService
{
    public function listKelas(): array
    {
        return $this->kelasBaseQuery()
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->get()->getResult();
    }

    public function getKelas(int $id): ?object
    {
        return $this->kelasBaseQuery()->where('kt.id_kelas_tanding', $id)->get()->getRow();
    }

    public function listPool(): array
    {
        return $this->poolBaseQuery()
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    public function getPool(int $id): ?object
    {
        return $this->poolBaseQuery()->where('kom.id_kompetisi_tanding', $id)->get()->getRow();
    }

    public function listPoolByKelas(int $idKelas): array
    {
        return $this->poolBaseQuery()
            ->where('kom.id_kelas_tanding', $idKelas)
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    public function listPesertaByKelas(int $idKelas): array
    {
        return db_connect()->table('peserta_tanding pt')
            ->select('pt.*, pt.keterangan AS keterangan_peserta_tanding, p.nama_pendaftar, p.berat_badan, p.tinggi_badan, p.tanggal_lahir, k.nama_kontingen, kom.nomor_pool, kt.label, kt.berat_minimal, kt.berat_maksimal, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT status_pembayaran FROM pembayaran pb WHERE pb.id_pembayaran = pt.id_pembayaran) AS status_pembayaran', false)
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen', 'left')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kt.id_kelas_tanding', $idKelas)
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()->getResult();
    }

    public function updatePool(int $id, array $data): bool
    {
        return (new KompetisiTandingModel())->update($id, [
            'max_peserta' => (int) ($data['max_peserta'] ?? 0),
            'perhitungan_medali' => (int) ($data['perhitungan_medali'] ?? 0),
            'nomor_pool' => $data['nomor_pool'] ?? null,
            'keterangan' => $data['keterangan'] ?? '',
        ]);
    }

    public function listPertandingan(): array
    {
        return $this->pertandinganBaseQuery()
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('kt.label', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->orderBy('p.nomor_pertandingan', 'ASC')
            ->get()->getResult();
    }

    public function getPertandingan(int $id): ?object
    {
        return $this->pertandinganBaseQuery()->where('p.id_pertandingan', $id)->get()->getRow();
    }

    public function createPertandingan(array $data): int
    {
        $model = new PertandinganModel();
        $model->insert($this->pertandinganPayload($data));

        return (int) $model->getInsertID();
    }

    public function updatePertandingan(int $id, array $data): bool
    {
        return (new PertandinganModel())->update($id, $this->pertandinganPayload($data));
    }

    public function deletePertandingan(int $id): bool
    {
        if (db_connect()->table('detail_jadwal_tanding')->where('id_pertandingan', $id)->countAllResults() > 0) {
            throw new \RuntimeException('Pertandingan sudah terhubung jadwal dan tidak dapat dihapus dari halaman ini.');
        }

        return (new PertandinganModel())->delete($id);
    }

    public function listPesertaByPool(int $idKompetisi): array
    {
        return db_connect()->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding, p.nama_pendaftar, k.nama_kontingen')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen', 'left')
            ->where('pt.id_kompetisi_tanding', $idKompetisi)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()->getResult();
    }

    public function kuotaPrestasi(): array
    {
        $rows = array_values(array_filter($this->listKelas(), static fn ($row): bool => ($row->jenis_perlombaan ?? '') === 'prestasi'));

        return ['tersedia' => $this->filterKuota($rows, '<'), 'penuh' => $this->filterKuota($rows, '='), 'kelebihan' => $this->filterKuota($rows, '>'), 'rows' => $rows];
    }

    private function kelasBaseQuery()
    {
        return db_connect()->table('kelas_tanding kt')
            ->select('kt.*, kl.jenis_perlombaan, kl.kuota_peserta, ku.nama_kategori_usia, ku.jenis_kelamin, ku.min_umur, ku.max_umur')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt JOIN kompetisi_tanding k ON k.id_kompetisi_tanding = pt.id_kompetisi_tanding WHERE k.id_kelas_tanding = kt.id_kelas_tanding) AS jumlah_peserta_tanding', false)
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt JOIN pembayaran pb ON pb.id_pembayaran = pt.id_pembayaran JOIN kompetisi_tanding k ON k.id_kompetisi_tanding = pt.id_kompetisi_tanding WHERE k.id_kelas_tanding = kt.id_kelas_tanding AND pb.status_pembayaran = "lunas") AS jumlah_peserta_tanding_lunas', false)
            ->select('((SELECT COUNT(*) FROM peserta_tanding pt JOIN kompetisi_tanding k ON k.id_kompetisi_tanding = pt.id_kompetisi_tanding WHERE k.id_kelas_tanding = kt.id_kelas_tanding) - ((SELECT COUNT(*) FROM kompetisi_tanding k WHERE k.id_kelas_tanding = kt.id_kelas_tanding) * kt.juara_tiga_bersama)) AS prediksi_jumlah_partai', false)
            ->select('(SELECT COALESCE(SUM(k.max_peserta), 0) FROM kompetisi_tanding k WHERE k.id_kelas_tanding = kt.id_kelas_tanding) AS max_peserta', false)
            ->select('(SELECT COUNT(*) FROM pertandingan p JOIN kompetisi_tanding k ON k.id_kompetisi_tanding = p.id_kompetisi_tanding WHERE k.id_kelas_tanding = kt.id_kelas_tanding AND p.jenis_kemenangan != "BYE") AS jumlah_partai_tanding', false)
            ->select('(SELECT COUNT(*) FROM pertandingan p JOIN kompetisi_tanding k ON k.id_kompetisi_tanding = p.id_kompetisi_tanding WHERE k.id_kelas_tanding = kt.id_kelas_tanding AND p.jenis_kemenangan != "BYE" AND p.id_pertandingan NOT IN (SELECT id_pertandingan FROM detail_jadwal_tanding WHERE id_pertandingan IS NOT NULL)) AS jumlah_partai_tanding_belum_dijadwalkan', false)
            ->select('(SELECT COUNT(*) FROM kompetisi_tanding k WHERE k.id_kelas_tanding = kt.id_kelas_tanding) AS jumlah_pool', false)
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kt.label !=', 'sisipan');
    }

    private function poolBaseQuery()
    {
        return db_connect()->table('kompetisi_tanding kom')
            ->select('kom.*, kom.keterangan AS keterangan, kt.label, kt.berat_minimal, kt.berat_maksimal, kt.juara_tiga_bersama, kl.jenis_perlombaan, kl.kuota_peserta, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) AS jumlah_peserta_tanding', false)
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt JOIN pembayaran pb ON pb.id_pembayaran = pt.id_pembayaran WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding AND pb.status_pembayaran = "lunas") AS jumlah_peserta_tanding_lunas', false)
            ->select('((SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) - kt.juara_tiga_bersama) AS prediksi_jumlah_partai', false)
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia');
    }

    private function pertandinganBaseQuery()
    {
        return db_connect()->table('pertandingan p')
            ->select('p.*, p.keterangan AS keterangan_pertandingan, kom.nomor_pool, kt.label, kt.berat_minimal, kt.berat_maksimal, kl.jenis_perlombaan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('CASE WHEN ku.jenis_kelamin = "putra" THEN "Men" WHEN ku.jenis_kelamin = "putri" THEN "Women" END AS gender', false)
            ->select('(SELECT pd.id_pendaftar FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_merah) AS id_pendaftar_merah', false)
            ->select('(SELECT pd.nama_pendaftar FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_merah) AS nama_atlet_merah', false)
            ->select('(SELECT TIMESTAMPDIFF(YEAR, pd.tanggal_lahir, CURDATE()) FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_merah) AS umur_merah', false)
            ->select('(SELECT pd.berat_badan FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_merah) AS berat_badan_merah', false)
            ->select('(SELECT pd.tinggi_badan FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_merah) AS tinggi_badan_merah', false)
            ->select('(SELECT k.nama_kontingen FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar JOIN kontingen k ON k.id_kontingen = pd.id_kontingen WHERE pt.id_peserta_tanding = p.id_atlet_merah) AS nama_kontingen_merah', false)
            ->select('(SELECT pd.id_pendaftar FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_biru) AS id_pendaftar_biru', false)
            ->select('(SELECT pd.nama_pendaftar FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_biru) AS nama_atlet_biru', false)
            ->select('(SELECT TIMESTAMPDIFF(YEAR, pd.tanggal_lahir, CURDATE()) FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_biru) AS umur_biru', false)
            ->select('(SELECT pd.berat_badan FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_biru) AS berat_badan_biru', false)
            ->select('(SELECT pd.tinggi_badan FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pt.id_peserta_tanding = p.id_atlet_biru) AS tinggi_badan_biru', false)
            ->select('(SELECT k.nama_kontingen FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar JOIN kontingen k ON k.id_kontingen = pd.id_kontingen WHERE pt.id_peserta_tanding = p.id_atlet_biru) AS nama_kontingen_biru', false)
            ->select('(SELECT IF(p.babak != "Perebutan Juara Tiga", (SELECT djt.nomor_partai FROM detail_jadwal_tanding djt JOIN pertandingan pl ON djt.id_pertandingan = pl.id_pertandingan WHERE pl.id_kompetisi_tanding = kom.id_kompetisi_tanding AND pl.nomor_pertandingan_selanjutnya = p.nomor_pertandingan AND pl.nomor_pertandingan % 2 = 0), (SELECT djt.nomor_partai FROM detail_jadwal_tanding djt JOIN pertandingan pl ON djt.id_pertandingan = pl.id_pertandingan WHERE pl.id_kompetisi_tanding = kom.id_kompetisi_tanding AND pl.babak = "Semi Final" AND pl.nomor_pertandingan % 2 = 0))) AS calon_atlet_merah', false)
            ->select('(SELECT IF(p.babak != "Perebutan Juara Tiga", (SELECT djt.nomor_partai FROM detail_jadwal_tanding djt JOIN pertandingan pl ON djt.id_pertandingan = pl.id_pertandingan WHERE pl.id_kompetisi_tanding = kom.id_kompetisi_tanding AND pl.nomor_pertandingan_selanjutnya = p.nomor_pertandingan AND pl.nomor_pertandingan % 2 = 1), (SELECT djt.nomor_partai FROM detail_jadwal_tanding djt JOIN pertandingan pl ON djt.id_pertandingan = pl.id_pertandingan WHERE pl.id_kompetisi_tanding = kom.id_kompetisi_tanding AND pl.babak = "Semi Final" AND pl.nomor_pertandingan % 2 = 1))) AS calon_atlet_biru', false)
            ->select('(SELECT djt.id_detail_jadwal_tanding FROM detail_jadwal_tanding djt WHERE djt.id_pertandingan = p.id_pertandingan) AS id_detail_jadwal_tanding', false)
            ->select('(SELECT nomor_partai FROM detail_jadwal_tanding djt WHERE djt.id_pertandingan = p.id_pertandingan) AS nomor_partai', false)
            ->select('(SELECT g.nama_gelanggang FROM gelanggang g JOIN jadwal_tanding jt ON g.id_gelanggang = jt.id_gelanggang JOIN detail_jadwal_tanding djt ON jt.id_jadwal_tanding = djt.id_jadwal_tanding WHERE djt.id_pertandingan = p.id_pertandingan) AS nama_gelanggang', false)
            ->select('(SELECT g.nomor_gelanggang FROM gelanggang g JOIN jadwal_tanding jt ON g.id_gelanggang = jt.id_gelanggang JOIN detail_jadwal_tanding djt ON jt.id_jadwal_tanding = djt.id_jadwal_tanding WHERE djt.id_pertandingan = p.id_pertandingan) AS nomor_gelanggang', false)
            ->select('(SELECT DATE_FORMAT(jt.tanggal, "%a, %d %M %Y") FROM jadwal_tanding jt JOIN detail_jadwal_tanding djt ON jt.id_jadwal_tanding = djt.id_jadwal_tanding WHERE djt.id_pertandingan = p.id_pertandingan) AS tanggal', false)
            ->select('CASE p.babak WHEN "Final" THEN 1 WHEN "Perebutan Juara Tiga" THEN 0.6 WHEN "Semi Final" THEN 0.5 WHEN "1/4 Final" THEN 0.25 WHEN "1/8 Final" THEN 0.125 WHEN "1/16 Final" THEN 0.0625 WHEN "1/32 Final" THEN 0.031 WHEN "1/64 Final" THEN 0.0156 END AS nilai_babak', false)
            ->select('(SELECT jenis_medali FROM perolehan_medali_tanding pmt WHERE pmt.id_peserta_tanding = p.id_atlet_merah) AS medali_merah', false)
            ->select('(SELECT jenis_medali FROM perolehan_medali_tanding pmt WHERE pmt.id_peserta_tanding = p.id_atlet_biru) AS medali_biru', false)
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = p.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia');
    }

    private function pertandinganPayload(array $data): array
    {
        return [
            'id_kompetisi_tanding' => (int) $data['id_kompetisi_tanding'],
            'id_atlet_merah' => $data['id_atlet_merah'] !== '' ? (int) $data['id_atlet_merah'] : null,
            'id_atlet_biru' => $data['id_atlet_biru'] !== '' ? (int) $data['id_atlet_biru'] : null,
            'babak' => $data['babak'] ?? '',
            'nomor_pertandingan' => (int) ($data['nomor_pertandingan'] ?? 0),
            'nomor_pertandingan_selanjutnya' => $data['nomor_pertandingan_selanjutnya'] !== '' ? (int) $data['nomor_pertandingan_selanjutnya'] : null,
            'jenis_kemenangan' => $data['jenis_kemenangan'] ?? '',
            'keterangan' => $data['keterangan'] ?? '',
        ];
    }

    private function filterKuota(array $rows, string $operator): array
    {
        return array_values(array_filter($rows, static function ($row) use ($operator): bool {
            $jumlah = (int) ($row->jumlah_peserta_tanding ?? 0);
            $max = (int) ($row->max_peserta ?? 0);
            return $operator === '<' ? $jumlah < $max : ($operator === '=' ? $jumlah === $max : $jumlah > $max);
        }));
    }
}
