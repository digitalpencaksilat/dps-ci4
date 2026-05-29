<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerangkatPenilaianTandingTables extends Migration
{
    public function up(): void
    {
        // CI3 source: db/db_structure_dps.sql
        // perangkat_pertandingan + penilaian_tanding

        $this->db->query("CREATE TABLE IF NOT EXISTS perangkat_pertandingan (
            id_perangkat_pertandingan INT AUTO_INCREMENT PRIMARY KEY,
            id_gelanggang INT NOT NULL,
            nama VARCHAR(255) NOT NULL,
            username VARCHAR(255) NOT NULL,
            password TEXT NOT NULL,
            posisi ENUM('juri','timer','ketua_pertandingan','operator','sekretaris','layar','broadcast_operator') NOT NULL,
            session_id TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY perangkat_pertandingan_username_uq (username),
            INDEX perangkat_pertandingan_id_gelanggang_idx (id_gelanggang)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS penilaian_tanding (
            id_penilaian_tanding INT AUTO_INCREMENT PRIMARY KEY,
            id_pertandingan INT NOT NULL,
            id_perangkat_pertandingan INT DEFAULT NULL,
            penilaian_merah TEXT NOT NULL,
            penilaian_biru TEXT NOT NULL,
            pemenang VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX penilaian_tanding_id_pertandingan_idx (id_pertandingan),
            INDEX penilaian_tanding_id_perangkat_idx (id_perangkat_pertandingan)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    public function down(): void
    {
        foreach (['penilaian_tanding', 'perangkat_pertandingan'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
