<?php
set_time_limit ( 0 );
@session_start ();

include_once '../conexao2.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';

include_once '../validacao.php';

$tpl = new Template ( 'rel-totvs.html' );

# INICIO INCLUDE #
$tpl->addFile ( "HEAD", "../head-tpl.php" );
$tpl->addFile ( "TOPO", "../topo-tpl.php" );

if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "../menu-tpl.php" );
} elseif (isset ( $vPermissao ['assistente'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "../menu-tpl.assistente.php" );
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

$coligada = (isset ( $_POST ['coligada'] ) && $_POST ['coligada'] == 8) ? 8 : 1;

extract ( $_POST );
$callCenter = false;

if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['assistente'] [$_SESSION ['user'] ['login']] )) {
	$empresa = $coligada;
} else {
	$empresa = 1;
}

if (! (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['assistente'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] ))) {
	$vetCallCenter = array ('adamasceno' => '01.03.0008', 'dfreitas' => '01.03.0004', 'elaine.cardoso' => '', 'klima' => '01.03.0003', 'mmendes' => '01.03.0005', 'patricia.reis' => '01.03.0001', 'regiane.lopes' => '01.03.0016', 'simone.sousa' => '01.03.0017', 'thiago.israel' => '01.03.0015' );
	$vendedor = $vetCallCenter [$_SESSION ['user'] ['login']];
	$callCenter = true;
}

if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] )) {
	$codigo1 = '01.01';
	$codigo2 = '02.01.0001';
	$produto = '185';
} else {
	$codigo1 = '01.02';
	$codigo2 = '01.03';
	$produto = '186, 3918';
	$tpl->block ( 'BLOCK_COLIGADA' );
}

$sql = "
SELECT 
	v.CODTB2FAT AS [CODIGO],
	v.DESCRICAO AS [VENDEDOR] 
FROM TTB2 v (nolock)
WHERE
	(v.CODCOLIGADA = $empresa)
";

if ($empresa != 8) {
	
	if ($codigo1 == '01.02' && $codigo2 = '01.03') {
		$sql .= "
	AND (v.CODTB2FAT NOT IN ('$codigo1', '$codigo2')) AND
	";
	} else {
		$sql .= "(v.CODTB2FAT NOT IN ('$codigo1')) AND";
	}
	
	$sql .= "
	(v.CODTB2FAT LIKE '$codigo1%' OR v.CODTB2FAT LIKE '$codigo2%')
";

} else {
	$sql .= "AND LEN(v.CODTB2FAT) = 10";
}

$sql .= "ORDER BY v.DESCRICAO";

$rs = $db2->Execute ( $sql );

if ($rs) {
	
	if (! ($callCenter)) {
		
		while ( $o = $rs->FetchNextObject () ) {
			$tpl->CODIGO = $o->CODIGO;
			$tpl->VENDEDOR = utf8_encode ( $o->VENDEDOR );
			
			if (count ( $_POST ) || isset ( $vendedor )) {
				if ($vendedor == $o->CODIGO) {
					$tpl->SELECTED = 'selected';
				} else {
					$tpl->clear ( 'SELECTED' );
				}
			}
			
			$tpl->block ( 'BLOCK_VENDEDOR' );
		
		}
		
		$tpl->block ( 'BLOCK_EXIBE_VENDEDOR' );
	}

}

if (! empty ( $_POST ['filtrodata'] )) {
	if ($_POST ['filtrodata'] == 1) {
		$tpl->SELECIONADOEMISSAO = 'selected';
	} elseif ($_POST ['filtrodata'] == 2) {
		$tpl->SELECIONADOVENCIMENTO = 'selected';
	} elseif ($_POST ['filtrodata'] == 3) {
		$tpl->SELECIONADOBAIXA = 'selected';
	} else {
		$tpl->clear ( 'SELECIONADOEMISSAO' );
		$tpl->clear ( 'SELECIONADOVENCIMENTO' );
		$tpl->clear ( 'SELECIONADOBAIXA' );
	}

} else {
	$tpl->clear ( 'SELECIONADO' );
}

