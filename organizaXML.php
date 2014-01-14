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

/**
	Organiza XMLs de notas fiscais, carta de correção e conhecimento de transporte
	07-06-2013 Tobias v0.1
	10-06-2013 Tobias v0.3
	11-06-2013 Tobias v0.4 Insere os valores em banco de dados
	19-06-2013 Tobias v0.5 Trabalha com $modoOperacao e incluido tabela nao_identificado
**/
if ( (! defined("DS")) || (constant("DS") != DIRECTORY_SEPARATOR) ) define ("DS",DIRECTORY_SEPARATOR);
require_once(dirname(__FILE__).DS.'config.php');
require_once(dirname(__FILE__).DS.'util.php');
register_shutdown_function("fimScript");
set_time_limit(0);
ini_set("memory_limit", "36M");

/*************************************
*              FUNÇÕES
**************************************/
/**
Função para tratar erros fatais
Necessario definir register_shutdown_function("fimScript");
http://stackoverflow.com/questions/277224/how-do-i-catch-a-php-fatal-error
**/
function fimScript() {
	$erro = error_get_last();
	if( $erro !== NULL) {
		$tipo   = $erro["type"];
		$arquivo = $erro["file"];
		$linha = $erro["line"];
		$mensagem  = $erro["message"];
		if ($arquivo != __FILE__) return; //apenas erros deste arquivos sao registrados
		logar("Erro do tipo '$tipo' encontrado na linha '$linha' do arquivo '$arquivo'. Informacoes: $mensagem");
	}
}
/**
Retorna true caso $arquivo seja um arquivo do tipo zip, false se contrario.
http://stackoverflow.com/questions/6977544/rar-zip-files-mime-type
**/
function isZip($arquivo) {
	if (! is_file($arquivo)) return FALSE;
	// get the first 7 bytes
	$bytes = file_get_contents($arquivo, FALSE, NULL, 0, 7);
	$ext = strtolower(substr($arquivo, - 4));
	// ZIP magic number: none, though PK\003\004, PK\005\006 (empty archive), 
	// or PK\007\008 (spanned archive) are common.
	// http://en.wikipedia.org/wiki/ZIP_(file_format)
	if ($ext == '.zip' and substr($bytes, 0, 2) == 'PK') {
		return TRUE;
	}
	return FALSE;
}
/**
Retorna true caso $arquivo seja um arquivo do tipo rar, false se contrario.
http://stackoverflow.com/questions/6977544/rar-zip-files-mime-type
**/
function isRar($arquivo) {
	if (! is_file($arquivo)) return FALSE;
	// get the first 7 bytes
	$bytes = file_get_contents($arquivo, FALSE, NULL, 0, 7);
	$ext = strtolower(substr($arquivo, - 4));
	// RAR magic number: Rar!\x1A\x07\x00
	// http://en.wikipedia.org/wiki/RAR
	if ($ext == '.rar' and bin2hex($bytes) == '526172211a0700') {
		return TRUE;
	}
	return FALSE;
}
/**
Retorna true se $arquivo é um arquivo xml
http://forums.devshed.com/php-development-5/checking-if-a-file-is-xml-not-html-716201.html
**/
function isXML($arquivo) {
	$estadoAnterior=libxml_use_internal_errors(true);
	libxml_clear_errors();
	$doc = new DOMDocument('1.0', 'utf-8');
	$doc->load($arquivo);
	$erros = libxml_get_errors();
	libxml_clear_errors();
	libxml_use_internal_errors($estadoAnterior);
	if (empty($erros)) return true;
	else return false;
}
/**
Organiza $arquivo
**/
function organizaXML($arquivo) {
	global $cnpjCpfEmpresa,$diretorioXML,$diretorioDestinoXML,$diretorioTemp,$objetoPDO,$modoOperacao,$objetoNfe;
	if (is_array($arquivo)) organizaXML($arquivo);
	//logar("Processando arquivo '$arquivo'");
	if (! isXML($arquivo)) {
		logar("[alerta 061] arquivo '$arquivo' nao eh xml");
		return FALSE;
	}
	//#FIXME caso o metodo getElementsByTagName seja chamado em uma variavel que nao é objeto, ocorreo o erro
	// Fatal error: Call to undefined method DOMNodeList::getElementsByTagName() in arquivoTal.php
	// empty, isset ou is_object nao previnem isso. Qual melhor meio de tratar?
	$objetoDOM = new DOMDocument();
	if ( ! $objetoDOM->load($arquivo) ) {
		logar("[alerta 050] nao foi possivel carregar o DOM do arquivo '$arquivo' ");
		return FALSE;
		//sair(50);
	}
	// define que tipo de xml eh
	if (isset($objetoDOM->getElementsByTagName('NFe')->item(0)->nodeValue)) { //ha softwares que nao usam o nfeProc
		// o xml é de uma nota fiscal eletronica. Obtem informacoes das notas
		$tipoXML = 'nfe';
		if (! isset($objetoDOM->getElementsByTagName('emit')->item(0)->getElementsByTagName('CNPJ')->item(0)->nodeValue) ) {
			if (! isset($objetoDOM->getElementsByTagName('emit')->item(0)->getElementsByTagName('CPF')->item(0)->nodeValue) ) {
				logar("[alerta 062] nao foi possivel extrair CNPJ ou CPF do emitente do arquivo '$arquivo'");
				return FALSE;
			} else $cnpjCpfEmitente = $objetoDOM->getElementsByTagName('emit')->item(0)->getElementsByTagName('CPF')->item(0)->nodeValue;
		} else $cnpjCpfEmitente = $objetoDOM->getElementsByTagName('emit')->item(0)->getElementsByTagName('CNPJ')->item(0)->nodeValue;

		if ( !isset($objetoDOM->getElementsByTagName('emit')->item(0)->getElementsByTagName('xNome')->item(0)->nodeValue) ) {
			logar("[alerta 063] nao foi possivel extrair nome do emitente do arquivo '$arquivo'");
			return FALSE;
		} else $nomeEmitente = $objetoDOM->getElementsByTagName('emit')->item(0)->getElementsByTagName('xNome')->item(0)->nodeValue;

		if ( !isset($objetoDOM->getElementsByTagName('infProt')->item(0)->getElementsByTagName('chNFe')->item(0)->nodeValue) ) {
			logar("[alerta 064] nao foi possivel extrair chave da nfe do arquivo '$arquivo'");
			return FALSE;
		} else $chaveNota = $objetoDOM->getElementsByTagName('infProt')->item(0)->getElementsByTagName('chNFe')->item(0)->nodeValue;

		if ( !isset($objetoDOM->getElementsByTagName('dest')->item(0)->getElementsByTagName('CNPJ')->item(0)->nodeValue) ) {
			if (! isset($objetoDOM->getElementsByTagName('dest')->item(0)->getElementsByTagName('CPF')->item(0)->nodeValue)) {
				logar("[alerta 065] nao foi possivel extrair CNPJ ou CPF do destinatario do arquivo '$arquivo'");
				return FALSE;
			} else $cnpjCpfDestinatario = $objetoDOM->getElementsByTagName('dest')->item(0)->getElementsByTagName('CPF')->item(0)->nodeValue;
		} else $cnpjCpfDestinatario = $objetoDOM->getElementsByTagName('dest')->item(0)->getElementsByTagName('CNPJ')->item(0)->nodeValue;

		if ( !isset($objetoDOM->getElementsByTagName('dest')->item(0)->getElementsByTagName('xNome')->item(0)->nodeValue) ) {
			logar("[alerta 066] nao foi possivel extrair nome do destinatario do arquivo '$arquivo'");
			return FALSE;
		} else $nomeDestinatario = $objetoDOM->getElementsByTagName('dest')->item(0)->getElementsByTagName('xNome')->item(0)->nodeValue;

		if ( !isset($objetoDOM->getElementsByTagName('ide')->item(0)->getElementsByTagName('nNF')->item(0)->nodeValue) ) {
			logar("[alerta 067] nao foi possivel extrair numero da nota do arquivo '$arquivo'");
			return FALSE;
		} else $numeroNota = $objetoDOM->getElementsByTagName('ide')->item(0)->getElementsByTagName('nNF')->item(0)->nodeValue;

		// Utilizo a data de emissao pois a data do recibo da nota pode nao estar disponivel
		if ( !isset($objetoDOM->getElementsByTagName('ide')->item(0)->getElementsByTagName('dEmi')->item(0)->nodeValue) ) {
			logar("[alerta 068] nao foi possivel extrair data de emissao do arquivo '$arquivo'");
			return FALSE;
		} else $dataEmissaoNota = $objetoDOM->getElementsByTagName('ide')->item(0)->getElementsByTagName('dEmi')->item(0)->nodeValue;

		// Tratando informacoes das notas
		$nomeEmitente = preg_replace('/ /', '-', $nomeEmitente);
		$nomeEmitente = preg_replace('/\-\-/', '-', $nomeEmitente);
		$nomeEmitente = preg_replace('/[^0-9A-Za-z\-]/', '', $nomeEmitente);
		$nomeEmitente = strtoupper($nomeEmitente);
		$nomeDestinatario = preg_replace('/ /', '-', $nomeDestinatario);
		$nomeDestinatario = preg_replace('/\-\-/', '-', $nomeDestinatario);
		$nomeDestinatario = preg_replace('/[^0-9A-Za-z\-]/', '', $nomeDestinatario);
		$nomeDestinatario = strtoupper($nomeDestinatario);
		preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $dataEmissaoNota,$retornosDataEmissao);
		$anoEmissaoNota = $retornosDataEmissao[1];
		$mesEmissaoNota = $retornosDataEmissao[2];
		$diaEmissaoNota = $retornosDataEmissao[3];
		// montando os destinos
		if ($cnpjCpfEmitente == $cnpjCpfEmpresa) {
			$diretorioDestino = $diretorioDestinoXML.DS."nfe".DS."saida".DS.$nomeDestinatario."_".$cnpjCpfDestinatario.DS.$anoEmissaoNota.DS.$mesEmissaoNota;
		}
		elseif ($cnpjCpfDestinatario == $cnpjCpfEmpresa) {
			$diretorioDestino = $diretorioDestinoXML.DS."nfe".DS."entrada".DS.$nomeEmitente."_".$cnpjCpfEmitente.DS.$anoEmissaoNota.DS.$mesEmissaoNota;
		} 
		else {
			$diretorioDestino = $diretorioDestinoXML.DS."nao_identificado".DS."nfe".DS.$nomeEmitente."_".$cnpjCpfEmitente.DS.$anoEmissaoNota.DS.$mesEmissaoNota;
		}
		$arquivoDestino = $diaEmissaoNota."-".$mesEmissaoNota."-".$anoEmissaoNota."_".$numeroNota."_".$chaveNota.".xml";
	} // fim do if para nfe
	// codigo 110110 para o campo tpEvento definido na pagina 77 do manual da receita, versao 5.0
	//elseif( (is_object($objetoDOM->getElementsByTagName('envEvento')->item(0)->getElementsByTagName('evento')->item(0)->getElementsByTagName('infEvento')->item(0)->getElementsByTagName('tpEvento')->item(0)))
	elseif( (is_object($objetoDOM->getElementsByTagName('tpEvento')->item(0)))
		&& (is_object($objetoDOM->getElementsByTagName('envEvento')->item(0)))
		&& (is_object($objetoDOM->getElementsByTagName('envEvento')->item(0)->getElementsByTagName('evento')))
		&& ($objetoDOM->getElementsByTagName('envEvento')->item(0)->getElementsByTagName('evento')->item(0)->getElementsByTagName('infEvento')->item(0)->getElementsByTagName('tpEvento')->item(0)->nodeValue == '110110') ) {
		// o xml é de uma carta de correção. Obtenho informações da carta
		$tipoXML = 'cce';
		if ( ! is_object($objetoDOM->getElementsByTagName('envEvento')->item(0)->getElementsByTagName('evento')->item(0)->getElementsByTagName('infEvento')->item(0)) ) {
			logar("[alerta 069] nao foi possivel extrair informacoes do arquivo '$arquivo'");
			return FALSE;
		} else $infEventoDoPrimeiroEvento = $objetoDOM->getElementsByTagName('envEvento')->item(0)->getElementsByTagName('evento')->item(0)->getElementsByTagName('infEvento')->item(0);

		if ( !isset($infEventoDoPrimeiroEvento->getElementsByTagName('CNPJ')->item(0)->nodeValue)) {
			if ( !isset($infEventoDoPrimeiroEvento->getElementsByTagName('CPF')->item(0)->nodeValue)) {
				logar("[alerta 070] nao foi possivel extrair CPF ou CNPJ do emitente do arquivo '$arquivo'");
				return FALSE;
			} else $cnpjCpfEmitente = $infEventoDoPrimeiroEvento->getElementsByTagName('CPF')->item(0)->nodeValue;;
		} else $cnpjCpfEmitente = $infEventoDoPrimeiroEvento->getElementsByTagName('CNPJ')->item(0)->nodeValue;

		if ( !isset($infEventoDoPrimeiroEvento->getElementsByTagName('chNFe')->item(0)->nodeValue)) {
			logar("[alerta 071] nao foi possivel extrair chave da nota do arquivo '$arquivo'");
			return FALSE;
		} else $chaveNota = $infEventoDoPrimeiroEvento->getElementsByTagName('chNFe')->item(0)->nodeValue;

		if ( ! isset($infEventoDoPrimeiroEvento->getElementsByTagName('dhEvento')->item(0)->nodeValue)) {
			logar("[alerta 072] nao foi possivel extrair data do evento do arquivo '$arquivo'");
			return FALSE;
		} else $dataEvento = $infEventoDoPrimeiroEvento->getElementsByTagName('dhEvento')->item(0)->nodeValue;
		
		if ( ! is_object($objetoDOM->getElementsByTagName('retEnvEvento')->item(0)->getElementsByTagName('retEvento')->item(0)->getElementsByTagName('infEvento')->item(0))) {
			logar("[alerta 073] nao foi possivel extrair informacoes do arquivo '$arquivo'");
			return FALSE;
		} else $retornoInfEvento = $objetoDOM->getElementsByTagName('retEnvEvento')->item(0)->getElementsByTagName('retEvento')->item(0)->getElementsByTagName('infEvento')->item(0);
		
		if ( ! isset($retornoInfEvento->getElementsByTagName('CNPJDest')->item(0)->nodeValue)) {
			if (! isset($retornoInfEvento->getElementsByTagName('CPFDest')->item(0)->nodeValue)) {
				logar("[alerta 074] nao foi possivel extrair CPF ou CNPJ do destinatario do arquivo '$arquivo'");
				return FALSE;
			} else $cnpjCpfDestinatario = $retornoInfEvento->getElementsByTagName('CPFDest')->item(0)->nodeValue;
		} else $cnpjCpfDestinatario = $retornoInfEvento->getElementsByTagName('CNPJDest')->item(0)->nodeValue;
		// trato informaçoes da carta
		preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $dataEvento,$retornosDataEvento);
		$anoEmissaoEvento = $retornosDataEvento[1];
		$mesEmissaoEvento = $retornosDataEvento[2];
		$diaEmissaoEvento = $retornosDataEvento[3];
		// montando os destinos
		if ($cnpjCpfEmitente == $cnpjCpfEmpresa) {
			$diretorioDestino = $diretorioDestinoXML.DS."cce".DS."saida".DS.$cnpjCpfDestinatario.DS.$anoEmissaoEvento.DS.$mesEmissaoEvento;
		}
		elseif ($cnpjCpfDestinatario == $cnpjCpfEmpresa) {
			$diretorioDestino = $diretorioDestinoXML.DS."cce".DS."entrada".DS.$cnpjCpfEmitente.DS.$anoEmissaoEvento.DS.$mesEmissaoEvento;
		} 
		else {
			$diretorioDestino = $diretorioDestinoXML.DS."nao_identificado".DS."cce".DS.$cnpjCpfEmitente.DS.$anoEmissaoEvento.DS.$mesEmissaoEvento;
		}
		$arquivoDestino = $diaEmissaoEvento."-".$mesEmissaoEvento."-".$anoEmissaoEvento."_".$chaveNota.".xml";
	} // fim do if para cce
	//elseif (isset($objetoDOM->getElementsByTagName('CTe')->item(0)->getElementsByTagName('infCte')->item(0)->nodeValue)) {
	elseif (isset($objetoDOM->getElementsByTagName('infCte')->item(0)->nodeValue)) {
		//xml de cte. Obtenho informações do cte
		$tipoXML = 'cte';
		if (! is_object($objetoDOM->getElementsByTagName('CTe')->item(0)->getElementsByTagName('infCte')->item(0))) {
			logar("[alerta 075] nao foi possivel extrair informacoes do arquivo '$arquivo'");
			return FALSE;
		} else $infCte = $objetoDOM->getElementsByTagName('CTe')->item(0)->getElementsByTagName('infCte')->item(0);

		if ( !isset($infCte->getElementsByTagName('ide')->item(0)->getElementsByTagName('dhEmi')->item(0)->nodeValue)) {
			logar("[alerta 076] nao foi possivel extrair a data de emissao do arquivo '$arquivo'");
			return FALSE;
		} else $dataEmissaoCte = $infCte->getElementsByTagName('ide')->item(0)->getElementsByTagName('dhEmi')->item(0)->nodeValue;

		if (! isset($infCte->getElementsByTagName('ide')->item(0)->getElementsByTagName('nCT')->item(0)->nodeValue)) {
			logar("[alerta 077] nao foi possivel extrair o numero do cte do arquivo '$arquivo'");
			return FALSE;
		} else $numeroCte = $infCte->getElementsByTagName('ide')->item(0)->getElementsByTagName('nCT')->item(0)->nodeValue;

		if ( !isset($infCte->getElementsByTagName('emit')->item(0)->getElementsByTagName('CNPJ')->item(0)->nodeValue)) {
			if (! isset($infCte->getElementsByTagName('emit')->item(0)->getElementsByTagName('CPF')->item(0)->nodeValue)) {
				logar("[alerta 078] nao foi possivel extrair o CNPJ ou CPF do arquivo '$arquivo'");
				return FALSE;
			} else $cnpjCpfEmitente = $infCte->getElementsByTagName('emit')->item(0)->getElementsByTagName('CPF')->item(0)->nodeValue;;
		} else $cnpjCpfEmitente = $infCte->getElementsByTagName('emit')->item(0)->getElementsByTagName('CNPJ')->item(0)->nodeValue;

		if (! isset($infCte->getElementsByTagName('emit')->item(0)->getElementsByTagName('xNome')->item(0)->nodeValue)) {
			logar("[alerta 079] nao foi possivel extrair o nome do emitente do arquivo '$arquivo'");
			return FALSE;
		} else $nomeEmitente = $infCte->getElementsByTagName('emit')->item(0)->getElementsByTagName('xNome')->item(0)->nodeValue;

		if (! isset($infCte->getElementsByTagName('dest') ->item(0)->getElementsByTagName('CNPJ')->item(0)->nodeValue)) {
			if (! isset($infCte->getElementsByTagName('dest') ->item(0)->getElementsByTagName('CPF')->item(0)->nodeValue)) {
				logar("[alerta 080] nao foi possivel extrair o CNPJ ou CPF do destinatario do arquivo '$arquivo'");
				return FALSE;
			} else $cnpjCpfDestinatario = $infCte->getElementsByTagName('dest') ->item(0)->getElementsByTagName('CPF')->item(0)->nodeValue;
		} else $cnpjCpfDestinatario = $infCte->getElementsByTagName('dest') ->item(0)->getElementsByTagName('CNPJ')->item(0)->nodeValue;

		if (! isset($infCte->getElementsByTagName('dest') ->item(0)->getElementsByTagName('xNome')->item(0)->nodeValue)) {
			logar("[alerta 081] nao foi possivel extrair o nome do destinatario do arquivo '$arquivo'");
			return FALSE;
		} else $nomeDestinatario = $infCte->getElementsByTagName('dest') ->item(0)->getElementsByTagName('xNome')->item(0)->nodeValue;

		if (! isset($objetoDOM->getElementsByTagName('protCTe')->item(0)->getElementsByTagName('infProt')->item(0)->getElementsByTagName('chCTe')->item(0)->nodeValue)) {
			logar("[alerta 077] nao foi possivel extrair a chave do cte do arquivo '$arquivo'");
			return FALSE;
		} else $chaveCte = $objetoDOM->getElementsByTagName('protCTe')->item(0)->getElementsByTagName('infProt')->item(0)->getElementsByTagName('chCTe')->item(0)->nodeValue;
		// trato informacoes do cte
		$nomeEmitente = preg_replace('/ /', '-', $nomeEmitente);
		$nomeEmitente = preg_replace('/\-\-/', '-', $nomeEmitente);
		$nomeEmitente = preg_replace('/[^0-9A-Za-z\-]/', '', $nomeEmitente);
		$nomeEmitente = strtoupper($nomeEmitente);
		$nomeDestinatario = preg_replace('/ /', '-', $nomeDestinatario);
		$nomeDestinatario = preg_replace('/\-\-/', '-', $nomeDestinatario);
		$nomeDestinatario = preg_replace('/[^0-9A-Za-z\-]/', '', $nomeDestinatario);
		$nomeDestinatario = strtoupper($nomeDestinatario);
		preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $dataEmissaoCte,$retornosDataEmissaoCte);
		$anoEmissaoCte = $retornosDataEmissaoCte[1];
		$mesEmissaoCte = $retornosDataEmissaoCte[2];
		$diaEmissaoCte = $retornosDataEmissaoCte[3];
		// montando os destinos
		if ($cnpjCpfEmitente == $cnpjCpfEmpresa) {
			$diretorioDestino = $diretorioDestinoXML.DS."cte".DS."saida".DS.$nomeDestinatario."_".$cnpjCpfDestinatario.DS.$anoEmissaoCte.DS.$mesEmissaoCte;
		}
		elseif ($cnpjCpfDestinatario == $cnpjCpfEmpresa) {
			$diretorioDestino = $diretorioDestinoXML.DS."cte".DS."entrada".DS.$nomeEmitente."_".$cnpjCpfEmitente.DS.$anoEmissaoCte.DS.$mesEmissaoCte;
		} 
		else {
			$diretorioDestino = $diretorioDestinoXML.DS."nao_identificado".DS."cte".DS.$nomeEmitente."_".$cnpjCpfEmitente.DS.$anoEmissaoCte.DS.$mesEmissaoCte;
		}
		$arquivoDestino = $diaEmissaoCte."-".$mesEmissaoCte."-".$anoEmissaoCte."_".$numeroCte."_".$chaveCte.".xml";
	} // fim do if para cte
	// cancelamento de nota fiscal
	else if ( (is_object($objetoDOM->getElementsByTagName('tpEvento')->item(0)))
		&& (is_object($objetoDOM->getElementsByTagName('evento')->item(0)))
		&& (is_object($objetoDOM->getElementsByTagName('evento')->item(0)->getElementsByTagName('infEvento')))
		&& ($objetoDOM->getElementsByTagName('evento')->item(0)->getElementsByTagName('infEvento')->item(0)->getElementsByTagName('tpEvento')->item(0)->nodeValue == '110111' ) ){
		$tipoXML = 'cancelamento';
		
		$infEvento = $objetoDOM->getElementsByTagName('evento')->item(0)->getElementsByTagName('infEvento')->item(0);
		$codigoOrgao = $infEvento->getElementsByTagName('cOrgao')->item(0)->nodeValue;
		if (!isset($infEvento->getElementsByTagName('CNPJ')->item(0)->nodeValue)) {
			if (!isset($infEvento->getElementsByTagName('CPF')->item(0)->nodeValue)) {
				logar ("[alerta ] nao foi possivel extrair o CNPJ ou CPF do arquivo '$arquivo'");
				return FALSE;
			} else $infEvento->getElementsByTagName('CPF')->item(0)->nodeValue;
		} else $cnpjCpfEmitente = $infEvento->getElementsByTagName('CNPJ')->item(0)->nodeValue;
		$chaveNota = $infEvento->getElementsByTagName('chNFe')->item(0)->nodeValue;
		$dataHoraEvento = $infEvento->getElementsByTagName('dhEvento')->item(0)->nodeValue;
		$numeroProtocolo = $infEvento->getElementsByTagName('detEvento')->item(0)->getElementsByTagName('nProt')->item(0)->nodeValue;
		$justificativaCancelamento = $infEvento->getElementsByTagName('detEvento')->item(0)->getElementsByTagName('xJust')->item(0)->nodeValue;
		if (!isset($objetoDOM->getElementsByTagName('retEvento')->item(0)->getElementsByTagName('CNPJDest')->item(0)->nodeValue)) {
			if (!isset($objetoDOM->getElementsByTagName('retEvento')->item(0)->getElementsByTagName('CPFDest')->item(0)->nodeValue)) {
				logar ("[alerta ] nao foi possivel extrair o CNPJ ou CPF do arquivo '$arquivo'");
				return FALSE;
			} else $objetoDOM->getElementsByTagName('retEvento')->item(0)->getElementsByTagName('CPFDest')->item(0)->nodeValue;
		} else $cnpjCpfDestinatario = $objetoDOM->getElementsByTagName('retEvento')->item(0)->getElementsByTagName('CNPJDest')->item(0)->nodeValue;
		
		$dataHoraEvento = preg_match('/([0-9]{4})\-([0-9]{2})\-([0-9]{2})[A-Za-z]([0-9]{2})\:([0-9]{2})\:([0-9]{2})/', $dataHoraEvento, $dataHoraEventoRegex);
		$anoEmissaoCanc =		$dataHoraEventoRegex[1];
		$mesEmissaoCanc =		$dataHoraEventoRegex[2];
		$diaEmissaoCanc =		$dataHoraEventoRegex[3];
		$horaEmissaoCanc =		$dataHoraEventoRegex[4];
		$minutoEmissaoCanc =	$dataHoraEventoRegex[5];
		$segundoEmissaoCanc =	$dataHoraEventoRegex[6];
		$dataCancelamento =		"$anoEmissaoCanc-$mesEmissaoCanc-$diaEmissaoCanc";
		$horaCancelamento =		"$horaEmissaoCanc:$minutoEmissaoCanc:$segundoEmissaoCanc";
		
		// montando os destinos
		if ($cnpjCpfEmitente == $cnpjCpfEmpresa) {
			$diretorioDestino = $diretorioDestinoXML.DS."cancelamento".DS."saida".DS.$cnpjCpfDestinatario.DS.$anoEmissaoCanc.DS.$mesEmissaoCanc;
		}
		elseif ($cnpjCpfDestinatario == $cnpjCpfEmpresa) {
			$diretorioDestino = $diretorioDestinoXML.DS."cancelamento".DS."entrada".DS.$cnpjCpfEmitente.DS.$anoEmissaoCanc.DS.$mesEmissaoCanc;
		} 
		else {
			$diretorioDestino = $diretorioDestinoXML.DS."nao_identificado".DS."cancelamento".DS.$cnpjCpfEmitente.DS.$anoEmissaoCanc.DS.$mesEmissaoCanc;
		}
		$arquivoDestino = $diaEmissaoCanc."-".$mesEmissaoCanc."-".$anoEmissaoCanc."_".$chaveNota.".xml";
	} //fim do if para cancelamento
	else {
		logar("[aviso 056] xml '$arquivo' nao identificado");
		$tipoXML = 'nao_identificado';
		$diretorioDestino = $diretorioDestinoXML.DS."nao_identificado".DS."desconhecido";
		$arquivoDestino = basename($arquivo);
	}
	if ( ($modoOperacao == 1) || ($modoOperacao == 2) ) {
		// Cuida da criação e checagem do diretorio de destino dos XMLs
		if ( ! file_exists($diretorioDestino)) {
			if (! mkdir($diretorioDestino,0777,true)) {
				logar("[erro 051] diretorio '$diretorioDestino' nao existe e nao foi possivel cria-lo");
				sair(51); 
			}
		} elseif (! is_dir($diretorioDestino)) {
			logar("'$diretorioDestino' nao eh um diretorio. Movendo-o para '$diretorioDestino".date('dmY-Gis').".old'");
			if ( ! rename($diretorioDestino,$diretorioDestino.date('dmY-Gis').'.old') ) {
				logar("[erro 052] nao foi possivel mover '$diretorioDestino'");
				sair(52);
			}
			elseif (! mkdir($diretorioDestino,0777,true)) {
				logar("[erro 053] '$diretorioDestino' foi movido mas nao foi possivel criar um diretorio no lugar");
				sair(53);
			}
		}
		// Move os XMLs
		//if (! rename($arquivo,$diretorioDestino.DS) ) { // nao funcionou no windows 7, ocorre acesso negado
		if (! copy($arquivo,$diretorioDestino.DS.$arquivoDestino) ) {
			logar("[erro 054] nao foi possivel copiar '$arquivo' para o diretorio de destino '$diretorioDestino'");
			sair(54);
		}
		elseif (! unlink($arquivo)) {
			logar("[alerta 055] nao foi possivel deletar o arquivo '$arquivo' apos ser copiado para o diretorio de destino");
			//continue;
			//sair(55);
		}
	}
	if ($modoOperacao == 1) return TRUE;
	elseif ($modoOperacao == 2) {
		$caminhoRelativoArquivo = substr($diretorioDestino.DS.$arquivoDestino,strlen($diretorioDestinoXML));
		$caminhoRelativoArquivo = preg_replace("/\//", '$DS', $caminhoRelativoArquivo);
		$caminhoRelativoArquivo = preg_replace("/\\\/", '$DS', $caminhoRelativoArquivo);
	}
	elseif ($modoOperacao == 3) {
		$conteudoXMLArquivo = addslashes(file_get_contents($arquivo));
	}
	$dataAtual = date('Y-m-d');
	$horaAtual = date('G:i:s');
	if ($tipoXML == 'nfe') {
		$d = "$anoEmissaoNota-$mesEmissaoNota-$diaEmissaoNota";
		$horaEmissaoNota = '00:00:00'; //somente o recibo tem hora
		$nomeEmitenteBd = preg_replace('/\-/', ' ', $nomeEmitente);
		$nomeDestinatarioBd = preg_replace('/\-/',' ',$nomeDestinatario);
		if ($modoOperacao == 2) {
			$queryInserir = "INSERT INTO nota_fiscal
			(data_importacao,hora_importacao,data_emissao_nota,hora_emissao_nota,chave_nota,numero_nota,cnpj_cpf_emitente,cnpj_cpf_destinatario,nome_emitente,nome_destinatario,caminho_relativo_arquivo)
			VALUES ('$dataAtual','$horaAtual','$d','$horaEmissaoNota','$chaveNota','$numeroNota','$cnpjCpfEmitente','$cnpjCpfDestinatario','$nomeEmitenteBd','$nomeDestinatarioBd','$caminhoRelativoArquivo')";
		} elseif ($modoOperacao == 3) {
			$queryInserir = "INSERT INTO nota_fiscal
			(data_importacao,hora_importacao,data_emissao_nota,hora_emissao_nota,chave_nota,numero_nota,cnpj_cpf_emitente,cnpj_cpf_destinatario,nome_emitente,nome_destinatario,xml)
			VALUES ('$dataAtual','$horaAtual','$d','$horaEmissaoNota','$chaveNota','$numeroNota','$cnpjCpfEmitente','$cnpjCpfDestinatario','$nomeEmitenteBd','$nomeDestinatarioBd','$conteudoXMLArquivo')";
		}
		$queryBuscar = "SELECT id FROM nota_fiscal WHERE chave_nota = '$chaveNota'";
		$queryDeletar = "DELETE FROM nota_fiscal WHERE id = :id";
	}
	elseif ($tipoXML == 'cce') {
		$d = "$anoEmissaoEvento-$mesEmissaoEvento-$diaEmissaoEvento";
		preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})/', $dataEvento,$horaEvento);
		$horaEvento = $horaEvento[0];
		if ($modoOperacao == 2) {
			$queryInserir = "INSERT INTO carta_correcao
			(data_importacao,hora_importacao,data_emissao_carta,hora_emissao_carta,chave_nota,cnpj_cpf_emitente,cnpj_cpf_destinatario,caminho_relativo_arquivo)
			VALUES ('$dataAtual','$horaAtual','$d','$horaEvento','$chaveNota','$cnpjCpfEmitente','$cnpjCpfDestinatario','$caminhoRelativoArquivo')";
		} elseif ($modoOperacao == 3) {
			$queryInserir = "INSERT INTO carta_correcao
			(data_importacao,hora_importacao,data_emissao_carta,hora_emissao_carta,chave_nota,cnpj_cpf_emitente,cnpj_cpf_destinatario,xml)
			VALUES ('$dataAtual','$horaAtual','$d','$horaEvento','$chaveNota','$cnpjCpfEmitente','$cnpjCpfDestinatario','$conteudoXMLArquivo')";
		}
		$queryBuscar = "SELECT id FROM carta_correcao WHERE chave_nota = '$chaveNota'";
		$queryDeletar = "DELETE FROM carta_correcao WHERE id = :id";
	}
	elseif ($tipoXML == 'cte') {
		$d = "$anoEmissaoCte-$mesEmissaoCte-$diaEmissaoCte";
		preg_match('/([0-9]{2}):([0-9]{2}):([0-9]{2})/', $dataEmissaoCte,$horaEmissaoCte);
		$horaEmissaoCte = $horaEmissaoCte[0];
		$nomeEmitenteBd = preg_replace('/\-/', ' ', $nomeEmitente);
		$nomeDestinatarioBd = preg_replace('/\-/',' ',$nomeDestinatario);
		if ($modoOperacao == 2) {
			$queryInserir = "INSERT INTO conhecimento_transporte
			(data_importacao,hora_importacao,data_emissao_conhecimento,hora_emissao_conhecimento,chave_conhecimento,numero_conhecimento,cnpj_cpf_emitente,cnpj_cpf_destinatario,'nome_emitente','nome_destinatario',caminho_relativo_arquivo)
			VALUES ('$dataAtual','$horaAtual','$d','$horaEmissaoCte','$chaveCte','$numeroCte','$cnpjCpfEmitente','$cnpjCpfDestinatario','$nomeEmitenteBd','$nomeDestinatarioBd','$caminhoRelativoArquivo')";
		} elseif($modoOperacao == 3) {
			$queryInserir = "INSERT INTO conhecimento_transporte
			(data_importacao,hora_importacao,data_emissao_conhecimento,hora_emissao_conhecimento,chave_conhecimento,numero_conhecimento,cnpj_cpf_emitente,cnpj_cpf_destinatario,nome_emitente,nome_destinatario,xml)
			VALUES ('$dataAtual','$horaAtual','$d','$horaEmissaoCte','$chaveCte','$numeroCte','$cnpjCpfEmitente','$cnpjCpfDestinatario','$nomeEmitenteBd','$nomeDestinatarioBd','$conteudoXMLArquivo')";
		}
		$queryBuscar = "SELECT id FROM conhecimento_transporte WHERE chave_conhecimento = '$chaveCte'";
		$queryDeletar = "DELETE FROM conhecimento_transporte WHERE id = :id";
	}
	elseif ($tipoXML == 'cancelamento') {
		if ($modoOperacao == 2) {
			$queryInserir = "INSERT INTO cancelamento
			(data_importacao,hora_importacao,codigo_orgao,cnpj_cpf_emitente,chave_nota,data_cancelamento,hora_cancelamento,numero_protocolo,justificativa_cancelamento,cnpj_cpf_destinatario,caminho_relativo_arquivo)
			VALUES ('$dataAtual','$horaAtual','$codigoOrgao','$cnpjCpfEmitente','$chaveNota','$dataCancelamento','$horaCancelamento','$numeroProtocolo','$justificativaCancelamento','$cnpjCpfDestinatario','$caminhoRelativoArquivo')";
		}
		elseif ($modoOperacao == 3) {
			$queryInserir = "INSERT INTO cancelamento
			(data_importacao,hora_importacao,codigo_orgao,cnpj_cpf_emitente,chave_nota,data_cancelamento,hora_cancelamento,numero_protocolo,justificativa_cancelamento,cnpj_cpf_destinatario,xml)
			VALUES ('$dataAtual','$horaAtual','$codigoOrgao','$cnpjCpfEmitente','$chaveNota','$dataCancelamento','$horaCancelamento','$numeroProtocolo','$justificativaCancelamento','$cnpjCpfDestinatario','$conteudoXMLArquivo')";
		}
		$queryBuscar = "SELECT id FROM cancelamento WHERE chave_nota = '$chaveNota'";
		$queryDeletar = "DELETE FROM cancelamento WHERE id = :id";
	}
	elseif ($tipoXML == 'nao_identificado') {
		$d = basename($arquivo);
		if ($modoOperacao == 2) {
			$queryInserir = "INSERT INTO nao_identificado
			(data_importacao,hora_importacao,nome_arquivo,caminho_relativo_arquivo)
			VALUES ('$dataAtual','$horaAtual','$d','$caminhoRelativoArquivo')";
		} elseif ($modoOperacao == 3) {
			$queryInserir = "INSERT INTO nao_identificado
			(data_importacao,hora_importacao,nome_arquivo,xml)
			VALUES ('$dataAtual','$horaAtual','$d','$conteudoXMLArquivo')";
		}
	}
	if ( (isset($queryBuscar)) && (! empty($queryBuscar)) ) {
		try {
			$resultadosBusca = $objetoPDO->query($queryBuscar);
		}
		catch (PDOException $e) {
			logar("[erro 082] ao buscar possiveis registros anteriores durante o processamento do arquivo '$arquivo'. Detalhes: ".$e->getMessage().". Query: $queryBuscar");
		}
		if (! $resultadosBusca) {
			$e = $objetoPDO->errorInfo();
			logar("[erro 083] ao buscar possiveis registros anteriores durante o processamento do arquivo '$arquivo'. Detalhes: ".$e[0]." ".$e[1]." ".$e[2].". Query: $queryBuscar");
		}
		else {
			while ($resultadoBusca = $resultadosBusca->fetch(PDO::FETCH_OBJ)) {
				try {
					$statement = $objetoPDO->prepare($queryDeletar);
					$statement->bindParam(':id',$resultadoBusca->id,PDO::PARAM_INT);
					$statement->execute();
				}
				catch (PDOException $e) {
					logar("[erro 084] ao deletar registro anteiror identificado pelo id '".$resultadoBusca->id."'. Detalhes: ".$e->getMessage());
				}
			}
		}
	}
	if ((isset($queryInserir)) && (! empty($queryInserir))) {
		try {
			if (! $objetoPDO->query($queryInserir)) {
				$e = $objetoPDO->errorInfo();
				logar("[erro 085] ao inserir dados no banco de dados durante o processamento do arquivo '$arquivo'. Detalhes: ".$e[0]." ".$e[1]." ".$e[2].". Query: $queryInserir");
				return FALSE;
			}
		} catch(PDOException $e) {
			logar("[erro 086] ao inserir valores no banco de dados durante o processamento do arquivo '$arquivo'. Detalhes: ".$e->getMessage().". Query: $queryInserir");
			return FALSE;
		}
	}
	if ( ($tipoXML == 'nfe') ) {
		$objetoNfe = new ToolsNFePHP;
		// verifica se o arquivo foi validado pela receita
		//#TODO definir um sleep a cada consulta?
		try {
			$consultaValidade = $objetoNfe->verifyNFe($arquivo);
		} catch (nfephpException $e) {
			logar("[erro 087] na consulta de validade do xml. Detalhes: ".$e->getMessage());
			return FALSE;
		}
		// "Nao houve retorno Soap" é uma string geralmente retornada nos erros
		if ( (! $consultaValidade) && (! preg_match('/SSL connection timeout/i', $objetoNfe->errMsg)) && (! preg_match('/houve retorno Soap/i', $objetoNfe->errMsg)) ) {
			logar("[alerta 088] arquivo '$arquivo' nao eh um xml validado pela receita federal. Detalhes: ".$objetoNfe->errMsg);
			$queryInserirInvalido = "INSERT INTO xml_invalido (data_registro,hora_registro,tipo_arquivo,id_arquivo,mensagem)
			VALUES('$dataAtual','$horaAtual','$tipoXML','".$objetoPDO->lastInsertId()."','".addslashes(utf8_decode($objetoNfe->errMsg))."')";
			if (! $objetoPDO->query($queryInserirInvalido)) {
				$e = $objetoPDO->errorInfo();
				logar("[erro 089] erro ao inserir dados do xml invalido no banco de dados. Detalhes: ".$e[0]." ".$e[1]." ".$e[2].". Query: $queryInserirInvalido");
				return FALSE;
			}
		}
	}
	if ($modoOperacao == 3) {
		if (! unlink($arquivo)) {
			logar("[erro 090] ao deletar arquivo '$arquivo' no modo de operacao 3");
			return FALSE;
		}
	}
	return TRUE;
}
/**
Procura arquivos zip em $diretorio, extrai os xmls e move para $diretorioXML
**/
function procuraOrganizaZip($diretorio) {
	global $diretorioXML,$diretorioTemp;
	$ponteiroDir = opendir($diretorio);
	while (false !== ($arquivo=readdir($ponteiroDir))) {
		$arquivoFull = $diretorio.DS.$arquivo;
		if (! isZip($arquivoFull)) continue;
		// caso o arquivo seja um zip, extraio
		$objetoZip = new ZipArchive();
		if ($objetoZip->open($arquivoFull) === true) {
			/* Extraio os arquivos do zip para um diretorio temporario.
			Outra abordagem seria apenas ver se a extensão dos arquivos é .xml e
			copia-los para o destino, assim não seriam criados diretorios que por ventura
			existam dentro do zip. Ver: http://www.php.net/manual/en/ziparchive.extractto.php#100802
			*/
			if ( ! $objetoZip->extractTo($diretorioTemp) ) {
				logar("[alerta 100] nao foi possivel extrair o arquivo zip '$arquivoFull'");
				continue;
				//sair(100);
			}
			// Le o diretorio recursivamente
			// http://php.net/manual/pt_BR/class.recursivedirectoryiterator.php
			$objetoIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($diretorioTemp), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($objetoIterator AS $nomeArquivoZipExtraido => $objetoDirectory) {
				if (! $objetoDirectory->isFile()) continue;
				if (isXML($nomeArquivoZipExtraido)) {
					if (! copy($nomeArquivoZipExtraido,$diretorioXML.DS.$objetoDirectory->getFilename()) ) {
						logar("[erro 101] nao foi possivel copiar '$nomeArquivoZipExtraido' para o diretorio de destino '$diretorioXML'");
						continue;
					}
				}
				if (! unlink($nomeArquivoZipExtraido)) {
					logar("[alerta 102] nao foi possivel deletar o arquivo '$nomeArquivoZipExtraido'");
					continue;
				}
			}
		}
		else {
			logar("[aviso 103] nao foi possivel abrir o arquivo zip '$arquivoFull'");
			continue;
			//sair(103);
		}
		if ( ! unlink($arquivoFull) ) {
			logar("[aviso 104] nao foi possivel excluir o arquivo '$arquivoFull'");
			continue;
			//sair(104);
		}
		$objetoZip->close();
	}
	closedir($ponteiroDir);
}

