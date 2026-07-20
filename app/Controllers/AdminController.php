<?php

namespace App\Controllers;

use App\Models\CompteModel;
use App\Models\OperateurModel;
use App\Models\PrefixeExterneModel;
use App\Models\TransactionModel;

class AdminController extends BaseController
{
    public function index()
    {
        $compteModel = new CompteModel();
        $transactionModel = new TransactionModel();

        // 1. Récupération des indicateurs globaux
        $masseMonetaire = $compteModel->getSoldeTotal();
        $nombreComptes   = $compteModel->countComptes();
        
        // 2. Calcul des gains totaux
        $gainsParType = $transactionModel->getGainTotalParType();
        $gainGlobal = 0;
        foreach ($gainsParType as $gain) {
            $gainGlobal += $gain->total_gain;
        }

        // 3. Récupérer les dernières transactions
        $dernieresTransactions = $transactionModel->select('transactions.*, types_operations.nom as type_nom')
                                                 ->join('types_operations', 'types_operations.id = transactions.type_operation_id')
                                                 ->orderBy('transactions.id', 'DESC')
                                                 ->findAll(5);

        return view('admin/dashboard', [
            'masseMonetaire'        => $masseMonetaire,
            'nombreComptes'          => $nombreComptes,
            'gainGlobal'            => $gainGlobal,
            'dernieresTransactions' => $dernieresTransactions
        ]);
    }

    /* -------------------------------------------------------------------------- */
    /*                         GESTION DES OPERATEURS (CRUD)                       */
    /* -------------------------------------------------------------------------- */

    /**
     * Liste de tous les opérateurs
     */
    public function operateurs()
    {
        $operateurModel = new OperateurModel();

        return view('admin/operateurs/index', [
            'operateurs' => $operateurModel->findAll()
        ]);
    }

    /**
     * Formulaire d'ajout + Traitement de la création
     */
    public function operateurAdd()
    {
        $operateurModel = new OperateurModel();

        if ($this->request->is('post')) {
            $data = [
                'nom'        => $this->request->getPost('nom'),
                'commission' => $this->request->getPost('commission') ?: 0,
            ];

            if ($operateurModel->insert($data)) {
                return redirect()->to(base_url('admin/operateurs'))
                                 ->with('success', 'Opérateur ajouté avec succès.');
            }

            return redirect()->back()
                             ->withInput()
                             ->with('errors', $operateurModel->errors());
        }

        return view('admin/operateurs/add');
    }

    /**
     * Formulaire de modification + Traitement de la mise à jour
     */
    public function operateurEdit($id = null)
    {
        $operateurModel = new OperateurModel();
        $operateur      = $operateurModel->find($id);

        if (!$operateur) {
            return redirect()->to(base_url('admin/operateurs'))
                             ->with('error', 'Opérateur introuvable.');
        }

        if ($this->request->is('post')) {
            $data = [
                'id'         => $id,
                'nom'        => $this->request->getPost('nom'),
                'commission' => $this->request->getPost('commission') ?: 0,
            ];

            if ($operateurModel->save($data)) {
                return redirect()->to(base_url('admin/operateurs'))
                                 ->with('success', 'Opérateur mis à jour avec succès.');
            }

            return redirect()->back()
                             ->withInput()
                             ->with('errors', $operateurModel->errors());
        }

        return view('admin/operateurs/edit', [
            'operateur' => $operateur
        ]);
    }

    /**
     * Suppression d'un opérateur
     */
    public function operateurDelete($id = null)
    {
        $operateurModel = new OperateurModel();
        $operateur      = $operateurModel->find($id);

        if (!$operateur) {
            return redirect()->to(base_url('admin/operateurs'))
                             ->with('error', 'Opérateur introuvable.');
        }

        // Vérification optionnelle : vérifier s'il existe des préfixes liés
        $prefixeModel = new PrefixeExterneModel();
        if ($prefixeModel->where('operateur_id', $id)->first()) {
            return redirect()->to(base_url('admin/operateurs'))
                             ->with('error', 'Impossible de supprimer cet opérateur car des préfixes y sont associés.');
        }

        $operateurModel->delete($id);

        return redirect()->to(base_url('admin/operateurs'))
                         ->with('success', 'Opérateur supprimé avec succès.');
    }
    /* -------------------------------------------------------------------------- */
    /*                    GESTION DES PREFIXES EXTERNES (CRUD)                     */
    /* -------------------------------------------------------------------------- */

    /**
     * Liste de tous les préfixes externes avec leurs opérateurs associés
     */
    public function prefixesExternes()
    {
        $prefixeExterneModel = new PrefixeExterneModel();

        return view('admin/prefixes_externes/index', [
            'prefixes' => $prefixeExterneModel->getPrefixesWithOperateur()
        ]);
    }

    /**
     * Formulaire d'ajout + Traitement de la création
     */
    public function prefixeExterneAdd()
    {
        $prefixeExterneModel = new PrefixeExterneModel();
        $operateurModel      = new OperateurModel();

        if ($this->request->is('post')) {
            $data = [
                'operateur_id' => $this->request->getPost('operateur_id'),
                'prefixe'      => trim($this->request->getPost('prefixe')),
            ];

            if ($prefixeExterneModel->insert($data)) {
                return redirect()->to(base_url('admin/prefixes-externes'))
                                 ->with('success', 'Préfixe externe ajouté avec succès.');
            }

            return redirect()->back()
                             ->withInput()
                             ->with('errors', $prefixeExterneModel->errors());
        }

        return view('admin/prefixes_externes/add', [
            'operateurs' => $operateurModel->findAll()
        ]);
    }

    /**
     * Suppression d'un préfixe externe
     */
    public function prefixeExterneDelete($id = null)
    {
        $prefixeExterneModel = new PrefixeExterneModel();
        $prefixe             = $prefixeExterneModel->find($id);

        if (!$prefixe) {
            return redirect()->to(base_url('admin/prefixes-externes'))
                             ->with('error', 'Préfixe externe introuvable.');
        }

        $prefixeExterneModel->delete($id);

        return redirect()->to(base_url('admin/prefixes-externes'))
                         ->with('success', 'Préfixe externe supprimé avec succès.');
    }
}