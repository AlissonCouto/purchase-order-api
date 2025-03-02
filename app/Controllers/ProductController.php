<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

use App\Services\ProductService;

class ProductController extends ResourceController
{

    protected $service;
    protected $validation;

    public function __construct()
    {
        $this->service = new ProductService();
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
        $product = $this->service->show($id);

        if (!$product) {
            return $this->failNotFound('Produto não encontrado');
        }

        return $this->respond([
            'header' => [
                'status' => 200,
                'message' => 'Produto retornado com sucesso'
            ],
            'return' => $product
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
        
        $this->validation->setRules([
            'name' => 'required|min_length[3]|max_length[255]',
            'price' => 'required|decimal',
        ]);

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

        $this->validation->setRules([
            'name' => 'permit_empty|min_length[3]|max_length[255]',
            'price' => 'permit_empty|decimal',
        ]);

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
                    'message' => 'Produto excluído com sucesso'
                ],
                'return' => null
            ]);
        } else {
            return $this->failNotFound('Produto não encontrado');
        }

        return $this->fail('Ocorreu um erro ao tentar excluir o produto');
    }
}
