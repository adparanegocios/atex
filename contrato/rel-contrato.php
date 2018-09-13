<?php
error_reporting ( 0 );
ini_set ( 'display_errors', 0 );
set_time_limit ( 0 );
@session_start ();
include_once '../conexao.php';
include_once '../conexao2.php';
include_once '../classes/class.Util.php';
include_once '../classes/Template.class.php';
include_once '../global.php';
include_once '../validacao.php';

$tpl = new Template ( 'rel-contrato-tpl.php' );

# INICIO INCLUDE #
$tpl->addFile ( "HEAD", "../head-tpl.php" );
$tpl->addFile ( "TOPO", "../topo-tpl.php" );

if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "../menu-tpl.php" );
} elseif ($vPermissao ['assistente'] [$_SESSION ['user'] ['login']]) {
	$tpl->addFile ( "MENU", "../menu-tpl.assistente.php" );
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

// verificando se o usuario logado eh da controladoria
if (isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->block ( 'BLOCK_CONTROLADORIA' );
}

if (count ( $_POST )) {
	
	$vData = getDate ();
	$dDataAtual = $vData ["mday"] . "/" . $vData ["mon"] . "/" . $vData ["year"];
	
	$departamento = isset ( $_POST ['departamento'] ) ? $_POST ['departamento'] : 1;
	$instancia = $_POST ['instancia'];
	$opcliente = $_POST ['opcliente'];
	$cliente = $_POST ['cliente'];
	
	//$data1 = isset ( $_POST ["data1"] ) && ! empty ( $_POST ["data1"] ) ? $_POST ["data1"] : $dDataAtual;
	//$data2 = isset ( $_POST ["data2"] ) && ! empty ( $_POST ["data2"] ) ? $_POST ["data2"] : $dDataAtual;
	$data1 = (! empty ( $_POST ["data1"] )) ? $_POST ["data1"] : '';
	$data2 = (! empty ( $_POST ["data2"] )) ? $_POST ["data2"] : '';
	
	$tpl->INSTANCIA = $instancia;
	$tpl->DADO = $cliente;
	
	if ($opcliente == 1) {
		$tpl->OPCAODEFAULT1 = 'selected';
	} elseif ($opcliente == 2) {
		$tpl->OPCAODEFAULT2 = 'selected';
	} elseif ($opcliente == 3) {
		$tpl->OPCAODEFAULT3 = 'selected';
	} else {
		$tpl->clear ( 'OPCAODEFAULT1' );
		$tpl->clear ( 'OPCAODEFAULT2' );
		$tpl->clear ( 'OPCAODEFAULT3' );
	}
	
	$tpl->DATA_INICIO = ! empty ( $data1 ) ? $data1 : '';
	$tpl->DATA_FIM = ! empty ( $data2 ) ? $data2 : '';
	
	switch ($departamento) {
		case 1 :
			$tpl->SELECIONA_TEM = "selected";
			break;
		case 2 :
			$tpl->SELECIONA_NOTICIARIO = "selected";
			break;
		case 3 :
			$tpl->SELECIONA_WEB = "selected";
			break;
	}
	
	$sql = "
	select distinct 
		i.[Name] 'instancia',
		i.[Notes] 'obs',
	    i.[Status] 'status',
	    c3.Name1 'cliente',
	    c3.Name2 'fantasia',
	    c3.FederalId 'documento',        
	    i.StartDate 'inicio',
	    i.EndDate 'fim',
	    (SELECT TOP 1 convert(VARCHAR(10),o.RunDateLast,103) FROM AoAdOrder o (nolock) WHERE o.Id IN (
			SELECT DISTINCT r.RefAdOrder FROM CoFulfillmentRec r (nolock) WHERE r.ContractInstanceId = i.Id
		) ORDER BY o.RunDateLast DESC) AS [ultima],
	    (case when sum (f.Var1Actual) <> '' then count(*) else 0 end) 'qtdpublicacoes',
	    (
	    	case 
	        	when n.[Description] is null 
	        		then d.[Description] 
	        	else n.[Description]  
	        end
	    ) 'pacote',
	    sum (f.Var1Actual) 'var1real',
	    (
	    	case 
	        	when n.Var1LowerLimit is null 
	            	then d.Var1LowerLimit 
	            else n.Var1LowerLimit  
	        end
	    ) 'var1esperado',
	    (
	    	case 
	        	when n.Var1LowerLimit is null 
	            	then d.Var1LowerLimit-sum (f.Var1Actual) 
	            else n.Var1LowerLimit-sum (f.Var1Actual)  
	        end
	    ) 'restavar1',
	    (
	    	case 
	        	when n.Var1LowerLimit is null 
	            	then (sum (f.Var1Actual)*100)/d.Var1LowerLimit 
	        	else (sum (f.Var1Actual)*100)/n.Var1LowerLimit  
	        end
	    ) 'porcentagemvar1',
	    sum (f.Var2Actual) 'var2real',
	    (
	    	case 
	        	when n.Var2LowerLimit is null 
	            	then d.Var2LowerLimit 
	            else n.Var2LowerLimit  
	        end
	    ) 'var2esperado',
	    (
	    	case when 
	        	n.Var2LowerLimit is null 
	            	then d.Var2LowerLimit-sum (f.Var2Actual) 
	            else n.Var2LowerLimit-sum (f.Var2Actual)  
	        end
	    ) 'restavar2',
	    (
	    	case 
	        	when n.Var2LowerLimit = '' or d.Var2LowerLimit = '' 
	            	then '' 
	            else 
	            	(
	                	case 
	                    	when n.Var1LowerLimit is null 
	                        	then (sum (f.Var2Actual)*100)/d.Var2LowerLimit 
	                        else (sum (f.Var2Actual)*100)/n.Var2LowerLimit  
	                    end 
	                )           	   
	        end
	    ) 'porcentagemvar2',
	    (SELECT upper(v.Name1+' '+v.Name2) FROM Customer v (nolock) WHERE v.AccountId = i.RefAccountId) 'vendedor'  
	from 
		CoContractInstance i
	left join CoRateLevel n on (i.ContractTemplateId = n.OwnerId and i.LevelSignedUpTo = n.ContractLevel)
	left join CoDiscountLevel d on (i.ContractTemplateId = d.OwnerId and i.LevelSignedUpTo = d.ContractLevel)
	left join CoFulfillmentRec f on (i.Id = f.ContractInstanceId)
	left join CoCustomerAcctEntry c1 on (i.Id = c1.OwnerId)
	left join CustomerAccNumber c2 on (c1.PayorAcctId = c2.Id)
	left join Customer c3 on (c2.CustAccNumberAccId = c3.AccountId)
	where (
		(i.[Status] = 2)
	";
	
	if (isset ( $departamento )) {
		if ($departamento == 1) { // contratos do tem!
			$sql .= " and (i.[Name] like 'TEM-%')";
		}
		
		if ($departamento == 2) { // contratos do comercial impresso
			$sql .= " and (i.[Name] like 'COM-%')";
		}
		
		if ($departamento == 3) { // contratos do comercial web
			$sql .= " and (i.[Name] like 'WEB-%')";
		}
	} elseif (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] )) { // contratos do tem!
		$sql .= " and (i.[Name] like 'TEM-%')";
	} elseif (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] )) { // contratos do comercial impresso
		$sql .= " and (i.[Name] like 'COM-%')";
	} elseif (isset ( $vPermissao ['web'] [$_SESSION ['user'] ['login']] )) { // contratos do comercial web
		$sql .= " and (i.[Name] like 'WEB-%')";
	}
	
	/*if ($data1 && $data2) {
		$data1 = Util::converteDataBanco ( $data1 );
		$data2 = Util::converteDataBanco ( $data2 );
		$sql .= " and (i.StartDate >= '$data1' and i.EndDate <= '$data2')";
	} elseif ($data1 && ! $data2) {
		$data1 = Util::converteDataBanco ( $data1 );
		$sql .= "and (i.StartDate = '$data1')";
	} elseif (! $data1 && $data2) {
		$data2 = Util::converteDataBanco ( $data2 );
		$sql .= "and (i.EndDate = '$data2')";
	}*/
	
	if ($data1 && $data2) {
		$data1 = Util::converteDataBanco ( $data1 );
		$data2 = Util::converteDataBanco ( $data2 );
		$sql .= " and (i.StartDate >= '$data1' and i.EndDate <= '$data2')";
	}
	
	if (isset ( $instancia )) {
		$sql .= " and i.[Name] like '%$instancia%'";
	}
	
	if (isset ( $opcliente ) && isset ( $cliente )) {
		if ($opcliente == 1) {
			$sql .= " and c3.Name1 like '%$cliente%'";
		} elseif ($opcliente == 2) {
			$sql .= " and c3.Name2 like '%$cliente%'";
		} elseif ($opcliente == 3) {
			$sql .= " and c3.FederalId = '$cliente'";
		}
	}
	
	$sql .= "
	) 
		group by i.[Name], i.[Notes], i.[Status], c3.Name1, c3.Name2, c3.FederalId, i.StartDate, i.EndDate, i.Id, n.[Description], d.[Description], n.Var1LowerLimit, d.Var1LowerLimit, n.Var2LowerLimit, d.Var2LowerLimit, i.RefAccountId
	order by 
		(
	    	case 
	        	when n.Var1LowerLimit is null 
	            	then (sum (f.Var1Actual)*100)/d.Var1LowerLimit 
	        	else (sum (f.Var1Actual)*100)/n.Var1LowerLimit  
	        end
	    ) desc, 
	    (
	    	case 
	        	when n.Var2LowerLimit = '' or d.Var2LowerLimit = '' 
	            	then '' 
	            else 
	            	(
	                	case 
	                    	when n.Var1LowerLimit is null 
	                        	then (sum (f.Var2Actual)*100)/d.Var2LowerLimit 
	                        else (sum (f.Var2Actual)*100)/n.Var2LowerLimit  
	                    end 
	                )           	   
	        end
	    )
	 ";
	//printvardie ( $sql );
	$rs = $db->Execute ( $sql );
	if ($rs) {
		
		if ($rs->RecordCount () == 0) {
			$tpl->block ( 'BLOCK_VAZIO' );
		} else {
			while ( $o = $rs->FetchNextObject () ) {
				
				$doc = $o->DOCUMENTO;
				$contrato = $o->INSTANCIA;
				
				$sql = "
				SELECT DISTINCT
					'VENCIDO' AS [VENCIDO] 
				FROM TMOV m (NOLOCK)
				INNER JOIN FLAN f (NOLOCK) ON (f.IDMOV = m.IDMOV AND f.CODCOLIGADA = m.CODCOLIGADA)
				WHERE
					m.CODCOLIGADA = 1 AND
					m.CODTMV = '2.2.01' AND
					m.CODCFO = dbo.RETORNACLIFORPORDOCUMENTO('$doc', m.CODCOLIGADA) AND
					m.CAMPOLIVRE1 = 'TEM'+SUBSTRING('$contrato', 9, 12) AND
					CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) > CONVERT(DATETIME,CONVERT(VARCHAR,f.DATAVENCIMENTO,103),103) AND
					f.DATABAIXA IS NULL
				";
				
				$res = $db2->Execute ( $sql );
				
				$tpl->FINANCEIRO = $res->FetchNextObject ()->VENCIDO;
				$tpl->CONTRATO = $o->INSTANCIA;
				$tpl->STATUS = $o->STATUS;
				$tpl->CLIENTE = utf8_encode ( $o->CLIENTE );
				$tpl->FANTASIA = utf8_encode ( $o->FANTASIA );
				$tpl->DOCUMENTO = $o->DOCUMENTO;
				$tpl->DATAI = Util::converteData ( $o->INICIO );
				$tpl->DATAF = Util::converteData ( $o->FIM );
				$tpl->ULTIMA = $o->ULTIMA;
				$tpl->QTDPUBLICACOES = $o->QTDPUBLICACOES;
				$tpl->PERCVAR1 = number_format ( $o->PORCENTAGEMVAR1, 2, '.', '' );
				$tpl->VAR1REAL = number_format ( $o->VAR1REAL, 2, ',', '.' );
				$tpl->VAR1ESPERADO = number_format ( $o->VAR1ESPERADO, 2, ',', '.' );
				$tpl->RESTAVAR1 = number_format ( $o->RESTAVAR1, 2, ',', '.' );
				$tpl->PACOTE = utf8_encode ( $o->PACOTE );
				$tpl->OBS = utf8_encode ( $o->OBS );
				$tpl->VENDEDOR = utf8_encode ( $o->VENDEDOR );
				
				/*
				$tpl->PERCVAR2 = number_format ( $o->PORCENTAGEMVAR2, 2, '.', '' );
				$tpl->VAR2REAL = number_format ( $o->VAR2REAL, 0, '', '' );
				$tpl->VAR2ESPERADO = number_format ( $o->VAR2ESPERADO, 0, '', '' );
				$tpl->RESTAVAR2 = number_format ( $o->RESTAVAR2, 0, '', '' );
				*/
				
				if ($o->PORCENTAGEMVAR1 >= PERCENTAGEM_LIMITE) {
					$tpl->STYLE = "class='vermelho' style='background-color:red !important; color:white;font-weight:bold;'";
				} else {
					$tpl->clear ( 'STYLE' );
				}
				
				$tpl->block ( 'BLOCK_DADOS' );
			}
		}
		
		$tpl->block ( 'BLOCK_TABELA' );
	
	} else {
		$tpl->block ( 'BLOCK_VAZIO' );
	}
}

$tpl->show ();
?>