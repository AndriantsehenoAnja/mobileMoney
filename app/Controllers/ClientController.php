<?php

namespace App\Controllers;

class ClientController extends BaseController
{
    public function index(): string
    {
        return view('/Client/index');
    }
}
