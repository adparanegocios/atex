<?php
set_time_limit ( 0 );
error_reporting (E_ALL & ~E_NOTICE);
@session_start ();
include_once '../conexao.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'rel-lojas-tpl.php' );

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

// recuperando todos os usuÃ¡rio que estejam vinculado ao time callcenter (9)
$sql = "
select 
	u.UserId 'id',
    u.UserFname+' '+u.UserLname 'usuario' 
from 
	UsrUsers u
where (
    (not (u.SalesTeamNameId is NULL)) and u.SalesTeamNameId IN (18, 20)
";


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

/*$sql = "
select 
	s.SalesTeamId 'teamid',
    s.TeamName 'descricao'
from
	SalesTeamName s
where s.SalesTeamId IN (18, 20) 
";
$sql .= " order by s.TeamName asc";

$rs = $db->Execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		$tpl->TEAMID = $o->TEAMID;
		$tpl->DESCRICAO = utf8_encode ( $o->DESCRICAO );
		$tpl->block ( 'BLOCK_OPTION_LOTACAO' );
	}
}*/


$tpl->DESCRICAO = utf8_encode ( 'TEM! Classificados' );


if(count($_POST)){
	
	$vData = getDate ();
	$dDataAtual = $vData ["mday"] . "/" . $vData ["mon"] . "/" . $vData ["year"];
	
	$user = $_POST['user'];
	$time = $_POST['time'];
	$data1 = isset ( $_POST ["data1"] ) && ! empty ( $_POST ["data1"] ) ? $_POST ["data1"] : $dDataAtual;
	$data2 = isset ( $_POST ["data2"] ) && ! empty ( $_POST ["data2"] ) ? $_POST ["data2"] : $dDataAtual;
	
	$tpl->DATA_INICIO = $data1;
	$tpl->DATA_FIM = $data2;
	
	/*switch ($time) {
		case "-3":
			$tpl->NOTICIARIO_SELECIONA = "selected";
		break;
		case "-1":
			$tpl->TEM_SELECIONA_TODOS = "selected";
		break;
		case "-2":
			$tpl->TEM_SELECIONA_LOJAS = "selected";
		break;
		
	}*/
	
	// recuperando o time de venda do usuÃ¡rio
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
	
	if (!empty($user)) {
		$sql .= "
		(t.SalesTeamId = (SELECT t.SalesTeamId FROM SalesTeamName t (nolock) WHERE t.SalesTeamId = (SELECT u.SalesTeamNameId FROM UsrUsers u (nolock) WHERE u.UserId =  $user)))
	";
	} else {
	
	$sql .= "
		(t.SalesTeamId IN (18, 20) )
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
		//recuperando todas as vendas por usuÃ¡rio e por tipo de moeda
		//DECLARE @inicio DATETIME = getdate();
		//DECLARE @fim DATETIME = getdate();
		$vData1 = explode('/',$data1);
		$sInicio = $vData1[2].'-'.$vData1[1].'-'.$vData1[0];
		$vData2 = explode('/',$data2);
		$sFim = $vData2[2].'-'.$vData2[1].'-'.$vData2[0];
		
		$inicio = $sInicio.' 00:00:00.000';
		$fim = $sFim.' 23:59:59.999';
		
		
		$sSql = "
			SELECT 				
				u.UserFname+' '+u.UserLname AS [captador],
				CASE 
					WHEN  
						(SELECT TOP 1 r.RefAdOrder FROM CoFulfillmentRec r (nolock) WHERE r.RefAdOrder = o.Id) <> ''
					THEN 
						-1
					ELSE
						p.Id		 
				END AS [id],
				CASE 
					WHEN  
						(SELECT TOP 1 r.RefAdOrder FROM CoFulfillmentRec r (nolock) WHERE r.RefAdOrder = o.Id) <> ''
					THEN 
						'Contrato'
					ELSE
						p.Name		 
				END AS [modalidade], /* forma de pagamento*/
				o.NetAmount AS [valor], /* total por forma de pagamento para cada captador */
				(SELECT count(*) FROM AoInsertion i (nolock) WHERE i.AdOrderId = o.Id AND i.DeletedFlag = 0) AS [anuncios]
			FROM PaymentMethodType p (nolock)
			INNER JOIN AoOrderCustomers c (nolock) ON (c.PaymentMethod = p.Id)
			INNER JOIN AoAdOrder o (nolock) ON (o.Id = c.AdOrderId AND c.PayedBy = 1)
			INNER JOIN UsrUsers u (nolock) ON (u.UserId = o.RepId)
			WHERE
				
				/*********  buscando por periodo em que as ordens foram criadas    **********/
				o.CreateDate >= '".$inicio."' and o.CreateDate <= '".$fim."'AND
				/*********  buscando por periodo em que as ordens foram criadas    **********/
				
				/*********  pegando todas as ordens que serao, de fato, publicadas  *********/
				o.OrderStatusId = 4 AND
				O.CURRENTQUEUE IN (3, 8) AND
				
				/*********  pegando todas as ordens que serao, de fato, publicadas  *********/
				
				
				";
			if($user){
				$sSql .= "	
				/********   pegando o total para o Call Center        ********/
				u.UserId = ".$user;
			}else{
				$sSql .= "	
				/********   pegando o total para o Call Center        ********/
				u.SalesTeamNameId IN (18, 20) ";
			}
			
			$sSql .= "	
				/********   pegando o total para o Call Center        ********/
			
			ORDER BY captador, modalidade
		";
		
		//echo $sSql;exit;
		$rs2 = $db->Execute ($sSql);
		
		$vModalidades = array();
		$vVendedores = array();
		$totalG = 0;
		$totalGP = 0;
		$totalA = 0;
		$totalAP = 0;
		while ( $o2 = $rs2->FetchNextObject () ) {		
			
			
			$vGeral[$o2->MODALIDADE][$o2->CAPTADOR]['NANUNCIO'] += $o2->ANUNCIOS;
			$vGeral[$o2->MODALIDADE][$o2->CAPTADOR]['VALOR'] += $o2->VALOR;
			
			/*$tpl->CAPTADOR = utf8_encode ( $o2->CAPTADOR );
			$tpl->MODALIDADE = utf8_encode ( $o2->MODALIDADE );
			$tpl->NANUNCIO = ( $o2->ANUNCIOS );
			$tpl->VALOR = 'R$ '.number_format($o2->VALOR,2,',','.');*/
			
			
			
			
			
		}
		
		foreach($vGeral as $modalidade=>$vCaptador){
			$tpl->MODALIDADE = utf8_encode ( $modalidade );
			foreach($vCaptador as $captador=>$vDetalhe){
				$tpl->CAPTADOR = utf8_encode ( $captador );				
				$tpl->NANUNCIO = ( $vDetalhe['NANUNCIO'] );
				$tpl->VALOR = 'R$ '.number_format($vDetalhe['VALOR'],2,',','.');
				
				if(Util::retira_acentos(utf8_encode($modalidade))<>'Compensacao' && Util::retira_acentos(utf8_encode($modalidade) )<>	'Cortesia' && Util::retira_acentos(utf8_encode($modalidade) )<>'Contrato' && Util::retira_acentos(utf8_encode($modalidade) )<>'Permuta'){
					
					$totalG += $vDetalhe['VALOR'];
					$totalA += $vDetalhe['NANUNCIO'];
					$vVendedores[utf8_encode ( $captador )] += ($vDetalhe['VALOR']);
					$vVendedoresAnuncios[utf8_encode ( $captador )] += ($vDetalhe['NANUNCIO']);
					
				}
				$vVendedoresProd[utf8_encode ( $captador )] += ($vDetalhe['VALOR']);
				$vVendedoresAnunciosProducao[utf8_encode ( $captador )] += ($vDetalhe['NANUNCIO']);
				$totalGP += $vDetalhe['VALOR'];
				$totalAP += $vDetalhe['NANUNCIO'];
				//$vVendedoresAnuncios[utf8_encode ( $captador )] += ($vDetalhe['NANUNCIO']);
				$vModalidades[utf8_encode ( $modalidade )] += ($vDetalhe['VALOR']);		
				$vModalidadesAnuncios[utf8_encode ( $modalidade )] += ($vDetalhe['NANUNCIO']);	
				$tpl->block ( 'BLOCK_DADOS' );		
			}
				
		}
		
		//var_dump($vGeral);exit;
		$tpl->TOTALGERAL = number_format($totalG,2,',','.');
		$tpl->TOTALGERALPRODUCAO = number_format($totalGP,2,',','.');
		$tpl->TOTALANUNCIOS = $totalA;
		$tpl->TOTALANUNCIOSPRODUCAO = $totalAP;
		$totalM = 0;
		$totalMa = 0;
		foreach($vModalidades as $modalidade=>$valor){
			$tpl->MODALIDADES = $modalidade;
			$tpl->TOTALMODALIDADE = number_format($valor,2,',','.');
			$tpl->MODALIDADESANUNCIOS = $vModalidadesAnuncios[$modalidade];
			$totalMa += $vModalidadesAnuncios[$modalidade];
			$totalM += $valor;			
			$tpl->block ( 'BLOCK_RESUMO_MODALIDADE' );			
		};
		$tpl->TOTALMODALIDADES = number_format($totalM,2,',','.');
		$tpl->TOTALMODALIDADESANUNCIOS = $totalMa;
		
		$totalV = 0;
		$totalVa = 0;
		$totalVP = 0;
		$totalVaP = 0;
		foreach($vVendedores as $vendedor=>$valor){
			$tpl->VENDEDORESANUNCIOS = $vVendedoresAnuncios[$vendedor];
			
			$tpl->VENDEDORES = $vendedor;
			
			$tpl->TOTALVENDEDOR = number_format($valor,2,',','.');
			
			$tpl->block ( 'BLOCK_RESUMO_VENDEDOR' );
			
			$totalVa += $vVendedoresAnuncios[$vendedor];
			$totalV += $valor;
			
			
			
		};
		foreach($vVendedoresProd as $vendedor=>$valor){
			
			$tpl->VENDEDORESANUNCIOSPROD = $vVendedoresAnunciosProducao[$vendedor];
			
			$tpl->VENDEDORESPROD = $vendedor;
			
			$tpl->TOTALVENDEDORPROD = number_format($vVendedoresProd[$vendedor],2,',','.');
			
			$tpl->block ( 'BLOCK_RESUMO_VENDEDORPROD' );
			
			$totalVaP += $vVendedoresAnunciosProducao[$vendedor];
			$totalVP += $vVendedoresProd[$vendedor];	
			
			
		};
		$tpl->TOTALVENDEDORESANUNCIOS = $totalVa;
		$tpl->TOTALVENDEDORES = number_format($totalV,2,',','.');
		$tpl->TOTALVENDEDORESANUNCIOSPROD = $totalVaP;
		$tpl->TOTALVENDEDORESPROD = number_format($totalVP,2,',','.');
		
		$sSql = "SELECT 
					u.UserFname+' '+u.UserLname AS [captador],
					c.Name1 AS [cliente],
					p.Description AS [pagamento],
					sum(o.NetAmount) AS [valor] 
				FROM UsrUsers u (nolock)
				INNER JOIN AoAdOrder o (nolock) ON (o.RepId = u.UserId)
				INNER JOIN AoOrderCustomers oc (nolock) ON (oc.AdOrderId = o.Id AND oc.PayedBy = 1)
				INNER JOIN Customer c (nolock) ON (c.AccountId = oc.CustomerId)
				INNER JOIN PaymentMethodType p (nolock) ON (p.Id = oc.PaymentMethod)
				WHERE
					o.OrderStatusId <> 2 AND
					u.SalesTeamNameId IN (18, 20) AND
					o.CreateDate >= '".$inicio."' and o.CreateDate <= '".$fim."' AND
					EXISTS (
						SELECT * FROM CoFulfillmentRec r (nolock) WHERE r.RefAdOrder = o.Id
					)
				GROUP BY u.UserFname+' '+u.UserLname, c.Name1, p.Description
				ORDER BY u.UserFname+' '+u.UserLname, c.Name1";
				
		$rs3 = $db->Execute ($sSql);
		
		$valor = 0;
		while ( $o3 = $rs3->FetchNextObject () ) {
			$tpl->block ( 'BLOCK_RESUMO_CONTRATO' );
			
			$tpl->CONCAPTADOR = utf8_encode($o3->CAPTADOR);
			$tpl->CONCLIENTE = utf8_encode($o3->CLIENTE);
			$tpl->CONMODALIDADE = utf8_encode($o3->PAGAMENTO);
			$tpl->CONVALOR = number_format($o3->VALOR,2,',','.');
			$valor += $o3->VALOR;
		}
		$tpl->CONTOTAL = $valor;
	}
	
	
	
} else {
	$tpl->block ( 'BLOCK_VAZIO' );
}
	
	
}


$tpl->show ();
?>