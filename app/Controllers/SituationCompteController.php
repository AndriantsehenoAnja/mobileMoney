<?php

namespace App\Controllers;

use App\Models\CompteModel;
use App\Models\TransactionModel;

class SituationCompteController extends BaseController
{
    public function index()
    {
        $compteModel = new CompteModel();
        $situation   = $compteModel->getSituationComptes();

        return view('situation_compte/index', [
            'clientsSituation' => $situation
        ]);
    }

    /**
     * Gains globaux par type d'opération (avec support optionnel des filtres de dates)
     */
    public function getGainTotalParType()
    {
        $transactionModel = new TransactionModel();

        $dateDebut = $this->request->getGet('date_debut');
        $dateFin   = $this->request->getGet('date_fin');

        $gainTotal = $transactionModel->getGainTotalParType($dateDebut, $dateFin);

        return view('situation_compte/gain', [
            'gainTotal' => $gainTotal,
            'dateDebut' => $dateDebut,
            'dateFin'   => $dateFin,
        ]);
    }

    /**
     * Situation des gains ventilés par opérateur (Notre opérateur vs Opérateurs externes)
     */
    public function gainsParOperateur()
    {
        $transactionModel = new TransactionModel();

        // Récupération des filtres depuis la requête GET
        $dateDebut = $this->request->getGet('date_debut');
        $dateFin   = $this->request->getGet('date_fin');

        // Récupération des statistiques
        $gains = $transactionModel->getGainsParOperateur($dateDebut, $dateFin);

        return view('situation_compte/gains_par_operateur', [
            'gainsInterne'  => $gains['interne'],
            'gainsExternes' => $gains['externes'],
            'totaux'        => $gains['totaux'],
            'dateDebut'     => $dateDebut,
            'dateFin'       => $dateFin,
        ]);
    }
}