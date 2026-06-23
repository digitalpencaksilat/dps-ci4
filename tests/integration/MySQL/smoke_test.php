<?php
/**
 * DPS CI4 — MySQL Integration Smoke Test
 *
 * Run: php tests/integration/MySQL/smoke_test.php
 *
 * Tests all CI4 models and services against the real db_sudinpora database.
 * Verifies: model queries, data integrity, cross-table joins, service logic.
 */

// Bootstrap CI4 — use 'production' to get MySQL connection
define('ENVIRONMENT', 'production');
define('HOMEPATH', realpath(__DIR__ . '/../../../') . '/');
define('CONFIGPATH', HOMEPATH . 'app/Config/');
define('PUBLICPATH', HOMEPATH . 'public/');

require HOMEPATH . 'vendor/codeigniter4/framework/system/Test/bootstrap.php';

// Ensure we're using the default MySQL connection
$db = \Config\Database::connect('default');

if ($db->getPlatform() !== 'MySQLi') {
    die("❌ ERROR: Not connected to MySQL. Platform: " . $db->getPlatform() . "\n");
}

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║    DPS CI4 — MySQL Integration Smoke Test               ║\n";
echo "║    Database: db_sudinpora                               ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$pass = 0;
$fail = 0;
$errors = [];

function test(string $label, callable $fn): void {
    global $pass, $fail, $errors;
    try {
        $result = $fn();
        if ($result === true || $result === null) {
            echo "  ✅ {$label}\n";
            $pass++;
        } else {
            echo "  ❌ {$label} — returned: " . var_export($result, true) . "\n";
            $fail++;
            $errors[] = $label;
        }
    } catch (\Throwable $e) {
        echo "  ❌ {$label} — EXCEPTION: {$e->getMessage()}\n";
        $fail++;
        $errors[] = "{$label}: {$e->getMessage()}";
    }
}

function testEq(string $label, $expected, $actual): void {
    global $pass, $fail, $errors;
    if ($expected === $actual) {
        echo "  ✅ {$label}\n";
        $pass++;
    } else {
        echo "  ❌ {$label} — expected " . var_export($expected, true) . ", got " . var_export($actual, true) . "\n";
        $fail++;
        $errors[] = $label;
    }
}

// ═══════════════════════════════════════════════════════════════
// 1. DATABASE CONNECTION & TABLE EXISTENCE
// ═══════════════════════════════════════════════════════════════
echo "── 1. Database Connection & Tables ──\n";

$requiredTables = [
    'admin', 'kontingen', 'pendaftar', 'peserta_tanding', 'peserta_seni',
    'kelompok_peserta_seni', 'kelas_tanding', 'kompetisi_tanding', 'kompetisi_seni',
    'kategori_lomba', 'kategori_usia', 'sub_kategori_seni',
    'gelanggang', 'jadwal_tanding', 'jadwal_seni', 'pembayaran',
    'pertandingan', 'detail_jadwal_tanding', 'detail_jadwal_seni',
    'perangkat_pertandingan', 'penilaian_tanding', 'penampilan_seni',
    'perolehan_medali_tanding', 'perolehan_medali_seni',
    'arsip_pendaftar', 'battle_seni',
    'site_builder_settings', 'site_builder_menus',
];

foreach ($requiredTables as $table) {
    test("Table '{$table}' exists", function () use ($db, $table) {
        return $db->tableExists($table);
    });
}

// ═══════════════════════════════════════════════════════════════
// 2. MODEL INSTANTIATION & TABLE MAPPING
// ═══════════════════════════════════════════════════════════════
echo "\n── 2. Model → Table Mapping ──\n";

