<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Router\RouteCollection;
use Config\Services;

/**
 * @var RouteCollection $routes
 */
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\\Controllers');
$routes->setDefaultController('PendaftaranController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Public landing
$routes->get('/', 'PendaftaranController::index');
$routes->get('pendaftaran', 'PendaftaranController::index');
$routes->get('maintenance', static fn() => service('response')->setStatusCode(503)->setHeader('Retry-After', '3600')->setBody(view('shared_sections/dps_error_panel', [
    'code' => '503',
    'title' => 'Sedang Maintenance',
    'message' => 'Sistem sedang dalam pemeliharaan. Silakan coba kembali dalam beberapa saat.',
    'actionUrl' => base_url('maintenance'),
    'actionLabel' => 'Muat Ulang',
    'showHome' => true,
])));

// Public pages kept for phase awal
$routes->get('registrasi', 'PendaftaranController::registrasi');
$routes->post('registrasi', 'PendaftaranController::submitRegistrasi');
$routes->get('pendaftaran/download-juknis', 'PendaftaranController::downloadJuknis');
$routes->get('download-form-excel', 'PendaftaranController::downloadFormExcel');

// Location helpers
$routes->get('location/countries', 'LocationController::countries');
$routes->get('location/provinces', 'LocationController::provinces');
$routes->get('location/regencies/(:segment)', 'LocationController::regencies/$1');
$routes->get('location/districts/(:segment)', 'LocationController::districts/$1');
$routes->get('location/villages/(:segment)', 'LocationController::villages/$1');

// Kontingen auth
$routes->get('admin', 'AdminAuthController::login');
$routes->post('admin', 'AdminAuthController::attemptLogin');
$routes->get('admin/login', static fn() => redirect()->to(base_url('admin')));
$routes->post('admin/login', static fn() => redirect()->to(base_url('admin')));
$routes->get('admin/logout', 'AdminAuthController::logout');

// Kontingen auth
$routes->get('pendaftaran/login', 'KontingenAuthController::login');
$routes->post('kontingen/login', 'KontingenAuthController::attemptLogin');
$routes->get('kontingen/logout', 'KontingenAuthController::logout');

// Kontingen dashboard
$routes->get('kontingen', 'DashboardController::index', ['filter' => 'kontingenauth']);
$routes->get('kontingen/dashboard', 'DashboardController::index', ['filter' => 'kontingenauth']);
$routes->get('kontingen/peserta', 'PesertaController::index', ['filter' => 'kontingenauth']);
$routes->get('kontingen/tanding', 'KategoriTandingController::index', ['filter' => 'kontingenauth']);
$routes->get('kontingen/tanding/options/(:num)', 'KategoriTandingController::options/$1', ['filter' => 'kontingenauth']);
$routes->get('kontingen/seni', 'KategoriSeniController::index', ['filter' => 'kontingenauth']);
$routes->get('kontingen/seni/options/(:num)', 'KategoriSeniController::options/$1', ['filter' => 'kontingenauth']);
$routes->get('kontingen/pembayaran', 'PembayaranKontingenController::index', ['filter' => 'kontingenauth']);
$routes->get('kontingen/pembayaran/menunggu-konfirmasi', 'PembayaranKontingenController::waiting', ['filter' => 'kontingenauth']);
$routes->get('kontingen/pembayaran/lunas', 'PembayaranKontingenController::paid', ['filter' => 'kontingenauth']);
$routes->get('kontingen/pembayaran/(:num)', 'PembayaranKontingenController::show/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta', 'PesertaController::store', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta/(:num)/update', 'PesertaController::update/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta/(:num)/delete', 'PesertaController::delete/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/tanding', 'KategoriTandingController::store', ['filter' => 'kontingenauth']);
$routes->post('kontingen/tanding/(:num)/update', 'KategoriTandingController::update/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/tanding/(:num)/delete', 'KategoriTandingController::delete/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/seni', 'KategoriSeniController::store', ['filter' => 'kontingenauth']);
$routes->post('kontingen/seni/(:num)/update', 'KategoriSeniController::update/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/seni/(:num)/delete', 'KategoriSeniController::delete/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/pembayaran', 'PembayaranKontingenController::store', ['filter' => 'kontingenauth']);

$routes->group('admin/bendahara', ['filter' => 'adminrole:bendahara'], static function ($routes): void {
    $routes->get('/', 'Admin\\Bendahara\\DashboardController::index');
    $routes->get('dashboard', 'Admin\\Bendahara\\DashboardController::index');
    $routes->get('pembayaran', 'Admin\\Bendahara\\PembayaranController::index');
    $routes->get('pembayaran/menunggu-konfirmasi', 'Admin\\Bendahara\\PembayaranController::waiting');
    $routes->get('pembayaran/lunas', 'Admin\\Bendahara\\PembayaranController::paid');
    $routes->get('pembayaran/belum-dibayar', 'Admin\\Bendahara\\PembayaranController::unpaid');
    $routes->get('pembayaran/tanding', 'Admin\\Bendahara\\PembayaranController::tanding');
    $routes->get('pembayaran/seni', 'Admin\\Bendahara\\PembayaranController::seni');
    $routes->get('pembayaran/(:num)', 'Admin\\Bendahara\\PembayaranController::show/$1');
    $routes->post('pembayaran/(:num)/konfirmasi', 'Admin\\Bendahara\\PembayaranController::confirm/$1');
    $routes->post('pembayaran/(:num)/tolak', 'Admin\\Bendahara\\PembayaranController::reject/$1');
    $routes->get('pembayaran/(:num)/nota', 'Admin\\Bendahara\\PembayaranController::nota/$1');
    $routes->get('pembayaran/(:num)/nota.pdf', 'Admin\\Bendahara\\PembayaranController::notaPdf/$1');
    $routes->get('kontingen', 'Admin\\Bendahara\\KontingenController::index');
    $routes->get('kontingen/(:num)', 'Admin\\Bendahara\\KontingenController::show/$1');
    $routes->post('kontingen/(:num)/buat-transaksi', 'Admin\\Bendahara\\PembayaranController::createForKontingen/$1');
});

$routes->group('admin/sekretariat', ['filter' => 'adminrole:sekretariat'], static function ($routes): void {
    $routes->get('/', 'Admin\\Sekretariat\\DashboardController::index');
    $routes->get('dashboard', 'Admin\\Sekretariat\\DashboardController::index');
    $routes->get('kontingen', 'Admin\\Sekretariat\\KontingenController::index');
    $routes->get('kontingen/(:num)', 'Admin\\Sekretariat\\KontingenController::show/$1');
    $routes->post('kontingen', 'Admin\\Sekretariat\\KontingenController::store');
    $routes->post('kontingen/(:num)/update', 'Admin\\Sekretariat\\KontingenController::update/$1');
    $routes->post('kontingen/(:num)/reset-password', 'Admin\\Sekretariat\\KontingenController::resetPassword/$1');
    $routes->post('kontingen/(:num)/delete', 'Admin\\Sekretariat\\KontingenController::delete/$1');
    $routes->post('kontingen/(:num)/pendaftar', 'Admin\\Sekretariat\\KontingenController::storePendaftar/$1');
    $routes->post('kontingen/(:num)/pendaftar/(:num)/update', 'Admin\\Sekretariat\\KontingenController::updatePendaftar/$1/$2');
    $routes->post('kontingen/(:num)/pendaftar/(:num)/delete', 'Admin\\Sekretariat\\KontingenController::deletePendaftar/$1/$2');
    $routes->post('kontingen/(:num)/peserta-tanding', 'Admin\\Sekretariat\\KontingenController::storePesertaTanding/$1');
    $routes->post('kontingen/(:num)/kelompok-seni', 'Admin\\Sekretariat\\KontingenController::storeKelompokSeni/$1');
    $routes->get('data-atlet', 'Admin\\Sekretariat\\PendaftarController::index');
    $routes->get('data-bpjs', 'Admin\\Sekretariat\\DataBpjsController::index');
    $routes->get('peserta-tanding', 'Admin\\Sekretariat\\PesertaTandingController::index');
    $routes->get('kompetisi-tanding/by-pendaftar/(:num)', 'Admin\\Sekretariat\\PesertaTandingController::byPendaftar/$1');
    $routes->get('peserta-tanding/(:num)', 'Admin\\Sekretariat\\PesertaTandingController::show/$1');
    $routes->post('peserta-tanding', 'Admin\\Sekretariat\\PesertaTandingController::store');
    $routes->post('peserta-tanding/(:num)/update', 'Admin\\Sekretariat\\PesertaTandingController::update/$1');
    $routes->get('pengadaan-medali', 'Admin\\Sekretariat\\PengadaanMedaliController::index');
    $routes->post('peserta-tanding/(:num)/delete', 'Admin\\Sekretariat\\PesertaTandingController::delete/$1');
    $routes->get('kelompok-seni', 'Admin\\Sekretariat\\KelompokPesertaSeniController::index');
    $routes->get('pendaftar/by-kompetisi-seni/(:num)/(:num)', 'Admin\\Sekretariat\\KelompokPesertaSeniController::pendaftarByKompetisi/$1/$2');
    $routes->get('kelompok-seni/(:num)', 'Admin\\Sekretariat\\KelompokPesertaSeniController::show/$1');
    $routes->post('kelompok-seni', 'Admin\\Sekretariat\\KelompokPesertaSeniController::store');
    $routes->post('kelompok-seni/(:num)/update', 'Admin\\Sekretariat\\KelompokPesertaSeniController::update/$1');
    $routes->post('kelompok-seni/(:num)/delete', 'Admin\\Sekretariat\\KelompokPesertaSeniController::delete/$1');
    $routes->post('kelompok-seni/(:num)/anggota', 'Admin\\Sekretariat\\KelompokPesertaSeniController::addMember/$1');
    $routes->post('kelompok-seni/(:num)/anggota/(:num)/delete', 'Admin\\Sekretariat\\KelompokPesertaSeniController::deleteMember/$1/$2');
    $routes->get('kelas-tanding', 'Admin\\Sekretariat\\KelasTandingController::index');
    $routes->get('kelas-tanding/(:num)', 'Admin\\Sekretariat\\KelasTandingController::show/$1');
    $routes->get('pool-tanding', 'Admin\\Sekretariat\\PoolTandingController::index');
    $routes->get('pool-tanding/(:num)', 'Admin\\Sekretariat\\PoolTandingController::show/$1');
    $routes->get('pool-tanding/(:num)/bagan.pdf', 'Admin\\Sekretariat\\PoolTandingController::printBagan/$1');
    $routes->post('pool-tanding/(:num)/update', 'Admin\\Sekretariat\\PoolTandingController::update/$1');
    $routes->post('pool-tanding/(:num)/acak-bagan', 'Admin\\Sekretariat\\PoolTandingController::acakBagan/$1');
    $routes->get('pertandingan-tanding', 'Admin\\Sekretariat\\PertandinganTandingController::index');
    $routes->get('pesilat-terbaik/pertandingan-tanding', 'Admin\\Sekretariat\\PertandinganTandingController::urutanPoin', ['filter' => 'adminrole:sekretariat,super_admin']);
    $routes->get('pertandingan-tanding/(:num)', 'Admin\\Sekretariat\\PertandinganTandingController::show/$1');
    $routes->post('pertandingan-tanding', 'Admin\\Sekretariat\\PertandinganTandingController::store');
    $routes->post('pertandingan-tanding/(:num)/update', 'Admin\\Sekretariat\\PertandinganTandingController::update/$1');
    $routes->post('pertandingan-tanding/(:num)/delete', 'Admin\\Sekretariat\\PertandinganTandingController::delete/$1');
    $routes->get('kuota-prestasi-tanding', 'Admin\\Sekretariat\\KuotaPrestasiTandingController::index');
    $routes->get('kategori-seni', 'Admin\\Sekretariat\\KategoriSeniAdminController::index');
    $routes->get('kategori-seni/(:num)', 'Admin\\Sekretariat\\KategoriSeniAdminController::show/$1');
    $routes->get('pool-seni', 'Admin\\Sekretariat\\PoolSeniController::index');
    $routes->get('pesilat-terbaik/pool-seni', 'Admin\\Sekretariat\\PoolSeniController::urutanPoin', ['filter' => 'adminrole:sekretariat,super_admin']);
    $routes->get('pool-seni/(:num)', 'Admin\\Sekretariat\\PoolSeniController::show/$1');
    $routes->get('pool-seni/(:num)/bagan.pdf', 'Admin\\Sekretariat\\PoolSeniController::printBagan/$1');
    $routes->post('pool-seni/(:num)/update', 'Admin\\Sekretariat\\PoolSeniController::update/$1');
    $routes->post('pool-seni/(:num)/acak-bagan-battle', 'Admin\\Sekretariat\\PoolSeniController::acakBaganBattle/$1');
    $routes->post('pool-seni/(:num)/beri-nomor-undi', 'Admin\\Sekretariat\\PoolSeniController::beriNomorUndi/$1');
    $routes->get('sistem-pool-seni', 'Admin\\Sekretariat\\SistemPoolSeniController::index');
    $routes->post('sistem-pool-seni/(:num)/update', 'Admin\\Sekretariat\\SistemPoolSeniController::update/$1');
    $routes->get('battle-seni', 'Admin\\Sekretariat\\BattleSeniController::index');
    $routes->get('pesilat-terbaik/battle-seni', 'Admin\\Sekretariat\\BattleSeniController::urutanPoin', ['filter' => 'adminrole:sekretariat,super_admin']);
    $routes->get('battle-seni/(:num)', 'Admin\\Sekretariat\\BattleSeniController::show/$1');
    $routes->get('kuota-prestasi-seni', 'Admin\\Sekretariat\\KuotaPrestasiSeniController::index');
    $routes->get('perolehan-medali/akumulasi', 'Admin\\Sekretariat\\MedalTallyController::aggregate');
    $routes->get('perolehan-medali/kategori-usia', 'Admin\\Sekretariat\\MedalTallyController::byAgeCategory');
    $routes->get('perolehan-medali/sekolah', 'Admin\\Sekretariat\\MedalTallyController::bySchool');
    $routes->get('perolehan-medali/akumulasi-eksklusif', 'Admin\\Sekretariat\\MedalTallyController::aggregateExclusive');
    $routes->get('perolehan-medali/kategori-usia-eksklusif', 'Admin\\Sekretariat\\MedalTallyController::byAgeCategoryExclusive');
    $routes->get('perolehan-medali/tanding', 'Admin\\Sekretariat\\MedalTallyController::tanding');
    $routes->get('perolehan-medali/seni', 'Admin\\Sekretariat\\MedalTallyController::seni');
    $routes->get('nomor-sertifikat', 'Admin\\Sekretariat\\NomorSertifikatController::index');
    $routes->get('pengadaan-medali', 'Admin\\Sekretariat\\PengadaanMedaliController::index');
    $routes->get('jadwal-tanding', 'Admin\\Sekretariat\\JadwalTandingController::index');
    $routes->get('jadwal-tanding/(:num)', 'Admin\\Sekretariat\\JadwalTandingController::show/$1');
    $routes->post('jadwal-tanding/create', 'Admin\\Sekretariat\\JadwalTandingController::create');
    $routes->post('jadwal-tanding/create-ajax', 'Admin\\Sekretariat\\JadwalTandingController::createFromModal');
    $routes->post('jadwal-tanding/(:num)/update-keterangan', 'Admin\\Sekretariat\\JadwalTandingController::updateKeterangan/$1');
    $routes->post('jadwal-tanding/(:num)/delete', 'Admin\\Sekretariat\\JadwalTandingController::delete/$1');
    $routes->post('jadwal-tanding/create-pdf-ajax/(:num)/(:num)', 'Admin\\Sekretariat\\JadwalTandingController::createPdfAjax/$1/$2');
    $routes->get('jadwal-tanding/get-all-ids-ajax', 'Admin\\Sekretariat\\JadwalTandingController::getAllIdsAjax');
    $routes->post('jadwal-tanding/tukar-atlet', 'Admin\\Sekretariat\\JadwalTandingController::tukarAtlet');
    $routes->post('jadwal-tanding/sortir-ulang/(:num)', 'Admin\\Sekretariat\\JadwalTandingController::sortirUlang/$1');
    $routes->post('jadwal-tanding/pola-penjadwalan/(:num)', 'Admin\\Sekretariat\\JadwalTandingController::polaPenjadwalan/$1');
    $routes->get('jadwal-seni', 'Admin\\Sekretariat\\JadwalSeniController::index');
    $routes->get('jadwal-seni/(:num)', 'Admin\\Sekretariat\\JadwalSeniController::show/$1');
    $routes->post('jadwal-seni/create', 'Admin\\Sekretariat\\JadwalSeniController::create');
    $routes->post('jadwal-seni/create-ajax', 'Admin\\Sekretariat\\JadwalSeniController::createFromModal');
    $routes->post('jadwal-seni/(:num)/update-keterangan', 'Admin\\Sekretariat\\JadwalSeniController::updateKeterangan/$1');
    $routes->post('jadwal-seni/(:num)/delete', 'Admin\\Sekretariat\\JadwalSeniController::delete/$1');
    $routes->post('jadwal-seni/create-pdf-ajax/(:num)/(:num)', 'Admin\\Sekretariat\\JadwalSeniController::createPdfAjax/$1/$2');
    $routes->get('jadwal-seni/get-all-ids-ajax', 'Admin\\Sekretariat\\JadwalSeniController::getAllIdsAjax');
});

$routes->group('development', ['filter' => 'developmentgate'], static function ($routes): void {
    $routes->get('/', 'Development\DashboardController::index');

    // System Health
    $routes->get('system-health', 'Development\SystemHealthController::index');

    // Log Viewer
    $routes->get('log-viewer', 'Development\LogViewerController::index');
    $routes->get('log-viewer/clear', 'Development\LogViewerController::clear');
    $routes->get('log-viewer/(:any)', 'Development\LogViewerController::index/$1');

    // Admin Utility
    $routes->get('admin-utility', 'Development\AdminUtilityController::index');
    $routes->post('admin-utility/update-password', 'Development\AdminUtilityController::updatePassword');

    // Database Manager
    $routes->get('database-manager', 'Development\DatabaseManagerController::index');
    $routes->get('database-manager/export', 'Development\DatabaseManagerController::export');
    $routes->post('database-manager/import', 'Development\DatabaseManagerController::import');
    $routes->post('database-manager/switch', 'Development\DatabaseManagerController::switchDatabase');
    $routes->post('database-manager/empty-tables', 'Development\DatabaseManagerController::emptyTables');
    $routes->post('database-manager/drop-tables', 'Development\DatabaseManagerController::dropTables');

    // Database Setup
    $routes->get('database-setup', 'Development\DatabaseSetupController::index');
    $routes->post('database-setup/process', 'Development\DatabaseSetupController::process');

    // Purger
    $routes->get('purger', 'Development\PurgerController::index');
    $routes->get('purger/clean/(:segment)', 'Development\PurgerController::clean/$1');

    // Data Pusher
    $routes->get('data-pusher', 'Development\DataPusherController::index');
    $routes->post('data-pusher/push', 'Development\DataPusherController::push');
});

$routes->group('admin/super', ['filter' => 'adminrole:super_admin'], static function ($routes): void {
    $routes->get('/', 'Admin\\Super\\DashboardController::index');
    $routes->get('dashboard', 'Admin\\Super\\DashboardController::index');
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes are one such time.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
