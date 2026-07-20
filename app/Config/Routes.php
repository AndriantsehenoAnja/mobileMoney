<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/produits', 'ProduitController::index');
$routes->get('/produit/(:num)', 'ProduitController::show/$1');


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
