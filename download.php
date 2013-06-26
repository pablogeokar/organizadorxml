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
if ((!defined("DS")) || (constant("DS") != DIRECTORY_SEPARATOR)) define("DS", DIRECTORY_SEPARATOR);
require_once(dirname(__FILE__) . DS . "config.php");
require_once(dirname(__FILE__) . DS . "util.php");
header('Content-type: text/html; charset=UTF-8');
set_time_limit(0);
ignore_user_abort(true); //http://stackoverflow.com/questions/2641667/deleting-a-file-after-user-download-it
try {
	$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco", "$bdUsuario", "$bdSenha");
} catch (PDOException $e) {
	die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

$tipoArquivo = strtolower(filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_STRING));
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
// atributos comum a nfe, cte e cce
$dataInicialLancamento = date('Y-m-d', strtotime($_GET['dataInicialLancamento']));
$dataFinalLancamento = date('Y-m-d', strtotime($_GET['dataFinalLancamento']));
$cnpjCpfEmitente = filter_input(INPUT_GET, 'cnpjCpfEmitente', FILTER_SANITIZE_NUMBER_INT);
$cnpjCpfDestinatario = filter_input(INPUT_GET, 'cnpjCpfDestinatario', FILTER_SANITIZE_NUMBER_INT);
// nfe
$numeroNfe = filter_input(INPUT_GET, 'numeroFfe', FILTER_SANITIZE_NUMBER_INT);
$chaveNfe = filter_input(INPUT_GET, 'chaveFfe', FILTER_SANITIZE_NUMBER_INT); // nfe e cce
// cte
$numeroCte = filter_input(INPUT_GET, 'numeroCte', FILTER_SANITIZE_NUMBER_INT);
$chaveCte = filter_input(INPUT_GET, 'chaveCte', FILTER_SANITIZE_NUMBER_INT);
// nao identificado
$dataInicialImportacao = date('Y-m-d', strtotime($_GET['data_inicial_importacao']));
$dataFinalImportacao = date('Y-m-d', strtotime($_GET['data_final_importacao']));
$nomeArquivo = filter_input(INPUT_GET, 'nome_arquivo', FILTER_SANITIZE_STRING);
//invalido
$dataInicialRegistro = date('Y-m-d', strtotime($_GET['data_inicial_registro']));
$dataFinalRegistro = date('Y-m-d', strtotime($_GET['data_inicial_registro']));
$tipoArquivoInvalido = filter_input(INPUT_GET, 'tipo_arquivo', FILTER_SANITIZE_STRING);
// variavel global
$diretorioTemp = $diretorioTemp . DS . "organizaXML-download-" . date('dmYGis');
//checagens
if (!file_exists($diretorioTemp)) {
	if (!mkdir($diretorioTemp, 0777)) {
		die("erro '$diretorioTemp' nao existe e nao foi possivel cria-lo");
	}
}
if ((isset($dataInicialLancamento)) && ($dataInicialLancamento == '1969-12-31')) $dataInicialLancamento = null;
if ((isset($dataFinalLancamento)) && ($dataFinalLancamento == '1969-12-31')) $dataFinalLancamento = null;
if ((isset($dataInicialImportacao)) && ($dataInicialImportacao == '1969-12-31')) $dataInicialImportacao = null;
if ((isset($dataFinalImportacao)) && ($dataFinalImportacao == '1969-12-31')) $dataFinalImportacao = null;
if ((!isset($modoOperacao)) || (is_null($modoOperacao)) || (!is_numeric($modoOperacao)) || ($modoOperacao < 1) || ($modoOperacao > 3) || ($modoOperacao == 1)) {
	die("Modo de operacao invalido");
}

function enviarParaDownload($arquivo) {
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=' . basename($arquivo));
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($arquivo));
	ob_clean();
	flush();
	readfile($arquivo);
}

function gerarZip($arquivos) {
	global $diretorioTemp;
	$objetoZip = new ZipArchive();
	$arquivoZip = $diretorioTemp . DS . "xmls.zip";
	if ($objetoZip->open($arquivoZip, ZIPARCHIVE::CREATE) === false) return false;
	foreach ($arquivos as $arquivo) {
		$nomeArquivo = $diretorioTemp . DS . basename($arquivo);
		file_put_contents($nomeArquivo, file_get_contents($arquivo));
		$objetoZip->addFile($nomeArquivo, basename($arquivo));
	}
	$objetoZip->close();
	return $arquivoZip;
}

