<?php
namespace App\Controllers;
use config\Database;
use App\Models\CandidatModel;
class ProduitController extends BaseController{
    public function index():string
        {
            $candidatModel = new CandidatModel();
            $data = $candidatModel->findAll();
            // $db = Database::connect();
            // $query = $db->query("SELECT * FROM candidat");
            // $data = $query->getResult();
            return view("produit/index", ['candidats' => $data]);
        }
    public function show($id)
        {
            return "Produit ID : " . $id;
        }
}