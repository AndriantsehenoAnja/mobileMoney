<?php
namespace App\Models;
use CodeIgniter\Model;

class ProduitModel extends Model
{
    protected $table = 'produits';
    protected $allowedFields = ['nom', 'prix'];
}