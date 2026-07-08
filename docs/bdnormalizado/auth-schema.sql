-- Tabelas de autenticação para o schema locadora_imd (BCNF).
-- Aplicar depois de docs/bdnormalizado/locadora_imd-bcnf.sql:
--
--   mysql -u root -p locadora_imd < docs/bdnormalizado/auth-schema.sql
--
-- Ambas são aditivas: nenhuma tabela existente é alterada. Seguem o mesmo
-- estilo 1:1/1:N de extensão de Usuario já usado no schema (comparar com
-- Usuario_endereco/Usuario_telefone).

USE `locadora_imd`;

DROP TABLE IF EXISTS `Refresh_Token`;
DROP TABLE IF EXISTS `Usuario_google`;

-- Vínculo opcional de login via Google. 1:1 com Usuario (mesmo padrão de
-- Usuario_endereco: Usuario_id é PK e FK ao mesmo tempo).
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Usuario_google` (
  `Usuario_id` INT NOT NULL,
  `google_id` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`Usuario_id`),
  UNIQUE INDEX `google_id_UNIQUE` (`google_id` ASC),
  CONSTRAINT `fk_Usuario_google_Usuario`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `locadora_imd`.`Usuario` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- Refresh tokens de autenticação JWT. 1:N com Usuario. Guarda só o hash
-- SHA-256 do token (token_hash), nunca o valor em texto puro.
CREATE TABLE IF NOT EXISTS `locadora_imd`.`Refresh_Token` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `Usuario_id` INT NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `revoked_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `token_hash_UNIQUE` (`token_hash` ASC),
  INDEX `fk_Refresh_Token_Usuario_idx` (`Usuario_id` ASC),
  CONSTRAINT `fk_Refresh_Token_Usuario`
    FOREIGN KEY (`Usuario_id`)
    REFERENCES `locadora_imd`.`Usuario` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;
