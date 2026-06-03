<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Models\GelanggangModel;
use App\Services\JadwalSeniOtomatisService;

class PenjadwalanSeniOtomatisController extends BaseController
{
    public function index(): string
    {
        $gelanggang = (new GelanggangModel())->findAll();

        $db = db_connect();
        $subKategori = $db->table('sub_kategori_seni sks')
            ->select('
                sks.id_sub_kategori_seni,
                sks.nama_seni,
                sks.jenis_seni,
                sks.sistem_penampilan,
                ku.nama_kategori_usia,
                ku.jenis_kelamin,
                (
                    SELECT COUNT(DISTINCT ks.id_kompetisi_seni)
                    FROM kompetisi_seni ks
                    JOIN kelompok_peserta_seni kps ON kps.id_kompetisi_seni = ks.id_kompetisi_seni
                    LEFT JOIN penampilan_seni ps ON ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni
                    LEFT JOIN detail_jadwal_seni djs ON djs.id_penampilan_seni = ps.id_penampilan_seni
                    WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni
                    AND djs.id_detail_jadwal_seni IS NULL
                ) AS jumlah_pool_seni,
                (
                    SELECT COUNT(DISTINCT kps.id_kelompok_peserta_seni)
                    FROM kompetisi_seni ks
                    JOIN kelompok_peserta_seni kps ON kps.id_kompetisi_seni = ks.id_kompetisi_seni
                    LEFT JOIN penampilan_seni ps ON ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni
                    LEFT JOIN detail_jadwal_seni djs ON djs.id_penampilan_seni = ps.id_penampilan_seni
                    WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni
                    AND djs.id_detail_jadwal_seni IS NULL
                ) AS jumlah_kelompok_belum_jadwal,
                (
                    SELECT COUNT(*)
                    FROM battle_seni bs
                    JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = bs.id_kompetisi_seni
                    LEFT JOIN detail_jadwal_seni djs ON djs.id_battle_seni = bs.id_battle_seni
                    WHERE ks.id_sub_kategori_seni = sks.id_sub_kategori_seni
                    AND bs.jenis_kemenangan != "BYE"
                    AND djs.id_detail_jadwal_seni IS NULL
                ) AS jumlah_battle_belum_jadwal
            ', false)
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->get()
            ->getResult();

        $babakOptions = $this->getBabakBattleOptions($db);

        return view('admin/super/jadwal_seni/penjadwalan_seni_otomatis', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_penjadwalan_otomatis_seni',
            'gelanggang' => $gelanggang,
            'subKategori' => $subKategori,
            'babakOptions' => $babakOptions,
        ], 'Penjadwalan Otomatis Seni'));
    }

    public function storePool()
    {
        if (! $this->validate([
            'tanggal' => 'required|valid_date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'id_gelanggang' => 'required',
            'jumlah_pool' => 'required',
            'urutan_id_sub_kategori_seni' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('status', false)->with('message', implode("\n", $this->validator->getErrors()));
        }

        $pengaturan = [
            'tanggal' => $this->request->getPost('tanggal'),
            'jam_mulai' => $this->request->getPost('jam_mulai'),
            'jam_selesai' => $this->request->getPost('jam_selesai'),
            'keterangan' => $this->request->getPost('keterangan') ?? '',
            'id_gelanggang' => $this->request->getPost('id_gelanggang'),
            'jumlah_pool' => $this->request->getPost('jumlah_pool'),
            'urutan_id_sub_kategori_seni' => $this->request->getPost('urutan_id_sub_kategori_seni'),
            'langsung_buat_pdf' => (string) ($this->request->getPost('langsung_buat_pdf') ?? '') === '1',
            'pdf_library' => 'mpdf',
        ];

        $service = new JadwalSeniOtomatisService();
        $result = $service->generatePool($pengaturan);

        if (! ($result['status'] ?? false)) {
            return redirect()->back()->withInput()->with('status', false)->with('message', (string) ($result['message'] ?? 'Gagal generate jadwal seni pool.'));
        }

        if (! empty($pengaturan['langsung_buat_pdf']) && ! empty($result['jadwal_ids']) && is_array($result['jadwal_ids'])) {
            try {
                $this->generatePdfForJadwalIds($result['jadwal_ids'], (string) ($pengaturan['pdf_library'] ?? 'mpdf'));
            } catch (\Throwable $e) {
                log_message('error', 'Generate PDF setelah penjadwalan seni pool gagal: {message}', ['message' => $e->getMessage()]);
                return redirect()->to(base_url('admin/super/jadwal-seni'))
                    ->with('status', false)
                    ->with('message', 'Jadwal seni pool berhasil dibuat, tapi generate PDF gagal: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('admin/super/jadwal-seni'))
            ->with('status', true)
            ->with('message', (string) ($result['message'] ?? 'Generate jadwal seni pool berhasil.'));
    }

    public function storeBattle()
    {
        if (! $this->validate([
            'tanggal' => 'required|valid_date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'id_gelanggang' => 'required',
            'jumlah_partai' => 'required',
            'babak_battle_seni' => 'required',
            'jenis_penjadwalan' => 'required',
            'urutan_id_sub_kategori_seni' => 'required',
        ])) {
            return redirect()->back()->withInput()->with('status', false)->with('message', implode("\n", $this->validator->getErrors()));
        }

        $pengaturan = [
            'tanggal' => $this->request->getPost('tanggal'),
            'jam_mulai' => $this->request->getPost('jam_mulai'),
            'jam_selesai' => $this->request->getPost('jam_selesai'),
            'keterangan' => $this->request->getPost('keterangan') ?? '',
            'id_gelanggang' => $this->request->getPost('id_gelanggang'),
            'jumlah_partai' => $this->request->getPost('jumlah_partai'),
            'babak_battle_seni' => $this->request->getPost('babak_battle_seni'),
            'jenis_penjadwalan' => $this->request->getPost('jenis_penjadwalan'),
            'urutan_id_sub_kategori_seni' => $this->request->getPost('urutan_id_sub_kategori_seni'),
            'langsung_buat_pdf' => (string) ($this->request->getPost('langsung_buat_pdf') ?? '') === '1',
            'pdf_library' => 'mpdf',
        ];

        $service = new JadwalSeniOtomatisService();
        $result = $service->generateBattle($pengaturan);

        if (! ($result['status'] ?? false)) {
            return redirect()->back()->withInput()->with('status', false)->with('message', (string) ($result['message'] ?? 'Gagal generate jadwal seni battle.'));
        }

        if (! empty($pengaturan['langsung_buat_pdf']) && ! empty($result['jadwal_ids']) && is_array($result['jadwal_ids'])) {
            try {
                $this->generatePdfForJadwalIds($result['jadwal_ids'], (string) ($pengaturan['pdf_library'] ?? 'mpdf'));
            } catch (\Throwable $e) {
                log_message('error', 'Generate PDF setelah penjadwalan seni battle gagal: {message}', ['message' => $e->getMessage()]);
                return redirect()->to(base_url('admin/super/jadwal-seni'))
                    ->with('status', false)
                    ->with('message', 'Jadwal seni battle berhasil dibuat, tapi generate PDF gagal: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('admin/super/jadwal-seni'))
            ->with('status', true)
            ->with('message', (string) ($result['message'] ?? 'Generate jadwal seni battle berhasil.'));
    }

    private function getBabakBattleOptions(\CodeIgniter\Database\BaseConnection $db): array
    {
        $rows = $db->table('battle_seni bs')
            ->distinct()
            ->select('bs.babak')
            ->join('detail_jadwal_seni djs', 'djs.id_battle_seni = bs.id_battle_seni', 'left')
            ->where('bs.jenis_kemenangan !=', 'BYE')
            ->where('djs.id_detail_jadwal_seni IS NULL', null, false)
            ->orderBy('bs.babak', 'ASC')
            ->get()
            ->getResult();

        $options = [];
        foreach ($rows as $row) {
            $babak = trim((string) ($row->babak ?? ''));
            if ($babak !== '') {
                $options[] = $babak;
            }
        }

        $babakOrder = [
            'Final',
            'Perebutan Juara Tiga',
            'Semi Final',
            '1/4 Final',
            '1/8 Final',
            '1/16 Final',
            '1/32 Final',
            '1/64 Final',
        ];
        usort($options, static function (string $a, string $b) use ($babakOrder): int {
            $ia = array_search($a, $babakOrder, true);
            $ib = array_search($b, $babakOrder, true);
            $ia = $ia === false ? 999 : $ia;
            $ib = $ib === false ? 999 : $ib;

            return $ia === $ib ? strcmp($a, $b) : $ia <=> $ib;
        });

        return $options !== [] ? $options : ['Final'];
    }

    private function generatePdfForJadwalIds(array $ids, string $library = 'mpdf'): void
    {
        if ($library !== '' && $library !== 'mpdf') {
            throw new \RuntimeException('PDF library belum didukung: ' . $library);
        }

        $model = new \App\Models\JadwalSeniModel();
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                continue;
            }

            $jadwal = $model->findWithGelanggang($id);
            if ($jadwal === null) {
                continue;
            }

            $details = $model->get_detail_jadwal($id);
            $html = view('admin/super/pdf/jadwal_seni', [
                'title' => 'Jadwal Seni Arena ' . ($jadwal->nama_gelanggang ?? $id),
                'jadwal' => $jadwal,
                'details' => $details,
                'withScore' => false,
            ]);

            $fileInfo = $this->writeSchedulePdf($html, 'seni', $this->buildSchedulePdfFilename($jadwal));
            $model->update($id, $this->schedulePdfUpdatePayload($model->table, $fileInfo));
        }
    }

    private function schedulePdfUpdatePayload(string $table, array $fileInfo): array
    {
        $db = db_connect();
        if ($db->fieldExists('pdf_path', $table)) {
            return [
                'nama_file' => (string) ($fileInfo['basename'] ?? ''),
                'pdf_path' => (string) ($fileInfo['path'] ?? ''),
            ];
        }

        // Schema legacy CI3 hanya punya nama_file. Simpan path relatif penuh agar download/merge tetap bisa resolve file.
        return ['nama_file' => (string) ($fileInfo['path'] ?? $fileInfo['basename'] ?? '')];
    }

    private function writeSchedulePdf(string $html, string $jenis, string $filename): array
    {
        $subdir = $jenis === 'seni' ? 'seni' : 'tanding';
        $relativeDirectory = 'uploads/jadwal-pdf/' . $subdir . '/';
        $directory = FCPATH . $relativeDirectory;
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Direktori PDF jadwal tidak dapat dibuat.');
        }

        if (! is_writable($directory)) {
            throw new \RuntimeException('Direktori PDF jadwal tidak dapat ditulis.');
        }

        $filename = $this->sanitizeSchedulePdfFilename($filename);
        $path = $relativeDirectory . $filename;
        ini_set('memory_limit', '512M');
        ini_set('pcre.backtrack_limit', '5000000');

        $oldDisplayErrors = ini_get('display_errors');
        $oldErrorReporting = error_reporting();
        ini_set('display_errors', '0');
        error_reporting(0);

        ob_start();
        try {
            $mpdf = (new \App\Services\Pdf\MpdfService())->make([
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 2,
                'margin_right' => 2,
                'margin_top' => 3,
                'margin_bottom' => 3,
                'margin_header' => 0,
                'margin_footer' => 0,
            ]);
            $mpdf->WriteHTML($html);
            $buffer = ob_get_clean();
        } finally {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            ini_set('display_errors', (string) $oldDisplayErrors);
            error_reporting($oldErrorReporting);
        }

        if ($buffer !== false && $buffer !== '') {
            log_message('warning', 'Output buffer dibersihkan sebelum generate PDF jadwal seni otomatis: {buffer}', ['buffer' => substr($buffer, 0, 200)]);
        }

        $mpdf->Output(FCPATH . $path, \Mpdf\Output\Destination::FILE);

        return [
            'basename' => $filename,
            'path' => $path,
        ];
    }

    private function buildSchedulePdfFilename(object $jadwal): string
    {
        $arena = trim((string) ($jadwal->nama_gelanggang ?? 'GELANGGANG'));
        $keterangan = trim((string) ($jadwal->keterangan_jadwal ?? $jadwal->keterangan ?? ''));
        $tanggal = $this->formatSchedulePdfDate((string) ($jadwal->tanggal ?? ''));
        $event = trim((string) (get_setting('event_name') ?? 'Digital Pencak Silat'));

        $parts = [$arena];
        if ($keterangan !== '') {
            $parts[] = $keterangan;
        }
        if ($tanggal !== '') {
            $parts[] = $tanggal;
        }
        if ($event !== '') {
            $parts[] = $event;
        }

        return implode(' - ', $parts) . '.pdf';
    }

    private function formatSchedulePdfDate(string $tanggal): string
    {
        if ($tanggal === '') {
            return '';
        }

        try {
            $date = new \DateTime($tanggal);
        } catch (\Throwable $e) {
            return $tanggal;
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return (int) $date->format('j') . ' ' . ($months[(int) $date->format('n')] ?? $date->format('F')) . ' ' . $date->format('Y');
    }

    private function sanitizeSchedulePdfFilename(string $filename): string
    {
        $filename = preg_replace('/[\\\\\/:*?"<>|]+/', '-', $filename) ?? 'jadwal.pdf';
        $filename = preg_replace('/\s+/', ' ', $filename) ?? 'jadwal.pdf';
        $filename = trim($filename, " .-\t\n\r\0\x0B");

        if ($filename === '') {
            $filename = 'jadwal';
        }

        if (! str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return $filename;
    }

    private function viewData(array $data, string $title = 'Penjadwalan Otomatis Seni'): array
    {
        return $data + [
            'title' => $title,
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Super'),
        ];
    }
}

