<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'LoginController::aller');

// ==========================================
// 2. AUTHENTIFICATION CLIENT
// ==========================================
$routes->get('loginClient', 'LoginController::index');
$routes->get('login', 'LoginController::index');
$routes->post('login/authenticate', 'LoginController::login');
$routes->get('logout', 'LoginController::logout');

// ==========================================
// 3. ESPACE CLIENT (Protection avec filtre 'auth')
// ==========================================
$routes->group('client', ['filter' => 'auth'], function ($routes) {
    // Accueil / Espace Personnel Client
    $routes->get('/', 'ClientController::index');

    // Dépôt
    $routes->get('depot', 'ClientController::depot');
    $routes->post('depot/effectuer', 'ClientController::effectuerDepot');

    // Retrait
    $routes->get('retrait', 'ClientController::retrait');
    $routes->post('retrait/effectuer', 'ClientController::effectuerRetrait');

    // Transfert (Interne & Inter-opérateurs V2)
    $routes->get('transfert', 'ClientController::transfert');
    $routes->get('verifier-destinataire', 'ClientController::verifierDestinataire');
    $routes->post('transfert/effectuer', 'ClientController::effectuerTransfert');
    // Multiple transfert
    $routes->get('transfert-multiple', 'ClientController::transfertMultiple');
    $routes->post('transfert-multiple/effectuer', 'ClientController::effectuerTransfertMultiple');

    // Historique des opérations
    $routes->get('historique', 'ClientController::historique');
});

// Alias pour compatibilité au cas où les vues Client appellent sans le préfixe /client/
$routes->get('depot', 'ClientController::depot', ['filter' => 'auth']);
$routes->post('depot/effectuer', 'ClientController::effectuerDepot', ['filter' => 'auth']);
$routes->get('retrait', 'ClientController::retrait', ['filter' => 'auth']);
$routes->post('retrait/effectuer', 'ClientController::effectuerRetrait', ['filter' => 'auth']);
$routes->get('transfert', 'ClientController::transfert', ['filter' => 'auth']);
$routes->get('verifier-destinataire', 'ClientController::verifierDestinataire', ['filter' => 'auth']);
$routes->post('transfert/effectuer', 'ClientController::effectuerTransfert', ['filter' => 'auth']);
$routes->get('historique', 'ClientController::historique', ['filter' => 'auth']);


// ==========================================
// 4. ESPACE ADMINISTRATEUR (Accès direct via index.php)
// ==========================================
$routes->group('admin', ['namespace' => 'App\Controllers'], function ($routes) {

    // Dashboard principal Admin
    $routes->get('/', 'AdminController::index');

    // --------------------------------------
    // A. Gestion des Préfixes (034, 032, 033...)
    // --------------------------------------
    $routes->group('prefix', function ($routes) {
        $routes->get('/', 'PrefixController::index');
        $routes->get('form', 'PrefixController::form');
        $routes->post('create', 'PrefixController::create');
        $routes->get('edit/(:num)', 'PrefixController::edit/$1');
        $routes->post('update/(:num)', 'PrefixController::update/$1');
        $routes->get('delete/(:num)', 'PrefixController::delete/$1');
    });

    // --------------------------------------
    // B. Types d'opérations
    // --------------------------------------
    $routes->group('type-operation', function ($routes) {
        $routes->get('/', 'TypeOperationController::index');
    });

    // --------------------------------------
    // C. Baremes de Frais
    // --------------------------------------
    $routes->group('bareme', function ($routes) {
        $routes->get('/', 'BaremeController::index');
        $routes->get('addbareme', 'BaremeController::form');
        $routes->post('addbareme', 'BaremeController::addBareme');
        $routes->get('edit/(:num)', 'BaremeController::edit/$1');
        $routes->post('update/(:num)', 'BaremeController::update/$1');
        $routes->get('showbytypeoperation/(:num)', 'BaremeController::showbyTypeOperation/$1');
    });

    // --------------------------------------
    // D. Situation des Comptes & Gains (V2)
    // --------------------------------------
    $routes->group('situation-compte', function ($routes) {
        $routes->get('/', 'SituationCompteController::index');
        $routes->get('gain-total', 'SituationCompteController::getGainTotalParType');
        $routes->get('gain', 'SituationCompteController::getGainTotalParType'); // Alias pour le lien gain.php
        $routes->get('operateurs', 'SituationCompteController::getSoldeOperateurs'); // Route V2 (Versement réseaux tiers)
    });

});