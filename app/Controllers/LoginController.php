<?php

namespace App\Controllers;

use App\Models\ClientModel;

class LoginController extends BaseController
{
    public function index(): string
    {
        return view('login');
    }
    public function login()
    {
        $clientModel = model('ClientModel');
        
        $numero = $this->request->getPost('numero');
        
        if (empty($numero)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Veuillez saisir votre numéro de téléphone'
            ]);
        }

        $verification = $clientModel->verifierNumeroClient($numero);
        
        if (!$verification['valid']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $verification['message']
            ]);
        }

        $session = session();
        $session->set([
            'client_id' => $verification['client']->id,
            'client_nom' => $verification['client']->nom,
            'client_numero' => $verification['client']->numero,
            'logged_in' => true
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Connexion réussie',
            'client' => [
                'id' => $verification['client']->id,
                'nom' => $verification['client']->nom,
                'numero' => $clientModel->formaterNumero($verification['client']->numero),
                'prefixe' => $verification['prefixe']
            ]
        ]);
    }

    public function verifierNumero()
    {
        $clientModel = model('ClientModel');
        $numero = $this->request->getGet('numero');
        
        if (empty($numero)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Numéro requis'
            ]);
        }

        $verification = $clientModel->verifierNumeroClient($numero);
        
        return $this->response->setJSON([
            'success' => $verification['valid'],
            'message' => $verification['message'],
            'data' => $verification['valid'] ? [
                'client' => $verification['client'],
                'prefixe' => $verification['prefixe']
            ] : null
        ]);
    }
}