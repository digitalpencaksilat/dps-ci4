<?php

namespace App\Services;

class PembayaranAdminService
{
    public function parityAudit(): array
    {
        $summary = $this->dashboardRecap();
        $transactions = $this->transactions();
        $kontingen = $this->kontingenRecap();
        $tandingHistory = $this->tandingPaymentHistory();
        $seniHistory = $this->seniPaymentHistory();
        $dashboardMetrics = $this->dashboardParityMetrics();

        return [
            [
                'feature' => 'Dashboard ringkasan pembayaran',
                'ci3_source' => 'controllers/resources/Pembayaran::dashboard + models/resources/Pembayaran_model::get_detail_pembayaran_atlet/get_swp/get_swo',
                'ci4_source' => 'Services/PembayaranAdminService::dashboardRecap',
                'status' => ($summary['jumlah_transaksi'] ?? 0) > 0 || ($summary['jumlah_pendaftar_belum_lunas'] ?? 0) >= 0 ? 'Perlu Validasi' : 'Belum',
                'notes' => sprintf(
                    'CI4 saat ini: lunas Rp %s, menunggu Rp %s, belum lunas Rp %s, pendaftar lunas %d, menunggu %d, belum lunas %d.',
                    number_format((int) ($dashboardMetrics['total_lunas'] ?? 0), 0, ',', '.'),
                    number_format((int) ($dashboardMetrics['total_menunggu'] ?? 0), 0, ',', '.'),
                    number_format((int) ($dashboardMetrics['total_belum_lunas'] ?? 0), 0, ',', '.'),
                    (int) ($dashboardMetrics['jumlah_pendaftar_lunas'] ?? 0),
                    (int) ($dashboardMetrics['jumlah_pendaftar_menunggu'] ?? 0),
                    (int) ($dashboardMetrics['jumlah_pendaftar_belum_lunas'] ?? 0)
                ),
            ],
            [
                'feature' => 'Daftar transaksi admin',
                'ci3_source' => 'controllers/resources/Pembayaran::index/menunggu_konfirmasi/pembayaran_lunas',
                'ci4_source' => 'Services/PembayaranAdminService::transactions',
                'status' => $transactions !== [] ? 'Perlu Validasi' : 'Sebagian',
                'notes' => sprintf('List CI4 memuat %d transaksi. Perlu validasi jumlah row, ordering tanggal, dan field status terhadap CI3.', count($transactions)),
            ],
            [
                'feature' => 'Rekap kontingen',
                'ci3_source' => 'controllers/resources/Pembayaran::kontingen',
                'ci4_source' => 'Services/PembayaranAdminService::kontingenRecap',
                'status' => $kontingen !== [] ? 'Perlu Validasi' : 'Sebagian',
                'notes' => sprintf('CI4 memuat %d kontingen dalam rekap. Perlu cek total lunas/menunggu per kontingen terhadap CI3.', count($kontingen)),
            ],
            [
                'feature' => 'Riwayat tanding',
                'ci3_source' => 'controllers/resources/Pembayaran::pembayaran_tanding',
                'ci4_source' => 'Services/PembayaranAdminService::tandingPaymentHistory',
                'status' => $tandingHistory !== [] ? 'Perlu Validasi' : 'Sebagian',
                'notes' => sprintf('CI4 memuat %d row tanding. Perlu cek distribusi unpaid/menunggu/lunas terhadap CI3.', count($tandingHistory)),
            ],
            [
                'feature' => 'Riwayat seni',
                'ci3_source' => 'controllers/resources/Pembayaran::pembayaran_seni',
                'ci4_source' => 'Services/PembayaranAdminService::seniPaymentHistory',
                'status' => $seniHistory !== [] ? 'Perlu Validasi' : 'Sebagian',
                'notes' => sprintf('CI4 memuat %d row seni. Perlu cek grouping anggota, unpaid/menunggu/lunas, dan efeknya ke nota.', count($seniHistory)),
            ],
        ];
    }

