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
$routes->post('kontingen/pembayaran/biaya-kontingen', 'PembayaranKontingenController::storeBiayaKontingen', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta', 'PesertaController::store', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta/(:num)/update', 'PesertaController::update/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta/(:num)/delete', 'PesertaController::delete/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta/(:num)/arsip', 'ArsipPendaftarController::create/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta/(:num)/arsip/(:num)/update', 'ArsipPendaftarController::update/$1/$2', ['filter' => 'kontingenauth']);
$routes->post('kontingen/peserta/(:num)/arsip/(:num)/delete', 'ArsipPendaftarController::delete/$1/$2', ['filter' => 'kontingenauth']);
$routes->post('kontingen/tanding', 'KategoriTandingController::store', ['filter' => 'kontingenauth']);
$routes->post('kontingen/tanding/(:num)/update', 'KategoriTandingController::update/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/tanding/(:num)/delete', 'KategoriTandingController::delete/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/seni', 'KategoriSeniController::store', ['filter' => 'kontingenauth']);
$routes->post('kontingen/seni/(:num)/update', 'KategoriSeniController::update/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/seni/(:num)/delete', 'KategoriSeniController::delete/$1', ['filter' => 'kontingenauth']);
$routes->post('kontingen/pembayaran', 'PembayaranKontingenController::store', ['filter' => 'kontingenauth']);

// Legacy import-excel-data aliases kept for CI3 parity.
$routes->get('import-excel-data', 'Admin\\Super\\ImportExcelDataController::index', ['filter' => 'adminrole:super_admin']);
$routes->get('import-excel-data/download-template', 'Admin\\Super\\ImportExcelDataController::downloadTemplate', ['filter' => 'adminrole:super_admin']);
$routes->get('import-excel-data/download_template', 'Admin\\Super\\ImportExcelDataController::downloadTemplate', ['filter' => 'adminrole:super_admin']);
$routes->post('import-excel-data/preview', 'Admin\\Super\\ImportExcelDataController::preview', ['filter' => 'adminrole:super_admin']);
$routes->get('import-excel-data/preview/(:segment)', 'Admin\\Super\\ImportExcelDataController::preview/$1', ['filter' => 'adminrole:super_admin']);
$routes->post('import-excel-data/commit', 'Admin\\Super\\ImportExcelDataController::commit', ['filter' => 'adminrole:super_admin']);
$routes->post('import-excel-data/cancel', 'Admin\\Super\\ImportExcelDataController::cancel', ['filter' => 'adminrole:super_admin']);

$routes->group('admin/bendahara', ['filter' => 'adminrole:bendahara'], static function ($routes): void {
    $routes->get('/', 'Admin\\Bendahara\\DashboardController::index');
    $routes->get('dashboard', 'Admin\\Bendahara\\DashboardController::index');
    $routes->get('pembayaran', 'Admin\\Bendahara\\PembayaranController::index');
    $routes->get('pembayaran/menunggu-konfirmasi', 'Admin\\Bendahara\\PembayaranController::waiting');
    $routes->get('pembayaran/lunas', 'Admin\\Bendahara\\PembayaranController::paid');
    $routes->get('pembayaran/belum-dibayar', 'Admin\\Bendahara\\PembayaranController::unpaid');
    $routes->get('pembayaran/tanding', 'Admin\\Bendahara\\PembayaranController::tanding');
    $routes->get('pembayaran/seni', 'Admin\\Bendahara\\PembayaranController::seni');
    $routes->get('pembayaran/biaya-kontingen', 'Admin\\Bendahara\\PembayaranController::biayaKontingen');
    $routes->post('pembayaran/biaya-kontingen/(:num)/konfirmasi', 'Admin\\Bendahara\\PembayaranController::confirmBiayaKontingen/$1');
    $routes->post('pembayaran/biaya-kontingen/(:num)/tolak', 'Admin\\Bendahara\\PembayaranController::rejectBiayaKontingen/$1');
    $routes->get('pembayaran/(:num)', 'Admin\\Bendahara\\PembayaranController::show/$1');
    $routes->post('pembayaran/(:num)/konfirmasi', 'Admin\\Bendahara\\PembayaranController::confirm/$1');
    $routes->post('pembayaran/(:num)/tolak', 'Admin\\Bendahara\\PembayaranController::reject/$1');
    $routes->get('pembayaran/(:num)/nota', 'Admin\\Bendahara\\PembayaranController::nota/$1');
    $routes->get('pembayaran/(:num)/nota.pdf', 'Admin\\Bendahara\\PembayaranController::notaPdf/$1');
    $routes->get('kontingen', 'Admin\\Bendahara\\KontingenController::index');
    $routes->get('kontingen/(:num)', 'Admin\\Bendahara\\KontingenController::show/$1');
    $routes->post('kontingen/(:num)/buat-transaksi', 'Admin\\Bendahara\\PembayaranController::createForKontingen/$1');
});

