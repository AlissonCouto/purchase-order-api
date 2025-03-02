<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Services\ClientService;

class ClientController extends ResourceController
{

    protected $service;
    protected $validation;

    public function __construct()
    {
        $this->service = new ClientService();
        $this->validation = \Config\Services::validation();
    }

    /**
     * Return an array of resource objects, themselves in array format.
     *
     * @return ResponseInterface
     */
    public function index()
    {
        $params = $this->request->getJSON();
        $response = $this->service->index($params);

        return $this->respond($response, $response['header']['status']);
    }

    /**
     * Return the properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function show($id = null)
    {
        $client = $this->service->show($id);

        if (!$client) {
            return $this->failNotFound('Cliente não encontrado');
        }

        return $this->respond([
            'header' => [
                'status' => 200,
                'message' => 'Cliente retornado com sucesso'
            ],
            'return' => $client
        ]);
    }

    /**
     * Return a new resource object, with default properties.
     *
     * @return ResponseInterface
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters.
     *
     * @return ResponseInterface
     */
    public function create()
    {
        $data = $this->request->getJSON();
        $data = $data->params;

        if (!empty($data->cpf) || !empty($data->name)) {
            $this->validation->setRules([
                'name' => 'required|min_length[3]|max_length[255]',
                'cpf'  => 'required|exact_length[14]|is_unique[clients.cpf]',
            ]);
        } else {
            $this->validation->setRules([
                'company_name' => 'required|max_length[255]',
                'cnpj' => 'required|exact_length[18]|is_unique[clients.cnpj]',
            ]);
        }

        if (!$this->validation->run((array) $data)) {
            return $this->failValidationErrors($this->validation->getErrors());
        }

        $response = $this->service->store($data);

        return $this->respond($response, $response['header']['status']);
    }

    /**
     * Return the editable properties of a resource object.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function update($id = null)
    {

        $data = $this->request->getJSON();

        $data = $data->params;

        $client = $this->service->show($id);

        $validationRules = [
            'name' => 'permit_empty|min_length[3]|max_length[255]',
            'cpf' => 'permit_empty|exact_length[14]|is_unique[clients.cpf,id,' . $id . ']',
            'company_name' => 'permit_empty|max_length[255]',
            'cnpj' => 'permit_empty|exact_length[14]|is_unique[clients.cnpj,id,' . $id . ']',
        ];

        $pf = true;
        if (is_null($client['cpf'])) {
            $pf = false;
        }

        $changedToPF = isset($data->cpf) || isset($data->name) && !$pf;
        $changedToPJ = isset($data->cnpj) || isset($data->company_name) && $pf;

        /*
         * Se mudar o tipo de pessoa, limpamos os dados do tipo anterior
         * Mudou de PJ para PF
         */
        if ($changedToPF) {
            $validationRules['cpf'] = 'required|exact_length[14]|is_unique[clients.cpf,id,' . $id . ']';
            $validationRules['name'] = 'required|min_length[3]|max_length[255]';
            $validationRules['company_name'] = 'permit_empty';
            $validationRules['cnpj'] = 'permit_empty';
        }

        // Mudou de PF para PJ
        if ($changedToPJ) {
            $validationRules['cnpj'] = 'required|exact_length[14]|is_unique[clients.cnpj,id,' . $id . ']';
            $validationRules['company_name'] = 'required|max_length[255]';
            $validationRules['name'] = 'permit_empty';
            $validationRules['cpf'] = 'permit_empty';
        }

        $this->validation->setRules($validationRules);

        if (!$this->validation->run((array) $data)) {
            return $this->failValidationErrors($this->validation->getErrors());
        }

        $response = $this->service->update($id, $data);
        return $this->respond($response, $response['header']['status']);
    }


    /**
     * Delete the designated resource object from the model.
     *
     * @param int|string|null $id
     *
     * @return ResponseInterface
     */
    public function delete($id = null)
    {
        $deleted = $this->service->delete($id);

        if ($deleted) {
            return $this->respondDeleted([
                'header' => [
                    'status' => 200,
                    'message' => 'Cliente excluído com sucesso'
                ],
                'return' => null
            ]);
        } else {
            return $this->failNotFound('Cliente não encontrado');
        }
    }
}
