<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Root::index');
$routes->get('login', 'Root::login');
$routes->post('login/check', 'Root::login_check');
$routes->get('logout', 'Root::logout');
$routes->get('menu/get', 'Menu::get');
$routes->get('unit', 'Unit::index');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('home', 'Home::index');
    $routes->get('dashboard', 'Home::index');
    $routes->get('undangan', 'Undangan::index');
    $routes->get('undangan/form', 'Undangan::form');
    $routes->post('undangan/save', 'Undangan::save');
    $routes->post('undangan/grid', 'Undangan::grid');
    $routes->post('undangan/grid-sales-dlg', 'Undangan::grid_sales_dlg');
    $routes->post('undangan/grid-owner-dlg', 'Undangan::grid_owner_dlg');
    $routes->post('undangan/grid-unit-dlg', 'Undangan::grid_unit_dlg');
    $routes->post('undangan/grid-meterrange-dlg', 'Undangan::grid_meterrange_dlg');
    $routes->post('undangan/cari-meterrange', 'Undangan::cariMeterrange');
    $routes->post('undangan/hitung-fee', 'Undangan::hitung_fee');
    $routes->post('undangan/cari-tarif-pajak', 'Undangan::cariTarifPajak');
    $routes->post('undangan/preview-nomor', 'Undangan::previewNomor');
    $routes->get('undangan/detail/(:num)', 'Undangan::detail/$1');
    $routes->get('undangan/edit/(:num)', 'Undangan::edit/$1');
    $routes->post('undangan/update/(:num)', 'Undangan::update/$1');
    $routes->post('undangan/cek-pin', 'Undangan::cekPin');
    $routes->post('undangan/approve', 'Undangan::approveUndangan');
    $routes->post('undangan/reject', 'Undangan::rejectUndangan');
    $routes->post('undangan/submit-ppjb', 'Undangan::submitPPJB');
    $routes->post('undangan/get-ppjb', 'Undangan::getPpjb');
    $routes->get('undangan/view-file-ppjb/(:num)', 'Undangan::view_file_ppjb/$1');
    $routes->get('undangan/download-file-ppjb/(:num)', 'Undangan::download_file_ppjb/$1');
});