$models = [
    \App\Models\AdminModel::class => 'admin',
    \App\Models\KontingenModel::class => 'kontingen',
    \App\Models\PendaftarModel::class => 'pendaftar',
    \App\Models\PesertaTandingModel::class => 'peserta_tanding',
    \App\Models\PesertaSeniModel::class => 'peserta_seni',
    \App\Models\KelompokPesertaSeniModel::class => 'kelompok_peserta_seni',
    \App\Models\KelasTandingModel::class => 'kelas_tanding',
    \App\Models\KompetisiTandingModel::class => 'kompetisi_tanding',
    \App\Models\KompetisiSeniModel::class => 'kompetisi_seni',
    \App\Models\KategoriLombaModel::class => 'kategori_lomba',
    \App\Models\KategoriUsiaModel::class => 'kategori_usia',
    \App\Models\SubKategoriSeniModel::class => 'sub_kategori_seni',
    \App\Models\GelanggangModel::class => 'gelanggang',
    \App\Models\JadwalTandingModel::class => 'jadwal_tanding',
    \App\Models\JadwalSeniModel::class => 'jadwal_seni',
    \App\Models\PembayaranModel::class => 'pembayaran',
    \App\Models\PertandinganModel::class => 'pertandingan',
    \App\Models\PerangkatPertandinganModel::class => 'perangkat_pertandingan',
    \App\Models\PenilaianTandingModel::class => 'penilaian_tanding',
    \App\Models\PenampilanSeniModel::class => 'penampilan_seni',
    \App\Models\PerolehanMedaliTandingModel::class => 'perolehan_medali_tanding',
    \App\Models\PerolehanMedaliSeniModel::class => 'perolehan_medali_seni',
    \App\Models\ArsipPendaftarModel::class => 'arsip_pendaftar',
    \App\Models\BattleSeniModel::class => 'battle_seni',
    \App\Models\SiteBuilderSettingModel::class => 'site_builder_settings',
    \App\Models\SiteBuilderMenusModel::class => 'site_builder_menus',
];

foreach ($models as $class => $expectedTable) {
    $model = new $class();
    testEq("{$class} → {$expectedTable}", $expectedTable, $model->table);
}

// ═══════════════════════════════════════════════════════════════
// 3. MODEL QUERIES (real data)
// ═══════════════════════════════════════════════════════════════
echo "\n── 3. Model Queries (real data) ──\n";

// Admin
$adminCount = (new \App\Models\AdminModel())->countAll();
testEq("AdminModel::countAll() > 0", true, $adminCount > 0);
echo "     (admin rows: {$adminCount})\n";

// Kontingen
$kontingenCount = (new \App\Models\KontingenModel())->countAll();
testEq("KontingenModel::countAll() > 0", true, $kontingenCount > 0);
echo "     (kontingen rows: {$kontingenCount})\n";

// Pendaftar
$pendaftarCount = (new \App\Models\PendaftarModel())->countAll();
testEq("PendaftarModel::countAll() > 0", true, $pendaftarCount > 0);
echo "     (pendaftar rows: {$pendaftarCount})\n";

// Peserta Tanding
$ptCount = (new \App\Models\PesertaTandingModel())->countAll();
testEq("PesertaTandingModel::countAll() > 0", true, $ptCount > 0);
echo "     (peserta_tanding rows: {$ptCount})\n";

// Peserta Seni
$psCount = (new \App\Models\PesertaSeniModel())->countAll();
testEq("PesertaSeniModel::countAll() >= 0", true, $psCount >= 0);
echo "     (peserta_seni rows: {$psCount})\n";

// Jadwal
$jtCount = (new \App\Models\JadwalTandingModel())->countAll();
$jsCount = (new \App\Models\JadwalSeniModel())->countAll();
echo "     (jadwal_tanding: {$jtCount}, jadwal_seni: {$jsCount})\n";

// ═══════════════════════════════════════════════════════════════
// 4. COMPLEX QUERIES (JOIN + SUBQUERY)
// ═══════════════════════════════════════════════════════════════
echo "\n── 4. Complex Queries ──\n";

// 4a. KontingenModel::baseSekretariatQuery() — uses correlated subqueries
test("KontingenModel::baseSekretariatQuery() runs", function () use ($db) {
    $result = (new \App\Models\KontingenModel())->baseSekretariatQuery()->get()->getResult();
    return count($result) > 0;
});

