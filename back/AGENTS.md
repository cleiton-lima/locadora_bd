# Instruções do Projeto - API Locadora IMD

> Use este mesmo conteúdo nos arquivos `AGENTS.md` e `CLAUDE.md` na raiz do projeto.
>
> - `AGENTS.md`: instruções para Codex.
> - `CLAUDE.md`: instruções para Claude Code.

---

## Contexto

Este projeto é uma API Laravel para uma locadora de veículos.

O banco de dados oficial se chama:

```text
locadora_imd
```

A modelagem oficial foi normalizada em BCNF e o script SQL final cria o schema `locadora_imd`.

O Laravel deve apenas se conectar a esse banco e executar operações com SQL puro.

---

## Regra obrigatória da disciplina

O professor proibiu o uso de ORM.

Portanto, é proibido usar:

```php
Model::query();
Model::create();
Model::find();
Model::where();
DB::table();
Eloquent;
belongsTo();
hasMany();
hasOne();
belongsToMany();
```

Também não criar Models Eloquent para as tabelas.

Usar apenas SQL puro via facade `DB`:

```php
DB::select();
DB::selectOne();
DB::insert();
DB::update();
DB::delete();
DB::transaction();
```

---

## Arquitetura obrigatória

A API deve seguir este fluxo:

```text
routes/api.php
    -> Controller
        -> FormRequest
        -> Service
            -> SQL puro com DB facade
                -> MySQL
```

Controllers devem ser simples.

Services devem conter as regras de negócio e os comandos SQL.

FormRequests devem validar os dados de entrada.

SQL não deve ficar dentro dos Controllers.

Não usar Repository neste projeto.

---

## Banco de dados

O banco oficial é:

```text
locadora_imd
```

Configuração esperada no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=locadora_imd
DB_USERNAME=root
DB_PASSWORD=sua_senha
```

Se estiver usando Docker, ajustar `DB_HOST`, `DB_PORT`, `DB_USERNAME` e `DB_PASSWORD` conforme o `docker-compose.yml`.

Não criar migrations Laravel para substituir a modelagem do Workbench.

O script oficial do banco é:

```text
docs/bdnormalizado/locadora_imd-bcnf.sql
```

---

## Teste de conexão com o banco

Criar uma rota temporária em `routes/api.php`:

```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/db-health', function () {
    $result = DB::selectOne('SELECT DATABASE() AS banco, 1 AS conectado');

    return response()->json([
        'database' => $result->banco,
        'connected' => (bool) $result->conectado,
    ]);
});
```

Resposta esperada:

```json
{
  "database": "locadora_imd",
  "connected": true
}
```

---

## Atenção aos nomes reais das tabelas

Usar exatamente os nomes das tabelas do banco.

Não converter automaticamente para snake_case.

Tabelas principais:

```text
Oficina
Montadora
Filial
Usuario
Funcionario
Gerente_Comercial
Administrador
Atendente
Lote
Veiculo
Servico
Empresa
Contrato_Frota
CNH
Pessoa_Fisica
Aluguel
Venda
Estado
Cidade
Bairro
Logradouro
CEP
Telefone_oficina
Telefone_montadora
Usuario_endereco
Usuario_telefone
Oficina_endereco
Montadora_endereco
Filial_endereco
Filial_telefone
```

---

## Atenção aos nomes reais das colunas

Usar exatamente os nomes das colunas no SQL.

Exemplos importantes:

```text
Usuario.id
Usuario.username
Usuario.nome
Usuario.email
Usuario.ultimo_acesso
Usuario.data_cadastro
Usuario.ativo
Usuario.senha_hash

Funcionario.Usuario_id
Funcionario.Filial_id

Gerente_Comercial.Funcionario_id
Administrador.Funcionario_id
Atendente.Funcionario_id

Lote.Gerente_Comercial_Funcionario_id
Lote.Montadora_id
Lote.quantidade_veiculos  -- quantidade comprada no lote

Veiculo.Administrador_cadastro_id
Veiculo.Administrador_responsavel_id
Veiculo.Filial_id
Veiculo.Lote_id

Servico.Veiculo_id
Servico.Administrador_Funcionario_id
Servico.Oficina_id

Empresa.Usuario_id
Empresa.CNPJ

Pessoa_Fisica.Cliente_id
Pessoa_Fisica.CPF
Pessoa_Fisica.CNH_numero

CNH.numero
CNH.estado
CNH.categoria
CNH.data_emissao
CNH.data_validade

Estado.sigla
Estado.nome

Cidade.id
Cidade.nome
Cidade.Estado_sigla

Bairro.id
Bairro.nome
Bairro.Cidade_id

Logradouro.id
Logradouro.nome
Logradouro.Bairro_id

CEP.CEP
CEP.Logradouro_id

Contrato_Frota.Gerente_Comercial_id
Contrato_Frota.Empresa_id

Aluguel.Atendente_entrega_id
Aluguel.Atendente_devolucao_id
Aluguel.Pessoa_Fisica_id
Aluguel.Contrato_frota_id
Aluguel.Veiculo_id

