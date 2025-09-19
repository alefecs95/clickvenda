# ClickVenda - Backend API

Sistema de vendas desenvolvido com Laravel 11 para pequenos comércios.

## Tecnologias

- Laravel 11
- Laravel Sanctum (Autenticação)
- MySQL
- PHP 8.1+

## Configuração

1. Instale as dependências:
```bash
composer install
```

2. Configure o arquivo `.env` com suas credenciais de banco de dados:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=clickvenda
DB_USERNAME=root
DB_PASSWORD=
```

3. Execute as migrações:
```bash
php artisan migrate
```

4. Execute os seeders para dados de teste:
```bash
php artisan db:seed
```

5. Gere a chave da aplicação:
```bash
php artisan key:generate
```

6. Inicie o servidor:
```bash
php artisan serve
```

## Endpoints da API

### Autenticação
- `POST /api/login` - Login
- `POST /api/register` - Registro
- `POST /api/logout` - Logout (autenticado)
- `GET /api/me` - Dados do usuário (autenticado)

### Produtos
- `GET /api/products` - Listar produtos
- `POST /api/products` - Criar produto
- `GET /api/products/{id}` - Ver produto
- `PUT /api/products/{id}` - Atualizar produto
- `DELETE /api/products/{id}` - Desativar produto
- `GET /api/products/search?q={query}` - Buscar produtos

### Clientes
- `GET /api/customers` - Listar clientes
- `POST /api/customers` - Criar cliente
- `GET /api/customers/{id}` - Ver cliente
- `PUT /api/customers/{id}` - Atualizar cliente
- `DELETE /api/customers/{id}` - Desativar cliente

### Pedidos
- `GET /api/orders` - Listar pedidos
- `POST /api/orders` - Criar pedido
- `GET /api/orders/{id}` - Ver pedido
- `PUT /api/orders/{id}` - Atualizar pedido
- `DELETE /api/orders/{id}` - Cancelar pedido

## Estrutura do Banco de Dados

### Tabelas Principais
- `users` - Usuários do sistema
- `products` - Produtos
- `customers` - Clientes
- `orders` - Pedidos
- `order_items` - Itens dos pedidos

## Autenticação

O sistema usa Laravel Sanctum para autenticação via tokens. Para acessar endpoints protegidos, inclua o header:
```
Authorization: Bearer {token}
```
