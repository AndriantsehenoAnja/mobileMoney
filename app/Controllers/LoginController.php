<?php

namespace App\Controllers;

use App\Models\ClientModel;

class LoginController extends BaseController
{
    public function aller(){
        return view("index");
    }
    public function index(): string
    {
        $session = session();
        if ($session->get('logged_in')) {
            return redirect()->to('/Client');
        }
        
        return view('Client/login');
    }

    public function login()
    {
        $clientModel = new ClientModel();
        
        $numero = $this->request->getPost('numero');
        
        if (empty($numero)) {
            return redirect()->back()->with('error', 'Veuillez saisir votre numéro de téléphone');
        }

        $verification = $clientModel->verifierNumeroClient($numero);
        
        if (!$verification['valid']) {
            return redirect()->back()->with('error', $verification['message']);
        }

        $session = session();
        $session->set([
            'client_id' => $verification['client_id'],
            'client_nom' => $verification['client_nom'],
            'client_numero' => $verification['client_numero'],
            'logged_in' => true
        ]);

        return redirect()->to('/Client')->with('success', 'Connexion réussie');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to('/login')->with('success', 'Vous avez été déconnecté');
    }
}