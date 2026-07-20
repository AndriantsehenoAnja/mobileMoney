<?php

namespace App\Models;

use CodeIgniter\Model;

class BaremeFraisModel extends Model
{
    protected $table            = 'baremes_frais';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['type_operation_id', 'montant_min', 'montant_max', 'frais'];

    // Dates
    protected $useTimestamps = false;

    // Validation
    protected $validationRules      = [
        'type_operation_id' => 'required|is_not_unique[types_operations.id]',
        'montant_min'       => 'required|numeric',
        'montant_max'       => 'required|numeric',
        'frais'             => 'required|numeric',
    ];
    protected $skipValidation       = false;

    /**
     * Méthode personnalisée pour trouver les frais applicables
     * à une opération spécifique et un montant donné.
     */
    public function obtenirFrais(int $typeOperationId, float $montant)
    {
        return $this->where('type_operation_id', $typeOperationId)
                    ->where('montant_min <=', $montant)
                    ->where('montant_max >=', $montant)
                    ->first();
    }

    public function calculerFrais(int $typeOperationId, float $montant)
    {
        $bareme = $this->obtenirFrais($typeOperationId, $montant);
        return $bareme ? (float) $bareme['frais'] : 0.0;
    }
}