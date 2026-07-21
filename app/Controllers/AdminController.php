<?php

namespace App\Controllers;

use App\Models\CompteModel;
use App\Models\TransactionModel;

class AdminController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $compteModel = new CompteModel();

        // 1. Solde Total des Clients
        $soldeTotal = $compteModel->selectSum('solde')->first();
        $soldeTotalClients = $soldeTotal->solde ?? 0;

        // 2. Gains Notre Réseau (Frais de base + Frais de retrait inclus)
        // On utilise COALESCE pour convertir les NULL en 0
        $gainInterneQuery = $db->table('transactions t')
            ->select('COALESCE(SUM(t.frais_base + COALESCE(t.frais_retrait_inclus, 0)), 0) as total')
            ->join('operateurs op', 'op.id = t.operateur_destination_id', 'left')
            ->groupStart()
                ->where('op.est_notre_operateur', 1)
                ->orWhere('t.operateur_destination_id IS NULL')
            ->groupEnd()
            ->get()
            ->getRow();
        
        $gainInterne = $gainInterneQuery ? (float)$gainInterneQuery->total : 0;

        // 3. Gains Opérateurs Tiers (Commissions externes + Frais de base inter-opérateurs)
        $gainExterneQuery = $db->table('transactions t')
            ->select('COALESCE(SUM(COALESCE(t.frais_base, 0) + COALESCE(t.frais_commission_externe, 0)), 0) as total')
            ->join('operateurs op', 'op.id = t.operateur_destination_id')
            ->where('op.est_notre_operateur', 0)
            ->get()
            ->getRow();

        $gainExterne = $gainExterneQuery ? (float)$gainExterneQuery->total : 0;

        // 4. À Reverser aux Opérateurs Tiers (Cumul des montants envoyés vers les autres réseaux)
        $totalAEnvoyerQuery = $db->table('transactions t')
            ->select('COALESCE(SUM(t.montant), 0) as total_a_envoyer')
            ->join('operateurs op', 'op.id = t.operateur_destination_id')
            ->where('op.est_notre_operateur', 0)
            ->where('t.type_operation_id', 4) // Transfert Inter-opérateur
            ->get()
            ->getResult(); // Renvoie un tableau d'objets pour correspondre à $totalAEnvoyerOperateurs[0]->total_a_envoyer dans la vue

        // 5. Récupération des 10 dernières transactions
        $recentTransactions = $db->table('transactions t')
            ->select('
                t.id, 
                t.date_transaction, 
                t.montant, 
                COALESCE(t.frais_total, t.frais_base, 0) as frais_total, 
                t.numero_destination, 
                tp.nom as type_operation, 
                op.nom as nom_operateur_destination,
                op.est_notre_operateur
            ')
            ->join('types_operations tp', 'tp.id = t.type_operation_id', 'left')
            ->join('operateurs op', 'op.id = t.operateur_destination_id', 'left')
            ->orderBy('t.date_transaction', 'DESC')
            ->limit(10)
            ->get()
            ->getResult();

        $data = [
            'soldeTotalClients'        => $soldeTotalClients,
            'gainsVentiles'            => [
                'interne' => $gainInterne,
                'externe' => $gainExterne
            ],
            'totalAEnvoyerOperateurs' => $totalAEnvoyerQuery,
            'recentTransactions'      => $recentTransactions
        ];

        return view('admin/dashboard', $data);
    }
}