-- Views de relatório para o schema locadora_imd (BCNF).
-- Aplicar depois de docs/bdnormalizado/locadora_imd-bcnf.sql:
--
--   mysql -u root -p locadora_imd < docs/bdnormalizado/views.sql
--
-- São views de leitura, consumidas por RelatorioService via
-- `SELECT * FROM vw_...`. Não substituem CRUD nem contêm regra de
-- negócio — só agregam dados que hoje exigiriam várias consultas manuais.

USE `locadora_imd`;

DROP VIEW IF EXISTS vw_frota_disponivel;
DROP VIEW IF EXISTS vw_ocupacao_frota;
DROP VIEW IF EXISTS vw_contrato_frota_resumo;
DROP VIEW IF EXISTS vw_veiculos_manutencao;

-- Veículos disponíveis para locação agora, com a filial. Suporta a
-- cotação (RF01/RF02 do documento de visão): mostrar frota disponível
-- por categoria/filial antes de calcular o valor.
CREATE VIEW vw_frota_disponivel AS
SELECT
    v.id AS veiculo_id,
    v.grupo,
    v.finalidade,
    v.placa,
    v.quilometragem,
    v.Filial_id,
    f.nome AS filial_nome
FROM Veiculo v
JOIN Filial f ON f.id = v.Filial_id
WHERE v.status = 'DISPONIVEL';

-- Ocupação da frota por filial e grupo: total de veículos, quantos estão
-- alugados/disponíveis e o percentual de ocupação. Suporta a necessidade
-- de "otimização de frota" (documento de visão, seção 4).
CREATE VIEW vw_ocupacao_frota AS
SELECT
    v.Filial_id,
    f.nome AS filial_nome,
    v.grupo,
    COUNT(*) AS total_veiculos,
    SUM(CASE WHEN v.status = 'ALUGADO' THEN 1 ELSE 0 END) AS veiculos_alugados,
    SUM(CASE WHEN v.status = 'DISPONIVEL' THEN 1 ELSE 0 END) AS veiculos_disponiveis,
    ROUND(
        SUM(CASE WHEN v.status = 'ALUGADO' THEN 1 ELSE 0 END) / COUNT(*) * 100,
        2
    ) AS percentual_ocupacao
FROM Veiculo v
JOIN Filial f ON f.id = v.Filial_id
GROUP BY v.Filial_id, f.nome, v.grupo;

-- Contratos de frota com a empresa e quantos veículos estão atualmente
-- alugados sob cada contrato (Aluguel ATIVO com aquele Contrato_frota_id).
-- Suporta a necessidade da Empresa (CNPJ) de monitorar a frota
-- terceirizada (documento de visão, seção 2.2).
CREATE VIEW vw_contrato_frota_resumo AS
SELECT
    cf.id AS contrato_id,
    cf.Empresa_id,
    e.CNPJ,
    cf.data_inicio,
    cf.data_final,
    cf.quantidade_veiculos AS quantidade_veiculos_contratada,
    COUNT(a.id) AS veiculos_alugados_ativos
FROM Contrato_Frota cf
JOIN Empresa e ON e.Usuario_id = cf.Empresa_id
LEFT JOIN Aluguel a ON a.Contrato_frota_id = cf.id AND a.status = 'ATIVO'
GROUP BY cf.id, cf.Empresa_id, e.CNPJ, cf.data_inicio, cf.data_final, cf.quantidade_veiculos;

-- Veículos atualmente em manutenção (Servico com status ATIVO), com a
-- oficina responsável. Dashboard operacional simples.
CREATE VIEW vw_veiculos_manutencao AS
SELECT
    s.id AS servico_id,
    s.Veiculo_id,
    v.placa,
    s.tipo,
    s.status,
    s.data_inicio,
    s.data_fim,
    s.custo,
    s.Oficina_id,
    o.nome AS oficina_nome
FROM Servico s
JOIN Veiculo v ON v.id = s.Veiculo_id
JOIN Oficina o ON o.id = s.Oficina_id
WHERE s.status = 'ATIVO';
