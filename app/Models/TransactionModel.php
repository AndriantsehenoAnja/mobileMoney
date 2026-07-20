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
        'numero_destination',        // NOUVEAU V2
        'operateur_destination_id', // NOUVEAU V2
        'montant',
        'frais',                     // frais de base
        'frais_commission_externe',  // NOUVEAU V2 (%)
        'frais_retrait_inclus',      // NOUVEAU V2 (option client)
        'frais_total',               // NOUVEAU V2
        'date_transaction'
    ];
    protected $useTimestamps = false;
    protected $returnType = 'object';

    public function getGainTotalParType()
    {
        return $this->select('type_operation_id, SUM(frais_total) as total_gain')
                    ->groupBy('type_operation_id')
                    ->findAll();
    }
    /**
     * Récupérer les transactions d'un compte avec toutes les infos
     */
        
/**
     * Calcule le gain réel généré par type d'opération (retrait, transfert, etc.)
     * en faisant la somme des frais perçus sur les transactions passées.
     */
    public function getGainTotalGlobal(){
        return $this->select('SUM(frais_total) as total_gain')
                    ->first()
                    ->total_gain ?? 0;
    }
    public function getTransactionsAvecDetails($limit)
    {
        return $this->select('transactions.*, 
                               types_operations.nom as type_operation,
                               source.id as numero_source,
                               source_client.nom as nom_source,
                               dest.id as numero_destination,
                               dest_client.nom as nom_destination')
                    ->join('types_operations', 'types_operations.id = transactions.type_operation_id')
                    ->join('comptes as source', 'source.id = transactions.compte_source', 'left')
                    ->join('clients as source_client', 'source_client.id = source.client_id', 'left')
                    ->join('comptes as dest', 'dest.id = transactions.compte_destination', 'left')
                    ->join('clients as dest_client', 'dest_client.id = dest.client_id', 'left')
                    ->orderBy('transactions.date_transaction', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    public function getMontantTotalAEnvoyerAuxOperateurs(){
        return $this->select('
                operateurs.nom as operateur,
                SUM(transactions.frais_commission_externe) as total_a_envoyer
            ')
            ->join('operateurs', 'operateurs.id = transactions.operateur_destination_id')
            ->where('operateurs.est_notre_operateur', 0) // Uniquement les opérateurs externes
            ->groupBy('transactions.operateur_destination_id')
            ->findAll();
    }
    public function getGainTotalVentile()
    {
        return $this->select('
                types_operations.nom as type_operation,
                operateurs.est_notre_operateur,
                SUM(transactions.frais_total) as total_gain
            ')
            ->join('types_operations', 'types_operations.id = transactions.type_operation_id')
            ->join('operateurs', 'operateurs.id = transactions.operateur_destination_id', 'left')
            ->groupBy('transactions.type_operation_id, operateurs.est_notre_operateur')
            ->findAll();
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

    public function getSituationMontantsParOperateur()
    {
        return $this->select('
                operateurs.nom as operateur,
                COUNT(transactions.id) as nombre_envois,
                SUM(transactions.montant) as total_montant,
                SUM(transactions.frais_commission_externe) as total_commissions
            ')
            ->join('operateurs', 'operateurs.id = transactions.operateur_destination_id')
            ->where('operateurs.est_notre_operateur', 0) // Uniquement les opérateurs externes
            ->groupBy('transactions.operateur_destination_id')
            ->findAll();
    }
}