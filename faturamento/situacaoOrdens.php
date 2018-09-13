<?php
error_reporting ( 0 );
ini_set ( 'display_errors', 0 );
set_time_limit ( 0 );
@session_start ();

include_once '../conexao.php';
include_once '../conexao2.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';

include_once '../validacao.php';

$tpl = new Template ( 'situacaoOrdens.html' );

# INICIO INCLUDE #
$tpl->addFile ( "HEAD", "../head-tpl.php" );
$tpl->addFile ( "TOPO", "../topo-tpl.php" );

if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "../menu-tpl.php" );
} elseif (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "../menu-tpl2.php" );
} else {
	$tpl->addFile ( "MENU", "../menu-tpl3.php" );
}

$tpl->USUARIO_LOGADO = $_SESSION ['user'] ['nome'];
# FIM INCLUDE #


for($i = INICIO; $i <= FIM; $i = $i + 10) {
	$tpl->PAGE = $i;
	
	if ($i == PADRAO) {
		$tpl->SELECIONADO = 'selected';
	} else {
		$tpl->clear ( 'SELECIONADO' );
	}
	
	$tpl->block ( 'BLOCK_PAGE' );
}

extract ( $_REQUEST );
//printvardie($_REQUEST);
if (isset ( $vPermissao ['web'] [$_SESSION ['user'] ['login']] )) {
	$sql = "SELECT u.UserId FROM UsrUsers u (nolock) WHERE u.LoginName = '" . $vPermissao ['web'] [$_SESSION ['user'] ['login']] . "'";
	$atendente = $db->Execute ( $sql )->FetchNextObject ()->USERID;
	$lotacao = 9;
	$tpl->DISABLED = 'disabled';
}

//printvardie ( $atendente );


$tpl->POST_TIPOCLIENTE = $opcao;
$tpl->POST_CLIENTE = $cliente;
$tpl->POST_ATENDENTE = $atendente;
$tpl->POST_LOTACAO = $lotacao;
$tpl->POST_MODALIDADE = $pagamento;
$tpl->POST_DATAINICIO = $data1;
$tpl->POST_DATAFIM = $data2;
$tpl->POST_ORDEM = $ordem;

if (! empty ( $ordem )) {
	$tpl->VAL_ORDEM = $ordem;
}

if (! empty ( $data1 ) && ! empty ( $data2 )) {
	$tpl->DATA_INICIO = $data1;
	$tpl->DATA_FIM = $data2;
}

if (! empty ( $opcao ) && $opcao == 1) {
	$tpl->SELECTED_CPFCNPJ = 'selected';
} elseif (! empty ( $opcao ) && $opcao == 2) {
	$tpl->SELECTED_NOME = 'selected';
}

$tpl->VAL_CLIENTE = $cliente;

if (isset ( $vPermissao ['web'] [$_SESSION ['user'] ['login']] )) {
	$SalesTeamNameId = '9';
} else {
	$SalesTeamNameId = '9, 20';
}

$sql = "
SELECT 
	u.UserId AS [IDATENDENTE],
	CASE u.SalesTeamNameId WHEN 9 THEN u.UserFname+' '+u.UserLname+' (Call Center)' ELSE u.UserFname+' '+u.UserLname END AS [ATENDENTE] 
FROM UsrUsers u (NOLOCK)
WHERE
	u.SalesTeamNameId IN ($SalesTeamNameId)
ORDER BY [ATENDENTE]
";

$rs = $db->Execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		
		$idatendente = $o->IDATENDENTE;
		
		if (! empty ( $atendente )) {
			if ($atendente == $idatendente) {
				$tpl->SELECTED_ATENDENTE = 'SELECTED';
			} else {
				$tpl->clear ( 'SELECTED_ATENDENTE' );
			}
		}
		
		$tpl->IDATENDENTE = $o->IDATENDENTE;
		$tpl->ATENDENTE = utf8_encode ( $o->ATENDENTE );
		$tpl->block ( 'BLOCK_ATENDENTE' );
	}
}

