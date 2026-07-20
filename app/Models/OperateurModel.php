<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table            = 'operateurs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['nom', 'commission'];

    protected $useTimestamps = false;

    protected $validationRules      = [
        'nom'        => 'required|is_unique[operateurs.nom,id,{id}]|min_length[2]|max_length[100]',
        'commission' => 'permit_empty|numeric|greater_than_equal_to[0]',
    ];

    protected $validationMessages   = [
        'nom' => [
            'required'  => 'Le nom de l\'opérateur est obligatoire.',
            'is_unique' => 'Cet opérateur existe déjà dans la base de données.',
        ],
        'commission' => [
            'numeric'               => 'La commission doit être un nombre valide.',
            'greater_than_equal_to' => 'La commission ne peut pas être négative.',
        ],
    ];

    protected $skipValidation = false;

    public function getOperateurById(int $id)
    {
        return $this->find($id);
    }

    public function getAllOperateurs()
    {
        return $this->findAll();
    }

    public function addOperateur(array $data)
    {
        return $this->insert($data);
    }

    public function updateOperateur(int $id, array $data)
    {
        return $this->update($id, $data);
    }

    public function deleteOperateur(int $id)
    {
        return $this->delete($id);
    }

    public function findByNom(string $nom)
    {
        return $this->where('nom', $nom)->first();
    }
}