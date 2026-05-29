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

        // Kelas + kategori dibutuhkan untuk UX parity CI3 (accordion + checkbox per kategori).
        $kelasModel = new KelasTandingModel();
        $kelas = $kelasModel
            ->orderBy('id_kelas_tanding', 'ASC')
            ->findAll();

        // Optional: jika skema/relasi belum lengkap, view tetap bisa render dengan array kosong.
        $kategoriLomba = [];
        $kelasByKategori = [];
        $dataKategoriTandingGabung = [];
        $jumlahPesertaTanding = 0;
        $jumlahKompetisiTanding = 0;
        $prediksiJumlahPartai = max(1, count($kelas));

        $babakOptions = [];

        try {
            $db = db_connect();

            // Babak yang ditampilkan hanya babak yang memang ada (parity CI3).
            $babakRows = $db->table('pertandingan')
                ->distinct()
                ->select('pertandingan.babak')
                ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
                ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
                ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
                ->where('kategori_lomba.nama_kategori_lomba', 'tanding')
                ->orderBy('pertandingan.babak', 'ASC')
                ->get()
                ->getResult();

            foreach ($babakRows as $row) {
                $babak = trim((string) ($row->babak ?? ''));
                if ($babak !== '') {
                    $babakOptions[] = $babak;
                }
            }

            // Urutan babak mengikuti kebiasaan CI3 (final dulu).
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
            usort($babakOptions, static function (string $a, string $b) use ($babakOrder): int {
                $ia = array_search($a, $babakOrder, true);
                $ib = array_search($b, $babakOrder, true);
                $ia = $ia === false ? 999 : $ia;
                $ib = $ib === false ? 999 : $ib;
                if ($ia === $ib) {
                    return strcmp($a, $b);
                }
                return $ia <=> $ib;
            });

            // Kategori lomba tanding (untuk group accordion). Field minimal mengikuti apa yang view butuhkan.
            $kategoriLomba = $db->table('kategori_lomba kl')
                ->select('kl.id_kategori_lomba, kl.jenis_perlombaan, ku.jenis_kelamin, ku.nama_kategori_usia')
                ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
                ->where('kl.nama_kategori_lomba', 'tanding')
                ->orderBy('kl.id_kategori_lomba', 'ASC')
                ->get()
                ->getResult();

            // Hitung jumlah partai per kelas per babak (yang belum dijadwalkan).
            $countRows = $db->table('pertandingan')
                ->select('kelas_tanding.id_kelas_tanding, pertandingan.babak, COUNT(*) AS jumlah')
                ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
                ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
                ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
                ->join('detail_jadwal_tanding djt', 'djt.id_pertandingan = pertandingan.id_pertandingan', 'left')
                ->where('kategori_lomba.nama_kategori_lomba', 'tanding')
                ->where('djt.id_pertandingan IS NULL', null, false)
                ->groupBy('kelas_tanding.id_kelas_tanding, pertandingan.babak')
                ->get()
                ->getResult();

            $perKelasPerBabak = [];
            foreach ($countRows as $r) {
                $idKelas = (int) ($r->id_kelas_tanding ?? 0);
                $babak = (string) ($r->babak ?? '');
                $jumlah = (int) ($r->jumlah ?? 0);
                if ($idKelas > 0 && $babak !== '') {
                    $perKelasPerBabak[$idKelas][$babak] = $jumlah;
                }
            }

            // Total peserta tanding.
            $jumlahPesertaTanding = (int) $db->table('peserta_tanding pt')
                ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
                ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
                ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
                ->where('kl.nama_kategori_lomba', 'tanding')
                ->countAllResults();

            // Total pool (hanya yang punya peserta, parity CI3 "pool dengan 0 peserta tidak dihitung").
            $jumlahKompetisiTanding = (int) $db->table('kompetisi_tanding kom')
                ->select('kom.id_kompetisi_tanding')
                ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
                ->join('kategori_lomba kl', 'kl.id_kategori_lomba = kt.id_kategori_lomba')
                ->join('peserta_tanding pt', 'pt.id_kompetisi_tanding = kom.id_kompetisi_tanding')
                ->where('kl.nama_kategori_lomba', 'tanding')
                ->groupBy('kom.id_kompetisi_tanding')
                ->countAllResults(false);

            // Prediksi jumlah partai = pertandingan non-BYE yang belum dijadwalkan.
            $prediksiJumlahPartai = (int) $db->table('pertandingan')
                ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
                ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
                ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
                ->join('detail_jadwal_tanding djt', 'djt.id_pertandingan = pertandingan.id_pertandingan', 'left')
                ->where('kategori_lomba.nama_kategori_lomba', 'tanding')
                ->where('djt.id_pertandingan IS NULL', null, false)
                ->where('pertandingan.jenis_kemenangan !=', 'BYE')
                ->countAllResults();

            foreach ($kategoriLomba as $kat) {
                $kelasRows = $db->table('kelas_tanding')
                    ->select('id_kelas_tanding, id_kategori_lomba, label')
                    ->where('id_kategori_lomba', $kat->id_kategori_lomba)
                    ->orderBy('id_kelas_tanding', 'ASC')
                    ->get()
                    ->getResult();

                // Enrich rows with CI3-like computed fields.
                foreach ($kelasRows as $kr) {
                    $idKelas = (int) ($kr->id_kelas_tanding ?? 0);
                    $kr->jumlah_pertandingan_per_babak = $idKelas > 0 ? ($perKelasPerBabak[$idKelas] ?? false) : false;
                    $kr->jumlah_partai_tanding_belum_dijadwalkan = 0;
                    if (is_array($kr->jumlah_pertandingan_per_babak)) {
                        foreach ($kr->jumlah_pertandingan_per_babak as $val) {
                            $kr->jumlah_partai_tanding_belum_dijadwalkan += (int) $val;
                        }
                    }
                }

                $kelasByKategori[(string) $kat->id_kategori_lomba] = $kelasRows;
            }

            // Ringkasan modal per "nama_kategori_usia" (parity CI3).
            foreach ($kategoriLomba as $kat) {
                $nama = (string) ($kat->nama_kategori_usia ?? 'Kategori');
                if (! isset($dataKategoriTandingGabung[$nama])) {
                    $dataKategoriTandingGabung[$nama] = [
                        'jumlah_peserta_tanding' => 0,
                        'jumlah_pool_tanding' => 0,
                        'jumlah_partai_tanding' => 0,
                    ];
                }

                // Peserta
                $dataKategoriTandingGabung[$nama]['jumlah_peserta_tanding'] += (int) $db->table('peserta_tanding pt')
                    ->join('kompetisi_tanding kom', 'kom.id_kompetisi_tanding = pt.id_kompetisi_tanding')
                    ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
                    ->where('kt.id_kategori_lomba', (int) $kat->id_kategori_lomba)
                    ->countAllResults();

                // Pool (yang punya peserta)
                $dataKategoriTandingGabung[$nama]['jumlah_pool_tanding'] += (int) $db->table('kompetisi_tanding kom')
                    ->select('kom.id_kompetisi_tanding')
                    ->join('kelas_tanding kt', 'kt.id_kelas_tanding = kom.id_kelas_tanding')
                    ->join('peserta_tanding pt', 'pt.id_kompetisi_tanding = kom.id_kompetisi_tanding')
                    ->where('kt.id_kategori_lomba', (int) $kat->id_kategori_lomba)
                    ->groupBy('kom.id_kompetisi_tanding')
                    ->countAllResults(false);

                // Partai (belum dijadwalkan, non-BYE)
                $dataKategoriTandingGabung[$nama]['jumlah_partai_tanding'] += (int) $db->table('pertandingan')
                    ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
                    ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
                    ->join('detail_jadwal_tanding djt', 'djt.id_pertandingan = pertandingan.id_pertandingan', 'left')
                    ->where('kelas_tanding.id_kategori_lomba', (int) $kat->id_kategori_lomba)
                    ->where('djt.id_pertandingan IS NULL', null, false)
                    ->where('pertandingan.jenis_kemenangan !=', 'BYE')
                    ->countAllResults();
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Gagal load kategori/kelas untuk penjadwalan tanding (UX parity CI3): {message}', ['message' => $e->getMessage()]);
        }

        if ($babakOptions === []) {
            // Fallback safe list.
            $babakOptions = ['Final'];
        }

        return view('admin/super/jadwal_tanding/penjadwalan_tanding_otomatis', $this->viewData([
            'activeMenu' => 'pembuatan_jadwal_penjadwalan_otomatis_tanding',
            'gelanggang' => $gelanggang,
            'kelas' => $kelas,
            'kategoriLomba' => $kategoriLomba,
            'kelasByKategori' => $kelasByKategori,
            'dataKategoriTandingGabung' => $dataKategoriTandingGabung,
            'jumlahPesertaTanding' => $jumlahPesertaTanding,
            'jumlahKompetisiTanding' => $jumlahKompetisiTanding,
            'prediksiJumlahPartai' => $prediksiJumlahPartai,
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

        // Parity CI3: id_gelanggang dan jumlah_partai dikirim sebagai array-asosiatif
        // (id_gelanggang[ID]=ID, jumlah_partai[ID]=X). Normalisasi ke array berurutan untuk service.
        $rawIdGelanggang = $this->request->getPost('id_gelanggang');
        $rawJumlahPartai = $this->request->getPost('jumlah_partai');

        $idGelanggang = [];
        $jumlahPartai = [];

        if (is_array($rawIdGelanggang) && is_array($rawJumlahPartai)) {
            foreach ($rawIdGelanggang as $id => $val) {
                $idInt = (int) (is_numeric($id) ? $id : $val);
                if ($idInt <= 0) {
                    continue;
                }
                $idGelanggang[] = $idInt;
                $jumlahPartai[] = (int) ($rawJumlahPartai[$id] ?? 0);
            }
        }

        $jenisPenjadwalan = (string) ($this->request->getPost('jenis_penjadwalan') ?? 'prestasi');
        $jumlahSelang = (int) ($this->request->getPost('jumlah_selang_seling') ?? 2);
        if (preg_match('/^pemasalan_seling_(\d+)$/', $jenisPenjadwalan, $m)) {
            $jenisPenjadwalan = 'pemasalan';
            $jumlahSelang = max(1, (int) $m[1]);
        }

        $pengaturan = [
            'tanggal' => $this->request->getPost('tanggal'),
            'jam_mulai' => $this->request->getPost('jam_mulai'),
            'jam_selesai' => $this->request->getPost('jam_selesai'),
            'keterangan' => $this->request->getPost('keterangan') ?? '',
            'id_gelanggang' => $idGelanggang,
            'jumlah_partai' => $jumlahPartai,
            'babak_pertandingan' => $this->request->getPost('babak_pertandingan'),
            'jenis_penjadwalan' => $jenisPenjadwalan,
            // If categories are re-ordered via drag, prioritize classes following that category order.
            // Fallback: keep CI3-compatible `urutan_id_kelas_tanding` order.
            'urutan_id_kelas_tanding' => $this->resolveKelasOrderFromKategoriDrag(),
            'jumlah_selang_seling' => $jumlahSelang,
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
            $model->update($id, ['nama_file' => $path]);
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

    /**
     * Category drag ordering is sent by the view as:
     * - urutan_kategori_drag[] (ordered category ids)
     * - ordered_kelas_from_drag[] (checked class ids in that category order)
     */
    private function resolveKelasOrderFromKategoriDrag(): array
    {
        $orderedFromDrag = $this->request->getPost('ordered_kelas_from_drag');
        if (is_array($orderedFromDrag) && $orderedFromDrag !== []) {
            return array_values(array_unique(array_map('intval', $orderedFromDrag)));
        }

        $fallback = $this->request->getPost('urutan_id_kelas_tanding');
        if (is_array($fallback) && $fallback !== []) {
            return array_values(array_unique(array_map('intval', $fallback)));
        }

        return [];
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
