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
     * Vérifier un numéro de téléphone client
     * @param string $numero
     * @return array
     */
    public function verifierNumeroClient($numero)
    {
        $numero = $this->nettoyerNumero($numero);
        
        if (strlen($numero) !== 10) {
            return [
                'valid' => false,
                'message' => 'Le numéro doit contenir exactement 10 chiffres',
                'client' => null,
                'prefixe' => null,
                'client_id' => null,
                'client_nom' => null,
                'client_numero' => null
            ];
        }

        $prefixe = substr($numero, 0, 3);
        
        $prefixeModel = new \App\Models\PrefixModel();
        $prefixeInfo = $prefixeModel->findByPrefixe($prefixe);
        
        if (!$prefixeInfo) {
            return [
                'valid' => false,
                'message' => "Le préfixe '$prefixe' n'est pas autorisé",
                'client' => null,
                'prefixe' => null,
                'client_id' => null,
                'client_nom' => null,
                'client_numero' => null
            ];
        }

        $prefixeValue = is_array($prefixeInfo) ? $prefixeInfo['prefixe'] : $prefixeInfo->prefixe;
        $prefixeId = is_array($prefixeInfo) ? $prefixeInfo['id'] : $prefixeInfo->id;

        $client = $this->findByNumero($numero);
        
        if (!$client) {
            return [
                'valid' => false,
                'message' => "Aucun client trouvé avec le numéro '$numero'",
                'client' => null,
                'prefixe' => $prefixeValue,
                'client_id' => null,
                'client_nom' => null,
                'client_numero' => null
            ];
        }

        return [
            'valid' => true,
            'message' => 'Numéro valide',
            'client' => $client,
            'prefixe' => $prefixeValue,
            'client_id' => $client->id,
            'client_nom' => $client->nom,
            'client_numero' => $client->numero
        ];
    }

    /**
     * Vérifier si un numéro existe et retourner le client
     */
    public function verifierEtGetClient($numero)
    {
        $result = $this->verifierNumeroClient($numero);
        return $result['valid'] ? $result['client'] : null;
    }

    /**
     * Vérifier uniquement si le numéro est valide
     */
    public function isNumeroValide($numero)
    {
        $result = $this->verifierNumeroClient($numero);
        return $result['valid'];
    }

    /**
     * Vérifier si un préfixe est autorisé
     */
    public function isPrefixeAutorise($prefixe)
    {
        $prefixeModel = new \App\Models\PrefixModel();
        return $prefixeModel->prefixeExists($prefixe);
    }

    /**
     * Nettoyer un numéro de téléphone
     */
    private function nettoyerNumero($numero)
    {
        $numero = preg_replace('/[\s\-\.\(\)]/', '', $numero);
        
        if (strpos($numero, '0') === 0) {
            return $numero;
        }
        
        if (strpos($numero, '+261') === 0) {
            return '0' . substr($numero, 4);
        }
        
        if (strpos($numero, '261') === 0) {
            return '0' . substr($numero, 3);
        }
        
        return $numero;
    }

    /**
     * Formater un numéro de téléphone
     */
    public function formaterNumero($numero, $format = 'standard')
    {
        $numero = $this->nettoyerNumero($numero);
        
        if ($format === 'international') {
            return '+261 ' . substr($numero, 1, 2) . ' ' . 
                   substr($numero, 3, 2) . ' ' . 
                   substr($numero, 5, 2) . ' ' . 
                   substr($numero, 7, 2) . ' ' . 
                   substr($numero, 9, 2);
        }
        
        return substr($numero, 0, 3) . ' ' . 
               substr($numero, 3, 2) . ' ' . 
               substr($numero, 5, 2) . ' ' . 
               substr($numero, 7, 2) . ' ' . 
               substr($numero, 9, 2);
    }

    /**
     * Valider le format d'un numéro de téléphone
     */
    public function validerFormatNumero($numero)
    {
        $numero = $this->nettoyerNumero($numero);
        return preg_match('/^0[3-9][0-9]{8}$/', $numero) === 1;
    }

    public function validerNumerosEnvoiMultiple(array $numeros)
    {
        $prefixeModel = new \App\Models\PrefixModel();
        
        foreach ($numeros as $numero) {
            $cleanNum = $this->nettoyerNumero($numero);
            $pref = substr($cleanNum, 0, 3);
            
            // Vérifier si le préfixe est valide et appartient à NOTRE opérateur
            if (!$prefixeModel->estNotrePrefixe($pref)) {
                return [
                    'valide' => false,
                    'message' => "Le numéro $numero n'appartient pas à notre réseau."
                ];
            }
        }
        return ['valide' => true];
    }

}