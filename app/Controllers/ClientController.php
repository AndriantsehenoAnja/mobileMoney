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

// Transfert

public function transfert()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        $compteModel = new CompteModel();
        $clientModel = new ClientModel();
        
        $compte = $compteModel->findByClientId($clientId);
        $client = $clientModel->find($clientId);
        
        $data = [
            'title' => 'Transfert - Mobile Money',
            // ✅ Utiliser -> au lieu de []
            'solde' => $compte ? $compte->solde : 0,
            'compte' => $compte,
            'client' => $client
        ];
        
        return view('Client/transfert', $data);
    }

    /**
     * Vérifier un numéro de destinataire en AJAX
     */
    public function verifierDestinataire()
    {
        $numero = $this->request->getGet('numero');
        
        if (empty($numero)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Numéro requis'
            ]);
        }
        
        $numero = preg_replace('/[\s\-\.\(\)]/', '', $numero);
        
        if (strlen($numero) !== 10) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Le numéro doit contenir exactement 10 chiffres'
            ]);
        }
        
        $session = session();
        if ($numero == $session->get('client_numero')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous transférer à vous-même'
            ]);
        }
        
        $clientModel = new ClientModel();
        $client = $clientModel->findByNumero($numero);
        
        if (!$client) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Destinataire non trouvé'
            ]);
        }
        
        $compteModel = new CompteModel();
        $compte = $compteModel->findByClientId($client->id);
        
        if (!$compte) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Le destinataire n\'a pas de compte actif'
            ]);
        }
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Destinataire trouvé',
            'client' => [
                'id' => $client->id,
                'nom' => $client->nom,
                'numero' => $client->numero
            ]
        ]);
    }

    /**
     * Traiter le transfert
     */
    public function effectuerTransfert()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        $clientNumero = $session->get('client_numero');
        
        $montant = $this->request->getPost('montant');
        $numeroDestinataire = $this->request->getPost('numero_destinataire');
        
        if (empty($numeroDestinataire) || strlen($numeroDestinataire) !== 10) {
            return redirect()->back()->with('error', 'Veuillez saisir un numéro de destinataire valide (10 chiffres)');
        }
        
        if ($numeroDestinataire == $clientNumero) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas vous transférer de l\'argent à vous-même');
        }
        
        if (empty($montant) || !is_numeric($montant) || $montant <= 0) {
            return redirect()->back()->with('error', 'Veuillez saisir un montant valide');
        }
        
        $montant = (float) $montant;
        
        if ($montant < 100) {
            return redirect()->back()->with('error', 'Le montant minimum de transfert est de 100 Ar');
        }
        
        $clientModel = new ClientModel();
        $compteModel = new CompteModel();
        $transactionModel = new TransactionModel();
        $typeOperationModel = new TypeOperationModel();
        $baremeFraisModel = new BaremeFraisModel();
        
        // Récupérer le compte source (c'est un objet)
        $compteSource = $compteModel->findByClientId($clientId);
        
        if (!$compteSource) {
            return redirect()->back()->with('error', 'Compte source non trouvé');
        }
        
        $destinataire = $clientModel->findByNumero($numeroDestinataire);
        
        if (!$destinataire) {
            return redirect()->back()->with('error', 'Destinataire non trouvé');
        }
        
        $compteDestination = $compteModel->findByClientId($destinataire->id);
        
        if (!$compteDestination) {
            return redirect()->back()->with('error', 'Le destinataire n\'a pas de compte actif');
        }
        
        $typeTransfert = $typeOperationModel->where('nom', 'Transfert')->first();
        
        if (!$typeTransfert) {
            return redirect()->back()->with('error', 'Type d\'opération non configuré');
        }
        
        // ✅ Utiliser -> au lieu de [] pour les objets
        $frais = $baremeFraisModel->calculerFrais($typeTransfert['id'], $montant);
        $montantTotal = $montant + $frais;
        
        // ✅ Utiliser -> au lieu de [] pour les objets
        if ($compteSource->solde < $montantTotal) {
            return redirect()->back()->with('error', 'Solde insuffisant. Solde disponible : ' . number_format($compteSource->solde, 2) . ' Ar');
        }
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // ✅ Utiliser -> au lieu de [] pour les objets
            $compteModel->debiter($compteSource->id, $montantTotal);
            $compteModel->crediter($compteDestination->id, $montant);
            
            $transactionModel->insert([
                'type_operation_id' => $typeTransfert['id'],
                'compte_source' => $compteSource->id,
                'compte_destination' => $compteDestination->id,
                'montant' => $montant,
                'frais' => $frais
            ]);
            
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Erreur lors de la transaction');
            }
            
            $message = 'Transfert de ' . number_format($montant, 2) . ' Ar vers ' . $numeroDestinataire . ' effectué avec succès';
            if ($frais > 0) {
                $message .= ' (Frais: ' . number_format($frais, 2) . ' Ar)';
            }
            
            return redirect()->to('/Client')->with('success', $message);
            
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Erreur lors du transfert : ' . $e->getMessage());
        }
    }

// Historique
/**
 * Afficher l'historique des transactions avec Query Builder
 */
public function historique()
{
    $session = session();
    
    if (!$session->get('logged_in')) {
        return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
    }
    
    $clientId = $session->get('client_id');
    
    $compteModel = new CompteModel();
    $transactionModel = new TransactionModel();
    
    // Récupérer le compte du client
    $compte = $compteModel->findByClientId($clientId);
    
    if (!$compte) {
        return redirect()->back()->with('error', 'Compte non trouvé');
    }
    
    // Récupérer les transactions avec Query Builder
    $transactions = $transactionModel
        ->select('transactions.*, 
                  types_operations.nom as type_operation,
                  cl1.numero as numero_source,
                  cl1.nom as nom_source,
                  cl2.numero as numero_destination,
                  cl2.nom as nom_destination')
        ->join('types_operations', 'types_operations.id = transactions.type_operation_id', 'left')
        ->join('comptes c1', 'c1.id = transactions.compte_source', 'left')
        ->join('clients cl1', 'cl1.id = c1.client_id', 'left')
        ->join('comptes c2', 'c2.id = transactions.compte_destination', 'left')
        ->join('clients cl2', 'cl2.id = c2.client_id', 'left')
        ->where('transactions.compte_source', $compte->id)
        ->orWhere('transactions.compte_destination', $compte->id)
        ->orderBy('transactions.date_transaction', 'DESC')
        ->findAll();
    
    // Calculer les statistiques
    $totalDepots = 0;
    $totalRetraits = 0;
    $totalTransferts = 0;
    $totalFrais = 0;
    
    foreach ($transactions as $tx) {
        $totalFrais += $tx->frais ?? 0;
        
        if (strtolower($tx->type_operation ?? '') == 'depot') {
            $totalDepots += $tx->montant;
        } elseif (strtolower($tx->type_operation ?? '') == 'retrait') {
            $totalRetraits += $tx->montant;
        } elseif (strtolower($tx->type_operation ?? '') == 'transfert') {
            $totalTransferts += $tx->montant;
        }
    }
    
    $data = [
        'title' => 'Historique - Mobile Money',
        'transactions' => $transactions,
        'compte' => $compte,
        'solde' => $compte->solde,
        'total_transactions' => count($transactions),
        'total_depots' => $totalDepots,
        'total_retraits' => $totalRetraits,
        'total_transferts' => $totalTransferts,
        'total_frais' => $totalFrais
    ];
    
    return view('Client/historique', $data);
}

}