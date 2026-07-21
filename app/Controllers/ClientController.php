<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\CompteModel;
use App\Models\TransactionModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
use App\Models\OperateurModel;
use App\Models\PrefixModel;

class ClientController extends BaseController
{
    /**
     * Dashboard client
     */
    public function index()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        
        $clientModel = new ClientModel();
        $compteModel = new CompteModel();
        $transactionModel = new TransactionModel();
        
        $client = $clientModel->find($clientId);
        $compte = $compteModel->findByClientId($clientId);
        
        $transactions = [];
        if ($compte) {
            $transactions = $transactionModel
                ->select('transactions.*, types_operations.nom as type_operation')
                ->join('types_operations', 'types_operations.id = transactions.type_operation_id', 'left')
                ->where('transactions.compte_source', $compte->id)
                ->orWhere('transactions.compte_destination', $compte->id)
                ->orderBy('transactions.date_transaction', 'DESC')
                ->limit(5)
                ->findAll();
        }
        
        $totalDepots = 0;
        $totalRetraits = 0;
        $totalTransferts = 0;
        $totalFrais = 0;
        $totalTransactions = 0;
        
        if ($compte) {
            $totalTransactions = $transactionModel
                ->where('compte_source', $compte->id)
                ->orWhere('compte_destination', $compte->id)
                ->countAllResults();
            
            $allTransactions = $transactionModel
                ->select('transactions.*, types_operations.nom as type_operation')
                ->join('types_operations', 'types_operations.id = transactions.type_operation_id', 'left')
                ->where('transactions.compte_source', $compte->id)
                ->orWhere('transactions.compte_destination', $compte->id)
                ->findAll();
            
            foreach ($allTransactions as $tx) {
                $totalFrais += $tx->frais ?? 0;
                if (strtolower($tx->type_operation ?? '') == 'depot') {
                    $totalDepots += $tx->montant;
                } elseif (strtolower($tx->type_operation ?? '') == 'retrait') {
                    $totalRetraits += $tx->montant;
                } elseif (strtolower($tx->type_operation ?? '') == 'transfert') {
                    $totalTransferts += $tx->montant;
                }
            }
        }
        
        $data = [
            'title' => 'Mon Compte - Mobile Money',
            'page_title' => 'Dashboard',
            'current_page' => 'dashboard',
            'client' => $client,
            'compte' => $compte,
            'transactions' => $transactions,
            'solde' => $compte ? $compte->solde : 0,
            'total_transactions' => $totalTransactions,
            'total_depots' => $totalDepots,
            'total_retraits' => $totalRetraits,
            'total_transferts' => $totalTransferts,
            'total_frais' => $totalFrais
        ];
        
