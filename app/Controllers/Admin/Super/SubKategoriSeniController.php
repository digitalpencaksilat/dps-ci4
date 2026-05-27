<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;

class SubKategoriSeniController extends BaseController
{
    public function index(): string
    {
        $rows = db_connect()
            ->table('sub_kategori_seni sks')
            ->select('sks.*, kl.nama_kategori_lomba, kl.jenis_perlombaan, kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->orderBy('ku.id_kategori_usia', 'ASC')
            ->orderBy('kl.id_kategori_lomba', 'ASC')
            ->orderBy('sks.id_sub_kategori_seni', 'ASC')
            ->get()
            ->getResult();

        return view('admin/super/sub_kategori_seni/index', $this->viewData([
            'rows' => $rows,
            'activeMode' => (string) (session()->get('tipe_super_admin') ?? ''),
        ], 'Sub Kategori Seni'));
    }

    private function viewData(array $data, string $title): array
    {
        return $data + [
            'title' => $title,
            'activeMenu' => 'pengaturan_kategori_lomba',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Super Admin'),
        ];
    }
}