$routes->group('admin/gelanggang', ['filter' => 'adminrole:super_admin'], static function ($routes): void {
    $routes->get('/', 'Admin\\GelanggangController::index');
    $routes->post('create', 'Admin\\GelanggangController::create');
    $routes->post('delete/(:num)', 'Admin\\GelanggangController::delete/$1');
    $routes->get('get-dates/(:num)', 'Admin\\GelanggangController::getDatesJson/$1');
    $routes->post('merge/(:num)', 'Admin\\GelanggangController::mergeJadwal/$1');
    $routes->post('merge-by-date/(:num)', 'Admin\\GelanggangController::mergeJadwalByDate/$1');
    $routes->post('merge-all', 'Admin\\GelanggangController::mergeJadwalAllArena');
});

$routes->group('admin/super', ['filter' => 'adminrole:super_admin'], static function ($routes): void {
    $routes->get('/', 'Admin\\Super\\DashboardController::index');
    $routes->get('dashboard', 'Admin\\Super\\DashboardController::index');
    $routes->get('menu-tipe', 'Admin\\Super\\ModeController::menuTipe');
    $routes->get('menu-utama', 'Admin\\Super\\ModeController::menuUtama');
    $routes->get('mode-pengaturan-event', 'Admin\\Super\\ModeController::pengaturanEvent');
    $routes->get('mode-pengaturan-kategori-lomba', 'Admin\\Super\\ModeController::pengaturanKategoriLomba');
    $routes->get('mode-pembuatan-jadwal', 'Admin\\Super\\ModeController::pembuatanJadwal');
    $routes->get('import-excel-data', 'Admin\\Super\\ImportExcelDataController::index');
    $routes->get('import-excel-data/download-template', 'Admin\\Super\\ImportExcelDataController::downloadTemplate');
    $routes->post('import-excel-data/preview', 'Admin\\Super\\ImportExcelDataController::preview');
    $routes->get('import-excel-data/preview/(:segment)', 'Admin\\Super\\ImportExcelDataController::preview/$1');
    $routes->post('import-excel-data/commit', 'Admin\\Super\\ImportExcelDataController::commit');
    $routes->post('import-excel-data/cancel', 'Admin\\Super\\ImportExcelDataController::cancel');
    $routes->get('dashboard-pengaturan-event', 'Admin\\Super\\PengaturanEventController::dashboard');

    // Mode: Pembuatan Jadwal
    $routes->get('dashboard-pembuatan-jadwal', 'Admin\\Super\\PembuatanJadwalController::dashboard');
    $routes->get('operasi-basis-data', 'Admin\\Super\\PembuatanJadwalController::operasiBasisData');
    $routes->post('operasi-basis-data/reset-seluruh-jadwal', 'Admin\\Super\\PembuatanJadwalController::resetSeluruhJadwal');
    $routes->post('operasi-basis-data/backup-database', 'Admin\\Super\\PembuatanJadwalController::backupDatabase');
    $routes->post('operasi-basis-data/hapus-pool-seni-kosong', 'Admin\\Super\\PembuatanJadwalController::hapusPoolSeniKosong');
    $routes->post('operasi-basis-data/hapus-data-dari-excel', 'Admin\\Super\\PembuatanJadwalController::hapusDataDariExcel');
    $routes->post('operasi-basis-data/hapus-atlet-belum-lunas', 'Admin\\Super\\PembuatanJadwalController::hapusAtletBelumLunas');
    $routes->post('operasi-basis-data/buat-pool-baru', 'Admin\\Super\\PembuatanJadwalController::buatPoolBaru');
    $routes->post('operasi-basis-data/buat-kategori-partai-tambahan', 'Admin\\Super\\PembuatanJadwalController::buatKategoriUntukPartaiTambahan');
    $routes->post('operasi-basis-data/reset-database', 'Admin\\Super\\PembuatanJadwalController::resetDatabase');

    $routes->get('operasi-basis-data/hapus-data-kosong', 'Admin\\Super\\PembuatanJadwalController::hapusDataKosong');
    $routes->post('operasi-basis-data/preview-hapus-data-kosong', 'Admin\\Super\\PembuatanJadwalController::previewHapusDataKosong');
    $routes->post('operasi-basis-data/proses-hapus-data-kosong', 'Admin\\Super\\PembuatanJadwalController::prosesHapusDataKosong');

    $routes->get('operasi-basis-data/hapus-peserta-per-kategori-usia', 'Admin\\Super\\PembuatanJadwalController::hapusPesertaPerKategoriUsia');
    $routes->post('operasi-basis-data/preview-hapus-peserta-berdasarkan-kategori-usia', 'Admin\\Super\\PembuatanJadwalController::previewHapusPesertaBerdasarkanKategoriUsia');
    $routes->post('operasi-basis-data/hapus-peserta-berdasarkan-kategori-usia', 'Admin\\Super\\PembuatanJadwalController::hapusPesertaBerdasarkanKategoriUsia');

    $routes->get('drawing-tanding', 'Admin\\Super\\PembuatanJadwalController::drawingTanding');
    $routes->post('drawing-tanding/distribusikan-peserta', 'Admin\\Super\\PembuatanJadwalController::distribusikanPesertaTanding');
    $routes->post('drawing-tanding/acak-bagan', 'Admin\\Super\\PembuatanJadwalController::acakBaganTandingBulk');
    $routes->post('drawing-tanding/distribusikan-tanpa-lawan/(:num)', 'Admin\\Super\\PembuatanJadwalController::distribusikanPesertaTandingTanpaLawan/$1');
    $routes->post('drawing-tanding/pisahkan-kontingen-sendiri', 'Admin\\Super\\PembuatanJadwalController::pisahkanKontingenTanding');
    $routes->get('drawing-tanding/laporan-hasil-drawing-bagan', 'Admin\\Super\\PembuatanJadwalController::laporanHasilDrawingBaganTanding');
    $routes->get('drawing-seni', 'Admin\\Super\\PembuatanJadwalController::drawingSeni');
    $routes->post('drawing-seni/distribusikan-kelompok', 'Admin\\Super\\PembuatanJadwalController::distribusikanKelompokPesertaSeni');
    $routes->post('drawing-seni/acak-bagan-battle', 'Admin\\Super\\PembuatanJadwalController::acakBaganBattleSeniBulk');
    $routes->post('drawing-seni/beri-nomor-undi', 'Admin\\Super\\PembuatanJadwalController::beriNomorUndiSeniBulk');
    $routes->get('generate-bagan-tanding-dari-jadwal', 'Admin\\Super\\PembuatanJadwalController::generateBaganTandingDariJadwal');
    $routes->post('generate-bagan-tanding-dari-jadwal', 'Admin\\Super\\PembuatanJadwalController::prosesGenerateBaganTandingDariJadwal');
    $routes->get('generate-bagan-seni-battle-dari-jadwal', 'Admin\\Super\\PembuatanJadwalController::generateBaganSeniBattleDariJadwal');
    $routes->post('generate-bagan-seni-battle-dari-jadwal', 'Admin\\Super\\PembuatanJadwalController::prosesGenerateBaganSeniBattleDariJadwal');
    $routes->get('jadwal-tanding', 'Admin\\Super\\PembuatanJadwalController::jadwalTanding');
    // Penjadwalan Otomatis Tanding (parity CI3)
    $routes->get('jadwal-tanding/penjadwalan-otomatis', 'Admin\\Super\\PenjadwalanTandingOtomatisController::index');
    $routes->post('jadwal-tanding/buat-jadwal-tanding-otomatis', 'Admin\\Super\\PenjadwalanTandingOtomatisController::store');

    $routes->get('jadwal-tanding/(:num)/download', 'Admin\\Super\\PembuatanJadwalController::downloadJadwalTanding/$1');
    $routes->get('jadwal-tanding/(:num)', 'Admin\\Super\\PembuatanJadwalController::showJadwalTanding/$1');
    $routes->post('jadwal-tanding/create', 'Admin\\Super\\PembuatanJadwalController::createJadwalTanding');
    $routes->post('jadwal-tanding/(:num)/update-keterangan', 'Admin\\Super\\PembuatanJadwalController::updateKeteranganJadwalTanding/$1');
    $routes->post('jadwal-tanding/(:num)/delete', 'Admin\\Super\\PembuatanJadwalController::deleteJadwalTanding/$1');
    $routes->post('jadwal-tanding/create-pdf-ajax/(:num)/(:num)', 'Admin\\Super\\PembuatanJadwalController::createPdfJadwalTandingAjax/$1/$2');
    $routes->get('jadwal-tanding/get-all-ids-ajax', 'Admin\\Super\\PembuatanJadwalController::getAllIdsJadwalTandingAjax');
    $routes->get('jadwal-tanding/tukar-atlet', 'Admin\\Super\\PembuatanJadwalController::halamanTukarAtlet');
    $routes->post('jadwal-tanding/tukar-atlet', 'Admin\\Super\\PembuatanJadwalController::tukarAtletJadwalTanding');
    $routes->post('jadwal-tanding/sortir-ulang/(:num)', 'Admin\\Super\\PembuatanJadwalController::sortirUlangJadwalTanding/$1');
    $routes->post('jadwal-tanding/pola-penjadwalan/(:num)', 'Admin\\Super\\PembuatanJadwalController::polaPenjadwalanJadwalTanding/$1');
    $routes->post('jadwal-tanding/(:num)/import-excel', 'Admin\\Super\\PembuatanJadwalController::importExcelJadwalTanding/$1');
    $routes->post('jadwal-tanding/(:num)/import-excel-commit', 'Admin\\Super\\PembuatanJadwalController::importExcelCommitJadwalTanding/$1');
    $routes->post('jadwal-tanding/(:num)/perbaiki-bracket-bentrok', 'Admin\\Super\\PembuatanJadwalController::perbaikiBracketBentrokJadwalTanding/$1');
    $routes->get('jadwal-tanding/pengaturan-urutan-partai-tanding/(:num)', 'Admin\\Super\\PembuatanJadwalController::pengaturanUrutanPartaiTanding/$1');
    $routes->post('jadwal-tanding/update-urutan-partai-tanding/(:num)', 'Admin\\Super\\PembuatanJadwalController::updateUrutanPartaiTanding/$1');
    $routes->get('jadwal-seni', 'Admin\\Super\\PembuatanJadwalController::jadwalSeni');
    // Penjadwalan Otomatis Seni
    $routes->get('jadwal-seni/penjadwalan-otomatis', 'Admin\\Super\\PenjadwalanSeniOtomatisController::index');
    $routes->post('jadwal-seni/buat-jadwal-seni-pool-otomatis', 'Admin\\Super\\PenjadwalanSeniOtomatisController::storePool');
    $routes->post('jadwal-seni/buat-jadwal-seni-battle-otomatis', 'Admin\\Super\\PenjadwalanSeniOtomatisController::storeBattle');
    $routes->get('jadwal-seni/diagnosis', 'Admin\\Super\\PembuatanJadwalController::diagnosisSeni');
    $routes->get('jadwal-seni/overview', 'Admin\\Super\\PembuatanJadwalController::overviewSeni');
    $routes->get('jadwal-seni/(:num)/download', 'Admin\\Super\\PembuatanJadwalController::downloadJadwalSeni/$1');
    $routes->get('jadwal-seni/(:num)', 'Admin\\Super\\PembuatanJadwalController::showJadwalSeni/$1');
    $routes->post('jadwal-seni/create', 'Admin\\Super\\PembuatanJadwalController::createJadwalSeni');
    $routes->post('jadwal-seni/(:num)/update-keterangan', 'Admin\\Super\\PembuatanJadwalController::updateKeteranganJadwalSeni/$1');
    $routes->post('jadwal-seni/(:num)/delete', 'Admin\\Super\\PembuatanJadwalController::deleteJadwalSeni/$1');
    $routes->post('jadwal-seni/(:num)/import-excel-pool', 'Admin\\Super\\PembuatanJadwalController::importExcelJadwalSeniPool/$1');
    $routes->post('jadwal-seni/(:num)/import-excel-pool-commit', 'Admin\\Super\\PembuatanJadwalController::importExcelCommitJadwalSeniPool/$1');
    $routes->post('jadwal-seni/(:num)/import-excel-battle', 'Admin\\Super\\PembuatanJadwalController::importExcelJadwalSeniBattle/$1');
    $routes->post('jadwal-seni/(:num)/import-excel-battle-commit', 'Admin\\Super\\PembuatanJadwalController::importExcelCommitJadwalSeniBattle/$1');
    $routes->post('jadwal-seni/create-pdf-ajax/(:num)/(:num)', 'Admin\\Super\\PembuatanJadwalController::createPdfJadwalSeniAjax/$1/$2');
    $routes->get('jadwal-seni/get-all-ids-ajax', 'Admin\\Super\\PembuatanJadwalController::getAllIdsJadwalSeniAjax');
    $routes->post('jadwal-seni/tukar-kelompok-peserta-seni-pool', 'Admin\\Super\\PembuatanJadwalController::tukarKelompokPesertaSeniPool');
    $routes->post('jadwal-seni/resequence-nomor-partai', 'Admin\\Super\\PembuatanJadwalController::resequenceNomorPartaiJadwalSeni');
    $routes->get('jadwal-seni/pengaturan-urutan-partai-seni/(:num)', 'Admin\\Super\\PembuatanJadwalController::pengaturanUrutanPartaiSeni/$1');
    $routes->post('jadwal-seni/update-urutan-partai-seni/(:num)', 'Admin\\Super\\PembuatanJadwalController::updateUrutanPartaiSeni/$1');
    $routes->get('pengaturan-event/profil-kejuaraan', 'Admin\\Super\\EventProfileController::edit');
    $routes->post('pengaturan-event/profil-kejuaraan/update', 'Admin\\Super\\EventProfileController::update');
    $routes->get('pengaturan-event/akses-pendaftaran', 'Admin\\Super\\AksesPendaftaranController::edit');
    $routes->post('pengaturan-event/akses-pendaftaran/update', 'Admin\\Super\\AksesPendaftaranController::update');
    $routes->get('pengaturan-event/akses-pemilihan-kategori', 'Admin\\Super\\AksesPemilihanKategoriController::edit');
    $routes->post('pengaturan-event/akses-pemilihan-kategori/update', 'Admin\\Super\\AksesPemilihanKategoriController::update');
    $routes->get('pengaturan-event/gambar-dan-juknis', 'Admin\\Super\\GambarDanJuknisController::edit');
    $routes->post('pengaturan-event/gambar-dan-juknis/update', 'Admin\\Super\\GambarDanJuknisController::update');
    $routes->get('pengaturan-event/rekening-pembayaran', 'Admin\\Super\\RekeningPembayaranController::edit');
    $routes->post('pengaturan-event/rekening-pembayaran/update', 'Admin\\Super\\RekeningPembayaranController::update');
    $routes->get('pengaturan-event/pengaturan-kontingen', 'Admin\\Super\\KontingenSettingsController::edit');
    $routes->post('pengaturan-event/pengaturan-kontingen/update', 'Admin\\Super\\KontingenSettingsController::update');

    // Pengaturan Arsip Pendaftar (CI3: pengaturan_arsip_pendaftar)
    $routes->get('pengaturan-event/arsip-pendaftar', 'Admin\\Super\\ArsipPendaftarSettingsController::index');
    $routes->post('pengaturan-event/arsip-pendaftar', 'Admin\\Super\\ArsipPendaftarSettingsController::store');
    $routes->post('pengaturan-event/arsip-pendaftar/update', 'Admin\\Super\\ArsipPendaftarSettingsController::update');
    $routes->post('pengaturan-event/arsip-pendaftar/delete', 'Admin\\Super\\ArsipPendaftarSettingsController::delete');
    $routes->post('pengaturan-event/arsip-pendaftar/toggle', 'Admin\\Super\\ArsipPendaftarSettingsController::toggleActive');
    $routes->get('kategori-usia', 'Admin\\Super\\KategoriUsiaController::index');
    $routes->get('kategori-usia/(:num)', 'Admin\\Super\\KategoriUsiaController::show/$1');
    $routes->get('kategori-usia/(:num)/edit', 'Admin\\Super\\KategoriUsiaController::edit/$1');
    $routes->post('kategori-usia', 'Admin\\Super\\KategoriUsiaController::store');
    $routes->post('kategori-usia/(:num)/update', 'Admin\\Super\\KategoriUsiaController::update/$1');
    $routes->post('kategori-usia/(:num)/delete', 'Admin\\Super\\KategoriUsiaController::delete/$1');
    $routes->get('kategori-lomba', 'Admin\\Super\\KategoriLombaController::index');
    $routes->get('kategori-lomba/(:num)/edit', 'Admin\\Super\\KategoriLombaController::edit/$1');
    $routes->post('kategori-lomba', 'Admin\\Super\\KategoriLombaController::store');
    $routes->post('kategori-lomba/(:num)/update', 'Admin\\Super\\KategoriLombaController::update/$1');
    $routes->post('kategori-lomba/(:num)/delete', 'Admin\\Super\\KategoriLombaController::delete/$1');
    $routes->get('sub-kategori-seni', 'Admin\\Super\\SubKategoriSeniController::index');
    $routes->get('sub-kategori-seni/(:num)', 'Admin\\Super\\SubKategoriSeniController::show/$1');
    $routes->get('sub-kategori-seni/(:num)/edit', 'Admin\\Super\\SubKategoriSeniController::edit/$1');
    $routes->post('sub-kategori-seni', 'Admin\\Super\\SubKategoriSeniController::store');
    $routes->post('sub-kategori-seni/(:num)/update', 'Admin\\Super\\SubKategoriSeniController::update/$1');
    $routes->post('sub-kategori-seni/(:num)/delete', 'Admin\\Super\\SubKategoriSeniController::delete/$1');
    $routes->post('sub-kategori-seni/update-max-peserta-per-pool', 'Admin\\Super\\SubKategoriSeniController::updateMaxPesertaPerPool');
    $routes->get('kelas-tanding', 'Admin\\Super\\KelasTandingController::index');
    $routes->get('kelas-tanding/(:num)', 'Admin\\Super\\KelasTandingController::show/$1');
    $routes->get('kelas-tanding/(:num)/edit', 'Admin\\Super\\KelasTandingController::edit/$1');
    $routes->post('kelas-tanding', 'Admin\\Super\\KelasTandingController::store');
    $routes->post('kelas-tanding/create-multiple', 'Admin\\Super\\KelasTandingController::storeMultiple');
    $routes->post('kelas-tanding/(:num)/update', 'Admin\\Super\\KelasTandingController::update/$1');
    $routes->post('kelas-tanding/(:num)/delete', 'Admin\\Super\\KelasTandingController::delete/$1');
    $routes->post('kelas-tanding/(:num)/otomatis-tambah-pool', 'Admin\\Super\\KelasTandingController::autoTambahPool/$1');
    $routes->post('kelas-tanding/update-jumlah-peserta-per-pool', 'Admin\\Super\\KelasTandingController::updateJumlahPesertaPerPool');
});

