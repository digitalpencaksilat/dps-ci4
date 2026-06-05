<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\IdCardService;
use CodeIgniter\HTTP\RedirectResponse;

class IdCardController extends BaseController
{
    private IdCardService $idCardService;

    public function __construct()
    {
        $this->idCardService = new IdCardService();
    }

    // ============================================================
    //  Dashboard
    // ============================================================

    public function index(): string
    {
        $kontingenRows = db_connect()
            ->table('kontingen')
            ->select('kontingen.*')
            ->select('(SELECT COUNT(*) FROM pendaftar p JOIN peserta_tanding pt ON pt.id_pendaftar = p.id_pendaftar WHERE p.id_kontingen = kontingen.id_kontingen) AS jml_tanding', false)
            ->select('(SELECT COUNT(*) FROM pendaftar p JOIN peserta_seni ps ON ps.id_pendaftar = p.id_pendaftar WHERE p.id_kontingen = kontingen.id_kontingen) AS jml_seni', false)
            ->orderBy('nama_kontingen', 'ASC')
            ->get()
            ->getResult();

        $totalTanding = array_sum(array_map(static fn($r) => (int) $r->jml_tanding, $kontingenRows));
        $totalSeni = array_sum(array_map(static fn($r) => (int) $r->jml_seni, $kontingenRows));

        return view('admin/sekretariat/id_card/index', $this->viewData([
            'kontingenRows' => $kontingenRows,
            'totalTanding'  => $totalTanding,
            'totalSeni'     => $totalSeni,
            'hasBackground' => $this->idCardService->hasBackground(),
        ], 'Pencetakan ID Card'));
    }

    // ============================================================
    //  Layout Configuration
    // ============================================================

    public function pengaturanTataLetak(string $section = 'nama_atlet'): string
    {
        $validSections = ['foto_atlet', 'nama_atlet', 'nama_kontingen', 'barcode', 'pertandingan'];
        if (! in_array($section, $validSections, true)) {
            $section = 'nama_atlet';
        }

        $labels = [
            'foto_atlet'     => 'Foto Atlet',
            'nama_atlet'     => 'Nama Atlet',
            'nama_kontingen' => 'Nama Kontingen',
            'barcode'        => 'Barcode',
            'pertandingan'   => 'Informasi Pertandingan',
        ];

        return view('admin/sekretariat/id_card/pengaturan_tata_letak', $this->viewData([
            'currentSection' => $section,
            'sectionLabel'   => $labels[$section] ?? $section,
            'sections'       => $labels,
            'fields'         => $this->idCardService->getLayoutSection($section),
            'errors'         => session()->getFlashdata('errors') ?? [],
        ], 'Pengaturan Tata Letak ID Card'));
    }

    public function simpanTataLetak(): RedirectResponse
    {
        $section = (string) $this->request->getPost('section');
        $validSections = ['foto_atlet', 'nama_atlet', 'nama_kontingen', 'barcode', 'pertandingan'];

        if (! in_array($section, $validSections, true)) {
            return redirect()->back()->with('status', false)->with('message', 'Section tidak valid.');
        }

        $current = $this->idCardService->getLayoutConfig();
        $fields = $current[$section] ?? [];

        foreach (array_keys($fields) as $key) {
            $value = $this->request->getPost($key);
            if ($value !== null) {
                $current[$section][$key] = trim((string) $value);
            }
        }

        $this->idCardService->saveLayoutConfig($current);

        return redirect()->to(base_url('admin/sekretariat/id-card/pengaturan-tata-letak/' . $section))
            ->with('status', true)
            ->with('message', 'Tata letak ' . $section . ' berhasil disimpan.');
    }

    // ============================================================
    //  Upload Background
    // ============================================================

