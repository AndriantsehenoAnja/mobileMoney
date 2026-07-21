<?php

namespace App\Controllers;

use App\Models\PrefixModel;
use App\Models\OperateurModel;

class PrefixController extends BaseController
{
    /**
     * Liste des préfixes
     */
    public function index()
    {
        // $session = session();
        // if (!$session->get('admin_logged_in')) {
        //     return redirect()->to('/login/admin');
        // }

        $prefixModel = new PrefixModel();
        
        // Récupérer les préfixes avec les informations de leur opérateur associé
        $prefixes = $prefixModel->getPrefixesAvecOperateur();

        $data = [
            'title'        => 'Gestion des Préfixes',
            'page_title'   => 'Configuration des préfixes réseaux',
            'current_page' => 'prefixe',
            'prefixes'     => $prefixes
        ];

        return view('admin/prefix/index', $data);
    }

    /**
     * Formulaire de création d'un préfixe
     */
    public function create()
    {
        // $session = session();
        // if (!$session->get('admin_logged_in')) {
        //     return redirect()->to('/login/admin');
        // }

        $operateurModel = new OperateurModel();
        $operateurs = $operateurModel->findAll();

        $data = [
            'title'        => 'Nouveau Préfixe',
            'page_title'   => 'Ajouter un préfixe',
            'current_page' => 'prefixe',
            'operateurs'   => $operateurs
        ];

        return view('admin/prefix/form', $data);
    }

    /**
     * Enregistrer un nouveau préfixe
     */
   public function store()
    {
        // 1. Récupération des données du formulaire
        $prefixeVal        = trim($this->request->getPost('prefixe'));
        $nomOperateur      = trim($this->request->getPost('nom_operateur'));
        $estNotreOperateur = (int)$this->request->getPost('est_notre_operateur');
        $commission        = $this->request->getPost('commission') ? (float)$this->request->getPost('commission') : 0.0;

        // 2. Validation basique
        if (empty($prefixeVal) || empty($nomOperateur)) {
            return redirect()->back()->withInput()->with('error', 'Le préfixe et le nom de l\'opérateur sont obligatoires.');
        }

        $prefixModel    = new PrefixModel();
        $operateurModel = new OperateurModel();

        // 3. Vérifier l'unicité du préfixe
        $existant = $prefixModel->where('prefixe', $prefixeVal)->first();
        if ($existant) {
            return redirect()->back()->withInput()->with('error', "Le préfixe '{$prefixeVal}' existe déjà.");
        }

        // 4. Gestion de l'opérateur (Création ou Récupération)
        $operateur = $operateurModel->where('nom', $nomOperateur)->first();

        if ($operateur) {
            $operateurId = $operateur->id;
        } else {
            // Création du nouvel opérateur
            $operateurId = $operateurModel->insert([
                'nom'                  => $nomOperateur,
                'commission'           => ($estNotreOperateur == 1) ? 0 : $commission,
                'est_notre_operateur'  => $estNotreOperateur
            ]);
        }

        // 5. Enregistrement du préfixe lié à l'opérateur
        $prefixModel->insert([
            'prefixe'      => $prefixeVal,
            'operateur_id' => $operateurId
        ]);

        return redirect()->to('/admin/prefix')->with('success', 'Préfixe enregistré avec succès.');
    }

    /**
     * Formulaire d'édition d'un préfixe
     */
    public function edit($id = null)
    {
        // $session = session();
        // if (!$session->get('admin_logged_in')) {
        //     return redirect()->to('/login/admin');
        // }

        $prefixModel = new PrefixModel();
        $prefixe = $prefixModel->find($id);

        if (!$prefixe) {
            return redirect()->to('/admin/prefix')->with('error', 'Préfixe introuvable.');
        }

        $operateurModel = new OperateurModel();
        $operateurs = $operateurModel->findAll();

        $data = [
            'title'        => 'Modifier le Préfixe',
            'page_title'   => 'Modifier le préfixe ' . $prefixe['prefixe'],
            'current_page' => 'prefixe',
            'prefixe'      => $prefixe,
            'operateurs'   => $operateurs
        ];

        return view('admin/prefix/form', $data);
    }

    /**
     * Mettre à jour un préfixe existant
     */
    public function update($id = null)
    {
        // $session = session();
        // if (!$session->get('admin_logged_in')) {
        //     return redirect()->to('/login/admin');
        // }

        $prefixModel = new PrefixModel();
        $prefixe = $prefixModel->find($id);

        if (!$prefixe) {
            return redirect()->to('/admin/prefix')->with('error', 'Préfixe introuvable.');
        }

        $prefixeVal  = $this->request->getPost('prefixe');
        $operateurId = $this->request->getPost('operateur_id');
        $commission  = $this->request->getPost('commission') ? (float)$this->request->getPost('commission') : 0;

        if (empty($prefixeVal) || empty($operateurId)) {
            return redirect()->back()->withInput()->with('error', 'Le préfixe et l\'opérateur sont obligatoires.');
        }

        // Vérifier l'unicité du préfixe hors de lui-même
        $existant = $prefixModel->where('prefixe', $prefixeVal)->where('id !=', $id)->first();
        if ($existant) {
            return redirect()->back()->withInput()->with('error', 'Ce préfixe est déjà utilisé par un autre enregistrement.');
        }

        $prefixModel->update($id, [
            'prefixe'      => $prefixeVal,
            'operateur_id' => $operateurId,
            'commission'   => $commission
        ]);

        return redirect()->to('/admin/prefix')->with('success', 'Préfixe mis à jour avec succès.');
    }

    /**
     * Supprimer un préfixe
     */
    public function delete($id = null)
    {
        // $session = session();
        // if (!$session->get('admin_logged_in')) {
        //     return redirect()->to('/login/admin');
        // }

        $prefixModel = new PrefixModel();
        if ($prefixModel->find($id)) {
            $prefixModel->delete($id);
            return redirect()->to('/admin/prefix')->with('success', 'Préfixe supprimé.');
        }

        return redirect()->to('/admin/prefix')->with('error', 'Préfixe introuvable.');
    }
}