$routes->group('admin/sekretariat', ['filter' => 'adminrole:sekretariat'], static function ($routes): void {
    $routes->get('/', 'Admin\\Sekretariat\\DashboardController::index');
    $routes->get('dashboard', 'Admin\\Sekretariat\\DashboardController::index');
    $routes->get('statistik', 'Admin\\Sekretariat\\StatistikController::index');
    $routes->get('statistik/tanding', 'Admin\\Sekretariat\\StatistikController::tanding');
    $routes->get('statistik/seni', 'Admin\\Sekretariat\\StatistikController::seni');
    $routes->get('kontingen', 'Admin\\Sekretariat\\KontingenController::index');
    $routes->get('kontingen/rekap-atlet', 'Admin\\Sekretariat\\KontingenController::rekapAtlet');
    $routes->get('kontingen/(:num)', 'Admin\\Sekretariat\\KontingenController::show/$1');
    $routes->post('kontingen', 'Admin\\Sekretariat\\KontingenController::store');
    $routes->post('kontingen/(:num)/update', 'Admin\\Sekretariat\\KontingenController::update/$1');
    $routes->post('kontingen/(:num)/reset-password', 'Admin\\Sekretariat\\KontingenController::resetPassword/$1');
    $routes->post('kontingen/(:num)/delete', 'Admin\\Sekretariat\\KontingenController::delete/$1');
     $routes->post('kontingen/(:num)/pendaftar', 'Admin\\Sekretariat\\KontingenController::storePendaftar/$1');
     $routes->post('kontingen/(:num)/pendaftar/(:num)/update', 'Admin\\Sekretariat\\KontingenController::updatePendaftar/$1/$2');
     $routes->post('kontingen/(:num)/pendaftar/(:num)/delete', 'Admin\\Sekretariat\\KontingenController::deletePendaftar/$1/$2');
     $routes->post('kontingen/(:num)/pendaftar/(:num)/arsip', 'ArsipPendaftarController::create/$1', ['filter' => 'adminrole:sekretariat']);
     $routes->post('kontingen/(:num)/pendaftar/(:num)/arsip/(:num)/update', 'ArsipPendaftarController::update/$1/$3', ['filter' => 'adminrole:sekretariat']);
     $routes->post('kontingen/(:num)/pendaftar/(:num)/arsip/(:num)/delete', 'ArsipPendaftarController::delete/$1/$3', ['filter' => 'adminrole:sekretariat']);
     $routes->post('kontingen/(:num)/peserta-tanding', 'Admin\\Sekretariat\\KontingenController::storePesertaTanding/$1');
    $routes->post('kontingen/(:num)/kelompok-seni', 'Admin\\Sekretariat\\KontingenController::storeKelompokSeni/$1');
    $routes->get('data-atlet', 'Admin\\Sekretariat\\PendaftarController::index');
    $routes->get('data-bpjs', 'Admin\\Sekretariat\\DataBpjsController::index');
    $routes->get('peserta-tanding', 'Admin\\Sekretariat\\PesertaTandingController::index');
    $routes->get('kompetisi-tanding/by-pendaftar/(:num)', 'Admin\\Sekretariat\\PesertaTandingController::byPendaftar/$1');
    $routes->get('peserta-tanding/(:num)', 'Admin\\Sekretariat\\PesertaTandingController::show/$1');
    $routes->get('peserta-tanding/(:num)/edit-kelas', 'Admin\\Sekretariat\\PesertaTandingController::editKelas/$1');
    $routes->get('peserta-tanding/(:num)/pindah-pool', 'Admin\\Sekretariat\\PesertaTandingController::pindahPool/$1');
    $routes->get('peserta-tanding/(:num)/ajax-edit-kelas', 'Admin\\Sekretariat\\PesertaTandingController::ajaxEditKelas/$1');
    $routes->get('peserta-tanding/(:num)/ajax-pindah-pool', 'Admin\\Sekretariat\\PesertaTandingController::ajaxPindahPool/$1');
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
    $routes->get('kelompok-seni/(:num)/ajax-edit-kelompok', 'Admin\\Sekretariat\\KelompokPesertaSeniController::ajaxEditKelompok/$1');
    $routes->get('kelompok-seni/(:num)/ajax-pindah-pool', 'Admin\\Sekretariat\\KelompokPesertaSeniController::ajaxPindahPool/$1');
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
    $routes->get('jadwal-tanding/(:num)/download', 'Admin\\Sekretariat\\JadwalTandingController::download/$1');
    $routes->get('jadwal-tanding/(:num)', 'Admin\\Sekretariat\\JadwalTandingController::show/$1');
    $routes->post('jadwal-tanding/create-pdf-ajax/(:num)/(:num)', 'Admin\\Sekretariat\\JadwalTandingController::createPdfAjax/$1/$2');
    $routes->get('jadwal-tanding/get-all-ids-ajax', 'Admin\\Sekretariat\\JadwalTandingController::getAllIdsAjax');
    $routes->get('jadwal-seni', 'Admin\\Sekretariat\\JadwalSeniController::index');
    $routes->get('jadwal-seni/(:num)/download', 'Admin\\Sekretariat\\JadwalSeniController::download/$1');
    $routes->get('jadwal-seni/(:num)', 'Admin\\Sekretariat\\JadwalSeniController::show/$1');
    $routes->post('jadwal-seni/create-pdf-ajax/(:num)/(:num)', 'Admin\\Sekretariat\\JadwalSeniController::createPdfAjax/$1/$2');
    $routes->get('jadwal-seni/get-all-ids-ajax', 'Admin\\Sekretariat\\JadwalSeniController::getAllIdsAjax');

    // Cek Data Arsip
    $routes->get('cek-data-arsip', 'Admin\Sekretariat\CekDataArsipController::index');
    $routes->post('cek-data-arsip/detail', 'Admin\Sekretariat\CekDataArsipController::getDetailArsip');

    // ID Card
    $routes->get('id-card', 'Admin\Sekretariat\IdCardController::index');
    $routes->get('id-card/pengaturan-tata-letak/(:segment)', 'Admin\Sekretariat\IdCardController::pengaturanTataLetak/$1');
    $routes->post('id-card/simpan-tata-letak', 'Admin\Sekretariat\IdCardController::simpanTataLetak');
    $routes->post('id-card/upload-background', 'Admin\Sekretariat\IdCardController::uploadBackground');
    $routes->get('id-card/preview', 'Admin\Sekretariat\IdCardController::preview');
    $routes->get('id-card/cetak-per-kontingen', 'Admin\Sekretariat\IdCardController::cetakPerKontingen');
    $routes->get('id-card/cetak-per-peserta', 'Admin\Sekretariat\IdCardController::cetakPerPeserta');
    $routes->post('id-card/proses-cetak-batch', 'Admin\Sekretariat\IdCardController::prosesCetakBatch');
    $routes->get('id-card/cetak/(:segment)/(:num)', 'Admin\Sekretariat\IdCardController::cetakSingle/$1/$2');
    $routes->get('id-card/api/peserta-tanding/(:num)', 'Admin\Sekretariat\IdCardController::apiPesertaTanding/$1');
    $routes->get('id-card/api/peserta-seni/(:num)', 'Admin\Sekretariat\IdCardController::apiPesertaSeni/$1');
});

