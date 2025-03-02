<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Client();
    }

    public function index($data)
    {
        $data = (array) $data->params;

        $limit = $data['limit'] ?? 10;
        $page = $data['page'] ?? 1;

        $query = $this->model;

        foreach ($data as $field => $value) {
            if (in_array($field, ['name', 'cpf', 'company_name', 'cnpj']) && !empty($value)) {
                $query = $query->like($field, $value);
            }
        }

        $clients = $query->paginate($limit, 'default', $page);

        return [
            'header' => [
                'status' => 200,
                'message' => 'Clientes retornados com sucesso'
            ],
            'return' => [
                'data' => $clients,
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
            if (!empty($data->cpf)) {
                $clientData = [
                    'name' => $data->name,
                    'cpf'  => $data->cpf,
                ];
            } else {
                $clientData = [
                    'company_name' => $data->company_name,
                    'cnpj' => $data->cnpj,
                ];
            }

            $id = $this->model->insert($clientData);
            $client = $this->model->find($id);

            return [
                'header' => [
                    'status' => 201,
                    'message' => 'Cliente cadastrado com sucesso'
                ],
                'return' => $client
            ];
        } catch (\Exception $e) {
            $status = $e->getCode();

            if ($status < 100 || $status > 599) {
                $status = 500;
            }

            return [
                'header' => [
                    'status' => $status,
                    'message' => 'Erro ao cadastrar cliente'
                ],
                'return' => ['error' => $e->getMessage()]
            ];
        }
    }

    public function update($id, $data)
    {
        try {
            $client = $this->model->find($id);

            if (!$client) {
                return [
                    'header' => [
                        'status' => 404,
                        'message' => 'Cliente não encontrado'
                    ],
                    'return' => []
                ];
            }

            $pf = true;

            if (is_null($client['cpf'])) {
                $pf = false;
            }

            $clientData = [
                'name' => $data->name ?? $client['name'],
                'cpf'  => $data->cpf ?? $client['cpf'],
                'company_name' => $data->company_name ?? $client['company_name'],
                'cnpj' => $data->cnpj ?? $client['cnpj'],
            ];

            /*
             * Se mudou o tipo de Pessoa
             *  Limpa os campos do tipo anterior
            */

            if ($pf && isset($data->cnpj)) {
                // Era PF agora é PJ
                $clientData['name'] = NULL;
                $clientData['cpf'] = NULL;
            }

            if (!$pf && isset($data->cpf)) {
                // Era PJ agora é PF
                $clientData['company_name'] = NULL;
                $clientData['cnpj'] = NULL;
            }

            $this->model->update($id, $clientData);
            $updatedClient = $this->model->find($id);

            return [
                'header' => [
                    'status' => 200,
                    'message' => 'Cliente atualizado com sucesso'
                ],
                'return' => $updatedClient
            ];
        } catch (\Exception $e) {
            $status = $e->getCode();
            if ($status < 100 || $status > 599) {
                $status = 500;
            }

            return [
                'header' => [
                    'status' => $status,
                    'message' => 'Erro ao atualizar cliente'
                ],
                'return' => ['error' => $e->getMessage(), 'line' => $e->getLine()]
            ];
        }
    }


    public function delete($id)
    {
        $client = $this->model->find($id);

        if (!$client) {
            return false;
        }

        return $this->model->delete($id);
    }
}
