<?php

namespace Tests\Integration;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Integration test untuk memverifikasi kompatibilitas database
 * antara CI3 (legacy) dan CI4.
 */
class DatabaseParityTest extends CIUnitTestCase
{
    private const LEGACY_TABLES = [
        ['admin', 'id_admin', \App\Models\AdminModel::class],
        ['kontingen', 'id_kontingen', \App\Models\KontingenModel::class],
        ['pendaftar', 'id_pendaftar', \App\Models\PendaftarModel::class],
        ['peserta_tanding', 'id_peserta_tanding', \App\Models\PesertaTandingModel::class],
        ['peserta_seni', 'id_peserta_seni', \App\Models\PesertaSeniModel::class],
        ['kelompok_peserta_seni', 'id_kelompok_peserta_seni', \App\Models\KelompokPesertaSeniModel::class],
        ['kelas_tanding', 'id_kelas_tanding', \App\Models\KelasTandingModel::class],
        ['kompetisi_tanding', 'id_kompetisi_tanding', \App\Models\KompetisiTandingModel::class],
        ['kompetisi_seni', 'id_kompetisi_seni', \App\Models\KompetisiSeniModel::class],
        ['kategori_lomba', 'id_kategori_lomba', \App\Models\KategoriLombaModel::class],
        ['kategori_usia', 'id_kategori_usia', \App\Models\KategoriUsiaModel::class],
        ['sub_kategori_seni', 'id_sub_kategori_seni', \App\Models\SubKategoriSeniModel::class],
        ['gelanggang', 'id_gelanggang', \App\Models\GelanggangModel::class],
        ['jadwal_tanding', 'id_jadwal_tanding', \App\Models\JadwalTandingModel::class],
        ['jadwal_seni', 'id_jadwal_seni', \App\Models\JadwalSeniModel::class],
        ['pembayaran', 'id_pembayaran', \App\Models\PembayaranModel::class],
        ['pertandingan', 'id_pertandingan', \App\Models\PertandinganModel::class],
        ['perangkat_pertandingan', 'id_perangkat_pertandingan', \App\Models\PerangkatPertandinganModel::class],
        ['penilaian_tanding', 'id_penilaian_tanding', \App\Models\PenilaianTandingModel::class],
        ['penampilan_seni', 'id_penampilan_seni', \App\Models\PenampilanSeniModel::class],
        ['perolehan_medali_tanding', 'id_perolehan_medali_tanding', \App\Models\PerolehanMedaliTandingModel::class],
        ['perolehan_medali_seni', 'id_perolehan_medali_seni', \App\Models\PerolehanMedaliSeniModel::class],
        ['arsip_pendaftar', 'id_arsip_pendaftar', \App\Models\ArsipPendaftarModel::class],
        ['battle_seni', 'id_battle_seni', \App\Models\BattleSeniModel::class],
        ['site_builder_settings', 'setting', \App\Models\SiteBuilderSettingModel::class],
        ['site_builder_menus', 'id', \App\Models\SiteBuilderMenusModel::class],
    ];

    public function testAllModelsAreInstantiable(): void
    {
        $errors = [];
        foreach (self::LEGACY_TABLES as [$table, $pk, $class]) {
            try {
                $model = new $class();
                $this->assertInstanceOf(\CodeIgniter\Model::class, $model);
            } catch (\Throwable $e) {
                $errors[] = "{$class}: {$e->getMessage()}";
            }
        }
        $this->assertEmpty($errors, "Model instantiation errors:\n" . implode("\n", $errors));
    }

    public function testAllModelsTargetCorrectTable(): void
    {
        foreach (self::LEGACY_TABLES as [$expectedTable, $expectedPk, $class]) {
            $model = new $class();
            $this->assertSame($expectedTable, $model->table, "{$class} target table mismatch");
        }
    }

    public function testAllModelsHaveCorrectPrimaryKey(): void
    {
        foreach (self::LEGACY_TABLES as [$table, $expectedPk, $class]) {
            $model = new $class();
            $this->assertSame($expectedPk, $model->primaryKey, "{$class} PK mismatch");
        }
    }

    public function testModelsDoNotUseSoftDeletes(): void
    {
        foreach (self::LEGACY_TABLES as [$table, $pk, $class]) {
            $model = new $class();
            $this->assertFalse($model->useSoftDeletes ?? false, "{$class} should not use soft deletes");
        }
    }

    public function testLegacyTablesHaveTimestampsDisabled(): void
    {
        $noTimestamps = [
            \App\Models\JadwalTandingModel::class,
            \App\Models\JadwalSeniModel::class,
            \App\Models\GelanggangModel::class,
        ];
        foreach ($noTimestamps as $class) {
            $model = new $class();
            $this->assertFalse(
                $model->useTimestamps ?? $model->createdField ?? false,
                "{$class} must have useTimestamps=false for legacy CI3 table"
            );
        }
    }
}