/*************************************
* 		      CHECAGENS
**************************************/
if ( (! isset($modoOperacao)) || (is_null($modoOperacao)) || (! is_numeric($modoOperacao)) || ($modoOperacao < 1) || ($modoOperacao > 3) ) {
	logar("[erro 001] modo de operacao invalido");
	sair(1);
}
if ( ! file_exists($diretorioXML)) {
	if ( ! mkdir($diretorioXML,0777)) {
		logar("[erro 002] '$diretorioXML' nao existe e nao foi possivel cria-lo");
		sair(2);
	}
}
if ( (! is_dir($diretorioXML)) || (! is_readable($diretorioXML)) ) {
	logar("[erro 003] '$diretorioXML' nao eh um diretorio ou nao tem permissao de leitura");
	sair(3);
}
if ( ($modoOperacao == 1) || ($modoOperacao == 2) ) {
	if (! file_exists($diretorioDestinoXML)) {
		if (! mkdir($diretorioDestinoXML,0777)) {
			logar("[erro 004] '$diretorioDestinoXML' nao existe e nao foi possivel cria-lo");
			sair(4);
		}
	}
	if ( (! is_dir($diretorioDestinoXML)) || (! is_writable($diretorioDestinoXML)) ) {
		logar("[erro 005] '$diretorioDestinoXML' nao eh um diretorio ou nao tem permissao de escrita");
		sair(5);
	}
}
if ( ! file_exists($arquivoLog)) {
	if ( ! file_put_contents($arquivoLog, date('d-m-Y_H:i:s ')."arquivo de log criado\r\n")) {
		print "[erro 006] arquivo de log '$arquivoLog' nao existe e nao foi possivel cria-lo";
		sair(6);
	}
}
if ( (! is_file($arquivoLog)) || (! is_writable($arquivoLog)) ) {
	print "[erro 007] '$arquivoLog' nao eh um arquivo ou nao tem permissao de escrita";
	sair(7);
}
$diretorioTemp = $diretorioTemp.DS."organizaXML-".date('dmYGis'); //modifica variavel global
if ( ! file_exists($diretorioTemp)) {
	if ( ! mkdir($diretorioTemp,0777)) {
		logar("[erro 008] '$diretorioTemp' nao existe e nao foi possivel cria-lo");
		sair(8);
	}
}
if ( (! is_dir($diretorioTemp)) || (! is_readable($diretorioTemp)) ) {
	logar("[erro 009] '$diretorioTemp' nao eh um diretorio ou nao tem permissao de leitura");
	sair(9);
}
if ( (! isset($cnpjCpfEmpresa)) || (is_null($cnpjCpfEmpresa)) ) {
	logar("[erro 010] variavel cnpjCpfEmpresa nao definida");
	sair(10);
}
try {
	$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco","$bdUsuario","$bdSenha");
} catch (PDOException $e) {
	logar("[erro 011] ao conectar ao banco de dados: ". $e->getMessage());
	sair(11);
}
require_once('libs/nfephp/libs/ToolsNFePHP.class.php');

