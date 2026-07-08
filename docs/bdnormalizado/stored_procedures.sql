-- Stored procedures para o schema locadora_imd (BCNF).
-- Aplicar depois de docs/bdnormalizado/locadora_imd-bcnf.sql:
--
--   mysql -u root -p locadora_imd < docs/bdnormalizado/stored_procedures.sql
--
-- Cobrem os fluxos transacionais de Aluguel (criação e devolução), Venda
-- (criação) e Contrato_Frota (encerramento), hoje implementados em
-- app/Services/AluguelService.php, VendaService.php e
-- ContratoFrotaService.php. As regras de bloqueio (veículo ALUGADO,
-- aluguel ATIVO, contrato de frota ativo) e a validação de FKs continuam
-- no PHP/Controller antes de chamar cada procedure; elas assumem que os
-- dados já foram validados e cuidam apenas da escrita atômica.

USE `locadora_imd`;

DROP PROCEDURE IF EXISTS sp_criar_aluguel;
DROP PROCEDURE IF EXISTS sp_devolver_aluguel;
DROP PROCEDURE IF EXISTS sp_criar_venda;
DROP PROCEDURE IF EXISTS sp_encerrar_contrato_frota;

DELIMITER $$

-- Cria um Aluguel calculando o valor a partir do grupo do veículo,
-- da disponibilidade na filial (escassez) e da duração (desconto em
-- locações longas), e marca o Veiculo como 'ALUGADO'. Espelha
-- AluguelService::criar()/calcularValor().
CREATE PROCEDURE sp_criar_aluguel(
    IN p_atendente_entrega_id INT,
    IN p_atendente_devolucao_id INT,
    IN p_pessoa_fisica_id INT,
    IN p_status VARCHAR(30),
    IN p_tipo VARCHAR(30),
    IN p_data_inicial DATETIME,
    IN p_data_final DATETIME,
    IN p_data_final_prevista DATETIME,
    IN p_contrato_frota_id INT,
    IN p_veiculo_id INT,
    OUT p_novo_id INT,
    OUT p_valor DECIMAL(10,2)
)
proc: BEGIN
    DECLARE v_grupo CHAR(1);
    DECLARE v_finalidade VARCHAR(30);
    DECLARE v_filial_id INT;
    DECLARE v_horas INT;
    DECLARE v_dias INT;
    DECLARE v_base DECIMAL(10,2);
    DECLARE v_disponiveis INT;
    DECLARE v_escassez DECIMAL(5,2);
    DECLARE v_desconto DECIMAL(5,2);

    SELECT grupo, finalidade, Filial_id
      INTO v_grupo, v_finalidade, v_filial_id
      FROM Veiculo
     WHERE id = p_veiculo_id;

    IF v_filial_id IS NULL THEN
        SET p_novo_id = NULL;
        SET p_valor = NULL;
        LEAVE proc;
    END IF;

    SET v_horas = GREATEST(1, CEIL(TIMESTAMPDIFF(SECOND, p_data_inicial, p_data_final_prevista) / 3600));

    IF v_horas < 24 THEN
        SET v_base = CASE v_grupo
            WHEN 'A' THEN 25 WHEN 'B' THEN 35 WHEN 'C' THEN 45
            WHEN 'D' THEN 60 WHEN 'E' THEN 80 ELSE 40 END;

        SELECT COUNT(*) INTO v_disponiveis
          FROM Veiculo
         WHERE Filial_id = v_filial_id
           AND finalidade = 'CURTA_DURACAO'
           AND status = 'DISPONIVEL';

        SET v_escassez = 1 + GREATEST(0, 5 - v_disponiveis) * 0.10;
        SET p_valor = ROUND(v_base * v_horas * v_escassez, 2);
    ELSE
        SET v_dias = GREATEST(1, CEIL(v_horas / 24));
        SET v_base = CASE v_grupo
            WHEN 'A' THEN 120 WHEN 'B' THEN 160 WHEN 'C' THEN 220
            WHEN 'D' THEN 300 WHEN 'E' THEN 420 ELSE 180 END;

        SELECT COUNT(*) INTO v_disponiveis
          FROM Veiculo
         WHERE Filial_id = v_filial_id
           AND finalidade = 'LONGA_DURACAO'
           AND status = 'DISPONIVEL';

        SET v_escassez = 1 + GREATEST(0, 5 - v_disponiveis) * 0.10;
        SET v_desconto = CASE
            WHEN v_dias >= 30 THEN 0.80
            WHEN v_dias >= 15 THEN 0.85
            WHEN v_dias >= 7 THEN 0.90
            ELSE 1.00 END;
        SET p_valor = ROUND(v_base * v_dias * v_escassez * v_desconto, 2);
    END IF;

    START TRANSACTION;

    INSERT INTO Aluguel (
        Atendente_entrega_id, Atendente_devolucao_id, Pessoa_Fisica_id,
        status, valor, tipo, data_inicial, data_final, data_final_prevista,
        Contrato_frota_id, Veiculo_id
    ) VALUES (
        p_atendente_entrega_id, p_atendente_devolucao_id, p_pessoa_fisica_id,
        p_status, p_valor, p_tipo, p_data_inicial, p_data_final, p_data_final_prevista,
        p_contrato_frota_id, p_veiculo_id
    );

    SET p_novo_id = LAST_INSERT_ID();

    UPDATE Veiculo SET status = 'ALUGADO' WHERE id = p_veiculo_id;

    COMMIT;
