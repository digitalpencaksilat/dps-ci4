<?php

namespace App\Controllers\Admin\Bendahara;

use App\Controllers\BaseController;
use App\Services\PembayaranAdminService;
use App\Services\PembayaranKontingenService;
use App\Services\Pdf\NotaPembayaranPdfService;
use CodeIgniter\Exceptions\PageNotFoundException;

class PembayaranController extends BaseController
{
    public function index(): string
    {
        return $this->renderList('Semua Pembayaran', 'all', null);
    }

    public function waiting(): string
    {
        return $this->renderList('Pembayaran Menunggu Konfirmasi', 'waiting', 'menunggu');
    }

    public function paid(): string
    {
        return $this->renderList('Pembayaran Lunas', 'paid', 'lunas');
    }

    public function tanding(): string
    {
        return view('admin/bendahara/pembayaran/tanding', [
            'title'          => 'Riwayat Pembayaran Tanding',
            'activeMenu'     => 'pembayaran',
            'paymentSubmenu' => 'tanding',
            'eventName'      => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'      => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'      => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Bendahara'),
            'rows'           => (new PembayaranAdminService())->tandingPaymentHistory(),
        ]);
    }

    public function seni(): string
    {
        return view('admin/bendahara/pembayaran/seni', [
            'title'          => 'Riwayat Pembayaran Seni',
            'activeMenu'     => 'pembayaran',
            'paymentSubmenu' => 'seni',
            'eventName'      => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'      => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'      => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Bendahara'),
            'rows'           => (new PembayaranAdminService())->seniPaymentHistory(),
        ]);
    }

    public function show(int $id): string
    {
        if ($id <= 0) {
            throw PageNotFoundException::forPageNotFound();
        }

        $detail = (new PembayaranAdminService())->transactionDetail($id);
        if ($detail === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/bendahara/pembayaran/show', [
            'title'          => 'Rincian Pembayaran',
            'activeMenu'     => 'pembayaran',
            'paymentSubmenu' => 'all',
            'eventName'      => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'      => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'      => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Bendahara'),
            'detail'         => $detail,
            'accounts'       => (new PembayaranKontingenService())->accounts(),
        ]);
    }

    public function nota(int $id): string
    {
        return view('admin/bendahara/pembayaran/nota', (new NotaPembayaranPdfService())->payloadForView($id));
    }

    public function confirm(int $id)
    {
        try {
            $result = (new PembayaranAdminService())->confirm($id);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/bendahara/pembayaran'))
                ->with('status', false)
                ->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/bendahara/pembayaran/' . $id))
            ->with('status', $result)
            ->with('message', $result ? 'Pembayaran berhasil dikonfirmasi menjadi lunas.' : 'Konfirmasi pembayaran gagal diproses.');
    }

    public function reject(int $id)
    {
        try {
            $result = (new PembayaranAdminService())->reject($id);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/bendahara/pembayaran'))
                ->with('status', false)
                ->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/bendahara/pembayaran'))
            ->with('status', $result)
            ->with('message', $result ? 'Pembayaran ditolak. Relasi item dan bukti pembayaran sudah dibersihkan.' : 'Penolakan pembayaran gagal diproses.');
    }

    public function notaPdf(int $id)
    {
        return (new NotaPembayaranPdfService())->stream($id);
    }

    public function unpaid(): string
    {
        $overview = (new PembayaranAdminService())->unpaidItemsOverview();

        return view('admin/bendahara/pembayaran/unpaid', [
            'title'          => 'Item Belum Dibayar',
            'activeMenu'     => 'pembayaran',
            'paymentSubmenu' => 'unpaid',
            'eventName'      => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'      => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'      => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Bendahara'),
            'overview'       => $overview,
        ]);
    }

    public function createForKontingen(int $idKontingen)
    {
        $file = $this->request->getFile('foto');

        if (! $this->validate(['foto' => 'uploaded[foto]|is_image[foto]|max_size[foto,10240]'])) {
            return redirect()->to(base_url('admin/bendahara/kontingen/' . $idKontingen))->with('status', false)->with('message', $this->validator->getErrors());
        }

        $tandingIds = array_values(array_filter(array_map('intval', (array) $this->request->getPost('id_peserta_tanding'))));
        $seniIds = array_values(array_filter(array_map('intval', (array) $this->request->getPost('id_kelompok_peserta_seni'))));

        try {
            (new PembayaranKontingenService())->createForAdmin($idKontingen, $tandingIds, $seniIds, $file);
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('admin/bendahara/kontingen/' . $idKontingen))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('admin/bendahara/kontingen/' . $idKontingen))->with('status', true)->with('message', 'Transaksi pembayaran berhasil dibuat dari panel bendahara.');
    }

    private function renderList(string $title, string $paymentSubmenu, ?string $status): string
    {
        $transactions = (new PembayaranAdminService())->transactions($status);

        return view('admin/bendahara/pembayaran/index', [
            'title'          => $title,
            'activeMenu'     => 'pembayaran',
            'paymentSubmenu' => $paymentSubmenu,
            'eventName'      => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'      => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName'      => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin Bendahara'),
            'transactions'   => $transactions,
        ]);
    }
}
