<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Models\GelanggangModel;
use App\Models\KelasTandingModel;
use App\Services\JadwalTandingOtomatisService;

class PenjadwalanTandingOtomatisController extends BaseController
{
    public function index(): string
    {
        $gelanggang = (new GelanggangModel())->findAll();

        // Kelas tanding untuk urutan scheduling.
        // Minimal: list all. Penyaringan (usia/JK/kategori) nanti.
        $kelas = (new KelasTandingModel())
            ->orderBy('id_kelas_tanding', 'ASC')
            ->findAll();

        // Babak list. CI3 pakai string babak pada pertandingan.
        $babakOptions = [
            'Final',
            'Perebutan Juara Tiga',
            'Semi Final',
            '1/4 Final',
            '1/8 Final',
            '1/16 Final',
            '1/32 Final',
            '1/64 Final',
        ];

        return view('admin/super/jadwal_tanding/penjadwalan_tanding_otomatis', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_penjadwalan_otomatis_tanding',
            'gelanggang' => $gelanggang,
            'kelas' => $kelas,
            'babakOptions' => $babakOptions,
        ], 'Penjadwalan Otomatis Tanding'));
    }

    public function store()
    {
        // Parity CI3: create_jadwal_tanding_otomatis
        if (! $this->validate([
            'tanggal' => 'required|valid_date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'id_gelanggang' => 'required',
            'jumlah_partai' => 'required',
            'babak_pertandingan' => 'required',
            'jenis_penjadwalan' => 'required',
            'urutan_id_kelas_tanding' => 'required',
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
            'babak_pertandingan' => $this->request->getPost('babak_pertandingan'),
            'jenis_penjadwalan' => $this->request->getPost('jenis_penjadwalan'),
            'urutan_id_kelas_tanding' => $this->request->getPost('urutan_id_kelas_tanding'),
            'jumlah_selang_seling' => (int) ($this->request->getPost('jumlah_selang_seling') ?? 2),
            'langsung_buat_pdf' => (string) ($this->request->getPost('langsung_buat_pdf') ?? '') === '1',
            'pdf_library' => $this->request->getPost('pdf_library') ?? '',
        ];

        $service = new JadwalTandingOtomatisService();
        $result = $service->generate($pengaturan);

        if (! ($result['status'] ?? false)) {
            return redirect()->back()->withInput()->with('status', false)->with('message', (string) ($result['message'] ?? 'Gagal generate jadwal.'));
        }

        // Parity CI3: opsi langsung_buat_pdf
        if (! empty($pengaturan['langsung_buat_pdf']) && ! empty($result['jadwal_ids']) && is_array($result['jadwal_ids'])) {
            try {
                $this->generatePdfForJadwalIds($result['jadwal_ids'], (string) ($pengaturan['pdf_library'] ?? 'mpdf'));
            } catch (\Throwable $e) {
                log_message('error', 'Generate PDF setelah penjadwalan otomatis gagal: {message}', ['message' => $e->getMessage()]);
                return redirect()->to(base_url('admin/super/jadwal-tanding'))
                    ->with('status', false)
                    ->with('message', 'Jadwal berhasil dibuat, tapi generate PDF gagal: ' . $e->getMessage());
            }
        }

        return redirect()->to(base_url('admin/super/jadwal-tanding'))
            ->with('status', true)
            ->with('message', (string) ($result['message'] ?? 'Generate jadwal berhasil.'));
    }

    private function generatePdfForJadwalIds(array $ids, string $library = 'mpdf'): void
    {
        // Saat ini hanya mPDF dipakai di CI4.
        if ($library !== '' && $library !== 'mpdf') {
            throw new \RuntimeException('PDF library belum didukung: ' . $library);
        }

        $model = new \App\Models\JadwalTandingModel();
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
            $html = view('admin/super/pdf/jadwal_tanding', [
                'title' => 'Jadwal Tanding Arena ' . ($jadwal->nama_gelanggang ?? $id),
                'jadwal' => $jadwal,
                'details' => $details,
                'withScore' => false,
            ]);

            $path = $this->writeSchedulePdf($html, 'jadwal-tanding-' . $id . '.pdf');
            $model->update($id, ['pdf_path' => $path]);
        }
    }

    private function writeSchedulePdf(string $html, string $filename): string
    {
        $directory = FCPATH . 'uploads/jadwal';
        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('Direktori PDF jadwal tidak dapat dibuat.');
        }

        if (! is_writable($directory)) {
            throw new \RuntimeException('Direktori PDF jadwal tidak dapat ditulis.');
        }

        $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '-', $filename) ?: 'jadwal.pdf';
        $path = 'uploads/jadwal/' . $filename;
        $mpdf = (new \App\Services\Pdf\MpdfService())->make(['format' => 'A4-L']);
        $mpdf->WriteHTML($html);
        $mpdf->Output(FCPATH . $path, \Mpdf\Output\Destination::FILE);

        return $path;
    }

    private function viewData(array $data, string $title = 'Penjadwalan Otomatis Tanding'): array
    {
        return $data + [
            'title' => $title,
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Super'),
        ];
    }
}
