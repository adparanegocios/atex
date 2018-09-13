<?php
set_time_limit ( 0 );
@session_start ();
include_once '../conexao.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'cliente-tpl.php' );

# INICIO INCLUDE #
$tpl->addFile ( "HEAD", "../head-tpl.php" );
$tpl->addFile ( "TOPO", "../topo-tpl.php" );

if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "../menu-tpl.php" );
} else {
	$tpl->addFile ( "MENU", "../menu-tpl2.php" );
}

$tpl->addFile ( "SUBMENU", "../menu-financeiro-tpl.php" );
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


if ($_SESSION ['user'] ["acesso"] == "controladoria" || $_SESSION ['user'] ["acesso"] == "noticiario") {
	$tpl->block ( "BLOCK_AGENCIA" );
}
// LIBERAR MENU AGENCIA


$vData = getDate ();

$dDataAtual = $vData ["mday"] . "/" . $vData ["mon"] . "/" . $vData ["year"];

$data1 = isset ( $_POST ["fDataInicio"] ) && ! empty ( $_POST ["fDataInicio"] ) ? $_POST ["fDataInicio"] : $dDataAtual;
$data2 = isset ( $_POST ["fDataFim"] ) && ! empty ( $_POST ["fDataFim"] ) ? $_POST ["fDataFim"] : $dDataAtual;

$faturamento = array ();
$nTotalGeral = 0;

$sAcesso = isset ( $_POST ["fAcesso"] ) && ! empty ( $_POST ["fAcesso"] ) ? $_POST ["fAcesso"] : $_SESSION ['user'] ["acesso"];

if ($_SESSION ['user'] ["acesso"] == "controladoria") {
	$tpl->block ( "BLOCK_CONTROLADORIA_SELECT" );
	$tpl->block ( "BLOCK_CONTROLADORIA_VALIDACAO" );
}

switch ($sAcesso) {
	case "tem" :
		$sSqlAcessoWhere = " u.SalesTeamNameId not in(7,3,19) ";
		break;
	
	case "noticiario" :
		$sSqlAcessoWhere = " u.SalesTeamNameId in(3,19) ";
		break;
	
	case "controladoria" :
		$sSqlAcessoJoin = "";
		$sSqlAcessoWhere = "";
		break;

}

$sSqlNome = isset($_POST ["fNome"]) && !empty($_POST ["fNome"]) ? " and c.Name1 like '%".$_POST ["fNome"]."%' " : "";
$tpl->NOME_CLIENTE = isset($_POST ["fNome"]) && !empty($_POST ["fNome"]) ? $_POST ["fNome"] : '';
// NOME CLIENE

// VERIFICAR ACESSO POR USUARIO
$tpl->AREA = @$_POST ["fAcesso"] != "" ? $_POST ["fAcesso"] : $_SESSION ['user'] ["acesso"];

if ($sAcesso == "tem" || $sAcesso == "noticiario") {
	
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
	left join aoordercustomers oc on (oc.CustomerId = c.AccountId and oc.CustomerTypeId = c.TypeId and oc.orderedby = 1)
	left join AoAdOrder o on (o.Id = oc.AdOrderId)
	left join UsrUsers u on (o.SellerId = u.UserId)
	$sJoinInsertion			
	where
		$sSqlAcessoWhere 
		$sSqlNome
	group by c.FederalId, c.Name1 having c.FederalId <> ''
	order by c.FederalId
	";
		
		
		
		$rs = $db->execute ( $sql );
		
		if ($rs) {
			$faturamento = array ();
			while ( $o = $rs->FetchNextObject () ) {
				$sql2 = "
			select 
				c.FederalId 'doc',
	    		o.Id 'ordem',
	    		o.NetAmount 'valor'
			from
				customer c
			left join aoordercustomers oc on (oc.CustomerId = c.AccountId and oc.CustomerTypeId = c.TypeId and oc.orderedby = 1)
			left join AoAdOrder o on (o.Id = oc.AdOrderId)
			left join UsrUsers u  on (o.SellerId = u.UserId)
			$sJoinInsertion
			where
			$sSqlAcessoWhere
	    	and (c.FederalId = '{$o->DOC}') 
			$sSqlNome
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

if ($sAcesso == "web" && isset ( $_POST ["fDataTipo"] ) && ! empty ( $_POST ["fDataTipo"] )) {
	
	$tpl->DATA_INICIO = $data1;
	$tpl->DATA_FIM = $data2;
	switch ($_POST ["fDataTipo"]) {
		case "publicacao" :
			$sSqlWhereTipoData = "and
			      i.AdOrderId = o.Id and
			      i.DeletedFlag = 0 and
			      convert(datetime,convert(varchar,i.InsertDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,i.InsertDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103)";
			$tpl->PUBLICACAO_SELECIONA = "selected";
			$tpl->DATA_DE = "publicacao";
			break;
		
		case "criacao" :
			$sSqlWhereTipoData = "and 
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
	customer c,
	aoordercustomers oc,
	AoAdOrder o,
	AoInsertion i,
	UsrUsers u 
	WHERE
	oc.CustomerId = c.AccountId and
	oc.orderedby = 1 and
	o.Id = oc.AdOrderId and
	o.SellerId = u.UserId and
	u.SalesTeamNameId = 7  
      $sSqlWhereTipoData 
	  $sSqlNome
	group by c.FederalId, c.Name1 having c.FederalId <> ''
	order by c.FederalId
	";
	      
	//echo nl2br( $sql );exit;
	
	$rs = $db->execute ( $sql );
	
	if ($rs) {
		$faturamento = array ();
		while ( $o = $rs->FetchNextObject () ) {
			$sql2 = "select
			c.FederalId 'doc',
		    o.Id 'ordem', 
	        o.NetAmount 'valor'      
			from
		     customer c, 
		     aoordercustomers oc, 
		     AoAdOrder o, 
		     AoInsertion i,
		     UsrUsers u
		    WHERE
		      oc.CustomerId = c.AccountId and 
		      oc.orderedby = 1 and 
		      o.Id = oc.AdOrderId and
		      c.FederalId = '{$o->DOC}' and
		      o.SellerId = u.UserId and
			  u.SalesTeamNameId = 7 
		      $sSqlWhereTipoData 
			  $sSqlNome
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

arsort ( $faturamento );

if (isset ( $_POST ["fAcesso"] ) && $_POST ["fAcesso"] != "") {
	switch ($_POST ["fAcesso"]) {
		case "web" :
			$tpl->WEB_SELECIONA = "selected";
			break;
		case "noticiario" :
			$tpl->NOTICIARIO_SELECIONA = "selected";
			break;
		case "tem" :
			$tpl->TEM_SELECIONA = "selected";
			break;
	}
	
}

if (count ( $faturamento )) {
	
	foreach ( $faturamento as $vCliente ) {
		$nTotalGeral += $vCliente ["total"];
		$tpl->DOC = $vCliente ["doc"];
		$tpl->CLIENTE = utf8_encode ( $vCliente ["nome"] );
		$tpl->TOTAL = number_format ( $vCliente ["total"], 2, ",", "." );
		$tpl->block ( "BLOCK_LINHA" );
	}
	$tpl->TOTAL_GERAL = number_format($nTotalGeral,2,",",".");
	$tpl->block ( "BLOCK_TABELA" );
}

$tpl->show ();
?>