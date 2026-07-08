# Comparação: modelagem original vs. modelagem normalizada

Este documento compara `docs/locadora_imd.sql` (schema original, usado como base da API atual)
com `docs/bdnormalizado/locadora-normalizada.sql` (schema após o processo de normalização),
e lista o que precisa ser corrigido/ajustado.

## 1. Diferenças estruturais entre os dois scripts

Tabelas sem nenhuma alteração: `Oficina`, `Montadora`, `Filial`, `Usuario`, `Funcionario`,
`Gerente_Comercial`, `Administrador`, `Lote`, `Veiculo`, `Servico`, `Atendente`, `Aluguel`,
`Venda`, `Telefone_oficina`, `Telefone_montadora`, `Usuario_telefone`, `Filial_telefone`.

Detalhe cosmético: o schema mudou de nome (`locadora_imd`, utf8mb4) para `mydb` (utf8) no
script normalizado — confirmar qual nome/charset deve valer no ambiente final.

### 1.1 `Empresa`: coluna renomeada
- Antes: `Cliente_id` (PK, FK para `Usuario.id`)
- Depois: `Usuario_id` (PK, FK para `Usuario.id`)
- Efeito em cascata: `Contrato_Frota.Empresa_id` agora referencia `Empresa(Usuario_id)`.

### 1.2 `Pessoa_Fisica`: CNH extraída para tabela própria
- Antes: `Cliente_id, CPF, CNH, estado, categoria, data_emissao, data_validade` tudo na mesma tabela.
- Depois:
  - `Pessoa_Fisica`: `Cliente_id (PK), CPF (UNIQUE), CNH_numero (FK)`
  - Nova tabela `CNH`: `numero (PK), estado, categoria, data_emissao, data_validade`
- Motivo: `estado/categoria/data_emissao/data_validade` são atributos da CNH, não da pessoa.

### 1.3 Endereços: CEP extraído para tabela compartilhada (parcialmente)
- `Oficina_endereco`, `Montadora_endereco`, `Filial_endereco`: antes tinham
  `estado, cidade, bairro, logradouro, CEP` embutidos; depois passam a referenciar uma nova
  tabela `CEP (CEP PK, estado, cidade, bairro)` e mantêm `logradouro, numero, complemento, referencia` localmente.
- `complemento` e `referencia` deixaram de ser `NULL` e viraram `NOT NULL` nessas tabelas
  (e também em `Usuario_endereco`).
- `Usuario_endereco` seguiu um caminho **diferente**: em vez de reusar a tabela `CEP`
  compartilhada, foi criada uma tabela separada `CEP_user (CEP PK, estado, cidade, bairro,
  logradouro, Usuario_endereco_Usuario_id FK)`.

## 2. Impacto na API atual (`back/`)

A API já tem `Requests`/`Controllers` construídos em cima do schema **original**. Nada em
`Models`/`database/migrations` foi criado ainda, então dá pra ajustar sem migração de dados.

| Tabela | Mudança | Onde ajustar |
|---|---|---|
| `Empresa` | `cliente_id` → `usuario_id` | `Http/Requests/Empresa/StoreEmpresaRequest.php`, `UpdateEmpresaRequest.php`, `EmpresaController.php` |
| `Pessoa_Fisica` / `CNH` | CNH virou entidade própria (`cnh`, `estado`, `categoria`, `data_emissao`, `data_validade` saem da Pessoa Física) | `Http/Requests/PessoaFisica/StorePessoaFisicaRequest.php`, `UpdatePessoaFisicaRequest.php`, `PessoaFisicaController.php`; falta criar CRUD de `CNH` |
| `Oficina_endereco`, `Montadora_endereco`, `Filial_endereco` | CEP passa a ser referência a tabela compartilhada; `complemento`/`referencia` obrigatórios | ainda não existem Requests/Controllers de endereço — devem já nascer seguindo o modelo novo |
| `Usuario_endereco` | modelo `CEP_user` divergente (ver seção 3) | idem — resolver o ponto 3 antes de implementar |

## 3. Problemas de normalização encontrados (BCNF)

A modelagem normalizada **não está 100% em BCNF**. Pontos encontrados, do mais crítico ao mais discutível:

### 3.0 Ajustes já incorporados ao schema oficial

O script `docs/bdnormalizado/locadora_imd-bcnf.sql` já incorpora uma rodada
de correções de anomalia usadas pela API:

- `Funcionario` não duplica mais `nome`, `sobrenome` e `telefone`; nome/e-mail
  vêm de `Usuario`, telefones vêm de `Usuario_telefone`.
- `Veiculo` separa `Administrador_cadastro_id` (histórico obrigatório) de
  `Administrador_responsavel_id` (responsável atual, nulo quando vendido).
- `Aluguel` tem `CHECK` para garantir exatamente um alvo: pessoa física ou
  contrato de frota.
- `Lote.quantidade_veiculos` fica documentado como quantidade comprada no
  lote, não como quantidade atual derivada de `Veiculo`.

### 3.1 `logradouro` tratado de forma inconsistente entre as tabelas de endereço (crítico)
No Brasil, um CEP de logradouro normalmente já determina a rua, ou seja `CEP → logradouro`
é uma dependência funcional válida, não só `CEP → estado/cidade/bairro`.
- Em `CEP_user`, o design está coerente: `CEP` é PK e `logradouro` está dentro dela.
- Em `Oficina_endereco`, `Montadora_endereco` e `Filial_endereco`, `logradouro` ficou **fora**
  da tabela `CEP` compartilhada, guardado na própria tabela de endereço (cuja chave é o `id`
  surrogate). Como `CEP` não é chave dessas três tabelas e determina `logradouro`, isso viola BCNF.
