<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\SertifikatService;
use App\Services\Admin\Super\SettingWriterService;
use CodeIgniter\HTTP\RedirectResponse;

class PrinterController extends BaseController
{
    private SertifikatService $service;

    public function __construct()
    {
        $this->service = new SertifikatService();
    }

    // ============================================================
    //  Dashboard
    // ============================================================

    public function dashboard(): string
    {
        return view('admin/printer/dashboard', $this->viewData([
            'hasBackground'  => $this->service->hasBackground(),
            'backgroundUrl'  => $this->service->backgroundUrl(),
            'domainHosting'  => $this->service->domainHosting(),
            'hideBg'         => $this->service->hideSertifikatBackground(),
            'statistik'      => $this->service->getStatistik(),
            'suffix'         => $this->service->nomorSertifikatSuffix(),
            'statNomor'      => $this->service->getStatistikNomorSertifikat(),
        ], 'Dashboard Printer'));
    }

    // ============================================================
    //  Pengaturan Tata Letak
    // ============================================================

    public function pengaturanTataLetak(): string
    {
        return view('admin/printer/pengaturan_tata_letak', $this->viewData([
            'layout'        => $this->service->getLayoutConfig(),
            'backgroundUrl' => $this->service->backgroundUrl(),
        ], 'Pengaturan Tata Letak Sertifikat', 'printer_tata_letak'));
    }

    public function simpanTataLetak(): RedirectResponse
    {
        $current = $this->service->getLayoutConfig();
        foreach (array_keys($current) as $key) {
            $val = $this->request->getPost($key);
            if ($val !== null) {
                $current[$key] = trim((string) $val);
            }
        }
        $this->service->saveLayoutConfig($current);
        return redirect()->to(base_url('admin/printer/pengaturan-tata-letak'))
            ->with('status', true)->with('message', 'Tata letak berhasil disimpan.');
    }

    // ============================================================
    //  Upload Background
    // ============================================================

    public function uploadBackground(): RedirectResponse
    {
        $file = $this->request->getFile('sertifikat');
        if ($file === null) {
            return redirect()->back()->with('status', false)->with('message', 'Tidak ada file yang diunggah.');
        }
        try {
            $this->service->uploadBackground($file);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('status', false)->with('message', $e->getMessage());
        }
        return redirect()->to(base_url('admin/printer/dashboard'))
            ->with('status', true)->with('message', 'Upload background berhasil.');
    }

    // ============================================================
    //  Settings
    // ============================================================

    public function updateDomainHosting(): RedirectResponse
    {
        (new SettingWriterService())->setString('domain_hosting', trim((string) $this->request->getPost('domain_hosting')));
        return redirect()->to(base_url('admin/printer/dashboard'))
            ->with('status', true)->with('message', 'Domain hosting berhasil disimpan.');
    }

    public function updateHideBackground(): RedirectResponse
    {
        (new SettingWriterService())->setString('hide_sertifikat_background', (string) $this->request->getPost('hide_sertifikat_background'));
        return redirect()->to(base_url('admin/printer/dashboard'))
            ->with('status', true)->with('message', 'Pengaturan background disimpan.');
    }

    // ============================================================
    //  Preview
    // ============================================================

    public function preview(): string
    {
        return view('print/sertifikat/template', [
            'main_view'  => 'print/sertifikat/pages/preview',
            'paper_size' => 'A4 landscape',
            'layout'     => $this->service->getLayoutConfig(),
            'background_url' => $this->service->backgroundUrl(),
            'hide_bg'    => $this->service->hideSertifikatBackground(),
        ]);
    }

    // ============================================================
    //  Daftar Cetak
    // ============================================================

    public function cetakTandingList(): string
    {
        return view('admin/printer/cetak_tanding', $this->viewData([
            'data' => $this->service->listPesertaTanding(),
        ], 'Cetak Sertifikat Peserta Tanding', 'printer_cetak_tanding'));
    }

    public function cetakSeniList(): string
    {
        return view('admin/printer/cetak_seni', $this->viewData([
            'data' => $this->service->listPesertaSeni(),
        ], 'Cetak Sertifikat Peserta Seni', 'printer_cetak_seni'));
    }

    // ============================================================
    //  Cetak Single (window.print)
    // ============================================================

    public function cetakSingle(string $tipe, int $id): string
    {
        $get = $this->request->getGet();

        if ($tipe === 'tanding') {
            $peserta = $this->service->getPesertaTanding($id);
            if ($peserta === null) {
                return 'Peserta tidak ditemukan.';
            }
            $this->service->ubahStatusSertifikatTanding($id);
            $defaultKategori = ($peserta->jenis_medali ?? null)
                ? $this->service->kategoriJuara($peserta, 'tanding')
                : $this->service->kategoriPeserta($peserta, 'tanding');
        } else {
            $peserta = $this->service->getPesertaSeni($id);
            if ($peserta === null) {
                return 'Peserta tidak ditemukan.';
            }
            $this->service->ubahStatusSertifikatSeni($id);
            $defaultKategori = ($peserta->jenis_medali ?? null)
                ? $this->service->kategoriJuara($peserta, 'seni')
                : $this->service->kategoriPeserta($peserta, 'seni');
        }

        // Editable: query param menang atas data DB (parity modal cetak legacy).
        $nomor     = ($get['nomor'] ?? '') !== '' ? (string) $get['nomor'] : (string) ($peserta->nomor_sertifikat ?? '');
        $nama      = ($get['nama'] ?? '') !== '' ? (string) $get['nama'] : strtoupper((string) ($peserta->nama_pendaftar ?? ''));
        $kategori  = ($get['kategori'] ?? '') !== '' ? (string) $get['kategori'] : $defaultKategori;
        $kontingen = ($get['kontingen'] ?? '') !== '' ? (string) $get['kontingen'] : strtoupper((string) ($peserta->nama_kontingen ?? ''));
        $sekolah   = ($get['sekolah'] ?? '') !== '' ? (string) $get['sekolah'] : strtoupper((string) ($peserta->nama_sekolah ?? ''));

        return view('print/sertifikat/template', [
            'main_view'      => 'print/sertifikat/pages/manual_print',
            'paper_size'     => 'A4 landscape',
            'layout'         => $this->service->getLayoutConfig(),
            'background_url' => $this->service->backgroundUrl(),
            'hide_bg'        => $this->service->hideSertifikatBackground(),
            'nomor'          => $nomor,
            'nama'           => $nama,
            'kategori'       => $kategori,
            'kontingen'      => $kontingen,
            'sekolah'        => $sekolah,
            'qrcode_url'     => $this->service->qrcodeUrl($tipe, $id),
        ]);
    }

