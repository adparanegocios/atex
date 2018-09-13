<?php

include_once 'C:/wamp/www/atex/conexao.php';

$sql = "
SELECT 
	o.AdOrderNumber as ordem 
FROM AoAdOrder o (nolock)
WHERE
	o.OrderStatusId = 6 AND
	o.CurrentQueue = 3 AND
	convert(datetime,convert(varchar,o.CreateDate,103),103) = convert(datetime,convert(varchar,getdate(),103),103) AND
	(SELECT SUM(P.APPLYAMOUNT) FROM AOPREPAYMENTAPPLY P WHERE P.ADORDERID = O.ID) IS NULL AND
	o.AdOrderNumber collate sql_latin1_general_cp1251_ci_as IN (
		SELECT ordem FROM openquery(
		[MASTER],
		'
		select
	      substring(c.`order_number`, 5, 10) as ordem
		from cielo_retorno_checkout c
		where
		     c.`order_number` like ''TEM-%'' and
		     c.`payment_status` = 2 and
		     c.`date` >= CURDATE() and
		     c.`type` = ''status''
		'
		)
	)
";

$rs = $db->Execute ( $sql );
$vetOrdens = array ();

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		$vetOrdens [] = $o->ORDEM;
	}
}

foreach ( $vetOrdens as $ordem ) {
	
	$sql = "

SELECT 
	ROW_NUMBER() OVER(ORDER BY o.CreateDate) + (SELECT max(c.Id) FROM AoCreditDebit c (nolock)) AS Id,
	oc.AccountId AS CustomerId,
	2 AS TransType,
	0 AS LinkId,
	'' AS TransNumber,
	o.NetAmount AS Amount,
	getdate() AS TransDate,
	convert(datetime,convert(varchar,getdate(),103),103) AS EffectiveDate,
	getdate() AS PostedDate,
	0 AS Posted,
	0 AS AmountPosted,
	o.NetAmount AS AmountAttempted,
	0 AS Deleted,
	NULL AS DeletedByUserId,
	getdate() AS DeletedData, 
	1 AS Category,
	'' AS PmtSysTransNum,
	1 AS PmtSysSeqNum,
	NULL AS BatchId,
	NULL AS CurrencyId,
	0 AS OtherCurrencyAmt,
	0 AS SubDetail,
	convert(datetime,convert(varchar,'3000-01-01',103),103) AS ClosedDate,
	1 AS AdvOrPayor,
	NULL AS FnAccountingPeriodId,
	0 AS InvoiceId,
	0 AS InCollections, 
	1 AS InhibitAutoApply,
	0 AS BadDebtAmount,
	0 AS CollectionsAmount,
	0 AS SingleStatementFlag,
	NULL AS DivisionId,
	1 AS CompanyId
FROM AoAdOrder o (nolock)
INNER JOIN AoOrderCustomers oc (nolock) ON (oc.AdOrderId = o.Id AND oc.PayedBy = 1)
WHERE
	o.AdOrderNumber = '$ordem';
";
	
	$rs = $db->Execute ( $sql );
	
	if ($rs) {
		while ( $o = $rs->FetchNextObject () ) {
			$CustomerId = $o->CUSTOMERID;
			$TransType = $o->TRANSTYPE;
			$LinkId = $o->LINKID;
			$TransNumber = $o->TRANSNUMBER;
			$Amount = $o->AMOUNT;
			$TransDate = $o->TRANSDATE;
			$EffectiveDate = $o->EFFECTIVEDATE;
			$PostedDate = $o->POSTEDDATE;
			$Posted = $o->POSTED;
			$AmountPosted = $o->AMOUNTPOSTED;
			$AmountAttempted = $o->AMOUNTATTEMPTED;
			$Deleted = $o->DELETED;
			$DeletedByUserId = $o->DELETEDBYUSERID;
			$DeletedData = $o->DELETEDDATA;
			$Category = $o->CATEGORY;
			$PmtSysTransNum = $o->PMTSYSTRANSNUM;
			$PmtSysSeqNum = $o->PMTSYSSEQNUM;
			$BatchId = $o->BATCHID;
			$CurrencyId = $o->CURRENCYID;
			$OtherCurrencyAmt = $o->OTHERCURRENCYAMT;
			$SubDetail = $o->SUBDETAIL;
			$ClosedDate = $o->CLOSEDDATE;
			$AdvOrPayor = $o->ADVORPAYOR;
			$FnAccountingPeriodId = $o->FNACCOUNTINGPERIODID;
			$InvoiceId = $o->INVOICEID;
			$InCollections = $o->INCOLLECTIONS;
			$InhibitAutoApply = $o->INHIBITAUTOAPPLY;
			$BadDebtAmount = $o->BADDEBTAMOUNT;
			$CollectionsAmount = $o->COLLECTIONSAMOUNT;
			$SingleStatementFlag = $o->SINGLESTATEMENTFLAG;
			$DivisionId = $o->DIVISIONID;
			$CompanyId = $o->COMPANYID;
			
			$sql = "
		declare @UniqueId int
		
		EXEC PI_AoCreditDebit
		@UniqueId,
		$CustomerId,
		$TransType,
		$LinkId,
		'$TransNumber',
		$Amount,
		'$TransDate',
		'$EffectiveDate',
		'$PostedDate',
		$Posted,
		$AmountPosted,
		$AmountAttempted,
		$Deleted,
		NULL,
		'$DeletedData',
		$Category,
		'$PmtSysTransNum',
		$PmtSysSeqNum,
		NULL,
		NULL,
		$OtherCurrencyAmt,
		$SubDetail,
		'$ClosedDate',
		$AdvOrPayor,
		NULL,
		$InvoiceId,
		$InCollections,
		$InhibitAutoApply,
		$BadDebtAmount,
		$CollectionsAmount,
		$SingleStatementFlag,
		NULL,
		$CompanyId
		";
			
			$db->Execute ( $sql );
		}
	}
	
	$sql = "
INSERT AoPayments
	
SELECT 
	(SELECT max(d.Id) FROM AoCreditDebit d (nolock)) AS TransId,
	oc.PaymentMethod AS PaymentMethod,
	'' AS ReferenceNumber,
	NULL AS CustomerCCardId,
	o.Id AS ApplyAdOrderId,
	NULL AS ApplyInvoiceId,
	0 AmountNotApplied,
	1 PaymentProcStatus,
	0 AS PaymentStatusDetail,
	0 AS PaymentAddressStatus,
	getdate() AS CreationDate,
	o.RepId AS CreatingUser,
	getdate() AS LastEditDate,
	o.RepId AS LastEditUser,
	1 AS CompanyId,
	NULL AS BatchId,
	'' AS InvoiceText,
	'' AS Notes,
	0 AS BadDebt,
	'' AS RoutingNum,
	'' AS CheckNumber,
	'' AS MoneyAcctNumber,
	NULL AS MerchantCodeId,
	c.Name1 AS AccountName,
	'' AS DriversLicense,
	NULL AS RefundLinkId,
	NULL AS PmtSysReferenceNumber,
	0 AS PaymentSourceType,
	0 AS FraudSecResp,
	0 AS EBAccountType,
	NULL AS RefundPaySourceId,
	'' AS PmtSysRef2,
	0 AS PmtAddrStatus2,
	NULL AS DDebitAcctId,
	NULL AS SuperPaymentId,
	NULL AS ReceiptPrinteDate,
	0 AS HLPayType,
	0 AS PayAction,
	NULL AS InsPayParentId,
	'' AS Comments,
	1 AS NumPayments,
	NULL AS RefundInvoceid,
	0 AS SecurityCodeBypassReason 
FROM AoAdOrder o (nolock)
INNER JOIN AoOrderCustomers oc (nolock) ON (oc.AdOrderId = o.Id AND oc.PayedBy = 1)
INNER JOIN Customer c (nolock) ON (c.AccountId = oc.CustomerId)
WHERE
	o.AdOrderNumber = '$ordem';
";
	
	$db->Execute ( $sql );
	
	$sql = "
SELECT 
	ROW_NUMBER() OVER(ORDER BY o.CreateDate) + (SELECT max(p.Id) FROM AoPrepaymentApply p (nolock)) AS Id,
	(SELECT max(d.Id) FROM AoCreditDebit d (nolock)) AS PaymentId,
	o.Id AS AdOrderId,
	0 AS CreditRef,
	o.NetAmount AS ApplyAmount,
	convert(datetime,convert(varchar,getdate(),103),103) AS DateApplied,
	getdate() AS DateToBill,
	0 AS AmountPosted,
	0 AS Posted,
	0 AS CCProcessed,
	0 AS TaxPortion 
FROM AoAdOrder o (nolock)
INNER JOIN AoOrderCustomers oc (nolock) ON (oc.AdOrderId = o.Id AND oc.PayedBy = 1)
INNER JOIN Customer c (nolock) ON (c.AccountId = oc.CustomerId)
WHERE
	o.AdOrderNumber = '$ordem';
";
	
	$rs = $db->Execute ( $sql );
	
	if ($rs) {
		while ( $o = $rs->FetchNextObject () ) {
			$PaymentId = $o->PAYMENTID;
			$AdOrderId = $o->ADORDERID;
			$CreditRef = $o->CREDITREF;
			$ApplyAmount = $o->APPLYAMOUNT;
			$DateApplied = $o->DATEAPPLIED;
			$DateToBill = $o->DATETOBILL;
			$AmountPosted = $o->AMOUNTPOSTED;
			$Posted = $o->POSTED;
			$CCProcessed = $o->CCPROCESSED;
			$TaxPortion = $o->TAXPORTION;
			
			$sql = "
		declare @UniqueId int
		EXEC PI_AoPrepaymentApply
		@UniqueId,
		$PaymentId,
		$AdOrderId,
		$CreditRef,
		$ApplyAmount,
		'$DateApplied',
		'$DateToBill',
		$AmountPosted,
		$Posted,
		$CCProcessed,
		$TaxPortion
		";
			
			$db->Execute ( $sql );
		}
	}

}

?>