# Setup do Backend - API Locadora IMD

Este documento explica como configurar e rodar o backend Laravel da Locadora IMD em uma máquina local.

O projeto está em um monorepo:

```text
locadora/
├── back/
├── front/
└── docs/
```

A API Laravel fica em:

```text
back/
```

O script SQL oficial do banco fica em:

```text
docs/locadora_imd.sql
```

---

# 1. Requisitos do projeto

Para rodar o backend, você precisa instalar:

```text
Git
PHP 8.5.0
Composer
MySQL Server
```

O projeto usa Laravel 13.

Importante: este projeto **não usa ORM** e **não usa migrations Laravel para criar as tabelas da locadora**.

O banco foi modelado no MySQL Workbench e deve ser criado usando o arquivo:

```text
docs/locadora_imd.sql
```

---

# 2. Instalar Git

No Ubuntu/Linux Mint:

```bash
sudo apt update
sudo apt install git -y
```

Verifique:

```bash
git --version
```

---

# 3. Instalar dependências para compilar PHP pelo asdf

Este guia usa `asdf` para instalar o PHP 8.5.0.

Instale os pacotes necessários:

```bash
sudo apt update

sudo apt install -y \
  curl \
  git \
  build-essential \
  autoconf \
  bison \
  re2c \
  pkg-config \
  libxml2-dev \
  libssl-dev \
  libcurl4-openssl-dev \
  libsqlite3-dev \
  libonig-dev \
  libzip-dev \
  libreadline-dev \
  libicu-dev \
  libpng-dev \
  libjpeg-dev \
  libfreetype6-dev \
  libxslt1-dev \
  libldap2-dev \
  libpq-dev \
  libmysqlclient-dev \
  unzip \
  zip
```

---

# 4. Instalar o asdf

Clone o asdf:

```bash
git clone https://github.com/asdf-vm/asdf.git ~/.asdf --branch v0.14.1
```

Adicione ao `~/.zshrc`:

```bash
echo '. "$HOME/.asdf/asdf.sh"' >> ~/.zshrc
echo '. "$HOME/.asdf/completions/asdf.bash"' >> ~/.zshrc
```

Recarregue o terminal:

```bash
source ~/.zshrc
```

Verifique:

```bash
asdf --version
```

Se você usa Bash em vez de Zsh, adicione ao `~/.bashrc`:

```bash
echo '. "$HOME/.asdf/asdf.sh"' >> ~/.bashrc
echo '. "$HOME/.asdf/completions/asdf.bash"' >> ~/.bashrc
source ~/.bashrc
```

---

# 5. Instalar PHP 8.5.0

Adicione o plugin do PHP:

```bash
asdf plugin add php https://github.com/asdf-community/asdf-php.git
```

Atualize o plugin:

```bash
asdf plugin update php
```

Liste versões disponíveis:

```bash
asdf list all php | grep 8.5
```

Instale o PHP 8.5.0:

```bash
asdf install php 8.5.0
```

Defina o PHP 8.5.0 como versão global:

```bash
asdf global php 8.5.0
```

Verifique:

```bash
php -v
```

A saída deve mostrar algo parecido com:

```text
PHP 8.5.0
```

Verifique extensões importantes:

```bash
php -m | grep -E "pdo_mysql|mbstring|openssl|curl|zip|fileinfo"
```

---

# 6. Instalar Composer

Baixe e instale o Composer:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

php composer-setup.php

sudo mv composer.phar /usr/local/bin/composer

php -r "unlink('composer-setup.php');"
```

Verifique:

```bash
composer --version
```

---

# 7. Instalar MySQL Server

No Ubuntu/Linux Mint:

```bash
sudo apt update
sudo apt install mysql-server -y
```

Inicie o serviço:

```bash
sudo systemctl enable mysql
sudo systemctl start mysql
```

Verifique:

```bash
sudo systemctl status mysql
```

Acesse o MySQL:

```bash
sudo mysql
```

Opcionalmente, defina senha para o usuário root:

```sql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'root2025';
FLUSH PRIVILEGES;
EXIT;
```

Depois teste:

```bash
mysql -u root -p
```

Digite a senha configurada.

---

# 8. Clonar o projeto

Escolha uma pasta para o projeto:

```bash
cd ~
```

Clone o repositório:

```bash
git clone git@github.com:cleiton-lima/locadora_bd.git locadora
```

Entre na pasta:

```bash
cd locadora
```

Estrutura esperada:

```text
locadora/
├── back/
├── front/
└── docs/
```

---

# 9. Criar o banco de dados

O banco oficial se chama:

```text
locadora_imd
```

A estrutura está em:

```text
docs/locadora_imd.sql
```

Na raiz do projeto, rode:

```bash
mysql -u root -p < docs/locadora_imd.sql
```

Digite a senha do MySQL.

Depois confira:

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

Saia do MySQL:

```sql
EXIT;
```

---

# 10. Configurar o backend Laravel

Entre na pasta do backend:

```bash
cd back
```

Instale as dependências do Laravel:

```bash
composer install
```

Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Abra o `.env`:

```bash
code .env
```

Configure o banco:

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
DB_PASSWORD=root2025
```

