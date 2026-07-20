<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixeExterneModel extends Model
{
    protected $table            = 'prefixesOperateurExterne';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['operateur_id', 'prefixe'];

    protected $useTimestamps = false;

    protected $validationRules      = [
        'operateur_id' => 'required|integer|is_not_unique[operateurs.id]',
        'prefixe'      => 'required|is_unique[prefixesOperateurExterne.prefixe,id,{id}]|min_length[2]|max_length[10]',
    ];

    protected $validationMessages   = [
        'operateur_id' => [
            'required'      => 'L\'opérateur associé est obligatoire.',
            'is_not_unique' => 'L\'opérateur sélectionné n\'existe pas.',
        ],
        'prefixe' => [
            'required'  => 'Le préfixe est obligatoire.',
            'is_unique' => 'Ce préfixe externe existe déjà dans la base de données.',
        ],
    ];

    protected $skipValidation = false;

    public function getPrefixeExterneById(int $id)
    {
        return $this->find($id);
    }

    public function getAllPrefixesExternes()
    {
        return $this->findAll();
    }

    public function addPrefixeExterne(array $data)
    {
        return $this->insert($data);
    }

    public function updatePrefixeExterne(int $id, array $data)
    {
        return $this->update($id, $data);
    }

    public function deletePrefixeExterne(int $id)
    {
        return $this->delete($id);
    }

    public function findByPrefixe(string $prefixe)
    {
        return $this->where('prefixe', $prefixe)->first();
    }

    /**
     * Récupère les préfixes externes avec les informations de leur opérateur associé.
     */
    public function getPrefixesWithOperateur()
    {
        return $this->select('prefixesOperateurExterne.*, operateurs.nom as operateur_nom, operateurs.commission')
                    ->join('operateurs', 'operateurs.id = prefixesOperateurExterne.operateur_id')
                    ->findAll();
    }
}