<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeOperationModel extends Model
{
    protected $table            = 'types_operations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['nom'];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'nom' => 'required|min_length[3]|max_length[50]|is_unique[types_operations.nom]',
    ];
    protected $validationMessages   = [
        'nom' => [
            'required'  => 'Le nom du type d\'opération est obligatoire.',
            'is_unique' => 'Ce type d\'opération existe déjà.',
        ],
    ];
    protected $skipValidation       = false;
}