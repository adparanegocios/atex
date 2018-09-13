<?php

include_once 'conexao.php';
include_once 'classes/class.Util.php';
include_once 'classes/Template.class.php';
include_once 'global.php';

$tpl = new Template ( 'matricial.html' );

extract ( $_REQUEST );

$sql = "
SELECT 
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
	O.ADORDERNUMBER LIKE '%$ordem%'
";

$rs = $db->execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		$tpl->ORDEM = $o->ORDEM;
		$tpl->TEXTO = $o->TEXTO;
		$tpl->LINHAS = $o->LINHAS;
		$tpl->SECAO = $o->SECAO;
		$tpl->ATENDENTE = $o->ATENDENTE;
		$tpl->VALOR = number_format ( $o->VALOR, 2, ',', '.' );
		$tpl->MODALIDADE = $o->PAGAMENTO;
		$tpl->DATAS = $o->VEICULACAO;
		$tpl->CLIENTE = $o->CLIENTE;
		$tpl->TELEFONE = $o->TELEFONE;
		$tpl->DATA = $o->DATA;
		$tpl->HORA = $o->HORA;
		$tpl->DATASISTEMA = $o->DATASISTEMA;
		$tpl->DOCUMENTO = $o->DOCUMENTO;
	}
}

$tpl->show ();

?>