/*************************************
*      Baixa os xmls nos e-mails
**************************************/
if (! extension_loaded('imap')) {
	logar("[alerta 070] modulo imap nao carregado. E-mails nao serao verificados");
}
else {
	if ( ! $caixaEntrada = imap_open($emailStringConexao, $emailUsuario, $emailSenha) ) {
		logar('[erro 071] nao foi possivel conectar a conta de e-mail. Detalhes: '.imap_last_error());
	}
	else {
		// http://sidneypalmeira.wordpress.com/2011/07/21/php-como-ler-um-e-mail-e-salvar-o-anexo-via-imap/
		for ($contador=1; $contador <= imap_num_msg($caixaEntrada); $contador++) {
			// define se a mensagem corrente será deletada apos o processamento
			$deletarMensagem = true;
			// pega a estrutura da mensagem
			$estrutura = imap_fetchstructure($caixaEntrada, $contador);
			/* No caso cada part pode ter um anexo (talvez exceto a primeira). Caso a mensagem
			tenha apenas um anexo, ele deve ser a part 2. Vasculho todos as partes */
			$numeroPart = 0;
			foreach($estrutura->parts as $parte) {
				// na funcao imap_fetchbody o terceiro parametro começa com o indice 1
				$numeroPart++;
				// parece que as partes que contem anexo tem este atributo definido
				if ( (isset($parte->disposition)) && (strtolower($parte->disposition) != 'attachment') ) {
					$deletarMensagem = false;
					continue;
				}
				$nomeAnexo = md5(strtolower($parte->parameters[0]->value));
				$nomeArquivoAnexo = $diretorioTemp.DS."email_".$nomeAnexo;
				//Da mensagem, pega o conteúdo do anexo
				// http://garrettstjohn.com/entry/extracting-attachments-from-emails-with-php/
				switch ($parte->encoding) {
					case 3:
						// 3 = Base64
						$conteudoAnexo = imap_base64(imap_fetchbody($caixaEntrada, $contador, $numeroPart));
						break;
					case 4:
						// 4 = quoted-printable
						$conteudoAnexo = quoted_printable_decode(imap_fetchbody($caixaEntrada, $contador, $numeroPart));
						break;
					default:
						$conteudoAnexo = imap_fetchbody($caixaEntrada, $contador, $numeroPart);
						break;
				}
				if ( ! $ponteiroArquivo = fopen($nomeArquivoAnexo,"w") ) {
						logar("[alerta 072] erro ao abrir um ponteiro para arquivo em $nomeArquivoAnexo");
						$deletarMensagem = false;
						continue;
						//sair(71);
				}
				if (! fwrite($ponteiroArquivo,$conteudoAnexo) ) {
					logar("[alerta 073] erro ao escrever no arquivo $nomeArquivoAnexo");
					unlink($nomeArquivoAnexo); // deleto o arquivo em branco que foi criado
					$deletarMensagem = false;
					continue;
				}
				fclose($ponteiroArquivo);
				// verifico se é um xml ou zip
				if ( (! isXML($nomeArquivoAnexo)) && (! isZip($nomeArquivoAnexo)) ) {
					//logar("[alerta 074] tipo de arquivo anexo nao suportado ('$nomeArquivoAnexo')");
					unlink($nomeArquivoAnexo);
					continue;
				}
				if (! copy($nomeArquivoAnexo,$diretorioXML.DS."email_".$nomeAnexo)) {
					logar("[alerta 075] nao foi possivel mover arquivo anexo do diretorio temporario para diretorio final");
					$deletarMensagem = false;
					continue;
				}
				unlink($nomeArquivoAnexo);
			} //termino da verificação das parts
			if ( ($deletarMensagem === true) && (! imap_delete($caixaEntrada,$contador)) ) {
				logar("[alerta 076] erro ao marcar mensagem $contador para ser deletada");
				continue;
			}
		} // término do loop para cada mensagem
		// para deletar as mensagens marcadas pelo imap_delete
		imap_expunge($caixaEntrada);
		// fecha a conexao ao servidor imap
		imap_close($caixaEntrada);
	}
}

/*************************************
*             Busca zips
**************************************/
//procuraOrganizaZip($diretorioTemp);
procuraOrganizaZip($diretorioXML);

/*************************************
*    Busca xmls no $diretorioXML
**************************************/
$ponteiroDirXML = opendir($diretorioXML);
while (false !== ($arquivo=readdir($ponteiroDirXML))) {
	if (! is_file($diretorioXML.DS.$arquivo)) continue;
	organizaXML($diretorioXML.DS.$arquivo);
}
closedir($ponteiroDirXML);

delTree($diretorioTemp);

//EOF