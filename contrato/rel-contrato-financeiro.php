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

$tpl = new Template ( 'rel-contrato-financeiro-tpl.php' );

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
	SELECT DISTINCT 
	ISNULL((SELECT UPPER(V.NAME1+' '+V.NAME2) FROM CUSTOMER V (NOLOCK) WHERE V.ACCOUNTID = I.REFACCOUNTID),'') AS 'CONTATO',
	I.[NAME] 'INSTANCIA',
	I.[NOTES] 'OBS',
	I.[STATUS] 'STATUS',
	C3.NAME1 'CLIENTE',
	C3.NAME2 'FANTASIA',
	C3.FEDERALID 'DOCUMENTO',        
	I.STARTDATE 'INICIO',
	I.ENDDATE 'FIM',
	(SELECT TOP 1 CONVERT(VARCHAR(10),O.RUNDATELAST,103) FROM AOADORDER O (NOLOCK) WHERE O.ID IN (
		SELECT DISTINCT R.REFADORDER FROM COFULFILLMENTREC R (NOLOCK) WHERE R.CONTRACTINSTANCEID = I.ID
	) ORDER BY O.RUNDATELAST DESC) AS [ULTIMA],
	(CASE WHEN SUM (F.VAR1ACTUAL) <> '' THEN COUNT(*) ELSE 0 END) 'QTDPUBLICACOES',
	(
	   	CASE 
	       	WHEN N.[DESCRIPTION] IS NULL 
	       		THEN D.[DESCRIPTION] 
	       	ELSE N.[DESCRIPTION]  
	    END
	) 'PACOTE',
	SUM (F.VAR1ACTUAL) 'VAR1REAL',
	(
	   	CASE 
	       	WHEN N.VAR1LOWERLIMIT IS NULL 
	           	THEN D.VAR1LOWERLIMIT 
	        ELSE N.VAR1LOWERLIMIT  
	    END
	) 'VAR1ESPERADO',
	(
	  	CASE 
	       	WHEN N.VAR1LOWERLIMIT IS NULL 
	           	THEN D.VAR1LOWERLIMIT-SUM (F.VAR1ACTUAL) 
	        ELSE N.VAR1LOWERLIMIT-SUM (F.VAR1ACTUAL)  
	    END
	) 'RESTAVAR1',
	(
	   	CASE 
	       	WHEN N.VAR1LOWERLIMIT IS NULL 
	           	THEN (SUM (F.VAR1ACTUAL)*100)/D.VAR1LOWERLIMIT 
	       	ELSE (SUM (F.VAR1ACTUAL)*100)/N.VAR1LOWERLIMIT  
	    END
	) 'PORCENTAGEMVAR1',
	SUM (F.VAR2ACTUAL) 'VAR2REAL',
	(
	  	CASE 
	       	WHEN N.VAR2LOWERLIMIT IS NULL 
	          	THEN D.VAR2LOWERLIMIT 
	        ELSE N.VAR2LOWERLIMIT  
	    END
	) 'VAR2ESPERADO',
	(
	  	CASE 
	  		WHEN 
	      		N.VAR2LOWERLIMIT IS NULL 
	    	THEN 
	    		D.VAR2LOWERLIMIT-SUM (F.VAR2ACTUAL) 
	        ELSE N.VAR2LOWERLIMIT-SUM (F.VAR2ACTUAL)  
	    END
	 ) 'RESTAVAR2',
	 (
	   	CASE 
	       	WHEN 
	       		N.VAR2LOWERLIMIT = '' OR D.VAR2LOWERLIMIT = '' 
	        THEN 
	        	'' 
	        ELSE 
	            (
	               	CASE 
	                   	WHEN N.VAR1LOWERLIMIT IS NULL 
	                       	THEN (SUM (F.VAR2ACTUAL)*100)/D.VAR2LOWERLIMIT 
	                    ELSE (SUM (F.VAR2ACTUAL)*100)/N.VAR2LOWERLIMIT  
	                END 
	            )           	   
	        END
	    ) 'PORCENTAGEMVAR2'  
	FROM 
		COCONTRACTINSTANCE I
	LEFT JOIN CORATELEVEL N ON (I.CONTRACTTEMPLATEID = N.OWNERID AND I.LEVELSIGNEDUPTO = N.CONTRACTLEVEL)
	LEFT JOIN CODISCOUNTLEVEL D ON (I.CONTRACTTEMPLATEID = D.OWNERID AND I.LEVELSIGNEDUPTO = D.CONTRACTLEVEL)
	LEFT JOIN COFULFILLMENTREC F ON (I.ID = F.CONTRACTINSTANCEID)
	LEFT JOIN COCUSTOMERACCTENTRY C1 ON (I.ID = C1.OWNERID)
	LEFT JOIN CUSTOMERACCNUMBER C2 ON (C1.PAYORACCTID = C2.ID)
	LEFT JOIN CUSTOMER C3 ON (C2.CUSTACCNUMBERACCID = C3.ACCOUNTID)
	WHERE 
		I.[STATUS] = 2 AND 
		I.[NAME] LIKE 'TEM-%'
	";
	
	if ($data1 && $data2) {
		$data1 = Util::converteDataBanco ( $data1 );
		$data2 = Util::converteDataBanco ( $data2 );
		$sql .= " AND I.STARTDATE >= '$data1' AND I.ENDDATE <= '$data2'";
	}
	
	if (isset ( $instancia )) {
		$sql .= " AND I.[NAME] LIKE '%$instancia%'";
	}
	
	if (isset ( $opcliente ) && isset ( $cliente )) {
		if ($opcliente == 1) {
			$sql .= " AND C3.NAME1 LIKE '%$cliente%'";
		} elseif ($opcliente == 2) {
			$sql .= " AND C3.NAME2 LIKE '%$cliente%'";
		} elseif ($opcliente == 3) {
			$sql .= " AND C3.FEDERALID = '$cliente'";
		}
	}
	
	$sql .= "
		GROUP BY I.[NAME], I.[NOTES], I.[STATUS], C3.NAME1, C3.NAME2, C3.FEDERALID, I.STARTDATE, I.ENDDATE, I.ID, N.[DESCRIPTION], D.[DESCRIPTION], N.VAR1LOWERLIMIT, D.VAR1LOWERLIMIT, N.VAR2LOWERLIMIT, D.VAR2LOWERLIMIT, I.REFACCOUNTID
		ORDER BY 
		(
	    	CASE 
	        	WHEN N.VAR1LOWERLIMIT IS NULL 
	            	THEN (SUM (F.VAR1ACTUAL)*100)/D.VAR1LOWERLIMIT 
	        	ELSE (SUM (F.VAR1ACTUAL)*100)/N.VAR1LOWERLIMIT  
	        END
	    ) DESC, 
	    (
	    	CASE 
	        	WHEN N.VAR2LOWERLIMIT = '' OR D.VAR2LOWERLIMIT = '' 
	            	THEN '' 
	            ELSE 
	            	(
	                	CASE 
	                    	WHEN N.VAR1LOWERLIMIT IS NULL 
	                        	THEN (SUM (F.VAR2ACTUAL)*100)/D.VAR2LOWERLIMIT 
	                        ELSE (SUM (F.VAR2ACTUAL)*100)/N.VAR2LOWERLIMIT  
	                    END 
	                )           	   
	        END
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
				SELECT 
					COUNT(DISTINCT DATAVENCIMENTO) AS [PARCELAS],
					DBO.FGETDATAVENCIMENTO(M.CODCOLIGADA, M.IDMOV, M.CODTMV) AS [VENCIMENTOS],
					(100/COUNT(DISTINCT DATAVENCIMENTO)) AS [PERCENTUAL]
				FROM TMOV M (NOLOCK)
				INNER JOIN FLAN F (NOLOCK) ON (F.IDMOV = M.IDMOV AND F.CODCOLIGADA = M.CODCOLIGADA)
				WHERE
					M.CODCOLIGADA = 1 AND
					M.CODTMV = '2.2.01' AND
					M.CODCFO = DBO.RETORNACLIFORPORDOCUMENTO('$doc', M.CODCOLIGADA) AND
					M.CAMPOLIVRE1 = 'TEM'+SUBSTRING('$contrato', 9, 12)
				GROUP BY M.CODCOLIGADA, M.IDMOV, M.CODTMV
				";
				//printvardie($sql);
				$res = $db2->Execute ( $sql );
				
				while ( $f = $res->FetchNextObject () ) {
					
					for($i = 1; $i <= $parcelas; $i ++) {
						$nome = "MENSAL$i";
						$tpl->$nome = '';
					}
					
					$percentual = $f->PERCENTUAL;
					$parcelas = $f->PARCELAS;
					
					$tpl->PARCELAS = $parcelas . 'x';
					$tpl->VENCIMENTOS = $f->VENCIMENTOS;
					
					for($i = 1; $i <= $parcelas; $i ++) {
						$nome = "MENSAL$i";
						$tpl->$nome = $percentual;
					}
				
				}
				
				$tpl->CONTATO = $o->CONTATO;
				$tpl->CONTRATO = $o->INSTANCIA;
				$tpl->STATUS = $o->STATUS;
				$tpl->CLIENTE = utf8_encode ( $o->CLIENTE );
				$tpl->FANTASIA = utf8_encode ( $o->FANTASIA );
				$tpl->DOCUMENTO = $o->DOCUMENTO;
				$tpl->DATAI = Util::converteData ( $o->INICIO );
				//$tpl->DATAF = Util::converteData ( $o->FIM );
				$tpl->ULTIMA = $o->ULTIMA;
				//$tpl->QTDPUBLICACOES = $o->QTDPUBLICACOES;
				$tpl->PERCVAR1 = number_format ( $o->PORCENTAGEMVAR1, 2, '.', '' );
				//$tpl->VAR1REAL = number_format ( $o->VAR1REAL, 2, ',', '.' );
				$tpl->VAR1ESPERADO = number_format ( $o->VAR1ESPERADO, 2, ',', '.' );
				//$tpl->RESTAVAR1 = number_format ( $o->RESTAVAR1, 2, ',', '.' );
				$tpl->PACOTE = utf8_encode ( $o->PACOTE );
				//$tpl->OBS = utf8_encode ( $o->OBS );
				

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