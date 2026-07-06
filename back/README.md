# API Locadora IMD

Backend em Laravel para o projeto acadêmico de Banco de Dados da Locadora IMD.

Este projeto faz parte de um monorepo:

```text
locadora/
├── back/
├── front/
└── docs/
```

A pasta `back/` contém a API Laravel.

A pasta `docs/` contém o script oficial do banco:

```text
docs/locadora_imd.sql
```

---

## Requisito da disciplina

Por exigência da disciplina, este projeto **não utiliza ORM**.

Portanto, a API não usa:

```php
Eloquent
Models
DB::table()
Model::query()
Model::create()
Model::find()
belongsTo()
hasMany()
```

Todas as operações de banco são feitas com **SQL puro em MySQL**, usando a facade `DB` do Laravel:

```php
DB::select()
DB::selectOne()
DB::insert()
DB::update()
DB::delete()
DB::transaction()
```

---

## Tecnologias

```text
PHP 8.5.0
Laravel 13
Composer
MySQL
```

O `composer.json` exige PHP `^8.3`, então PHP 8.5.0 é compatível.

---

## Documentação completa de instalação

Se você nunca rodou um projeto Laravel antes, siga primeiro o guia completo:

```text
../docs/setup-backend.md
```

Esse documento explica como instalar:

```text
PHP 8.5.0
Composer
MySQL
Laravel
```

---

## Configuração rápida para quem já tem PHP, Composer e MySQL

Entre na pasta do backend:

```bash
cd back
```

Instale as dependências:

```bash
composer install
```

Crie o arquivo `.env`:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Configure o `.env` com os dados do seu MySQL:

```env
APP_NAME="Locadora IMD"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=locadora_imd
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

Também mantenha estas configurações:

```env
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

Essas opções são importantes porque o banco oficial não possui as tabelas internas do Laravel, como `cache`, `sessions` e `jobs`.

---

## Banco de dados

O banco oficial se chama:

```text
locadora_imd
```

Este projeto **não usa migrations Laravel** para criar as tabelas da locadora.

A modelagem oficial foi feita no MySQL Workbench e está versionada em:

```text
../docs/locadora_imd.sql
```

Para criar o banco localmente, rode a partir da raiz do projeto:

```bash
mysql -u root -p < docs/locadora_imd.sql
```

Depois confira no MySQL:

```bash
mysql -u root -p
```

Dentro do MySQL:

```sql
SHOW DATABASES;
USE locadora_imd;
SHOW TABLES;
```

Você deve ver tabelas como:

```text
Usuario
Funcionario
Pessoa_Fisica
Empresa
Filial
Montadora
Oficina
Lote
Veiculo
Aluguel
Venda
Servico
```

---

## Atenção: não rodar migrations

Não rode:

```bash
php artisan migrate
```

O banco deve ser criado pelo arquivo SQL oficial:

```text
../docs/locadora_imd.sql
```

Também não use:

```bash
composer run setup
```

O script `setup` padrão do Laravel tenta rodar migrations, mas este projeto não usa migrations para a modelagem da locadora.

O fluxo correto é:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan optimize:clear
php artisan serve
```

---

## Limpar cache do Laravel

Depois de configurar o `.env`, rode:

```bash
php artisan optimize:clear
```

A saída esperada deve ser parecida com:

```text
config ........ DONE
cache ......... DONE
compiled ...... DONE
events ........ DONE
routes ........ DONE
views ......... DONE
```

---

## Rodar a API

Dentro da pasta `back`:

```bash
php artisan serve
```

A API ficará disponível em:

```text
http://127.0.0.1:8000
```

As rotas da API usam o prefixo:

```text
/api
```

Exemplo:

```text
http://127.0.0.1:8000/api/filiais
```

---

## Listar rotas

```bash
php artisan route:list
```

---

## Teste rápido

Criar uma filial:

```bash
curl -X POST http://127.0.0.1:8000/api/filiais \
  -H "Content-Type: application/json" \
  -d '{"nome":"Filial Centro"}'