    public function notaAudit(): array
    {
        return [
            [
                'feature' => 'Nota HTML/print',
                'ci3' => 'Ada via Pembayaran::nota + views/print/pembayaran/nota.php',
                'ci4' => 'Sudah ada route/view HTML khusus',
                'status' => 'Sebagian',
            ],
            [
                'feature' => 'Nota PDF',
                'ci3' => 'Print HTML browser',
                'ci4' => 'Ada via NotaPembayaranPdfService',
                'status' => 'Sebagian',
            ],
            [
                'feature' => 'Ringkasan jumlah item per nomor',
                'ci3' => 'Ada: tanding/tunggal/ganda/beregu/solo kreatif',
                'ci4' => 'Sudah tampil di HTML dan PDF, perlu uji parity hasil cetak',
                'status' => 'Sebagian',
            ],
            [
                'feature' => 'Identitas penerima dan metadata transaksi',
                'ci3' => 'Ada',
                'ci4' => 'Ada sebagian',
                'status' => 'Sebagian',
            ],
        ];
    }

    public function dashboardParityMetrics(): array
    {
        $summary = $this->dashboardRecap();

        return [
            'total_lunas' => (int) ($summary['total_lunas'] ?? 0),
            'total_menunggu' => (int) ($summary['total_menunggu'] ?? 0),
            'total_belum_lunas' => $this->unpaidPotentialAmount(),
            'jumlah_pendaftar_lunas' => (int) ($summary['jumlah_pendaftar_lunas'] ?? 0),
            'jumlah_pendaftar_menunggu' => (int) ($summary['jumlah_pendaftar_menunggu'] ?? 0),
            'jumlah_pendaftar_belum_lunas' => (int) ($summary['jumlah_pendaftar_belum_lunas'] ?? 0),
            'jumlah_transaksi' => (int) ($summary['jumlah_transaksi'] ?? 0),
        ];
    }

    public function dashboardRecap(): array
    {
        $db = db_connect();

        $paymentSummary = $db->table('pembayaran')
            ->select([
                'COUNT(*) AS jumlah_transaksi',
                'SUM(CASE WHEN status_pembayaran = "lunas" THEN total_pembayaran ELSE 0 END) AS total_lunas',
                'SUM(CASE WHEN status_pembayaran = "menunggu" THEN total_pembayaran ELSE 0 END) AS total_menunggu',
                'SUM(CASE WHEN status_pembayaran = "lunas" THEN 1 ELSE 0 END) AS jumlah_lunas',
                'SUM(CASE WHEN status_pembayaran = "menunggu" THEN 1 ELSE 0 END) AS jumlah_menunggu',
            ])
            ->get()
            ->getRow();

        $tandingUnpaid = $db->table('peserta_tanding pt')
            ->selectCount('pt.id_peserta_tanding', 'total')
            ->where('pt.id_pembayaran', null)
            ->get()
            ->getRow();

        $seniUnpaid = $db->table('kelompok_peserta_seni kps')
            ->selectCount('kps.id_kelompok_peserta_seni', 'total')
            ->where('kps.id_pembayaran', null)
            ->get()
            ->getRow();

        $tandingPaid = $db->table('peserta_tanding pt')
            ->selectCount('pt.id_peserta_tanding', 'total')
            ->join('pembayaran pay', 'pay.id_pembayaran = pt.id_pembayaran', 'left')
            ->where('pay.status_pembayaran', 'lunas')
            ->get()
            ->getRow();

        $tandingWaiting = $db->table('peserta_tanding pt')
            ->selectCount('pt.id_peserta_tanding', 'total')
            ->join('pembayaran pay', 'pay.id_pembayaran = pt.id_pembayaran', 'left')
            ->where('pay.status_pembayaran', 'menunggu')
            ->get()
            ->getRow();

        $seniPaid = $db->table('kelompok_peserta_seni kps')
            ->selectCount('kps.id_kelompok_peserta_seni', 'total')
            ->join('pembayaran pay', 'pay.id_pembayaran = kps.id_pembayaran', 'left')
            ->where('pay.status_pembayaran', 'lunas')
            ->get()
            ->getRow();

        $seniWaiting = $db->table('kelompok_peserta_seni kps')
            ->selectCount('kps.id_kelompok_peserta_seni', 'total')
            ->join('pembayaran pay', 'pay.id_pembayaran = kps.id_pembayaran', 'left')
            ->where('pay.status_pembayaran', 'menunggu')
            ->get()
            ->getRow();

        $categoryBreakdown = [
            'tanding' => $this->categoryPaymentBreakdown('tanding'),
            'tunggal' => $this->categoryPaymentBreakdown('tunggal'),
            'ganda' => $this->categoryPaymentBreakdown('ganda'),
            'beregu' => $this->categoryPaymentBreakdown('beregu'),
            'solo_kreatif' => $this->categoryPaymentBreakdown('solo_kreatif'),
        ];

        return [
            'jumlah_transaksi' => (int) ($paymentSummary->jumlah_transaksi ?? 0),
            'jumlah_lunas'     => (int) ($paymentSummary->jumlah_lunas ?? 0),
            'jumlah_menunggu'  => (int) ($paymentSummary->jumlah_menunggu ?? 0),
            'total_lunas'      => (int) ($paymentSummary->total_lunas ?? 0),
            'total_menunggu'   => (int) ($paymentSummary->total_menunggu ?? 0),
            'tanding_belum'    => (int) ($tandingUnpaid->total ?? 0),
            'seni_belum'       => (int) ($seniUnpaid->total ?? 0),
            'jumlah_pendaftar_lunas' => (int) ($tandingPaid->total ?? 0) + (int) ($seniPaid->total ?? 0),
            'jumlah_pendaftar_menunggu' => (int) ($tandingWaiting->total ?? 0) + (int) ($seniWaiting->total ?? 0),
            'jumlah_pendaftar_belum_lunas' => (int) ($tandingUnpaid->total ?? 0) + (int) ($seniUnpaid->total ?? 0),
            'detail_pembayaran_atlet' => $categoryBreakdown,
        ];
    }

