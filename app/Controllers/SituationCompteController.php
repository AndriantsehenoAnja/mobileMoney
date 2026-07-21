<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\OperateurModel;
use App\Models\CompteModel;

class SituationCompteController extends BaseController
{
    protected $transactionModel;
    protected $operateurModel;
    protected $compteModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->operateurModel   = new OperateurModel();
        $this->compteModel      = new CompteModel();
    }

    // Situation générale des comptes
public function index()
{
    // On sélectionne tous les champs attendus par la vue avec leurs bons alias
    $data['clientsSituation'] = $this->compteModel
        ->select('
            comptes.id as compte_id,
            comptes.solde,
            clients.nom,
            clients.numero,
            clients.date_creation,
            prefixes.prefixe,
            operateurs.nom as nom_operateur
        ')
        ->join('clients', 'clients.id = comptes.client_id')
        ->join('prefixes', 'prefixes.id = clients.prefixe_id', 'left')
        ->join('operateurs', 'operateurs.id = prefixes.operateur_id', 'left')
        ->findAll();

    return view('admin/situation_compte/index', $data);
}
    // Situation des gains ventilée par type et séparant Notre Réseau des Autres Opérateurs (V2)
    public function getGainTotalParType()
    {
        $db = \Config\Database::connect();

        // 1. Gains issus de NOTRE RÉSEAU :
        // - Opérations internes/locales (Dépôt, Retrait, Transfert Interne)
        // - On comptabilise : frais_base + frais_retrait_inclus
        $gainsNotreReseau = $db->table('transactions t')
            ->select('
                t.type_operation_id,
                tp.nom as type_nom,
                SUM(t.frais_base) as total_frais_base,
                SUM(t.frais_retrait_inclus) as total_frais_retrait_inclus,
                SUM(t.frais_base + t.frais_retrait_inclus) as gain_total
            ')
            ->join('types_operations tp', 'tp.id = t.type_operation_id')
            ->join('operateurs op', 'op.id = t.operateur_destination_id', 'left')
            ->groupStart()
                ->where('op.est_notre_operateur', 1)
                ->orWhere('t.operateur_destination_id IS NULL')
            ->groupEnd()
            ->groupBy('t.type_operation_id, tp.nom')
            ->get()
            ->getResultArray();

        // 2. Gains issus des AUTRES OPÉRATEURS :
        // - Transferts Inter-opérateurs
        // - On comptabilise : frais_base + frais_commission_externe
        $gainsAutresOperateurs = $db->table('transactions t')
            ->select('
                op.nom as operateur_nom,
                SUM(t.frais_base) as total_frais_base,
                SUM(t.frais_commission_externe) as total_commission_externe,
                SUM(t.frais_base + t.frais_commission_externe) as gain_total
            ')
            ->join('operateurs op', 'op.id = t.operateur_destination_id')
            ->where('op.est_notre_operateur', 0)
            ->groupBy('op.id, op.nom')
            ->get()
            ->getResultArray();

        // Calcul des totaux
        $totalNotreReseau = 0;
        foreach ($gainsNotreReseau as $g) {
            $totalNotreReseau += (float)$g['gain_total'];
        }

        $totalAutresOperateurs = 0;
        foreach ($gainsAutresOperateurs as $ga) {
            $totalAutresOperateurs += (float)$ga['gain_total'];
        }

        $data = [
            'gainsNotreReseau'      => $gainsNotreReseau,
            'gainsAutresOperateurs' => $gainsAutresOperateurs,
            'totalNotreReseau'      => $totalNotreReseau,
            'totalAutresOperateurs' => $totalAutresOperateurs,
            'grandTotal'            => $totalNotreReseau + $totalAutresOperateurs
        ];

        return view('admin/situation_compte/gain', $data);
    }
    // Situation des montants cumulés à envoyer/reverser à chaque opérateur (V2)
    public function operateurs()
    {
        $db = \Config\Database::connect();

        // Requête cumulant les montants principaux transférés + la commission externe retenue par opérateur partenaire
        $situationOperateurs = $db->table('operateurs op')
            ->select('
                op.id,
                op.nom as operateur_nom,
                op.commission,
                COUNT(t.id) as nombre_transactions,
                COALESCE(SUM(t.montant), 0) as total_montant_envoye,
                COALESCE(SUM(t.frais_commission_externe), 0) as total_commission
            ')
            ->join('transactions t', 't.operateur_destination_id = op.id AND t.type_operation_id = 4', 'left')
            ->where('op.est_notre_operateur', 0)
            ->groupBy('op.id, op.nom, op.commission')
            ->get()
            ->getResultArray();

        $data['situationOperateurs'] = $situationOperateurs;

        return view('admin/situation_compte/operateurs', $data);
    }
}