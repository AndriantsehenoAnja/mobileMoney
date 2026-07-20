<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixModel extends Model
{
    protected $table            = 'prefixes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    // Champs autorisés lors de l'insertion ou la mise à jour
    protected $allowedFields    = ['prefixe'];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'prefixe' => 'required|is_unique[prefixes.prefixe]|min_length[2]|max_length[10]',
    ];
    protected $validationMessages   = [
        'prefixe' => [
            'required'  => 'Le préfixe est obligatoire.',
            'is_unique' => 'Ce préfixe existe déjà dans la base de données.',
        ],
    ];
    protected $skipValidation       = false;

    public function getPrefixById(int $id)
    {
        return $this->find($id);
    }
    public function getAllPrefixes()
    {
        return $this->findAll();
    }
    public function addPrefix(string $prefix)
    {
        return $this->insert(['prefixe' => $prefix]);
    }   

    public function updatePrefix(int $id, string $prefix)
    {
        return $this->update($id, ['prefixe' => $prefix]);
    }

    public function deletePrefix(int $id)
    {
        return $this->delete($id);
    }

    public function findByPrefixe(string $prefixe)
    {
        return $this->where('prefixe', $prefixe)->first();
    }
}