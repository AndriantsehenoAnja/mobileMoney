<?php

namespace App\Controllers;

use App\Models\BaremeFraisModel;
use App\Models\TypeOperationModel;
class BaremeController extends BaseController
{
    public function index()
    {
        $baremeModel = new BaremeFraisModel();
        $baremes = $baremeModel->findAll();

        return view("admin/bareme/index", ['baremes' => $baremes]);
    }
    public function showbyTypeOperation($id)
    {
        $baremeModel = new BaremeFraisModel();
        $typeOperationModel = new TypeOperationModel();
        $type_operation = $typeOperationModel->find($id);
        $baremes = $baremeModel->where('type_operation_id', $id)->findAll();

        if (!$baremes) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Baremes with Type Operation ID $id not found");
        }

        return view("admin/bareme/show", ['baremes' => $baremes, 'type_operation' => $type_operation]);
    }

    public function edit($id)
    {
        $baremeModel = new BaremeFraisModel();
        $typeOperationModel = new TypeOperationModel();
        $bareme = $baremeModel->find($id);

        if (!$bareme) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Bareme not found: " . $id);
        }

        $type_operations = $typeOperationModel->findAll();

        return view("admin/bareme/edit", ['bareme' => $bareme, 'type_operations' => $type_operations]);
    }
    
    public function update($id)
    {
        $baremeModel = new BaremeFraisModel();
        $typeOperationModel = new TypeOperationModel();
        
        $bareme = $baremeModel->find($id);

        if (!$bareme) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Bareme not found: " . $id);
        }

        if ($this->request->is('post')) {
            $data = [
                'type_operation_id' => $this->request->getPost('type_operation_id'),
                'montant_min'       => $this->request->getPost('montant_min'),
                'montant_max'       => $this->request->getPost('montant_max'),
                'frais'             => $this->request->getPost('frais'),
            ];

            if ($baremeModel->update($id, $data)) {
                return redirect()->to('/admin/bareme/showbytypeoperation/' . $data['type_operation_id'])
                                ->with('success', 'Bareme updated successfully.');
            } else {
                return view("admin/bareme/edit", [
                    'errors'          => $baremeModel->errors(),
                    'bareme'          => array_merge($bareme, $data),
                    'type_operations' => $typeOperationModel->findAll()
                ]);
            }
        }

        // Affichage initial du formulaire d'édition (GET)
        return view("admin/bareme/edit", [
            'bareme'          => $bareme, 
            'type_operations' => $typeOperationModel->findAll()
        ]);
    }

    public function addBareme()
    {
        $baremeModel = new BaremeFraisModel();

        if ($this->request->is('post')) {
            $data = [
                'type_operation_id' => $this->request->getPost('type_operation_id'),
                'montant_min' => $this->request->getPost('montant_min'),
                'montant_max' => $this->request->getPost('montant_max'),
                'frais' => $this->request->getPost('frais'),
            ];

            if ($baremeModel->insert($data)) {
                return redirect()->to('/admin/type-operation')->with('success', 'Bareme added successfully.');
            } else {
                return view("admin/bareme/add", [
                    'errors' => $baremeModel->errors(),
                    'old' => $data
                ]);
            }
        }

        return view("admin/bareme/add",[
            'type_operations' => (new TypeOperationModel())->findAll()
        ]);
    }

    public function form(){
        $typeOperationModel = new TypeOperationModel();
        $type_operations = $typeOperationModel->findAll();

        return view("admin/bareme/add", ['type_operations' => $type_operations]);
    }

}