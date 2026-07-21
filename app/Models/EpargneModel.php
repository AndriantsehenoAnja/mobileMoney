<?php

namespace App\Models;

use CodeIgniter\Model;

class EpargneModel extends Model
{
    protected $table            = 'epargne';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['idcompte','transactionid','montant','dateEpargne'];


    public function getCompteEpargne($idCompte){
        return $this->where('idCompte', $idCompte)->first();
    }

    public function addEpargne($idCompte,$montant,$idTransaction)
    {
        return $this->insert(['idCompte' => $idCompte,'transactionid' => $idTransaction,'montant' => $montant]);
    }

}

?>