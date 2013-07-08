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
$pathScript = obtemPathScript();
if ((!isset($modoLogin)) || (empty($modoLogin)) || ((strtolower($modoLogin) != 'ldap') && (strtolower($modoLogin) != 'banco'))) {
	//
} else {
	session_start("organizadorxml");
	
	if (empty($_SESSION['usuario'])) {
		header("Location: {$pathScript}login.php?msg=login");
	}
	else {
		$segundosTimeout = 60 * 60; // 1 hora
		if (isset($_SESSION['timeout'])) {
			$tempoDecorridoSessao = time() - $_SESSION['timeout'];
			if ($tempoDecorridoSessao > $segundosTimeout) {
				session_destroy();
				header("Location: {$pathScript}login.php?msg=timeout");
			}
		}
		$_SESSION['timeout'] = time();
	}
}

if ( (!isset($logarAcessos)) || (empty($logarAcessos)) || (!is_numeric($logarAcessos)) || ($logarAcessos != 1) ) {
	//
}
else {
	// registra o acesso a pagina que incluiu esta
	try {
		$objetoPDO = new PDO("mysql:host=$bdHost;port=$bdPort;dbname=$bdNomeBanco", "$bdUsuario", "$bdSenha");
	} catch (PDOException $e) {
		logar("[erro] ao conectar ao banco de dados: " . $e->getMessage());
	}

	$dataAtual = date('Y-m-d');
	$horaAtual = date('G:i:s');
	$paginaSendoAcessada = basename($_SERVER["PHP_SELF"]);
	$parametros = addslashes($_SERVER['QUERY_STRING']);
	$userAgent = addslashes($_SERVER['HTTP_USER_AGENT']);

	$objetoPDO->query("INSERT INTO registro_acesso
		(data,hora,usuario,pagina,parametros,endereco_ip_origem,user_agent)
	VALUES('$dataAtual','$horaAtual','{$_SESSION['usuario']}','$paginaSendoAcessada','$parametros','{$_SERVER['REMOTE_ADDR']}','$userAgent')");
}