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
        $solde = $compteModel->getSolde(session()->get('client_id'));
        $data = [
            'title' => 'Dépôt - Mobile Money',
            'solde' => $solde,
            'compte' => $compte
        ];
        
        return view('Client/depot', $data);
    
    }

    /**
     * Traiter le dépôt
     */
    public function effectuerDepot()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        
        $montant = $this->request->getPost('montant');
        $description = $this->request->getPost('description');
        
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
        
        $typeDepot = $typeOperationModel->where('nom', 'depot')->first();
        
        if (!$typeDepot) {
            return redirect()->back()->with('error', 'Type d\'opération non configuré');
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        // Créditer le compte
        $compteModel->crediter($compte->id, $montant);
        
        // Créer la transaction
        $transactionModel->insert([
            'type_operation_id' => $typeDepot->id,
            'compte_source' => null,
            'compte_destination' => $compte->id,
            'montant' => $montant,
            'frais' => 0,
            'description' => $description ?? 'Dépôt effectué'
        ]);
        
        $db->transComplete();
        
        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Erreur lors du dépôt');
        }
        
        return redirect()->to('/client')->with('success', 'Dépôt de ' . number_format($montant, 2) . ' Ar effectué avec succès');
    }

}