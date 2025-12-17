<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Auth::login');

// Public routes (tidak perlu login)
$routes->get('login', 'Auth::login');
$routes->post('auth/attemptLogin', 'Auth::attemptLogin');
$routes->get('register', 'Auth::register');
$routes->post('auth/attemptRegister', 'Auth::attemptRegister');
$routes->get('logout', 'Auth::logout');

// Protected routes dengan filter auth
$routes->group('', ['filter' => 'auth'], function ($routes) {
    
    // Dashboard - semua role bisa akses
    $routes->get('dashboard', 'Dashboard::index');
    
    // Profile - semua role bisa akses
    $routes->get('pengguna/profile', 'Pengguna::profile');
    $routes->post('pengguna/updateProfile', 'Pengguna::updateProfile');
    
    // Laporan - semua role bisa akses
    $routes->get('laporan/', 'Laporan::index');
    $routes->get('laporan/transaksi', 'Laporan::transaksi');
    $routes->get('laporan/stock', 'Laporan::stock');
    $routes->get('laporan/mutasi', 'Laporan::mutasi');
    $routes->get('laporan/print/(:segment)', 'Laporan::printLaporan/$1');
    
    // Transaksi - semua role bisa akses
    $routes->get('transaksi', 'Transaksi::index');
    $routes->get('transaksi/masuk', 'Transaksi::createMasuk');
    $routes->post('transaksi/saveMasuk', 'Transaksi::saveMasuk');
    $routes->get('transaksi/keluar', 'Transaksi::createKeluar');
    $routes->post('transaksi/saveKeluar', 'Transaksi::saveKeluar');
    $routes->get('transaksi/mutasi', 'Transaksi::createMutasi');
    $routes->post('transaksi/saveMutasi', 'Transaksi::saveMutasi');
    $routes->get('transaksi/detail/(:num)', 'Transaksi::detail/$1');
    $routes->get('transaksi/print/(:num)', 'Transaksi::print/$1');
    $routes->get('transaksi/print-label-kayu/(:num)', 'Transaksi::printLabelKayu/$1');
    $routes->get('transaksi/print-label/(:num)', 'Transaksi::printLabelTransaksi/$1');
    $routes->get('transaksi/getDetailByKode/(:any)', 'Transaksi::getDetailByKode/$1');
    $routes->get('transaksi/getStock', 'Transaksi::getStock');
    $routes->get('transaksi/getStock/(:num)', 'Transaksi::getStock/$1');
    $routes->get('transaksi/getStock/(:num)/(:num)', 'Transaksi::getStock/$1/$2');
    
    // Jenis Kayu - semua role bisa akses
    $routes->get('jenis-kayu', 'JenisKayu::index');
    $routes->post('jenis-kayu/create', 'JenisKayu::create');
    $routes->post('jenis-kayu/update', 'JenisKayu::update');
    $routes->delete('jenis-kayu/delete/(:num)', 'JenisKayu::delete/$1');
    $routes->get('jenis-kayu/export', 'JenisKayu::exportExcel');

    // Gudang - semua role bisa akses
    $routes->get('gudang', 'Gudang::index');
    $routes->post('gudang/create', 'Gudang::create');
    $routes->post('gudang/update', 'Gudang::update');
    $routes->delete('gudang/delete/(:num)', 'Gudang::delete/$1');
    
    // Data Kayu - semua role bisa akses
    $routes->get('kayu', 'Kayu::index');
    $routes->get('kayu/create', 'Kayu::create');
    $routes->post('kayu/store', 'Kayu::store');
    $routes->get('kayu/edit/(:num)', 'Kayu::edit/$1');
    $routes->post('kayu/update/(:num)', 'Kayu::update/$1');
    $routes->delete('kayu/delete/(:num)', 'Kayu::delete/$1');
    $routes->get('kayu/barcode/(:num)', 'Kayu::barcode/$1');
    $routes->get('kayu/print-barcode/(:num)', 'Kayu::printBarcode/$1');
    $routes->get('kayu/print-qrcode/(:num)', 'Kayu::printQrCode/$1');
});

// Routes khusus untuk admin dan manager (manajemen pengguna)
$routes->group('', ['filter' => 'auth:admin,manager'], function ($routes) {
    $routes->get('pengguna', 'Pengguna::index');
    $routes->get('pengguna/create', 'Pengguna::create');
    $routes->post('pengguna/store', 'Pengguna::store');
    $routes->get('pengguna/edit/(:num)', 'Pengguna::edit/$1');
    $routes->put('pengguna/update/(:num)', 'Pengguna::update/$1');
    $routes->delete('pengguna/delete/(:num)', 'Pengguna::delete/$1');
    $routes->post('pengguna/updateStatus/(:num)', 'Pengguna::updateStatus/$1');
});

// Barcode - public route
$routes->get('barcode/generate/(:any)', 'Barcode::generate/$1');

// Route untuk unauthorized access
$routes->get('unauthorized', function() {
    return view('errors/unauthorized');
});