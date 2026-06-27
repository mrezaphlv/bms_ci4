<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Root::index');
$routes->get('login', 'Root::login');
$routes->post('login/check', 'Root::login_check');
$routes->get('logout', 'Root::logout');

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('home', 'Home::index');
    $routes->get('dashboard', 'Home::index');
});
