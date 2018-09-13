<?php

include_once 'C:/wamp/www/atex/conexao.php';
include_once 'C:/wamp/www/atex/classes/phpmailer/class.phpmailer.php';
include_once 'C:/wamp/www/atex/global.php';

$sql = "
SELECT 
	(SELECT s.TeamName FROM SalesTeamName s (nolock) WHERE s.SalesTeamId = u.SalesTeamNameId) AS [PDV],
	(SELECT u.UserFname+' '+u.UserLname FROM UsrUsers u (nolock) WHERE u.UserId = o.RepId) AS [ATENDENTE],
	substring(o.AdOrderNumber,5,len(o.AdOrderNumber)) AS [ORDEM],
	convert(VARCHAR(10),o.CreateDate,103) AS [CRIACAO],
	o.NetAmount AS [VALOR],
	CASE WHEN (SELECT TOP 1 p.TransId FROM AoPayments p (nolock) WHERE p.ApplyAdOrderId = o.Id) IS NULL THEN 'Pagamento não aplicado' ELSE CASE WHEN (SELECT CASE WHEN (SELECT s.ExternalName FROM ShStringMapper s (nolock) WHERE s.ExternalSystemType = 2 AND s.ObjectType = 9 AND s.AdBaseId = (SELECT u.UserId FROM UsrUsers u (nolock) WHERE u.UserId = o.RepId)) NOT IN ('', '') THEN (SELECT s.ExternalName FROM ShStringMapper s (nolock) WHERE s.ExternalSystemType = 2 AND s.ObjectType = 9 AND s.AdBaseId = (SELECT u.UserId FROM UsrUsers u (nolock) WHERE u.UserId = o.RepId)) ELSE NULL END) IS NULL THEN 'Atendente não vinculado' ELSE '' END END AS [MOTIVO]
FROM AoAdOrder o (nolock)
INNER JOIN UsrUsers u (nolock) ON (o.SellerId = u.UserId)
WHERE
	(convert(datetime,convert(varchar,o.CreateDate,103),103) >= '2015-06-02' AND convert(datetime,convert(varchar,o.CreateDate,103),103) <= convert(datetime,convert(varchar,getdate() - CONVERT(DATETIME,1),103),103)) AND
	((SELECT TOP 1 p.TransId FROM AoPayments p (nolock) WHERE p.ApplyAdOrderId = o.Id) IS NULL OR (SELECT s.ExternalName FROM ShStringMapper s (nolock) WHERE s.ExternalSystemType = 2 AND s.ObjectType = 9 AND s.AdBaseId = (SELECT u.UserId FROM UsrUsers u (nolock) WHERE u.UserId = o.RepId)) IS NULL) AND
	((SELECT TOP 1 r.RefAdOrder FROM CoFulfillmentRec r (nolock) WHERE r.RefAdOrder = o.Id) IS NULL) AND
	(u.SalesTeamNameId IN (1, 9, 10, 11, 12, 13, 14, 15, 16, 17, 22)) AND
	(o.OrderStatusId = 4) AND
	(o.CurrentQueue = 3) AND
	(o.CustomPackNumber IS NULL OR o.CustomPackNumber = '')
ORDER BY (SELECT s.TeamName FROM SalesTeamName s (nolock) WHERE s.SalesTeamId = u.SalesTeamNameId), convert(datetime,convert(varchar,o.CreateDate,103),103), (SELECT u.UserFname+' '+u.UserLname FROM UsrUsers u (nolock) WHERE u.UserId = o.RepId)
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

$mail->AddAddress ( 'andreia@diariodopara.com.br' );
$mail->AddAddress ( 'tem@diariodopara.com.br' );
$mail->AddAddress ( 'natasha.correa@diariodopara.com.br' );
$mail->AddAddress ( 'thais.reis@diariodopara.com.br' );
$mail->AddAddress ( 'luizeduardo@diariodopara.com.br' );
$mail->AddAddress ( 'anna.figueiredo@diariodopara.com.br' );
$mail->AddAddress ( 'danielle.motta@diariodopara.com.br' );
$mail->AddAddress ( 'janriete.souza@rbadecomunicacao.com.br' );

if ($enviar) {
	if (! $mail->Send ()) {
		exit ();
	}
} else {
	exit ();
}

$mail->SmtpClose ();


?>