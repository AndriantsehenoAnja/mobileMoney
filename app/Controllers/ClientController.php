<?php

namespace App\Controllers;
use App\Models\ClientModel;
use App\Models\CompteModel;
use App\Models\TransactionModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
class ClientController extends BaseController
{
    public function index(): string
    {
    $compte = new CompteModel();
    $solde = $compte->getSolde(session()->get('client_id'));
        return view('/Client/index', ['solde' => $solde]);
    }
// Depots
    public function depot()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        $compteModel = new CompteModel();
        $compte = $compteModel->findByClientId($clientId);
        $solde = $compteModel->getSolde(session()->get('client_id'));
        $data = [
            'title' => 'Dépôt - Mobile Money',
            'solde' => $solde,
            'compte' => $compte
        ];
        
        return view('Client/depot', $data);
    
    }

    public function effectuerDepot()
{
    $session = session();
    
    if (!$session->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
    }
    
    $clientId = $session->get('client_id');
    
    $montant = $this->request->getPost('montant');
    
    if (empty($montant) || !is_numeric($montant) || $montant <= 0) {
        return redirect()->back()->with('error', 'Veuillez saisir un montant valide');
    }
    
    $montant = (float) $montant;
    
    if ($montant < 100) {
        return redirect()->back()->with('error', 'Le montant minimum de dépôt est de 100 Ar');
    }
    
    $compteModel = new CompteModel();
    $transactionModel = new TransactionModel();
    $typeOperationModel = new TypeOperationModel();
    
    $compte = $compteModel->findByClientId($clientId);
    
    if (!$compte) {
        return redirect()->back()->with('error', 'Compte non trouvé');
    }
    
    $typeDepot = $typeOperationModel->where('nom', 'Depot')->first();
    
    if (!$typeDepot) {
        return redirect()->back()->with('error', 'Type d\'opération non configuré');
    }
    
    $db = \Config\Database::connect();
    $db->transStart();
    
    // Créditer le compte
    $compteModel->crediter($compte->id, $montant);
    
    $transactionModel->insert([
        'type_operation_id' => $typeDepot['id'],  
        'compte_source' => null,
        'compte_destination' => $compte->id,
        'montant' => $montant,
        'frais' => 0
    ]);
    
    $db->transComplete();
    
    if ($db->transStatus() === false) {
        $db->transRollback();
        return redirect()->back()->with('error', 'Erreur lors du dépôt');
    }
    
    return redirect()->to('/Client')->with('success', 'Dépôt de ' . number_format($montant, 2) . ' Ar effectué avec succès');
    }

// Retraits
    public function retrait()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        $compteModel = new CompteModel();
        $compte = $compteModel->findByClientId($clientId);
        $solde = $compteModel->getSolde(session()->get('client_id'));
        
        $data = [
            'title' => 'Retrait - Mobile Money',
            'solde' => $solde,
            'compte' => $compte
        ];
        
        return view('Client/retrait', $data);
    }

        public function effectuerRetrait()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        
        $montant = $this->request->getPost('montant');
        
        if (empty($montant) || !is_numeric($montant) || $montant <= 0) {
            return redirect()->back()->with('error', 'Veuillez saisir un montant valide');
        }
        
        $montant = (float) $montant;
        
        if ($montant < 100) {
            return redirect()->back()->with('error', 'Le montant minimum de retrait est de 100 Ar');
        }
        
        $compteModel = new CompteModel();
        $transactionModel = new TransactionModel();
        $typeOperationModel = new TypeOperationModel();
        $baremeFraisModel = new BaremeFraisModel();
        
        $compte = $compteModel->findByClientId($clientId);
        
        if (!$compte) {
            return redirect()->back()->with('error', 'Compte non trouvé');
        }
        
        $typeRetrait = $typeOperationModel->where('nom', 'Retrait')->first();
        
        if (!$typeRetrait) {
            return redirect()->back()->with('error', 'Type d\'opération non configuré');
        }
        
        $frais = $baremeFraisModel->calculerFrais($typeRetrait['id'], $montant);
        $montantTotal = $montant + $frais;
        
        if ($compte->solde < $montantTotal) {
            return redirect()->back()->with('error', 'Solde insuffisant. Solde disponible : ' . number_format($compte->solde, 2) . ' Ar');
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        $compteModel->debiter($compte->id, $montantTotal);
        
        $transactionModel->insert([
            'type_operation_id' => $typeRetrait['id'],
            'compte_source' => $compte->id,
            'compte_destination' => null,
            'montant' => $montant,
            'frais' => $frais
        ]);
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Erreur lors du retrait');
        }
        
        $message = 'Retrait de ' . number_format($montant, 2) . ' Ar effectué avec succès';
        if ($frais > 0) {
            $message .= ' (Frais: ' . number_format($frais, 2) . ' Ar)';
        }
        
        return redirect()->to('/Client')->with('success', $message);
    }

    

}