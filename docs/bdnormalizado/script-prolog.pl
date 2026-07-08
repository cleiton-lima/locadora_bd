% Carregamos a biblioteca de listas para usar subset/2
:- use_module(library(lists)).

% ==========================================
% 1. FATOS: MAPEAMENTO DE 100% DO SCHEMA
% ==========================================

% --- TABELAS SIMPLES (CADASTROS BASE) ---
relation(oficina, [id, nome]).
key(oficina, [id]).
fd(oficina, [id], [nome]).

relation(montadora, [id, nome]).
key(montadora, [id]).
fd(montadora, [id], [nome]).

relation(filial, [id, nome]).
key(filial, [id]).
fd(filial, [id], [nome]).

relation(estado, [sigla, nome]).
key(estado, [sigla]).
key(estado, [nome]). % UNIQUE
fd(estado, [sigla], [nome]).
fd(estado, [nome], [sigla]).

% --- TABELAS DE USUÁRIOS E FUNCIONÁRIOS ---
relation(usuario, [id, username, nome, email, ultimo_acesso, data_cadastro, ativo, senha_hash]).
key(usuario, [id]).
key(usuario, [username]). % UNIQUE
key(usuario, [email]). % UNIQUE
fd(usuario, [id], [username, nome, email, ultimo_acesso, data_cadastro, ativo, senha_hash]).
fd(usuario, [username], [id, nome, email, ultimo_acesso, data_cadastro, ativo, senha_hash]).
fd(usuario, [email], [id, username, nome, ultimo_acesso, data_cadastro, ativo, senha_hash]).

% --- AUTENTICAÇÃO (JWT + login Google) ---
relation(usuario_google, [usuario_id, google_id]).
key(usuario_google, [usuario_id]).
key(usuario_google, [google_id]). % UNIQUE
fd(usuario_google, [usuario_id], [google_id]).
fd(usuario_google, [google_id], [usuario_id]).

relation(refresh_token, [id, usuario_id, token_hash, expires_at, revoked_at, created_at]).
key(refresh_token, [id]).
key(refresh_token, [token_hash]). % UNIQUE
fd(refresh_token, [id], [usuario_id, token_hash, expires_at, revoked_at, created_at]).
fd(refresh_token, [token_hash], [id, usuario_id, expires_at, revoked_at, created_at]).

relation(funcionario, [usuario_id, nome, sobrenome, telefone, filial_id]).
key(funcionario, [usuario_id]).
fd(funcionario, [usuario_id], [nome, sobrenome, telefone, filial_id]).

relation(gerente_comercial, [funcionario_id]).
key(gerente_comercial, [funcionario_id]).
% Sem atributos além da PK, não há dependências não-triviais

relation(administrador, [funcionario_id]).
key(administrador, [funcionario_id]).

relation(atendente, [funcionario_id]).
key(atendente, [funcionario_id]).

relation(empresa, [usuario_id, cnpj]).
key(empresa, [usuario_id]).
key(empresa, [cnpj]). % UNIQUE
fd(empresa, [usuario_id], [cnpj]).
fd(empresa, [cnpj], [usuario_id]).

relation(cnh, [numero, estado, categoria, data_emissao, data_validade]).
key(cnh, [numero]).
fd(cnh, [numero], [estado, categoria, data_emissao, data_validade]).

relation(pessoa_fisica, [cliente_id, cpf, cnh_numero]).
key(pessoa_fisica, [cliente_id]).
key(pessoa_fisica, [cpf]). % UNIQUE
key(pessoa_fisica, [cnh_numero]). % UNIQUE
fd(pessoa_fisica, [cliente_id], [cpf, cnh_numero]).
fd(pessoa_fisica, [cpf], [cliente_id, cnh_numero]).
fd(pessoa_fisica, [cnh_numero], [cliente_id, cpf]).

% --- TABELAS DE NEGÓCIO ---
relation(lote, [id, gerente_comercial_funcionario_id, montadora_id, preco_total, quantidade_veiculos]).
key(lote, [id]).
fd(lote, [id], [gerente_comercial_funcionario_id, montadora_id, preco_total, quantidade_veiculos]).

relation(veiculo, [id, status, finalidade, placa, grupo, quilometragem, administrador_funcionario_id, filial_id, lote_id]).
key(veiculo, [id]).
key(veiculo, [placa]). % UNIQUE
fd(veiculo, [id], [status, finalidade, placa, grupo, quilometragem, administrador_funcionario_id, filial_id, lote_id]).
fd(veiculo, [placa], [id, status, finalidade, grupo, quilometragem, administrador_funcionario_id, filial_id, lote_id]).

relation(servico, [id, status, data_inicio, data_fim, custo, tipo, veiculo_id, administrador_funcionario_id, oficina_id]).
key(servico, [id]).
fd(servico, [id], [status, data_inicio, data_fim, custo, tipo, veiculo_id, administrador_funcionario_id, oficina_id]).

