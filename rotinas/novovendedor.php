<?php

include_once 'C:/wamp/www/atex/conexao.php';
include_once 'C:/wamp/www/atex/conexao2.php';

$sql = "
SELECT 
	u.UserId AS [id],
	upper(u.UserFname+' '+u.UserLname) AS [nome] 
FROM UsrUsers u (nolock)
WHERE
	u.SalesTeamNameId IN (9, 20) AND
	u.LoginDisabledFlag = 0 AND
	(
	SELECT 
		s.ExternalName 
	FROM ShStringMapper s (nolock)
	WHERE
		s.ExternalSystemType = 2 AND
		s.ObjectType = 9 AND
		s.AdBaseId = u.UserId
	) IS NULL 
";

$rs = $db->Execute ( $sql );

if ($rs) {
	while ( $o = $rs->FetchNextObject () ) {
		$id = $o->ID;
		$nome = utf8_encode ( $o->NOME );
		
		$sql = "
		DECLARE @CODVEN VARCHAR(6) = (
			SELECT 
				'01.'+REPLICATE('0', (3 - LEN(CAST(CAST(SUBSTRING(MAX(V.CODVEN),4,3) AS INT)+1 AS VARCHAR))))+CAST(CAST(SUBSTRING(MAX(V.CODVEN),4,3) AS INT)+1 AS VARCHAR)
			FROM TVEN V (NOLOCK)
			WHERE
				V.CODCOLIGADA = 1 AND
				V.CODVEN LIKE '01.%'
		);
		
		DECLARE @IDFUNCIONARIO INT = (
			SELECT I.VALAUTOINC+1 FROM GAUTOINC I (NOLOCK) WHERE I.CODCOLIGADA = 1 AND I.CODAUTOINC = 'IDFUNCIONARIO'
		);
		
		
		INSERT INTO dbo.TVEN (CODCOLIGADA, CODVEN, NOME, CARGO, CODFILIAL, CODLOC, COMISSAO1, COMISSAO2, COMISSAO3, CODPESSOA, VENDECOMPRA, CODUSUARIO, SENHA, INATIVO, PFVENDEDOR, PFCAIXA, PFSUPERVISOR, PFGERENTE, IDFUNCIONARIO, COMISSAO4, DESCMAXIMO, RECCREATEDBY, RECCREATEDON, RECMODIFIEDBY, RECMODIFIEDON)
		VALUES (1, @CODVEN, '$nome', 'VENDEDOR(A)', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'mestre', NULL, 0, NULL, NULL, NULL, NULL, @IDFUNCIONARIO, NULL, NULL, 'mestre', GETDATE(), 'mestre', GETDATE());
		
		UPDATE GAUTOINC SET GAUTOINC.VALAUTOINC = @IDFUNCIONARIO, GAUTOINC.RECCREATEDON = GETDATE(), GAUTOINC.RECMODIFIEDON = GETDATE() WHERE GAUTOINC.CODCOLIGADA = 1 AND GAUTOINC.CODAUTOINC = 'IDFUNCIONARIO';
		";
		$db2->Execute ( $sql );
		
		$sql = "SELECT MAX(V.CODVEN) AS [CODIGO] FROM TVEN V (NOLOCK) WHERE V.CODCOLIGADA = 1 AND V.CODVEN LIKE '01.%'";
		$res = $db2->Execute ( $sql );
		$codigo = $res->FetchNextObject ()->CODIGO;
		
		$sql = "
			DECLARE @UNIQUEID INT;
			EXEC PI_SHSTRINGMAPPER
			@UNIQUEID, 
			2, 
			9, 
			$id,
			'$codigo',
			'', 
			'', 
			'', 
			'', 
			'', 
			'', 
			''
			";
		
		$db->Execute ( $sql );
	
	}
}

?>