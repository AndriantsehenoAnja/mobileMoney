<?php

namespace App\Controllers;

use App\Models\TypeOperationModel;

class TypeOperationController extends BaseController
{
    public function index(): string
    {
        $typeOperationModel = new TypeOperationModel();
        $data = $typeOperationModel->findAll();
        return view("/type_operation/index", ['type_operations' => $data]);
    }

}