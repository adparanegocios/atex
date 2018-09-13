<?php

error_reporting ( 0 );
ini_set ( 'display_errors', 0 );
set_time_limit ( 0 );

include_once 'C:/wamp/www/atex/conexao.php';
include_once 'C:/wamp/www/atex/conexao2.php';
include_once 'C:/wamp/www/atex/classes/phpmailer/class.phpmailer.php';
include_once 'C:/wamp/www/atex/global.php';

function converteData(&$data) {
	$data = substr ( $data, 0, 10 );
	list ( $ano, $mes, $dia ) = explode ( "-", $data );
	$data = $dia . "/" . $mes . "/" . $ano;
	return $data;
}

$porcentagem = PERCENTAGEM_LIMITE;

$sql = "
select distinct 
	i.[Name] 'instancia',
	i.[Notes] 'observacao',
    i.[Status] 'status',
    c3.Name1 'cliente',
    c3.Name2 'fantasia',
    c3.FederalId 'documento',        
    i.StartDate 'inicio',
    i.EndDate 'fim',
    (case when sum (f.Var1Actual) <> '' then count(*) else 0 end) 'qtdpublicacoes',
    dbo.FGetRunDateUltimaData(i.[Name]) AS 'ultimo',
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
    ) 'porcentagemvar2'  
from 
	CoContractInstance i (nolock)
left join CoRateLevel n (nolock) on (i.ContractTemplateId = n.OwnerId and i.LevelSignedUpTo = n.ContractLevel)
left join CoDiscountLevel d (nolock) on (i.ContractTemplateId = d.OwnerId and i.LevelSignedUpTo = d.ContractLevel)
left join CoFulfillmentRec f (nolock) on (i.Id = f.ContractInstanceId)
left join CoCustomerAcctEntry c1 (nolock) on (i.Id = c1.OwnerId)
left join CustomerAccNumber c2 (nolock) on (c1.PayorAcctId = c2.Id)
left join Customer c3 (nolock) on (c2.CustAccNumberAccId = c3.AccountId)
where (
	(i.[Status] = 2) and
	((i.[Name] like 'TEM-%'))
) 
group by i.[Name], i.[Notes], i.[Status], c3.Name1, c3.Name2, c3.FederalId, i.StartDate, i.EndDate, n.[Description], d.[Description], n.Var1LowerLimit, d.Var1LowerLimit, n.Var2LowerLimit, d.Var2LowerLimit
HAVING (
    	case 
        	when n.Var1LowerLimit is null 
            	then (sum (f.Var1Actual)*100)/d.Var1LowerLimit 
        	else (sum (f.Var1Actual)*100)/n.Var1LowerLimit  
        end
    ) >= $porcentagem
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

$mail = new PHPMailer ();
$mail->SetLanguage ( "br", "../classes/phpmailer/language/" );
$mail->IsSMTP ();
$mail->Host = HOST_EMAIL;
$mail->SMTPAuth = true;
$mail->Port = PORT_EMAIL;
$mail->Username = USER_EMAIL;
$mail->Password = PASS_EMAIL;
$mail->From = USER_EMAIL;
$mail->AddReplyTo ( USER_EMAIL );
$mail->FromName = "Contract Manager";
$mail->WordWrap = 50;
$mail->IsHTML ( true );
$mail->Subject = 'ATEX: gerenciamento de contratos ...';

$rs = $db->Execute ( $sql );

