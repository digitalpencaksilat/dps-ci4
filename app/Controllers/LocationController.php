<?php

namespace App\Controllers;

use CodeIgniter\Database\BaseConnection;
use Throwable;

class LocationController extends BaseController
{
    public function countries()
    {
        return $this->response->setJSON($this->loadNamedMapFromTable('countries', null, 'name', 'id', true));
    }

    public function provinces()
    {
        return $this->response->setJSON($this->loadNamedMapFromTable('provinces'));
    }

    public function regencies(string $provinceId)
    {
        return $this->response->setJSON($this->loadNamedMapFromTable('regencies', ['province_id' => $provinceId]));
    }

    public function districts(string $regencyId)
    {
        return $this->response->setJSON($this->loadNamedMapFromTable('districts', ['regency_id' => $regencyId]));
    }

    public function villages(string $districtId)
    {
        return $this->response->setJSON($this->loadNamedMapFromTable('villages', ['district_id' => $districtId]));
    }

    private function loadNamedMapFromTable(
        string $table,
        ?array $where = null,
        string $nameColumn = 'name',
        string $idColumn = 'id',
        bool $returnNameAsValue = false,
    ): array
    {
        try {
            $builder = $this->locationDb()->table($table)->select($idColumn . ', ' . $nameColumn)->orderBy($nameColumn, 'ASC');

            foreach ($where ?? [] as $column => $value) {
                $builder->where($column, $value);
            }

            $rows = $builder->get()->getResultArray();
        } catch (Throwable) {
            return ['status' => false];
        }

        $result = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row[$nameColumn] ?? ''));
            $id = trim((string) ($row[$idColumn] ?? ''));

            if ($name === '' || $id === '') {
                continue;
            }

            $result[$name] = $returnNameAsValue ? $name : $id;
        }

        return $result === [] ? ['status' => false] : $result;
    }

    private function locationDb(): BaseConnection
    {
        return db_connect('location');
    }
}
