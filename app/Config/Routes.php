<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/produits', 'ProduitController::index');
$routes->get('/produit/(:num)', 'ProduitController::show/$1');
