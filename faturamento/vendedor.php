<?php
set_time_limit ( 0 );
@session_start ();
include_once '../conexao.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'vendedor-tpl.php' );

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
if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ('BLOCK_TEM');
}

// verificando se o usuario logado eh do noticiario ou da controladoria
if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ('BLOCK_NOTICIARIO');
}

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
		if(isset($_POST['user']) && $_POST['user'] == $o->ID){
			@$tpl->USUARIO_SELECIONA = 'selected';
		}else{
			@$tpl->clear(USUARIO_SELECIONA);
		}
		$tpl->USERID = $o->ID;
		$tpl->USERNAME = utf8_encode ( $o->USUARIO );
		$tpl->block ( 'BLOCK_OPTION_USER' );
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
	if(isset($_POST['time']) && $_POST['time'] == $o->TEAMID){
			@$tpl->LOTACAO_SELECIONA = 'selected';
		}else{
			@$tpl->clear(LOTACAO_SELECIONA);
		}
		$tpl->TEAMID = $o->TEAMID;
		$tpl->DESCRICAO = utf8_encode ( $o->DESCRICAO );
		$tpl->block ( 'BLOCK_OPTION_LOTACAO' );
	}
}

########################## VERIFICA TIME DE VENDA E VENDEDORES ########################

if(count($_POST)){
	
	$vData = getDate ();
	
	$dDataAtual = $vData ["mday"] . "/" . $vData ["mon"] . "/" . $vData ["year"];
	
	$user = $_POST['user'];
	
	$time = $_POST['time'];
	$tipo_data = $_POST['fDataTipo'];
	
	$tpl->DATA_DE = $tipo_data;
		
	$data1 = isset ( $_POST ["data1"] ) && ! empty ( $_POST ["data1"] ) ? $_POST ["data1"] : $dDataAtual;
	$data2 = isset ( $_POST ["data2"] ) && ! empty ( $_POST ["data2"] ) ? $_POST ["data2"] : $dDataAtual;
	
	$data1br = isset ( $_POST ["data1"] ) && ! empty ( $_POST ["data1"] ) ? $_POST ["data1"] : $dDataAtual;
	$data2br = isset ( $_POST ["data2"] ) && ! empty ( $_POST ["data2"] ) ? $_POST ["data2"] : $dDataAtual;
	
if(isset($_POST ["fDataTipo"]) && $_POST ["fDataTipo"] != ""){	
	
switch ($_POST ["fDataTipo"]) {
		case "publicacao" :
			$sSqlWhereTipoData = "and
			      i.AdOrderId = o.Id and
			      i.DeletedFlag = 0 and
			      convert(datetime,convert(varchar,i.InsertDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,i.InsertDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103)";
			$tpl->PUBLICACAO_SELECIONA = "selected";
			break;
		
		case "criacao" :
			$sSqlWhereTipoData = "and 
				  convert(datetime,convert(varchar,o.createDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,o.createDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103)";
			$tpl->CRIACAO_SELECIONA = "selected";
			break;
	}
}
	
	$tpl->DATA_INICIO = $data1;
	$tpl->DATA_FIM = $data2;
	
	switch ($time) {
		case "-3":
			$tpl->NOTICIARIO_SELECIONA = "selected";
		break;
		case "-1":
			$tpl->TEM_SELECIONA_TODOS = "selected";
		break;
		case "-2":
			$tpl->TEM_SELECIONA_LOJAS = "selected";
		break;
		
	}
	
if ($user) {
	$sql = "
	select 
		u.SalesTeamNameId 'time'
	from 
		UsrUsers u
	where (
		(u.UserId = $user)
	)
	";
	
	$rs = $db->Execute ( $sql );
	$rs = fetch_array ( $rs );
	$time_venda = array_shift ( $rs );
}

$sql = "
select
	u.UserId 'Id',
	u.UserFname+' '+u.UserLname 'Captador',
    t.TeamName 'Time',
	 t.SalesTeamId 'id_time'    
from
	UsrUsers u
left join SalesTeamName t on (u.SalesTeamNameId = t.SalesTeamId)
where 
	(
";

if ($user) {
	$sql .= " ( u.UserId = $user ) and";
}

if ($time) {
	
	if ($time == - 1) { // recuperando todas as vendas para o TEM! Classificados
		$sql .= "
			(
					(t.SalesTeamId = 1) or
					(t.SalesTeamId = 8) or
					(t.SalesTeamId = 9) or
					(t.SalesTeamId = 10) or
					(t.SalesTeamId = 11) or
					(t.SalesTeamId = 12) or
					(t.SalesTeamId = 13) or
					(t.SalesTeamId = 14) or
					(t.SalesTeamId = 15) or
					(t.SalesTeamId = 16) or
					(t.SalesTeamId = 17)
				) 
		";
	} elseif ($time == - 2) { // recuperando todas as vendas para somente as lojas do TEM! Classificados
		$sql .= "
			(
					(t.SalesTeamId = 1) or
					(t.SalesTeamId = 10) or
					(t.SalesTeamId = 11) or
					(t.SalesTeamId = 12) or
					(t.SalesTeamId = 13) or
					(t.SalesTeamId = 14) or
					(t.SalesTeamId = 15) or
					(t.SalesTeamId = 16) or
					(t.SalesTeamId = 17)
				) 
		";
	} elseif ($time == - 3) { // recuperando todas as vendas para somente o comercial noticiario
		$sql .= "
			(
					(t.SalesTeamId = 3) or
					(t.SalesTeamId = 19)
				) 
		";
	} else {
		$sql .= "
			(t.SalesTeamId = '$time')
		";
	}

} else {
	// verificando se o usuario logado eh do tem!
	if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] )) {
		$sql .= "
			(t.SalesTeamId <> 3)
			and (t.SalesTeamId <> 7)
			and (t.SalesTeamId <> 18)
			and (t.SalesTeamId <> 19)
		";
	}
	
	// verificando se o usuario logado eh do comercial impresso
	if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] )) {
		$sql .= "
			( (t.SalesTeamId = 3) or (t.SalesTeamId = 19) )		
		";
	}
	
	// verificando se o usuario logado eh do comercial impresso
	if (isset ( $vPermissao ['web'] [$_SESSION ['user'] ['login']] )) {
		$sql .= "
			(t.SalesTeamId = 7)		
		";
	}

}

