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
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "util.php");

try {
	$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco", "$bdUsuario", "$bdSenha");
} catch (PDOException $e) {
	$erro = "Erro ao conectar ao banco de dados: " . $e->getMessage();
}

if ((!isset($modoOperacao)) || (is_null($modoOperacao)) || (!is_numeric($modoOperacao)) || ($modoOperacao < 1) || ($modoOperacao > 3) || ($modoOperacao == 1)) {
	die("Modo de operacao invalido");
}

$usuarioLogin = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
$senhaLogin = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);
$mensagemLogin = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_STRING);

switch ($mensagemLogin) {
	case 'login':
		$tipoMensagem = "error";
		$mensagem = "É necessário fazer login";
		break;
	
	case 'timeout':
		$tipoMensagem = "error";
		$mensagem = "Tempo de sessão esgotado";
		break;

	case 'logout':
		session_start("organizadorxml");
		if (! empty($_SESSION['usuario'])) {
			session_destroy();
			unset($_SESSION);
			$tipoMensagem = "success";
			$mensagem = "Logout efetuado com sucesso";
			break;
		}
}

if ((isset($_POST['entrar'])) && (!empty($usuarioLogin)) && (!empty($senhaLogin))) {
	if ((!isset($modoLogin)) || (empty($modoLogin)) || ((strtolower($modoLogin) != 'ldap') && (strtolower($modoLogin) != 'banco'))) {
		$tipoMensagem = "error";
		$mensagem = "O sistema não está configurado para utilizar login";
	} else {
		$retornoLogin = 0;
		if (strtolower($modoLogin) == 'ldap') require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "login_ldap.php");
		elseif (strtolower($modoLogin) == 'banco') require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "login_bd.php");

		if ($retornoLogin == 1) {
			/*if (isset($_POST['lembrarLogin'])) {
				$usuarioCookie = sha1(md5("spock".$usuarioLogin));
				$senhaCookie = sha1(md5("spock".$senhaLogin));
				$tempoCookie = strtotime('7 day', time()); //cookie expira em 7 dias
				setcookie('kirkOXML', "x={$usuarioCookie}&z=${$senhaCookie}", $tempoCookie);
			}*/
			session_start("organizadorxml");
			$_SESSION['usuario'] = $usuarioLogin;
			session_regenerate_id();
			$pathScript = obtemPathScript();
			header("Location: {$pathScript}index.php");
		} else {
			$tipoMensagem = "error";
			$mensagem = "Usuário ou senha incorretos";
		}
	}
}
?>
<!DOCTYPE html>
<html lang="pt-br">
	<head>
		<meta charset="utf-8">
		<title>Organizador XML - entrar</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="organizador xml">
		<meta name="author" content="Tobias<tobiasette@gmail.com>">

		<link href="libs/bootstrap/css/bootstrap.css" rel="stylesheet">
		<style type="text/css">
			body {
				padding-top: 40px;
				padding-bottom: 40px;
				background-color: #f5f5f5;
			}

			.form-signin {
				max-width: 300px;
				padding: 19px 29px 29px;
				margin: 0 auto 20px;
				background-color: #fff;
				border: 1px solid #e5e5e5;
				-webkit-border-radius: 5px;
				-moz-border-radius: 5px;
				border-radius: 5px;
				-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
				-moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
				box-shadow: 0 1px 2px rgba(0,0,0,.05);
			}
			.form-signin .form-signin-heading,
			.form-signin .checkbox {
				margin-bottom: 10px;
			}
			.form-signin input[type="text"],
			.form-signin input[type="password"] {
				font-size: 16px;
				height: auto;
				margin-bottom: 15px;
				padding: 7px 9px;
			}

		</style>
		<link href="libs/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="libs/bootstrap/js/html5shiv.js"></script>
		<![endif]-->

		<!-- Fav and touch icons -->
		<link rel="apple-touch-icon-precomposed" sizes="144x144" href="libs/bootstrap/ico/apple-touch-icon-144-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="114x114" href="libs/bootstrap/ico/apple-touch-icon-114-precomposed.png">
		<link rel="apple-touch-icon-precomposed" sizes="72x72" href="libs/bootstrap/ico/apple-touch-icon-72-precomposed.png">
		<link rel="apple-touch-icon-precomposed" href="libs/bootstrap/ico/apple-touch-icon-57-precomposed.png">
		<link rel="shortcut icon" href="libs/bootstrap/ico/favicon.png">
	</head>

	<body>

		<div class="container">
			<form class="form-signin" method="post">
				<?php if (!empty($mensagem)) : ?>
					<div class="alert alert-<?php print $tipoMensagem; ?>">
						<button type="button" class="close" data-dismiss="alert">&times;</button>
						<?php print $mensagem; ?>
					</div>
				<?php endif; ?>
				<h2 class="form-signin-heading">Entrar</h2>
				<input type="text" class="input-block-level" name="usuario" placeholder="Usuário">
				<input type="password" class="input-block-level" name="senha" placeholder="Senha">
				<!--<label class="checkbox">
					<input type="checkbox" value="lembrarLogin"> Lembrar-me
				</label>-->
				<button class="btn btn-large btn-primary" name="entrar" type="submit">Entrar</button>
			</form>

		</div> <!-- /container -->

		<script src="libs/bootstrap/js/jquery.js"></script>
		<script src="libs/bootstrap/js/bootstrap.min.js"></script>
		<script type="text/javascript">
			$(".alert").alert();
		</script>
	</body>
</html>