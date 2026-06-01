<?php

namespace App\Controllers\Admin\Super;

use App\Controllers\BaseController;
use App\Services\Admin\Super\ImportExcelDataService;
use CodeIgniter\HTTP\ResponseInterface;

class ImportExcelDataController extends BaseController
{
    private const TEMPLATE_PATH = ROOTPATH . 'public/assets/excel/FORM_EXCEL.xlsx';

    public function index(): string
    {
        (new ImportExcelDataService())->cleanupExpiredPreviews();

        return view('admin/super/import_excel/index', $this->viewData([
            'activeMenu' => 'import_excel_data',
        ], 'Import Data Excel'));
    }

    public function downloadTemplate(): ResponseInterface
    {
        if (! is_file(self::TEMPLATE_PATH)) {
            return redirect()->to(base_url('admin/super/import-excel-data'))
                ->with('status', false)
                ->with('message', 'Template Excel tidak ditemukan. Hubungi pengembang.');
        }

        return $this->response->download(self::TEMPLATE_PATH, null)->setFileName('FORM_EXCEL.xlsx');
    }

    public function preview(string $token = '')
    {
        $service = new ImportExcelDataService();

        if ($token !== '') {
            $payload = $service->loadPreview($token);
            if ($payload === null) {
                return redirect()->to(base_url('admin/super/import-excel-data'))
                    ->with('status', false)
                    ->with('message', 'Sesi pratinjau tidak ditemukan atau sudah kedaluwarsa. Silakan upload ulang.');
            }

            return view('admin/super/import_excel/preview', $this->viewData([
                'activeMenu' => 'import_excel_data',
                'preview' => $payload,
                'columnLabels' => $service->columnLabels(),
            ], 'Pratinjau Import Excel'));
        }

        if (! $this->request->is('post')) {
            return redirect()->to(base_url('admin/super/import-excel-data'));
        }

        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to(base_url('admin/super/import-excel-data'))
                ->with('status', false)
                ->with('message', 'File Excel tidak valid.');
        }

        try {
            $payload = $service->buildPreviewFromUpload($file, (string) (session()->get('username') ?? 'super_admin'));
        } catch (\Throwable $e) {
            return redirect()->to(base_url('admin/super/import-excel-data'))
                ->with('status', false)
                ->with('message', 'Gagal memproses file: ' . $e->getMessage());
        }

        return redirect()->to(base_url('admin/super/import-excel-data/preview/' . $payload['token']));
    }

    public function commit()
    {
        $token = (string) $this->request->getPost('token');
        $service = new ImportExcelDataService();
        $payload = $service->loadPreview($token);

        if ($payload === null) {
            return redirect()->to(base_url('admin/super/import-excel-data'))
                ->with('status', false)
                ->with('message', 'Sesi pratinjau tidak ditemukan atau sudah kedaluwarsa. Silakan upload ulang.');
        }

        if (empty($payload['is_valid'])) {
            return redirect()->to(base_url('admin/super/import-excel-data/preview/' . $token))
                ->with('status', false)
                ->with('message', 'Data masih mengandung kesalahan. Tidak dapat diimport.');
        }

        try {
            $stats = $service->commitPreview($payload);
        } catch (\Throwable $e) {
            return redirect()->to(base_url('admin/super/import-excel-data/preview/' . $token))
                ->with('status', false)
                ->with('message', $e->getMessage());
        }

        $service->deletePreview($token);

        return redirect()->to(base_url('admin/super/import-excel-data'))
            ->with('status', true)
            ->with('message', sprintf(
                'Import berhasil. %d baris diproses (Tanding: %d, Tunggal: %d, Ganda: %d, Beregu: %d).',
                (int) ($payload['total_rows_in_excel'] ?? 0),
                (int) ($stats['tanding'] ?? 0),
                (int) ($stats['tunggal'] ?? 0),
                (int) ($stats['ganda'] ?? 0),
                (int) ($stats['beregu'] ?? 0)
            ));
    }

    public function cancel()
    {
        $token = (string) ($this->request->getPost('token') ?? '');
        if ($token !== '') {
            (new ImportExcelDataService())->deletePreview($token);
        }

        return redirect()->to(base_url('admin/super/import-excel-data'))
            ->with('status', true)
            ->with('message', 'Pratinjau dibatalkan. Tidak ada data yang diimport.');
    }

    private function viewData(array $data, string $title): array
    {
        return $data + [
            'title' => $title,
            'eventName' => (string) (get_setting('event_name') ?? 'Digital Pencak Silat'),
            'eventLogo' => get_setting('event_logo', 'pendaftaran/gambar_dan_juknis'),
            'adminName' => (string) (session()->get('nama') ?? session()->get('username') ?? 'Admin'),
        ];
    }
}
