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
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "libs/nfephp/libs/ToolsNFePHP.class.php");

$uf = null;
$cnpj = null;
$ie = null;
$cpf = null;
$cnpjCpf = null;
if (isset($_REQUEST['uf'])) $uf = $_REQUEST['uf'];
if (isset($_REQUEST['cnpj'])) $cnpj = $_REQUEST['cnpj'];
if (isset($_REQUEST['ie'])) $ie = $_REQUEST['ie'];
if (isset($_REQUEST['cpf'])) $cpf = $_REQUEST['cpf'];
if (isset($_REQUEST['cnpjCpf'])) $cnpjCpf = $_REQUEST['cnpjCpf'];
$tpAmb = '1';
$modSOAP = '2';
// checagens
if (empty($uf)) $uf = $siglaUf;
if (!empty($cnpjCpf)) {
	if (strlen($cnpjCpf) == 14)
		$cnpj = $cnpjCpf;
	else if (strlen($cnpjCpf) == 11)
		$cpf = $cnpjCpf;
	else
		$erro = "CNPJ ou CPF inválido";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<meta charset="utf-8">
		<title>Organizador XML</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="organizador xml">
		<meta name="author" content="Tobias<tobiasette@gmail.com>">

		<link href="libs/bootstrap/css/bootstrap.css" rel="stylesheet">
		<link href="libs/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link href="libs/css/css.css" rel="stylesheet">

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="libs/bootstrap/js/html5shiv.js"></script>
		<![endif]-->

		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="libs/bootstrap/ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="libs/bootstrap/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="libs/bootstrap/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="libs/bootstrap/ico/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="libs/bootstrap/ico/favicon.png">
	</head>

	<body>

		<div class="container-narrow">

			<div class="masthead">
				<ul class="nav nav-pills pull-right">
					<li><a href="index.php">Início</a></li>
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#">Pesquisar <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="pesquisarNfe.php">Nota fiscal</a></li>
							<li><a href="pesquisarCce.php">Carta de correção</a></li>
							<li><a href="pesquisarCancelamento.php">Cancelamento</a></li>
							<li><a href="pesquisarCte.php">Conhecimento de transporte</a></li>
							<li><a href="pesquisarNaoIdentificados.php">XMLs não identificados</a></li>
							<li><a href="pesquisarInvalidos.php">XMLs não validados</a></li>
							<li><a href="pesquisarNfesEmitidas.php">Notas fiscais emitidas para esta empresa</a></li>
						</ul>
					</li>
					<li class="active" class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#">Utilitários <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="importacao.php">Importação manual</a></li>
							<li><a href="consultarCadastro.php">Consultar cadastro</a></li>
						</ul>
					</li>
					<li><a href="login.php?msg=logout">Sair</a></li>
				</ul>
				<h3 class="muted">Organizador XML</h3>
			</div>

			<hr>

			<form name="consultar" id="consultar" class="form-inline" method="post">
				<legend>Consultar cadastro</legend>
				<p>
					<label class="span1">CNPJ:</label>
					<input type="text" class="span5" name="cnpj">
				</p>

				<p>
					<label class="span1">I.E.:</label>
					<input type="text" class="span5" name="ie">
				</p>

				<p>
					<label class="span1">CPF:</label>
					<input type="text" class="span5" name="cpf">
				</p>

				<p>
					<label class="span1">Estado</label>
					<select name="uf">
						<option value="">Selecione</option>
						<option value="AC">Acre</option>
						<option value="AL">Alagoas</option>
						<option value="AM">Amazonas</option>
						<option value="AP">Amapá</option>
						<option value="BA">Bahia</option>
						<option value="CE">Ceará</option>
						<option value="DF">Distrito Federal</option>
						<option value="ES">Espirito Santo</option>
						<option value="GO">Goiás</option>
						<option value="MA">Maranhão</option>
						<option value="MG">Minas Gerais</option>
						<option value="MS">Mato Grosso do Sul</option>
						<option value="MT">Mato Grosso</option>
						<option value="PA">Pará</option>
						<option value="PB">Paraíba</option>
						<option value="PE">Pernambuco</option>
						<option value="PI">Piauí</option>
						<option value="PR">Paraná</option>
						<option value="RJ">Rio de Janeiro</option>
						<option value="RN">Rio Grande do Norte</option>
						<option value="RO">Rondônia</option>
						<option value="RR">Roraima</option>
						<option value="RS">Rio Grande do Sul</option>
						<option value="SC">Santa Catarina</option>
						<option value="SE">Sergipe</option>
						<option value="SP">São Paulo</option>
						<option value="TO">Tocantins</option>
					</select>

				</p>

				<div class="form-actions">
					<button type="submit" class="btn btn-primary" name="enviar">Consultar</button>
				</div>
			</form>

			<hr>

			<div class="row-fluid marketing">
				<?php
				if ((isset($erro)) && (!empty($erro))) {
					die('<div class="alert alert-error">' . $erro . '</div>');
				}
				$nfe = new ToolsNFePHP('', 1);

				if ((isset($cnpj)) || (isset($ie)) || (isset($cpf))) {
					if ($resposta = $nfe->consultaCadastro($uf, $cnpj, $ie, $cpf, $tpAmb, $modSOAP)) {
						print '<div class="alert alert-success">';
						print '<button type="button" class="close" data-dismiss="alert">&times;</button>';
						print $resposta['xMotivo'];
						print '</div>';
						foreach ($resposta['dados'] as $item) {
							print "<span class='label label-success'>Nome: </span>" . $item['xNome'];
							print "<br/><span class='label label-success'>CNPJ:</span>" . $item['CNPJ'];
							print "<br/><span class='label label-success'>I.E.: </span>" . $item['IE'];
							print "<br/><span class='label label-success'>Endereço: </span>" . $item['xLgr'] . " " . $item['nro'] . " " . $item['xCpl'] . " " . $item['xBairro'] . " " . $item['xMun'] . " " . $item['CEP'];
							print "<br/><span class='label label-success'>Situação: </span>" . $item['cSit'];
							print "<br/><span class='label label-success'>CNAE: </span>" . $item['CNAE'];
							print "<br/><span class='label label-success'>Regime de apuração: </span>" . $item['xRegApur'];
							print "<hr>";
						}
					} else {
						print '<div class="alert alert-error">';
						print '<button type="button" class="close" data-dismiss="alert">&times;</button>';
						print "Houve um erro: " . $nfe->errMsg;
						print '</div>';
					}
				}
				?>

			</div>

			<div class="footer">
				<p>Gerado em: <?php print date('d/m/Y G:i:s'); ?></p>
			</div>

		</div> <!-- /container -->

		<script src="libs/bootstrap/js/jquery.js"></script>
		<script src="libs/bootstrap/js/bootstrap.min.js"></script>

	</body>
</html>