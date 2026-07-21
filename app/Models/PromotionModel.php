<?php

namespace App\Models;

use CodeIgniter\Model;

class PromotionModel extends Model
{
    protected $table = 'promossion';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'operateur_id',
        'pourcentage'
    ];

    public function getReductionIntertActive($operation)
    {
        $result = $this->where('operateur_id =', $operation)
                       ->first();
    }


}