END$$

-- Finaliza um Aluguel (status = 'FINALIZADO') e devolve o Veiculo para
-- 'DISPONIVEL'. Espelha AluguelService::devolver().
CREATE PROCEDURE sp_devolver_aluguel(
    IN p_aluguel_id INT,
    IN p_data_final DATETIME,
    IN p_atendente_devolucao_id INT,
    OUT p_linhas_afetadas INT
)
proc: BEGIN
    DECLARE v_veiculo_id INT;

    SELECT Veiculo_id INTO v_veiculo_id
      FROM Aluguel
     WHERE id = p_aluguel_id;

    IF v_veiculo_id IS NULL THEN
        SET p_linhas_afetadas = 0;
        LEAVE proc;
    END IF;

    START TRANSACTION;

    UPDATE Aluguel
       SET status = 'FINALIZADO',
           data_final = p_data_final,
           Atendente_devolucao_id = p_atendente_devolucao_id
     WHERE id = p_aluguel_id;

    UPDATE Veiculo
       SET status = 'DISPONIVEL'
     WHERE id = v_veiculo_id;

    COMMIT;
    SET p_linhas_afetadas = 1;
END$$

-- Cria uma Venda e marca o Veiculo como 'VENDIDO' numa transação atômica.
-- Espelha VendaService::criar(). O bloqueio de vender veículo ALUGADO é
-- garantido separadamente pela trigger trg_venda_bloqueia_veiculo_alugado
-- (docs/bdnormalizado/triggers.sql), que dispara mesmo chamando esta
-- procedure, já que atua sobre o INSERT em Venda.
CREATE PROCEDURE sp_criar_venda(
    IN p_valor DECIMAL(10,2),
    IN p_status VARCHAR(30),
    IN p_veiculo_id INT,
    IN p_pessoa_fisica_id INT,
    IN p_gerente_comercial_funcionario_id INT,
    OUT p_novo_id INT
)
BEGIN
    START TRANSACTION;

    INSERT INTO Venda (
        valor, status, Veiculo_id, Pessoa_Fisica_id, Gerente_Comercial_Funcionario_id
    ) VALUES (
        p_valor, p_status, p_veiculo_id, p_pessoa_fisica_id, p_gerente_comercial_funcionario_id
    );

    SET p_novo_id = LAST_INSERT_ID();

    UPDATE Veiculo SET status = 'VENDIDO' WHERE id = p_veiculo_id;

    COMMIT;
END$$

-- Encerra um Contrato_Frota: define data_final, finaliza (status =
-- 'FINALIZADO') todos os Aluguel ATIVO vinculados a ele e devolve os
-- respectivos Veiculo para 'DISPONIVEL'. Espelha
-- ContratoFrotaService::encerrar() (RF10 - Encerramento do Contrato).
CREATE PROCEDURE sp_encerrar_contrato_frota(
    IN p_contrato_id INT,
    IN p_data_final DATETIME,
    OUT p_veiculos_liberados INT
)
proc: BEGIN
    DECLARE v_existe INT;

    SELECT COUNT(*) INTO v_existe FROM Contrato_Frota WHERE id = p_contrato_id;

    IF v_existe = 0 THEN
        SET p_veiculos_liberados = -1;
        LEAVE proc;
    END IF;

    START TRANSACTION;

    UPDATE Veiculo v
        JOIN Aluguel a ON a.Veiculo_id = v.id
       SET v.status = 'DISPONIVEL'
     WHERE a.Contrato_frota_id = p_contrato_id
       AND a.status = 'ATIVO';

    SET p_veiculos_liberados = ROW_COUNT();

    UPDATE Aluguel
       SET status = 'FINALIZADO',
           data_final = p_data_final
     WHERE Contrato_frota_id = p_contrato_id
       AND status = 'ATIVO';

    UPDATE Contrato_Frota
       SET data_final = p_data_final
     WHERE id = p_contrato_id;

    COMMIT;
END$$

DELIMITER ;
