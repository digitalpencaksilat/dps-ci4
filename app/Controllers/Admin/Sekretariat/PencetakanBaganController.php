<?php

namespace App\Controllers\Admin\Sekretariat;

use App\Controllers\BaseController;
use App\Services\PencetakanBaganService;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Pencetakan Bagan (parity CI3 Sekretariat::pencetakan_bagan / cetak_semua_bagan
 * dan Kategori_lomba::cetak_bagan). Halaman index berada di sub menu Tools sekretariat.
 */
class PencetakanBaganController extends BaseController
{
    public function index(): string
    {
        $service = new PencetakanBaganService();

        return view('admin/sekretariat/pencetakan_bagan/index', [
            'title'      => 'Pencetakan Bagan',
            'breadcrumb' => 'Pencetakan Bagan',
            'activeMenu' => 'pencetakan_bagan',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'  => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Sekretariat'),
            'dataKategoriLomba' => $service->listKategoriLomba(),
        ]);
    }

    /**
     * Cetak seluruh bagan per jenis (parity cetak_semua_bagan): tanding | seni | seni_pool.
     */
    public function cetakSemua(string $jenis = 'tanding'): string
    {
        return $this->renderPrint($jenis, null);
    }

    /**
     * Cetak bagan per kategori lomba (parity Kategori_lomba::cetak_bagan).
     * $sistem dipakai untuk seni: battle (default) | pool.
     */
    public function cetakKategori(int $idKategoriLomba, string $sistem = 'battle'): string
    {
        $kategori = (new PencetakanBaganService())->getKategoriLomba($idKategoriLomba);
        if ($kategori === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (($kategori->nama_kategori_lomba ?? '') === 'tanding') {
            return $this->renderPrint('tanding', $idKategoriLomba);
        }

        if (($kategori->nama_kategori_lomba ?? '') === 'seni') {
            return $this->renderPrint($sistem === 'pool' ? 'seni_pool' : 'seni', $idKategoriLomba);
        }

        throw PageNotFoundException::forPageNotFound();
    }

    private function renderPrint(string $jenis, ?int $idKategoriLomba): string
    {
        $service = new PencetakanBaganService();
        $event   = [
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventHost' => (string) (get_setting('event_host') ?? ''),
            'logoHost'  => get_setting('event_host_big_logo', 'pendaftaran/gambar_dan_juknis')
                ?? get_setting('event_host_logo', 'pendaftaran/gambar_dan_juknis')
                ?? get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'logoEvent' => get_setting('event_big_logo', 'pendaftaran/gambar_dan_juknis')
                ?? get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'brandName' => (string) (get_setting('brand_name') ?? 'Digital Pencak Silat'),
            'brandAbbr' => strtolower((string) (get_setting('brand_abbreviation') ?? 'dps')),
        ];

        if ($jenis === 'tanding') {
            return view('admin/sekretariat/pencetakan_bagan/cetak_tanding', $event + [
                'dataKompetisiTanding' => $service->listKompetisiTanding($idKategoriLomba),
            ]);
        }

        if ($jenis === 'seni') {
            return view('admin/sekretariat/pencetakan_bagan/cetak_seni_battle', $event + [
                'dataKompetisiSeni' => $service->listKompetisiSeniBattle($idKategoriLomba),
            ]);
        }

        if ($jenis === 'seni_pool') {
            $kompetisi = $service->listKompetisiSeniPool($idKategoriLomba);
            $penampilan = [];
            foreach ($kompetisi as $row) {
                $penampilan[(int) $row->id_kompetisi_seni] = $service->listPenampilanPool((int) $row->id_kompetisi_seni);
            }

            return view('admin/sekretariat/pencetakan_bagan/cetak_seni_pool', $event + [
                'dataKompetisiSeni' => $kompetisi,
                'penampilanPerKompetisi' => $penampilan,
            ]);
        }

        throw PageNotFoundException::forPageNotFound();
    }
}
