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

/* Define como os arquivos xmls serão armazenados. As possibilidades são:
1 - Somente no sistema de arquivos. Neste modo o banco de dados não é
    utilizado e a interface gráfica não funciona.
2 - Sistema de arquivos e Banco de dados. Neste modo os dados principais
    do xml são salvos no banco de dados, mas o xml em si é salvo no sistema
    de arquivos.
3 - Somente banco de dados. Os dados sobre o xml e o xml em si são salvos
    no banco de dados.
Caso queira trocar o modo de operação após ter importado arquivos xmls,
limpe as tabelas do banco de dados (se aplicável) e reimporte todos os
arquivos xml.
*/
$modoOperacao = 3;
/* Diretorio onde ficam os xmls a serem organizados*/
// Exemplo: $diretorioXML = 'D:\xampp\htdocs\nfe\xmls';
$diretorioXML = "/diretorio/tal";
/* Diretorio para onde irao os xmls organizados caso o modo de operação não seja 3 */
// Exemplo: $diretorioDestinoXML = 'D:\xampp\htdocs\nfe\xmls\organizados';
$diretorioDestinoXML = "/diretorio/tal/organizados";
/* Arquivo de log do script */
// Exemplo: $arquivoLog = 'D:\xampp\htdocs\nfe\xmls\organizados\organizaXML.log';
$arquivoLog = "/var/log/organizaXML.log";
/* Diretorio base onde serão criados diretórios temporarios para armazenar arquivos processados pelo script.
Os diretórios cirados são deletados ao final do script */
// Exemplo: $diretorioTemp = 'C:\Users\Usuario\AppData\Local\Temp';
$diretorioTemp = sys_get_temp_dir();

/* CNPJ ou CPF da empresa para a qual se deseja organizar os arquivos */
$cnpjCpfEmpresa = '01234567891234';

/* Dados da conta imap que será lida em busca de xmls.
Para mais informações veja http://php.net/manual/pt_BR/function.imap-open.php */
$emailStringConexao = '{mail.provedor.com.br:143/imap/novalidate-cert}INBOX';
$emailUsuario = 'usuario@provedor.com.br';
$emailSenha = 'senha';

/* Dados do banco de Dados MySQL, caso o $modoOperacao seja maior que 1 */
$bdHost = '127.0.0.1';
$bdPort = 3306;
$bdUsuario = 'usuario';
$bdSenha = 'senha';
$bdNomeBanco = 'organizaxml';

/* Método de login.
nenhum - nao utilizar login
ldap - pesquisa em uma base LDAP
banco - pesquisa em um banco de dados (MySQL) */
$modoLogin='ldap';
/* Dados para autenticação LDAP */
$ldapServidor = '127.0.0.1';
$ldapPorta = 389;
$ldapDN = "ou=Usuarios,dc=organizacao,dc=com,dc=br";
$ldapIdentificadorUsuario = "uid";
// se definido, somente usuarios deste grupo poderao logar
$ldapGrupo = 'cn=organizadorxml,ou=Grupos,dc=organizacao,dc=com,dc=br';

/* Define se o sistema irá armazenar no banco de 
dados os acessos feitos ao sistema. */
$logarAcessos = 1;

/* Timezone da aplicação, para uma lista veja: 
http://www.php.net/manual/pt_BR/timezones.america.php */
date_default_timezone_set('America/Sao_Paulo');

/* Para a lib nfephp */
//Diretório onde serão mantidos os arquivos com as NFe em xml
//a partir deste diretório serão montados todos os subdiretórios do sistema
//de manipulação e armazenamento das NFe e CTe
$diretorioArquivosNfe=$diretorioTemp.DIRECTORY_SEPARATOR."nfe";
$diretorioArquivosCte=$diretorioTemp.DIRECTORY_SEPARATOR."cte";
$nomeEmpresa="Empresa LTDA";
//Sigla da UF
$siglaUf="MG";
/*Código da UF
'AC'=>'12',
'AL'=>'27',
'AM'=>'13',
'AP'=>'16',
'BA'=>'29',
'CE'=>'23',
'DF'=>'53',
'ES'=>'32',
'GO'=>'52',
'MA'=>'21',
'MG'=>'31',
'MS'=>'50',
'MT'=>'51',
'PA'=>'15',
'PB'=>'25',
'PE'=>'26',
'PI'=>'22',
'PR'=>'41',
'RJ'=>'33',
'RN'=>'24',
'RO'=>'11',
'RR'=>'14',
'RS'=>'43',
'SC'=>'42',
'SE'=>'28',
'SP'=>'35',
'TO'=>'17'
*/
$codigoUf="31";
//Nome do certificado que deve ser colocado na pasta certs da API
$nomeCertificado="certificado.pfx";
//Senha da chave privada
$senhaCertificado="senhacertificado";
//Senha de decriptaçao da chave, normalmente não é necessaria
$senhaDecriptacaoChave="";