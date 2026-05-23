<?php

namespace App\Services;

use App\Models\KelompokPesertaSeniModel;
use App\Models\PendaftarModel;

class KategoriSeniService
{
    public function listByKontingen(int $idKontingen): array
    {
        return db_connect()->table('kelompok_peserta_seni kps')
            ->select([
                'kps.id_kelompok_peserta_seni',
                'kps.id_kontingen',
                'kps.id_kompetisi_seni',
                'kps.id_pembayaran',
                'kps.keterangan',
                'kps.nomor_undi',
                'kom.nomor_pool',
                'kps.id_kompetisi_seni',
                'sks.nama_seni',
                'sks.jenis_seni',
                'sks.jumlah_peserta',
                'sks.sistem_penampilan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                '(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni',
                '(SELECT GROUP_CONCAT(CAST(p.berat_badan AS CHAR) SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS berat_anggota_kelompok_peserta_seni',
                '(SELECT GROUP_CONCAT(CAST(p.tinggi_badan AS CHAR) SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS tinggi_anggota_kelompok_peserta_seni',
                '(SELECT status_pembayaran FROM pembayaran WHERE pembayaran.id_pembayaran = kps.id_pembayaran) AS status_pembayaran',
            ])
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kps.id_kontingen', $idKontingen)
            ->orderBy('kps.id_kelompok_peserta_seni', 'DESC')
            ->get()
            ->getResult();
    }

    public function availableKompetisi(int $idKontingen): array
    {
        $items = db_connect()->table('kompetisi_seni kom')
            ->select([
                'kom.id_kompetisi_seni',
                'kom.max_peserta',
                'kom.nomor_pool',
                'sks.id_sub_kategori_seni',
                'sks.nama_seni',
                'sks.jenis_seni',
                'sks.jumlah_peserta',
                'sks.sistem_penampilan',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kl.kuota_peserta',
                'kl.jenis_perlombaan',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni k2 WHERE k2.id_kompetisi_seni = kom.id_kompetisi_seni) AS jumlah_kelompok_peserta_seni',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni k2 JOIN kompetisi_seni ko2 ON ko2.id_kompetisi_seni = k2.id_kompetisi_seni WHERE ko2.id_sub_kategori_seni = sks.id_sub_kategori_seni) AS jumlah_per_sub_kategori',
            ])
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->get()
            ->getResult();

        foreach ($items as $item) {
            $item->disabled = false;
            $item->message = null;

            if ((int) $item->jumlah_kelompok_peserta_seni >= (int) $item->max_peserta) {
                $item->disabled = true;
                $item->message = 'Kuota penuh';
            }
        }

        return $items;
    }

    public function availablePendaftarByKompetisi(int $idKompetisi, int $idKontingen): array
    {
        $kompetisi = db_connect()->table('kompetisi_seni kom')
            ->select([
                'kom.id_kompetisi_seni',
                'sks.jenis_seni',
                'sks.jumlah_peserta',
                'ku.jenis_kelamin',
            ])
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kom.id_kompetisi_seni', $idKompetisi)
            ->get()
            ->getRow();

        if ($kompetisi === null) {
            return [];
        }

        return db_connect()->table('pendaftar p')
            ->select('p.*')
            ->join('peserta_seni ps', 'ps.id_pendaftar = p.id_pendaftar', 'left')
            ->where('p.id_kontingen', $idKontingen)
            ->where('p.jenis_kelamin', $kompetisi->jenis_kelamin)
            ->where('ps.id_peserta_seni IS NULL', null, false)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();
    }

    public function create(int $idKontingen, int $idKompetisi, array $idPendaftar, ?string $keterangan): bool
    {
        $kompetisi = db_connect()->table('kompetisi_seni kom')
            ->select('kom.id_kompetisi_seni, sks.jenis_seni, sks.jumlah_peserta')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->where('kom.id_kompetisi_seni', $idKompetisi)
            ->get()
            ->getRow();

        if ($kompetisi === null) {
            throw new \RuntimeException('Kategori seni tidak ditemukan.');
        }

        $count = count($idPendaftar);
        $strictTypes = ['tunggal', 'ganda', 'beregu', 'solo kreatif', 'perorangan', 'berpasangan', 'berkelompok'];
        $strictMatch = in_array(strtolower($kompetisi->jenis_seni), $strictTypes, true);

        if (($strictMatch && $count !== (int) $kompetisi->jumlah_peserta) || (! $strictMatch && $count < (int) $kompetisi->jumlah_peserta)) {
            throw new \RuntimeException('Jumlah atlet yang dipilih tidak sesuai kebutuhan kategori seni.');
        }

        $db = db_connect();
        $db->transStart();

        $model = new KelompokPesertaSeniModel();
        $model->insert([
            'id_kompetisi_seni' => $idKompetisi,
            'id_kontingen'      => $idKontingen,
            'id_pembayaran'     => null,
            'status'            => 'ok',
            'keterangan'        => $keterangan ?? '',
            'nomor_undi'        => 0,
        ]);

        $idKelompok = (int) $model->getInsertID();

        foreach ($idPendaftar as $id) {
            $db->table('peserta_seni')->insert([
                'id_pendaftar' => (int) $id,
                'id_kelompok_peserta_seni' => $idKelompok,
                'status_sertifikat' => 'belum_dicetak',
                'nomor_sertifikat' => null,
            ]);
        }

        $db->transComplete();

        return $db->transStatus();
    }

    public function update(object $record, int $idKompetisi): bool
    {
        return (new KelompokPesertaSeniModel())->update($record->id_kelompok_peserta_seni, [
            'id_kompetisi_seni' => $idKompetisi,
        ]);
    }

    public function delete(object $record): bool
    {
        $db = db_connect();
        $db->transStart();
        $db->table('peserta_seni')->where('id_kelompok_peserta_seni', $record->id_kelompok_peserta_seni)->delete();
        $db->table('kelompok_peserta_seni')->where('id_kelompok_peserta_seni', $record->id_kelompok_peserta_seni)->delete();
        $db->transComplete();

        return $db->transStatus();
    }
}
