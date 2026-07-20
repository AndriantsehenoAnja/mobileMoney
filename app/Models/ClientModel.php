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
                'prefixe' => null
            ];
        }

        // Extraire le préfixe (les 3 premiers chiffres)
        $prefixe = substr($numero, 0, 3);
        
        // Vérifier si le préfixe est autorisé
        $prefixeModel = model('PrefixeModel');
        $prefixeInfo = $prefixeModel->findByPrefixe($prefixe);
        
        if (!$prefixeInfo) {
            return [
                'valid' => false,
                'message' => "Le préfixe '$prefixe' n'est pas autorisé",
                'client' => null,
                'prefixe' => null
            ];
        }

        // Vérifier si le client existe avec ce numéro
        $client = $this->findByNumero($numero);
        
        if (!$client) {
            return [
                'valid' => false,
                'message' => "Aucun client trouvé avec le numéro '$numero'",
                'client' => null,
                'prefixe' => $prefixeInfo->prefixe
            ];
        }

        // Tout est valide
        return [
            'valid' => true,
            'message' => 'Client valide',
            'client' => $client,
            'prefixe' => $prefixeInfo->prefixe
        ];
    }

    /**
     * Vérifier si un numéro existe et retourner le client
     * @param string $numero
     * @return object|null Le client ou null si non trouvé
     */
    public function verifierEtGetClient($numero)
    {
        $result = $this->verifierNumeroClient($numero);
        return $result['valid'] ? $result['client'] : null;
    }

    /**
     * Vérifier uniquement si le numéro est valide (existe et préfixe autorisé)
     * @param string $numero
     * @return bool
     */
    public function isNumeroValide($numero)
    {
        $result = $this->verifierNumeroClient($numero);
        return $result['valid'];
    }

    /**
     * Récupérer un client avec validation du préfixe
     * @param string $numero
     * @return object|null
     */
    public function getClientWithValidation($numero)
    {
        $result = $this->verifierNumeroClient($numero);
        if ($result['valid'] && $result['client']) {
            // Ajouter le préfixe aux données du client
            $result['client']->prefixe_valide = $result['prefixe'];
            return $result['client'];
        }
        return null;
    }

    /**
     * Vérifier si un préfixe est autorisé
     * @param string $prefixe
     * @return bool
     */
    public function isPrefixeAutorise($prefixe)
    {
        $prefixeModel = model('PrefixeModel');
        return $prefixeModel->prefixeExists($prefixe);
    }

    /**
     * Nettoyer un numéro de téléphone
     * @param string $numero
     * @return string
     */
    private function nettoyerNumero($numero)
    {
        // Enlever les espaces, tirets, points, etc.
        $numero = preg_replace('/[\s\-\.\(\)]/', '', $numero);
        
        // Si le numéro commence par 0, on le garde
        if (strpos($numero, '0') === 0) {
            return $numero;
        }
        
        // Si le numéro commence par +261, on le convertit en 0
        if (strpos($numero, '+261') === 0) {
            return '0' . substr($numero, 4);
        }
        
        // Si le numéro commence par 261, on le convertit en 0
        if (strpos($numero, '261') === 0) {
            return '0' . substr($numero, 3);
        }
        
        return $numero;
    }

    /**
     * Formater un numéro de téléphone
     * @param string $numero
     * @param string $format 'standard' (032 56 100 52) ou 'international' (+261 32 56 100 52)
     * @return string
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
        
        // Format standard
        return substr($numero, 0, 3) . ' ' . 
               substr($numero, 3, 2) . ' ' . 
               substr($numero, 5, 2) . ' ' . 
               substr($numero, 7, 2) . ' ' . 
               substr($numero, 9, 2);
    }

    /**
     * Valider le format d'un numéro de téléphone
     * @param string $numero
     * @return bool
     */
    public function validerFormatNumero($numero)
    {
        $numero = $this->nettoyerNumero($numero);
        return preg_match('/^0[3-9][0-9]{8}$/', $numero) === 1;
    }
}