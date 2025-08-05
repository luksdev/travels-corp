# TravelsCorp - Configuração Docker

Sistema de solicitação de viagens corporativas usando Laravel (Backend) + Vue.js (Frontend) com PostgreSQL.

## Postman Collection

[<img src="https://run.pstmn.io/button.svg" alt="Run In Postman" style="width: 128px; height: 32px;">](https://app.getpostman.com/run-collection/37990858-7875ed27-4ab2-428c-8e53-98d5e3b05b7f?action=collection%2Ffork&source=rip_markdown&collection-url=entityId%3D37990858-7875ed27-4ab2-428c-8e53-98d5e3b05b7f%26entityType%3Dcollection%26workspaceId%3D5a97dc99-f19e-4d03-bd2e-092a3339ced9#?env%5BLocal%5D=W3sia2V5IjoiaG9zdCIsInZhbHVlIjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwIiwiZW5hYmxlZCI6dHJ1ZSwidHlwZSI6ImRlZmF1bHQiLCJzZXNzaW9uVmFsdWUiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAiLCJjb21wbGV0ZVNlc3Npb25WYWx1ZSI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCIsInNlc3Npb25JbmRleCI6MH0seyJrZXkiOiJob3N0IiwidmFsdWUiOiIiLCJlbmFibGVkIjp0cnVlLCJ0eXBlIjoiYW55Iiwic2Vzc2lvblZhbHVlIjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwIiwiY29tcGxldGVTZXNzaW9uVmFsdWUiOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAiLCJzZXNzaW9uSW5kZXgiOjF9LHsia2V5IjoidHJhdmVsX3JlcXVlc3RfaWQiLCJ2YWx1ZSI6IiIsImVuYWJsZWQiOnRydWUsInR5cGUiOiJhbnkiLCJzZXNzaW9uVmFsdWUiOiIwMTk4NmU2MC02NzE1LTcxNDYtOGVmOC00OTQzZTNiNTY1MzEiLCJjb21wbGV0ZVNlc3Npb25WYWx1ZSI6IjAxOTg2ZTYwLTY3MTUtNzE0Ni04ZWY4LTQ5NDNlM2I1NjUzMSIsInNlc3Npb25JbmRleCI6Mn1d)

## Execução

```bash
# Iniciar todos os serviços
docker-compose up -d --build
```

A inicialização do Laravel (migrations, seeds) acontece automaticamente.

## Acessos

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000
- PostgreSQL: localhost:5432

## Banco de dados

- Host: localhost
- Porta: 5432
- Database: onhappy
- Usuário: laravel
- Senha: secret

## Comandos principais

```bash
# Parar containers
docker-compose down

# Ver logs
docker-compose logs -f backend
docker-compose logs -f frontend

# Executar testes
docker-compose exec backend php artisan test

# Executar migrações
docker-compose exec backend php artisan migrate

# Acessar containers
docker-compose exec backend bash
docker-compose exec frontend sh
```

## Estrutura

- **Backend**: Laravel 10 + PHP 8.2 + PostgreSQL
- **Frontend**: Vue.js 3 + Vite + TypeScript
- **Proxy**: Nginx
- **Database**: PostgreSQL 15