// ============================================================
// PRINTER
// ============================================================
$routes->group('admin/printer', ['filter' => 'adminrole:printer'], static function ($routes): void {
    $routes->get('/', 'Admin\PrinterController::dashboard');
    $routes->get('dashboard', 'Admin\PrinterController::dashboard');
    $routes->get('pengaturan-tata-letak', 'Admin\PrinterController::pengaturanTataLetak');
    $routes->post('simpan-tata-letak', 'Admin\PrinterController::simpanTataLetak');
    $routes->post('upload-background', 'Admin\PrinterController::uploadBackground');
    $routes->post('update-domain-hosting', 'Admin\PrinterController::updateDomainHosting');
    $routes->post('update-hide-background', 'Admin\PrinterController::updateHideBackground');
    $routes->get('preview', 'Admin\PrinterController::preview');
    $routes->get('cetak-tanding', 'Admin\PrinterController::cetakTandingList');
    $routes->get('cetak-seni', 'Admin\PrinterController::cetakSeniList');
    $routes->get('cetak/(:segment)/(:num)', 'Admin\PrinterController::cetakSingle/$1/$2');
    $routes->get('api/peserta-tanding/(:num)', 'Admin\PrinterController::apiPesertaTanding/$1');
    $routes->get('api/peserta-seni/(:num)', 'Admin\PrinterController::apiPesertaSeni/$1');

    // Nomor sertifikat
    $routes->post('update-nomor-sertifikat-suffix', 'Admin\PrinterController::updateNomorSertifikatSuffix');
    $routes->post('generate-nomor-sertifikat', 'Admin\PrinterController::generateNomorSertifikatAjax');
    $routes->post('generate-semua-nomor-sertifikat', 'Admin\PrinterController::generateSemuaNomorSertifikat');
    $routes->post('reset-nomor-sertifikat', 'Admin\PrinterController::resetNomorSertifikat');
    $routes->get('statistik-nomor-sertifikat', 'Admin\PrinterController::statistikNomorSertifikatAjax');
});

$routes->group('utilities', ['filter' => 'adminrole:super_admin'], static function ($routes): void {
    $routes->get('db-sync', 'Utilities\\DbSyncController::index');
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
