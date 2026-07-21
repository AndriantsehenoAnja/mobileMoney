<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table            = 'operateurs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['nom', 'commission', 'est_notre_operateur'];

    public function getNotreOperateur()
    {
        return $this->where('est_notre_operateur', 1)->first();
    }

    public function getOperateursExternes()
    {
        return $this->where('est_notre_operateur', 0)->findAll();
    }
}

?>