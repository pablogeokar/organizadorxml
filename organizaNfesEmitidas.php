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

/* O serviço de Consulta da Relação de Documentos Destinados
podeserconsumidopordestinatáriodeNF-e,PessoaJurídica,
quepossuaum certificado digital de PJ com o seu CNPJ base.
página 13 da nota técnica 2012/002

Aempresa deverá aguardar um tempo mínimo de 1 hora para efetuar uma nova solicitação de
distribuição, caso receba aindicação que nãoexiste mmais documentos a serem pesquisados na
base de dados da SEFAZ (indCont=0).
pagina 26.
IMPORTANTE: Este script deverá rodar a cada uma hora.
 */

error_reporting(E_ALL);
register_shutdown_function("fimScript");
set_time_limit(0);
ini_set("memory_limit", "36M");

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'util.php');
require_once('libs/nfephp/libs/ToolsNFePHP.class.php');

/*************************************
*FUNÇÕES
**************************************/

/**
Função para tratar erros fatais
Necessario definir register_shutdown_function("fimScript");
http://stackoverflow.com/questions/277224/how-do-i-catch-a-php-fatal-error
**/
function fimScript() {
	$erro = error_get_last();
	if ($erro !== NULL) {
		$tipo = $erro["type"];
		$arquivo = $erro["file"];
		$linha = $erro["line"];
		$mensagem = $erro["message"];
		if ($arquivo != __FILE__)
			return; //apenas erros deste arquivos sao registrados
		logar("Erro do tipo '$tipo' encontrado na linha '$linha' do arquivo '$arquivo'. Informacoes: $mensagem");
	}
}

/*************************************
* VARIÁVEIS
**************************************/
$objetoNfe = new ToolsNFePHP('', 1, false);
try {
	$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco", "$bdUsuario", "$bdSenha");
} catch (PDOException $e) {
	logar("[erro 001] ao conectar ao banco de dados: " . $e->getMessage());
	sair();
}
$objetoDOM = new DOMDocument();
$modSOAP = '2'; //usando cURL
/* 1 = producao
2 = homologacao */
$tpAmb = '1';
/*
pagina 11 da nota técnica 2012/002
Indicador de NF-e consultada:
0=Todas as NF-e;
1=Somente as NF-e que ainda não tiveram manifestação do
destinatário (Desconhecimento da operação, Operação não
Realizada ou Confirmação da Operação);
2=Idem anterior, incluindo as NF-e que também não tiveram a
Ciência da Operação.
 */
$indNFe = '0';
/*
Indicador do Emissor da NF-e:
0=Todos os Emitentes / Remetentes;
1=Somente as NF-e emitidas por emissores / remetentes que
não tenham o mesmo CNPJ-Base do destinatário (para excluir
as notas fiscais de transferência entre filiais).
pagina 11 da nota técnica 2012/002 */
$indEmi = '0';
/* Último NSU recebido pela Empresa
Caso seja informado com zero, ou com um NSU muito antigo, a
consulta retornará unicamente as notas fiscais que tenham sido
recepcionadas nos últimos 15 dias.
pagina 11 da nota técnica 2012/002
Caso o ultNSU seja informado com 0 (zero), o WS fará a consulta a partir da primeira nota fiscal
recepcionada há menos de 15 dias ou outro prazo maior que a UF entender conveniente;
IMPORTANTE: O campo ultNSU representa a numeração única da NF-e dentro do ambiente que
estásendoconsultado.Seestiversendoconsultado oAN,esteseráovalordoNSU_RFB.Se
tiver sendo consulta uma determinada SEFAZ, este será o valor do NSU_SEFAZ_XX.
pagina 13 da nota técnica 2012/002 */
/*Se o NSU nao for especificado a lib nfePHP irá pegar o ultimo valor armazenada em config/numNSU.xml*/
/*
$buscaUltimoNSU = $objetoPDO->query("SELECT max(nsu) AS ultimo FROM nota_fiscal_destinada")->fetch();
if (empty($buscaUltimoNSU['ultimo'])) $ultNSU = 0;
else $ultNSU = $buscaUltimoNSU['ultimo'];
 */
$ultNSU = 0;
/* usa ambiente Nacional para buscar a lista de NFe, FALSE usa sua própria SEFAZ */
$AN = true;

$retorno = array();
$numeroItensInseridos = 0;
$numeroIteracoes = 0;
$continuacao = 1;
/* * ***********************************
 * CONSULTA NFEs
 * ************************************ */