    // ============================================================
    //  API (barcode scan)
    // ============================================================

    public function apiPesertaTanding(int $id): \CodeIgniter\HTTP\Response
    {
        $p = $this->service->getPesertaTanding($id);
        if ($p === null) {
            return $this->response->setJSON(['status' => false]);
        }
        return $this->response->setJSON([
            'status'    => true,
            'nama'      => strtoupper((string) $p->nama_pendaftar),
            'kontingen' => strtoupper((string) $p->nama_kontingen),
            'sekolah'   => strtoupper((string) ($p->nama_sekolah ?? '')),
            'kategori'  => ($p->jenis_medali ?? null)
                ? $this->service->kategoriJuara($p, 'tanding')
                : $this->service->kategoriPeserta($p, 'tanding'),
            'nomor'      => $p->nomor_sertifikat ?? '',
            'qrcode_url' => $this->service->qrcodeUrl('tanding', $id),
        ]);
    }

    public function apiPesertaSeni(int $id): \CodeIgniter\HTTP\Response
    {
        $p = $this->service->getPesertaSeni($id);
        if ($p === null) {
            return $this->response->setJSON(['status' => false]);
        }
        return $this->response->setJSON([
            'status'    => true,
            'nama'      => strtoupper((string) $p->nama_pendaftar),
            'kontingen' => strtoupper((string) $p->nama_kontingen),
            'sekolah'   => strtoupper((string) ($p->nama_sekolah ?? '')),
            'kategori'  => ($p->jenis_medali ?? null)
                ? $this->service->kategoriJuara($p, 'seni')
                : $this->service->kategoriPeserta($p, 'seni'),
            'nomor'      => $p->nomor_sertifikat ?? '',
            'qrcode_url' => $this->service->qrcodeUrl('seni', $id),
        ]);
    }

    // ============================================================
    //  Nomor Sertifikat
    // ============================================================

    public function updateNomorSertifikatSuffix(): RedirectResponse
    {
        $suffix = trim((string) $this->request->getPost('nomor_sertifikat_suffix'));
        (new SettingWriterService())->setString('nomor_sertifikat_suffix', $suffix);
        return redirect()->to(base_url('admin/printer/dashboard'))
            ->with('status', true)->with('message', 'Suffix nomor sertifikat berhasil disimpan.');
    }

    /**
     * AJAX: generate nomor sertifikat untuk satu peserta.
     * POST: jenis (tanding|seni), id
     */
    public function generateNomorSertifikatAjax(): \CodeIgniter\HTTP\Response
    {
        $jenis = (string) $this->request->getPost('jenis');
        $id    = (int) $this->request->getPost('id');

        if (! in_array($jenis, ['tanding', 'seni'], true) || $id <= 0) {
            return $this->response->setJSON([
                'status'    => false,
                'message'   => 'Parameter tidak valid.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $nomor = $jenis === 'tanding'
            ? $this->service->generateNomorTandingSingle($id)
            : $this->service->generateNomorSeniSingle($id);

        return $this->response->setJSON([
            'status'    => true,
            'nomor'     => $nomor,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function generateSemuaNomorSertifikat(): RedirectResponse
    {
        $result = $this->service->generateBulkNomorSertifikat();
        return redirect()->to(base_url('admin/printer/dashboard'))
            ->with('status', true)
            ->with('message', "Berhasil generate {$result['generated']} nomor sertifikat." . ($result['skipped'] > 0 ? " ({$result['skipped']} dilewati)" : ''));
    }

    public function resetNomorSertifikat(): RedirectResponse
    {
        $input = (string) $this->request->getPost('pass_code');
        if ($input !== env('DEV_SECURITY_PASSCODE', '4321')) {
            return redirect()->to(base_url('admin/printer/dashboard'))
                ->with('status', false)->with('message', 'Passcode salah! Reset dibatalkan.');
        }
        $this->service->resetSemuaNomorSertifikat();
        return redirect()->to(base_url('admin/printer/dashboard'))
            ->with('status', true)->with('message', 'Semua nomor sertifikat berhasil direset.');
    }

    public function statistikNomorSertifikatAjax(): \CodeIgniter\HTTP\Response
    {
        return $this->response->setJSON($this->service->getStatistikNomorSertifikat());
    }

    // ============================================================
    //  Helpers
    // ============================================================

    private function viewData(array $data, string $title, string $activeMenu = 'dashboard'): array
    {
        return $data + [
            'title'      => $title,
            'activeMenu' => $activeMenu,
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Printer'),
        ];
    }
}