        return view('Client/index', $data);
    }

    /**
     * Formulaire de dépôt
     */
    public function depot()
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
            'title' => 'Dépôt - Mobile Money',
            'page_title' => 'Dépôt',
            'current_page' => 'depot',
            'solde' => $compte ? $compte->solde : 0,
            'compte' => $compte,
            'client' => $client
        ];
        
        return view('Client/depot', $data);
    }

    /**
     * Formulaire de retrait
     */
    public function retrait()
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
            'title' => 'Retrait - Mobile Money',
            'page_title' => 'Retrait',
            'current_page' => 'retrait',
            'solde' => $compte ? $compte->solde : 0,
            'compte' => $compte,
            'client' => $client
        ];
        
        return view('Client/retrait', $data);
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
        $clientModel = new ClientModel();

        $compte = $compteModel->findByClientId($clientId);
        if (!$compte) {
            return redirect()->back()->with('error', 'Compte non trouvé');
        }

        $client = $clientModel->find($clientId);

        // Récupération du type d'opération "Depot"
        $typeDepot = $typeOperationModel->where('nom', 'Depot')->first();
        if (!$typeDepot) {
            return redirect()->back()->with('error', 'Type d\'opération non configuré');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Créditer le compte du client
        $compteModel->crediter($compte->id, $montant);

        // Insertion compatible V2
        $transactionModel->insert([
            'type_operation_id'        => $typeDepot['id'],
            'compte_source'            => null,
            'compte_destination'       => $compte->id,
            'numero_destination'       => $client->numero ?? null,
            'operateur_destination_id' => $client->operateur_id ?? 1, // Opérateur interne
            'montant'                  => $montant,
            'frais_base'               => 0,
            'frais_commission_externe' => 0,
            'frais_retrait_inclus'     => 0,
            'frais_total'              => 0,
            'date_transaction'         => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement du dépôt');
        }

        return redirect()->to('/client')->with('success', 'Dépôt de ' . number_format($montant, 2, ',', ' ') . ' Ar effectué avec succès');
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
        $clientModel = new ClientModel();

        $compte = $compteModel->findByClientId($clientId);
        if (!$compte) {
            return redirect()->back()->with('error', 'Compte non trouvé');
        }

        $client = $clientModel->find($clientId);

        $typeRetrait = $typeOperationModel->where('nom', 'Retrait')->first();
        if (!$typeRetrait) {
            return redirect()->back()->with('error', 'Type d\'opération non configuré');
        }

        // Calcul des frais selon le barème
        $frais = $baremeFraisModel->calculerFrais($typeRetrait['id'], $montant);

        // Vérification de l'existence d'un barème configuré pour ce montant
        if ($frais === null) {
            return redirect()->back()->with('error', 'Aucun barème de frais configuré pour ce montant.');
        }

        $montantTotal = $montant + $frais;

        // Vérification du solde (Montant + Frais)
        if ($compte->solde < $montantTotal) {
            return redirect()->back()->with('error', 'Solde insuffisant. Requis : ' . number_format($montantTotal, 2, ',', ' ') . ' Ar (Dont frais : ' . number_format($frais, 2, ',', ' ') . ' Ar)');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Débiter le compte du montant total (Montant + Frais)
        $compteModel->debiter($compte->id, $montantTotal);

        // Insertion compatible V2
        $transactionModel->insert([
            'type_operation_id'        => $typeRetrait['id'],
            'compte_source'            => $compte->id,
            'compte_destination'       => null,
            'numero_destination'       => $client->numero ?? null,
            'operateur_destination_id' => $client->operateur_id ?? 1,
            'montant'                  => $montant,
            'frais_base'               => $frais,
            'frais_commission_externe' => 0,
            'frais_retrait_inclus'     => 0,
            'frais_total'              => $frais,
            'date_transaction'         => date('Y-m-d H:i:s')
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Erreur lors de l\'enregistrement du retrait');
        }

        $message = 'Retrait de ' . number_format($montant, 2, ',', ' ') . ' Ar effectué avec succès';
        if ($frais > 0) {
            $message .= ' (Frais retenus : ' . number_format($frais, 2, ',', ' ') . ' Ar)';
        }

        return redirect()->to('/client')->with('success', $message);
    }

    /**
     * Formulaire de transfert
     */
    public function transfert()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        $compteModel = new CompteModel();
        $clientModel = new ClientModel();
        $operateurModel = new OperateurModel();
        
        $compte = $compteModel->findByClientId($clientId);
        $client = $clientModel->find($clientId);
        
        // Récupérer la liste des opérateurs externes pour information/commissions dans la vue
        $operateursExternes = $operateurModel->getOperateursExternes();

        $data = [
            'title'               => 'Transfert - Mobile Money',
            'page_title'          => 'Transfert d\'argent',
            'current_page'        => 'transfert',
            'solde'               => $compte ? $compte->solde : 0,
            'compte'              => $compte,
            'client'              => $client,
            'operateursExternes'  => $operateursExternes
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

        // 1. Vérification de la session client
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $clientId = $session->get('client_id');
        $numeroDestinataire = trim($this->request->getPost('numero_destinataire') ?? '');
        $montant = $this->request->getPost('montant');

        // 2. Validation des champs saisis
        if (empty($numeroDestinataire)) {
            return redirect()->back()->with('error', 'Veuillez saisir le numéro du destinataire.');
        }

        if (empty($montant) || !is_numeric($montant) || $montant <= 0) {
            return redirect()->back()->with('error', 'Veuillez saisir un montant valide.');
        }

        $montant = (float) $montant;
        if ($montant < 100) {
            return redirect()->back()->with('error', 'Le montant minimum de transfert est de 100 Ar.');
        }

        // 3. Initialisation des modèles
        $compteModel          = new \App\Models\CompteModel();
        $clientModel          = new \App\Models\ClientModel();
        $prefixModel          = new \App\Models\PrefixModel();
        $transactionModel     = new \App\Models\TransactionModel();
        $typeOperationModel   = new \App\Models\TypeOperationModel();
        $baremeFraisModel     = new \App\Models\BaremeFraisModel();

        // 4. Récupération du compte de l'expéditeur
        $compteExpediteur = $compteModel->findByClientId($clientId);
        if (!$compteExpediteur) {
            return redirect()->back()->with('error', 'Compte expéditeur non trouvé.');
        }

        // 5. Analyse du préfixe du destinataire 
        $prefixeSaisi = substr($numeroDestinataire, 0, 3);
        $prefixData   = $prefixModel->findByPrefixeWithOperateur($prefixeSaisi);

        if (!$prefixData) {
            return redirect()->back()->with('error', "Le préfixe ($prefixeSaisi) n'est pas reconnu par notre réseau.");
        }

        // Extraction sécurisée des données de l'opérateur (Gestion Tableau ou Objet)
        $estNotreOperateur = is_array($prefixData) ? ($prefixData['est_notre_operateur'] ?? 0) : ($prefixData->est_notre_operateur ?? 0);
        $operateurDestId   = is_array($prefixData) ? ($prefixData['operateur_id'] ?? null) : ($prefixData->operateur_id ?? null);
        $nomOperateurDest  = is_array($prefixData) ? ($prefixData['nom_operateur'] ?? 'Réseau Tiers') : ($prefixData->nom_operateur ?? 'Réseau Tiers');
        $commissionTaux    = is_array($prefixData) ? ($prefixData['commission'] ?? 0) : ($prefixData->commission ?? 0);

        // 6. Détermination du type d'opération et identification du destinataire
    $compteDestinataireId = null;
    
    if ($estNotreOperateur == 1) {
        // === TRANSFERT INTERNE ===
        $typeOp = $typeOperationModel->where('nom', 'Transfert Interne')->first();
        
        // Empêcher l'auto-transfert
        $clientExpediteur = $clientModel->find($clientId);
        $numExp = $clientExpediteur ? (is_array($clientExpediteur) ? $clientExpediteur['numero'] : $clientExpediteur->numero) : '';
        
        if ($numExp === $numeroDestinataire) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas effectuer un transfert vers votre propre numéro.');
        }
        
        // Recherche du client destinataire en BDD
        $clientDest = $clientModel->where('numero', $numeroDestinataire)->first();
        if (!$clientDest) {
            return redirect()->back()->with('error', 'Aucun compte client associé à ce numéro interne.');
        }
        
        $clientDestId = is_array($clientDest) ? $clientDest['id'] : $clientDest->id;
        $compteDestinataire = $compteModel->findByClientId($clientDestId);
        if (!$compteDestinataire) {
            return redirect()->back()->with('error', 'Le destinataire ne possède pas de compte actif.');
        }
        
        $compteDestinataireId = is_array($compteDestinataire) ? $compteDestinataire['id'] : $compteDestinataire->id;
        
    } else {
        // === TRANSFERT INTER-OPÉRATEUR (EXTERNE) ===
        $typeOp = $typeOperationModel->where('nom', 'Transfert Inter-operateur')->first();
    }
    
    if (!$typeOp) {
        return redirect()->back()->with('error', "Type d'opération non configuré.");
    }
    
    $typeOpId = is_array($typeOp) ? $typeOp['id'] : $typeOp->id;

    // 7. Calcul des frais selon le barème
    $fraisBase = $baremeFraisModel->calculerFrais($typeOpId, $montant) ?? 0;
    
    $fraisCommissionExterne = 0;
    if ($estNotreOperateur == 0 && $commissionTaux > 0) {
        $fraisCommissionExterne = ($montant * $commissionTaux) / 100;
    }
    
    $fraisTotal = $fraisBase + $fraisCommissionExterne;
    $montantTotalDebite = $montant + $fraisTotal;
    
    // 8. Vérification du solde de l'expéditeur
    $soldeExpediteur = is_array($compteExpediteur) ? $compteExpediteur['solde'] : $compteExpediteur->solde;
    $compteExpediteurId = is_array($compteExpediteur) ? $compteExpediteur['id'] : $compteExpediteur->id;

    if ($soldeExpediteur < $montantTotalDebite) {
        return redirect()->back()->with('error', 'Solde insuffisant. Requis : ' . number_format($montantTotalDebite, 2, ',', ' ') . ' Ar.');
    }
    
    // 9. Exécution de la Transaction
    $db = \Config\Database::connect();
    $db->transStart();
    
    $compteModel->debiter($compteExpediteurId, $montantTotalDebite);
    
    if ($compteDestinataireId !== null) {
        $compteModel->crediter($compteDestinataireId, $montant);
    }
    
    $transactionModel->insert([
        'type_operation_id'        => $typeOpId,
        'compte_source'            => $compteExpediteurId,
        'compte_destination'       => $compteDestinataireId,
        'numero_destination'       => $numeroDestinataire,
        'operateur_destination_id' => $operateurDestId,
        'montant'                  => $montant,
        'frais_base'               => $fraisBase,
        'frais_commission_externe' => $fraisCommissionExterne,
        'frais_retrait_inclus'     => 0,
        'frais_total'              => $fraisTotal,
        'date_transaction'         => date('Y-m-d H:i:s')
    ]);
    
    $db->transComplete();

        // 10. Traitement du résultat
        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Une erreur réseau est survenue lors du transfert.');
        }

        $message = 'Transfert de ' . number_format($montant, 2, ',', ' ') . ' Ar vers ' . esc($numeroDestinataire) . ' (' . esc($nomOperateurDest) . ') effectué avec succès.';

        return redirect()->to('/client')->with('success', $message);
    }
    /**
     * Historique des transactions
     */
    public function historique()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        
        $compteModel = new CompteModel();
        $clientModel = new ClientModel();
        
        $compte = $compteModel->findByClientId($clientId);
        
        if (!$compte) {
            return redirect()->back()->with('error', 'Compte non trouvé');
        }
        
        $db = \Config\Database::connect();
        
        $sql = "SELECT 
                    t.*,
                    tp.nom as type_operation,
                    cl1.numero as numero_source,
                    cl1.nom as nom_source,
                    cl2.numero as numero_destination,
                    cl2.nom as nom_destination
                FROM transactions t
                LEFT JOIN types_operations tp ON tp.id = t.type_operation_id
                LEFT JOIN comptes c1 ON c1.id = t.compte_source
                LEFT JOIN clients cl1 ON cl1.id = c1.client_id
                LEFT JOIN comptes c2 ON c2.id = t.compte_destination
                LEFT JOIN clients cl2 ON cl2.id = c2.client_id
                WHERE t.compte_source = ? OR t.compte_destination = ?
                ORDER BY t.date_transaction DESC";
        
        $transactions = $db->query($sql, [$compte->id, $compte->id])->getResult();
        
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
            'page_title' => 'Historique',
            'current_page' => 'historique',
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

    /**
     * Affiche l'interface de multi-transfert
     */
    public function transfertMultiple()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $clientId = $session->get('client_id');
        $compteModel = new \App\Models\CompteModel();
        $clientModel = new \App\Models\ClientModel();

        $compte = $compteModel->findByClientId($clientId);
        $client = $clientModel->find($clientId);

        return view('Client/transfert-multiple', [
            'compte'       => $compte,
            'client'       => is_array($client) ? (object)$client : $client,
            'solde'        => is_array($compte) ? ($compte['solde'] ?? 0) : ($compte->solde ?? 0),
            'current_page' => 'transfert_multiple'
        ]);
    }

    /**
     * Traite et exécute les transferts multiples
     */
    public function effectuerTransfertMultiple()
    {
        $session = session();
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $clientId = $session->get('client_id');
        $montantTotal = (float) $this->request->getPost('montant_total');
        $numerosRaw   = $this->request->getPost('numeros');

        // Nettoyage de la liste des numéros
        $numeros = array_values(array_filter(array_map('trim', $numerosRaw ?? [])));
        $nbNumeros = count($numeros);

        if ($montantTotal <= 0) {
            return redirect()->back()->with('error', 'Veuillez saisir un montant total valide.');
        }

        if ($nbNumeros === 0) {
            return redirect()->back()->with('error', 'Veuillez saisir au moins un numéro de destinataire.');
        }

        // 1. Calcul de la part individuelle
        $partIndividuelle = $montantTotal / $nbNumeros;
        if ($partIndividuelle < 100) {
            return redirect()->back()->with('error', 'Le montant par destinataire après division (' . number_format($partIndividuelle, 2, ',', ' ') . ' Ar) doit être d\'au moins 100 Ar.');
        }

        // Modèles
        $compteModel        = new \App\Models\CompteModel();
        $clientModel        = new \App\Models\ClientModel();
        $prefixModel        = new \App\Models\PrefixModel();
        $transactionModel   = new \App\Models\TransactionModel();
        $typeOperationModel = new \App\Models\TypeOperationModel();
        $baremeFraisModel   = new \App\Models\BaremeFraisModel();

        // Expéditeur
        $compteExpediteur = $compteModel->findByClientId($clientId);
        if (!$compteExpediteur) {
            return redirect()->back()->with('error', 'Compte expéditeur non trouvé.');
        }

        $compteExpediteurId = is_array($compteExpediteur) ? $compteExpediteur['id'] : $compteExpediteur->id;
        $soldeExpediteur    = is_array($compteExpediteur) ? $compteExpediteur['solde'] : $compteExpediteur->solde;

        $clientExpediteur = $clientModel->find($clientId);
        $numExpediteur    = $clientExpediteur ? (is_array($clientExpediteur) ? $clientExpediteur['numero'] : $clientExpediteur->numero) : '';

        // 2. VÉRIFICATION DU MÊME OPÉRATEUR & PRÉPARATIONS
        $firstOperateurId = null;
        $estNotreOp       = null;
        $commissionTaux   = 0;
        $preparedItems    = [];

        foreach ($numeros as $index => $numero) {
            $lineNo = $index + 1;

            if ($numero === $numExpediteur) {
                return redirect()->back()->with('error', "Destinataire n°{$lineNo} ({$numero}) : Vous ne pouvez pas faire un transfert vers votre propre numéro.");
            }

            $prefixe = substr($numero, 0, 3);
            $prefixData = $prefixModel->findByPrefixeWithOperateur($prefixe);

            if (!$prefixData) {
                return redirect()->back()->with('error', "Destinataire n°{$lineNo} : Le préfixe ({$prefixe}) n'est pas reconnu.");
            }

            $currentOpId    = is_array($prefixData) ? ($prefixData['operateur_id'] ?? null) : ($prefixData->operateur_id ?? null);
            $currentNotreOp = is_array($prefixData) ? ($prefixData['est_notre_operateur'] ?? 0) : ($prefixData->est_notre_operateur ?? 0);
            $currentComm    = is_array($prefixData) ? ($prefixData['commission'] ?? 0) : ($prefixData->commission ?? 0);

            // ⚠️ RÈGLE DE MÊME OPÉRATEUR ⚠️
            if ($firstOperateurId === null) {
                $firstOperateurId = $currentOpId;
                $estNotreOp       = $currentNotreOp;
                $commissionTaux   = $currentComm;
            } else if ($firstOperateurId !== $currentOpId) {
                return redirect()->back()->with('error', "Tous les numéros doivent appartenir au MÊME opérateur. Le numéro {$numero} n'a pas le même opérateur que les précédents.");
            }

            // Destinataire Interne vs Externe
            $compteDestinataireId = null;
            if ($estNotreOp == 1) {
                $clientDest = $clientModel->where('numero', $numero)->first();
                if (!$clientDest) {
                    return redirect()->back()->with('error', "Aucun compte client trouvé pour le numéro interne {$numero}.");
                }
                $clientDestId = is_array($clientDest) ? $clientDest['id'] : $clientDest->id;
                $compteDest = $compteModel->findByClientId($clientDestId);
                if (!$compteDest) {
                    return redirect()->back()->with('error', "Le compte du destinataire {$numero} n'est pas actif.");
                }
                $compteDestinataireId = is_array($compteDest) ? $compteDest['id'] : $compteDest->id;
            }

            $preparedItems[] = [
                'numero'               => $numero,
                'compte_destination'   => $compteDestinataireId
            ];
        }

        // 3. TYPES D'OPÉRATION & FRAIS PAR TRANCHE
        $typeOpNom = ($estNotreOp == 1) ? 'Transfert Interne' : 'Transfert Inter-operateur';
        $typeOp = $typeOperationModel->where('nom', $typeOpNom)->first();
        if (!$typeOp) {
            return redirect()->back()->with('error', "Type d'opération '{$typeOpNom}' non configuré.");
        }
        $typeOpId = is_array($typeOp) ? $typeOp['id'] : $typeOp->id;

        // Calcul des frais sur la part individuelle
        $fraisBaseIndiv = $baremeFraisModel->calculerFrais($typeOpId, $partIndividuelle) ?? 0;
        $fraisCommIndiv = ($estNotreOp == 0 && $commissionTaux > 0) ? ($partIndividuelle * $commissionTaux) / 100 : 0;
        $fraisTotalIndiv = $fraisBaseIndiv + $fraisCommIndiv;

        // Bilan total
        $totalFraisLot = $fraisTotalIndiv * $nbNumeros;
        $coutTotalDebite = $montantTotal + $totalFraisLot;

        // 4. VÉRIFICATION DU SOLDE EXÉCUTION
        if ($soldeExpediteur < $coutTotalDebite) {
            return redirect()->back()->with('error', 'Solde insuffisant. Requis : ' . number_format($coutTotalDebite, 2, ',', ' ') . ' Ar (Montant: ' . number_format($montantTotal, 2, ',', ' ') . ' Ar + Frais totaux: ' . number_format($totalFraisLot, 2, ',', ' ') . ' Ar).');
        }

        // 5. TRANSACTION SQL ATOMIQUE
        $db = \Config\Database::connect();
        $db->transStart();

        // Débit total émetteur
        $compteModel->debiter($compteExpediteurId, $coutTotalDebite);

        // Répartition
        foreach ($preparedItems as $item) {
            if ($item['compte_destination'] !== null) {
                $compteModel->crediter($item['compte_destination'], $partIndividuelle);
            }

            $transactionModel->insert([
                'type_operation_id'        => $typeOpId,
                'compte_source'            => $compteExpediteurId,
                'compte_destination'       => $item['compte_destination'],
                'numero_destination'       => $item['numero'],
                'operateur_destination_id' => $firstOperateurId,
                'montant'                  => $partIndividuelle,
                'frais_base'               => $fraisBaseIndiv,
                'frais_commission_externe' => $fraisCommIndiv,
                'frais_retrait_inclus'     => 0,
                'frais_total'              => $fraisTotalIndiv,
                'date_transaction'         => date('Y-m-d H:i:s')
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Erreur lors du traitement du transfert multiple.');
        }

        return redirect()->to('/client')->with('success', "Montant de " . number_format($montantTotal, 2, ',', ' ') . " Ar divisé et transféré avec succès vers {$nbNumeros} numéros (" . number_format($partIndividuelle, 2, ',', ' ') . " Ar / destinataire).");
    }
    
    
}