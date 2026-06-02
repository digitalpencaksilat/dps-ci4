<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLegacyNamaFileToJadwalTables extends Migration
{
    public function up(): void
    {
        $this->addColumnIfMissing('jadwal_tanding', 'nama_file', 'ALTER TABLE jadwal_tanding ADD COLUMN nama_file VARCHAR(255) DEFAULT NULL AFTER jumlah_partai');
        $this->addColumnIfMissing('jadwal_seni', 'nama_file', 'ALTER TABLE jadwal_seni ADD COLUMN nama_file VARCHAR(255) DEFAULT NULL AFTER jumlah_penampilan');
    }

    public function down(): void
    {
        $this->dropColumnIfExists('jadwal_tanding', 'nama_file');
        $this->dropColumnIfExists('jadwal_seni', 'nama_file');
    }

    private function addColumnIfMissing(string $table, string $column, string $sql): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $fields = $this->db->getFieldNames($table);
        if (! in_array($column, $fields, true)) {
            $this->db->query($sql);
        }
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if (! $this->db->tableExists($table)) {
            return;
        }

        $fields = $this->db->getFieldNames($table);
        if (in_array($column, $fields, true)) {
            $this->db->query(sprintf('ALTER TABLE %s DROP COLUMN %s', $table, $column));
        }
    }
}
