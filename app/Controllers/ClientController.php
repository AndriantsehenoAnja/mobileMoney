<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\CompteModel;
use App\Models\TransactionModel;
use App\Models\TypeOperationModel;
use App\Models\BaremeFraisModel;
use App\Models\OperateurModel;

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
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }

        $clientId = $session->get('client_id');
        $clientNumero = $session->get('client_numero');

        $montantGlobal = (float) $this->request->getPost('montant');
        $numerosBruts  = $this->request->getPost('numeros_destinataires'); // Peut être une chaîne séparée par des virgules/espaces ou un tableau
        $inclureFraisRetrait = $this->request->getPost('inclure_frais_retrait') ? true : false;

        // 1. Validation du montant global
        if (empty($montantGlobal) || $montantGlobal <= 0) {
            return redirect()->back()->with('error', 'Veuillez saisir un montant valide');
        }

        // 2. Traitement et nettoyage des numéros
        if (is_string($numerosBruts)) {
            // Sépare par virgule, point-virgule, espace ou retour à la ligne
            $listeNumeros = preg_split('/[\s,;]+/', trim($numerosBruts));
        } else {
            $listeNumeros = (array) $numerosBruts;
        }

        // Filtrer les valeurs vides
        $listeNumeros = array_filter(array_map('trim', $listeNumeros));

        if (empty($listeNumeros)) {
            return redirect()->back()->with('error', 'Veuillez saisir au moins un numéro destinataire');
        }

        $nombreDestinataires = count($listeNumeros);
        $estMultiple = ($nombreDestinataires > 1);

        // 3. Initialisation des Modèles
        $clientModel         = new ClientModel();
        $compteModel         = new CompteModel();
        $transactionModel    = new TransactionModel();
        $typeOperationModel  = new TypeOperationModel();
        $baremeFraisModel    = new BaremeFraisModel();
        $prefixModel         = new PrefixModel();

        $compteSource = $compteModel->findByClientId($clientId);
        if (!$compteSource) {
            return redirect()->back()->with('error', 'Compte source non trouvé');
        }

        // 4. VERIFICATION V2 : Envoi Multiple = Même opérateur uniquement
        if ($estMultiple) {
            foreach ($listeNumeros as $num) {
                $pref = substr($num, 0, 3);
                if (!$prefixModel->estNotrePrefixe($pref)) {
                    return redirect()->back()->with('error', "L'envoi multiple est réservé uniquement aux numéros de notre réseau. Le numéro $num appartient à un autre opérateur.");
                }
            }
        }

        // 5. Calcul du montant par destinataire
        $montantParPersonne = $montantGlobal / $nombreDestinataires;

        if ($montantParPersonne < 100) {
            return redirect()->back()->with('error', 'Le montant unitaire minimum par destinataire est de 100 Ar');
        }

        // Récupérer les types d'opérations
        $typeTransfert = $typeOperationModel->where('nom', 'Transfert')->first();
        $typeRetrait   = $typeOperationModel->where('nom', 'Retrait')->first();

        // 6. Analyse des destinataires et calcul des frais totaux
        $destinatairesData = [];
        $fraisBaseTotal = 0;
        $commissionExterneTotal = 0;
        $fraisRetraitInclusTotal = 0;

        foreach ($listeNumeros as $num) {
            if ($num === $clientNumero) {
                return redirect()->back()->with('error', 'Vous ne pouvez pas effectuer un transfert vers votre propre numéro');
            }

            $pref = substr($num, 0, 3);
            $estNotreReseau = $prefixModel->estNotrePrefixe($pref);
            $prefixInfo = $prefixModel->findByPrefixeWithOperateur($pref);

            // Recherche si le client existe chez nous
            $destClient = $clientModel->findByNumero($num);
            $destCompte = $destClient ? $compteModel->findByClientId($destClient->id) : null;

            // Calcul des frais de transfert de base[cite: 1, 3]
            $fraisBase = $baremeFraisModel->calculerFrais($typeTransfert['id'], $montantParPersonne);
            
            // Calcul commission réseau externe si applicable
            $commExterne = 0;
            if (!$estNotreReseau && isset($prefixInfo['commission'])) {
                $commExterne = ($montantParPersonne * $prefixInfo['commission']) / 100;
            }

            // Calcul frais de retrait inclus (V2: PAS de frais de retrait si autre opérateur)
            $fraisRetraitInc = 0;
            if ($inclureFraisRetrait && $estNotreReseau && $typeRetrait) {
                $fraisRetraitInc = $baremeFraisModel->calculerFrais($typeRetrait['id'], $montantParPersonne);
            }

            $fraisTotalUnitaire = $fraisBase + $commExterne + $fraisRetraitInc;

            $fraisBaseTotal          += $fraisBase;
            $commissionExterneTotal  += $commExterne;
            $fraisRetraitInclusTotal += $fraisRetraitInc;

            $destinatairesData[] = [
                'numero'               => $num,
                'est_notre_reseau'     => $estNotreReseau,
                'operateur_id'         => $prefixInfo['operateur_id'] ?? null,
                'client_dest'          => $destClient,
                'compte_dest'          => $destCompte,
                'frais_base'           => $fraisBase,
                'commission_externe'   => $commExterne,
                'frais_retrait_inclus' => $fraisRetraitInc,
                'frais_total'          => $fraisTotalUnitaire,
            ];
        }

        // 7. Vérification du solde global nécessaire
        $fraisGlobaux = $fraisBaseTotal + $commissionExterneTotal + $fraisRetraitInclusTotal;
        $montantTotalADebiter = $montantGlobal + $fraisGlobaux;

        if ($compteSource->solde < $montantTotalADebiter) {
            return redirect()->back()->with('error', 'Solde insuffisant. Montant requis (avec frais) : ' . number_format($montantTotalADebiter, 2) . ' Ar. Solde dispo : ' . number_format($compteSource->solde, 2) . ' Ar');
        }

        // 8. Exécution de la transaction SQL
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Débiter le montant global + frais du compte source
            $compteModel->debiter($compteSource->id, $montantTotalADebiter);

            foreach ($destinatairesData as $dest) {
                // Créditer le destinataire s'il est un client de notre réseau
                if ($dest['est_notre_reseau'] && $dest['compte_dest']) {
                    // Si l'option "inclure frais de retrait" est cochée, on ajoute les frais de retrait au montant crédité
                    $montantAValiderSurCompte = $montantParPersonne + $dest['frais_retrait_inclus'];
                    $compteModel->crediter($dest['compte_dest']->id, $montantAValiderSurCompte);
                }

                // Enregistrer chaque transaction
                $transactionModel->insert([
                    'type_operation_id'        => $typeTransfert['id'],
                    'compte_source'            => $compteSource->id,
                    'compte_destination'       => $dest['compte_dest'] ? $dest['compte_dest']->id : null,
                    'numero_destination'       => $dest['numero'],
                    'operateur_destination_id' => $dest['operateur_id'],
                    'montant'                  => $montantParPersonne,
                    'frais'                    => $dest['frais_base'],
                    'frais_commission_externe' => $dest['commission_externe'],
                    'frais_retrait_inclus'     => $dest['frais_retrait_inclus'],
                    'frais_total'              => $dest['frais_total']
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Erreur pendant le traitement du transfert.');
            }

            $msg = $estMultiple 
                ? 'Transfert multiple de ' . number_format($montantGlobal, 2) . " Ar vers $nombreDestinataires numéros effectué avec succès."
                : 'Transfert de ' . number_format($montantGlobal, 2) . ' Ar vers ' . $listeNumeros[0] . ' effectué avec succès.';

            return redirect()->to('/Client')->with('success', $msg);

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Échec du transfert : ' . $e->getMessage());
        }
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
     * Profil utilisateur
     */
    public function profile()
    {
        $session = session();
        
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter');
        }
        
        $clientId = $session->get('client_id');
        $clientModel = new ClientModel();
        $compteModel = new CompteModel();
        
        $client = $clientModel->find($clientId);
        $compte = $compteModel->findByClientId($clientId);
        
        $data = [
            'title' => 'Mon Profil - Mobile Money',
            'page_title' => 'Mon Profil',
            'current_page' => 'profile',
            'client' => $client,
            'compte' => $compte,
            'solde' => $compte ? $compte->solde : 0
        ];
        
        return view('Client/profile', $data);
    }
}