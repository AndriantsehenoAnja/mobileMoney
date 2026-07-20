<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'LoginController::index');
$routes->get('login', 'LoginController::index');
$routes->post('login/authenticate', 'LoginController::login');

$routes->get('/Client', 'ClientController::index', ['filter' => 'auth']);


$routes->group('prefix', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'PrefixController::index');
    $routes->get('form', 'PrefixController::form');
    $routes->post('create', 'PrefixController::create');
    $routes->get('edit/(:num)', 'PrefixController::edit/$1');
    $routes->post('update/(:num)', 'PrefixController::update/$1');
    $routes->get('delete/(:num)', 'PrefixController::delete/$1');
});

$routes->group('type-operation', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'TypeOperationController::index');
});

$routes->group('bareme', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'BaremeController::index');
    $routes->get('showbytypeoperation/(:num)', 'BaremeController::showbyTypeOperation/$1');
    $routes->post('update/(:num)', 'BaremeController::update/$1');
    $routes->get('edit/(:num)', 'BaremeController::edit/$1');
    $routes->post('addbareme', 'BaremeController::addBareme');
    $routes->get('addbareme', 'BaremeController::form');

});

$routes->group('situation-compte', ['namespace' => 'App\Controllers'], function ($routes) {
    $routes->get('/', 'SituationCompteController::index');
    $routes->get('gain-total', 'SituationCompteController::getGainTotalParType');
});
