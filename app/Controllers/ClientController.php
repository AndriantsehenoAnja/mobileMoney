<?php

namespace App\Controllers;
use App\Models\ClientModel;
use App\Models\CompteModel;
class ClientController extends BaseController
{
    public function index(): string
    {
    $compte = new CompteModel();
    $solde = $compte->getSolde(session()->get('client_id'));
        return view('/Client/index', ['solde' => $solde]);
    }

    public function depot()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        $compteModel = new CompteModel();
        $compte = $compteModel->findByClientId($clientId);
        
        $data = [
            'title' => 'Dépôt - Mobile Money',
            'solde' => $compte ? $compte->solde : 0,
            'compte' => $compte
        ];
        
        return view('Client/depot', $data);
    
}

}