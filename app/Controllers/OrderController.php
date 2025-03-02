<?php

namespace App\Controllers;

use App\Services\OrderService;
use CodeIgniter\RESTful\ResourceController;

class OrderController extends ResourceController
{
    protected $service;
    protected $validation;

    public function __construct()
    {
        $this->service = new OrderService();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $params = $this->request->getJSON();
        $response = $this->service->index($params);

        return $this->respond($response, $response['header']['status']);
    }

    public function show($id = null)
    {
        $order = $this->service->show($id);

        if (!$order) {
            return $this->failNotFound('Pedido não encontrado');
        }

        return $this->respond([
            'header' => [
                'status' => 200,
                'message' => 'Pedido retornado com sucesso'
            ],
            'return' => $order
        ]);
    }

    public function create()
    {
        $data = $this->request->getJSON();
        $data = $data->params;

        $this->validation->setRules([
            'client_id' => 'required|is_not_unique[clients.id]',
            'status' => 'required|in_list[open,paid,canceled]',
            'items' => 'required',
        ]);

        if (!$this->validation->run((array) $data)) {
            return $this->failValidationErrors($this->validation->getErrors());
        }

        $response = $this->service->store($data);

        return $this->respond($response, $response['header']['status']);
    }

    public function update($id = null)
    {
        $data = $this->request->getJSON();
        $data = $data->params;

        $this->validation->setRules([
            'status' => 'permit_empty|in_list[open,paid,canceled]',
        ]);

        if (!$this->validation->run((array) $data)) {
            return $this->failValidationErrors($this->validation->getErrors());
        }

        $response = $this->service->update($id, $data);
        return $this->respond($response, $response['header']['status']);
    }

    public function delete($id = null)
    {
        $deleted = $this->service->delete($id);

        if ($deleted) {
            return $this->respondDeleted([
                'header' => [
                    'status' => 200,
                    'message' => 'Pedido excluído com sucesso'
                ],
                'return' => null
            ]);
        } else {
            return $this->failNotFound('Pedido não encontrado');
        }
    }
}
