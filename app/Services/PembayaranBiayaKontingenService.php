<?php

namespace App\Services;

use App\Models\KontingenModel;
use App\Models\PembayaranModel;
use App\Services\UploadedFilePayload;
use CodeIgniter\HTTP\Files\UploadedFile;

class PembayaranBiayaKontingenService
{
    public function isEnabled(): bool
    {
        return (string) (get_setting('aktifkan_tagihan_biaya_kontingen') ?? '0') === '1';
    }

    public function detail(int $idKontingen): ?array
    {
        $kontingen = db_connect()->table('kontingen k')
            ->select([
                'k.*',
                'p.id_pembayaran AS biaya_kontingen_id_pembayaran',
                'p.total_pembayaran AS biaya_kontingen_total_pembayaran',
                'p.status_pembayaran AS biaya_kontingen_status_pembayaran',
                'p.tanggal_pembayaran AS biaya_kontingen_tanggal_pembayaran',
                'p.foto AS biaya_kontingen_foto',
            ])
            ->join('pembayaran p', 'p.id_pembayaran = k.id_pembayaran', 'left')
            ->where('k.id_kontingen', $idKontingen)
            ->get()
            ->getRow();

        if ($kontingen === null) {
            return null;
        }

        $nominal = $this->nominalFor($kontingen);
        $status = $this->statusFor($kontingen, $nominal);

        return [
            'enabled' => $this->isEnabled(),
            'kontingen' => $kontingen,
            'nominal' => $nominal,
            'status' => $status,
            'payment' => empty($kontingen->biaya_kontingen_id_pembayaran) ? null : (object) [
                'id_pembayaran' => (int) $kontingen->biaya_kontingen_id_pembayaran,
                'total_pembayaran' => (int) $kontingen->biaya_kontingen_total_pembayaran,
                'status_pembayaran' => (string) $kontingen->biaya_kontingen_status_pembayaran,
                'tanggal_pembayaran' => $kontingen->biaya_kontingen_tanggal_pembayaran,
                'foto' => (string) $kontingen->biaya_kontingen_foto,
            ],
            'can_pay' => $this->isEnabled() && $nominal > 0 && $status === 'belum_dibayar',
        ];
    }

    public function listForBendahara(?string $status = null): array
    {
        $rows = db_connect()->table('kontingen k')
            ->select([
                'k.id_kontingen',
                'k.nama_kontingen',
                'k.nama_penanggungjawab AS nama_pimpinan_kontingen',
                'k.jenis_kontingen',
                'k.pembayaran_dn',
                'k.pembayaran_ln',
                'p.id_pembayaran',
                'p.total_pembayaran',
                'p.status_pembayaran',
                'p.tanggal_pembayaran',
                'p.foto',
            ])
            ->join('pembayaran p', 'p.id_pembayaran = k.id_pembayaran', 'left')
            ->orderBy('k.nama_kontingen', 'ASC')
            ->get()
            ->getResult();

        $mapped = [];
        foreach ($rows as $row) {
            $row->nominal_tagihan = $this->nominalFor($row);
            $row->status_tagihan = $this->statusFor($row, (int) $row->nominal_tagihan);

            if ((int) $row->nominal_tagihan <= 0) {
                continue;
            }

            if ($status !== null && $status !== '' && $row->status_tagihan !== $status) {
                continue;
            }

            $mapped[] = $row;
        }

        return $mapped;
    }

    public function createForKontingen(int $idKontingen, UploadedFile $file): int
    {
        if (! $this->isEnabled()) {
            throw new \RuntimeException('Tagihan biaya kontingen sedang tidak aktif.');
        }

        $detail = $this->detail($idKontingen);
        if ($detail === null) {
            throw new \RuntimeException('Kontingen tidak ditemukan.');
        }

        if (! $detail['can_pay']) {
            throw new \RuntimeException('Tagihan biaya kontingen tidak tersedia untuk dibayar.');
        }

        $filePayload = new UploadedFilePayload($file, $idKontingen);
        $foto = $filePayload->store();
        $db = db_connect();
        $pembayaranModel = new PembayaranModel();

        $db->transStart();
        $pembayaranModel->insert([
            'id_kontingen' => $idKontingen,
            'tanggal_pembayaran' => date('Y-m-d'),
            'total_pembayaran' => (int) $detail['nominal'],
            'foto' => $foto,
            'status_pembayaran' => 'menunggu',
        ]);

        $idPembayaran = (int) $pembayaranModel->getInsertID();
        (new KontingenModel())->update($idKontingen, ['id_pembayaran' => $idPembayaran]);
        $db->transComplete();

        if (! $db->transStatus()) {
            throw new \RuntimeException('Gagal membuat transaksi biaya kontingen.');
        }

        return $idPembayaran;
    }

    public function confirm(int $idPembayaran): bool
    {
        return $this->updateStatus($idPembayaran, 'lunas');
    }

    public function reject(int $idPembayaran): bool
    {
        $db = db_connect();
        $kontingen = $db->table('kontingen')->where('id_pembayaran', $idPembayaran)->get()->getRow();
        if ($kontingen === null) {
            throw new \RuntimeException('Transaksi biaya kontingen tidak ditemukan.');
        }

        $db->transStart();
        $db->table('pembayaran')->where('id_pembayaran', $idPembayaran)->update(['status_pembayaran' => 'ditolak']);
        $db->table('kontingen')->where('id_kontingen', (int) $kontingen->id_kontingen)->update(['id_pembayaran' => null]);
        $db->transComplete();

        return $db->transStatus();
    }

    private function updateStatus(int $idPembayaran, string $status): bool
    {
        $db = db_connect();
        $kontingen = $db->table('kontingen')->where('id_pembayaran', $idPembayaran)->get()->getRow();
        if ($kontingen === null) {
            throw new \RuntimeException('Transaksi biaya kontingen tidak ditemukan.');
        }

        return (bool) $db->table('pembayaran')
            ->where('id_pembayaran', $idPembayaran)
            ->update(['status_pembayaran' => $status]);
    }

    private function nominalFor(object $kontingen): int
    {
        $field = (string) ($kontingen->jenis_kontingen ?? '') === 'luar_negeri' ? 'pembayaran_ln' : 'pembayaran_dn';
        $nominal = (int) ($kontingen->{$field} ?? 0);

        if ($nominal > 0) {
            return $nominal;
        }

        return (string) ($kontingen->jenis_kontingen ?? '') === 'luar_negeri'
            ? (int) (get_setting('biaya_pendaftaran_kontingen_luar_negeri') ?? 0)
            : (int) (get_setting('biaya_pendaftaran_kontingen_dalam_negeri') ?? 0);
    }

    private function statusFor(object $kontingen, int $nominal): string
    {
        if (! $this->isEnabled()) {
            return 'nonaktif';
        }

        if ($nominal <= 0) {
            return 'gratis';
        }

        if (empty($kontingen->id_pembayaran) && empty($kontingen->biaya_kontingen_id_pembayaran)) {
            return 'belum_dibayar';
        }

        return (string) ($kontingen->status_pembayaran ?? $kontingen->biaya_kontingen_status_pembayaran ?? 'menunggu');
    }
}