// 4b. KontingenModel::findWithSummary()
test("KontingenModel::findWithSummary(id) with real ID", function () {
    $firstKontingen = (new \App\Models\KontingenModel())->first();
    if (!$firstKontingen) return false;
    $result = (new \App\Models\KontingenModel())->findWithSummary($firstKontingen->id_kontingen);
    return $result !== null && isset($result->jumlah_pendaftar);
});

// 4c. PesertaTandingModel::baseSekretariatQuery() — complex joins
test("PesertaTandingModel::baseSekretariatQuery() runs", function () {
    $result = (new \App\Models\PesertaTandingModel())->baseSekretariatQuery()->get()->getResult();
    return is_array($result);
});

// 4d. PesertaTandingModel::findDetailed() with real ID
test("PesertaTandingModel::findDetailed(id) works", function () {
    $first = (new \App\Models\PesertaTandingModel())->first();
    if (!$first) return 'no data to test';
    $result = (new \App\Models\PesertaTandingModel())->findDetailed($first->id_peserta_tanding);
    return $result !== null && isset($result->nama_pendaftar);
});

// 4e. PesertaSeniModel::baseSekretariatQuery()
test("PesertaSeniModel::baseSekretariatQuery() runs", function () {
    $result = (new \App\Models\PesertaSeniModel())->baseSekretariatQuery()->get()->getResult();
    return is_array($result);
});

// 4f. PendaftarModel::baseSekretariatQuery() with age calculation
test("PendaftarModel::baseSekretariatQuery() runs (TIMESTAMPDIFF)", function () {
    $result = (new \App\Models\PendaftarModel())->baseSekretariatQuery()->get()->getResult();
    return is_array($result);
});

// 4g. JadwalTandingModel::get_all() with subqueries
test("JadwalTandingModel::get_all() runs", function () {
    $result = (new \App\Models\JadwalTandingModel())->get_all();
    return is_array($result);
});

// 4h. JadwalTandingModel::findWithGelanggang()
$firstJt = (new \App\Models\JadwalTandingModel())->first();
if ($firstJt) {
    test("JadwalTandingModel::findWithGelanggang(id) works", function () use ($firstJt) {
        $result = (new \App\Models\JadwalTandingModel())->findWithGelanggang($firstJt->id_jadwal_tanding);
        return $result !== null && isset($result->nama_gelanggang);
    });

    test("JadwalTandingModel::get_detail_jadwal(id) works", function () use ($firstJt) {
        $result = (new \App\Models\JadwalTandingModel())->get_detail_jadwal($firstJt->id_jadwal_tanding);
        return is_array($result);
    });
}

// 4i. JadwalSeniModel
$firstJs = (new \App\Models\JadwalSeniModel())->first();
test("JadwalSeniModel::get_all() runs", function () {
    $result = (new \App\Models\JadwalSeniModel())->get_all();
    return is_array($result);
});

if ($firstJs) {
    test("JadwalSeniModel::get_detail_jadwal(id) works", function () use ($firstJs) {
        $result = (new \App\Models\JadwalSeniModel())->get_detail_jadwal($firstJs->id_jadwal_seni);
        return is_array($result);
    });
}

// ═══════════════════════════════════════════════════════════════
// 5. SERVICE LOGIC (against real data)
// ═══════════════════════════════════════════════════════════════
echo "\n── 5. Service Logic ──\n";

// 5a. KategoriTandingService::listByKontingen() — MySQL GROUP_CONCAT/SEPARATOR
$firstKontingen = (new \App\Models\KontingenModel())->first();
if ($firstKontingen) {
    test("KategoriTandingService::listByKontingen() runs on MySQL", function () use ($firstKontingen) {
        $result = (new \App\Services\KategoriTandingService())->listByKontingen($firstKontingen->id_kontingen);
        return is_array($result);
    });

    test("KategoriTandingService::availablePendaftar() runs", function () use ($firstKontingen) {
        $result = (new \App\Services\KategoriTandingService())->availablePendaftar($firstKontingen->id_kontingen);
        return is_array($result);
    });
}

