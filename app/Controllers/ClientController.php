<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\CompteModel;
use App\Models\PrefixModel;
use App\Models\OperateurModel;
use App\Models\TransactionModel;
use App\Models\BaremeFraisModel;

class ClientController extends BaseController
{
    protected $clientModel;
    protected $compteModel;
    protected $prefixModel;
    protected $operateurModel;
    protected $transactionModel;
    protected $baremeModel;

    public function __construct()
    {
        $this->clientModel      = new ClientModel();
        $this->compteModel      = new CompteModel();
        $this->prefixModel      = new PrefixModel();
        $this->operateurModel   = new OperateurModel();
        $this->transactionModel = new TransactionModel();
        $this->baremeModel      = new BaremeFraisModel();
    }

    // ==========================================
    // ACCUEIL & HISTORIQUE
    // ==========================================

    public function index()
    {
        $session = session();
        $clientId = $session->get('client_id');

        $client = $this->clientModel->find($clientId);
        $compte = $this->compteModel->where('client_id', $clientId)->first();

        $data = [
            'client' => $client,
            'compte' => $compte
        ];

        return view('Client/index', $data);
    }

    public function historique()
    {
        $session = session();
        $clientId = $session->get('client_id');
        $compte = $this->compteModel->where('client_id', $clientId)->first();

        $transactions = [];
        if ($compte) {
            $transactions = $this->transactionModel
                ->select('transactions.*, types_operations.nom as type_nom, operateurs.nom as operateur_dest_nom')
                ->join('types_operations', 'types_operations.id = transactions.type_operation_id')
                ->join('operateurs', 'operateurs.id = transactions.operateur_destination_id', 'left')
                ->groupStart()
                    ->where('compte_source', $compte['id'])
                    ->orWhere('compte_destination', $compte['id'])
                ->groupEnd()
                ->orderBy('date_transaction', 'DESC')
                ->findAll();
        }

        return view('Client/historique', ['transactions' => $transactions]);
    }

    // ==========================================
    // DÉPÔT & RETRAIT
    // ==========================================

    public function depot()
    {
        return view('Client/depot');
    }