$sql .= "
	)
";

$sql .= "
group by u.UserId, u.UserFname+' '+u.UserLname, t.TeamName, t.SalesTeamId
order by u.UserFname+' '+u.UserLname asc, t.TeamName asc
";

$rs = $db->Execute ( $sql );

if ($rs) {
	
	if ($rs->RecordCount () == 0) {
		$tpl->block ( 'BLOCK_VAZIO' );
	} else {
		$Tdinheiro = 0;
		$Tcartao = 0;
		$Tgratis = 0;
		$Tgeral = 0;
		
		/******************************* periodo especificado para a consulta ***********************************************/
		$sqldata = '';
		$left = '';
		
		if ($data1 && $data2) {
			list ( $d1, $m1, $a1 ) = explode ( '/', $data1 );
			list ( $d2, $m2, $a2 ) = explode ( '/', $data2 );
			
			$data1 = $a1 . '-' . $m1 . '-' . $d1;
			$data2 = $a2 . '-' . $m2 . '-' . $d2;
			
			$timestamp1 = strtotime ( $data1 );
			$timestamp2 = strtotime ( $data2 );
			
			$sqldata .= " ( ";
			
			while ( $timestamp1 <= $timestamp2 ) {
				
				$data = date ( 'd/m/Y', $timestamp1 );
				
				if ($timestamp1 != $timestamp2) {
					$sqldata .= "
						(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, '$data', 103),103)) or 
			";
				} else {
					$sqldata .= "
						(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, '$data', 103),103))
			";
				}
				
				$timestamp1 += 86400;
			
			}
			
			$sqldata .= " ) ";
		
		} elseif ($data1 && !$data2) {
			$sqldata .= "
						(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, '$data1', 103),103))
			";
		} elseif (!$data1 && $data2) {
			$sqldata .= "
						(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, '$data2', 103),103))
			";
		} else {
			$sqldata .= "(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, getdate(), 103),103))";
		}
		/******************************* periodo especificado para a consulta ***********************************************/
		
		if (! empty ( $time_venda )) {
			if ($time_venda ['time'] == 20) { // recuperando a renda por captador
				$left .= "left join AoAdOrder o on (u.UserId = o.RepId)";
			} else { // recuperando a renda por vendedor
				$left .= "left join AoAdOrder o on (u.UserId = o.SellerId)";
			}
		} else { // recuperando a renda por vendedor
			$left .= "left join AoAdOrder o on (u.UserId = o.SellerId)";
		}
		
		############################## SE FOR DATA DE PUBLICACAO #############################
			if(isset($_POST ["fDataTipo"]) && $_POST ["fDataTipo"] == "publicacao"){	
		
							$left .= " left join AoInsertion i on (i.AdOrderId = o.Id) ";
							
				}
		############################## SE FOR DATA DE PUBLICACAO #############################
		
		while ( $o = $rs->FetchNextObject () ) {
			$id = $o->ID;
			$tpl->CAPTADOR = utf8_encode ( $o->CAPTADOR );
			$tpl->USER = $o->ID;
			
			$time_user = isset($time_venda) ? $time_venda ['time'] : $time;	
									
			$tpl->TIME = $o->ID_TIME;
						
									
			if(abs($time_user) == 3 || $time_user == 19 ){
				$tpl->AREA = 'noticiario';
			}
			
			if(abs($time_user) != 3 && $time_user != 19 && $time_user != 7){
				$tpl->AREA = 'tem';
			}
			
			if($time_user == 7){
				$tpl->AREA = 'web';
			}
			
			/////////////////////////// achando o total em dinheiro ///////////////////////////////////////////////////////////
			$sql_dinheiro = "
			select 
				sum (o.NetAmount) 'dinheiro'	
			from
				UsrUsers u
			$left
			left join aoordercustomers oc on oc.adorderid = o.id and oc.payedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(p.Id = 8 or p.Id = 29 or p.Id = 36) and
    			(u.UserId = $id)
			";
			
			############################## SE FOR DATA DE PUBLICACAO #############################
			if(isset($_POST ["fDataTipo"]) && $_POST ["fDataTipo"] == "publicacao"){	
		
							$sqldata = "
							      i.AdOrderId = o.Id and
							      i.DeletedFlag = 0 and
							      convert(datetime,convert(varchar,i.InsertDate,103),103) >= convert(datetime,convert(varchar, '$data1br', 103),103) and
								  convert(datetime,convert(varchar,i.InsertDate,103),103) <= convert(datetime,convert(varchar, '$data2br', 103),103)";
							
				}
			############################## SE FOR DATA DE PUBLICACAO #############################
			
				
			$sql_dinheiro .= " and $sqldata";
			//echo nl2br($sql_dinheiro);exit;
			$rs_dinheiro = $db->Execute ( $sql_dinheiro );
			$rs_dinheiro = fetch_array ( $rs_dinheiro );
			$dinheiro = array_shift ( $rs_dinheiro );
			$dinheiro = $dinheiro ['dinheiro'];
			$Tdinheiro += $dinheiro;
			$tpl->DINHEIRO = ($dinheiro) ? number_format ( $dinheiro, 2, ',', '.' ) : 0;
			/////////////////////////// achando o total em dinheiro ///////////////////////////////////////////////////////////
			

			/////////////////////////// achando o total em cartao ///////////////////////////////////////////////////////////
			$sql_cartao = "
			select 
				sum (o.NetAmount) 'cartao'	
			from
				UsrUsers u
			$left
			left join aoordercustomers oc on oc.adorderid = o.id and oc.payedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(p.Id = 2 or p.Id = 4) and
    			(u.UserId = $id)
			";
			
			$sql_cartao .= " and $sqldata";
			
			$rs_cartao = $db->Execute ( $sql_cartao );
			$rs_cartao = fetch_array ( $rs_cartao );
			$cartao = array_shift ( $rs_cartao );
			$cartao = $cartao ['cartao'];
			$Tcartao += $cartao;
			$tpl->CARTAO = ($cartao) ? number_format ( $cartao, 2, ',', '.' ) : 0;
			/////////////////////////// achando o total em cartao ///////////////////////////////////////////////////////////
			

			/////////////////////////// achando o total gratis ///////////////////////////////////////////////////////////
			$sql_gratis = "
			select 
				sum (o.NetAmount) 'gratis'	
			from
				UsrUsers u
			$left
			left join aoordercustomers oc on oc.adorderid = o.id and oc.payedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(p.Id = 23 or p.Id = 39) and
    			(u.UserId = $id)
			";
			
			$sql_gratis .= " and $sqldata";
			
			$rs_gratis = $db->Execute ( $sql_gratis );
			$rs_gratis = fetch_array ( $rs_gratis );
			$gratis = array_shift ( $rs_gratis );
			$gratis = $gratis ['gratis'];
			$Tgratis += $gratis;
			$tpl->GRATIS = ($gratis) ? number_format ( $gratis, 2, ',', '.' ) : 0;
			
			/////////////////////////// achando o total gratis ///////////////////////////////////////////////////////////
						
			$tpl->LOTACAO = utf8_encode ( $o->TIME );
						
			$tpl->block ( 'BLOCK_LINHA' );
		}
		
		$Tgeral = $Tdinheiro + $Tcartao + $Tgratis;
		$tpl->TOTALD = number_format ( $Tdinheiro, 2, ',', '.' );
		$tpl->TOTALC = number_format ( $Tcartao, 2, ',', '.' );
		$tpl->TOTALG = number_format ( $Tgratis, 2, ',', '.' );
		$tpl->GERAL = number_format ( $Tgeral, 2, ',', '.' );
	
	}

	$tpl->block ( 'BLOCK_TABELA' );
	
} else {
	$tpl->block ( 'BLOCK_VAZIO' );
}



}

