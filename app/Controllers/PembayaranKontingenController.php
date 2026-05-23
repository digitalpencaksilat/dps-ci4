<?php

namespace App\Controllers;

use App\Services\PembayaranKontingenService;
use App\Services\UploadedFilePayload;
use CodeIgniter\Exceptions\PageNotFoundException;

class PembayaranKontingenController extends BaseController
{
    public function index(): string
    {
        $service = new PembayaranKontingenService();
        $idKontingen = (int) session()->get('id_kontingen');
        $pending = $service->pendingItems($idKontingen);

        return view('kontingen/pembayaran/index', [
            'title'        => 'Pembayaran',
            'activeMenu'   => 'pembayaran',
            'paymentSubmenu' => 'tagihan',
            'eventName'    => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'    => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'kontingen'    => $pending['kontingen'],
            'tanding'      => $pending['tanding'],
            'seni'         => $pending['seni'],
            'accounts'     => $service->accounts(),
            'allowPayment' => (bool) (ci3_config_item('perbolehkan_kontingen_melunasi_pembayaran', 'pendaftaran/akses_pendaftaran') ?? false),
        ]);
    }

    public function store()
    {
        if (! (bool) (ci3_config_item('perbolehkan_kontingen_melunasi_pembayaran', 'pendaftaran/akses_pendaftaran') ?? false)) {
            return redirect()->to(base_url('kontingen/pembayaran'))->with('status', false)->with('message', 'Akses pembayaran sedang ditutup.');
        }

        $file = $this->request->getFile('foto');
        if (! $this->validate(['foto' => 'uploaded[foto]|is_image[foto]|max_size[foto,10240]'])) {
            return redirect()->to(base_url('kontingen/pembayaran'))->with('status', false)->with('message', $this->validator->getErrors());
        }

        $tandingIds = array_map('intval', (array) $this->request->getPost('id_peserta_tanding'));
        $seniIds = array_map('intval', (array) $this->request->getPost('id_kelompok_peserta_seni'));

        try {
            (new PembayaranKontingenService())->create(
                (int) session()->get('id_kontingen'),
                $tandingIds,
                $seniIds,
                new UploadedFilePayload($file, (int) session()->get('id_kontingen'))
            );
        } catch (\RuntimeException $e) {
            return redirect()->to(base_url('kontingen/pembayaran'))->with('status', false)->with('message', $e->getMessage());
        }

        return redirect()->to(base_url('kontingen/pembayaran/menunggu-konfirmasi'))->with('status', true)->with('message', 'Bukti pembayaran berhasil diunggah. Silakan tunggu konfirmasi admin.');
    }

    public function waiting(): string
    {
        return $this->renderTransactions('menunggu', 'Menunggu Konfirmasi', 'kontingen/pembayaran/waiting');
    }

    public function paid(): string
    {
        return $this->renderTransactions('lunas', 'Pembayaran Lunas', 'kontingen/pembayaran/paid');
    }

    public function show(int $id)
    {
        $detail = (new PembayaranKontingenService())->transactionDetail((int) session()->get('id_kontingen'), $id);
        if ($detail === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('kontingen/pembayaran/show', [
            'title'      => 'Rincian Pembayaran',
            'activeMenu' => 'pembayaran',
            'paymentSubmenu' => 'tagihan',
            'eventName'  => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'  => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'detail'     => $detail,
        ]);
    }

    private function renderTransactions(string $status, string $title, string $view): string
    {
        $transactions = (new PembayaranKontingenService())->transactionsByStatus((int) session()->get('id_kontingen'), $status);

        return view($view, [
            'title'        => $title,
            'activeMenu'   => 'pembayaran',
            'paymentSubmenu' => $status === 'lunas' ? 'paid' : 'waiting',
            'eventName'    => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo'    => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'transactions' => $transactions,
        ]);
    }
}