if (isset ( $vPermissao ['web'] [$_SESSION ['user'] ['login']] )) {
	$SalesTeamNameId = '9';
} else {
	$SalesTeamNameId = '1, 9, 10, 11, 12, 13, 14, 15, 16, 17, 22';
}

$sql = "
SELECT 
	t.SalesTeamId AS [IDLOTACAO],
	t.TeamName AS [LOTACAO]
FROM SalesTeamName t (NOLOCK)
WHERE
	t.SalesTeamId IN ($SalesTeamNameId)
ORDER BY [LOTACAO]
";

$rs = $db->Execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		
		$idlotacao = $o->IDLOTACAO;
		
		if (! empty ( $lotacao )) {
			if ($lotacao == $idlotacao) {
				$tpl->SELECTED_LOTACAO = 'SELECTED';
			} else {
				$tpl->clear ( 'SELECTED_LOTACAO' );
			}
		}
		
		$tpl->IDLOTACAO = $o->IDLOTACAO;
		$tpl->LOTACAO = utf8_encode ( $o->LOTACAO );
		$tpl->block ( 'BLOCK_LOTACAO' );
	}
}

$sql = "
SELECT 
	p.Id AS [IDPAGAMENTO],
	p.Name AS [PAGAMENTO] 
FROM PaymentMethodType p (NOLOCK)
WHERE
	p.IsInactiveFlag = 0
ORDER BY [PAGAMENTO]
";

$rs = $db->Execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		
		$idpagamento = $o->IDPAGAMENTO;
		
		if (! empty ( $pagamento )) {
			if ($pagamento == $idpagamento) {
				$tpl->SELECTED_PAGAMENTO = 'SELECTED';
			} else {
				$tpl->clear ( 'SELECTED_PAGAMENTO' );
			}
		}
		
		$tpl->IDPAGAMENTO = $o->IDPAGAMENTO;
		$tpl->PAGAMENTO = utf8_encode ( $o->PAGAMENTO );
		$tpl->block ( 'BLOCK_PAGAMENTO' );
	}
}

