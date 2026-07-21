<?php

namespace App\Models;

use CodeIgniter\Model;

class ChoixEpargneModel extends Model
{
    protected $table            = 'choixEpargne';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['idcompte','pourcentage'];

    public function getChoixEpargne($idCompte){
        return $this->where('idCompte', $idCompte)->first();
    }

}

?>