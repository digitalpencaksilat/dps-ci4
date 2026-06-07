<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\SekretariatKategoriSeniService;
use App\Services\SekretariatKategoriTandingService;
use App\Services\SistemGugurTunggalService;

/**
 * Drawing Prestasi terkonsolidasi untuk role super_admin.
 *
 * Parity CI3:
 * - Kompetisi_tanding::drawing_tanding_prestasi (+ acak_bagan, halaman/buat_bagan_manual, sinkronkan_bagan)
 * - Kompetisi_seni::drawing_seni_battle_prestasi (+ acak_bagan, sinkronkan_bagan)
 * - Kompetisi_seni::drawing_seni_pool_prestasi (+ beri_nomor_undi)
 *
 * Logika drawing memakai service yang sudah dimigrasikan (SistemGugurTunggalService /
 * Sekretariat*Service) agar tidak ada duplikasi dengan halaman pool sekretariat.
 */
class DrawingPrestasiController extends BaseController
{
    private const BASE = 'admin/super/drawing-prestasi';

    // =====================================================================
    // TANDING
    // =====================================================================

    public function tanding(): string
    {
        $service = new SekretariatKategoriTandingService();
        $rows = $this->filterPrestasi($service->listPool());

        $data = [
            'activeMenu' => 'drawing_prestasi_tanding',
            'rows' => $rows,
            'selected' => null,
            'prev' => null,
            'next' => null,
            'peserta' => [],
            'pertandinganRows' => [],
        ];

        $id = (int) ($this->request->getGet('id') ?? 0);
        if ($id > 0) {
            $selected = $service->getPool($id);
            if ($selected !== null && ($selected->jenis_perlombaan ?? '') === 'prestasi') {
                [$prev, $next] = $this->prevNext($rows, $id, 'id_kompetisi_tanding');
                $data['selected'] = $selected;
                $data['prev'] = $prev;
                $data['next'] = $next;
                $data['peserta'] = $service->listPesertaByPool($id);
                $data['pertandinganRows'] = $service->listPertandinganByPool($id);
            }
        }

        return view('admin/super/drawing_prestasi/tanding', $this->viewData($data, 'Drawing Prestasi - Tanding'));
    }

    public function acakBaganTanding(int $id)
    {
        try {
            $mode = (string) ($this->request->getPost('mode') ?? 'full_random_persilat');
            $result = (new SistemGugurTunggalService())->acakBaganTanding($id, $mode);
            return $this->redirectTanding($id, true, 'Bagan tanding berhasil dibuat: ' . $result['jumlah_pertandingan'] . ' pertandingan.');
        } catch (\Throwable $e) {
            return $this->redirectTanding($id, false, $e->getMessage());
        }
    }

    public function sinkronkanBaganTanding(int $id)
    {
        try {
            (new SistemGugurTunggalService())->sinkronkanBaganTanding($id);
            return $this->redirectTanding($id, true, 'Bagan tanding berhasil disinkronkan dengan database.');
        } catch (\Throwable $e) {
            return $this->redirectTanding($id, false, $e->getMessage());
        }
    }

    public function halamanAcakBaganManualTanding(int $id): string
    {
        $service = new SekretariatKategoriTandingService();
        $selected = $service->getPool($id);
        if ($selected === null || ($selected->jenis_perlombaan ?? '') !== 'prestasi') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $peserta = $service->listPesertaByPool($id);

        return view('admin/super/drawing_prestasi/acak_manual_tanding', $this->viewData([
            'activeMenu' => 'drawing_prestasi_tanding',
            'selected' => $selected,
            'peserta' => $peserta,
            'jumlahPeserta' => count($peserta),
        ], 'Manual Shuffle - ' . trim(($selected->nama_kategori_usia ?? '') . ' ' . ($selected->label ?? ''))));
    }

    public function buatBaganManualTanding(int $id)
    {
        try {
            $idPeserta = array_map('intval', (array) $this->request->getPost('id_peserta_tanding'));
            $urutanSlot = array_map('intval', (array) $this->request->getPost('urutan_slot'));
            (new SistemGugurTunggalService())->buatBaganManualTanding($id, $urutanSlot, $idPeserta);
            return $this->redirectTanding($id, true, 'Bagan tanding berhasil diacak secara manual.');
        } catch (\Throwable $e) {
            return redirect()->to(base_url(self::BASE . '/tanding/' . $id . '/acak-manual'))->with('status', false)->with('message', $e->getMessage());
        }
    }

    // =====================================================================
    // SENI BATTLE
    // =====================================================================

    public function seniBattle(): string
    {
        $service = new SekretariatKategoriSeniService();
        $rows = $this->filterPrestasiBattle($service->listPool());

        $data = [
            'activeMenu' => 'drawing_prestasi_seni_battle',
            'rows' => $rows,
            'selected' => null,
            'prev' => null,
            'next' => null,
            'kelompok' => [],
            'battleRows' => [],
        ];

        $id = (int) ($this->request->getGet('id') ?? 0);
        if ($id > 0) {
            $selected = $service->getPool($id);
            if ($selected !== null && ($selected->jenis_perlombaan ?? '') === 'prestasi') {
                [$prev, $next] = $this->prevNext($rows, $id, 'id_kompetisi_seni');
                $data['selected'] = $selected;
                $data['prev'] = $prev;
                $data['next'] = $next;
                $data['kelompok'] = $service->listKelompokByPool($id);
                $data['battleRows'] = $service->listBattleByPool($id);
            }
        }

        return view('admin/super/drawing_prestasi/seni_battle', $this->viewData($data, 'Drawing Prestasi - Seni Battle'));
    }