// 5b. KategoriSeniService::listByKontingen()
// Note: session must be initialized BEFORE output (CI4 limitation)
if ($firstKontingen) {
    // Pre-init session before any output in CI4
    try {
        @session_start();
        session()->set('id_kontingen', $firstKontingen->id_kontingen);
    } catch (\Throwable) {}

    test("KategoriSeniService::listByKontingen() runs on MySQL", function () use ($firstKontingen) {
        // Suppress session warning
        $result = @(new \App\Services\KategoriSeniService())->listByKontingen($firstKontingen->id_kontingen);
        return is_array($result);
    });
}

// 5c. PembayaranAdminService
test("PembayaranAdminService can be instantiated", function () {
    $svc = new \App\Services\PembayaranAdminService();
    return $svc instanceof \App\Services\PembayaranAdminService;
});

// 5d. SekretariatStatistikService
// 5d. SekretariatStatistikService
test("SekretariatStatistikService::getTandingStats() runs", function () {
    try {
        $result = (new \App\Services\SekretariatStatistikService())->getTandingStats();
        return is_array($result);
    } catch (\Throwable $e) {
        return 'skipped: ' . $e->getMessage();
    }
});

// 5e. MedalTallyService
test("MedalTallyService::getAkumulasiMedali() runs", function () {
    try {
        $result = (new \App\Services\MedalTallyService())->getAkumulasiMedali();
        return is_array($result);
    } catch (\Throwable $e) {
        return 'skipped: ' . $e->getMessage();
    }
});

// 5f. PenilaianTandingService
test("PenilaianTandingService instantiated", function () {
    $svc = new \App\Services\PenilaianTandingService();
    return $svc instanceof \App\Services\PenilaianTandingService;
});

// ═══════════════════════════════════════════════════════════════
// 6. DATA INTEGRITY
// ═══════════════════════════════════════════════════════════════
echo "\n── 6. Data Integrity Checks ──\n";

// 6a. All peserta_tanding have valid pendaftar FK
test("peserta_tanding → pendaftar FK integrity", function () use ($db) {
    $orphans = $db->query(
        "SELECT COUNT(*) AS cnt FROM peserta_tanding pt LEFT JOIN pendaftar p ON p.id_pendaftar = pt.id_pendaftar WHERE p.id_pendaftar IS NULL"
    )->getRow()->cnt;
    return (int)$orphans === 0 ? true : "found {$orphans} orphaned peserta_tanding";
});

// 6b. All kelompok_peserta_seni have valid kontingen FK
test("kelompok_peserta_seni → kontingen FK integrity", function () use ($db) {
    $orphans = $db->query(
        "SELECT COUNT(*) AS cnt FROM kelompok_peserta_seni kps LEFT JOIN kontingen k ON k.id_kontingen = kps.id_kontingen WHERE k.id_kontingen IS NULL"
    )->getRow()->cnt;
    return (int)$orphans === 0 ? true : "found {$orphans} orphaned kelompok";
});

// 6c. No duplicate peserta_tanding per pendaftar per kompetisi
test("No duplicate peserta_tanding (same pendaftar + kompetisi)", function () use ($db) {
    $dupes = $db->query(
        "SELECT COUNT(*) AS cnt FROM (SELECT id_pendaftar, id_kompetisi_tanding, COUNT(*) c FROM peserta_tanding GROUP BY id_pendaftar, id_kompetisi_tanding HAVING c > 1) sub"
    )->getRow()->cnt;
    return (int)$dupes === 0 ? true : "found {$dupes} duplicate entries (may be intentional — same athlete in different pools)";
});

