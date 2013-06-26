<?php

/*
Organizador xml
Copyright (C) 2013Tobias <tobiasette@gmail.com>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.If not, see <http://www.gnu.org/licenses/>.
 */
require(dirname(__FILE__) . DIRECTORY_SEPARATOR . "verifica_sessao.php");
error_reporting(0); //para nao prejudicar a emissao do pdf, visto que nao pode haver saida antes dele
if ((!defined("DS")) || (constant("DS") != DIRECTORY_SEPARATOR)) define("DS", DIRECTORY_SEPARATOR);
require_once(dirname(__FILE__) . DS . "config.php");
require_once(dirname(__FILE__) . DS . "util.php");
ignore_user_abort(true); //http://stackoverflow.com/questions/2641667/deleting-a-file-after-user-download-it
try {
	$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco", "$bdUsuario", "$bdSenha");
} catch (PDOException $e) {
	die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

$tipoArquivo = strtolower(filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_STRING));
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$diretorioTemp = $diretorioTemp . DS . "organizaXML-imprimir-" . date('dmYGis');
//checagens
if (!file_exists($diretorioTemp)) {
	if (!mkdir($diretorioTemp, 0777)) {
		die("erro '$diretorioTemp' nao existe e nao foi possivel cria-lo");
	}
}
if ((!isset($modoOperacao)) || (is_null($modoOperacao)) || (!is_numeric($modoOperacao)) || ($modoOperacao < 1) || ($modoOperacao > 3) || ($modoOperacao == 1)) {
	die("Modo de operacao invalido");
}

switch ($tipoArquivo) {
	case 'nfe':
		if (empty($id))
			die("<script type='text/javascript'>alert('Arquivo não especificado')</script>");
		if ($modoOperacao == 2) {
			$busca = $objetoPDO->query("SELECT caminho_relativo_arquivo FROM nota_fiscal WHERE id = '$id'");
			if ($busca->rowCount() < 1)
				die("<script type='text/javascript'>alert('Arquivo nao econtrado')</script>");
			$caminhoRelativo = $busca->fetch(PDO::FETCH_OBJ)->caminho_relativo_arquivo;
			$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
			$local = $diretorioDestinoXML . DS . $caminhoRelativo;
			$xml = file_get_contents($local);
		}
		elseif ($modoOperacao == 3) {
			$busca = $objetoPDO->query("SELECT xml FROM nota_fiscal WHERE id = '$id'");
			if ($busca->rowCount() < 1)
				die("<script type='text/javascript'>alert('Arquivo nao econtrado')</script>");
			$xml = $busca->fetch(PDO::FETCH_OBJ)->xml;
		}
		require_once(dirname(__FILE__) . DS . "libs/nfephp/libs/DanfeNFePHP.class.php");
		$danfe = new DanfeNFePHP($xml, 'P', 'A4', null, 'I', '');
		$id = $danfe->montaDANFE();
		$teste = $danfe->printDANFE($id . '.pdf', 'I');
		break;

	case 'cte':
		if (empty($id))
			die("<script type='text/javascript'>alert('Arquivo não especificado')</script>");
		$busca = $objetoPDO->query("SELECT caminho_relativo_arquivo FROM conhecimento_transporte WHERE id = '$id'");
		if ($busca->rowCount() < 1)
			die("<script type='text/javascript'>alert('Arquivo nao econtrado')</script>");
		$caminhoRelativo = $busca->fetch(PDO::FETCH_OBJ)->caminho_relativo_arquivo;
		$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
		$local = $diretorioDestinoXML . DS . $caminhoRelativo;
		$xml = file_get_contents($local);

		break;

	case 'cce':
		if (empty($id))
			die("<script type='text/javascript'>alert('Arquivo não especificado')</script>");
		if ($modoOperacao == 2) {
			$busca = $objetoPDO->query("SELECT caminho_relativo_arquivo FROM carta_correcao WHERE id = '$id'");
			if ($busca->rowCount() < 1)
				die("<script type='text/javascript'>alert('Arquivo nao econtrado')</script>");
			$caminhoRelativo = $busca->fetch(PDO::FETCH_OBJ)->caminho_relativo_arquivo;
			$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
			$local = $diretorioDestinoXML . DS . $caminhoRelativo;
		}
		elseif ($modoOperacao == 3) {
			$busca = $objetoPDO->query("SELECT chave_nota,xml FROM carta_correcao WHERE id = '$id'");
			if ($busca->rowCount() < 1)
				die("<script type='text/javascript'>alert('Arquivo nao econtrado')</script>");
			$busca = $busca->fetch(PDO::FETCH_OBJ);
			$xml = $busca->xml;
			$chave = $busca->chave_nota;
			file_put_contents($diretorioTemp . DS . $chave . ".xml", $xml);
			$local = $diretorioTemp . DS . $chave . ".xml";
		}
		require_once(dirname(__FILE__) . DS . "libs/nfephp/libs/DacceNFePHP.class.php");
		$aEnd = array('razao' => '', 'logradouro' => '', 'numero' => '', 'complemento' => '', 'bairro' => '', 'CEP' => '', 'municipio' => '', 'UF' => '', 'telefone' => '', 'email' => '');
		$cce = new DacceNFePHP($local, 'P', 'A4', null, 'I', $aEnd, '', 'Times', 1);
		$teste = $cce->printCCe('cce.pdf', 'I');
		break;

	default:
		die("<script type='text/javascript'>alert('Tipo de arquivo não especificado')</script>");
		break;
}

delTree($diretorioTemp);