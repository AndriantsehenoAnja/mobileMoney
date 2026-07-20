<?php
namespace App\Models;
use CodeIgniter\Model;
class CandidatModel extends Model
{
    protected $table = 'candidat';
    protected $primaryKey = 'id_candidat';
    protected $allowedFields = ['nom'];
}