if (count ( $_POST )) {
	
	$tpl->VAL_CLIENTE = $cliente;
	$tpl->DATA_INICIO = $data1;
	$tpl->DATA_FIM = $data2;
	
	if ($coligada == 1) {
		$tpl->SELECTED_DIARIO = 'selected';
	} elseif ($coligada == 8) {
		$tpl->SELECTED_DOL = 'selected';
		$produto = '4036';
	}
	
	if ($opcao == 1) {
		$tpl->SELECTED_CPFCNPJ = 'selected';
	} elseif ($opcao == 2) {
		$tpl->SELECTED_NOME = 'selected';
	}
	
	$sql = "
SELECT DISTINCT
	C.CGCCFO AS [CPFCNPJ],
	C.NOME AS [CLIENTE],
	V.CODTB2FAT AS [CODIGO],
	V.DESCRICAO AS [VENDEDOR],
	M.CAMPOLIVRE1 AS [PI],
	F.NUMERODOCUMENTO AS [DOCUMENTO],
	CONVERT(VARCHAR(10),F.DATAEMISSAO,103) AS [EMISSAO],
	CONVERT(VARCHAR(10),F.DATAVENCIMENTO,103) AS [VENCIMENTO],
	CONVERT(VARCHAR(10),B.DATABAIXA,103) AS [BAIXA],
	F.VALORORIGINAL AS [VALOR],
	UPPER(P.DESCFORMAPAGTO) AS [PAGAMENTO] 
FROM TMOV M (NOLOCK)
INNER JOIN FCFO C (NOLOCK) ON (C.CODCFO = M.CODCFO AND (C.CODCOLIGADA = M.CODCOLIGADA OR C.CODCOLIGADA = 0))
INNER JOIN TITMMOV I (NOLOCK) ON (I.IDMOV = M.IDMOV AND I.CODCOLIGADA = M.CODCOLIGADA)
INNER JOIN FLAN F (NOLOCK) ON (F.IDMOV = M.IDMOV AND F.CODCOLIGADA = M.CODCOLIGADA)
INNER JOIN TTB2 V (NOLOCK) ON (V.CODTB2FAT = I.CODTB2FAT AND V.CODCOLIGADA = I.CODCOLIGADA)
LEFT JOIN FLANBAIXA B (NOLOCK) ON (B.IDLAN = F.IDLAN AND B.CODCOLIGADA = F.CODCOLIGADA  AND B.STATUS NOT IN (1))
LEFT JOIN TFORMAPAGTO P (NOLOCK) ON (P.IDFORMAPAGTO = B.IDFORMAPAGTO AND P.CODCOLIGADA = B.CODCOLIGADA)
WHERE
	(m.CODCOLIGADA = $empresa) AND
	(i.IDPRD IN ($produto)) AND
	(m.STATUS <> 'C')
";
	
	if ($empresa == 1) {
		if ($codigo1 == '01.02' && $codigo2 = '01.03') {
			$sql .= "
	AND (v.CODTB2FAT NOT IN ('$codigo1', '$codigo2')) 
	";
		}
		
		$sql .= "
	AND (v.CODTB2FAT LIKE '$codigo1%' OR v.CODTB2FAT LIKE '$codigo2%')
	";
	}
	
	if (! empty ( $vendedor )) {
		$sql .= " AND v.CODTB2FAT = '$vendedor'";
	}
	
	if (! empty ( $opcao )) {
		$sql .= ($opcao == 1) ? " AND c.CGCCFO = '$cliente'" : " AND c.NOME LIKE '%$cliente%'";
	}
	
	if (! empty ( $data1 ) && ! empty ( $data2 )) {
		
		$campo = '';
		switch ($filtrodata) {
			case 1 :
				$campo = 'm.DATAEMISSAO';
				break;
			case 2 :
				$campo = 'f.DATAVENCIMENTO';
				break;
			case 3 :
				$campo = 'f.DATABAIXA';
				break;
		}
		
		$sql .= " AND
	((convert(DATETIME,convert(VARCHAR,$campo,103),103) >= convert(DATETIME,convert(VARCHAR,'$data1',103),103)) AND
	(convert(DATETIME,convert(VARCHAR,$campo,103),103) <= convert(DATETIME,convert(VARCHAR,'$data2',103),103)))
	ORDER BY [CLIENTE], [EMISSAO], [VENCIMENTO]
";
	}
	
	$rs = $db2->Execute ( $sql );
	
	if ($rs) {
		while ( $o = $rs->FetchNextObject () ) {
			$tpl->CPFCNPJ = $o->CPFCNPJ;
			$tpl->CLIENTE = utf8_encode ( $o->CLIENTE );
			$tpl->CODIGO = $o->CODIGO;
			$tpl->VENDEDOR = utf8_encode ( $o->VENDEDOR );
			$tpl->PI = utf8_encode ( $o->PI );
			$tpl->DOCUMENTO = $o->DOCUMENTO;
			$tpl->EMISSAO = $o->EMISSAO;
			$tpl->VENCIMENTO = $o->VENCIMENTO;
			$tpl->BAIXA = $o->BAIXA;
			$tpl->VALOR = number_format ( $o->VALOR, 2, ',', '.' );
			$tpl->PAGAMENTO = utf8_encode ( $o->PAGAMENTO );
			
			list ( $d1, $m1, $a1 ) = explode ( '/', date ( 'd/m/Y' ) );
			list ( $d2, $m2, $a2 ) = explode ( '/', $o->VENCIMENTO );
			$data1 = "$a1-$m1-$d1";
			$data2 = "$a2-$m2-$d2";
			
			$data1 = strtotime ( $data1 );
			$data2 = strtotime ( $data2 );
			
			if ($data1 > $data2 && empty ( $o->BAIXA )) {
				$tpl->VENCIDO = "style='color:#FF0000'";
			} else {
				$tpl->clear ( 'VENCIDO' );
			}
			
			$tpl->block ( 'BLOCK_DADOS' );
		}
		$tpl->block ( 'BLOCK_TABELA' );
	}
}

$tpl->show ();

?>