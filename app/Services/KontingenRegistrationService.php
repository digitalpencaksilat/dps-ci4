<?php

namespace App\Services;

use App\Models\KontingenModel;

class KontingenRegistrationService
{
    public function create(array $payload): bool
    {
        $model = new KontingenModel();

        $email = strtolower(trim((string) ($payload['email_kontingen'] ?? '')));
        if ($model->where('email_kontingen', $email)->first() !== null) {
            throw new \RuntimeException('Email kontingen sudah terdaftar.');
        }

        $jenisKontingen = $payload['jenis_kontingen'] ?? 'dalam_negeri';
        $namaKontingen = trim((string) ($payload['nama_kontingen'] ?? ''));
        $nomorPj = $this->normalizePhone((string) ($payload['nomor_telepon_penanggungjawab'] ?? ''));
        $nomorKontingenInput = trim((string) ($payload['nomor_telepon_kontingen'] ?? ''));
        $nomorKontingen = $nomorKontingenInput !== '' ? $this->normalizePhone($nomorKontingenInput) : $nomorPj;
        $jabatanPj = trim((string) ($payload['jabatan_penanggungjawab'] ?? ''));
        $jabatanPj = $jabatanPj !== '' ? $jabatanPj : 'Manager Kontingen';
        $alamatLengkap = trim((string) ($payload['alamat_lengkap'] ?? ''));

        $data = [
            'nama_kontingen'                => $namaKontingen,
            'singkatan_nama_kontingen'      => strtoupper(substr(preg_replace('/\s+/', '', $namaKontingen), 0, 10)),
            'jenis_kontingen'               => $jenisKontingen,
            'perguruan'                     => 'ipsi',
            'email_kontingen'               => $email,
            'nomor_telepon_kontingen'       => $nomorKontingen,
            'alamat_kontingen'              => $alamatLengkap,
            'username'                      => $email,
            'password'                      => password_hash((string) $payload['password'], PASSWORD_BCRYPT, ['cost' => 10]),
            'nama_penanggungjawab'          => trim((string) ($payload['nama_penanggungjawab'] ?? '')),
            'jabatan_penanggungjawab'       => $jabatanPj,
            'nomor_telepon_penanggungjawab' => $nomorPj,
            'negara'                        => $jenisKontingen === 'dalam_negeri' ? 'indonesia' : trim((string) ($payload['negara'] ?? '')),
            'provinsi'                      => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['provinsi'] ?? '')) : null,
            'kabupaten_kota'                => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kabupaten_kota'] ?? '')) : null,
            'kecamatan'                     => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kecamatan'] ?? '')) : null,
            'kelurahan'                     => $jenisKontingen === 'dalam_negeri' ? trim((string) ($payload['kelurahan'] ?? '')) : null,
            'alamat_lengkap'                => $alamatLengkap,
            'alamat_penanggungjawab'        => $alamatLengkap,
            'keterangan'                    => '',
            'pembayaran_dn'                 => (int) (get_setting('biaya_pendaftaran_kontingen_dalam_negeri') ?? 0),
            'pembayaran_ln'                 => (int) (get_setting('biaya_pendaftaran_kontingen_luar_negeri') ?? 0),
            'status_data'                   => 'belum_final',
            'jenis_pendaftaran'             => 'web',
        ];

        return $model->insert($data) !== false;
    }

    private function normalizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '62')) {
            return '0' . substr($digits, 2);
        }

        return $digits;
    }
}
