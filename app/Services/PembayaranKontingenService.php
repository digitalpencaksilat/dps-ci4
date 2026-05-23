<?php

namespace App\Services;

use App\Models\PembayaranModel;

class PembayaranKontingenService
{
    public function pendingItems(int $idKontingen): array
    {
        $kontingen = db_connect()->table('kontingen')->where('id_kontingen', $idKontingen)->get()->getRow();

        $tanding = db_connect()->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding',
                'pt.id_pembayaran',
                'p.nama_pendaftar',
                'k.jenis_kontingen',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kt.label',
                'kt.biaya_pendaftaran_dn',
                'kt.biaya_pendaftaran_ln',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('p.id_kontingen', $idKontingen)
            ->where('pt.id_pembayaran IS NULL', null, false)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();

        $seni = db_connect()->table('kelompok_peserta_seni kps')
            ->select([
                'kps.id_kelompok_peserta_seni',
                'kps.id_pembayaran',
                'k.jenis_kontingen',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'sks.jenis_seni',
                'sks.nama_seni',
                'sks.biaya_pendaftaran_dn',
                'sks.biaya_pendaftaran_ln',
                '(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni',
            ])
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kps.id_kontingen', $idKontingen)
            ->where('kps.id_pembayaran IS NULL', null, false)
            ->orderBy('kps.id_kelompok_peserta_seni', 'DESC')
            ->get()
            ->getResult();

        return [
            'kontingen' => $kontingen,
            'tanding'   => $tanding,
            'seni'      => $seni,
        ];
    }

    public function create(int $idKontingen, array $tandingIds, array $seniIds, UploadedFilePayload $filePayload): bool
    {
        if ($tandingIds === [] && $seniIds === []) {
            throw new \RuntimeException('Pilih minimal satu item untuk dibayarkan.');
        }

        $db = db_connect();
        $db->transStart();

        $kontingen = $db->table('kontingen')->where('id_kontingen', $idKontingen)->get()->getRow();
        if ($kontingen === null) {
            throw new \RuntimeException('Kontingen tidak ditemukan.');
        }

        $total = 0;

        if ($tandingIds !== []) {
            $rows = $db->table('peserta_tanding pt')
                ->select('pt.id_peserta_tanding, kt.biaya_pendaftaran_dn, kt.biaya_pendaftaran_ln, k.jenis_kontingen')
                ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
                ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
                ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
                ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
                ->whereIn('pt.id_peserta_tanding', $tandingIds)
                ->where('p.id_kontingen', $idKontingen)
                ->where('pt.id_pembayaran IS NULL', null, false)
                ->get()
                ->getResult();

            foreach ($rows as $row) {
                $total += $row->jenis_kontingen === 'dalam_negeri' ? (int) $row->biaya_pendaftaran_dn : (int) $row->biaya_pendaftaran_ln;
            }
        }

        if ($seniIds !== []) {
            $rows = $db->table('kelompok_peserta_seni kps')
                ->select('kps.id_kelompok_peserta_seni, sks.biaya_pendaftaran_dn, sks.biaya_pendaftaran_ln, k.jenis_kontingen')
                ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
                ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
                ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
                ->whereIn('kps.id_kelompok_peserta_seni', $seniIds)
                ->where('kps.id_kontingen', $idKontingen)
                ->where('kps.id_pembayaran IS NULL', null, false)
                ->get()
                ->getResult();

            foreach ($rows as $row) {
                $total += $row->jenis_kontingen === 'dalam_negeri' ? (int) $row->biaya_pendaftaran_dn : (int) $row->biaya_pendaftaran_ln;
            }
        }

        $foto = $filePayload->store();

        $pembayaranModel = new PembayaranModel();
        $pembayaranModel->insert([
            'id_kontingen'        => $idKontingen,
            'tanggal_pembayaran'  => date('Y-m-d'),
            'total_pembayaran'    => $total,
            'foto'                => $foto,
            'status_pembayaran'   => 'menunggu',
        ]);

        $idPembayaran = (int) $pembayaranModel->getInsertID();

        if ($tandingIds !== []) {
            $db->table('peserta_tanding')->whereIn('id_peserta_tanding', $tandingIds)->update(['id_pembayaran' => $idPembayaran]);
        }

        if ($seniIds !== []) {
            $db->table('kelompok_peserta_seni')->whereIn('id_kelompok_peserta_seni', $seniIds)->update(['id_pembayaran' => $idPembayaran]);
        }

        $db->transComplete();

        return $db->transStatus();
    }

    public function transactionsByStatus(int $idKontingen, string $status): array
    {
        return db_connect()->table('pembayaran')
            ->where('id_kontingen', $idKontingen)
            ->where('status_pembayaran', $status)
            ->orderBy('tanggal_pembayaran', 'DESC')
            ->get()
            ->getResult();
    }

    public function transactionDetail(int $idKontingen, int $idPembayaran): ?array
    {
        $pembayaran = db_connect()->table('pembayaran')
            ->where('id_kontingen', $idKontingen)
            ->where('id_pembayaran', $idPembayaran)
            ->get()
            ->getRow();

        if ($pembayaran === null) {
            return null;
        }

        $tanding = db_connect()->table('peserta_tanding pt')
            ->select('p.nama_pendaftar, ku.nama_kategori_usia, ku.jenis_kelamin, kt.label')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('pt.id_pembayaran', $idPembayaran)
            ->get()
            ->getResult();

        $seni = db_connect()->table('kelompok_peserta_seni kps')
            ->select('ku.nama_kategori_usia, ku.jenis_kelamin, sks.jenis_seni, sks.nama_seni, (SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kps.id_pembayaran', $idPembayaran)
            ->get()
            ->getResult();

        return [
            'pembayaran' => $pembayaran,
            'tanding'    => $tanding,
            'seni'       => $seni,
        ];
    }

    public function accounts(): array
    {
        $config = [];
        $path = APPPATH . 'Config/ci3/pendaftaran/rekening_pembayaran.php';

        if (is_file($path)) {
            if (! defined('BASEPATH')) {
                define('BASEPATH', APPPATH);
            }

            require $path;
        }

        return array_values(array_filter($config, static fn ($item) => ! empty($item['active'])));
    }
}

class UploadedFilePayload
{
    public function __construct(private readonly \CodeIgniter\HTTP\Files\UploadedFile $file)
    {
    }

    public function store(): string
    {
        if (! $this->file->isValid()) {
            throw new \RuntimeException('Bukti pembayaran tidak valid.');
        }

        $targetDir = FCPATH . 'uploads/bukti-pembayaran';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $name = $this->file->getRandomName();
        $this->file->move($targetDir, $name, true);

        return $name;
    }
}
