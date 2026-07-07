-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`Oficina`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Oficina` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Montadora`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Montadora` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Filial`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Filial` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Usuario` (
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
-- Table `mydb`.`Funcionario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Funcionario` (
  `Usuario_id` INT NOT NULL,
  `nome` VARCHAR(30) NOT NULL,
  `sobrenome` VARCHAR(30) NOT NULL,
  `telefone` CHAR(11) NOT NULL,
  `Filial_id` INT NOT NULL,
  INDEX `fk_Funcionario_Locadora1_idx` (`Filial_id` ASC) VISIBLE,
  PRIMARY KEY (`Usuario_id`),
  CONSTRAINT `fk_Funcionario_Locadora1`
    FOREIGN KEY (`Filial_id`)
    REFERENCES `mydb`.`Filial` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Funcionario_Usuario1`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `mydb`.`Usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Gerente_Comercial`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Gerente_Comercial` (
  `Funcionario_id` INT NOT NULL,
  PRIMARY KEY (`Funcionario_id`),
  CONSTRAINT `fk_Gerente_Comercial_Funcionario`
    FOREIGN KEY (`Funcionario_id`)
    REFERENCES `mydb`.`Funcionario` (`Usuario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Lote`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Lote` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Gerente_Comercial_Funcionario_id` INT NOT NULL,
  `Montadora_id` INT NOT NULL,
  `preco_total` DECIMAL(10,2) NOT NULL,
  `quantidade_veiculos` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Lote_Montadora1_idx` (`Montadora_id` ASC) VISIBLE,
  INDEX `fk_Lote_Gerente_Comercial1_idx` (`Gerente_Comercial_Funcionario_id` ASC) VISIBLE,
  CONSTRAINT `fk_Lote_Montadora1`
    FOREIGN KEY (`Montadora_id`)
    REFERENCES `mydb`.`Montadora` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Lote_Gerente_Comercial1`
    FOREIGN KEY (`Gerente_Comercial_Funcionario_id`)
    REFERENCES `mydb`.`Gerente_Comercial` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Administrador`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Administrador` (
  `Funcionario_id` INT NOT NULL,
  PRIMARY KEY (`Funcionario_id`),
  CONSTRAINT `fk_Administrador_Funcionario`
    FOREIGN KEY (`Funcionario_id`)
    REFERENCES `mydb`.`Funcionario` (`Usuario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Veiculo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Veiculo` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `status` VARCHAR(30) NOT NULL,
  `finalidade` VARCHAR(30) NOT NULL,
  `placa` CHAR(7) NOT NULL,
  `grupo` CHAR(1) NOT NULL,
  `quilometragem` INT NOT NULL,
  `Administrador_Funcionario_id` INT NOT NULL,
  `Filial_id` INT NOT NULL,
  `Lote_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Veiculo_Lote1_idx` (`Lote_id` ASC) VISIBLE,
  INDEX `fk_Veiculo_Locadora1_idx` (`Filial_id` ASC) VISIBLE,
  INDEX `fk_Veiculo_Administrador1_idx` (`Administrador_Funcionario_id` ASC) VISIBLE,
  UNIQUE INDEX `placa_UNIQUE` (`placa` ASC) VISIBLE,
  CONSTRAINT `fk_Veiculo_Lote1`
    FOREIGN KEY (`Lote_id`)
    REFERENCES `mydb`.`Lote` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Veiculo_Locadora1`
    FOREIGN KEY (`Filial_id`)
    REFERENCES `mydb`.`Filial` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Veiculo_Administrador1`
    FOREIGN KEY (`Administrador_Funcionario_id`)
    REFERENCES `mydb`.`Administrador` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Servico`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Servico` (
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
    REFERENCES `mydb`.`Oficina` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Servico_Veiculo`
    FOREIGN KEY (`Veiculo_id`)
    REFERENCES `mydb`.`Veiculo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Servico_Administrador1`
    FOREIGN KEY (`Administrador_Funcionario_id`)
    REFERENCES `mydb`.`Administrador` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Empresa`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Empresa` (
  `Usuario_id` INT NOT NULL,
  `CNPJ` CHAR(14) NOT NULL,
  PRIMARY KEY (`Usuario_id`),
  UNIQUE INDEX `CNPJ_UNIQUE` (`CNPJ` ASC) VISIBLE,
  CONSTRAINT `fk_Empresa_Cliente1`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `mydb`.`Usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Contrato_Frota`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Contrato_Frota` (
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
    REFERENCES `mydb`.`Empresa` (`Usuario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contrato_Frota_Gerente_Comercial1`
    FOREIGN KEY (`Gerente_Comercial_id`)
    REFERENCES `mydb`.`Gerente_Comercial` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`CNH`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`CNH` (
  `numero` INT NOT NULL,
  `estado` CHAR(2) NOT NULL,
  `categoria` CHAR(3) NOT NULL,
  `data_emissao` DATE NOT NULL,
  `data_validade` DATE NOT NULL,
  PRIMARY KEY (`numero`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Pessoa_Fisica`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Pessoa_Fisica` (
  `Cliente_id` INT NOT NULL,
  `CPF` CHAR(11) NOT NULL,
  `CNH_numero` INT NOT NULL,
  PRIMARY KEY (`Cliente_id`),
  UNIQUE INDEX `CPF_UNIQUE` (`CPF` ASC) VISIBLE,
  INDEX `fk_Pessoa_Fisica_CNH1_idx` (`CNH_numero` ASC) VISIBLE,
  CONSTRAINT `fk_Pessoa_Fisica_Usuario`
    FOREIGN KEY (`Cliente_id`)
    REFERENCES `mydb`.`Usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Pessoa_Fisica_CNH1`
    FOREIGN KEY (`CNH_numero`)
    REFERENCES `mydb`.`CNH` (`numero`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Atendente`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Atendente` (
  `Funcionario_id` INT NOT NULL,
  PRIMARY KEY (`Funcionario_id`),
  CONSTRAINT `fk_Atendente_Funcionario`
    FOREIGN KEY (`Funcionario_id`)
    REFERENCES `mydb`.`Funcionario` (`Usuario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Aluguel`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Aluguel` (
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
    REFERENCES `mydb`.`Veiculo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Aluguel_Contrato_Frota`
    FOREIGN KEY (`Contrato_frota_id`)
    REFERENCES `mydb`.`Contrato_Frota` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Aluguel_Pessoa_Fisica`
    FOREIGN KEY (`Pessoa_Fisica_id`)
    REFERENCES `mydb`.`Pessoa_Fisica` (`Cliente_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Aluguel_Atendente1`
    FOREIGN KEY (`Atendente_entrega_id`)
    REFERENCES `mydb`.`Atendente` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Aluguel_Atendente2`
    FOREIGN KEY (`Atendente_devolucao_id`)
    REFERENCES `mydb`.`Atendente` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Venda`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Venda` (
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
    REFERENCES `mydb`.`Veiculo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Venda_Pessoa_Fisica`
    FOREIGN KEY (`Pessoa_Fisica_id`)
    REFERENCES `mydb`.`Pessoa_Fisica` (`Cliente_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Venda_Gerente_Comercial`
    FOREIGN KEY (`Gerente_Comercial_Funcionario_id`)
    REFERENCES `mydb`.`Gerente_Comercial` (`Funcionario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Telefone_oficina`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Telefone_oficina` (
  `oficina_id` INT NOT NULL,
  `telefone` CHAR(11) NOT NULL,
  PRIMARY KEY (`oficina_id`, `telefone`),
  CONSTRAINT `fk_Telefone_oficina_Oficina1`
    FOREIGN KEY (`oficina_id`)
    REFERENCES `mydb`.`Oficina` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Telefone_montadora`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Telefone_montadora` (
  `montadora_id` INT NOT NULL,
  `telefone` CHAR(13) NOT NULL,
  PRIMARY KEY (`montadora_id`, `telefone`),
  CONSTRAINT `fk_Telefone_montadora_Montadora1`
    FOREIGN KEY (`montadora_id`)
    REFERENCES `mydb`.`Montadora` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Usuario_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Usuario_endereco` (
  `Usuario_id` INT NOT NULL,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NOT NULL,
  `referencia` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`Usuario_id`),
  CONSTRAINT `fk_Usuario_endereco_Usuario`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `mydb`.`Usuario` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Usuario_telefone`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Usuario_telefone` (
  `Usuario_id` INT NOT NULL,
  `telefone` CHAR(11) NOT NULL,
  PRIMARY KEY (`Usuario_id`, `telefone`),
  CONSTRAINT `fk_Usuario_telefone_Usuario`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `mydb`.`Usuario` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`CEP`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`CEP` (
  `CEP` CHAR(8) NOT NULL,
  `estado` CHAR(2) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `bairro` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`CEP`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Oficina_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Oficina_endereco` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NOT NULL,
  `referencia` VARCHAR(100) NOT NULL,
  `Oficina_id` INT NOT NULL,
  `logradouro` VARCHAR(150) NULL,
  `CEP` CHAR(8) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Oficina_endereco_Cep1_idx` (`CEP` ASC) VISIBLE,
  CONSTRAINT `fk_Oficina_endereco_Oficina1`
    FOREIGN KEY (`Oficina_id`)
    REFERENCES `mydb`.`Oficina` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Oficina_endereco_Cep1`
    FOREIGN KEY (`CEP`)
    REFERENCES `mydb`.`CEP` (`CEP`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Montadora_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Montadora_endereco` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Montadora_id` INT NOT NULL,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NOT NULL,
  `referencia` VARCHAR(100) NOT NULL,
  `logradouro` VARCHAR(150) NULL,
  `CEP` CHAR(8) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Montadora_endereco_Cep1_idx` (`CEP` ASC) VISIBLE,
  CONSTRAINT `fk_Montadora_endereco_Montadora1`
    FOREIGN KEY (`Montadora_id`)
    REFERENCES `mydb`.`Montadora` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Montadora_endereco_Cep1`
    FOREIGN KEY (`CEP`)
    REFERENCES `mydb`.`CEP` (`CEP`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Filial_endereco`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Filial_endereco` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(20) NULL,
  `complemento` VARCHAR(100) NOT NULL,
  `logradouro` VARCHAR(150) NULL,
  `referencia` VARCHAR(100) NOT NULL,
  `Filial_id` INT NOT NULL,
  `CEP` CHAR(8) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_Filial_endereco_Cep1_idx` (`CEP` ASC) VISIBLE,
  CONSTRAINT `fk_Filial_endereco_Filial1`
    FOREIGN KEY (`Filial_id`)
    REFERENCES `mydb`.`Filial` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_Filial_endereco_Cep1`
    FOREIGN KEY (`CEP`)
    REFERENCES `mydb`.`CEP` (`CEP`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`Filial_telefone`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`Filial_telefone` (
  `Filial_id` INT NOT NULL,
  `telefone` CHAR(11) NOT NULL,
  PRIMARY KEY (`Filial_id`, `telefone`),
  CONSTRAINT `fk_Filial_telefone_Filial1`
    FOREIGN KEY (`Filial_id`)
    REFERENCES `mydb`.`Filial` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`CEP_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`CEP_user` (
  `CEP` CHAR(8) NOT NULL,
  `estado` CHAR(2) NOT NULL,
  `cidade` VARCHAR(100) NOT NULL,
  `bairro` VARCHAR(100) NULL,
  `logradouro` VARCHAR(150) NULL,
  `Usuario_endereco_Usuario_id` INT NOT NULL,
  PRIMARY KEY (`CEP`),
  INDEX `fk_CEP_user_Usuario_endereco1_idx` (`Usuario_endereco_Usuario_id` ASC) VISIBLE,
  CONSTRAINT `fk_CEP_user_Usuario_endereco1`
    FOREIGN KEY (`Usuario_endereco_Usuario_id`)
    REFERENCES `mydb`.`Usuario_endereco` (`Usuario_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