    public function effectuerDepot()
    {
        $session = session();
        $clientId = $session->get('client_id');
        $montant = (float) $this->request->getPost('montant');

        if ($montant <= 0) {
            return redirect()->back()->with('error', 'Le montant du dépôt doit être supérieur à 0.');
        }

        $compte = $this->compteModel->where('client_id', $clientId)->first();

        // Transaction SQLite
        $db = \Config\Database::connect();
        $db->transStart();

        // Créditer le compte
        $nouveauSolde = $compte['solde'] + $montant;
        $this->compteModel->update($compte['id'], ['solde' => $nouveauSolde]);

        // Enregistrer la transaction (Type 1: Dépôt)
        $this->transactionModel->insert([
            'type_operation_id'        => 1,
            'compte_source'            => $compte['id'],
            'compte_destination'       => $compte['id'],
            'numero_destination'       => $session->get('client_numero'),
            'operateur_destination_id' => 1, // Réseau local
            'montant'                  => $montant,
            'frais_base'               => 0,
            'frais_commission_externe' => 0,
            'frais_retrait_inclus'     => 0,
            'frais_total'              => 0
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Échec du dépôt.');
        }

        return redirect()->to('/client')->with('success', "Dépôt de $montant Ar effectué avec succès.");
    }

    public function retrait()
    {
        return view('Client/retrait');
    }

    public function effectuerRetrait()
    {
        $session = session();
        $clientId = $session->get('client_id');
        $montant = (float) $this->request->getPost('montant');

        if ($montant <= 0) {
            return redirect()->back()->with('error', 'Montant invalide.');
        }

        $compte = $this->compteModel->where('client_id', $clientId)->first();

        // Calcul du frais de retrait (Type 2)
        $bareme = $this->baremeModel->where('type_operation_id', 2)
                                    ->where('montant_min <=', $montant)
                                    ->where('montant_max >=', $montant)
                                    ->first();
        $frais = $bareme ? (float)$bareme['frais'] : 0.0;
        $totalA_Deduire = $montant + $frais;

        if ($compte['solde'] < $totalA_Deduire) {
            return redirect()->back()->with('error', "Solde insuffisant. Requis : $totalA_Deduire Ar (Frais : $frais Ar).");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Déduire le montant + frais
        $this->compteModel->update($compte['id'], ['solde' => $compte['solde'] - $totalA_Deduire]);

        // Enregistrer la transaction
        $this->transactionModel->insert([
            'type_operation_id'        => 2,
            'compte_source'            => $compte['id'],
            'compte_destination'       => NULL,
            'numero_destination'       => $session->get('client_numero'),
            'operateur_destination_id' => 1,
            'montant'                  => $montant,
            'frais_base'               => $frais,
            'frais_commission_externe' => 0,
            'frais_retrait_inclus'     => 0,
            'frais_total'              => $frais
        ]);

        $db->transComplete();

        return redirect()->to('/client')->with('success', "Retrait de $montant Ar effectué (Frais: $frais Ar).");
    }

    // ==========================================
    // VERIFICATION AJAX DESTINATAIRE
    // ==========================================

    public function verifierDestinataire()
    {
        $numero = $this->request->getGet('numero');
        $prefixeStr = substr(trim($numero), 0, 3);

        $prefixe = $this->prefixModel->select('prefixes.*, operateurs.nom as operateur_nom, operateurs.est_notre_operateur')
                                     ->join('operateurs', 'operateurs.id = prefixes.operateur_id')
                                     ->where('prefixes.prefixe', $prefixeStr)
                                     ->first();

        if ($prefixe) {
            return $this->response->setJSON([
                'success' => true,
                'operateur_nom' => $prefixe['operateur_nom'],
                'est_notre_operateur' => (bool)$prefixe['est_notre_operateur']
            ]);
        }

        return $this->response->setJSON(['success' => false]);
    }

    // ==========================================
    // TRANSFERT SIMPLE (V2)
    // ==========================================

    public function transfert()
    {
        return view('Client/transfert');
    }

    public function effectuerTransfert()
    {
        $session = session();
        $clientId = $session->get('client_id');

        $numeroDest = trim($this->request->getPost('numero_destination'));
        $montant = (float) $this->request->getPost('montant');
        $inclureFraisRetrait = (bool) $this->request->getPost('inclure_frais_retrait');

        if ($montant <= 0 || empty($numeroDest)) {
            return redirect()->back()->with('error', 'Données du formulaire invalides.');
        }

        // Vérification de l'opérateur du destinataire
        $prefixeStr = substr($numeroDest, 0, 3);
        $prefixeInfo = $this->prefixModel->select('prefixes.*, operateurs.id as op_id, operateurs.nom as op_nom, operateurs.commission, operateurs.est_notre_operateur')
                                         ->join('operateurs', 'operateurs.id = prefixes.operateur_id')
                                         ->where('prefixes.prefixe', $prefixeStr)
                                         ->first();

        if (!$prefixeInfo) {
            return redirect()->back()->with('error', 'Numéro destinataire non reconnu.');
        }

        $estNotreOperateur = (bool) $prefixeInfo['est_notre_operateur'];
        $typeOperationId = $estNotreOperateur ? 3 : 4; // 3: Interne, 4: Inter-opérateur

        // RÈGLE V2 : Pas de frais de retrait inclus pour les autres opérateurs
        if (!$estNotreOperateur) {
            $inclureFraisRetrait = false;
        }

        // 1. Frais de base (Barème)
        $bareme = $this->baremeModel->where('type_operation_id', $typeOperationId)
                                    ->where('montant_min <=', $montant)
                                    ->where('montant_max >=', $montant)
                                    ->first();
        $fraisBase = $bareme ? (float)$bareme['frais'] : 0.0;

        // 2. Frais de commission externe (% si autre opérateur)
        $fraisCommission = 0.0;
        if (!$estNotreOperateur && $prefixeInfo['commission'] > 0) {
            $fraisCommission = ($montant * (float)$prefixeInfo['commission']) / 100;
        }

        // 3. Frais de retrait inclus (si applicable et coché)
        $fraisRetraitInclus = 0.0;
        if ($inclureFraisRetrait && $estNotreOperateur) {
            $baremeRetrait = $this->baremeModel->where('type_operation_id', 2)
                                               ->where('montant_min <=', $montant)
                                               ->where('montant_max >=', $montant)
                                               ->first();
            $fraisRetraitInclus = $baremeRetrait ? (float)$baremeRetrait['frais'] : 0.0;
        }

        $fraisTotal = $fraisBase + $fraisCommission + $fraisRetraitInclus;
        $totalA_Deduire = $montant + $fraisTotal;

        $compteSource = $this->compteModel->where('client_id', $clientId)->first();

        if ($compteSource['solde'] < $totalA_Deduire) {
            return redirect()->back()->with('error', "Solde insuffisant. Total à prélever : $totalA_Deduire Ar (Frais totaux : $fraisTotal Ar).");
        }

        // Identifier si le destinataire possède un compte chez nous
        $clientDest = $this->clientModel->where('numero', $numeroDest)->first();
        $compteDest = $clientDest ? $this->compteModel->where('client_id', $clientDest['id'])->first() : null;

        $db = \Config\Database::connect();
        $db->transStart();

        // Déduire le solde de l'expéditeur
        $this->compteModel->update($compteSource['id'], ['solde' => $compteSource['solde'] - $totalA_Deduire]);

        // Créditer le destinataire s'il est dans notre réseau
        if ($compteDest) {
            $montantACrediter = $montant + $fraisRetraitInclus;
            $this->compteModel->update($compteDest['id'], ['solde' => $compteDest['solde'] + $montantACrediter]);
        }

        // Enregistrer la transaction
        $this->transactionModel->insert([
            'type_operation_id'        => $typeOperationId,
            'compte_source'            => $compteSource['id'],
            'compte_destination'       => $compteDest ? $compteDest['id'] : NULL,
            'numero_destination'       => $numeroDest,
            'operateur_destination_id' => $prefixeInfo['op_id'],
            'montant'                  => $montant,
            'frais_base'               => $fraisBase,
            'frais_commission_externe' => $fraisCommission,
            'frais_retrait_inclus'     => $fraisRetraitInclus,
            'frais_total'              => $fraisTotal
        ]);

        $db->transComplete();

        return redirect()->to('/client')->with('success', "Transfert de $montant Ar à $numeroDest réussi !");
    }

    // ==========================================
    // TRANSFERT MULTIPLE (V2)
    // ==========================================

    public function transfertMultiple()
    {
        return view('Client/transfert-multiple');
    }

    public function effectuerTransfertMultiple()
    {
        $session = session();
        $clientId = $session->get('client_id');

        $rawNumeros = $this->request->getPost('numeros');
        $montantTotal = (float) $this->request->getPost('montant_total');

        $numeros = array_unique(array_filter(array_map('trim', preg_split('/[\s,]+/', $rawNumeros))));

        if (empty($numeros) || $montantTotal <= 0) {
            return redirect()->back()->with('error', 'Saisie invalide ou montant égal à zéro.');
        }

        // RÈGLE V2 : Tous les numéros doivent appartenir au même opérateur (Notre réseau)
        foreach ($numeros as $num) {
            $prefixeStr = substr($num, 0, 3);
            $prefixeInfo = $this->prefixModel->select('prefixes.*, operateurs.est_notre_operateur')
                                             ->join('operateurs', 'operateurs.id = prefixes.operateur_id')
                                             ->where('prefixes.prefixe', $prefixeStr)
                                             ->first();

            if (!$prefixeInfo || !$prefixeInfo['est_notre_operateur']) {
                return redirect()->back()->with('error', "Le numéro $num n'appartient pas à notre réseau. Le transfert multiple est strictement réservé au réseau local.");
            }
        }

        $nombreDestinataires = count($numeros);
        $montantUnitaire = $montantTotal / $nombreDestinataires;

        // Calcul des frais par transaction unitaire (Type 3: Transfert Interne)
        $bareme = $this->baremeModel->where('type_operation_id', 3)
                                    ->where('montant_min <=', $montantUnitaire)
                                    ->where('montant_max >=', $montantUnitaire)
                                    ->first();
        $fraisUnitaire = $bareme ? (float)$bareme['frais'] : 0.0;

        $fraisTotalGlobal = $fraisUnitaire * $nombreDestinataires;
        $totalA_DeduireGlobal = $montantTotal + $fraisTotalGlobal;

        $compteSource = $this->compteModel->where('client_id', $clientId)->first();

        if ($compteSource['solde'] < $totalA_DeduireGlobal) {
            return redirect()->back()->with('error', "Solde insuffisant pour exécuter l'envoi multiple. Montant requis : $totalA_DeduireGlobal Ar (dont $fraisTotalGlobal Ar de frais total).");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Déduction du solde
        $this->compteModel->update($compteSource['id'], ['solde' => $compteSource['solde'] - $totalA_DeduireGlobal]);

        // Effectuer chaque transfert
        foreach ($numeros as $num) {
            $clientDest = $this->clientModel->where('numero', $num)->first();
            $compteDest = $clientDest ? $this->compteModel->where('client_id', $clientDest['id'])->first() : null;

            if ($compteDest) {
                $this->compteModel->update($compteDest['id'], ['solde' => $compteDest['solde'] + $montantUnitaire]);
            }

            $this->transactionModel->insert([
                'type_operation_id'        => 3,
                'compte_source'            => $compteSource['id'],
                'compte_destination'       => $compteDest ? $compteDest['id'] : NULL,
                'numero_destination'       => $num,
                'operateur_destination_id' => 1,
                'montant'                  => $montantUnitaire,
                'frais_base'               => $fraisUnitaire,
                'frais_commission_externe' => 0,
                'frais_retrait_inclus'     => 0,
                'frais_total'              => $fraisUnitaire
            ]);
        }

        $db->transComplete();

        return redirect()->to('/client')->with('success', "Envoi multiple de $montantTotal Ar ($montantUnitaire Ar x $nombreDestinataires numéros) exécuté avec succès !");
    }
}