<?php

namespace App\Models;

use CodeIgniter\Model;

class CompteModel extends Model
{
    protected $table = 'comptes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'client_id',
        'solde'
    ];
    protected $returnType = 'object';
    
    public function getSituationComptes()
    {
        // On utilise la table principale définie ($this->table qui est 'comptes')
        return $this->select('comptes.id as compte_id, comptes.solde, clients.nom, clients.numero, prefixes.prefixe, clients.date_creation')
                    ->join('clients', 'clients.id = comptes.client_id', 'right') // RIGHT ou LEFT selon si on veut afficher même les clients sans compte
                    ->join('prefixes', 'prefixes.id = clients.prefixe_id', 'left')
                    ->findAll(); // Retournera un tableau d'objets grâce à $returnType = 'object'
    }


    /**
     * Récupérer un compte par ID client
     */
    public function findByClientId($clientId)
    {
        return $this->where('client_id', $clientId)->first();
    }

    /**
     * Récupérer un compte avec les informations du client
    */
    public function getCompteWithClient($compteId)
    {
        return $this->select('comptes.*, clients.nom, clients.numero, prefixes.prefixe')
                    ->join('clients', 'clients.id = comptes.client_id')
                    ->join('prefixes', 'prefixes.id = clients.prefixe_id', 'left')
                    ->where('comptes.id', $compteId)
                    ->first();
    }


    /**
     * Récupérer un compte par numéro client
     */
    public function findByClientNumero($numero)
    {
        return $this->select('comptes.*, clients.nom, clients.numero')
                    ->join('clients', 'clients.id = comptes.client_id')
                    ->where('clients.numero', $numero)
                    ->first();
    }

    /**
     * Créditer un compte (SQLite compatible)
     */
    public function crediter($compteId, $montant)
    {
        // Récupérer le compte actuel
        $compte = $this->find($compteId);
        if (!$compte) {
            return false;
        }

        // Calculer le nouveau solde
        $nouveauSolde = $compte->solde + $montant;

        // Mettre à jour
        return $this->update($compteId, ['solde' => $nouveauSolde]);
    }

    /**
     * Débiter un compte (SQLite compatible)
     */
    public function debiter($compteId, $montant)
    {
        // Récupérer le compte actuel
        $compte = $this->find($compteId);
        if (!$compte) {
            return false;
        }

        // Vérifier si le solde est suffisant
        if ($compte->solde < $montant) {
            return false;
        }

        // Calculer le nouveau solde
        $nouveauSolde = $compte->solde - $montant;

        // Mettre à jour
        return $this->update($compteId, ['solde' => $nouveauSolde]);
    }

    /**
     * Vérifier si le solde est suffisant
     */
    public function soldeSuffisant($compteId, $montant)
    {
        $compte = $this->find($compteId);
        return $compte && $compte->solde >= $montant;
    }

    /**
     * Obtenir le solde d'un compte
     */
    public function getSolde($compteId)
    {
        $compte = $this->find($compteId);
        return $compte ? $compte->solde : null;
    }

    /**
     * Récupérer les comptes avec solde minimum
     */
    public function getComptesBySoldeMin($montant)
    {
        return $this->where('solde >=', $montant)->findAll();
    }

    /**
     * Récupérer les comptes avec solde maximum
     */
    public function getComptesBySoldeMax($montant)
    {
        return $this->where('solde <=', $montant)->findAll();
    }

    /**
     * Calculer le solde total de tous les comptes
     */
    public function getSoldeTotal()
    {
        $result = $this->select('SUM(solde) as total')->first();
        return $result ? $result->total : 0;
    }

    /**
     * Récupérer le nombre de comptes
     */
    public function countComptes()
    {
        return $this->countAllResults();
    }

    /**
     * Vérifier si un compte existe pour un client
     */
    public function compteExists($clientId)
    {
        return $this->where('client_id', $clientId)->countAllResults() > 0;
    }

    /**
     * Transférer de l'argent entre deux comptes (SQLite compatible)
     */
    public function transferer($compteSourceId, $compteDestinationId, $montant)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Débiter le compte source
        $debit = $this->debiter($compteSourceId, $montant);

        // Créditer le compte destination
        $credit = $this->crediter($compteDestinationId, $montant);

        $db->transComplete();

        return $db->transStatus() && $debit && $credit;
    }

    /**
     * Ajouter un montant au solde (méthode alternative pour SQLite)
     */
    public function addToSolde($compteId, $montant)
    {
        return $this->crediter($compteId, $montant);
    }

    /**
     * Soustraire un montant du solde (méthode alternative pour SQLite)
     */
    public function subtractFromSolde($compteId, $montant)
    {
        return $this->debiter($compteId, $montant);
    }
}