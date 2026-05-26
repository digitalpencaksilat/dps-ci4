<?php

namespace App\Services\Pdf;

use App\Services\PembayaranAdminService;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use Mpdf\Output\Destination;

class NotaPembayaranPdfService
{
    public function __construct(
        private readonly PembayaranAdminService $pembayaranService = new PembayaranAdminService(),
        private readonly MpdfService $mpdfService = new MpdfService()
    ) {
    }

    public function stream(int $idPembayaran): ResponseInterface
    {
        $payload = $this->payload($idPembayaran);
        $mpdf = $this->mpdfService->make();
        $mpdf->WriteHTML($this->renderHtml($payload));

        $content = $mpdf->Output($this->filename($idPembayaran), Destination::STRING_RETURN);

        return service('response')
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $this->filename($idPembayaran) . '"')
            ->setBody($content);
    }

    public function renderHtml(array $payload): string
    {
        return view('admin/bendahara/pdf/nota_pembayaran', $payload);
    }

    public function payloadForView(int $idPembayaran): array
    {
        return $this->payload($idPembayaran);
    }

    private function payload(int $idPembayaran): array
    {
        $detail = $this->pembayaranService->transactionDetail($idPembayaran);
        if ($detail === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return [
            'detail'    => $detail,
            'title'     => 'Nota Pembayaran',
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'issuedAt'  => date('Y-m-d H:i:s'),
        ];
    }

    private function filename(int $idPembayaran): string
    {
        return 'nota-pembayaran-' . $idPembayaran . '.pdf';
    }
}
