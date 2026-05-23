<?php

namespace App\Controllers;

class LocationController extends BaseController
{
    public function countries()
    {
        $data = $this->readJsonFile(FCPATH . 'assets/location/negara.json');
        $result = [];

        foreach ($data as $row) {
            if (isset($row['name'])) {
                $result[$row['name']] = $row['name'];
            }
        }

        ksort($result);

        return $this->response->setJSON($result);
    }

    public function provinces()
    {
        $data = $this->readJsonFile(FCPATH . 'assets/location/provinsi.json');
        $result = [];

        foreach ($data as $row) {
            if (isset($row['nama'], $row['id'])) {
                $result[$row['nama']] = $row['id'];
            }
        }

        ksort($result);

        return $this->response->setJSON($result);
    }

    public function regencies(string $provinceId)
    {
        return $this->response->setJSON($this->loadNamedMap(FCPATH . 'assets/location/kabupaten/' . $provinceId . '.json'));
    }

    public function districts(string $regencyId)
    {
        return $this->response->setJSON($this->loadNamedMap(FCPATH . 'assets/location/kecamatan/' . $regencyId . '.json'));
    }

    public function villages(string $districtId)
    {
        return $this->response->setJSON($this->loadNamedMap(FCPATH . 'assets/location/kelurahan/' . $districtId . '.json'));
    }

    private function loadNamedMap(string $path): array
    {
        $data = $this->readJsonFile($path);
        $result = [];

        foreach ($data as $row) {
            if (isset($row['nama'], $row['id'])) {
                $result[$row['nama']] = $row['id'];
            }
        }

        ksort($result);

        return $result === [] ? ['status' => false] : $result;
    }

    private function readJsonFile(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
