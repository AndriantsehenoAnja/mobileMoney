<?php

namespace App\Controllers;

use App\Models\CompteModel;
use App\Models\TransactionModel;

class AdminController extends BaseController
{
    public function index()
    {
        $compteModel = new CompteModel();
        $transactionModel = new TransactionModel();

        // 1. Récupération des indicateurs globaux
        $masseMonetaire = $compteModel->getSoldeTotal();
        $nombreComptes   = $compteModel->countComptes();
        
        // 2. Calcul des gains totaux
        $gainsParType = $transactionModel->getGainTotalParType();
        $gainGlobal = 0;
        foreach ($gainsParType as $gain) {
            $gainGlobal += $gain->total_gain;
        }

        // 3. Récupérer les dernières transactions (à ajouter dans votre TransactionModel si besoin)
        $dernieresTransactions = $transactionModel->select('transactions.*, types_operations.nom as type_nom')
                                                 ->join('types_operations', 'types_operations.id = transactions.type_operation_id')
                                                 ->orderBy('transactions.id', 'DESC')
                                                 ->findAll(5); // Limité aux 5 dernières

        // Envoi de toutes les statistiques à la vue
        return view('admin/dashboard', [
            'masseMonetaire' => $masseMonetaire,
            'nombreComptes'   => $nombreComptes,
            'gainGlobal'     => $gainGlobal,
            'dernieresTransactions' => $dernieresTransactions
        ]);
    }
}