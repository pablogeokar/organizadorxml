<?php
/**
 * Parâmetros de configuração do sistema
 * Última alteração em 03-06-2013 17:02:04 
 **/

//###############################
//########## GERAL ##############
//###############################
// tipo de ambiente esta informação deve ser editada pelo sistema
// 1-Produção 2-Homologação
// esta variável será utilizada para direcionar os arquivos e
// estabelecer o contato com o SEFAZ
$ambiente=1;
//esta variável contêm o nome do arquivo com todas as url dos webservices do sefaz
//incluindo a versao dos mesmos, pois alguns estados não estão utilizando as
//mesmas versões
$arquivoURLxml="nfe_ws2.xml";
$arquivoURLxmlCTe="cte_ws1.xml";
//Diretório onde serão mantidos os arquivos com as NFe em xml
//a partir deste diretório serão montados todos os subdiretórios do sistema
//de manipulação e armazenamento das NFe e CTe
$arquivosDir='/var/www/nfe';
$arquivosDirCTe="/var/www/cte";
//URL base da API, passa a ser necessária em virtude do uso dos arquivos wsdl
//para acesso ao ambiente nacional
$baseurl="http://dominio/nfephp/";
//Versão em uso dos shemas utilizados para validação dos xmls
$schemes="PL_006n";
$schemesCTe="PL_CTE_104";

//###############################
//###### EMPRESA EMITENTE #######
//###############################
//Nome da Empresa
$empresa="";
//Sigla da UF
$UF="";
//Código da UF
$cUF="";
//Número do CNPJ
$cnpj="";

//###############################
//#### CERITIFICADO DIGITAL #####
//###############################
//Nome do certificado que deve ser colocado na pasta certs da API
$certName="";
//Senha da chave privada
$keyPass="";
//Senha de decriptaçao da chave, normalmente não é necessaria
$passPhrase="";

//###############################
//############ DANFE ############
//###############################
//Configuração do DANFE
$danfeFormato="P"; //P-Retrato L-Paisagem 
$danfePapel="A4"; //Tipo de papel utilizado 
$danfeCanhoto=1; //se verdadeiro imprime o canhoto na DANFE 
$danfeLogo="/diretorio/libs/nfephp/images/logo.jpg"; //passa o caminho para o LOGO da empresa 
$danfeLogoPos="L"; //define a posição do logo na Danfe L-esquerda, C-dentro e R-direta 
$danfeFonte="Times"; //define a fonte do Danfe limitada as fontes compiladas no FPDF (Times) 
$danfePrinter="hpteste"; //define a impressora para impressão da Danfe 

//###############################
//############ DACTE ############
//###############################
//Configuração do DACTE
$dacteFormato="P"; //P-Retrato L-Paisagem 
$dactePapel="A4"; //Tipo de papel utilizado 
$dacteCanhoto=1; //se verdadeiro imprime o canhoto na DANFE 
$dacteLogo="/diretorio/libs/nfephp/images/logo.jpg"; //passa o caminho para o LOGO da empresa 
$dacteLogoPos="L"; //define a posição do logo na Danfe L-esquerda, C-dentro e R-direta 
$dacteFonte="Times"; //define a fonte do Danfe limitada as fontes compiladas no FPDF (Times) 
$dactePrinter="hpteste"; //define a impressora para impressão da Dacte 

//###############################
//############ EMAIL ############
//###############################
//Configuração do email
$mailAuth="1"; //ativa ou desativa a obrigatoriedade de autenticação no envio de email, na maioria das vezes ativar 
$mailFROM="notafiscaleletronica@provedor.com.br"; //identificação do emitente 
$mailHOST="mail.provedor.com.br"; //endereço do servidor SMTP 
$mailUSER="notafiscaleletronica@provedor.com.br"; //username para autenticação, usando quando mailAuth é 1
$mailPASS="senha"; //senha de autenticação do serviço de email
$mailPROTOCOL=""; //protocolo de email utilizado (classe alternate)
$mailPORT="587"; //porta utilizada pelo smtp (classe alternate)
$mailFROMmail="nfe@seudominio.com.br"; //para alteração da identificação do remetente, pode causar problemas com filtros de spam 
$mailFROMname="NFePHP"; //para indicar o nome do remetente 
$mailREPLYTOmail="usuario@provedor.com.br"; //para indicar o email de resposta
$mailREPLYTOname="NFe"; //para indicar email de cópia
$mailIMAPhost="mail.provedor.com.br"; //url para o servidor IMAP
$mailIMAPport="143"; //porta do servidor IMAP
$mailIMAPsecurity="tls"; //esquema de segurança do servidor IMAP
$mailIMAPnocerts="novalidate-cert"; //desabilita verificação de certificados do Servidor IMAP
$mailIMAPbox="INBOX"; //caixa postal de entrada do servidor IMAP
$mailLayoutFile=""; //layout da mensagem do email

//###############################
//############ PROXY ############
//###############################
//Configuração de Proxy
$proxyIP=""; //ip do servidor proxy, se existir 
$proxyPORT=""; //numero da porta usada pelo proxy 
$proxyUSER=""; //nome do usuário, se o proxy exigir autenticação
$proxyPASS=""; //senha de autenticação do proxy 

$ds = DIRECTORY_SEPARATOR;
require(dirname(__FILE__).$ds."..".$ds."..".$ds."..".$ds."wrapperConfigNfePhp.php");
?>
