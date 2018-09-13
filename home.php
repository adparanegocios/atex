<?php
set_time_limit ( 0 );
@session_start ();
include_once 'conexao.php';
include_once 'classes/class.Util.php';
include_once 'classes/Template.class.php';
include_once 'global.php';
include_once 'validacao.php';

$tpl = new Template ( 'home-tpl.php' );

# INICIO INCLUDE #

if (isset ( $vPermissao ['tem'] [$_SESSION ['user'] ['login']] ) or isset ( $vPermissao ['controladoria'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "menu-tpl.php" );
} elseif (isset ( $vPermissao ['noticiario'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "menu-tpl2.php" );
}elseif (isset ( $vPermissao ['assistente'] [$_SESSION ['user'] ['login']] )) {
	$tpl->addFile ( "MENU", "menu-tpl.assistente.php" );	
}else {
	$tpl->addFile ( "MENU", "menu-tpl3.php" );
}

$tpl->USUARIO_LOGADO = $_SESSION ['user'] ['nome'];
# FIM INCLUDE #

$tpl->show ();
?>