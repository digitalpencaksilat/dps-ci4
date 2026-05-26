<?php

namespace App\Services;

use App\Models\PendaftarModel;

class PesertaService
{
    public function create(int $idKontingen, array $payload): int
    {
        $this->ensureQuotaAvailable($idKontingen);

        $model = new PendaftarModel();
        $data = [
            'id_kontingen'               => $idKontingen,
            'nama_pendaftar'             => ucwords(strtolower(trim((string) ($payload['nama_pendaftar'] ?? '')))),
            'jenis_kelamin'              => $payload['jenis_kelamin'] ?? 'putra',
            'tinggi_badan'               => (float) ($payload['tinggi_badan'] ?? 0),
            'berat_badan'                => (float) ($payload['berat_badan'] ?? 0),
            'tempat_lahir'               => trim((string) ($payload['tempat_lahir'] ?? '')),
            'tanggal_lahir'              => $payload['tanggal_lahir'] ?? null,
            'nama_sekolah'               => trim((string) ($payload['nama_sekolah'] ?? '')),
            'alamat'                     => trim((string) ($payload['alamat'] ?? '')),
            'nomor_induk_kependudukan'   => trim((string) ($payload['nomor_induk_kependudukan'] ?? '')),
            'nomor_kartu_keluarga'       => trim((string) ($payload['nomor_kartu_keluarga'] ?? '')),
            'foto'                       => null,
            'status_data'                => 'belum_final',
            'keterangan'                 => '',
        ];

        $model->insert($data);

        $id = (int) $model->getInsertID();
        if ($id <= 0) {
            throw new \RuntimeException('Gagal menambahkan peserta.');
        }

        return $id;
    }

    public function update(object $peserta, array $payload): bool
    {
        $data = [
            'nama_pendaftar'             => ucwords(strtolower(trim((string) ($payload['nama_pendaftar'] ?? '')))),
            'jenis_kelamin'              => $payload['jenis_kelamin'] ?? $peserta->jenis_kelamin,
            'tinggi_badan'               => (float) ($payload['tinggi_badan'] ?? $peserta->tinggi_badan),
            'berat_badan'                => (float) ($payload['berat_badan'] ?? $peserta->berat_badan),
            'tempat_lahir'               => trim((string) ($payload['tempat_lahir'] ?? '')),
            'tanggal_lahir'              => $payload['tanggal_lahir'] ?? $peserta->tanggal_lahir,
            'nama_sekolah'               => trim((string) ($payload['nama_sekolah'] ?? '')),
            'alamat'                     => trim((string) ($payload['alamat'] ?? '')),
            'nomor_induk_kependudukan'   => trim((string) ($payload['nomor_induk_kependudukan'] ?? '')),
            'nomor_kartu_keluarga'       => trim((string) ($payload['nomor_kartu_keluarga'] ?? '')),
        ];

        return (new PendaftarModel())->update($peserta->id_pendaftar, $data);
    }

    public function delete(object $peserta): bool
    {
        return (new PendaftarModel())->delete($peserta->id_pendaftar);
    }

    private function ensureQuotaAvailable(int $idKontingen): void
    {
        $count = (new PendaftarModel())->where('id_kontingen', $idKontingen)->countAllResults();
        $max = (int) (ci3_config_item('max_atlet_per_kontingen', 'pendaftaran/max_atlet_per_kontingen') ?? 0);

        if ($max > 0 && $count >= $max) {
            throw new \RuntimeException('Kuota maksimal atlet per kontingen sudah tercapai.');
        }
    }
}
