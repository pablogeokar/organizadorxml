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

// ldap_bind retorna warning caso o bind nao seja realizado
error_reporting(0);

$retornoLogin = 0;

if (!extension_loaded('ldap')) {
	logar("[erro] modulo ldap nao carregado");
	sair();
}

if (!$conexaoLdap = ldap_connect($ldapServidor, $ldapPorta)) {
	logar("[erro] ao conectar ao servidor LDAP. Detalhes: " . ldap_error($conexaoLdap));
} else {
	ldap_set_option($conexaoLdap, LDAP_OPT_PROTOCOL_VERSION, 3);
	$usuarioLDAP = "{$ldapIdentificadorUsuario}=$usuarioLogin,$ldapDN";
	$bind = ldap_bind($conexaoLdap, $usuarioLDAP, $senhaLogin);
	if ($bind)
		$retornoLogin = 1;
	ldap_close($conexaoLdap);
}