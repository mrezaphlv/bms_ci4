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
    $routes->get('checklist_engineer', 'Checklist_engineer::index');
    $routes->post('checklist_engineer/grid', 'Checklist_engineer::grid');
    $routes->get('checklist_engineer/input/(:num)', 'Checklist_engineer::input/$1');
    $routes->get('checklist_engineer/view/(:num)', 'Checklist_engineer::view/$1');
    $routes->get('checklist_engineer/edit/(:num)', 'Checklist_engineer::edit/$1');
    $routes->get('checklist_engineer/detailUndangan/(:num)', 'Checklist_engineer::detailUndangan/$1');
    $routes->post('checklist_engineer/save', 'Checklist_engineer::saveChecklist');
    $routes->post('checklist_engineer/update', 'Checklist_engineer::updateChecklist');
    $routes->post('checklist_engineer/cek-pin', 'Checklist_engineer::cekPin');
    $routes->post('checklist_engineer/approve', 'Checklist_engineer::approveChecklist');
    $routes->post('checklist_engineer/reject', 'Checklist_engineer::rejectChecklist');
     $routes->post('checklist_engineer/add-item', 'Checklist_engineer::addnewItem');
     $routes->get('kirim_undangan', 'Kirim_undangan::index');
     $routes->post('kirim_undangan/grid', 'Kirim_undangan::grid');
     $routes->get('kirim_undangan/detail/(:num)', 'Kirim_undangan::detail/$1');
     $routes->post('kirim_undangan/submitConfirm', 'Kirim_undangan::submitConfirm');
     $routes->post('kirim_undangan/load_reconfirm', 'Kirim_undangan::load_reconfirm');
     $routes->post('kirim_undangan/kirimEmail', 'Kirim_undangan::kirimEmail');
     $routes->get('kirim_undangan/printFileEmail/(:num)', 'Kirim_undangan::printFileEmail/$1');
     $routes->get('kirim_undangan/download-file-ppjb/(:num)', 'Kirim_undangan::download_file_ppjb/$1');
  });
