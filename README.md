# Travel Orders Service – Laravel Microservice

![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Compose-blue?logo=docker&logoColor=white)
![Tests](https://img.shields.io/badge/Tests-PHPUnit-success?logo=phpunit&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-blue?logo=mysql&logoColor=white)
![JWT](https://img.shields.io/badge/Auth-JWT-purple)

Microsserviço desenvolvido em **Laravel** para gerenciamento de **pedidos de viagem corporativa**, expondo uma **API REST protegida por JWT**, com regras de negócio, controle de acesso por perfil e testes automatizados.

---

## Visão Geral

Este serviço é responsável por gerenciar pedidos de viagem corporativa, permitindo que usuários autenticados criem e consultem seus próprios pedidos, enquanto usuários administradores podem aprovar ou cancelar pedidos, seguindo regras de negócio.

---

## Tecnologias Utilizadas

Este microsserviço foi desenvolvido utilizando as seguintes tecnologias e versões:

### Backend
- **PHP 8.2**
- **Laravel 12.0**

### Autenticação
- **JWT (JSON Web Token)**  
  Biblioteca: `php-open-source-saver/jwt-auth`

### Banco de Dados
- **MySQL 8.0**

### Infraestrutura e DevOps
- **Docker**
- **Docker Compose**

### Testes
- **PHPUnit**
- **Laravel HTTP Testing (Feature Tests)**

### Outros
- **FakerPHP** (geração de dados para testes e seeds)
- **Laravel Notifications** (envio de notificações)

## Funcionalidades Implementadas

- Criar pedido de viagem
- Consultar pedido por ID
- Listar pedidos com filtros:
  - status
  - destino
  - período de viagem
  - período de criação
- Atualizar status do pedido (**admin**)
- Regra de negócio:
  - pedidos **aprovados não podem ser cancelados**
- Notificação ao solicitante quando o pedido for:
  - aprovado
  - cancelado
- Autenticação via **JWT**
- Controle de acesso:
  - usuários veem apenas seus próprios pedidos
  - administradores podem aprovar/cancelar
- Testes automatizados cobrindo os principais cenários
- Execução completa via Docker

---

## Arquitetura da Solução e Organização do Código

### routes/api.php
Define o contrato da API REST e centraliza autenticação e middlewares.

### app/Http/Controllers
Responsáveis por orquestrar as requisições HTTP.
- AuthController
- TravelOrderController

### app/Models
Entidades de domínio e relacionamento com o banco.
- User
- TravelOrder

### database/migrations
Versionamento da estrutura do banco de dados.

### app/Http/Requests
Validação de dados e regras de entrada.
- StoreTravelOrderRequest
- UpdateTravelOrderStatusRequest

### app/Http/Resources
Padronização das respostas JSON.

### app/Http/Middleware
Regras de autorização.
- EnsureAdmin

### app/Notifications
Envio de notificações.
- TravelOrderStatusChanged

### tests/Feature
Testes automatizados de comportamento da API.
- TravelOrderApiTest

### database/seeders
Dados iniciais para facilitar avaliação.

---

## Autenticação

Autenticação baseada em JWT utilizando:
php-open-source-saver/jwt-auth

Tokens enviados via header:
Authorization: Bearer {token}

---

## Execução com Docker

Subir containers:
```
docker compose up -d --build
```
Instalar dependências 
```
docker compose exec app composer install

```
Executar migrations:
```
docker compose exec app php artisan migrate
```
Executar seeds:
```
docker compose exec app php artisan db:seed
```
Executar testes:
```
docker compose exec app php artisan test
```
Acessar a aplicação
```
http://localhost:8080
```
---
## Comandos úteis
Limpar caches do Laravel:
```
docker compose exec app php artisan optimize:clear
```
Parar e remover containers e volumes (reset completo do ambiente):
```
docker compose down -v
```

---
## Usuários de Teste

| Perfil | Email | Senha |
|------|------|------|
| Admin | admin@corp.com | password |
| User | user@corp.com | password |

---

## Exemplos de uso

Login:
POST /api/auth/login

Criar pedido:
POST /api/travel-orders

Aprovar pedido:
PATCH /api/travel-orders/{id}/status

---

## Testando a API com Postman

1. Importe a coleção `TravelOrders.postman_collection.json`
2. Crie um Environment chamado **Travel Orders - Local**
3. Configure a variável `base_url` como `http://localhost:8080/api`
4. Execute a requisição **Auth - Login**
5. O token JWT será salvo automaticamente
6. Execute as demais requisições na ordem

## API Tests (Bruno)

Necessário instalar a ferramenta BRUNO 
```
npm i -g @usebruno/cli
```
Após sua instalação é importante entrar na pasta 
```
cd src/tests_with_bruno
```
E rodar o comando abaixo 
```
bru run . --env local --reporter-html reports/bruno-report.html   --reporter-junit reports/bruno-report.xml   --reporter-json reports/bruno-report.json
```
Relatórios são gerados na pasta **src/tests_with_bruno/reports**

## Considerações Finais

O projeto buscou seguir boas práticas do Laravel, possuindo cobertura de testes, autenticação JWT real.
