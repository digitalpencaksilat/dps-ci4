<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJadwalTables extends Migration
{
    public function up(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS gelanggang (
            id_gelanggang INT AUTO_INCREMENT PRIMARY KEY,
            nama_gelanggang VARCHAR(100) NOT NULL,
            nomor_gelanggang VARCHAR(10) DEFAULT NULL,
            keterangan TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS jadwal_tanding (
            id_jadwal_tanding INT AUTO_INCREMENT PRIMARY KEY,
            id_gelanggang INT NOT NULL,
            tanggal DATE DEFAULT NULL,
            jam_mulai TIME DEFAULT '08:00:00',
            jam_selesai TIME DEFAULT '22:00:00',
            keterangan TEXT DEFAULT NULL,
            nomor_partai_awal INT DEFAULT NULL,
            nomor_partai_akhir INT DEFAULT NULL,
            jumlah_partai INT DEFAULT 0,
            nama_file VARCHAR(255) DEFAULT NULL,
            pdf_path VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX jadwal_tanding_id_gelanggang_idx (id_gelanggang)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS detail_jadwal_tanding (
            id_detail_jadwal_tanding INT AUTO_INCREMENT PRIMARY KEY,
            id_jadwal_tanding INT NOT NULL,
            id_pertandingan INT NOT NULL,
            nomor_partai INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX detail_jadwal_tanding_id_jadwal_idx (id_jadwal_tanding),
            INDEX detail_jadwal_tanding_id_pertandingan_idx (id_pertandingan)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS jadwal_seni (
            id_jadwal_seni INT AUTO_INCREMENT PRIMARY KEY,
            id_gelanggang INT NOT NULL,
            tanggal DATE DEFAULT NULL,
            jam_mulai TIME DEFAULT '08:00:00',
            jam_selesai TIME DEFAULT '22:00:00',
            keterangan TEXT DEFAULT NULL,
            nomor_partai_awal INT DEFAULT NULL,
            nomor_partai_akhir INT DEFAULT NULL,
            jumlah_penampilan INT DEFAULT 0,
            nama_file VARCHAR(255) DEFAULT NULL,
            pdf_path VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX jadwal_seni_id_gelanggang_idx (id_gelanggang)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS detail_jadwal_seni (
            id_detail_jadwal_seni INT AUTO_INCREMENT PRIMARY KEY,
            id_jadwal_seni INT NOT NULL,
            id_battle_seni INT DEFAULT NULL,
            id_penampilan_seni INT DEFAULT NULL,
            nomor_partai INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX detail_jadwal_seni_id_jadwal_idx (id_jadwal_seni),
            INDEX detail_jadwal_seni_id_battle_idx (id_battle_seni),
            INDEX detail_jadwal_seni_id_penampilan_idx (id_penampilan_seni)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    public function down(): void
    {
        foreach ([
            'detail_jadwal_seni',
            'jadwal_seni',
            'detail_jadwal_tanding',
            'jadwal_tanding',
            'gelanggang',
        ] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
