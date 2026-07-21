<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\OperateurModel;
use App\Models\CompteModel;

class SituationCompteController extends BaseController
{
    /**
     * Page principale : Synthèse de la situation du compte et des gains V2
     */
    public function index()
    {
        // $session = session();
        // if (!$session->get('admin_logged_in')) {
        //     return redirect()->to('/login/admin');
        // }

        $transactionModel = new TransactionModel();
        $operateurModel   = new OperateurModel();
        $compteModel      = new CompteModel();

        // 1. Total général des gains (Frais perçus)
        $gainTotalGlobal = $transactionModel->getGainTotalGlobal();

        // 2. V2 : Ventilation des gains (Notre réseau vs Autres Opérateurs)
        $gainsVentiles = $transactionModel->getGainTotalVentile(); 
        // Exemple de retour attendu : ['interne' => X, 'externe' => Y]

        // 3. Gains détaillés par type d'opération (Dépôt, Retrait, Transfert, etc.)
        $gainsParType = $transactionModel->getGainTotalParType();

        // 4. V2 : Situation des montants à envoyer à chaque opérateur
        $montantsAEnvoyerParOperateur = $transactionModel->getSituationMontantsParOperateur();

        // 5. Total du solde des comptes clients en circulation
        $soldeTotalClients = $compteModel->selectSum('solde')->first()->solde ?? 0;

        $data = [
            'title'                        => 'Situation de Compte V2',
            'page_title'                   => 'Situation Globale et Gain des Frais',
            'current_page'                 => 'situation',
            'gainTotalGlobal'              => $gainTotalGlobal,
            'gainsVentiles'                => $gainsVentiles,
            'gainsParType'                 => $gainsParType,
            'montantsAEnvoyerParOperateur' => $montantsAEnvoyerParOperateur,
            'soldeTotalClients'            => $soldeTotalClients
        ];

        return view('admin/situation_compte/index', $data);
    }

    /**
     * Vue détaillée dédiée uniquement à la situation des opérateurs externes (V2)
     */
    public function operateurs()
    {
        // $session = session();
        // if (!$session->get('admin_logged_in')) {
        //     return redirect()->to('/login/admin');
        // }

        $transactionModel = new TransactionModel();

        // Ventilation des gains et situation des montants dus
        $gainsVentiles                = $transactionModel->getGainTotalVentile();
        $montantsAEnvoyerParOperateur = $transactionModel->getSituationMontantsParOperateur();

        $data = [
            'title'                        => 'Situation par Opérateur',
            'page_title'                   => 'Montants à envoyer aux autres opérateurs',
            'current_page'                 => 'situation_operateurs',
            'gainsVentiles'                => $gainsVentiles,
            'montantsAEnvoyerParOperateur' => $montantsAEnvoyerParOperateur
        ];

        return view('admin/situation_compte/operateurs', $data);
    }
}