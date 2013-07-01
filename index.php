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
try {
	$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco", "$bdUsuario", "$bdSenha");
} catch (PDOException $e) {
	$erro = "Erro ao conectar ao banco de dados: " . $e->getMessage();
}
if ((!isset($modoOperacao)) || (is_null($modoOperacao)) || (!is_numeric($modoOperacao)) || ($modoOperacao < 1) || ($modoOperacao > 3) || ($modoOperacao == 1)) {
	die("Modo de operacao invalido");
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
					<li class="active"><a href="index.php">Início</a></li>
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
					<li class="dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#">Utilitários <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="importar.php">Importação manual</a></li>
							<li><a href="nfesEmitidas.php">Organização manual de NFes emitidas</a></li>
							<li><a href="consultarCadastro.php">Consultar cadastro</a></li>
						</ul>
					</li>
					<li><a href="login.php?msg=logout">Sair</a></li>
				</ul>
				<h3 class="muted">Organizador XML</h3>
			</div>

			<hr>

			<?php if ((isset($erro)) && (!empty($erro))) die($erro); ?>

			<div class="jumbotron">
				<h1>Organizador de arquivos xml</h1>
				<p class="lead">Organiza arquivos de nota fiscal, carta de correção, cancelamento e conhecimento de transporte</p>
				<a class="btn btn-large btn-success" href="pesquisarNfe.php">Pesquisar NFe</a>
				<a class="btn btn-large btn-danger" href="pesquisarCancelamento.php">Pesquisar Cancelamento</a>
				<a class="btn btn-large btn-warning" href="pesquisarCce.php">Pesquisar CCe</a>
				<a class="btn btn-large btn-info" href="pesquisarCte.php">Pesquisar CTe</a>
			</div>

			<hr>

			<div class="row-fluid marketing">
				<div class="span6">
					<h4>Nota fiscal</h4>
					<?php
					$consulta = $objetoPDO->query("SELECT COUNT(id) FROM nota_fiscal");
					if (!$consulta) $mensagem = "Oooops! Ocorreu um erro ao realizar a consulta ao banco de dados";
					else {
						$r = $consulta->fetch(PDO::FETCH_ASSOC);
						if ($r['COUNT(id)'] == 0) $mensagem = "Não há notas fiscais armazenadas.";
						else	$mensagem = "Há " . $r['COUNT(id)'] . " nota(s) fiscal(is) armazenada(s).";
					}
					?>
					<p><?php print $mensagem; ?></p>

					<h4>Carta de correção</h4>
					<?php
					$consulta = $objetoPDO->query("SELECT COUNT(id) FROM carta_correcao");
					if (!$consulta) $mensagem = "Oooops! Ocorreu um erro ao realizar a consulta ao banco de dados";
					else {
						$r = $consulta->fetch(PDO::FETCH_ASSOC);
						if ($r['COUNT(id)'] == 0) $mensagem = "Não há cartas de correção armazenadas.";
						else	$mensagem = "Há " . $r['COUNT(id)'] . " carta(s) de correção armazenada(s).";
					}
					?>
					<p><?php print $mensagem; ?></p>
					
					<h4>Cancelamento</h4>
					<?php
					$consulta = $objetoPDO->query("SELECT COUNT(id) FROM cancelamento");
					if (!$consulta) $mensagem = "Oooops! Ocorreu um erro ao realizar a consulta ao banco de dados";
					else {
						$r = $consulta->fetch(PDO::FETCH_ASSOC);
						if ($r['COUNT(id)'] == 0) $mensagem = "Não há cancelamentos armazenados.";
						else	$mensagem = "Há " . $r['COUNT(id)'] . " cancelamento(s) armazenado(s).";
					}
					?>
					<p><?php print $mensagem; ?></p>

					<h4>Conhecimento de transporte</h4>
					<?php
					$consulta = $objetoPDO->query("SELECT COUNT(id) FROM conhecimento_transporte");
					if (!$consulta) $mensagem = "Oooops! Ocorreu um erro ao realizar a consulta ao banco de dados";
					else {
						$r = $consulta->fetch(PDO::FETCH_ASSOC);
						if ($r['COUNT(id)'] == 0) $mensagem = "Não há conhecimentos de transporte armazenados.";
						else $mensagem = "Há " . $r['COUNT(id)'] . " conhecimento(s) de transporte armazenado(s).";
					}
					?>
					<p><?php print $mensagem; ?></p>
				</div>

				<div class="span6">

					<h4>Notas fiscais emitidas</h4>
					<?php
					$consulta = $objetoPDO->query("SELECT COUNT(id) FROM nota_fiscal_destinada WHERE xml_esta_no_sistema = false");
					if (!$consulta) $mensagem = "Oooops! Ocorreu um erro ao realizar a consulta ao banco de dados";
					else {
						$r = $consulta->fetch(PDO::FETCH_ASSOC);
						if ($r['COUNT(id)'] == 0) $mensagem = "Não há arquivos XML faltando para as notas fiscais emitidas para esta empresa.";
						else {
							$mensagem = "<a href='pesquisarNfesEmitidas.php?xml_esta_no_sistema=nao'>
							Faltam " . $r['COUNT(id)'] . " arquivo(s) XML de notas emitidas para esta empresa. <span class='label label-important'>Verifique!</span>
							</a>";
						}
					}
					?>
					<p><?php print $mensagem; ?></p>

					<h4>XMLs não identificados</h4>
					<?php
					$consulta = $objetoPDO->query("SELECT COUNT(id) FROM nao_identificado");
					if (!$consulta) $mensagem = "Oooops! Ocorreu um erro ao realizar a consulta ao banco de dados";
					else {
						$r = $consulta->fetch(PDO::FETCH_ASSOC);
						if ($r['COUNT(id)'] == 0) $mensagem = "Não há arquivos xmls não identificados.";
						else {
							$consulta2 = $objetoPDO->query("SELECT min(data_importacao) AS data_minima, max(data_importacao) AS data_maxima FROM nao_identificado");
							$r2 = $consulta2->fetch(PDO::FETCH_ASSOC);
							$mensagem = "<a href='pesquisarNaoIdentificados.php?data_inicial_importacao=" . $r2['data_minima'] . "&data_final_importacao=" . $r2['data_maxima'] . "'>
							Há " . $r['COUNT(id)'] . " arquivo(s) xml não identificado(s). <span class='label label-important'>Verifique!</span>
							</a>";
						}
					}
					?>
					<p><?php print $mensagem; ?></p>

					<h4>XMLs não validados</h4>
					<?php
					$consulta = $objetoPDO->query("SELECT COUNT(id) FROM xml_invalido");
					if (!$consulta) $mensagem = "Oooops! Ocorreu um erro ao realizar a consulta ao banco de dados";
					else {
						$r = $consulta->fetch(PDO::FETCH_ASSOC);
						if ($r['COUNT(id)'] == 0) $mensagem = "Não há arquivos xmls inválidos.";
						else {
							$consulta2 = $objetoPDO->query("SELECT min(data_registro) AS data_minima, max(data_registro) AS data_maxima FROM xml_invalido");
							$r2 = $consulta2->fetch(PDO::FETCH_ASSOC);
							$mensagem = "<a href='pesquisarInvalidos.php?data_inicial_registro=" . $r2['data_minima'] . "&data_final_registro=" . $r2['data_maxima'] . "'>
									Há " . $r['COUNT(id)'] . " arquivo(s) xml não validado(s) pela receita. <span class='label label-important'>Verifique!</span>
									</a>";
						}
					}
					?>
					<p><?php print $mensagem; ?></p>

					<h4>Configurações</h4>
					<p><?php print "Coletando xmls da conta '$emailUsuario'"; ?></p>
				</div>
			</div>

			<hr>

			<div class="footer">
				<p>Gerado em: <?php print date('d/m/Y G:i:s'); ?></p>
			</div>

		</div> <!-- /container -->

		<script src="libs/bootstrap/js/jquery.js"></script>
		<script src="libs/bootstrap/js/bootstrap.min.js"></script>

	</body>
</html>