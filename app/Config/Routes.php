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