Venda.Veiculo_id
Venda.Pessoa_Fisica_id
Venda.Gerente_Comercial_Funcionario_id
```

Não inventar nomes como:

```text
usuario_id
cliente_id
filial_id
veiculo_id
pessoa_fisica_id
contrato_frota_id
gerente_comercial_id
administrador_funcionario_id
```

A API pode receber JSON em snake_case, mas o SQL precisa mapear para os nomes reais das colunas.

Exemplo de JSON recebido:

```json
{
  "filial_id": 1,
  "lote_id": 2,
  "administrador_cadastro_id": 3,
  "administrador_responsavel_id": 3
}
```

SQL correto:

```sql
INSERT INTO Veiculo (
    status,
    finalidade,
    placa,
    grupo,
    quilometragem,
    Administrador_cadastro_id,
    Administrador_responsavel_id,
    Filial_id,
    Lote_id
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
```

---

## Estrutura recomendada no Laravel

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── FilialController.php
│   │       ├── MontadoraController.php
│   │       ├── OficinaController.php
│   │       ├── UsuarioController.php
│   │       ├── FuncionarioController.php
│   │       ├── GerenteComercialController.php
│   │       ├── AdministradorController.php
│   │       ├── AtendenteController.php
│   │       ├── EmpresaController.php
│   │       ├── PessoaFisicaController.php
│   │       ├── LoteController.php
│   │       ├── VeiculoController.php
│   │       ├── ServicoController.php
│   │       ├── ContratoFrotaController.php
│   │       ├── AluguelController.php
│   │       └── VendaController.php
│   └── Requests/
│       ├── Filial/
│       ├── Montadora/
│       ├── Oficina/
│       ├── Usuario/
│       ├── Funcionario/
│       ├── GerenteComercial/
│       ├── Administrador/
│       ├── Atendente/
│       ├── Empresa/
│       ├── PessoaFisica/
│       ├── Lote/
│       ├── Veiculo/
│       ├── Servico/
│       ├── ContratoFrota/
│       ├── Aluguel/
│       └── Venda/
└── Services/
    ├── FilialService.php
    ├── MontadoraService.php
    ├── OficinaService.php
    ├── UsuarioService.php
    ├── FuncionarioService.php
    ├── GerenteComercialService.php
    ├── AdministradorService.php
    ├── AtendenteService.php
    ├── EmpresaService.php
    ├── PessoaFisicaService.php
    ├── LoteService.php
    ├── VeiculoService.php
    ├── ServicoService.php
    ├── ContratoFrotaService.php
    ├── AluguelService.php
    └── VendaService.php
```

---

## Ordem de implementação

Implementar primeiro os CRUDs com menos dependências:

```text
1. Filial
2. Montadora
3. Oficina
4. Usuario
5. Funcionario
6. Gerente_Comercial
7. Administrador
8. Atendente
9. Empresa
10. Pessoa_Fisica
11. Lote
12. Veiculo
13. Servico
14. Contrato_Frota
15. Aluguel
16. Venda
17. Endereços e telefones
```

Para uma versão apresentável, priorizar:

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
12. Aluguel
13. Venda
```

---

## Rotas sugeridas

Em `routes/api.php`:

```php
use App\Http\Controllers\Api\AdministradorController;
use App\Http\Controllers\Api\AluguelController;
use App\Http\Controllers\Api\AtendenteController;
use App\Http\Controllers\Api\ContratoFrotaController;
use App\Http\Controllers\Api\EmpresaController;
use App\Http\Controllers\Api\FilialController;
use App\Http\Controllers\Api\FuncionarioController;
use App\Http\Controllers\Api\GerenteComercialController;
use App\Http\Controllers\Api\LoteController;
use App\Http\Controllers\Api\MontadoraController;
use App\Http\Controllers\Api\OficinaController;
use App\Http\Controllers\Api\PessoaFisicaController;
use App\Http\Controllers\Api\ServicoController;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\VeiculoController;
use App\Http\Controllers\Api\VendaController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/db-health', function () {
    $result = DB::selectOne('SELECT DATABASE() AS banco, 1 AS conectado');

    return response()->json([
        'database' => $result->banco,
        'connected' => (bool) $result->conectado,
    ]);
});

Route::apiResource('filiais', FilialController::class);
Route::apiResource('montadoras', MontadoraController::class);
Route::apiResource('oficinas', OficinaController::class);
Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('funcionarios', FuncionarioController::class);
Route::apiResource('gerentes-comerciais', GerenteComercialController::class);
Route::apiResource('administradores', AdministradorController::class);
Route::apiResource('atendentes', AtendenteController::class);
Route::apiResource('empresas', EmpresaController::class);
Route::apiResource('pessoas-fisicas', PessoaFisicaController::class);
Route::apiResource('lotes', LoteController::class);
Route::apiResource('veiculos', VeiculoController::class);
Route::apiResource('servicos', ServicoController::class);
Route::apiResource('contratos-frota', ContratoFrotaController::class);
Route::apiResource('alugueis', AluguelController::class);
Route::apiResource('vendas', VendaController::class);
```

---

## Padrão dos Controllers

Controllers devem apenas:

```text
- receber request;
- chamar FormRequest;
- chamar Service;
- retornar JSON;
- retornar status HTTP correto.
```

Não colocar SQL em Controller.

Exemplo:

```php
public function index()
{
    return response()->json(
        $this->service->listar()
    );
}

