<?php

namespace App\Controllers\Utilities;

use App\Controllers\BaseController;

class DbSyncController extends BaseController
{
    public function index()
    {
        // CI3 parity: only super admin should access this page. We already protect it via route filter.
        set_time_limit(300);

        $db = db_connect();
        $dryRun = ((string) $this->request->getGet('run') !== 'true');

        $sqlFile = FCPATH . 'db/db_structure_dps.sql';
        if (! is_file($sqlFile)) {
            return $this->response->setStatusCode(500)->setBody('SQL target file not found: ' . $sqlFile);
        }

        $sqlContent = (string) file_get_contents($sqlFile);
        // Split on CREATE TABLE markers (same strategy as CI3).
        $tablesData = preg_split('/CREATE TABLE/i', $sqlContent);
        array_shift($tablesData);

        $results = [];
        $changesFound = 0;

        foreach ($tablesData as $tableSql) {
            if (! preg_match('/`([^`]+)`/', $tableSql, $matches)) {
                continue;
            }
            $tableName = $matches[1];

            // 1) Check table existence
            if (! $db->tableExists($tableName)) {
                $changesFound++;
                if ($dryRun) {
                    $results[] = [
                        'type' => 'table',
                        'name' => $tableName,
                        'status' => 'PLAN',
                        'message' => 'Will CREATE table',
                    ];
                } else {
                    $statement = explode(';', $tableSql)[0];
                    try {
                        $db->query('CREATE TABLE ' . $statement . ';');
                        $results[] = [
                            'type' => 'table',
                            'name' => $tableName,
                            'status' => 'DONE',
                            'message' => 'Table Created',
                        ];
                    } catch (\Throwable $e) {
                        $results[] = [
                            'type' => 'table',
                            'name' => $tableName,
                            'status' => 'ERROR',
                            'message' => $e->getMessage(),
                        ];
                    }
                }
                continue;
            }

            // 2) Check columns existence
            $existingFields = $db->getFieldNames($tableName);

            $firstParen = strpos($tableSql, '(');
            $lastParen = strrpos($tableSql, ')');
            if ($firstParen === false || $lastParen === false || $lastParen <= $firstParen) {
                continue;
            }

            $body = substr($tableSql, $firstParen + 1, $lastParen - $firstParen - 1);
            $lines = explode("\n", $body);

            foreach ($lines as $line) {
                $line = trim($line);
                if (strpos($line, '`') !== 0) {
                    continue;
                }

                if (! preg_match('/^`([^`]+)`\s+(.+)$/', rtrim($line, ','), $colParts)) {
                    continue;
                }

                $colName = $colParts[1] ?? null;
                $colDef = $colParts[2] ?? null;
                if (! $colName || ! $colDef) {
                    continue;
                }

                if (in_array($colName, $existingFields, true)) {
                    continue;
                }

                $changesFound++;
                if ($dryRun) {
                    $results[] = [
                        'type' => 'column',
                        'table' => $tableName,
                        'name' => $colName,
                        'status' => 'PLAN',
                        'message' => "Will ADD column `$colName`",
                    ];
                    continue;
                }

                try {
                    $db->query("ALTER TABLE `$tableName` ADD COLUMN `$colName` $colDef");
                    $results[] = [
                        'type' => 'column',
                        'table' => $tableName,
                        'name' => $colName,
                        'status' => 'DONE',
                        'message' => "Added column `$colName`",
                    ];
                } catch (\Throwable $e) {
                    $results[] = [
                        'type' => 'column',
                        'table' => $tableName,
                        'name' => $colName,
                        'status' => 'ERROR',
                        'message' => $e->getMessage(),
                    ];
                }
            }
        }

        return view('utilities/db_sync', [
            'title' => 'Database Synchronizer',
            'breadcrumb' => [
                ['link' => base_url('admin/super/operasi-basis-data'), 'content' => lang('operasi_basis_data')],
                ['link' => base_url('utilities/db-sync'), 'content' => lang('sinkronisasi_basis_data')],
            ],
            'dry_run' => $dryRun,
            'database_name' => $db->getDatabase(),
            'sync_results' => $results,
            'changes_found' => $changesFound,
        ]);
    }
}