    public function acakBaganBattleSeni(int $id)
    {
        try {
            $mode = (string) ($this->request->getPost('mode') ?? 'full_random_persilat');
            $result = (new SistemGugurTunggalService())->acakBaganBattleSeni($id, $mode);
            return $this->redirectSeniBattle($id, true, 'Bagan battle seni berhasil dibuat: ' . $result['jumlah_battle'] . ' battle.');
        } catch (\Throwable $e) {
            return $this->redirectSeniBattle($id, false, $e->getMessage());
        }
    }

    public function sinkronkanBaganBattleSeni(int $id)
    {
        try {
            (new SistemGugurTunggalService())->sinkronkanBaganBattleSeni($id);
            return $this->redirectSeniBattle($id, true, 'Bagan battle seni berhasil disinkronkan dengan database.');
        } catch (\Throwable $e) {
            return $this->redirectSeniBattle($id, false, $e->getMessage());
        }
    }

    // =====================================================================
    // SENI POOL
    // =====================================================================

    public function seniPool(): string
    {
        $service = new SekretariatKategoriSeniService();
        $rows = $this->filterPrestasiPool($service->listPool());

        $data = [
            'activeMenu' => 'drawing_prestasi_seni_pool',
            'rows' => $rows,
            'selected' => null,
            'prev' => null,
            'next' => null,
            'kelompok' => [],
        ];

        $id = (int) ($this->request->getGet('id') ?? 0);
        if ($id > 0) {
            $selected = $service->getPool($id);
            if ($selected !== null && ($selected->jenis_perlombaan ?? '') === 'prestasi') {
                [$prev, $next] = $this->prevNext($rows, $id, 'id_kompetisi_seni');
                $data['selected'] = $selected;
                $data['prev'] = $prev;
                $data['next'] = $next;
                $data['kelompok'] = $service->listKelompokByPool($id);
            }
        }

        return view('admin/super/drawing_prestasi/seni_pool', $this->viewData($data, 'Drawing Prestasi - Seni Pool'));
    }

    public function beriNomorUndi(int $id)
    {
        try {
            $ids = array_map('intval', (array) $this->request->getPost('id_kelompok_peserta_seni'));
            $this->simpanNomorUndi($id, $ids);
            return $this->redirectSeniPool($id, true, 'Pengundian berhasil ditetapkan.');
        } catch (\Throwable $e) {
            return $this->redirectSeniPool($id, false, $e->getMessage());
        }
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    /**
     * Simpan hasil undian roulette: nomor_undi = urutan terpilih.
     * Parity CI3 Kompetisi_seni::beri_nomor_undi.
     *
     * @param array<int,int> $idKelompokTerurut
     */
    private function simpanNomorUndi(int $idKompetisiSeni, array $idKelompokTerurut): void
    {
        $db = db_connect();
        $kompetisi = $db->table('kompetisi_seni')->where('id_kompetisi_seni', $idKompetisiSeni)->get()->getRow();
        if ($kompetisi === null) {
            throw new \RuntimeException('Pool seni tidak ditemukan.');
        }

        $jumlahKelompok = (int) $db->table('kelompok_peserta_seni')->where('id_kompetisi_seni', $idKompetisiSeni)->countAllResults();
        if ($idKelompokTerurut === []) {
            throw new \RuntimeException('Data peserta terundi tidak ditemukan.');
        }
        if (count($idKelompokTerurut) !== $jumlahKelompok) {
            throw new \RuntimeException('Jumlah peserta terundi tidak sesuai dengan jumlah peserta di dalam pool.');
        }

        foreach ($idKelompokTerurut as $index => $idKelompok) {
            $db->table('kelompok_peserta_seni')
                ->where('id_kelompok_peserta_seni', $idKelompok)
                ->where('id_kompetisi_seni', $idKompetisiSeni)
                ->update(['nomor_undi' => $index + 1]);
        }
    }

    /**
     * @param array<int,object> $rows
     * @return array{0:?object,1:?object} [prev, next]
     */
    private function prevNext(array $rows, int $id, string $key): array
    {
        $index = null;
        foreach ($rows as $position => $row) {
            if ((int) ($row->{$key} ?? 0) === $id) {
                $index = $position;
                break;
            }
        }

        if ($index === null) {
            return [null, null];
        }

        return [$rows[$index - 1] ?? null, $rows[$index + 1] ?? null];
    }

    /** @param array<int,object> $rows */
    private function filterPrestasi(array $rows): array
    {
        return array_values(array_filter($rows, static fn ($row): bool => ($row->jenis_perlombaan ?? '') === 'prestasi'));
    }

    /** @param array<int,object> $rows */
    private function filterPrestasiBattle(array $rows): array
    {
        return array_values(array_filter($rows, static fn ($row): bool => ($row->jenis_perlombaan ?? '') === 'prestasi' && ($row->sistem_penampilan ?? '') === 'battle'));
    }

    /** @param array<int,object> $rows */
    private function filterPrestasiPool(array $rows): array
    {
        return array_values(array_filter($rows, static fn ($row): bool => ($row->jenis_perlombaan ?? '') === 'prestasi' && ($row->sistem_penampilan ?? '') === 'pool'));
    }

    private function redirectTanding(int $id, bool $status, string $message)
    {
        return redirect()->to(base_url(self::BASE . '/tanding?id=' . $id))->with('status', $status)->with('message', $message);
    }

    private function redirectSeniBattle(int $id, bool $status, string $message)
    {
        return redirect()->to(base_url(self::BASE . '/seni-battle?id=' . $id))->with('status', $status)->with('message', $message);
    }

    private function redirectSeniPool(int $id, bool $status, string $message)
    {
        return redirect()->to(base_url(self::BASE . '/seni-pool?id=' . $id))->with('status', $status)->with('message', $message);
    }

    private function viewData(array $data, string $title): array
    {
        return $data + [
            'title' => $title,
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Super'),
        ];
    }
}
