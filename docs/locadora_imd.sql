-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema locadora_imd
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema locadora_imd
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `locadora_imd` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
USE `locadora_imd` ;

-- -----------------------------------------------------
-- Table `locadora_imd`.`Oficina`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Oficina` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Montadora`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Montadora` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Filial`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Filial` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Usuario` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(60) NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(254) NOT NULL,
  `ultimo_acesso` DATETIME NULL,
  `data_cadastro` DATETIME NOT NULL,
  `ativo` TINYINT NOT NULL,
  `senha_hash` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE,
  UNIQUE INDEX `username_UNIQUE` (`username` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Funcionario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Funcionario` (
  `Usuario_id` INT NOT NULL,
  `Filial_id` INT NOT NULL,
  INDEX `fk_Funcionario_Locadora1_idx` (`Filial_id` ASC) VISIBLE,
  PRIMARY KEY (`Usuario_id`),
  CONSTRAINT `fk_Funcionario_Locadora1`
    FOREIGN KEY (`Filial_id`)
    REFERENCES `locadora_imd`.`Filial` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Funcionario_Usuario1`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `locadora_imd`.`Usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Gerente_Comercial`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Gerente_Comercial` (
  `Funcionario_id` INT NOT NULL,
  PRIMARY KEY (`Funcionario_id`),
  CONSTRAINT `fk_Gerente_Comercial_Funcionario`
    FOREIGN KEY (`Funcionario_id`)
    REFERENCES `locadora_imd`.`Funcionario` (`Usuario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Lote`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Lote` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Gerente_Comercial_Funcionario_id` INT NOT NULL,
  `Montadora_id` INT NOT NULL,
  `preco_total` DECIMAL(10,2) NOT NULL,
  `quantidade_veiculos` INT NOT NULL COMMENT 'Quantidade comprada no lote, não total atual derivado da tabela Veiculo.',
  PRIMARY KEY (`id`),
  INDEX `fk_Lote_Montadora1_idx` (`Montadora_id` ASC) VISIBLE,
  INDEX `fk_Lote_Gerente_Comercial1_idx` (`Gerente_Comercial_Funcionario_id` ASC) VISIBLE,
  CONSTRAINT `fk_Lote_Montadora1`
    FOREIGN KEY (`Montadora_id`)
    REFERENCES `locadora_imd`.`Montadora` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Lote_Gerente_Comercial1`
    FOREIGN KEY (`Gerente_Comercial_Funcionario_id`)
    REFERENCES `locadora_imd`.`Gerente_Comercial` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Administrador`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Administrador` (
  `Funcionario_id` INT NOT NULL,
  PRIMARY KEY (`Funcionario_id`),
  CONSTRAINT `fk_Administrador_Funcionario`
    FOREIGN KEY (`Funcionario_id`)
    REFERENCES `locadora_imd`.`Funcionario` (`Usuario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Veiculo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Veiculo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `status` VARCHAR(30) NOT NULL,
  `finalidade` VARCHAR(30) NOT NULL,
  `placa` CHAR(7) NOT NULL,
  `grupo` CHAR(1) NOT NULL,
  `quilometragem` INT NOT NULL,
  `Administrador_cadastro_id` INT NOT NULL,
  `Administrador_responsavel_id` INT NULL,
  `Filial_id` INT NOT NULL,
  `Lote_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Veiculo_Lote1_idx` (`Lote_id` ASC) VISIBLE,
  INDEX `fk_Veiculo_Locadora1_idx` (`Filial_id` ASC) VISIBLE,
  INDEX `fk_Veiculo_Administrador_cadastro_idx` (`Administrador_cadastro_id` ASC) VISIBLE,
  INDEX `fk_Veiculo_Administrador_responsavel_idx` (`Administrador_responsavel_id` ASC) VISIBLE,
  UNIQUE INDEX `placa_UNIQUE` (`placa` ASC) VISIBLE,
  CONSTRAINT `fk_Veiculo_Lote1`
    FOREIGN KEY (`Lote_id`)
    REFERENCES `locadora_imd`.`Lote` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Veiculo_Locadora1`
    FOREIGN KEY (`Filial_id`)
    REFERENCES `locadora_imd`.`Filial` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Veiculo_Administrador_cadastro`
    FOREIGN KEY (`Administrador_cadastro_id`)
    REFERENCES `locadora_imd`.`Administrador` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Veiculo_Administrador_responsavel`
    FOREIGN KEY (`Administrador_responsavel_id`)
    REFERENCES `locadora_imd`.`Administrador` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Servico`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Servico` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `status` VARCHAR(30) NOT NULL,
  `data_inicio` DATETIME NOT NULL,
  `data_fim` DATETIME NULL,
  `custo` DECIMAL(10,2) NULL,
  `tipo` VARCHAR(30) NOT NULL,
  `Veiculo_id` INT NOT NULL,
  `Administrador_Funcionario_id` INT NOT NULL,
  `Oficina_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Servico_Oficina_idx` (`Oficina_id` ASC) VISIBLE,
  INDEX `fk_Servico_Veiculo_idx` (`Veiculo_id` ASC) VISIBLE,
  INDEX `fk_Servico_Administrador1_idx` (`Administrador_Funcionario_id` ASC) VISIBLE,
  CONSTRAINT `fk_Servico_Oficina`
    FOREIGN KEY (`Oficina_id`)
    REFERENCES `locadora_imd`.`Oficina` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Servico_Veiculo`
    FOREIGN KEY (`Veiculo_id`)
    REFERENCES `locadora_imd`.`Veiculo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Servico_Administrador1`
    FOREIGN KEY (`Administrador_Funcionario_id`)
    REFERENCES `locadora_imd`.`Administrador` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Empresa`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Empresa` (
  `Cliente_id` INT NOT NULL,
  `CNPJ` CHAR(14) NOT NULL,
  PRIMARY KEY (`Cliente_id`),
  UNIQUE INDEX `CNPJ_UNIQUE` (`CNPJ` ASC) VISIBLE,
  CONSTRAINT `fk_Empresa_Cliente1`
    FOREIGN KEY (`Cliente_id`)
    REFERENCES `locadora_imd`.`Usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Contrato_Frota`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Contrato_Frota` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Gerente_Comercial_id` INT NOT NULL,
  `Empresa_id` INT NOT NULL,
  `data_inicio` DATETIME NOT NULL,
  `data_final` DATETIME NOT NULL,
  `quantidade_veiculos` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Contrato_Frota_Empresa_idx` (`Empresa_id` ASC) VISIBLE,
  INDEX `fk_Contrato_Frota_Gerente_Comercial1_idx` (`Gerente_Comercial_id` ASC) VISIBLE,
  CONSTRAINT `fk_Contrato_Frota_Empresa`
    FOREIGN KEY (`Empresa_id`)
    REFERENCES `locadora_imd`.`Empresa` (`Cliente_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contrato_Frota_Gerente_Comercial1`
    FOREIGN KEY (`Gerente_Comercial_id`)
    REFERENCES `locadora_imd`.`Gerente_Comercial` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Pessoa_Fisica`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Pessoa_Fisica` (
  `Cliente_id` INT NOT NULL,
  `CPF` CHAR(11) NOT NULL,
  `CNH` CHAR(11) NOT NULL,
  `estado` CHAR(2) NOT NULL,
  `categoria` CHAR(3) NOT NULL,
  `data_emissao` DATE NOT NULL,
  `data_validade` DATE NOT NULL,
  PRIMARY KEY (`Cliente_id`),
  UNIQUE INDEX `CPF_UNIQUE` (`CPF` ASC) VISIBLE,
  UNIQUE INDEX `CNH_UNIQUE` (`CNH` ASC) VISIBLE,
  CONSTRAINT `fk_Pessoa_Fisica_Usuario`
    FOREIGN KEY (`Cliente_id`)
    REFERENCES `locadora_imd`.`Usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Atendente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Atendente` (
  `Funcionario_id` INT NOT NULL,
  PRIMARY KEY (`Funcionario_id`),
  CONSTRAINT `fk_Atendente_Funcionario`
    FOREIGN KEY (`Funcionario_id`)
    REFERENCES `locadora_imd`.`Funcionario` (`Usuario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Aluguel`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Aluguel` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Atendente_entrega_id` INT NOT NULL,
  `Atendente_devolucao_id` INT NULL,
  `Pessoa_Fisica_id` INT NULL,
  `status` VARCHAR(30) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `tipo` VARCHAR(30) NOT NULL,
  `data_inicial` DATETIME NOT NULL,
  `data_final` DATETIME NULL,
  `data_final_prevista` DATETIME NULL,
  `Contrato_frota_id` INT NULL,
  `Veiculo_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Aluguel_Veiculo1_idx` (`Veiculo_id` ASC) VISIBLE,
  INDEX `fk_Aluguel_Contrato_frota_idx` (`Contrato_frota_id` ASC) VISIBLE,
  INDEX `fk_Aluguel_Pessoa_Fisica_idx` (`Pessoa_Fisica_id` ASC) VISIBLE,
  INDEX `fk_Aluguel_Atendente1_idx` (`Atendente_entrega_id` ASC) VISIBLE,
  INDEX `fk_Aluguel_Atendente2_idx` (`Atendente_devolucao_id` ASC) VISIBLE,
  CONSTRAINT `fk_Aluguel_Veiculo1`
    FOREIGN KEY (`Veiculo_id`)
    REFERENCES `locadora_imd`.`Veiculo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Aluguel_Contrato_Frota`
    FOREIGN KEY (`Contrato_frota_id`)
    REFERENCES `locadora_imd`.`Contrato_Frota` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Aluguel_Pessoa_Fisica`
    FOREIGN KEY (`Pessoa_Fisica_id`)
    REFERENCES `locadora_imd`.`Pessoa_Fisica` (`Cliente_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Aluguel_Atendente1`
    FOREIGN KEY (`Atendente_entrega_id`)
    REFERENCES `locadora_imd`.`Atendente` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Aluguel_Atendente2`
    FOREIGN KEY (`Atendente_devolucao_id`)
    REFERENCES `locadora_imd`.`Atendente` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `chk_Aluguel_alvo_unico`
    CHECK (
      (`Pessoa_Fisica_id` IS NOT NULL AND `Contrato_frota_id` IS NULL)
      OR (`Pessoa_Fisica_id` IS NULL AND `Contrato_frota_id` IS NOT NULL)
    ))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Venda`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Venda` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `valor` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(30) NOT NULL,
  `Veiculo_id` INT NOT NULL,
  `Pessoa_Fisica_id` INT NOT NULL,
  `Gerente_Comercial_Funcionario_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Venda_Veiculo_idx` (`Veiculo_id` ASC) VISIBLE,
  INDEX `fk_Venda_Pessoa_Fisica_idx` (`Pessoa_Fisica_id` ASC) VISIBLE,
  INDEX `fk_Venda_Gerente_Comercial1_idx` (`Gerente_Comercial_Funcionario_id` ASC) VISIBLE,
  CONSTRAINT `fk_Venda_Veiculo`
    FOREIGN KEY (`Veiculo_id`)
    REFERENCES `locadora_imd`.`Veiculo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Venda_Pessoa_Fisica`
    FOREIGN KEY (`Pessoa_Fisica_id`)
    REFERENCES `locadora_imd`.`Pessoa_Fisica` (`Cliente_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Venda_Gerente_Comercial`
    FOREIGN KEY (`Gerente_Comercial_Funcionario_id`)
    REFERENCES `locadora_imd`.`Gerente_Comercial` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Telefone_oficina`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Telefone_oficina` (
  `oficina_id` INT NOT NULL,
  `telefone` CHAR(11) NOT NULL,
  PRIMARY KEY (`oficina_id`, `telefone`),
  CONSTRAINT `fk_Telefone_oficina_Oficina1`
    FOREIGN KEY (`oficina_id`)
    REFERENCES `locadora_imd`.`Oficina` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Telefone_montadora`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Telefone_montadora` (
  `montadora_id` INT NOT NULL,
  `telefone` CHAR(13) NOT NULL,
  PRIMARY KEY (`montadora_id`, `telefone`),
  CONSTRAINT `fk_Telefone_montadora_Montadora1`
    FOREIGN KEY (`montadora_id`)
    REFERENCES `locadora_imd`.`Montadora` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Usuario_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Usuario_endereco` (
  `Usuario_id` INT NOT NULL,
  `estado` CHAR(2) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `bairro` VARCHAR(100) NOT NULL,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NULL,
  `logradouro` VARCHAR(150) NULL,
  `CEP` CHAR(8) NOT NULL,
  `referencia` VARCHAR(100) NULL,
  PRIMARY KEY (`Usuario_id`),
  CONSTRAINT `fk_Usuario_endereco_Usuario`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `locadora_imd`.`Usuario` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Usuario_telefone`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Usuario_telefone` (
  `Usuario_id` INT NOT NULL,
  `telefone` CHAR(11) NOT NULL,
  PRIMARY KEY (`Usuario_id`, `telefone`),
  CONSTRAINT `fk_Usuario_telefone_Usuario`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `locadora_imd`.`Usuario` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Oficina_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Oficina_endereco` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `estado` CHAR(2) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `bairro` VARCHAR(100) NULL,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NULL,
  `logradouro` VARCHAR(150) NULL,
  `CEP` CHAR(8) NOT NULL,
  `referencia` VARCHAR(100) NULL,
  `Oficina_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Oficina_endereco_Oficina1`
    FOREIGN KEY (`Oficina_id`)
    REFERENCES `locadora_imd`.`Oficina` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Montadora_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Montadora_endereco` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Montadora_id` INT NOT NULL,
  `estado` CHAR(2) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `bairro` VARCHAR(100) NOT NULL,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NULL,
  `logradouro` VARCHAR(150) NULL,
  `CEP` CHAR(8) NOT NULL,
  `referencia` VARCHAR(100) NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Montadora_endereco_Montadora1`
    FOREIGN KEY (`Montadora_id`)
    REFERENCES `locadora_imd`.`Montadora` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Filial_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Filial_endereco` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `estado` CHAR(2) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `bairro` VARCHAR(100) NOT NULL,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NULL,
  `logradouro` VARCHAR(150) NULL,
  `CEP` CHAR(8) NOT NULL,
  `referencia` VARCHAR(100) NULL,
  `Filial_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_Filial_endereco_Filial1`
    FOREIGN KEY (`Filial_id`)
    REFERENCES `locadora_imd`.`Filial` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `locadora_imd`.`Filial_telefone`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Filial_telefone` (
  `Filial_id` INT NOT NULL,
  `telefone` CHAR(11) NOT NULL,
  PRIMARY KEY (`Filial_id`, `telefone`),
  CONSTRAINT `fk_Filial_telefone_Filial1`
    FOREIGN KEY (`Filial_id`)
    REFERENCES `locadora_imd`.`Filial` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
