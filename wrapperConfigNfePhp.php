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

require(dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "util.php");

// define os valores das variaveis do arquivo config.php do nfephp
$empresa = $nomeEmpresa;
$UF = $siglaUf;
$cUF = $codigoUf;
$cnpj = $cnpjCpfEmpresa;
$certName = $nomeCertificado;
$keyPass = $senhaCertificado;
$passPhrase = $senhaDecriptacaoChave;
$arquivosDir = $diretorioArquivosNfe;
$arquivosDirCTe = $diretorioArquivosCte;

if (!file_exists($arquivosDir)) {
	if (!mkdir($arquivosDir, 0777)) {
		logar("erro: '$arquivosDir' nao existe e nao foi possivel cria-lo");
	}
}
if (!file_exists($arquivosDirCTe)) {
	if (!mkdir($arquivosDirCTe, 0777)) {
		logar("erro: '$arquivosDirCTe' nao existe e nao foi possivel cria-lo");
	}
}
if ((!is_dir($arquivosDir)) || (!is_readable($arquivosDir))) {
	logar("erro: '$arquivosDir' nao eh um diretorio ou nao tem permissao de escrita");
}
if ((!is_dir($arquivosDirCTe)) || (!is_readable($arquivosDirCTe))) {
	logar("erro: '$arquivosDirCTe' nao eh um diretorio ou nao tem permissao de escrita");
}