public function show(int $id)
{
    $registro = $this->service->buscarPorId($id);

    if (! $registro) {
        return response()->json([
            'message' => 'Registro não encontrado.',
        ], 404);
    }

    return response()->json($registro);
}
```

---

## Padrão dos Services

Cada service deve usar SQL puro com bindings.

Correto:

```php
DB::selectOne("
    SELECT id, nome
    FROM Filial
    WHERE id = ?
", [$id]);
```

Errado:

```php
DB::selectOne("
    SELECT id, nome
    FROM Filial
    WHERE id = $id
");
```

Métodos básicos esperados:

```php
public function listar(array $filtros = []): array;
public function buscarPorId(int $id): ?object;
public function criar(array $data): int|bool;
public function atualizar(int $id, array $data): int;
public function remover(int $id): int;
```

---

## Exemplo de Service simples

```php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FilialService
{
    public function listar(): array
    {
        return DB::select("
            SELECT id, nome
            FROM Filial
            ORDER BY nome
        ");
    }

    public function buscarPorId(int $id): ?object
    {
        return DB::selectOne("
            SELECT id, nome
            FROM Filial
            WHERE id = ?
        ", [$id]);
    }

    public function criar(array $data): bool
    {
        return DB::insert("
            INSERT INTO Filial (nome)
            VALUES (?)
        ", [
            $data['nome'],
        ]);
    }

    public function atualizar(int $id, array $data): int
    {
        return DB::update("
            UPDATE Filial
            SET nome = ?
            WHERE id = ?
        ", [
            $data['nome'],
            $id,
        ]);
    }

    public function remover(int $id): int
    {
        return DB::delete("
            DELETE FROM Filial
            WHERE id = ?
        ", [$id]);
    }
}
```

---

## Validação de FKs

Validar FKs via `DB::selectOne()` antes de inserir ou atualizar.

Exemplos:

```php
DB::selectOne('SELECT id FROM Filial WHERE id = ?', [$filialId]);

DB::selectOne('SELECT id FROM Montadora WHERE id = ?', [$montadoraId]);

DB::selectOne('SELECT id FROM Oficina WHERE id = ?', [$oficinaId]);

DB::selectOne('SELECT id FROM Usuario WHERE id = ?', [$usuarioId]);

DB::selectOne('SELECT Usuario_id FROM Funcionario WHERE Usuario_id = ?', [$funcionarioId]);

DB::selectOne('SELECT Funcionario_id FROM Administrador WHERE Funcionario_id = ?', [$administradorId]);

DB::selectOne('SELECT Funcionario_id FROM Atendente WHERE Funcionario_id = ?', [$atendenteId]);

DB::selectOne('SELECT Funcionario_id FROM Gerente_Comercial WHERE Funcionario_id = ?', [$gerenteId]);

DB::selectOne('SELECT Cliente_id FROM Pessoa_Fisica WHERE Cliente_id = ?', [$pessoaFisicaId]);

DB::selectOne('SELECT Usuario_id FROM Empresa WHERE Usuario_id = ?', [$empresaId]);

DB::selectOne('SELECT id FROM Lote WHERE id = ?', [$loteId]);

DB::selectOne('SELECT id FROM Veiculo WHERE id = ?', [$veiculoId]);

DB::selectOne('SELECT id FROM Contrato_Frota WHERE id = ?', [$contratoFrotaId]);
```

---

## Campos únicos

Validar duplicidade antes de inserir ou atualizar.

Campos únicos:

```text
Usuario.email
Usuario.username
Empresa.CNPJ
Pessoa_Fisica.CPF
Pessoa_Fisica.CNH_numero
Veiculo.placa
```

Exemplos:

```php
DB::selectOne('SELECT id FROM Usuario WHERE email = ?', [$email]);

DB::selectOne('SELECT id FROM Usuario WHERE username = ?', [$username]);

DB::selectOne('SELECT Cliente_id FROM Pessoa_Fisica WHERE CPF = ?', [$cpf]);

DB::selectOne('SELECT Cliente_id FROM Pessoa_Fisica WHERE CNH_numero = ?', [$cnhNumero]);

DB::selectOne('SELECT Usuario_id FROM Empresa WHERE CNPJ = ?', [$cnpj]);

DB::selectOne('SELECT id FROM Veiculo WHERE placa = ?', [$placa]);
```

---

## Transações obrigatórias

Usar `DB::transaction()` quando a operação envolver mais de uma tabela.

### Criar Pessoa Física

Fluxo obrigatório:

```text
1. Inserir em Usuario
2. Capturar Usuario.id
3. Inserir em Pessoa_Fisica com Cliente_id = Usuario.id
```

Exemplo:

```php
DB::transaction(function () use ($data) {
    DB::insert("
        INSERT INTO Usuario (
            username,
            nome,
            email,
            data_cadastro,
            ativo,
            senha_hash
        ) VALUES (?, ?, ?, NOW(), ?, ?)
    ", [
        $data['username'],
        $data['nome'],
        $data['email'],
        1,
        $data['senha_hash'],
    ]);

    $usuarioId = (int) DB::getPdo()->lastInsertId();

    DB::insert("
        INSERT INTO Pessoa_Fisica (
            Cliente_id,
            CPF,
            CNH,
            estado,
            categoria,
            data_emissao,
            data_validade
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ", [
        $usuarioId,
        $data['cpf'],
        $data['cnh'],
        $data['estado'],
        $data['categoria'],
        $data['data_emissao'],
        $data['data_validade'],
    ]);

    return $usuarioId;
});
```

### Criar Empresa

Fluxo obrigatório:

```text
1. Inserir em Usuario
2. Capturar Usuario.id
3. Inserir em Empresa com Cliente_id = Usuario.id
```

### Criar Funcionário

Fluxo obrigatório:

```text
1. Inserir em Usuario
2. Capturar Usuario.id
3. Inserir em Funcionario com Usuario_id = Usuario.id
4. Opcionalmente inserir em Gerente_Comercial, Administrador ou Atendente
```

### Criar Aluguel

Fluxo obrigatório:

```text
1. Validar Atendente_entrega_id
2. Validar Veiculo_id
3. Validar Pessoa_Fisica_id quando informado
4. Validar Contrato_frota_id quando informado
5. Garantir que Pessoa_Fisica_id ou Contrato_frota_id foi informado
6. Inserir em Aluguel
7. Atualizar Veiculo.status para 'ALUGADO'
```

SQL de atualização:

```sql
UPDATE Veiculo
SET status = 'ALUGADO'
WHERE id = ?
```

### Criar Venda

Fluxo obrigatório:

```text
1. Validar Veiculo_id
2. Validar Pessoa_Fisica_id
3. Validar Gerente_Comercial_Funcionario_id
4. Inserir em Venda
5. Atualizar Veiculo.status para 'VENDIDO'
```

SQL de atualização:

```sql
UPDATE Veiculo
SET status = 'VENDIDO'
WHERE id = ?
```

---

## Stored procedures

Alguns fluxos transacionais têm uma versão em stored procedure no MySQL,
criada por:

```text
docs/bdnormalizado/stored_procedures.sql
```

Aplicar depois do script principal:

```bash
mysql -u root -p locadora_imd < docs/bdnormalizado/stored_procedures.sql
```

Procedures:

```text
sp_criar_aluguel(
    p_atendente_entrega_id, p_atendente_devolucao_id, p_pessoa_fisica_id,
    p_status, p_tipo, p_data_inicial, p_data_final, p_data_final_prevista,
    p_contrato_frota_id, p_veiculo_id,
    OUT p_novo_id, OUT p_valor
)

sp_devolver_aluguel(
    p_aluguel_id, p_data_final, p_atendente_devolucao_id,
    OUT p_linhas_afetadas
)

sp_criar_venda(
    p_valor, p_status, p_veiculo_id, p_pessoa_fisica_id,
    p_gerente_comercial_funcionario_id,
    OUT p_novo_id
)

sp_encerrar_contrato_frota(
    p_contrato_id, p_data_final,
    OUT p_veiculos_liberados
)
```

- `sp_criar_aluguel` calcula o `valor` (grupo do veículo, escassez por
  filial, desconto por duração) e faz `INSERT em Aluguel` + `UPDATE
  Veiculo.status = 'ALUGADO'` numa única transação no banco.
- `sp_devolver_aluguel` faz `UPDATE Aluguel.status = 'FINALIZADO'` +
  `UPDATE Veiculo.status = 'DISPONIVEL'`.
- `sp_criar_venda` faz `INSERT em Venda` + `UPDATE Veiculo.status =
  'VENDIDO'`.
- `sp_encerrar_contrato_frota` define `Contrato_Frota.data_final`,
  finaliza (`status = 'FINALIZADO'`) todos os `Aluguel` `ATIVO` vinculados
  ao contrato e devolve os respectivos `Veiculo` para `DISPONIVEL`. Retorna
  em `p_veiculos_liberados` quantos veículos foram liberados (`-1` se o
  contrato não existir).

As validações de FK e os bloqueios de negócio (veículo `ALUGADO`, aluguel
`ATIVO`, contrato de frota ativo, CNH vencida) continuam no
Controller/Service antes de chamar a procedure — ela assume que os dados já
foram validados.

`AluguelService::criar()`/`devolver()`, `VendaService::criar()` e
`ContratoFrotaService::encerrar()` decidem em runtime qual caminho usar:

```php
if (DB::getDriverName() === 'mysql') {
    // CALL sp_..._(...)
}
```

Isso é necessário porque os testes de feature rodam em SQLite em memória
(`phpunit.xml`), que não suporta `CREATE PROCEDURE`/`CALL`. Em SQLite, o
Service usa o SQL puro equivalente (o mesmo que existia antes da procedure).
Ao adicionar uma nova stored procedure, mantenha as duas implementações
sincronizadas ou documente a divergência.

Chamando a procedure via `DB` facade:

```php
DB::statement('CALL sp_criar_aluguel(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @novo_id, @valor)', [
    $data['atendente_entrega_id'],
    $data['atendente_devolucao_id'] ?? null,
    $data['pessoa_fisica_id'] ?? null,
    $data['status'],
    $data['tipo'],
    $data['data_inicial'],
    $data['data_final'] ?? null,
    $data['data_final_prevista'],
    $data['contrato_frota_id'] ?? null,
    $data['veiculo_id'],
]);

$id = (int) DB::selectOne('SELECT @novo_id AS id')->id;
```

---

## Triggers

Duas regras de integridade têm uma segunda linha de defesa como trigger no
MySQL, criadas por:

```text
docs/bdnormalizado/triggers.sql
```

Aplicar depois do script principal:

```bash
mysql -u root -p locadora_imd < docs/bdnormalizado/triggers.sql
```

Triggers:

```text
trg_aluguel_valida_cnh          (BEFORE INSERT ON Aluguel)
trg_venda_bloqueia_veiculo_alugado (BEFORE INSERT ON Venda)
```

- `trg_aluguel_valida_cnh` bloqueia o `INSERT` se a `Pessoa_Fisica_id`
  informada tiver CNH com `data_validade` anterior à `data_inicial` do
  aluguel (`SIGNAL SQLSTATE '45000'`).
- `trg_venda_bloqueia_veiculo_alugado` bloqueia o `INSERT` em `Venda` se o
  `Veiculo_id` estiver com `status = 'ALUGADO'`.

Essas regras já são validadas em PHP **antes** do INSERT
(`AluguelService::cnhValidaParaAluguel()`, `VendaService::veiculoDisponivelParaVenda()`),
o que garante uma resposta HTTP 409 com mensagem amigável pela API. A
trigger é a garantia de integridade no banco — protege mesmo contra um
INSERT feito fora da API (SQL direto, outra aplicação). Mantenha a mensagem
de erro da trigger e a mensagem do Service em sincronia.

As triggers não têm equivalente no schema SQLite usado pelos testes
(`tests/Feature/BcnfApiTest.php`) — SQLite não dispara `SIGNAL`. A cobertura
de teste dessas regras vem inteiramente da validação em PHP, que roda
igual em qualquer driver.

---

## Views

Quatro views de relatório somente-leitura, criadas por:

```text
docs/bdnormalizado/views.sql
```

Aplicar depois do script principal:

```bash
mysql -u root -p locadora_imd < docs/bdnormalizado/views.sql
```

Views:

```text
vw_frota_disponivel      -- Veiculo DISPONIVEL + Filial
vw_ocupacao_frota        -- % ocupação por Filial/grupo
vw_contrato_frota_resumo -- Contrato_Frota + Empresa + veículos alugados ativos
vw_veiculos_manutencao   -- Servico ATIVO + Veiculo + Oficina
```

Consumidas por `RelatorioService` (`SELECT * FROM vw_...`) e expostas em:

```text
GET /api/relatorios/frota-disponivel
GET /api/relatorios/ocupacao-frota
GET /api/relatorios/contratos-frota
GET /api/relatorios/veiculos-manutencao
```

São views só de leitura, sem regra de negócio. Assim como as procedures e
triggers, só existem no schema MySQL — não têm equivalente no schema
SQLite dos testes, então esses 4 endpoints não têm teste de feature
automatizado; foram validados manualmente contra MySQL real (ver
histórico do projeto). Se for adicionar teste automatizado para eles no
futuro, será necessário replicar as views (ou o `SELECT` equivalente) no
`createSchema()` de `tests/Feature/BcnfApiTest.php`.

---

## Autenticação

A disciplina proíbe ORM, o que elimina Sanctum/Passport (usam Eloquent
internamente). A autenticação é JWT via `firebase/php-jwt` (só assina/valida
token, sem tocar banco) + SQL puro via `DB` facade para tudo que persiste.

### Schema

Duas tabelas aditivas em `docs/bdnormalizado/auth-schema.sql` (aplicar depois
do script principal: `mysql -u root -p locadora_imd <
docs/bdnormalizado/auth-schema.sql`):

```text
Usuario_google (Usuario_id PK/FK -> Usuario.id, google_id UNIQUE)
Refresh_Token  (id PK, Usuario_id FK, token_hash UNIQUE, expires_at, revoked_at, created_at)
```

Nenhuma tabela existente foi alterada. `Refresh_Token.token_hash` guarda só o
SHA-256 do refresh token — nunca o valor em texto puro. Prova de BCNF em
`docs/bdnormalizado/script-prolog.pl` (ver `NORMALIZACAO.md`, seção 5).

### Roles: derivadas, não armazenadas

Não existe coluna `role`. `RoleService::rolesDoUsuario()` deriva a(s) role(s)
de um `Usuario_id` consultando as tabelas de ator que já existem:

```text
Funcionario -> Administrador/Atendente/Gerente_Comercial  =>  ADMINISTRADOR / ATENDENTE / GERENTE_COMERCIAL
Pessoa_Fisica                                              =>  CLIENTE_PF
Empresa                                                     =>  EMPRESA
```

Constantes em `App\Support\Roles`. Um `Usuario` recém-criado (cadastro
público ou login Google) não tem role nenhuma até completar o cadastro em
`Pessoa_Fisica`/`Empresa` (ou até um Administrador vinculá-lo a
`Funcionario`) — `roles: []` no token é esperado e válido.

### Fluxo JWT

```text
POST /api/auth/login    {login, senha} -> {access_token, refresh_token, expires_in, usuario, roles}
POST /api/auth/refresh  {refresh_token} -> novo par (rotação: o antigo é revogado)
POST /api/auth/logout   {refresh_token} -> revoga
GET  /api/auth/me       (jwt.auth)      -> usuario + roles do token
```

- Access token: JWT assinado (`HS256`, segredo em `JWT_SECRET`), payload
  `{sub, roles, iat, exp}`, TTL em `JWT_TTL` (segundos).
- Refresh token: opaco (`bin2hex(random_bytes(32))`), TTL em
  `JWT_REFRESH_TTL`. A cada `refresh`, o token antigo é revogado e um par
  novo é emitido (rotação) — reuso de um token já revogado retorna 401.
- `AuthService` (login/refresh/logout/Google) e `RoleService` (derivar
  roles) ficam em `app/Services/`, mesmo padrão dos outros Services.

### Middlewares

```text
jwt.auth        App\Http\Middleware\JwtAuthenticate  - exige Authorization: Bearer <token> válido
role:X,Y,...    App\Http\Middleware\EnsureRole        - exige que o token tenha pelo menos uma das roles
```

Registrados em `bootstrap/app.php` (`$middleware->alias([...])`). Uso em
`routes/api.php`:

```php
Route::middleware('jwt.auth')->group(function () {
    Route::middleware('role:'.Roles::ADMINISTRADOR)->group(function () {
        Route::apiResource('veiculos', VeiculoController::class)->except(['index', 'show']);
    });

    Route::apiResource('veiculos', VeiculoController::class)->only(['index', 'show']);
});
```

Mapeamento de role por recurso (ver `routes/api.php` para a lista completa):

```text
ADMINISTRADOR                    -> escrita em filiais/montadoras/oficinas/funcionarios/
                                     administradores/atendentes/gerentes-comerciais/lotes/
                                     veiculos/servicos
GERENTE_COMERCIAL, ADMINISTRADOR -> escrita em contratos-frota/vendas
ATENDENTE, ADMINISTRADOR         -> escrita em alugueis
qualquer autenticado             -> leitura (index/show) de tudo acima, e
                                     leitura+escrita de usuarios (exceto o
                                     cadastro), cnhs, pessoas-fisicas, empresas,
                                     endereços/telefones, estados/cidades/
                                     bairros/logradouros/ceps, relatorios/*
público (sem jwt.auth)           -> POST /api/usuarios (cadastro), auth/*
```

`POST /api/usuarios` fica fora do `jwt.auth` de propósito: é o único jeito de
um cliente novo conseguir uma conta antes de ter qualquer token.

### Login com Google

`composer require laravel/socialite` — usado só pela camada OAuth (retorna
um DTO com `id`/`email`/`name`; nunca chamamos `->save()` nem qualquer coisa
ligada a Eloquent). Credenciais em `config/services.php` -> `google`, lidas
de `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET`/`GOOGLE_REDIRECT_URI`.

```text
GET /api/auth/google/redirect  -> redireciona para o consentimento do Google
GET /api/auth/google/callback  -> AuthService::loginOuCriarComGoogle(), depois
                                   redireciona para
                                   {FRONTEND_URL}/auth/callback?access_token=...&refresh_token=...
```

`loginOuCriarComGoogle()` (SQL puro): busca por `Usuario_google.google_id`;
se não achar, tenta linkar por `Usuario.email`; se não achar, cria
`Usuario` novo (senha aleatória inutilizável — login por senha continua
impossível para essas contas) + `Usuario_google`. Conta criada via Google não
tem `Funcionario`/`Pessoa_Fisica`/`Empresa` ainda, então nasce sem role
(mesmo caso do cadastro público) — o front deve tratar `roles: []`
direcionando para completar o cadastro de `Pessoa_Fisica`.

### CORS

`config/cors.php`: `allowed_origins` = `FRONTEND_URL` (não `*`),
`supports_credentials = false` (tudo via `Authorization: Bearer`, sem
cookie cross-domain).

### Variáveis de ambiente

```env
JWT_SECRET=                # openssl rand -base64 32 — nunca reaproveitar APP_KEY
JWT_TTL=3600
JWT_REFRESH_TTL=2592000
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/api/auth/google/callback
FRONTEND_URL=http://localhost:5173
```

### Deploy

Runbook completo (Railway, API + MySQL) em `docs/deploy-railway.md`.

---

## Regras por módulo

### Filial

Tabela:

```text
Filial
```

Campos:

```text
id
nome
```

CRUD simples.

### Montadora

Tabela:

```text
Montadora
```

Campos:

```text
id
nome
```

CRUD simples.

### Oficina

Tabela:

```text
Oficina
```

Campos:

```text
id
nome
```

CRUD simples.

### Usuario

Tabela:

```text
Usuario
```

Campos:

```text
id
username
nome
email
ultimo_acesso
data_cadastro
ativo
senha_hash
```

Regras:

```text
- username obrigatório e único;
- email obrigatório e único;
- senha deve ser salva em senha_hash;
- pode usar Hash::make() para gerar hash;
- inserção precisa ser via SQL puro.
```

### Funcionario

Tabela:

```text
Funcionario
```

Campos:

```text
Usuario_id
nome
sobrenome
telefone
Filial_id
```

Regras:

```text
- Usuario_id deve existir em Usuario;
- Filial_id deve existir em Filial.
```

### Gerente_Comercial

Tabela:

```text
Gerente_Comercial
```

Campos:

```text
Funcionario_id
```

Regras:

```text
- Funcionario_id deve existir em Funcionario.Usuario_id.
```

### Administrador

Tabela:

```text
Administrador
```

Campos:

```text
Funcionario_id
```

Regras:

```text
- Funcionario_id deve existir em Funcionario.Usuario_id.
```

### Atendente

Tabela:

```text
Atendente
```

Campos:

```text
Funcionario_id
```

Regras:

```text
- Funcionario_id deve existir em Funcionario.Usuario_id.
```

### Empresa

Tabela:

```text
Empresa
```

Campos:

```text
Cliente_id
CNPJ
```

Regras:

```text
- Cliente_id deve existir em Usuario.id;
- CNPJ obrigatório e único.
```

### Pessoa_Fisica

Tabela:

```text
Pessoa_Fisica
```

Campos:

```text
Cliente_id
CPF
CNH
estado
categoria
data_emissao
data_validade
```

Regras:

```text
- Cliente_id deve existir em Usuario.id;
- CPF obrigatório e único;
- CNH obrigatório e único;
- data_validade não pode ser menor que data_emissao.
```

### Lote

Tabela:

```text
Lote
```

Campos:

```text
id
Gerente_Comercial_Funcionario_id
Montadora_id
preco_total
quantidade_veiculos
```

Regras:

```text
- Gerente_Comercial_Funcionario_id deve existir em Gerente_Comercial.Funcionario_id;
- Montadora_id deve existir em Montadora.id;
- preco_total deve ser positivo;
- quantidade_veiculos deve ser maior que zero e representa a quantidade comprada no lote.
```

### Veiculo

Tabela:

```text
Veiculo
```

Campos:

```text
id
status
finalidade
placa
grupo
quilometragem
Administrador_cadastro_id
Administrador_responsavel_id
Filial_id
Lote_id
```

Regras:

```text
- placa obrigatória e única;
- status obrigatório;
- finalidade obrigatória;
- grupo obrigatório;
- quilometragem não pode ser negativa;
- Administrador_cadastro_id deve existir em Administrador.Funcionario_id;
- Administrador_responsavel_id deve existir em Administrador.Funcionario_id quando informado;
- se status for diferente de VENDIDO e Administrador_responsavel_id vier vazio, usar Administrador_cadastro_id;
- se status for VENDIDO, Administrador_responsavel_id pode ser nulo;
- Filial_id deve existir em Filial.id;
- Lote_id deve existir em Lote.id.
```

### Servico

Tabela:

```text
Servico
```

Campos:

```text
id
status
data_inicio
data_fim
custo
tipo
Veiculo_id
Administrador_Funcionario_id
Oficina_id
```

Regras:

```text
- Veiculo_id deve existir em Veiculo.id;
- Administrador_Funcionario_id deve existir em Administrador.Funcionario_id;
- Oficina_id deve existir em Oficina.id;
- data_fim não pode ser menor que data_inicio;
- custo pode ser nulo, mas se informado deve ser positivo.
```

### Contrato_Frota

Tabela:

```text
Contrato_Frota
```

Campos:

```text
id
Gerente_Comercial_id
Empresa_id
data_inicio
data_final
quantidade_veiculos
```

Regras:

```text
- Gerente_Comercial_id deve existir em Gerente_Comercial.Funcionario_id;
- Empresa_id deve existir em Empresa.Usuario_id;
- data_final não pode ser menor que data_inicio;
- quantidade_veiculos deve ser maior que zero.
```

Encerramento (`PATCH /api/contratos-frota/{id}/encerrar`, ver "Stored
procedures"): define `data_final`, finaliza os `Aluguel` `ATIVO`
vinculados ao contrato e devolve os `Veiculo` correspondentes para
`DISPONIVEL`.

### Aluguel

Tabela:

```text
Aluguel
```

Campos:

```text
id
Atendente_entrega_id
Atendente_devolucao_id
Pessoa_Fisica_id
status
valor
tipo
data_inicial
data_final
data_final_prevista
Contrato_frota_id
Veiculo_id
```

Regras:

```text
- Atendente_entrega_id deve existir em Atendente.Funcionario_id;
- Atendente_devolucao_id pode ser nulo, mas se informado deve existir em Atendente.Funcionario_id;
- Pessoa_Fisica_id pode ser nulo, mas se informado deve existir em Pessoa_Fisica.Cliente_id;
- Contrato_frota_id pode ser nulo, mas se informado deve existir em Contrato_Frota.id;
- pelo menos Pessoa_Fisica_id ou Contrato_frota_id deve ser informado;
- Veiculo_id deve existir em Veiculo.id;
- valor deve ser positivo;
- data_final_prevista não pode ser menor que data_inicial;
- data_final não pode ser menor que data_inicial;
- se Pessoa_Fisica_id informado, a CNH vinculada não pode estar vencida na
  data_inicial (ver "Triggers": trg_aluguel_valida_cnh +
  AluguelService::cnhValidaParaAluguel());
- ao criar aluguel, atualizar Veiculo.status para 'ALUGADO'.
```

### Venda

Tabela:

```text
Venda
```

Campos:

```text
id
valor
status
Veiculo_id
Pessoa_Fisica_id
Gerente_Comercial_Funcionario_id
```

Regras:

```text
- Veiculo_id deve existir em Veiculo.id;
- Pessoa_Fisica_id deve existir em Pessoa_Fisica.Cliente_id;
- Gerente_Comercial_Funcionario_id deve existir em Gerente_Comercial.Funcionario_id;
- valor deve ser positivo;
- Veiculo não pode estar com status 'ALUGADO' (ver "Triggers":
  trg_venda_bloqueia_veiculo_alugado + VendaService::veiculoDisponivelParaVenda());
- ao criar venda, atualizar Veiculo.status para 'VENDIDO'.
```

---

## Endereços e telefones

Tabelas auxiliares:

```text
Telefone_oficina
Telefone_montadora
Usuario_endereco
Usuario_telefone
Oficina_endereco
Montadora_endereco
Filial_endereco
Filial_telefone
```

Essas tabelas podem ser implementadas depois dos CRUDs principais.

Regras gerais:

```text
- validar a entidade principal antes de inserir;
- usar SQL puro;
- usar transação quando inserir junto com a entidade principal;
- respeitar chaves compostas em tabelas de telefone.
```

---

## Padrão de resposta JSON

Sucesso em criação:

```json
{
  "message": "Registro criado com sucesso."
}
```

Sucesso em atualização:

```json
{
  "message": "Registro atualizado com sucesso."
}
```

Erro de registro não encontrado:

```json
{
  "message": "Registro não encontrado."
}
```

Erro de FK:

```json
{
  "message": "Registro relacionado não encontrado."
}
```

Erro de duplicidade:

```json
{
  "message": "Já existe um registro com estes dados."
}
```

---

## Status HTTP

```text
200 - listagem, busca e atualização com sucesso
201 - criação com sucesso
204 - exclusão com sucesso sem corpo
404 - registro não encontrado
409 - conflito, duplicidade ou regra de negócio
422 - erro de validação
500 - erro inesperado
```

---

## Comandos úteis

Criar controllers:

```bash
php artisan make:controller Api/FilialController --api
php artisan make:controller Api/MontadoraController --api
php artisan make:controller Api/OficinaController --api
php artisan make:controller Api/UsuarioController --api
php artisan make:controller Api/FuncionarioController --api
php artisan make:controller Api/GerenteComercialController --api
php artisan make:controller Api/AdministradorController --api
php artisan make:controller Api/AtendenteController --api
php artisan make:controller Api/EmpresaController --api
php artisan make:controller Api/PessoaFisicaController --api
php artisan make:controller Api/LoteController --api
php artisan make:controller Api/VeiculoController --api
php artisan make:controller Api/ServicoController --api
php artisan make:controller Api/ContratoFrotaController --api
php artisan make:controller Api/AluguelController --api
php artisan make:controller Api/VendaController --api
```

Criar requests:

```bash
php artisan make:request Filial/StoreFilialRequest
php artisan make:request Filial/UpdateFilialRequest

php artisan make:request Montadora/StoreMontadoraRequest
php artisan make:request Montadora/UpdateMontadoraRequest

php artisan make:request Oficina/StoreOficinaRequest
php artisan make:request Oficina/UpdateOficinaRequest

php artisan make:request Usuario/StoreUsuarioRequest
php artisan make:request Usuario/UpdateUsuarioRequest

php artisan make:request Funcionario/StoreFuncionarioRequest
php artisan make:request Funcionario/UpdateFuncionarioRequest

php artisan make:request GerenteComercial/StoreGerenteComercialRequest
php artisan make:request GerenteComercial/UpdateGerenteComercialRequest

php artisan make:request Administrador/StoreAdministradorRequest
php artisan make:request Administrador/UpdateAdministradorRequest

php artisan make:request Atendente/StoreAtendenteRequest
php artisan make:request Atendente/UpdateAtendenteRequest

php artisan make:request Empresa/StoreEmpresaRequest
php artisan make:request Empresa/UpdateEmpresaRequest

php artisan make:request PessoaFisica/StorePessoaFisicaRequest
php artisan make:request PessoaFisica/UpdatePessoaFisicaRequest

php artisan make:request Lote/StoreLoteRequest
php artisan make:request Lote/UpdateLoteRequest

php artisan make:request Veiculo/StoreVeiculoRequest
php artisan make:request Veiculo/UpdateVeiculoRequest

php artisan make:request Servico/StoreServicoRequest
php artisan make:request Servico/UpdateServicoRequest

php artisan make:request ContratoFrota/StoreContratoFrotaRequest
php artisan make:request ContratoFrota/UpdateContratoFrotaRequest

php artisan make:request Aluguel/StoreAluguelRequest
php artisan make:request Aluguel/UpdateAluguelRequest

php artisan make:request Venda/StoreVendaRequest
php artisan make:request Venda/UpdateVendaRequest
```

Criar services:

```bash
mkdir -p app/Services

touch app/Services/FilialService.php
touch app/Services/MontadoraService.php
touch app/Services/OficinaService.php
touch app/Services/UsuarioService.php
touch app/Services/FuncionarioService.php
touch app/Services/GerenteComercialService.php
touch app/Services/AdministradorService.php
touch app/Services/AtendenteService.php
touch app/Services/EmpresaService.php
touch app/Services/PessoaFisicaService.php
touch app/Services/LoteService.php
touch app/Services/VeiculoService.php
touch app/Services/ServicoService.php
touch app/Services/ContratoFrotaService.php
touch app/Services/AluguelService.php
touch app/Services/VendaService.php
```

---

## Testes manuais mínimos

Para cada CRUD, testar:

```text
GET    /api/recurso
GET    /api/recurso/{id}
POST   /api/recurso
PUT    /api/recurso/{id}
DELETE /api/recurso/{id}
```

Também testar:

```text
- criação com dados válidos;
- criação com campo obrigatório ausente;
- busca de id inexistente;
- atualização de id inexistente;
- exclusão de id inexistente;
- FK inexistente;
- duplicidade em campos únicos.
```

---

## Critérios de aceite

A implementação só está correta se:

```text
- não criar Models Eloquent;
- não usar Eloquent;
- não usar DB::table();
- não usar migrations para substituir a modelagem oficial;
- usar apenas SQL puro via DB facade;
- manter SQL dentro dos Services;
- manter Controllers simples;
- preservar nomes reais das tabelas;
- preservar nomes reais das colunas;
- usar bindings em todos os SQLs;
- validar FKs antes de inserir ou atualizar;
- validar campos únicos antes de inserir ou atualizar;
- usar DB::transaction em operações com múltiplas tabelas;
- conectar no banco locadora_imd;
- retornar JSON padronizado;
- implementar primeiro os CRUDs principais.
```

---

## Prompt recomendado para começar no Codex ou Claude Code

Implementar primeiro apenas o CRUD de Filial.

Prompt:

```text
Leia o AGENTS.md/CLAUDE.md e implemente apenas o CRUD de Filial. Não use Eloquent, não crie Model e não use DB::table(). Use SQL puro via DB facade dentro de FilialService. Crie Controller, Requests, Service e rota apiResource. Ao final, liste os arquivos alterados e explique como testar manualmente.
```

Depois seguir módulo por módulo, sem implementar tudo de uma vez.
