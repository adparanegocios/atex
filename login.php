<?php

include_once 'classes/Template.class.php';

$tpl = new Template ( 'login.html' );

if ((isset ( $_REQUEST ['msg'] )) && ($_REQUEST ['msg'] == 'erro1')) {
	$tpl->MSG = 'Acesso n�o autorizado!';
} elseif ((isset ( $_REQUEST ['msg'] )) && ($_REQUEST ['msg'] == 'erro2')) {
	$tpl->MSG = 'Falha na autentica��o!';
} else {
	$tpl->MSG = '';
}

$tpl->show ();

?>