if (count ( $_REQUEST )) {
	
	$sql = "
	SELECT 
		F.SEGUNDONUMERO AS [SEGUNDONUMERO],
		CASE WHEN B.DATABAIXA IS NULL THEN 'EM ABERTO' ELSE CONVERT(VARCHAR(10),B.DATABAIXA,103) END AS [STATUS],
		ISNULL(B.VALORBAIXA, 0) AS [VALORBAIXA] 
	FROM FLAN F (NOLOCK)
	LEFT JOIN FLANBAIXA B (NOLOCK) ON (B.IDLAN = F.IDLAN AND B.CODCOLIGADA = F.CODCOLIGADA AND B.STATUS NOT IN (1))
	WHERE
		F.CODCOLIGADA = 1 AND
		F.PAGREC = 1 AND
		F.STATUSLAN <> 2 AND
	";
	
	if ($lotacao == 9) {
		$sql .= "F.CODTB1FLX IN ('1.04.003') AND ";
	} elseif ($lotacao == '1' || $lotacao == '10' || $lotacao == '11' || $lotacao == '12' || $lotacao == '13' || $lotacao == '14' || $lotacao == '15' || $lotacao == '16' || $lotacao == '17' || $lotacao == '22') {
		$sql .= "F.CODTB1FLX IN ('1.04.002') AND ";
	} else {
		$sql .= "F.CODTB1FLX IN ('1.04.001', '1.04.002', '1.04.003') AND ";
	}
	
	if (! empty ( $ordem )) {
		$sql .= "F.SEGUNDONUMERO = '$ordem' AND ";
	}
	
	if (! empty ( $data1 ) && ! empty ( $data2 )) {
		$sql .= "
		CONVERT(DATETIME,CONVERT(VARCHAR,F.DATACRIACAO,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,'$data1',103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,F.DATACRIACAO,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,'$data2',103),103)
	";
	} else {
		$sql .= "F.SEGUNDONUMERO IS NOT NULL AND YEAR(F.DATACRIACAO) >= '2015'";
	}
	
	$sql .= "ORDER BY F.IDLAN";
	
	//printvardie($sql);
	$vetFinanceiro = array ();
	$rs = $db2->Execute ( $sql );
	
	while ( $o = $rs->FetchNextObject () ) {
		$vetFinanceiro [$o->SEGUNDONUMERO] ['STATUS'] .= $o->STATUS . '*';
		$vetFinanceiro [$o->SEGUNDONUMERO] ['VALOR'] += $o->VALORBAIXA;
	}
	
	//printvardie ( $vetFinanceiro );
	

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
	m.Name AS [MODALIDADE],
	CASE WHEN (SELECT sum(p.ApplyAmount) FROM AoPrepaymentApply p WHERE p.AdOrderId = o.Id) IS NOT NULL THEN CASE WHEN ROUND((SELECT sum(p.ApplyAmount) FROM AoPrepaymentApply p WHERE p.AdOrderId = o.Id),2) >= ROUND(o.NETAMOUNT, 2) THEN 'Sim' ELSE 'Não' END ELSE 'Pendente' END AS [APLICADO],
	o.NETAMOUNT AS [VALOR],
	c.FederalId AS [DOCUMENTO],
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
	O.CURRENTQUEUE IN (3, 8) AND
";
	if (! empty ( $ordem )) {
		$sql .= "o.AdOrderNumber like '%$ordem%' AND ";
	}
	
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
			$sql .= "c.FederalId = '$cliente' AND";
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
	//printvardie($sql);
	$vFormasPagamento = array ('43', '44', '45', '47', '49', '52', '26', '32', '33', '46', '48', '51', '17', '29', '50', '35', '13' );
	
	$rs = $db->Execute ( $sql );
	
	if ($rs) {
		
		$valort = 0;
		$valorbaixat = 0;
		
		while ( $o = $rs->FetchNextObject () ) {
			$tpl->ORDEM = $o->ORDEM;
			$tpl->LOTACAO = utf8_encode ( $o->LOTACAO );
			$tpl->ATENDENTE = utf8_encode ( $o->ATENDENTE );
			$tpl->CLIENTE = utf8_encode ( $o->CLIENTE );
			$tpl->MODALIDADE = utf8_encode ( $o->MODALIDADE );
			$tpl->VALOR = number_format ( $o->VALOR, 2, ',', '.' );
			$tpl->DOCUMENTO = $o->DOCUMENTO;
			$tpl->CRIACAO = Util::converteData ( $o->CRIACAO );
			$tpl->VEICULACAO = $o->VEICULACAO;
			$formaPagamento = $o->FORMADEPAGAMENTO;
			
			if (in_array ( $formaPagamento, $vFormasPagamento )) {
				$tpl->FINANCEIRO = substr ( $vetFinanceiro [$o->ORDEM] ['STATUS'], 0, - 1 );
				$tpl->VALORBAIXA = number_format ( $vetFinanceiro [$o->ORDEM] ['VALOR'], 2, ',', '.' );
				$valorbaixat += $vetFinanceiro [$o->ORDEM] ['VALOR'];
			} else {
				$tpl->clear ( 'FINANCEIRO' );
				$tpl->clear ( 'VALORBAIXA' );
			}
			
			$valort += $o->VALOR;
			
			$tpl->block ( 'BLOCK_DADOS' );
		}
		
		$tpl->VALORT = number_format ( $valort, 2, ',', '.' );
		$tpl->VALORBAIXAT = number_format ( $valorbaixat, 2, ',', '.' );
		
		$tpl->block ( 'BLOCK_TABELA' );
	}
}

//printvardie($sql);
$tpl->show ();

?>