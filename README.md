# Documentação da API

## Requisitos

Antes de iniciar a instalação, certifique-se de que seu ambiente atende aos seguintes requisitos:

* PHP 8.1+
* Composer
* MySQL 5.7+ ou MariaDB
* CodeIgniter 4
* Extensão PHP intl habilitada

## Instalação

### Siga os passos abaixo para instalar e configurar a API:

#### Clone o repositório:

```
git clone https://github.com/AlissonCouto/purchase-order-api.git
cd purchase-order-api
```
#### Instale as dependências:

```
composer install
```

#### Copie o arquivo de configuração:

```
cp env .env
```

Abra o arquivo .env e configure a conexão com o banco de dados.

#### Configuração do banco de dados:

Crie um banco de dados no MySQL/MariaDB.

```
CREATE DATABASE dbPurchaseOrderApi
	DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_general_ci;
    
    USE dbPurchaseOrderApi;
```

#### Configure as credenciais do banco no arquivo .env:

```
database.default.hostname = localhost
database.default.database = nome_do_banco
database.default.username = usuario
database.default.password = senha
database.default.DBDriver = MySQLi
```

#### Execute as migrações:

```
php spark migrate
```

#### Executando o Servidor

Para iniciar a aplicação, utilize o comando:

```
php spark serve
```

#### A API estará acessível em http://localhost:8080.

### Como Testar a API


#### As requisições devem seguir o padrão:

```
{
  "params": {
    	"page": 1,
    	"limit": 10,
			"name": "Lorem ipsum"
  }
}
```

#### As respostas serão no formato

```
{
	"header": {
		"status": STATUS_CODE,
		"message": MESSAGE
	},
	"return": {
	    DATA
	}
}
```

### Na raiz do projeto está o arquivo PurchaseOrderApi.pdf

Esse arquivo contém o diagrama do banco de dados que foi construído utilizando a ferramenta [dbdiagram.io](https://dbdiagram.io/).

## Observações sobre as entidades

### Clientes

Os clientes podem ser pessoa física ou jurídica. Quando informar **name** e **cpf** no cadastro ou edição o cliente será pessoa física.

Quando informar **company_name** e **cnpj** no cadastro ou edição o cliente será pessoa Jurídica.

Na **edição** de clientes essa alteração de tipo é feita automaticamente de acordo com os dados informados no cadastro.

Isso significa que, se um cliente era PF e agora recebe **company_name** e **cnpj** ele automaticamente limpa os campos **name** e **cpf**.

### Produtos

Ao editar os pedidos, todos os itens são deletados e criados novamente. Sendo assim, caso queira manter os itens de pedido que já estavam no pedido, será necessário passa-los no payload.

#### Rotas para testar

##### Clientes

* **GET**  /clients/{id} - Busca por id
* **POST**  /clients - Cadastra novo cliente
* **PUT**  /clients/{id} - Atualiza cliente específico
* **DELETE**  /clients/{id} - Deleta cliente
* **GET**  /clients - Listagem de clientes com paginação


##### Produtos

* **GET**  /products/{id} - Busca por id
* **POST**  /products - Cadastra novo produto
* **PUT**  /products/{id} - Atualiza produto específico
* **DELETE**  /products/{id} - Deleta produto
* **GET**  /products - Listagem de produtos com paginação

##### Pedidos

* **GET**  /orders/{id} - Busca por id
* **POST**  /orders - Cadastra novo pedido
* **PUT**  /orders/{id} - Atualiza pedido específico
* **DELETE**  /orders/{id} - Deleta pedido
* **GET**  /orders - Listagem de pedidos com paginação