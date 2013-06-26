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
$resultados = 0;
$dataInicialImportacao = null;
$dataFinalImportacao = null;
$nomeArquivo = null;
if (isset($_GET['data_inicial_importacao']) || isset($_GET['data_final_importacao']) || isset($_GET['nome_arquivo'])) {
	if (isset($_GET['data_inicial_importacao'])) $dataInicialImportacao = date('Y-m-d',strtotime($_GET['data_inicial_importacao']));
	if (isset($_GET['data_final_importacao'])) $dataFinalImportacao = date('Y-m-d',strtotime($_GET['data_final_importacao']));
	if (isset($_GET['nome_arquivo'])) $nomeArquivo = filter_input(INPUT_GET,'nome_arquivo',FILTER_SANITIZE_STRING);
	$campos = array();
	if ( (isset($dataInicialImportacao)) && ($dataInicialImportacao == '1969-12-31')) $dataInicialImportacao = null;
	if ( (isset($dataFinalImportacao)) && ($dataFinalImportacao == '1969-12-31')) $dataFinalImportacao = null;
	if (isset($dataInicialImportacao) && !empty($dataInicialImportacao) ) $campos += array('data_importacao >=' => $dataInicialImportacao);
	if (isset($dataFinalImportacao) && !empty($dataFinalImportacao) ) $campos += array('data_importacao <=' => $dataFinalImportacao);
	if (isset($nomeArquivo) && !empty($nomeArquivo)) $campos += array("nome_arquivo LIKE" => "%".$nomeArquivo."%");
	$queryBuscar = "SELECT * FROM nao_identificado WHERE 1";
	if (empty($campos)) $queryBuscar = $queryBuscar . "=2";
	foreach ($campos as $campo => $valor) {
		$nomeCampo = explode(' ', $campo);
		$queryBuscar = $queryBuscar . " AND $nomeCampo[0] $nomeCampo[1] '$valor'";
	}
	try {
		$resultados = $objetoPDO->query($queryBuscar);
	} catch (PDOException $e) {
		$erro = "Erro ao realizar busca. Detalhes: " . $e->getMessage();
	}
}
?>
<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<meta charset="utf-8">
		<title>Pesquisar arquivos não identificados</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="organizador xml">
		<meta name="author" content="Tobias<tobiasette@gmail.com>">

		<link href="libs/bootstrap/css/bootstrap.css" rel="stylesheet">
		<link href="libs/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
		<link href="libs/css/css.css" rel="stylesheet">
		<link href="libs/datepicker/css/datepicker.css" rel="stylesheet">

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
					<li class="active dropdown">
						<a data-toggle="dropdown" class="dropdown-toggle" href="#">Pesquisar <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="pesquisarNfe.php">Nota fiscal</a></li>
							<li><a href="pesquisarCte.php">Conhecimento de transporte</a></li>
							<li><a href="pesquisarCce.php">Carta de correção</a></li>
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

			<?php if ((isset($erro)) && (!empty($erro))) die($erro); ?>

			<form name="pesquisar" id="pesquisar" class="form-inline" method="get">
				<legend>Pesquisar arquivos xml não identificados</legend>

				<p>
				<div class="input-append date div_data" id="div_data_inicial">
					<input type="text" class="span3" readonly="" name="data_inicial_importacao" placeholder="Data inicial da importacao">
					<span class="add-on"><i class="icon-calendar"></i></span>
				</div>

				<div class="input-append date div_data" id="div_data_final">
					<input type="text" class="span3" readonly="" name="data_final_importacao" placeholder="Data final da importacao">
					<span class="add-on"><i class="icon-calendar"></i></span>
				</div>
				</p>

				<p>
					<input type="text" class="span6" name="nome_arquivo" placeholder="Nome, completo ou não, do arquivo">
				</p>

				<div class="form-actions">
					<button type="submit" class="btn btn-primary" name="enviar">Pesquisar</button>
				</div>
			</form>

			<hr>

			<div class="row-fluid">
				<?php if ((is_object($resultados)) && ($resultados->rowCount() > 0)) : ?>
					<div class="alert alert-success">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong><?php print $resultados->rowCount(); ?></strong> resultados encontrados.
					</div>
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Data importação</th>
								<th>Hora importação</th>
								<th>Nome do arquivo</th>
								<th colspan="2">Opções</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($resultados as $resultado) {
								print "<tr>";
								print "<td>" . date('d/m/Y', strtotime($resultado['data_importacao'])) . "</td>";
								print "<td>" . $resultado['hora_importacao'] . "</td>";
								print "<td>" . $resultado['nome_arquivo'] . "</td>";
								print "<td><a alt='Download' title='Download' href=download.php?tipo=naoidentificado&id=" . $resultado['id'] . "><i class='icon-download-alt'</i></a>";
								print "<td><a alt='Deletar' title='Deletar' href=deletar.php?tipo=naoidentificado&id=" . $resultado['id'] . "><i class='icon-remove'</i></a>";
								print "</tr>";
							}
							?>
						</tbody>
					</table>
					<hr>
				<?php print "<a class='btn btn-success' href='download.php?tipo=naoidentificado&
data_inicial_importacao=$dataInicialImportacao&data_final_importacao=$dataFinalImportacao
&nome_arquivo=$nomeArquivo'
>Download de todos os xmls desta pesquisa</a>"; ?>
				<?php elseif ((is_object($resultados)) && ($resultados->rowCount() == 0)) : ?>
					<div class="alert alert-error">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<strong>Nenhum</strong> resultado encontrado.
					</div>
				<?php endif; ?>
			</div>

			<hr>

			<div class="footer">
				<p>Gerado em: <?php print date('d/m/Y G:i:s'); ?></p>
			</div>

		</div> <!-- /container -->

		<script src="libs/bootstrap/js/jquery.js"></script>
		<script src="libs/bootstrap/js/bootstrap.min.js"></script>
		<script src="libs/datepicker/js/bootstrap-datepicker.js"></script>
		<script src="libs/datepicker/js/locales/bootstrap-datepicker.pt-BR.js"></script>
		<script type="text/javascript">
			$('.div_data').datepicker({
				format: 'yyyy-mm-dd',
				language: 'pt-BR',
				autoclose: true,
			});
			$(".alert").alert();
		</script>

	</body>
</html>