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
	session_cache_expire(120);
	session_start("organizadorxml");
	if (empty($_SESSION['usuario'])) {
		header("Location: {$pathScript}login.php?msg=valores");
	}
}