relation(contrato_frota, [id, gerente_comercial_id, empresa_id, data_inicio, data_final, quantidade_veiculos]).
key(contrato_frota, [id]).
fd(contrato_frota, [id], [gerente_comercial_id, empresa_id, data_inicio, data_final, quantidade_veiculos]).

relation(aluguel, [id, atendente_entrega_id, atendente_devolucao_id, pessoa_fisica_id, status, valor, tipo, data_inicial, data_final, data_final_prevista, contrato_frota_id, veiculo_id]).
key(aluguel, [id]).
fd(aluguel, [id], [atendente_entrega_id, atendente_devolucao_id, pessoa_fisica_id, status, valor, tipo, data_inicial, data_final, data_final_prevista, contrato_frota_id, veiculo_id]).

relation(venda, [id, valor, status, veiculo_id, pessoa_fisica_id, gerente_comercial_funcionario_id]).
key(venda, [id]).
fd(venda, [id], [valor, status, veiculo_id, pessoa_fisica_id, gerente_comercial_funcionario_id]).

% --- TABELAS MULTIVALORADAS (NxM / Telefones) ---
relation(telefone_oficina, [oficina_id, telefone]).
key(telefone_oficina, [oficina_id, telefone]).

relation(telefone_montadora, [montadora_id, telefone]).
key(telefone_montadora, [montadora_id, telefone]).

relation(usuario_telefone, [usuario_id, telefone]).
key(usuario_telefone, [usuario_id, telefone]).

relation(filial_telefone, [filial_id, telefone]).
key(filial_telefone, [filial_id, telefone]).

% --- ENDEREÇOS E HIERARQUIA GEOGRÁFICA ---
relation(cidade, [id, nome, estado_sigla]).
key(cidade, [id]).
key(cidade, [nome, estado_sigla]). % UNIQUE composto
fd(cidade, [id], [nome, estado_sigla]).
fd(cidade, [nome, estado_sigla], [id]).

relation(bairro, [id, nome, cidade_id]).
key(bairro, [id]).
key(bairro, [nome, cidade_id]). % UNIQUE composto
fd(bairro, [id], [nome, cidade_id]).
fd(bairro, [nome, cidade_id], [id]).

relation(logradouro, [id, nome, bairro_id]).
key(logradouro, [id]).
key(logradouro, [nome, bairro_id]). % UNIQUE composto
fd(logradouro, [id], [nome, bairro_id]).
fd(logradouro, [nome, bairro_id], [id]).

relation(cep, [cep, logradouro_id]).
key(cep, [cep]).
fd(cep, [cep], [logradouro_id]).

relation(usuario_endereco, [usuario_id, numero, complemento, referencia, cep]).
key(usuario_endereco, [usuario_id]).
fd(usuario_endereco, [usuario_id], [numero, complemento, referencia, cep]).

relation(oficina_endereco, [id, numero, complemento, referencia, oficina_id, cep]).
key(oficina_endereco, [id]).
fd(oficina_endereco, [id], [numero, complemento, referencia, oficina_id, cep]).

relation(montadora_endereco, [id, montadora_id, numero, complemento, referencia, cep]).
key(montadora_endereco, [id]).
fd(montadora_endereco, [id], [montadora_id, numero, complemento, referencia, cep]).

relation(filial_endereco, [id, numero, complemento, referencia, filial_id, cep]).
key(filial_endereco, [id]).
fd(filial_endereco, [id], [numero, complemento, referencia, filial_id, cep]).


% ==========================================
% 2. REGRAS: LÓGICA DE VERIFICAÇÃO BCNF
% ==========================================

is_bcnf(Tabela) :-
    relation(Tabela, _),
    \+ violacao_bcnf(Tabela, _, _),
    format('SUCESSO: A tabela ~w ESTA na Forma Normal de Boyce-Codd (BCNF).~n', [Tabela]).

check_violations(Tabela) :-
    violacao_bcnf(Tabela, X, Y),
    format('FALHA: Violacao BCNF na tabela ~w. A dependencia ~w -> ~w nao atende aos requisitos.~n', [Tabela, X, Y]).

violacao_bcnf(Tabela, X, Y) :-
    fd(Tabela, X, Y),
    \+ trivial(X, Y),
    \+ superchave(Tabela, X).

trivial(X, Y) :-
    subset(Y, X).

superchave(Tabela, X) :-
    key(Tabela, ChaveCandidata),
    subset(ChaveCandidata, X).

% ==========================================
% 3. EXECUÇÃO
% ==========================================
analisar_schema :-
    format('~n=== INICIANDO ANALISE BCNF DO SCHEMA LOCADORA ===~n~n', []),
    forall(relation(Tabela, _), is_bcnf(Tabela)),
    format('~n=== ANALISE CONCLUIDA ===~n', []).