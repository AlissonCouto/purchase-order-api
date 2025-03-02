<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\ProductService;
use CodeIgniter\Database\Exceptions\DatabaseException;

class OrderService
{
    protected $model;
    protected $orderItemModel;
    protected $productService;

    public function __construct()
    {
        $this->model = new Order();
        $this->orderItemModel = new OrderItem();
        $this->productService = new ProductService();
    }

    public function index($data)
    {
        $data = (array) $data->params;

        $limit = $data['limit'] ?? 10;
        $page = $data['page'] ?? 1;

        $query = $this->model;

        foreach ($data as $field => $value) {
            if (in_array($field, ['client_id', 'status']) && !empty($value)) {
                $query = $query->like($field, $value);
            }
        }

        $orders = $query->paginate($limit, 'default', $page);

        return [
            'header' => [
                'status' => 200,
                'message' => 'Pedidos retornados com sucesso'
            ],
            'return' => [
                'data' => $orders,
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
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $orderData = [
                'client_id' => $data->client_id,
                'status' => $data->status,
                'total' => 0.00
            ];

            $orderId = $this->model->insert($orderData);

            $totalOrderPrice = 0.00;

            foreach ($data->items as $item) {

                $product = $this->productService->show($item->product_id);

                $unitPrice = $product['price'];

                $totalItemPrice = $unitPrice * $item->quantity;

                $itemData = [
                    'order_id' => $orderId,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'total' => $totalItemPrice
                ];

                $this->orderItemModel->insert($itemData);

                $totalOrderPrice += $totalItemPrice;
            }

            $this->model->update($orderId, ['total' => $totalOrderPrice]);

            $order = $this->model->find($orderId);

            $db->transCommit();

            return [
                'header' => [
                    'status' => 201,
                    'message' => 'Pedido cadastrado com sucesso'
                ],
                'return' => $order
            ];
        } catch (DatabaseException $e) {

            $db->transRollback();

            $status = $e->getCode();

            if ($status < 100 || $status > 599) {
                $status = 500;
            }

            return [
                'header' => [
                    'status' => $status,
                    'message' => 'Erro ao cadastrar pedido'
                ],
                'return' => ['error' => $e->getMessage()]
            ];
        }
    }

    public function update($id, $data)
    {

        $order = $this->model->find($id);

        if (!$order) {
            return [
                'header' => [
                    'status' => 404,
                    'message' => 'Pedido não encontrado'
                ],
                'return' => []
            ];
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {

            if (isset($data->status)) {
                $updateData['status'] = $data->status;
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = date('Y-m-d H:i:s');
                $this->model->update($id, $updateData);
            }

            $totalOrderPrice = 0.0;

            if (isset($data->items)) {
                $this->orderItemModel->where('order_id', $id)->delete();

                foreach ($data->items as $item) {
                    $product = $this->productService->show($item->product_id);

                    if (!$product) {
                        return [
                            'header' => [
                                'status' => 404,
                                'message' => 'Produto não encontrado'
                            ],
                            'return' => []
                        ];
                    }

                    $unitPrice = $product['price'];

                    $totalItemPrice = $unitPrice * $item->quantity;

                    $itemData = [
                        'order_id' => $id,
                        'product_id' => $item->product_id,
                        'unit_price' => $unitPrice,
                        'total' => $totalItemPrice,
                        'quantity' => $item->quantity,
                    ];

                    $this->orderItemModel->insert($itemData);

                    $totalOrderPrice += $totalItemPrice;
                }
            }

            $this->model->update($id, ['total' => $totalOrderPrice]);

            $db->transCommit();

            $updatedOrder = $this->model->find($id);

            return [
                'header' => [
                    'status' => 200,
                    'message' => 'Pedido atualizado com sucesso'
                ],
                'return' => $updatedOrder
            ];
        } catch (DatabaseException $e) {
            $db->transRollback();

            $status = $e->getCode();
            if ($status < 100 || $status > 599) {
                $status = 500;
            }

            return [
                'header' => [
                    'status' => $status,
                    'message' => 'Erro ao atualizar pedido'
                ],
                'return' => ['error' => $e->getMessage(), 'line' => $e->getLine()]
            ];
        }
    }

    public function delete($id)
    {
        $order = $this->model->find($id);

        if (!$order) {
            return false;
        }

        return $this->model->delete($id);
    }
}
