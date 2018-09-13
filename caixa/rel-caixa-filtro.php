<?php

date_default_timezone_set ( "Brazil/East" );

session_start ();
include_once '../conexao.php';
include_once '../classes/Template.class.php';
include_once '../classes/class.Util.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'rel-caixa-filtro.html' );

extract ( $_REQUEST );

// recuperando o time de venda do usuário
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
			
			$sql_dinheiro .= " and $sqldata";
			
			$rs_dinheiro = $db->Execute ( $sql_dinheiro );
			$rs_dinheiro = fetch_array ( $rs_dinheiro );
			$dinheiro = array_shift ( $rs_dinheiro );
			$dinheiro = $dinheiro ['dinheiro'];
			$Tdinheiro += $dinheiro;
			$tpl->DINHEIRO = ($dinheiro) ? number_format ( $dinheiro, 2, ',', '.' ) : 0;
			/////////////////////////// achando o total em dinheiro ///////////////////////////////////////////////////////////
			

			/////////////////////////// achando o total em cartao ///////////////////////////////////////////////////////////
			
			// recuperando todas as vendas através do adbasee
			if ($id == 1) {
				$sql_cartao = "
					select 
						case when sum(o.NetAmount) > 0 then cast(sum(o.NetAmount) as money) else 0 end as [web]
					from 
						AoAdOrder o (nolock)
					inner join AoOrderCustomers c (nolock) on (c.AdOrderId = o.Id and c.PayedBy = 1)
					inner join PaymentMethodType p (nolock) on (p.Id = c.PaymentMethod)
					where
						(p.Id = 41) and
						(o.SellerId = 1) and
						(o.OrderStatusId = 4) and
						(o.CurrentQueue = 3)
				"
			} else {
			
			
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
			
			}
			
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
						
			$tpl->block ( 'BLOCK_DADOS' );
		}
		
		$Tgeral = $Tdinheiro + $Tcartao + $Tgratis;
		$tpl->TOTALD = number_format ( $Tdinheiro, 2, ',', '.' );
		$tpl->TOTALC = number_format ( $Tcartao, 2, ',', '.' );
		$tpl->TOTALG = number_format ( $Tgratis, 2, ',', '.' );
		$tpl->GERAL = number_format ( $Tgeral, 2, ',', '.' );
	
	}

} else {
	$tpl->block ( 'BLOCK_VAZIO' );
}

$data = getdate ();
$tpl->DATAHORA = date ( 'd/m/Y' ) . '  ' . $data ['hours'] . ':' . $data ['minutes'];

$tpl->show ();

?>
