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
Neste arquivo ficam funções utilizadas em mais de um script
**/

/**
Termina o script com o codigo especificado por $codigo
exit usa codigos até 255, sendo que este ultimo é reservado ao PHP
**/
function sair($codigo = 254) {
	exit($codigo);
}

/**
Envia $string para o arquivo de log e para a saida padrao
**/
function logar($string) {
	global $arquivoLog;
	$arquivo = basename($_SERVER["PHP_SELF"]);
	$saida = date('d-m-Y_H:i:s ') . "[$arquivo] " . $string . "\r\n";
	print ($saida);
	file_put_contents($arquivoLog, $saida, FILE_APPEND);
}

/**
Deleta um diretorio recursivamente
http://www.php.net/manual/pt_BR/function.rmdir.php#110489
**/
function delTree($dir) {
	$files = array_diff(scandir($dir), array('.', '..'));
	foreach ($files as $file) {
		(is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
	}
	return rmdir($dir);
}

function obtemPathScript() {
	// http://www.php.net/manual/pt_BR/reserved.variables.server.php#111396
	$caminho = $_SERVER['PHP_SELF'];
	$caminhoPartes = explode('/', $caminho);
	$nomeScript = end($caminhoPartes);
	$pathScript = str_replace($nomeScript, '', $caminho);
	return $pathScript;
}