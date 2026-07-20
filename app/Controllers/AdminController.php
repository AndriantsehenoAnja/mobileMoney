<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\CompteModel;
use App\Models\TransactionModel;
use App\Models\OperateurModel;

class AdminController extends BaseController
{
    /**
     * Dashboard Administrateur / Opérateur (V2)
     */
    public function index()
    {
        $session = session();
        if (!$session->get('admin_logged_in')) {
            return redirect()->to('/login/admin');
        }

        $clientModel      = new ClientModel();
        $compteModel      = new CompteModel();
        $transactionModel = new TransactionModel();
        $operateurModel   = new OperateurModel();

        // Statistiques générales
        $totalClients      = $clientModel->countAll();
        $totalComptes      = $compteModel->countAll();
        $soldeTotalClients = $compteModel->selectSum('solde')->first()['solde'] ?? 0;

        // V2 : Gains ventilés (Notre opérateur vs Autres)
        $gainsVentiles = $transactionModel->getGainTotalVentile();
        
        // V2 : Total cumulé des montants dus/à envoyer aux opérateurs tiers
        $totalAEnvoyerOperateurs = $transactionModel->getMontantTotalAEnvoyerAuxOperateurs();

        // Dernières transactions récentes
        $recentTransactions = $transactionModel->getTransactionsAvecDetails(10);

        $data = [
            'title'                   => 'Tableau de Bord - Administration',
            'page_title'              => 'Tableau de bord V2',
            'current_page'            => 'dashboard',
            'totalClients'            => $totalClients,
            'totalComptes'            => $totalComptes,
            'soldeTotalClients'       => $soldeTotalClients,
            'gainsVentiles'           => $gainsVentiles,
            'totalAEnvoyerOperateurs' => $totalAEnvoyerOperateurs,
            'recentTransactions'      => $recentTransactions
        ];

        return view('admin/dashboard', $data);
    }
}