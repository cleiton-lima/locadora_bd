# Deploy no Railway - API Locadora IMD

Este documento descreve como hospedar a API (`back/`) e o banco `locadora_imd`
no [Railway](https://railway.app), deixando tudo pronto para o front (ainda
não criado) se conectar via `Authorization: Bearer <token>`.

Pré-requisitos: conta no Railway, projeto já com o repositório no GitHub, e
tudo que está descrito em `docs/setup-backend.md` funcionando localmente
primeiro (é bem mais fácil depurar localmente do que em produção).

---

## 1. Criar o serviço MySQL

No painel do Railway, dentro do projeto:

```text
New -> Database -> Add MySQL
```

O Railway provisiona um MySQL gerenciado e expõe variáveis como `MYSQLHOST`,
`MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD` (e uma
`MYSQL_PUBLIC_URL` para acesso externo).

---

## 2. Criar o serviço da API

```text
New -> GitHub Repo -> selecionar este repositório
```

Configurar o **Root Directory** do serviço para `back/` (o Laravel não está
na raiz do monorepo).

O Railway detecta PHP via Nixpacks automaticamente pelo `composer.json`. O
arquivo `back/nixpacks.toml` já define o comando de build
(`composer install --no-dev --optimize-autoloader`) e o comando de start:

```toml
[start]
cmd = "php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=$PORT"
```

`php artisan serve` é aceitável para este projeto acadêmico/demo (processo
único, sem muita carga simultânea). Para produção real, trocar por PHP-FPM +
Nginx — fora do escopo deste deploy.

---

## 3. Variáveis de ambiente da API

No serviço da API, aba **Variables**, usar "Reference Variables" para
apontar para o serviço MySQL em vez de copiar valores manualmente:

```env
APP_NAME="Locadora IMD"
APP_ENV=production
APP_DEBUG=false
APP_KEY=                      # gerar local com: php artisan key:generate --show
APP_URL=https://<seu-app>.up.railway.app

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=pt_BR

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

JWT_SECRET=                   # gerar com: openssl rand -base64 32
JWT_TTL=3600
JWT_REFRESH_TTL=2592000

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://<seu-app>.up.railway.app/api/auth/google/callback

FRONTEND_URL=https://<seu-front>.vercel.app
```

`SESSION_DRIVER=file`, `CACHE_STORE=file` e `QUEUE_CONNECTION=sync` evitam
depender das tabelas `sessions`/`cache`/`jobs` do Laravel, que não existem no
schema oficial (mesma config recomendada em `docs/setup-backend.md`).

---

## 4. Aplicar o schema

Não existe `php artisan migrate` neste projeto (proibido pela disciplina). O
schema é aplicado uma vez, do computador local, contra o host público do
MySQL do Railway (pegar host/porta/usuário/senha na aba **Connect** do
serviço MySQL):

```bash
mysql -h <host-railway> -P <porta> -u <user> -p<senha> < docs/bdnormalizado/locadora_imd-bcnf.sql
mysql -h <host-railway> -P <porta> -u <user> -p<senha> locadora_imd < docs/bdnormalizado/auth-schema.sql
mysql -h <host-railway> -P <porta> -u <user> -p<senha> locadora_imd < docs/bdnormalizado/stored_procedures.sql
mysql -h <host-railway> -P <porta> -u <user> -p<senha> locadora_imd < docs/bdnormalizado/triggers.sql
mysql -h <host-railway> -P <porta> -u <user> -p<senha> locadora_imd < docs/bdnormalizado/views.sql
```

Rodar sempre nessa ordem — cada script depende do anterior (procedures,
triggers e views referenciam tabelas do schema base + `auth-schema.sql`).

Se precisar recriar o banco do zero no Railway, repetir os 5 comandos acima
(todos usam `DROP ... IF EXISTS` antes de recriar).

---

## 5. Credenciais OAuth do Google

No [Google Cloud Console](https://console.cloud.google.com/apis/credentials),
na credencial OAuth 2.0 já usada em desenvolvimento, adicionar (sem remover a
local) o redirect URI de produção:

```text
https://<seu-app>.up.railway.app/api/auth/google/callback
```

Mantendo também, para uso local:

```text
http://127.0.0.1:8000/api/auth/google/callback
```

---

## 6. Verificação pós-deploy

```bash
curl https://<seu-app>.up.railway.app/api/filiais \
  -H "Authorization: Bearer <token-invalido>"
# esperado: 401 {"message":"Não autenticado."}

curl -X POST https://<seu-app>.up.railway.app/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"login":"...","senha":"..."}'
# esperado: 200 com access_token/refresh_token
```

Repetir o fluxo manual completo (login → `/auth/me` → rota protegida por
role → refresh → logout) já usado em desenvolvimento, agora contra a URL
pública, antes de considerar o deploy concluído.
