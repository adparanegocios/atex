<?php

include_once 'conexao.php';
include_once 'global.php';

session_start ();

$helper = new COM ( 'Helper.clsHelper' ); //helper app for decryption


extract ( $_REQUEST );

$sql = "
select 
	u.UserId 'id',
    u.UserFname + ' ' + u.UserLname 'nome',
    u.LoginName 'login',
    u.LoginPassword 'senha' 
from 
	UsrUsers u
where (
	(u.LoginName = '$login') and
    (u.LoginPassword = '{$helper->Encrypt($senha)}') and 
    (u.LoginDisabledFlag = 0)
)
";

$rs = $db->Execute ( $sql );
$numlinhas = $rs->RecordCount ();

if (! ((in_array ( $login, $vPermissao ['tem'] )) or (in_array ( $login, $vPermissao ['noticiario'] )) or (in_array ( $login, $vPermissao ['web'] )) or (in_array ( $login, $vPermissao ['controladoria'] )))) {
	header ( 'location: login.php?msg=erro1' );
	exit;
} else {
	if ($numlinhas != 0) {
		$rs = fetch_array ( $rs );
		$rs = array_shift ( $rs );
		
		if(in_array ( $login, $vPermissao ['tem'] )){
			$sAcesso = "tem";
		}
		
		if(in_array ( $login, $vPermissao ['noticiario'] )){
			$sAcesso = "noticiario";
		}
		
		if(in_array ( $login, $vPermissao ['web'] )){
			$sAcesso = "web";
		}
		
		if(in_array ( $login, $vPermissao ['controladoria'] )){
			$sAcesso = "controladoria";
		}
		
		$rs["acesso"] = $sAcesso;
		
		$_SESSION ['user'] = $rs;
		header ( 'location: home.php' );
	} else {
		header ( 'location: login.php?msg=erro2' );
		exit;
	}
}

?>