SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `organizaxml` DEFAULT CHARACTER SET utf8 ;
USE `organizaxml` ;

-- -----------------------------------------------------
-- Table `organizaxml`.`cancelamento`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`cancelamento` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `codigo_orgao` VARCHAR(2) NOT NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NOT NULL ,
  `chave_nota` VARCHAR(45) NOT NULL ,
  `data_cancelamento` DATE NOT NULL ,
  `hora_cancelamento` TIME NOT NULL ,
  `numero_protocolo` INT(11) NOT NULL ,
  `justificativa_cancelamento` VARCHAR(250) NOT NULL ,
  `cnpj_cpf_destinatario` VARCHAR(14) NULL DEFAULT NULL ,
  `caminho_relativo_arquivo` TEXT NULL DEFAULT NULL ,
  `xml` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `organizaxml`.`carta_correcao`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`carta_correcao` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `data_emissao_carta` DATE NOT NULL ,
  `hora_emissao_carta` TIME NOT NULL ,
  `chave_nota` VARCHAR(45) NOT NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NOT NULL ,
  `cnpj_cpf_destinatario` VARCHAR(14) NOT NULL ,
  `caminho_relativo_arquivo` TEXT NULL DEFAULT NULL ,
  `xml` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `organizaxml`.`conhecimento_transporte`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`conhecimento_transporte` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `data_emissao_conhecimento` DATE NOT NULL ,
  `hora_emissao_conhecimento` TIME NOT NULL ,
  `chave_conhecimento` VARCHAR(44) NOT NULL ,
  `numero_conhecimento` VARCHAR(45) NOT NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NOT NULL ,
  `cnpj_cpf_destinatario` VARCHAR(14) NOT NULL ,
  `nome_emitente` VARCHAR(255) NULL DEFAULT NULL ,
  `nome_destinatario` VARCHAR(255) NULL DEFAULT NULL ,
  `caminho_relativo_arquivo` TEXT NULL DEFAULT NULL ,
  `xml` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `organizaxml`.`nao_identificado`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`nao_identificado` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `nome_arquivo` VARCHAR(200) NOT NULL ,
  `caminho_relativo_arquivo` TEXT NULL DEFAULT NULL ,
  `xml` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `organizaxml`.`nota_fiscal`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`nota_fiscal` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `data_emissao_nota` DATE NOT NULL ,
  `hora_emissao_nota` TIME NOT NULL ,
  `chave_nota` VARCHAR(44) NOT NULL ,
  `numero_nota` VARCHAR(9) NOT NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NOT NULL ,
  `cnpj_cpf_destinatario` VARCHAR(14) NOT NULL ,
  `nome_emitente` VARCHAR(255) NULL DEFAULT NULL ,
  `nome_destinatario` VARCHAR(255) NULL DEFAULT NULL ,
  `caminho_relativo_arquivo` TEXT NULL DEFAULT NULL ,
  `xml` TEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `organizaxml`.`nota_fiscal_destinada`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`nota_fiscal_destinada` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `data_importacao` DATE NOT NULL ,
  `hora_importacao` TIME NOT NULL ,
  `tipo_registro` INT(11) NOT NULL ,
  `nsu` VARCHAR(45) NOT NULL ,
  `chave` VARCHAR(44) NULL DEFAULT NULL ,
  `cnpj_cpf_emitente` VARCHAR(14) NULL DEFAULT NULL ,
  `nome_emitente` VARCHAR(200) NULL DEFAULT NULL ,
  `data_emissao` DATE NOT NULL ,
  `tipo_operacao` INT(11) NOT NULL ,
  `valor` FLOAT NULL DEFAULT NULL ,
  `situacao_nota` INT(11) NULL DEFAULT NULL ,
  `situacao_manifesto` INT(11) NULL DEFAULT NULL ,
  `xml_esta_no_sistema` TINYINT(1) NOT NULL DEFAULT '0' ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `organizaxml`.`registro_acesso`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`registro_acesso` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `data` DATE NOT NULL ,
  `hora` TIME NOT NULL ,
  `usuario` VARCHAR(255) NULL DEFAULT NULL ,
  `pagina` VARCHAR(100) NOT NULL ,
  `parametros` TEXT NULL DEFAULT NULL ,
  `endereco_ip_origem` VARCHAR(255) NULL DEFAULT NULL ,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL ,
  `id_sessao` CHAR(32) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `organizaxml`.`xml_invalido`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `organizaxml`.`xml_invalido` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `data_registro` DATE NOT NULL ,
  `hora_registro` TIME NOT NULL ,
  `tipo_arquivo` CHAR(3) NOT NULL ,
  `id_arquivo` INT(11) NOT NULL ,
  `mensagem` VARCHAR(500) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8;

 CREATE TABLE `organizaxml`.`manifestacoes` (
`id` INT(11) NOT NULL AUTO_INCREMENT ,
`data_hora` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`chave` CHAR( 44 ) NOT NULL ,
`tipo_evento` CHAR( 6 ) NOT NULL ,
`justificativa` TEXT NOT NULL ,
`ambiente` CHAR( 1 ) NOT NULL ,
PRIMARY KEY (`id`)
) ENGINE = MYISAM ;

USE `organizaxml` ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
