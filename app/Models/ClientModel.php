<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table = 'clients';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nom',
        'numero',
        'prefixe_id',
        'date_creation'
    ];
    protected $useTimestamps = false;
    protected $createdField = 'date_creation';
    protected $returnType = 'object';

    /**
     * Récupérer un client par son numéro de téléphone
     */
    public function findByNumero($numero)
    {
        return $this->where('numero', $numero)->first();
    }

    /**
     * Récupérer un client avec son compte et son préfixe
     */
    public function getClientWithCompte($clientId)
    {
        return $this->select('clients.*, comptes.solde, prefixes.prefixe')
                    ->join('comptes', 'comptes.client_id = clients.id', 'left')
                    ->join('prefixes', 'prefixes.id = clients.prefixe_id', 'left')
                    ->where('clients.id', $clientId)
                    ->first();
    }

    /**
     * Récupérer tous les clients avec leurs comptes
     */
    public function getAllClientsWithComptes()
    {
        return $this->select('clients.*, comptes.solde, prefixes.prefixe')
                    ->join('comptes', 'comptes.client_id = clients.id', 'left')
                    ->join('prefixes', 'prefixes.id = clients.prefixe_id', 'left')
                    ->findAll();
    }

    /**
     * Créer un nouveau client avec son compte automatiquement
     */
    public function createClientWithCompte($data)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Insérer le client
        $clientId = $this->insert($data, true);

        if ($clientId) {
            // Créer le compte associé
            $compteModel = model('CompteModel');
            $compteModel->insert([
                'client_id' => $clientId,
                'solde' => 0
            ]);
        }

        $db->transComplete();

        return $db->transStatus() ? $clientId : false;
    }

    /**
     * Mettre à jour le numéro de téléphone d'un client
     */
    public function updateNumero($clientId, $numero)
    {
        return $this->update($clientId, ['numero' => $numero]);
    }

    /**
     * Compter le nombre de clients
     */
    public function countClients()
    {
        return $this->countAllResults();
    }

    /**
     * Récupérer les clients par préfixe
     */
    public function getClientsByPrefixe($prefixeId)
    {
        return $this->where('prefixe_id', $prefixeId)->findAll();
    }

    /**
     * Vérifier si un numéro existe déjà
     */
    public function numeroExists($numero)
    {
        return $this->where('numero', $numero)->countAllResults() > 0;
    }

    /**
     * Supprimer un client et son compte associé
     */
    public function deleteClientWithCompte($clientId)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Supprimer le compte
        $compteModel = model('CompteModel');
        $compteModel->where('client_id', $clientId)->delete();

        // Supprimer le client
        $this->delete($clientId);

        $db->transComplete();

        return $db->transStatus();
    }
}