/*else{


###########################333


// levantando quanto cada vendedor fez ao longo do dia de trabalho
$sql = "
select 
	u.UserId 'id',
    u.UserFname+' '+u.UserLname 'Captador',
	t.TeamName 'Time'	
from
	UsrUsers u
left join AoAdOrder o on (u.UserId = o.SellerId)
left join SalesTeamName t on (u.SalesTeamNameId = t.SalesTeamId)
left join aoordercustomers oc on oc.adorderid = o.id and oc.orderedby = 1
left join PaymentMethodType p  on p.id = oc.PaymentMethod
where
convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, getdate(), 103),103)
";

// verificando se o usuario logado eh do tem!
if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		and (t.SalesTeamId <> 3)
		and (t.SalesTeamId <> 7)
		and (t.SalesTeamId <> 18)
		and (t.SalesTeamId <> 19)		
	";
}

// verificando se o usuario logado eh do comercial impresso
if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		and ( (t.SalesTeamId = 3) or (t.SalesTeamId = 19) )		
	";
}

// verificando se o usuario logado eh do comercial web
if (isset ( $vPermissao ['web'] [$_SESSION ['user'] ['login']] )) {
	$sql .= "
		and (t.SalesTeamId = 7)		
	";
}

$sql .= "
group by u.UserId, u.UserFname+' '+u.UserLname, t.TeamName
order by u.UserFname+' '+u.UserLname asc, t.TeamName asc
";

$rs = $db->Execute ( $sql );
if ($rs) {
	
	if ($rs->RecordCount () == 0) {
		$tpl->block ( 'BLOCK_VAZIO' );
	} else {
		$Tdinheiro = 0;
		$Tcartao = 0;
		$Tgratis = 0;
		$Tgeral = 0;
		while ( $o = $rs->FetchNextObject () ) {
			$id = $o->ID;
			$tpl->CAPTADOR = utf8_encode ( $o->CAPTADOR );
			
			/////////////////////////// achando o total em dinheiro ///////////////////////////////////////////////////////////
			$sql_dinheiro = "
			select 
				sum (o.NetAmount) 'dinheiro'	
			from
				UsrUsers u
			left join AoAdOrder o on (u.UserId = o.SellerId)
			left join aoordercustomers oc on oc.adorderid = o.id and oc.orderedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, getdate(), 103),103)) and
    			(p.Id = 8 or p.Id = 29 or p.Id = 36) and
    			(u.UserId = $id)
			";
			
			$rs_dinheiro = $db->Execute ( $sql_dinheiro );
			$rs_dinheiro = fetch_array ( $rs_dinheiro );
			$dinheiro = array_shift ( $rs_dinheiro );
			$dinheiro = $dinheiro ['dinheiro'];
			$Tdinheiro += $dinheiro;
			$tpl->DINHEIRO = ($dinheiro) ? number_format ( $dinheiro, 2, ',', '.' ) : 0;
			/////////////////////////// achando o total em dinheiro ///////////////////////////////////////////////////////////
			

			/////////////////////////// achando o total em cartao ///////////////////////////////////////////////////////////
			$sql_cartao = "
			select 
				sum (o.NetAmount) 'cartao'	
			from
				UsrUsers u
			left join AoAdOrder o on (u.UserId = o.SellerId)
			left join aoordercustomers oc on oc.adorderid = o.id and oc.orderedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, getdate(), 103),103)) and
    			(p.Id = 2 or p.Id = 4) and
    			(u.UserId = $id)
			";
			
			$rs_cartao = $db->Execute ( $sql_cartao );
			$rs_cartao = fetch_array ( $rs_cartao );
			$cartao = array_shift ( $rs_cartao );
			$cartao = $cartao ['cartao'];
			$Tcartao += $cartao;
			$tpl->CARTAO = ($cartao) ? number_format ( $cartao, 2, ',', '.' ) : 0;
			/////////////////////////// achando o total em cartao ///////////////////////////////////////////////////////////
			

			/////////////////////////// achando o total gratis ///////////////////////////////////////////////////////////
			$sql_gratis = "
			select 
				sum (o.NetAmount) 'gratis'	
			from
				UsrUsers u
			left join AoAdOrder o on (u.UserId = o.SellerId)
			left join aoordercustomers oc on oc.adorderid = o.id and oc.orderedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, getdate(), 103),103)) and
    			(p.Id = 23 or p.Id = 39) and
    			(u.UserId = $id)
			";
			
			$rs_gratis = $db->Execute ( $sql_gratis );
			$rs_gratis = fetch_array ( $rs_gratis );
			$gratis = array_shift ( $rs_gratis );
			$gratis = $gratis ['gratis'];
			$Tgratis += $gratis;
			$tpl->GRATIS = ($gratis) ? number_format ( $gratis, 2, ',', '.' ) : 0;
			/////////////////////////// achando o total gratis ///////////////////////////////////////////////////////////			
			

			$tpl->LOTACAO = utf8_encode ( $o->TIME );
			$tpl->block ( 'BLOCK_LINHA' );
		}
		
		$Tgeral = $Tdinheiro + $Tcartao + $Tgratis;
		$tpl->TOTALD = number_format ( $Tdinheiro, 2, ',', '.' );
		$tpl->TOTALC = number_format ( $Tcartao, 2, ',', '.' );
		$tpl->TOTALG = number_format ( $Tgratis, 2, ',', '.' );
		$tpl->GERAL = number_format ( $Tgeral, 2, ',', '.' );
	}

	$tpl->block ( 'BLOCK_TABELA' );
	
} else {
	$tpl->block ( 'BLOCK_VAZIO' );
}

}
*/
$tpl->show ();
?>