<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSekretariatResourceTables extends Migration
{
    public function up(): void
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS kontingen (
            id_kontingen INT AUTO_INCREMENT PRIMARY KEY,
            id_pembayaran INT DEFAULT NULL,
            nama_kontingen VARCHAR(255) NOT NULL,
            singkatan_nama_kontingen VARCHAR(50) DEFAULT NULL,
            jenis_kontingen ENUM('dalam_negeri', 'luar_negeri') NOT NULL,
            perguruan VARCHAR(100) DEFAULT 'ipsi',
            email_kontingen VARCHAR(255) NOT NULL UNIQUE,
            nomor_telepon_kontingen VARCHAR(20) DEFAULT NULL,
            alamat_kontingen TEXT DEFAULT NULL,
            username VARCHAR(255) DEFAULT NULL,
            password VARCHAR(255) NOT NULL,
            nama_penanggungjawab VARCHAR(255) DEFAULT NULL,
            jabatan_penanggungjawab VARCHAR(255) DEFAULT NULL,
            nomor_telepon_penanggungjawab VARCHAR(20) DEFAULT NULL,
            alamat_penanggungjawab TEXT DEFAULT NULL,
            negara VARCHAR(100) DEFAULT NULL,
            provinsi VARCHAR(150) DEFAULT NULL,
            kabupaten_kota VARCHAR(150) DEFAULT NULL,
            kecamatan VARCHAR(150) DEFAULT NULL,
            kelurahan VARCHAR(150) DEFAULT NULL,
            alamat_lengkap TEXT DEFAULT NULL,
            keterangan TEXT DEFAULT NULL,
            pembayaran_dn DECIMAL(10,2) DEFAULT 0,
            pembayaran_ln DECIMAL(10,2) DEFAULT 0,
            status_data VARCHAR(50) DEFAULT NULL,
            jenis_pendaftaran ENUM('web', 'excel', 'manual') DEFAULT 'web',
            tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS pembayaran (
            id_pembayaran INT AUTO_INCREMENT PRIMARY KEY,
            id_kontingen INT NOT NULL,
            total_pembayaran DECIMAL(12,2) NOT NULL DEFAULT 0,
            status_pembayaran ENUM('menunggu', 'ditolak', 'lunas') DEFAULT 'menunggu',
            tanggal_pembayaran TIMESTAMP NULL DEFAULT NULL,
            bukti_pembayaran VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX pembayaran_id_kontingen_idx (id_kontingen)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS pendaftar (
            id_pendaftar INT AUTO_INCREMENT PRIMARY KEY,
            id_kontingen INT NOT NULL,
            nama_pendaftar VARCHAR(255) NOT NULL,
            jenis_kelamin ENUM('putra', 'putri') NOT NULL,
            tinggi_badan DECIMAL(5,2) NOT NULL,
            berat_badan DECIMAL(5,2) NOT NULL,
            tempat_lahir VARCHAR(255) NOT NULL,
            tanggal_lahir DATE NOT NULL,
            nama_sekolah VARCHAR(255) DEFAULT NULL,
            alamat TEXT DEFAULT NULL,
            foto VARCHAR(255) DEFAULT NULL,
            status_data VARCHAR(50) DEFAULT NULL,
            keterangan TEXT DEFAULT NULL,
            tanggal_daftar TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            nomor_induk_kependudukan VARCHAR(100) DEFAULT NULL,
            nomor_kartu_keluarga VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX pendaftar_id_kontingen_idx (id_kontingen)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS peserta_tanding (
            id_peserta_tanding INT AUTO_INCREMENT PRIMARY KEY,
            id_pendaftar INT NOT NULL,
            id_kompetisi_tanding INT NOT NULL,
            id_pembayaran INT DEFAULT NULL,
            nomor_bagan INT DEFAULT NULL,
            keterangan TEXT DEFAULT NULL,
            status VARCHAR(50) DEFAULT NULL,
            status_sertifikat VARCHAR(50) DEFAULT NULL,
            nomor_sertifikat VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX peserta_tanding_id_pendaftar_idx (id_pendaftar),
            INDEX peserta_tanding_id_kompetisi_idx (id_kompetisi_tanding)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS kelompok_peserta_seni (
            id_kelompok_peserta_seni INT AUTO_INCREMENT PRIMARY KEY,
            id_kontingen INT NOT NULL,
            id_kompetisi_seni INT NOT NULL,
            id_pembayaran INT DEFAULT NULL,
            status VARCHAR(50) DEFAULT NULL,
            keterangan TEXT DEFAULT NULL,
            nomor_undi INT DEFAULT NULL,
            nomor_sertifikat VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX kelompok_seni_id_kontingen_idx (id_kontingen),
            INDEX kelompok_seni_id_kompetisi_idx (id_kompetisi_seni)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS peserta_seni (
            id_peserta_seni INT AUTO_INCREMENT PRIMARY KEY,
            id_pendaftar INT NOT NULL,
            id_kelompok_peserta_seni INT NOT NULL,
            status_sertifikat VARCHAR(50) DEFAULT NULL,
            nomor_sertifikat VARCHAR(50) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX peserta_seni_id_pendaftar_idx (id_pendaftar),
            INDEX peserta_seni_id_kelompok_idx (id_kelompok_peserta_seni)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS perolehan_medali_tanding (
            id_perolehan_medali_tanding INT AUTO_INCREMENT PRIMARY KEY,
            id_peserta_tanding INT NOT NULL,
            jenis_medali ENUM('emas', 'perak', 'perunggu') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX medali_tanding_id_peserta_idx (id_peserta_tanding)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        $this->db->query("CREATE TABLE IF NOT EXISTS perolehan_medali_seni (
            id_perolehan_medali_seni INT AUTO_INCREMENT PRIMARY KEY,
            id_kelompok_peserta_seni INT NOT NULL,
            jenis_medali ENUM('emas', 'perak', 'perunggu') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX medali_seni_id_kelompok_idx (id_kelompok_peserta_seni)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");
    }

    public function down(): void
    {
        foreach (['perolehan_medali_seni', 'perolehan_medali_tanding', 'peserta_seni', 'kelompok_peserta_seni', 'peserta_tanding', 'pendaftar', 'pembayaran', 'kontingen'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
