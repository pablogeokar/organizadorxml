SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `organizaxml` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `organizaxml` ;

-- -----------------------------------------------------
-- Table `organizaxml`.`nota_fiscal`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`nota_fiscal` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `data_emissao_nota` DATE NOT NULL ,
  `hora_emissao_nota` TIME NOT NULL ,
  `chave_nota` VARCHAR(44) NOT NULL ,
  `numero_nota` VARCHAR(9) NOT NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NOT NULL ,
  `cnpj_cpf_destinatario` VARCHAR(14) NOT NULL ,
  `nome_emitente` VARCHAR(255) NULL ,
  `nome_destinatario` VARCHAR(255) NULL ,
  `caminho_relativo_arquivo` TEXT NULL ,
  `xml` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `organizaxml`.`conhecimento_transporte`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`conhecimento_transporte` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `data_emissao_conhecimento` DATE NOT NULL ,
  `hora_emissao_conhecimento` TIME NOT NULL ,
  `chave_conhecimento` VARCHAR(44) NOT NULL ,
  `numero_conhecimento` VARCHAR(45) NOT NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NOT NULL ,
  `cnpj_cpf_destinatario` VARCHAR(14) NOT NULL ,
  `nome_emitente` VARCHAR(255) NULL ,
  `nome_destinatario` VARCHAR(255) NULL ,
  `caminho_relativo_arquivo` TEXT NULL ,
  `xml` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `organizaxml`.`carta_correcao`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`carta_correcao` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `data_emissao_carta` DATE NOT NULL ,
  `hora_emissao_carta` TIME NOT NULL ,
  `chave_nota` VARCHAR(45) NOT NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NOT NULL ,
  `cnpj_cpf_destinatario` VARCHAR(14) NOT NULL ,
  `caminho_relativo_arquivo` TEXT NULL ,
  `xml` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `organizaxml`.`nao_identificado`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`nao_identificado` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `nome_arquivo` VARCHAR(200) NOT NULL ,
  `caminho_relativo_arquivo` TEXT NULL ,
  `xml` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `organizaxml`.`xml_invalido`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`xml_invalido` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `data_registro` DATE NOT NULL ,
  `hora_registro` TIME NOT NULL ,
  `tipo_arquivo` CHAR(3) NOT NULL ,
  `id_arquivo` INT NOT NULL ,
  `mensagem` VARCHAR(500) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `organizaxml`.`nota_fiscal_destinada`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`nota_fiscal_destinada` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `tipo_registro` INT NOT NULL ,
  `nsu` VARCHAR(45) NOT NULL ,
  `chave` VARCHAR(44) NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NULL ,
  `nome_emitente` VARCHAR(200) NULL ,
  `data_emissao` DATE NOT NULL ,
  `tipo_operacao` INT NOT NULL ,
  `valor` FLOAT NULL ,
  `situacao_nota` INT NULL ,
  `situacao_manifesto` INT NULL ,
  `xml_esta_no_sistema` TINYINT(1) NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM;

USE `organizaxml` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
