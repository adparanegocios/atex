<?php

error_reporting ( 0 );
ini_set ( 'display_errors', 0 );
set_time_limit ( 0 );
@session_start ();

include_once '../conexao.php';
include_once '../conexao2.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';

//include_once '../validacao.php';


$tpl = new Template ( 'situacaoOrdens2.html' );

// Definimos o nome do arquivo que será exportado
$arquivo = 'DetalharOrdens.xls';

extract ( $_REQUEST );

if (count ( $_REQUEST )) {
	
	$sql = "
	SELECT 
		F.SEGUNDONUMERO AS [SEGUNDONUMERO],
		CASE WHEN f.DATABAIXA IS NULL THEN 'EM ABERTO' ELSE CONVERT(VARCHAR(10),f.DATABAIXA,103) END AS [STATUS] 
	FROM FLAN F (NOLOCK)
	WHERE
		F.CODCOLIGADA = 1 AND
		F.PAGREC = 1 AND
		F.CODTB1FLX IN ('1.04.001', '1.04.002', '1.04.003') AND
		CONVERT(DATETIME,CONVERT(VARCHAR,F.DATACRIACAO,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,'$data1',103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,F.DATACRIACAO,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,'$data2',103),103)
	";
	
	$vetFinanceiro = array ();
	$rs = $db2->Execute ( $sql );
	
	while ( $o = $rs->FetchNextObject () ) {
		$vetFinanceiro [$o->SEGUNDONUMERO] = $o->STATUS;
	}
	
	$sql = "
SELECT 
	SUBSTRING(o.AdOrderNumber,5,LEN(o.AdOrderNumber)) AS [ORDEM],
	(SELECT 
		t.TeamName 
	FROM SalesTeamName t (NOLOCK)
	INNER JOIN UsrUsers u2 (NOLOCK) ON (u2.SalesTeamNameId = t.SalesTeamId)
	WHERE
		u2.UserId = o.SellerId
	) AS [LOTACAO],
	u.UserFname+' '+u.UserLname AS [ATENDENTE],
	c.Name1+' '+c.Name2 AS [CLIENTE],
	c.FederalId AS [DOCUMENTO],
	m.Name AS [MODALIDADE],
	CASE WHEN (SELECT sum(p.ApplyAmount) FROM AoPrepaymentApply p WHERE p.AdOrderId = o.Id) IS NOT NULL THEN CASE WHEN ROUND((SELECT sum(p.ApplyAmount) FROM AoPrepaymentApply p WHERE p.AdOrderId = o.Id),2) >= ROUND(o.NetAmount, 2) THEN 'Sim' ELSE 'Não' END ELSE 'Pendente' END AS [APLICADO],
	o.NetAmount AS [VALOR],
	ISNULL((SELECT sum(p.ApplyAmount) FROM AoPrepaymentApply p WHERE p.AdOrderId = o.Id), 0) AS [RECEBIDO],
	m.Id AS [FORMADEPAGAMENTO],
	CONVERT(DATETIME,CONVERT(VARCHAR,o.CreateDate,103),103) AS [CRIACAO],
	dbo.FGetRunDateList(o.Id) AS [VEICULACAO]
FROM AoAdOrder o (NOLOCK)
INNER JOIN UsrUsers u (NOLOCK) ON (u.UserId = o.RepId)
INNER JOIN AoOrderCustomers oc (NOLOCK) ON (oc.AdOrderId = o.Id AND oc.PayedBy = 1)
INNER JOIN Customer c (NOLOCK) ON (c.AccountId = oc.CustomerId)
LEFT JOIN PaymentMethodType m (NOLOCK) ON (m.id = oc.PaymentMethod)
WHERE
	o.OrderStatusId = 4 AND
	o.CurrentQueue = 3 AND
";
	if (! empty ( $data1 ) && ! empty ( $data2 )) {
		$sql .= "
	CONVERT(DATETIME,CONVERT(VARCHAR,o.CreateDate,103),103) BETWEEN
	CONVERT(DATETIME,CONVERT(VARCHAR,'$data1',103),103) AND
	CONVERT(DATETIME,CONVERT(VARCHAR,'$data2',103),103) AND
";
	}
	
	$sql .= "
	((SELECT TOP 1 r.RefAdOrder FROM CoFulfillmentRec r (nolock) WHERE r.RefAdOrder = o.Id) IS NULL) AND
";
	
	if (! empty ( $ordem )) {
		$sql .= "o.AdOrderNumber like '%$ordem%' AND ";
	}
	
	if (! empty ( $atendente )) {
		$sql .= "u.UserId = '$atendente' AND ";
	}
	
	if (! empty ( $lotacao )) {
		$lista = $lotacao;
	} elseif ($vPermissao ['web'] [$_SESSION ['user'] ['login']]) {
		$lista = '9';
	} else {
		$lista = '1, 9, 10, 11, 12, 13, 14, 15, 16, 17, 22';
	}
	
	if (! empty ( $pagamento )) {
		$sql .= "m.Id = '$pagamento' AND ";
	}
	
	if (! empty ( $opcao ) && ! empty ( $cliente )) {
		if ($opcao == 1) {
			$sql .= "c.FederalId = '$cliente'";
		} else {
			$sql .= "c.Name1+' '+c.Name2 LIKE '%$cliente%' AND";
		}
	}
	
	$sql .= "
	(SELECT u3.SalesTeamNameId FROM UsrUsers u3 (NOLOCK) WHERE u3.UserId = o.SellerId) IN ($lista)
";
	
	$sql .= "
ORDER BY [LOTACAO], [ATENDENTE], [CLIENTE]
";
	
	$vFormasPagamento = array ('43', '44', '45', '47', '49', '52', '26', '32', '33', '46', '48', '51', '17', '29', '50', '35', '13' );
	
	$rs = $db->Execute ( $sql );
	
	if ($rs) {
		while ( $o = $rs->FetchNextObject () ) {
			$tpl->ORDEM = $o->ORDEM;
			$tpl->LOTACAO = $o->LOTACAO;
			$tpl->ATENDENTE = $o->ATENDENTE;
			$tpl->CLIENTE = $o->CLIENTE;
			$tpl->MODALIDADE = $o->MODALIDADE;
			$tpl->DOCUMENTO = $o->DOCUMENTO;
			$tpl->VALOR = number_format ( $o->VALOR, 2, ',', '.' );
			$tpl->CRIACAO = Util::converteData ( $o->CRIACAO );
			$tpl->VEICULACAO = $o->VEICULACAO;
			$formaPagamento = $o->FORMADEPAGAMENTO;
			
			if (in_array ( $formaPagamento, $vFormasPagamento )) {
				$tpl->FINANCEIRO = $vetFinanceiro [$o->ORDEM];
			}
			
			$tpl->block ( 'BLOCK_DADOS' );
		}
	
	}
}

// Configurações header para forçar o download
header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header ( "Last-Modified: " . gmdate ( "D,d M YH:i:s" ) . " GMT" );
header ( "Cache-Control: no-cache, must-revalidate" );
header ( "Pragma: no-cache" );
header ( "Content-type: application/x-msexcel" );
header ( "Content-Disposition: attachment; filename=\"{$arquivo}\"" );
header ( "Content-Description: PHP Generated Data" );

$tpl->show ();

exit ();

?>