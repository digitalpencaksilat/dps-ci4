<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Models\GelanggangModel;
use App\Models\JadwalSeniModel;
use App\Models\JadwalTandingModel;
use App\Models\KelasTandingModel;
use App\Models\KompetisiTandingModel;
use App\Services\Admin\Super\PembuatanJadwalAuditService;
use App\Services\Pdf\MpdfService;
use App\Services\SekretariatKategoriSeniService;
use App\Services\SekretariatKategoriTandingService;
use App\Services\SistemGugurTunggalService;

class PembuatanJadwalController extends BaseController
{
    public function dashboard(): string
    {
        $gelanggangModel    = new GelanggangModel();
        $jadwalTandingModel = new JadwalTandingModel();
        $jadwalSeniModel    = new JadwalSeniModel();
        $auditService       = new PembuatanJadwalAuditService();
        $auditData          = $auditService->getDashboardAudit();

        return view('admin/super/dashboard_pembuatan_jadwal', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_dashboard',
            'data_gelanggang' => $gelanggangModel->findAll(),
            'data_jadwal_tanding' => $jadwalTandingModel->get_all(),
            'data_jadwal_seni' => $jadwalSeniModel->get_all(),
        ] + $auditData, 'Dashboard Pembuatan Jadwal'));
    }

    public function operasiBasisData(): string
    {
        $db = db_connect();

        return view('admin/super/operasi_basis_data', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_operasi_basis_data',
            'stats' => $this->scheduleDatabaseStats($db),
            'checks' => $this->scheduleDatabaseChecks($db),
        ], 'Operasi Basis Data'));
    }

    public function resetSeluruhJadwal()
    {
        if ((string) $this->request->getPost('confirm') !== 'RESET JADWAL') {
            return redirect()->back()->with('status', false)->with('message', 'Konfirmasi tidak sesuai. Ketik RESET JADWAL untuk melanjutkan.');
        }

        $db = db_connect();
        $db->transStart();
        $db->table('detail_jadwal_tanding')->emptyTable();
        $db->table('detail_jadwal_seni')->emptyTable();
        $db->table('jadwal_tanding')->emptyTable();
        $db->table('jadwal_seni')->emptyTable();
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('status', false)->with('message', 'Gagal reset seluruh jadwal.');
        }

        return redirect()->to(base_url('admin/super/operasi-basis-data'))->with('status', true)->with('message', 'Seluruh jadwal tanding dan seni berhasil di-reset.');
    }

    public function drawingTanding(): string
    {
        return view('admin/super/drawing_tanding', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_drawing_tanding',
            'kategoriRows' => $this->drawingKategoriTandingRows(),
            'summary' => $this->drawingTandingSummary(),
        ], 'Drawing Tanding'));
    }

    public function drawingSeni(): string
    {
        return view('admin/super/drawing_seni', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_drawing_seni',
            'kategoriRows' => $this->drawingKategoriSeniRows(),
            'summary' => $this->drawingSeniSummary(),
        ], 'Drawing Seni'));
    }

    /**
     * CI3: Super_admin::laporan_hasil_drawing_bagan_tanding
     */
    public function laporanHasilDrawingBaganTanding(): string
    {
        return view('admin/super/report/laporan_hasil_drawing_bagan_tanding', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_laporan_hasil_drawing_bagan_tanding',
            'data_peserta_tanding_tanpa_lawan' => $this->reportPesertaTandingTanpaLawan(),
            'data_peserta_tanding_bertemu_kontingen_sendiri_diatas_dua_peserta' => $this->reportPesertaKontingenSendiri('> 2'),
            'data_peserta_tanding_bertemu_kontingen_sendiri_dua_peserta' => $this->reportPesertaKontingenSendiri('= 2'),
            'data_kelas_tanding' => $this->reportKelasTandingKuotaTersedia(),
        ], 'Laporan Hasil Drawing Bagan Tanding'));
    }

    public function generateBaganTandingDariJadwal(): string
    {
        return view('admin/super/generate_bagan_tanding_dari_jadwal', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_generate_bagan_tanding',
            'rows' => $this->baganTandingScheduleRows(),
        ], 'Generate Bagan Tanding dari Jadwal'));
    }

    public function generateBaganSeniBattleDariJadwal(): string
    {
        return view('admin/super/generate_bagan_seni_battle_dari_jadwal', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_generate_bagan_seni_battle',
            'rows' => $this->baganSeniScheduleRows(),
        ], 'Generate Bagan Seni Battle dari Jadwal'));
    }

    public function distribusikanPesertaTanding()
    {
        $ids = $this->normalizeIds($this->request->getPost('id_kategori_lomba'));
        $mode = (string) ($this->request->getPost('mode') ?? 'prestasi');
        if ($ids === [] || ! in_array($mode, ['prestasi', 'pemasalan', 'komposisi_seimbang', 'komposisi_lengkap'], true)) {
            return redirect()->back()->with('status', false)->with('message', 'Pilih minimal satu kategori dan metode distribusi yang valid.');
        }

        $result = $this->distributeTandingByKategori($ids, $mode);

        return redirect()->to(base_url('admin/super/drawing-tanding'))->with('status', $result['gagal'] === 0)->with('message', $result['message']);
    }

    public function acakBaganTandingBulk()
    {
        $ids = $this->normalizeIds($this->request->getPost('id_kategori_lomba_bagan'));
        if ($ids === []) {
            return redirect()->back()->with('status', false)->with('message', 'Pilih minimal satu kategori tanding untuk acak bagan.');
        }

        $result = $this->bulkAcakBaganTanding($ids);

        return redirect()->to(base_url('admin/super/drawing-tanding'))->with('status', $result['gagal'] === 0)->with('message', $result['message']);
    }

    public function distribusikanPesertaTandingTanpaLawan(int $toleransi)
    {
        if (! in_array($toleransi, [3, 5, 7, 10, 13], true)) {
            return redirect()->back()->with('status', false)->with('message', 'Toleransi berat badan tidak valid.');
        }

        $start = microtime(true);
        $db = db_connect();
        $kelasModel = new KelasTandingModel();
        $sukses = 0;
        $gagal = 0;
        $errors = [];

        $pools = $db->table('kompetisi_tanding kom')
            ->select('kom.id_kelas_tanding')
            ->having('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) = 1', null, false)
            ->get()->getResult();

        $kelasIds = array_values(array_unique(array_filter(array_map(static fn ($row): int => (int) ($row->id_kelas_tanding ?? 0), $pools), static fn (int $id): bool => $id > 0)));

        foreach ($kelasIds as $idKelasTanding) {
            try {
                $kelasModel->distribukan_peserta_tanding_tanpa_lawan($idKelasTanding, true, $toleransi);
                $sukses++;
            } catch (\Throwable $e) {
                $gagal++;
                $errors[] = 'Kelas ' . $idKelasTanding . ': ' . $e->getMessage();
                log_message('error', 'Gagal distribusi peserta tanding tanpa lawan: {message}', ['message' => $e->getMessage()]);
            }
        }

        $message = sprintf('Berhasil - (Benchmark %.3f detik)', microtime(true) - $start);
        if ($gagal > 0) {
            $message .= sprintf(' | Gagal: %d', $gagal);
        }
        if ($errors !== []) {
            $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
        }

        return redirect()->to(base_url('admin/super/drawing-tanding'))->with('status', $gagal === 0)->with('message', $message);
    }

    public function pisahkanKontingenTanding()
    {
        $start = microtime(true);
        $db = db_connect();
        $kelasModel = new KelasTandingModel();
        $sukses = 0;
        $gagal = 0;
        $errors = [];

        $kelasRows = $db->table('kelas_tanding')
            ->select('id_kelas_tanding')
            ->get()->getResult();

        foreach ($kelasRows as $kelas) {
            $idKelasTanding = (int) ($kelas->id_kelas_tanding ?? 0);
            if ($idKelasTanding <= 0) {
                continue;
            }
            try {
                $kelasModel->pisahkan_atlet_bertemu_kontingen_sendiri($idKelasTanding);
                $sukses++;
            } catch (\Throwable $e) {
                $gagal++;
                $errors[] = 'Kelas ' . $idKelasTanding . ': ' . $e->getMessage();
                log_message('error', 'Gagal pisahkan kontingen tanding: {message}', ['message' => $e->getMessage()]);
            }
        }

        $message = sprintf('Berhasil - (Benchmark %.3f detik)', microtime(true) - $start);
        if ($gagal > 0) {
            $message .= sprintf(' | Gagal: %d', $gagal);
        }
        if ($errors !== []) {
            $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
        }

        return redirect()->to(base_url('admin/super/drawing-tanding'))->with('status', $gagal === 0)->with('message', $message);
    }

    public function distribusikanKelompokPesertaSeni()
    {
        $ids = $this->normalizeIds($this->request->getPost('id_kategori_lomba'));
        $mode = (string) ($this->request->getPost('mode_nomor_undi') ?? 'acak_ulang');
        if ($ids === [] || ! in_array($mode, ['acak_ulang', 'gunakan_nomor_undi', 'pisah_kontingen'], true)) {
            return redirect()->back()->with('status', false)->with('message', 'Pilih minimal satu kategori dan mode nomor undi yang valid.');
        }

        $result = $this->distributeSeniByKategori($ids, $mode);

        return redirect()->to(base_url('admin/super/drawing-seni'))->with('status', $result['gagal'] === 0)->with('message', $result['message']);
    }

    public function acakBaganBattleSeniBulk()
    {
        $ids = $this->normalizeIds($this->request->getPost('id_kategori_lomba_bagan'));
        if ($ids === []) {
            return redirect()->back()->with('status', false)->with('message', 'Pilih minimal satu kategori seni untuk acak bagan battle.');
        }

        $result = $this->bulkAcakBaganBattleSeni($ids);

        return redirect()->to(base_url('admin/super/drawing-seni'))->with('status', $result['gagal'] === 0)->with('message', $result['message']);
    }

    public function beriNomorUndiSeniBulk()
    {
        $ids = $this->normalizeIds($this->request->getPost('id_kategori_lomba'));
        if ($ids === []) {
            return redirect()->back()->with('status', false)->with('message', 'Pilih minimal satu kategori seni untuk isi nomor undi.');
        }

        $result = $this->bulkBeriNomorUndiSeni($ids);

        return redirect()->to(base_url('admin/super/drawing-seni'))->with('status', $result['gagal'] === 0)->with('message', $result['message']);
    }

    public function prosesGenerateBaganTandingDariJadwal()
    {
        $ids = $this->request->getPost('id_kompetisi_tanding');
        if (! is_array($ids) || $ids === []) {
            return redirect()->back()->with('status', false)->with('message', 'Pilih minimal satu kompetisi tanding.');
        }

        $result = $this->bulkGenerateBaganDariJadwal($ids, 'tanding');

        return redirect()->back()->with('status', $result['gagal'] === 0)->with('message', $result['message']);
    }

    public function prosesGenerateBaganSeniBattleDariJadwal()
    {
        $ids = $this->request->getPost('id_kompetisi_seni');
        if (! is_array($ids) || $ids === []) {
            return redirect()->back()->with('status', false)->with('message', 'Pilih minimal satu kompetisi seni battle.');
        }

        $result = $this->bulkGenerateBaganDariJadwal($ids, 'seni');

        return redirect()->back()->with('status', $result['gagal'] === 0)->with('message', $result['message']);
    }

    public function diagnosisTanding(): string
    {
        $auditData = (new PembuatanJadwalAuditService())->getDashboardAudit();

        return view('admin/super/jadwal_tanding_diagnosis', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_diagnosis_tanding',
        ] + $auditData, 'Diagnosis Jadwal Tanding'));
    }

    public function overviewTanding(): string
    {
        $rows = (new JadwalTandingModel())->get_all();

        return view('admin/super/jadwal_tanding_overview', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_overview_tanding',
            'rows' => $rows,
        ], 'Overview Jadwal Tanding'));
    }

    public function diagnosisSeni(): string
    {
        $auditData = (new PembuatanJadwalAuditService())->getDashboardAudit();

        return view('admin/super/jadwal_seni_diagnosis', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_diagnosis_seni',
        ] + $auditData, 'Diagnosis Jadwal Seni'));
    }

    public function overviewSeni(): string
    {
        $rows = (new JadwalSeniModel())->get_all();

        return view('admin/super/jadwal_seni_overview', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_overview_seni',
            'rows' => $rows,
        ], 'Overview Jadwal Seni'));
    }

    public function jadwalTanding(): string
    {
        $model = new JadwalTandingModel();

        // Reuse view sekretariat, tapi lewat route super agar mode/side-nav tetap konsisten.
        return view('admin/sekretariat/jadwal_tanding/index', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_jadwal_tanding',
            'rows'       => $model->get_all(),
            'gelanggang' => (new GelanggangModel())->findAll(),
            'routePrefix' => 'admin/super/jadwal-tanding',
        ], 'Daftar Jadwal Tanding'));
    }

    public function showJadwalTanding(int $id): string
    {
        $model  = new JadwalTandingModel();
        $jadwal = $model->findWithGelanggang($id);
        if ($jadwal === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('admin/sekretariat/jadwal_tanding/show', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_jadwal_tanding',
            'jadwal'     => $jadwal,
            'details'    => $model->get_detail_jadwal($id),
            'peserta'    => (new \App\Models\PesertaTandingModel())->findAll(),
            'routePrefix' => 'admin/super/jadwal-tanding',
        ], 'Schedule Arena ' . esc($jadwal->nama_gelanggang ?? 'Arena ' . $id)));
    }

    public function createJadwalTanding()
    {
        if (! $this->validate([
            'id_gelanggang' => 'required|is_natural_no_zero',
            'tanggal'       => 'required|valid_date',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
        ])) {
            return redirect()->back()->withInput()->with('status', false)->with('message', implode("\n", $this->validator->getErrors()));
        }

        (new JadwalTandingModel())->insert([
            'id_gelanggang' => $this->request->getPost('id_gelanggang'),
            'tanggal'       => $this->request->getPost('tanggal'),
            'jam_mulai'     => $this->request->getPost('jam_mulai'),
            'jam_selesai'   => $this->request->getPost('jam_selesai'),
            'keterangan'    => $this->request->getPost('keterangan'),
        ]);

        return redirect()->to(base_url('admin/super/jadwal-tanding'))->with('status', true)->with('message', 'Jadwal tanding berhasil ditambahkan.');
    }

    public function updateKeteranganJadwalTanding(int $id)
    {
        $model = new JadwalTandingModel();
        if ($model->find($id) === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        $model->update($id, ['keterangan' => $this->request->getPost('keterangan') ?? '']);

        return $this->response->setJSON(['status' => true, 'message' => 'Keterangan berhasil diperbarui.']);
    }

    public function deleteJadwalTanding(int $id)
    {
        $model = new JadwalTandingModel();
        if ($model->find($id) === null) {
            return redirect()->back()->with('status', false)->with('message', 'Jadwal tidak ditemukan.');
        }

        db_connect()->table('detail_jadwal_tanding')->where('id_jadwal_tanding', $id)->delete();
        $model->delete($id);

        return redirect()->to(base_url('admin/super/jadwal-tanding'))->with('status', true)->with('message', 'Jadwal berhasil dihapus.');
    }

    public function jadwalSeni(): string
    {
        $model = new JadwalSeniModel();

        return view('admin/sekretariat/jadwal_seni/index', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_jadwal_seni',
            'rows'       => $model->get_all(),
            'gelanggang' => (new GelanggangModel())->findAll(),
            'routePrefix' => 'admin/super/jadwal-seni',
        ], 'Daftar Jadwal Seni'));
    }

    public function showJadwalSeni(int $id): string
    {
        $model  = new JadwalSeniModel();
        $jadwal = $model->findWithGelanggang($id);
        if ($jadwal === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $allDetails    = $model->get_detail_jadwal($id);
        $battleDetails = array_filter($allDetails, static fn($d) => ! empty($d->id_battle_seni));
        $poolDetails   = array_filter($allDetails, static fn($d) => ! empty($d->id_penampilan_seni));

        return view('admin/sekretariat/jadwal_seni/show', $this->viewData([
            'activeMenu'    => 'pembuatan_jadwal_jadwal_seni',
            'jadwal'        => $jadwal,
            'details'       => $allDetails,
            'battleDetails' => $battleDetails,
            'poolDetails'   => $poolDetails,
            'routePrefix'   => 'admin/super/jadwal-seni',
        ], 'Schedule Seni Arena ' . esc($jadwal->nama_gelanggang ?? 'Arena ' . $id)));
    }

    public function createJadwalSeni()
    {
        if (! $this->validate([
            'id_gelanggang' => 'required|is_natural_no_zero',
            'tanggal'       => 'required|valid_date',
            'jam_mulai'     => 'required',
            'jam_selesai'   => 'required',
        ])) {
            return redirect()->back()->withInput()->with('status', false)->with('message', implode("\n", $this->validator->getErrors()));
        }

        (new JadwalSeniModel())->insert([
            'id_gelanggang' => $this->request->getPost('id_gelanggang'),
            'tanggal'       => $this->request->getPost('tanggal'),
            'jam_mulai'     => $this->request->getPost('jam_mulai'),
            'jam_selesai'   => $this->request->getPost('jam_selesai'),
            'keterangan'    => $this->request->getPost('keterangan'),
        ]);

        return redirect()->to(base_url('admin/super/jadwal-seni'))->with('status', true)->with('message', 'Jadwal seni berhasil ditambahkan.');
    }

    public function updateKeteranganJadwalSeni(int $id)
    {
        $model = new JadwalSeniModel();
        if ($model->find($id) === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        $model->update($id, ['keterangan' => $this->request->getPost('keterangan') ?? '']);

        return $this->response->setJSON(['status' => true, 'message' => 'Keterangan berhasil diperbarui.']);
    }

    public function deleteJadwalSeni(int $id)
    {
        $model = new JadwalSeniModel();
        if ($model->find($id) === null) {
            return redirect()->back()->with('status', false)->with('message', 'Jadwal tidak ditemukan.');
        }

        db_connect()->table('detail_jadwal_seni')->where('id_jadwal_seni', $id)->delete();
        $model->delete($id);

        return redirect()->to(base_url('admin/super/jadwal-seni'))->with('status', true)->with('message', 'Jadwal berhasil dihapus.');
    }

    public function createPdfJadwalTandingAjax(int $id, int $withScore = 0)
    {
        $model = new JadwalTandingModel();
        $jadwal = $model->findWithGelanggang($id);
        if ($jadwal === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        try {
            $details = $model->get_detail_jadwal($id);
            $html = view('admin/super/pdf/jadwal_tanding', [
                'title' => 'Jadwal Tanding Arena ' . ($jadwal->nama_gelanggang ?? $id),
                'jadwal' => $jadwal,
                'details' => $details,
                'withScore' => (bool) $withScore,
            ]);
            $path = $this->writeSchedulePdf($html, 'jadwal-tanding-' . $id . ($withScore ? '-skor' : '') . '.pdf');
        } catch (\Throwable $e) {
            log_message('error', 'Gagal generate PDF jadwal tanding: {message}', ['message' => $e->getMessage()]);
            return $this->response->setJSON(['status' => false, 'message' => 'Generate PDF jadwal tanding gagal.']);
        }

        $model->update($id, ['pdf_path' => $path]);

        return $this->response->setJSON(['status' => true, 'message' => 'PDF jadwal tanding berhasil dibuat.', 'path' => $path, 'url' => base_url($path)]);
    }

    public function getAllIdsJadwalTandingAjax()
    {
        $rows = (new JadwalTandingModel())->get_all();
        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'id' => $row->id_jadwal_tanding,
                'nama' => 'Arena ' . ($row->nama_gelanggang ?? '') . ' - ' . ($row->keterangan_jadwal ?? ''),
            ];
        }

        return $this->response->setJSON(['status' => true, 'data' => $data]);
    }

    public function tukarAtletJadwalTanding()
    {
        $idAtlet1 = (int) $this->request->getPost('id_atlet_1');
        $idAtlet2 = (int) $this->request->getPost('id_atlet_2');
        if ($idAtlet1 <= 0 || $idAtlet2 <= 0 || $idAtlet1 === $idAtlet2) {
            return redirect()->back()->with('status', false)->with('message', 'Pilih dua atlet yang berbeda.');
        }

        if ($this->athleteHasLockedMatches($idAtlet1) || $this->athleteHasLockedMatches($idAtlet2)) {
            return redirect()->back()->with('status', false)->with('message', 'Atlet tidak bisa ditukar karena sudah terlibat pertandingan yang memiliki skor atau pemenang.');
        }

        $db = db_connect();
        $db->transStart();
        foreach (['id_atlet_merah', 'id_atlet_biru'] as $field) {
            // Swap via temp sentinel to avoid collisions.
            $db->table('pertandingan')->where($field, $idAtlet1)->update([$field => -$idAtlet1]);
            $db->table('pertandingan')->where($field, $idAtlet2)->update([$field => $idAtlet1]);
            $db->table('pertandingan')->where($field, -$idAtlet1)->update([$field => $idAtlet2]);
        }
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('status', false)->with('message', 'Gagal menukar atlet.');
        }

        return redirect()->back()->with('status', true)->with('message', 'Atlet berhasil ditukar pada data pertandingan.');
    }

    public function sortirUlangJadwalTanding(int $id)
    {
        $awal = (int) $this->request->getPost('nomor_partai_awal');
        if ($awal <= 0) {
            return redirect()->back()->with('status', false)->with('message', 'Nomor partai awal tidak valid.');
        }

        if ($this->jadwalTandingHasLockedMatches($id)) {
            return redirect()->back()->with('status', false)->with('message', 'Nomor partai tidak dapat diurutkan ulang karena ada pertandingan dengan skor atau pemenang.');
        }

        $this->renumberJadwalTanding($id, $awal);

        return redirect()->back()->with('status', true)->with('message', 'Nomor partai berhasil diurutkan ulang.');
    }

    public function polaPenjadwalanJadwalTanding(int $id)
    {
        $pola = (string) $this->request->getPost('jenis_pola_penjadwalan');
        if (! in_array($pola, ['prestasi', 'pemasalan_seling_1', 'pemasalan_seling_2', 'pemasalan_seling_3', 'pemasalan_seling_4'], true)) {
            return redirect()->back()->with('status', false)->with('message', 'Jenis pola penjadwalan tidak valid.');
        }

        if ($this->jadwalTandingHasLockedMatches($id)) {
            return redirect()->back()->with('status', false)->with('message', 'Pola penjadwalan tidak dapat diubah karena ada pertandingan dengan skor atau pemenang.');
        }

        $details = (new JadwalTandingModel())->get_detail_jadwal($id);
        if ($details === []) {
            return redirect()->back()->with('status', false)->with('message', 'Detail jadwal kosong.');
        }

        usort($details, static function ($a, $b) use ($pola): int {
            $jenisA = (string) ($a->jenis_perlombaan ?? '');
            $jenisB = (string) ($b->jenis_perlombaan ?? '');
            if ($jenisA !== $jenisB) {
                return $pola === 'prestasi' ? ($jenisA <=> $jenisB) : ($jenisB <=> $jenisA);
            }

            return ((int) ($a->nomor_partai ?? 0)) <=> ((int) ($b->nomor_partai ?? 0));
        });

        $awal = (int) ($details[0]->nomor_partai ?? 1);
        $db = db_connect();
        $db->transStart();
        foreach (array_values($details) as $index => $detail) {
            $db->table('detail_jadwal_tanding')
                ->where('id_detail_jadwal_tanding', $detail->id_detail_jadwal_tanding)
                ->update(['nomor_partai' => $awal + $index]);
        }
        $db->transComplete();
        $this->syncJadwalTandingRange($id);

        if (! $db->transStatus()) {
            return redirect()->back()->with('status', false)->with('message', 'Gagal mengatur pola penjadwalan.');
        }

        return redirect()->back()->with('status', true)->with('message', 'Pola penjadwalan berhasil diterapkan.');
    }

    public function createPdfJadwalSeniAjax(int $id, int $withScore = 0)
    {
        $model = new JadwalSeniModel();
        $jadwal = $model->findWithGelanggang($id);
        if ($jadwal === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jadwal tidak ditemukan.']);
        }

        try {
            $details = $model->get_detail_jadwal($id);
            $html = view('admin/super/pdf/jadwal_seni', [
                'title' => 'Jadwal Seni Arena ' . ($jadwal->nama_gelanggang ?? $id),
                'jadwal' => $jadwal,
                'details' => $details,
                'withScore' => (bool) $withScore,
            ]);
            $path = $this->writeSchedulePdf($html, 'jadwal-seni-' . $id . ($withScore ? '-skor' : '') . '.pdf');
        } catch (\Throwable $e) {
            log_message('error', 'Gagal generate PDF jadwal seni: {message}', ['message' => $e->getMessage()]);
            return $this->response->setJSON(['status' => false, 'message' => 'Generate PDF jadwal seni gagal.']);
        }

        $model->update($id, ['pdf_path' => $path]);

        return $this->response->setJSON(['status' => true, 'message' => 'PDF jadwal seni berhasil dibuat.', 'path' => $path, 'url' => base_url($path)]);
    }

    public function getAllIdsJadwalSeniAjax()
    {
        $rows = (new JadwalSeniModel())->get_all();
        $data = [];

        foreach ($rows as $row) {
            $data[] = [
                'id' => $row->id_jadwal_seni,
                'nama' => 'Arena ' . ($row->nama_gelanggang ?? '') . ' - ' . ($row->keterangan_jadwal ?? ''),
            ];
        }

        return $this->response->setJSON(['status' => true, 'data' => $data]);
    }

    private function viewData(array $data, string $title): array
    {
        return $data + [
            'title' => $title,
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ];
    }



    private function jadwalTandingHasLockedMatches(int $id): bool
    {
        $row = db_connect()->table('detail_jadwal_tanding djt')
            ->select('COUNT(*) AS total')
            ->join('pertandingan p', 'p.id_pertandingan = djt.id_pertandingan')
            ->where('djt.id_jadwal_tanding', $id)
            ->groupStart()
                ->where('p.id_pemenang IS NOT NULL', null, false)
                ->orWhere('p.skor_merah >', 0)
                ->orWhere('p.skor_biru >', 0)
            ->groupEnd()
            ->get()
            ->getRow();

        return (int) ($row->total ?? 0) > 0;
    }

    private function athleteHasLockedMatches(int $idAtlet): bool
    {
        $row = db_connect()->table('pertandingan p')
            ->select('COUNT(*) AS total')
            ->groupStart()
                ->where('p.id_atlet_merah', $idAtlet)
                ->orWhere('p.id_atlet_biru', $idAtlet)
            ->groupEnd()
            ->groupStart()
                ->where('p.id_pemenang IS NOT NULL', null, false)
                ->orWhere('p.skor_merah >', 0)
                ->orWhere('p.skor_biru >', 0)
            ->groupEnd()
            ->get()
            ->getRow();

        return (int) ($row->total ?? 0) > 0;
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
        $mpdf = (new MpdfService())->make(['format' => 'A4-L']);
        $mpdf->WriteHTML($html);
        $mpdf->Output(FCPATH . $path, \Mpdf\Output\Destination::FILE);

        return $path;
    }

    private function bulkGenerateBaganDariJadwal(array $ids, string $type): array
    {
        $service = new SistemGugurTunggalService();
        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $start = microtime(true);

        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                $gagal++;
                $errors[] = 'ID tidak valid.';
                continue;
            }

            try {
                if ($type === 'seni') {
                    $service->generateBaganBattleSeniDariJadwal($id);
                } else {
                    $service->generateBaganTandingDariJadwal($id);
                }
                $sukses++;
            } catch (\Throwable $e) {
                $gagal++;
                $errors[] = 'ID ' . $id . ': ' . $e->getMessage();
                log_message('error', 'Gagal generate bagan dari jadwal super admin: {message}', ['message' => $e->getMessage()]);
            }
        }

        $message = sprintf('Sukses: %d | Gagal: %d (%.3f detik)', $sukses, $gagal, microtime(true) - $start);
        if ($errors !== []) {
            $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
        }

        return ['sukses' => $sukses, 'gagal' => $gagal, 'message' => $message];
    }

    private function scheduleDatabaseStats($db): array
    {
        $tables = ['jadwal_tanding', 'detail_jadwal_tanding', 'jadwal_seni', 'detail_jadwal_seni', 'kompetisi_tanding', 'pertandingan', 'kompetisi_seni', 'battle_seni', 'penampilan_seni'];
        $stats = [];

        foreach ($tables as $table) {
            $stats[$table] = $db->table($table)->countAllResults();
        }

        return $stats;
    }

    private function scheduleDatabaseChecks($db): array
    {
        return [
            ['label' => 'Pertandingan tanding belum terjadwal', 'total' => $db->table('pertandingan p')->where('p.jenis_kemenangan !=', 'BYE')->where('p.id_pertandingan NOT IN (SELECT id_pertandingan FROM detail_jadwal_tanding WHERE id_pertandingan IS NOT NULL)', null, false)->countAllResults()],
            ['label' => 'BYE tanding masuk jadwal', 'total' => $db->table('detail_jadwal_tanding djt')->join('pertandingan p', 'p.id_pertandingan = djt.id_pertandingan')->where('p.jenis_kemenangan', 'BYE')->countAllResults()],
            ['label' => 'Battle seni belum terjadwal', 'total' => $db->table('battle_seni bs')->where('bs.jenis_kemenangan !=', 'BYE')->where('bs.id_battle_seni NOT IN (SELECT id_battle_seni FROM detail_jadwal_seni WHERE id_battle_seni IS NOT NULL)', null, false)->countAllResults()],
            ['label' => 'BYE seni masuk jadwal', 'total' => $db->table('detail_jadwal_seni djs')->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni')->where('bs.jenis_kemenangan', 'BYE')->countAllResults()],
        ];
    }

    private function baganTandingScheduleRows(): array
    {
        return db_connect()->table('kompetisi_tanding ktg')
            ->select('ktg.id_kompetisi_tanding, ktg.nomor_pool, ktg.bagan_pertandingan, kls.label, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('COUNT(DISTINCT p.id_pertandingan) AS jumlah_pertandingan', false)
            ->select('COUNT(DISTINCT djt.id_detail_jadwal_tanding) AS jumlah_terjadwal', false)
            ->join('kelas_tanding kls', 'kls.id_kelas_tanding = ktg.id_kelas_tanding')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kls.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('pertandingan p', 'p.id_kompetisi_tanding = ktg.id_kompetisi_tanding AND p.jenis_kemenangan != "BYE"', 'left')
            ->join('detail_jadwal_tanding djt', 'djt.id_pertandingan = p.id_pertandingan', 'left')
            ->groupBy('ktg.id_kompetisi_tanding')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('kls.label', 'ASC')
            ->orderBy('ktg.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    private function baganSeniScheduleRows(): array
    {
        return db_connect()->table('kompetisi_seni ks')
            ->select('ks.id_kompetisi_seni, ks.nomor_pool, ks.bagan_battle_seni, sks.nama_seni, sks.jenis_seni, sks.sistem_penampilan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('COUNT(DISTINCT bs.id_battle_seni) AS jumlah_battle', false)
            ->select('COUNT(DISTINCT djs.id_detail_jadwal_seni) AS jumlah_terjadwal', false)
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('battle_seni bs', 'bs.id_kompetisi_seni = ks.id_kompetisi_seni AND bs.jenis_kemenangan != "BYE"', 'left')
            ->join('detail_jadwal_seni djs', 'djs.id_battle_seni = bs.id_battle_seni', 'left')
            ->where('sks.sistem_penampilan', 'battle')
            ->groupBy('ks.id_kompetisi_seni')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('sks.jenis_seni', 'ASC')
            ->orderBy('sks.nama_seni', 'ASC')
            ->orderBy('ks.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    private function renumberJadwalTanding(int $id, int $awal): void
    {
        $db = db_connect();
        $details = $db->table('detail_jadwal_tanding')
            ->where('id_jadwal_tanding', $id)
            ->orderBy('nomor_partai', 'ASC')
            ->get()
            ->getResult();

        $db->transStart();
        foreach ($details as $index => $detail) {
            $db->table('detail_jadwal_tanding')
                ->where('id_detail_jadwal_tanding', $detail->id_detail_jadwal_tanding)
                ->update(['nomor_partai' => $awal + $index]);
        }
        $db->transComplete();

        $this->syncJadwalTandingRange($id);
    }

    private function syncJadwalTandingRange(int $id): void
    {
        $db = db_connect();
        $range = $db->table('detail_jadwal_tanding')
            ->select('MIN(nomor_partai) AS awal, MAX(nomor_partai) AS akhir, COUNT(*) AS total')
            ->where('id_jadwal_tanding', $id)
            ->get()
            ->getRow();

        (new JadwalTandingModel())->update($id, [
            'nomor_partai_awal' => $range->awal ?? null,
            'nomor_partai_akhir' => $range->akhir ?? null,
            'jumlah_partai' => (int) ($range->total ?? 0),
        ]);
    }

    private function normalizeIds($input): array
    {
        if (! is_array($input)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(static fn ($id): int => (int) $id, $input), static fn (int $id): bool => $id > 0)));
    }

    private function drawingKategoriTandingRows(): array
    {
        return db_connect()->table('kategori_lomba kl')
            ->select('kl.id_kategori_lomba, kl.jenis_perlombaan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt JOIN kompetisi_tanding kom ON kom.id_kompetisi_tanding = pt.id_kompetisi_tanding JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding WHERE kt.id_kategori_lomba = kl.id_kategori_lomba) AS jumlah_peserta_tanding', false)
            ->select('(SELECT COUNT(*) FROM pertandingan p JOIN kompetisi_tanding kom ON kom.id_kompetisi_tanding = p.id_kompetisi_tanding JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding WHERE kt.id_kategori_lomba = kl.id_kategori_lomba AND p.jenis_kemenangan != "BYE") AS jumlah_partai_tanding', false)
            ->select('(SELECT COUNT(*) FROM kompetisi_tanding kom JOIN kelas_tanding kt ON kt.id_kelas_tanding = kom.id_kelas_tanding WHERE kt.id_kategori_lomba = kl.id_kategori_lomba) AS jumlah_pool', false)
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kl.nama_kategori_lomba', 'tanding')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->get()->getResult();
    }

    private function drawingKategoriSeniRows(): array
    {
        return db_connect()->table('kategori_lomba kl')
            ->select('kl.id_kategori_lomba, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT COUNT(*) FROM kelompok_peserta_seni kps JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = kps.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba) AS jumlah_kelompok', false)
            ->select('(SELECT COUNT(*) FROM battle_seni bs JOIN kompetisi_seni ks ON ks.id_kompetisi_seni = bs.id_kompetisi_seni JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba AND bs.jenis_kemenangan != "BYE") AS jumlah_battle', false)
            ->select('(SELECT COUNT(*) FROM kompetisi_seni ks JOIN sub_kategori_seni sks ON sks.id_sub_kategori_seni = ks.id_sub_kategori_seni WHERE sks.id_kategori_lomba = kl.id_kategori_lomba) AS jumlah_pool', false)
            ->select('(SELECT COUNT(*) FROM sub_kategori_seni sks WHERE sks.id_kategori_lomba = kl.id_kategori_lomba AND sks.sistem_penampilan = "battle") AS jumlah_sub_kategori_battle', false)
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kl.nama_kategori_lomba', 'seni')
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->get()->getResult();
    }

    private function drawingTandingSummary(): array
    {
        $db = db_connect();

        return [
            'kategori' => $db->table('kategori_lomba')->where('nama_kategori_lomba', 'tanding')->countAllResults(),
            'pool' => $db->table('kompetisi_tanding')->countAllResults(),
            // jumlah_peserta_tanding not stored as column in CI4 DB; compute via peserta_tanding.
            'satuPeserta' => (int) ($db->query('SELECT COUNT(*) AS total FROM kompetisi_tanding kt WHERE (SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kt.id_kompetisi_tanding) = 1')->getRow()->total ?? 0),
        ];
    }

    private function drawingSeniSummary(): array
    {
        $db = db_connect();

        return [
            'kategori' => $db->table('kategori_lomba')->where('nama_kategori_lomba', 'seni')->countAllResults(),
            'pool' => $db->table('kompetisi_seni')->countAllResults(),
            'battlePool' => $db->table('sub_kategori_seni')->where('sistem_penampilan', 'battle')->countAllResults(),
        ];
    }

    private function reportPesertaTandingTanpaLawan(): array
    {
        $db = db_connect();

        $pools = $db->table('kompetisi_tanding kom')
            ->select('kom.id_kompetisi_tanding')
            ->having('(SELECT COUNT(*) FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding) = 1', null, false)
            ->get()->getResult();

        $ids = array_values(array_filter(array_map(static fn ($r): int => (int) ($r->id_kompetisi_tanding ?? 0), $pools), static fn (int $id): bool => $id > 0));
        if ($ids === []) {
            return [];
        }

        return (new \App\Models\PesertaTandingModel())
            ->baseSekretariatQuery()
            ->whereIn('pt.id_kompetisi_tanding', $ids)
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('kt.berat_maksimal', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->get()->getResult();
    }

    private function reportPesertaKontingenSendiri(string $jumlahPesertaCondition): array
    {
        $allowed = ['= 2', '> 2'];
        if (! in_array($jumlahPesertaCondition, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid jumlah peserta condition.');
        }

        return (new \App\Models\PesertaTandingModel())
            ->baseSekretariatQuery()
            ->where('kl.jenis_perlombaan', 'pemasalan')
            ->having('jumlah_peserta_tanding_kontingen_sama > 1', null, false)
            ->having('jumlah_peserta_tanding ' . $jumlahPesertaCondition, null, false)
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('kt.berat_maksimal', 'ASC')
            ->orderBy('kom.nomor_pool', 'ASC')
            ->orderBy('k.nama_kontingen', 'ASC')
            ->orderBy('p.berat_badan', 'ASC')
            ->get()->getResult();
    }

    private function reportKelasTandingKuotaTersedia(): array
    {
        return db_connect()->table('kelas_tanding kt')
            ->select('kt.*')
            ->select('kl.jenis_perlombaan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->select('(SELECT COUNT(*) FROM peserta_tanding pt JOIN kompetisi_tanding kom ON kom.id_kompetisi_tanding = pt.id_kompetisi_tanding WHERE kom.id_kelas_tanding = kt.id_kelas_tanding) AS jumlah_peserta_tanding', false)
            ->select('(SELECT COALESCE(SUM(kom.max_peserta), 0) FROM kompetisi_tanding kom WHERE kom.id_kelas_tanding = kt.id_kelas_tanding) AS max_peserta', false)
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('kl.nama_kategori_lomba', 'tanding')
            ->having('jumlah_peserta_tanding < max_peserta', null, false)
            ->orderBy('ku.min_umur', 'ASC')
            ->orderBy('ku.jenis_kelamin', 'ASC')
            ->orderBy('kt.berat_maksimal', 'ASC')
            ->get()->getResult();
    }

    private function distributeTandingByKategori(array $ids, string $mode): array
    {
        $db = db_connect();
        $kelasModel = new KelasTandingModel();
        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $start = microtime(true);

        foreach ($ids as $idKategori) {
            try {
                $kelasRows = $db->table('kelas_tanding')
                    ->select('id_kelas_tanding')
                    ->where('id_kategori_lomba', $idKategori)
                    ->get()->getResult();

                foreach ($kelasRows as $kelas) {
                    $kelasModel->distribusikan_peserta_tanding((int) $kelas->id_kelas_tanding, $mode);
                }

                $sukses++;
            } catch (\Throwable $e) {
                $gagal++;
                $errors[] = 'Kategori ' . $idKategori . ': ' . $e->getMessage();
                log_message('error', 'Gagal distribusi tanding super admin: {message}', ['message' => $e->getMessage()]);
            }
        }

        $message = sprintf('Berhasil - (Benchmark %.3f detik)', microtime(true) - $start);
        if ($gagal > 0) {
            $message .= sprintf(' | Gagal: %d', $gagal);
        }
        if ($errors !== []) {
            $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
        }

        return ['sukses' => $sukses, 'gagal' => $gagal, 'message' => $message];
    }

    private function bulkAcakBaganTanding(array $ids): array
    {
        $db = db_connect();
        $kompetisiTandingModel = new KompetisiTandingModel();
        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $start = microtime(true);

        foreach ($ids as $idKategori) {
            try {
                $randomSeed = $this->request->getPost('random_kategori_lomba_' . $idKategori) !== null;
                $pools = $db->table('kompetisi_tanding kom')
                    ->select('kom.id_kompetisi_tanding')
                    ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
                    ->where('kt.id_kategori_lomba', $idKategori)
                    ->where('EXISTS (SELECT 1 FROM peserta_tanding pt WHERE pt.id_kompetisi_tanding = kom.id_kompetisi_tanding)', null, false)
                    ->get()->getResult();
                foreach ($pools as $pool) {
                    $kompetisiTandingModel->acak_bagan_tanding((int) $pool->id_kompetisi_tanding, $randomSeed);
                }
                $sukses++;
            } catch (\Throwable $e) {
                $gagal++;
                $errors[] = 'Kategori ' . $idKategori . ': ' . $e->getMessage();
            }
        }

        $message = sprintf('Berhasil - (Benchmark %.3f detik)', microtime(true) - $start);
        if ($gagal > 0) {
            $message .= sprintf(' | Gagal: %d', $gagal);
        }
        if ($errors !== []) {
            $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
        }

        return ['sukses' => $sukses, 'gagal' => $gagal, 'message' => $message];
    }

    private function distributeSeniByKategori(array $ids, string $mode): array
    {
        $db = db_connect();
        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $start = microtime(true);

        foreach ($ids as $idKategori) {
            try {
                $subKategoriRows = $db->table('sub_kategori_seni')
                    ->select('id_sub_kategori_seni')
                    ->where('id_kategori_lomba', $idKategori)
                    ->get()->getResult();

                foreach ($subKategoriRows as $sub) {
                    $idSubKategori = (int) $sub->id_sub_kategori_seni;

                    if ($mode === 'pisah_kontingen') {
                        $this->distribusiSeniPisahKontingen($idSubKategori);
                        continue;
                    }

                    // Default CI4 behavior: keep pool membership as-is, only (re)number undi when requested.
                    if ($mode !== 'gunakan_nomor_undi') {
                        $poolRows = $db->table('kompetisi_seni')
                            ->select('id_kompetisi_seni')
                            ->where('id_sub_kategori_seni', $idSubKategori)
                            ->orderBy('nomor_pool', 'ASC')
                            ->get()->getResult();

                        $svc = new SekretariatKategoriSeniService();
                        foreach ($poolRows as $pool) {
                            $svc->beriNomorUndi((int) $pool->id_kompetisi_seni);
                        }
                    }
                }

                $sukses++;
            } catch (\Throwable $e) {
                $gagal++;
                $errors[] = 'Kategori ' . $idKategori . ': ' . $e->getMessage();
                log_message('error', 'Gagal distribusi seni super admin: {message}', ['message' => $e->getMessage()]);
            }
        }

        $message = sprintf('Distribusi kelompok seni diproses untuk %d kategori, gagal %d (%.3f detik).', $sukses, $gagal, microtime(true) - $start);
        if ($mode === 'pisah_kontingen') {
            $message .= ' Mode pisah kontingen menggunakan round-robin per kontingen (parity CI3).';
        }
        if ($errors !== []) {
            $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
        }

        return ['sukses' => $sukses, 'gagal' => $gagal, 'message' => $message];
    }

    private function distribusiSeniPisahKontingen(int $idSubKategoriSeni): void
    {
        $db = db_connect();

        // Safety: do not reshuffle if there is any schedule already referencing pools from this sub category.
        $scheduled = $db->table('detail_jadwal_seni djs')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni', 'left')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->where('ks.id_sub_kategori_seni', $idSubKategoriSeni)
            ->countAllResults();
        if ($scheduled > 0) {
            throw new \RuntimeException('Tidak bisa pisah kontingen: beberapa pool sudah dijadwalkan.');
        }

        $pools = $db->table('kompetisi_seni')
            ->select('id_kompetisi_seni, nomor_pool, max_peserta, keterangan')
            ->where('id_sub_kategori_seni', $idSubKategoriSeni)
            ->orderBy('nomor_pool', 'ASC')
            ->get()->getResult();

        if ($pools === []) {
            return;
        }

        $kelompok = $db->table('kelompok_peserta_seni')
            ->select('id_kelompok_peserta_seni, id_kontingen')
            ->whereIn('id_kompetisi_seni', array_map(static fn ($p) => (int) $p->id_kompetisi_seni, $pools))
            ->get()->getResult();

        if ($kelompok === []) {
            return;
        }

        // Ensure capacity: add pools by cloning last pool configuration.
        $totalPeserta = count($kelompok);
        $capacity = array_sum(array_map(static fn ($p) => (int) $p->max_peserta, $pools));
        if ($capacity < $totalPeserta) {
            $last = $pools[count($pools) - 1];
            $nextNomor = (int) $last->nomor_pool + 1;
            while ($capacity < $totalPeserta) {
                $db->table('kompetisi_seni')->insert([
                    'id_sub_kategori_seni' => $idSubKategoriSeni,
                    'nomor_pool' => $nextNomor,
                    'max_peserta' => (int) $last->max_peserta,
                    'perhitungan_medali' => 1,
                    'keterangan' => (string) ($last->keterangan ?? ''),
                    'bagan_battle_seni' => null,
                ]);
                $capacity += (int) $last->max_peserta;
                $nextNomor++;
            }

            $pools = $db->table('kompetisi_seni')
                ->select('id_kompetisi_seni, nomor_pool, max_peserta, keterangan')
                ->where('id_sub_kategori_seni', $idSubKategoriSeni)
                ->orderBy('nomor_pool', 'ASC')
                ->get()->getResult();
        }

        // Build pool capacity tracker.
        $poolStates = [];
        foreach ($pools as $pool) {
            $poolStates[] = [
                'id' => (int) $pool->id_kompetisi_seni,
                'max' => (int) $pool->max_peserta,
                'count' => 0,
            ];
        }

        // Group by kontingen, sort by biggest group first.
        $byKontingen = [];
        foreach ($kelompok as $row) {
            $byKontingen[(int) $row->id_kontingen][] = (int) $row->id_kelompok_peserta_seni;
        }
        uasort($byKontingen, static fn ($a, $b) => count($b) <=> count($a));

        $db->transStart();

        // Round-robin placement; for each kontingen start from currently least-filled pool.
        foreach ($byKontingen as $kontingenId => $idsKelompok) {
            usort($poolStates, static fn ($a, $b) => $a['count'] <=> $b['count']);
            $offset = 0;

            foreach ($idsKelompok as $idKelompok) {
                $tries = 0;
                while ($tries < count($poolStates)) {
                    $idx = $offset % count($poolStates);
                    if ($poolStates[$idx]['count'] < $poolStates[$idx]['max']) {
                        $db->table('kelompok_peserta_seni')
                            ->where('id_kelompok_peserta_seni', $idKelompok)
                            ->update(['id_kompetisi_seni' => $poolStates[$idx]['id']]);

                        $poolStates[$idx]['count']++;
                        $offset++;
                        break;
                    }
                    $offset++;
                    $tries++;
                }
            }
        }

        $db->transComplete();

        // Renumber undi sequentially within each pool after reshuffle.
        $svc = new SekretariatKategoriSeniService();
        foreach ($pools as $pool) {
            $svc->beriNomorUndi((int) $pool->id_kompetisi_seni);
        }
    }

    private function bulkAcakBaganBattleSeni(array $ids): array
    {
        $db = db_connect();
        $service = new SistemGugurTunggalService();
        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $start = microtime(true);

        foreach ($ids as $idKategori) {
            try {
                $mode = $this->request->getPost('random_kategori_lomba_' . $idKategori) !== null ? 'full_random_persilat' : 'formula';
                $pools = $db->table('kompetisi_seni ks')
                    ->select('ks.id_kompetisi_seni')
                    ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
                    ->where('sks.id_kategori_lomba', $idKategori)
                    ->where('sks.sistem_penampilan', 'battle')
                    ->where('ks.jumlah_kelompok_peserta_seni >', 1)
                    ->get()->getResult();
                foreach ($pools as $pool) {
                    $service->acakBaganBattleSeni((int) $pool->id_kompetisi_seni, $mode);
                }
                $sukses++;
            } catch (\Throwable $e) {
                $gagal++;
                $errors[] = 'Kategori ' . $idKategori . ': ' . $e->getMessage();
            }
        }

        $message = sprintf('Acak bagan battle seni selesai untuk %d kategori, gagal %d (%.3f detik).', $sukses, $gagal, microtime(true) - $start);
        if ($errors !== []) {
            $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
        }

        return ['sukses' => $sukses, 'gagal' => $gagal, 'message' => $message];
    }

    private function bulkBeriNomorUndiSeni(array $ids): array
    {
        $db = db_connect();
        $service = new SekretariatKategoriSeniService();
        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $start = microtime(true);

        foreach ($ids as $idKategori) {
            try {
                $pools = $db->table('kompetisi_seni ks')
                    ->select('ks.id_kompetisi_seni')
                    ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
                    ->where('sks.id_kategori_lomba', $idKategori)
                    ->get()->getResult();
                foreach ($pools as $pool) {
                    $service->beriNomorUndi((int) $pool->id_kompetisi_seni);
                }
                $sukses++;
            } catch (\Throwable $e) {
                $gagal++;
                $errors[] = 'Kategori ' . $idKategori . ': ' . $e->getMessage();
            }
        }

        $message = sprintf('Isi nomor undi seni selesai untuk %d kategori, gagal %d (%.3f detik).', $sukses, $gagal, microtime(true) - $start);
        if ($errors !== []) {
            $message .= '<br><small>' . implode('<br>', array_slice($errors, 0, 5)) . '</small>';
        }

        return ['sukses' => $sukses, 'gagal' => $gagal, 'message' => $message];
    }
}
