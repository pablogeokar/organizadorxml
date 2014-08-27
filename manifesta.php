<?php

require_once('libs/nfephp/libs/ToolsNFePHP.class.php');
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.php');

$modSOAP = '2'; //usando cURL
/* 1 = producao
2 = homologacao */
$tpAmb = '1';

$tipoEvento = 210210;
$justificativaEvento = '';


$objetoNfe = new ToolsNFePHP('', 1, false);

try {
	$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco","$bdUsuario","$bdSenha");
} catch (PDOException $e) {
	logar("[erro 011] ao conectar ao banco de dados: ". $e->getMessage());
	sair(11);
}

$buscaNotasEmitidas = $objetoPDO->query("SELECT * FROM nota_fiscal_destinada WHERE
	data_emissao >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)
	AND xml_esta_no_sistema = 0");
while ($notaEmitida = $buscaNotasEmitidas->fetch()) {
	$objetoNfe->errMsg = null; // zera as mensagens de erro, caso existam
	$manisfesta = $objetoNfe->manifDest($notaEmitida['chave'], $tipoEvento, $justificativaEvento, $tpAmb, $modSOAP);
	if (!$manisfesta) {
		logar("Falha ao manifestar a nota com a chave \"{$notaEmitida['chave']}\". Detalhes: ".rtrim($objetoNfe->errMsg));
	}
	else {
		$i = $objetoPDO->query("INSERT INTO manifestacoes (chave, tipo_evento, justificativa, ambiente)
		VALUES('{$notaEmitida['chave']}', '$tipoEvento', '$justificativaEvento', '$tpAmb');");
		if (! $i) {
			$e = $objetoPDO->errorInfo();
			logar("Erro ao inserir dados da nota \"{$notaEmitida['chave']}\" manifestada. Detalhes: ".$e[0]." ".$e[1]." ".$e[2].".");
		}
		//logar("Nota \"$chaveNota\" manifestada");
	}
}

logar("fim");