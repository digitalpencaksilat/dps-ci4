<?php

namespace App\Controllers\Development;

use App\Controllers\BaseController;
use Config\Database;

class DatabaseManagerController extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $tables = $db->listTables();
        $currentDb = $db->getDatabase();

        $allDatabases = [];
        $systemDbs = ['information_schema', 'mysql', 'performance_schema', 'phpmyadmin', 'sys'];
        $query = $db->query('SHOW DATABASES');
        foreach ($query->getResultArray() as $row) {
            $dbName = reset($row);
            if (! in_array($dbName, $systemDbs, true)) {
                $allDatabases[] = $dbName;
            }
        }

        return view('development/database_manager', [
            'title' => 'Database Manager',
            'tables' => $tables,
            'current_db' => $currentDb,
            'all_databases' => $allDatabases,
        ]);
    }

    public function switchDatabase()
    {
        $newDb = $this->request->getPost('new_database');

        if (empty($newDb)) {
            session()->setFlashdata('error', 'Please select a database.');
            return redirect()->to(base_url('development/database-manager'));
        }

        $newDb = preg_replace('/[^a-zA-Z0-9_]/', '', $newDb);

        $envPath = ROOTPATH . '.env';

        if (! is_writable($envPath)) {
            session()->setFlashdata('error', 'Cannot write to .env file. Check file permissions.');
            return redirect()->to(base_url('development/database-manager'));
        }

        $envContent = file_get_contents($envPath);

        $pattern = '/^database\.default\.database\s*=\s*(.*)$/m';
        $replacement = 'database.default.database = ' . $newDb;

        if (preg_match($pattern, $envContent)) {
            $newContent = preg_replace($pattern, $replacement, $envContent);
        } else {
            $newContent = rtrim($envContent) . "\n" . $replacement . "\n";
        }

        if ($newContent === $envContent) {
            session()->setFlashdata('error', 'Database is already active: <b>' . esc($newDb) . '</b>');
            return redirect()->to(base_url('development/database-manager'));
        }

        file_put_contents($envPath, $newContent);

        session()->setFlashdata('success', 'Switched to database: <b>' . esc($newDb) . '</b>. Halaman akan dimuat ulang.');
        return redirect()->to(base_url('development/database-manager'));
    }

    public function export()
    {
        $db = db_connect();
        $dbName = $db->getDatabase();
        $tables = $db->listTables();

        $output = "-- Database backup: {$dbName}\n";
        $output .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        foreach ($tables as $table) {
            $row = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();
            if (isset($row['Create Table'])) {
                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $output .= $row['Create Table'] . ";\n\n";
            }

            $rows = $db->table($table)->get()->getResultArray();
            if ($rows !== []) {
                $columns = array_keys($rows[0]);
                $columnList = '`' . implode('`, `', $columns) . '`';

                $output .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
                $valueRows = [];
                foreach ($rows as $dataRow) {
                    $values = [];
                    foreach ($dataRow as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            $values[] = $db->escape($value);
                        }
                    }
                    $valueRows[] = '(' . implode(', ', $values) . ')';
                }
                $output .= implode(",\n", $valueRows) . ";\n\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        $filename = $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';
        return $this->response
            ->setHeader('Content-Type', 'application/sql')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($output);
    }

    public function import()
    {
        $file = $this->request->getFile('sql_file');

        if (! $file || ! $file->isValid()) {
            session()->setFlashdata('error', 'Please upload a valid SQL file.');
            return redirect()->to(base_url('development/database-manager'));
        }

        $tmpPath = $file->getTempName();
        $handle = fopen($tmpPath, 'r');
        if (! $handle) {
            session()->setFlashdata('error', 'Failed to open the uploaded SQL file.');
            return redirect()->to(base_url('development/database-manager'));
        }

        $db = db_connect();
        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        $templine = '';
        $successCount = 0;
        $errorCount = 0;
        $lastError = '';

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '#')) {
                continue;
            }

            $templine .= $line;

            if (str_ends_with($trimmed, ';')) {
                try {
                    $db->query($templine);
                    $successCount++;
                } catch (\Throwable $e) {
                    $errorCount++;
                    $lastError = $e->getMessage();
                }
                $templine = '';
            }
        }
        fclose($handle);

        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        if ($errorCount > 0) {
            $msg = "Import selesai dengan {$errorCount} kesalahan. Berhasil mengeksekusi {$successCount} perintah.";
            if ($lastError) {
                $msg .= " Error terakhir: " . $lastError;
            }
            session()->setFlashdata('error', $msg);
        } else {
            session()->setFlashdata('success', "Database berhasil diimpor sepenuhnya! Total {$successCount} perintah dieksekusi.");
        }

        return redirect()->to(base_url('development/database-manager'));
    }

    public function emptyTables()
    {
        $passCode = $this->request->getPost('pass_code');

        if ($passCode !== env('DEV_SECURITY_PASSCODE', '4321')) {
            session()->setFlashdata('error', 'Invalid Passcode.');
            return redirect()->to(base_url('development/database-manager'));
        }

        $excludedTables = [
            'admin',
            'site_builder_menus',
            'site_builder_pages',
            'site_builder_page_translations',
            'site_builder_settings',
            'site_builder_uploads',
        ];

        $db = db_connect();
        $tables = $db->listTables();
        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        $count = 0;
        foreach ($tables as $table) {
            if (! in_array($table, $excludedTables, true)) {
                $db->table($table)->emptyTable();
                $count++;
            }
        }

        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        session()->setFlashdata('success', "Successfully emptied {$count} tables.");
        return redirect()->to(base_url('development/database-manager'));
    }

    public function dropTables()
    {
        $passCode = $this->request->getPost('pass_code');

        if ($passCode !== env('DEV_SECURITY_PASSCODE', '4321')) {
            session()->setFlashdata('error', 'Invalid Passcode.');
            return redirect()->to(base_url('development/database-manager'));
        }

        $db = db_connect();
        $forge = \Config\Database::forge();
        $tables = $db->listTables();

        $db->query('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            $forge->dropTable($table, true);
        }
        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        session()->setFlashdata('success', 'All tables have been dropped.');
        return redirect()->to(base_url('development/database-manager'));
    }
}