while ($continuacao == 1) {
	$numeroIteracoes++;
	if ($numeroIteracoes > 300) {
		// O retorno pode vir vazio mas o indicador de continuação está presente,
		// tento pegar o maximo de registros possiveis, consultando varias vezes
		logar("[alerta] script encerrado devido a varias requisicoes ja feitas");
		$continuacao = 0;
		continue;
	}
	/* (pag 14) Amensagem será descartada se o tamanho exceder o limite previsto(10KB). */
	if (!$xml = $objetoNfe->getListNFe($AN, $indNFe, $indEmi, $ultNSU, $tpAmb, $modSOAP, $retorno)) {
		logar("[erro 001] durante a obtencao da listagem das notas fiscais. Detalhes: " . $objetoNfe->errMsg);
		sair();
	}
	// Começa na pagina 11 da nota técnica 2012/002
	$objetoDOM->loadXML($xml);

	if (!is_object($objetoDOM->getElementsByTagName('cStat'))) {
		logar("[erro 002] resposta nao contem o elemento cStat");
		$continuacao = 0;
		continue;
	}
	
	/* pagina 12 da nota técnica 2012/002
	Indicador de continuação:
	0=SEFAZ não possui mais documentos para o CNPJ informado;
	1=SEFAZ possui mais documentos para o CNPJ informado, ou
	ainda não avaliou a totalidade da sua base de dados. */
	$continuacao = $objetoDOM->getElementsByTagName('indCont')->item(0)->nodeValue;
	
	if ($objetoDOM->getElementsByTagName('cStat')->item(0)->nodeValue != '138') {
		//logar("nao ha resultados");
		//$continuacao = 0;
		continue;
	}
	
	// Conjunto de informações resumo da NF-e, Cancelamento e CCe localizadas 
	$retornos = $objetoDOM->getElementsByTagName('ret');
	foreach ($retornos as $retorno) { //maximo de 50 registros
		if (is_object($retorno->getElementsByTagName('resNFe'))) { //evento é uma nfe
			$nomeObjetoRetorno = 'resNFe';
			$objetoRetorno = $retorno->getElementsByTagName('resNFe');
			$tipoRegistro = 1;
		} elseif (is_object($retorno->getElementsByTagName('resCanc'))) { //evento é um cancelamento
			$nomeObjetoRetorno = 'resCanc';
			$objetoRetorno = $retorno->getElementsByTagName('resCanc');
			$tipoRegistro = 2;
		} elseif (is_object($retorno->getElementsByTagName('resCCe'))) { //evento é uma carta de correção
			$nomeObjetoRetorno = 'resCCe';
			$objetoRetorno = $retorno->getElementsByTagName('resCCe');
			$tipoRegistro = 3;
		}
		// para cada resnfe, ou resscan ou rescce
		foreach ($objetoRetorno as $retorno2) {
			$dataAtual = date('Y-m-d');
			$horaAtual = date('G:i:s');

			// estes atributos vem em todos os tipos de retorno
			$nsuAtual = $retorno2->getAttribute('NSU');
			// (pag 17) a chave nao virá se o ambiente nao for o nacional e a UF do destinatario nao for a do emitente
			$chaveNota = $retorno2->getElementsByTagName('chNFe')->item(0)->nodeValue;
			//Tipo de Operação da NF-e: 0=Entrada; 1=Saída
			$tipoOperacao = $retorno2->getElementsByTagName('tpNF')->item(0)->nodeValue;

			//os atributos abaixo vem apenas em notas fsicais e cancelamentos de notas fiscais
			if ($nomeObjetoRetorno == 'resNFe' || $nomeObjetoRetorno == 'resCanc') {
				if (empty($retorno2->getElementsByTagName('CNPJ')->item(0)->nodeValue)) {
					if (empty($retorno2->getElementsByTagName('CPF')->item(0)->nodeValue)) {
						logar("[alerta 003] nao foi possivel extrair CNPJ ou CPF da nota fiscal cuja chave eh '$chaveNota'");
					} else $cnpjCpfEmitente = $retorno2->getElementsByTagName('CPF')->item(0)->nodeValue;
				} else $cnpjCpfEmitente = $retorno2->getElementsByTagName('CNPJ')->item(0)->nodeValue;
				$nomeEmitente = utf8_decode($retorno2->getElementsByTagName('xNome')->item(0)->nodeValue);
				$dataEmissao = $retorno2->getElementsByTagName('dEmi')->item(0)->nodeValue;
				$valorNfe = $retorno2->getElementsByTagName('vNF')->item(0)->nodeValue;
				/* Situação da NF-e: 
				1=Uso autorizado no momento da consulta;
				2=Uso denegado;
				3=NF-e cancelada; */
				$situacaoNfe = $retorno2->getElementsByTagName('cSitNFe')->item(0)->nodeValue;
				/* Situação da Manifestação do Destinatário: 
				0=Sem Manifestação do Destinatário;
				1=Confirmada Operação;
				2=Desconhecida;
				3=Operação não Realizada;
				4=Ciência. */
				$situacaoManifesto = $retorno2->getElementsByTagName('cSitConf')->item(0)->nodeValue;

				$queryInserir = "INSERT INTO nota_fiscal_destinada
(data_importacao,hora_importacao,tipo_registro,nsu,chave,cnpj_cpf_emitente,nome_emitente,data_emissao,tipo_operacao,valor,situacao_nota,situacao_manifesto)
VALUES('$dataAtual','$horaAtual','$tipoRegistro','$nsuAtual','$chaveNota','$cnpjCpfEmitente','$nomeEmitente','$dataEmissao','$tipoOperacao','$valorNfe','$situacaoNfe','$situacaoManifesto')";
			}
			elseif ($nomeObjetoRetorno == 'resCCe') {
				// este atributos vem apenas em cartas de correção
				$dataEvento = $retorno2->getElementsByTagName('dhEvento')->item(0)->nodeValue;
				preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/', $dataEvento, $retornosDataEvento);
				$dataEvento = $retornosDataEvento[0];

				$queryInserir = "INSERT INTO nota_fiscal_destinada
(data_importacao,hora_importacao,tipo_registro,nsu,chave,data_emissao,tipo_operacao)
VALUES ('$dataAtual','$horaAtual','$tipoRegistro','$nsuAtual','$chaveNota','$dataEvento','$tipoOperacao')";
			}

			$pesquisaNSUjaInserido = $objetoPDO->query("SELECT id FROM nota_fiscal_destinada WHERE nsu = '$nsuAtual'");
			if ($pesquisaNSUjaInserido->rowCount() > 0) continue;

			try {
				if (!$objetoPDO->query($queryInserir)) {
					$e = $objetoPDO->errorInfo();
					logar("[erro 004] ao inserir dados no banco de dados. Detalhes: " . $e[0] . " " . $e[1] . " " . $e[2]);
					continue;
				}
			} catch (PDOException $e) {
				logar("[erro 005] ao inserir valores no banco de dados. Detalhes: " . $e->getMessage());
				continue;
			}
			
			$numeroItensInseridos++;
		} // fim do loop para cada resnfe, ou resscan ou rescce
	} //fim do loop para cada retorno
	//"tem de haver um intervalo de tempo entre cada pesquisa caso contrario o
	//webservice pode parar de responder, considerando ou um excesso de consultas
	//ou um ataque DoS"
	sleep(5);
} // fim do while de continuação

