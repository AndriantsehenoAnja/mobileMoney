<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'type_operation_id',
        'compte_source',
        'compte_destination',
        'montant',
        'frais',
        'date_transaction'
    ];
    protected $useTimestamps = false;
    protected $returnType = 'object';

    /**
     * Récupérer les transactions d'un compte avec toutes les infos
     */
        // getGainTotalParType
/**
     * Calcule le gain réel généré par type d'opération (retrait, transfert, etc.)
     * en faisant la somme des frais perçus sur les transactions passées.
     */

/**
 * Calcule les frais et commissions par opérateur avec filtrage par période.
 */
    public function getGainsParOperateur(?string $dateDebut = null, ?string $dateFin = null): array
    {
        // --- 1. Gains sur notre propre réseau (Opérations internes) ---
        $builderInterne = $this->db->table('transactions t')
            ->select('SUM(t.frais) as total_frais, COUNT(t.id) as nombre_transactions')
            ->where('t.est_inter_operateur', 0);

        if (!empty($dateDebut)) {
            $builderInterne->where('t.date_transaction >=', $dateDebut . ' 00:00:00');
        }
        if (!empty($dateFin)) {
            $builderInterne->where('t.date_transaction <=', $dateFin . ' 23:59:59');
        }

        $resInterne = $builderInterne->get()->getRowArray();
        $fraisInterne = (float)($resInterne['total_frais'] ?? 0);
        $countInterne = (int)($resInterne['nombre_transactions'] ?? 0);

        // --- 2. Gains sur les réseaux externes (Inter-opérateurs) ---
        $builderExterne = $this->db->table('transactions t')
            ->select('
                op.id as operateur_id,
                op.nom as operateur_nom,
                COUNT(t.id) as nombre_transactions,
                SUM(t.frais) as total_frais,
                SUM(t.commission_inter_operateur) as total_commission
            ')
            ->join('operateurs op', 'op.id = t.operateur_destination_id', 'left')
            ->where('t.est_inter_operateur', 1)
            ->groupBy('t.operateur_destination_id, op.id, op.nom');

        if (!empty($dateDebut)) {
            $builderExterne->where('t.date_transaction >=', $dateDebut . ' 00:00:00');
        }
        if (!empty($dateFin)) {
            $builderExterne->where('t.date_transaction <=', $dateFin . ' 23:59:59');
        }

        $resExternes = $builderExterne->get()->getResultArray();

        // Calcul des totaux externes
        $totalFraisExterne = 0;
        $totalCommissionExterne = 0;
        $totalCountExterne = 0;

        foreach ($resExternes as $ext) {
            $totalFraisExterne      += (float)$ext['total_frais'];
            $totalCommissionExterne += (float)$ext['total_commission'];
            $totalCountExterne      += (int)$ext['nombre_transactions'];
        }

        return [
            'interne' => [
                'nombre_transactions' => $countInterne,
                'frais'               => $fraisInterne,
                'total_gain'          => $fraisInterne
            ],
            'externes' => $resExternes,
            'totaux'   => [
                'frais_interne'            => $fraisInterne,
                'frais_externe'            => $totalFraisExterne,
                'commission_externe'       => $totalCommissionExterne,
                'total_frais_global'       => $fraisInterne + $totalFraisExterne,
                'total_commission_global'  => $totalCommissionExterne,
                'gain_net_global'          => $fraisInterne + $totalFraisExterne + $totalCommissionExterne,
                'total_transactions'       => $countInterne + $totalCountExterne
            ]
        ];
    }
    public function getGainTotalParType()
    {
        return $this->select('types_operations.nom as type_operation, SUM(transactions.frais) as total_gain')
                    ->join('types_operations', 'types_operations.id = transactions.type_operation_id')
                    ->groupBy('transactions.type_operation_id, types_operations.nom')
                    ->findAll(); // Retourne un tableau d'objets avec type_operation et total_gain
    } 
    public function getTransactionsByCompte($compteId, $limit = null, $offset = 0)
    {
        $query = $this->select('transactions.*, 
                               types_operations.nom as type_operation,
                               source.numero as numero_source,
                               source_client.nom as nom_source,
                               dest.numero as numero_destination,
                               dest_client.nom as nom_destination')
                       ->join('types_operations', 'types_operations.id = transactions.type_operation_id')
                       ->join('comptes as source', 'source.id = transactions.compte_source', 'left')
                       ->join('clients as source_client', 'source_client.id = source.client_id', 'left')
                       ->join('comptes as dest', 'dest.id = transactions.compte_destination', 'left')
                       ->join('clients as dest_client', 'dest_client.id = dest.client_id', 'left')
                       ->where('transactions.compte_source', $compteId)
                       ->orWhere('transactions.compte_destination', $compteId)
                       ->orderBy('transactions.date_transaction', 'DESC');

        if ($limit !== null) {
            $query->limit($limit, $offset);
        }

        return $query->findAll();
    }

    /**
     * Récupérer les transactions d'un client
     */
    public function getTransactionsByClient($clientId, $limit = null, $offset = 0)
    {
        // Récupérer le compte du client
        $compteModel = model('CompteModel');
        $compte = $compteModel->findByClientId($clientId);

        if (!$compte) {
            return [];
        }

        return $this->getTransactionsByCompte($compte->id, $limit, $offset);
    }

    /**
     * Récupérer les transactions par type
     */
    public function getTransactionsByType($typeOperationId, $limit = null)
    {
        $query = $this->where('type_operation_id', $typeOperationId)
                      ->orderBy('date_transaction', 'DESC');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->findAll();
    }

    /**
     * Récupérer les transactions entre deux dates
     */
    public function getTransactionsByDateRange($dateDebut, $dateFin)
    {
        return $this->where('date_transaction >=', $dateDebut)
                    ->where('date_transaction <=', $dateFin)
                    ->orderBy('date_transaction', 'DESC')
                    ->findAll();
    }

    /**
     * Créer une nouvelle transaction
     */
    public function createTransaction($data)
    {
        return $this->insert($data, true);
    }

    /**
     * Créer un dépôt
     */
    public function createDepot($compteId, $montant, $frais = 0)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Créditer le compte
        $compteModel = model('CompteModel');
        $compteModel->crediter($compteId, $montant);

        // Récupérer le type d'opération "depot"
        $typeOperationModel = model('TypeOperationModel');
        $typeDepot = $typeOperationModel->where('nom', 'depot')->first();

        if (!$typeDepot) {
            $db->transRollback();
            return false;
        }

        // Créer la transaction
        $transactionId = $this->insert([
            'type_operation_id' => $typeDepot->id,
            'compte_source' => null,
            'compte_destination' => $compteId,
            'montant' => $montant,
            'frais' => $frais
        ], true);

        $db->transComplete();

        return $db->transStatus() ? $transactionId : false;
    }

    /**
     * Créer un retrait
     */
    public function createRetrait($compteId, $montant, $frais = 0)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Vérifier le solde
        $compteModel = model('CompteModel');
        if (!$compteModel->soldeSuffisant($compteId, $montant + $frais)) {
            $db->transRollback();
            return false;
        }

        // Débiter le compte
        $compteModel->debiter($compteId, $montant + $frais);

        // Récupérer le type d'opération "retrait"
        $typeOperationModel = model('TypeOperationModel');
        $typeRetrait = $typeOperationModel->where('nom', 'retrait')->first();

        if (!$typeRetrait) {
            $db->transRollback();
            return false;
        }

        // Créer la transaction
        $transactionId = $this->insert([
            'type_operation_id' => $typeRetrait->id,
            'compte_source' => $compteId,
            'compte_destination' => null,
            'montant' => $montant,
            'frais' => $frais
        ], true);

        $db->transComplete();

        return $db->transStatus() ? $transactionId : false;
    }

    /**
     * Créer un transfert entre deux comptes
     */
    public function createTransfert($compteSourceId, $compteDestinationId, $montant, $frais = 0)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $compteModel = model('CompteModel');

        // Vérifier le solde du compte source
        if (!$compteModel->soldeSuffisant($compteSourceId, $montant + $frais)) {
            $db->transRollback();
            return false;
        }

        // Débiter le compte source
        $compteModel->debiter($compteSourceId, $montant + $frais);

        // Créditer le compte destination
        $compteModel->crediter($compteDestinationId, $montant);

        // Récupérer le type d'opération "transfert"
        $typeOperationModel = model('TypeOperationModel');
        $typeTransfert = $typeOperationModel->where('nom', 'transfert')->first();

        if (!$typeTransfert) {
            $db->transRollback();
            return false;
        }

        // Créer la transaction
        $transactionId = $this->insert([
            'type_operation_id' => $typeTransfert->id,
            'compte_source' => $compteSourceId,
            'compte_destination' => $compteDestinationId,
            'montant' => $montant,
            'frais' => $frais
        ], true);

        $db->transComplete();

        return $db->transStatus() ? $transactionId : false;
    }

    /**
     * Calculer le total des frais pour une période donnée
     */
    public function getTotalFraisByPeriode($dateDebut, $dateFin)
    {
        $result = $this->select('SUM(frais) as total')
                       ->where('date_transaction >=', $dateDebut)
                       ->where('date_transaction <=', $dateFin)
                       ->first();

        return $result ? $result->total : 0;
    }

    /**
     * Calculer le total des transactions par type pour une période
     */
    public function getTotalByTypeAndPeriode($typeOperationId, $dateDebut, $dateFin)
    {
        $result = $this->select('SUM(montant) as total')
                       ->where('type_operation_id', $typeOperationId)
                       ->where('date_transaction >=', $dateDebut)
                       ->where('date_transaction <=', $dateFin)
                       ->first();

        return $result ? $result->total : 0;
    }

    /**
     * Compter les transactions par type pour une période
     */
    public function countTransactionsByType($typeOperationId, $dateDebut, $dateFin)
    {
        return $this->where('type_operation_id', $typeOperationId)
                    ->where('date_transaction >=', $dateDebut)
                    ->where('date_transaction <=', $dateFin)
                    ->countAllResults();
    }

    /**
     * Récupérer le montant total des transactions d'un client
     */
    public function getTotalTransactionsByClient($clientId, $typeOperationId = null)
    {
        $compteModel = model('CompteModel');
        $compte = $compteModel->findByClientId($clientId);

        if (!$compte) {
            return 0;
        }

        $query = $this->where('compte_source', $compte->id)
                      ->orWhere('compte_destination', $compte->id);

        if ($typeOperationId !== null) {
            $query->where('type_operation_id', $typeOperationId);
        }

        $result = $query->select('SUM(montant) as total')->first();

        return $result ? $result->total : 0;
    }

    /**
     * Récupérer les transactions avec pagination
     */
    public function getTransactionsPaginated($compteId, $perPage = 10, $page = 1)
    {
        $offset = ($page - 1) * $perPage;
        $transactions = $this->getTransactionsByCompte($compteId, $perPage, $offset);
        $total = $this->where('compte_source', $compteId)
                      ->orWhere('compte_destination', $compteId)
                      ->countAllResults();

        return [
            'transactions' => $transactions,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Récupérer le nombre total de transactions
     */
    public function countTransactions()
    {
        return $this->countAllResults();
    }

    /**
     * Supprimer les transactions d'un compte
     */
    public function deleteTransactionsByCompte($compteId)
    {
        return $this->where('compte_source', $compteId)
                    ->orWhere('compte_destination', $compteId)
                    ->delete();
    }

    /**
     * Récupérer le solde d'un compte à partir des transactions (méthode alternative)
     */
    public function getSoldeFromTransactions($compteId)
    {
        // Récupérer toutes les transactions du compte
        $transactions = $this->where('compte_source', $compteId)
                             ->orWhere('compte_destination', $compteId)
                             ->findAll();

        $solde = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->compte_destination == $compteId) {
                $solde += $transaction->montant;
            }
            if ($transaction->compte_source == $compteId) {
                $solde -= ($transaction->montant + $transaction->frais);
            }
        }

        return $solde;
    }
}