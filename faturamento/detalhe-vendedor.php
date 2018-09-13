<?php
set_time_limit ( 0 );
@session_start ();
include_once '../conexao.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'detalhe-tpl.php' );

# INICIO INCLUDE #
$tpl->addFile ( "HEAD", "../head-tpl.php" );

$vData = getDate ();

$dDataAtual = $vData ["mday"] . "/" . $vData ["mon"] . "/" . $vData ["year"];

$data1 = isset ( $_GET ["dt_inicio"] ) && ! empty ( $_GET ["dt_inicio"] ) ? $_GET ["dt_inicio"] : $dDataAtual;
$data2 = isset ( $_GET ["dt_fim"] ) && ! empty ( $_GET ["dt_fim"] ) ? $_GET ["dt_fim"] : $dDataAtual;

$sSqlAcessoJoin = "";
$sSqlWhereTipoData = "";


switch ($_GET['area']) {
case "tem" :
		$sSqlWhereTipoData = "and u.SalesTeamNameId not in(7,3,19) ";
		break;
	
	case "noticiario" :
		$sSqlWhereTipoData = "and u.SalesTeamNameId in(3,19) ";
	break;
	
	case "web" :
		$sSqlWhereTipoData = "and u.SalesTeamNameId in(7) ";
	break;
}

switch ($_GET ["tipo_data"]) {
		case "publicacao" :
			$sSqlWhereTipoData .= " and 
			      i.AdOrderId = o.Id and
			      i.DeletedFlag = 0 and
			      convert(datetime,convert(varchar,i.InsertDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,i.InsertDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103)";
				  $sJoinInsertion = "left join AoInsertion i on (i.AdOrderId = o.Id)";
			break;
		
		case "criacao" :
		$sJoinInsertion = "";
			$sSqlWhereTipoData .= " and 
				  convert(datetime,convert(varchar,o.createDate,103),103) >= convert(datetime,convert(varchar, '$data1', 103),103) and
				  convert(datetime,convert(varchar,o.createDate,103),103) <= convert(datetime,convert(varchar, '$data2', 103),103)";
			break;
	}
// Data de publicação


if(@$_GET['area'] == 'noticiario' || @$_GET['area'] == 'tem'){

$tpl->block('BLOCK_TIPO_ANUNCIO_COLUNA');

$sql = "select 
				c.FederalId 'doc',
				c.Name1 'nome',
				o.AdOrderNumber 'ordem',
	    		o.Id 'idordem',
	    		o.NetAmount 'valor',
                f.AdTypeId 'id_tipo',
				f.AdSubTypeId 'id_subtipo',
				o.RunDateFirst 'dt_inicio',
                o.RunDateLast 'dt_fim'
                 
			from
				customer c
			left join aoordercustomers oc on (oc.CustomerId = c.AccountId and oc.CustomerTypeId = c.TypeId and oc.orderedby = 1)
			left join AoAdOrder o on (o.Id = oc.AdOrderId)
			left join AoAdInfo f on (substring (f.AdNumber,0,11) = o.AdOrderNumber)
			left join UsrUsers u on (o.SellerId = u.UserId)
			$sJoinInsertion
			where		
			u.SalesTeamNameId = ".$_GET['time']." AND
	    	(o.SellerId = ".$_GET['user'].")
			$sSqlWhereTipoData
			group by c.FederalId, o.AdOrderNumber, o.Id, f.AdTypeId, c.Name1,o.RunDateFirst, o.RunDateLast,f.AdSubTypeId , o.NetAmount having (c.FederalId <> '')
			order by o.Id desc";


}

if(@$_GET['area'] == 'web'){

$sql = "select
			c.FederalId 'doc',
			o.AdOrderNumber 'ordem',
		    o.Id 'idordem', 
	        o.NetAmount 'valor',
			c.Name1 'nome',
			o.RunDateFirst 'dt_inicio',
            o.RunDateLast 'dt_fim'     
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
		     (o.SellerId = '".$_GET['user']."') and
			  u.SalesTeamNameId = ".$_GET['time']." and
		      o.SellerId = u.UserId and
			  u.SalesTeamNameId = 7 
			  $sSqlWhereTipoData
			group by c.FederalId,c.Name1, o.AdOrderNumber, o.Id, o.RunDateFirst, o.RunDateLast, o.NetAmount having (c.FederalId <> '') 
		    order by c.FederalId, o.NetAmount desc";

}


$rs = $db->execute ( $sql );
		if ($rs) {
			while ( $o = $rs->FetchNextObject () ) {
				
			$tpl->ORDEM = $o->ORDEM;
			$tpl->VALOR = number_format ( $o->VALOR, 2, ",", "." );
			$tpl->NOME = utf8_encode($o->NOME);
			$tpl->DT_INICIO = date('d/m/Y',strtotime($o->DT_INICIO));
			$tpl->DT_FIM = date('d/m/Y',strtotime($o->DT_FIM));
			
			
			if(@$_GET['area'] == 'noticiario' || @$_GET['area'] == 'tem'){
				
					
				$rsTipo = $db->execute ( '
				select 
					case when p.[Name] is null then s.[Name] else p.[Name] end as caderno
				from 
					AoAdOrder o
				left join AoAdRunSchedule r on (r.AdOrderId = o.Id)
				left join AoAdPositions p on (p.Id = r.PositionId)
				left join AoPlacements s on (s.Id = r.PlacementId)
				where o.Id = '.$o->IDORDEM.'');
				
				$vCaderno = array();
				
				if($rsTipo){
					while ( $tipo = $rsTipo->FetchNextObject () ) {
						$vCaderno[] = utf8_encode($tipo->CADERNO)." ";
						}
					if(count($vCaderno) > 0){
						$tpl->CADERNO = implode(', ',$vCaderno);;
						$tpl->block('BLOCK_TIPO_ANUNCIO');
						}
				}
				// TIPO
			}
			
			$tpl->block('BLOCK_DETALHE');
			}
			
		}

$tpl->show ();
?>