    public function uploadBackground(): RedirectResponse
    {
        $file = $this->request->getFile('id_card');

        if ($file === null) {
            return redirect()->back()->with('status', false)->with('message', 'Tidak ada file yang diunggah.');
        }

        try {
            $this->idCardService->uploadBackground($file);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/sekretariat/id-card'))
            ->with('status', true)
            ->with('message', 'Background ID Card berhasil diunggah.');
    }

    // ============================================================
    //  Preview
    // ============================================================

    public function preview(): string
    {
        helper('kartu_peserta');

        $pesertaTanding = $this->idCardService->getCardDataTanding(
            $this->idCardService->getAllPesertaTandingIds()[0] ?? 0
        );

        if ($pesertaTanding === null) {
            return view('admin/sekretariat/id_card/preview_empty', $this->viewData([], 'Preview ID Card'));
        }

        $partai = get_partai_pertandingan(
            $this->idCardService->getPertandinganData((int) $pesertaTanding->id_kompetisi_tanding),
            (int) $pesertaTanding->id_peserta_tanding
        );

        // Render preview using the print template
        return view('print/id_card/template', [
            'main_view'      => 'print/id_card/pages/preview',
            'paper_size'     => 'A6 portrait',
            'peserta'        => $pesertaTanding,
            'partai'         => $partai,
            'layout'         => $this->idCardService->getLayoutConfig(),
            'background_url' => $this->idCardService->backgroundUrl(),
            'barcode_value'  => IdCardService::barcodeValueTanding((int) $pesertaTanding->id_peserta_tanding),
            'is_preview'     => true,
        ]);
    }

    // ============================================================
    //  Print Single Card
    // ============================================================

    public function cetakSingle(string $tipe, int $id): string
    {
        helper('kartu_peserta');

        if ($tipe === 'tanding') {
            $peserta = $this->idCardService->getCardDataTanding($id);
            if ($peserta === null) {
                return 'Peserta tanding tidak ditemukan.';
            }

            $partai = get_partai_pertandingan(
                $this->idCardService->getPertandinganData((int) $peserta->id_kompetisi_tanding),
                (int) $peserta->id_peserta_tanding
            );

            $mainView = 'print/id_card/pages/peserta_tanding';
            $barcodeValue = IdCardService::barcodeValueTanding((int) $peserta->id_peserta_tanding);
        } else {
            $peserta = $this->idCardService->getCardDataSeni($id);
            if ($peserta === null) {
                return 'Peserta seni tidak ditemukan.';
            }

            $dataPenampilan = $this->idCardService->getPenampilanSeniData((int) $peserta->id_kompetisi_seni);
            $dataBattle = $this->idCardService->getBattleSeniData((int) $peserta->id_kompetisi_seni);

            $mainView = 'print/id_card/pages/peserta_seni';
            $barcodeValue = IdCardService::barcodeValueSeni((int) $peserta->id_peserta_seni);
        }

        return view('print/id_card/template', [
            'main_view'        => $mainView,
            'paper_size'       => 'A6 portrait',
            'peserta'          => $peserta,
            'partai'           => $partai ?? [],
            'data_penampilan'  => $dataPenampilan ?? [],
            'data_battle'      => $dataBattle ?? [],
            'layout'           => $this->idCardService->getLayoutConfig(),
            'background_url'   => $this->idCardService->backgroundUrl(),
            'barcode_value'    => $barcodeValue,
            'is_preview'       => false,
        ]);
    }

    // ============================================================
    //  Print Batch
    // ============================================================

    public function cetakPerKontingen(): string
    {
        $kontingenRows = db_connect()
            ->table('kontingen')
            ->select('id_kontingen, nama_kontingen, jenis_kontingen')
            ->select('(SELECT COUNT(*) FROM pendaftar p JOIN peserta_tanding pt ON pt.id_pendaftar = p.id_pendaftar WHERE p.id_kontingen = kontingen.id_kontingen) AS jml_tanding', false)
            ->select('(SELECT COUNT(*) FROM pendaftar p JOIN peserta_seni ps ON ps.id_pendaftar = p.id_pendaftar WHERE p.id_kontingen = kontingen.id_kontingen) AS jml_seni', false)
            ->orderBy('nama_kontingen', 'ASC')
            ->get()
            ->getResult();

        return view('admin/sekretariat/id_card/cetak_per_kontingen', $this->viewData([
            'kontingenRows' => $kontingenRows,
        ], 'Cetak ID Card Per Kontingen'));
    }

    public function cetakPerPeserta(): string
    {
        $kontingenRows = db_connect()
            ->table('kontingen')
            ->select('id_kontingen, nama_kontingen')
            ->orderBy('nama_kontingen', 'ASC')
            ->get()
            ->getResult();

        return view('admin/sekretariat/id_card/cetak_per_peserta', $this->viewData([
            'kontingenRows' => $kontingenRows,
        ], 'Cetak ID Card Per Peserta'));
    }

    /**
     * AJAX endpoint to get tanding peserta by kontingen.
     */
    public function apiPesertaTanding(int $idKontingen): \CodeIgniter\HTTP\Response
    {
        $rows = db_connect()
            ->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding, p.nama_pendaftar, ku.nama_kategori_usia, ku.jenis_kelamin, kt.label')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
            ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('p.id_kontingen', $idKontingen)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON($rows);
    }

    /**
     * AJAX endpoint to get seni peserta by kontingen.
     */
    public function apiPesertaSeni(int $idKontingen): \CodeIgniter\HTTP\Response
    {
        $rows = db_connect()
            ->table('peserta_seni ps')
            ->select('ps.id_peserta_seni, p.nama_pendaftar, ku.nama_kategori_usia, ku.jenis_kelamin, sks.nama_seni, sks.jenis_seni')
            ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni kom', 'kom.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = kom.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('p.id_kontingen', $idKontingen)
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON($rows);
    }

    /**
     * Process batch print: accepts POST arrays and renders all cards.
     */
    public function prosesCetakBatch(): string
    {
        helper('kartu_peserta');

        $tandingIds = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) $this->request->getPost('id_peserta_tanding')
        ))));

        $seniIds = array_values(array_unique(array_filter(array_map(
            'intval',
            (array) $this->request->getPost('id_peserta_seni')
        ))));

        if ($tandingIds === [] && $seniIds === []) {
            return 'Tidak ada peserta yang dipilih.';
        }

        $cards = [];
        $layout = $this->idCardService->getLayoutConfig();
        $backgroundUrl = $this->idCardService->backgroundUrl();

        foreach ($tandingIds as $idTanding) {
            $peserta = $this->idCardService->getCardDataTanding($idTanding);
            if ($peserta === null) {
                continue;
            }
            $partai = get_partai_pertandingan(
                $this->idCardService->getPertandinganData((int) $peserta->id_kompetisi_tanding),
                (int) $peserta->id_peserta_tanding
            );
            $cards[] = [
                'type'          => 'tanding',
                'peserta'       => $peserta,
                'partai'        => $partai,
                'barcode_value' => IdCardService::barcodeValueTanding((int) $peserta->id_peserta_tanding),
            ];
        }

        foreach ($seniIds as $idSeni) {
            $peserta = $this->idCardService->getCardDataSeni($idSeni);
            if ($peserta === null) {
                continue;
            }
            $dataPenampilan = $this->idCardService->getPenampilanSeniData((int) $peserta->id_kompetisi_seni);
            $dataBattle = $this->idCardService->getBattleSeniData((int) $peserta->id_kompetisi_seni);
            $cards[] = [
                'type'            => 'seni',
                'peserta'         => $peserta,
                'data_penampilan' => $dataPenampilan,
                'data_battle'     => $dataBattle,
                'barcode_value'   => IdCardService::barcodeValueSeni((int) $peserta->id_peserta_seni),
            ];
        }

        return view('print/id_card/template', [
            'main_view'      => 'print/id_card/pages/batch',
            'paper_size'     => 'A4 portrait',
            'cards'          => $cards,
            'layout'         => $layout,
            'background_url' => $backgroundUrl,
            'is_iframe'      => true,
        ]);
    }

    // ============================================================
    //  Shared View Data
    // ============================================================

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function viewData(array $data, string $title): array
    {
        return $data + [
            'title'       => $title,
            'activeMenu'  => 'pencetakan_id_card',
            'eventName'   => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'   => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'   => (string) (session()->get('nama') ?? session()->get('username') ?? 'Sekretariat'),
            'activeMode'  => (string) (session()->get('tipe_super_admin') ?? ''),
        ];
    }
}
