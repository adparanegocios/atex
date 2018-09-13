<?php
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

if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "../menu-tpl.php" );
} else {
	$tpl->addFile ( "MENU", "../menu-tpl2.php" );
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

// verificando se o usuario logado eh do tem! ou da controladoria
if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ( 'BLOCK_TEM' );
}

// verificando se o usuario logado eh do noticiario ou da controladoria
if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ( 'BLOCK_NOTICIARIO' );
}

// recuperando todos os usu�rio que estejam vinculado a um time de venda
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
		if (isset ( $_POST ['user'] ) && $_POST ['user'] == $o->ID) {
			@$tpl->USUARIO_SELECIONA = 'selected';
		} else {
			@$tpl->clear ( USUARIO_SELECIONA );
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
			(s.SalesTeamId <> 11) and
			(s.SalesTeamId <> 18) and
			(s.SalesTeamId <> 19)
		)	
	";
}

// verificando se o usuario logado eh do comercial impresso
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

if (count ( $_POST )) {
	
	$vData = getDate ();
	$dDataAtual = $vData ["mday"] . "/" . $vData ["mon"] . "/" . $vData ["year"];
	
	$user = $_POST ['user'];
	$time = $_POST ['time'];
	$data1 = isset ( $_POST ["data1"] ) && ! empty ( $_POST ["data1"] ) ? $_POST ["data1"] : $dDataAtual;
	$data2 = isset ( $_POST ["data2"] ) && ! empty ( $_POST ["data2"] ) ? $_POST ["data2"] : $dDataAtual;
	
	$tpl->DATA_INICIO = $data1;
	$tpl->DATA_FIM = $data2;
	
	switch ($time) {
		case "-3" :
			$tpl->NOTICIARIO_SELECIONA = "selected";
			break;
		case "-1" :
			$tpl->TEM_SELECIONA_TODOS = "selected";
			break;
		case "-2" :
			$tpl->TEM_SELECIONA_LOJAS = "selected";
			break;
	
	}
	
	// recuperando o time de venda do usu�rio
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
    t.TeamName 'Time'    
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
					(t.SalesTeamId = 17) or
					(t.SalesTeamId = 22) or
					(t.SalesTeamId = 23)
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
					(t.SalesTeamId = 17) or
					(t.SalesTeamId = 22) or
					(t.SalesTeamId = 23)
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
group by u.UserId, u.UserFname+' '+u.UserLname, t.TeamName
order by u.UserFname+' '+u.UserLname asc, t.TeamName asc
";
	//echo nl2br($sql);exit;
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
			
			} elseif ($data1 && ! $data2) {
				$sqldata .= "
						(convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar, '$data1', 103),103))
			";
			} elseif (! $data1 && $data2) {
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
			
			while ( $o = $rs->FetchNextObject () ) {
				$id = $o->ID;
				$tpl->CAPTADOR = utf8_encode ( $o->CAPTADOR );
				$nTotalQuant = 0;
				
				/////////////////////////// achando o total em dinheiro ///////////////////////////////////////////////////////////
				$sql_dinheiro = "
			select 
				sum (o.NetAmount) 'dinheiro',count (*) AS [total] 
			from
				UsrUsers u
			$left
			left join aoordercustomers oc on oc.adorderid = o.id and oc.payedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(p.Id IN (8, 17, 29, 36, 50)) and
    			(u.UserId = $id)
			";
				
				$sql_dinheiro .= " and $sqldata";
				
				$rs_dinheiro = $db->Execute ( $sql_dinheiro );
				$rs_dinheiro = fetch_array ( $rs_dinheiro );
				$dinheiro = array_shift ( $rs_dinheiro );
				$dinheiro = $dinheiro ['dinheiro'];
				$nTotalQuant += ($dinheiro ['total']) ? $dinheiro ['total'] : 0;
				$Tdinheiro += $dinheiro;
				$tpl->DINHEIRO = ($dinheiro) ? number_format ( $dinheiro, 2, ',', '.' ) : 0;
				/////////////////////////// achando o total em dinheiro ///////////////////////////////////////////////////////////
				

				/////////////////////////// achando o total em cartao ///////////////////////////////////////////////////////////
				

				// recuperando todas as vendas atrav�s do adbasee
				if ($id == 1) {
					$sql_cartao = "
				select 
					case when sum(o.NetAmount) > 0 then cast(sum(o.NetAmount) as money) else 0 end as [cartao], count (*) AS [total]
				from 
					AoAdOrder o (nolock)
				inner join AoOrderCustomers c (nolock) on (c.AdOrderId = o.Id and c.PayedBy = 1)
				inner join PaymentMethodType p (nolock) on (p.Id = c.PaymentMethod)
				where
					(p.Id = 41) and
					(o.SellerId = 1) and
					(o.OrderStatusId = 4) and
					(o.CurrentQueue = 3)
				";
				} else {
					
					$sql_cartao = "
			select 
				sum (o.NetAmount) 'cartao',	count (*) AS [total]
			from
				UsrUsers u
			$left
			left join aoordercustomers oc on oc.adorderid = o.id and oc.payedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(p.Id IN (2, 4, 43, 44, 45, 47, 49, 52, 26, 32, 33, 46, 48, 51)) and
    			(u.UserId = $id)
			";
				}
				$sql_cartao .= " and $sqldata";
				
				$rs_cartao = $db->Execute ( $sql_cartao );
				$rs_cartao = fetch_array ( $rs_cartao );
				$cartao = array_shift ( $rs_cartao );
				$cartao = $cartao ['cartao'];
				$nTotalQuant += ($cartao ['total']) ? $cartao ['total'] : 0;
				$Tcartao += $cartao;
				$tpl->CARTAO = ($cartao) ? number_format ( $cartao, 2, ',', '.' ) : 0;
				/////////////////////////// achando o total em cartao ///////////////////////////////////////////////////////////
				

				/////////////////////////// achando o total gratis ///////////////////////////////////////////////////////////
				$sql_gratis = "
			select 
				sum (o.NetAmount) 'gratis', count (*) AS [total]
			from
				UsrUsers u
			$left
			left join aoordercustomers oc on oc.adorderid = o.id and oc.payedby = 1
			left join PaymentMethodType p  on p.id = oc.PaymentMethod
			where
				(p.Id IN (23, 39)) and
    			(u.UserId = $id)
			";
				
				$sql_gratis .= " and $sqldata";
				
				$rs_gratis = $db->Execute ( $sql_gratis );
				$rs_gratis = fetch_array ( $rs_gratis );
				$gratis = array_shift ( $rs_gratis );
				$gratis = $gratis ['gratis'];
				$nTotalQuant += ($gratis ['total']) ? $gratis ['total'] : 0;
				$Tgratis += $gratis;
				$tpl->GRATIS = ($gratis) ? number_format ( $gratis, 2, ',', '.' ) : 0;
				/////////////////////////// achando o total gratis ///////////////////////////////////////////////////////////
				

				$tpl->LOTACAO = utf8_encode ( $o->TIME );
				
				$tpl->block ( 'BLOCK_DADOS' );
			}
			
			$Tgeral = $Tdinheiro + $Tcartao + $Tgratis;
			$tpl->TOTALD = number_format ( $Tdinheiro, 2, ',', '.' );
			$tpl->TOTALC = number_format ( $Tcartao, 2, ',', '.' );
			$tpl->TOTALG = number_format ( $Tgratis, 2, ',', '.' );
			$tpl->GERAL = number_format ( $Tgeral, 2, ',', '.' );
			
			$tpl->QUANTIDADE = ($nTotalQuant) ? $nTotalQuant : 0;
		
		}
		
		$tpl->block ( 'BLOCK_TABELA' );
	
	} else {
		$tpl->block ( 'BLOCK_VAZIO' );
	}

}

$tpl->show ();
?>