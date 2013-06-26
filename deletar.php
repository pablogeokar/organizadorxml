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
if ((!defined("DS")) || (constant("DS") != DIRECTORY_SEPARATOR))
	define("DS", DIRECTORY_SEPARATOR);
require_once(dirname(__FILE__) . DS . "config.php");
try {
	$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco", "$bdUsuario", "$bdSenha");
} catch (PDOException $e) {
	die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

$tipoArquivo = strtolower(filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_STRING));
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if ((!isset($modoOperacao)) || (is_null($modoOperacao)) || (!is_numeric($modoOperacao)) || ($modoOperacao < 1) || ($modoOperacao > 3) || ($modoOperacao == 1)) {
	die("Modo de operacao invalido");
}

switch ($tipoArquivo) {
	case 'naoidentificado':
		if ((isset($id)) || (!is_null($id))) {
			if ($modoOperacao == 2) {
				$busca = $objetoPDO->query("SELECT caminho_relativo_arquivo FROM nao_identificado WHERE id = $id");
				$caminhoRelativo = $busca->fetch(PDO::FETCH_OBJ)->caminho_relativo_arquivo;
				$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
				if (!unlink($diretorioDestinoXML . DS . $caminhoRelativo)) {
					die("<script type='text/javascript'>alert('Nao foi possivel deletar o arquivo'); history.go(-1)</script>");
				}
			}
			if ($modoOperacao == 2 || $modoOperacao == 3) {
				$queryDeletar = "DELETE FROM nao_identificado WHERE id = $id";
				$r = $objetoPDO->exec($queryDeletar);
				die("<script type='text/javascript'>alert('$r registros deletados'); history.go(-1)</script>");
			}
		}
		else
			die("<script type='text/javascript'>alert('Arquivo nao especificado'); history.go(-1)</script>");
		break;

	case 'invalido':
		if ((isset($id)) || (!is_null($id))) {
			$queryDeletar = "DELETE FROM xml_invalido WHERE id = $id";
			$r = $objetoPDO->exec($queryDeletar);
			die("<script type='text/javascript'>alert('$r registros processados'); history.go(-1)</script>");
		}
		else
			die("<script type='text/javascript'>alert('Arquivo nao especificado'); history.go(-1)</script>");
		break;

	default:
		die("<script type='text/javascript'>alert('Tipo de arquivo desconhecido'); history.go(-1)</script>");
		break;
}