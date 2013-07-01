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
if (isset($_POST) && isset($_POST['enviar'])) {
	$dataInicialLancamento =date('Y-m-d',strtotime($_POST['data_inicial_lancamento']));
	$dataFinalLancamento =date('Y-m-d',strtotime($_POST['data_final_lancamento']));
	$cnpjCpfEmitente =filter_input(INPUT_POST,'cnpj_cpf_emitente',FILTER_SANITIZE_NUMBER_INT);
	$cnpjCpfDestinatario =filter_input(INPUT_POST,'cnpj_cpf_destinatario',FILTER_SANITIZE_NUMBER_INT);
	$chaveNfe = filter_input(INPUT_POST,'chave_nfe',FILTER_SANITIZE_NUMBER_INT);
	$campos = array();
	if ( (isset($dataInicialLancamento)) && ($dataInicialLancamento == '1969-12-31')) $dataInicialLancamento = null;
	if ( (isset($dataFinalLancamento)) && ($dataFinalLancamento == '1969-12-31')) $dataFinalLancamento = null;
	if (isset($dataInicialLancamento) && !empty($dataInicialLancamento) ) $campos += array('data_emissao_carta >=' => $dataInicialLancamento);
	if (isset($dataFinalLancamento) && !empty($dataFinalLancamento) ) $campos += array('data_emissao_carta <=' => $dataFinalLancamento);
	if (isset($cnpjCpfEmitente) && !empty($cnpjCpfEmitente)) $campos += array('cnpj_cpf_emitente =' => $cnpjCpfEmitente);
	if (isset($cnpjCpfDestinatario) && !empty($cnpjCpfDestinatario)) $campos += array('cnpj_cpf_destinatario =' => $cnpjCpfDestinatario);
	if (isset($chaveNfe) && !empty($chaveNfe)) $campos += array('chave_nota =' => $chaveNfe);
	$queryBuscar = "SELECT * FROM carta_correcao WHERE 1";
	if (empty($campos))
		$queryBuscar = $queryBuscar . "=2";
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
		<title>Pesquisar carta de correção eletrônica</title>
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

			<?php if ((isset($erro)) && (!empty($erro))) die($erro); ?>

			<form name="pesquisar" id="pesquisar" class="form-inline" method="post">
				<legend>Pesquisar carta de correção</legend>

				<p>
				<div class="input-append date div_data" id="div_data_inicial">
					<input type="text" class="span3" readonly="" name="data_inicial_lancamento" placeholder="Data inicial da emissão">
					<span class="add-on"><i class="icon-calendar"></i></span>
				</div>

				<div class="input-append date div_data" id="div_data_final">
					<input type="text" class="span3" readonly="" name="data_final_lancamento" placeholder="Data final da emissão">
					<span class="add-on"><i class="icon-calendar"></i></span>
				</div>
				</p>

				<p>
					<input type="text" class="span3" name="cnpj_cpf_emitente" placeholder="CNPJ/CPF Emitente">
					<input type="text" class="span3" name="cnpj_cpf_destinatario" placeholder="CNPJ/CPF Destinatário">
				</p>

				<p>
					<input type="text" class="span6" name="chave_nfe" placeholder="Chave nfe">
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
								<th>Data emissão</th>
								<th>Chave</th>
								<th>Emitente</th>
								<th>Destinatário</th>
								<th colspan="2">Opções</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($resultados as $resultado) {
								print "<tr>";
								print "<td>" . date('d/m/Y', strtotime($resultado['data_emissao_carta'])) . "</td>";
								print "<td>" . $resultado['chave_nota'] . "</td>";
								print "<td><a href=consultarCadastro.php?cnpjCpf=" . $resultado['cnpj_cpf_emitente'] . ">" . $resultado['cnpj_cpf_emitente'] . "</a>";
								print "<td><a href=consultarCadastro.php?cnpjCpf=" . $resultado['cnpj_cpf_destinatario'] . ">" . $resultado['cnpj_cpf_destinatario'] . "</a>";
								print "<td><a alt='Download' title='Download' href=download.php?tipo=cce&id=" . $resultado['id'] . "><i class='icon-download-alt'</i></a>";
								print "<td><a alt='Imprimir' title='Imprimir' target='_blank' href=imprimir.php?tipo=cce&id=" . $resultado['id'] . "><i class='icon-print'</i></a>";
								print "</tr>";
							}
							?>
						</tbody>
					</table>
					<hr>
					<?php print "<a class='btn btn-success' href='download.php?tipo=cce
&dataInicialLancamento=$dataInicialLancamento&dataFinalLancamento=$dataFinalLancamento&cnpjCpfEmitente=$cnpjCpfEmitente
&cnpjCpfDestinatario=$cnpjCpfDestinatario&chaveNfe=$chaveNfe'
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