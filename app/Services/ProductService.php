<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Product();
    }

    public function index($data)
    {

        $data = (array) $data->params;

        $limit = $data['limit'] ?? 10;
        $page =  $data['page'] ?? 1;

        $query = $this->model;

        foreach ($data as $field => $value) {
            if (in_array($field, ['name', 'price']) && !empty($value)) {
                $query = $query->like($field, $value);
            }
        }

        $products = $query->paginate($limit, 'default', $page);

        return [
            'header' => [
                'status' => 200,
                'message' => 'Produtos retornados com sucesso'
            ],
            'return' => [
                'data' => $products,
                'pagination' => $this->model->pager->getDetails()
            ]
        ];
    }

    public function show($id)
    {
        return $this->model->find($id);
    }

    public function store($data)
    {
        try {
            $id = $this->model->insert($data);
            $product = $this->model->find($id);

            return [
                'header' => [
                    'status' => 201,
                    'message' => 'Produto cadastrado com sucesso'
                ],
                'return' => $product
            ];
        } catch (\Exception $e) {
            $status = $e->getCode();

            if ($status < 100 || $status > 599) {
                $status = 500;
            }

            return [
                'header' => [
                    'status' => $status,
                    'message' => 'Erro ao cadastrar produto'
                ],
                'return' => ['error' => $e->getMessage()]
            ];
        }
    }

    public function update($id, $data)
    {
        try {
            $product = $this->model->find($id);

            if (!$product) {
                return [
                    'header' => [
                        'status' => 404,
                        'message' => 'Produto não encontrado'
                    ],
                    'return' => []
                ];
            }

            $this->model->update($id, $data);
            $updatedProduct = $this->model->find($id);

            return [
                'header' => [
                    'status' => 200,
                    'message' => 'Produto atualizado com sucesso'
                ],
                'return' => $updatedProduct
            ];
        } catch (\Exception $e) {
            $status = $e->getCode();
            if ($status < 100 || $status > 599) {
                $status = 500;
            }

            return [
                'header' => [
                    'status' => $status,
                    'message' => 'Erro ao atualizar produto'
                ],
                'return' => ['error' => $e->getMessage(), 'line' => $e->getLine()]
            ];
        }
    }


    public function delete($id)
    {
        $product = $this->model->find($id);

        if (!$product) {
            return false;
        }

        return $this->model->delete($id);
    }
}
