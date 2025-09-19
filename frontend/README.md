# ClickVenda - Frontend

Sistema de vendas desenvolvido com Vue 3 + TypeScript para pequenos comércios.

## Tecnologias

- Vue 3
- TypeScript
- Vite
- TailwindCSS
- Pinia (Estado)
- Vue Router
- Axios

## Configuração

1. Instale as dependências:
```bash
npm install
```

2. Configure as variáveis de ambiente:
Crie um arquivo `.env` na raiz do projeto:
```env
VITE_API_URL=http://localhost:8000/api
```

3. Inicie o servidor de desenvolvimento:
```bash
npm run dev
```

## Estrutura do Projeto

```
src/
├── assets/          # Recursos estáticos
├── components/      # Componentes Vue
├── config/          # Configurações (API, etc.)
├── router/          # Configuração de rotas
├── services/        # Serviços (API, etc.)
├── stores/          # Stores Pinia
├── views/           # Páginas/Views
└── main.ts          # Ponto de entrada
```

## Variáveis de Ambiente

### VITE_API_URL
URL base da API do backend Laravel.
- Desenvolvimento: `http://localhost:8000/api`
- Produção: `https://seu-dominio.com/api`

## Desenvolvimento

### Comandos Disponíveis

```bash
# Instalar dependências
npm install

# Servidor de desenvolvimento
npm run dev

# Build para produção
npm run build

# Preview do build
npm run preview

# Linting
npm run lint
```

## Integração com Backend

O frontend se comunica com o backend Laravel através da API REST. Todas as requisições são feitas através do serviço centralizado em `src/services/api.ts`.

### Autenticação

O sistema usa Laravel Sanctum para autenticação via tokens. O token é automaticamente incluído em todas as requisições através dos interceptors do Axios.

### Endpoints Principais

- **Autenticação**: `/login`, `/register`, `/logout`, `/me`
- **Produtos**: `/products`, `/products/search`
- **Clientes**: `/customers`
- **Pedidos**: `/orders`
