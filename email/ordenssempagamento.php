<?php

include_once 'C:/wamp/www/atex/conexao.php';
include_once 'C:/wamp/www/atex/classes/phpmailer/class.phpmailer.php';
include_once 'C:/wamp/www/atex/global.php';

$sql = "
SELECT 
	(SELECT S.TEAMNAME FROM SALESTEAMNAME S (NOLOCK) WHERE S.SALESTEAMID = (SELECT U.SALESTEAMNAMEID FROM USRUSERS U (NOLOCK) WHERE U.USERID = O.SELLERID)) AS [PDV],
	(SELECT U.USERFNAME+' '+U.USERLNAME FROM USRUSERS U (NOLOCK) WHERE U.USERID = O.REPID) AS [ATENDENTE],
	SUBSTRING(O.ADORDERNUMBER,5,LEN(O.ADORDERNUMBER)) AS [ORDEM],
	CONVERT(VARCHAR(10),O.CREATEDATE,103) AS [CRIACAO],
	O.NETAMOUNT AS [VALOR],
	CASE WHEN (SELECT SUM(P.APPLYAMOUNT) FROM AOPREPAYMENTAPPLY P WHERE P.ADORDERID = O.ID) IS NULL THEN 'PAGAMENTO NÃO APLICADO' ELSE CASE WHEN (SELECT CASE WHEN (SELECT S.EXTERNALNAME FROM SHSTRINGMAPPER S (NOLOCK) WHERE S.EXTERNALSYSTEMTYPE = 2 AND S.OBJECTTYPE = 9 AND S.ADBASEID = (SELECT U.USERID FROM USRUSERS U (NOLOCK) WHERE U.USERID = O.REPID)) NOT IN ('', '') THEN (SELECT S.EXTERNALNAME FROM SHSTRINGMAPPER S (NOLOCK) WHERE S.EXTERNALSYSTEMTYPE = 2 AND S.OBJECTTYPE = 9 AND S.ADBASEID = (SELECT U.USERID FROM USRUSERS U (NOLOCK) WHERE U.USERID = O.REPID)) ELSE NULL END) IS NULL THEN 'ATENDENTE NÃO VINCULADO' ELSE '' END END AS [MOTIVO] 
FROM AOADORDER O (NOLOCK)
INNER JOIN USRUSERS U (NOLOCK) ON (O.REPID = U.USERID)
INNER JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1 AND OC.PAYMENTMETHOD IN (26, 32, 33, 46, 48, 51, 43, 44, 45, 47, 49, 52, 17, 29, 50, 13, 35))
INNER JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
WHERE
	CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) BETWEEN '2015-06-02' AND CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE() - CONVERT(DATETIME,1),103),103) AND
	O.ORDERSTATUSID = 4 AND
	O.CURRENTQUEUE = 3 AND
	(SELECT SUM(P.APPLYAMOUNT) FROM AOPREPAYMENTAPPLY P WHERE P.ADORDERID = O.ID) IS NULL AND
	U.SALESTEAMNAMEID IN (1, 9, 10, 11, 12, 13, 14, 15, 16, 17, 20, 22) AND
	(SELECT TOP 1 R.REFADORDER FROM COFULFILLMENTREC R (NOLOCK) WHERE R.REFADORDER = O.ID) IS NULL AND
	P.ID IN (26, 32, 33, 46, 48, 51, 43, 44, 45, 47, 49, 52, 17, 29, 50, 13, 35)
ORDER BY (SELECT S.TEAMNAME FROM SALESTEAMNAME S (NOLOCK) WHERE S.SALESTEAMID = U.SALESTEAMNAMEID), CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103), (SELECT U.USERFNAME+' '+U.USERLNAME FROM USRUSERS U (NOLOCK) WHERE U.USERID = O.REPID)
";

$mail = new PHPMailer ();
$mail->SetLanguage ( "br", "../classes/phpmailer/language/" );
$mail->IsSMTP ();
$mail->Host = HOST_EMAIL;
$mail->SMTPAuth = true;
$mail->Port = PORT_EMAIL;
$mail->Username = USER_EMAIL;
$mail->Password = PASS_EMAIL;
$mail->From = USER_EMAIL;
$mail->AddReplyTo ( USER_EMAIL );
$mail->FromName = "Integração";
$mail->WordWrap = 50;
$mail->IsHTML ( true );
$mail->Subject = 'ATEX: ordens sem pagamentos aplicados ...';

$rs = $db->Execute ( $sql );

if ($rs) {
	
	$enviar = false;
	
	$conteudo = "
	<style type='text/css'>
<!--
.style1 {
	color: #FF0000;
	font-style: italic;
}
.style2 {
	color: #000000;
	font-weight: bold;
}
-->
</style>
	<table width='996' border='1'>
  <tr>
    <td width='192'><div align='center'><strong>PDV</strong></div></td>
    <td width='221'><div align='center'><strong>ATENDENTE</strong></div></td>
    <td width='103'><div align='center'><strong>ORDEM</strong></div></td>
    <td width='74'><div align='center'><strong>CRIA&Ccedil;&Atilde;O</strong></div></td>
    <td width='74'><div align='center'><strong>VALOR</strong></div></td>
    <td width='210'><div align='center'><strong>MOTIVO</strong></div></td>
  </tr>
	";
	
	while ( $o = $rs->FetchNextObject () ) {
		$pdv = utf8_encode($o->PDV);
		$atendente = utf8_encode($o->ATENDENTE);
		$ordem = $o->ORDEM;
		$criacao = $o->CRIACAO;
		$valor = number_format ( $o->VALOR, 2, ',', '.' );
		$motivo = utf8_encode($o->MOTIVO);
		
		$enviar = true;
			
			$conteudo .= "
				<tr>
    				<td>$pdv</td>
    				<td>$atendente</td>
    				<td>$ordem</td>
    				<td>$criacao</td>
    				<td>$valor</td>
    				<td>$motivo</td>
 				 </tr>
		";
	
	}
	$conteudo .= "
	<tr>
    <td colspan='6'><span class='style1'>Obs: quando o MOTIVO for <span class='style2'>Atendente n&atilde;o vinculado</span>, por favor, entrar em contato com <span class='style2'>erp@rbadecomunicacao.com.br</span>. </span></td>
  </tr>
	</table>
	";
}

$mail->Body = utf8_decode ( $conteudo );

$mail->AddAddress ( 'tem@diariodopara.com.br' );
$mail->AddAddress ( 'andreia@diariodopara.com.br' );
$mail->AddAddress ( 'janriete.souza@rbadecomunicacao.com.br' );
$mail->AddAddress ( 'ailton.carvalho@diariodopara.com.br' );
//$mail->AddAddress ( 'adeilson@diarioonline.com.br' );

if ($enviar) {
	if (! $mail->Send ()) {
		exit ();
	}
} else {
	exit ();
}

$mail->SmtpClose ();


?>