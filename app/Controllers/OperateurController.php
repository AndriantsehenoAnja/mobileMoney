<?php

namespace App\Controllers;

use App\Models\OperateurModel;

class OperateurController extends BaseController
{
    protected $operateurModel;

    public function __construct()
    {
        $this->operateurModel = new OperateurModel();
    }

    // Afficher la liste des opérateurs
    public function index()
    {
        $data['operateurs'] = $this->operateurModel->findAll();
        return view('admin/operateur/index', $data);
    }

    // Afficher le formulaire d'édition de la commission
    public function edit($id)
    {
        $operateur = $this->operateurModel->find($id);

        if (!$operateur) {
            return redirect()->to('/admin/operateur')->with('error', 'Opérateur non trouvé.');
        }

        $data['operateur'] = $operateur;
        return view('admin/operateur/edit', $data);
    }

    // Mettre à jour la commission
    public function update($id)
    {
        $operateur = $this->operateurModel->find($id);

        if (!$operateur) {
            return redirect()->to('/admin/operateur')->with('error', 'Opérateur non trouvé.');
        }

        $commission = $this->request->getPost('commission');

        // Validation simple
        if (!is_numeric($commission) || $commission < 0) {
            return redirect()->back()->with('error', 'Veuillez saisir un pourcentage valide.');
        }

        $this->operateurModel->update($id, [
            'commission' => $commission
        ]);

        return redirect()->to('/admin/operateur')->with('success', 'Commission mise à jour avec succès.');
    }
}