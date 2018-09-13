<?php

error_reporting ( 0 );
ini_set ( 'display_errors', 0 );
set_time_limit ( 0 );

include_once 'C:/wamp/www/atex/conexao.php';
include_once 'C:/wamp/www/atex/conexao2.php';
include_once 'C:/wamp/www/atex/classes/phpmailer/class.phpmailer.php';
include_once 'C:/wamp/www/atex/global.php';

$timestamp = mktime ( date ( "H" ) - 3, date ( "i" ), date ( "s" ), date ( "m" ), date ( "d" ), date ( "Y" ), 0 );
$data = gmdate ( "d/m/Y H:i:s", $timestamp );

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
$mail->FromName = "BI";
$mail->WordWrap = 50;
$mail->IsHTML ( true );
$mail->Subject = "RESUMO DO CAIXA EM $data ...";

$sql = "
SELECT 
	UP.USERFNAME+' '+UP.USERLNAME AS [USUARIO],
	ISNULL((
	SELECT 
		SUM(O.NETAMOUNT) 
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (O.SELLERID = U.USERID)
	LEFT JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		O.ORDERSTATUSID NOT IN (2) AND
		P.ID IN (8, 29, 36) AND
		U.USERID IN (UP.USERID)
	),0) AS [DINHEIRO],
	ISNULL((
	SELECT 
		SUM(O.NETAMOUNT)
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (O.SELLERID = U.USERID)
	LEFT JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		O.ORDERSTATUSID NOT IN (2) AND
		P.ID IN (2, 4, 43, 44, 45, 47, 49, 52, 26, 32, 33, 46, 48, 51) AND
		U.USERID IN (UP.USERID)
	),0) AS [CARTAO],
	ISNULL((
	SELECT 
		SUM(O.NETAMOUNT) AS [TOTAL] 
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (O.SELLERID = U.USERID)
	LEFT JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		O.ORDERSTATUSID NOT IN (2) AND
		P.ID IN (23, 39) AND
		U.USERID IN (UP.USERID)
	),0) AS [CORTESIA],
	ISNULL((
	ISNULL((
	SELECT 
		SUM(O.NETAMOUNT) 
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (O.SELLERID = U.USERID)
	LEFT JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		O.ORDERSTATUSID NOT IN (2) AND
		P.ID IN (8, 29, 36) AND
		U.USERID IN (UP.USERID)
	),0)
	) + (
	ISNULL((
	SELECT 
		SUM(O.NETAMOUNT)
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (O.SELLERID = U.USERID)
	LEFT JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		O.ORDERSTATUSID NOT IN (2) AND
		P.ID IN (2, 4, 43, 44, 45, 47, 49, 52, 26, 32, 33, 46, 48, 51) AND
		U.USERID IN (UP.USERID)
	),0)
	) + (
	ISNULL((
	SELECT 
		SUM(O.NETAMOUNT) AS [TOTAL] 
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (O.SELLERID = U.USERID)
	LEFT JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND
		O.ORDERSTATUSID NOT IN (2) AND
		P.ID IN (23, 39) AND
		U.USERID IN (UP.USERID)
	),0)
	),0) AS [TOTAL]
FROM USRUSERS UP (NOLOCK)
WHERE
	UP.USERID IN (135, 136, 138, 139, 140, 141, 142, 143, 180)
ORDER BY UP.USERFNAME+' '+UP.USERLNAME

";

$rs = $db->Execute ( $sql );
$soma1 = 0;