// 6f. Pembayaran status values check
test("Pembayaran status values consistency", function () use ($db) {
    $stats = $db->query("SELECT status_pembayaran, COUNT(*) cnt FROM pembayaran GROUP BY status_pembayaran")->getResult();
    echo "     (status distribution: ";
    foreach ($stats as $s) echo "{$s->status_pembayaran}={$s->cnt} ";
    echo ")\n";
    return count($stats) > 0;
});

// 6g. Admin level values check
test("Admin level values consistency", function () use ($db) {
    $levels = $db->query("SELECT level, COUNT(*) cnt FROM admin GROUP BY level")->getResult();
    echo "     (admin levels: ";
    foreach ($levels as $l) echo "{$l->level}={$l->cnt} ";
    echo ")\n";
    return count($levels) > 0;
});

// ═══════════════════════════════════════════════════════════════
// 7. CI4-SPECIFIC FEATURES
// ═══════════════════════════════════════════════════════════════
echo "\n── 7. CI4-Specific Features ──\n";

// 7a. JadwalTandingModel::resequenceNomorPartai() (ROW_NUMBER window function)
test("JadwalTandingModel::resequenceNomorPartai() syntax valid", function () use ($firstJt) {
    if (!$firstJt) return 'no jadwal_tanding data';
    try {
        (new \App\Models\JadwalTandingModel())->resequenceNomorPartai($firstJt->id_jadwal_tanding, 1);
        return true;
    } catch (\Throwable $e) {
        // ROW_NUMBER requires MySQL 8+. Check if error is about syntax or missing data
        if (str_contains($e->getMessage(), 'ROW_NUMBER') || str_contains($e->getMessage(), 'syntax')) {
            return 'ROW_NUMBER not supported (MySQL < 8?)';
        }
        return 'data issue, not syntax: ' . $e->getMessage();
    }
});

// 7b. JadwalSeniModel::resequenceNomorPartai()
test("JadwalSeniModel::resequenceNomorPartai() syntax valid", function () use ($firstJs) {
    if (!$firstJs) return 'no jadwal_seni data';
    try {
        (new \App\Models\JadwalSeniModel())->resequenceNomorPartai($firstJs->id_jadwal_seni, 1);
        return true;
    } catch (\Throwable $e) {
        if (str_contains($e->getMessage(), 'ROW_NUMBER') || str_contains($e->getMessage(), 'syntax')) {
            return 'ROW_NUMBER not supported (MySQL < 8?)';
        }
        return 'data issue: ' . $e->getMessage();
    }
});

// 7c. JadwalTandingModel::getPertandinganPola() with CASE WHEN
test("JadwalTandingModel::getPertandinganPola() runs", function () use ($firstJt) {
    if (!$firstJt) return 'no jadwal data';
    try {
        $result = (new \App\Models\JadwalTandingModel())->getPertandinganPola(
            $firstJt->id_jadwal_tanding,
            'ku.min_umur ASC, kl.berat_maksimal ASC, nilai_babak ASC'
        );
        return is_array($result);
    } catch (\Throwable $e) {
        return 'no detail data or error: ' . $e->getMessage();
    }
});

// ═══════════════════════════════════════════════════════════════
// FINAL REPORT
// ═══════════════════════════════════════════════════════════════
echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║    RESULTS                                              ║\n";
echo "╠══════════════════════════════════════════════════════════╣\n";
$total = $pass + $fail;
printf("║    ✅ PASSED: %-3d                                      ║\n", $pass);
printf("║    ❌ FAILED: %-3d                                      ║\n", $fail);
printf("║    📊 TOTAL:  %-3d                                      ║\n", $total);
echo "║                                                          ║\n";
if ($fail === 0) {
    echo "║    🎉 ALL TESTS PASSED — Production Ready!               ║\n";
} else {
    echo "║    ⚠️  Some tests failed — see details above             ║\n";
}
echo "╚══════════════════════════════════════════════════════════╝\n\n";

if ($errors !== []) {
    echo "Failed tests:\n";
    foreach ($errors as $i => $e) {
        echo "  " . ($i + 1) . ". {$e}\n";
    }
}

exit($fail > 0 ? 1 : 0);
