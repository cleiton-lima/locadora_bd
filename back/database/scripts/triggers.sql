-- Triggers para o schema locadora_imd (BCNF).
-- Aplicar depois de docs/bdnormalizado/locadora_imd-bcnf.sql:
--
--   mysql -u root -p locadora_imd < docs/bdnormalizado/triggers.sql
--
-- Estas triggers são uma segunda linha de defesa para regras de negócio
-- que a API já valida em PHP antes do INSERT (ver AluguelService e
-- VendaService). Elas garantem a integridade mesmo se alguém inserir
-- direto via SQL, sem passar pela API.

USE `locadora_imd`;

DROP TRIGGER IF EXISTS trg_aluguel_valida_cnh;
DROP TRIGGER IF EXISTS trg_venda_bloqueia_veiculo_alugado;

DELIMITER $$

-- Impede criar um Aluguel para pessoa física cuja CNH já estava vencida
-- na data_inicial da retirada. Regra vem da seção 1.2/4 do documento de
-- visão ("validação presencial de CNH na hora de retirada do veículo").
CREATE TRIGGER trg_aluguel_valida_cnh
BEFORE INSERT ON Aluguel
FOR EACH ROW
BEGIN
    DECLARE v_data_validade DATE;

    IF NEW.Pessoa_Fisica_id IS NOT NULL THEN
        SELECT c.data_validade
          INTO v_data_validade
          FROM Pessoa_Fisica pf
          JOIN CNH c ON c.numero = pf.CNH_numero
         WHERE pf.Cliente_id = NEW.Pessoa_Fisica_id;

        IF v_data_validade IS NOT NULL AND v_data_validade < DATE(NEW.data_inicial) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'CNH vencida para a data de retirada.';
        END IF;
    END IF;
END$$

-- Impede vender um Veiculo que está com status 'ALUGADO'.
CREATE TRIGGER trg_venda_bloqueia_veiculo_alugado
BEFORE INSERT ON Venda
FOR EACH ROW
BEGIN
    DECLARE v_status VARCHAR(30);

    SELECT status INTO v_status FROM Veiculo WHERE id = NEW.Veiculo_id;

    IF v_status = 'ALUGADO' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Veículo indisponível para venda: está alugado.';
    END IF;
END$$

DELIMITER ;
