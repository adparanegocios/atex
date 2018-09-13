<?php

date_default_timezone_set ( "Brazil/East" );

session_start ();
include_once '../conexao.php';
include_once '../classes/Template.class.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'rel-caixa.html' );
include_once '../menu.php';

// verificando se o usuario logado eh do tem! ou da controladoria
if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ('BLOCK_TEM');
}

// verificando se o usuario logado eh do noticiario ou da controladoria
if (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ('BLOCK_NOTICIARIO');
}

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
		$tpl->TEAMID = $o->TEAMID;
		$tpl->DESCRICAO = utf8_encode ( $o->DESCRICAO );
		$tpl->block ( 'BLOCK_OPTION_LOTACAO' );
	}
}

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