if ($numeroItensInseridos > 0) {
	logar("$numeroItensInseridos notas fiscais destinadas foram inseridas no banco de dados");
}

/* * ****************************************
 * VERIFICA QUAIS XMLs JÁ ESTÃO NO SISTEMA
 * ***************************************** */

$buscaNFeSemXML = $objetoPDO->query("SELECT id, chave FROM nota_fiscal_destinada WHERE xml_esta_no_sistema = false");
while ($NFeSemXML = $buscaNFeSemXML->fetch()) {
	$buscaXMLNFe	= $objetoPDO->query("SELECT id FROM nota_fiscal WHERE chave_nota = '{$NFeSemXML['chave']}'");
	$buscaXMLCTe	= $objetoPDO->query("SELECT id FROM conhecimento_transporte WHERE chave_conhecimento = '{$NFeSemXML['chave']}'");
	$buscaXMLCCe	= $objetoPDO->query("SELECT id FROM carta_correcao WHERE chave_nota = '{$NFeSemXML['chave']}'");
	$buscaXMLCanc	= $objetoPDO->query("SELECT id FROM cancelamento WHERE chave_nota = '{$NFeSemXML['chave']}'");
	if ( ($buscaXMLNFe->rowCount() > 0) || ($buscaXMLCTe->rowCount() > 0) || ($buscaXMLCCe->rowCount() > 0) || ($buscaXMLCanc->rowCount() > 0) ) {
		if (!$objetoPDO->exec("UPDATE nota_fiscal_destinada SET xml_esta_no_sistema = true WHERE id = '{$NFeSemXML['id']}'")) {
			logar("[erro ] nenhum registro de nota fiscal destinada foi atualizado. ID do registro: {$NFeSemXML['id']}");
		}
	}
}
?>