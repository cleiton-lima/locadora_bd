-- Seed local para desenvolvimento: filiais, endereços e frota mínima no RN.
-- Aplicar depois do schema, autenticação, procedures, triggers e views.

SET NAMES utf8mb4;

USE `locadora_imd`;

-- Senha padrao para todos os usuarios demo: 12345678
SET @senha_local = '$2y$12$aqgXOIvOG.mw6wS0kxb68.Q3t2M36Y8PX8uYPaz4SwiaVBGOZLuqS';

INSERT IGNORE INTO Estado (sigla, nome) VALUES
  ('RN', 'Rio Grande do Norte');

INSERT IGNORE INTO Cidade (id, nome, Estado_sigla) VALUES
  (1, 'Natal', 'RN'),
  (2, 'Mossoró', 'RN');

INSERT IGNORE INTO Bairro (id, nome, Cidade_id) VALUES
  (1, 'Capim Macio', 1),
  (2, 'Ponta Negra', 1),
  (3, 'Centro', 2);

INSERT IGNORE INTO Logradouro (id, nome, Bairro_id) VALUES
  (1, 'Avenida Engenheiro Roberto Freire', 1),
  (2, 'Avenida Praia de Ponta Negra', 2),
  (3, 'Avenida Alberto Maranhão', 3);

INSERT IGNORE INTO CEP (CEP, Logradouro_id) VALUES
  ('59082000', 1),
  ('59090000', 2),
  ('59600000', 3);

INSERT IGNORE INTO Filial (id, nome) VALUES
  (1, 'Natal - Capim Macio'),
  (2, 'Natal - Ponta Negra'),
  (3, 'Mossoró - Centro');

INSERT IGNORE INTO Filial_endereco (id, numero, complemento, referencia, Filial_id, CEP) VALUES
  (1, '1500', 'Agência principal', 'Próximo ao shopping', 1, '59082000'),
  (2, '220', 'Agência praia', 'Via costeira sul', 2, '59090000'),
  (3, '480', 'Agência oeste', 'Centro comercial', 3, '59600000');

INSERT IGNORE INTO Filial_telefone (Filial_id, telefone) VALUES
  (1, '8430301000'),
  (2, '8430302000'),
  (3, '8430303000');

INSERT IGNORE INTO Montadora (id, nome) VALUES
  (1, 'Fiat'),
  (2, 'Hyundai'),
  (3, 'Chevrolet');

INSERT INTO Usuario (id, username, nome, email, data_cadastro, ativo, senha_hash) VALUES
  (1, 'cliente', 'Cliente Teste', 'cliente@example.com', NOW(), 1, @senha_local),
  (2, 'cliente.rn', 'Cliente RN', 'cliente.rn@example.com', NOW(), 1, @senha_local),
  (10, 'gerente', 'Gerente Comercial RN', 'gerente@example.com', NOW(), 1, @senha_local),
  (20, 'atendente.capim', 'Atendente Capim Macio', 'atendente.capim@example.com', NOW(), 1, @senha_local),
  (21, 'atendente.ponta', 'Atendente Ponta Negra', 'atendente.ponta@example.com', NOW(), 1, @senha_local),
  (22, 'atendente.mossoro', 'Atendente Mossoró', 'atendente.mossoro@example.com', NOW(), 1, @senha_local),
  (30, 'admin', 'Administrador RN', 'admin@example.com', NOW(), 1, @senha_local)
ON DUPLICATE KEY UPDATE
  username = VALUES(username),
  nome = VALUES(nome),
  email = VALUES(email),
  ativo = VALUES(ativo),
  senha_hash = VALUES(senha_hash);

INSERT IGNORE INTO Usuario_telefone (Usuario_id, telefone) VALUES
  (1, '84999990000'),
  (2, '84999990002'),
  (10, '84999990010'),
  (20, '84999990020'),
  (21, '84999990021'),
  (22, '84999990022'),
  (30, '84999990030');

INSERT IGNORE INTO Funcionario (Usuario_id, Filial_id) VALUES
  (10, 1),
  (20, 1),
  (21, 2),
  (22, 3),
  (30, 1);

INSERT IGNORE INTO Gerente_Comercial (Funcionario_id) VALUES
  (10);

INSERT IGNORE INTO Atendente (Funcionario_id) VALUES
  (20),
  (21),
  (22);

INSERT IGNORE INTO Administrador (Funcionario_id) VALUES
  (30);

INSERT IGNORE INTO CNH (numero, estado, categoria, data_emissao, data_validade) VALUES
  ('00012345678', 'RN', 'B', '2020-01-01', '2030-01-01'),
  ('00022345678', 'RN', 'B', '2021-01-01', '2031-01-01');

INSERT INTO Pessoa_Fisica (Cliente_id, CPF, CNH_numero) VALUES
  (1, '52998224725', '00012345678'),
  (2, '11144477735', '00022345678')
ON DUPLICATE KEY UPDATE
  CPF = VALUES(CPF),
  CNH_numero = VALUES(CNH_numero);

INSERT IGNORE INTO Usuario_endereco (Usuario_id, numero, complemento, referencia, CEP) VALUES
  (1, '100', 'Apartamento 101', 'Cliente seed', '59082000'),
  (2, '220', 'Casa', 'Cliente RN seed', '59090000');

INSERT IGNORE INTO Lote (id, Gerente_Comercial_Funcionario_id, Montadora_id, preco_total, quantidade_veiculos) VALUES
  (1, 10, 1, 180000.00, 3),
  (2, 10, 2, 260000.00, 3),
  (3, 10, 3, 320000.00, 3);

INSERT IGNORE INTO Veiculo (
  id,
  status,
  finalidade,
  placa,
  grupo,
  quilometragem,
  Administrador_cadastro_id,
  Administrador_responsavel_id,
  Filial_id,
  Lote_id
) VALUES
  (100, 'DISPONIVEL', 'CURTA_DURACAO', 'RN10001', 'A', 8500, 30, 30, 1, 1),
  (101, 'DISPONIVEL', 'CURTA_DURACAO', 'RN10002', 'B', 11200, 30, 30, 1, 2),
  (102, 'DISPONIVEL', 'LONGA_DURACAO', 'RN10003', 'C', 19400, 30, 30, 1, 3),
  (200, 'DISPONIVEL', 'CURTA_DURACAO', 'RN20001', 'A', 7200, 30, 30, 2, 1),
  (201, 'DISPONIVEL', 'CURTA_DURACAO', 'RN20002', 'B', 9800, 30, 30, 2, 2),
  (202, 'DISPONIVEL', 'LONGA_DURACAO', 'RN20003', 'C', 16000, 30, 30, 2, 3),
  (300, 'DISPONIVEL', 'CURTA_DURACAO', 'RN30001', 'A', 6400, 30, 30, 3, 1),
  (301, 'DISPONIVEL', 'CURTA_DURACAO', 'RN30002', 'B', 8900, 30, 30, 3, 2),
  (302, 'DISPONIVEL', 'LONGA_DURACAO', 'RN30003', 'C', 14000, 30, 30, 3, 3);