Se sua senha do MySQL for diferente, altere:

```env
DB_PASSWORD=sua_senha
```

Também mantenha estas configurações:

```env
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
CACHE_STORE=file
```

Essas configurações são importantes porque o banco oficial da disciplina não possui tabelas internas do Laravel como `cache`, `sessions` e `jobs`.

---

# 11. Limpar cache do Laravel

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

# 12. Rodar o servidor local

Dentro da pasta `back`, rode:

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

# 13. Listar rotas da API

Em outro terminal, dentro de `back`, rode:

```bash
php artisan route:list
```

Você deve ver rotas como:

```text
api/filiais
api/montadoras
api/oficinas
api/usuarios
api/funcionarios
api/veiculos
api/alugueis
api/vendas
```

---

# 14. Teste rápido da API

Com o servidor rodando, teste criar uma filial:

```bash
curl -X POST http://127.0.0.1:8000/api/filiais \
  -H "Content-Type: application/json" \
  -d '{"nome":"Filial Centro"}'
```

Liste as filiais:

```bash
curl http://127.0.0.1:8000/api/filiais
```

Busque uma filial por ID:

```bash
curl http://127.0.0.1:8000/api/filiais/1
```

Atualize a filial:

```bash
curl -X PUT http://127.0.0.1:8000/api/filiais/1 \
  -H "Content-Type: application/json" \
  -d '{"nome":"Filial Centro Atualizada"}'
```

Remova a filial:

```bash
curl -X DELETE http://127.0.0.1:8000/api/filiais/1
```

---

# 15. Ordem recomendada para testar os CRUDs

Como o banco possui várias chaves estrangeiras, teste os recursos nesta ordem:

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

Por exemplo:

```text
Funcionario depende de Usuario e Filial.
Administrador depende de Funcionario.
Lote depende de Montadora e Gerente_Comercial.
Veiculo depende de Administrador, Filial e Lote.
Aluguel depende de Atendente, Veiculo e Pessoa_Fisica ou Contrato_Frota.
Venda depende de Veiculo, Pessoa_Fisica e Gerente_Comercial.
```

---

# 16. Importante: este projeto não usa migrations

Não rode:

```bash
php artisan migrate
```

O banco deve ser criado pelo arquivo:

```text
docs/locadora_imd.sql
```

Se precisar recriar o banco:

```bash
mysql -u root -p < docs/locadora_imd.sql
```

---

# 17. Importante: este projeto não usa ORM

Por exigência da disciplina, a API não usa ORM.

Não usamos:

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

Usamos apenas SQL puro via facade `DB`:

```php
DB::select()
DB::selectOne()
DB::insert()
DB::update()
DB::delete()
DB::transaction()
```

---

# 18. Verificar se não há uso de ORM

Dentro da pasta `back`, rode:

```bash
grep -RInE "DB::table\(|extends[[:space:]]+Model|::query\(|::find\(|::create\(|hasMany\(|belongsTo\(|belongsToMany\(|hasOne\(" app routes database --exclude-dir=vendor
```

O ideal é o comando não retornar nada.

---

# 19. Problemas comuns

## Erro: tabela `cache` não existe

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

## Erro: tabela `sessions` não existe

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

## Erro: tabela `jobs` não existe

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

## Erro: Connection sqlite

Se aparecer algo como:

```text
Connection: sqlite
Database file at path [locadora_imd] does not exist
```

Confira no `.env`:

```env
DB_CONNECTION=mysql
```

Depois limpe o cache:

```bash
php artisan optimize:clear
```

## Erro: Access denied for user root

Confira usuário e senha do MySQL:

```env
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

Teste no terminal:

```bash
mysql -u root -p
```

## Erro: Unknown database locadora_imd

O banco ainda não foi criado.

Rode na raiz do projeto:

```bash
mysql -u root -p < docs/locadora_imd.sql
```

---

# 20. Comandos úteis

Entrar no backend:

```bash
cd back
```

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

Verificar versão do PHP:

```bash
php -v
```

Verificar versão do Composer:

```bash
composer --version
```

Verificar conexão com MySQL:

```bash
mysql -u root -p
```