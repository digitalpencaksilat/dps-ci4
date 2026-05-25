<?php

namespace App\Controllers\Development;

use App\Controllers\BaseController;
use App\Models\PerolehanMedaliTandingModel;
use App\Models\PerolehanMedaliSeniModel;

class DataPusherController extends BaseController
{
    public function index()
    {
        return view('development/data_pusher', [
            'title' => 'Data Pusher - Integrasi Portal',
        ]);
    }

    public function push()
    {
        error_reporting(0);

        $url = $this->request->getPost('url');
        $eventId = $this->request->getPost('event_id');
        $apiKey = $this->request->getPost('api_key');

        if (empty($url) || empty($eventId) || empty($apiKey)) {
            return $this->responseJson(['status' => 'error', 'message' => 'All fields are required'], 400);
        }

        $tandingModel = new PerolehanMedaliTandingModel();
        $seniModel = new PerolehanMedaliSeniModel();

        $results = [];

        $tandingRaw = $tandingModel->getPemenangTandingPortal();
        foreach ($tandingRaw as $row) {
            $kelasLabel = isset($row->label) ? 'Kelas ' . $row->label : 'N/A';
            $poolLabel = (isset($row->nomor_pool) && $row->nomor_pool !== '') ? ' - Pool ' . $row->nomor_pool : '';

            $results[] = [
                'category_main' => 'tanding',
                'category_detail' => $kelasLabel . $poolLabel,
                'age_category' => $row->nama_kategori_usia ?? 'N/A',
                'gender' => isset($row->jenis_kelamin) ? ucfirst($row->jenis_kelamin) : 'N/A',
                'winner_name' => $row->nama_pendaftar ?? 'N/A',
                'contingent' => $row->nama_kontingen ?? 'N/A',
                'school' => (isset($row->nama_sekolah) && trim((string) $row->nama_sekolah) !== '') ? trim($row->nama_sekolah) : null,
                'rank_label' => isset($row->jenis_medali) ? ucfirst($row->jenis_medali) : 'N/A',
            ];
        }

        $seniRaw = $seniModel->getPemenangSeniPortal();
        foreach ($seniRaw as $row) {
            $jenisSeni = isset($row->jenis_seni) ? ucfirst($row->jenis_seni) : '';
            $namaSeni = isset($row->nama_seni) ? ' - ' . $row->nama_seni : '';
            $poolLabel = (isset($row->nomor_pool) && $row->nomor_pool !== '') ? ' - Pool ' . $row->nomor_pool : '';

            $results[] = [
                'category_main' => 'seni',
                'category_detail' => $jenisSeni . $namaSeni . $poolLabel,
                'age_category' => $row->nama_kategori_usia ?? 'N/A',
                'gender' => isset($row->jenis_kelamin) ? ucfirst($row->jenis_kelamin) : 'N/A',
                'winner_name' => isset($row->anggota_kelompok_peserta_seni) ? str_replace('<br>', ', ', $row->anggota_kelompok_peserta_seni) : 'N/A',
                'contingent' => $row->nama_kontingen ?? 'N/A',
                'school' => (isset($row->sekolah_kelompok_peserta_seni) && trim((string) $row->sekolah_kelompok_peserta_seni) !== '') ? trim($row->sekolah_kelompok_peserta_seni) : null,
                'rank_label' => isset($row->jenis_medali) ? ucfirst($row->jenis_medali) : 'N/A',
            ];
        }

        if ($results === []) {
            return $this->responseJson(['status' => 'error', 'message' => 'No medalist data found to push'], 404);
        }

        $payload = [
            'event_id' => $eventId,
            'results' => $results,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-API-KEY: ' . $apiKey,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $resultData = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return $this->responseJson([
                'status' => 'success',
                'message' => 'Successfully pushed ' . count($results) . ' results.',
                'api_response' => $resultData,
            ]);
        }

        return $this->responseJson([
            'status' => 'error',
            'message' => 'API Error (HTTP ' . $httpCode . '): ' . ($resultData['message'] ?? 'Unknown error'),
            'api_response' => $resultData,
        ], $httpCode);
    }

    private function responseJson(array $data, int $statusCode = 200)
    {
        return $this->response
            ->setContentType('application/json')
            ->setStatusCode($statusCode)
            ->setBody(json_encode($data));
    }
}
