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
?>
<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<meta charset="utf-8">
		<title>Organizador XML - importação manual</title>
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

			<legend>Importação manual</legend>
			<textarea rows="10" class="span6"><?php
				print date('d-m-Y_H:i:s ') . "início\n";
				ob_flush();
				flush();
				require_once("organizaXML.php");
				print date('d-m-Y_H:i:s ') . "fim\n";
				?>
			</textarea>

			<hr>

			<div class="footer">
				<p>Gerado em: <?php print date('d/m/Y G:i:s'); ?></p>
			</div>

		</div> <!-- /container -->

		<script src="libs/bootstrap/js/jquery.js"></script>
		<script src="libs/bootstrap/js/bootstrap.min.js"></script>

	</body>
</html>