    public function transactions(?string $status = null): array
    {
        $builder = db_connect()->table('pembayaran p')
            ->select([
                'p.id_pembayaran',
                'p.id_kontingen',
                'p.tanggal_pembayaran',
                'p.total_pembayaran',
                'p.status_pembayaran',
                'p.foto',
                'k.nama_kontingen',
                'k.nama_penanggungjawab AS nama_pimpinan_kontingen',
                'k.jenis_kontingen',
            ])
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen', 'left')
            ->orderBy('p.tanggal_pembayaran', 'DESC')
            ->orderBy('p.id_pembayaran', 'DESC');

        if ($status !== null && $status !== '') {
            $builder->where('p.status_pembayaran', $status);
        }

        return $builder->get()->getResult();
    }

    public function transactionDetail(int $idPembayaran): ?array
    {
        $pembayaran = db_connect()->table('pembayaran p')
            ->select([
                'p.id_pembayaran',
                'p.id_kontingen',
                'p.tanggal_pembayaran',
                'p.total_pembayaran',
                'p.status_pembayaran',
                'p.foto',
                'k.nama_kontingen',
                'k.nama_penanggungjawab AS nama_pimpinan_kontingen',
                'k.nomor_telepon_penanggungjawab',
                'k.jenis_kontingen',
                'k.negara',
                'k.provinsi',
                'k.kabupaten_kota AS kabupaten',
            ])
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen', 'left')
            ->where('p.id_pembayaran', $idPembayaran)
            ->get()
            ->getRow();

        if ($pembayaran === null) {
            return null;
        }

        $tanding = db_connect()->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding',
                'p.nama_pendaftar',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kt.label',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('pt.id_pembayaran', $idPembayaran)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();

        $seni = db_connect()->table('kelompok_peserta_seni kps')
            ->select([
                'kps.id_kelompok_peserta_seni',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'sks.jenis_seni',
                'sks.nama_seni',
                '(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni',
            ])
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kps.id_pembayaran', $idPembayaran)
            ->orderBy('kps.id_kelompok_peserta_seni', 'ASC')
            ->get()
            ->getResult();

        return [
            'pembayaran' => $pembayaran,
            'tanding'    => $tanding,
            'seni'       => $seni,
        ];
    }

