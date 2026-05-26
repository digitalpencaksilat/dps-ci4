<?php

namespace App\Controllers\Development;

use App\Controllers\BaseController;
use mysqli;

class DatabaseSetupController extends BaseController
{
    public function index()
    {
        return view('development/database_setup', [
            'title' => 'Database Setup',
        ]);
    }

    public function process()
    {
        $dbName = $this->request->getPost('database_name');

        if (empty($dbName)) {
            session()->setFlashdata('error', 'Database name cannot be empty.');
            return redirect()->to(base_url('development/database-setup'));
        }

        $dbName = preg_replace('/[^a-zA-Z0-9_]/', '', $dbName);

        $hostname = env('database.default.hostname', 'localhost');
        $username = env('database.default.username', 'root');
        $password = env('database.default.password', '');
        $port = env('database.default.port', '3306');

        mysqli_report(MYSQLI_REPORT_OFF);

        $mysqli = new mysqli($hostname, $username, $password, '', (int) $port);

        if ($mysqli->connect_error) {
            session()->setFlashdata('error', 'Connection failed: ' . $mysqli->connect_error);
            return redirect()->to(base_url('development/database-setup'));
        }

        $sqlCreate = "CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
        if (! $mysqli->query($sqlCreate)) {
            session()->setFlashdata('error', 'Failed to create database: ' . $mysqli->error);
            $mysqli->close();
            return redirect()->to(base_url('development/database-setup'));
        }

        $mysqli->select_db($dbName);

        $sqlFile = FCPATH . 'db/db_structure_dps.sql';

        if (! file_exists($sqlFile)) {
            session()->setFlashdata('error', 'SQL structure file not found at: ' . $sqlFile);
            $mysqli->close();
            return redirect()->to(base_url('development/database-setup'));
        }

        $sqlContents = file_get_contents($sqlFile);

        if ($mysqli->multi_query($sqlContents)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->next_result());
        }

        if ($mysqli->error) {
            session()->setFlashdata('error', 'Error importing SQL: ' . $mysqli->error);
            $mysqli->close();
            return redirect()->to(base_url('development/database-setup'));
        }

        $mysqli->close();

        session()->setFlashdata('success', "Database <b>{$dbName}</b> has been successfully created and imported!");
        return redirect()->to(base_url('development/database-setup'));
    }
}
