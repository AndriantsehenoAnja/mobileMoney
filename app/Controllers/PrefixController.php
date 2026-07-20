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
        $session = session();
        if (!$session->get('admin_logged_in')) {
            return redirect()->to('/login/admin');
        }

        $prefixModel = new PrefixModel();
        
        // Récupérer les préfixes avec les informations de leur opérateur associé
        $prefixes = $prefixModel->getPrefixesAvecOperateur();

        $data = [
            'title'        => 'Gestion des Préfixes',
            'page_title'   => 'Configuration des préfixes réseaux',
            'current_page' => 'prefixe',
            'prefixes'     => $prefixes
        ];

        return view('admin/prefixe/index', $data);
    }

    /**
     * Formulaire de création d'un préfixe
     */
    public function create()
    {
        $session = session();
        if (!$session->get('admin_logged_in')) {
            return redirect()->to('/login/admin');
        }

        $operateurModel = new OperateurModel();
        $operateurs = $operateurModel->findAll();

        $data = [
            'title'        => 'Nouveau Préfixe',
            'page_title'   => 'Ajouter un préfixe',
            'current_page' => 'prefixe',
            'operateurs'   => $operateurs
        ];

        return view('admin/prefixe/form', $data);
    }

    /**
     * Enregistrer un nouveau préfixe
     */
    public function store()
    {
        $session = session();
        if (!$session->get('admin_logged_in')) {
            return redirect()->to('/login/admin');
        }

        $prefixeVal = trim($this->request->getPost('prefixe'));
        $operateurId = $this->request->getPost('operateur_id');
        $commission  = $this->request->getPost('commission') ? (float)$this->request->getPost('commission') : 0;

        // Validation basique
        if (empty($prefixeVal) || empty($operateurId)) {
            return redirect()->back()->withInput()->with('error', 'Le préfixe et l\'opérateur sont obligatoires.');
        }

        $prefixModel = new PrefixModel();

        // Vérifier l'unicité du préfixe
        $existant = $prefixModel->where('prefixe', $prefixeVal)->first();
        if ($existant) {
            return redirect()->back()->withInput()->with('error', 'Ce préfixe existe déjà.');
        }

        $prefixModel->insert([
            'prefixe'      => $prefixeVal,
            'operateur_id' => $operateurId,
            'commission'   => $commission
        ]);

        return redirect()->to('/admin/prefixe')->with('success', 'Préfixe ajouté avec succès.');
    }

    /**
     * Formulaire d'édition d'un préfixe
     */
    public function edit($id = null)
    {
        $session = session();
        if (!$session->get('admin_logged_in')) {
            return redirect()->to('/login/admin');
        }

        $prefixModel = new PrefixModel();
        $prefixe = $prefixModel->find($id);

        if (!$prefixe) {
            return redirect()->to('/admin/prefixe')->with('error', 'Préfixe introuvable.');
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

        return view('admin/prefixe/form', $data);
    }

    /**
     * Mettre à jour un préfixe existant
     */
    public function update($id = null)
    {
        $session = session();
        if (!$session->get('admin_logged_in')) {
            return redirect()->to('/login/admin');
        }

        $prefixModel = new PrefixModel();
        $prefixe = $prefixModel->find($id);

        if (!$prefixe) {
            return redirect()->to('/admin/prefixe')->with('error', 'Préfixe introuvable.');
        }

        $prefixeVal  = trim($this->request->getPost('prefixe'));
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

        return redirect()->to('/admin/prefixe')->with('success', 'Préfixe mis à jour avec succès.');
    }

    /**
     * Supprimer un préfixe
     */
    public function delete($id = null)
    {
        $session = session();
        if (!$session->get('admin_logged_in')) {
            return redirect()->to('/login/admin');
        }

        $prefixModel = new PrefixModel();
        if ($prefixModel->find($id)) {
            $prefixModel->delete($id);
            return redirect()->to('/admin/prefixe')->with('success', 'Préfixe supprimé.');
        }

        return redirect()->to('/admin/prefixe')->with('error', 'Préfixe introuvable.');
    }
}