    public function confirm(int $idPembayaran): bool
    {
        $db = db_connect();
        $pembayaran = $db->table('pembayaran')
            ->where('id_pembayaran', $idPembayaran)
            ->get()
            ->getRow();

        if ($pembayaran === null) {
            throw new \RuntimeException('Transaksi pembayaran tidak ditemukan.');
        }

        if ((string) $pembayaran->status_pembayaran === 'lunas') {
            return true;
        }

        return (bool) $db->table('pembayaran')
            ->where('id_pembayaran', $idPembayaran)
            ->update(['status_pembayaran' => 'lunas']);
    }

    public function waitingTransactions(): array
    {
        return $this->transactions('menunggu');
    }

    public function paidTransactions(): array
    {
        return $this->transactions('lunas');
    }

    public function reject(int $idPembayaran): bool
    {
        $db = db_connect();
        $pembayaran = $db->table('pembayaran')
            ->where('id_pembayaran', $idPembayaran)
            ->get()
            ->getRow();

        if ($pembayaran === null) {
            throw new \RuntimeException('Transaksi pembayaran tidak ditemukan.');
        }

        $db->transStart();

        $db->table('peserta_tanding')
            ->where('id_pembayaran', $idPembayaran)
            ->update(['id_pembayaran' => null]);

        $db->table('kelompok_peserta_seni')
            ->where('id_pembayaran', $idPembayaran)
            ->update(['id_pembayaran' => null]);

        $db->table('pembayaran')
            ->where('id_pembayaran', $idPembayaran)
            ->delete();

        $db->transComplete();

        if (! $db->transStatus()) {
            return false;
        }

        $this->deleteProofFile((string) ($pembayaran->foto ?? ''));

        return true;
    }

