<?php

include_once 'conexao.php';
include_once 'classes/class.Util.php';
include_once 'classes/Template.class.php';
include_once 'global.php';

$tpl = new Template ( 'matricial-periodo.html' );


extract ( $_REQUEST );

/*
$cpf = str_replace(".", "",$cpf);
$cpf = str_replace("-", "",$cpf);
$cpf = str_replace("/", "",$cpf);*/

if ($cpf_cnpj == "") {
	echo "<script language='JavaScript'> alert('Informe o CPF ou CNPJ'); </script>
		  <script>history.back()</script>";
}

if ($dataini == "") {
	echo "<script language='JavaScript'> alert('Informe a data inicial'); </script>
		 <script>history.back()</script>";
}


if ($datafim == "") {
	echo "<script language='JavaScript'> alert('Informe a data final'); </script>
		  <script>history.back()</script>;";
}


$datainiarray = explode("/", $dataini);
$dataini = $datainiarray[1]."/".$datainiarray[2]."/".$datainiarray[0];


$datafimarray = explode("/", $datafim);
$datafim = $datafimarray[1]."/".$datafimarray[2]."/".$datafimarray[0];


$sql = "SELECT 
	O.ADORDERNUMBER AS [ORDEM],
	C.TAGLINE AS [TEXTO],
	C.NUMLINES AS [LINHAS],
	SUBSTRING(UPPER(S.NAME),4,50) AS [SECAO],
	UPPER(U.USERFNAME+' '+U.USERLNAME) AS [ATENDENTE],
	O.NETAMOUNT AS [VALOR],
	UPPER(P.NAME) AS [PAGAMENTO],
	DBO.FGETRUNDATELIST(O.ID) AS [VEICULACAO],
	UPPER(CL.NAME1) AS [CLIENTE],
	CL.PRIMARYTELEPHONE AS [TELEFONE],
	DBO.FORMATACPFCNPJ(CL.FEDERALID) AS [DOCUMENTO],
	CONVERT(VARCHAR(10),O.CREATEDATE,103) AS [DATA],
	CONVERT(VARCHAR(5),O.CREATEDATE,114) AS [HORA],
	CONVERT(VARCHAR(10),GETDATE(),103)+' '+CONVERT(VARCHAR(8),GETDATE(),114) AS [DATASISTEMA]
FROM AOADORDER O (NOLOCK)
INNER JOIN AOADINFO I (NOLOCK) ON (SUBSTRING(I.ADNUMBER,0,11) = O.ADORDERNUMBER)
INNER JOIN AOADCONTENT C (NOLOCK) ON (C.ADINFOID = I.ID)
INNER JOIN AOADRUNSCHEDULE D (NOLOCK) ON (D.ADORDERID = O.ID)
INNER JOIN AOPLACEMENTS S (NOLOCK) ON (S.ID = D.PLACEMENTID)
INNER JOIN AOORDERCUSTOMERS OC (NOLOCK) ON (OC.ADORDERID = O.ID AND OC.PAYEDBY = 1)
INNER JOIN CUSTOMER CL (NOLOCK) ON (CL.ACCOUNTID = OC.CUSTOMERID)
INNER JOIN USRUSERS U (NOLOCK) ON (U.USERID = O.REPID)
INNER JOIN PAYMENTMETHODTYPE P (NOLOCK) ON (P.ID = OC.PAYMENTMETHOD)
WHERE
	DBO.FORMATACPFCNPJ(CL.FEDERALID) LIKE '%".$cpf_cnpj."%' and
    O.CREATEDATE >= '".$dataini." 00:00:00' and 
    O.CREATEDATE <= '".$datafim." 23:59:59'
	order by DOCUMENTO";

    

//$rs = $db->execute ( $sql );
$arr = $db->GetArray($sql);



//if ($rs) {'
	$nmanuncios = 0;
	$total	= 0;
	foreach ( $arr as  $o) {

		$tpl->ORDEM = $o[0];
		$tpl->TEXTO = $o[1];
		$tpl->LINHAS = $o[2];
		$tpl->SECAO = $o[3];
		$tpl->ATENDENTE = $o[4];
		$tpl->VALOR = number_format ( $o[5], 2, ',', '.' );
		$tpl->MODALIDADE = $o[6];
		$tpl->DATAS = $o[7];
		$tpl->CLIENTE = $o[8];
		$tpl->TELEFONE = $o[9];
		$tpl->DATA = $o[11];
		$tpl->HORA = $o[12];
		$tpl->DATASISTEMA = $o[13];
		$tpl->DOCUMENTO = $o[10];

		$total = $total + $o[5];

		$tpl->block("BLOCK_REGISTROS");
		$tpl->block("BLOCK_REGISTROS2");
		$tpl->block("BLOCK_REGISTROS3");
		$nmanuncios++;
	}
//}
$tpl->TOTAL 		= number_format ( $total, 2, ',', '.' );
$tpl->NM_ANUNCIOS 	= $nmanuncios;


$tpl->DATA_INICIAL 	= $datainiarray[0]."/".$datainiarray[1]."/".$datainiarray[2];;
$tpl->DATA_FINAL 	= $datafimarray[0]."/".$datafimarray[1]."/".$datafimarray[2];;

$tpl->show ();

?>