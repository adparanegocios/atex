<?php
set_time_limit ( 0 );
@session_start ();
include_once '../conexao.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'agencia-tpl.php' );

# INICIO INCLUDE #
$tpl->addFile ( "HEAD", "../head-tpl.php" );
$tpl->addFile ( "TOPO", "../topo-tpl.php" );
$tpl->addFile ( "MENU", "../menu-tpl.php" );
$tpl->addFile ( "SUBMENU", "../menu-financeiro-tpl.php" );
$tpl->USUARIO_LOGADO = $_SESSION ['user'] ['nome'];
# FIM INCLUDE #


if ($_SESSION ['user'] ["acesso"] == "controladoria" || $_SESSION ['user'] ["acesso"] == "noticiario") {
	$tpl->block ( "BLOCK_AGENCIA" );
}
// LIBERAR MENU AGENCIA

$vData = getDate ();

$dDataAtual = $vData ["mday"] . "/" . $vData ["mon"] . "/" . $vData ["year"];

// verificando se o usuario logado eh do tem! ou da controladoria
/* if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ('BLOCK_TEM');
}

// verificando se o usuario logado eh do noticiario ou da controladoria
if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ('BLOCK_NOTICIARIO');
}*/

########################## VERIFICA TIME DE VENDA E VENDEDORES ########################


// recuperando todos os usuário que estejam vinculado a um time de venda
$sql = "
select 
	u.UserId 'id',
    u.UserFname+' '+u.UserLname 'usuario' 
from 
	UsrUsers u
where (
    (not (u.SalesTeamNameId is NULL))
";

// verificando se o usuario logado eh do tem!
if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		and (u.SalesTeamNameId <> 3)
		and (u.SalesTeamNameId <> 7)
		and (u.SalesTeamNameId <> 18)
		and (u.SalesTeamNameId <> 19)		
	";
}

// verificando se o usuario logado eh do comercial impresso
if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		and (
			(u.SalesTeamNameId = 3) or
			(u.SalesTeamNameId = 19) 
		)		
	";
}

// verificando se o usuario logado eh do comercial web
if (isset ( $vPermissao ['web'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		and (u.SalesTeamNameId = 7)		
	";
}

$sql .= "
) 
order by u.UserFname+' '+u.UserLname asc
";

$rs = $db->Execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		if ($o->ID == 144) {
			if (isset ( $_POST ['user'] ) && $_POST ['user'] == $o->ID) {
				$tpl->USER = $_POST ['user'];
				@$tpl->USUARIO_SELECIONA = 'selected';
			} else {
				@$tpl->clear ( USUARIO_SELECIONA );
			}
			$tpl->USERID = $o->ID;
			$tpl->USERNAME = utf8_encode ( $o->USUARIO );
			$tpl->block ( 'BLOCK_OPTION_USER' );
		}
	}
}

// recuperando todos os  times de venda
$sql = "
select 
	s.SalesTeamId 'teamid',
    s.TeamName 'descricao'
from
	SalesTeamName s
";

// verificando se o usuario logado eh do tem!
if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		 where ( 
			(s.SalesTeamId <> 3) and
			(s.SalesTeamId <> 7) and
			(s.SalesTeamId <> 18) and
			(s.SalesTeamId <> 19)
		)	
	";
}

// verificando se o usuario logado é do comercial impresso
if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		 where ( 
			(s.SalesTeamId = 3) or
			(s.SalesTeamId = 19)
		)	
	";
}

// verificando se o usuario logado eh do comercial web
if (isset ( $vPermissao ['web'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		 where ( 
			(s.SalesTeamId = 7)
		)	
	";
}

$sql .= " order by s.TeamName asc";

$rs = $db->Execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		if ($o->TEAMID == 3) {
			if (isset ( $_POST ['time'] ) && $_POST ['time'] == $o->TEAMID) {
				@$tpl->LOTACAO_SELECIONA = 'selected';
			} else {
				@$tpl->clear ( LOTACAO_SELECIONA );
			}
			$tpl->TEAMID = $o->TEAMID;
			$tpl->DESCRICAO = utf8_encode ( $o->DESCRICAO );
			$tpl->block ( 'BLOCK_OPTION_LOTACAO' );
		}
	}
}

########################## VERIFICA TIME DE VENDA E VENDEDORES ########################

$sAcesso = isset ( $_POST ["time"] ) && ! empty ( $_POST ["time"] ) ? $_POST ["time"] : $_SESSION ['user'] ["acesso"];

if($sAcesso == 3){
	$sAcesso = 'noticiario';
	}

$sSqlAcessoJoin = "";
$sSqlAcessoWhere = "";