if ($rs) {
	$conteudo .= "
	<table width='680' border='1'>
	  <tr>
	    <td colspan='5'><div align='center'><em><strong>POR LOJA </strong></em></div></td>
	  </tr>
	  <tr>
    <td width='321'><strong>USU&Aacute;RIO</strong></td>
    <td width='90'><strong>DINHEIRO</strong></td>
    <td width='79'><strong>CART&Atilde;O</strong></td>
    <td width='91'><strong>CORTESIA</strong></td>
    <td width='65'><strong>TOTAL</strong></td>
  </tr>
	";
	while ( $o = $rs->FetchNextObject () ) {
		$usuario = utf8_encode ( $o->USUARIO );
		$dinheiro = number_format ( $o->DINHEIRO, 2, ',', '.' );
		$cartao = number_format ( $o->CARTAO, 2, ',', '.' );
		$cortesia = number_format ( $o->CORTESIA, 2, ',', '.' );
		$total = number_format ( $o->TOTAL, 2, ',', '.' );
		
		$conteudo .= "
		<tr>
	    <td>$usuario</td>
	    <td>$dinheiro</td>
	    <td>$cartao</td>
	    <td>$cortesia</td>
	    <td>$total</td>
	  </tr>
		";
		
		$soma1 += $o->TOTAL;
	}
	$soma1 = number_format ( $soma1, 2, ',', '.' );
	$conteudo .= "
	<tr>
    <td><div align='center'><strong>TOTAL </strong></div></td>
    <td colspan='4'><div align='center'><em><strong>$soma1</strong></em></div></td>
  </tr>
	</table>
	";
}

$conteudo .= "<p>-----------------------------------------------------------------------------------------------------------------</p>";

$soma2 = 0;

$sql = "
SELECT 
	U.USERFNAME+' '+U.USERLNAME AS [ATENDENTE],
	P.DESCRIPTION AS [PAGAMENTO],
	SUM(O.NETAMOUNT) AS [VALOR] 
FROM USRUSERS U (NOLOCK)
INNER JOIN AOADORDER O (NOLOCK) ON (O.REPID = U.USERID)
INNER JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
INNER JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
WHERE
	O.ORDERSTATUSID <> 2 AND
	U.SALESTEAMNAMEID IN (18, 20) AND
	O.ORDERSTATUSID = 4 AND O.CURRENTQUEUE = 3 AND
	CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) AND 
	CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103)
GROUP BY U.USERFNAME+' '+U.USERLNAME, P.DESCRIPTION
ORDER BY U.USERFNAME+' '+U.USERLNAME
";

$rs = $db->Execute ( $sql );

if ($rs) {
	$conteudo .= "
	<table width='678' border='1'>
	  <tr>
	    <td colspan='3'><div align='center'><em><strong>POR ATENDENTE </strong></em></div></td>
	  </tr>
	  <tr>
    <td width='310'><strong>ATENDENTE</strong></td>
    <td width='278'><strong>PAGAMENTO</strong></td>
    <td width='68'><strong>VALOR</strong></td>
  </tr>
	";
	
	while ( $o = $rs->FetchNextObject () ) {
		$atendente = utf8_encode ( $o->ATENDENTE );
		$pagamento = utf8_encode ( $o->PAGAMENTO );
		$valor = number_format ( $o->VALOR, 2, ',', '.' );
		
		$conteudo .= "
		<tr>
    		<td>$atendente</td>
    		<td>$pagamento</td>
    		<td>$valor</td>
  		</tr>
		";
		$soma2 += $o->VALOR;
	}
	$soma2 = number_format ( $soma2, 2, ',', '.' );
	$conteudo .= "
	<tr>
    <td><div align='center'><strong>TOTAL</strong></div></td>
    <td colspan='2'><div align='center'><em><strong>$soma2</strong></em></div></td>
  </tr>
	</table>
	";
}

//printvardie($conteudo);
$mail->Body = utf8_decode ( $conteudo );

//$mail->AddAddress ( 'adeilson@diarioonline.com.br' ); 
//$mail->AddAddress ( 'mauricio@rbadecomunicacao.com.br' );
$mail->AddAddress ( 'andreia@diariodopara.com.br' );
$mail->AddAddress ( 'tem@diariodopara.com.br ' );


$mail->Send ();
$mail->SmtpClose ();
