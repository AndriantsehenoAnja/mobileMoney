<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupeTransfertModel extends Model
{
    protected $table            = 'groupes_transferts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'compte_source',
        'montant_total',
        'nombre_destinataires',
        'frais_total',
        'commission_total',
        'date_transfert'
    ];

    protected $useTimestamps = false;

    protected $validationRules      = [
        'compte_source'        => 'required|integer|is_not_unique[comptes.id]',
        'montant_total'        => 'required|numeric|greater_than[0]',
        'nombre_destinataires' => 'required|integer|greater_than[0]',
        'frais_total'          => 'permit_empty|numeric|greater_than_equal_to[0]',
        'commission_total'     => 'permit_empty|numeric|greater_than_equal_to[0]',
    ];

    protected $validationMessages   = [
        'compte_source' => [
            'required'      => 'Le compte source est obligatoire.',
            'is_not_unique' => 'Le compte source n\'existe pas.',
        ],
        'montant_total' => [
            'required'     => 'Le montant total est obligatoire.',
            'greater_than' => 'Le montant total doit être supérieur à zéro.',
        ],
        'nombre_destinataires' => [
            'required'     => 'Le nombre de destinataires est obligatoire.',
            'greater_than' => 'Il doit y avoir au moins 1 destinataire.',
        ],
    ];

    protected $skipValidation = false;

    public function getGroupeTransfertById(int $id)
    {
        return $this->find($id);
    }

    public function getAllGroupesTransferts()
    {
        return $this->findAll();
    }

    public function addGroupeTransfert(array $data)
    {
        return $this->insert($data);
    }

    public function deleteGroupeTransfert(int $id)
    {
        return $this->delete($id);
    }

    /**
     * Récupère un groupe de transfert avec toutes ses transactions enfants associées.
     */
    public function getGroupeWithTransactions(int $groupeId)
    {
        $groupe = $this->find($groupeId);

        if ($groupe) {
            $db = \Config\Database::connect();
            $groupe['transactions'] = $db->table('transactions')
                                         ->where('groupe_transfert_id', $groupeId)
                                         ->get()
                                         ->getResultArray();
        }

        return $groupe;
    }
}