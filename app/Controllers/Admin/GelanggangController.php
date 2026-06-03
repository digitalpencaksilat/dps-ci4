<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GelanggangModel;
use App\Models\JadwalTandingModel;
use App\Models\JadwalSeniModel;
use App\Services\PdfMergeService;

class GelanggangController extends BaseController
{
    protected $gelanggangModel;
    protected $jadwalTandingModel;
    protected $jadwalSeniModel;
    protected $pdfMergeService;

    public function __construct()
    {
        $this->gelanggangModel = new GelanggangModel();
        $this->jadwalTandingModel = new JadwalTandingModel();
        $this->jadwalSeniModel = new JadwalSeniModel();
        $this->pdfMergeService = new PdfMergeService();
    }

    public function index()
    {
        // Only super_admin can access
        if (session()->get('level') !== 'super_admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $data = [
            'title' => 'Daftar Gelanggang',
            'breadcrumb' => 'Daftar Gelanggang',
            'activeMenu' => 'pembuatan_jadwal_gelanggang',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'data_gelanggang' => $this->gelanggangModel->orderBy('nomor_gelanggang', 'ASC')->findAll(),
        ];

        return view('admin/gelanggang/index', $data);
    }

    public function create()
    {
        if (session()->get('level') !== 'super_admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $rules = [
            'nama_gelanggang' => 'required|min_length[3]|max_length[100]',
            'nomor_gelanggang' => 'required|integer',
            'keterangan' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama_gelanggang' => $this->request->getPost('nama_gelanggang'),
            'nomor_gelanggang' => $this->request->getPost('nomor_gelanggang'),
            'keterangan' => $this->request->getPost('keterangan'),
        ];

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $id_gelanggang = $this->gelanggangModel->insert($data);

            if ($id_gelanggang) {
                // Create related perangkat_pertandingan records
                $this->createPerangkatPertandinganGelanggang($id_gelanggang);
                
                // Create related broadcast_graphic records if table exists
                $this->createBroadcastGraphicGelanggang($id_gelanggang);

                $db->transCommit();
                return redirect()->to('/admin/gelanggang')->with('success', 'Gelanggang berhasil ditambahkan');
            } else {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Gagal menambahkan gelanggang');
            }
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to create gelanggang: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan gelanggang: ' . $e->getMessage());
        }
    }

    public function delete($id_gelanggang)
    {
        if (session()->get('level') !== 'super_admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $gelanggang = $this->gelanggangModel->find($id_gelanggang);
        if (!$gelanggang) {
            return redirect()->to('/admin/gelanggang')->with('error', 'Gelanggang tidak ditemukan');
        }

        if ($this->gelanggangModel->delete($id_gelanggang)) {
            return redirect()->to('/admin/gelanggang')->with('success', 'Gelanggang berhasil dihapus');
        } else {
            return redirect()->to('/admin/gelanggang')->with('error', 'Gagal menghapus gelanggang');
        }
    }

    public function mergeJadwal($id_gelanggang)
    {
        // Check access: super_admin or sekretaris
        if (session()->get('level') !== 'super_admin' && session()->get('posisi') !== 'sekretaris') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $result = $this->prepareMergedJadwal($id_gelanggang);

        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message']);
        }

        try {
            $mergedPdfPath = $this->pdfMergeService->mergePdfFiles($result['files']);
            
            $gelanggang = $this->gelanggangModel->find($id_gelanggang);
            $event_name = get_setting('event_name') ?? 'Event';
            $output_filename = 'Jadwal Arena ' . $gelanggang->nama_gelanggang . ' - ' . $event_name . ' - version ' . date('d-M-Y - H.i') . '.pdf';

            return $this->response->download($mergedPdfPath, null)->setFileName($output_filename);
        } catch (\Exception $e) {
            log_message('error', 'PDF merge failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal merge PDF: ' . $e->getMessage());
        }
    }

    public function mergeJadwalByDate($id_gelanggang)
    {
        // Check access: super_admin or sekretaris
        if (session()->get('level') !== 'super_admin' && session()->get('posisi') !== 'sekretaris') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $tanggal = $this->request->getPost('tanggal');

        if (empty($tanggal)) {
            return redirect()->back()->with('error', 'Harap pilih tanggal');
        }

        $result = $this->prepareMergedJadwalByDate($id_gelanggang, $tanggal);

        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message']);
        }

        try {
            $mergedPdfPath = $this->pdfMergeService->mergePdfFiles($result['files']);

            $gelanggang = $this->gelanggangModel->find($id_gelanggang);
            
            // Format date Indonesian style
            $date_obj = new \DateTime($tanggal);
            $bulan_indonesia = [
                1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
                5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
                9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
            ];
            $tanggal_indo = $date_obj->format('d') . ' ' . $bulan_indonesia[(int)$date_obj->format('m')] . ' ' . $date_obj->format('Y');
            
            $event_name = strtoupper(get_setting('event_name') ?? 'EVENT');
            $arena_name = strtoupper($gelanggang->nama_gelanggang);
            $output_filename = 'ARENA ' . $arena_name . ' - ' . $tanggal_indo . ' - ' . $event_name . '.pdf';

            return $this->response->download($mergedPdfPath, null)->setFileName($output_filename);
        } catch (\Exception $e) {
            log_message('error', 'PDF merge by date failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal merge PDF: ' . $e->getMessage());
        }
    }

    public function getDatesJson($id_gelanggang)
    {
        // Check access: super_admin or sekretaris
        if (session()->get('level') !== 'super_admin' && session()->get('posisi') !== 'sekretaris') {
            return $this->response->setJSON(['error' => 'Access denied']);
        }

        $dates = $this->getAvailableDates($id_gelanggang);
        return $this->response->setJSON($dates);
    }

    public function mergeJadwalAllArena()
    {
        // Only super_admin
        if (session()->get('level') !== 'super_admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak');
        }

        $gelanggang_list = $this->gelanggangModel->findAll();
        
        if (empty($gelanggang_list)) {
            return redirect()->back()->with('error', 'Tidak ada gelanggang ditemukan');
        }

        $all_files = [];

        foreach ($gelanggang_list as $gelanggang) {
            $result = $this->prepareMergedJadwal($gelanggang->id_gelanggang);

            if (!$result['status']) {
                // Skip arena without schedules instead of failing
                continue;
            }

            $all_files = array_merge($all_files, $result['files']);
        }

        if (empty($all_files)) {
            return redirect()->back()->with('error', 'Tidak ada jadwal PDF untuk di-merge');
        }

        try {
            $mergedPdfPath = $this->pdfMergeService->mergePdfFiles($all_files);
            
            $event_name = get_setting('event_name') ?? 'Event';
            $output_filename = 'Jadwal Semua Arena - ' . $event_name . ' - version ' . date('d-M-Y - H.i') . '.pdf';

            return $this->response->download($mergedPdfPath, null)->setFileName($output_filename);
        } catch (\Exception $e) {
            log_message('error', 'PDF merge all arena failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal merge PDF semua arena: ' . $e->getMessage());
        }
    }

    // ========== PRIVATE HELPERS ==========

    private function prepareMergedJadwal($id_gelanggang)
    {
        $gelanggang = $this->gelanggangModel->find($id_gelanggang);

        if (!$gelanggang) {
            return [
                'status' => false,
                'message' => 'Failed, Arena not found'
            ];
        }

        $db = \Config\Database::connect();

        $data_jadwal_tanding = $db->table('jadwal_tanding jt')
            ->select('jt.*, (SELECT nomor_partai FROM detail_jadwal_tanding WHERE detail_jadwal_tanding.id_jadwal_tanding = jt.id_jadwal_tanding ORDER BY nomor_partai ASC LIMIT 1) as nomor_partai_awal', false)
            ->where('jt.id_gelanggang', $id_gelanggang)
            ->get()
            ->getResult();

        $data_jadwal_seni = $db->table('jadwal_seni js')
            ->select('js.*, (SELECT nomor_partai FROM detail_jadwal_seni WHERE detail_jadwal_seni.id_jadwal_seni = js.id_jadwal_seni ORDER BY nomor_partai ASC LIMIT 1) as nomor_partai_awal', false)
            ->where('js.id_gelanggang', $id_gelanggang)
            ->get()
            ->getResult();

        $data_jadwal_gabungan = array_merge($data_jadwal_seni, $data_jadwal_tanding);

        if (empty($data_jadwal_gabungan)) {
            return [
                'status' => false,
                'message' => 'Failed, No Schedule Found: Arena ' . $gelanggang->nama_gelanggang . ' is empty'
            ];
        }

        // Sort by nomor_partai_awal dengan fallback aman bila field belum ada/null.
        usort($data_jadwal_gabungan, function ($a, $b) {
            $nomorA = isset($a->nomor_partai_awal) && $a->nomor_partai_awal !== null ? (int) $a->nomor_partai_awal : PHP_INT_MAX;
            $nomorB = isset($b->nomor_partai_awal) && $b->nomor_partai_awal !== null ? (int) $b->nomor_partai_awal : PHP_INT_MAX;

            if ($nomorA === $nomorB) {
                $jamA = (string) ($a->jam_mulai ?? '99:99:99');
                $jamB = (string) ($b->jam_mulai ?? '99:99:99');

                if ($jamA === $jamB) {
                    return strcmp((string) ($a->nama_file ?? ''), (string) ($b->nama_file ?? ''));
                }

                return strcmp($jamA, $jamB);
            }

            return $nomorA <=> $nomorB;
        });

        $file_paths = [];
        foreach ($data_jadwal_gabungan as $jadwal) {
            $file_path = isset($jadwal->id_jadwal_tanding)
                ? 'uploads/jadwal-pdf/tanding/'
                : 'uploads/jadwal-pdf/seni/';

            $full_path = FCPATH . $file_path . $jadwal->nama_file;

            if (!file_exists($full_path)) {
                return [
                    'status' => false,
                    'message' => 'Failed, File not found: ' . $jadwal->nama_file . ' in ' . $full_path
                ];
            }

            $file_paths[] = $full_path;
        }

        return [
            'status' => true,
            'gelanggang_name' => $gelanggang->nama_gelanggang,
            'files' => $file_paths
        ];
    }

    private function prepareMergedJadwalByDate($id_gelanggang, $tanggal)
    {
        $gelanggang = $this->gelanggangModel->find($id_gelanggang);

        if (!$gelanggang) {
            return [
                'status' => false,
                'message' => 'Failed, Arena not found'
            ];
        }

        $db = \Config\Database::connect();

        $data_jadwal_tanding = $db->table('jadwal_tanding jt')
            ->select('jt.*, (SELECT nomor_partai FROM detail_jadwal_tanding WHERE detail_jadwal_tanding.id_jadwal_tanding = jt.id_jadwal_tanding ORDER BY nomor_partai ASC LIMIT 1) as nomor_partai_awal', false)
            ->where('jt.id_gelanggang', $id_gelanggang)
            ->where('jt.tanggal', $tanggal)
            ->get()
            ->getResult();

        $data_jadwal_seni = $db->table('jadwal_seni js')
            ->select('js.*, (SELECT nomor_partai FROM detail_jadwal_seni WHERE detail_jadwal_seni.id_jadwal_seni = js.id_jadwal_seni ORDER BY nomor_partai ASC LIMIT 1) as nomor_partai_awal', false)
            ->where('js.id_gelanggang', $id_gelanggang)
            ->where('js.tanggal', $tanggal)
            ->get()
            ->getResult();

        $data_jadwal_gabungan = array_merge($data_jadwal_seni, $data_jadwal_tanding);

        if (empty($data_jadwal_gabungan)) {
            return [
                'status' => false,
                'message' => 'Failed, No Schedule Found for Arena ' . $gelanggang->nama_gelanggang . ' on date ' . $tanggal
            ];
        }

        // Sort by nomor_partai_awal dengan fallback aman bila field belum ada/null.
        usort($data_jadwal_gabungan, function ($a, $b) {
            $nomorA = isset($a->nomor_partai_awal) && $a->nomor_partai_awal !== null ? (int) $a->nomor_partai_awal : PHP_INT_MAX;
            $nomorB = isset($b->nomor_partai_awal) && $b->nomor_partai_awal !== null ? (int) $b->nomor_partai_awal : PHP_INT_MAX;

            if ($nomorA === $nomorB) {
                $jamA = (string) ($a->jam_mulai ?? '99:99:99');
                $jamB = (string) ($b->jam_mulai ?? '99:99:99');

                if ($jamA === $jamB) {
                    return strcmp((string) ($a->nama_file ?? ''), (string) ($b->nama_file ?? ''));
                }

                return strcmp($jamA, $jamB);
            }

            return $nomorA <=> $nomorB;
        });

        $file_paths = [];
        foreach ($data_jadwal_gabungan as $jadwal) {
            $file_path = isset($jadwal->id_jadwal_tanding)
                ? 'uploads/jadwal-pdf/tanding/'
                : 'uploads/jadwal-pdf/seni/';

            $full_path = FCPATH . $file_path . $jadwal->nama_file;

            if (!file_exists($full_path)) {
                return [
                    'status' => false,
                    'message' => 'Failed, File not found: ' . $jadwal->nama_file . ' in ' . $full_path
                ];
            }

            $file_paths[] = $full_path;
        }

        return [
            'status' => true,
            'gelanggang_name' => $gelanggang->nama_gelanggang,
            'tanggal' => $tanggal,
            'files' => $file_paths
        ];
    }

    private function getAvailableDates($id_gelanggang)
    {
        $db = \Config\Database::connect();

        // Get unique dates from jadwal_tanding
        $dates_tanding = $db->table('jadwal_tanding')
            ->select('tanggal')
            ->distinct()
            ->where('id_gelanggang', $id_gelanggang)
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResult();

        // Get unique dates from jadwal_seni
        $dates_seni = $db->table('jadwal_seni')
            ->select('tanggal')
            ->distinct()
            ->where('id_gelanggang', $id_gelanggang)
            ->orderBy('tanggal', 'ASC')
            ->get()
            ->getResult();

        // Merge and get unique dates
        $all_dates = [];
        foreach ($dates_tanding as $date) {
            if ($date->tanggal) {
                $all_dates[$date->tanggal] = $date->tanggal;
            }
        }
        foreach ($dates_seni as $date) {
            if ($date->tanggal) {
                $all_dates[$date->tanggal] = $date->tanggal;
            }
        }

        // Sort dates
        ksort($all_dates);

        return array_values($all_dates);
    }

    private function createPerangkatPertandinganGelanggang($id_gelanggang)
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('perangkat_pertandingan')) {
            return;
        }

        $namaGelanggang = (string) ($this->request->getPost('nama_gelanggang') ?? ('gelanggang_' . $id_gelanggang));
        $slugGelanggang = preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim($namaGelanggang))) ?: ('gelanggang_' . $id_gelanggang);

        for ($i = 1; $i <= 10; $i++) {
            $username = 'juri' . $i . '_' . $slugGelanggang;
            $db->table('perangkat_pertandingan')->insert([
                'id_gelanggang' => $id_gelanggang,
                'nama' => 'juri ' . $i . ' ' . $namaGelanggang,
                'username' => $username,
                'password' => password_hash($username, PASSWORD_BCRYPT, ['cost' => 10]),
                'posisi' => 'juri',
            ]);
        }

        $perangkatTambahan = [
            [
                'nama' => 'timer ' . $namaGelanggang,
                'username' => 'timer_' . $slugGelanggang,
                'posisi' => 'sekretaris',
            ],
            [
                'nama' => 'kp ' . $namaGelanggang,
                'username' => 'kp_' . $slugGelanggang,
                'posisi' => 'ketua_pertandingan',
            ],
            [
                'nama' => 'sekretaris ' . $namaGelanggang,
                'username' => 'sekretaris_' . $slugGelanggang,
                'posisi' => 'sekretaris',
            ],
            [
                'nama' => 'layar_ ' . $namaGelanggang,
                'username' => 'layar_' . $slugGelanggang,
                'posisi' => 'layar',
            ],
            [
                'nama' => 'monitor_ ' . $namaGelanggang,
                'username' => 'monitor_' . $slugGelanggang,
                'posisi' => 'layar',
            ],
            [
                'nama' => 'broadcast_operator ' . $namaGelanggang,
                'username' => 'broadcast_operator_' . $slugGelanggang,
                'posisi' => 'broadcast_operator',
            ],
        ];

        foreach ($perangkatTambahan as $perangkat) {
            $db->table('perangkat_pertandingan')->insert([
                'id_gelanggang' => $id_gelanggang,
                'nama' => $perangkat['nama'],
                'username' => $perangkat['username'],
                'password' => password_hash($perangkat['username'], PASSWORD_BCRYPT, ['cost' => 10]),
                'posisi' => $perangkat['posisi'],
            ]);
        }
    }

    private function createBroadcastGraphicGelanggang($id_gelanggang)
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('broadcast_graphic')) {
            return;
        }

        $tandingScenes = ['intro', 'lower-third-intro', 'scoreboard', 'highlight-hukuman-biru', 'highlight-hukuman-merah', 'match-stats', 'next-match-lower-third', 'next-match-table', 'match-bracket', 'verification-dialog'];
        $seniScenes = ['intro', 'lower-third-intro', 'scoreboard', 'match-stats', 'next-match-lower-third', 'next-match-table', 'match-bracket'];

        foreach ($tandingScenes as $scene) {
            $db->table('broadcast_graphic')->insert([
                'id_gelanggang' => $id_gelanggang,
                'jenis' => 'tanding',
                'scene' => $scene,
                'status' => 'active',
            ]);
        }

        foreach ($seniScenes as $scene) {
            $db->table('broadcast_graphic')->insert([
                'id_gelanggang' => $id_gelanggang,
                'jenis' => 'seni',
                'scene' => $scene,
                'status' => 'active',
            ]);
        }
    }
}