switch ($tipoArquivo) {
	case 'nfe':
		if (!empty($id)) {
			if ($modoOperacao == 2) {
				$busca = $objetoPDO->query("SELECT caminho_relativo_arquivo FROM nota_fiscal WHERE id = '$id'");
				if ($busca->rowCount() < 1)
					die("<script type='text/javascript'>alert('Arquivo nao econtrado');history.go(-1)</script>");
				$caminhoRelativo = $busca->fetch(PDO::FETCH_OBJ)->caminho_relativo_arquivo;
				$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
				$local = $diretorioDestinoXML . DS . $caminhoRelativo;
			}
			elseif ($modoOperacao == 3) {
				$busca = $objetoPDO->query("SELECT chave_nota,xml FROM nota_fiscal WHERE id = '$id'");
				if ($busca->rowCount() < 1)
					die("<script type='text/javascript'>alert('Arquivo nao econtrado');history.go(-1)</script>");
				$busca = $busca->fetch(PDO::FETCH_OBJ);
				$chave = $busca->chave_nota;
				$xml = $busca->xml;
				file_put_contents($diretorioTemp . DS . $chave . ".xml", $xml);
				$local = $diretorioTemp . DS . $chave . ".xml";
			}
			enviarParaDownload($local);
		}
		else {
			$campos = array();
			if (isset($dataInicialLancamento) && !empty($dataInicialLancamento)) $campos += array('data_emissao_nota >=' => $dataInicialLancamento);
			if (isset($dataFinalLancamento) && !empty($dataFinalLancamento))	$campos += array('data_emissao_nota <=' => $dataFinalLancamento);
			if (isset($cnpjCpfEmitente) && !empty($cnpjCpfEmitente)) $campos += array('cnpj_cpf_emitente =' => $cnpjCpfEmitente);
			if (isset($cnpjCpfDestinatario) && !empty($cnpjCpfDestinatario)) $campos += array('cnpj_cpf_destinatario =' => $cnpjCpfDestinatario);
			if (isset($numeroNfe) && !empty($numeroNfe)) $campos += array('numero_nota =' => $numeroNfe);
			if (isset($chaveNfe) && !empty($chaveNfe)) $campos += array('chave_nota =' => $chaveNfe);
			if ($modoOperacao == 2) {
				$queryBuscar = "SELECT caminho_relativo_arquivo FROM nota_fiscal WHERE 1";
				if (empty($campos)) $queryBuscar = $queryBuscar . "=2";
				foreach ($campos as $campo => $valor) {
					$nomeCampo = explode(' ', $campo);
					$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
				}
				$resultados = $objetoPDO->query($queryBuscar);
				if (!$resultados)
					die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
				$arquivos = array();
				foreach ($resultados as $resultado) {
					$caminhoRelativo = $resultado['caminho_relativo_arquivo'];
					$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
					$arquivos[] = $diretorioDestinoXML . DS . $caminhoRelativo;
				}
			} elseif ($modoOperacao == 3) {
				$queryBuscar = "SELECT chave_nota,xml FROM nota_fiscal WHERE 1";
				if (empty($campos))
					$queryBuscar = $queryBuscar . "=2";
				foreach ($campos as $campo => $valor) {
					$nomeCampo = explode(' ', $campo);
					$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
				}
				$resultados = $objetoPDO->query($queryBuscar);
				if (!$resultados)
					die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
				$arquivos = array();
				foreach ($resultados as $resultado) {
					$chave = $resultado['chave_nota'];
					$xml = $resultado['xml'];
					file_put_contents($diretorioTemp . DS . $chave . ".xml", $xml);
					$arquivos[] = $diretorioTemp . DS . $chave . ".xml";
				}
			}
			if (empty($arquivos))
				die("<script type='text/javascript'>alert('Nao ha arquivos para compactar');history.go(-1)</script>");
			if (($arquivoFinal = gerarZip($arquivos)) === false)
				die("<script type='text/javascript'>alert('Erro ao compactar arquivos');history.go(-1)</script>");
			enviarParaDownload($arquivoFinal);
		}
		break;

	case 'cte':
		if (!empty($id)) {
			if ($modoOperacao == 2) {
				$busca = $objetoPDO->query("SELECT caminho_relativo_arquivo FROM conhecimento_transporte WHERE id = '$id'");
				if ($busca->rowCount() < 1)
					die("<script type='text/javascript'>alert('Arquivo nao econtrado');history.go(-1)</script>");
				$caminhoRelativo = $busca->fetch(PDO::FETCH_OBJ)->caminho_relativo_arquivo;
				$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
				$local = $diretorioDestinoXML . DS . $caminhoRelativo;
			}
			elseif ($modoOperacao == 3) {
				$busca = $objetoPDO->query("SELECT chave_conhecimento, xml FROM conhecimento_transporte WHERE id = '$id'");
				if ($busca->rowCount() < 1)
					die("<script type='text/javascript'>alert('Arquivo nao econtrado');history.go(-1)</script>");
				$busca = $busca->fetch(PDO::FETCH_OBJ);
				$chave = $busca->chave_conhecimento;
				$xml = $busca->xml;
				file_put_contents($diretorioTemp . DS . $chave . ".xml", $xml);
				$local = $diretorioTemp . DS . $chave . ".xml";
			}
			enviarParaDownload($local);
		}
		else {
			$campos = array();
			if (isset($dataInicialLancamento) && !empty($dataInicialLancamento)) $campos += array('data_emissao_conhecimento >=' => $dataInicialLancamento);
			if (isset($dataFinalLancamento) && !empty($dataFinalLancamento)) $campos += array('data_emissao_conhecimento <=' => $dataFinalLancamento);
			if (isset($cnpjCpfEmitente) && !empty($cnpjCpfEmitente)) $campos += array('cnpj_cpf_emitente =' => $cnpjCpfEmitente);
			if (isset($cnpjCpfDestinatario) && !empty($cnpjCpfDestinatario)) $campos += array('cnpj_cpf_destinatario =' => $cnpjCpfDestinatario);
			if (isset($numeroCte) && !empty($numeroCte)) $campos += array('numero_conhecimento =' => $numeroCte);
			if (isset($chaveCte) && !empty($chaveCte)) $campos += array('chave_conhecimento =' => $chaveCte);
			if ($modoOperacao == 2) {
				$queryBuscar = "SELECT caminho_relativo_arquivo FROM conhecimento_transporte WHERE 1";
				if (empty($campos))
					$queryBuscar = $queryBuscar . "=2";
				foreach ($campos as $campo => $valor) {
					$nomeCampo = explode(' ', $campo);
					$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
				}
				$resultados = $objetoPDO->query($queryBuscar);
				if (!$resultados)
					die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
				$arquivos = array();
				foreach ($resultados as $resultado) {
					$caminhoRelativo = $resultado['caminho_relativo_arquivo'];
					$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
					$arquivos[] = $diretorioDestinoXML . DS . $caminhoRelativo;
				}
			} elseif ($modoOperacao == 3) {
				$queryBuscar = "SELECT chave_conhecimento,xml FROM conhecimento_transporte WHERE 1";
				if (empty($campos)) $queryBuscar = $queryBuscar . "=2";
				foreach ($campos as $campo => $valor) {
					$nomeCampo = explode(' ', $campo);
					$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
				}
				$resultados = $objetoPDO->query($queryBuscar);
				if (!$resultados)
					die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
				$arquivos = array();
				foreach ($resultados as $resultado) {
					$chave = $resultado['chave_conhecimento'];
					$xml = $resultado['xml'];
					file_put_contents($diretorioTemp . DS . $chave . ".xml", $xml);
					$arquivos[] = $diretorioTemp . DS . $chave . ".xml";
				}
			}
			if (empty($arquivos))
				die("<script type='text/javascript'>alert('Nao ha arquivos para compactar');history.go(-1)</script>");
			if (($arquivoFinal = gerarZip($arquivos)) === false)
				die("<script type='text/javascript'>alert('Erro ao compactar arquivos');history.go(-1)</script>");
			enviarParaDownload($arquivoFinal);
		}
		break;

	case 'cce':
		if (!empty($id)) {
			if ($modoOperacao == 2) {
				$busca = $objetoPDO->query("SELECT caminho_relativo_arquivo FROM carta_correcao WHERE id = '$id'");
				if ($busca->rowCount() < 1)
					die("<script type='text/javascript'>alert('Arquivo nao econtrado');history.go(-1)</script>");
				$caminhoRelativo = $busca->fetch(PDO::FETCH_OBJ)->caminho_relativo_arquivo;
				$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
				$local = $diretorioDestinoXML . DS . $caminhoRelativo;
			}
			elseif ($modoOperacao == 3) {
				$busca = $objetoPDO->query("SELECT chave_nota,xml FROM carta_correcao WHERE id = '$id'");
				if ($busca->rowCount() < 1)
					die("<script type='text/javascript'>alert('Arquivo nao econtrado');history.go(-1)</script>");
				$busca = $busca->fetch(PDO::FETCH_OBJ);
				$chave = $busca->chave_nota;
				$xml = $busca->xml;
				file_put_contents($diretorioTemp . DS . $chave . ".xml", $xml);
				$local = $diretorioTemp . DS . $chave . ".xml";
			}
			enviarParaDownload($local);
		}
		else {
			$campos = array();
			if (isset($dataInicialLancamento) && !empty($dataInicialLancamento)) $campos += array('data_emissao_carta >=' => $dataInicialLancamento);
			if (isset($dataFinalLancamento) && !empty($dataFinalLancamento)) $campos += array('data_emissao_carta <=' => $dataFinalLancamento);
			if (isset($cnpjCpfEmitente) && !empty($cnpjCpfEmitente)) $campos += array('cnpj_cpf_emitente =' => $cnpjCpfEmitente);
			if (isset($cnpjCpfDestinatario) && !empty($cnpjCpfDestinatario)) $campos += array('cnpj_cpf_destinatario =' => $cnpjCpfDestinatario);
			if (isset($chaveNfe) && !empty($chaveNfe)) $campos += array('chave_nota =' => $chaveNfe);
			if ($modoOperacao == 2) { 
				$queryBuscar = "SELECT caminho_relativo_arquivo FROM carta_correcao WHERE 1";
				if (empty($campos))
					$queryBuscar = $queryBuscar . "=2";
				foreach ($campos as $campo => $valor) {
					$nomeCampo = explode(' ', $campo);
					$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
				}
				$resultados = $objetoPDO->query($queryBuscar);
				if (!$resultados)
					die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
				$arquivos = array();
				foreach ($resultados as $resultado) {
					$caminhoRelativo = $resultado['caminho_relativo_arquivo'];
					$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
					$arquivos[] = $diretorioDestinoXML . DS . $caminhoRelativo;
				}
			} elseif ($modoOperacao == 3) {
				$queryBuscar = "SELECT chave_nota,xml FROM carta_correcao WHERE 1";
				if (empty($campos))
					$queryBuscar = $queryBuscar . "=2";
				foreach ($campos as $campo => $valor) {
					$nomeCampo = explode(' ', $campo);
					$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
				}
				$resultados = $objetoPDO->query($queryBuscar);
				if (!$resultados)
					die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
				$arquivos = array();
				foreach ($resultados as $resultado) {
					$chave = $resultado['chave_nota'];
					$xml = $resultado['xml'];
					file_put_contents($diretorioTemp . DS . $chave . ".xml", $xml);
					$arquivos[] = $diretorioTemp . DS . $chave . ".xml";
				}
			}
			if (empty($arquivos))
				die("<script type='text/javascript'>alert('Nao ha arquivos para compactar');history.go(-1)</script>");
			if (($arquivoFinal = gerarZip($arquivos)) === false)
				die("<script type='text/javascript'>alert('Erro ao compactar arquivos');history.go(-1)</script>");
			enviarParaDownload($arquivoFinal);
		}
		break;

	case 'naoidentificado':
		if (!empty($id)) {
			if ($modoOperacao == 2) {
				$busca = $objetoPDO->query("SELECT caminho_relativo_arquivo FROM nao_identificado WHERE id = '$id'");
				if ($busca->rowCount() < 1)
					die("<script type='text/javascript'>alert('Arquivo nao econtrado');history.go(-1)</script>");
				$caminhoRelativo = $busca->fetch(PDO::FETCH_OBJ)->caminho_relativo_arquivo;
				$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
				$local = $diretorioDestinoXML . DS . $caminhoRelativo;
			}
			elseif ($modoOperacao == 3) {
				$busca = $objetoPDO->query("SELECT nome_arquivo,xml FROM nao_identificado WHERE id = '$id'");
				if ($busca->rowCount() < 1)
					die("<script type='text/javascript'>alert('Arquivo nao econtrado');history.go(-1)</script>");
				$busca = $busca->fetch(PDO::FETCH_OBJ);
				$nome = $busca->nome_arquivo;
				$xml = $busca->xml;
				file_put_contents($diretorioTemp . DS . $nome . ".xml", $xml);
				$local = $diretorioTemp . DS . $nome . ".xml";
			}
			enviarParaDownload($local);
		}
		else {
			$campos = array();
			if (isset($dataInicialImportacao) && !empty($dataInicialImportacao)) $campos += array('data_importacao >=' => $dataInicialImportacao);
			if (isset($dataFinalImportacao) && !empty($dataFinalImportacao)) $campos += array('data_importacao <=' => $dataFinalImportacao);
			if (isset($nomeArquivo) && !empty($nomeArquivo)) $campos += array('nome_arquivo LIKE' => "%" . $nomeArquivo . "%");
			if ($modoOperacao == 2) {
				$queryBuscar = "SELECT caminho_relativo_arquivo FROM nao_identificado WHERE 1";
				if (empty($campos)) $queryBuscar = $queryBuscar . "=2";
				foreach ($campos as $campo => $valor) {
					$nomeCampo = explode(' ', $campo);
					$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
				}
				$resultados = $objetoPDO->query($queryBuscar);
				if (!$resultados)
					die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
				$arquivos = array();
				foreach ($resultados as $resultado) {
					$caminhoRelativo = $resultado['caminho_relativo_arquivo'];
					$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
					$arquivos[] = $diretorioDestinoXML . DS . $caminhoRelativo;
				}
			} elseif ($modoOperacao == 3) {
				$queryBuscar = "SELECT nome_arquivo,xml FROM nao_identificado WHERE 1";
				if (empty($campos)) $queryBuscar = $queryBuscar . "=2";
				foreach ($campos as $campo => $valor) {
					$nomeCampo = explode(' ', $campo);
					$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
				}
				$resultados = $objetoPDO->query($queryBuscar);
				if (!$resultados)
					die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
				$arquivos = array();
				foreach ($resultados as $resultado) {
					$nome = $resultado['nome_arquivo'];
					$xml = $resultado['xml'];
					file_put_contents($diretorioTemp . DS . $nome . ".xml", $xml);
					$arquivos[] = $diretorioTemp . DS . $nome . ".xml";
				}
			}
			if (empty($arquivos))
				die("<script type='text/javascript'>alert('Nao ha arquivos para compactar');history.go(-1)</script>");
			if (($arquivoFinal = gerarZip($arquivos)) === false)
				die("<script type='text/javascript'>alert('Erro ao compactar arquivos');history.go(-1)</script>");
			enviarParaDownload($arquivoFinal);
		}
		break;

	case 'invalido':
		$campos = array();
		$tiposArquivo = array(
			'nfe' => 'nota_fiscal',
			'cte' => 'conhecimento_transporte',
			'cce' => 'carta_correcao',
		);
		if (isset($dataInicialRegistro) && !empty($dataInicialRegistro)) $campos += array('data_registro >=' => $dataInicialRegistro);
		if (isset($dataFinalRegistro) && !empty($dataFinalRegistro)) $campos += array('data_registro <=' => $dataFinalRegistro);
		if (isset($tipoArquivoInvalido) && !empty($tipoArquivoInvalido)) $campos += array('tipo_arquivo =' => $tipoArquivoInvalido);
		$queryBuscar = "SELECT * FROM xml_invalido WHERE 1";
		if (empty($campos)) $queryBuscar = $queryBuscar . "=2";
		foreach ($campos as $campo => $valor) {
			$nomeCampo = explode(' ', $campo);
			$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
		}
		$resultados = $objetoPDO->query($queryBuscar);
		if (!$resultados)
			die("<script type='text/javascript'>alert('Nenhum resultado encontrado');history.go(-1)</script>");
		$arquivos = array();
		foreach ($resultados as $resultado) {
			if ($modoOperacao == 2) {
				$queryBuscar = "SELECT caminho_relativo_arquivo FROM " . $tiposArquivo[$resultado['tipo_arquivo']] . " WHERE id=" . $resultado['id_arquivo'] . " ";
				$resultados = $objetoPDO->query($queryBuscar);
				if ($resultados) {
					foreach ($resultados as $resultado) {
						$caminhoRelativo = $resultado['caminho_relativo_arquivo'];
						$caminhoRelativo = preg_replace('/\$DS/', DS, $caminhoRelativo);
						$arquivos[] = $diretorioDestinoXML . DS . $caminhoRelativo;
					}
				}
			} elseif ($modoOperacao == 3) {
				$queryBuscar = "SELECT xml FROM " . $tiposArquivo[$resultado['tipo_arquivo']] . " WHERE id=" . $resultado['id_arquivo'] . " ";
				$resultados = $objetoPDO->query($queryBuscar);
				if ($resultados) {
					foreach ($resultados as $resultado) {
						$nome = rand() . rand() . rand();
						$xml = $resultado['xml'];
						file_put_contents($diretorioTemp . DS . $nome . ".xml", $xml);
						$arquivos[] = $diretorioTemp . DS . $nome . ".xml";
					}
				}
			}
		}
		if (empty($arquivos))
			die("<script type='text/javascript'>alert('Nao ha arquivos para compactar');history.go(-1)</script>");
		if (($arquivoFinal = gerarZip($arquivos)) === false)
			die("<script type='text/javascript'>alert('Erro ao compactar arquivos');history.go(-1)</script>");
		enviarParaDownload($arquivoFinal);
		break;

	default:
		die("<script type='text/javascript'>alert('Tipo de arquivo nao especificado');history.go(-1)</script>");
		break;
}

delTree($diretorioTemp);