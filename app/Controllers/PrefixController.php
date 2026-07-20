<?php

namespace App\Controllers;

use App\Models\PrefixModel;

class PrefixController extends BaseController
{
    public function index(): string
    {
        $prefixModel = new PrefixModel();
        $data = $prefixModel->findAll();
        return view("prefix/index", ['prefixes' => $data]);
    }

    public function form()
    {
        return view("prefix/form");
    }

    public function create()
    {
        // Sécurité : On vérifie que c'est bien une requête POST
        if (!$this->request->is('post')) {
            return redirect()->to('/prefix/form');
        }

        $prefixModel = new PrefixModel();
        $data = [
            'prefixe' => $this->request->getPost('prefixe')
        ];

        // insert() renvoie l'ID généré ou false en cas d'échec de validation
        if ($prefixModel->insert($data) !== false) {
            return redirect()->to('/prefix')->with('success', 'Préfixe créé avec succès.');
        }

        // Si l'insertion échoue (ex: doublon), on recharge le formulaire en renvoyant les erreurs
        return view("prefix/form", [
            'errors' => $prefixModel->errors(),
            'old'    => $data // Permet de réafficher ce que l'utilisateur avait tapé
        ]);
    }

    public function edit($id)
    {
        $prefixModel = new PrefixModel();
        $prefix = $prefixModel->find($id);
        
        if (!$prefix) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Préfixe non trouvé : " . $id);
        }
        
        return view("prefix/edit", ['prefix' => $prefix]);
    }
    
    public function update($id)
    {
        $prefixModel = new PrefixModel();

        if ($this->request->is('post')) {
            $data = [
                'prefixe' => $this->request->getPost('prefixe')
            ];

            if ($prefixModel->update($id, $data)) {
                return redirect()->to('/prefix')->with('success', 'Préfixe mis à jour.');
            }

            // En cas d'erreur lors de la modification (ex: le préfixe existe déjà ailleurs)
            return view("prefix/edit", [
                'prefix' => $prefixModel->find($id),
                'errors' => $prefixModel->errors()
            ]);
        }
        
        return redirect()->to('/prefix/edit/' . $id);
    }

    public function delete($id)
    {
        $prefixModel = new PrefixModel();
        
        if ($prefixModel->find($id) && $prefixModel->delete($id)) {
            return redirect()->to('/prefix')->with('success', 'Préfixe supprimé.');
        }
        
        throw new \CodeIgniter\Exceptions\PageNotFoundException("Erreur lors de la suppression du préfixe : " . $id);
    }
}