    public function kontingenRecap(): array
    {
        return db_connect()->table('kontingen k')
            ->select([
                'k.id_kontingen',
                'k.nama_kontingen',
                'k.nama_penanggungjawab AS nama_pimpinan_kontingen',
                'k.jenis_kontingen',
                '(SELECT COUNT(*) FROM pembayaran p WHERE p.id_kontingen = k.id_kontingen) AS jumlah_transaksi',
                '(SELECT COUNT(*) FROM peserta_tanding pt JOIN pendaftar pd ON pd.id_pendaftar = pt.id_pendaftar WHERE pd.id_kontingen = k.id_kontingen) AS jumlah_peserta_tanding',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni kom ON kom.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = kom.id_sub_kategori_seni WHERE kps.id_kontingen = k.id_kontingen AND LOWER(sks.jenis_seni) = "tunggal") AS jumlah_tunggal',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni kom ON kom.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = kom.id_sub_kategori_seni WHERE kps.id_kontingen = k.id_kontingen AND LOWER(sks.jenis_seni) = "ganda") AS jumlah_ganda',
                '(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni kom ON kom.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = kom.id_sub_kategori_seni WHERE kps.id_kontingen = k.id_kontingen AND LOWER(sks.jenis_seni) = "beregu") AS jumlah_beregu',
                '(SELECT COALESCE(SUM(p.total_pembayaran), 0) FROM pembayaran p WHERE p.id_kontingen = k.id_kontingen AND p.status_pembayaran = "lunas") AS total_lunas',
                '(SELECT COALESCE(SUM(p.total_pembayaran), 0) FROM pembayaran p WHERE p.id_kontingen = k.id_kontingen AND p.status_pembayaran = "menunggu") AS total_menunggu',
            ])
            ->orderBy('k.nama_kontingen', 'ASC')
            ->get()
            ->getResult();
    }

    public function kontingenDetail(int $idKontingen): ?array
    {
        $kontingen = db_connect()->table('kontingen')
            ->select([
                'kontingen.*',
                'kontingen.nama_penanggungjawab AS nama_pimpinan_kontingen',
                'kontingen.kabupaten_kota AS kabupaten',
            ])
            ->where('id_kontingen', $idKontingen)
            ->get()
            ->getRow();

        if ($kontingen === null) {
            return null;
        }

        $transactions = db_connect()->table('pembayaran')
            ->where('id_kontingen', $idKontingen)
            ->orderBy('tanggal_pembayaran', 'DESC')
            ->orderBy('id_pembayaran', 'DESC')
            ->get()
            ->getResult();

        $pendingItems = (new PembayaranKontingenService())->pendingItems($idKontingen);

        return [
            'kontingen'    => $kontingen,
            'transactions' => $transactions,
            'pendingItems' => $pendingItems,
            'accounts'     => (new PembayaranKontingenService())->accounts(),
            'summary'      => [
                'total_pending_items' => count($pendingItems['tanding'] ?? []) + count($pendingItems['seni'] ?? []),
                'total_pending_amount' => $this->pendingAmount($pendingItems),
            ],
        ];
    }

    public function unpaidItemsOverview(): array
    {
        return [
            'tanding' => db_connect()->table('peserta_tanding pt')
                ->select([
                    'pt.id_peserta_tanding',
                    'p.nama_pendaftar',
                    'k.id_kontingen',
                    'k.nama_kontingen',
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
                ->where('pt.id_pembayaran IS NULL', null, false)
                ->orderBy('k.nama_kontingen', 'ASC')
                ->orderBy('p.nama_pendaftar', 'ASC')
                ->get()
                ->getResult(),
            'seni' => db_connect()->table('kelompok_peserta_seni kps')
                ->select([
                    'kps.id_kelompok_peserta_seni',
                    'k.id_kontingen',
                    'k.nama_kontingen',
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
                ->where('kps.id_pembayaran IS NULL', null, false)
                ->orderBy('k.nama_kontingen', 'ASC')
                ->orderBy('kps.id_kelompok_peserta_seni', 'DESC')
                ->get()
                ->getResult(),
        ];
    }

    private function pendingAmount(array $pendingItems): int
    {
        $total = 0;

        foreach (($pendingItems['tanding'] ?? []) as $row) {
            $total += (string) ($row->jenis_kontingen ?? '') === 'luar_negeri'
                ? (int) ($row->biaya_pendaftaran_ln ?? 0)
                : (int) ($row->biaya_pendaftaran_dn ?? 0);
        }

        foreach (($pendingItems['seni'] ?? []) as $row) {
            $total += (string) ($row->jenis_kontingen ?? '') === 'luar_negeri'
                ? (int) ($row->biaya_pendaftaran_ln ?? 0)
                : (int) ($row->biaya_pendaftaran_dn ?? 0);
        }

        return $total;
    }

    public function tandingPaymentHistory(): array
    {
        return db_connect()->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding',
                'p.nama_pendaftar',
                'k.id_kontingen',
                'k.nama_kontingen',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'kt.label',
                'pay.id_pembayaran',
                'pay.status_pembayaran',
                'pay.tanggal_pembayaran',
                'pay.total_pembayaran',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('pembayaran pay', 'pay.id_pembayaran = pt.id_pembayaran', 'left')
            ->orderBy('pay.tanggal_pembayaran', 'DESC')
            ->orderBy('pt.id_peserta_tanding', 'DESC')
            ->get()
            ->getResult();
    }

    public function seniPaymentHistory(): array
    {
        return db_connect()->table('kelompok_peserta_seni kps')
            ->select([
                'kps.id_kelompok_peserta_seni',
                'k.id_kontingen',
                'k.nama_kontingen',
                'ku.nama_kategori_usia',
                'ku.jenis_kelamin',
                'sks.jenis_seni',
                'sks.nama_seni',
                'pay.id_pembayaran',
                'pay.status_pembayaran',
                'pay.tanggal_pembayaran',
                'pay.total_pembayaran',
                '(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ", ") FROM peserta_seni ps JOIN pendaftar p ON p.id_pendaftar = ps.id_pendaftar WHERE ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni',
            ])
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('pembayaran pay', 'pay.id_pembayaran = kps.id_pembayaran', 'left')
            ->orderBy('pay.tanggal_pembayaran', 'DESC')
            ->orderBy('kps.id_kelompok_peserta_seni', 'DESC')
            ->get()
            ->getResult();
    }

    private function deleteProofFile(string $filename): void
    {
        $filename = trim($filename);
        if ($filename === '') {
            return;
        }

        $path = FCPATH . 'uploads/bukti-pembayaran/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function categoryPaymentBreakdown(string $category): array
    {
        return match ($category) {
            'tanding' => $this->tandingBreakdown(),
            default => $this->seniBreakdown($category),
        };
    }

    private function tandingBreakdown(): array
    {
        $rows = db_connect()->table('peserta_tanding pt')
            ->select([
                'pt.id_peserta_tanding AS item_id',
                'pay.status_pembayaran',
                'k.jenis_kontingen',
                'kt.biaya_pendaftaran_dn',
                'kt.biaya_pendaftaran_ln',
            ])
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('pembayaran pay', 'pay.id_pembayaran = pt.id_pembayaran', 'left')
            ->get()
            ->getResult();

        return $this->summarizeBreakdown($rows, static function ($row): int {
            return (string) ($row->jenis_kontingen ?? '') === 'luar_negeri'
                ? (int) ($row->biaya_pendaftaran_ln ?? 0)
                : (int) ($row->biaya_pendaftaran_dn ?? 0);
        });
    }

    private function seniBreakdown(string $jenisSeni): array
    {
        $normalizedJenisSeni = str_replace('_', ' ', $jenisSeni);

        $rows = db_connect()->table('kelompok_peserta_seni kps')
            ->select([
                'kps.id_kelompok_peserta_seni AS item_id',
                'pay.status_pembayaran',
                'k.jenis_kontingen',
                'sks.biaya_pendaftaran_dn',
                'sks.biaya_pendaftaran_ln',
            ])
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('pembayaran pay', 'pay.id_pembayaran = kps.id_pembayaran', 'left')
            ->where('LOWER(sks.jenis_seni)', strtolower($normalizedJenisSeni))
            ->get()
            ->getResult();

        return $this->summarizeBreakdown($rows, static function ($row): int {
            return (string) ($row->jenis_kontingen ?? '') === 'luar_negeri'
                ? (int) ($row->biaya_pendaftaran_ln ?? 0)
                : (int) ($row->biaya_pendaftaran_dn ?? 0);
        });
    }

    private function summarizeBreakdown(array $rows, callable $amountResolver): array
    {
        $summary = [
            'jumlah_atlet_input' => 0,
            'jumlah_atlet_lunas' => 0,
            'jumlah_atlet_belum_lunas' => 0,
            'jumlah_atlet_menunggu' => 0,
            'jumlah_uang_diterima' => 0,
            'jumlah_uang_belum_diterima' => 0,
        ];

        foreach ($rows as $row) {
            $summary['jumlah_atlet_input']++;
            $amount = (int) $amountResolver($row);
            $status = (string) ($row->status_pembayaran ?? '');

            if ($status === 'lunas') {
                $summary['jumlah_atlet_lunas']++;
                $summary['jumlah_uang_diterima'] += $amount;
                continue;
            }

            if ($status === 'menunggu') {
                $summary['jumlah_atlet_menunggu']++;
            }

            $summary['jumlah_atlet_belum_lunas']++;
            $summary['jumlah_uang_belum_diterima'] += $amount;
        }

        return $summary;
    }

    private function unpaidPotentialAmount(): int
    {
        $overview = $this->unpaidItemsOverview();
        return $this->pendingAmount($overview);
    }
}
