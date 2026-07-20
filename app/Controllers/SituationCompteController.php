<?php

namespace App\Controllers;

use App\Models\CompteModel;
class SituationCompteController extends BaseController
{
   public function index()
   {
         $compteModel = new CompteModel();
         $situation = $compteModel->getSituationComptes();

        return view('situation_compte/index', [
            'clientsSituation' => $situation
        ]);
   }

   public function getGainTotalParType(){
    $transactionModel = new \App\Models\TransactionModel();
       $gainTotal = $transactionModel->getGainTotalParType();

       return view('situation_compte/gain', [
           'gainTotal' => $gainTotal
       ]);
   }
}