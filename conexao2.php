<?php

include_once 'global.php';
require_once 'classes/adodb5/adodb.inc.php';

try {
	
	$db2 = & ADONewConnection ( 'odbc_mssql' );
	$dsn2 = "Driver={SQL Server};Server=" . HOST_BANCO2 . ";Database=" . NAME_BANCO2 . ";";
	$conexao2 = $db2->Connect ( $dsn2, USER_BANCO2, PASS_BANCO2 );
	
	function &fetch_array2(&$rs) {
		$numeroColunas = $rs->FieldCount ();
		while ( ! $rs->EOF ) {
			for($i = 0; $i < $numeroColunas; $i ++) {
				$coluna = $rs->FetchField ( $i );
				$nomeColuna = strtolower ( $coluna->name );
				$tipoColuna = $rs->MetaType ( $coluna->type );
				if ($tipoColuna == 'D') {
					$vetor ["$nomeColuna"] = $this->converteData ( $rs->fields [$i] );
				} else {
					$vetor ["$nomeColuna"] = $rs->fields [$i];
				}
			}
			$vetorRegistro [] = $vetor;
			$rs->MoveNext ();
		}
		return $vetorRegistro;
	}

} catch ( Exception $e ) {
	echo "<h1 align = 'center'>Banco de Dados indisponível. Tente novamente mais tarde, por favor.</h1>'";
	exit ();
}

?>