```

Listar filiais:

```bash
curl http://127.0.0.1:8000/api/filiais
```

Buscar por ID:

```bash
curl http://127.0.0.1:8000/api/filiais/1
```

Atualizar:

```bash
curl -X PUT http://127.0.0.1:8000/api/filiais/1 \
  -H "Content-Type: application/json" \
  -d '{"nome":"Filial Centro Atualizada"}'
```

Excluir:

```bash
curl -X DELETE http://127.0.0.1:8000/api/filiais/1
```

---

## Ordem recomendada para testes

Como existem várias chaves estrangeiras, teste os CRUDs nesta ordem:

```text
1. Filial
2. Montadora
3. Oficina
4. Usuario
5. Funcionario
6. Administrador
7. Atendente
8. Gerente_Comercial
9. Lote
10. Veiculo
11. Pessoa_Fisica
12. Empresa
13. Contrato_Frota
14. Servico
15. Aluguel
16. Venda
```

Essa ordem evita erros de FK.

Exemplos:

```text
Funcionario depende de Usuario e Filial.
Administrador depende de Funcionario.
Lote depende de Montadora e Gerente_Comercial.
Veiculo depende de Administrador, Filial e Lote.
Aluguel depende de Atendente, Veiculo e Pessoa_Fisica ou Contrato_Frota.
Venda depende de Veiculo, Pessoa_Fisica e Gerente_Comercial.
```

---

## Estrutura principal da API

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   └── Requests/
└── Services/
```

A regra da arquitetura é:

```text
routes/api.php
    -> Controller
        -> FormRequest
        -> Service
            -> SQL puro com DB facade
                -> MySQL
```

Os Controllers devem ser simples.

Os Services concentram as regras de negócio e os comandos SQL.

---

## Verificar se não existe uso de ORM

Dentro da pasta `back`, rode:

```bash
grep -RInE "DB::table\(|extends[[:space:]]+Model|::query\(|::find\(|::create\(|hasMany\(|belongsTo\(|belongsToMany\(|hasOne\(" app routes database --exclude-dir=vendor
```

O ideal é o comando não retornar nada.

---

## Problemas comuns

### Erro: tabela `cache` não existe

Verifique no `.env`:

```env
CACHE_STORE=file
```

Não use:

```env
CACHE_STORE=database
```

Depois rode:

```bash
php artisan optimize:clear
```

---

### Erro: tabela `sessions` não existe

Verifique no `.env`:

```env
SESSION_DRIVER=file
```

Não use:

```env
SESSION_DRIVER=database
```

Depois rode:

```bash
php artisan optimize:clear
```

---

### Erro: tabela `jobs` não existe

Verifique no `.env`:

```env
QUEUE_CONNECTION=sync
```

Não use:

```env
QUEUE_CONNECTION=database
```

Depois rode:

```bash
php artisan optimize:clear
```

---

### Erro: Connection sqlite

Se aparecer algo como:

```text
Connection: sqlite
Database file at path [locadora_imd] does not exist
```

Confira no `.env`:

```env
DB_CONNECTION=mysql
```

Depois rode:

```bash
php artisan optimize:clear
```

---

### Erro: Unknown database locadora_imd

O banco ainda não foi criado.

Rode na raiz do projeto:

```bash
mysql -u root -p < docs/locadora_imd.sql
```

---

### Erro: Access denied for user

Confira usuário e senha no `.env`:

```env
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

Teste no terminal:

```bash
mysql -u root -p
```

---

## Comandos úteis

Instalar dependências:

```bash
composer install
```

Copiar `.env`:

```bash
cp .env.example .env
```

Gerar chave:

```bash
php artisan key:generate
```

Limpar cache:

```bash
php artisan optimize:clear
```

Rodar servidor:

```bash
php artisan serve
```

Listar rotas:

```bash
php artisan route:list
```

Ver versão do PHP:

```bash
php -v
```

Ver versão do Composer:

```bash
composer --version
```

---

## Observação para desenvolvimento

Nunca envie o arquivo `.env` para o GitHub.

O arquivo correto para versionar é:

```text
.env.example
```

O arquivo `.env` deve ser criado localmente por cada integrante do grupo.