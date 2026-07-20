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
$routes->get('login/verify', 'LoginController::verifierNumero');