if (count ( $_POST )) {
	
	$vData = getDate ();
	
	$tpl->TIME = $_POST ["time"];
	
	$dDataAtual = $vData ["mday"] . "/" . $vData ["mon"] . "/" . $vData ["year"];
	
	$user = $_POST ['user'];
	$time = $_POST ['time'];
	$tipo_data = $_POST ['fDataTipo'];
	$tpl->DATA_DE = $tipo_data;
	
	if($time == 3 || $time == 19 ){
		$tpl->AREA = 'noticiario';
		}
		
	if($time != 3 && $time != 19 && $time != 7){
		$tpl->AREA = 'tem';
		}
	
	
	$data1 = isset ( $_POST ["data1"] ) && ! empty ( $_POST ["data1"] ) ? $_POST ["data1"] : $dDataAtual;
	$data2 = isset ( $_POST ["data2"] ) && ! empty ( $_POST ["data2"] ) ? $_POST ["data2"] : $dDataAtual;
	
	$data1br = isset ( $_POST ["data1"] ) && ! empty ( $_POST ["data1"] ) ? $_POST ["data1"] : $dDataAtual;
	$data2br = isset ( $_POST ["data2"] ) && ! empty ( $_POST ["data2"] ) ? $_POST ["data2"] : $dDataAtual;
	
	if (isset ( $_POST ["fDataTipo"] ) && $_POST ["fDataTipo"] != "") {
		
		switch ($_POST ["fDataTipo"]) {
			case "publicacao" :
				$sSqlAcessoWhere = "and
			      i.AdOrderId = o.Id and
			      i.DeletedFlag = 0 and
			      convert(datetime,convert(varchar,i.InsertDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,i.InsertDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103)";
				$tpl->PUBLICACAO_SELECIONA = "selected";
				break;
			
			case "criacao" :
				$sSqlAcessoWhere = "and 
				  convert(datetime,convert(varchar,o.createDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,o.createDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103)";
				$tpl->CRIACAO_SELECIONA = "selected";
				break;
		}
	}
	
	$tpl->DATA_INICIO = $data1;
	$tpl->DATA_FIM = $data2;
	
	
if ($sAcesso == "noticiario") {
	
	if (isset ( $_POST ["fDataTipo"] ) && ! empty ( $_POST ["fDataTipo"] )) {
		
		$tpl->DATA_INICIO = $data1;
		$tpl->DATA_FIM = $data2;
		
		switch ($_POST ["fDataTipo"]) {
			case "publicacao" :
				$sSqlAcessoWhere .= "and
			      i.AdOrderId = o.Id and
			      i.DeletedFlag = 0 and
			      convert(datetime,convert(varchar,i.InsertDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,i.InsertDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103) and 
				  (i.DeletedFlag = 0)";
				$sJoinInsertion = "left join AoInsertion i on (i.AdOrderId = o.Id)";
				$tpl->PUBLICACAO_SELECIONA = "selected";
				$tpl->DATA_DE = "publicacao";
				break;
			
			case "criacao" :
				$sJoinInsertion = "";
				$sSqlAcessoWhere .= "and 
				  convert(datetime,convert(varchar,o.createDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,o.createDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103)";
				$tpl->CRIACAO_SELECIONA = "selected";
				$tpl->DATA_DE = "criacao";
				break;
		}
				
		$sql = "
		select 
			c.FederalId 'doc',
	    	c.Name1 'nome'
		from 
		 customer c
		left join aoordercustomers oc on (oc.CustomerId = c.AccountId and oc.orderedby = 1)
		left join AoAdOrder o on (o.Id = oc.AdOrderId)
		left join UsrUsers u on (o.SellerId = u.UserId)
		$sJoinInsertion	
		where			
			(o.SellerId = ".$_POST ["user"].")
			$sSqlAcessoWhere 
			group by c.FederalId, c.Name1 having c.FederalId <> ''
			order by c.FederalId
			";
		
		$rs = $db->execute ( $sql );
		
		if ($rs) {
			$nTotalGeral = 0;
			$faturamento = array ();
			while ( $o = $rs->FetchNextObject () ) {
				
				$sql2 = "
			select 
				c.FederalId 'doc',
	    		o.Id 'ordem',
	    		o.NetAmount 'valor'
			from
				customer c
			left join aoordercustomers oc on (oc.CustomerId = c.AccountId and oc.orderedby = 1)
			left join AoAdOrder o on (o.Id = oc.AdOrderId)
			left join UsrUsers u  on (o.SellerId = u.UserId)
			$sJoinInsertion
			where
			(o.SellerId = 144) 
			$sSqlAcessoWhere
	    	and (c.FederalId = '{$o->DOC}')
			group by c.FederalId, o.Id, o.NetAmount having (c.FederalId <> '')
			order by c.FederalId, o.NetAmount desc
			";
				
				$rs2 = $db->execute ( $sql2 );
				
				if ($rs2) {
					$total = 0;
					while ( $o2 = $rs2->FetchNextObject () ) {
						$total += $o2->VALOR;						
					}
					$faturamento [$o->DOC] ["nome"] = $o->NOME;
					$faturamento [$o->DOC] ["doc"] = $o->DOC;
					$faturamento [$o->DOC] ["total"] = $total;
				
				}
			}
		}
	}

}

arsort ( $faturamento );

if (count ( $faturamento )) {
	
	foreach ( $faturamento as $vCliente ) {
		$nTotalGeral += $vCliente ["total"];
		//$tpl->DOC = $vCliente ["doc"];
		//$tpl->CLIENTE = utf8_encode ( $vCliente ["nome"] );
		//$tpl->TOTAL = number_format ( $vCliente ["total"], 2, ",", "." );
	}
	
	//$tpl->block ( "BLOCK_LINHA" );
		
	$sql2 = "select 
	UserFname 'nome1',
    UserLname 'nome2'
	from
	UsrUsers 
    where UserId = ".$_POST ["user"];

	$rs2 = $db->Execute ( $sql2 );
	while ( $o = $rs2->FetchNextObject () ) {
		$tpl->NOME_USER = utf8_encode($o->NOME1.' '.$o->NOME2);
		}	
	$tpl->TOTAL_GERAL = number_format($nTotalGeral,2,",",".");
	$tpl->block ( "BLOCK_TABELA" );
}

}
$tpl->show ();
?>