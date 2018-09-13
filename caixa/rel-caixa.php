<?php

ini_set ( 'display_errors', false );
set_time_limit ( 0 );
@session_start ();
include_once '../conexao.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'rel-caixa-tpl.php' );

# INICIO INCLUDE #
$tpl->addFile ( "HEAD", "../head-tpl.php" );
$tpl->addFile ( "TOPO", "../topo-tpl.php" );

extract ( $_REQUEST );

if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "../menu-tpl.php" );
} else {
	$tpl->addFile ( "MENU", "../menu-tpl2.php" );
}

$tpl->USUARIO_LOGADO = $_SESSION ['user'] ['nome'];
# FIM INCLUDE #


$sql = "
SELECT 
	T.SALESTEAMID AS [CODIGO],
	UPPER(T.TEAMNAME) AS [LOJA]
FROM SALESTEAMNAME T (NOLOCK)
WHERE
	T.SALESTEAMID IN (1, 10, 12, 13, 14, 16, 17, 23)
ORDER BY T.TEAMNAME
";

$rs = $db->Execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		$tpl->TEAMID = $o->CODIGO;
		$tpl->DESCRICAO = utf8_encode ( $o->LOJA );
		
		if ($time == $tpl->TEAMID) {
			$tpl->SELECTED = 'selected';
		} elseif ($time == '996') {
			$tpl->SELECTED_TODOS = 'selected';
		} elseif ($time == '997') {
			$tpl->SELECTED_LOJAS = 'selected';
		} elseif ($time == '998') {
			$tpl->SELECTED_CALLCENTER = 'selected';
		} elseif ($time == '999') {
			$tpl->SELECTED_ADBASEE = 'selected';
		} else {
			$tpl->clear ( 'SELECTED' );
			$tpl->clear ( 'SELECTED_TODOS' );
			$tpl->clear ( 'SELECTED_LOJAS' );
			$tpl->clear ( 'SELECTED_CALLCENTER' );
			$tpl->clear ( 'SELECTED_ADBASEE' );
		}
		
		$tpl->block ( 'BLOCK_OPTION_LOTACAO' );
	}
}

if (isset ( $time ) && empty ( $time ) == false) {
	if ($time == '996') {
		$timeDeVenda = '1, 9, 10, 12, 13, 14, 16, 17, 21, 23';
	} elseif ($time == '997') {
		$timeDeVenda = '1, 10, 12, 13, 14, 16, 17, 23';
	} elseif ($time == '998') {
		$timeDeVenda = '9';
	} elseif ($time == '999') {
		$timeDeVenda = '21';
	} else {
		$timeDeVenda = $time;
	}
} else {
	$timeDeVenda = '1, 9, 10, 12, 13, 14, 16, 17, 21, 23';
}

if (! empty ( $data1 ) && ! empty ( $data2 )) {
	
	$tpl->DATA_INICIO = $data1;
	$tpl->DATA_FIM = $data2;
	
	$sql = "
SELECT 
	UPPER(T.TEAMNAME) AS [LOJA],
	(
	SELECT 
		ISNULL(SUM(O.NETAMOUNT), 0) 
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (U.USERID = O.SELLERID)
	INNER JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,'$data1',103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,'$data2',103),103) AND
		U.SALESTEAMNAMEID = T.SALESTEAMID AND
		O.ORDERSTATUSID = 4 AND
		O.CURRENTQUEUE IN (3, 8) AND
		P.ID IN (8, 17, 29, 36, 50)
	) AS [DINHEIRO],
	(
	SELECT 
		ISNULL(SUM(O.NETAMOUNT), 0) 
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (U.USERID = O.SELLERID)
	INNER JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,'$data1',103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,'$data2',103),103) AND
		U.SALESTEAMNAMEID = T.SALESTEAMID AND
		O.ORDERSTATUSID = 4 AND
		O.CURRENTQUEUE IN (3, 8) AND
		P.ID IN (2, 4, 43, 44, 45, 47, 49, 52, 26, 32, 33, 46, 48, 51)
	) AS [CARTAO],
	(
	SELECT 
		ISNULL(SUM(O.NETAMOUNT), 0) 
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (U.USERID = O.SELLERID)
	INNER JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,'$data1',103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,'$data2',103),103) AND
		U.SALESTEAMNAMEID = T.SALESTEAMID AND
		O.ORDERSTATUSID = 4 AND
		O.CURRENTQUEUE IN (3, 8) AND
		P.ID IN (23, 39)
	) AS [CORTESIA],
	(
	SELECT 
		ISNULL(SUM(O.NETAMOUNT), 0) 
	FROM AOADORDER O (NOLOCK)
	INNER JOIN USRUSERS U (NOLOCK) ON (U.USERID = O.SELLERID)
	INNER JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
	LEFT JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
	WHERE
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) >= CONVERT(DATETIME,CONVERT(VARCHAR,'$data1',103),103) AND
		CONVERT(DATETIME,CONVERT(VARCHAR,O.CREATEDATE,103),103) <= CONVERT(DATETIME,CONVERT(VARCHAR,'$data2',103),103) AND
		U.SALESTEAMNAMEID = T.SALESTEAMID AND
		O.ORDERSTATUSID = 4 AND
		O.CURRENTQUEUE IN (3, 8) AND
		P.ID IN (41)
	) AS [WEB] 
FROM SALESTEAMNAME T (NOLOCK)
WHERE
	T.SALESTEAMID IN ($timeDeVenda)
ORDER BY [LOJA]
";
	
	$rs = $db->Execute ( $sql );
	$Tgeral = 0;
	$totalD = 0;
	$totalC = 0;
	$totalG = 0;
	$totalW = 0;
	
	if ($rs) {
		while ( $o = $rs->FetchNextObject () ) {
			$Tdinheiro = $o->DINHEIRO;
			$Tcartao = $o->CARTAO;
			$Tgratis = $o->CORTESIA;
			$Tweb = $o->WEB;
			
			$Tgeral += $Tdinheiro + $Tcartao + $Tgratis + $Tweb;
			
			$tpl->LOJA = utf8_encode ( $o->LOJA );
			$tpl->DINHEIRO = number_format ( $Tdinheiro, 2, ',', '.' );
			$tpl->CARTAO = number_format ( $Tcartao, 2, ',', '.' );
			$tpl->GRATIS = number_format ( $Tgratis, 2, ',', '.' );
			$tpl->WEB = number_format ( $Tweb, 2, ',', '.' );
			
			$totalD += $Tdinheiro;
			$totalC += $Tcartao;
			$totalG += $Tgratis;
			$totalW += $Tweb;
			
			$tpl->block ( 'BLOCK_DADOS' );
		}
	}
	$tpl->TOTALD = number_format ( $totalD, 2, ',', '.' );
	$tpl->TOTALC = number_format ( $totalC, 2, ',', '.' );
	$tpl->TOTALG = number_format ( $totalG, 2, ',', '.' );
	$tpl->TOTALW = number_format ( $totalW, 2, ',', '.' );
	$tpl->GERAL = number_format ( $Tgeral, 2, ',', '.' );

}

$tpl->show ();
?>