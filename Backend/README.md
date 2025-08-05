# OnHappy Travel Request Management API

Uma API REST desenvolvida em Laravel 12.x para gerenciamento de pedidos de viagem, com sistema de autenticação JWT, controle de permissões e notificações automáticas.

[![Run with Postman](https://run.pstmn.io/button.svg)](https://app.getpostman.com/run-collection/your-collection-id)

## Funcionalidades

- ✅ **Autenticação JWT** - Registro, login, logout e refresh de tokens
- ✅ **Gestão de Pedidos de Viagem** - CRUD completo com autorização
- ✅ **Controle de Status** - Solicitado → Aprovado/Cancelado (apenas admins)
- ✅ **Notificações Automáticas** - Email e database para mudanças de status
- ✅ **Filtros Avançados** - Por status, destino, período de datas
- ✅ **Isolamento de Dados** - Usuários veem apenas seus próprios pedidos
- ✅ **Testes Automatizados** - Cobertura completa Feature e Unit tests

## Tecnologias

- **Laravel 12.x** com PHP 8.2
- **JWT Authentication** (`tymon/jwt-auth`)
- **PostgreSQL** para banco de dados
- **Laravel Pint** para code style (PSR-12)
- **PHPUnit** para testes automatizados
- **Vite** para build de assets

## Instalação e Configuração

### Pré-requisitos

- PHP 8.2+
- Composer
- Node.js e npm

### Setup do Projeto

```bash
# Clone o repositório
git clone <repository-url>
cd Backend

# Instale dependências PHP
composer install

# Instale dependências Node.js
npm install

# Configure ambiente
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Configure database
# Certifique-se de configurar DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE no .env

# Execute migrações e seeders
php artisan migrate --seed

# Build assets
npm run build
```

## Executando o Projeto

### Ambiente de Desenvolvimento Completo

```bash
# Inicia servidor, queue, logs e vite simultaneamente
composer run dev
```

### Comandos Individuais

```bash
# Servidor Laravel
php artisan serve

# Queue worker (para notificações)
php artisan queue:work

# Watch de assets
npm run dev

# Logs em tempo real
php artisan pail
```

## API Endpoints

### Autenticação

```http
POST /api/auth/register    # Registro de usuário
POST /api/auth/login       # Login
POST /api/auth/logout      # Logout
POST /api/auth/refresh     # Refresh token
GET  /api/auth/user        # Dados do usuário autenticado
```

### Pedidos de Viagem

```http
GET    /api/travel-requests                           # Listar pedidos
POST   /api/travel-requests                           # Criar pedido
GET    /api/travel-requests/{id}                      # Ver pedido específico
PUT    /api/travel-requests/{id}                      # Atualizar pedido
DELETE /api/travel-requests/{id}                      # Deletar pedido
PATCH  /api/travel-requests/{id}/status               # Atualizar status (admin)
PATCH  /api/travel-requests/{id}/cancel               # Cancelar pedido
GET    /api/travel-requests/stats                     # Estatísticas dos pedidos
```

### Filtros de Listagem

```http
GET /api/travel-requests?status=approved
GET /api/travel-requests?destination=tokyo
GET /api/travel-requests?departure_from=2025-12-01&departure_to=2025-12-31
GET /api/travel-requests?created_from=2025-01-01&created_to=2025-01-31
```

### Estrutura de Dados

#### Travel Request
```json
{
  "id": "uuid",
  "destination": "Tokyo, Japan",
  "departure_date": "2025-12-01",
  "return_date": "2025-12-15",
  "status": "requested|approved|cancelled",
  "user": {
    "id": "uuid",
    "name": "John Doe",
    "email": "john@example.com"
  },
  "created_at": "2025-08-03T10:00:00Z",
  "updated_at": "2025-08-03T10:00:00Z"
}
```

#### User Registration
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

## Regras de Negócio

### Permissões

- **Usuários regulares**: Podem criar, ver, editar e deletar apenas seus próprios pedidos
- **Administradores**: Podem ver todos os pedidos e alterar status

### Status de Pedidos

- **requested**: Status inicial ao criar pedido
- **approved**: Apenas admins podem aprovar
- **cancelled**: Usuários podem cancelar apenas pedidos "requested"

### Notificações

- Enviadas automaticamente por email e salvas no banco
- Disparadas quando status muda (aprovação/cancelamento)
- Processadas em background via queue

## Testes

### Executar Todos os Testes

```bash
composer test
# ou
php artisan test
```

### Testes Específicos

```bash
# Testes de funcionalidades
php artisan test tests/Feature/

# Testes unitários
php artisan test tests/Unit/

# Teste específico
php artisan test --filter=TravelRequestTest
```

### Estrutura de Testes

- **Feature Tests**: Testes de API endpoints e fluxos completos
- **Unit Tests**: Testes de services, policies e notifications
- **Factories**: Geração de dados de teste
- **Database**: Testes usam banco em memória

## Code Quality

```bash
# Aplicar Laravel Pint (PSR-12)
./vendor/bin/pint

# Verificar sem aplicar correções
./vendor/bin/pint --test
```

## Arquitetura

### Estrutura de Pastas

```
app/
├── Http/
│   ├── Controllers/     # Controllers com Route Model Binding
│   ├── Requests/        # Form Request Validation
│   └── Resources/       # API Resources para respostas
├── Models/              # Eloquent Models com UUIDs
├── Services/            # Lógica de negócio
├── Policies/            # Autorização
├── Notifications/       # Notificações por email/database
└── Enums/              # Enums para tipos (Status, Roles)
```

### Padrões Implementados

- **Service Layer**: Lógica de negócio separada dos controllers
- **Policy Authorization**: Controle granular de permissões
- **API Resources**: Transformação consistente de respostas
- **Form Requests**: Validação centralizada
- **Route Model Binding**: Injeção automática de models
- **Queue Jobs**: Processamento assíncrono de notificações


## Possíveis Melhorias

- **API de Cidades**: Implementar endpoint para busca de cidades/destinos com autocomplete
- **Upload de Anexos**: Permitir anexar documentos aos pedidos de viagem
- **Relatórios Avançados**: Dashboard com gráficos e métricas detalhadas
- **Integração Externa**: APIs de voos, hotéis e clima
- **Notificações Push**: Notificações em tempo real
- **Aprovação em Lote**: Permitir aprovar/cancelar múltiplos pedidos

## Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/amazing-feature`)
3. Commit suas mudanças (`git commit -m 'Add amazing feature'`)
4. Push para a branch (`git push origin feature/amazing-feature`)
5. Abra um Pull Request

### Code Standards

- Execute `./vendor/bin/pint` antes do commit
- Escreva testes para novas funcionalidades

## Licença

Este projeto está licenciado sob a [MIT License](https://opensource.org/licenses/MIT).

## Suporte

Para dúvidas ou problemas:

1. Verifique a documentação
2. Execute os testes: `composer test`
3. Verifique os logs: `php artisan pail`
4. Abra uma issue no repositório