- **Correção sugerida:** mover `logradouro` para dentro da tabela `CEP` compartilhada (junto de
  `estado`, `cidade`, `bairro`), como já foi feito em `CEP_user`.

### 3.2 `CEP_user` tem cardinalidade errada (crítico)
`CEP_user` usa `CEP` como **chave primária** e carrega `Usuario_endereco_Usuario_id` como FK
obrigatória. Isso força, estruturalmente, que um CEP só possa estar associado a **um único**
endereço de usuário no sistema inteiro — dois clientes que morem na mesma rua (mesmo CEP) vão
colidir na PK ao tentar inserir o segundo endereço.
- A relação real é N:1 (muitos endereços de usuário → um CEP), mas o modelo força 1:1.
- **Correção sugerida:** eliminar `CEP_user` e fazer `Usuario_endereco` referenciar a mesma
  tabela `CEP` genérica usada por `Oficina_endereco`/`Montadora_endereco`/`Filial_endereco`,
  igual ao padrão das outras três. Isso também resolve o ponto 3.1 de uma vez, já que a tabela
  `CEP` passaria a conter `logradouro` para todo mundo.

### 3.3 `Pessoa_Fisica.CNH_numero` sem `UNIQUE` (integridade, não BCNF em si)
Semanticamente uma CNH pertence a uma única pessoa (`CNH_numero → Cliente_id` deveria valer),
mas no schema `CNH_numero` só tem um índice normal (`INDEX`), não um `UNIQUE INDEX`.
- Pela letra da DDL isso não quebra BCNF de `Pessoa_Fisica` (a única FD declarada tem `Cliente_id`/
  `CPF` como determinante, e ambos são chave), mas deixa uma brecha de integridade: nada impede
  duas pessoas apontando para o mesmo número de CNH.
- **Correção sugerida:** adicionar `UNIQUE INDEX` em `CNH_numero` na tabela `Pessoa_Fisica`.

### 3.4 `CEP → estado, cidade, bairro` ainda esconde `cidade → estado` (discutível)
Uma cidade pertence a um único estado (`cidade → estado`), e `cidade` não é chave da tabela
`CEP` (várias linhas de `CEP` repetem a mesma cidade). A rigor isso viola até 3NF/BCNF se a
decomposição for levada até o fim (implicaria criar uma tabela `Cidade(estado, cidade)` e
`CEP` referenciar só a cidade).
- Isso costuma ser aceito como simplificação prática em modelagens desse porte — mas fica
  registrado caso o professor/avaliador exija normalização completa.

## 4. Resumo do que precisa ser feito
1. Corrigir `Empresa`: renomear `Cliente_id` → `Usuario_id` (e ajustar `Contrato_Frota`).
2. Extrair `CNH` como tabela própria e ajustar `Pessoa_Fisica` (`CPF`, `CNH_numero`), adicionando
   `UNIQUE` em `CNH_numero`.
3. Unificar a tabela de CEP: eliminar `CEP_user`, mover `logradouro` para dentro da tabela `CEP`
   compartilhada, e fazer `Usuario_endereco`, `Oficina_endereco`, `Montadora_endereco` e
   `Filial_endereco` referenciarem essa mesma tabela `CEP`.
4. (Opcional/discutir) Avaliar se vale a pena extrair `Cidade`/`Estado` da tabela `CEP` para
   fechar BCNF por completo.
5. Depois de fechado o modelo, atualizar `Requests`/`Controllers` da API (`Empresa`,
   `PessoaFisica`) e criar os que faltam (`CNH`, `CEP`, endereços).

## 5. Extensão: schema de autenticação (JWT + login Google)

Para viabilizar login com JWT e login via Google sem ORM, foram adicionadas duas
tabelas aditivas em `docs/bdnormalizado/auth-schema.sql`, seguindo o mesmo estilo
1:1 de extensão de `Usuario` já usado por `Usuario_endereco`/`Usuario_telefone`.
Nenhuma tabela existente foi alterada.

- **`Usuario_google (Usuario_id PK/FK → Usuario.id, google_id UNIQUE)`**: vínculo
  opcional 1:1 entre um `Usuario` e uma conta Google. `Usuario_id` já é
  superchave (chave primária), então `google_id` (também `UNIQUE`, chave
  candidata alternativa) não cria nenhuma dependência funcional fora de uma
  superchave — BCNF trivial.
- **`Refresh_Token (id PK, Usuario_id FK, token_hash UNIQUE, expires_at,
  revoked_at, created_at)`**: 1:N com `Usuario` (um usuário pode ter vários
  tokens ao longo do tempo). `id` e `token_hash` são as duas chaves candidatas;
  todo atributo depende inteiramente de uma delas — BCNF trivial.

Verificação automatizada (`docs/bdnormalizado/script-prolog.pl`, função
`analisar_schema/0`, executada com `swipl -g analisar_schema -t halt
docs/bdnormalizado/script-prolog.pl`):

```text
SUCESSO: A tabela usuario_google ESTA na Forma Normal de Boyce-Codd (BCNF).
SUCESSO: A tabela refresh_token ESTA na Forma Normal de Boyce-Codd (BCNF).
```

Todas as demais 30 tabelas do schema original continuam `SUCESSO` após a
extensão — nenhuma regressão de normalização.

**Pendente (ação manual)**: replicar as duas tabelas no diagrama ER (`.dia`) e no
modelo relacional do MySQL Workbench, que não fazem parte deste repositório.