if ($rs) {
	
	$enviar = false;
	
	$conteudo = "
	<table width='1694' border='1'  align='center'>
    <tr>
      <th width='181' scope='col'>Contrato</th>
	  <th width='234' scope='col'>Financeiro</th>
      <th width='234' scope='col'>Cliente</th>
      <th width='249' scope='col'>Fantasia</th>
      <th width='125' scope='col'>CPF/CNPJ</th>
      <th width='83' scope='col'>In&iacute;cio</th>
      <th width='83' scope='col'>Fim</th>
      <th width='154' scope='col'>Inser&ccedil;&otilde;es </th>
      <th width='107' scope='col'>&Uacute;ltima Veicula&ccedil;&atilde;o </th>
      <th width='107' scope='col'>Percentagem Var1 </th>
	  <th width='94' scope='col'>Var1 Real </th>
      <th width='155' scope='col'>Var1 Esperado </th>
      <th width='110' scope='col'>Resta Var1 </th>
	  <th width='298' scope='col'>Observa&ccedil;&atilde;o</th>
	  <th width='298' scope='col'>Pacote</th>      
    </tr>
	";
	
	while ( $o = $rs->FetchNextObject () ) {
		$contrato = $o->INSTANCIA;
		$cliente = utf8_encode ( $o->CLIENTE );
		$fantasia = utf8_encode ( $o->FANTASIA );
		$documento = $o->DOCUMENTO;
		$datai = converteData ( $o->INICIO );
		$dataf = converteData ( $o->FIM );
		$datapublicacoes = $o->QTDPUBLICACOES;
		$percvar1 = number_format ( $o->PORCENTAGEMVAR1, 2, ',', '.' );
		$var1real = number_format ( $o->VAR1REAL, 2, ',', '.' );
		$var1esperado = number_format ( $o->VAR1ESPERADO, 2, ',', '.' );
		$restavar1 = number_format ( $o->RESTAVAR1, 2, ',', '.' );
		$pacote = utf8_encode($o->PACOTE);
		$observacao = utf8_encode($o->OBSERVACAO);
		$ultimo = $o->ULTIMO;
		
		$enviar = true;
		
			$sql = "
				SELECT DISTINCT
					'VENCIDO' AS [VENCIDO] 
				FROM TMOV m (NOLOCK)
				INNER JOIN FLAN f (NOLOCK) ON (f.IDMOV = m.IDMOV AND f.CODCOLIGADA = m.CODCOLIGADA)
				WHERE
					m.CODCOLIGADA = 1 AND
					m.CODTMV = '2.2.01' AND
					m.CODCFO = dbo.RETORNACLIFORPORDOCUMENTO('$documento', m.CODCOLIGADA) AND
					m.CAMPOLIVRE1 = 'TEM'+SUBSTRING('$contrato', 9, 12) AND
					CONVERT(DATETIME,CONVERT(VARCHAR,GETDATE(),103),103) > CONVERT(DATETIME,CONVERT(VARCHAR,f.DATAVENCIMENTO,103),103) AND
					f.DATABAIXA IS NULL
				";
				
				$res = $db2->Execute ( $sql );
				$financeiro = $res->FetchNextObject ()->VENCIDO;
			
			$conteudo .= "
				<tr>
		      		<td>$contrato</td>
					<td>$financeiro</td>
		      		<td>$cliente</td>
		      		<td>$fantasia</td>
		      		<td>$documento</td>
		      		<td>$datai</td>
		      		<td>$dataf</td>
		      		<td style='text-align:center;'>$datapublicacoes</td>
		      		<td style='text-align:center;'>$ultimo</td>
		      		<td style='text-align:center;'>$percvar1%</td>
		      		<td style='text-align:center;'>$var1real</td>
		      		<td style='text-align:center;'>$var1esperado</td>
		      		<td style='text-align:center;'>$restavar1</td>
					<td style='text-align:center;'>$observacao</td>
		      		<td style='text-align:center;'>$pacote</td>
		        </tr>
		";
	
	}
}
//printvardie($conteudo);
$mail->Body = utf8_decode ( $conteudo );

//$mail->AddAddress ( 'adeilson@diarioonline.com.br' );
$mail->AddAddress ( 'andreia@diariodopara.com.br' );
$mail->AddAddress ( 'tem@diariodopara.com.br' );
$mail->AddAddress ( 'natasha.correa@diariodopara.com.br' );

if ($enviar) {
	if (! $mail->Send ()) {
		exit ();
	}
} else {
	exit ();
}

$mail->SmtpClose ();

?>